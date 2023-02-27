<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_zero_pmt'])) $_POST['form_zero_pmt'] = '';
$rpt_lines = 0;

function ListLook($thisData, $thisList) {
  if($thisData == '') return ''; 
  $rret=sqlQuery("SELECT * FROM list_options WHERE list_id=? ".
        "AND option_id=?", array($thisList, $thisData));
	if($rret{'title'}) {
    $dispValue= $rret{'title'};
  } else {
    $dispValue= '* Not Found *';
  }
  return $dispValue;
}

function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function facilityTotals() {
	global $facility_id, $facility_desc, $facility_qty, $facility_pmt_total;
  global $prev_sort, $prev_sort_desc, $prev_facility, $prev_facility_desc;
	global $form_facility, $form_csvexport, $from_date, $to_date, $bgcolor;
	global $prim_sort, $prim_desc, $prim_sort_left, $prim_desc_left;
	global $form_zero_pmt;

	if(!$form_facility) {
		if($facility_id != $prev_facility && $prev_facility) {
     	if(!$form_csvexport) {
				?>
 				<tr bgcolor="#ddffff">
  				<td class="detail" colspan="3">
						<?php echo 'Total For: ',display_desc($prev_facility_desc); ?>&nbsp;
					</td>
					<td class="detail" colspan="3">&nbsp;</td>
  				<td align="right"><?php echo $facility_qty; ?></td>
  				<td align="right"><?php bucks($facility_pmt_total); ?></td>
				</tr>
				<?php if($facility_id && $facility_id != '^end^') { ?>
				<tr><td colspan="9">&nbsp;</td></tr>
				<tr>
  				<td class="detail" colspan="3">
						<?php echo display_desc($facility_desc); ?>&nbsp;</td>
					<td class="detail" colspan="6">&nbsp;</td>
				</tr>	
				<?php	
				}
			} else {
				// Need to provide the unapplied detail	
			}
   		$facility_pmt_total = $facility_qty = 0;
  		$prev_facility      = $facility_id;
  		$prev_facility_desc = $facility_desc;
    }
	}
	if(!$prev_facility) {
		if(!$form_csvexport) {
	?>
		<tr>
  		<td class="detail" colspan="3">
				<?php echo display_desc($facility_desc); ?>&nbsp;</td>
			<td class="detail" colspan="6">&nbsp;</td>
		</tr>	
	<?php
		}
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
	}
}

function primSortTotals() {
  global $prim_sort, $prim_desc, $prim_pmt_total, $prim_qty;
  global $prev_sort, $prev_sort_desc, $prim_sort_left, $prim_desc_left;
	global $form_details, $form_csvexport, $dtl_lines;

  if ($prim_sort != $prev_sort && $prev_sort) {
    // Print primary sort total.
    if ($form_csvexport) {
			// If we are printing details we don't total for spreadsheets
      if(!$form_details) {
        echo '"' . display_desc($prev_desc)  . '",';
        echo '"' . $prim_qty. '",';
        echo '"'; bucks($prim_pmt_total); echo '"' . "\n";
			}
    } else { 
			if(!$form_details) { ?>
 				<tr bgcolor="#ddffff">
  				<td class="detail"><?php echo display_desc($prev_sort_desc); ?>&nbsp;
					</td>
  				<td align="right"><?php echo $prim_qty; ?></td>
  				<td align="right"><?php bucks($prim_pmt_total); ?></td>
 				</tr>
			<?php
			} else {
				if($dtl_lines > 1) {
			?>
 				<tr bgcolor="#ddffff">
  				<td class="detail" colspan="4">
							<?php echo 'Total For: ',display_desc($prev_sort),'&nbsp;-&nbsp;',
								display_desc($prev_sort_desc); ?></td>
					<td class="detail" colspan="2">&nbsp;</td>
  				<td align="right"><?php echo $prim_qty; ?></td>
  				<td align="right"><?php bucks($prim_pmt_total); ?></td>
				</tr>
				<?php 
				}
				if($prim_sort && $prim_sort != '^end^') { ?>
				<!-- tr> <td class="detail" colspan="8">&nbsp;</td></tr -->	
			<?php
				}
      } // End not csv export
			// echo "Finished the total Line<br>\n";
    }
    $prim_pmt_total = $prim_qty = $dtl_lines = 0;
    $prev_sort      = $prim_sort;
    $prev_sort_desc = $prim_desc;
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
  } else if(!$prev_sort) {
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
	}
}

function thisLineItem() {
  global $prim_sort, $prim_desc, $prim_pmt_total, $prim_qty;
	global $grand_pmt_total, $grand_qty;
	global $facility_id, $facility_desc, $facility_qty, $facility_pmt_total;
  global $prev_sort, $prev_sort_desc, $prev_facility, $prev_facility_desc;
	global $prim_sort_left, $prim_desc_left, $dtl_lines, $from_date, $to_date;
	global $form_facility, $form_csvexport, $form_details, $primary_sort, $bgcolor;
	global $row, $form_zero_pmt;

  $row_pmt = sprintf('%01.2f', $row['pay_total']);

	primSortTotals();
	facilityTotals();

	$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";

  if($form_details) {
    if($form_csvexport) {
      echo '"' . display_desc($prim_desc) . '",';
      echo '"' . $row['created_time'] . '",';
      echo '"' . display_desc($row['patient_id']) . '",';
      echo '"' . display_desc($row['plast'].', '.$row['pfirst']) . '",';
      echo '"' . display_desc(ListLook($row['payment_method'], 'payment_method')) . '",';
      echo '"' . display_desc($row['reference'])  . '",';
      echo '"' . display_desc($row['payment_type']) . '",';
      echo '"'; bucks($row_pmt); echo '"' . "\n";
    } else {
?>

 <tr bgcolor="<?php echo $bgcolor; ?>">
  <td class="detail">
		<?php echo display_desc($prim_desc_left); $prim_desc_left = "&nbsp;"?>
	&nbsp;</td>
  <td><?php echo $row['created_time']; ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($row['patient_id']); ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($row['plast'].', '.$row['pfirst']); ?>&nbsp;</td>
  <td class="detail">
   <?php echo display_desc(ListLook($row['payment_method'], 'payment_method')); ?>&nbsp;
  </td>
  <td align="right"><?php echo display_desc($row['reference']); ?>&nbsp;</td>
  <td align="right"><?php echo display_desc($row['payment_type']); ?>&nbsp;</td>
  <td align="right"><?php bucks($row_pmt); ?></td>
 </tr>
<?php

    } // End not csv export
  } // end details
  $prim_pmt_total     += $row['pay_total'];
  $facility_pmt_total += $row['pay_total'];
  $grand_pmt_total    += $row['pay_total'];
  $prim_qty           ++;
  $facility_qty       ++;
  $grand_qty          ++;
	$prev_facility      =  $facility_id;
	$prev_facility_desc =  $facility_desc;
	$prev_sort          =  $prim_sort;
	$prev_sort_desc     =  $prim_desc;
	$dtl_lines++;
} // end line print function

if (! AclMain::aclCheckCore('acct', 'rep')) die(xl("Unauthorized access."));

$default_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(!isset($_POST['form_from_date'])) $_POST['form_from_date'] = $default_date;
if(!isset($_POST['form_to_date'])) $_POST['form_to_date'] = $default_date;
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility=isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_user=isset($_POST['form_user']) ? $_POST['form_user'] : '';
$form_details=isset($_POST['form_details']) ? $_POST['form_details'] : '1';
$form_csvexport = $_POST['form_csvexport'];
$form_zero_pmt = $_POST['form_zero_pmt'];
$form_details = 1;
$primary_sort = 'CPT';

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=pmts_by_user_date.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo '"Operator",';
		echo '"Date/Time",';
		echo '"PID",';
		echo '"Patient Name",';
    echo '"Reference",';
    echo '"Method",';
		echo '"Source",';
    echo '"Amount"' . "\n";
  } else {
		echo '"Date/Time",';
		echo '"PID",';
		echo '"Patient Name",';
    echo '"Amount"' . "\n";
  }
	// End of Export
} else {
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

<title><?php echo xl('Cash Receipts by Facility') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Cash Receipts by Facility/Operator'); ?></span>

<form method='post' action='cash_by_operator_facility.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='750px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
				<?php echo xl('Facility'); ?>:
			</td>
			<td>
			<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?>
			</td>
			<td class='label'>
			   <?php echo xl('From'); ?>:
			</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo xl('Click here to choose a date'); ?>'>
			</td>
			<td class='label'>
			   <?php echo xl('To'); ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo xl('Click here to choose a date'); ?>'>
			</td>
		</tr>
		<tr>
			<td class='label'>
				<?php echo xl('Operator'); ?>:
			</td>
      <td style='width: 18%;'><?php
        // Build a drop-down list of providers.
        $query = "SELECT id, username, lname, fname FROM users " .
					"WHERE username!='' AND active='1' ORDER BY lname, fname";
        $ures = sqlStatement($query);

        echo "   <select name='form_user' id='form_user'>\n";
        echo "    <option value=''";
				if($form_user == '') { echo " selected"; }
				echo ">-- " . xl('All') . " --</option>\n";
        while ($urow = sqlFetchArray($ures)) {
          $provid = $urow['id'];
          echo "    <option value='$provid'";
          if ($provid == $form_user) echo " selected";
          echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
        }
        echo "   </select>\n";
        ?></td>
			<td colspan="2" class="label">
			   <input type='checkbox' name="form_zero_pmt" id="form_zero_pmt" <?php if($form_zero_pmt) echo 'checked'; ?>>&nbsp;&nbsp;<label for="form_zero_pmt"><?php echo xl('Include Zero $ Pmts'); ?></label>
			</td>
			<!--td>
			   <input type='checkbox' name='form_details'<?php // if ($form_details) echo ' checked'; ?>>
			   <?php // xl('Details','e'); ?>
			</td -->
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
					<span><?php echo xl('Submit'); ?></span></a>

					<?php if ($_POST['form_refresh'] || $_POST['form_csvexport']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span><?php echo xl('Print'); ?></span></a>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
						<span><?php echo xl('CSV Export'); ?></span></a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div> <!-- end of parameters -->

<?php
	if($_POST['form_refresh']) {
?>
	<div id="report_results">
	<table >
 	<thead>
  <th> <?php echo xl('Operatory'); ?> </th>
  <th> <?php echo xl('Date/Time'); ?> </th>
  <th> <?php echo xl('PID'); ?> </th>
  <th> <?php echo xl('Patient Name'); ?> </th>
  <th> <?php echo xl('Reference'); ?> </th>
  <th> <?php echo xl('Method'); ?> </th>
  <th> <?php echo xl('Source'); ?> </th>
  <th align="right"> <?php echo xl('Amount'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;

  $prim_sort = $prim_desc = $facility_id = $bgcolor = '';
	$prim_pmt_total = $prim_qty = 0;
	$facility_pmt_total = $facility_qty = 0;
	$grand_pmt_total = $grand_qty = 0;
	$dtl_lines = $rpt_lines = 0;
	$prev_sort = $prev_sort_desc = $prev_facility= $prev_facility_desc = '';

	$query = 	"SELECT ar.*, " . 
		"pat.lname AS plast, pat.fname AS pfirst, pat.mname AS pmi, " .
		"users.lname AS ulast, users.fname AS ufirst, users.mname AS umi, " .
		"users.facility_id, " .
		"facility.name ".
		"FROM ar_session AS ar " .
		"LEFT JOIN patient_data AS pat ON ar.patient_id = pat.pid " .
		"LEFT JOIN users ON ar.user_id = users.id " .
		"LEFT JOIN facility ON users.facility_id = facility.id " .
     "WHERE ar.created_time >= '$from_date 00:00:00' AND " .
		"ar.created_time <= '$to_date 23:59:59' " .
		"AND payment_type = 'patient' ";
  // If a facility was specified.
  if ($form_facility) $query .= " AND users.facility_id = '$form_facility'";
  if ($form_user) $query .= " AND ar.user_id = '$form_user'";
	$query .= " ORDER BY users.facility_id, ar.user_id, ar.created_time";
  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
		$prim_sort = $row['user_id'];
		$prim_desc = $row['ulast'].','.$row['ufirst'];
		$facility_id = $row['facility_id'];
		$facility_desc = $row['name'];
    thisLineItem();
		$rpt_lines++;
  }

	$prim_sort = '^end^';
	$facility_id = '^end^';
	primSortTotals();
	facilityTotals();

	if(!$form_user && !$form_csvexport) {
	?>
 	<tr bgcolor="#ddffff">
 	 <td class="detail" colspan="6"> <?php echo xl('Grand Total'); ?> </td>
 	 <td align="right"> <?php echo $grand_qty; ?> </td>
 	 <td align="right"> <?php bucks($grand_pmt_total); ?> </td>
 	</tr>

<?php
	}
}

if(!$_POST['form_csvexport']) {
?>

</table>
</div> <!-- report results -->
	<?php if(!$rpt_lines) { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
	<?php } ?>

</form>

</body>

<!-- stuff for the popup calendar -->
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
} // End not csv export
?>
