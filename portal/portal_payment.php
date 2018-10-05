<?php
/**
 *
 * namespace OnsitePortal
 *
 * Copyright (C) 2006-2015 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 *
 */
session_start();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    require_once(dirname(__FILE__) . "/../interface/globals.php");
} else {
    session_destroy();
    $ignoreAuth = false;
    require_once(dirname(__FILE__) . "/../interface/globals.php");
    if (! isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
}

require_once(dirname(__FILE__) . "/lib/appsql.class.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/payment.inc.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/invoice_summary.inc.php");
require_once("../custom/code_types.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/encounter_events.inc.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/crypto.php");

$appsql = new ApplicationTable();

$pid = $_REQUEST['hidden_patient_code'] > 0 ? $_REQUEST['hidden_patient_code'] : $pid;

$edata = $appsql->getPortalAudit($pid, 'review', 'payment');
$ccdata = array();
$invdata = array();

if ($edata) {
    $ccdata = json_decode(aes256Decrypt($edata['checksum']), true);
    $invdata = json_decode($edata['table_args'], true);
    echo "<script  type='text/javascript'>var jsondata='" . $edata['table_args'] . "';var ccdata='" . $edata['checksum'] . "'</script>";
}

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
    $var_index ++;
    $balance = bucks($charges - $ptpaid - $inspaid);
    $balance = ( round($duept, 2) != 0 ) ? 0 : $balance; // if balance is due from patient, then insurance balance is displayed as zero
    $encounter = $encounter ? $encounter : '';
    echo " <tr id='tr_" . attr($var_index) . "' >\n";
    echo "  <td class='detail'>" . text(oeFormatShortDate($date)) . "</td>\n";
    echo "  <td class='detail' id='" . attr($date) . "' align='left'>" . htmlspecialchars($encounter, ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_charges_$var_index' >" . htmlspecialchars(bucks($charges), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_inspaid_$var_index' >" . htmlspecialchars(bucks($inspaid * - 1), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_ptpaid_$var_index' >" . htmlspecialchars(bucks($ptpaid * - 1), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_patient_copay_$var_index' >" . htmlspecialchars(bucks($patcopay), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='td_copay_$var_index' >" . htmlspecialchars(bucks($copay), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='balance_$var_index'>" . htmlspecialchars(bucks($balance), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='center' id='duept_$var_index'>" . htmlspecialchars(bucks(round($duept, 2) * 1), ENT_QUOTES) . "</td>\n";
    echo "  <td class='detail' align='right'><input class='form-control' style='width:60px;padding:2px 2px;' type='text' name='" . attr($iname) . "'  id='paying_" . attr($var_index) . "' " . " value='" . '' . "' onchange='coloring();calctotal()'  autocomplete='off' " . "onkeyup='calctotal()'/></td>\n";
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

        $trow = sqlQuery("SELECT option_value FROM list_options WHERE " . "list_id = 'taxrate' AND option_id = ? LIMIT 1", array ($value
        ));
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


// $patdata = getPatientData($pid, 'fname,lname,pubpid');

$patdata = sqlQuery("SELECT " . "p.fname, p.mname, p.lname, p.pubpid,p.pid, i.copay " . "FROM patient_data AS p " . "LEFT OUTER JOIN insurance_data AS i ON " . "i.pid = p.pid AND i.type = 'primary' " . "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", array ($pid
));

$alertmsg = ''; // anything here pops up in an alert box

// If the Save button was clicked...
if ($_POST['form_save']) {
    // $extra = json_decode($_POST['ajax_mode'], true);
    $form_pid = $_POST['form_pid'];
    $form_method = trim($_POST['form_method']);
    $form_source = trim($_POST['form_source']);
    $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');
    $NameNew = $patdata['fname'] . " " . $patdata['lname'] . " " . $patdata['mname'];

    if ($_REQUEST['radio_type_of_payment'] == 'pre_payment') {
        $payment_id = idSqlStatement("insert into ar_session set " . "payer_id = ?" . ", patient_id = ?" . ", user_id = ?" . ", closed = ?" . ", reference = ?" . ", check_date =  now() , deposit_date = now() " . ",  pay_total = ?" . ", payment_type = 'patient'" . ", description = ?" . ", adjustment_code = 'pre_payment'" . ", post_to_date = now() " . ", payment_method = ?", array (
                0,$form_pid,$_SESSION['authUserID'],0,$form_source,$_REQUEST['form_prepayment'],$NameNew,$form_method
        ));

        frontPayment($form_pid, 0, $form_method, $form_source, $_REQUEST['form_prepayment'], 0, $timestamp); // insertion to 'payments' table.
    }

    if ($_POST['form_upay'] && $_REQUEST['radio_type_of_payment'] != 'pre_payment') {
        foreach ($_POST['form_upay'] as $enc => $payment) {
            if ($amount = 0 + $payment) {
                $zero_enc = $enc;
                if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance') {
                    ;
                } else {
                    if (! $enc) {
                        $enc = calendar_arrived($form_pid);
                    }
                }

                // ----------------------------------------------------------------------------------------------------
                // Fetching the existing code and modifier
                $ResultSearchNew = sqlStatement("SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key " . "WHERE code_types.ct_fee=1 AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier", array ($form_pid,$enc
                ));
                if ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                    $Codetype = $RowSearch['code_type'];
                    $Code = $RowSearch['code'];
                    $Modifier = $RowSearch['modifier'];
                } else {
                    $Codetype = '';
                    $Code = '';
                    $Modifier = '';
                }

                // ----------------------------------------------------------------------------------------------------
                if ($_REQUEST['radio_type_of_payment'] == 'copay') { // copay saving to ar_session and ar_activity tables
                    $session_id = idSqlStatement("INSERT INTO ar_session (payer_id,user_id,reference,check_date,deposit_date,pay_total," . " global_amount,payment_type,description,patient_id,payment_method,adjustment_code,post_to_date) " . " VALUES ('0',?,?,now(),now(),?,'','patient','COPAY',?,?,'patient_payment',now())", array (
                            $_SESSION['authId'],$form_source,$amount,$form_pid,$form_method
                    ));

                    $insrt_id = idSqlStatement("INSERT INTO ar_activity (pid,encounter,code_type,code,modifier,payer_type,post_time,post_user,session_id,pay_amount,account_code)" . " VALUES (?,?,?,?,?,0,now(),?,?,?,'PCP')", array ($form_pid,$enc,$Codetype,$Code,$Modifier,'3',$session_id,$amount
                    ));

                    frontPayment($form_pid, $enc, $form_method, $form_source, $amount, 0, $timestamp); // insertion to 'payments' table.
                }

                if ($_REQUEST['radio_type_of_payment'] == 'invoice_balance' || $_REQUEST['radio_type_of_payment'] == 'cash') { // Payment by patient after insurance paid, cash patients similar to do not bill insurance in feesheet.
                    if ($_REQUEST['radio_type_of_payment'] == 'cash') {
                        sqlStatement("update form_encounter set last_level_closed=? where encounter=? and pid=? ", array (4,$enc,$form_pid
                        ));
                        sqlStatement("update billing set billed=? where encounter=? and pid=?", array (1,$enc,$form_pid
                        ));
                    }

                    $adjustment_code = 'patient_payment';
                    $payment_id = idSqlStatement("insert into ar_session set " . "payer_id = ?" . ", patient_id = ?" . ", user_id = ?" . ", closed = ?" . ", reference = ?" . ", check_date =  now() , deposit_date = now() " . ",  pay_total = ?" . ", payment_type = 'patient'" . ", description = ?" . ", adjustment_code = ?" . ", post_to_date = now() " . ", payment_method = ?", array (
                            0,$form_pid,$_SESSION['authUserID'],0,$form_source,$amount,$NameNew,$adjustment_code,$form_method
                    ));

                    // --------------------------------------------------------------------------------------------------------------------

                    frontPayment($form_pid, $enc, $form_method, $form_source, 0, $amount, $timestamp); // insertion to 'payments' table.

                    // --------------------------------------------------------------------------------------------------------------------

                    $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid =? and " . "encounter =? and payer_type=0 and account_code='PCP'", array ($form_pid,$enc
                    )); // new fees screen copay gives account_code='PCP'
                    $rowMoneyGot = sqlFetchArray($resMoneyGot);
                    $Copay = $rowMoneyGot['PatientPay'];

                    // --------------------------------------------------------------------------------------------------------------------

                    // Looping the existing code and modifier
                    $ResultSearchNew = sqlStatement("SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key WHERE code_types.ct_fee=1 " . "AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier", array ($form_pid,$enc
                    ));
                    while ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                        $Codetype = $RowSearch['code_type'];
                        $Code = $RowSearch['code'];
                        $Modifier = $RowSearch['modifier'];
                        $Fee = $RowSearch['fee'];

                        $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where pid =? " . "and code_type=? and code=? and modifier=? and encounter =? and !(payer_type=0 and account_code='PCP')", array ($form_pid,$Codetype,$Code,$Modifier,$enc
                        ));
                        // new fees screen copay gives account_code='PCP'
                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                        $resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where " . "pid =? and code_type=? and code=? and modifier=? and encounter =?", array ($form_pid,$Codetype,$Code,$Modifier,$enc
                        ));
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

                            sqlStatement("insert into ar_activity set " . "pid = ?" . ", encounter = ?" . ", code_type = ?" . ", code = ?" . ", modifier = ?" . ", payer_type = ?" . ", post_time = now() " . ", post_user = ?" . ", session_id = ?" . ", pay_amount = ?" . ", adj_amount = ?" . ", account_code = 'PP'", array (
                                    $form_pid,$enc,$Codetype,$Code,$Modifier,0,3,$payment_id,$insert_value,0
                            ));
                        } // if
                    } // while
                    if ($amount != 0) { // if any excess is there.
                        sqlStatement("insert into ar_activity set " . "pid = ?" . ", encounter = ?" . ", code_type = ?" . ", code = ?" . ", modifier = ?" . ", payer_type = ?" . ", post_time = now() " . ", post_user = ?" . ", session_id = ?" . ", pay_amount = ?" . ", adj_amount = ?" . ", account_code = 'PP'", array (
                                $form_pid,$enc,$Codetype,$Code,$Modifier,0,3,$payment_id,$amount,0
                        ));
                    }

                    // --------------------------------------------------------------------------------------------------------------------
                } // invoice_balance
            } // if ($amount = 0 + $payment)
        } // foreach
    } // if ($_POST['form_upay'])
} // if ($_POST['form_save'])

if ($_POST['form_save'] || $_REQUEST['receipt']) {
    if ($_REQUEST['receipt']) {
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
    "MAX(user) AS user, " . "MAX(encounter) as encounter " . "FROM payments WHERE " . "pid = ? AND dtime = ?", array ($form_pid,$timestamp
    ));

    // Create key for deleting, just in case.
    $ref_id = ( $_REQUEST['radio_type_of_payment'] == 'copay' ) ? $session_id : $payment_id;
    $payment_key = $form_pid . '.' . preg_replace('/[^0-9]/', '', $timestamp) . '.' . $ref_id;

    // get facility from encounter
    $tmprow = sqlQuery("
    SELECT facility_id
    FROM form_encounter
    WHERE encounter = ?", array ($payrow['encounter']
    ));
    $frow = sqlQuery("SELECT * FROM facility " . " WHERE id = ?", array ($tmprow['facility_id']
    ));

    // Now proceed with printing the receipt.
    ?>

<title><?php echo xlt('Receipt for Payment'); ?></title>

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js"></script>
<script type="text/javascript">
$( document ).ready();
function goHome(){
     window.location.replace("./patient/onsiteactivityviews");
}
function notifyPatient(){
    var pid = <?php echo attr($pid);?>;
    var note = $('#pop_receipt').text();
    var formURL = './messaging/handle_note.php';
    $.ajax({
        url: formURL,
        type: "POST",
        data: {'task':'add', 'pid':pid, 'inputBody':note, 'title':'Bill/Collect', 'sendto':'-patient-','noteid':'0'},
        success: function(data, textStatus, jqXHR) {
            alert('Receipt sent to patient via Messages.')
        },
        error: function(jqXHR, status, error) {
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
        <p><?php echo text($frow['name'])?>
        <br><?php echo text($frow['street'])?>
        <br><?php echo text($frow['city'] . ', ' . $frow['state']) . ' ' . text($frow['postal_code'])?>
        <br><?php echo htmlentities($frow['phone'])?>
        <p>
        <div style="text-align: center; margin: auto;">
            <table border='0' cellspacing='8'
                style="text-align: center; margin: auto;">
                <tr>
                    <td><?php echo xlt('Date'); ?>:</td>
                    <td><?php echo text(oeFormatSDFT(strtotime($payrow['dtime']))) ?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Patient'); ?>:</td>
                    <td><?php echo text($patdata['fname']) . " " . text($patdata['mname']) . " " . text($patdata['lname']) . " (" . text($patdata['pubpid']) . ")"?></td>
                </tr>
                <tr>
                    <td><?php echo xlt('Paid Via'); ?>:</td>
                    <td><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'payment_method'), $payrow['method']); ?></td>
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
<style type="text/css">
body {
 /* font-family:sans-serif; font-size:10pt; font-weight:normal */
}

.dehead {
    color: #000000; /*font-family:sans-serif; font-size:10pt;*/
    font-weight: bold
}

.detail {
    padding: 1px 1px;
    /* width: 65px; */
    color: #000000; /*font-family:sans-serif; font-size:10pt; */
    font-weight: normal
}
</style>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-creditcardvalidator-1-1-0/jquery.creditCardValidator.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>

<script type="text/javascript">
var mypcc = '1';
function calctotal() {
 var flag=0;
 var f = document.forms["payfrm"];
 var total = 0;
 for (var i = 0; i < f.elements.length; ++i) {
  var elem = f.elements[i];
  var ename = elem.name;
  if (ename.indexOf('form_upay[') == 0 || ename.indexOf('form_bpay[') == 0) {
   if (elem.value.length > 0){
       total += Number(elem.value);
        if(total < 0) flag=1;
   }
  }
 }
     f.form_paytotal.value = Number(total).toFixed(2);
     if(flag){
        $('#payfrm')[0].reset();
        alert("<?php echo addslashes(xl('Negative payments not accepted')) ?>")
    }
 return true;
}
function coloring()
 {
   for (var i = 1; ; ++i)
    {
      if(document.getElementById('paying_'+i))
       {
        paying=document.getElementById('paying_'+i).value*1;
        patient_balance=document.getElementById('duept_'+i).innerHTML*1;
        //balance=document.getElementById('balance_'+i).innerHTML*1;
        if(patient_balance>0 && paying>0)
         {
            if(paying>patient_balance)
             {
              document.getElementById('paying_'+i).style.background='#FF0000';
             }
            else if(paying<patient_balance)
             {
              document.getElementById('paying_'+i).style.background='#99CC00';
             }
            else if(paying==patient_balance)
             {
              document.getElementById('paying_'+i).style.background='#ffffff';
             }
         }
        else
         {
          document.getElementById('paying_'+i).style.background='#ffffff';
         }
       }
      else
       {
        break;
       }
    }
 }
function CheckVisible(MakeBlank)
 {//Displays and hides the check number text box.
   if(document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='check_payment' ||
         document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='bank_draft'  )
   {
    document.getElementById('check_number').disabled=false;
   }
   else
   {
    document.getElementById('check_number').disabled=true;
   }
 }
function validate()
 {
  var f = document.forms["payfrm"];
  ok=-1;
  //no checks taken here....
  issue='no';
   /*if(((document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='check_payment' ||
         document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='bank_draft') &&
       document.getElementById('check_number').value=='' ))
   {
    alert("<?php //echo addslashes( xl('Please Fill the Check/Ref Number')) ?>");
    document.getElementById('check_number').focus();
    return false;
   }*/

   if(document.getElementById('radio_type_of_payment_self1').checked==false &&
          document.getElementById('radio_type_of_payment1').checked==false
           && document.getElementById('radio_type_of_payment2').checked==false
           && document.getElementById('radio_type_of_payment4').checked==false)
    {
      alert("<?php //echo addslashes( xl('Please Select Type Of Payment.')) ?>");
       return false;
    }
  if(document.getElementById('radio_type_of_payment_self1').checked==true ||  document.getElementById('radio_type_of_payment1').checked==true)
   {
     for (var i = 0; i < f.elements.length; ++i)
     {
      var elem = f.elements[i];
      var ename = elem.name;
      if (ename.indexOf('form_upay[0') == 0) //Today is this text box.
      {
       if(elem.value*1>0)
        {//A warning message, if the amount is posted with out encounter.
         if(confirm("<?php echo addslashes(xl('Are you sure to post for today?')) ?>"))
          {
           ok=1;
          }
         else
          {
           elem.focus();
           return false;
          }
        }
       break;
      }
    }
   }
//CO-PAY
  /* if(document.getElementById('radio_type_of_payment1').checked==true)
   {
     var total = 0;
     for (var i = 0; i < f.elements.length; ++i)
     {
      var elem = f.elements[i];
      var ename = elem.name;
      if (ename.indexOf('form_upay[') == 0) //Today is this text box.
      {
       if(f.form_paytotal.value*1!=elem.value*1)//Total CO-PAY is not posted against today
        {//A warning message, if the amount is posted against an old encounter.
         if(confirm("<?php //echo addslashes( xl('You are posting against an old encounter?')) ?>"))
          {
           ok=1;
          }
        // else
          {
           elem.focus();
           return false;
          }
        }
       break;
      }
    }
   }*///Co Pay
 else if( document.getElementsByName('form_paytotal')[0].value <= 0 )//total 0
  {
    alert("<?php echo addslashes(xl('Invalid Total!')) ?>")
    return false;
  }
 if(ok==-1)
  {
     //return true;
     if(confirm("<?php echo addslashes(xl('Payment Validated: Save?')) ?>"))
      {
       return true;
      }
     else
      {
       return false;
      }
  }
}
function cursor_pointer()
 {//Point the cursor to the latest encounter(Today)
     var f = document.forms["payfrm"];
     var total = 0;
     for (var i = 0; i < f.elements.length; ++i)
     {
      var elem = f.elements[i];
      var ename = elem.name;
      if (ename.indexOf('form_upay[') == 0)
      {
       elem.focus();
       break;
      }
    }
 }
 //=====================================================
function make_it_hide_enc_pay()
 {
      document.getElementById('td_head_insurance_payment').style.display="none";
      document.getElementById('td_head_patient_co_pay').style.display="none";
      document.getElementById('td_head_co_pay').style.display="none";
      document.getElementById('td_head_insurance_balance').style.display="none";
  for (var i = 1; ; ++i)
  {
       var td_inspaid_elem = document.getElementById('td_inspaid_'+i)
    var td_patient_copay_elem = document.getElementById('td_patient_copay_'+i)
       var td_copay_elem = document.getElementById('td_copay_'+i)
       var balance_elem = document.getElementById('balance_'+i)
   if (td_inspaid_elem)
   {
    td_inspaid_elem.style.display="none";
        td_patient_copay_elem.style.display="none";
    td_copay_elem.style.display="none";
    balance_elem.style.display="none";
   }
  else
   {
    break;
   }
  }
  document.getElementById('td_total_4').style.display="none";
  document.getElementById('td_total_7').style.display="none";
    document.getElementById('td_total_8').style.display="none";
  document.getElementById('td_total_6').style.display="none";

  document.getElementById('table_display').width="420px";
 }

 //=====================================================
function make_visible()
 {
  document.getElementById('td_head_rep_doc').style.display="";
  document.getElementById('td_head_description').style.display="";
  document.getElementById('td_head_total_charge').style.display="none";
  document.getElementById('td_head_insurance_payment').style.display="none";
  document.getElementById('td_head_patient_payment').style.display="none";
  document.getElementById('td_head_patient_co_pay').style.display="none";
  document.getElementById('td_head_co_pay').style.display="none";
  document.getElementById('td_head_insurance_balance').style.display="none";
  document.getElementById('td_head_patient_balance').style.display="none";
  for (var i = 1; ; ++i)
  {
   var td_charges_elem = document.getElementById('td_charges_'+i)
   var td_inspaid_elem = document.getElementById('td_inspaid_'+i)
   var td_ptpaid_elem = document.getElementById('td_ptpaid_'+i)
   var td_patient_copay_elem = document.getElementById('td_patient_copay_'+i)
   var td_copay_elem = document.getElementById('td_copay_'+i)
   var balance_elem = document.getElementById('balance_'+i)
   var duept_elem = document.getElementById('duept_'+i)
   if (td_charges_elem)
   {
    td_charges_elem.style.display="none";
    td_inspaid_elem.style.display="none";
    td_ptpaid_elem.style.display="none";
    td_patient_copay_elem.style.display="none";
    td_copay_elem.style.display="none";
    balance_elem.style.display="none";
    duept_elem.style.display="none";
   }
  else
   {
    break;
   }
  }
  document.getElementById('td_total_7').style.display="";
  document.getElementById('td_total_8').style.display="";
  document.getElementById('td_total_1').style.display="none";
  document.getElementById('td_total_2').style.display="none";
  document.getElementById('td_total_3').style.display="none";
  document.getElementById('td_total_4').style.display="none";
  document.getElementById('td_total_5').style.display="none";
  document.getElementById('td_total_6').style.display="none";

  document.getElementById('table_display').width="505px";
 }
function make_it_hide()
 {
  document.getElementById('td_head_rep_doc').style.display="none";
  document.getElementById('td_head_description').style.display="none";
  document.getElementById('td_head_total_charge').style.display="";
  document.getElementById('td_head_insurance_payment').style.display="";
  document.getElementById('td_head_patient_payment').style.display="";
  document.getElementById('td_head_patient_co_pay').style.display="";
    document.getElementById('td_head_co_pay').style.display="";
  document.getElementById('td_head_insurance_balance').style.display="";
  document.getElementById('td_head_patient_balance').style.display="";
  for (var i = 1; ; ++i)
  {
   var td_charges_elem = document.getElementById('td_charges_'+i)
   var td_inspaid_elem = document.getElementById('td_inspaid_'+i)
   var td_ptpaid_elem = document.getElementById('td_ptpaid_'+i)
   var td_patient_copay_elem = document.getElementById('td_patient_copay_'+i)
   var td_copay_elem = document.getElementById('td_copay_'+i)
   var balance_elem = document.getElementById('balance_'+i)
   var duept_elem = document.getElementById('duept_'+i)
   if (td_charges_elem)
   {
    td_charges_elem.style.display="";
    td_inspaid_elem.style.display="";
    td_ptpaid_elem.style.display="";
        td_patient_copay_elem.style.display="";
    td_copay_elem.style.display="";
    balance_elem.style.display="";
    duept_elem.style.display="";
   }
  else
   {
    break;
   }
  }
  document.getElementById('td_total_1').style.display="";
  document.getElementById('td_total_2').style.display="";
  document.getElementById('td_total_3').style.display="";
  document.getElementById('td_total_4').style.display="";
  document.getElementById('td_total_5').style.display="";
  document.getElementById('td_total_6').style.display="";
  document.getElementById('td_total_7').style.display="";
  document.getElementById('td_total_8').style.display="";

  document.getElementById('table_display').width="100%";
 }
function make_visible_radio()
 {
  document.getElementById('tr_radio1').style.display="";
  document.getElementById('tr_radio2').style.display="none";
 }
function make_hide_radio()
 {
  document.getElementById('tr_radio1').style.display="none";
  document.getElementById('tr_radio2').style.display="";
 }
function make_visible_row()
 {
  document.getElementById('table_display').style.display="";
  document.getElementById('table_display_prepayment').style.display="none";
 }
function make_hide_row()
 {
  document.getElementById('table_display').style.display="none";
  document.getElementById('table_display_prepayment').style.display="";
 }
function make_self()
 {
  make_visible_row();
  make_it_hide();
  make_it_hide_enc_pay();
  document.getElementById('radio_type_of_payment_self1').checked=true;
  cursor_pointer();
 }
function make_insurance()
 {
  make_visible_row();
  make_it_hide();
  cursor_pointer();
  document.getElementById('radio_type_of_payment1').checked=true;
 }
 //--------------------------------------------------------------------------------------------------//
$('#paySubmit').click( function(e) {
    e.preventDefault()
    $("#mode").val( "portal-save" );
    var inv_values= JSON.stringify(getFormObj('payfrm'));
    var extra_values=JSON.stringify(getFormObj('paycredit'));
    var extra = "&inv_values="+inv_values+"&extra_values="+extra_values;

    var flag = 0
    var liburl = './lib/paylib.php';
    $.ajax({
        type: "POST",
        url: liburl,
        data: $("#payfrm").serialize()+extra,
        beforeSend: function(xhr){
            if( validateCC() !== true) return false;
            if( $('#pin').val() == "" || $('#ccname').val() == "" || $('#ccyear').val() == "" || $('#ccmonth').val() == ""){
                 alert("<?php echo addslashes(xl('Invalid Credit Card Values: Please correct')) ?>")
                 return false;
            }
            if( validate() != true){
                flag = 1;
                alert("<?php echo addslashes(xl('Validation error: Fix and resubmit. This popup info is preserved!')) ?>")
                return false;
            }
            $("#openPayModal .close").click()
        },
        error: function(qXHR, textStatus, errorThrow){
            console.log("There was an error:"+errorThrow);
        },
        success: function(templateHtml, textStatus, jqXHR){
            alert("<?php echo addslashes(xl('Payment successfully sent for authorization. You will be notified when payment is posted. Until payment is accepted and you are notified, you may resubmit this payment at anytime with new amounts or different credit card. Thank you')) ?>")
            window.location.reload(false);
        }
    });
     if(flag)
        $("#openPayModal .close").click();
});
 //---------------------------------------------------------------------------------------//
$("#payfrm").on('submit', function(e){
    e.preventDefault();
    var thisform = this;
    $("#mode").val( "review-save" );
    var inv_values= JSON.stringify(getFormObj('payfrm'));
    var extra_values=JSON.stringify(getFormObj('paycredit'));
    var extra = "&inv_values="+inv_values+"&extra_values="+extra_values;

    var flag = 0
    var liburl ='<?php echo $GLOBALS["webroot"] ?>/portal/lib/paylib.php';
    $.ajax({
        type: "POST",
        url: liburl,
        data: $("#payfrm").serialize()+extra,
        beforeSend: function(xhr){
            if( validate() != true){
                flag = 1;
                alert("<?php echo addslashes(xl('Validation error: Fix and resubmit. Payment values are preserved!')) ?>")
                return false;
            }
        },
        error: function(xhr, textStatus, error){
            alert("<?php echo addslashes(xl('There is a Post error')) ?>")
            console.log("There was an error:"+textStatus);
            return false;
        },
        success: function(templateHtml, textStatus, jqXHR){
            thisform.submit();
        }
    });
});
function getFormObj(formId) {
    var formObj = {};
    var inputs = $('#'+formId).serializeArray();
    $.each(inputs, function (i, input) {
        formObj[input.name] = input.value;
    });
    return formObj;
}
function formRepopulate(jsondata){
    data = $.parseJSON(jsondata);
    $.each(data, function(name, val){
        var $el = $('[name="'+name+'"]'),
            type = $el.attr('type');
        switch(type){
            case 'checkbox':
                $el.prop('checked',true);
                break;
            case 'radio':
                $el.filter('[value="'+val+'"]').prop('checked', true);
                break;
            default:
                $el.val(val);
        }
    });
}
function getAuth(){
    var authnum = prompt("<?php echo  xlt('Please enter card comfirmation authorization') ?>", "");
    if (authnum != null) {
        $('#check_number').val(authnum);
    }
}
</script>

<body class="skin-blue" onunload='imclosing()' onLoad="cursor_pointer();"
    style="text-align: center; margin: auto;">

    <form id="payfrm" method='post'
        action='<?php echo $GLOBALS["webroot"] ?>/portal/portal_payment.php'>
        <input type='hidden' name='form_pid' value='<?php echo attr($pid) ?>' />
        <input type='hidden' name='form_save'
            value='<?php echo xlt('Invoice');?>' />

        <table>
            <tr height="10">
                <td colspan="3">&nbsp;</td>
            </tr>

            <tr>
                <td colspan='3' align='center' class='text'><b><?php echo xlt('Accept Payment for'); ?>&nbsp;:&nbsp;&nbsp;<?php

                echo htmlspecialchars($patdata['fname'], ENT_QUOTES) . " " . htmlspecialchars($patdata['lname'], ENT_QUOTES) . " " . htmlspecialchars($patdata['mname'], ENT_QUOTES) . " (" . htmlspecialchars($patdata['pid'], ENT_QUOTES) . ")"?></b>
    <?php $NameNew=$patdata['fname'] . " " .$patdata['lname']. " " .$patdata['mname'];?>
  </td>
            </tr>
            <tr height="15">
                <td colspan='3'></td>
            </tr>
            <tr>
                <td class='text'>
    <?php echo xlt('Payment Method'); ?>:
  </td>
                <td colspan='2'><select name="form_method" id="form_method"
                    class="text" onChange='CheckVisible("yes")'>
    <?php
    $query1112 = "SELECT * FROM list_options where list_id=?  ORDER BY seq, title ";
    $bres1112 = sqlStatement($query1112, array ('payment_method'));
    while ($brow1112 = sqlFetchArray($bres1112)) {
        if ($brow1112['option_id'] != 'credit_card' || $brow1112['option_id'] == 'electronic' || $brow1112['option_id'] == 'bank_draft') {
            continue;
        }

        echo "<option value='" . htmlspecialchars($brow1112['option_id'], ENT_QUOTES) . "'>" . htmlspecialchars(xl_list_label($brow1112['title']), ENT_QUOTES) . "</option>";
    }
    ?>
  </select></td>
            </tr>

            <tr height="5">
                <td colspan='3'></td>
            </tr>

            <tr>
                <td class='text'>
    <?php echo xla('Authorized'); ?>:
  </td>
                <td colspan='2'>
    <?php
    if (isset($_SESSION['authUserID'])) {
        echo "<input type='text'  id='check_number' name='form_source' style='width:120px;' value='" . htmlspecialchars($payrow['source'], ENT_QUOTES) . "'>";
    }
    ?>
  </td>
            </tr>
            <tr height="5">
                <td colspan='3'></td>
            </tr>
            <tr>
                <td class='text' valign="middle">
    <?php echo xlt('Patient Coverage'); ?>:
  </td>
                <td class='text' colspan="2">
                    <input type="radio"    name="radio_type_of_coverage" id="radio_type_of_coverage1"
                    value="self" onClick="make_visible_radio();make_self();" />
                    <?php echo xlt('Self'); ?>
                      <input type="radio" name="radio_type_of_coverage"    id="radio_type_of_coverag2" value="insurance" checked="checked"
                    onClick="make_hide_radio();make_insurance();" />
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
                    <input type="radio"    name="radio_type_of_payment" id="radio_type_of_payment_self1"
                    value="cash" onClick="make_visible_row();make_it_hide_enc_pay();cursor_pointer();" />
                    <?php echo xlt('Encounter Payment'); ?>
                </td>
            </tr>
            <tr id="tr_radio2">
                <!-- For radio self -->
                <td class='text' valign="top"><?php echo xlt('Payment against'); ?>:
  </td>
                <td class='text' colspan="2"><input type="radio"    name="radio_type_of_payment" id="radio_type_of_payment1" checked="checked"
                    value="copay" onClick="make_visible_row();cursor_pointer();" /><?php echo xlt('Co Pay'); ?>
          <input type="radio" name="radio_type_of_payment" id="radio_type_of_payment2"
                    value="invoice_balance" onClick="make_visible_row();" /><?php echo xlt('Invoice Balance'); ?><br />
        <input type="radio" name="radio_type_of_payment"    id="radio_type_of_payment4" value="pre_payment"
                    onClick="make_hide_row();" /><?php echo xlt('Pre Pay'); ?></td>
            </tr>
            <tr height="15">
                <td colspan='3'></td>
            </tr>
        </table>
        <table width="35%" border="0" cellspacing="0" cellpadding="0" id="table_display_prepayment" style="display: none">
            <tr>
                <td class='detail'><?php echo xlt('Pre Payment'); ?></td>
                <td><input class="form-control" type='text' name='form_prepayment' style='width: 100px' /></td>
            </tr>
        </table>
        <table id="table_display" style="width: 100%; background: #eee;" class="table table-striped table-responsive">
            <thead>
            </thead>
            <tbody>
                <!-- <table border='0' id="table_display" cellpadding='0' cellspacing='0' width='100%'> -->
    <tr bgcolor="#cccccc" id="tr_head">
    <td class="dehead" width="60">
    <?php echo xlt('DOS')?>
  </td>
                    <td class="dehead" width="120">
    <?php echo xlt('Visit Reason')?>
  </td>
                    <td class="dehead" align="center" width="70" id="td_head_total_charge">
    <?php echo xlt('Total Charge')?>
  </td>
                    <td class="dehead" align="center" width="70" id="td_head_rep_doc" style='display: none'>
    <?php echo xlt('Report/ Form')?>
  </td>
                    <td class="dehead" align="center" width="200"    id="td_head_description" style='display: none'>
    <?php echo xlt('Description')?>
  </td>
                    <td class="dehead" align="center" width="70" id="td_head_insurance_payment">
    <?php echo xlt('Insurance Payment')?>
  </td>
                    <td class="dehead" align="center" width="70" id="td_head_patient_payment">
    <?php echo xlt('Patient Payment')?>
  </td>
                    <td class="dehead" align="center" width="55" id="td_head_patient_co_pay">
    <?php echo xlt('Co Pay Paid')?>
  </td>
                    <td class="dehead" align="center" width="55" id="td_head_co_pay">
    <?php echo xlt('Required Co Pay')?>
  </td>
                    <td class="dehead" align="center" width="70" id="td_head_insurance_balance">
    <?php echo xlt('Insurance Balance')?>
  </td>
                    <td class="dehead" align="center" width="70" id="td_head_patient_balance">
    <?php echo xlt('Patient Balance')?>
  </td>
                    <td class="dehead" align="center" width="50">
    <?php echo xlt('Paying')?>
  </td>
                </tr>
<?php
    $encs = array ();
    // Get the unbilled service charges and payments by encounter for this patient.
    //
    $query = "SELECT fe.encounter, fe.reason, b.code_type, b.code, b.modifier, b.fee, " . "LEFT(fe.date, 10) AS encdate ,fe.last_level_closed " . "FROM  form_encounter AS fe left join billing AS b  on " . "b.pid = ? AND b.activity = 1  AND " . // AND b.billed = 0
"b.code_type != 'TAX' AND b.fee != 0 " . "AND fe.pid = b.pid AND fe.encounter = b.encounter " . "where fe.pid = ? " . "ORDER BY b.encounter";
    $bres = sqlStatement($query, array ($pid,$pid));
    //
while ($brow = sqlFetchArray($bres)) {
    $key = 0 + $brow['encounter'];
    if (empty($encs[$key])) {
        $encs[$key] = array ('encounter' => $brow['encounter'],'date' => $brow['encdate'],'last_level_closed' => $brow['last_level_closed'],'charges' => 0,'payments' => 0,'reason'=>$brow['reason']
        );
    }

    if ($brow['code_type'] === 'COPAY') {
        // $encs[$key]['payments'] -= $brow['fee'];
    } else {
        $encs[$key]['charges'] += $brow['fee'];
        // Add taxes.
        $sql_array = array ();
        $query = "SELECT taxrates FROM codes WHERE " . "code_type = ? AND " . "code = ? AND ";
        array_push($sql_array, $code_types[$brow['code_type']]['id'], $brow['code']);
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
    $query = "SELECT fe.encounter, fe.reason, s.drug_id, s.fee, " . "LEFT(fe.date, 10) AS encdate,fe.last_level_closed " . "FROM form_encounter AS fe left join drug_sales AS s " . "on s.pid = ? AND s.fee != 0 " . // AND s.billed = 0
"AND fe.pid = s.pid AND fe.encounter = s.encounter " . "where fe.pid = ? " . "ORDER BY s.encounter";

    $dres = sqlStatement($query, array ($pid,$pid));
    //
while ($drow = sqlFetchArray($dres)) {
    $key = 0 + $drow['encounter'];
    if (empty($encs[$key])) {
        $encs[$key] = array ('encounter' => $drow['encounter'],'date' => $drow['encdate'],'last_level_closed' => $drow['last_level_closed'],'charges' => 0,'payments' => 0
        );
    }

    $encs[$key]['charges'] += $drow['fee'];
    // Add taxes.
    $trow = sqlQuery("SELECT taxrates FROM drug_templates WHERE drug_id = ? " . "ORDER BY selector LIMIT 1", array ($drow['drug_id']
    ));
    $encs[$key]['charges'] += calcTaxes($trow, $drow['fee']);
}

    ksort($encs, SORT_NUMERIC);
    $gottoday = false;
    // Bringing on top the Today always
foreach ($encs as $key => $value) {
    $dispdate = $value['date'];
    if (strcmp($dispdate, $today) == 0 && ! $gottoday) {
        $gottoday = true;
        break;
    }
}

    // If no billing was entered yet for today, then generate a line for
    // entering today's co-pay.
    //
if (! $gottoday) {
    // echoLine("form_upay[0]", date("Y-m-d"), 0, 0, 0, 0 /*$duept*/);//No encounter yet defined.
}

    $gottoday = false;
foreach ($encs as $key => $value) {
    $enc = $value['encounter'];
    $reason = $value['reason'];
    $dispdate = $value['date'];
    if (strcmp($dispdate, $today) == 0 && ! $gottoday) {
        $dispdate = date("Y-m-d");
        $gottoday = true;
    }

    // ------------------------------------------------------------------------------------
    $inscopay = getCopay($pid, $dispdate);
    $patcopay = getPatientCopay($pid, $enc);
    // Insurance Payment
    // -----------------
    $drow = sqlQuery("SELECT  SUM(pay_amount) AS payments, " . "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " . "pid = ? and encounter = ? and " . "payer_type != 0 and account_code!='PCP' ", array ($pid,$enc
    ));
    $dpayment = $drow['payments'];
    $dadjustment = $drow['adjustments'];
// Patient Payment
// ---------------
    $drow = sqlQuery("SELECT  SUM(pay_amount) AS payments, " . "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " . "pid = ? and encounter = ? and " . "payer_type = 0 and account_code!='PCP' ", array ($pid,$enc
    ));
    $dpayment_pat = $drow['payments'];

// ------------------------------------------------------------------------------------
// NumberOfInsurance
    $ResultNumberOfInsurance = sqlStatement("SELECT COUNT( DISTINCT TYPE ) NumberOfInsurance FROM insurance_data
            where pid = ? and provider>0 ", array ($pid
    ));
    $RowNumberOfInsurance = sqlFetchArray($ResultNumberOfInsurance);
    $NumberOfInsurance = $RowNumberOfInsurance['NumberOfInsurance'] * 1;
// ------------------------------------------------------------------------------------
    $duept = 0;
    if (( ( $NumberOfInsurance == 0 || $value['last_level_closed'] == 4 || $NumberOfInsurance == $value['last_level_closed'] ) )) { // Patient balance
        $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " . "pid = ? and encounter = ? AND activity = 1", array ($pid,$enc
        ));
        $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " . "pid = ? and encounter = ? ", array ($pid,$enc
        ));
        $drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " . "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " . "pid = ? and encounter = ? ", array ($pid,$enc
        ));
        $duept = $brow['amount'] + $srow['amount'] - $drow['payments'] - $drow['adjustments'];
    }

    echoLine("form_upay[$enc]", $dispdate, $value['charges'], $dpayment_pat, ( $dpayment + $dadjustment ), $duept, ($enc.':'.$reason), $inscopay, $patcopay);
}

    // Continue with display of the data entry form.
    ?>
<tr>
                    <td class="dehead" id='td_total_1'></td>
                    <td class="dehead" id='td_total_2'></td>
                    <td class="dehead" id='td_total_3'></td>
                    <td class="dehead" id='td_total_4'></td>
                    <td class="dehead" id='td_total_5'></td>
                    <td class="dehead" id='td_total_6'></td>
                    <td class="dehead" id='td_total_7'></td>
                    <td class="dehead" id='td_total_8'></td>
                    <td class="dehead" align="right"><?php echo xlt('Total');?></td>
                    <td class="dehead" align="right"><input class="form-control" type='text' name='form_paytotal'
                    value='' style='color: #00aa00; width: 65px; padding: 1px 1px;' readonly />
                    </td>
                </tr>
        </table>
<?php
if (isset($ccdata["name"])) {
    echo '<div class="col-xs-12 col-md-4 col-lg-4">
        <div class="panel panel-default height">';
    if (! isset($_SESSION['authUserID'])) {
        echo '<div class="panel-heading">'.xlt("Payment Information").'<span style="color:#cc0000"><em> '.xlt("Pending Auth since").': </em>'.text($edata["date"]).'</span></div>';
    } else {
        echo '<div class="panel-heading">'.xlt("Payment Information").' <button type="button" class="btn btn-danger btn-sm" onclick="getAuth()">'.xlt("Authorize").'</button></div>';
    }
} else {
    echo '<div style="display:none" class="col-xs-12 col-md-6 col-lg-6"><div class="panel panel-default height"><div class="panel-heading">'.xlt("Payment Information").' </div>';
}
    ?>
                     <div class="panel-body">
                             <strong><?php echo xlt('Card Name');?>:  </strong><span id="cn"><?php echo attr($ccdata["cc_type"])?></span><br>
                            <strong><?php echo xlt('Name on Card');?>:  </strong><span id="nc"><?php echo attr($ccdata["name"])?></span><br>
                            <strong><?php echo xlt('Card Number');?>:  </strong><span id="ccn"><?php
                            if (isset($_SESSION['authUserID'])) {
                                echo $ccdata["cc_number"] . "</span><br>";
                            } else {
                                echo "**********  ".substr($ccdata["cc_number"], -4) . "</span><br>";
                            }
                                    ?>
                            <strong><?php echo xlt('Exp Date');?>:  </strong><span id="ed"><?php echo attr($ccdata["month"])."/".attr($ccdata["year"])?></span><br>
                            <strong><?php echo xlt('Charge Total');?>:  </strong><span id="ct"><?php echo attr($invdata["form_paytotal"])?></span><br>
                      </div>
                  </div>
             </div>
    <p>
<?php
if (! isset($_SESSION['authUserID'])) {
    echo '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#openPayModal">' . xlt("Pay Invoice") . '</button>';
} else {
    echo "<button type='submit' class='btn btn-danger' form='payfrm'>" . xlt('Post Payment') . "</button>";
}
    ?>
 &nbsp;
    </p>
        <input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo attr($pid);?>" />
        <input type='hidden' name='mode' id='mode' value='' />
    </form>

<script type="text/javascript">
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
                    <h3><?php echo xlt('Submit Payment for Authorization');?></h3>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body container">
                    <form id='paycredit' class="form-horizontal col-xs-12 col-sm-6 col-md-6">
                        <fieldset>
                            <div class="control-group">
                                <label label-default="label-default" class="control-label"><?php echo xlt('Name on Card');?></label>
                                <div class="controls">
                                    <input name="name" id="ccname" type="text" class="form-control"
                                        pattern="\w+ \w+.*" title="<?php echo xla('Fill your first and last name'); ?>"
                                        required value="" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo xlt('Card Number');?></label>
                                <div class="controls">
                                    <div class="row">
                                    <div class="col-sm-12">
                                            <input name="cc_number" id="cc_number" type="text" class="form-control inline col-sm-3"
                                                autocomplete="off" maxlength="19" pattern="\d" onchange="validateCC()"
                                                title="<?php echo xla('Card Number'); ?>" required value="" />
                                            <input disabled name="cardtype" id="cardtype" type="text" class="form-control inline" title="<?php echo xla('Card Type'); ?>" style="max-width:160px;font-weight:bold;color:red;" value="" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label label-default="label-default" class="control-label"><?php echo xlt('Card Expiry Date');?></label>
                                <div class="controls">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select name="month" id="ccmonth" class="form-control">
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
                                        <div class="col-md-3">
                                            <select name="year" id="ccyear" class="form-control">
                                                <option value=""><?php echo xlt('Select Year'); ?></option>
                                                <option value="2017">2017</option>
                                                <option value="2018">2018</option>
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                                <option value="2021">2021</option>
                                                <option value="2022">2022</option>
                                                <option value="2023">2023</option>
                                                <option value="2024">2024</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label label-default="label-default" class="control-label"><?php echo xlt('Card CVV');?></label>
                                <div class="controls">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input name="pin" id="pin" type="text" class="form-control"
                                                autocomplete="off" maxlength="4" pattern="\d{3}" onfocus="validateCC()"
                                                title="<?php echo xlt('Three or four digits at back of your card'); ?>" required value="" />
                                        </div>
                                        <div class="col-md-3">
                                            <img src='./images/img_cvc.png' style='height: 58px; width: auto'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button id="paySubmit" class="btn btn-danger"><?php echo xlt('Submit');?></button>
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo xlt('Cancel');?></button>
                            </div>
                        </fieldset>
                        <input type='hidden' name='cc_type' id='cc_type' value='' />
                    </form>
                </div>
                <!-- Body  -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo xlt('Close');?></button>
                </div>
            </div>
        </div>
    </div>
<?php echo "<script>var ccerr='". xlt('Invalid Credit Card Number') . "';</script>";?>
<script type="text/javascript">
$('#cc_number').validateCreditCard(function(result){
    var r = (result.card_type == null ? '' : result.card_type.name.toUpperCase())
    var v = (result.valid== true ? ' Valid #' : ' Invalid #')
    $('#cardtype').val(r+v);
 });
function validateCC() {
    var result = $('#cc_number').validateCreditCard();
    var r = (result.card_type == null ? '' : result.card_type.name.toUpperCase())
    var v = (result.valid == true ? ' Okay' : ' Invalid #')
    $('#cardtype').val(r+v);
    $('#cc_type').val(r);
    if(!result.valid){
         alert(ccerr)
         return false;
    }
    else{
        return true;
    }
}
</script>
</body>
<?php
}
?>
