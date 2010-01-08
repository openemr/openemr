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
<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}
</style><link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php xl('Indigent Patients Report','e')?></title>

<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="JavaScript">

</script>

</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Indigent Patients','e'); ?></span>

<form method='post' action='indigent_patients_report.php' id='theform'>

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

<table>
 <tr>
  <td width='410px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
			   <?php xl('Visits From','e'); ?>:
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
</div> <!-- end of parameters -->

<div id="report_results">
<table>

 <thead bgcolor="#dddddd">
  <th>
   &nbsp;<?php xl('Patient','e')?>
  </th>
  <th>
   &nbsp;<?php xl('SSN','e')?>
  </th>
  <th>
   &nbsp;<?php xl('Invoice','e')?>
  </th>
  <th>
   &nbsp;<?php xl('Svc Date','e')?>
  </th>
  <th>
   &nbsp;<?php xl('Due Date','e')?>
  </th>
  <th align="right">
   <?php xl('Amount','e')?>&nbsp;
  </th>
  <th align="right">
   <?php xl('Paid','e')?>&nbsp;
  </th>
  <th align="right">
   <?php xl('Balance','e')?>&nbsp;
  </th>
 </thead>

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
   &nbsp;<?php xl('Totals','e'); ?>
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
</div>

</form>
<script>
<?php
	if ($alertmsg) {
		echo "alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
