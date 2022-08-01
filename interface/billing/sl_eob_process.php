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
use OpenEMR\Services\InsuranceService;

$debug = $_GET['debug'] ? 1 : 0; // set to 1 for debugging mode
$paydate = parse_date($_GET['paydate']);
$encount = 0;

$last_ptname = '';
$last_invnumber = '';
$last_code = '';
$invoice_total = 0.00;
$InsertionId; // last inserted ID of

///////////////////////// Assorted Functions /////////////////////////

function parse_date($date)
{
    $date = substr(trim($date), 0, 10);
    if (preg_match('/^(\d\d\d\d)\D*(\d\d)\D*(\d\d)$/', $date, $matches)) {
        return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }

    return '';
}

function writeMessageLine($bgcolor, $class, $description, $nl2br_process = "false")
{
    $dline =
    " <tr bgcolor='" . attr($bgcolor) . "'>\n" .
    "  <td class='" . attr($class) . "' colspan='4'></td>\n";
    if ($nl2br_process) {
        $dline .= "  <td class='" . attr($class) . "'>" . nl2br(text($description)) . "</td>\n";
    } else {
        $dline .= "  <td class='" . attr($class) . "'>" . text($description) . "</td>\n";
    }
    $dline .=
    "  <td class='" . attr($class) . "' colspan='2'></td>\n" .
    " </tr>\n";
    echo $dline;
}

function writeDetailLine(
    $bgcolor,
    $class,
    $ptname,
    $invnumber,
    $code,
    $date,
    $description,
    $amount,
    $balance
) {

    global $last_ptname, $last_invnumber, $last_code;
    if ($ptname == $last_ptname) {
        $ptname = '';
    } else {
        $last_ptname = $ptname;
    }

    if ($invnumber == $last_invnumber) {
        $invnumber = '';
    } else {
        $last_invnumber = $invnumber;
    }

    if ($code == $last_code) {
        $code = '';
    } else {
        $last_code = $code;
    }

    if ($amount) {
        $amount  = sprintf("%.2f", $amount);
    }

    if ($balance) {
        $balance = sprintf("%.2f", $balance);
    }

    $dline =
    " <tr bgcolor='" . attr($bgcolor) . "'>\n" .
    "  <td class='" . attr($class) . "'>" . text($ptname) . "</td>\n" .
    "  <td class='" . attr($class) . "'>" . text($invnumber) . "</td>\n" .
    "  <td class='" . attr($class) . "'>" . text($code) . "</td>\n" .
    "  <td class='" . attr($class) . "'>" . text(oeFormatShortDate($date)) . "</td>\n" .
    "  <td class='" . attr($class) . "'>" . text($description) . "</td>\n" .
    "  <td class='" . attr($class) . "' align='right'>" . text(oeFormatMoney($amount)) . "</td>\n" .
    "  <td class='" . attr($class) . "' align='right'>" . text(oeFormatMoney($balance)) . "</td>\n" .
    " </tr>\n";
    echo $dline;
}

    // This writes detail lines that were already in SQL-Ledger for a given
    // charge item.
    //
function writeOldDetail(&$prev, $ptname, $invnumber, $dos, $code, $bgcolor)
{
    global $invoice_total;
    // $prev['total'] = 0.00; // to accumulate total charges
    ksort($prev['dtl']);
    foreach ($prev['dtl'] as $dkey => $ddata) {
        $ddate = substr($dkey, 0, 10);
        $description = ($ddata['src'] ?? '') . ($ddata['rsn'] ?? '');
        if ($ddate == '          ') { // this is the service item
            $ddate = $dos;
            $description = 'Service Item';
        }

        $amount = sprintf("%.2f", (int)($ddata['chg'] ?? '') - (int)($ddata['pmt'] ?? ''));
        $invoice_total = sprintf("%.2f", $invoice_total + $amount);
        writeDetailLine(
            $bgcolor,
            'olddetail',
            $ptname,
            $invnumber,
            $code,
            $ddate,
            $description,
            $amount,
            $invoice_total
        );
    }
}

    // This is called back by ParseERA::parseERA() once per claim.
    //

// TODO: Sort colors here for Bootstrap themes
function era_callback_check(&$out)
{
    // last inserted ID of ar_session table
    global $InsertionId;
    global $StringToEcho,$debug;

    if (!empty($_GET['original']) && $_GET['original'] == 'original') {
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
            if ($check_count % 2 == 1) {
                $bgcolor = '#ddddff';
            } else {
                $bgcolor = '#ffdddd';
            }

            $rs = sqlQ("select reference from ar_session where reference=?", array($out['check_number' . $check_count]));

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

        if ($WarningFlag == true) {
            $StringToEcho .= "<tr class='table-danger'><td colspan='4' align='center'>" . xlt('Warning, Check Number already exist in the database') . "</td></tr>";
        }
        $StringToEcho .= "</tbody>";
        $StringToEcho .= "</table>";
    } else {
        for ($check_count = 1; $check_count <= $out['check_count']; $check_count++) {
            $chk_num = $out['check_number' . $check_count];
            $chk_num = str_replace(' ', '_', $chk_num);
            if (isset($_REQUEST['chk' . $chk_num])) {
                $check_date = $out['check_date' . $check_count] ? $out['check_date' . $check_count] : $_REQUEST['paydate'];
                $post_to_date = $_REQUEST['post_to_date'] != '' ? $_REQUEST['post_to_date'] : date('Y-m-d');
                $deposit_date = $_REQUEST['deposit_date'] != '' ? $_REQUEST['deposit_date'] : date('Y-m-d');
                $InsertionId[$out['check_number' . $check_count]] = SLEOB::arPostSession($_REQUEST['InsId'], $out['check_number' . $check_count], $out['check_date' . $check_count], $out['check_amount' . $check_count], $post_to_date, $deposit_date, $debug);
            }
        }
    }
}
function era_callback(&$out)
{
    global $encount, $debug;
    global $invoice_total, $last_code, $paydate;
    // last inserted ID of ar_session table
    global $InsertionId;

    // Some heading information.
    $chk_123 = $out['check_number'];
    $chk_123 = str_replace(' ', '_', $chk_123);
    if (isset($_REQUEST['chk' . $chk_123])) {
        if ($encount == 0) {
            writeMessageLine(
                'var(--white)',
                'infdetail',
                "Payer: " . $out['payer_name']
            );
            if ($debug) {
                writeMessageLine(
                    'var(--white)',
                    'infdetail',
                    "WITHOUT UPDATE is selected; no changes will be applied."
                );
            }
        }

        $last_code = '';
        $invoice_total = 0.00;
        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
        list($pid, $encounter, $invnumber) = SLEOB::slInvoiceNumber($out);

        // Get details, if we have them, for the invoice.
        $inverror = true;
        $codes = array();
        if ($pid && $encounter) {
            // Get invoice data into $arrow or $ferow.
            $ferow = sqlQuery("SELECT e.*, p.fname, p.mname, p.lname " .
            "FROM form_encounter AS e, patient_data AS p WHERE " .
            "e.pid = ? AND e.encounter = ? AND " .
            "p.pid = e.pid", array($pid, $encounter));
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
        $inslabel = 'Ins1';
        if ($csc == '1' || $csc == '19') {
            $inslabel = 'Ins1';
        }

        if ($csc == '2' || $csc == '20') {
            $inslabel = 'Ins2';
        }

        if ($csc == '3' || $csc == '21') {
            $inslabel = 'Ins3';
        }

        $primary = ($inslabel == 'Ins1');
        writeMessageLine(
            $bgcolor,
            'infdetail',
            "Claim status $csc: " . BillingUtilities::CLAIM_STATUS_CODES_CLP02[$csc]
        );

    // Show an error message if the claim is missing or already posted.
        if ($inverror) {
            writeMessageLine(
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

        if ($csc == '4') {//Denial case, code is stored in the claims table for display in the billing manager screen with reason explained.
            $inverror = true;
            if (!$debug) {
                if ($pid && $encounter) {
                    $code_value = '';
                    foreach ($out['svc'] as $svc) {
                        foreach ($svc['adj'] as $adj) {//Per code and modifier the reason will be showed in the billing manager.
                            $code_value .= $svc['code'] . '_' . $svc['mod'] . '_' . $adj['group_code'] . '_' . $adj['reason_code'] . ',';
                        }
                    }

                    $code_value = substr($code_value, 0, -1);
                    //We store the reason code to display it with description in the billing manager screen.
                    //process_file is used as for the denial case file name will not be there, and extra field(to store reason) can be avoided.
                    BillingUtilities::updateClaim(true, $pid, $encounter, $_REQUEST['InsId'], substr($inslabel, 3), 7, 0, $code_value);
                }
            }

            writeMessageLine(
                $bgcolor,
                'errdetail',
                "Not posting adjustments for denied claims, please follow up manually!"
            );
        } elseif ($csc == '22') {
            $inverror = true;
            writeMessageLine(
                $bgcolor,
                'errdetail',
                "Payment reversals are not automated, please enter manually!"
            );
        }

        if ($out['warnings']) {
            writeMessageLine($bgcolor, 'infdetail', rtrim($out['warnings']), true);
        }

    // Simplify some claim attributes for cleaner code.
        $service_date = parse_date(isset($out['dos']) ? $out['dos'] : $out['claim_date']);
        $check_date      = $paydate ? $paydate : parse_date($out['check_date']);
        $production_date = $paydate ? $paydate : parse_date($out['production_date']);

        $insurance_id = SLEOB::arGetPayerID($pid, $service_date, substr($inslabel, 3));
        if (empty($ferow['lname'])) {
              $patient_name = $out['patient_fname'] . ' ' . $out['patient_lname'];
        } else {
            $patient_name = $ferow['fname'] . ' ' . $ferow['lname'];
        }

        $error = $inverror;

    // This loops once for each service item in this claim.
        foreach ($out['svc'] as $svc) {
          // Treat a modifier in the remit data as part of the procedure key.
          // This key will then make its way into SQL-Ledger.
            $codekey = $svc['code'];
            if ($svc['mod']) {
                $codekey .= ':' . $svc['mod'];
            }

            $prev = $codes[$codekey] ?? '';
            $codetype = ''; //will hold code type, if exists

            // This reports detail lines already on file for this service item.
            if ($prev) {
                $codetype = $codes[$codekey]['code_type']; //store code type
                writeOldDetail($prev, $patient_name, $invnumber, $service_date, $codekey, $bgcolor);
                // Check for sanity in amount charged.
                $prevchg = sprintf("%.2f", $prev['chg'] + ($prev['adj'] ?? null));
                if ($prevchg != abs($svc['chg'])) {
                    writeMessageLine(
                        $bgcolor,
                        'errdetail',
                        "EOB charge amount " . $svc['chg'] . " for this code does not match our invoice"
                    );
                    $error = true;
                }

                // Check for already-existing primary remittance activity.
                // Removed this check because it was not allowing for copays manually
                // entered into the invoice under a non-copay billing code.
                /****
            if ((sprintf("%.2f",$prev['chg']) != sprintf("%.2f",$prev['bal']) ||
                $prev['adj'] != 0) && $primary)
            {
                writeMessageLine($bgcolor, 'errdetail',
                    "This service item already has primary payments and/or adjustments!");
                $error = true;
            }
                ****/

                unset($codes[$codekey]);
            } else { // If the service item is not in our database...
                // This is not an error. If we are not in error mode and not debugging,
                // insert the service item into SL.  Then display it (in green if it
                // was inserted, or in red if we are in error mode).
                $description = "CPT4:$codekey Added by $inslabel $production_date";
                if (!$error && !$debug) {
                    SLEOB::arPostCharge(
                        $pid,
                        $encounter,
                        0,
                        $svc['chg'],
                        1,
                        $service_date,
                        $codekey,
                        $description,
                        $debug,
                        '',
                        $codetype ?? ''
                    );
                    $invoice_total += $svc['chg'];
                }

                $class = $error ? 'errdetail' : 'newdetail';
                writeDetailLine(
                    $bgcolor,
                    $class,
                    $patient_name,
                    $invnumber,
                    $codekey,
                    $production_date,
                    $description,
                    $svc['chg'],
                    ($error ? '' : $invoice_total)
                );
            }

            $class = $error ? 'errdetail' : 'newdetail';

            // Report Allowed Amount.
            if ($svc['allowed'] ?? '') {
                writeMessageLine(
                    $bgcolor,
                    'infdetail',
                    'Allowed amount is ' . sprintf("%.2f", $svc['allowed'])
                );
            }

            // Report miscellaneous remarks.
            if ($svc['remark'] ?? '') {
                $rmk = $svc['remark'];
                writeMessageLine($bgcolor, 'infdetail', "$rmk: " .
                    BillingUtilities::REMITTANCE_ADVICE_REMARK_CODES[$rmk]);
            }

            // Post and report the payment for this service item from the ERA.
            // By the way a 'Claim' level payment is probably going to be negative,
            // i.e. a payment reversal.
            if ($svc['paid'] ?? '') {
                if (!$error && !$debug) {
                    SLEOB::arPostPayment(
                        $pid,
                        $encounter,
                        $InsertionId[$out['check_number']],
                        $svc['paid'], //$InsertionId[$out['check_number']] gives the session id
                        $codekey,
                        substr($inslabel, 3),
                        $out['check_number'],
                        $debug,
                        '',
                        $codetype
                    );
                    $invoice_total -= $svc['paid'];
                }

                $description = "$inslabel/" . $out['check_number'] . ' payment';
                if ($svc['paid'] < 0) {
                    $description .= ' reversal';
                }

                writeDetailLine(
                    $bgcolor,
                    $class,
                    $patient_name,
                    $invnumber,
                    $codekey,
                    $check_date,
                    $description,
                    0 - $svc['paid'],
                    ($error ? '' : $invoice_total)
                );
            }

            // Post and report adjustments from this ERA.  Posted adjustment reasons
            // must be 25 characters or less in order to fit on patient statements.
            foreach ($svc['adj'] as $adj) {
                $description = $adj['reason_code'] ?? '' . ': ' .
                    BillingUtilities::CLAIM_ADJUSTMENT_REASON_CODES[$adj['reason_code'] ?? ''];
                if ($adj['group_code'] == 'PR' || !$primary) {
                    // Group code PR is Patient Responsibility.  Enter these as zero
                    // adjustments to retain the note without crediting the claim.
                    if ($primary) {
                /****
                    $reason = 'Pt resp: '; // Reasons should be 25 chars or less.
                    if ($adj['reason_code'] == '1') $reason = 'To deductible: ';
                    else if ($adj['reason_code'] == '2') $reason = 'Coinsurance: ';
                    else if ($adj['reason_code'] == '3') $reason = 'Co-pay: ';
                ****/
                        $reason = "$inslabel ptresp: "; // Reasons should be 25 chars or less.
                        if ($adj['reason_code'] == '1') {
                            $reason = "$inslabel dedbl: ";
                        } elseif ($adj['reason_code'] == '2') {
                            $reason = "$inslabel coins: ";
                        } elseif ($adj['reason_code'] == '3') {
                            $reason = "$inslabel copay: ";
                        }
                    } else { // Non-primary insurance adjustments are garbage, either repeating
                        // the primary or are not adjustments at all.  Report them as notes
                        // but do not post any amounts.
                        $reason = "$inslabel note " . $adj['reason_code'] . ': ';
                /****
                    $reason .= sprintf("%.2f", $adj['amount']);
                ****/
                    }

                    $reason .= sprintf("%.2f", $adj['amount']);
                    // Post a zero-dollar adjustment just to save it as a comment.
                    if (!$error && !$debug) {
                        SLEOB::arPostAdjustment(
                            $pid,
                            $encounter,
                            $InsertionId[$out['check_number']],
                            0,
                            $codekey, //$InsertionId[$out['check_number']] gives the session id
                            substr($inslabel, 3),
                            $reason,
                            $debug,
                            '',
                            $codetype
                        );
                    }

                    writeMessageLine($bgcolor, $class, $description . ' ' .
                    sprintf("%.2f", $adj['amount']));
                } else { // Other group codes for primary insurance are real adjustments.
                    if (!$error && !$debug) {
                        SLEOB::arPostAdjustment(
                            $pid,
                            $encounter,
                            $InsertionId[$out['check_number']],
                            $adj['amount'], //$InsertionId[$out['check_number']] gives the session id
                            $codekey,
                            substr($inslabel, 3),
                            "Adjust code " . $adj['reason_code'],
                            $debug,
                            '',
                            $codetype ?? ''
                        );
                        $invoice_total -= $adj['amount'];
                    }

                    writeDetailLine(
                        $bgcolor,
                        $class,
                        $patient_name,
                        $invnumber,
                        $codekey,
                        $production_date,
                        $description,
                        0 - $adj['amount'],
                        ($error ? '' : $invoice_total)
                    );
                }
            }
        } // End of service item

    // Report any existing service items not mentioned in the ERA, and
    // determine if any of them are still missing an insurance response
    // (if so, then insurance is not yet done with the claim).
        $insurance_done = true;
        foreach ($codes as $code => $prev) {
          // writeOldDetail($prev, $arrow['name'], $invnumber, $service_date, $code, $bgcolor);
            writeOldDetail($prev, $patient_name, $invnumber, $service_date, $code, $bgcolor);
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
            $level_done = 0 + substr($inslabel, 3);

            if ($out['crossover'] == 1) {//Automatic forward case.So need not again bill from the billing manager screen.
                sqlStatement("UPDATE form_encounter " .
                "SET last_level_closed = ?,last_level_billed=? WHERE " .
                "pid = ? AND encounter = ?", array($level_done, $level_done, $pid, $encounter));
                writeMessageLine(
                    $bgcolor,
                    'infdetail',
                    'This claim is processed by Insurance ' . $level_done . ' and automatically forwarded to Insurance ' . ($level_done + 1) . ' for processing. '
                );
            } else {
                sqlStatement("UPDATE form_encounter " .
                "SET last_level_closed = ? WHERE " .
                "pid = ? AND encounter = ?", array($level_done, $pid, $encounter));
            }

            // Check for secondary insurance.
            if ($primary && SLEOB::arGetPayerID($pid, $service_date, 2)) {
                SLEOB::arSetupSecondary($pid, $encounter, $debug, $out['crossover']);

                if ($out['crossover'] <> 1) {
                    writeMessageLine(
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

$eraname = $_GET['eraname'];

if (! $eraname) {
    die(xlt("You cannot access this page directly."));
}

    // Open the output file early so that in case it fails, we do not post a
    // bunch of stuff without saving the report.  Also be sure to retain any old
    // report files.  Do not save the report if this is a no-update situation.
    //
if (!$debug) {
    $nameprefix = $GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname";
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
if (!empty($_GET['original']) && $_GET['original'] == 'original') {
    $alertmsg = ParseERA::parseERAForCheck($GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname.edi", 'era_callback');
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
    <?php echo xl('Balance'); ?>&nbsp;
      </td>
     </tr>

    <?php
    global $InsertionId;

    $eraname = $_REQUEST['eraname'];
    $alertmsg = ParseERA::parseERAForCheck($GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname.edi");
    $alertmsg = ParseERA::parseERA($GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname.edi", 'era_callback');
    if (!$debug) {
          $StringIssue = xl("Total Distribution for following check number is not full") . ': ';
          $StringPrint = 'No';
        if (is_countable($InsertionId)) {
            foreach ($InsertionId as $key => $value) {
                $rs = sqlQ("select pay_total from ar_session where session_id=?", array($value));
                $row = sqlFetchArray($rs);
                $pay_total = $row['pay_total'];
                $rs = sqlQ(
                    "select sum(pay_amount) sum_pay_amount from ar_activity where deleted IS NULL AND session_id = ?",
                    array($value)
                );
                $row = sqlFetchArray($rs);
                $pay_amount = $row['sum_pay_amount'];

                if (($pay_total - $pay_amount) <> 0) {
                    $StringIssue .= $key . ' ';
                    $StringPrint = 'Yes';
                }
            }
        }

        if ($StringPrint == 'Yes') {
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
?>
