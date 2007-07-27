<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");
?>
<html>
<head>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<?php
 $payment_methods = array(
  xl('Cash'),
  xl('Check'),
  xl('MC'),
  xl('VISA'),
  xl('AMEX'),
  xl('DISC'),
  xl('Other'));

 // $patdata = getPatientData($pid, 'fname,lname,pubpid');

 $patdata = sqlQuery("SELECT " .
   "p.fname, p.mname, p.lname, p.pubpid, i.copay " .
   "FROM patient_data AS p " .
   "LEFT OUTER JOIN insurance_data AS i ON " .
   "i.pid = p.pid AND i.type = 'primary' " .
   "WHERE p.pid = '$pid' ORDER BY i.date DESC LIMIT 1");

 $alertmsg = ''; // anything here pops up in an alert box

 // If the Save button was clicked...
 if ($_POST['form_save']) {
  $form_pid = $_POST['form_pid'];
  $payid = $_REQUEST['payid'];

  if ($payid) {
   sqlStatement("UPDATE payments SET " .
    "dtime = NOW(), " .
    "user = '"    . $_SESSION['authUser']  . "', " .
    "method = '"  . $_POST['form_method']  . "', " .
    "source = '"  . $_POST['form_source']  . "', " .
    "amount1 = '" . $_POST['form_amount1'] . "', " .
    "amount2 = '" . $_POST['form_amount2'] . "' " .
    "WHERE id = '$payid'");
  } else {
   $payid = sqlInsert("INSERT INTO payments ( " .
    "pid, dtime, user, method, source, amount1, amount2 " .
    ") VALUES ( " .
    "'$form_pid', " .
    "NOW(), " .
    "'" . $_SESSION['authUser']  . "', " .
    "'" . $_POST['form_method']  . "', " .
    "'" . $_POST['form_source']  . "', " .
    "'" . $_POST['form_amount1'] . "', " .
    "'" . $_POST['form_amount2'] . "' "  .
    ")");
  }
 }
?>

<?php
 if ($_POST['form_save'] || $_REQUEST['receipt']) {

  // Get details for what we guess is the primary facility.
  $frow = sqlQuery("SELECT * FROM facility " .
    "ORDER BY billing_location DESC, accepts_assignment DESC, id LIMIT 1");

  // Re-fetch info for this payment.
  $payrow = sqlQuery("SELECT * FROM payments WHERE id = '$payid'");

  // Get the patient's name and chart number.
  $patdata = getPatientData($payrow['pid'], 'fname,mname,lname,pubpid');

  // Now proceed with printing the receipt.
?>

<title><? xl('Receipt for Payment','e'); ?></title>
<body bgcolor='#ffffff'>
<center>

<p><h2><? xl('Receipt for Payment','e'); ?></h2>

<p><?php echo htmlentities($frow['name']) ?>
<br><?php echo htmlentities($frow['street']) ?>
<br><?php echo htmlentities($frow['city'] . ', ' . $frow['state']) . ' ' .
    $frow['postal_code'] ?>
<br><?php echo htmlentities($frow['phone']) ?>

<p>
<table border='0' cellspacing='8'>
 <tr>
  <td><? xl('Date','e'); ?>:</td>
  <td><?php echo date('Y-m-d', strtotime($payrow['dtime'])) ?></td>
 </tr>
 <tr>
  <td><? xl('Patient','e'); ?>:</td>
  <td><?php echo $patdata['fname'] . " " . $patdata['mname'] . " " .
       $patdata['lname'] . " (" . $patdata['pubpid'] . ")" ?></td>
 </tr>
 <tr>
  <td><? xl('Paid Via','e'); ?>:</td>
  <td><?php echo $payrow['method'] ?></td>
 </tr>
 <tr>
  <td><? xl('Check/Ref Number','e'); ?>:</td>
  <td><?php echo $payrow['source'] ?></td>
 </tr>
 <tr>
  <td><? xl('Amount for This Visit','e'); ?>:</td>
  <td><?php echo $payrow['amount1'] ?></td>
 </tr>
 <tr>
  <td><? xl('Amount for Past Balance','e'); ?>:</td>
  <td><?php echo $payrow['amount2'] ?></td>
 </tr>
 <tr>
  <td><? xl('Received By','e'); ?>:</td>
  <td><?php echo $payrow['user'] ?></td>
 </tr>
</table>

</center>

<p>&nbsp;<br>
<a href='' class='link_submit' onclick='window.print();return false;'><? xl('Print','e'); ?></a>

</body>

<?php
 } else {

  // This is the case where we display the form for data entry.

  $payrow = array('amount1' => $patdata['copay']);
  if ($_REQUEST['payid']) {
   $payrow = sqlQuery("SELECT * FROM payments WHERE id = '" .
    $_REQUEST['payid'] . "'");
  }
  // Continue with display of the data entry form.
?>
<title><? xl('Record Payment','e'); ?></title>

<style>
</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

// See main_screen.php for an explanation of this.
function restoreSession() {
 document.cookie = '<?php echo session_name() . '=' . session_id(); ?>; path=/';
 return true;
}

</script>

</head>

<body <?echo $top_bg_line;?> leftmargin='0' topmargin='0' marginwidth='0'
 marginheight='0' onunload='imclosing()'>

<form method='post' action='front_payment.php<?php if ($payid) echo "?payid=$payid"; ?>'
 onsubmit='return top.restoreSession()'>
<input type='hidden' name='form_pid' value='<?php echo $pid ?>' />

<center>
<p>
<table border='0' cellspacing='8'>

 <tr>
  <td colspan='2' align='center'>
   &nbsp;<br>
   <b><? xl('Accept Payment for ','e'); ?><?php echo $patdata['fname'] . " " .
    $patdata['lname'] . " (" . $patdata['pubpid'] . ")" ?></b>
    <br>&nbsp;
  </td>
 </tr>

 <tr>
  <td>
   <? xl('Payment Method','e'); ?>:
  </td>
  <td>
   <select name='form_method'>
<?
 foreach ($payment_methods as $value) {
  echo "    <option value='$value'";
  if ($value == $payrow['method']) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td>
   <? xl('Check/Reference Number','e'); ?>:
  </td>
  <td>
   <input type='text' name='form_source' size='10' value='<?php echo $payrow['source'] ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <? xl('Amount for Todays Visit','e'); ?>:
  </td>
  <td>
   <input type='text' name='form_amount1' size='10' value='<?php echo $payrow['amount1'] ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <? xl('Amount for Prior Balance','e'); ?>:
  </td>
  <td>
   <input type='text' name='form_amount2' size='10' value='<?php echo $payrow['amount2'] ?>'>
  </td>
 </tr>

 <tr>
  <td colspan='2' align='center'>
   &nbsp;<br>
   <input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' /> &nbsp;
   <input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />
  </td>
 </tr>

</table>
</center>

</form>

</body>

<?php
 }
?>
</html>
