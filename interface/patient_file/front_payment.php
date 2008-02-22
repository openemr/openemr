<?php
// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/invoice_summary.inc.php");
?>
<html>
<head>
<? html_header_show();?>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<?php

// Format dollars for display.
//
function bucks($amount) {
  if ($amount) {
    $amount = sprintf("%.2f", $amount);
    if ($amount != 0.00) return $amount;
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
  return -1;
}

// Display a row of data for an encounter.
//
function echoLine($iname, $date, $charges, $ptpaid, $inspaid, $duept) {
  $balance = bucks($charges - $ptpaid - $inspaid);
  $getfrompt = ($duept > 0) ? $duept : 0;
  echo " <tr>\n";
  echo "  <td class='detail'>$date</td>\n";
  echo "  <td class='detail' align='right'>" . bucks($charges) . "</td>\n";
  echo "  <td class='detail' align='right'>" . bucks($ptpaid) . "</td>\n";
  echo "  <td class='detail' align='right'>" . bucks($inspaid) . "</td>\n";
  echo "  <td class='detail' align='right'>$balance</td>\n";
  echo "  <td class='detail' align='right'>" . bucks($duept) . "</td>\n";
  echo "  <td class='detail' align='right'><input type='text' name='$iname' " .
    "size='6' value='" . bucks($getfrompt) . "' onchange='calctotal()' " .
    "onkeyup='calctotal()' /></td>\n";
  echo " </tr>\n";
}

// Post a payment to the payments table.
//
function frontPayment($patient_id, $encounter, $method, $source, $amount1, $amount2) {
  global $timestamp;
  $payid = sqlInsert("INSERT INTO payments ( " .
    "pid, encounter, dtime, user, method, source, amount1, amount2 " .
    ") VALUES ( " .
    "'$patient_id', " .
    "'$encounter', " .
    "'$timestamp', " .
    "'" . $_SESSION['authUser']  . "', " .
    "'$method', " .
    "'$source', " .
    "'$amount1', " .
    "'$amount2' " .
    ")");
  return $payid;
}

// Get the patient's encounter ID for today, creating it if there is none.
//
function todaysEncounter($patient_id) {
  global $today;

  $tmprow = sqlQuery("SELECT encounter FROM form_encounter WHERE " .
    "pid = '$patient_id' AND date = '$today 00:00:00'");

  if (!empty($tmprow['encounter'])) return $tmprow['encounter'];

  $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users " .
    "WHERE id = '" . $_SESSION["authUserID"] . "'");
  $username = $tmprow['username'];
  $facility = $tmprow['facility'];
  $facility_id = $tmprow['facility_id'];
  $conn = $GLOBALS['adodb']['db'];
  $encounter = $conn->GenID("sequences");
  addForm($encounter, "New Patient Encounter",
    sqlInsert("INSERT INTO form_encounter SET " .
      "date = '$today', " .
      "onset_date = '$today', " .
      "reason = 'Please indicate visit reason', " .
      "facility = '$facility', " .
      "facility_id = '$facility_id', " .
      "pid = '$patient_id', " .
      "encounter = '$encounter'"
    ),
    "newpatient", $patient_id, "1", "NOW()", $username
  );
  return $encounter;
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

$payment_methods = array(
  xl('Cash'),
  xl('Check'),
  xl('MC'),
  xl('VISA'),
  xl('AMEX'),
  xl('DISC'),
  xl('Other'));

$now = time();
$today = date('Y-m-d', $now);
$timestamp = date('Y-m-d H:i:s', $now);

slInitialize();

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
  $form_method = trim($_POST['form_method']);
  $form_source = trim($_POST['form_source']);

  foreach ($_POST['form_upay'] as $encounter => $payment) {
    if ($amount = 0 + $payment) {
      if (!$encounter) $encounter = todaysEncounter($form_pid);
      addBilling($encounter, 'COPAY', sprintf('%.2f', $amount),
        $form_method, $form_pid, 1, $_SESSION["authUserID"],
        '', 1, 0 - $amount, '', '');
      frontPayment($form_pid, $encounter, $form_method, $form_source, $amount, 0);
    }
  }

  foreach ($_POST['form_bpay'] as $encounter => $payment) {
    if ($amount = 0 + $payment) {
      $thissrc = 'Pt/';
      if ($form_method) {
        $thissrc .= $form_method;
        if ($form_source) $thissrc .= " $form_source";
      }
      $trans_id = SLQueryValue("SELECT id FROM ar WHERE " .
        "ar.invnumber = '$form_pid.$encounter' LIMIT 1");
      if (! $trans_id) die("Cannot find invoice '$form_pid.$encounter'!");
      slPostPayment($trans_id, $amount, date('Y-m-d'), $thissrc,
        '', 0, 0);
      frontPayment($form_pid, $encounter, $form_method, $form_source, 0, $amount);
    }
  }
}
?>

<?php
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
    "MAX(user) AS user " .
    "FROM payments WHERE " .
    "pid = '$form_pid' AND dtime = '$timestamp'");

  // Create key for deleting, just in case.
  $payment_key = $form_pid . '.' . preg_replace('/[^0-9]/', '', $timestamp);

  // Now proceed with printing the receipt.
?>

<title><? xl('Receipt for Payment','e'); ?></title>
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

</script>
</head>
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

<div id='hideonprint'>
<p>
<input type='button' value='<?php xl('Print','e'); ?>' onclick='printme()' />

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
<title><? xl('Record Payment','e'); ?></title>

<style type="text/css">
 body    { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

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

</script>

</head>

<body <?echo $top_bg_line;?> leftmargin='0' topmargin='0' marginwidth='0'
 marginheight='0' onunload='imclosing()'>

<form method='post' action='front_payment.php<?php if ($payid) echo "?payid=$payid"; ?>'
 onsubmit='return top.restoreSession()'>
<input type='hidden' name='form_pid' value='<?php echo $pid ?>' />

<center>

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
<?php
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

</table>

<table border='0' cellpadding='2' cellspacing='0' width='98%'>
 <tr bgcolor="#cccccc">
  <td class="dehead">
   <?php xl('DOS','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Charges','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Pt Paid','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Insurance','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Balance','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Due Pt','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Paying','e')?>
  </td>
 </tr>

<?php
  $gottoday = false;

  // Show the unbilled charges and payments by encounter for this patient.
  //
  $query = "SELECT b.encounter, " .
    "SUM(IF(b.code_type = 'COPAY', 0, b.fee)) AS charges, " .
    "SUM(IF(b.code_type = 'COPAY', 0 - b.fee, 0)) AS payments, " .
    "LEFT(fe.date, 10) AS encdate " .
    "FROM billing AS b, form_encounter AS fe " .
    "WHERE b.pid = '$pid' AND b.activity = 1 AND b.billed = 0 " .
    "AND fe.pid = b.pid AND fe.encounter = b.encounter " .
    "GROUP BY b.encounter ORDER BY b.encounter";
  $bres = sqlStatement($query);
  //
  while ($brow = sqlFetchArray($bres)) {
    $dispdate = $brow['encdate'];
    if (strcmp($dispdate, $today) == 0) {
      $dispdate = 'Today';
      $gottoday = true;
    }
    $inscopay = getCopay($pid, $brow['encdate']);
    $encounter = $brow['encounter'];
    $balance = bucks($brow['charges'] - $brow['payments']);
    $duept = (($inscopay >= 0) ? $inscopay : $brow['charges']) - $brow['payments'];
    echoLine("form_upay[$encounter]", $dispdate, $brow['charges'],
      $brow['payments'], 0, $duept);
  }

  // If no billing was entered yet for today, then generate a line for
  // entering today's co-pay.
  //
  if (! $gottoday) {
    $inscopay = getCopay($pid, $today);
    $duept = ($inscopay >= 0) ? $inscopay : 0;
    echoLine("form_upay[0]", "Today", 0, 0, 0, $duept);
  }

  // Query for all open invoices.
  $query = "SELECT ar.id, ar.invnumber, ar.amount, ar.paid, " .
    "ar.intnotes, ar.notes, ar.shipvia, " .
    "(SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
    "invoice.trans_id = ar.id AND invoice.fxsellprice > 0) AS charges, " .
    "(SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
    "invoice.trans_id = ar.id AND invoice.fxsellprice < 0) AS adjustments, " .
    "(SELECT SUM(acc_trans.amount) FROM acc_trans WHERE " .
    "acc_trans.trans_id = ar.id AND acc_trans.chart_id = $chart_id_cash " .
    "AND acc_trans.source NOT LIKE 'Ins%') AS ptpayments " .
    "FROM ar WHERE ar.invnumber LIKE '$pid.%' AND " .
    "ar.amount != ar.paid " .
    "ORDER BY ar.invnumber";
  $ires = SLQuery($query);
  if ($sl_err) die($sl_err);
  $num_invoices = SLRowCount($ires);

  for ($ix = 0; $ix < $num_invoices; ++$ix) {
    $irow = SLGetRow($ires, $ix);

    // Get encounter ID and date of service.
    list($patient_id, $encounter) = explode(".", $irow['invnumber']);
    $tmp = sqlQuery("SELECT LEFT(date, 10) AS encdate FROM form_encounter " .
      "WHERE encounter = '$encounter'");
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

    echoLine("form_bpay[$encounter]", $svcdate, $irow['charges'],
      0 - $irow['ptpayments'], $inspaid, $duept);
  }

  // Continue with display of the data entry form.
?>

 <tr bgcolor="#cccccc">
  <td class="dehead" colspan="6">
   <?php xl('Total Amount Paid','e')?>
  </td>
  <td class="dehead" align="right">
   <input type='text' name='form_paytotal' size='6' value=''
    style='color:#00aa00' readonly />
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' /> &nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />

</center>
</form>
<script language="JavaScript">
 calctotal();
</script>
</body>

<?php
}
SLClose();
?>
</html>
