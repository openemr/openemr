<?php
/**
 * This is the Indigent Patients Report.  It displays a summary of
 * encounters within the specified time period for patients without
 * insurance.
 *
 *  Copyright (C) 2005-2015 Rod Roark <rod@sunsetsystems.com>
 *  Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Core\Header;
require_once("../globals.php");
require_once("$srcdir/patient.inc");

$alertmsg = '';

function bucks($amount) {
  if ($amount) return oeFormatMoney($amount);
  return "";
}

$form_start_date = fixDate($_POST['form_start_date'], date("Y-01-01"));
$form_end_date   = fixDate($_POST['form_end_date'], date("Y-m-d"));

?>
<html>
<head>

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

</style>

<?php Header::setupHeader('datetime-picker'); ?>

<title><?php xl('Indigent Patients Report','e')?></title>

<script language="JavaScript">

 $(document).ready(function() {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
 });

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
			<td class='control-label'>
			   <?php xl('Visits From','e'); ?>:
			</td>
			<td>
			   <input type='text' class='datepicker form-control' name='form_start_date' id="form_start_date" size='10' value='<?php echo $form_start_date ?>'
				title='yyyy-mm-dd'>
			</td>
			<td class='control-label'>
			   <?php xl('To','e'); ?>:
			</td>
			<td>
			   <input type='text' class='datepicker form-control' name='form_end_date' id="form_end_date" size='10' value='<?php echo $form_end_date ?>'
				title='yyyy-mm-dd'>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div class="text-center">
          <div class="btn-group" role="group">
					  <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
						  <?php echo xlt('Submit'); ?>
					  </a>
					  <?php if ($_POST['form_refresh']) { ?>
					    <a href='#' class='btn btn-default btn-print' id='printbutton'>
							  <?php echo xlt('Print'); ?>
					    </a>
					  <?php } ?>
          </div>
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
  if ($_POST['form_refresh']) {

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
   &nbsp;<?php  echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>
  </td>
  <td class="detail">
   &nbsp;<?php  echo oeFormatShortDate($inv_duedate) ?>
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
