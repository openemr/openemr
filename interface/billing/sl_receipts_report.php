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
    return ($bcodes['CPT4'][xl('Lab')][$code]     ||
      $bcodes['CPT4'][xl('Immunizations')][$code] ||
      $bcodes['HCPCS'][xl('Therapeutic Injections')][$code]);
  }

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  SLConnect();

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
<?php html_header_show();?>
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
<?
  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);

  if ($_POST['form_refresh']) {
    $form_doctor = $_POST['form_doctor'];

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

    if ($form_doctor) {
      $tmp = sqlQuery("select foreign_id from integration_mapping where " .
        "foreign_table = 'salesman' and local_id = $form_doctor");
      // $emplid = SLQueryValue("select id from employee where employeenumber = " .
      //   $tmp['foreign_id']);
      $emplid = $tmp['foreign_id'];
      $query .= " and ar.employee_id = $emplid";
    }

    $query .= " order by ar.employee_id, acc_trans.transdate, ar.invnumber, acc_trans.memo";

    echo "<!-- $query -->\n";

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
          list($patient_id, $encounter_id) = explode(".", $row['invnumber']);
          $tmp = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
            "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
            "facility_id = '$form_facility'");
          if (empty($tmp['count'])) {
            $skipping = true;
            continue;
          }
        }
      }

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

      if ($docid != $row['employee_id']) {
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
        $docid = $row['employee_id'];
        $docname = SLQueryValue("select name from employee where id = $docid");
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
<?php if ($form_cptcode) { ?>
  <td class="detail" align='right'>
   <?php bucks($row['sellprice'] * $row['qty']) ?>
  </td>
<?php } ?>
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
      }
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
  SLClose();
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
