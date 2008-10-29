<?php
// Copyright (C) 2005, 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is the Indigent Patients Report.  It displays a summary of
// encounters within the specified time period for patients without
// insurance.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/sql-ledger.inc");

$alertmsg = '';

function bucks($amount) {
  if ($amount) return sprintf("%.2f", $amount);
  return "";
}

$form_start_date = fixDate($_POST['form_start_date'], date("Y-01-01"));
$form_end_date   = fixDate($_POST['form_end_date'], date("Y-m-d"));

$INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;

if (!$INTEGRATED_AR) SLConnect();
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php xl('Indigent Patients Report','e')?></title>

<script language="JavaScript">

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h3>Indigent Patients Report</h3>

<form method='post' action='indigent_patients_report.php'>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <?php xl('Start Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_start_date' size='10' value='<?php  echo $form_start_date ?>'
    title='<?php xl("Beginning date of service yyyy-mm-dd","e")?>'>
  </td>
  <td>
   <?php xl('End Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_end_date' size='10' value='<?php  echo $form_end_date ?>'
    title='<?php xl("Ending date of service yyyy-mm-dd","e")?>'>
  </td>
  <td>
   <input type='submit' name='form_search' value='<?php xl("Search","e")?>'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?php xl('Patient','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('SSN','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Invoice','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Svc Date','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Due Date','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Amount','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Paid','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Balance','e')?>&nbsp;
  </td>
 </tr>
<?php 
  if ($_POST['form_search']) {

    $where = "";

    if ($form_start_date) {
      $where .= " AND e.date >= '$form_start_date'";
    }
    if ($form_end_date) {
      $where .= " AND e.date <= '$form_end_date'";
    }

    $rez = sqlStatement("SELECT " .
      "e.date, e.encounter, p.pid, p.lname, p.fname, p.mname, p.ss " .
      "FROM form_encounter AS e, patient_data AS p, insurance_data AS i " .
      "WHERE p.pid = e.pid AND i.pid = e.pid AND i.type = 'primary' " .
      "AND i.provider = ''$where " .
      "ORDER BY p.lname, p.fname, p.mname, p.pid, e.date"
    );

    $total_amount = 0;
    $total_paid   = 0;

    for ($irow = 0; $row = sqlFetchArray($rez); ++$irow) {
      $patient_id = $row['pid'];
      $encounter_id = $row['encounter'];
      $invnumber = $row['pid'] . "." . $row['encounter'];

      if ($INTEGRATED_AR) {
        $inv_duedate = '';
        $arow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id'");
        $inv_amount = $arow['amount'];
        $arow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
          "activity = 1 AND code_type != 'COPAY'");
        $inv_amount += $arow['amount'];
        $arow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
          "activity = 1 AND code_type = 'COPAY'");
        $inv_paid = 0 - $arow['amount'];
        $arow = sqlQuery("SELECT SUM(pay_amount) AS pay, " .
          "sum(adj_amount) AS adj FROM ar_activity WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id'");
        $inv_paid   += $arow['pay'];
        $inv_amount -= $arow['adj'];
      }
      else {
        $ares = SLQuery("SELECT duedate, amount, paid FROM ar WHERE " .
          "ar.invnumber = '$invnumber'");
        if ($sl_err) die($sl_err);
        if (SLRowCount($ares) == 0) continue;
        $arow = SLGetRow($ares, 0);
        $inv_amount  = $arow['amount'];
        $inv_paid    = $arow['paid'];
        $inv_duedate = $arow['duedate'];
      }
      $total_amount += bucks($inv_amount);
      $total_paid   += bucks($inv_paid);

      $bgcolor = (($irow & 1) ? "#ffdddd" : "#ddddff");
?>
 <tr bgcolor='<?php  echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<?php  echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php  echo $row['ss'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php  echo $invnumber ?></a>
  </td>
  <td class="detail">
   &nbsp;<?php  echo substr($row['date'], 0, 10) ?>
  </td>
  <td class="detail">
   &nbsp;<?php  echo $inv_duedate ?>
  </td>
  <td class="detail" align="right">
   <?php  echo bucks($inv_amount) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  echo bucks($inv_paid) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  echo bucks($inv_amount - $inv_paid) ?>&nbsp;
  </td>
 </tr>
<?php 
    }
?>
 <tr bgcolor='#dddddd'>
  <td class="detail">
   &nbsp;Totals
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail" align="right">
   <?php  echo bucks($total_amount) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  echo bucks($total_paid) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  echo bucks($total_amount - $total_paid) ?>&nbsp;
  </td>
 </tr>
<?php 
  }
  if (!$INTEGRATED_AR) SLClose();
?>

</table>

</form>
</center>
<script>
<?php 
	if ($alertmsg) {
		echo "alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
