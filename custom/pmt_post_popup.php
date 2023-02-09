<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once('../interface/globals.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
$success = false;
$err = '';
$form_action="pmt_post_popup.php";
if(!isset($_GET['pid'])) $_GET['pid'] = '';
if(!isset($_GET['enc'])) $_GET['enc'] = '';
if(!isset($_GET['form_dt'])) $_GET['form_dt'] = '';
if(!isset($_GET['method'])) $_GET['method'] = '';
if(!isset($_GET['amt'])) $_GET['amt'] = '';
if(!isset($_GET['to_enc'])) $_GET['to_enc'] = '';
if(!isset($_GET['reference'])) $_GET['reference'] = '';

$pid = strip_tags($_GET['pid']);
$encounter = strip_tags($_GET['enc']);
$post_dt = strip_tags($_GET['form_dt']);
$method = strip_tags($_GET['method']);
$amt = strip_tags($_GET['amt']);
$to_enc = strip_tags($_GET['to_enc']);
$reference = strip_tags($_GET['reference']);
$override = false;
if(isset($_GET['override'])) $override = true;

$form_action = $GLOBALS['rootdir'].'/custom/pmt_post_popup.php?pid=' .
		$pid . "&enc=$encounter&form_dt=$form_dt&method=$method&amt=$amt" .
		"&to_enc=$to_enc&reference=$reference&override=true";

$patient = sqlQuery('SELECT * FROM patient_data WHERE pid=?', array($pid));
if($to_enc) {
	$desc = $patient{'fname'} . ' ' . $patient{'lname'};
} else {
	$desc = 'COPAY';
}

$sql = 'SELECT * FROM ar_session WHERE patient_id = ? AND pay_total = ? AND ' .
		'post_to_date = ? AND adjustment_code = "patient_payment"';
if($desc == 'COPAY') $sql .= ' AND description = "COPAY"';
$fres = sqlStatement($sql, array($pid, $amt, $post_dt));
$dup_test = sqlNumRows($fres);

if(!$dup_test && !$override) {
	$sql = 'INSERT INTO ar_session (payer_id, user_id, closed, reference, ' .
		'check_date, deposit_date, pay_total, created_time, payment_type, ' .
		'description, adjustment_code, post_to_date, patient_id, payment_method) ' .
		'VALUES (0, ?, 0, ?, ?, ?, ?, NOW(), "patient", ?, "patient_payment", ?, ' .
		'?, ?)';
	$binds = array($_SESSION['authUserID'], $reference, $post_dt, $post_dt, 
		$amt, $desc, $post_dt, $pid, $method);
	$ar_session_id = sqlInsert($sql, $binds);
	echo "Pmt ID: ($ar_session_id)<br>\n";
	$amount1 = $amount2 = 0;
	if($post_dt == date('Y-m-d')) {
		$amount1 = $amt;
	} else {
		$amount2 = $amt;
	}
	$sql = 'INSERT INTO payments (pid, dtime, encounter, user, method, ' .
		'source, amount1, amount2) VALUES (?, NOW(), ?, ?, ?, ?, ?)';
	$source = (substr($method,0,5) == 'check') ? '' : $reference;
	$binds = array($pid, $encounter, $_SESSION['authUser'], $method, $source,
		$amount1, $amount2)
	$pmt_id = sqlInsert($sql, $binds);

	if($desc == 'COPAY') {
	} else {
	}
}

exit;
?>

<html>
<head>
<title>Payment Posting Popup</title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript">

function set_pin(success) {
	if(!success) {
		return false;
	}
  if (opener.closed || ! opener.set_pin)
   alert('The destination form was closed; I cannot act on your selection.');
  else
   opener.set_pin(success);
  window.close();
  return false;
}

function pin_check(test) {
	var pin = document.forms[0].elements['pin'].value;
	if(pin == '') {
		alert("Please Enter a PIN!");
		return false;
	}
	if(test == '') {
		alert("Can NOT Verify, Internal Error");
		return false;
	}
	if(test == pin) {
		set_pin(true);
		return true;
	}
	alert("Incorrect PIN...");
	return false;
}

<?php if($test) { ?>
	window.close(); 
<?php } ?>

</script>
</head>

<body class="body_top" onload="" >
<form method='post' name='pmt_post_popup' action="<?php echo $form_action; ?>">
<center>
<table width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td><b>Possible Duplicates Found</b></td>
	</tr>
</table>


</center>
</form>
</body>
</html>
