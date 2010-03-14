<?php
// Copyright (C) 2006-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/sql-ledger.inc");
require_once("../../library/invoice_summary.inc.php");
require_once("../../library/sl_eob.inc.php");

$INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;

$alertmsg = '';
$bgcolor = "#aaaaaa";
$export_patient_count = 0;
$export_dollars = 0;

$today = date("Y-m-d");

$form_date      = fixDate($_POST['form_date'], "");
$form_to_date   = fixDate($_POST['form_to_date'], "");
$is_ins_summary = $_POST['form_category'] == xl('Ins Summary');
$is_due_ins     = ($_POST['form_category'] == xl('Due Ins')) || $is_ins_summary;
$is_due_pt      = $_POST['form_category'] == xl('Due Pt');
$is_all         = $_POST['form_category'] == xl('All');
$is_ageby_lad   = strpos($_POST['form_ageby'], 'Last') !== false;
$form_facility  = $_POST['form_facility'];

if ($_POST['form_search'] || $_POST['form_export'] || $_POST['form_csvexport']) {
  if ($is_ins_summary) {
    $form_cb_ssn      = false;
    $form_cb_dob      = false;
    $form_cb_pubpid   = false;
    $form_cb_adate    = false;
    $form_cb_policy   = false;
    $form_cb_phone    = false;
    $form_cb_city     = false;
    $form_cb_ins1     = false;
    $form_cb_referrer = false;
    $form_cb_idays    = false;
    $form_cb_err      = false;
  } else {
    $form_cb_ssn      = $_POST['form_cb_ssn']      ? true : false;
    $form_cb_dob      = $_POST['form_cb_dob']      ? true : false;
    $form_cb_pubpid   = $_POST['form_cb_pubpid']   ? true : false;
    $form_cb_adate    = $_POST['form_cb_adate']    ? true : false;
    $form_cb_policy   = $_POST['form_cb_policy']   ? true : false;
    $form_cb_phone    = $_POST['form_cb_phone']    ? true : false;
    $form_cb_city     = $_POST['form_cb_city']     ? true : false;
    $form_cb_ins1     = $_POST['form_cb_ins1']     ? true : false;
    $form_cb_referrer = $_POST['form_cb_referrer'] ? true : false;
    $form_cb_idays    = $_POST['form_cb_idays']    ? true : false;
    $form_cb_err      = $_POST['form_cb_err']      ? true : false;
  }
} else {
  $form_cb_ssn      = true;
  $form_cb_dob      = false;
  $form_cb_pubpid   = false;
  $form_cb_adate    = false;
  $form_cb_policy   = false;
  $form_cb_phone    = true;
  $form_cb_city     = false;
  $form_cb_ins1     = false;
  $form_cb_referrer = false;
  $form_cb_idays    = false;
  $form_cb_err      = false;
}
$form_age_cols = (int) $_POST['form_age_cols'];
$form_age_inc  = (int) $_POST['form_age_inc'];
if ($form_age_cols > 0 && $form_age_cols < 50) {
  if ($form_age_inc <= 0) $form_age_inc = 30;
} else {
  $form_age_cols = 0;
  $form_age_inc  = 0;
}

$initial_colspan = 1;
if ($is_due_ins      ) ++$initial_colspan;
if ($form_cb_ssn     ) ++$initial_colspan;
if ($form_cb_dob     ) ++$initial_colspan;
if ($form_cb_pubpid  ) ++$initial_colspan;
if ($form_cb_policy  ) ++$initial_colspan;
if ($form_cb_phone   ) ++$initial_colspan;
if ($form_cb_city    ) ++$initial_colspan;
if ($form_cb_ins1    ) ++$initial_colspan;
if ($form_cb_referrer) ++$initial_colspan;

$final_colspan = $form_cb_adate ? 6 : 5;

$grand_total_charges     = 0;
$grand_total_adjustments = 0;
$grand_total_paid        = 0;
$grand_total_agedbal = array();
for ($c = 0; $c < $form_age_cols; ++$c) $grand_total_agedbal[$c] = 0;

if (!$INTEGRATED_AR) SLConnect();

function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
}

function endPatient($ptrow) {
  global $export_patient_count, $export_dollars, $bgcolor;
  global $grand_total_charges, $grand_total_adjustments, $grand_total_paid;
  global $grand_total_agedbal, $is_due_ins, $form_age_cols;
  global $initial_colspan, $final_colspan, $form_cb_idays, $form_cb_err;

  if (!$ptrow['pid']) return;

  $pt_balance = $ptrow['amount'] - $ptrow['paid'];

  if ($_POST['form_export']) {
    // This is a fixed-length format used by Transworld Systems.  Your
    // needs will surely be different, so consider this just an example.
    //
    echo "1896H"; // client number goes here
    echo "000";   // filler
    echo sprintf("%-30s", substr($ptrow['ptname'], 0, 30));
    echo sprintf("%-30s", " ");
    echo sprintf("%-30s", substr($ptrow['address1'], 0, 30));
    echo sprintf("%-15s", substr($ptrow['city'], 0, 15));
    echo sprintf("%-2s", substr($ptrow['state'], 0, 2));
    echo sprintf("%-5s", $ptrow['zipcode'] ? substr($ptrow['zipcode'], 0, 5) : '00000');
    echo "1";                      // service code
    echo sprintf("%010.0f", $ptrow['pid']); // transmittal number = patient id
    echo " ";                      // filler
    echo sprintf("%-15s", substr($ptrow['ss'], 0, 15));
    echo substr($ptrow['dos'], 5, 2) . substr($ptrow['dos'], 8, 2) . substr($ptrow['dos'], 2, 2);
    echo sprintf("%08.0f", $pt_balance * 100);
    echo sprintf("%-9s\n", " ");

    if (!$_POST['form_without']) {
      sqlStatement("UPDATE patient_data SET " .
        "genericname2 = 'Billing', " .
        "genericval2 = CONCAT('IN COLLECTIONS " . date("Y-m-d") . "', genericval2) " .
        "WHERE pid = '" . $ptrow['pid'] . "'");
    }
    $export_patient_count += 1;
    $export_dollars += $pt_balance;
  }
  else if ($_POST['form_csvexport']) {
    $export_patient_count += 1;
    $export_dollars += $pt_balance;
  }
  else {
    if ($ptrow['count'] > 1) {
      echo " <tr bgcolor='$bgcolor'>\n";
      echo "  <td class='detail' colspan='$initial_colspan'>";
      echo "&nbsp;</td>\n";
      echo "  <td class='detotal' colspan='$final_colspan'>&nbsp;Total Patient Balance:</td>\n";
      if ($form_age_cols) {
        for ($c = 0; $c < $form_age_cols; ++$c) {
          echo "  <td class='detotal' align='right'>&nbsp;" .
            sprintf("%.2f", $ptrow['agedbal'][$c]) . "&nbsp;</td>\n";
        }
      }
      else {
        echo "  <td class='detotal' align='right'>&nbsp;" .
          sprintf("%.2f", $pt_balance) . "&nbsp;</td>\n";
      }
      if ($form_cb_idays) echo "  <td class='detail'>&nbsp;</td>\n";
      echo "  <td class='detail' colspan='2'>&nbsp;</td>\n";
      if ($form_cb_err) echo "  <td class='detail'>&nbsp;</td>\n";
      echo " </tr>\n";
    }
  }
  $grand_total_charges     += $ptrow['charges'];
  $grand_total_adjustments += $ptrow['adjustments'];
  $grand_total_paid        += $ptrow['paid'];
  for ($c = 0; $c < $form_age_cols; ++$c) {
    $grand_total_agedbal[$c] += $ptrow['agedbal'][$c];
  }
}

function endInsurance($insrow) {
  global $export_patient_count, $export_dollars, $bgcolor;
  global $grand_total_charges, $grand_total_adjustments, $grand_total_paid;
  global $grand_total_agedbal, $is_due_ins, $form_age_cols;
  global $initial_colspan, $form_cb_idays, $form_cb_err;
  if (!$insrow['pid']) return;
  $ins_balance = $insrow['amount'] - $insrow['paid'];
  if ($_POST['form_export'] || $_POST['form_csvexport']) {
    // No exporting of insurance summaries.
    $export_patient_count += 1;
    $export_dollars += $ins_balance;
  }
  else {
    echo " <tr bgcolor='$bgcolor'>\n";
    echo "  <td class='detail'>" . $insrow['insname'] . "</td>\n";
    echo "  <td class='detotal' align='right'>&nbsp;" .
      sprintf("%.2f", $insrow['charges']) . "&nbsp;</td>\n";
    echo "  <td class='detotal' align='right'>&nbsp;" .
      sprintf("%.2f", $insrow['adjustments']) . "&nbsp;</td>\n";
    echo "  <td class='detotal' align='right'>&nbsp;" .
      sprintf("%.2f", $insrow['paid']) . "&nbsp;</td>\n";
    if ($form_age_cols) {
      for ($c = 0; $c < $form_age_cols; ++$c) {
        echo "  <td class='detotal' align='right'>&nbsp;" .
          sprintf("%.2f", $insrow['agedbal'][$c]) . "&nbsp;</td>\n";
      }
    }
    else {
      echo "  <td class='detotal' align='right'>&nbsp;" .
        sprintf("%.2f", $ins_balance) . "&nbsp;</td>\n";
    }
    echo " </tr>\n";
  }
  $grand_total_charges     += $insrow['charges'];
  $grand_total_adjustments += $insrow['adjustments'];
  $grand_total_paid        += $insrow['paid'];
  for ($c = 0; $c < $form_age_cols; ++$c) {
    $grand_total_agedbal[$c] += $insrow['agedbal'][$c];
  }
}

function getInsName($payerid) {
  $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE id = '$payerid'");
  return $tmp['name'];
}

// In the case of CSV export only, a download will be forced.
if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=collections_report.csv");
  header("Content-Description: File Transfer");
}
else {
?>
<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php xl('Collections Report','e')?></title>
<style type="text/css">

@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results {
       margin-top: 30px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>

<script language="JavaScript">

function checkAll(checked) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  if (ename.indexOf('form_cb[') == 0)
   f.elements[i].checked = checked;
 }
}

</script>

</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Collections','e'); ?></span>

<form method='post' action='collections_report.php' enctype='multipart/form-data' id='theform'>

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_export' id='form_export' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='610px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
				<table>
					<tr>
						<td><?php xl('Displayed Columns','e') ?>:</td>
					</tr>
					<tr>
						<td>
						   <input type='checkbox' name='form_cb_ssn'<?php if ($form_cb_ssn) echo ' checked'; ?>>
						   <?php xl('SSN','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_dob'<?php if ($form_cb_dob) echo ' checked'; ?>>
						   <?php xl('DOB','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_pubpid'<?php if ($form_cb_pubpid) echo ' checked'; ?>>
						   <?php xl('ID','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_policy'<?php if ($form_cb_policy) echo ' checked'; ?>>
						   <?php xl('Policy','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_phone'<?php if ($form_cb_phone) echo ' checked'; ?>>
						   <?php xl('Phone','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_city'<?php if ($form_cb_city) echo ' checked'; ?>>
						   <?php xl('City','e') ?>&nbsp;
						</td>
					</tr>
					<tr>
						<td>
						   <input type='checkbox' name='form_cb_ins1'<?php if ($form_cb_ins1) echo ' checked'; ?>>
						   <?php xl('Primary Ins','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_referrer'<?php if ($form_cb_referrer) echo ' checked'; ?>>
						   <?php xl('Referrer','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_adate'<?php if ($form_cb_adate) echo ' checked'; ?>>
						   <?php xl('Act Date','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_idays'<?php if ($form_cb_idays) echo ' checked'; ?>>
						   <?php xl('Inactive Days','e') ?>&nbsp;
						</td>
						<td>
						   <input type='checkbox' name='form_cb_err'<?php if ($form_cb_err) echo ' checked'; ?>>
						   <?php xl('Errors','e') ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</tr>
			<td>
				<table>

					<tr>
						<td class='label'>
						   <?php xl('Service Date','e'); ?>:
						</td>
						<td>
						   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
							onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
						   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
							title='<?php xl('Click here to choose a date','e'); ?>'>
						</td>
						<td class='label'>
						   <?php xl('To','e'); ?>:
						</td>
						<td>
						   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
							onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
						   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
							title='<?php xl('Click here to choose a date','e'); ?>'>
						</td>
						<td>
						   <select name='form_category'>
						<?php
						 foreach (array(xl('Open'),xl('Due Pt'),xl('Due Ins'),xl('Ins Summary'),xl('Credits'),xl('All')) as $value) {
						  echo "    <option value='$value'";
						  if ($_POST['form_category'] == $value) echo " selected";
						  echo ">$value</option>\n";
						 }
						?>
						   </select>
						</td>

					</tr>


					<tr>
						<td class='label'>
						   <?php xl('Facility','e'); ?>:
						</td>
						<td>
							<?php
							  // Build a drop-down list of facilities.
							  //
							  $query = "SELECT id, name FROM facility ORDER BY name";
							  $fres = sqlStatement($query);
							  echo "   <select name='form_facility'>\n";
							  echo "    <option value=''>-- " . xl('All Facilities') . " --\n";
							  while ($frow = sqlFetchArray($fres)) {
								$facid = $frow['id'];
								echo "    <option value='$facid'";
								if ($facid == $form_facility) echo " selected";
								echo ">" . $frow['name'] . "\n";
							  }
							  echo "   </select>\n";
							?>
						</td>
					</tr>

					<tr>
						<td class='label'>
						   <?php xl('Age By','e') ?>:
						</td>
						<td>
						   <select name='form_ageby'>
						<?php
						 foreach (array('Service Date', 'Last Activity Date') as $value) {
						  echo "    <option value='$value'";
						  if ($_POST['form_ageby'] == $value) echo " selected";
						  echo ">" . xl($value) . "</option>\n";
						 }
						?>
						   </select>
						</td>
					</tr>
					</tr>
						<td class='label'>
						   <?php xl('Aging Columns:','e') ?>
						</td>
						<td>
						   <input type='text' name='form_age_cols' size='2' value='<?php echo $form_age_cols; ?>' />
						</td>
						<td class='label'>
						   <?php xl('Days/Col:','e') ?>
						</td>
						<td>
						   <input type='text' name='form_age_inc' size='3' value='<?php echo $form_age_inc; ?>' />
						</td>
					</tr>


				</table>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>
</div>


<?php

} // end not form_csvexport

if ($_POST['form_refresh'] || $_POST['form_export'] || $_POST['form_csvexport']) {
  $rows = array();
  $where = "";

  if ($INTEGRATED_AR) {
    if ($_POST['form_export'] || $_POST['form_csvexport']) {
      $where = "( 1 = 2";
      foreach ($_POST['form_cb'] as $key => $value) $where .= " OR f.pid = $key";
      $where .= ' )';
    }
    if ($form_date) {
      if ($where) $where .= " AND ";
      if ($form_to_date) {
        $where .= "f.date >= '$form_date 00:00:00' AND f.date <= '$form_to_date 23:59:59'";
      }
      else {
        $where .= "f.date >= '$form_date 00:00:00' AND f.date <= '$form_date 23:59:59'";
      }
    }
    if ($form_facility) {
      if ($where) $where .= " AND ";
      $where .= "f.facility_id = '$form_facility'";
    }
    if (! $where) {
      $where = "1 = 1";
    }

    $query = "SELECT f.id, f.date, f.pid, f.encounter, f.last_level_billed, " .
      "f.last_level_closed, f.last_stmt_date, f.stmt_count, " .
      "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
      "p.postal_code, p.phone_home, p.ss, p.genericname2, p.genericval2, " .
      "p.pubpid, p.DOB, CONCAT(u.lname, ', ', u.fname) AS referrer, " .
      "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type != 'COPAY' ) AS charges, " .
      "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type = 'COPAY' ) AS copays, " .
      "( SELECT SUM(s.fee) FROM drug_sales AS s WHERE " .
      "s.pid = f.pid AND s.encounter = f.encounter ) AS sales, " .
      "( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE " .
      "a.pid = f.pid AND a.encounter = f.encounter ) AS payments, " .
      "( SELECT SUM(a.adj_amount) FROM ar_activity AS a WHERE " .
      "a.pid = f.pid AND a.encounter = f.encounter ) AS adjustments " .
      "FROM form_encounter AS f " .
      "JOIN patient_data AS p ON p.pid = f.pid " .
      "LEFT OUTER JOIN users AS u ON u.id = p.providerID " .
      "WHERE $where " .
      "ORDER BY f.pid, f.encounter";
    $eres = sqlStatement($query);

    while ($erow = sqlFetchArray($eres)) {
      $patient_id = $erow['pid'];
      $encounter_id = $erow['encounter'];
      $pt_balance = $erow['charges'] + $erow['sales'] + $erow['copays'] - $erow['payments'] - $erow['adjustments'];
      $pt_balance = 0 + sprintf("%.2f", $pt_balance); // yes this seems to be necessary
      $svcdate = substr($erow['date'], 0, 10);

      if ($_POST['form_search'] && ! $is_all) {
        if ($pt_balance == 0) continue;
      }
      if ($_POST['form_category'] == 'Credits') {
        if ($pt_balance > 0) continue;
      }

      // If we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.  Here we also compute $insname as the name of
      // the insurance plan from which we are awaiting payment, and its sequence
      // number $insposition (1-3).
      $last_level_closed = $erow['last_level_closed'];
      $duncount = $erow['stmt_count'];
      $payerids = array();
      $insposition = 0;
      $insname = '';
      if (! $duncount) {
        for ($i = 1; $i <= 3; ++$i) {
          $tmp = arGetPayerID($patient_id, $svcdate, $i);
          if (empty($tmp)) break;
          $payerids[] = $tmp;
        }
        $duncount = $last_level_closed - count($payerids);
        if ($duncount < 0) {
          if (!empty($payerids[$last_level_closed])) {
            $insname = getInsName($payerids[$last_level_closed]);
            $insposition = $last_level_closed + 1;
          }
        }
      }

      // Skip invoices not in the desired "Due..." category.
      //
      if ($is_due_ins && $duncount >= 0) continue;
      if ($is_due_pt  && $duncount <  0) continue;

      // echo "<!-- " . $erow['encounter'] . ': ' . $erow['charges'] . ' + ' . $erow['sales'] . ' + ' . $erow['copays'] . ' - ' . $erow['payments'] . ' - ' . $erow['adjustments'] . "  -->\n"; // debugging

      // An invoice is due from the patient if money is owed and we are
      // not waiting for insurance to pay.
      $isduept = ($duncount >= 0) ? " checked" : "";

      $row = array();

      $row['id']        = $erow['id'];
      $row['invnumber'] = "$patient_id.$encounter_id";
      $row['custid']    = $patient_id;
      $row['name']      = $erow['fname'] . ' ' . $erow['lname'];
      $row['address1']  = $erow['street'];
      $row['city']      = $erow['city'];
      $row['state']     = $erow['state'];
      $row['zipcode']   = $erow['postal_code'];
      $row['phone']     = $erow['phone_home'];
      $row['duncount']  = $duncount;
      $row['dos']       = $svcdate;
      $row['ss']        = $erow['ss'];
      $row['DOB']       = $erow['DOB'];
      $row['pubpid']    = $erow['pubpid'];
      $row['billnote']  = ($erow['genericname2'] == 'Billing') ? $erow['genericval2'] : '';
      $row['referrer']  = $erow['referrer'];

      // Also get the primary insurance company name whenever there is one.
      $row['ins1'] = '';
      if ($insposition == 1) {
        $row['ins1'] = $insname;
      } else {
        if (empty($payerids)) {
          $tmp = arGetPayerID($patient_id, $svcdate, 1);
          if (!empty($tmp)) $payerids[] = $tmp;
        }
        if (!empty($payerids)) {
          $row['ins1'] = getInsName($payerids[0]);
        }
      }

      // This computes the invoice's total original charges and adjustments,
      // date of last activity, and determines if insurance has responded to
      // all billing items.
      $invlines = ar_get_invoice_summary($patient_id, $encounter_id, true);

      // if ($encounter_id == 185) { // debugging
      //   echo "\n<!--\n";
      //   print_r($invlines);
      //   echo "\n-->\n";
      // }

      $row['charges'] = 0;
      $row['adjustments'] = 0;
      $row['paid'] = 0;
      $ins_seems_done = true;
      $ladate = $svcdate;
      foreach ($invlines as $key => $value) {
        $row['charges'] += $value['chg'] + $value['adj'];
        $row['adjustments'] += 0 - $value['adj'];
        $row['paid'] += $value['chg'] - $value['bal'];
        foreach ($value['dtl'] as $dkey => $dvalue) {
          $dtldate = trim(substr($dkey, 0, 10));
          if ($dtldate && $dtldate > $ladate) $ladate = $dtldate;
        }
        $lckey = strtolower($key);
        if ($lckey == 'co-pay' || $lckey == 'claim') continue;
        if (count($value['dtl']) <= 1) $ins_seems_done = false;
      }

      // Simulating ar.amount in SQL-Ledger which is charges with adjustments:
      $row['amount'] = $row['charges'] + $row['adjustments'];

      $row['billing_errmsg'] = '';
      if ($is_due_ins && $last_level_closed < 1 && $ins_seems_done)
        $row['billing_errmsg'] = 'Ins1 seems done';
      else if ($last_level_closed >= 1 && !$ins_seems_done)
        $row['billing_errmsg'] = 'Ins1 seems not done';

      $row['ladate'] = $ladate;

      // Compute number of days since last activity.
      $latime = mktime(0, 0, 0, substr($ladate, 5, 2),
        substr($ladate, 8, 2), substr($ladate, 0, 4));
      $row['inactive_days'] = floor((time() - $latime) / (60 * 60 * 24));

      // Look up insurance policy number if we need it.
      if ($form_cb_policy) {
        $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
        $insrow = sqlQuery("SELECT policy_number FROM insurance_data WHERE " .
          "pid = '$patient_id' AND type = '$instype' AND date <= '$svcdate' " .
          "ORDER BY date DESC LIMIT 1");
        $row['policy'] = $insrow['policy_number'];
      }

      $ptname = $erow['lname'] . ", " . $erow['fname'];
      if ($erow['mname']) $ptname .= " " . substr($erow['mname'], 0, 1);

      if (!$is_due_ins ) $insname = '';
      $rows[$insname . '|' . $ptname . '|' . $encounter_id] = $row;
    } // end while
  } // end $INTEGRATED_AR
  else {
    if ($_POST['form_export'] || $_POST['form_csvexport']) {
      $where = "( 1 = 2";
      foreach ($_POST['form_cb'] as $key => $value) $where .= " OR ar.customer_id = $key";
      $where .= ' )';
    }

    if ($form_date) {
      if ($where) $where .= " AND ";
      $date1 = substr($form_date, 0, 4) . substr($form_date, 5, 2) .
        substr($form_date, 8, 2);
      if ($form_to_date) {
        $date2 = substr($form_to_date, 0, 4) . substr($form_to_date, 5, 2) .
          substr($form_to_date, 8, 2);
        $where .= "((CAST (substring(ar.invnumber from position('.' in ar.invnumber) + 1 for 8) AS integer) " .
          "BETWEEN '$date1' AND '$date2')";
        $tmp = "date >= '$form_date' AND date <= '$form_to_date'";
      }
      else {
        // This catches old converted invoices where we have no encounters:
        $where .= "(ar.invnumber LIKE '%.$date1'";
        $tmp = "date = '$form_date'";
      }
      // Pick out the encounters from MySQL with the desired DOS:
      $rez = sqlStatement("SELECT pid, encounter FROM form_encounter WHERE $tmp");
      while ($row = sqlFetchArray($rez)) {
        $where .= " OR ar.invnumber = '" . $row['pid'] . "." . $row['encounter'] . "'";
      }
      $where .= ")";
    }

    if (! $where) {
      $where = "1 = 1";
    }

    // Instead of the subselects in the following query, we will call
    // get_invoice_summary() in order to get data at the procedure level and
    // thus decide if insurance appears to be done with each invoice.

    $query = "SELECT ar.id, ar.invnumber, ar.duedate, ar.amount, ar.paid, " .
      "ar.intnotes, ar.notes, ar.shipvia, " .
      "customer.id AS custid, customer.name, customer.address1, " .
      "customer.city, customer.state, customer.zipcode, customer.phone " .
      // ", (SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
      // "invoice.trans_id = ar.id AND invoice.fxsellprice > 0) AS charges, " .
      // "(SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
      // "invoice.trans_id = ar.id AND invoice.fxsellprice < 0) AS adjustments " .
      "FROM ar JOIN customer ON customer.id = ar.customer_id " .
      "WHERE ( $where ) ";
    if ($_POST['form_search'] && ! $is_all) {
      $query .= "AND ar.amount != ar.paid ";
    }
    $query .= "ORDER BY ar.invnumber";

    // echo "<!-- $query -->\n"; // debugging

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);
    $num_invoices = SLRowCount($t_res);

    //////////////////////////////////////////////////////////////////

    for ($irow = 0; $irow < $num_invoices; ++$irow) {
      $row = SLGetRow($t_res, $irow);

      // If a facility was specified then skip invoices whose encounters
      // do not indicate that facility.
      if ($form_facility) {
        list($patient_id, $encounter_id) = explode(".", $row['invnumber']);
        $tmp = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
          "facility_id = '$form_facility'");
        if (empty($tmp['count'])) continue;
      }

      $pt_balance = sprintf("%.2f",$row['amount']) - sprintf("%.2f",$row['paid']);

      if ($_POST['form_category'] == 'Credits') {
        if ($pt_balance > 0) continue;
      }

      // $duncount was originally supposed to be the number of times that
      // the patient was sent a statement for this invoice.
      //
      $duncount = substr_count(strtolower($row['intnotes']), "statement sent");

      // But if we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.  Here we also compute $insname as the name of
      // the insurance plan from which we are awaiting payment, and its sequence
      // number $insposition (1-3).
      //
      $insname = '';
      $insposition = 0;
      $inseobs = strtolower($row['shipvia']);
      $insgot = strtolower($row['notes']);
      if (! $duncount) {
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          $i = strpos($insgot, $value);
          if ($i !== false && strpos($inseobs, $value) === false) {
            --$duncount;
            if (!$insname && $is_due_ins) {
              $j = strpos($insgot, "\n", $i);
              if (!$j) $j = strlen($insgot);
              $insname = trim(substr($row['notes'], $i + 5, $j - $i - 5));
              $insposition = substr($value, 3); // 1, 2 or 3
            }
          }
        }
      }
      $row['insname'] = $insname;

      // Also get the primary insurance company name whenever there is one.
      $row['ins1'] = '';
      $i = strpos($insgot, 'ins1');
      if ($i !== false) {
        $j = strpos($insgot, "\n", $i);
        if (!$j) $j = strlen($insgot);
        $row['ins1'] = trim(substr($row['notes'], $i + 5, $j - $i - 5));
      }

      // An invoice is now due from the patient if money is owed and we are
      // not waiting for insurance to pay.  We no longer look at the due date
      // for this.
      //
      $isduept = ($duncount >= 0) ? " checked" : "";

      // Skip invoices not in the desired "Due..." category.
      //
      if ($is_due_ins && $duncount >= 0) continue;
      if ($is_due_pt  && $duncount <  0) continue;

      $row['duncount'] = $duncount;

      // Determine the date of service.  An 8-digit encounter number is
      // presumed to be a date of service imported during conversion.
      // Otherwise look it up in the form_encounter table.
      //
      $svcdate = "";
      list($pid, $encounter) = explode(".", $row['invnumber']);
      if (strlen($encounter) == 8) {
        $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
          "-" . substr($encounter, 6, 2);
      }
      else if ($encounter) {
        $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
          "encounter = $encounter");
        $svcdate = substr($tmp['date'], 0, 10);
      }

      $row['dos'] = $svcdate;

      // This computes the invoice's total original charges and adjustments,
      // date of last activity, and determines if insurance has responded to
      // all billing items.
      //
      $invlines = get_invoice_summary($row['id'], true);
      $row['charges'] = 0;
      $row['adjustments'] = 0;
      $ins_seems_done = true;
      $ladate = $svcdate;
      foreach ($invlines as $key => $value) {
        $row['charges'] += $value['chg'] + $value['adj'];
        $row['adjustments'] += 0 - $value['adj'];
        foreach ($value['dtl'] as $dkey => $dvalue) {
          $dtldate = trim(substr($dkey, 0, 10));
          if ($dtldate && $dtldate > $ladate) $ladate = $dtldate;
        }
        $lckey = strtolower($key);
        if ($lckey == 'co-pay' || $lckey == 'claim') continue;
        if (count($value['dtl']) <= 1) $ins_seems_done = false;
      }
      $row['billing_errmsg'] = '';
      if ($is_due_ins && strpos($inseobs, 'ins1') === false && $ins_seems_done)
        $row['billing_errmsg'] = 'Ins1 seems done';
      else if (strpos($inseobs, 'ins1') !== false && !$ins_seems_done)
        $row['billing_errmsg'] = 'Ins1 seems not done';

      $row['ladate'] = $ladate;

      // Compute number of days since last activity.
      $latime = mktime(0, 0, 0, substr($ladate, 5, 2),
        substr($ladate, 8, 2), substr($ladate, 0, 4));
      $row['inactive_days'] = floor((time() - $latime) / (60 * 60 * 24));

      $pdrow = sqlQuery("SELECT pd.fname, pd.lname, pd.mname, pd.ss, " .
        "pd.genericname2, pd.genericval2, pd.pid, pd.pubpid, pd.DOB, " .
        "CONCAT(u.lname, ', ', u.fname) AS referrer FROM " .
        "integration_mapping AS im, patient_data AS pd " .
        "LEFT OUTER JOIN users AS u ON u.id = pd.providerID " .
        "WHERE im.foreign_id = " . $row['custid'] . " AND " .
        "im.foreign_table = 'customer' AND " .
        "pd.id = im.local_id");

      $row['ss'] = $pdrow['ss'];
      $row['DOB'] = $pdrow['DOB'];
      $row['pubpid'] = $pdrow['pubpid'];
      $row['billnote'] = ($pdrow['genericname2'] == 'Billing') ? $pdrow['genericval2'] : '';
      $row['referrer'] = $pdrow['referrer'];

      $ptname = $pdrow['lname'] . ", " . $pdrow['fname'];
      if ($pdrow['mname']) $ptname .= " " . substr($pdrow['mname'], 0, 1);

      // Look up insurance policy number if we need it.
      if ($form_cb_policy) {
        $patient_id = $pdrow['pid'];
        $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
        $insrow = sqlQuery("SELECT policy_number FROM insurance_data WHERE " .
          "pid = '$patient_id' AND type = '$instype' AND date <= '$svcdate' " .
          "ORDER BY date DESC LIMIT 1");
        $row['policy'] = $insrow['policy_number'];
      }

      $rows[$insname . '|' . $ptname . '|' . $encounter] = $row;
    } // end for
  } // end not $INTEGRATED_AR

  ksort($rows);

  if ($_POST['form_export']) {
    echo "<textarea rows='35' cols='100' readonly>";
  }
  else if ($_POST['form_csvexport']) {
    // CSV headers:
    if (true) {
      echo '"Insurance",';
      echo '"Name",';
      echo '"Invoice",';
      echo '"DOS",';
      echo '"Referrer",';
      echo '"Charge",';
      echo '"Adjust",';
      echo '"Paid",';
      echo '"Balance",';
      echo '"IDays",';
      echo '"LADate"' . "\n";
    }
  }
  else {
?>

<div id="report_results">
<table>

 <thead>
<?php if ($is_due_ins) { ?>
  <th>&nbsp;<?php xl('Insurance','e')?></th>
<?php } ?>
<?php if (!$is_ins_summary) { ?>
  <th>&nbsp;<?php xl('Name','e')?></th>
<?php } ?>
<?php if ($form_cb_ssn) { ?>
  <th>&nbsp;<?php xl('SSN','e')?></th>
<?php } ?>
<?php if ($form_cb_dob) { ?>
  <th>&nbsp;<?php xl('DOB','e')?></th>
<?php } ?>
<?php if ($form_cb_pubpid) { ?>
  <th>&nbsp;<?php xl('ID','e')?></th>
<?php } ?>
<?php if ($form_cb_policy) { ?>
  <th>&nbsp;<?php xl('Policy','e')?></th>
<?php } ?>
<?php if ($form_cb_phone) { ?>
  <th>&nbsp;<?php xl('Phone','e')?></th>
<?php } ?>
<?php if ($form_cb_city) { ?>
  <th>&nbsp;<?php xl('City','e')?></th>
<?php } ?>
<?php if ($form_cb_ins1) { ?>
  <th>&nbsp;<?php xl('Primary Ins','e')?></th>
<?php } ?>
<?php if ($form_cb_referrer) { ?>
  <th>&nbsp;<?php xl('Referrer','e')?></th>
<?php } ?>
<?php if (!$is_ins_summary) { ?>
  <th>&nbsp;<?php xl('Invoice','e') ?></th>
  <th>&nbsp;<?php xl('Svc Date','e') ?></th>
<?php if ($form_cb_adate) { ?>
  <th>&nbsp;<?php xl('Act Date','e')?></th>
<?php } ?>
<?php } ?>
  <th align="right"><?php xl('Charge','e') ?>&nbsp;</th>
  <th align="right"><?php xl('Adjust','e') ?>&nbsp;</th>
  <th align="right"><?php xl('Paid','e') ?>&nbsp;</th>
<?php
    // Generate aging headers if appropriate, else balance header.
    if ($form_age_cols) {
      for ($c = 0; $c < $form_age_cols;) {
        echo "  <th class='dehead' align='right'>";
        echo $form_age_inc * $c;
        if (++$c < $form_age_cols) {
          echo "-" . ($form_age_inc * $c - 1);
        } else {
          echo "+";
        }
        echo "</th>\n";
      }
    }
    else {
?>
  <th align="right"><?php xl('Balance','e') ?>&nbsp;</th>
<?php
      }
?>
<?php if ($form_cb_idays) { ?>
  <th align="right"><?php xl('IDays','e')?>&nbsp;</th>
<?php } ?>
<?php if (!$is_ins_summary) { ?>
  <th align="center"><?php xl('Prv','e') ?></th>
  <th align="center"><?php xl('Sel','e') ?></th>
<?php } ?>
<?php if ($form_cb_err) { ?>
  <th>&nbsp;<?php xl('Error','e')?></th>
<?php } ?>
 </thead>

<?php
  } // end not export

  $ptrow = array('insname' => '', 'pid' => 0);
  $orow = -1;

  foreach ($rows as $key => $row) {
    list($insname, $ptname, $trash) = explode('|', $key);
    list($pid, $encounter) = explode(".", $row['invnumber']);

    if ($is_ins_summary && $insname != $ptrow['insname']) {
      endInsurance($ptrow);
      $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
      $ptrow = array('insname' => $insname, 'ptname' => $ptname, 'pid' => $pid, 'count' => 1);
      foreach ($row as $key => $value) $ptrow[$key] = $value;
      $ptrow['agedbal'] = array();
    }
    else if (!$is_ins_summary && ($insname != $ptrow['insname'] || $pid != $ptrow['pid'])) {
      // For the report, this will write the patient totals.  For the
      // collections export this writes everything for the patient:
      endPatient($ptrow);
      $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
      $ptrow = array('insname' => $insname, 'ptname' => $ptname, 'pid' => $pid, 'count' => 1);
      foreach ($row as $key => $value) $ptrow[$key] = $value;
      $ptrow['agedbal'] = array();
    } else {
      $ptrow['amount']      += $row['amount'];
      $ptrow['paid']        += $row['paid'];
      $ptrow['charges']     += $row['charges'];
      $ptrow['adjustments'] += $row['adjustments'];
      ++$ptrow['count'];
    }

    // Compute invoice balance and aging column number, and accumulate aging.
    $balance = $row['charges'] + $row['adjustments'] - $row['paid'];
    if ($form_age_cols) {
      $agedate = $is_ageby_lad ? $row['ladate'] : $row['dos'];
      $agetime = mktime(0, 0, 0, substr($agedate, 5, 2),
        substr($agedate, 8, 2), substr($agedate, 0, 4));
      $days = floor((time() - $agetime) / (60 * 60 * 24));
      $agecolno = min($form_age_cols - 1, max(0, floor($days / $form_age_inc)));
      $ptrow['agedbal'][$agecolno] += $balance;
    }

    if (!$is_ins_summary && !$_POST['form_export'] && !$_POST['form_csvexport']) {
      $in_collections = stristr($row['billnote'], 'IN COLLECTIONS') !== false;
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
<?php
      if ($ptrow['count'] == 1) {
        if ($is_due_ins) {
          echo "  <td class='detail'>&nbsp;$insname</td>\n";
        }
        echo "  <td class='detail'>&nbsp;$ptname</td>\n";
        if ($form_cb_ssn) {
          echo "  <td class='detail'>&nbsp;" . $row['ss'] . "</td>\n";
        }
        if ($form_cb_dob) {
          echo "  <td class='detail'>&nbsp;" . $row['DOB'] . "</td>\n";
        }
        if ($form_cb_pubpid) {
          echo "  <td class='detail'>&nbsp;" . $row['pubpid'] . "</td>\n";
        }
        if ($form_cb_policy) {
          echo "  <td class='detail'>&nbsp;" . $row['policy'] . "</td>\n";
        }
        if ($form_cb_phone) {
          echo "  <td class='detail'>&nbsp;" . $row['phone'] . "</td>\n";
        }
        if ($form_cb_city) {
          echo "  <td class='detail'>&nbsp;" . $row['city'] . "</td>\n";
        }
        if ($form_cb_ins1) {
          echo "  <td class='detail'>&nbsp;" . $row['ins1'] . "</td>\n";
        }
        if ($form_cb_referrer) {
          echo "  <td class='detail'>&nbsp;" . $row['referrer'] . "</td>\n";
        }
      } else {
        echo "  <td class='detail' colspan='$initial_colspan'>";
        echo "&nbsp;</td>\n";
      }
?>
  <td class="detail">
   &nbsp;<a href="../billing/sl_eob_invoice.php?id=<?php echo $row['id'] ?>"
    target="_blank"><?php echo $row['invnumber'] ?></a>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['dos']; ?>
  </td>
<?php if ($form_cb_adate) { ?>
  <td class='detail'>
   &nbsp;<?php echo $row['ladate']; ?>
  </td>
<?php } ?>
  <td class="detail" align="right">
   <?php bucks($row['charges']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['adjustments']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['paid']) ?>&nbsp;
  </td>
<?php
      if ($form_age_cols) {
        for ($c = 0; $c < $form_age_cols; ++$c) {
          echo "  <td class='detail' align='right'>";
          if ($c == $agecolno) {
            bucks($balance);
          }
          echo "&nbsp;</td>\n";
        }
      }
      else {
?>
  <td class="detail" align="right"><?php bucks($balance) ?>&nbsp;</td>
<?php
      } // end else
?>
<?php
      if ($form_cb_idays) {
        echo "  <td class='detail' align='right'>";
        echo $row['inactive_days'] . "&nbsp;</td>\n";
      }
?>
  <td class="detail" align="center">
   <?php echo $row['duncount'] ? $row['duncount'] : "&nbsp;" ?>
  </td>
  <td class="detail" align="center">
<?php
      if ($ptrow['count'] == 1) {
        if ($in_collections) {
          echo "   <b><font color='red'>IC</font></b>\n";
        } else {
          echo "   <input type='checkbox' name='form_cb[" . $row['custid'] . "]' />\n";
        }
      } else {
        echo "   &nbsp;\n";
      }
?>
  </td>
<?php
      if ($form_cb_err) {
        echo "  <td class='detail'>&nbsp;";
        echo $row['billing_errmsg'] . "</td>\n";
      }
?>
 </tr>
<?php
    } // end not export and not insurance summary

    else if ($_POST['form_csvexport']) {
      // The CSV detail line is written here.
      $balance = $row['charges'] + $row['adjustments'] - $row['paid'];
      // echo '"' . $insname                             . '",';
      echo '"' . $row['ins1']                         . '",';
      echo '"' . $ptname                              . '",';
      echo '"' . $row['invnumber']                    . '",';
      echo '"' . $row['dos']                          . '",';
      echo '"' . $row['referrer']                     . '",';
      echo '"' . sprintf('%.2f', $row['charges'])     . '",';
      echo '"' . sprintf('%.2f', $row['adjustments']) . '",';
      echo '"' . sprintf('%.2f', $row['paid'])        . '",';
      echo '"' . sprintf('%.2f', $balance)            . '",';
      echo '"' . $row['inactive_days']                . '",';
      echo '"' . $row['ladate']                       . '"' . "\n";
    } // end $form_csvexport

  } // end loop

  if ($is_ins_summary)
    endInsurance($ptrow);
  else
    endPatient($ptrow);

  if ($_POST['form_export']) {
    echo "</textarea>\n";
    $alertmsg .= "$export_patient_count patients representing $" .
      sprintf("%.2f", $export_dollars) . " have been exported ";
    if ($_POST['form_without']) {
      $alertmsg .= "but NOT flagged as in collections.";
    } else {
      $alertmsg .= "AND flagged as in collections.";
    }
  }
  else if ($_POST['form_csvexport']) {
    // echo "</textarea>\n";
    // $alertmsg .= "$export_patient_count patients representing $" .
    //   sprintf("%.2f", $export_dollars) . " have been exported.";
  }
  else {
    echo " <tr bgcolor='#ffffff'>\n";
    if ($is_ins_summary) {
      echo "  <td class='dehead'>&nbsp;" . xl('Report Totals') . ":</td>\n";
    } else {
      echo "  <td class='detail' colspan='$initial_colspan'>\n";
      echo "   &nbsp;</td>\n";
      echo "  <td class='dehead' colspan='" . ($final_colspan - 3) .
        "'>&nbsp;" . xl('Report Totals') . ":</td>\n";
    }
    echo "  <td class='dehead' align='right'>&nbsp;" .
      sprintf("%.2f", $grand_total_charges) . "&nbsp;</td>\n";
    echo "  <td class='dehead' align='right'>&nbsp;" .
      sprintf("%.2f", $grand_total_adjustments) . "&nbsp;</td>\n";
    echo "  <td class='dehead' align='right'>&nbsp;" .
      sprintf("%.2f", $grand_total_paid) . "&nbsp;</td>\n";
    if ($form_age_cols) {
      for ($c = 0; $c < $form_age_cols; ++$c) {
        echo "  <td class='dehead' align='right'>" .
          sprintf("%.2f", $grand_total_agedbal[$c]) . "&nbsp;</td>\n";
      }
    }
    else {
      echo "  <td class='dehead' align='right'>" .
        sprintf("%.2f", $grand_total_charges +
        $grand_total_adjustments - $grand_total_paid) . "&nbsp;</td>\n";
    }
    if ($form_cb_idays) echo "  <td class='detail'>&nbsp;</td>\n";
    if (!$is_ins_summary) echo "  <td class='detail' colspan='2'>&nbsp;</td>\n";
    if ($form_cb_err) echo "  <td class='detail'>&nbsp;</td>\n";
    echo " </tr>\n";
    echo "</table>\n";
	echo "</div>\n";
  }
} // end if form_search

if (!$INTEGRATED_AR) SLClose();

if (!$_POST['form_csvexport']) {
  if (!$_POST['form_export']) {
?>

<div style='float;margin-top:5px'>

<a href='javascript:;' class='css_button'  onclick='checkAll(true)'><span><?php xl('Select All','e'); ?></span></a>
<a href='javascript:;' class='css_button'  onclick='checkAll(false)'><span><?php xl('Clear All','e'); ?></span></a>
<a href='javascript:;' class='css_button' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
	<span><?php xl('Export Selected as CSV','e'); ?></span>
</a>
<a href='javascript:;' class='css_button' onclick='$("#form_export").attr("value","true"); $("#theform").submit();'>
	<span><?php xl('Export Selected to Collections','e'); ?></span>
</a>
</div>

<div style='float:left'>
<input type='checkbox' name='form_without' value='1' /> <?php xl('Without Update','e') ?>
</div>

<?php
  } // end not export
?>
</form>
</center>
<script language="JavaScript">
<?php
  if ($alertmsg) {
    echo "alert('" . htmlentities($alertmsg) . "');\n";
  }
?>
</script>
</body>
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_date", ifFormat:"%m/%d/%Y", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%m/%d/%Y", button:"img_to_date"});
</script>
</html>
<?php
} // end not form_csvexport
?>
