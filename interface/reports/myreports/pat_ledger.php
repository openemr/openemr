<?php
/**
 * This is a report to create a patient ledger of charges with payments
 * applied.
 *
 * Copyright (C) 2015-2020 Rich Genandt <rgenandt@gmail.com>
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
 * @author  WMT
 * @link    http://www.open-emr.org
 */

$sanitize_all_escapes=true;
$fake_register_globals=false;

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/appointments.inc.php');

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

$enc_units = $total_units = 0;
$enc_chg = $total_chg = 0;
$enc_pmt = $total_pmt = 0;
$enc_adj = $total_adj = 0;
$enc_bal = $total_bal = 0;
$bgcolor = "#FFFFDD";
$orow = 0;

if (! AclMain::aclCheckCore('acct', 'rep')) die(xlt("Unauthorized access."));

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

function UserNameFromID($thisField) {
  $ret = '';
  if(!$thisField) return $ret;
  $rrow = sqlQuery("SELECT * FROM users WHERE id=?",array($thisField));
  if($rrow{'id'}) $ret = $rrow{'lname'}.', '.$rrow{'fname'}.' '.$rrow{'mname'};
  return $ret;
}

function GetAllUnapplied($pat='',$from_dt='',$to_dt='') {
	global $ins_only, $pat_only;
  $all = array();
	if(!$pat) return($all);
  $sql = "SELECT ar_session.*, ins.name, " .
			"pat.lname, pat.fname, pat.mname, " .
			"(SELECT SUM(ar_activity.pay_amount) FROM ar_activity WHERE " .
			"ar_activity.session_id = ar_session.session_id AND " .
			"ar_activity.deleted = 0) AS applied " .
      "FROM ar_session " .
      "LEFT JOIN insurance_companies AS ins on ar_session.payer_id = ins.id " .
      "LEFT JOIN patient_data AS pat on ar_session.patient_id = pat.pid " .
      "WHERE ";
	if($ins_only) $sql .= "(ar_session.payer_id != 0 AND ".
				"ar_session.payer_id != '') AND ";
	if($pat_only) $sql .= "(ar_session.payer_id = 0 OR ".
				"ar_session.payer_id = '') AND ";
  $sql .= "ar_session.created_time >= ? AND ar_session.created_time <= ? " .
			"AND ar_session.patient_id=?";
  $result = sqlStatement($sql, array($from_dt, $to_dt, $pat));
  $iter = 0;
  while($row = sqlFetchArray($result)) {
    $all[$iter] = $row;
    $iter++;
  }
	return($all);
}

function GetAllCredits($enc = '', $pat='') {
	global $ins_only, $pat_only;
	$all = array();
	if(!$enc || !$pat) return($all);
  $sql = "SELECT activity.*, session.*, ins.name FROM ar_activity AS ".
    "activity LEFT JOIN ar_session AS session USING (session_id) ".
		"LEFT JOIN insurance_companies AS ins ON session.payer_id = ".
    "ins.id WHERE ";
	if($ins_only) $sql .= "(session.payer_id != 0 AND session.payer_id != '') ".
		"AND activity.adj_amount = 0.00 AND ";
	if($pat_only) $sql .= "(session.payer_id = 0 OR session.payer_id = '') ".
		"AND activity.adj_amount = 0.00 AND ";
	$sql .= "encounter=? AND pid=? AND activity.deleted = 0 ".
		"ORDER BY sequence_no";
  $result = sqlStatement($sql, array($enc, $pat));
  $iter = 0;
  while($row = sqlFetchArray($result)) {
    $all[$iter] = $row;
    $iter++;
  }
	return($all);
}

function PrintEncHeader($dt, $rsn, $dr, $enc) {
	global $bgcolor, $orow, $csv_output;
	if($csv_output) return false;
 	$bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
	echo "<tr bgcolor='#FFFFFF'>";
	if(strlen($rsn) > 50) $rsn = substr($rsn,0,50).'...';
	echo "<td colspan='5'><span class='bold'>Encounter Dt / Enc /  Rsn: </span>".
			"<span class='detail'>" . text(substr($dt,0,10))." / " . 
			text($enc) . " / " .text($rsn) .  "</span></td>";
	echo "<td colspan='4'><span class='bold'>Provider: </span>".
			"<span class='detail'>".text(UserNameFromID($dr))."</span></td>";
 	echo "</tr>\n";	
	$orow++;
}

function PrintEncFooter($enc) {
	global $enc_units, $enc_chg, $enc_pmt, $enc_adj, $enc_bal, $csv_output;
	global $chg_only, $pmt_only;
	if($csv_output) return false;
	echo "<tr bgcolor='#DDFFFF'>";
  echo "<td colspan='3'>&nbsp;</td>";
	echo "<td class='detail'>Encounter ($enc) Balance:</td>";
	if(!$pmt_only) {
 		echo "<td class='detail' style='text-align: right;'>&nbsp;".
				text($enc_units)."</td>";
 		echo "<td class='detail' style='text-align: right;'>&nbsp;".
				text(oeFormatMoney($enc_chg))."</td>";
	} else {
		echo "<td colspan='2'>&nbsp;</td>";
	}
	if(!$chg_only) {
 		echo "<td class='detail' style='text-align: right;'>&nbsp;".
				text(oeFormatMoney($enc_pmt))."</td>";
 		echo "<td class='detail' style='text-align: right;'>&nbsp;".
				text(oeFormatMoney($enc_adj))."</td>";
	} else {
		echo "<td colspan='2'>&nbsp;</td>";
	}
 	echo "<td class='detail' style='text-align: right;'>&nbsp;".
		text(oeFormatMoney($enc_bal))."</td>";
	echo "</tr>\n";
}

function PrintCreditDetail($detail, $pat, $unassigned=false) {
	global $enc_pmt, $total_pmt, $enc_adj, $total_adj, $enc_bal, $total_bal;
	global $bgcolor, $orow, $enc_units, $enc_chg, $use_post_to_date, $csv_output;
  foreach($detail as $pmt) {
		if(!isset($pmt['follow_up_note'])) $pmt['follow_up_note'] = '';
		if($unassigned) {
			// FIX - HERE WE NEED TO SCAN AND SEE IF ANY CHARGES ARE ATTACHED TO 
			// THIS ENCOUNTER BEFORE WE SKIP THIS PAYMENT
			if(($pmt['pay_total'] - $pmt['applied']) == 0) continue;
		}
    $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
		if($csv_output) {
			$print = '"",';
		} else {
			$print = "<tr bgcolor='$bgcolor'>";
	  	$print .= "<td class='detail'>&nbsp;</td>";
		}
		$method = ListLook($pmt['payment_method'],'payment_method');
		$type = ListLook($pmt['payment_type'],'payment_type');
		$desc = $pmt['description'];
    $ref = $pmt['reference'];
    if($unassigned) {
      $memo = ListLook($pmt['adjustment_code'],'payment_adjustment_code');
    } else {
		  $memo = $pmt['memo'];
    }
		$description = $method;
		if($ref) {
			if($description) $description .= ' - ';
			$description .= $ref;
		}
		if($desc && $type != 'Insurance') {
			if($description) $description .= ': ';
			$description .= $desc;
		}
		if($memo) {
			if($description) $description .= ' ';
			$description .= '['.$memo.']';
		}
		if($csv_output) {
			$print .= '"' . text($description) . '",';
		} else {
			$print .= "<td class='detail' colspan='2'>".
                                      text($description)."&nbsp;</td>";
		}
		$payer = ($pmt['name'] == '') ? 'Patient' : $pmt['name'];
    if($unassigned || $use_post_to_date) {
      $pmt_date = substr($pmt['post_to_date'],0,10);
    } else {
      $pmt_date = substr($pmt['created_time'],0,10);
    }
		if($csv_output) {
			$print .= '"' . text($pmt_date) . ' / ' . text($payer) . '",';
		} else {
    	$print .= "<td class='detail'>".
            text($pmt_date)."&nbsp;/&nbsp;".text($payer)."</td>";
		}
		if($csv_output) {
			$print .= '"' . text($type) . '",';
		} else {
			$print .= "<td class='detail'>".text($type)."&nbsp;</td>";
		}
    if($unassigned) {
			$adj_amt = '';	
		  $pmt_amt = $pmt['pay_total'] - $pmt['applied'];
			$uac_bal = $pmt_amt * -1;
			$uac_appl = oeFormatMoney($pmt['applied']);
			$uac_total = oeFormatMoney($pmt['pay_total']);
			$pmt_amt = $pmt['pay_total'];
		  $total_pmt = $total_pmt - $uac_bal;
			$print_uac_bal = oeFormatMoney($uac_bal);
    } else {
			$uac_total = '';
			$print_uac_bal = '';
			$uac_appl = '';
		  $pmt_amt = $pmt['pay_amount'];
			$adj_amt = $pmt['adj_amount'];
		  $enc_pmt = $enc_pmt + $pmt['pay_amount'];
		  $total_pmt = $total_pmt + $pmt['pay_amount'];
		  $enc_adj = $enc_adj + $pmt['adj_amount'];
		  $total_adj = $total_adj + $pmt['adj_amount'];
    }
		$print_pmt = '';
		if($pmt_amt != 0) $print_pmt = oeFormatMoney($pmt_amt);
		$print_adj = '';
		if($adj_amt != 0) $print_adj = oeFormatMoney($adj_amt);
		if($csv_output) {
			$print .= '"' . text($uac_appl) . '",';
			$print .= '"' . text($print_pmt) . '",';
			$print .= '"' . text($print_adj) . '",';
			$print .= '"' . text($print_uac_bal) . '",';
			if($pmt['follow_up_note'] != '') {
				$print .= '"' . text($pmt['follow_up_note']) . '",';
			}
			$print .= '"';
			$print .= "\n";
		} else {
			$print .= "<td class='detail' style='text-align: right;'>".text($uac_appl)."&nbsp;</td>";
   		$print .= "<td class='detail' style='text-align: right;'>".text($print_pmt)."&nbsp;</td>";
   		$print .= "<td class='detail' style='text-align: right;'>".text($print_adj)."&nbsp;</td>";
			$print .= "<td class='detail' style='text-align: right;'>".text($print_uac_bal)."&nbsp;</td>";
    	$print .= "</tr>\n";
		}
		echo $print;
		if($pmt['follow_up_note'] != '' && !$csv_output) {
  		$bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
			$print = "<tr bgcolor='$bgcolor'>";
	  	$print .= "<td class='detail' colspan='2'>&nbsp;</td>";
			$print .= "<td colspan='7'>Follow Up Note: ";
			$print .= $pmt['follow_up_note'];
			$print .= "</td></tr>\n";
			echo $print;
		}
		if($unassigned) {
			$total_bal = $total_bal + $uac_bal;
		} else {
			$enc_bal = $enc_bal - $pmt_amt - $adj_amt;
			$total_bal = $total_bal - $pmt_amt - $adj_amt;
		}
		$orow++;
  }
  $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
}

if(!isset($_REQUEST['form_from_date'])) $_REQUEST['form_from_date'] = '';
if(!isset($_REQUEST['form_to_date'])) $_REQUEST['form_to_date'] = '';
if(!isset($_REQUEST['form_facility'])) $_REQUEST['form_facility'] = '';
if(!isset($_REQUEST['form_provider'])) $_REQUEST['form_provider'] = '';
if(!isset($_REQUEST['form_patient'])) $_REQUEST['form_patient'] = $pid;
if(!isset($_REQUEST['form_csvexport'])) $_REQUEST['form_csvexport'] = '';
if(!isset($_REQUEST['form_refresh'])) $_REQUEST['form_refresh'] = '';
if(!isset($_REQUEST['form_pmt_only'])) $_REQUEST['form_pmt_only'] = '';
if(!isset($_REQUEST['form_chg_only'])) $_REQUEST['form_chg_only'] = '';
if(!isset($_REQUEST['form_pat_only'])) $_REQUEST['form_pat_only'] = '';
if(!isset($_REQUEST['form_ins_only'])) $_REQUEST['form_ins_only'] = '';
if(!isset($_REQUEST['form_use_eob_date'])) $_REQUEST['form_use_eob_date'] = '';
if(!isset($_REQUEST['form_csvexport'])) $_REQUEST['form_csvexport'] = '';

$last_year = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$form_from_date = date('Y-m-d', $last_year);
if($_REQUEST['form_from_date']) {
  $form_from_date = fixDate($_REQUEST['form_from_date'], $last_year);
}
$form_to_date     = fixDate($_REQUEST['form_to_date']  , date('Y-m-d'));
$form_facility    = $_REQUEST['form_facility'];
$form_provider    = $_REQUEST['form_provider'];
$form_patient     = $_REQUEST['form_patient'];
$use_post_to_date = $_REQUEST['form_use_eob_date'];
$csv_output       = $_REQUEST['form_csvexport'];
$chg_only         = $_REQUEST['form_chg_only'];
$pmt_only         = $_REQUEST['form_pmt_only'];
$pat_only         = $_REQUEST['form_pat_only'];
$ins_only         = $_REQUEST['form_ins_only'];

if ($_REQUEST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=svc_financial_report_".attr($form_from_date)."--".attr($form_to_date).".csv");
  header("Content-Description: File Transfer");
} else {
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<script type="text/javascript">
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
var pt_name;
var pt_id;

function checkSubmit() {
	var pat = document.forms[0].elements['form_patient'].value;
	if(!pat || pat == 0) {
		alert("A Patient Must Be Selected to Generate This Report");
		return false;
	}
	document.forms[0].elements['form_refresh'].value = true;
	document.forms[0].elements['form_csvexport'].value = '';
	document.forms[0].submit();
}

function setpatient(pid, lname, fname, dob) {
	// pt_name.value = lname + ', ' + fname + ' (' + pid + ')';
	document.forms[0].elements['form_patient'].value = pid;
}

function sel_patient(pname, pid) {
	pt_name = pname;
	pt_id = pid;
	dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 500, 400);
}
</script>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
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
  #report_header {
    visibility: visible;
    display: inline;
  }
  #title {
    visibility: hidden;
    display: none;
  }
}

/* specifically exclude some from the screen */
@media screen {
  #report_parameters_daterange {
    visibility: hidden;
    display: none;
  }
  #report_header {
    visibility: hidden;
    display: none;
  }
  #title {
    visibility: visible;
    display: inline;
  }
}
</style>

<title><?php echo xlt('Patient Ledger by Date') ?></title>
</head>
<body class="body_top">
<span class='title' id='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient Ledger by Date'); ?></span>
<form method='post' action='pat_ledger.php' id='theform'>
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='90%'>
	<div style='float:left'>
	<table class='text'>
		<tr>
			<td class='label'>
				<?php echo xlt('Facility'); ?>:
			</td>
			<td>
			<?php dropdown_facility($form_facility, 'form_facility', true); ?>
			</td>
      <td style="text-align: right;">
        <?php echo xlt('From'); ?>:
			</td>
			<td>
        <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr($form_from_date) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla("Click here to choose a date"); ?>'>
      </td>
			<td>
				<input name="form_chg_only" id="form_chg_only" type="checkbox" value="1" <?php echo ($_REQUEST['form_chg_only']) ? 'checked="checked" ' : ''; ?> onchange="if(this.checked == true) document.getElementById('form_pmt_only').checked = false; " />&nbsp;&nbsp;<label for="form_chg_only">Charges Only</label>
			</td>
			<td>
				<input name="form_use_eob_date" id="form_use_eob_date" type="checkbox" value="1" <?php echo ($_REQUEST['form_use_eob_date']) ? 'checked="checked" ' : ''; ?> />&nbsp;&nbsp;<label for="form_use_eob_date">Use Dt Posted To</label>
			</td>
      <td><span class='label'><?php xl('Patient','e'); ?>:&nbsp;&nbsp;</span>
        <input type='text' name='form_patient' id='form_patient' style="width: 90px;" value="<?php echo $form_patient; ?>" onclick="sel_patient();" />
      </td>
		</tr>
		<tr>
      <td><?php echo xlt('Provider'); ?>:</td>
      <td><?php
        $query = "SELECT id, lname, fname FROM users WHERE ".
                "authorized=1 AND active!=0 ORDER BY lname, fname"; 
        $ures = sqlStatement($query);
        echo "   <select name='form_provider'>\n";
        echo "    <option value=''>-- " . xlt('All') . " --\n";
        while ($urow = sqlFetchArray($ures)) {
          $provid = $urow['id'];
          echo "    <option value='" . attr($provid) ."'";
          if ($provid == $_REQUEST['form_provider']) echo " selected";
          echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
        }
        echo "   </select>\n";
      ?></td>
      <td class='label'>
        <?php echo xlt('To'); ?>:
      </td>
      <td>
        <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr($form_to_date) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla("Click here to choose a date"); ?>'>
      </td>
			<td>
				<input name="form_pmt_only" id="form_pmt_only" type="checkbox" value="1" <?php echo ($_REQUEST['form_pmt_only']) ? 'checked="checked" ' : ''; ?> onchange="if(this.checked == true) document.getElementById('form_chg_only').checked = false; " />&nbsp;&nbsp;<label for="form_pmt_only">Payments Only</label>
			</td>
			<td>
				<input name="form_ins_only" id="form_ins_only" type="checkbox" value="1" <?php echo ($_REQUEST['form_ins_only']) ? 'checked="checked" ' : ''; ?> onchange="if(this.checked == true) document.getElementById('form_pat_only').checked = false; " />&nbsp;&nbsp;<label for="form_ins_only">Insurance Only</label>
			</td>
			<td>
				<input name="form_pat_only" id="form_pat_only" type="checkbox" value="1" <?php echo ($_REQUEST['form_pat_only']) ? 'checked="checked" ' : ''; ?> onchange="if(this.checked == true) document.getElementById('form_ins_only').checked = false; " />&nbsp;&nbsp;<label for="form_pat_only">Patient Only</label>
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
					<a href='#' class='css_button' onclick="checkSubmit();" >
					<span><?php echo xlt('Submit'); ?></span></a>
				</div>
			</td>
		</tr>

		<tr>
			<td>
			<?php if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) { ?>
					<div style="margin-left: 15px;" id="controls">
					<a href='#' class='css_button' onclick='window.print()'>
						<span><?php echo xlt('Print'); ?></span></a>
					<!-- a href='#' class='css_button' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
						<span><?php // echo xlt('CSV Export'); ?></span></a -->
					</div>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>
</div> <!-- CLOSES PARAMETER DIV -->

<?php
} // END OF NOT CSV EXPORT LOGIC
$from_date = $form_from_date . ' 00:00:00';
$to_date = $form_to_date . ' 23:59:59';

if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) {
  $rows = array();
  $sqlBindArray = array();
  $query = "SELECT b.code_type, b.code, b.code_text, b.pid, b.provider_id, ".
				"b.billed, b.payer_id, b.units, b.fee, b.bill_date, b.id, ".
        "ins.name, ".
				"ct.ct_fee, ct.ct_key, ct.ct_proc, ".
				"(SELECT SUM(ar_activity.pay_amount) FROM ar_activity WHERE " .
				"ar_activity.encounter = fe.encounter AND ar_activity.deleted = 0) " .
				"AS applied, " .
        "fe.encounter, fe.date, fe.reason, fe.provider_id ".
        "FROM form_encounter AS fe ".
        "LEFT JOIN billing AS b USING (encounter) ".
        "LEFT JOIN insurance_companies AS ins ON b.payer_id = ins.id ".
        "LEFT JOIN code_types AS ct ON (b.code_type = ct.ct_key) ".
				"WHERE fe.date >= ? AND fe.date <= ? AND fe.pid = ? ";
  array_push($sqlBindArray,$from_date,$to_date,$form_patient);
  if ($form_facility) {
    $query .= "AND fe.facility_id = ? ";
    array_push($sqlBindArray,$form_facility);
  }
  if ($form_provider) {
    $query .= "AND b.provider_id = ? ";
    array_push($sqlBindArray,$form_provider);
  }
  $query .= "AND (activity > 0 OR activity IS NULL) ORDER BY fe.date, fe.id ";
  $res = sqlStatement($query,$sqlBindArray);
	// echo "Query: $query<br>\n"; 

  if ($_REQUEST['form_csvexport']) {
  // CSV headers:
    if (true) {
      echo '"Code",';
      echo '"Description",';
      echo '"Billed",';
      echo '"Who",';
      echo '"Type",';
      echo '"Units",';
		  echo '"Charge",';
		  echo '"Payment",';
		  echo '"Adjustment",';
		  echo '"Balance",';
			echo '"Notes"';
			echo "\n";
    }
  } else {
		if(!$form_facility) $form_facility = '3';
    $facility = sqlQuery("SELECT * FROM facility WHERE id=?", array($form_facility));
    $patient = sqlQuery("SELECT * from patient_data WHERE pid=?", array($form_patient));
    
?>
<div id="report_header">
<table width="98%"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="title">Patient Ledger</td>
	</tr>
  <tr>
    <td class="title"><?php echo $facility{'name'}; ?></td>
  </tr>
  <tr>
    <td class="title"><?php echo $facility{'street'}; ?></td>
  </tr>
  <tr>
    <td class="title"><?php echo $facility{'city'}.", ".$facility{'state'}." ".$facility{'postal_code'}; ?></td>
  </tr>
	<tr>
		<?php 
			$title = 'All Providers';
			if($form_provider) { $title = 'For Provider: '.UserNameFromID($form_provider); }
		?>
    <td class="title"><?php echo $title; ?></td>
	</tr>
	<tr>
		<?php 
			$title = 'For Dates: '.$form_from_date.' through '.$form_to_date;
		?>
    <td class="title"><?php echo $title; ?></td>
	</tr>
</table>
<br/>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="bold">Date:</td>
		<td class="text"><?php echo date('Y-m-d'); ?></td>
    <td class="bold">Patient:</td>
		<td class="text"><?php echo $patient{'lname'}.", ".$patient{'fname'}." ".$patient{'mname'}; ?></td>
    <td class="bold">DOB:</td>
		<td class="text"><?php echo $patient{'DOB'};?></td>
    <td class="bold">ID:</td>
		<td class="text"><?php echo $form_patient;?></td>
  </tr>
</table>
</div>
<div id="report_results">
<table >
 <thead>
  <th><?php xl('Code','e'); ?></th>
  <th colspan="2"><?php xl('Description','e'); ?></th>
	<?php if($pmt_only) { ?>
  	<th><?php xl('Pmt Dt','e'); ?> / <?php xl('Pmt From','e'); ?></th>
	<?php } else { ?>
  	<th><?php xl('Billed','e'); ?> / <?php xl('Who','e'); ?></th>
	<?php } ?>
  <th><?php xl('Type','e'); ?> / 
	<?php if(!$pmt_only) xl('Units','e'); ?></th>
	<?php if(!$pmt_only) { ?>
  	<th style="text-align: right;">
		<?php 
		if(!$chg_only) {
			xl('Charge/UAC Appl','e'); 
		} else {
			xl('Charge','e'); 
		}
		?>
		</th>
	<?php } else { ?>
		<th style="text-align: right;"><?php xl('UAC Amt Applied','e'); ?></th>
	<?php } ?>
	<?php if(!$chg_only) { ?>
		<th style="text-align: right;"><?php xl('Payment/UAC Tot','e'); ?></th>
		<th style="text-align: right;"><?php xl('Adjustment','e'); ?></th>
	<?php } else { ?>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	<?php } ?>
	<?php if($chg_only || $pmt_only) { ?>
		<th style="text-align: right;"><?php xl('Total','e'); ?></th>
	<?php } else { ?>
		<th style="text-align: right;"><?php xl('Balance','e'); ?></th>
	<?php } ?>
 </thead>
 <?php
  }
  $orow = 0;
	$prev_encounter_id = -1;
	$hdr_printed = false;
	$prev_row = array();
	$credits = array();

  while ($erow = sqlFetchArray($res)) {
    $print = '';
    $csv = '';

		// WE NEED ONLY ENCOUNTERS THAT HAVE A BILLABLE ITEM OR CREDITS
		if($erow{'ct_fee'} < 1 && !$erow{'applied'}) continue;

		if($erow['encounter'] != $prev_encounter_id) {
			if($prev_encounter_id != -1) {
				if(!$chg_only) {
					if(count($credits) > 0) {
						if(!$hdr_printed) {
							PrintEncHeader($prev_row{'date'}, $prev_row{'reason'}, 
							$prev_row{'provider_id'}, $prev_row{'encounter'});
						}
						PrintCreditDetail($credits, $form_patient);
					}
				}
				if($hdr_printed) PrintEncFooter($prev_row{'encounter'});
				$hdr_printed = false;
			}
			$enc_units = $enc_chg = $enc_pmt = $enc_adj = $enc_bal = 0;
		}

		if(!$chg_only) $credits = GetAllCredits($erow['encounter'], $form_patient);

		// TO PRINT A NEW ENCOUNTER HEADER       
		if(!$hdr_printed) {
			if(!$pmt_only || count($credits) > 0) {
				PrintEncHeader($erow{'date'}, $erow{'reason'}, 
					$erow{'provider_id'}, $erow{'encounter'});
				$hdr_printed = true;
			}
		}
	
		if(!$pmt_only && $erow{'ct_fee'} > 0) {
			$code_desc = $erow['code_text'];
			if(strlen($code_desc) > 50) $code_desc = substr($code_desc,0,50).'...';
   		$bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
   		$print = "<tr bgcolor='$bgcolor'>";
			$print .= "<td class='detail'>".text($erow['code'])."</td>";
			$print .= "<td class='detail' colspan='2'>".text($code_desc)."</td>";
   		$who = ($erow['name'] == '') ? 'Self' : $erow['name'];
			$bill = substr($erow['bill_date'],0,10);
   		if($bill == '') { $bill = 'unbilled'; }
			$print .= "<td class='detail'>".text($bill).
					"&nbsp;/&nbsp;".text($who)."</td>";
			$print .= "<td class='detail' style='text-align: right;'>".
					text($erow['units'])."</td>";
   		$print .= "<td class='detail' style='text-align: right;'>".
					text(oeFormatMoney($erow['fee']))."</td>";
			$print .= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
			$print .= "</tr>\n";
	
     	$total_units  += $erow['units'];
     	$total_chg += $erow['fee'];
     	$total_bal += $erow['fee'];
     	$enc_units  += $erow['units'];
     	$enc_chg += $erow['fee'];
     	$enc_bal += $erow['fee'];
			$orow++;
		
     	if ($_REQUEST['form_csvexport']) { 
      		echo $csv;
     	} else { 
      		echo $print;
    	}
		}
		$prev_encounter_id = $erow{'encounter'};
		$prev_row = $erow;
  }
	if($prev_encounter_id != -1) {
		if(!$chg_only) {
			$credits = GetAllCredits($prev_encounter_id, $form_patient);
			if(count($credits) > 0) {
				if(!$hdr_printed) {
					PrintEncHeader($prev_row{'date'}, $prev_row{'reason'}, 
						$prev_row{'provider_id'}, $prev_row{'encounter'});
				}
				PrintCreditDetail($credits, $form_patient);
			}
		}
		if($hdr_printed) PrintEncFooter($prev_row{'encounter'});
	}
  // THIS IS THE ENC OF THE MAIN ENCOUNTER / CHARGE LOOP
	if(!$chg_only) {
		$uac = GetAllUnapplied($form_patient,$from_date,$to_date);
		if(count($uac) > 0) {
			if($orow) {
      	$bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
				echo "<tr bgcolor='#FFFFFF'><td class='bold' colspan='9'>Payments that have not been applied&nbsp;</td></tr>\n";
    	}
			PrintCreditDetail($uac, $form_patient, true);
  	}
	}
  if (!$_REQUEST['form_csvexport'] && $orow) {
    echo "<tr bgcolor='#DDFFFF'>\n";
		echo " <td colspan='2'>&nbsp;</td>";
    echo " <td class='bold' colspan='2'>" . xlt("Grand Total") ."</td>\n"; 
		if(!$pmt_only) {
    	echo " <td class='bold' style='text-align: right;'>&nbsp;". 
						$total_units ."</td>\n";
			echo " <td class='bold' style='text-align: right;'>&nbsp;". 
						text(oeFormatMoney($total_chg)) ."</td>\n";
		} else {
			echo " <td colspan='2'>&nbsp;</td>";
		}
		if(!$chg_only) {
			echo " <td class='bold' style='text-align: right;'>&nbsp;".  
						text(oeFormatMoney($total_pmt)) ."</td>\n";
   		echo " <td class='bold' style='text-align: right;'>&nbsp;".  
						text(oeFormatMoney($total_adj)) ."</td>\n";
		} else {
			echo " <td colspan='2'>&nbsp;</td>";
		}
    echo " <td class='bold' style='text-align: right;'>&nbsp;". 
						text(oeFormatMoney($total_bal)) . "</td>\n";
    echo " </tr>\n";
  ?>
    </table>
    <?php
  }
	echo "</div>\n";
}

if (! $_REQUEST['form_csvexport']) {
  if ( $_REQUEST['form_refresh'] && $orow <= 0) {
    echo "<span style='font-size:10pt;'>";
    echo xlt('No matches found. Try search again.');
    echo "</span>";
		echo '<script>document.getElementById("report_results").style.display="none";</script>';
		echo '<script>document.getElementById("controls").style.display="none";</script>';
  }
		
  if (!$_REQUEST['form_refresh'] && !$_REQUEST['form_csvexport']) { ?>
<div class='text'>
 	<?php echo xlt('Please input search criteria above, and click Submit to view results.' ); ?>
</div>
<?php } ?>
</form>
</body>

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
 top.restoreSession();
</script>
</html>
<?php
} // END OC NOT CSV EXPORT LOGIC
?>
