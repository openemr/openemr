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
require_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_zero_pmt'])) $_POST['form_zero_pmt'] = '';
if(!isset($_POST['form_diff_doc'])) $_POST['form_diff_doc'] = '';
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_no_uac'])) $_POST['form_no_uac'] = '';
if(!isset($_POST['form_contract'])) $_POST['form_contract'] = '';
if(!isset($_POST['form_method'])) $_POST['form_method'] = 'all';
if(!isset($_POST['form_source'])) $_POST['form_source'] = 'all';
if(!isset($_POST['form_memo'])) $_POST['form_memo'] = '';
if(!isset($_POST['form_date_mode'])) $_POST['form_date_mode'] = 'create';
$rpt_lines = 0;
if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';
set_time_limit(0);

function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function GetAllUnapplied($user='') {
	global $form_zero_pmt, $from_date, $to_date, $form_provider, $form_source;
  $all = array();
	if(!$user) return($all);
	$binds = array();
	$from_date_time = $from_date . ' 00:00:00';
	$to_date_time = $to_date . ' 23:59:59';
  $sql = "SELECT ar_session.*, ins.name, " .
			"pat.lname, pat.fname, pat.mname, pat.genericval2, " .
			"(SELECT SUM(ar_activity.pay_amount) FROM ar_activity WHERE " .
			"ar_activity.session_id = ar_session.session_id AND " .
			"ar_activity.deleted = 0) AS applied " .
      "FROM ar_session " .
      "LEFT JOIN insurance_companies AS ins on ar_session.payer_id = ins.id " .
      "LEFT JOIN patient_data AS pat on ar_session.patient_id = pat.pid " .
      "WHERE ";
	if($_POST['form_date_mode'] == 'service') {
    $sql .= "ar_session.deposit_date >= ? AND ar_session.deposit_date <= ?";
		$binds[] = $from_date;
		$binds[] = $to_date;
	} else if($_POST['form_date_mode'] == 'post') {
    $sql .= "ar_session.post_to_date >= ? AND ar_session.post_to_date <= ?";
		$binds[] = $from_date;
		$binds[] = $to_date;
	} else {
    $sql .= "ar_session.created_time >= ? AND ar_session.created_time <= ?";
		$binds[] = $from_date_time;
		$binds[] = $to_date_time;
	}

	$sql  .= "AND ar_session.user_id = ?";
	$binds[] = $user;
	if($_POST['form_contract']) {
		if($GLOBALS['wmt::client_id'] == 'cmb') {
			$sql .= " AND pat.genericval2 = ?"; 
			$binds[] = $_POST['form_contract'];
		}
	}
	if($form_source != 'all') {
		$sql .= " AND ar_session.payment_type = ?";
		$binds[] = $form_source;
	}
	if(!$form_zero_pmt) $sql .= " AND ar_session.pay_total != 0";
  $result = sqlStatement($sql, $binds);
  $iter = 0;
  while($row = sqlFetchArray($result)) {
    $all[$iter] = $row;
    $iter++;
  }
	return($all);
}

function PrintUacHeader() {
	global $hdr_printed;	
	$cols = ($GLOBALS['wmt::client_id'] == 'cmb') ? 15 : 14;
	echo "<tr><td class='bold' colspan='$cols'>";
	echo xl('Unapplied Detail');
	echo "</td></tr>\n";
	$hdr_printed = true;
}

function printUacDetail($uac) {
	global $user_qty, $grand_qty, $user_pmt_total, $user_adj_total;
	global $prev_user_desc, $bgcolor, $form_csvexport, $form_details;
	global $grand_pmt_total, $hdr_printed;

	$uac_total = 0;
	$hdr_printed = false;
	$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";
	if(!$form_csvexport && $form_details) {
	}
	foreach($uac as $dtl) {
		if(($dtl['pay_total'] - $dtl['applied']) == 0) continue;
  	$pmt = sprintf('%01.2f', ($dtl['pay_total'] - $dtl['applied']));
  	$pmt_orig = sprintf('%01.2f', $dtl['pay_total']);
		if($form_details) {
			if(!$hdr_printed && !$form_csvexport) PrintUacHeader();
			$addl = '';
			if($dtl['patient_id']) { 
				$addl = $dtl['lname'].', '.$dtl['fname'].' '.$dtl['mname'];
			}
			if($form_csvexport) {
      	echo '"'.display_desc(ListLook($dtl['payment_type'],'payment_type')).
							'",';
      	echo '"'.display_desc($dtl['description']) . '",';
				echo '" ",';
				echo '" ",';
				echo '" ",';
      	echo '"'.oeFormatShortDate(substr($dtl['created_time'],0,10)) . '",';
      	echo '"'.oeFormatShortDate(substr($dtl['post_to_date'],0,10)) . '",';
				echo '" ",';
      	echo '"'.display_desc('Payment to Unapplied Credt') . '",';
      	echo '"'.display_desc($addl).'",';
      	echo '"'.display_desc(ListLook($dtl['payment_method'], 'payment_method')) . '",';
				echo '" ",';
				if($GLOBALS['wmt::client_id'] == 'cmb') {
      		echo '"'.display_desc($dtl['genericval2']) . '",';
				} else {
      		// echo '"'.display_desc("") . '",';
				}
      	echo '"'.display_desc($dtl['reference']) . '",';
      	echo '"';
				bucks($pmt_orig);
				echo '",';
      	echo '"';
				bucks($pmt);
				echo '"' . "\n";
			} else {
				$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";
			?>
				<tr bgcolor="<?php echo $bgcolor; ?>">
  				<td class="detail">
						<?php echo display_desc(ListLook($dtl['payment_type'],'payment_type'));?>
					&nbsp;</td>
  				<td colspan="2" class="detail"><?php echo display_desc($dtl['description']); ?>&nbsp;</td>
					<td>&nbsp;</td>
  				<td>
						<?php echo oeFormatShortDate(substr($dtl['created_time'],0,10)); ?>
  				<td>
						<?php echo oeFormatShortDate(substr($dtl['post_to_date'],0,10)); ?>
					&nbsp;</td>
  				<td colspan="2" class="detail"><?php echo xl('UAC'); ?>- -&gt; <?php echo xl('Orig Ttl'); ?> / <?php echo xl('Remain'); ?></td>
  				<td class="detail"><?php echo display_desc($addl); ?>&nbsp;</td>
  				<td class="detail" colspan="2">
   				<?php echo display_desc(ListLook($dtl['payment_method'], 'payment_method')); ?>&nbsp;
  				</td>
					<?php if($GLOBALS['wmt::client_id'] == 'cmb') { ?>
      			<td class="detail"><?php echo display_desc($dtl['genericval2']); ?>&nbsp;</td>
					<?php } else { ?>
      			<!-- td class="detail">&nbsp;</td -->
					<?php } ?>
  				<td align="right"><?php echo display_desc($dtl['reference']); ?>&nbsp;
					</td>
  				<td align="right"><?php bucks($pmt_orig); ?></td>
  				<td align="right"><?php bucks($pmt); ?></td>
 				</tr>
			<?php
			}
		}
		$user_pmt_total  += $pmt; 
  	$grand_pmt_total += $pmt;
		$uac_total       += $pmt;
		$user_qty++;
		$grand_qty++;
	}	
	// Just a one line summary of the UAC
	if(!$form_details) {
		if($form_csvexport) {
      echo '"Total UAC For: '.display_desc($prev_user_desc) . '",';
      echo '" ",';
      echo '" ",';
      echo '" ",';
      echo '" ",';
      echo '"';
			bucks($pmt);
			echo '"' . "\n";
		} else {
		?>
 			<tr bgcolor="#ddffff">
  			<td class="detail">
					<?php echo 'Total Unapplied For: ',display_desc($prev_user_desc); ?>
				</td>
				<td class="detail">&nbsp;</td>
  			<td align="right">&nbsp;</td>
  			<td align="right">&nbsp;</td>
  			<td align="right">&nbsp;</td>
  			<td align="right"><?php bucks($uac_total); ?></td>
			</tr>
	<?php
		}
	}
}

function userTotals() {
	global $user_id, $user_desc, $user_qty, $user_pmt_total, $user_adj_total;
  global $prev_sort, $prev_sort_desc, $prev_user, $prev_user_desc;
	global $form_user, $form_csvexport, $from_date, $to_date, $bgcolor;
	global $prim_sort, $prim_desc, $prim_sort_left, $prim_desc_left;
	global $form_zero_pmt, $form_provider, $form_no_uac;


	if($user_id != $prev_user && $prev_user) {
		if(!$form_no_uac) {
			$uac = GetAllUnapplied($prev_user);
			if(count($uac) > 0) printUacDetail($uac);
		}
    if(!$form_csvexport) {
			$cols = ($GLOBALS['wmt::client_id'] == 'cmb') ? 5 : 4;
		?>
 			<tr bgcolor="#ddffff">
  			<td class="detail" colspan="7">
					<?php echo 'Total For: ',display_desc($prev_user_desc); ?>&nbsp;
				</td>
				<td class="detail" colspan="<?php echo $cols; ?>">&nbsp;</td>
  			<td align="right"><?php echo $user_qty; ?></td>
  			<td align="right"><?php bucks($user_adj_total); ?></td>
  			<td align="right"><?php bucks($user_pmt_total); ?></td>
			</tr>
			<?php if($user_id && $user_id != '^end^') { ?>
			<tr><td colspan="14">&nbsp;</td></tr>
			<tr>
  			<td class="detail" colspan="7">
					<?php echo display_desc($user_desc); ?>&nbsp;</td>
				<td class="detail" colspan="7">&nbsp;</td>
			</tr>	
			<?php	
			}
		} else {
			// Need to provide the unapplied detail	
		}
   	$user_pmt_total = $user_adj_total = $user_qty = 0;
  	$prev_user      = $user_id;
  	$prev_user_desc = $user_desc;
  }
	if(!$prev_user) {
		if(!$form_csvexport) {
	?>
		<tr>
  		<td class="detail" colspan="7">
				<?php echo display_desc($user_desc); ?>&nbsp;</td>
			<td class="detail" colspan="8">&nbsp;</td>
		</tr>	
	<?php
		}
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
	}
}

function primSortTotals() {
  global $prim_sort, $prim_desc, $prim_pmt_total, $prim_adj_total, $prim_qty;
  global $prev_sort, $prev_sort_desc, $prim_sort_left, $prim_desc_left;
	global $form_details, $form_csvexport, $dtl_lines;

  if ($prim_sort != $prev_sort && $prev_sort) {
    // Print primary sort total.
    if ($form_csvexport) {
			// If we are printing details we don't total for spreadsheets
      if(!$form_details) {
        echo '"' . display_desc($prev_sort) . '",';
        echo '"' . display_desc($prev_desc)  . '",';
        echo '"' . $prim_qty. '",';
        echo '"'; bucks($prim_adj_total); echo '"';
        echo '"'; bucks($prim_pmt_total); echo '"' . "\n";
			}
    } else { 
			if(!$form_details) { ?>
 				<tr bgcolor="#ddffff">
  				<td class="detail"><?php echo display_desc($prev_sort); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($prev_sort_desc); ?>&nbsp;
					</td>
  				<td align="right"><?php echo $prim_qty; ?></td>
  				<td align="right"><?php bucks($prim_adj_total); ?></td>
  				<td align="right"><?php bucks($prim_pmt_total); ?></td>
 				</tr>
			<?php
			} else {
				if($dtl_lines > 1) {
				$cols = ($GLOBALS['wmt::client_id'] == 'cmb') ? 6 : 5;
			?>
 				<tr bgcolor="#ddffff">
  				<td class="detail" colspan="6">
							<?php echo 'Total For: ',display_desc($prev_sort),'&nbsp;-&nbsp;',
								display_desc($prev_sort_desc); ?></td>
					<td class="detail" colspan="<?php echo $cols; ?>">&nbsp;</td>
  				<td align="right"><?php echo $prim_qty; ?></td>
  				<td align="right"><?php bucks($prim_adj_total); ?></td>
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
    $prim_pmt_total = $prim_adj_total = $prim_qty = $dtl_lines = 0;
    $prev_sort      = $prim_sort;
    $prev_sort_desc = $prim_desc;
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
  } else if(!$prev_sort) {
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
	}
}

function thisLineItem($post,$desc,$method,$ref,$memo,$qty,$adj,$pmt,$contract='',$create,$rendering,$acct_doc,$dos,$row = array()) {
  global $prim_sort, $prim_desc, $prim_pmt_total, $prim_adj_total, $prim_qty;
	global $grand_pmt_total, $grand_adj_total, $grand_qty;
	global $user_id, $user_desc, $user_qty, $user_pmt_total, $user_adj_total;
  global $prev_sort, $prev_sort_desc, $prev_user, $prev_user_desc;
	global $prim_sort_left, $prim_desc_left, $dtl_lines, $from_date, $to_date;
	global $form_user, $form_csvexport, $form_details, $primary_sort, $bgcolor;
	global $form_zero_pmt;

  $row_pmt = sprintf('%01.2f', $pmt);
  $row_adj = sprintf('%01.2f', $adj);

	primSortTotals();
	userTotals();

	$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";

  if($form_details) {
    if($form_csvexport) {
      echo '"' . display_desc($row{'session_id'}) . '",';
      echo '"' . display_desc($row{'encounter'}) . '",';
      echo '"' . display_desc($row{'sequence_no'}) . '",';
      echo '"' . display_desc($prim_sort) . '",';
      echo '"' . display_desc($prim_desc) . '",';
      echo '"' . display_desc($rendering) . '",';
      echo '"' . display_desc($acct_doc) . '",';
      echo '"' . display_desc($row{'facility'}) . '",';
      echo '"' . oeFormatShortDate($create) . '",';
      echo '"' . oeFormatShortDate($post) . '",';
      echo '"' . oeFormatShortDate($dos) . '",';
      echo '"' . display_desc($desc) . '",';
      echo '"' . display_desc($memo)  . '",';
      echo '"' . display_desc($row{'code'})  . '",';
      echo '"' . display_desc(ListLook($method, 'payment_method')) . '",';
			if($GLOBALS['wmt::client_id'] == 'cmb') {
      	echo '"'.display_desc($contract) . '",';
			} else {
      	// echo '"'.display_desc("") . '",';
			}
      echo '"' . display_desc($ref) . '",';
      echo '"'; bucks($row_adj); echo '",';
      echo '"'; bucks($row_pmt); echo '"' . "\n";
    } else {
?>

 <tr bgcolor="<?php echo $bgcolor; ?>">
  <td class="detail">
		<?php echo display_desc($prim_sort_left); $prim_sort_left = "&nbsp;"; ?>
	&nbsp;</td>
  <td class="detail">
		<?php echo display_desc($prim_desc_left); $prim_desc_left = "&nbsp;"?>
	&nbsp;</td>
  <td class="detail"><?php echo display_desc($rendering); ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($acct_doc); ?>&nbsp;</td>
  <td><?php echo oeFormatShortDate($create); ?>&nbsp;</td>
  <td><?php echo oeFormatShortDate($post); ?>&nbsp;</td>
  <td><?php echo oeFormatShortDate($dos); ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($desc); ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($memo); ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($row{'code'}); ?>&nbsp;</td>
  <td class="detail">
   <?php echo display_desc(ListLook($method, 'payment_method')); ?>&nbsp;
  </td>
	<?php if($GLOBALS['wmt::client_id'] == 'cmb') { ?>
 		<td class="detail"><?php echo display_desc($contract); ?>&nbsp;</td>
	<?php } else { ?>
 		<!-- td class="detail">&nbsp;</td -->
	<?php } ?>
  <td align="right"><?php echo display_desc($ref); ?>&nbsp;</td>
  <td align="right"><?php bucks($row_adj); ?></td>
  <td align="right"><?php bucks($row_pmt); ?></td>
 </tr>
<?php

    } // End not csv export
  } // end details
  $prim_pmt_total  += $pmt;
  $prim_adj_total  += $adj;
  $user_pmt_total  += $pmt;
  $user_adj_total  += $adj;
  $grand_pmt_total += $pmt;
  $grand_adj_total += $adj;
  $prim_qty        += $qty;
  $user_qty        += $qty;
  $grand_qty       += $qty;
	$prev_user       =  $user_id;
	$prev_user_desc  =  $user_desc;
	$prev_sort       =  $prim_sort;
	$prev_sort_desc  =  $prim_desc;
	$dtl_lines++;
} // end line print function

if (! AclMain::aclCheckCore('acct', 'rep')) die(xl("Unauthorized access."));

$default_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(!isset($_POST['form_from_date'])) {
	$_POST['form_from_date'] = $default_date;
} $_POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
if(!isset($_POST['form_to_date'])) {
	$_POST['form_to_date'] = $default_date;
} $_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_user      = isset($_POST['form_user']) ? $_POST['form_user'] : '';
$form_provider  = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_details   = isset($_POST['form_details']) ? $_POST['form_details'] : '1';
$form_contract  = isset($_POST['form_contract']) ? $_POST['form_contract'] : '';
$form_csvexport = $_POST['form_csvexport'];
$form_zero_pmt  = $_POST['form_zero_pmt'];
$form_no_uac    = $_POST['form_no_uac'];
$form_diff_doc  = $_POST['form_diff_doc'];
$form_source    = $_POST['form_source'];
$form_method    = $_POST['form_method'];
$form_memo      = $_POST['form_memo'];
$form_only_pmt  = isset($_POST['form_only_payments']);
$form_only_adj  = isset($_POST['form_only_adjustments']);
$form_details   = 1;
$primary_sort   = 'CPT';

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=pmts_by_user_date.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo '"Session ID",';
		echo '"Encounter",';
		echo '"Sequence",';
		echo '"PID",';
		echo '"Patient Name",';
		echo '"Performing",';
		echo '"Dr Of Record",';
		echo '"Facility",';
    echo '"Create Date",';
    echo '"Post To Date",';
    echo '"Service Date",';
		echo '"Description",';
		echo '"Memo",';
		echo '"Procedure",';
    echo '"Method",';
    if($GLOBALS['wmt::client_id'] == 'cmb') echo '"Contract",';
    echo '"Reference",';
    echo '"Adjustment",';
    echo '"Payment"' . "\n";
  } else {
		echo '"PID",';
		echo '"Patient Name",';
    echo '"Qty",';
    echo '"Adjustment",';
    echo '"Payment"' . "\n";
  }
	// End of Export
} else {
?>
<html>
<head>
<style type="text/ctss">
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

<title><?php echo xl('Payments by Date and Operator') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Payments by Date and Operator'); ?></span>

<form method='post' action='payments_by_date_user.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='900px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'><?php echo xl('Facility'); ?>:</td>
			<td>
			<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?>
			</td>
			<td class='label'><?php echo xl('From'); ?>:</td>
			<td><input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmr; ?>'><img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'></td>
			<td class='label'><?php echo xl('To'); ?>:</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'><img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'></td>
		<?php if($GLOBALS['wmt::client_id'] == 'cmb') { ?>
			<td class='label'><?php echo xl('Contract'); ?>:</td>
			<td><select name="form_contract" id="form_contract">
			<?php ListSel($form_contract,'chiro_contract',' - ALL - '); ?>
			</select></td>
		<?php } ?>
		</tr>
		<tr>
			<td class='label'><?php echo xl('Operator'); ?>:</td>
      <td><?php
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
      <td class='label'><?php xl('Provider','e'); ?>:</td>
      <td><?php
      // Build a drop-down list of providers.
      $query = "SELECT id, username, lname, fname FROM users " .
			 "WHERE authorized=1 AND username!='' AND active='1' ".
			 "AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
			 "UPPER(specialty) LIKE '%SUPERVISOR%') ORDER BY lname, fname";
      $ures = sqlStatement($query);

      echo "   <select name='form_provider'>\n";
      echo "    <option value=''";
			if($form_provider == '') echo 'selected="selected"';
			echo ">-- " . xl('All') . " --</option>\n";
      echo "    <option value='none'";
			if($form_provider == 'none') echo 'selected="selected"';
			echo ">-- " . xl('No Provider Assigned') . " --</option>\n";
      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $form_provider) echo " selected";
        echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
     }
     echo "   </select>\n";
     ?></td>
			<td colspan="2" class="label">
			   <input type='checkbox' name="form_zero_pmt" id="form_zero_pmt" <?php if($form_zero_pmt) echo 'checked'; ?>>&nbsp;&nbsp;<label for="form_zero_pmt"><?php echo xl('Include Zero $ Pmts'); ?></label>
			</td>
			<td colspan="2" class="label" style="text-align: left;">
			   <input type='checkbox' name="form_diff_doc" id="form_diff_doc" <?php if($form_diff_doc) echo 'checked'; ?>>&nbsp;&nbsp;<label for="form_diff_doc"><?php echo xl('Only Different Dr'); ?></label>
			</td>
			<!--td>
			   <input type='checkbox' name='form_details'<?php // if ($form_details) echo ' checked'; ?>>
			   <?php // xl('Details','e'); ?>
			</td -->
		</tr>
		<tr>
			<td class="label"><?php echo xl('Source'); ?>:</td>
			<td><select name="form_source" id="form_source">
				<option value="all" <?php echo $form_source == 'all' ? 'selected' : ''; ?>>- <?php echo xl('All'); ?> - </option>
				<option value="patient" <?php echo $form_source == 'patient' ? 'selected' : ''; ?>><?php echo xl('Patient'); ?></option>
				<option value="insurance" <?php echo $form_source == 'insurance' ? 'selected' : ''; ?>><?php echo xl('Insurance'); ?></option>
			</select></td>
			<td class="label"><?php echo xl('Filter Dt'); ?>:</td>
			<td class="text" colspan="3">
				<input name="form_date_mode" id="form_date_mode_create" type="radio" value="create" <?php echo $_POST['form_date_mode'] == 'create' ? 'checked="checked"' : ''; ?> /><label for="form_date_mode_create"><?php echo xl('Created'); ?></label>&nbsp;&nbsp;&nbsp;
				<input name="form_date_mode" id="form_date_mode_post" type="radio" value="post" <?php echo $_POST['form_date_mode'] == 'post' ? 'checked="checked"' : ''; ?> /><label for="form_date_mode_post"><?php echo xl('Post To'); ?></label>&nbsp;&nbsp;&nbsp;
				<input name="form_date_mode" id="form_date_mode_service" type="radio" value="service" <?php echo $_POST['form_date_mode'] == 'service' ? 'checked="checked"' : ''; ?> /><label for="form_date_mode_service"><?php echo xl('Service (Applied To)'); ?></label>&nbsp;&nbsp;&nbsp;
			</td>
			<td colspan="2" class="label" style="text-align: left;">
			   <input type='checkbox' name="form_no_uac" id="form_no_uac" <?php if($form_no_uac) echo 'checked'; ?>>&nbsp;&nbsp;<label for="form_no_uac"><?php echo xl('No Unapplied'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="label">Memo:</td>
			<td><input type='text' name="form_memo" id="form_memo" value="<?php echo htmlspecialchars($form_memo, ENT_QUOTES); ?>" /></td>
			<td class="text" colspan="3"><?php echo ('Use the "%" character as a wildcard for matching'); ?></td>
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
  <th> <?php echo xl('PID'); ?> </th>
  <th> <?php echo xl('Patient Name'); ?> </th>
  <th> <?php echo xl('Performing'); ?> </th>
  <th> <?php echo xl('Dr Of Record'); ?> </th>
  <th> <?php echo xl('Create Date'); ?> </th>
  <th> <?php echo xl('Post To Date'); ?> </th>
  <th> <?php echo xl('Service Date'); ?> </th>
  <th> <?php echo xl('Description'); ?> </th>
  <th> <?php echo xl('Memo'); ?> </th>
  <th> <?php echo xl('Procedure'); ?> </th>
  <th> <?php echo xl('Method'); ?> </th>
  <?php if($GLOBALS['wmt::client_id'] == 'cmb') { ?>
  <th> <?php echo xl('Contract'); ?> </th>
  <?php } ?>
  <th> <?php echo xl('Reference'); ?> </th>
  <th align="right"> <?php echo xl('Adjustment'); ?> </th>
  <th align="right"> <?php echo xl('Payment'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $bind_from_date = $from_date = $form_from_date;
  $bind_to_date = $to_date = $form_to_date;

  $prim_sort = $prim_desc = $user_id = $user_desc = $bgcolor = '';
	$prim_pmt_total = $prim_adj_total = $prim_qty = 0;
	$user_pmt_total = $user_adj_total = $user_qty = 0;
	$grand_pmt_total = $grand_adj_total = $grand_qty = 0;
	$dtl_lines = $rpt_lines = 0;
	$prev_sort = $prev_sort_desc = $prev_user = $prev_user_desc = '';

	$binds = array();
  $query = 'SELECT ar.pid, ar.encounter, ar.sequence_no, ar.code, ' .
		'ar.post_time AS post_dt, ar.post_user AS user, ar.session_id, ' .
		'ar.memo, ar.pay_amount AS pmt_applied, ar.adj_amount AS adj_applied, ' .
		'ar.follow_up, ar.follow_up_note, ar.reason_code, ar.memo, ' . 
		'ss.reference, ss.payment_type, ss.description, ss.adjustment_code, ' .
		'ss.payment_method, ss.payer_id, ss.post_to_date, ss.created_time, ' .
		'pat.lname AS plast, pat.fname AS pfirst, pat.mname AS pmi, ' .
		'pat.genericval2, pat.providerID, f.name AS facility, ' .
		'users.lname AS ulast, users.fname AS ufirst, users.mname AS umi, ' .
		// 'dr.lname AS drlast, dr.fname AS drfirst, dr.mname AS drmi, ' .
		'fe.facility_id, SUBSTRING(fe.date,1,10) AS service_date, ' .
		'fe.provider_id AS dr ' .
		// 'b.provider_id ' .
    'FROM ar_activity AS ar ' .
		'LEFT JOIN ar_session AS ss USING (session_id) ' .
		'LEFT JOIN form_encounter AS fe ON (ar.pid = fe.pid AND ' .
		'ar.encounter = fe.encounter) ' .
		// 'LEFT JOIN billing AS b ON (b.encounter = ar.encounter AND ' .
		// 'b.code = ar.code AND b.code_type = ar.code_type AND ' .
		// 'b.modifier = ar.modifier) ' .
		'LEFT JOIN patient_data AS pat ON (ar.pid = pat.pid) ' .
		'LEFT JOIN users ON (ar.post_user = users.id) ' .
		'LEFT JOIN facility AS f ON (fe.facility_id = f.id) ' .
		// 'LEFT JOIN users AS dr ON pat.providerID = users.id ' .
    'WHERE ';

	$orderby = ' ORDER BY ar.post_user, ';
	if($_POST['form_date_mode'] == 'post') {
		$query .= '(ss.post_to_date >= ? AND ss.post_to_date <= ?)';
		$orderby .= 'ss.post_to_date, ';
	} else if($_POST['form_date_mode'] == 'service') {
		$query .= '(fe.date >= ? AND fe.date <= ?)';
		$bind_from_date .= ' 00:00:00';
		$bind_to_date .= ' 23:59:59';
		$orderby .= 'service_date, ';
	} else {
		$query .= '(ss.created_time >= ? AND ss.created_time <= ?)';
		$bind_from_date .= ' 00:00:00';
		$bind_to_date .= ' 23:59:59';
		$orderby .= 'ss.created_time, ';
	}
	$binds[] = $bind_from_date;
	$binds[] = $bind_to_date;
	if($form_contract) {
		if($GLOBALS['wmt::client_id'] == 'cmb') {
			$query .= ' AND genericval2 = ?';
			$binds[] = $form_contract;
		}
	}
	if($form_source && strtolower($form_source) != 'all') {
		$query .= " AND ss.payment_type = ?";
		$binds[] = $form_source;
	}
	if(!$form_zero_pmt) {
		$query .= " AND (ar.pay_amount != 0 OR ar.adj_amount != 0)";
	}
  if ($form_facility) {
		$query .= ' AND fe.facility_id = ?';
		$binds[] = $form_facility;
	}
  if ($form_only_adj) {
		$query .= ' AND ar.adj_amount != 0.00';
	}
  if ($form_only_pmt) {
		$query .= ' AND ar.pay_amount != 0.00';
	}
  if ($form_method && strtolower($form_method) != 'all') {
		$query .= ' AND ss.payment_method = ?';
		$binds[] = $form_method;
	}
  if ($form_memo) {
		$query .= ' AND ar.memo LIKE ?';
		$binds[] = $form_memo;
	}
  if ($form_user) {
		$query .= ' AND ar.post_user = ?';
		$binds[] = $form_user;
	}
  if ($form_provider == 'none') {
		// $query .= ' AND ((b.provider_id = 0 OR b.provider_id IS NULL) AND (fe.provider_id = 0 OR fe.provider_id IS NULL))';
		$query .= ' AND (fe.provider_id = 0 OR fe.provider_id IS NULL)';
  } else if ($form_provider) {
		// $query .= ' AND (b.provider_id = ? OR ((b.provider_id IS NULL OR b.provider_id = 0) AND fe.provider_id = ?) )';
		$query .= ' AND fe.provider_id = ?';
		$binds[] = $form_provider;
	}
	// $query .= ' AND ar.deleted = 0 ' . $orderby . ' ar.pid';
	$query .= ' ' . $orderby . ' ar.pid';
	 // echo "Query: $query)<br>\n";
	 // echo "Binds: ";
	 // print_r($binds);
	 // echo "<br>\n";
  $res = sqlStatement($query, $binds);
  while ($row = sqlFetchArray($res)) {
		$user_id = $row['user'];
		$user_desc = $row['ulast'].', '.$row['ufirst'];
		$prim_sort = $row['pid'];
		$prim_desc = $row['plast'].','.$row['pfirst'];
		if(!$row{'dr'}) {
			// Here we need to attempt to grab a billing provider
		}
		// $rendering = $row['provider_id'] ? $row['provider_id'] : $row['dr'];
		$rendering = $row{'dr'};
		if($form_diff_doc) {
			if($rendering == $row['providerID']) continue;
		}
		$tmp = sqlQuery('SELECT lname, fname, mname FROM users WHERE id=?',
			array($row['providerID']));
		$drlast = $tmp['lname'];
		$tmp = sqlQuery('SELECT lname, fname, mname FROM users WHERE id=?',
			array($rendering));
		$rendering = $tmp['lname'];
    thisLineItem(substr($row['post_to_date'], 0, 10), $row['description'], 
			$row['payment_method'], $row['reference'], $row['memo'], 
			1, $row['adj_applied'], $row['pmt_applied'], $row['genericval2'],
			substr($row['created_time'], 0, 10),$rendering,$drlast,
			$row['service_date'], $row);
		$rpt_lines++;
  }

	$prim_sort = '^end^';
	$user_id = '^end^';
	primSortTotals();
	userTotals();

	if(!$form_user && !$form_csvexport) {
    $cols = ($GLOBALS['wmt::client_id'] == 'cmb') ? 12 : 11;
	?>
 	<tr bgcolor="#ddffff">
 	 <td class="detail" colspan="<?php echo $cols; ?>"> <?php echo xl('Grand Total'); ?> </td>
 	 <td align="right"> <?php echo $grand_qty; ?> </td>
 	 <td align="right"> <?php bucks($grand_adj_total); ?> </td>
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

<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});
</script>

</html>
<?php
} // End not csv export
?>
