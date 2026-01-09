<?php

/**
 * This processes X12 835 remittances and produces a report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Buffer all output so we can archive it to a file.
ob_start();

require_once("../globals.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Billing\ParseERA;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

/** @var int $debug */
$debug = $_GET['debug'] ? 1 : 0; // set to 1 for debugging mode
/** @var string $paydate */
$paydate = parseDate($_GET['paydate']);
/** @var int $encount */
$encount = 0;

/** @var string $last_ptname */
$last_ptname = '';
/** @var string $last_invnumber */
$last_invnumber = '';
/** @var string $last_code */
$last_code = '';
/** @var float $invoice_total */
$invoice_total = 0.00;
/** @var array<string, int> $InsertionId last inserted ID of */
$InsertionId = [];
/** @var string $StringToEcho a manual buffer */
$StringToEcho = '';

///////////////////////// Assorted Functions /////////////////////////

/**
 * Parse a date string into YYYY-MM-DD format.
 *
 * @param string $date The date string to parse
 * @return string The formatted date string or empty string if invalid
 */
function parseDate(string $date): string
{
    $date = substr(trim($date), 0, 10);
    if (preg_match('/^(\d\d\d\d)\D*(\d\d)\D*(\d\d)$/', $date, $matches)) {
        return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }

    return '';
}

/**
 * Gets a message line for the EOB report HTML table.
 *
 * Returns a table row spanning most columns with informational, warning, or error messages.
 * Optionally converts newlines to <br> tags for multi-line messages.
 *
 * @param string $bgcolor The background color for the table row
 * @param string $class The CSS class for styling (e.g., 'infdetail', 'errdetail')
 * @param string $description The message text to display
 * @param bool $nl2br_process Whether to convert newlines to <br> tags
 * @return string The HTML table row
 */
function getMessageLine(string $bgcolor, string $class, string $description, bool $nl2br_process = false): string
{
    $descriptionText = $nl2br_process ? nl2br(text($description)) : text($description);
    $bgcolorAttr = attr($bgcolor);
    $classAttr = attr($class);
    return <<<HTML
     <tr bgcolor='$bgcolorAttr'>
      <td class='$classAttr' colspan='4'></td>
      <td class='$classAttr'>$descriptionText</td>
      <td class='$classAttr' colspan='2'></td>
     </tr>

    HTML;
}

/**
 * Gets a detail line for the EOB report HTML table.
 *
 * Returns an HTML table row with payment/adjustment details. Suppresses duplicate values
 * for patient name, invoice number, and code when they match the previous row.
 *
 * @param string $bgcolor The background color for the table row
 * @param string $class The CSS class for styling (e.g., 'olddetail', 'newdetail', 'errdetail')
 * @param string $ptname The patient name
 * @param string $invnumber The invoice number
 * @param string $code The procedure code (CPT/HCPCS with optional modifiers)
 * @param string $date The transaction date
 * @param string $description The transaction description
 * @param float|int $amount The transaction amount
 * @param float|int|string $balance The running balance after this transaction
 * @return string The HTML table row
 */
function getDetailLine(
    string $bgcolor,
    string $class,
    string $ptname,
    string $invnumber,
    string $code,
    string $date,
    string $description,
    float|int $amount,
    float|int|string $balance
): string {

    global $last_ptname, $last_invnumber, $last_code;
    if ($ptname === $last_ptname) {
        $ptname = '';
    } else {
        $last_ptname = $ptname;
    }

    if ($invnumber === $last_invnumber) {
        $invnumber = '';
    } else {
        $last_invnumber = $invnumber;
    }

    if ($code === $last_code) {
        $code = '';
    } else {
        $last_code = $code;
    }

    $bgcolorAttr = attr($bgcolor);
    $classAttr = attr($class);
    $ptnameText = ($ptname === '&nbsp;') ? '' : text($ptname);
    $invnumberText = ($invnumber === '&nbsp;') ? '' : text($invnumber);
    $codeText = ($code === '&nbsp;') ? '' : text($code);
    $dateText = text(oeFormatShortDate($date));
    $descriptionText = text($description);
    $amountText = $amount ? text(oeFormatMoney($amount)) : '';
    $balanceText = $balance ? text(oeFormatMoney($balance)) : '';

    return <<<HTML
     <tr bgcolor='$bgcolorAttr'>
      <td class='$classAttr'>$ptnameText</td>
      <td class='$classAttr'>$invnumberText</td>
      <td class='$classAttr'>$codeText</td>
      <td class='$classAttr'>$dateText</td>
      <td class='$classAttr'>$descriptionText</td>
      <td class='$classAttr' align='right'>$amountText</td>
      <td class='$classAttr' align='right'>$balanceText</td>
     </tr>

    HTML;
}

/**
 * Gets detail lines that were already in SQL-Ledger for a given charge item.
 *
 * @param array $prev The previous charge details array containing 'dtl' with charges and payments
 * @param string $ptname The patient name
 * @param string $invnumber The invoice number
 * @param string $dos The date of service (YYYY-MM-DD format)
 * @param string $code The procedure code (CPT/HCPCS with optional modifiers)
 * @param string $bgcolor The background color for the HTML table row
 * @return Generator<string> Yields HTML table rows for each detail line
 */
function getOldDetail(array &$prev, string $ptname, string $invnumber, string $dos, string $code, string $bgcolor): Generator
{
    global $invoice_total;
    // $prev['total'] = 0.00; // to accumulate total charges
    ksort($prev['dtl']);
    foreach ($prev['dtl'] as $dkey => $ddata) {
        $ddate = substr((string) $dkey, 0, 10);
        $description = ($ddata['src'] ?? '') . ($ddata['rsn'] ?? '');
        if ($ddate === '          ') { // this is the service item
            $ddate = $dos;
            $description = 'Service Item';
        }

        $amount = sprintf("%.2f", (floatval($ddata['chg'] ?? '')) - (floatval($ddata['pmt'] ?? '')));
        $invoice_total = sprintf("%.2f", $invoice_total + $amount);
        yield getDetailLine(
            bgcolor: $bgcolor,
            class: 'olddetail',
            ptname: $ptname,
            invnumber: $invnumber,
            code: $code,
            date: $ddate,
            description: $description,
            amount: $amount,
            balance: $invoice_total
        );
    }
}

// This is called back by ParseERA::parseERA() once per claim.

// TODO: Sort colors here for Bootstrap themes
/**
 * Callback function for ERA check processing.
 *
 * @param array $out The ERA output data containing check information
 * @return void
 */
function era_callback_check(array &$out): void
{
    // last inserted ID of ar_session table
    global $InsertionId;
    global $StringToEcho, $debug;

    if (!empty($_GET['original']) && $_GET['original'] === 'original') {
        $StringToEcho .= "<table class='table'>";
        $StringToEcho .= "<thead>";
        $StringToEcho .= "<tr>";
        $StringToEcho .= "<th scope='col'>" . xlt('Check Number') . "</th>";
        $StringToEcho .= "<th scope='col'>" . xlt('Payee Name') . "</th>";
        $StringToEcho .= "<th scope='col'>" . xlt('Payer Name') . "</th>";
        $StringToEcho .= "<th scope='col'>" . xlt('Check Amount') . "</th>";
        $StringToEcho .= "</tr>";
        $StringToEcho .= "</thead>";
        $StringToEcho .= "<tbody>";
        $WarningFlag = false;
        for ($check_count = 1; $check_count <= $out['check_count']; $check_count++) {
            $bgcolor = $check_count % 2 === 1 ? '#ddddff' : '#ffdddd';

            $rs = sqlQ("select reference from ar_session where reference=?", [$out['check_number' . $check_count]]);

            if (sqlNumRows($rs) > 0) {
                $bgcolor = '#ff0000';
                $WarningFlag = true;
            }

            $StringToEcho .= "<tr bgcolor='" . attr($bgcolor) . "'>";
            $StringToEcho .= "<th scope='row'>";
            $StringToEcho .= "<input type='checkbox' name='chk" . attr($out['check_number' . $check_count]) . "' id='chk" . attr($out['check_number' . $check_count]) . "'/>";
            $StringToEcho .= "<label for='chk" . attr($out['check_number' . $check_count]) . "'>";
            $StringToEcho .= "&nbsp" . text($out['check_number' . $check_count]) . "</label>";
            $StringToEcho .= "</th>";
            $StringToEcho .= "<td>" . text($out['payee_name' . $check_count]) . "</td>";
            $StringToEcho .= "<td>" . text($out['payer_name' . $check_count]) . "</td>";
            $StringToEcho .= "<td>" . text(number_format($out['check_amount' . $check_count], 2)) . "</td>";
            $StringToEcho .= "</tr>";
        }

        $StringToEcho .= "<tr class='table-light'><td align='left'><button type='button' class='btn btn-secondary btn-save' name='Submit1' onclick='checkAll(true)'>" . xlt('Check All') . "</button></td>";
        $StringToEcho .= "<td><input type='submit' name='CheckSubmit' value='Submit'/></td>";
        $StringToEcho .= "</tr>";

        if ($WarningFlag === true) {
            $StringToEcho .= "<tr class='table-danger'><td colspan='4' align='center'>" . xlt('Warning, Check Number already exist in the database') . "</td></tr>";
        }
        $StringToEcho .= "</tbody>";
        $StringToEcho .= "</table>";
    } else {
        for ($check_count = 1; $check_count <= $out['check_count']; $check_count++) {
            $chk_num = $out['check_number' . $check_count];
            $chk_num = str_replace(' ', '_', $chk_num);
            if (isset($_REQUEST['chk' . $chk_num])) {
                $check_date = $out['check_date' . $check_count] ?: $_REQUEST['paydate'];
                $post_to_date = $_REQUEST['post_to_date'] ?: date('Y-m-d');
                $deposit_date = $_REQUEST['deposit_date'] ?: date('Y-m-d');
                $InsertionId[$out['check_number' . $check_count]] = SLEOB::arPostSession(
                    payer_id: $_REQUEST['InsId'],
                    check_number: $out['check_number' . $check_count],
                    check_date: $out['check_date' . $check_count],
                    pay_total: $out['check_amount' . $check_count],
                    post_to_date: $post_to_date,
                    deposit_date: $deposit_date,
                    debug: $debug
                );
            }
        }
    }
}

/**
 * Callback function for processing ERA claim details.
 *
 * @param array $out The ERA output data containing claim information
 * @return void
 */
function era_callback(array &$out): void
{
    global $encount, $debug;
    global $invoice_total, $last_code, $paydate;
    // last inserted ID of ar_session table
    global $InsertionId;

    // Some heading information.
    $chk_123 = str_replace(' ', '_', $out['check_number']);
    if (isset($_REQUEST['chk' . $chk_123])) {
        if ($encount === 0) {
            echo getMessageLine(
                'var(--white)',
                'infdetail',
                "Payer: " . $out['payer_name']
            );
            if ($debug) {
                echo getMessageLine(
                    'var(--white)',
                    'infdetail',
                    "WITHOUT UPDATE is selected; no changes will be applied."
                );
            }
        }

        $last_code = '';
        $invoice_total = 0.00;
        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
        [$pid, $encounter, $invnumber] = SLEOB::slInvoiceNumber($out);

        // Get details, if we have them, for the invoice.
        $inverror = true;
        $codes = [];
        if ($pid && $encounter) {
            // Get invoice data into $arrow or $ferow.
            $ferow = sqlQuery("SELECT e.*, p.fname, p.mname, p.lname " .
            "FROM form_encounter AS e, patient_data AS p WHERE " .
            "e.pid = ? AND e.encounter = ? AND " .
            "p.pid = e.pid", [$pid, $encounter]);
            if (empty($ferow)) {
                  $pid = $encounter = 0;
                  $invnumber = $out['our_claim_id'];
            } else {
                  $inverror = false;
                  $codes = InvoiceSummary::arGetInvoiceSummary($pid, $encounter, true);
                  // $svcdate = substr($ferow['date'], 0, 10);
            }
        }

        // Show the claim status.
        $csc = $out['claim_status_code'];
        $inslabel = match ($csc) {
            '2', '20' => 'Ins2',
            '3', '21' => 'Ins3',
            default => 'Ins1'
        };

        $primary = ($inslabel === 'Ins1');
        echo getMessageLine(
            $bgcolor,
            'infdetail',
            "Claim status $csc: " . BillingUtilities::CLAIM_STATUS_CODES_CLP02[$csc]
        );

        // Show an error message if the claim is missing or already posted.
        if ($inverror) {
            echo getMessageLine(
                $bgcolor,
                'errdetail',
                "The following claim is not in our database"
            );
        } else {
            // Skip this test. Claims can get multiple CLPs from the same payer!
            //
            // $insdone = strtolower($arrow['shipvia']);
            // if (strpos($insdone, 'ins1') !== false) {
            //  $inverror = true;
            //  writeMessageLine($bgcolor, 'errdetail',
            //   "Primary insurance EOB was already posted for the following claim");
            // }
        }

        if ($csc === '4') {
            // Denial case, code is stored in the claims table for display in the billing manager screen with reason explained.
            $inverror = true;
            if (!$debug) {
                if ($pid && $encounter) {
                    $code_value = '';
                    foreach ($out['svc'] as $svc) {
                        foreach ($svc['adj'] as $adj) {
                            // Per code and modifier the reason will be showed in the billing manager.
                            $code_value .= $svc['code'] . '_' . $svc['mod'] . '_' . $adj['group_code'] . '_' . $adj['reason_code'] . ',';
                        }
                    }

                    $code_value = substr($code_value, 0, -1);
                    // We store the reason code to display it with description in the billing manager screen.
                    // process_file is used as for the denial case file name will not be there, and extra field(to store reason) can be avoided.
                    BillingUtilities::updateClaim(true, $pid, $encounter, $_REQUEST['InsId'], substr($inslabel, 3), 7, 0, $code_value);
                }
            }

            echo getMessageLine(
                $bgcolor,
                'errdetail',
                "Not posting adjustments for denied claims, please follow up manually!"
            );
        } elseif ($csc === '22') {
            $inverror = true;
            echo getMessageLine(
                $bgcolor,
                'errdetail',
                "Payment reversals are not automated, please enter manually!"
            );
        }

        $warnings = rtrim((string) $out['warnings']);
        if ($warnings) {
            echo getMessageLine(
                bgcolor: $bgcolor,
                class: 'infdetail',
                description: $warnings,
                nl2br_process: true
            );
        }

        // Simplify some claim attributes for cleaner code.
        $service_date = parseDate($out['dos'] ?? $out['claim_date']);
        $check_date = $paydate ?: parseDate($out['check_date']);
        $production_date = $paydate ?: parseDate($out['production_date']);

        $insurance_id = SLEOB::arGetPayerID($pid, $service_date, substr($inslabel, 3));
        $patient_name = empty($ferow['lname'])
            ? $out['patient_fname'] . ' ' . $out['patient_lname']
            : $ferow['fname'] . ' ' . $ferow['lname'];

        $error = $inverror;

        // create array of cpts and mods for complex matching
        $codes_arr_keys = array_keys($codes);
        $cpts = [];
        $mods = [];
        foreach ($codes_arr_keys as $value) {
            $tmp = explode(":", (string) $value);
            $count = count($tmp) - 1;
            $cpt = $tmp[0];
            $cpts[] = $cpt;
            for ($i = 1; $i <= $count; $i++) {
                $mods[$cpt][] = $tmp[$i] ?? null;
            }
        }

        // This loops once for each service item in this claim.
        foreach ($out['svc'] as $svc) {
            // Treat a modifier in the remit data as part of the procedure key.
            // This key will then make its way into SQL-Ledger.
            $codekey = $svc['code'];
            if ($svc['mod']) {
                $codekey .= ':' . $svc['mod'];
            }

            $prev = $codes[$codekey] ?? '';
            // However sometimes a secondary insurance doesn't return the modifier that was on the service item
            // that was processed by the primary payer so try to deal with that
            if (!$prev && !$svc['mod'] && in_array($svc['code'], $cpts)) {
                foreach ($cpts as $v) {
                    if ($v === $svc['code']) {
                        $codekey = $v . ':' . implode(':', $mods[$v] ?? []);
                    }
                }
                $prev = $codes[$codekey] ?? '';
            }
            $codetype = ''; //will hold code type, if exists

            // This reports detail lines already on file for this service item.
            if ($prev) {
                $codetype = $codes[$codekey]['code_type'] ?? 'none'; // store code type
                $oldDetails = iterator_to_array(getOldDetail(
                    prev: $prev,
                    ptname: $patient_name,
                    invnumber: $invnumber,
                    dos: $service_date,
                    code: $codekey,
                    bgcolor: $bgcolor
                ));
                echo implode('', $oldDetails);
                // Check for sanity in amount charged.
                $prevchg = sprintf("%.2f", $prev['chg'] + ($prev['adj'] ?? null));
                if ($prevchg !== sprintf("%.2f", abs($svc['chg']))) {
                    echo getMessageLine(
                        $bgcolor,
                        'errdetail',
                        "EOB charge amount " . $svc['chg'] . " for this code does not match our invoice"
                    );
                    $error = true;
                }

                unset($codes[$codekey]);
            } else {
                // If the service item is not in our database...
                // This is not an error. If we are not in error mode and not debugging,
                // insert the service item into billing. Then display it (in green if it
                // was inserted, or in red if we are in error mode).
                // Check the global to see if this is preferred to be an error.
                if ($GLOBALS['add_unmatched_code_from_ins_co_era_to_billing'] ?? '') {
                    $description = "CPT4:$codekey Added by $inslabel $production_date";
                } else {
                    $error = true;
                    $description = "CPT4:$codekey returned by $inslabel $production_date";
                }
                if (!$error && !$debug) {
                    SLEOB::arPostCharge(
                        patient_id: $pid,
                        encounter_id: $encounter,
                        session_id: 0,
                        amount: $svc['chg'],
                        units: 1,
                        thisdate: $service_date,
                        code: $codekey,
                        description: $description,
                        debug: $debug,
                        codetype: $codetype
                    );
                    $invoice_total += $svc['chg'];
                }

                $class = $error ? 'errdetail' : 'newdetail';
                echo getDetailLine(
                    bgcolor: $bgcolor,
                    class: $class,
                    ptname: $patient_name,
                    invnumber: $invnumber,
                    code: $codekey,
                    date: $production_date,
                    description: $description,
                    amount: $svc['chg'],
                    balance: ($error ? '' : $invoice_total)
                );
            }

            $class = $error ? 'errdetail' : 'newdetail';

            // Report Allowed Amount.
            if ($svc['allowed'] ?? '') {
                echo getMessageLine(
                    $bgcolor,
                    'infdetail',
                    'Allowed amount is ' . sprintf("%.2f", $svc['allowed'])
                );
            }

            // Report miscellaneous remarks.
            if ($svc['remark'] ?? '') {
                $rmk = $svc['remark'];
                echo getMessageLine($bgcolor, 'infdetail', "$rmk: " .
                    BillingUtilities::REMITTANCE_ADVICE_REMARK_CODES[$rmk]);
            }

            // Post and report the payment for this service item from the ERA.
            // By the way a 'Claim' level payment is probably going to be negative,
            // i.e. a payment reversal.
            if ($svc['paid'] ?? '') {
                if (!$error && !$debug) {
                    SLEOB::arPostPayment(
                        patient_id: $pid,
                        encounter_id: $encounter,
                        session_id: $InsertionId[$out['check_number']],
                        amount: $svc['paid'],
                        code: $codekey,
                        payer_type: substr($inslabel, 3),
                        memo: $out['check_number'],
                        debug: $debug,
                        time: '',
                        codetype: $codetype,
                        date: $check_date,
                        payer_claim_number: $out['payer_claim_id']
                    );
                    $invoice_total -= $svc['paid'];
                }

                $description = "$inslabel/" . $out['check_number'] . ' payment';
                if ($svc['paid'] < 0) {
                    $description .= ' reversal';
                }

                echo getDetailLine(
                    bgcolor: $bgcolor,
                    class: $class,
                    ptname: $patient_name,
                    invnumber: $invnumber,
                    code: $codekey,
                    date: $check_date,
                    description: $description,
                    amount: 0 - $svc['paid'],
                    balance: ($error ? '' : $invoice_total)
                );
            }

            // Post and report adjustments from this ERA.  Posted adjustment reasons
            // must be 25 characters or less in order to fit on patient statements.
            foreach ($svc['adj'] as $adj) {
                $description = ($adj['reason_code'] ?? '') . ': ' .
                    BillingUtilities::CLAIM_ADJUSTMENT_REASON_CODES[$adj['reason_code'] ?? ''];
                if ($adj['group_code'] === 'PR' || !$primary) {
                    // Group code PR is Patient Responsibility.  Enter these as zero
                    // adjustments to retain the note without crediting the claim.
                    if ($primary) {
                        // Reasons should be 25 chars or less.
                        $reason = match ($adj['reason_code']) {
                            '1' => "$inslabel dedbl: ",
                            '2' => "$inslabel coins: ",
                            '3' => "$inslabel copay: ",
                            default => "$inslabel ptresp: "
                        };
                    } else {
                        // Non-primary insurance adjustments are garbage, either repeating
                        // the primary or are not adjustments at all.  Report them as notes
                        // but do not post any amounts.
                        $reason = "$inslabel note " . $adj['reason_code'] . ': ';
                    }

                    $reason .= sprintf("%.2f", $adj['amount']);
                    // Post a zero-dollar adjustment just to save it as a comment.
                    if (!$error && !$debug) {
                        SLEOB::arPostAdjustment(
                            patient_id: $pid,
                            encounter_id: $encounter,
                            session_id: $InsertionId[$out['check_number']],
                            amount: 0,
                            code: $codekey,
                            payer_type: substr($inslabel, 3),
                            reason: $reason,
                            debug: $debug,
                            time: '',
                            codetype: $codetype,
                        );
                    }

                    echo getMessageLine($bgcolor, $class, $description . ' ' .
                    sprintf("%.2f", $adj['amount']));
                } elseif (
                    $svc['paid'] === 0
                    && !(
                        $adj['group_code'] === "CO"
                        && (
                            $adj['reason_code'] === '45'
                            || $adj['reason_code'] === '59'
                        )
                    )
                ) {
                    $class = 'errdetail';
                    $error = true;
                } elseif (!$error && !$debug) {
                    SLEOB::arPostAdjustment(
                        patient_id: $pid,
                        encounter_id: $encounter,
                        session_id: $InsertionId[$out['check_number']],
                        amount: $adj['amount'],
                        code: $codekey,
                        payer_type: substr($inslabel, 3),
                        reason: "Adjust code " . $adj['reason_code'],
                        debug: $debug,
                        time: '',
                        codetype: $codetype,
                    );
                    $invoice_total -= $adj['amount'];
                }

                echo getDetailLine(
                    bgcolor: $bgcolor,
                    class: $class,
                    ptname: $patient_name,
                    invnumber: $invnumber,
                    code: $codekey,
                    date: $production_date,
                    description: $description,
                    amount: 0 - $adj['amount'],
                    balance: ($error ? '' : $invoice_total)
                );
            }
        } // End of service item

        // Report any existing service items not mentioned in the ERA, and
        // determine if any of them are still missing an insurance response
        // (if so, then insurance is not yet done with the claim).
        $insurance_done = true;
        foreach ($codes as $code => $prev) {
            $oldDetails = iterator_to_array(getOldDetail(
                prev: $prev,
                ptname: $patient_name,
                invnumber: $invnumber,
                dos: $service_date,
                code: $code,
                bgcolor: $bgcolor
            ));
            echo implode('', $oldDetails);
            $got_response = false;
            foreach ($prev['dtl'] as $ddata) {
                if ($ddata['pmt'] ?? '' || ($ddata['rsn'] ?? '')) {
                    $got_response = true;
                }
            }

            if (!$got_response) {
                $insurance_done = false;
            }
        }

        // Cleanup: If all is well, mark Ins<x> done and check for secondary billing.
        if (!$error && !$debug && $insurance_done) {
            $level_done = (int)substr($inslabel, 3);

            if ($out['crossover'] === 1) {
                // Automatic forward case. So need not bill again from the billing manager screen.
                sqlStatement("UPDATE form_encounter " .
                "SET last_level_closed = ?,last_level_billed=? WHERE " .
                "pid = ? AND encounter = ?", [$level_done, $level_done, $pid, $encounter]);
                echo getMessageLine(
                    $bgcolor,
                    'infdetail',
                    'This claim is processed by Insurance ' . $level_done . ' and automatically forwarded to Insurance ' . ($level_done + 1) . ' for processing. '
                );
            } else {
                sqlStatement("UPDATE form_encounter " .
                "SET last_level_closed = ? WHERE " .
                "pid = ? AND encounter = ?", [$level_done, $pid, $encounter]);
            }

            // Check for secondary insurance.
            if ($primary && SLEOB::arGetPayerID($pid, $service_date, 2)) {
                SLEOB::arSetupSecondary($pid, $encounter, $debug, $out['crossover']);

                if ($out['crossover'] <> 1) {
                    echo getMessageLine(
                        $bgcolor,
                        'infdetail',
                        'This claim is now re-queued for secondary paper billing'
                    );
                }
            }
        }
    }
}

/////////////////////////// End Functions ////////////////////////////

$info_msg = "";

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$eraname = $_REQUEST['eraname'];

if (!$eraname) {
    die(xlt("You cannot access this page directly."));
}

// Open the output file early so that in case it fails, we do not post a
// bunch of stuff without saving the report.  Also be sure to retain any old
// report files.  Do not save the report if this is a no-update situation.

// Common path used by parseERAForCheck() calls
$nameprefix = $GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname";
$eraFilePath = $nameprefix . '.edi';

if (!$debug) {
    $namesuffix = '';
    for ($i = 1; is_file("$nameprefix$namesuffix.html"); ++$i) {
        $namesuffix = "_$i";
    }

    $fnreport = "$nameprefix$namesuffix.html";
    $fhreport = fopen($fnreport, 'w');
    if (!$fhreport) {
        die(xlt("Cannot create") . " '" . text($fnreport) . "'");
    }
}

?>
<html>
<head>
<?php Header::setupHeader(); ?>
<style>
    body {
        font-family: sans-serif;
        font-size: 0.6875rem;
        font-weight: normal;
    }
    .dehead {
        font-family: sans-serif;
        font-size: 0.75rem;
        font-weight: bold;
    }
    .olddetail {
        font-family: sans-serif;
        font-size: 0.75rem;
        font-weight: normal;
    }
    .newdetail {
        color: var(--success);
        font-family: sans-serif;
        font-size: 0.75rem;
        font-weight: normal;
    }
    .errdetail {
        color: var(--danger);
        font-family: sans-serif;
        font-size: 0.75rem;
        font-weight: normal;
    }
    .infdetail {
        color: var(--primary);
        font-family: sans-serif;
        font-size: 0.75rem;
        font-weight: normal;
    }
</style>
<title><?php echo xlt('EOB Posting - Electronic Remittances'); ?></title>
</head>
<body class='m-0'>
<form action="sl_eob_process.php" method="get">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<?php
if (!empty($_GET['original']) && $_GET['original'] === 'original') {
    $alertmsg = ParseERA::parseERAForCheck($eraFilePath);
    echo $StringToEcho;
} else {
    ?>
    <table class='table table-borderless w-100' cellpadding='2' cellspacing='0'>

     <tr class="table-light">
      <td class="dehead">
    <?php echo xlt('Patient'); ?>
      </td>
      <td class="dehead">
    <?php echo xlt('Invoice'); ?>
      </td>
      <td class="dehead">
    <?php echo xlt('Code'); ?>
      </td>
      <td class="dehead">
    <?php echo xlt('Date'); ?>
      </td>
      <td class="dehead">
    <?php echo xlt('Description'); ?>
      </td>
      <td class="dehead" align="right">
    <?php echo xlt('Amount'); ?>&nbsp;
      </td>
      <td class="dehead" align="right">
    <?php echo xlt('Balance'); ?>&nbsp;
      </td>
     </tr>

    <?php
    global $InsertionId;

    $alertmsg = (
        ParseERA::parseERAForCheck($eraFilePath)
        . ParseERA::parseERA($eraFilePath, 'era_callback')
    );
    if (!$debug) {
          $StringIssue = xl("Total Distribution for following check number is not full") . ': ';
          $StringPrint = 'No';
        if (is_countable($InsertionId)) {
            foreach ($InsertionId as $key => $value) {
                $rs = sqlQ("select pay_total from ar_session where session_id=?", [$value]);
                $row = sqlFetchArray($rs);
                $pay_total = $row['pay_total'];
                $rs = sqlQ(
                    "select sum(pay_amount) sum_pay_amount from ar_activity where deleted IS NULL AND session_id = ?",
                    [$value]
                );
                $row = sqlFetchArray($rs);
                $pay_amount = $row['sum_pay_amount'];

                if (($pay_total - $pay_amount) <> 0) {
                    $StringIssue .= $key . ' ';
                    $StringPrint = 'Yes';
                }
            }
        }

        if ($StringPrint === 'Yes') {
            echo "<script>alert(" . js_escape($StringIssue) . ")</script>";
        }
    }


    ?>
    </table>
    <?php
}
?>
<script>
<?php
if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
}
?>
function checkAll(checked) {
    var f = document.forms[0];
    for (var i = 0; i < f.elements.length; ++i) {
        var etype = f.elements[i].type;
        if (etype === 'checkbox')
            f.elements[i].checked = checked;
    }
}
</script>
<input type="hidden" name="paydate" value="<?php echo attr(DateToYYYYMMDD($_REQUEST['paydate'])); ?>" />
<input type="hidden" name="post_to_date" value="<?php echo attr(DateToYYYYMMDD($_REQUEST['post_to_date'] ?? '')); ?>" />
<input type="hidden" name="deposit_date" value="<?php echo attr(DateToYYYYMMDD($_REQUEST['deposit_date'] ?? '')); ?>" />
<input type="hidden" name="debug" value="<?php echo attr($_REQUEST['debug']); ?>" />
<input type="hidden" name="InsId" value="<?php echo attr($_REQUEST['InsId'] ?? ''); ?>" />
<input type="hidden" name="eraname" value="<?php echo attr($eraname); ?>" />
</form>
</body>
</html>
<?php
// Save all of this script's output to a report file.
if (!$debug) {
    fwrite($fhreport, ob_get_contents());
    fclose($fhreport);
}

ob_end_flush();
