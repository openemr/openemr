<?php
// Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/invoice_summary.inc.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/encounter_events.inc.php");
$pid = $_REQUEST['hidden_patient_code'] > 0 ? $_REQUEST['hidden_patient_code'] : $pid;

$INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;
?>
<html>
<head>
<?php html_header_show();?>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<?php
// Format dollars for display.
//
function bucks($amount) {
  if ($amount) {
    $amount = oeFormatMoney($amount);
    return $amount;
  }
  return '';
}

function rawbucks($amount) {
  if ($amount) {
    $amount = sprintf("%.2f", $amount);
    return $amount;
  }
  return '';
}

// Get the co-pay amount that is effective on the given date.
// Or if no insurance on that date, return -1.
//
function getCopay($patient_id, $encdate) {
 $tmp = sqlQuery("SELECT provider, copay FROM insurance_data " .
   "WHERE pid = '$patient_id' AND type = 'primary' " .
   "AND date <= '$encdate' ORDER BY date DESC LIMIT 1");
 if ($tmp['provider']) return sprintf('%01.2f', 0 + $tmp['copay']);
 return 0;
}

// Get the total co-pay amount paid by the patient for an encounter
function getPatientCopay($patient_id, $encounter) {
	$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where ".
	  "pid = ? and encounter = ? and payer_type=0 and account_code='PCP'",
	  array($patient_id,$encounter));
	 //new fees screen copay gives account_code='PCP'
	$rowMoneyGot = sqlFetchArray($resMoneyGot);
	$Copay=$rowMoneyGot['PatientPay'];
	return $Copay*-1;
}

// Display a row of data for an encounter.
//
$var_index=0;
function echoLine($iname, $date, $charges, $ptpaid, $inspaid, $duept,$encounter=0,$copay=0,$patcopay=0) {
  global $var_index;
  $var_index++;
  $balance = bucks($charges - $ptpaid - $inspaid - $copay*-1);
  $balance = (round($duept,2) != 0) ? 0 : $balance;//if balance is due from patient, then insurance balance is displayed as zero
  $encounter = $encounter ? $encounter : '';
  echo " <tr id='tr_".attr($var_index)."' >\n";
  echo "  <td class='detail'>" . text(oeFormatShortDate($date)) . "</td>\n";
  echo "  <td class='detail' id='".attr($date)."' align='center'>" . htmlspecialchars($encounter, ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_charges_$var_index' >" . htmlspecialchars(bucks($charges), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_inspaid_$var_index' >" . htmlspecialchars(bucks($inspaid*-1), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_ptpaid_$var_index' >" . htmlspecialchars(bucks($ptpaid*-1), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_patient_copay_$var_index' >" . htmlspecialchars(bucks($patcopay), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='td_copay_$var_index' >" . htmlspecialchars(bucks($copay), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='balance_$var_index'>" . htmlspecialchars(bucks($balance), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='center' id='duept_$var_index'>" . htmlspecialchars(bucks(round($duept,2)*1), ENT_QUOTES) . "</td>\n";
  echo "  <td class='detail' align='right'><input type='text' name='".attr($iname)."'  id='paying_".attr($var_index)."' " .
    " value='" .  '' . "' onchange='coloring();calctotal()'  autocomplete='off' " .
    "onkeyup='calctotal()'  style='width:50px'/></td>\n";
  echo " </tr>\n";
}

// Post a payment to the payments table.
//
function frontPayment($patient_id, $encounter, $method, $source, $amount1, $amount2) {
  global $timestamp;
  $tmprow = sqlQuery("SELECT date FROM form_encounter WHERE " .
    "encounter=? and pid=?",
		array($encounter,$patient_id));
	//the manipulation is done to insert the amount paid into payments table in correct order to show in front receipts report,
	//if the payment is for today's encounter it will be shown in the report under today field and otherwise shown as previous
  $tmprowArray=explode(' ',$tmprow['date']);
  if(date('Y-m-d')==$tmprowArray[0])
   {
    if($amount1==0)
	 {
	  $amount1=$amount2;
	  $amount2=0;
	 }
   }
  else
   {
    if($amount2==0)
	 {
	  $amount2=$amount1;
	  $amount1=0;
	 }
   }
  $payid = sqlInsert("INSERT INTO payments ( " .
    "pid, encounter, dtime, user, method, source, amount1, amount2 " .
    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)", array($patient_id,$encounter,$timestamp,$_SESSION['authUser'],$method,$source,$amount1,$amount2) );
  return $payid;
}

// We use this to put dashes, colons, etc. back into a timestamp.
//
function decorateString($fmt, $str) {
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
function calcTaxes($row, $amount) {
  $total = 0;
  if (empty($row['taxrates'])) return $total;
  $arates = explode(':', $row['taxrates']);
  if (empty($arates)) return $total;
  foreach ($arates as $value) {
    if (empty($value)) continue;
    $trow = sqlQuery("SELECT option_value FROM list_options WHERE " .
      "list_id = 'taxrate' AND option_id = ? LIMIT 1", array($value) );
    if (empty($trow['option_value'])) {
      echo "<!-- Missing tax rate '".text($value)."'! -->\n";
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

if (!$INTEGRATED_AR) slInitialize();

// $patdata = getPatientData($pid, 'fname,lname,pubpid');

$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid,p.pid, i.copay " .
  "FROM patient_data AS p " .
  "LEFT OUTER JOIN insurance_data AS i ON " .
  "i.pid = p.pid AND i.type = 'primary' " .
  "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", array($pid) );

$alertmsg = ''; // anything here pops up in an alert box

// If the Save button was clicked...
if ($_POST['form_save']) {
  $form_pid = $_POST['form_pid'];
  $form_method = trim($_POST['form_method']);
  $form_source = trim($_POST['form_source']);
  $patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');
  $NameNew=$patdata['fname'] . " " .$patdata['lname']. " " .$patdata['mname'];

	if($_REQUEST['radio_type_of_payment']=='pre_payment')
	 {
		  $payment_id = idSqlStatement("insert into ar_session set "    .
			"payer_id = ?"       .
			", patient_id = ?"   .
			", user_id = ?"     . 
			", closed = ?"      .
			", reference = ?"   . 
			", check_date =  now() , deposit_date = now() "	.
			",  pay_total = ?"    . 
			", payment_type = 'patient'" .
			", description = ?"   .
			", adjustment_code = 'pre_payment'" .
			", post_to_date = now() " .
			", payment_method = ?",
			array(0,$form_pid,$_SESSION['authUserID'],0,$form_source,$_REQUEST['form_prepayment'],$NameNew,$form_method));
	
		 frontPayment($form_pid, 0, $form_method, $form_source, $_REQUEST['form_prepayment'], 0);//insertion to 'payments' table.
	 }
  
  if ($_POST['form_upay'] && $_REQUEST['radio_type_of_payment']!='pre_payment') {
    foreach ($_POST['form_upay'] as $enc => $payment) {
      if ($amount = 0 + $payment) {
	       $zero_enc=$enc;
	       if($_REQUEST['radio_type_of_payment']=='invoice_balance')
		    { 
			 ;
		    }
		   else
		    { 
			 if (!$enc) 
			  {
					$enc = calendar_arrived($form_pid);
			  }
		    }
//----------------------------------------------------------------------------------------------------
			//Fetching the existing code and modifier
			$ResultSearchNew = sqlStatement("SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key ".
				"WHERE code_types.ct_fee=1 AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
				array($form_pid,$enc));
			 if($RowSearch = sqlFetchArray($ResultSearchNew))
			  {
                                $Codetype=$RowSearch['code_type'];
				$Code=$RowSearch['code'];
				$Modifier=$RowSearch['modifier'];
			  }
			 else
			  {
                                $Codetype='';
				$Code='';
				$Modifier='';
			  }
//----------------------------------------------------------------------------------------------------
			if($_REQUEST['radio_type_of_payment']=='copay')//copay saving to ar_session and ar_activity tables
			 {
				$session_id=idSqlStatement("INSERT INTO ar_session (payer_id,user_id,reference,check_date,deposit_date,pay_total,".
				 " global_amount,payment_type,description,patient_id,payment_method,adjustment_code,post_to_date) ".
				 " VALUES ('0',?,?,now(),now(),?,'','patient','COPAY',?,?,'patient_payment',now())",
				 array($_SESSION['authId'],$form_source,$amount,$form_pid,$form_method));
				 
				  $insrt_id=idSqlStatement("INSERT INTO ar_activity (pid,encounter,code_type,code,modifier,payer_type,post_time,post_user,session_id,pay_amount,account_code)".
				   " VALUES (?,?,?,?,?,0,now(),?,?,?,'PCP')",
					 array($form_pid,$enc,$Codetype,$Code,$Modifier,$_SESSION['authId'],$session_id,$amount));
				   
				 frontPayment($form_pid, $enc, $form_method, $form_source, $amount, 0);//insertion to 'payments' table.
			 }
			if($_REQUEST['radio_type_of_payment']=='invoice_balance' || $_REQUEST['radio_type_of_payment']=='cash')
			 {				//Payment by patient after insurance paid, cash patients similar to do not bill insurance in feesheet.
				  if($_REQUEST['radio_type_of_payment']=='cash')
				   {
				    sqlStatement("update form_encounter set last_level_closed=? where encounter=? and pid=? ",
							array(4,$enc,$form_pid));
				    sqlStatement("update billing set billed=? where encounter=? and pid=?",
							array(1,$enc,$form_pid));
				   }
				  $adjustment_code='patient_payment';
				  $payment_id = idSqlStatement("insert into ar_session set "    .
					"payer_id = ?"       .
					", patient_id = ?"   .
					", user_id = ?"     .
					", closed = ?"      .
					", reference = ?"   .
					", check_date =  now() , deposit_date = now() "	.
					",  pay_total = ?"    .
					", payment_type = 'patient'" .
					", description = ?"   .
					", adjustment_code = ?" .
					", post_to_date = now() " .
					", payment_method = ?",
					array(0,$form_pid,$_SESSION['authUserID'],0,$form_source,$amount,$NameNew,$adjustment_code,$form_method));

	//--------------------------------------------------------------------------------------------------------------------

        			frontPayment($form_pid, $enc, $form_method, $form_source, 0, $amount);//insertion to 'payments' table.

	//--------------------------------------------------------------------------------------------------------------------

					$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid =? and ".
						"encounter =? and payer_type=0 and account_code='PCP'",
						array($form_pid,$enc));//new fees screen copay gives account_code='PCP'
					$rowMoneyGot = sqlFetchArray($resMoneyGot);
					$Copay=$rowMoneyGot['PatientPay'];
					
	//--------------------------------------------------------------------------------------------------------------------

					//Looping the existing code and modifier
					$ResultSearchNew = sqlStatement("SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key WHERE code_types.ct_fee=1 ".
						"AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier",
					  array($form_pid,$enc));
					 while($RowSearch = sqlFetchArray($ResultSearchNew))
					  {
                                                $Codetype=$RowSearch['code_type'];
						$Code=$RowSearch['code'];
						$Modifier =$RowSearch['modifier'];
						$Fee =$RowSearch['fee'];
						
						$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where pid =? ".
							"and code_type=? and code=? and modifier=? and encounter =? and !(payer_type=0 and account_code='PCP')",
						array($form_pid,$Codetype,$Code,$Modifier,$enc));
						//new fees screen copay gives account_code='PCP'
						$rowMoneyGot = sqlFetchArray($resMoneyGot);
						$MoneyGot=$rowMoneyGot['MoneyGot'];

						$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where ".
						  "pid =? and code_type=? and code=? and modifier=? and encounter =?",
						  array($form_pid,$Codetype,$Code,$Modifier,$enc));
						$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
						$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];
						
						$Remainder=$Fee-$Copay-$MoneyGot-$MoneyAdjusted;
						$Copay=0;
						if(round($Remainder,2)!=0 && $amount!=0) 
						 {
						  if($amount-$Remainder >= 0)
						   {
								$insert_value=$Remainder;
								$amount=$amount-$Remainder;
						   }
						  else
						   {
								$insert_value=$amount;
								$amount=0;
						   }
						  sqlStatement("insert into ar_activity set "    .
							"pid = ?"       .
							", encounter = ?"     .
                                                        ", code_type = ?"      .
							", code = ?"      .
							", modifier = ?"      .
							", payer_type = ?"   .
							", post_time = now() " .
							", post_user = ?" .
							", session_id = ?"    .
							", pay_amount = ?" .
							", adj_amount = ?"    .
							", account_code = 'PP'",
							array($form_pid,$enc,$Codetype,$Code,$Modifier,0,$_SESSION['authUserID'],$payment_id,$insert_value,0));
						 }//if
					  }//while
					 if($amount!=0)//if any excess is there.
					  {
						  sqlStatement("insert into ar_activity set "    .
							"pid = ?"       .
							", encounter = ?"     .
                                                        ", code_type = ?"      .
							", code = ?"      .
							", modifier = ?"      .
							", payer_type = ?"   .
							", post_time = now() " .
							", post_user = ?" .
							", session_id = ?"    .
							", pay_amount = ?" .
							", adj_amount = ?"    .
							", account_code = 'PP'",
							array($form_pid,$enc,$Codetype,$Code,$Modifier,0,$_SESSION['authUserID'],$payment_id,$amount,0));
					  }

	//--------------------------------------------------------------------------------------------------------------------
			   }//invoice_balance
			}//if ($amount = 0 + $payment) 
		}//foreach
	 }//if ($_POST['form_upay'])
  }//if ($_POST['form_save'])

if ($_POST['form_save'] || $_REQUEST['receipt']) {

  if ($_REQUEST['receipt']) {
    $form_pid = $_GET['patient'];
    $timestamp = decorateString('....-..-.. ..:..:..', $_GET['time']);
  }

  // Get details for what we guess is the primary facility.
  $frow = sqlQuery("SELECT * FROM facility " .
    "ORDER BY billing_location DESC, accepts_assignment DESC, id LIMIT 1");

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
    "MAX(encounter) as encounter ".
    "FROM payments WHERE " .
    "pid = ? AND dtime = ?", array($form_pid,$timestamp) );

  // Create key for deleting, just in case.
	$ref_id = ($_REQUEST['radio_type_of_payment']=='copay') ? $session_id : $payment_id ;
  $payment_key = $form_pid . '.' . preg_replace('/[^0-9]/', '', $timestamp).'.'.$ref_id;

  // get facility from encounter
  $tmprow = sqlQuery("
    SELECT facility_id
    FROM form_encounter
    WHERE encounter = ?", array($payrow['encounter']) );
  $frow = sqlQuery("SELECT * FROM facility " .
    " WHERE id = ?", array($tmprow['facility_id']) );

  // Now proceed with printing the receipt.
?>

<title><?php echo xlt('Receipt for Payment'); ?></title>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // Process click on Print button.
 function printme() {
  var divstyle = document.getElementById('hideonprint').style;
  divstyle.display = 'none';
  window.print();
  // divstyle.display = 'block';
 }
 // Process click on Delete button.
 function deleteme() {
  dlgopen('deleter.php?payment=<?php echo $payment_key ?>', '_blank', 500, 450);
  return false;
 }
 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  window.close();
 }

 // Called to switch to the specified encounter having the specified DOS.
 // This also closes the popup window.
 function toencounter(enc, datestr, topframe) {
  topframe.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
  // Hard-coding of RBot for this purpose is awkward, but since this is a
  // pop-up and our openemr is left_nav, we have no good clue as to whether
  // the top frame is more appropriate.
  topframe.left_nav.forceDual();
  topframe.left_nav.setEncounter(datestr, enc, '');
  topframe.left_nav.setRadio('RBot', 'enc');
  topframe.left_nav.loadFrame('enc2', 'RBot', 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
<?php } else { ?>
  topframe.Title.location.href = 'encounter/encounter_title.php?set_encounter='   + enc;
  topframe.Main.location.href  = 'encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>
  window.close();
 }

</script>
</head>
<body bgcolor='#ffffff'>
<center>

<p><h2><?php echo xlt('Receipt for Payment'); ?></h2>

<p><?php echo text($frow['name']) ?>
<br><?php echo text($frow['street']) ?>
<br><?php echo text($frow['city'] . ', ' . $frow['state']) . ' ' .
    text($frow['postal_code']) ?>
<br><?php echo htmlentities($frow['phone']) ?>

<p>
<table border='0' cellspacing='8'>
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
  <td><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'payment_method'),$payrow['method']); ?></td>
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
  <td><?php echo xlt('Amount for Past Balance'); ?>:</td>
  <td><?php echo text(oeFormatMoney($payrow['amount2'])) ?></td>
 </tr>
 <tr>
  <td><?php echo xlt('Received By'); ?>:</td>
  <td><?php echo text($payrow['user']) ?></td>
 </tr>
</table>

<div id='hideonprint'>
<p>
<input type='button' value='<?php echo xla('Print'); ?>' onclick='printme()' />

<?php
  $todaysenc = todaysEncounterIf($pid);
  if ($todaysenc && $todaysenc != $encounter) {
    echo "&nbsp;<input type='button' " .
      "value='" . xla('Open Today`s Visit') . "' " .
      "onclick='toencounter($todaysenc,\"$today\",opener.top)' />\n";
  }
?>

<?php if (acl_check('admin', 'super')) { ?>
&nbsp;
<input type='button' value='<?php xl('Delete','e'); ?>' style='color:red' onclick='deleteme()' />
<?php } ?>

</div>
</center>
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

<style type="text/css">
 body    { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
#ajax_div_patient {
	position: absolute;
	z-index:10;
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
</style>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>



<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="../../library/js/jquery.easydrag.handler.beta2.js"></script> 
<script language='JavaScript'>
 var mypcc = '1';
</script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script language="javascript" type="text/javascript">
document.onclick=HideTheAjaxDivs;
</script>

<script type="text/javascript" src="../../library/topdialog.js"></script>

<script language="JavaScript">
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

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
  var f = document.forms[0];
  ok=-1;
  top.restoreSession();
  issue='no';
   if(((document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='check_payment' ||
   	  document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value=='bank_draft') &&
	   document.getElementById('check_number').value=='' ))
   {
    alert("<?php echo addslashes( xl('Please Fill the Check/Ref Number')) ?>");
	document.getElementById('check_number').focus();
	return false;
   }

  if(document.getElementById('radio_type_of_payment_self1').checked==false && document.getElementById('radio_type_of_payment_self2').checked==false   && document.getElementById('radio_type_of_payment1').checked==false && document.getElementById('radio_type_of_payment2').checked==false  && document.getElementById('radio_type_of_payment5').checked==false  && document.getElementById('radio_type_of_payment4').checked==false)
   {
	  alert("<?php echo addslashes( xl('Please Select Type Of Payment.')) ?>");
	  return false;
   }
  if(document.getElementById('radio_type_of_payment_self1').checked==true || document.getElementById('radio_type_of_payment_self2').checked==true || document.getElementById('radio_type_of_payment1').checked==true || document.getElementById('radio_type_of_payment5').checked==true)
   {
	 for (var i = 0; i < f.elements.length; ++i) 
	 {
	  var elem = f.elements[i];
	  var ename = elem.name;
	  if (ename.indexOf('form_upay[0') == 0) //Today is this text box.
	  {
	   if(elem.value*1>0)
	    {//A warning message, if the amount is posted with out encounter.
		 if(confirm("<?php echo addslashes( xl('Are you sure to post for today?')) ?>"))
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

  if(document.getElementById('radio_type_of_payment1').checked==true)//CO-PAY 
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
		 if(confirm("<?php echo addslashes( xl('You are posting against an old encounter?')) ?>"))
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
   }//Co Pay
 else if(document.getElementById('radio_type_of_payment2').checked==true)//Invoice Balance
  {
   if(document.getElementById('Today').innerHTML=='')
    {
	 for (var i = 0; i < f.elements.length; ++i) 
	  {
	   var elem = f.elements[i];
	   var ename = elem.name;
	   if (ename.indexOf('form_upay[') == 0) 
		{
		 if (elem.value*1 > 0)
		  {
			  alert("<?php echo addslashes( xl('Invoice Balance cannot be posted. No Encounter is created.')) ?>");
			  return false;
		 }
		 break;
	   }
	  }
	}
  }
 if(ok==-1)
  {
	 if(confirm("<?php echo addslashes( xl('Would you like to save?')) ?>"))
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
	 var f = document.forms[0];
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
 
  document.getElementById('table_display').width="635px";
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
</script>

</head>

<body class="body_top" onunload='imclosing()' onLoad="cursor_pointer();">
<center>

<form method='post' action='front_payment.php<?php if ($payid) echo "?payid=$payid"; ?>'
 onsubmit='return validate();'>
<input type='hidden' name='form_pid' value='<?php echo attr($pid) ?>' />


<table border='0' cellspacing='0' cellpadding="0">

 <tr height="10">
 	<td colspan="3">&nbsp;</td>
 </tr>

 <tr>
  <td colspan='3' align='center' class='text' >
   <b><?php echo htmlspecialchars(xl('Accept Payment for'), ENT_QUOTES); ?>&nbsp;:&nbsp;&nbsp;<?php echo htmlspecialchars($patdata['fname'], ENT_QUOTES) . " " .
    htmlspecialchars($patdata['lname'], ENT_QUOTES) . " " .htmlspecialchars($patdata['mname'], ENT_QUOTES). " (" . htmlspecialchars($patdata['pid'], ENT_QUOTES) . ")" ?></b>
	<?php $NameNew=$patdata['fname'] . " " .$patdata['lname']. " " .$patdata['mname'];?>
  </td>
 </tr>

 <tr height="15"><td colspan='3'></td></tr>


 <tr>
  <td class='text' >
   <?php echo xlt('Payment Method'); ?>:
  </td>
  <td colspan='2' >
  <select name="form_method" id="form_method"  class="text" onChange='CheckVisible("yes")'>
  <?php
  $query1112 = "SELECT * FROM list_options where list_id=?  ORDER BY seq, title ";
  $bres1112 = sqlStatement($query1112,array('payment_method'));
  while ($brow1112 = sqlFetchArray($bres1112)) 
   {
  	if($brow1112['option_id']=='electronic' || $brow1112['option_id']=='bank_draft')
	 continue;
	echo "<option value='".htmlspecialchars($brow1112['option_id'], ENT_QUOTES)."'>".htmlspecialchars(xl_list_label($brow1112['title']), ENT_QUOTES)."</option>";
   }
  ?>
  </select>
  </td>
 </tr>
 
 <tr height="5"><td colspan='3'></td></tr>

 <tr>
  <td class='text' >
   <?php echo xla('Check/Ref Number'); ?>:
  </td>
  <td colspan='2' ><div id="ajax_div_patient" style="display:none;"></div>
   <input type='text'  id="check_number" name='form_source' style="width:120px" value='<?php echo htmlspecialchars($payrow['source'], ENT_QUOTES); ?>'>
  </td>
 </tr>
 <tr height="5"><td colspan='3'></td></tr>

 <tr>
  <td class='text' valign="middle" >
   <?php echo htmlspecialchars(xl('Patient Coverage'), ENT_QUOTES); ?>:
  </td>
  <td class='text' colspan="2" ><input type="radio" name="radio_type_of_coverage" id="radio_type_of_coverage1" value="self" onClick="make_visible_radio();make_self();"/><?php echo htmlspecialchars(xl('Self'), ENT_QUOTES); ?><input type="radio" name="radio_type_of_coverage" id="radio_type_of_coverag2" value="insurance"  checked="checked" onClick="make_hide_radio();make_insurance();"/><?php echo htmlspecialchars(xl('Insurance'), ENT_QUOTES); ?>  </td>
 </tr>

 <tr height="5"><td colspan='3'></td></tr>

 <tr id="tr_radio1" style="display:none"><!-- For radio Insurance -->
  <td class='text' valign="top"  >
   <?php echo htmlspecialchars(xl('Payment against'), ENT_QUOTES); ?>:
  </td>
  <td class='text' colspan="2" ><input type="radio" name="radio_type_of_payment" id="radio_type_of_payment_self1" value="cash" onClick="make_visible_row();make_it_hide_enc_pay();cursor_pointer();"/><?php echo htmlspecialchars(xl('Encounter Payment'), ENT_QUOTES); ?></td>
 </tr>
 <tr id="tr_radio2"><!-- For radio self -->
  <td class='text' valign="top" >
   <?php echo htmlspecialchars(xl('Payment against'), ENT_QUOTES); ?>:
  </td>
  <td class='text' colspan="2" ><input type="radio" name="radio_type_of_payment" id="radio_type_of_payment1" value="copay" checked="checked" onClick="make_visible_row();cursor_pointer();"/><?php echo htmlspecialchars(xl('Co Pay'), ENT_QUOTES); ?><input type="radio" name="radio_type_of_payment" id="radio_type_of_payment2" value="invoice_balance"  onClick="make_visible_row();"/><?php echo htmlspecialchars(xl('Invoice Balance'), ENT_QUOTES); ?><br/><input type="radio" name="radio_type_of_payment" id="radio_type_of_payment4" value="pre_payment" onClick="make_hide_row();"/><?php echo htmlspecialchars(xl('Pre Pay'), ENT_QUOTES); ?></td>
 </tr>

 <tr height="15"><td colspan='3'></td></tr>

</table>
<table width="200" border="0" cellspacing="0" cellpadding="0" id="table_display_prepayment" style="display:none">
  <tr>
    <td class='detail'><?php echo htmlspecialchars(xl('Pre Payment'), ENT_QUOTES); ?></td>
    <td><input type='text' name='form_prepayment' style='width:100px' /></td>
  </tr>
</table>

<table border='0' id="table_display" cellpadding='0' cellspacing='0' width='635'>
 <tr bgcolor="#cccccc" id="tr_head">
  <td class="dehead" width="70">
   <?php echo htmlspecialchars( xl('DOS'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" width="65">
   <?php echo htmlspecialchars( xl('Encounter'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="80" id="td_head_total_charge" >
   <?php echo htmlspecialchars( xl('Total Charge'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="70" id="td_head_rep_doc" style='display:none'>
   <?php echo htmlspecialchars( xl('Report/ Form'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="200" id="td_head_description" style='display:none'>
   <?php echo htmlspecialchars( xl('Description'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="80" id="td_head_insurance_payment" >
   <?php echo htmlspecialchars( xl('Insurance Payment'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="80" id="td_head_patient_payment" >
   <?php echo htmlspecialchars( xl('Patient Payment'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="55" id="td_head_patient_co_pay" >
   <?php echo htmlspecialchars( xl('Co Pay Paid'), ENT_QUOTES) ?>
  </td>
	<td class="dehead" align="center" width="55" id="td_head_co_pay" >
   <?php echo htmlspecialchars( xl('Required Co Pay'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="80" id="td_head_insurance_balance" >
   <?php echo htmlspecialchars( xl('Insurance Balance'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="80" id="td_head_patient_balance" >
   <?php echo htmlspecialchars( xl('Patient Balance'), ENT_QUOTES) ?>
  </td>
  <td class="dehead" align="center" width="50">
   <?php echo htmlspecialchars( xl('Paying'), ENT_QUOTES) ?>
  </td>
 </tr>

<?php
  $encs = array();

  // Get the unbilled service charges and payments by encounter for this patient.
  //
  $query = "SELECT fe.encounter, b.code_type, b.code, b.modifier, b.fee, " .
    "LEFT(fe.date, 10) AS encdate ,fe.last_level_closed " .
    "FROM  form_encounter AS fe left join billing AS b  on " .
    "b.pid = ? AND b.activity = 1  AND " .//AND b.billed = 0
    "b.code_type != 'TAX' AND b.fee != 0 " .
    "AND fe.pid = b.pid AND fe.encounter = b.encounter " .
	"where fe.pid = ? " .
    "ORDER BY b.encounter";
  $bres = sqlStatement($query,array($pid,$pid));
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
      $encs[$key]['charges']  += $brow['fee'];
      // Add taxes.
      $sql_array=array();
      $query = "SELECT taxrates FROM codes WHERE " .
        "code_type = ? AND " .
        "code = ? AND ";
      array_push($sql_array,$code_types[$brow['code_type']]['id'],$brow['code']);
      if ($brow['modifier']) {
        $query .= "modifier = ?";
        array_push($sql_array,$brow['modifier']);
      } else {
        $query .= "(modifier IS NULL OR modifier = '')";
      }
      $query .= " LIMIT 1";
      $trow = sqlQuery($query,$sql_array);
      $encs[$key]['charges'] += calcTaxes($trow, $brow['fee']);
    }
  }

  // Do the same for unbilled product sales.
  //
  $query = "SELECT fe.encounter, s.drug_id, s.fee, " .
    "LEFT(fe.date, 10) AS encdate,fe.last_level_closed " .
    "FROM form_encounter AS fe left join drug_sales AS s " .
    "on s.pid = ? AND s.fee != 0 " .//AND s.billed = 0 
    "AND fe.pid = s.pid AND fe.encounter = s.encounter " .
	"where fe.pid = ? " .
    "ORDER BY s.encounter";

  $dres = sqlStatement($query,array($pid,$pid));
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
      "ORDER BY selector LIMIT 1", array($drow['drug_id']) );
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
  if (! $gottoday) {
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
    $inscopay = getCopay($pid, $dispdate);
	$patcopay = getPatientCopay($pid, $enc);
//Insurance Payment
//-----------------
	$drow = sqlQuery("SELECT  SUM(pay_amount) AS payments, " .
	  "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " .
      "pid = ? and encounter = ? and " .
      "payer_type != 0 and account_code!='PCP' ",
			array($pid,$enc));
	$dpayment=$drow['payments'];
	$dadjustment=$drow['adjustments'];
//Patient Payment
//---------------
	$drow = sqlQuery("SELECT  SUM(pay_amount) AS payments, " .
	  "SUM(adj_amount) AS adjustments  FROM ar_activity WHERE " .
      "pid = ? and encounter = ? and " .
      "payer_type = 0 and account_code!='PCP' ",
			array($pid,$enc));
	$dpayment_pat=$drow['payments'];

 //------------------------------------------------------------------------------------
 //NumberOfInsurance
 	$ResultNumberOfInsurance = sqlStatement("SELECT COUNT( DISTINCT TYPE ) NumberOfInsurance FROM insurance_data
			where pid = ? and provider>0 ",array($pid));
	$RowNumberOfInsurance = sqlFetchArray($ResultNumberOfInsurance);
	$NumberOfInsurance=$RowNumberOfInsurance['NumberOfInsurance']*1;
 //------------------------------------------------------------------------------------
	$duept=0;
	if((($NumberOfInsurance==0 || $value['last_level_closed']==4 || $NumberOfInsurance== $value['last_level_closed'])))
	 {//Patient balance
	  $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
	  "pid = ? and encounter = ? AND activity = 1",array($pid,$enc));
	  $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
	  "pid = ? and encounter = ? ",array($pid,$enc));
	  $drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " .
	  "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
	  "pid = ? and encounter = ? ",array($pid,$enc));
	  $duept= $brow['amount'] + $srow['amount'] - $drow['payments'] - $drow['adjustments'];
	 }
    echoLine("form_upay[$enc]", $dispdate, $value['charges'],
      $dpayment_pat, ($dpayment + $dadjustment), $duept,$enc,$inscopay,$patcopay);
  }


  // Now list previously billed visits.

  if ($INTEGRATED_AR) {

 } // end $INTEGRATED_AR
  else {
    // Query for all open invoices.
    $query = "SELECT ar.id, ar.invnumber, ar.amount, ar.paid, " .
      "ar.intnotes, ar.notes, ar.shipvia, " .
      "(SELECT SUM(invoice.sellprice * invoice.qty) FROM invoice WHERE " .
      "invoice.trans_id = ar.id AND invoice.sellprice > 0) AS charges, " .
      "(SELECT SUM(invoice.sellprice * invoice.qty) FROM invoice WHERE " .
      "invoice.trans_id = ar.id AND invoice.sellprice < 0) AS adjustments, " .
      "(SELECT SUM(acc_trans.amount) FROM acc_trans WHERE " .
      "acc_trans.trans_id = ar.id AND acc_trans.chart_id = ? " .
      "AND acc_trans.source NOT LIKE 'Ins%') AS ptpayments " .
      "FROM ar WHERE ar.invnumber LIKE ? AND " .
      "ar.amount != ar.paid " .
      "ORDER BY ar.invnumber";
    $ires = SLQuery($query, array($chart_id_cash,$pid."%") );
    if ($sl_err) die($sl_err);
    $num_invoices = SLRowCount($ires);

    for ($ix = 0; $ix < $num_invoices; ++$ix) {
      $irow = SLGetRow($ires, $ix);

      // Get encounter ID and date of service.
      list($patient_id, $enc) = explode(".", $irow['invnumber']);
      $tmp = sqlQuery("SELECT LEFT(date, 10) AS encdate FROM form_encounter " .
        "WHERE encounter = ?", array($enc) );
      $svcdate = $tmp['encdate'];

      // Compute $duncount as in sl_eob_search.php to determine if
      // this invoice is at patient responsibility.
      $duncount = substr_count(strtolower($irow['intnotes']), "statement sent");
      if (! $duncount) {
        $insgot = strtolower($irow['notes']);
        $inseobs = strtolower($irow['shipvia']);
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          if (strpos($insgot, $value) !== false &&
              strpos($inseobs, $value) === false)
            --$duncount;
        }
      }

      $inspaid = $irow['paid'] + $irow['ptpayments'] - $irow['adjustments'];
      $balance = $irow['amount'] - $irow['paid'];
      $duept  = ($duncount < 0) ? 0 : $balance;

      echoLine("form_bpay[$enc]", $svcdate, $irow['charges'],
        0 - $irow['ptpayments'], $inspaid, $duept);
    }
  } // end not $INTEGRATED_AR

  // Continue with display of the data entry form.
?>

 <tr bgcolor="#cccccc">
  <td class="dehead" id='td_total_1'></td>
  <td class="dehead" id='td_total_2'></td>
  <td class="dehead" id='td_total_3'></td>
  <td class="dehead" id='td_total_4'></td>
  <td class="dehead" id='td_total_5'></td>
  <td class="dehead" id='td_total_6'></td>
	<td class="dehead" id='td_total_7'></td>
  <td class="dehead" id='td_total_8'></td>
  <td class="dehead" align="right">
   <?php echo htmlspecialchars( xl('Total'), ENT_QUOTES);?>
  </td>
  <td class="dehead" align="right">
   <input type='text' name='form_paytotal'  value=''
    style='color:#00aa00;width:50px' readonly  />
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php echo htmlspecialchars( xl('Generate Invoice'), ENT_QUOTES);?>' /> &nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />

<input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo attr($pid);?>"/>
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
<input type='hidden' name='mode' id='mode' value='' />
</form>
<script language="JavaScript">
 calctotal();
</script>
</center>
</body>

<?php
}
if (!$INTEGRATED_AR) SLClose();
?>
</html>
