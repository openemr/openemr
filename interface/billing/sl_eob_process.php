<?php
	// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
	//
	// This program is free software; you can redistribute it and/or
	// modify it under the terms of the GNU General Public License
	// as published by the Free Software Foundation; either version 2
	// of the License, or (at your option) any later version.

	// This processes X12 835 remittances and produces a report.

	// Buffer all output so we can archive it to a file.
	ob_start();

	include_once("../globals.php");
	include_once("../../library/invoice_summary.inc.php");
	include_once("../../library/sl_eob.inc.php");
	include_once("../../library/parse_era.inc.php");
	include_once("claim_status_codes.php");
	include_once("adjustment_reason_codes.php");
	include_once("remark_codes.php");

	$debug = $_GET['debug'] ? 1 : 0; // set to 1 for debugging mode
	$encount = 0;

	$last_ptname = '';
	$last_invnumber = '';
	$last_code = '';
	$invoice_total = 0.00;

///////////////////////// Assorted Functions /////////////////////////

	function parse_date($date) {
		$date = substr(trim($date), 0, 10);
		if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)$/', $date, $matches)) {
			return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
		}
		return '';
	}

	function writeMessageLine($bgcolor, $class, $description) {
		$dline =
			" <tr bgcolor='$bgcolor'>\n" .
			"  <td class='$class' colspan='4'>&nbsp;</td>\n" .
			"  <td class='$class'>$description</td>\n" .
			"  <td class='$class' colspan='2'>&nbsp;</td>\n" .
			" </tr>\n";
		echo $dline;
	}

	function writeDetailLine($bgcolor, $class, $ptname, $invnumber,
		$code, $date, $description, $amount, $balance)
	{
		global $last_ptname, $last_invnumber, $last_code;
		if ($ptname == $last_ptname) $ptname = '&nbsp;';
			else $last_ptname = $ptname;
		if ($invnumber == $last_invnumber) $invnumber = '&nbsp;';
			else $last_invnumber = $invnumber;
		if ($code == $last_code) $code = '&nbsp;';
			else $last_code = $code;
		if ($amount ) $amount  = sprintf("%.2f", $amount );
		if ($balance) $balance = sprintf("%.2f", $balance);
		$dline =
			" <tr bgcolor='$bgcolor'>\n" .
			"  <td class='$class'>$ptname</td>\n" .
			"  <td class='$class'>$invnumber</td>\n" .
			"  <td class='$class'>$code</td>\n" .
			"  <td class='$class'>$date</td>\n" .
			"  <td class='$class'>$description</td>\n" .
			"  <td class='$class' align='right'>$amount</td>\n" .
			"  <td class='$class' align='right'>$balance</td>\n" .
			" </tr>\n";
		echo $dline;
	}

	// This writes detail lines that were already in SQL-Ledger for a given
	// charge item.
	//
	function writeOldDetail(&$prev, $ptname, $invnumber, $dos, $code, $bgcolor) {
		global $invoice_total;
		// $prev['total'] = 0.00; // to accumulate total charges
		ksort($prev['dtl']);
		foreach ($prev['dtl'] as $dkey => $ddata) {
			$ddate = substr($dkey, 0, 10);
			$description = $ddata['src'] . $ddata['rsn'];
			if ($ddate == '          ') { // this is the service item
				$ddate = $dos;
				$description = 'Service Item';
			}
			$amount = sprintf("%.2f", $ddata['chg'] - $ddata['pmt']);
			$invoice_total = sprintf("%.2f", $invoice_total + $amount);
			writeDetailLine($bgcolor, 'olddetail', $ptname, $invnumber,
				$code, $ddate, $description, $amount, $invoice_total);
		}
	}

	// This is called back by parse_era() once per claim.
	//
	function era_callback(&$out) {
		global $encount, $debug, $claim_status_codes, $adjustment_reasons, $remark_codes;
		global $invoice_total, $last_code;

		// Some heading information.
		if ($encount == 0) {
			writeMessageLine('#ffffff', 'infdetail',
				"Payer: " . htmlentities($out['payer_name']));
			if ($debug) {
				writeMessageLine('#ffffff', 'infdetail',
					"WITHOUT UPDATE is selected; no changes will be applied.");
			}
		}

		$last_code = '';
		$invoice_total = 0.00;
		$bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
		list($pid, $encounter, $invnumber) = slInvoiceNumber($out);

		// Get details, if we have them, for the invoice.
		$inverror = true;
		$codes = array();
		if ($pid && $encounter) {
			// Get invoice data into $arrow.
			$arres = SLQuery("SELECT ar.id, ar.notes, ar.shipvia, customer.name " .
				"FROM ar, customer WHERE ar.invnumber = '$invnumber' AND " .
				"customer.id = ar.customer_id");
			if ($sl_err) die($sl_err);
			$arrow = SLGetRow($arres, 0);
			if ($arrow) {
				$inverror = false;
				$codes = get_invoice_summary($arrow['id'], true);
			} else { // oops, no such invoice
				$pid = $encounter = 0;
				$invnumber = $out['our_claim_id'];
			}
		}

		$insurance_id = 0;
		foreach ($codes as $cdata) {
			if ($cdata['ins']) {
				$insurance_id = $cdata['ins'];
				break;
			}
		}

		// Show the claim status.
		$csc = $out['claim_status_code'];
		$inslabel = 'Ins1';
		if ($csc == '1' || $csc == '19') $inslabel = 'Ins1';
		if ($csc == '2' || $csc == '20') $inslabel = 'Ins2';
		if ($csc == '3' || $csc == '21') $inslabel = 'Ins3';
		$primary = ($inslabel == 'Ins1');
		writeMessageLine($bgcolor, 'infdetail',
			"Claim status $csc: " . $claim_status_codes[$csc]);

		// Show an error message if the claim is missing or already posted.
		if ($inverror) {
			writeMessageLine($bgcolor, 'errdetail',
				"The following claim is not in our database");
		}
		else {
			// Skip this test. Claims can get multiple CLPs from the same payer!
			//
			// $insdone = strtolower($arrow['shipvia']);
			// if (strpos($insdone, 'ins1') !== false) {
			//  $inverror = true;
			//  writeMessageLine($bgcolor, 'errdetail',
			//   "Primary insurance EOB was already posted for the following claim");
			// }
		}

		if ($csc == '4') {
			$inverror = true;
			writeMessageLine($bgcolor, 'errdetail',
				"Not posting adjustments for denied claims, please follow up manually!");
		}
		else if ($csc == '22') {
			$inverror = true;
			writeMessageLine($bgcolor, 'errdetail',
				"Payment reversals are not automated, please enter manually!");
		}

		if ($out['warnings']) {
			writeMessageLine($bgcolor, 'infdetail', nl2br(rtrim($out['warnings'])));
		}

		// Simplify some claim attributes for cleaner code.
		$service_date = parse_date($out['dos']);
		$check_date = parse_date($out['check_date']);
		$production_date = parse_date($out['production_date']);
		$patient_name = $arrow['name'] ? $arrow['name'] :
			($out['patient_fname'] . ' ' . $out['patient_lname']);

		$error = $inverror;

		// This loops once for each service item in this claim.
		foreach ($out['svc'] as $svc) {
			$prev = $codes[$svc['code']];

			// This reports detail lines already on file for this service item.
			if ($prev) {
				writeOldDetail($prev, $patient_name, $invnumber, $service_date, $svc['code'], $bgcolor);
				// Check for sanity in amount charged.
				$prevchg = sprintf("%.2f", $prev['chg'] + $prev['adj']);
				if ($prevchg != abs($svc['chg'])) {
					writeMessageLine($bgcolor, 'errdetail',
						"EOB charge amount " . $svc['chg'] . " for this code does not match our invoice");
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

				unset($codes[$svc['code']]);
			}

			// If the service item is not in our database...
			else {

				// This is not an error. If we are not in error mode and not debugging,
				// insert the service item into SL.  Then display it (in green if it
				// was inserted, or in red if we are in error mode).
				$description = 'CPT4:' . $svc['code'] . " Added by $inslabel $production_date";
				if (!$error && !$debug) {
					slPostCharge($arrow['id'], $svc['chg'], $service_date, $svc['code'],
						$insurance_id, $description, $debug);
					$invoice_total += $svc['chg'];
				}
				$class = $error ? 'errdetail' : 'newdetail';
				writeDetailLine($bgcolor, $class, $patient_name, $invnumber,
					$svc['code'], $production_date, $description,
					$svc['chg'], ($error ? '' : $invoice_total));

			}

			$class = $error ? 'errdetail' : 'newdetail';

			// Report Allowed Amount.
			if ($svc['allowed']) {
				// A problem here is that some payers will include an adjustment
				// reflecting the allowed amount, others not.  So here we need to
				// check if the adjustment exists, and if not then create it.  We
				// assume that any nonzero CO (Contractual Obligation) adjustment
				// is good enough.
				$contract_adj = sprintf("%.2f", $svc['chg'] - $svc['allowed']);
				foreach ($svc['adj'] as $adj) {
					if ($adj['group_code'] == 'CO' && $adj['amount'] != 0)
						$contract_adj = 0;
				}
				if ($contract_adj > 0) {
					$svc['adj'][] = array('group_code' => 'CO', 'reason_code' => 'A2',
						'amount' => $contract_adj);
				}
				writeMessageLine($bgcolor, 'infdetail',
					'Allowed amount is ' . sprintf("%.2f", $svc['allowed']));
			}

			// Report miscellaneous remarks.
			if ($svc['remark']) {
				$rmk = $svc['remark'];
				writeMessageLine($bgcolor, 'infdetail', "$rmk: " . $remark_codes[$rmk]);
			}

			// Post and report the payment for this service item from the ERA.
			// By the way a 'Claim' level payment is probably going to be negative,
			// i.e. a payment reversal.
			if ($svc['paid']) {
				if (!$error && !$debug) {
					slPostPayment($arrow['id'], $svc['paid'], $check_date,
						"$inslabel/" . $out['check_number'], $svc['code'], $insurance_id, $debug);
					$invoice_total -= $svc['paid'];
				}
				$description = "$inslabel/" . $out['check_number'] . ' payment';
				if ($svc['paid'] < 0) $description .= ' reversal';
				writeDetailLine($bgcolor, $class, $patient_name, $invnumber,
					$svc['code'], $check_date, $description,
					0 - $svc['paid'], ($error ? '' : $invoice_total));
			}

			// Post and report adjustments from this ERA.  Posted adjustment reasons
			// must be 25 characters or less in order to fit on patient statements.
			foreach ($svc['adj'] as $adj) {
				$description = $adj['reason_code'] . ': ' . $adjustment_reasons[$adj['reason_code']];
				if ($adj['group_code'] == 'PR' || !$primary) {
					// Group code PR is Patient Responsibility.  Enter these as zero
					// adjustments to retain the note without crediting the claim.
					if ($primary) {
						$reason = 'Pt resp: '; // Reasons should be 25 chars or less.
						if ($adj['reason_code'] == '1') $reason = 'To deductible: ';
						else if ($adj['reason_code'] == '2') $reason = 'Coinsurance: ';
						else if ($adj['reason_code'] == '3') $reason = 'Co-pay: ';
					}
					// Non-primary insurance adjustments are garbage, either repeating
					// the primary or are not adjustments at all.  Report them as notes
					// but do not post any amounts.
					else {
						$reason = "$inslabel note " . $adj['reason_code'] . ': ';
						$reason .= sprintf("%.2f", $adj['amount']);
					}
					$reason .= sprintf("%.2f", $adj['amount']);
					// Post a zero-dollar adjustment just to save it as a comment.
					if (!$error && !$debug) {
						slPostAdjustment($arrow['id'], 0, $production_date,
							$out['check_number'], $svc['code'], $insurance_id,
							$reason, $debug);
					}
					writeMessageLine($bgcolor, $class, $description . ' ' .
						sprintf("%.2f", $adj['amount']));
				}
				// Other group codes for primary insurance are real adjustments.
				else {
					if (!$error && !$debug) {
						slPostAdjustment($arrow['id'], $adj['amount'], $production_date,
							$out['check_number'], $svc['code'], $insurance_id,
							"$inslabel adjust code " . $adj['reason_code'], $debug);
						$invoice_total -= $adj['amount'];
					}
					writeDetailLine($bgcolor, $class, $patient_name, $invnumber,
						$svc['code'], $production_date, $description,
						0 - $adj['amount'], ($error ? '' : $invoice_total));
				}
			}

		} // End of service item

		// Report any existing service items not mentioned in the ERA, and
		// determine if any of them are still missing an insurance response
		// (if so, then insurance is not yet done with the claim).
		$insurance_done = true;
		foreach ($codes as $code => $prev) {
			writeOldDetail($prev, $arrow['name'], $invnumber, $service_date, $code, $bgcolor);
			$got_response = false;
			foreach ($prev['dtl'] as $ddata) {
				if ($ddata['pmt'] || $ddata['rsn']) $got_response = true;
			}
			if (!$got_response) $insurance_done = false;
		}

		// Cleanup: If all is well, mark Ins<x> done and check for secondary billing.
		if (!$error && !$debug && $insurance_done) {
			$shipvia = 'Done: Ins1';
			if ($inslabel != 'Ins1') $shipvia .= ',Ins2';
			if ($inslabel == 'Ins3') $shipvia .= ',Ins3';
			$query = "UPDATE ar SET shipvia = '$shipvia' WHERE id = " . $arrow['id'];
			SLQuery($query);
			if ($sl_err) die($sl_err);
			// Check for secondary insurance.
			$insgot = strtolower($arrow['notes']);
			if ($primary && strpos($insgot, 'ins2') !== false) {
				slSetupSecondary($arrow['id'], $debug);
				writeMessageLine($bgcolor, 'infdetail',
					'This claim is now re-queued for secondary paper billing');
			}
		}

	}

/////////////////////////// End Functions ////////////////////////////

	$info_msg = "";

	$eraname = $_GET['eraname'];
	if (! $eraname) die(xl("You cannot access this page directly."));

	// Open the output file early so that in case it fails, we do not post a
	// bunch of stuff without saving the report.  Also be sure to retain any old
	// report files.  Do not save the report if this is a no-update situation.
	//
	if (!$debug) {
		$nameprefix = "$webserver_root/era/$eraname";
		$namesuffix = '';
		for ($i = 1; is_file("$nameprefix$namesuffix.html"); ++$i) {
			$namesuffix = "_$i";
		}
		$fnreport = "$nameprefix$namesuffix.html";
		$fhreport = fopen($fnreport, 'w');
		if (!$fhreport) die(xl("Cannot create") . " '$fnreport'");
	}

	slInitialize();
?>
<html>
<head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">
 body       { font-family:sans-serif; font-size:8pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:9pt; font-weight:bold }
 .olddetail { color:#000000; font-family:sans-serif; font-size:9pt; font-weight:normal }
 .newdetail { color:#00dd00; font-family:sans-serif; font-size:9pt; font-weight:normal }
 .errdetail { color:#dd0000; font-family:sans-serif; font-size:9pt; font-weight:normal }
 .infdetail { color:#0000ff; font-family:sans-serif; font-size:9pt; font-weight:normal }
</style>
<title><?php xl('EOB Posting - Electronic Remittances','e')?></title>
<script language="JavaScript">
</script>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<table border='0' cellpadding='2' cellspacing='0' width='100%'>

 <tr bgcolor="#cccccc">
  <td class="dehead">
   <?php xl('Patient','e') ?>
  </td>
  <td class="dehead">
   <?php xl('Invoice','e') ?>
  </td>
  <td class="dehead">
   <?php xl('Code','e') ?>
  </td>
  <td class="dehead">
   <?php xl('Date','e') ?>
  </td>
  <td class="dehead">
   <?php xl('Description','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Amount','e') ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Balance','e') ?>&nbsp;
  </td>
 </tr>

<?php
  $alertmsg = parse_era("$webserver_root/era/$eraname.edi", 'era_callback');
  slTerminate();
?>
</table>
</center>
<script language="JavaScript">
<?php
	if ($alertmsg) echo " alert('" . htmlentities($alertmsg) . "');\n";
?>
</script>
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
