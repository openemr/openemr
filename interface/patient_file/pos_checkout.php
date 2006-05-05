<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This module supports a popup window to handle patient checkout
 // as a point-of-sale transaction.  Support for in-house drug sales
 // is included.

 // Important notes about system design:
 //
 // (1) Drug sales may or may not be associated with an encounter;
 //     they are if they are paid for concurrently with an encounter.
 // (2) Drug sales without an encounter will have 20YYMMDD, possibly
 //     with a suffix, as the encounter-number portion of their invoice
 //     number.
 // (3) Payments are saved in SL only, don't mess with the billing table.
 //     See library/classes/WSClaim.class.php for posting code.
 // (4) On checkout, the billing table entries are marked as billed and
 //     so become unavailable for further billing.
 // (5) On checkout, drug_sales entries are marked as billed by having
 //     an invoice number assigned.
 // (6) Receipt printing must be a separate operation from payment,
 //     and repeatable, therefore driven from the SL database and
 //     mirroring the contents of the invoice.

 require_once("../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/sql-ledger.inc");
 require_once("$srcdir/freeb/xmlrpc.inc");
 require_once("$srcdir/freeb/xmlrpcs.inc");

 // Get the patient's name and chart number.
 $patdata = getPatientData($pid, 'fname,mname,lname,pubpid,street,city,state,postal_code');

//////////////////////////////////////////////////////////////////////
// The following functions are inline here temporarily, and should be
// moved to an includable module for common use.  In particular
// WSClaim.class.php should be rewritten to use them.
//////////////////////////////////////////////////////////////////////

// Initialize the array of invoice information for posting to the
// accounting system.
//
function invoice_initialize(& $invoice_info, $patient_id, $provider_id,
	$payer_id = 0, $encounter = 0)
{
	$db = $GLOBALS['adodb']['db'];

	// Get foreign ID (customer) for patient.
	$sql = "SELECT foreign_id from integration_mapping as im " .
		"LEFT JOIN patient_data as pd on im.local_id=pd.id " .
		"where pd.pid = '" .
		$patient_id .
		"' and im.local_table='patient_data' and im.foreign_table='customer'";
	$result = $db->Execute($sql);
	if($result && !$result->EOF) {
		$foreign_patient_id = $result->fields['foreign_id'];
	}
	else {
		return "Patient '" . $patient_id . "' has not yet been posted to the accounting system.";
	}

	// Get foreign ID (salesman) for provider.
	$sql = "SELECT foreign_id from integration_mapping WHERE " .
		"local_id = $provider_id AND local_table='users' and foreign_table='salesman'";
	$result = $db->Execute($sql);
	if($result && !$result->EOF) {
		$foreign_provider_id = $result->fields['foreign_id'];
	}
	else {
		return "Provider '" . $provider_id . "' has not yet been posted to the accounting system.";
	}

	// Get foreign ID (customer) for insurance payer.
	if ($payer_id && ! $GLOBALS['insurance_companies_are_not_customers']) {
		$sql = "SELECT foreign_id from integration_mapping WHERE " .
			"local_id = $payer_id AND local_table = 'insurance_companies' AND foreign_table='customer'";
		$result = $db->Execute($sql);
		if($result && !$result->EOF) {
			$foreign_payer_id = $result->fields['foreign_id'];
		}
		else {
			return "Payer '" . $payer_id . "' has not yet been posted to the accounting system.";
		}
	} else {
		$foreign_payer_id = $payer_id;
	}

	// Create invoice notes for the new invoice that list the patient's
	// insurance plans.  This is so that when payments are posted, the user
	// can easily see if a secondary claim needs to be submitted.
	//
	$insnotes = "";
	$insno = 0;
	foreach (array("primary", "secondary", "tertiary") as $instype) {
		++$insno;
		$sql = "SELECT insurance_companies.name " .
			"FROM insurance_data, insurance_companies WHERE " .
			"insurance_data.pid = $patient_id AND " .
			"insurance_data.type = '$instype' AND " .
			"insurance_companies.id = insurance_data.provider " .
			"LIMIT 1";
		$result = $db->Execute($sql);
		if ($result && !$result->EOF && $result->fields['name']) {
			if ($insnotes) $insnotes .= "\n";
			$insnotes .= "Ins$insno: " . $result->fields['name'];
		}
	}
	$invoice_info['notes'] = $insnotes;

	$invoice_info['salesman']   = $foreign_provider_id;
	$invoice_info['customerid'] = $foreign_patient_id;
	$invoice_info['payer_id']   = $foreign_payer_id;
	$invoice_info['invoicenumber'] = $patient_id . "." . $encounter;
	$invoice_info['dosdate'] = date("m-d-Y");
	$invoice_info['items'] = array();
	$invoice_info['total'] = '0.00';

	return '';
}

function invoice_add_line_item(& $invoice_info, $code_type, $code,
	$code_text, $amount)
{
	$tii = array();
	$tii['maincode'] = $code;
	$tii['itemtext'] = "$code_type:$code $code_text";
	$tii['qty'] = 1;
	$tii['price'] = sprintf("%01.2f", $amount);
	$tii['glaccountid'] = $GLOBALS['oer_config']['ws_accounting']['income_acct'];
	$invoice_info['total'] = sprintf("%01.2f", $invoice_info['total'] + $tii['price']);
	$invoice_info['items'][] = $tii;
	return '';
}

function invoice_post(& $invoice_info)
{
	$function['ezybiz.add_invoice'] = array(new xmlrpcval($invoice_info, "struct"));

	list($name, $var) = each($function);
	$f = new xmlrpcmsg($name, $var);

	$c = new xmlrpc_client($GLOBALS['oer_config']['ws_accounting']['url'],
		$GLOBALS['oer_config']['ws_accounting']['server'],
		$GLOBALS['oer_config']['ws_accounting']['port']);

	$c->setCredentials($GLOBALS['oer_config']['ws_accounting']['username'],
		$GLOBALS['oer_config']['ws_accounting']['password']);

	$r = $c->send($f);
	if (!$r) return "XMLRPC send failed";

	// We are not doing anything with the return value yet... should we?
	$tv = $r->value();
	if (is_object($tv)) {
		$value = $tv->getval();
	}
	else {
		$value = null;	
	}

	if ($r->faultCode()) {
		return "Fault: Code: " . $r->faultCode() . " Reason '" . $r->faultString() . "'";
	}

	return '';
}

/////////////////// End of invoice posting functions /////////////////

 // Generate a receipt from the last-billed invoice for this patient.
 //
 function generate_receipt($patient_id) {
  global $sl_err, $sl_cash_acc, $css_header;

  // Get details for what we guess is the primary facility.
  $frow = sqlQuery("SELECT * FROM facility " .
    "ORDER BY billing_location DESC, accepts_assignment DESC, id LIMIT 1");

  $patdata = getPatientData($patient_id, 'fname,mname,lname,pubpid,street,city,state,postal_code');

  SLConnect();

  // Get the most recent invoice data into $arrow.
  //
  $arres = SLQuery("SELECT * FROM ar WHERE " .
    "invnumber LIKE '$patient_id.%' " .
    "ORDER BY id DESC LIMIT 1");
  if ($sl_err) die($sl_err);
  $arrow = SLGetRow($arres, 0);
  if (! $arrow) die(xl("This patient has no activity."));

  $trans_id = $arrow['id'];

  // Determine the date of service.  An 8-digit encounter number is
  // presumed to be a date of service imported during conversion or
  // associated with prescriptions only.  Otherwise look it up in the
  // form_encounter table.
  //
  $svcdate = "";
  list($trash, $encounter) = explode(".", $arrow['invnumber']);
  if (strlen($encounter) >= 8) {
    $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
      "-" . substr($encounter, 6, 2);
  }
  else if ($encounter) {
    $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
      "encounter = $encounter");
    $svcdate = substr($tmp['date'], 0, 10);
  }
?>
<html>
<head>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<title><? xl('Receipt for Payment','e'); ?></title>
</head>
<body <?echo $top_bg_line;?> leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>
<p><b><?php echo $frow['name'] ?>
<br><?php echo $frow['street'] ?>
<br><?php echo $frow['city'] . ', ' . $frow['state'] . ' ' . $frow['postal_code'] ?>
<br><?php echo $frow['phone'] ?>
<br>&nbsp;
<br><?php echo date('F j, Y') ?>
<br>&nbsp;
</b></p>
</center>
<p>
<?php echo $patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname'] ?>
<br><?php echo $patdata['street'] ?>
<br><?php echo $patdata['city'] . ', ' . $patdata['state'] . ' ' . $patdata['postal_code'] ?>
<br>&nbsp;
</p>
<center>
<table>
 <tr>
  <td><b>Date</b></td>
  <td><b>Description</b></td>
  <td align='right'><b>Amount</b></td>
 </tr>
<?php
 $charges = 0.00;

 // Request all line items with money belonging to the invoice.
 $inres = SLQuery("select * from invoice where trans_id = $trans_id and sellprice != 0");
 if ($sl_err) die($sl_err);

 for ($irow = 0; $irow < SLRowCount($inres); ++$irow) {
  $row = SLGetRow($inres, $irow);
  $amount = sprintf('%01.2f', $row['sellprice']);
  $charges += $amount;
  echo " <tr>\n";
  echo "  <td>$svcdate</td>\n";
  echo "  <td>" . $row['description'] . "</td>\n";
  echo "  <td align='right'>$amount</td>\n";
  echo " </tr>\n";
 }

 $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
 if ($sl_err) die($sl_err);
 if (! $chart_id_cash) die("There is no COA entry for cash account '$sl_cash_acc'");

 // Request all cash entries belonging to the invoice.
 $atres = SLQuery("select * from acc_trans where trans_id = $trans_id and chart_id = $chart_id_cash");
 if ($sl_err) die($sl_err);

 for ($irow = 0; $irow < SLRowCount($atres); ++$irow) {
  $row = SLGetRow($atres, $irow);
  $amount = sprintf('%01.2f', $row['amount']); // negative
  $charges += $amount;
  echo " <tr>\n";
  echo "  <td>" . $row['transdate'] . "</td>\n";
  echo "  <td>Payment " . $row['source'] . "</td>\n";
  echo "  <td align='right'>$amount</td>\n";
  echo " </tr>\n";
 }
?>
 <tr>
  <td colspan='3'>&nbsp;</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
  <td><b>Balance Due</b></td>
  <td align='right'><?php echo sprintf('%01.2f', $charges) ?></td>
 </tr>
</table>
</center>
<p>&nbsp;<a href='' onclick='window.print(); return false;'>Print</a></p>
</body>
</html>
<?php
  SLClose();
 } // end function

 // Function to output a line item for the input form.
 //
 $lino = 0;
 function write_form_line($code_type, $code, $id, $date, $description, $amount) {
  global $lino;
  $amount = sprintf("%01.2f", $amount);
  echo " <tr>\n";
  echo "  <td>$date";
  echo "<input type='hidden' name='line[$lino][code_type]' value='$code_type'>";
  echo "<input type='hidden' name='line[$lino][code]' value='$code'>";
  echo "<input type='hidden' name='line[$lino][id]' value='$id'>";
  echo "<input type='hidden' name='line[$lino][description]' value='$description'>";
  echo "</td>\n";
  echo "  <td>$description</td>";
  echo "  <td align='right'><input type='text' name='line[$lino][amount]' " .
       "value='$amount' size='6' maxlength='8' style='text-align:right'></td>\n";
  echo " </tr>\n";
  ++$lino;
 }

 $payment_methods = array(
  'Cash',
  'Check',
  'MC',
  'VISA',
  'AMEX',
  'DISC',
  'Other');

 $alertmsg = ''; // anything here pops up in an alert box

 // If the Save button was clicked...
 //
 if ($_POST['form_save']) {

  // On a save, do the following:
  // Flag drug_sales and billing items as billed.
  // Post the corresponding invoice with its payment(s) to sql-ledger
  // and be careful to use a unique invoice number.
  // Call the generate-receipt function.
  // Exit.

  $form_pid = $_POST['form_pid'];
  $form_encounter = $_POST['form_encounter'];

  // If there is no associated encounter (i.e. this invoice has only
  // prescriptions) then assign an encounter number of the current
  // date, with an optional suffix to ensure that it's unique.
  //
  if (! $form_encounter) {
   SLConnect();
   $form_encounter = date('Ymd');
   $tmp = '';
   while (SLQueryValue("select id from ar where " .
    "invnumber = '$form_pid.$form_encounter$tmp'")) {
    $tmp = $tmp ? $tmp + 1 : 1;
   }
   $form_encounter .= $tmp;
   SLClose();
  }

  // Initialize an array of invoice information for posting.
  //
  $invoice_info = array();
  $msg = invoice_initialize($invoice_info, $_POST['form_pid'],
   $_POST['form_provider'], $_POST['form_payer'], $form_encounter);
  if ($msg) die($msg);

  $form_amount = $_POST['form_amount'];
  $lines = $_POST['line'];

  for ($lino = 0; $lines[$lino]['code_type']; ++$lino) {
   $line = $lines[$lino];

   $code_type = $line['code_type'];
   $id        = $line['id'];
   $amount    = sprintf('%01.2f', trim($line['amount']));

   $msg = invoice_add_line_item($invoice_info, $code_type,
    $line['code'], $line['description'], $amount);
   if ($msg) die($msg);

   if ($code_type == 'MED') {
    $query = "update drug_sales SET fee = '$amount', " .
     "encounter = '$form_encounter' WHERE " .
     "sale_id = '$id'";
   }
   else {
    $query = "UPDATE billing SET billed = 1, bill_date = NOW() WHERE " .
     "id = '$id'";
   }
   sqlQuery($query);
   // echo $query . "<br>\n"; // debugging
  }

  if ($_POST['form_amount']) {
   $paydesc = 'Received at time of visit';
   if ($_POST['form_source']) $paydesc .= ' (' . $_POST['form_source'] . ')';
   $msg = invoice_add_line_item($invoice_info, 'COPAY',
    $_POST['form_method'],
    $paydesc,
    $_POST['form_amount']);
   if ($msg) die($msg);
  }

  $msg = invoice_post($invoice_info);
  if ($msg) die($msg);

  generate_receipt($_POST['form_pid']);
  exit();
 }

 // Get the unbilled billing table items and prescription sales for
 // this patient.

 $query = "SELECT id, date, code_type, code, code_text, " .
  "provider_id, payer_id, fee, encounter " .
  "FROM billing " .
  "WHERE pid = '$pid' AND activity = 1 AND billed = 0 " .
  "ORDER BY encounter";
 $bres = sqlStatement($query);

 $query = "SELECT s.sale_id, s.sale_date, s.prescription_id, s.fee, " .
  "d.name, r.provider_id " .
  "FROM drug_sales AS s " .
  "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
  "LEFT JOIN prescriptions AS r ON r.id = s.prescription_id " .
  "WHERE s.pid = '$pid' AND s.encounter = 0 " .
  "ORDER BY s.sale_id";
 $dres = sqlStatement($query);

 // If there are none, just redisplay the last receipt and exit.
 //
 if (mysql_num_rows($bres) == 0 && mysql_num_rows($dres) == 0) {
  generate_receipt($pid);
  exit();
 }

 // Now write a data entry form:
 // List unbilled billing items (cpt, hcpcs, copays) for the patient.
 // List unbilled prescription sales for the patient.
 // Present an editable dollar amount for each line item, a total
 // which is also the default value of the input payment amount,
 // and OK and Cancel buttons.
?>
<html>
<head>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<title><? xl('Patient Checkout','e'); ?></title>
<style>
</style>
<script language="JavaScript">
</script>
</head>

<body <?echo $top_bg_line;?> leftmargin='0' topmargin='0' marginwidth='0'
 marginheight='0'>

<form method='post' action='pos_checkout.php'>
<input type='hidden' name='form_pid' value='<?php echo $pid ?>' />

<center>

<p>
<table cellspacing='5'>
 <tr>
  <td colspan='3' align='center'>
   <b><? xl('Patient Checkout for ','e'); ?><?php echo $patdata['fname'] . " " .
    $patdata['lname'] . " (" . $patdata['pubpid'] . ")" ?></b>
  </td>
 </tr>
 <tr>
  <td><b>Date</b></td>
  <td><b>Description</b></td>
  <td align='right'><b>Amount</b>&nbsp;</td>
 </tr>
<?php
 $inv_encounter = '';
 $inv_provider  = 0;
 $inv_payer     = 0;
 $total         = 0.00;
 while ($brow = sqlFetchArray($bres)) {
  write_form_line($brow['code_type'], $brow['code'], $brow['id'],
   substr($brow['date'], 0, 10), $brow['code_text'], $brow['fee']);
  $inv_encounter = $brow['encounter'];
  $inv_provider  = $brow['provider_id'];
  $inv_payer     = $brow['payer_id'];
  $total += $brow['fee'];
 }
 while ($drow = sqlFetchArray($dres)) {
  write_form_line('MED', $drow['prescription_id'], $drow['sale_id'],
   $drow['sale_date'], $drow['name'], $drow['fee']);
  $inv_provider = $drow['provider_id'];
  $total += $drow['fee'];
 }
?>
</table>

<p>
<table border='0' cellspacing='8'>

 <tr>
  <td>
   <? xl('Payment Method','e'); ?>:
  </td>
  <td>
   <select name='form_method'>
<?
 foreach ($payment_methods as $value) {
  echo "    <option value='$value'";
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
   <input type='text' name='form_source' size='10' value=''>
  </td>
 </tr>

 <tr>
  <td>
   <? xl('Amount Paid','e'); ?>:
  </td>
  <td>
   <input type='text' name='form_amount' size='10' value='<?php echo sprintf("%01.2f", $total) ?>'>
  </td>
 </tr>

 <tr>
  <td colspan='2' align='center'>
   &nbsp;<br>
   <input type='submit' name='form_save' value='Save' /> &nbsp;
   <input type='button' value='Cancel' onclick='window.close()' />
   <input type='hidden' name='form_provider'  value='<?php echo $inv_provider  ?>' />
   <input type='hidden' name='form_payer'     value='<?php echo $inv_payer     ?>' />
   <input type='hidden' name='form_encounter' value='<?php echo $inv_encounter ?>' />
  </td>
 </tr>

</table>
</center>

</form>

</body>
</html>
