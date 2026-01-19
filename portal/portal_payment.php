<?php

/**
 *
 * namespace OnsitePortal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
$v_js_includes = $globalsBag->get('v_js_includes');
$session = SessionWrapperFactory::getInstance()->getWrapper();

$isPortal = false;
if ($session->isSymfonySession() && !empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    $isPortal = true;
    require_once(__DIR__ . "/../interface/globals.php");
} else {
    SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../interface/globals.php");
    if (!$session->has('authUserID')) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
}
$srcdir = $globalsBag->getString('srcdir');
require_once(__DIR__ . "/lib/appsql.class.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/payment.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/encounter_events.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\FormatMoney;
use OpenEMR\PaymentProcessing\Sphere\SpherePayment;

$twig = (new TwigContainer(null, $globalsBag->get('kernel')))->getTwig();

$cryptoGen = new CryptoGen();

$appsql = new ApplicationTable();
$pid = $_REQUEST['pid'] ?? $pid;
$pid = ($_REQUEST['hidden_patient_code'] ?? null) > 0 ? $_REQUEST['hidden_patient_code'] : $pid;
$recid = isset($_REQUEST['recid']) ? (int) $_REQUEST['recid'] : 0;
$adminUser = '';
$portalPatient = '';

$query = "SELECT pao.portal_username as recip_id, Concat_Ws(' ', patient_data.fname, patient_data.lname) as username FROM patient_data " .
    "LEFT JOIN patient_access_onsite pao ON pao.pid = patient_data.pid " .
    "WHERE patient_data.pid = ? AND pao.portal_pwd_status = 1";
$portalPatient = sqlQueryNoLog($query, [$pid]);
if ($session->get('authUserID', '')) {
    $query = "SELECT users.username as recip_id, users.authorized as dash, CONCAT(users.fname,' ',users.lname) as username  " .
        "FROM users WHERE id = ?";
    $adminUser = sqlQueryNoLog($query, [$session->get('authUserID')]);
}

$edata = $recid ? $appsql->getPortalAuditRec($recid) : $appsql->getPortalAudit($pid, 'review', 'payment');
$ccdata = [];
$invdata = [];
if ($edata) {
    $ccdata = json_decode($cryptoGen->decryptStandard($edata['checksum']), true);
    $invdata = json_decode((string) $edata['table_args'], true);
    echo "<script>var jsondata='" . $edata['table_args'] . "';var ccdata='" . $edata['checksum'] . "'</script>";
}

// Display a row of data for an encounter.
//
$var_index = 0;
$sum_charges = $sum_ptpaid = $sum_inspaid = $sum_duept = $sum_copay = $sum_patcopay = $sum_balance = 0;
function echoLine($iname, $date, $charges, $ptpaid, $inspaid, $duept, $encounter = 0, $copay = 0, $patcopay = 0): void
{
    global $sum_charges, $sum_ptpaid, $sum_inspaid, $sum_duept, $sum_copay, $sum_patcopay, $sum_balance;
    global $var_index;
    $var_index++;
    $balance = FormatMoney::getBucks($charges - $ptpaid - $inspaid);
    $balance = (round($duept, 2) != 0) ? 0 : $balance; // if balance is due from patient, then insurance balance is displayed as zero
    $encounter = $encounter ?: '';
    echo " <tr id='tr_" . attr($var_index) . "' >\n";
    echo "  <td class='detail'>" . text(oeFormatShortDate($date)) . "</td>\n";
    echo "  <td class='detail' id='" . attr($date) . "' align='left'>" . text($encounter) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_charges_$var_index' >" . text(FormatMoney::getBucks($charges)) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_inspaid_$var_index' >" . text(FormatMoney::getBucks($inspaid * -1)) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_ptpaid_$var_index' >" . text(FormatMoney::getBucks($ptpaid * -1)) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_patient_copay_$var_index' >" . text(FormatMoney::getBucks($patcopay)) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_copay_$var_index' >" . text(FormatMoney::getBucks($copay)) . "</td>\n";
    echo "  <td class='detail' align='center' id='balance_$var_index'>" . text(FormatMoney::getBucks($balance)) . "</td>\n";
    echo "  <td class='detail' align='center' id='duept_$var_index'>" . text(FormatMoney::getBucks(round($duept, 2) * 1)) . "</td>\n";
    echo "  <td class='detail' align='center'><input class='form-control' name='" . attr($iname) . "'  id='paying_" . attr($var_index) .
        "' " . " value='" . '' . "' onchange='coloring();calctotal()'  autocomplete='off' " . "onkeyup='calctotal()'/></td>\n";
    echo " </tr>\n";

    $sum_charges += (float)$charges * 1;
    $sum_ptpaid += (float)$ptpaid * -1;
    $sum_inspaid += (float)$inspaid * -1;
    $sum_duept += (float)$duept * 1;
    $sum_patcopay += (float)$patcopay * 1;
    $sum_copay += (float)$copay * 1;
    $sum_balance += (float)$balance * 1;
}

// We use this to put dashes, colons, etc. back into a timestamp.
//
function decorateString($fmt, $str)
{
    $res = '';
    while ($fmt) {
        $fc = substr((string) $fmt, 0, 1);
        $fmt = substr((string) $fmt, 1);
        if ($fc == '.') {
            $res .= substr((string) $str, 0, 1);
            $str = substr((string) $str, 1);
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

    $arates = explode(':', (string) $row['taxrates']);
    if (empty($arates)) {
        return $total;
    }

    foreach ($arates as $value) {
        if (empty($value)) {
            continue;
        }

        $trow = sqlQuery("SELECT option_value FROM list_options WHERE " . "list_id = 'taxrate' AND option_id = ? LIMIT 1", [$value
        ]);
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

$patdata = sqlQuery("SELECT " . "p.fname, p.mname, p.lname, p.postal_code, p.pubpid,p.pid, i.copay " . "FROM patient_data AS p " . "LEFT OUTER JOIN insurance_data AS i ON " . "i.pid = p.pid AND i.type = 'primary' " . "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", [$pid
]);

$alertmsg = ''; // anything here pops up in an alert box

// If the Save button was clicked...
if ($_POST['form_save'] ?? '') {
    $form_pid = $_POST['form_pid'];
    $form_method = trim((string) $_POST['form_method']);
    $form_source = trim((string) $_POST['form_source']);
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
            [0, $form_pid, $session->get('authUserID'), 0, $form_source, $_REQUEST['form_prepayment'], $NameNew, $form_method]
        );

        frontPayment($form_pid, 0, $form_method, $form_source, $_REQUEST['form_prepayment'], 0, $timestamp);//insertion to 'payments' table.
    }

    if ($_POST['form_upay'] && $_REQUEST['radio_type_of_payment'] != 'pre_payment') {
        foreach ($_POST['form_upay'] as $enc => $payment) {
            if ($amount = (float)$payment) {
                $zero_enc = $enc;

                //----------------------------------------------------------------------------------------------------
                //Fetching the existing code and modifier
                $ResultSearchNew = sqlStatement(
                    "SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key " .
                    "WHERE code_types.ct_fee=1 AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
                    [$form_pid, $enc]
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
                        [$session->get('authUserID'), $form_source, $amount, $form_pid, $form_method]
                    );

                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM       ar_activity WHERE pid = ? AND encounter = ?", [$form_pid, $enc]);
                    $insrt_id = sqlInsert(
                        "INSERT INTO ar_activity (pid,encounter,sequence_no,code_type,code,modifier,payer_type,post_time,post_user,session_id,pay_amount,account_code)" .
                        " VALUES (?,?,?,?,?,?,0,now(),?,?,?,'PCP')",
                        [$form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, $session->get('authUserID'), $session_id, $amount]
                    );
                    sqlCommitTrans();

                    frontPayment($form_pid, $enc, $form_method, $form_source, $amount, 0, $timestamp);//insertion to 'payments' table.
                }

                if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance' || $_REQUEST['radio_type_of_payment'] == 'cash') {                //Payment by patient after insurance paid, cash patients similar to do not bill insurance in feesheet.
                    if ($_REQUEST['radio_type_of_payment'] == 'cash') {
                        sqlStatement(
                            "update form_encounter set last_level_closed=? where encounter=? and pid=? ",
                            [4, $enc, $form_pid]
                        );
                        sqlStatement(
                            "update billing set billed=? where encounter=? and pid=?",
                            [1, $enc, $form_pid]
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
                        [0, $form_pid, $session->get('authUserID'), 0, $form_source, $amount, $NameNew, $adjustment_code, $form_method]
                    );

                    //--------------------------------------------------------------------------------------------------------------------

                    frontPayment($form_pid, $enc, $form_method, $form_source, 0, $amount, $timestamp);//insertion to 'payments' table.

                    //--------------------------------------------------------------------------------------------------------------------

                    $resMoneyGot = sqlStatement(
                        "SELECT sum(pay_amount) as PatientPay FROM ar_activity where deleted IS NULL AND pid =? and " .
                        "encounter =? and payer_type=0 and account_code='PCP'",
                        [$form_pid, $enc]
                    );//new fees screen copay gives account_code='PCP'
                    $rowMoneyGot = sqlFetchArray($resMoneyGot);
                    $Copay = $rowMoneyGot['PatientPay'];

                    //--------------------------------------------------------------------------------------------------------------------

                    //Looping the existing code and modifier
                    $ResultSearchNew = sqlStatement(
                        "SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key WHERE code_types.ct_fee=1 " .
                        "AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
                        [$form_pid, $enc]
                    );
                    while ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                        $Codetype = $RowSearch['code_type'];
                        $Code = $RowSearch['code'];
                        $Modifier = $RowSearch['modifier'];
                        $Fee = $RowSearch['fee'];

                        $resMoneyGot = sqlStatement(
                            "SELECT sum(pay_amount) as MoneyGot FROM ar_activity where deleted IS NULL AND pid = ? " .
                            "and code_type=? and code=? and modifier=? and encounter =? and !(payer_type=0 and account_code='PCP')",
                            [$form_pid, $Codetype, $Code, $Modifier, $enc]
                        );
                        //new fees screen copay gives account_code='PCP'
                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                        $resMoneyAdjusted = sqlStatement(
                            "SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where deleted IS NULL AND " .
                            "pid =? and code_type=? and code=? and modifier=? and encounter =?",
                            [$form_pid, $Codetype, $Code, $Modifier, $enc]
                        );
                        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                        $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];

                        $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;
                        $Copay = 0;
                        if (round($Remainder, 2) != 0 && $amount != 0) {
                            if ($amount - $Remainder >= 0) {
                                $insert_value = $Remainder;
                                $amount -= $Remainder;
                            } else {
                                $insert_value = $amount;
                                $amount = 0;
                            }

                            sqlBeginTrans();
                            $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", [$form_pid, $enc]);
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
                                [$form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, 0, $session->get('authUserID'), $payment_id, $insert_value, 0]
                            );
                            sqlCommitTrans();
                        }//if
                    }//while
                    if ($amount != 0) {//if any excess is there.
                        sqlBeginTrans();
                        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", [$form_pid, $enc]);
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
                            [$form_pid, $enc, $sequence_no['increment'], $Codetype, $Code, $Modifier, 0, $session->get('authUserID'), $payment_id, $amount, 0]
                        );
                        sqlCommitTrans();
                    }

                    //--------------------------------------------------------------------------------------------------------------------
                }//invoice_balance
            }//if ($amount = 0 + $payment)
        }//foreach
    }//if ($_POST['form_upay'])
}//if ($_POST['form_save'])

if (($_POST['form_save'] ?? null) || ($_REQUEST['receipt'] ?? null)) {
    if (($_REQUEST['receipt'] ?? null)) {
        $form_pid = $_GET['patient'];
        $timestamp = decorateString('....-..-.. ..:..:..', $_GET['time']);
    }

// Get details for what we guess is the primary facility.
    $frow = sqlQuery("SELECT * FROM facility " . "ORDER BY billing_location DESC, accepts_assignment DESC, id LIMIT 1");

// Get the patient's name and chart number.
    $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');

// Re-fetch payment info.
    $payrow = sqlQuery("SELECT " . "SUM(amount1) AS amount1, " . "SUM(amount2) AS amount2, " . "MAX(method) AS method, " . "MAX(source) AS source, " . "MAX(dtime) AS dtime, " .
// "MAX(user) AS user " .
        "MAX(user) AS user, " . "MAX(encounter) as encounter " . "FROM payments WHERE " . "pid = ? AND dtime = ?", [$form_pid, $timestamp
    ]);

// Create key for deleting, just in case.
    $ref_id = ($_REQUEST['radio_type_of_payment'] == 'copay') ? $session_id : $payment_id;
    $payment_key = $form_pid . '.' . preg_replace('/[^0-9]/', '', (string) $timestamp) . '.' . $ref_id;

// get facility from encounter
    $tmprow = sqlQuery("SELECT facility_id FROM form_encounter WHERE encounter = ?", [$payrow['encounter']]);
    $frow = sqlQuery("SELECT * FROM facility " . " WHERE id = ?", [$tmprow['facility_id']
    ]);

// Now proceed with printing the receipt.
    ?>

    <title><?php echo xlt('Receipt for Payment'); ?></title>
    <script src="<?php echo $globalsBag->getString('assets_static_relative'); ?>/jquery/dist/jquery.min.js"></script>
    <script>

        function goHome() {
            window.location.replace("./patient/onsiteactivityviews");
        }

        function notifyPatient() {
            let pid = <?php echo js_escape($pid); ?>;
            let note = $('#pop_receipt').html();
            let formURL = './messaging/handle_note.php';
            let owner = <?php echo js_escape($adminUser['recip_id']); ?>;
            let sn = <?php echo js_escape($adminUser['username']); ?>;
            let rid = <?php echo js_escape($portalPatient['recip_id']); ?>;
            let rn = <?php echo js_escape($portalPatient['username']); ?>;
            $.ajax({
                url: formURL,
                type: "POST",
                data: {
                    'csrf_token_form': <?php echo js_escape(CsrfUtils::collectCsrfToken('messages-portal', $session->getSymfonySession())); ?>,
                    'task': 'add',
                    'pid': pid,
                    'inputBody': note,
                    'title': 'Bill/Collect',
                    'recipient_name': rn,
                    'recipient_id': rid,
                    'sender_id': owner,
                    'sender_name': sn
                },
                success: function (data, textStatus, jqXHR) {
                    alert('Receipt sent to patient via Messages.')
                },
                error: function (jqXHR, status, error) {
                    console.log(status + ": " + error);
                }
            });
        }
    </script>
    <?php
    ob_start();
    echo '<htlm><head></head><body style="text-align: center; margin: auto;">';
    ?>
    <div id='pop_receipt' style='display: block'>
        <p>
        <h2><?php echo xlt('Receipt for Payment'); ?></h2>
        <p><?php echo text($frow['name']) ?>
            <br /><?php echo text($frow['street']) ?>
            <br /><?php echo text($frow['city'] . ', ' . $frow['state']) . ' ' . text($frow['postal_code']) ?>
            <br /><?php echo text($frow['phone']) ?>
        <p>
        <div class="text-center" style="margin: auto;">
            <table border='0' cellspacing='8' class="text-center" style="margin: auto;">
                <tr>
                    <td><?php echo xlt('Date'); ?>:</td>
                    <td><?php echo text(oeFormatSDFT(strtotime((string) $payrow['dtime']))) ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Patient'); ?>:</td>
                    <td><?php echo text($patdata['fname']) . " " . text($patdata['mname']) . " " . text($patdata['lname']) . " (" . text($patdata['pubpid']) . ")" ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Paid Via'); ?>:</td>
                    <td><?php echo generate_display_field(['data_type' => '1', 'list_id' => 'payment_method'], $payrow['method']); ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Authorized Id'); ?>:</td>
                    <td><?php echo text($payrow['source']) ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Amount for This Visit'); ?>:</td>
                    <td><?php echo text(oeFormatMoney($payrow['amount1'])) ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Amount for Past Balance'); ?>:</td>
                    <td><?php echo text(oeFormatMoney($payrow['amount2'])) ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Received By'); ?>:</td>
                    <td><?php echo text($payrow['user']) ?></td>
                </tr>
            </table>
        </div>
    </div>
    <button class='btn btn-sm' type='button' onclick='goHome()' id='returnhome'><?php echo xla('Return Home'); ?></button>
    <button class='btn btn-sm' type='button' onclick="notifyPatient()"><?php echo xla('Notify Patient'); ?></button>
    </body></html>
    <?php
    ob_end_flush();
} else {
//
// Here we display the form for data entry.
//
    ?>
    <title><?php echo xlt('Record Payment'); ?></title>
    <style>
        .dehead {
            color: #000000;
            font-weight: bold
        }
        .detail {
            padding: 1px 1px;
            color: #000000;
            font-weight: normal
        }
    </style>
    <script src="<?php echo $globalsBag->getString('assets_static_relative'); ?>/jquery-creditcardvalidator/jquery.creditCardValidator.js"></script>
    <script src="<?php echo $globalsBag->getString('webroot') ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="portal_payment.js?v=<?=$v_js_includes?>"></script>
    <script>
        var chargeMsg = <?php $amsg = xl('Payment was successfully authorized and your card is charged.') . "\n" .
                xl("You will be notified when your payment is applied for this invoice.") . "\n" .
                xl('Until then you will continue to see payment details here.') . "\n" . xl('Thank You.');
            echo json_encode($amsg);
        ?>;
        var publicKey = <?php echo json_encode($cryptoGen->decryptStandard($globalsBag->get('gateway_public_key'))); ?>;
        var apiKey = <?php echo json_encode($cryptoGen->decryptStandard($globalsBag->get('gateway_api_key'))); ?>;

        function calctotal() {
            var flag = 0;
            var f = document.forms["invoiceForm"];
            var total = 0;
            for (var i = 0; i < f.elements.length; ++i) {
                var elem = f.elements[i];
                var ename = elem.name;
                if (ename.indexOf('form_upay[') == 0 || ename.indexOf('form_bpay[') == 0) {
                    if (elem.value.length > 0) {
                        total += Number(elem.value);
                        if (total < 0) flag = 1;
                    }
                }
            }
            f.form_paytotal.value = Number(total).toFixed(2);
            if (flag) {
                $('#invoiceForm')[0].reset();
                alert(<?php echo xlj('Negative payments not accepted'); ?>)
            }
            return true;
        }

        function validate() {
            var f = document.forms["invoiceForm"];
            ok = -1;
//no checks taken here....
            issue = 'no';
            if (document.getElementById('radio_type_of_payment_self1').checked == false &&
                document.getElementById('radio_type_of_payment1').checked == false
                && document.getElementById('radio_type_of_payment2').checked == false
                && document.getElementById('radio_type_of_payment4').checked == false) {
                alert("<?php //echo addslashes( xl('Please Select Type Of Payment.')) ?>");
                return false;
            }
            if (document.getElementById('radio_type_of_payment_self1').checked == true || document.getElementById('radio_type_of_payment1').checked == true) {
                for (var i = 0; i < f.elements.length; ++i) {
                    var elem = f.elements[i];
                    var ename = elem.name;
                    if (ename.indexOf('form_upay[0') == 0) //Today is this text box.
                    {
                        if (elem.value * 1 > 0) {//A warning message, if the amount is posted with out encounter.
                            if (confirm(<?php echo xlj('Are you sure to post for today?'); ?>)) {
                                ok = 1;
                            }
                            else {
                                elem.focus();
                                return false;
                            }
                        }
                        break;
                    }
                }
            }
            else if (document.getElementsByName('form_paytotal')[0].value <= 0)//total 0
            {
                alert(<?php echo xlj('Invalid Total!'); ?>)
                return false;
            }
            if (ok == -1) {
                if (confirm(<?php echo xlj('Payment Validated: Save?'); ?>)) {
                    return true;
                }
                else {
                    return false;
                }
            }
        }

        $('#paySubmit').click(function (e) {
            e.preventDefault();e.stopPropagation();
            $("#mode").val("portal-save");
            let inv_values = JSON.stringify(getFormObj('invoiceForm'));
            let extra_values = JSON.stringify(getFormObj('paymentForm'));
            const params = new URLSearchParams({
                extra_values: extra_values,
                inv_values: inv_values
            });
            let extra = "&" + params;
            let flag = 0
            let liburl = './lib/paylib.php';
            $.ajax({
                type: "POST",
                url: liburl,
                data: $("#invoiceForm").serialize() + extra,
                beforeSend: function (xhr) {
                    if (validateCC() !== true) return false;
                    if ($('#cardCode').val() == "" || $('#cardHolderName').val() == "" || $('#expYear').val() == "" || $('#expMonth').val() == "") {
                        alert(<?php echo xlj('Invalid Credit Card Values: Please correct'); ?>)
                        return false;
                    }
                    if (validate() != true) {
                        flag = 1;
                        alert(<?php echo xlj('Validation error: Fix and resubmit. This popup info is preserved!'); ?>)
                        return false;
                    }
                    $("#openPayModal .close").click()
                },
                error: function (qXHR, textStatus, errorThrow) {
                    console.log("There was an error:" + errorThrow);
                },
                success: function (templateHtml, textStatus, jqXHR) {
                    let msg = <?php $amsg = xl('Payment successfully sent for review and posting to your account.') . "\n" .
                        xl("You will be notified when the payment transaction is confirmed.") . "\n" .
                        xl('Until then you will continue to see payment details here.') . "\n" . xl('Thank You.');
                        echo json_encode($amsg); // backward compatable 5.0.1
                    ?>;
                    alert(msg);
                    window.location.reload(false);
                }
            });
            if (flag) {
                $("#openPayModal .close").click();
            }
        });

        $('#openPayModal').on('show.bs.modal', function () {
            let total = $("#form_paytotal").val();
            if(Number(total) < 1) {
                let error = <?php echo json_encode("Please enter a payment amount"); ?>;
                alert(error);
                return false;
            }
            $("#payTotal").text(total);
            $("#paymentAmount").val(total);
        });

        $("#invoiceForm").on('submit', function (e) {
            e.preventDefault();
            let thisform = this;
            $("#mode").val("review-save");
            let inv_values = JSON.stringify(getFormObj('invoiceForm'));
            let extra_values = JSON.stringify(getFormObj('paymentForm'));
            let extra = "&inv_values=" + inv_values + "&extra_values=" + extra_values;

            let flag = 0
            let liburl = '<?php echo $globalsBag->getString("webroot") ?>/portal/lib/paylib.php';
            $.ajax({
                type: "POST",
                url: liburl,
                data: $("#invoiceForm").serialize() + extra,
                beforeSend: function (xhr) {
                    if (validate() != true) {
                        flag = 1;
                        alert(<?php echo xlj('Validation error: Fix and resubmit.'); ?>)
                        return false;
                    }
                },
                error: function (xhr, textStatus, error) {
                    alert(<?php echo xlj('There is a Post error'); ?>)
                    console.log("There was an error:" + textStatus);
                    return false;
                },
                success: function (templateHtml, textStatus, jqXHR) {
                    thisform.submit();
                }
            });
        });

        function getAuth() {
            let authnum = document.getElementById("check_number").value;
            authnum = prompt(<?php echo xlj('Please enter card comfirmation authorization'); ?>, authnum);
            if (authnum != null) {
                document.getElementById("check_number").value = authnum;
            }
        }
    </script>

    <body class="skin-blue" onunload='imclosing()' onLoad="cursor_pointer();"
          style="text-align: center; margin: auto;">

    <form id="invoiceForm" method='post' action='<?php echo $globalsBag->getString("webroot") ?>/portal/portal_payment.php'>
        <input type='hidden' name='form_pid' value='<?php echo attr($pid) ?>'/>
        <input type='hidden' name='form_save' value='<?php echo xla('Invoice'); ?>'/>
        <table>
            <tr height="10">
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td colspan='3' align='center' class='text'>
                    <b><?php echo xlt('Accept Payment for'); ?>&nbsp;:&nbsp;&nbsp;<?php
                    echo text($patdata['fname']) . " " .
                        text($patdata['lname']) . " " .
                        text($patdata['mname']) . " (" .
                        text($patdata['pid']) . ")" ?></b>
                    <?php $NameNew = $patdata['fname'] . " " . $patdata['lname'] . " " . $patdata['mname']; ?>
                </td>
            </tr>
            <tr height="15">
                <td colspan='3'></td>
            </tr>
            <tr>
                <td class='text'>
                    <?php echo xlt('Payment Method'); ?>:
                </td>
                <td colspan='2'><select name="form_method" id="form_method" class="form-control" onChange='CheckVisible("yes")'>
                        <?php
                        $query1112 = "SELECT * FROM list_options where list_id=?  ORDER BY seq, title ";
                        $bres1112 = sqlStatement($query1112, ['payment_method']);
                        while ($brow1112 = sqlFetchArray($bres1112)) {
                            if ($brow1112['option_id'] != 'credit_card' || $brow1112['option_id'] == 'debit' || $brow1112['option_id'] == 'bank_draft') {
                                continue;
                            }
                            echo "<option value='" . attr($brow1112['option_id']) . "'>" .
                                text(xl_list_label($brow1112['title'])) . "</option>";
                        }
                        ?>
                    </select></td>
            </tr>
            <?php if ($session->has('authUserID')) { ?>
                <tr height="5">
                    <td colspan='3'></td>
                </tr>
                <tr>
                    <td class='text'>
                        <?php echo xlt('Authorized'); ?>:
                    </td>
                    <td colspan='2'>
                        <?php if ($ccdata['authCode'] && empty($payrow['source'])) {
                            $payrow['source'] = $ccdata['authCode'] . " : " . $ccdata['transId'];
                        }
                        ?>
                        <input class="form-control form-control-sm" id='check_number' name='form_source' style='' value='<?php echo attr($payrow['source']) ?>' />
                    </td>
                </tr>
            <?php } ?>
                <?php if ($session->has('authUserID')) {
                        $hide = '';
                        echo '<tr height="5"><td colspan="3"></td></tr><tr">';
                } else {
                    $hide = 'hidden';
                    echo '<tr class="hidden">';
                }
                ?>
                <td class='text' valign="middle">
                    <?php echo xlt('Patient Coverage'); ?>:
                </td>
                <td class='text' colspan="2">
                    <input type="radio" name="radio_type_of_coverage" id="radio_type_of_coverage1"
                           value="self" onClick="make_visible_radio();make_self();"/>
                    <?php echo xlt('Self'); ?>
                    <input type="radio" name="radio_type_of_coverage" id="radio_type_of_coverag2" value="insurance"
                           checked="checked"
                           onClick="make_hide_radio();make_insurance();"/>
                    <?php echo xlt('Insurance'); ?>
                </td>
            </tr>
            <tr height="5">
                <td colspan='3'></td>
            </tr>
            <tr id="tr_radio1" style="display: none">
                <!-- For radio Insurance -->
                <td class='text' valign="top">
                    <?php echo xlt('Payment against'); ?>:
                </td>
                <td class='text' colspan="2">
                    <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment_self1"
                           value="cash" onClick="make_visible_row();make_it_hide_enc_pay();cursor_pointer();"/>
                    <?php echo xlt('Encounter Payment'); ?>
                </td>
            </tr>
            <tr id="tr_radio2">
                <!-- For radio self -->
                <td class='text' valign="top"><?php echo xlt('Payment against'); ?>:</td>
                <td class='text' colspan="2">
                    <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment1" class="<?php echo $hide ?: ''; ?>"
                           value="copay" onClick="make_visible_row();cursor_pointer();"/><?php echo !$hide ? xlt('Co Pay') : ''; ?>
                    <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment2" checked="checked"
                           value="invoice_balance" onClick="make_visible_row();"/><?php echo xlt('Invoice Balance'); ?>
                    <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment4" value="pre_payment"
                           onClick="make_hide_row();"/><?php echo xlt('Pre Pay'); ?>
                </td>
            </tr>
            <tr height="15">
                <td colspan='3'></td>
            </tr>
        </table>
        <table width="20%" border="0" cellspacing="0" cellpadding="0" id="table_display_prepayment" style="margin-bottom: 10px; display: none">
            <tr>
                <td class='detail'><?php echo xlt('Pre Payment'); ?></td>
                <td><input class="form-control" type='text' id= 'form_prepayment' name='form_prepayment' style=''/></td>
            </tr>
        </table>
        <table id="table_display" style="background: #eee;" class="table table-sm table-striped table-bordered w-100">
            <thead>
            </thead>
            <tbody>
            <tr bgcolor="#cccccc" id="tr_head">
                <td class="dehead" width="60">
                    <?php echo xlt('DOS') ?>
                </td>
                <td class="dehead" width="120">
                    <?php echo xlt('Visit Reason') ?>
                </td>
                <td class="dehead" align="center" width="70" id="td_head_total_charge">
                    <?php echo xlt('Total Charge') ?>
                </td>
                <td class="dehead" align="center" width="70" id="td_head_rep_doc" style='display: none'>
                    <?php echo xlt('Report/ Form') ?>
                </td>
                <td class="dehead" align="center" width="200" id="td_head_description" style='display: none'>
                    <?php echo xlt('Description') ?>
                </td>
                <td class="dehead" align="center" width="70" id="td_head_insurance_payment">
                    <?php echo xlt('Insurance Payment') ?>
                </td>
                <td class="dehead" align="center" width="70" id="td_head_patient_payment">
                    <?php echo xlt('Patient Payment') ?>
                </td>
                <td class="dehead" align="center" width="55" id="td_head_patient_co_pay">
                    <?php echo xlt('Co Pay Paid') ?>
                </td>
                <td class="dehead" align="center" width="55" id="td_head_co_pay">
                    <?php echo xlt('Required Co Pay') ?>
                </td>
                <td class="dehead" align="center" width="70" id="td_head_insurance_balance">
                    <?php echo xlt('Insurance Balance') ?>
                </td>
                <td class="dehead" align="center" width="70" id="td_head_patient_balance">
                    <?php echo xlt('Patient Balance') ?>
                </td>
                <td class="dehead" align="center" width="50">
                    <?php echo xlt('Paying') ?>
                </td>
            </tr>
            <?php
            $encs = [];
            // Get the unbilled service charges and payments by encounter for this patient.
            //
            $query = "SELECT fe.encounter, fe.reason, b.code_type, b.code, b.modifier, b.fee, " .
                "LEFT(fe.date, 10) AS encdate ,fe.last_level_closed " . "FROM  form_encounter AS fe left join billing AS b  on " .
                "b.pid = ? AND b.activity = 1  AND " . "b.code_type != 'TAX' AND b.fee != 0 " . "AND fe.pid = b.pid AND fe.encounter = b.encounter " .
                "where fe.pid = ? " . "ORDER BY b.encounter";
            $bres = sqlStatement($query, [$pid, $pid]);
            //
            while ($brow = sqlFetchArray($bres)) {
                $key = (int)$brow['encounter'];
                if (empty($encs[$key])) {
                    $encs[$key] = ['encounter' => $brow['encounter'], 'date' => $brow['encdate'], 'last_level_closed' => $brow['last_level_closed'], 'charges' => 0, 'payments' => 0, 'reason' => $brow['reason']
                    ];
                }

                if ($brow['code_type'] !== 'COPAY') {
                    $encs[$key]['charges'] += $brow['fee'];
                    // Add taxes.
                    $sql_array = [];
                    $query = "SELECT taxrates FROM codes WHERE " . "code_type = ? AND " . "code = ? AND ";
                    array_push($sql_array, $code_types[$brow['code_type']]['id'] ?? '', $brow['code'] ?? '');
                    if ($brow['modifier'] ?? '') {
                        $query .= "modifier = ?";
                        $sql_array[] = $brow['modifier'] ?? '';
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
            $query = "SELECT fe.encounter, fe.reason, s.drug_id, s.fee, " .
                "LEFT(fe.date, 10) AS encdate,fe.last_level_closed " .
                "FROM form_encounter AS fe left join drug_sales AS s " .
                "on s.pid = ? AND s.fee != 0 " .
                "AND fe.pid = s.pid AND fe.encounter = s.encounter " .
                "where fe.pid = ? " . "ORDER BY s.encounter";

            $dres = sqlStatement($query, [$pid, $pid]);
            //
            while ($drow = sqlFetchArray($dres)) {
                $key = (int)$drow['encounter'];
                if (empty($encs[$key])) {
                    $encs[$key] = [
                        'encounter' => $drow['encounter'], 'date' => $drow['encdate'],
                        'last_level_closed' => $drow['last_level_closed'],
                        'charges' => 0, 'payments' => 0
                    ];
                }

                $encs[$key]['charges'] += $drow['fee'];
                // Add taxes.
                $trow = sqlQuery(
                    "SELECT taxrates FROM drug_templates WHERE drug_id = ? " .
                    "ORDER BY selector LIMIT 1",
                    [$drow['drug_id']]
                );
                $encs[$key]['charges'] += calcTaxes($trow, $drow['fee']);
            }

            ksort($encs, SORT_NUMERIC);

            foreach ($encs as $value) {
                $enc = $value['encounter'];
                $reason = $value['reason'];
                $dispdate = $value['date'];

                $inscopay = BillingUtilities::getCopay($pid, $dispdate);
                $patcopay = BillingUtilities::getPatientCopay($pid, $enc);
                // Insurance Payment
                //
                $drow = sqlQuery(
                    "SELECT  SUM(pay_amount) AS payments, " .
                    "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
                    "deleted IS NULL AND pid = ? and encounter = ? AND " .
                    "payer_type != 0 AND account_code != 'PCP'",
                    [$pid, $enc]
                );
                $dpayment = $drow['payments'];
                $dadjustment = $drow['adjustments'];
                // Patient Payment
                //
                $drow = sqlQuery(
                    "SELECT  SUM(pay_amount) AS payments, SUM(adj_amount) AS adjustments " .
                    "FROM ar_activity WHERE deleted IS NULL AND pid = ? and encounter = ? and " .
                    "payer_type = 0 and account_code != 'PCP'",
                    [$pid, $enc]
                );
                $dpayment_pat = $drow['payments'];

                // NumberOfInsurance
                //
                $ResultNumberOfInsurance = sqlStatement(
                    "SELECT COUNT( DISTINCT TYPE ) NumberOfInsurance FROM insurance_data where pid = ? and provider>0 ",
                    [$pid]
                );
                $RowNumberOfInsurance = sqlFetchArray($ResultNumberOfInsurance);
                $NumberOfInsurance = $RowNumberOfInsurance['NumberOfInsurance'] * 1;
                $duept = 0;
                if ((($NumberOfInsurance == 0 || $value['last_level_closed'] == 4 || $NumberOfInsurance == $value['last_level_closed']))) { // Patient balance
                    $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " . "pid = ? and encounter = ? AND activity = 1", [$pid, $enc
                    ]);
                    $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " . "pid = ? and encounter = ? ", [$pid, $enc
                    ]);
                    $drow = sqlQuery(
                        "SELECT SUM(pay_amount) AS payments, SUM(adj_amount) AS adjustments " .
                        "FROM ar_activity WHERE deleted IS NULL AND pid = ? and encounter = ? ",
                        [$pid, $enc]
                    );
                    $duept = $brow['amount'] + $srow['amount'] - $drow['payments'] - $drow['adjustments'];
                }

                echoLine("form_upay[$enc]", $dispdate, $value['charges'], $dpayment_pat, ($dpayment + $dadjustment), $duept, ($enc . ': ' . $reason), $inscopay, $patcopay);
            }

            // Continue with display of the data entry form.
            ?>
            <tr>
                <td class="dehead" align="center"><?php echo xlt('Total'); ?></td>
                <td class="dehead" id='td_total_1' align="center"></td>
                <td class="dehead" id='td_total_2' align="center"><?php echo text(FormatMoney::getBucks($sum_charges)) ?></td>
                <td class="dehead" id='td_total_3' align="center"><?php echo text(FormatMoney::getBucks($sum_inspaid)) ?></td>
                <td class="dehead" id='td_total_4' align="center"><?php echo text(FormatMoney::getBucks($sum_ptpaid)) ?></td>
                <td class="dehead" id='td_total_5' align="center"><?php echo text(FormatMoney::getBucks($sum_patcopay)) ?></td>
                <td class="dehead" id='td_total_6' align="center"><?php echo text(FormatMoney::getBucks($sum_copay)) ?></td>
                <td class="dehead" id='td_total_7' align="center"><?php echo text(FormatMoney::getBucks($sum_balance)) ?></td>
                <td class="dehead" id='td_total_8' align="center"><?php echo text(FormatMoney::getBucks($sum_duept)) ?></td>
                <td class="dehead" align="center">
                    <input class="form-control" name='form_paytotal' id='form_paytotal' value='' style='color: #3b9204;' readonly />
                </td>
            </tr>
        </table>
        <?php
        if (isset($ccdata["cardHolderName"])) {
            echo '<div class="col-5"><div class="card panel-default height">';
            if (!$session->has('authUserID')) {
                echo '<div class="card-heading">' . xlt("Payment Information") .
                    '<span style="color: #cc0000"><em> ' . xlt("Pending Auth since") . ': </em>' . text($edata["date"]) . '</span></div>';
            } else {
                echo '<div class="card-heading">' . xlt("Audit Payment") .
                    '<span style="color: #cc0000"><em> ' . xlt("Pending since") . ': </em>' . text($edata["date"]) . '</span>' .
                    ' <button type="button" class="btn btn-warning btn-sm" onclick="getAuth()">' . xlt("Authorize") . '</button></div>';
            }
        } else {
            echo '<div style="display:none" class="col-6"><div class="card panel-default height">' .
                '<div class="card-heading">' . xlt("Payment Information") . ' </div>';
        }
        ?>
        <div class="card-body">
            <span class="font-weight-bold"><?php echo xlt('Card Name'); ?>: </span><span id="cn"><?php echo text($ccdata["cc_type"] ?? '') ?></span><br />
            <span class="font-weight-bold"><?php echo xlt('Name on Card'); ?>: </span><span id="nc"><?php echo text($ccdata["cardHolderName"] ?? '') ?></span>
            <span class="font-weight-bold"><?php echo xlt('Card Holder Zip'); ?>: </span><span id="czip"><?php echo text($ccdata["zip"] ?? '') ?></span><br />
            <span class="font-weight-bold"><?php echo xlt('Card Number'); ?>: </span><span id="ccn">
        <?php
        if ($session->has('authUserID') || isset($ccdata["transId"])) {
            echo text($ccdata["cardNumber"]) . "</span><br />";
        } elseif (strlen($ccdata["cardNumber"] ?? '') > 4) {
            echo "**********  " . text(substr((string) $ccdata["cardNumber"], -4)) . "</span><br />";
        }
        ?>
        <?php
        if (!isset($ccdata["transId"])) { ?>
                <span class="font-weight-bold"><?php echo xlt('Exp Date'); ?>:  </span><span id="ed"><?php echo text($ccdata["month"] ?? '') . "/" . text($ccdata["year"] ?? '') ?></span>
                <span class="font-weight-bold"><?php echo xlt('CVV'); ?>:  </span><span id="cvvpin"><?php echo text($ccdata["cardCode"] ?? '') ?></span><br />
        <?php } else { ?>
                <span class="font-weight-bold"><?php echo xlt('Transaction Id'); ?>:  </span><span id="ed"><?php echo text($ccdata["transId"] ?? '') . "/" . text($ccdata["year"]) ?></span>
                <span class="font-weight-bold"><?php echo xlt('Authorization'); ?>:  </span><span id="cvvpin"><?php echo text($ccdata["authCode"] ?? '') ?></span><br />
        <?php } ?>
        <span class="font-weight-bold"><?php echo xlt('Charge Total'); ?>:  </span><span id="ct"><?php echo text($invdata["form_paytotal"] ?? '') ?></span><br />
        </div>
        </div>
        </div>
        <div>
        <?php
        if (!$session->has('authUserID')) {
            if (!isset($ccdata["cardHolderName"])) {
                if ($globalsBag->get('payment_gateway') === 'Sphere') {
                    echo SpherePayment::renderSphereHtml('patient');
                } else {
                    echo '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#openPayModal">' . xlt("Pay Invoice") . '</button>';
                }
            } else {
                echo '<h4><span class="bg-danger">' . xlt("Locked Payment Pending") . '</span></h4>';
            }
        } else {
            echo "<button type='submit' class='btn btn-success' form='invoiceForm'>" . xlt('Post Payment') . "</button>";
        }
        ?>
        </div>
        <input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo attr($pid); ?>"/>
        <input type='hidden' name='mode' id='mode' value=''/>
    </form>

    <script>
        if (typeof jsondata !== 'undefined') {
            formRepopulate(jsondata);
        }
        calctotal();
    </script>
    <!-- credit payment modal -->
    <div id="openPayModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4><?php echo xlt('Submit Payment for Authorization'); ?></h4>
                    <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                </div>
                <div class="modal-body">
                    <?php if ($globalsBag->get('payment_gateway') !== 'Stripe' && $globalsBag->get('payment_gateway') !== 'Sphere') { ?>
                    <form id='paymentForm' method='post' action='<?php echo $globalsBag->getString("webroot") ?>/portal/lib/paylib.php'>
                        <fieldset>
                            <div class="form-group">
                                <label label-default="label-default"
                                       class="control-label"><?php echo xlt('Name on Card'); ?></label>
                                <div class="controls">
                                    <input name="cardHolderName" id="cardHolderName" type="text" class="form-control" pattern="\w+ \w+.*" title="<?php echo xla('Fill your first and last name'); ?>" value="<?php echo attr($patdata['fname']) . ' ' . attr($patdata['lname']) ?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?php echo xlt('Card Number'); ?></label>
                                <div class="controls">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input name="cardNumber" id="cardNumber" type="text" class="form-control inline col-sm-4" autocomplete="off" maxlength="19" pattern="\d" onchange="validateCC()" title="<?php echo xla('Card Number'); ?>" value="" />&nbsp;&nbsp;
                                            <h4 name="cardtype" id="cardtype" style="display: inline-block; color:#cc0000;"><?php echo xlt('Validating') ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label label-default="label-default"><?php echo xlt('Card Expiry Date and Card Holders Zip'); ?></label>
                                <div class="controls">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select name="month" id="expMonth" class="form-control">
                                                <option value=""><?php echo xlt('Select Month'); ?></option>
                                                <?=$twig->render('forms/month_dropdown.html.twig')?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select name="year" id="expYear" class="form-control">
                                                <option value=""><?php echo xlt('Select Year'); ?></option>
                                                <?=$twig->render('forms/exp_year_dropdown.html.twig')?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input name="zip" id="cczip" type="text" class="form-control" pattern="\d" title="<?php echo xla('Enter Your Zip'); ?>" placeholder="<?php echo xla('Card Holder Zip'); ?>" value="<?php echo attr($patdata['postal_code']) ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label label-default="label-default" class="control-label"><?php echo xlt('Card CVV'); ?></label>
                                <div class="controls">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input name="cardCode" id="cardCode" type="text" class="form-control" autocomplete="off" maxlength="4" onfocus="validateCC()" title="<?php echo xla('Three or four digits at back of your card'); ?>" value="" />
                                        </div>
                                        <div class="col-md-3">
                                            <img src='./images/img_cvc.png' style='height: 40px; width: auto' />
                                        </div>
                                        <div class="col-md-6">
                                            <h4 style="display: inline-block;"><?php echo xlt('Payment Amount'); ?>:&nbsp;
                                                <span class="font-weight-bold"><span id="payTotal"></span></span></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type='hidden' name='pid' id='pid' value='<?php echo attr($pid) ?>'/>
                            <input type='hidden' name='mode' id='mode' value=''/>
                            <input type='hidden' name='cc_type' id='cc_type' value=''/>
                            <input type='hidden' name='payment' id='paymentAmount' value=''/>
                            <input type='hidden' name='invValues' id='invValues' value=''/>
                            <input type="hidden" name="dataValue" id="dataValue" />
                            <input type="hidden" name="dataDescriptor" id="dataDescriptor" />
                        </fieldset>
                    </form>
                    <?php } else { ?>
                        <form method="post" name="payment-form" id="payment-form">
                            <fieldset>
                                <div class="form-group">
                                    <label label-default="label-default"><?php echo xlt('Name on Card'); ?></label>
                                    <div class="controls">
                                        <input name="cardHolderName" id="cardHolderName" type="text" class="form-control" pattern="\w+ \w+.*" title="<?php echo xla('Fill your first and last name'); ?>" value="<?php echo attr($patdata['fname']) . ' ' . attr($patdata['lname']) ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="card-element"><?php echo xlt('Credit or Debit Card') ?></label>
                                    <div class="form-group" id="card-element"></div>
                                    <div id="card-errors" role="alert"></div>
                                </div>
                                <div class="col-md-6">
                                    <h4 style="display: inline-block;"><?php echo xlt('Payment Amount'); ?>:&nbsp;
                                        <strong><span id="payTotal"></span></strong></h4>
                                </div>
                                <input type='hidden' name='mode' id='mode' value=''/>
                                <input type='hidden' name='cc_type' id='cc_type' value=''/>
                                <input type='hidden' name='payment' id='paymentAmount' value=''/>
                                <input type='hidden' name='invValues' id='invValues' value=''/>
                            </fieldset>
                        </form>
                    <?php } ?>
                </div>
                <!-- Body  -->
                <div class="modal-footer">
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                        <?php
                        if ($globalsBag->get('payment_gateway') === 'InHouse') { ?>
                            <button id="paySubmit" class="btn btn-primary"><?php echo xlt('Send Payment'); ?></button>
                        <?php } elseif ($globalsBag->get('payment_gateway') === 'AuthorizeNet') { ?>
                            <button id="payAurhorizeNet" class="btn btn-primary"
                                    onclick="sendPaymentDataToAnet(event)"><?php echo xlt('Pay Now'); ?></button>
                        <?php }
                        if ($globalsBag->get('payment_gateway') === 'Stripe') { ?>
                            <button id="stripeSubmit" class="btn btn-primary"><?php echo xlt('Pay Now'); ?></button>
                                                <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var ccerr = <?php echo xlj('Invalid Credit Card Number'); ?>

        // In House CC number Validation
        /*$('#cardNumber').validateCreditCard(function (result) {
            var r = (result.card_type === null ? '' : result.card_type.name.toUpperCase())
            var v = (result.valid === true ? ' Valid Number' : ' Validating')
            if (result.valid === true) {
                document.getElementById("cardtype").style.color = "#00aa00";
            } else {
                document.getElementById("cardtype").style.color = "#aa0000";
            }
            $('#cardtype').text(r + v);
        });*/

        // In House CC Validation
        function validateCC() {
            var result = $('#cardNumber').validateCreditCard();
            var r = (result.card_type == null ? '' : result.card_type.name.toUpperCase())
            var v = (result.valid == true ? ' Valid Card Number' : ' Invalid Card Number')
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
    </script>

    <?php if ($globalsBag->get('payment_gateway') === 'AuthorizeNet' && $session->has('patient_portal_onsite_two')) {
        // Include Authorize.Net dependency to tokenize card.
        // Will return a token to use for payment request keeping
        // credit info off the server.
        ?>
        <script src="portal_payment.authorizenet.js?v=<?=$v_js_includes?>"></script>
    <?php }  // end authorize.net ?>

    <?php if ($globalsBag->get('payment_gateway') === 'Stripe' && $session->has('patient_portal_onsite_two')) { // Begin Include Stripe ?>
        <script src="portal_payment.stripe.js?v=<?=$v_js_includes?>"></script>
    <?php } ?>

    <?php
    if ($globalsBag->get('payment_gateway') === 'Sphere' && $session->has('patient_portal_onsite_two')) {
        echo (new SpherePayment('patient', $pid))->renderSphereJs();
    }
    ?>

    </body>
    <?php } // end else display ?>
