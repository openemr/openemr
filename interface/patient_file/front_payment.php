<?php

/**
 * Front payment gui.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/payment.inc.php");
require_once("$srcdir/forms.inc");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/encounter_events.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\FacilityService;

$pid = (!empty($_REQUEST['hidden_patient_code']) && ($_REQUEST['hidden_patient_code'] > 0)) ? $_REQUEST['hidden_patient_code'] : $pid;

$facilityService = new FacilityService();

?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['opener']);?>
    <?php if ($GLOBALS['payment_gateway'] == 'Stripe') { ?>
        <script src="https://js.stripe.com/v3/"></script>
    <?php } ?>
    <?php if ($GLOBALS['payment_gateway'] == 'AuthorizeNet') {
        // Must be loaded from their server
        $script = "https://jstest.authorize.net/v1/Accept.js"; // test script
        if ($GLOBALS['gateway_mode_production']) {
            $script = "https://js.authorize.net/v1/Accept.js"; // Production script
        } ?>
        <script src=<?php echo $script; ?> charset="utf-8"></script>
    <?php } ?>
<?php
// Format dollars for display.

function bucks($amount)
{
    if ($amount) {
        $amount = oeFormatMoney($amount);
        return $amount;
    }
    return '';
}

function rawbucks($amount)
{
    if ($amount) {
        $amount = sprintf("%.2f", $amount);
        return $amount;
    }
    return '';
}

// Display a row of data for an encounter.
//
$var_index = 0;
function echoLine($iname, $date, $charges, $ptpaid, $inspaid, $duept, $encounter = 0, $copay = 0, $patcopay = 0)
{
    global $var_index;
    $var_index++;
    $balance = bucks($charges - $ptpaid - $inspaid);
    $balance = (round($duept, 2) != 0) ? 0 : $balance;//if balance is due from patient, then insurance balance is displayed as zero
    $encounter = $encounter ? $encounter : '';
    echo " <tr id='tr_" . attr($var_index) . "' >\n";
    echo "  <td>" . text(oeFormatShortDate($date)) . "</td>\n";
    echo "  <td class='text-center' id='" . attr($date) . "'>" . text($encounter) . "</td>\n";
    echo "  <td class='text-center' id='td_charges_$var_index' >" . text(bucks($charges)) . "</td>\n";
    echo "  <td class='text-center' id='td_inspaid_$var_index' >" . text(bucks($inspaid * -1)) . "</td>\n";
    echo "  <td class='text-center' id='td_ptpaid_$var_index' >" . text(bucks($ptpaid * -1)) . "</td>\n";
    echo "  <td class='text-center' id='td_patient_copay_$var_index' >" . text(bucks($patcopay)) . "</td>\n";
    echo "  <td class='text-center' id='td_copay_$var_index' >" . text(bucks($copay)) . "</td>\n";
    echo "  <td class='text-center' id='balance_$var_index'>" . text(bucks($balance)) . "</td>\n";
    echo "  <td class='text-center' id='duept_$var_index'>" . text(bucks(round($duept, 2) * 1)) . "</td>\n";
    echo "  <td class='text-right'><input type='text' class='form-control' name='" . attr($iname) . "'  id='paying_" . attr($var_index) . "' " .
        " value='' onchange='coloring();calctotal()'  autocomplete='off' " .
        "onkeyup='calctotal()'/></td>\n";
    echo " </tr>\n";
}

// We use this to put dashes, colons, etc. back into a timestamp.
//
function decorateString($fmt, $str)
{
    $res = '';
    while ($fmt) {
        $fc = substr($fmt, 0, 1);
        $fmt = substr($fmt, 1);
        if ($fc == '.') {
            $res .= substr($str, 0, 1);
            $str = substr($str, 1);
        } else {
            $res .= $fc;
        }
    }

    return $res;
}

// Compute taxes from a tax rate string and a possibly taxable amount.
//
function calcTaxes($row, $amount)
{
    $total = 0;
    if (empty($row['taxrates'])) {
        return $total;
    }

    $arates = explode(':', $row['taxrates']);
    if (empty($arates)) {
        return $total;
    }

    foreach ($arates as $value) {
        if (empty($value)) {
            continue;
        }

        $trow = sqlQuery("SELECT option_value FROM list_options WHERE " .
                "list_id = 'taxrate' AND option_id = ? AND activity = 1 LIMIT 1", array($value));
        if (empty($trow['option_value'])) {
            echo "<!-- Missing tax rate '" . text($value) . "'! -->\n";
            continue;
        }

        $tax = sprintf("%01.2f", $amount * $trow['option_value']);
        // echo "<!-- Rate = '$value', amount = '$amount', tax = '$tax' -->\n";
        $total += $tax;
    }

    return $total;
}

$now = time();
$today = date('Y-m-d', $now);
$timestamp = date('Y-m-d H:i:s', $now);

$patdata = sqlQuery("SELECT " .
    "p.fname, p.mname, p.lname, p.pubpid,p.pid, i.copay " .
    "FROM patient_data AS p " .
    "LEFT OUTER JOIN insurance_data AS i ON " .
    "i.pid = p.pid AND i.type = 'primary' " .
    "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", array($pid));

$alertmsg = ''; // anything here pops up in an alert box

// If the Save button was clicked...
if (!empty($_POST['form_save'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $form_pid = $_POST['form_pid'];
    $form_method = trim($_POST['form_method']);
    $form_source = trim($_POST['form_source']);
    $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');
    $NameNew = $patdata['fname'] . " " . $patdata['lname'] . " " . $patdata['mname'];

    if ($_REQUEST['radio_type_of_payment'] == 'pre_payment') {
            $payment_id = sqlInsert(
                "insert into ar_session set " .
                "payer_id = ?" .
                ", patient_id = ?" .
                ", user_id = ?" .
                ", closed = ?" .
                ", reference = ?" .
                ", check_date =  now() , deposit_date = now() " .
                ",  pay_total = ?" .
                ", payment_type = 'patient'" .
                ", description = ?" .
                ", adjustment_code = 'pre_payment'" .
                ", post_to_date = now() " .
                ", payment_method = ?",
                array(0, $form_pid, $_SESSION['authUserID'], 0, $form_source, $_REQUEST['form_prepayment'], $NameNew, $form_method)
            );

         frontPayment($form_pid, 0, $form_method, $form_source, $_REQUEST['form_prepayment'], 0, $timestamp);//insertion to 'payments' table.
    }

    if ($_POST['form_upay'] && $_REQUEST['radio_type_of_payment'] != 'pre_payment') {
        foreach ($_POST['form_upay'] as $enc => $payment) {
            if ($amount = 0 + $payment) {
                 $zero_enc = $enc;
                if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance') {
                    if (!$enc) {
                        $enc = calendar_arrived($form_pid);
                    }
                } else {
                    if (!$enc) {
                           $enc = calendar_arrived($form_pid);
                    }
                }

                //----------------------------------------------------------------------------------------------------
                  //Fetching the existing code and modifier
                  $ResultSearchNew = sqlStatement(
                      "SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key " .
                      "WHERE code_types.ct_fee=1 AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
                      array($form_pid, $enc)
                  );
                if ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                    $Codetype = $RowSearch['code_type'];
                    $Code = $RowSearch['code'];
                    $Modifier = $RowSearch['modifier'];
                } else {
                    $Codetype = '';
                    $Code = '';
                    $Modifier = '';
                }

                //----------------------------------------------------------------------------------------------------
                if ($_REQUEST['radio_type_of_payment'] == 'copay') {//copay saving to ar_session and ar_activity tables
                    $session_id = sqlInsert(
                        "INSERT INTO ar_session (payer_id,user_id,reference,check_date,deposit_date,pay_total," .
                        " global_amount,payment_type,description,patient_id,payment_method,adjustment_code,post_to_date) " .
                        " VALUES ('0',?,?,now(),now(),?,'','patient','COPAY',?,?,'patient_payment',now())",
                        array($_SESSION['authUserID'], $form_source, $amount, $form_pid, $form_method)
                    );

                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM       ar_activity WHERE pid = ? AND encounter = ?", array($form_pid, $enc));
                    $insrt_id = sqlInsert(
                        "INSERT INTO ar_activity (pid,encounter,sequence_no,code_type,code,modifier,payer_type,post_time,post_user,session_id,pay_amount,account_code)" .
                        " VALUES (?,?,?,?,?,?,0,now(),?,?,?,'PCP')",
                        array($form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, $_SESSION['authUserID'], $session_id, $amount)
                    );
                    sqlCommitTrans();

                    frontPayment($form_pid, $enc, $form_method, $form_source, $amount, 0, $timestamp);//insertion to 'payments' table.
                }

                if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance' || $_REQUEST['radio_type_of_payment'] == 'cash') {                //Payment by patient after insurance paid, cash patients similar to do not bill insurance in feesheet.
                    if ($_REQUEST['radio_type_of_payment'] == 'cash') {
                        sqlStatement(
                            "update form_encounter set last_level_closed=? where encounter=? and pid=? ",
                            array(4, $enc, $form_pid)
                        );
                        sqlStatement(
                            "update billing set billed=? where encounter=? and pid=?",
                            array(1, $enc, $form_pid)
                        );
                    }

                          $adjustment_code = 'patient_payment';
                          $payment_id = sqlInsert(
                              "insert into ar_session set " .
                              "payer_id = ?" .
                              ", patient_id = ?" .
                              ", user_id = ?" .
                              ", closed = ?" .
                              ", reference = ?"   .
                              ", check_date =  now() , deposit_date = now() " .
                              ",  pay_total = ?" .
                              ", payment_type = 'patient'" .
                              ", description = ?" .
                              ", adjustment_code = ?" .
                              ", post_to_date = now() " .
                              ", payment_method = ?",
                              array(0, $form_pid, $_SESSION['authUserID'], 0, $form_source, $amount, $NameNew, $adjustment_code, $form_method)
                          );

                    //--------------------------------------------------------------------------------------------------------------------

                            frontPayment($form_pid, $enc, $form_method, $form_source, 0, $amount, $timestamp);//insertion to 'payments' table.

                    //--------------------------------------------------------------------------------------------------------------------

                            $resMoneyGot = sqlStatement(
                                "SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid =? and " .
                                "encounter = ? and payer_type = 0 and account_code = 'PCP' AND deleted IS NULL",
                                array($form_pid, $enc)
                            );//new fees screen copay gives account_code='PCP'
                            $rowMoneyGot = sqlFetchArray($resMoneyGot);
                            $Copay = $rowMoneyGot['PatientPay'];

                    //--------------------------------------------------------------------------------------------------------------------

                            //Looping the existing code and modifier
                            $ResultSearchNew = sqlStatement(
                                "SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key WHERE code_types.ct_fee=1 " .
                                "AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
                                array($form_pid, $enc)
                            );
                    while ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                        $Codetype = $RowSearch['code_type'];
                        $Code = $RowSearch['code'];
                        $Modifier = $RowSearch['modifier'];
                        $Fee = $RowSearch['fee'];

                        $resMoneyGot = sqlStatement(
                            "SELECT sum(pay_amount) as MoneyGot FROM ar_activity where pid = ? AND deleted IS NULL " .
                            "and code_type=? and code=? and modifier=? and encounter =? and !(payer_type=0 and account_code='PCP')",
                            array($form_pid, $Codetype, $Code, $Modifier, $enc)
                        );
                        //new fees screen copay gives account_code='PCP'
                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                        $resMoneyAdjusted = sqlStatement(
                            "SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where " .
                            "pid = ? and code_type = ? and code = ? and modifier = ? and encounter = ? AND deleted IS NULL",
                            array($form_pid, $Codetype, $Code, $Modifier, $enc)
                        );
                        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                        $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];

                        $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;
                        $Copay = 0;
                        if (round($Remainder, 2) != 0 && $amount != 0) {
                            if ($amount - $Remainder >= 0) {
                                $insert_value = $Remainder;
                                $amount = $amount - $Remainder;
                            } else {
                                $insert_value = $amount;
                                $amount = 0;
                            }

                              sqlBeginTrans();
                              $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($form_pid, $enc));
                              sqlStatement(
                                  "insert into ar_activity set " .
                                  "pid = ?" .
                                  ", encounter = ?" .
                                  ", sequence_no = ?" .
                                  ", code_type = ?" .
                                  ", code = ?" .
                                  ", modifier = ?" .
                                  ", payer_type = ?" .
                                  ", post_time = now() " .
                                  ", post_user = ?" .
                                  ", session_id = ?" .
                                  ", pay_amount = ?" .
                                  ", adj_amount = ?" .
                                  ", account_code = 'PP'",
                                  array($form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, 0, $_SESSION['authUserID'], $payment_id, $insert_value, 0)
                              );
                              sqlCommitTrans();
                        }//if
                    }//while
                    if ($amount != 0) {//if any excess is there.
                              sqlBeginTrans();
                                $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($form_pid, $enc));
                                sqlStatement(
                                    "insert into ar_activity set " .
                                    "pid = ?" .
                                    ", encounter = ?" .
                                    ", sequence_no = ?" .
                                    ", code_type = ?" .
                                    ", code = ?" .
                                    ", modifier = ?" .
                                    ", payer_type = ?" .
                                    ", post_time = now() " .
                                    ", post_user = ?" .
                                    ", session_id = ?" .
                                    ", pay_amount = ?" .
                                    ", adj_amount = ?" .
                                    ", account_code = 'PP'",
                                    array($form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, 0, $_SESSION['authUserID'], $payment_id, $amount, 0)
                                );
                                sqlCommitTrans();
                    }

                    //--------------------------------------------------------------------------------------------------------------------
                }//invoice_balance
            }//if ($amount = 0 + $payment)
        }//foreach
    }//if ($_POST['form_upay'])
}//if ($_POST['form_save'])

if (!empty($_POST['form_save']) || !empty($_REQUEST['receipt'])) {
    if (!empty($_REQUEST['receipt'])) {
        $form_pid = $_GET['patient'];
        $timestamp = decorateString('....-..-.. ..:..:..', $_GET['time']);
    }

    // Get details for what we guess is the primary facility.
    $frow = $facilityService->getPrimaryBusinessEntity(array("useLegacyImplementation" => true));

    // Get the patient's name and chart number.
    $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');

    // Re-fetch payment info.
    $payrow = sqlQuery("SELECT " .
    "SUM(amount1) AS amount1, " .
    "SUM(amount2) AS amount2, " .
    "MAX(method) AS method, " .
    "MAX(source) AS source, " .
    "MAX(dtime) AS dtime, " .
    // "MAX(user) AS user " .
    "MAX(user) AS user, " .
    "MAX(encounter) as encounter " .
    "FROM payments WHERE " .
    "pid = ? AND dtime = ?", array($form_pid, $timestamp));

    // Create key for deleting, just in case.
    $ref_id = ($_REQUEST['radio_type_of_payment'] == 'copay') ? $session_id : $payment_id;
    $payment_key = $form_pid . '.' . preg_replace('/[^0-9]/', '', $timestamp) . '.' . $ref_id;

    if ($_REQUEST['radio_type_of_payment'] != 'pre_payment') {
        // get facility from encounter
        $tmprow = sqlQuery("SELECT `facility_id` FROM `form_encounter` WHERE `encounter` = ?", array($payrow['encounter']));
        $frow = $facilityService->getById($tmprow['facility_id']);
    } else {
        // if pre_payment, then no encounter yet, so get main office address
        $frow = $facilityService->getPrimaryBillingLocation();
    }

    // Now proceed with printing the receipt.
    ?>

<title><?php echo xlt('Receipt for Payment'); ?></title>
    <?php Header::setupHeader(); ?>
<script>

    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

$(function () {
    var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));
});

function closeHow(e) {
    if (opener) {
        dlgclose();
        return;
    }
    top.activateTabByName('pat', true);
    top.tabCloseByName(window.name);
}

// This is action to take before printing and is called from restoreSession.php.
function printlog_before_print() {
    let divstyle = document.getElementById('hideonprint').style;
    divstyle.display = 'none';
    // currently exit is not hidden by default in case receipt print is not needed
    // and left here for future option to force users to print via global etc..
    // can still print later via reports.
    divstyle = document.getElementById('showonprint').style;
    divstyle.display = '';
}

// Process click on Delete button.
function deleteme() {
    dlgopen('deleter.php?payment=' + <?php echo js_url($payment_key); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
    return false;
}

// Called by the deleteme.php window on a successful delete.
function imdeleted() {
    if (opener) {
        dlgclose(); // we're in reports/leftnav and callback reloads.
    } else {
        window.history.back(); // this is us full screen.
    }
}

// Called to switch to the specified encounter having the specified DOS.
// This also closes the popup window.
function toencounter(enc, datestr, topframe) {
    topframe.restoreSession();
    top.goToEncounter(enc);
    if (opener) dlgclose();
}
</script>
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12 text-center">
                <h2><?php echo xlt('Receipt for Payment'); ?></h2>
                <p>
                    <?php echo text($frow['name']) ?>
                    <br />
                    <?php echo text($frow['street']) ?>
                    <br />
                    <?php echo text($frow['city'] . ', ' . $frow['state']) . ' ' . text($frow['postal_code']) ?>
                    <br />
                    <?php echo text($frow['phone']) ?>
                </p>

                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr>
                            <td><?php echo xlt('Date'); ?>:</td>
                            <td><?php echo text(oeFormatSDFT(strtotime($payrow['dtime']))) ?></td>
                        </tr>
                        <tr>
                            <td><?php echo xlt('Patient'); ?>:</td>
                            <td><?php echo text($patdata['fname']) . " " . text($patdata['mname']) . " " .
                            text($patdata['lname']) . " (" . text($patdata['pubpid']) . ")" ?></td>
                        </tr>
                        <tr>
                            <td><?php echo xlt('Paid Via'); ?>:</td>
                            <td><?php echo generate_display_field(array('data_type' => '1', 'list_id' => 'payment_method'), $payrow['method']); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo xlt('Check/Ref Number'); ?>:</td>
                            <td><?php echo text($payrow['source']) ?></td>
                        </tr>
                        <tr>
                            <td><?php echo xlt('Amount for This Visit'); ?>:</td>
                            <td><?php echo text(oeFormatMoney($payrow['amount1'])) ?></td>
                        </tr>
                        <tr>
                            <td>
                            <?php
                            if ($_REQUEST['radio_type_of_payment'] == 'pre_payment') {
                                echo xlt('Pre-payment Amount');
                            } else {
                                echo xlt('Amount for Past Balance');
                            }
                            ?>
                            :</td>
                            <td><?php echo text(oeFormatMoney($payrow['amount2'])) ?></td>
                        </tr>
                        <tr>
                            <td><?php echo xlt('Received By'); ?>:</td>
                            <td><?php echo text($payrow['user']) ?></td>
                        </tr>
                    </table>
                </div>
                <div id='hideonprint'>
                    <button type="button" class="btn btn-primary btn-print" value='<?php echo xla('Print'); ?>' id='printbutton'>
                        <?php echo xlt('Print'); ?>
                    </button>

                    <?php
                    $todaysenc = todaysEncounterIf($pid);
                    if ($todaysenc && $todaysenc != $encounter) {
                        echo "&nbsp;<input type='button' class='btn btn-primary' " .
                        "value='" . xla('Open Today`s Visit') . "' " .
                        "onclick='toencounter(" . attr_js($todaysenc) . ", " . attr_js($today) . ", (opener ? opener.top : top))' />\n";
                    }
                    ?>

                    <?php if (AclMain::aclCheckCore('admin', 'super') || AclMain::aclCheckCore('acct', 'bill')) {
                        // allowing biller to delete payments ?>
                    <button type="button" class="btn btn-danger btn-delete" value='<?php echo xla('Delete'); ?>' onclick="deleteme()">
                        <?php echo xlt('Delete'); ?>
                    </button>
                    <?php } ?>
                </div>
                <div class='mt-3' id='showonprint'>
                    <button type="button" class="btn btn-secondary btn-cancel" value='<?php echo xla('Exit'); ?>' id='donebutton' onclick="closeHow(event)">
                        <?php echo xlt('Exit'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

    <?php
    //
    // End of receipt printing logic.
    //
} else {
    //
    // Here we display the form for data entry.
    //
    ?>
<title><?php echo xlt('Record Payment'); ?></title>

<style>
    #ajax_div_patient {
        position: absolute;
        z-index:10;
        background-color: #FBFDD0;
        border: 1px solid #ccc;
        padding: 10px;
    }
</style>
<!--Removed standard dependencies 12/29/17 as not needed any longer since moved to a tab/frame not popup.-->

<!-- supporting javascript code -->
<script>
    var mypcc = '1';
</script>
    <?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script>
    document.onclick=HideTheAjaxDivs;
</script>

    <?php Header::setupAssets('topdialog'); ?>

<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-creditcardvalidator/jquery.creditCardValidator.js"></script>

<script>
    var chargeMsg = <?php echo xlj('Payment was successfully authorized and charged. Thank You.'); ?>;
    var publicKey = <?php echo json_encode($cryptoGen->decryptStandard($GLOBALS['gateway_public_key'])); ?>;
    var apiKey = <?php echo json_encode($cryptoGen->decryptStandard($GLOBALS['gateway_api_key'])); ?>;
$(function() {
    $('#openPayModal').on('show.bs.modal', function () {
        let total = $("[name='form_paytotal']").val();
        let prepay = $("#form_prepayment").val();
        if (Number(total) < 1) {
            if (Number(prepay) < 1) {
                let error = <?php echo xlj("Please enter a payment amount"); ?>;
                alert(error);
                return false;
            }
            total = prepay;
        }
        $("#form_method").val('credit_card');
        $("#payTotal").text(total);
        $("#paymentAmount").val(total);
    });
});
    <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
function closeHow(e) {
    if (opener) {
        dlgclose();
        return;
    }
    top.activateTabByName('pat', true);
    top.tabCloseByName(window.name);
}
function calctotal() {
    var f = document.forms[0];
    var total = 0;
    for (var i = 0; i < f.elements.length; ++i) {
        var elem = f.elements[i];
        var ename = elem.name;
        if (ename.indexOf('form_upay[') == 0 || ename.indexOf('form_bpay[') == 0) {
            if (elem.value.length > 0) total += Number(elem.value);
        }
    }
    f.form_paytotal.value = Number(total).toFixed(2);
    return true;
}

function coloring() {
    for (var i = 1; ; ++i) {
        if (document.getElementById('paying_' + i)) {
            paying = document.getElementById('paying_' + i).value * 1;
            patient_balance = document.getElementById('duept_' + i).innerHTML * 1;

            //balance=document.getElementById('balance_'+i).innerHTML*1;
            if (patient_balance > 0 && paying > 0) {
                if (paying > patient_balance) {
                   document.getElementById('paying_' + i).style.background = '#FF0000';
                }
                else if (paying < patient_balance) {
                    document.getElementById('paying_' + i).style.background = '#99CC00';
                }
                else if (paying == patient_balance) {
                    document.getElementById('paying_' + i).style.background = 'var(--white)';
                }
            } else {
                document.getElementById('paying_' + i).style.background = 'var(--white)';
            }
        }
        else {
            break;
        }
    }
}

function CheckVisible(MakeBlank) { //Displays and hides the check number text box.
    if (document.getElementById('form_method').options[document.getElementById(
            'form_method').selectedIndex].value == 'check_payment' || document.getElementById(
            'form_method').options[document.getElementById('form_method').selectedIndex]
        .value == 'bank_draft') {
        document.getElementById('check_number').disabled = false;
    } else {
        document.getElementById('check_number').disabled = true;
    }
}

function validate() {
    var f = document.forms[0];
    ok = -1;
    top.restoreSession();
    issue = 'no';
    // prevent an empty form submission
    let flgempty = true;
    for (let i = 0; i < f.elements.length; ++i) {
        let ename = f.elements[i].name;
        if (f.elements[i].value == 'pre_payment' && f.elements[i].checked === true) {
            if (Number(f.elements.namedItem("form_prepayment").value) !== 0) {
                flgempty = false;
            }
            break;
        }
        if (ename.indexOf('form_upay[') === 0 || ename.indexOf('form_bpay[') === 0) {
            if (Number(f.elements[i].value) !== 0) flgempty = false;
        }
    }
    if (flgempty) {
        alert(<?php echo xlj('A Payment is Required!. Please input a payment line item entry.'); ?>);
        return false;
    }
    // continue validation.
    if (((document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'check_payment' ||
            document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'bank_draft') &&
            document.getElementById('check_number').value == '')) {
        alert(<?php echo xlj('Please Fill the Check/Ref Number'); ?>);
        document.getElementById('check_number').focus();
        return false;
    }
    if (document.getElementById('radio_type_of_payment_self1').checked == false &&
        document.getElementById('radio_type_of_payment1').checked == false &&
        document.getElementById('radio_type_of_payment2').checked == false &&
        document.getElementById('radio_type_of_payment4').checked == false) {
        alert(<?php echo xlj('Please Select Type Of Payment.'); ?>);
        return false;
    }
    if (document.getElementById('radio_type_of_payment_self1').checked == true ||
        document.getElementById('radio_type_of_payment1').checked == true) {
        for (var i = 0; i < f.elements.length; ++i) {
            var elem = f.elements[i];
            var ename = elem.name;
            if (ename.indexOf('form_upay[0') == 0) //Today is this text box.
            {
                if (elem.value * 1 > 0) {//A warning message, if the amount is posted with out encounter.
                    if (confirm(<?php echo xlj('If patient has appointment click OK to create encounter otherwise, cancel this and then create an encounter for today visit.'); ?>)) {
                        ok = 1;
                    } else {
                        elem.focus();
                        return false;
                    }
                }
                break;
            }
        }
    }

    if (document.getElementById('radio_type_of_payment1').checked == true){//CO-PAY
        var total = 0;
        for (var i = 0; i < f.elements.length; ++i) {
            var elem = f.elements[i];
            var ename = elem.name;
            if (ename.indexOf('form_upay[0]') == 0) {//Today is this text box.
                if (f.form_paytotal.value * 1 != elem.value * 1) {//Total CO-PAY is not posted against today
                //A warning message, if the amount is posted against an old encounter.
                    if (confirm(<?php echo xlj('You are posting against an old encounter?'); ?>)) {
                        ok = 1;
                    } else {
                        elem.focus();
                        return false;
                    }
                }
                break;
            }
        }
    }//Co Pay
    else if (document.getElementById('radio_type_of_payment2').checked == true) {//Invoice Balance
        for (var i = 0; i < f.elements.length; ++i) {
            var elem = f.elements[i];
            var ename = elem.name;
            if (ename.indexOf('form_upay[0') == 0) {
                if (elem.value * 1 > 0) {
                    alert(<?php echo xlj('Invoice Balance cannot be posted. No Encounter is created.'); ?>);
                    return false;
                }
                break;
            }
        }
    }
    if (ok == -1) {
        if (confirm(<?php echo xlj('Would you like to save?'); ?>)) {
            return true;
        }
        else {
            return false;
        }
    }
}

function cursor_pointer() { //Point the cursor to the latest encounter(Today)
    var f = document.forms[0];
    var total = 0;
    for (var i = 0; i < f.elements.length; ++i) {
        var elem = f.elements[i];
        var ename = elem.name;
        if (ename.indexOf('form_upay[') == 0) {
            elem.focus();
            break;
        }
    }
}
//=====================================================
function make_it_hide_enc_pay() {
    document.getElementById('td_head_insurance_payment').style.display = "none";
    document.getElementById('td_head_patient_co_pay').style.display = "none";
    document.getElementById('td_head_co_pay').style.display = "none";
    document.getElementById('td_head_insurance_balance').style.display = "none";
    for (var i = 1; ; ++i) {
        var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
        var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)
        var td_copay_elem = document.getElementById('td_copay_' + i)
        var balance_elem = document.getElementById('balance_' + i)
        if (td_inspaid_elem) {
            td_inspaid_elem.style.display = "none";
            td_patient_copay_elem.style.display = "none";
            td_copay_elem.style.display = "none";
            balance_elem.style.display = "none";
        } else {
            break;
        }
    }
    document.getElementById('td_total_4').style.display = "none";
    document.getElementById('td_total_7').style.display = "none";
    document.getElementById('td_total_8').style.display = "none";
    document.getElementById('td_total_6').style.display = "none";
    document.getElementById('table_display').width = "420px";
}
//=====================================================
function make_visible() {
    document.getElementById('td_head_rep_doc').style.display = "";
    document.getElementById('td_head_description').style.display = "";
    document.getElementById('td_head_total_charge').style.display = "none";
    document.getElementById('td_head_insurance_payment').style.display = "none";
    document.getElementById('td_head_patient_payment').style.display = "none";
    document.getElementById('td_head_patient_co_pay').style.display = "none";
    document.getElementById('td_head_co_pay').style.display = "none";
    document.getElementById('td_head_insurance_balance').style.display = "none";
    document.getElementById('td_head_patient_balance').style.display = "none";
    for (var i = 1; ; ++i) {
        var td_charges_elem = document.getElementById('td_charges_' + i)
        var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
        var td_ptpaid_elem = document.getElementById('td_ptpaid_' + i)
        var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)

        var td_copay_elem = document.getElementById('td_copay_' + i)
        var balance_elem = document.getElementById('balance_' + i)
        var duept_elem = document.getElementById('duept_' + i)
        if (td_charges_elem) {
            td_charges_elem.style.display = "none";
            td_inspaid_elem.style.display = "none";
            td_ptpaid_elem.style.display = "none";
            td_patient_copay_elem.style.display = "none";
            td_copay_elem.style.display = "none";
            balance_elem.style.display = "none";
            duept_elem.style.display = "none";
        } else {
            break;
        }
    }
    document.getElementById('td_total_7').style.display = "";
    document.getElementById('td_total_8').style.display = "";
    document.getElementById('td_total_1').style.display = "none";
    document.getElementById('td_total_2').style.display = "none";
    document.getElementById('td_total_3').style.display = "none";
    document.getElementById('td_total_4').style.display = "none";
    document.getElementById('td_total_5').style.display = "none";
    document.getElementById('td_total_6').style.display = "none";
    document.getElementById('table_display').width = "505px";
}

function make_it_hide() {
    document.getElementById('td_head_rep_doc').style.display = "none";
    document.getElementById('td_head_description').style.display = "none";
    document.getElementById('td_head_total_charge').style.display = "";
    document.getElementById('td_head_insurance_payment').style.display = "";
    document.getElementById('td_head_patient_payment').style.display = "";
    document.getElementById('td_head_patient_co_pay').style.display = "";
    document.getElementById('td_head_co_pay').style.display = "";
    document.getElementById('td_head_insurance_balance').style.display = "";
    document.getElementById('td_head_patient_balance').style.display = "";
    for (var i = 1; ; ++i) {
        var td_charges_elem = document.getElementById('td_charges_' + i)
        var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
        var td_ptpaid_elem = document.getElementById('td_ptpaid_' + i)
        var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)

        var td_copay_elem = document.getElementById('td_copay_' + i)
        var balance_elem = document.getElementById('balance_' + i)
        var duept_elem = document.getElementById('duept_' + i)
        if (td_charges_elem) {
            td_charges_elem.style.display = "";
            td_inspaid_elem.style.display = "";
            td_ptpaid_elem.style.display = "";
            td_patient_copay_elem.style.display = "";
            td_copay_elem.style.display = "";
            balance_elem.style.display = "";
            duept_elem.style.display = "";
        } else {
            break;
        }
    }
    document.getElementById('td_total_1').style.display = "";
    document.getElementById('td_total_2').style.display = "";
    document.getElementById('td_total_3').style.display = "";
    document.getElementById('td_total_4').style.display = "";
    document.getElementById('td_total_5').style.display = "";
    document.getElementById('td_total_6').style.display = "";
    document.getElementById('td_total_7').style.display = "";
    document.getElementById('td_total_8').style.display = "";
    document.getElementById('table_display').width = "635px";
}

function make_visible_radio() {
    document.getElementById('tr_radio1').style.display = "";
    document.getElementById('tr_radio2').style.display = "none";
}

function make_hide_radio() {
    document.getElementById('tr_radio1').style.display = "none";
    document.getElementById('tr_radio2').style.display = "";
}

function make_visible_row() {
    document.getElementById('table_display').style.display = "";
    document.getElementById('table_display_prepayment').style.display = "none";
}

function make_hide_row() {
    document.getElementById('table_display').style.display = "none";
    document.getElementById('table_display_prepayment').style.display = "";
}

function make_self() {
    make_visible_row();
    make_it_hide();
    make_it_hide_enc_pay();
    document.getElementById('radio_type_of_payment_self1').checked = true;
    cursor_pointer();
}

function make_insurance() {
    make_visible_row();
    make_it_hide();
    cursor_pointer();
    document.getElementById('radio_type_of_payment1').checked = true;
}
</script>

<style>
@media (min-width: 992px) {
    .modal-lg {
        width: 1000px !Important;
    }
}
</style>
<title><?php echo xlt('Record Payment'); ?></title>
    <?php $NameNew = $patdata['fname'] . " " . $patdata['lname'] . " " . $patdata['mname']; ?>
    <?php
    $arrOeUiSettings = array(
    'heading_title' => xl('Accept Payment'),
    'include_patient_name' => true,// use only in appropriate pages
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => ""
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body>
    <div class="container mt-3"><!--begin container div for form-->
        <div class="row">
            <div class="col-sm-12">
                <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form method='post' action='front_payment.php<?php echo (!empty($payid)) ? "?payid=" . attr_url($payid) : ""; ?>' onsubmit='return validate();'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <input name='form_pid' type='hidden' value='<?php echo attr($pid) ?>' />
                    <fieldset>
                        <legend><?php echo xlt('Payment'); ?></legend>
                        <div class="col-12 oe-custom-line">
                            <label class="control-label" for="form_method"><?php echo xlt('Payment Method'); ?>:</label>
                            <select class="form-control" id="form_method" name="form_method" onchange='CheckVisible("yes")'>
                                <?php
                                $query1112 = "SELECT * FROM list_options where list_id=?  ORDER BY seq, title ";
                                $bres1112 = sqlStatement($query1112, array('payment_method'));
                                while ($brow1112 = sqlFetchArray($bres1112)) {
                                    if ($brow1112['option_id'] == 'electronic' || $brow1112['option_id'] == 'bank_draft') {
                                        continue;
                                    }
                                    echo "<option value='" . attr($brow1112['option_id']) . "'>" . text(xl_list_label($brow1112['title'])) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 oe-custom-line">
                            <label class="control-label" for="check_number"><?php echo xlt('Check/Ref Number'); ?>:</label>
                            <div id="ajax_div_patient" style="display:none;"></div>
                            <input type='text' id="check_number" name='form_source' class='form-control' value='<?php echo attr($payrow['source'] ?? ''); ?>' />
                        </div>
                        <div class="col-12 oe-custom-line">
                            <label class="control-label" for="form_discount"><?php echo xla('Patient Coverage'); ?>:</label>
                            <div class="pl-3">
                                <label class="radio-inline">
                                    <input id="radio_type_of_coverage1" name="radio_type_of_coverage" onclick="make_visible_radio();make_self();" type="radio" value="self"><?php echo xlt('Self'); ?>
                                </label>
                                <label class="radio-inline">
                                    <input checked="checked" id="radio_type_of_coverag2" name="radio_type_of_coverage" onclick="make_hide_radio();make_insurance();" type="radio" value="insurance"><?php echo xlt('Insurance'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 oe-custom-line">
                            <label class="control-label" for=""><?php echo xlt('Payment against'); ?>:</label>
                            <div id="tr_radio1" style="padding-left:15px; display:none"><!-- For radio Insurance -->
                                <label class="radio-inline">
                                  <input id="radio_type_of_payment_self1" name="radio_type_of_payment" onclick="make_visible_row();make_it_hide_enc_pay();cursor_pointer();" type="radio" value="cash"><?php echo xlt('Encounter Payment'); ?>
                                </label>
                            </div>
                            <div id="tr_radio2" style="padding-left:15px"><!-- For radio self -->
                                <label class="radio-inline">
                                  <input checked="checked" id="radio_type_of_payment1" name="radio_type_of_payment" onclick="make_visible_row();cursor_pointer();" type="radio" value="copay"><?php echo xlt('Co Pay'); ?>
                                </label>
                                <label class="radio-inline">
                                  <input id="radio_type_of_payment2" name="radio_type_of_payment" onclick="make_visible_row();" type="radio" value="invoice_balance"><?php echo xlt('Invoice Balance'); ?><br />
                                </label>
                                <label class="radio-inline">
                                  <input id="radio_type_of_payment4" name="radio_type_of_payment" onclick="make_hide_row();" type="radio" value="pre_payment"><?php echo xlt('Pre Pay'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 oe-custom-line">
                            <div id="table_display_prepayment" style="display:none">
                                <label class="control-label" for="form_prepayment"><?php echo xlt('Pre Payment'); ?>:</label>
                                <input name='form_prepayment' id='form_prepayment'class='form-control' type='text' value ='' />
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Collect For'); ?></legend>
                        <div class="table-responsive">
                            <table class="table" id="table_display">
                                <thead>
                                    <tr class="table-active" id="tr_head">
                                        <td class="font-weight-bold" width="70"><?php echo xlt('DOS'); ?></td>
                                        <td class="font-weight-bold" width="65"><?php echo xlt('Encounter'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_total_charge" width="80"><?php echo xlt('Total Charge'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_rep_doc" style='display:none' width="70"><?php echo xlt('Report/ Form'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_description" style='display:none' width="200"><?php echo xlt('Description'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_insurance_payment" width="80"><?php echo xlt('Insurance Payment'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_patient_payment" width="80"><?php echo xlt('Patient Payment'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_patient_co_pay" width="55"><?php echo xlt('Co Pay Paid'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_co_pay" width="55"><?php echo xlt('Required Co Pay'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_insurance_balance" width="80"><?php echo xlt('Insurance Balance'); ?></td>
                                        <td class="font-weight-bold text-center" id="td_head_patient_balance" width="80"><?php echo xlt('Patient Balance'); ?></td>
                                        <td class="font-weight-bold text-center" width="50"><?php echo xlt('Paying'); ?></td>
                                    </tr>
                                </thead>
                                <?php
                                $encs = array();

                                // Get the unbilled service charges and payments by encounter for this patient.
                                //
                                $query = "SELECT fe.encounter, b.code_type, b.code, b.modifier, b.fee, " .
                                "LEFT(fe.date, 10) AS encdate ,fe.last_level_closed " .
                                "FROM  form_encounter AS fe left join billing AS b  on " .
                                "b.pid = ? AND b.activity = 1  AND " . //AND b.billed = 0
                                "b.code_type != 'TAX' AND b.fee != 0 " .
                                "AND fe.pid = b.pid AND fe.encounter = b.encounter " .
                                "where fe.pid = ? " .
                                "ORDER BY b.encounter";
                                $bres = sqlStatement($query, array($pid, $pid));
                                //
                                while ($brow = sqlFetchArray($bres)) {
                                    $key = 0 - $brow['encounter'];
                                    if (empty($encs[$key])) {
                                        $encs[$key] = array(
                                        'encounter' => $brow['encounter'],
                                        'date' => $brow['encdate'],
                                        'last_level_closed' => $brow['last_level_closed'],
                                        'charges' => 0,
                                        'payments' => 0);
                                    }

                                    if ($brow['code_type'] === 'COPAY') {
                                        //$encs[$key]['payments'] -= $brow['fee'];
                                    } else {
                                        $encs[$key]['charges'] += $brow['fee'];
                                        // Add taxes.
                                        $sql_array = array();
                                        $query = "SELECT taxrates FROM codes WHERE " .
                                        "code_type = ? AND " .
                                        "code = ? AND ";
                                        array_push($sql_array, ($code_types[$brow['code_type']]['id'] ?? null), $brow['code']);
                                        if ($brow['modifier']) {
                                            $query .= "modifier = ?";
                                            array_push($sql_array, $brow['modifier']);
                                        } else {
                                            $query .= "(modifier IS NULL OR modifier = '')";
                                        }

                                        $query .= " LIMIT 1";
                                        $trow = sqlQuery($query, $sql_array);
                                        $encs[$key]['charges'] += calcTaxes($trow, $brow['fee']);
                                    }
                                }

                                // Do the same for unbilled product sales.
                                //
                                $query = "SELECT fe.encounter, s.drug_id, s.fee, " .
                                "LEFT(fe.date, 10) AS encdate,fe.last_level_closed " .
                                "FROM form_encounter AS fe left join drug_sales AS s " .
                                "on s.pid = ? AND s.fee != 0 " . //AND s.billed = 0
                                "AND fe.pid = s.pid AND fe.encounter = s.encounter " .
                                "where fe.pid = ? " .
                                "ORDER BY s.encounter";

                                $dres = sqlStatement($query, array($pid, $pid));
                                //
                                while ($drow = sqlFetchArray($dres)) {
                                    $key = 0 - $drow['encounter'];
                                    if (empty($encs[$key])) {
                                        $encs[$key] = array(
                                        'encounter' => $drow['encounter'],
                                        'date' => $drow['encdate'],
                                        'last_level_closed' => $drow['last_level_closed'],
                                        'charges' => 0,
                                        'payments' => 0);
                                    }

                                    $encs[$key]['charges'] += $drow['fee'];
                                    // Add taxes.
                                    $trow = sqlQuery("SELECT taxrates FROM drug_templates WHERE drug_id = ? " .
                                    "ORDER BY selector LIMIT 1", array($drow['drug_id']));
                                    $encs[$key]['charges'] += calcTaxes($trow, $drow['fee']);
                                }

                                ksort($encs, SORT_NUMERIC);
                                $gottoday = false;
                                //Bringing on top the Today always
                                foreach ($encs as $key => $value) {
                                    $dispdate = $value['date'];
                                    if (strcmp($dispdate, $today) == 0 && !$gottoday) {
                                        $gottoday = true;
                                        break;
                                    }
                                }

                                // If no billing was entered yet for today, then generate a line for
                                // entering today's co-pay.
                                //
                                if (!$gottoday) {
                                    echoLine("form_upay[0]", date("Y-m-d"), 0, 0, 0, 0 /*$duept*/);//No encounter yet defined.
                                }

                                $gottoday = false;
                                foreach ($encs as $key => $value) {
                                    $enc = $value['encounter'];
                                    $dispdate = $value['date'];
                                    if (strcmp($dispdate, $today) == 0 && !$gottoday) {
                                        $dispdate = date("Y-m-d");
                                        $gottoday = true;
                                    }
                                    //------------------------------------------------------------------------------------
                                    $inscopay = BillingUtilities::getCopay($pid, $dispdate);
                                    $patcopay = BillingUtilities::getPatientCopay($pid, $enc);
                                    //Insurance Payment
                                    //-----------------
                                    $drow = sqlQuery(
                                        "SELECT  SUM(pay_amount) AS payments, " .
                                        "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " .
                                        "deleted IS NULL AND pid = ? and encounter = ? and " .
                                        "payer_type != 0 and account_code!='PCP' ",
                                        array($pid, $enc)
                                    );
                                    $dpayment = $drow['payments'];
                                    $dadjustment = $drow['adjustments'];
                                    //Patient Payment
                                    //---------------
                                    $drow = sqlQuery(
                                        "SELECT  SUM(pay_amount) AS payments, " .
                                        "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " .
                                        "deleted IS NULL AND pid = ? and encounter = ? and " .
                                        "payer_type = 0 and account_code!='PCP' ",
                                        array($pid, $enc)
                                    );
                                    $dpayment_pat = $drow['payments'];

                                    //------------------------------------------------------------------------------------
                                    //NumberOfInsurance
                                    $ResultNumberOfInsurance = sqlStatement("SELECT COUNT( DISTINCT TYPE ) NumberOfInsurance FROM insurance_data
                                    where pid = ? and provider>0 ", array($pid));
                                    $RowNumberOfInsurance = sqlFetchArray($ResultNumberOfInsurance);
                                    $NumberOfInsurance = $RowNumberOfInsurance['NumberOfInsurance'] * 1;
                                    //------------------------------------------------------------------------------------
                                    $duept = 0;
                                    if ((($NumberOfInsurance == 0 || $value['last_level_closed'] == 4 || $NumberOfInsurance == $value['last_level_closed']))) {//Patient balance
                                        $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
                                            "pid = ? and encounter = ? AND activity = 1", array($pid, $enc));
                                        $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
                                            "pid = ? and encounter = ? ", array($pid, $enc));
                                        $drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " .
                                            "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and encounter = ? ", array($pid, $enc));
                                        $duept = $brow['amount'] + $srow['amount'] - $drow['payments'] - $drow['adjustments'];
                                    }

                                    echoLine(
                                        "form_upay[$enc]",
                                        $dispdate,
                                        $value['charges'],
                                        $dpayment_pat,
                                        ($dpayment + $dadjustment),
                                        $duept,
                                        $enc,
                                        $inscopay,
                                        $patcopay
                                    );
                                }


                                // Continue with display of the data entry form.
                                ?>

                                <tr class="table-active">
                                    <td class="font-weight-bold" id='td_total_1'></td>
                                    <td class="font-weight-bold" id='td_total_2'></td>
                                    <td class="font-weight-bold" id='td_total_3'></td>
                                    <td class="font-weight-bold" id='td_total_4'></td>
                                    <td class="font-weight-bold" id='td_total_5'></td>
                                    <td class="font-weight-bold" id='td_total_6'></td>
                                    <td class="font-weight-bold" id='td_total_7'></td>
                                    <td class="font-weight-bold" id='td_total_8'></td>
                                    <td class="font-weight-bold text-right"><?php echo xlt('Total');?></td>
                                    <td class="font-weight-bold text-right">
                                        <input type='text' class='form-control text-success' name='form_paytotal' value='' readonly />
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group" role="group">
                                <button type='submit' class="btn btn-primary btn-save" name='form_save' value='<?php echo xla('Generate Invoice');?>'><?php echo xlt('Generate Invoice');?></button>
                                <?php if ($GLOBALS['cc_front_payments'] && $GLOBALS['payment_gateway'] != 'InHouse') {
                                    echo '<button type="button" class="btn btn-success btn-transmit" data-toggle="modal" data-target="#openPayModal">' . xlt("Credit Card Pay") . '</button>';
                                }  ?>
                                <button type='button' class="btn btn-secondary btn-cancel" value='<?php echo xla('Cancel'); ?>' onclick='closeHow(event)'><?php echo xlt('Cancel'); ?></button>
                                <input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo attr($pid);?>"/>
                                <input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
                                <input type='hidden' name='mode' id='mode' value='' />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
        calctotal();
        </script>
        <!-- credit payment modal -->
        <div id="openPayModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4><?php echo xlt('Submit Payment for Authorization'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <?php if ($GLOBALS['payment_gateway'] == 'AuthorizeNet') { ?>
                            <form id='paymentForm' method='post' action='./front_payment_cc.php'>
                                <fieldset>
                                    <div class="form-group">
                                        <label
                                            class="control-label"><?php echo xlt('Name on Card'); ?></label>
                                        <div class="controls">
                                            <input name="cardHolderName" id="cardHolderName" type="text" class="form-control"
                                                pattern="\w+ \w+.*"
                                                title="<?php echo xla('Fill your first and last name'); ?>"
                                                value="<?php echo attr($patdata['fname']) . ' ' . attr($patdata['lname']) ?>" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <label class="control-label"><?php echo xlt('Card Number'); ?></label>
                                                <input name="cardNumber" id="cardNumber" type="text"
                                                    class="form-control"
                                                    autocomplete="off" maxlength="19" pattern="\d"
                                                    onchange="validateCC()"
                                                    title="<?php echo xla('Card Number'); ?>" value="" />&nbsp;&nbsp;
                                            </div>
                                            <span class="col-sm-6">
                                                    <label class="control-label"><?php echo xlt('Entry Status'); ?></label>
                                                    <h5 name="cardtype" id="cardtype" style="color:#cc0000;"><?php echo xlt('Validating') ?></h5>
                                                </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            class="control-label"><?php echo xlt('Card Expiry Date and Card Holders Zip'); ?></label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <select name="month" id="expMonth" class="form-control">
                                                    <option value=""><?php echo xlt('Select Month'); ?></option>
                                                    <option value="01"><?php echo xlt('January'); ?></option>
                                                    <option value="02"><?php echo xlt('February'); ?></option>
                                                    <option value="03"><?php echo xlt('March'); ?></option>
                                                    <option value="04"><?php echo xlt('April'); ?></option>
                                                    <option value="05"><?php echo xlt('May'); ?></option>
                                                    <option value="06"><?php echo xlt('June'); ?></option>
                                                    <option value="07"><?php echo xlt('July'); ?></option>
                                                    <option value="08"><?php echo xlt('August'); ?></option>
                                                    <option value="09"><?php echo xlt('September'); ?></option>
                                                    <option value="10"><?php echo xlt('October'); ?></option>
                                                    <option value="11"><?php echo xlt('November'); ?></option>
                                                    <option value="12"><?php echo xlt('December'); ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="year" id="expYear" class="form-control">
                                                    <option value=""><?php echo xlt('Select Year'); ?></option>
                                                    <option value="2019">2019</option>
                                                    <option value="2020">2020</option>
                                                    <option value="2021">2021</option>
                                                    <option value="2022">2022</option>
                                                    <option value="2023">2023</option>
                                                    <option value="2024">2024</option>
                                                    <option value="2025">2025</option>
                                                    <option value="2026">2026</option>
                                                    <option value="2027">2027</option>
                                                    <option value="2028">2028</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <input name="zip" id="cczip" type="text" class="form-control"
                                                    pattern="\d"
                                                    title="<?php echo xla('Enter Your Zip'); ?>"
                                                    placeholder="<?php echo xla('Card Holder Zip'); ?>"
                                                    value="<?php echo attr($patdata['postal_code']) ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?php echo xlt('Card CVV'); ?></label>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input name="cardCode" id="cardCode" type="text" class="form-control"
                                                    autocomplete="off" maxlength="4" onfocus="validateCC()"
                                                    title="<?php echo xla('Three or four digits at back of your card'); ?>"
                                                    value="" />
                                            </div>
                                            <div class="col-md-3">
                                                <img src='./../../portal/images/img_cvc.png' style='height: 40px; width: auto'>
                                            </div>
                                            <div class="col-md-6">
                                                <h4 style="display: inline-block;"><?php echo xlt('Payment Amount'); ?>:&nbsp;
                                                    <strong><span id="payTotal"></span></strong></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <input type='hidden' name='pid' id='pid' value='<?php echo attr($pid) ?>' />
                                    <input type='hidden' name='mode' id='mode' value='' />
                                    <input type='hidden' name='cc_type' id='cc_type' value='' />
                                    <input type='hidden' name='payment' id='paymentAmount' value='' />
                                    <input type='hidden' name='invValues' id='invValues' value='' />
                                    <input type="hidden" name="dataValue" id="dataValue" />
                                    <input type="hidden" name="dataDescriptor" id="dataDescriptor" />
                                </fieldset>
                            </form>
                        <?php }
                        if ($GLOBALS['payment_gateway'] == 'Stripe') { ?>
                            <form class="form" method="post" name="payment-form" id="payment-form">
                                <fieldset>
                                    <div class="form-group">
                                        <label for="cardHolderName" class="control-label"><?php echo xlt('Name on Card'); ?></label>
                                        <input name="cardHolderName" id="cardHolderName" type="text"
                                            class="form-control"
                                            pattern="\w+ \w+.*"
                                            title="<?php echo xla('Fill your first and last name'); ?>"
                                            value="<?php echo attr($patdata['fname']) . ' ' . attr($patdata['lname']) ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label for="card-element"><?php echo xlt('Credit or Debit Card') ?></label>
                                        <div class="form-group" id="card-element"></div>
                                        <div class="text-danger" id="card-errors" role="alert"></div>
                                    </div>
                                    <div class="form-row">
                                        <?php echo xlt('Payment Amount'); ?>:&nbsp;<span id="payTotal"></span>
                                    </div>
                                    <input type='hidden' name='mode' id='mode' value='' />
                                    <input type='hidden' name='cc_type' id='cc_type' value='' />
                                    <input type='hidden' name='payment' id='paymentAmount' value='' />
                                    <input type='hidden' name='invValues' id='invValues' value='' />
                                </fieldset>
                            </form>
                        <?php } ?>
                    </div>
                    <!-- Body  -->
                    <div class="modal-footer">
                        <div class="button-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                            <?php
                            if ($GLOBALS['payment_gateway'] == 'AuthorizeNet') { ?>
                                <button id="payAurhorizeNet" class="btn btn-primary"
                                    onclick="sendPaymentDataToAnet(event)"><?php echo xlt('Pay Now'); ?></button>
                            <?php }
                            if ($GLOBALS['payment_gateway'] == 'Stripe') { ?>
                                <button id="stripeSubmit" class="btn btn-primary"><?php echo xlt('Pay Now'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($GLOBALS['payment_gateway'] == 'AuthorizeNet') {
            // Include Authorize.Net dependency to tokenize card.
            // Will return a token to use for payment request keeping
            // credit info off the server.
            ?>
            <script>
                var ccerr = <?php echo xlj('Invalid Credit Card Number'); ?>

                    // In House CC number Validation
                    $('#cardNumber').validateCreditCard(function (result) {
                        var r = (result.card_type === null ? '' : result.card_type.name.toUpperCase())
                        var v = (result.valid === true ? ' Valid Number' : ' Validating')
                        if (result.valid === true) {
                            document.getElementById("cardtype").style.color = "#00aa00";
                        } else {
                            document.getElementById("cardtype").style.color = "#aa0000";
                        }
                        $('#cardtype').text(r + v);
                    });

                // Authorize.net
                function validateCC() {
                    var result = $('#cardNumber').validateCreditCard();
                    var r = (result.card_type == null ? '' : result.card_type.name.toUpperCase())
                    var v = (result.valid === true ? ' Valid Card Number' : ' Invalid Card Number')
                    if (result.valid === true) {
                        document.getElementById("cardtype").style.color = "#00aa00";
                    } else {
                        document.getElementById("cardtype").style.color = "#aa0000";
                    }
                    $('#cardtype').text(r + v);
                    $('#cc_type').val(r);
                    if (!result.valid) {
                        alert(ccerr);
                        return false;
                    }
                    else {
                        return true;
                    }
                }

                function sendPaymentDataToAnet(e) {
                    e.preventDefault();
                    const authData = {};
                    authData.clientKey = publicKey;
                    authData.apiLoginID = apiKey;

                    const cardData = {};
                    cardData.cardNumber = document.getElementById("cardNumber").value;
                    cardData.month = document.getElementById("expMonth").value;
                    cardData.year = document.getElementById("expYear").value;
                    cardData.cardCode = document.getElementById("cardCode").value;
                    cardData.fullName = document.getElementById("cardHolderName").value;
                    cardData.zip = document.getElementById("cczip").value;

                    const secureData = {};
                    secureData.authData = authData;
                    secureData.cardData = cardData;

                    Accept.dispatchData(secureData, acceptResponseHandler);

                    function acceptResponseHandler(response) {
                        if (response.messages.resultCode === "Error") {
                            let i = 0;
                            let errorMsg = '';
                            while (i < response.messages.message.length) {
                                errorMsg = errorMsg + response.messages.message[i].code + ": " +response.messages.message[i].text;
                                console.log(errorMsg);
                                i = i + 1;
                            }
                            alert(errorMsg);
                        } else {
                            paymentFormUpdate(response.opaqueData);
                        }
                    }
                }

                function paymentFormUpdate(opaqueData) {
                    // this is card tokenized
                    document.getElementById("dataDescriptor").value = opaqueData.dataDescriptor;
                    document.getElementById("dataValue").value = opaqueData.dataValue;
                    let oForm = document.forms['paymentForm'];
                    oForm.elements['mode'].value = "AuthorizeNet";
                    // empty out the fields before submitting to server.
                    document.getElementById("cardNumber").value = "";
                    document.getElementById("expMonth").value = "";
                    document.getElementById("expYear").value = "";
                    document.getElementById("cardCode").value = "";

                    // Submit payment to server
                    fetch('./front_payment_cc.php', {
                        method: 'POST',
                        body: new FormData(oForm)
                    }).then((response) => {
                        if (!response.ok) {
                            throw Error(response.statusText);
                        }
                        return response.json();
                    }).then(function(data) {
                        if(data.status !== 'ok') {
                            alert(data);
                            return;
                        }
                        document.getElementById("check_number").value = data.authCode;
                        alert(chargeMsg + "\n" + 'Auth: ' + data.authCode + ' TransId: ' + data.transId);
                        $("[name='form_save']").click();
                    }).catch(function(error) {
                        alert(error)
                    });
                }
            </script>
        <?php }  // end authorize.net ?>

        <?php if ($GLOBALS['payment_gateway'] == 'Stripe') { // Begin Include Stripe ?>
            <script>
                    const stripe = Stripe(publicKey);
                    const elements = stripe.elements();
                    const style = {
                        base: {
                            color: '#32325d',
                            lineHeight: '1.2rem',
                            fontSmoothing: 'antialiased',
                            '::placeholder': {
                                color: '#ccc'
                            }
                        },
                        invalid: {
                            color: '#f42c03',
                            iconColor: '#ff0000'
                        }

                    };
                    // Create an instance of the card Element.
                const card = elements.create('card', {style: style});
                    // Add an instance of the card Element into the `card-element` <div>.
                    card.mount('#card-element');
                    // Handle real-time validation errors from the card Element.
                    card.addEventListener('change', function (event) {
                        let displayError = document.getElementById('card-errors');
                        if (event.error) {
                            displayError.textContent = event.error.message;
                        } else {
                            displayError.textContent = '';
                        }
                    });
                    // Handle form submission.
                    let form = document.getElementById('stripeSubmit');
                    form.addEventListener('click', function (event) {
                        event.preventDefault();
                        stripe.createToken(card).then(function (result) {
                            if (result.error) {
                                // Inform the user if there was an error.
                                let errorElement = document.getElementById('card-errors');
                                errorElement.textContent = result.error.message;
                            } else {
                                // Send the token to server.
                                stripeTokenHandler(result.token);
                            }
                        });
                    });

                    // Submit the form with the token ID.
                    function stripeTokenHandler(token) {
                        // Insert the token ID into the form so it gets submitted to the server
                        let oForm = document.forms['payment-form'];
                        oForm.elements['mode'].value = "Stripe";

                        let hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'stripeToken');
                        hiddenInput.setAttribute('value', token.id);
                        oForm.appendChild(hiddenInput);

                        // Submit payment to server
                        fetch('./front_payment_cc.php', {
                            method: 'POST',
                            body: new FormData(oForm)
                        }).then((response) => {
                            if (!response.ok) {
                                throw Error(response.statusText);
                            }
                            return response.json();
                        }).then(function (data) {
                            if (data.status !== 'ok') {
                                alert(data);
                                return;
                            }
                            document.getElementById("check_number").value = data.authCode;
                            alert(chargeMsg + "\n" + 'Auth: ' + data.authCode + ' TransId: ' + data.transId);
                            $("[name='form_save']").click();
                        }).catch(function (error) {
                            alert(error)
                        });
                    }
            </script>
        <?php } ?>

    </div><!--end of container div of accept payment i.e the form-->
    <?php
        $oemr_ui->oeBelowContainerDiv();
} // forms else close
?>
</body>
</html>
