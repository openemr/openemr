<?php

/**
 * This processes X12 835 remittances and produces a report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Buffer all output so we can archive it to a file.
ob_start();

require_once("../globals.php");

use OpenEMR\Billing\EraCheckPosting;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\EraPosting\ClaimPosting;
use OpenEMR\Billing\EraPosting\ReportMessage;
use OpenEMR\Billing\EraReversal;
use OpenEMR\Billing\ParseERA;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Recorder;

if (!AclMain::aclCheckCore('acct', 'bill', '', 'write') && !AclMain::aclCheckCore('acct', 'eob', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill or acct/eob: ERA Posting", xl("ERA Posting"));
}

/** @var int $debug */
$debug = $_GET['debug'] ? 1 : 0; // set to 1 for debugging mode
/** @var int $encount */
$encount = 0;

/** @var string $last_ptname */
$last_ptname = '';
/** @var string $last_invnumber */
$last_invnumber = '';
/** @var string $last_code */
$last_code = '';
/** @var array<string, int> $InsertionId last inserted ID of */
$InsertionId = [];
/** @var string $StringToEcho a manual buffer */
$StringToEcho = '';

///////////////////////// Assorted Functions /////////////////////////

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
 * @param bool $firstOfClaim The claim's first detail line: show its code even if the last claim ended with it
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
    float|int|string $balance,
    bool $firstOfClaim = false
): string {

    global $last_ptname, $last_invnumber, $last_code;
    if ($firstOfClaim) {
        $last_code = '';
    }
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

// This is called back by ParseERA::parseERA() once per claim.

// TODO: Sort colors here for Bootstrap themes
/**
 * Callback function for ERA check processing.
 *
 * @param array $out The ERA output data containing check information
 * @return void
 */
function eob_process_era_callback_check(array &$out): void
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

            $listedCheck = $out['check_number' . $check_count];
            $records = QueryUtils::fetchRecords(
                "select reference from ar_session where reference IN (?, ?)",
                EraCheckPosting::references(is_string($listedCheck) ? $listedCheck : '')
            );

            if (!empty($records)) {
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
                // A check posted before keeps its payment session: a second one would
                // count the check twice. Its claims already posted are skipped below.
                $thisCheck = $out['check_number' . $check_count];
                $thisCheck = is_string($thisCheck) ? $thisCheck : '';
                $thisCheckDate = $out['check_date' . $check_count];
                $eraRun = EraCheckPosting::forRequest(CurrentRequest::get());
                $existingSession = $eraRun->existingSession($thisCheck, is_string($thisCheckDate) ? $thisCheckDate : '');
                if ($existingSession > 0) {
                    $eraRun->reuseSession($thisCheck, $existingSession);
                }
                $InsertionId[$out['check_number' . $check_count]] = $existingSession > 0 ? $existingSession : SLEOB::arPostSession(
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
 * Callback function for processing ERA claim details: posts the claim payment
 * with ClaimPosting and writes its report lines.
 *
 * @param array $out The ERA output data containing claim information
 * @return void
 */
function eob_process_era_callback(array &$out): void
{
    global $encount, $debug;
    // last inserted ID of ar_session table
    global $InsertionId;

    $checkNumber = is_string($out['check_number'] ?? null) ? $out['check_number'] : '';

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
        $eraRun = EraCheckPosting::forRequest(CurrentRequest::get());
        $reusedSession = $eraRun->takeReusedSession($checkNumber);
        if ($reusedSession !== null) {
            echo getMessageLine(
                'var(--white)',
                'errdetail',
                "Check $checkNumber was posted before (payment session $reusedSession); "
                . "only claims not yet posted from it are posted now"
            );
        }

        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
        $query = CurrentRequest::get()->query;
        $payerId = $query->getString('InsId');
        $postUser = SessionWrapperFactory::getInstance()->getActiveSession()->get('authUserID');
        $claimPosting = new ClaimPosting(
            checkPosting: $eraRun,
            reversal: new EraReversal($payerId),
            recorder: new Recorder(),
            postUser: is_numeric($postUser) ? (int) $postUser : 0,
            payerId: $payerId,
            withoutUpdate: $debug === 1,
            payDate: ClaimPosting::parseDate($query->getString('paydate')),
            addUnmatchedCodes: OEGlobalsBag::getInstance()->getBoolean('add_unmatched_code_from_ins_co_era_to_billing'),
        );
        $sessionId = $InsertionId[$out['check_number']] ?? 0;
        $result = $claimPosting->post(
            ClaimPayment::fromParsedEra($out),
            is_numeric($sessionId) ? (int) $sessionId : 0
        );
        $firstOfClaim = true;
        foreach ($result->lines as $line) {
            if ($line instanceof ReportMessage) {
                echo getMessageLine($bgcolor, $line->style->cssClass(), $line->text, $line->multiline);
                continue;
            }
            echo getDetailLine(
                bgcolor: $bgcolor,
                class: $line->style->cssClass(),
                ptname: $line->patientName,
                invnumber: $line->invoiceNumber,
                code: $line->code,
                date: $line->date,
                description: $line->description,
                amount: $line->amount,
                balance: $line->balance,
                firstOfClaim: $firstOfClaim
            );
            $firstOfClaim = false;
        }
    }
}

/////////////////////////// End Functions ////////////////////////////

$info_msg = "";
$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

$eraname = filter_input(INPUT_GET, 'eraname') ?? '';
if (
    !$eraname
    || !preg_match('/^[A-Za-z0-9._-]+$/', $eraname)
    || str_contains($eraname, '..')
) {
    die(xlt("Invalid ERA name"));
}
$eraname = basename($eraname);

// Open the output file early so that in case it fails, we do not post a
// bunch of stuff without saving the report.  Also be sure to retain any old
// report files.  Do not save the report if this is a no-update situation.

// Common path used by parseERAForCheck() calls
$eraDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . "/documents/era";
if (!is_dir($eraDir) && !mkdir($eraDir, 0755, true) && !is_dir($eraDir)) {
    die(xlt("Cannot create ERA directory") . " '" . text($eraDir) . "'");
}

// Containment check mirroring era_payments.php: the resolved read target must
// sit inside $eraDir. The .edi is read/parsed; the .html write target is
// protected by basename() above (realpath() can't validate a not-yet-created file).
$realEraDir = realpath($eraDir);
$realEraFile = realpath("$eraDir/$eraname.edi");
if (
    $realEraFile !== false && $realEraDir !== false
    && !str_starts_with($realEraFile, $realEraDir . DIRECTORY_SEPARATOR)
) {
    die(xlt("Invalid ERA name"));
}

$nameprefix = "$eraDir/$eraname";
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
<form action="sl_eob_process.php" method="get" onsubmit="return submitOnce(this)">
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />

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
        . ParseERA::parseERA($eraFilePath, 'eob_process_era_callback')
    );
    if (!$debug) {
          $StringIssue = xl("Total Distribution for following check number is not full") . ': ';
          $StringPrint = 'No';
        if (is_countable($InsertionId)) {
            foreach ($InsertionId as $key => $value) {
                $row = QueryUtils::querySingleRow("select pay_total from ar_session where session_id=?", [$value]);
                $pay_total = $row['pay_total'];
                $row = QueryUtils::querySingleRow(
                    "select sum(pay_amount) sum_pay_amount from ar_activity where deleted IS NULL AND session_id = ?",
                    [$value]
                );
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
// Post a check list once: a double click or a second Enter would post the checks again.
function submitOnce(form) {
    if (form.dataset.submitted) {
        return false;
    }
    form.dataset.submitted = '1';
    setTimeout(function () {
        form.querySelectorAll('input[type=submit]').forEach(function (button) {
            button.disabled = true;
        });
    }, 0);
    return true;
}

// Coming back to the check list with the Back button can restore it from the
// browser's cache still marked as submitted; unlock it so it can be used again.
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        document.querySelectorAll('form').forEach(function (form) {
            delete form.dataset.submitted;
            form.querySelectorAll('input[type=submit]').forEach(function (button) {
                button.disabled = false;
            });
        });
    }
});

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
