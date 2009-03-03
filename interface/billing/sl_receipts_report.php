<?php
  // Copyright (C) 2006-2008 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This module was written for one of my clients to report on cash
  // receipts by practitioner.  It is not as complete as it should be
  // but I wanted to make the code available to the project because
  // many other practices have this same need. - rod@sunsetsystems.com

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");
  include_once("../../library/acl.inc");

  // This determines if a particular procedure code corresponds to receipts
  // for the "Clinic" column as opposed to receipts for the practitioner.  Each
  // practice will have its own policies in this regard, so you'll probably
  // have to customize this function.  If you use the "fee sheet" encounter
  // form then the code below may work for you.
  //
  include_once("../forms/fee_sheet/codes.php");
  function is_clinic($code) {
    global $bcodes;
    $i = strpos($code, ':');
    if ($i) $code = substr($code, 0, $i);
    return ($bcodes['CPT4'][xl('Lab')][$code]     ||
      $bcodes['CPT4'][xl('Immunizations')][$code] ||
      $bcodes['HCPCS'][xl('Therapeutic Injections')][$code]);
  }

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  $INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;

  if (!$INTEGRATED_AR) {
    SLConnect();
    $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
    if ($sl_err) die($sl_err);
  }

  $form_use_edate  = $_POST['form_use_edate'];
  $form_cptcode    = trim($_POST['form_cptcode']);
  $form_icdcode    = trim($_POST['form_icdcode']);
  $form_procedures = empty($_POST['form_procedures']) ? 0 : 1;
  $form_from_date  = fixDate($_POST['form_from_date'], date('Y-m-01'));
  $form_to_date    = fixDate($_POST['form_to_date'], date('Y-m-d'));
  $form_facility   = $_POST['form_facility'];
?>
<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>
<title><?xl('Cash Receipts by Provider','e')?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?xl('Cash Receipts by Provider','e')?></h2>

<form method='post' action='sl_receipts_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
<?php
 // Build a drop-down list of facilities.
 //
 $query = "SELECT id, name FROM facility ORDER BY name";
 $fres = sqlStatement($query);
 echo "   <select name='form_facility'>\n";
 echo "    <option value=''>-- All Facilities --\n";
 while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "    <option value='$facid'";
  if ($facid == $form_facility) echo " selected";
  echo ">" . $frow['name'] . "\n";
 }
 echo "   </select>\n";
?>
<?php
	if (acl_check('acct', 'rep_a')) {
		// Build a drop-down list of providers.
		//
		$query = "select id, lname, fname from users where " .
			"authorized = 1 order by lname, fname";
		$res = sqlStatement($query);
		echo "   &nbsp;<select name='form_doctor'>\n";
		echo "    <option value=''>-- All Providers --\n";
		while ($row = sqlFetchArray($res)) {
			$provid = $row['id'];
			echo "    <option value='$provid'";
			if ($provid == $_POST['form_doctor']) echo " selected";
			echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
		}
		echo "   </select>\n";
	} else {
		echo "<input type='hidden' name='form_doctor' value='" . $_SESSION['authUserID'] . "'>";
	}
?>
   &nbsp;<select name='form_use_edate'>
    <option value='0'><?php xl('Payment Date','e'); ?></option>
    <option value='1'<?php if ($form_use_edate) echo ' selected' ?>><?php xl('Invoice Date','e'); ?></option>
   </select>
   &nbsp;<?xl('From:','e')?>

   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;To:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   <?php if (!$GLOBALS['simplified_demographics']) echo '&nbsp;' . xl('CPT') . ':'; ?>
   <input type='text' name='form_cptcode' size='5' value='<? echo $form_cptcode; ?>'
    title='<?php xl('Optional procedure code','e'); ?>'
    <?php if ($GLOBALS['simplified_demographics']) echo "style='display:none'"; ?>>
   <?php if (!$GLOBALS['simplified_demographics']) echo '&nbsp;' . xl('ICD') . ':'; ?>
   <input type='text' name='form_icdcode' size='5' value='<? echo $form_icdcode; ?>'
    title='<?php xl('Enter a diagnosis code to exclude all invoices not containing it','e'); ?>'
    <?php if ($GLOBALS['simplified_demographics']) echo "style='display:none'"; ?>>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<? if ($_POST['form_details']) echo " checked"; ?>><?xl('Details','e')?>
   &nbsp;
   <input type='checkbox' name='form_procedures' value='1'<? if ($form_procedures) echo " checked"; ?>><?xl('Procedures','e')?>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?xl('Refresh','e')?>">
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <?php xl('Practitioner','e') ?>
  </td>
  <td class="dehead">
   <?php xl('Date','e') ?>
  </td>
<?php if ($form_procedures) { ?>
  <td class="dehead">
   <?php xl('Invoice','e') ?>
  </td>
<?php } ?>
<?php if ($form_cptcode) { ?>
  <td class="dehead" align='right'>
   <?php xl('InvAmt','e') ?>
  </td>
<?php } ?>
<?php if ($form_cptcode) { ?>
  <td class="dehead">
   <?php xl('Insurance','e') ?>
  </td>
<?php } ?>
<?php if ($form_procedures) { ?>
  <td class="dehead">
   <?php xl('Procedure','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Prof.','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Clinic','e') ?>
  </td>
<?php } else { ?>
  <td class="dehead" align="right">
   <?php xl('Received','e') ?>
  </td>
<?php } ?>
 </tr>
<?php
  if ($_POST['form_refresh']) {
    $form_doctor = $_POST['form_doctor'];
    $arows = array();

    if ($INTEGRATED_AR) {
      $ids_to_skip = array();
      $irow = 0;

      // Get copays.  These are ignored if a CPT code was specified.
      //
      if (!$form_cptcode) {
        $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, b.code, b.modifier, " .
          "fe.date, fe.id AS trans_id, u.id AS docid " .
          "FROM billing AS b " .
          "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
          "JOIN forms AS f ON f.pid = b.pid AND f.encounter = b.encounter AND f.formdir = 'newpatient' " .
          "LEFT OUTER JOIN users AS u ON u.username = f.user " .
          "WHERE b.code_type = 'COPAY' AND b.activity = 1 AND " .
          "fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59'";
        // If a facility was specified.
        if ($form_facility) {
          $query .= " AND fe.facility_id = '$form_facility'";
        }
        // If a doctor was specified.
        if ($form_doctor) {
          $query .= " AND u.id = '$form_doctor'";
        }
        //
        $res = sqlStatement($query);
        while ($row = sqlFetchArray($res)) {
          $trans_id = $row['trans_id'];
          $thedate = substr($row['date'], 0, 10);
          $patient_id = $row['pid'];
          $encounter_id = $row['encounter'];
          //
          if (!empty($ids_to_skip[$trans_id])) continue;
          //
          // If a diagnosis code was given then skip any invoices without
          // that diagnosis.
          if ($form_icdcode) {
            $tmp = sqlQuery("SELECT count(*) AS count FROM billing WHERE " .
              "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
              "code_type = 'ICD9' AND code LIKE '$form_icdcode' AND " .
              "activity = 1");
            if (empty($tmp['count'])) {
              $ids_to_skip[$trans_id] = 1;
              continue;
            }
          }
          //
          $key = sprintf("%08u%s%08u%08u%06u", $row['docid'], $thedate,
            $patient_id, $encounter_id, ++$irow);
          $arows[$key] = array();
          $arows[$key]['transdate'] = $thedate;
          $arows[$key]['amount'] = $row['fee'];
          $arows[$key]['docid'] = $row['docid'];
          $arows[$key]['project_id'] = 0;
          $arows[$key]['memo'] = '';
          $arows[$key]['invnumber'] = "$patient_id.$encounter_id";
        } // end while
      } // end copays (not $form_cptcode)

      // Get ar_activity (having payments), form_encounter, forms, users, optional ar_session
      $query = "SELECT a.pid, a.encounter, a.post_time, a.code, a.modifier, a.pay_amount, " .
        "fe.date, fe.id AS trans_id, u.id AS docid, s.deposit_date, s.payer_id " .
        "FROM ar_activity AS a " .
        "JOIN form_encounter AS fe ON fe.pid = a.pid AND fe.encounter = a.encounter " .
        "JOIN forms AS f ON f.pid = a.pid AND f.encounter = a.encounter AND f.formdir = 'newpatient' " .
        "LEFT OUTER JOIN users AS u ON u.username = f.user " .
        "LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id " .
        "WHERE a.pay_amount != 0 AND ( " .
        "a.post_time >= '$form_from_date 00:00:00' AND a.post_time <= '$form_to_date 23:59:59' " .
        "OR fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' " .
        "OR s.deposit_date >= '$form_from_date' AND s.deposit_date <= '$form_to_date' )";
      // If a procedure code was specified.
      if ($form_cptcode) $query .= " AND a.code = '$form_cptcode'";
      // If a facility was specified.
      if ($form_facility) $query .= " AND fe.facility_id = '$form_facility'";
      // If a doctor was specified.
      if ($form_doctor) $query .= " AND u.id = '$form_doctor'";
      //
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        $trans_id = $row['trans_id'];
        $patient_id = $row['pid'];
        $encounter_id = $row['encounter'];
        //
        if (!empty($ids_to_skip[$trans_id])) continue;
        //
        if ($form_use_edate) {
          $thedate = substr($row['date'], 0, 10);
        } else {
          if (!empty($row['deposit_date']))
            $thedate = $row['deposit_date'];
          else
            $thedate = substr($row['post_time'], 0, 10);
        }
        if (strcmp($thedate, $form_from_date) < 0 || strcmp($thedate, $form_to_date) > 0) continue;
        //
        // If a diagnosis code was given then skip any invoices without
        // that diagnosis.
        if ($form_icdcode) {
          $tmp = sqlQuery("SELECT count(*) AS count FROM billing WHERE " .
            "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
            "code_type = 'ICD9' AND code LIKE '$form_icdcode' AND " .
            "activity = 1");
          if (empty($tmp['count'])) {
            $ids_to_skip[$trans_id] = 1;
            continue;
          }
        }
        //
        $key = sprintf("%08u%s%08u%08u%06u", $row['docid'], $thedate,
          $patient_id, $encounter_id, ++$irow);
        $arows[$key] = array();
        $arows[$key]['transdate'] = $thedate;
        $arows[$key]['amount'] = 0 - $row['pay_amount'];
        $arows[$key]['docid'] = $row['docid'];
        $arows[$key]['project_id'] = empty($row['payer_id']) ? 0 : $row['payer_id'];
        $arows[$key]['memo'] = $row['code'];
        $arows[$key]['invnumber'] = "$patient_id.$encounter_id";
      } // end while
    } // end $INTEGRATED_AR

    else {
      if ($form_cptcode) {
        $query = "SELECT acc_trans.amount, acc_trans.transdate, " .
          "acc_trans.memo, acc_trans.project_id, acc_trans.trans_id, " .
          "ar.invnumber, ar.employee_id, invoice.sellprice, invoice.qty " .
          "FROM acc_trans, ar, invoice WHERE " .
          "acc_trans.chart_id = $chart_id_cash AND " .
          "acc_trans.memo ILIKE '$form_cptcode' AND " .
          "ar.id = acc_trans.trans_id AND " .
          "invoice.trans_id = acc_trans.trans_id AND " .
          "invoice.serialnumber ILIKE acc_trans.memo AND " .
          "invoice.sellprice >= 0.00 AND " .
          "( invoice.description ILIKE 'CPT%' OR invoice.description ILIKE 'Proc%' ) AND ";
      }
      else {
        $query = "select acc_trans.amount, acc_trans.transdate, " .
          "acc_trans.memo, acc_trans.trans_id, " .
          "ar.invnumber, ar.employee_id from acc_trans, ar where " .
          "acc_trans.chart_id = $chart_id_cash and " .
          "ar.id = acc_trans.trans_id and ";
      }

      if ($form_use_edate) {
        $query .= "ar.transdate >= '$form_from_date' and " .
        "ar.transdate <= '$form_to_date'";
      } else {
        $query .= "acc_trans.transdate >= '$form_from_date' and " .
        "acc_trans.transdate <= '$form_to_date'";
      }

      $query .= " order by ar.invnumber";

      // echo "<!-- $query -->\n"; // debugging

      $t_res = SLQuery($query);
      if ($sl_err) die($sl_err);

      $docname     = "";
      $docnameleft = "";
      $docid       = 0;
      $doctotal1   = 0;
      $grandtotal1 = 0;
      $doctotal2   = 0;
      $grandtotal2 = 0;
      $last_trans_id = 0;
      $skipping      = false;

      for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
        $row = SLGetRow($t_res, $irow);

        list($patient_id, $encounter_id) = explode(".", $row['invnumber']);

        // Under some conditions we may skip invoices that matched the SQL query.
        //
        if ($row['trans_id'] == $last_trans_id) {
          if ($skipping) continue;
          // same invoice and not skipping, do nothing.
        } else { // new invoice
          $skipping = false;
          // If a diagnosis code was given then skip any invoices without
          // that diagnosis.
          if ($form_icdcode) {
            if (!SLQueryValue("SELECT count(*) FROM invoice WHERE " .
              "invoice.trans_id = '" . $row['trans_id'] . "' AND " .
              "( invoice.description ILIKE 'ICD9:$form_icdcode %' OR " .
              "invoice.serialnumber ILIKE 'ICD9:$form_icdcode' )"))
            {
              $skipping = true;
              continue;
            }
          }
          // If a facility was specified then skip invoices whose encounters
          // do not indicate that facility.
          if ($form_facility) {
            $tmp = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
              "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
              "facility_id = '$form_facility'");
            if (empty($tmp['count'])) {
              $skipping = true;
              continue;
            }
          }
          // Find out who the practitioner is.
          $tmp = sqlQuery("SELECT users.id, users.authorized FROM forms, users WHERE " .
            "forms.pid = '$patient_id' AND forms.encounter = '$encounter_id' AND " .
            "forms.formdir = 'newpatient' AND users.username = forms.user");
          $docid = empty($tmp['id']) ? 0 : $tmp['id'];
          if (empty($tmp['authorized'])) {
            $tmp = sqlQuery("SELECT users.id FROM billing, users WHERE " .
              "billing.pid = '$patient_id' AND billing.encounter = '$encounter_id' AND " .
              "billing.activity = 1 AND billing.fee > 0 AND " .
              "users.id = billing.provider_id AND users.authorized = 1 " .
              "ORDER BY billing.fee DESC, billing.id ASC LIMIT 1");
            if (!empty($tmp['id'])) $docid = $tmp['id'];
          }
          // If a practitioner was specified then skip other practitioners.
          if ($form_doctor) {
            if ($form_doctor != $docid) {
              $skipping = true;
              continue;
            }
          }
        } // end new invoice

        $row['docid'] = $docid;
        $key = sprintf("%08u%s%08u%08u%06u", $docid, $row['transdate'],
          $patient_id, $encounter_id, $irow);
        $arows[$key] = $row;
      }

    } // end not $INTEGRATED_AR

    ksort($arows);
    $docid = 0;

    foreach ($arows as $row) {

      // Get insurance company name
      $insconame = '';
      if ($form_cptcode && $row['project_id']) {
        $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE " .
          "id = '" . $row['project_id'] . "'");
        $insconame = $tmp['name'];
      }

      $amount1 = 0;
      $amount2 = 0;
      if ($form_procedures && is_clinic($row['memo']))
        $amount2 -= $row['amount'];
      else
        $amount1 -= $row['amount'];

      // if ($docid != $row['employee_id']) {
      if ($docid != $row['docid']) {
        if ($docid) {
          // Print doc totals.
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="<?php echo ($form_cptcode ? 4 : 2) + ($form_procedures ? 2 : 0); ?>">
   <? echo xl('Totals for ') . $docname ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($doctotal1) ?>
  </td>
<?php if ($form_procedures) { ?>
  <td class="dehead" align="right">
   <?php bucks($doctotal2) ?>
  </td>
<?php } ?>
 </tr>
<?
        }
        $doctotal1 = 0;
        $doctotal2 = 0;

        $docid = $row['docid'];
        $tmp = sqlQuery("SELECT lname, fname FROM users WHERE id = '$docid'");
        $docname = empty($tmp) ? 'Unknown' : $tmp['fname'] . ' ' . $tmp['lname'];

        $docnameleft = $docname;
      }

      if ($_POST['form_details']) {
?>

 <tr>
  <td class="detail">
   <?php echo $docnameleft; $docnameleft = "&nbsp;" ?>
  </td>
  <td class="detail">
   <?php echo $row['transdate'] ?>
  </td>
<?php if ($form_procedures) { ?>
  <td class="detail">
   <?php echo $row['invnumber'] ?>
  </td>
<?php } ?>
<?php
        if ($form_cptcode) {
          echo "  <td class='detail' align='right'>";
          if ($INTEGRATED_AR) {
            list($patient_id, $encounter_id) = explode(".", $row['invnumber']);
            $tmp = sqlQuery("SELECT SUM(fee) AS sum FROM billing WHERE " .
              "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
              "code = '$form_cptcode' AND activity = 1");
            bucks($tmp['sum']);
          }
          else {
            bucks($row['sellprice'] * $row['qty']);
          }
          echo "  </td>\n";
        }
?>
<?php if ($form_cptcode) { ?>
  <td class="detail">
   <?php echo $insconame ?>
  </td>
<?php } ?>
<?php if ($form_procedures) { ?>
  <td class="detail">
   <?php echo $row['memo'] ?>
  </td>
<?php } ?>
  <td class="detail" align="right">
   <?php bucks($amount1) ?>
  </td>
<?php if ($form_procedures) { ?>
  <td class="detail" align="right">
   <?php bucks($amount2) ?>
  </td>
<?php } ?>
 </tr>
<?
      } // end details
      $doctotal1   += $amount1;
      $doctotal2   += $amount2;
      $grandtotal1 += $amount1;
      $grandtotal2 += $amount2;
    }
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="<?php echo ($form_cptcode ? 4 : 2) + ($form_procedures ? 2 : 0); ?>">
   <?echo xl('Totals for ') . $docname ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($doctotal1) ?>
  </td>
<?php if ($form_procedures) { ?>
  <td class="dehead" align="right">
   <?php bucks($doctotal2) ?>
  </td>
<?php } ?>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="<?php echo ($form_cptcode ? 4 : 2) + ($form_procedures ? 2 : 0); ?>">
   <?php xl('Grand Totals','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($grandtotal1) ?>
  </td>
<?php if ($form_procedures) { ?>
  <td class="dehead" align="right">
   <?php bucks($grandtotal2) ?>
  </td>
<?php } ?>
 </tr>

<?
  }
  if (!$INTEGRATED_AR) SLClose();
?>

</table>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
