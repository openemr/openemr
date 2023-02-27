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
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_contract'])) $_POST['form_contract'] = '';
$rpt_lines = 0;
if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';

function ListSel($thisField, $thisList, $empty_label = '') {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE list_id=? AND ".
		"seq >= 0 ORDER BY seq, title",array($thisList));
  echo "<option value=''";
  echo ">$empty_label&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow{'option_id'} . "'";
    if($thisField == $rrow{'option_id'}) {
			echo " selected='selected'";
		} else if(empty($thisField)) {
			if($rrow{'is_default'} == 1) echo " selected='selected'";
		}
    echo ">" . htmlspecialchars($rrow{'title'}, ENT_NOQUOTES);
    echo "</option>";
  }
}

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
	if(!$amount) $amount = '0.00';
  if ($amount) echo oeFormatMoney($amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function GetAllUnapplied($user='') {
	global $form_zero_pmt, $from_date, $to_date, $form_provider, $form_user;
  $all = array();
  $sql = "SELECT ar_session.*, ins.name, " .
			"pat.lname, pat.fname, pat.mname, pat.genericval2, " .
			"(SELECT SUM(ar_activity.pay_amount) FROM ar_activity WHERE " .
			"ar_activity.session_id = ar_session.session_id AND ".
			"ar_activity.deleted = 0) AS applied, " .
			"users.lname AS ulast, users.fname AS ufirst, users.mname AS umiddle " .
      "FROM ar_session " .
      "LEFT JOIN insurance_companies AS ins ON ar_session.payer_id = ins.id " .
      "LEFT JOIN patient_data AS pat ON ar_session.patient_id = pat.pid " .
			"LEFT JOIN users ON ar_session.user_id = users.id " .
      "WHERE " .
      "ar_session.created_time >= '$from_date 00:00:00' AND " .
			"ar_session.created_time <= '$to_date 23:59:59'";
	if($form_user) $sql .= " AND ar_session.user_id = $form_user";
	if($_POST['form_contract']) {
		if($GLOBALS['wmt::client_id'] == 'cmb') 
								$sql .= " AND pat.genericval2 = '".$_POST['form_contract']."'";
	}
	if(!$form_zero_pmt) $sql .= " AND ar_session.pay_total != 0";
	$sql .= " ORDER BY user_id ASC";
  $result = sqlStatement($sql);
  $iter = 0;
  while($row = sqlFetchArray($result)) {
    $all[$iter] = $row;
    $iter++;
  }
	return($all);
}

function userTotals() {
	global $user_id, $user_desc, $user_qty, $user_pmt_total, $user_applied_total;
  global $prev_user, $prev_user_desc;
	global $form_user, $form_csvexport, $from_date, $to_date, $bgcolor;
	global $form_zero_pmt, $form_provider;

	if(!$form_user) {
		if($user_id != $prev_user && $prev_user) {
     	if(!$form_csvexport) {
				?>
 				<tr bgcolor="#ddffff">
  				<td class="detail" colspan="4">
						<?php echo 'Total For: ',display_desc($prev_user_desc); ?>&nbsp;
					</td>
					<td class="detail" colspan="5">&nbsp;</td>
  				<td align="right"><?php bucks($user_pmt_total); ?></td>
  				<td align="right"><?php bucks($user_applied_total); ?></td>
  				<td align="right"><?php bucks(($user_pmt_total - $user_applied_total)); ?></td>
				</tr>
				<?php if($user_id && $user_id != '^end^') { ?>
				<tr><td colspan="12">&nbsp;</td></tr>
				<tr>
  				<td class="detail" colspan="6">
						<?php echo display_desc($user_desc); ?>&nbsp;</td>
					<td class="detail" colspan="6">&nbsp;</td>
				</tr>	
				<?php	
				}
			} else {
				// Need to provide the unapplied detail	
			}
   		$user_pmt_total = $user_applied_total = $user_qty = 0;
    }
	}
	if(!$prev_user) {
		if(!$form_csvexport) {
	?>
		<tr>
  		<td class="detail" colspan="3">
				<?php echo display_desc($user_desc); ?>&nbsp;</td>
			<td class="detail" colspan="9">&nbsp;</td>
		</tr>	
	<?php
		}
	}
  $prev_user      = $user_id;
 	$prev_user_desc = $user_desc;
}

if (! AclMain::aclCheckCore('acct', 'rep')) die(xl("Unauthorized access."));

$INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;
$INTEGRATED_AR = true;

$default_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(!isset($_POST['form_from_date'])) $_POST['form_from_date'] = $default_date;
if(!isset($_POST['form_to_date'])) $_POST['form_to_date'] = $default_date;
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_user = isset($_POST['form_user']) ? $_POST['form_user'] : '';
$form_provider = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_details = isset($_POST['form_details']) ? $_POST['form_details'] : '1';
$form_contract = isset($_POST['form_contract']) ? $_POST['form_contract'] : '';
$form_csvexport = $_POST['form_csvexport'];
$form_zero_pmt = $_POST['form_zero_pmt'];
$form_details = 1;
// NOT REALLY IN USE
$primary_sort = 'CPT';

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=unapplied_by_user_date.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo '"PID",';
		echo '"Plan or Patient Name",';
    echo '"Created",';
    echo '"Post To Date",';
		echo '"Description",';
    echo '"Method",';
    echo '"Contract",';
    echo '"Reference",';
    echo '"Type",';
    echo '"Total Payment",';
    echo '"Applied",';
    echo '"Balance"' . "\n";
  } else {
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

<title><?php echo xl('Unapplied Credit by Date and Operator') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Unapplied Credit by Date and Operator'); ?></span>

<form method='post' action='unapplied_rpt.php' id='theform'>

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
		<?php if($GLOBALS['wmt::client_id'] == 'cmb') { ?>
			<td class='label'>
				<?php echo xl('Contract'); ?>:
			</td>
			<td><select name="form_contract" id="form_contract">
			<?php ListSel($form_contract,'chiro_contract',' - ALL - '); ?>
			</select></td>
		<?php } ?>
			<td colspan="2" class="label">
			   <input type='checkbox' name="form_zero_pmt" id="form_zero_pmt" <?php if($form_zero_pmt) echo 'checked'; ?>>&nbsp;&nbsp;<label for="form_zero_pmt"><?php echo xl('Include Zero $ Pmts'); ?></label>
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
  <th> <?php echo xl('Plan or Patient Name'); ?> </th>
  <th> <?php echo xl('Created'); ?> </th>
  <th> <?php echo xl('Post To Date'); ?> </th>
  <th> <?php echo xl('Description'); ?> </th>
  <th> <?php echo xl('Method'); ?> </th>
  <th> <?php echo xl('Contract'); ?> </th>
  <th> <?php echo xl('Reference'); ?> </th>
  <th> <?php echo xl('Type'); ?> </th>
	<th align="right"> <?php echo xl('Total Pmt'); ?></th>
  <th align="right"> <?php echo xl('Applied'); ?> </th>
  <th align="right"> <?php echo xl('Remaining'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
	global $user_qty, $grand_qty, $user_pmt_total, $user_applied_total;
	global $user_desc, $prev_user_desc, $bgcolor, $form_csvexport, $form_details;
	global $prev_user, $grand_pmt_total, $grand_applied_total, $hdr_printed;

  $from_date = $form_from_date;
  $to_date   = $form_to_date;

  $user_id = $user_desc = $bgcolor = '';
	$user_pmt_total = $user_applied_total = $user_qty = 0;
	$grand_pmt_total = $grand_applied_total = $grand_qty = 0;
	$dtl_lines = $rpt_lines = 0;
	$prev_user = $prev_user_desc = '';

	$uac = GetAllUnapplied();

	$hdr_printed = false;
	$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";
	foreach($uac as $dtl) {
		if(($dtl['pay_total'] - $dtl['applied']) == 0) continue;

		// Deal with a user change here
		$user_id = $dtl{'user_id'};
		$user_desc = $dtl{'ulast'} . ', ' . $dtl{'ufirst'};
		if($user_id != $prev_user) userTotals();

  	$bal = sprintf('%01.2f', ($dtl['pay_total'] - $dtl['applied']));
  	$pmt = sprintf('%01.2f', $dtl['pay_total']);
  	$applied = sprintf('%01.2f', $dtl['applied']);
		$addl = '';
		if($dtl{'patient_id'}) {
			$addl = $dtl{'lname'} . ', ' . $dtl{'fname'};
		} else {
			$addl = $dtl{'name'};
		}
		
		if($form_details) {
			if($dtl['patient_id'] == 0) $dtl['patient_id'] = '';
			if($form_csvexport) {
      	echo '"'.display_desc($dtl['patient_id']).'","';
							'","';
      	echo display_desc($addl) . '","';
      	echo oeFormatShortDate(substr($dtl['created_time'],0,10)) . '","';
      	echo oeFormatShortDate(substr($dtl['post_to_date'],0,10)) . '","';
      	echo display_desc(ListLook($dtl['payment_type'],'payment_type')). '","';
      	echo display_desc(ListLook($dtl['payment_method'], 'payment_method')) . '","';
				if($GLOBALS['wmt::client_id'] == 'cmb') {
      		echo display_desc($dtl['genericval2']) . '","';
				} else {
      		echo display_desc("") . '","';
				}
      	echo display_desc($dtl['reference']) . '","';
      	echo display_desc(ucfirst(str_replace('_', ' ', $dtl['adjustment_code']))) . '","';
				bucks($pmt);
				echo '","';
				bucks($applied);
				echo '","';
				bucks($bal);
				echo '"' . "\n";
			} else {
				$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";
			?>
				<tr bgcolor="<?php echo $bgcolor; ?>">
  				<td class="detail"><?php echo display_desc($dtl['patient_id']); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($addl); ?>&nbsp;</td>
  				<td>
						<?php echo oeFormatShortDate(substr($dtl['created_time'],0,10)); ?>
					&nbsp;</td>
  				<td>
						<?php echo oeFormatShortDate(substr($dtl['post_to_date'],0,10)); ?>
					&nbsp;</td>
  				<td class="detail">
						<?php echo display_desc(ListLook($dtl['payment_type'],'payment_type')); ?>&nbsp;</td>
  				<td class="detail">
   				<?php echo display_desc(ListLook($dtl['payment_method'], 'payment_method')); ?>&nbsp;
  				</td>
					<?php if($GLOBALS['wmt::client_id'] == 'cmb') { ?>
      			<td class="detail"><?php echo display_desc($dtl['genericval2']); ?>&nbsp;</td>
					<?php } else { ?>
      			<td class="detail">&nbsp;</td>
					<?php } ?>
  				<td class="detail"><?php echo display_desc($dtl['reference']); ?>&nbsp;</td>
					<td class="detail">
      	<?php echo display_desc(ucfirst(str_replace('_', ' ', $dtl['adjustment_code']))); ?>&nbsp;</td>
  				<td align="right"><?php bucks($pmt); ?></td>
  				<td align="right"><?php bucks($applied); ?></td>
  				<td align="right"><?php bucks($bal); ?></td>
 				</tr>
			<?php
			}
		}
		$user_pmt_total  += $pmt; 
  	$grand_pmt_total += $pmt;
		$user_applied_total  += $applied; 
  	$grand_applied_total += $applied;
		$user_qty++;
		$grand_qty++;
		$rpt_lines++;
	}

	$user_id = '^end^';
	userTotals();

	if(!$form_user && !$form_csvexport && $rpt_lines) {
	?>
 	<tr bgcolor="#ddffff">
 	 <td class="detail" colspan="9"> <?php echo xl('Grand Total'); ?> </td>
 	 <td align="right"> <?php bucks($grand_pmt_total); ?> </td>
 	 <td align="right"> <?php bucks($grand_applied_total); ?> </td>
 	 <td align="right"> <?php bucks(($grand_pmt_total - $grand_applied_total)); ?> </td>
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
