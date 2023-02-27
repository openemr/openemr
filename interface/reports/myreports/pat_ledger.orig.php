<?php
/**
 * This is a report to create a patient ledger of charges with payments
 * applied.
 *
 * Copyright (C) 20015-2020 Rich Genandt <rgenandt@gmail.com>
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
require_once($GLOBALS['srcdir'].'/sql-ledger.inc');
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
      "WHERE " .
      "ar_session.created_time >= ? AND ar_session.created_time <= ? " .
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
	$all = array();
	if(!$enc || !$pat) return($all);
  $sql = "SELECT activity.*, session.*, ins.name FROM ar_activity AS ".
    "activity LEFT JOIN ar_session AS session USING (session_id) ".
		"LEFT JOIN insurance_companies AS ins ON session.payer_id = ".
    "ins.id WHERE encounter=? AND pid=? AND activity.deleted = 0 ".
		"ORDER BY sequence_no";
  $result = sqlStatement($sql, array($enc, $pat));
  $iter = 0;
  while($row = sqlFetchArray($result)) {
    $all[$iter] = $row;
    $iter++;
  }
	return($all);
}

function PrintEncHeader($dt, $rsn, $dr) {
	global $bgcolor, $orow;
 	$bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
	echo "<tr bgcolor='#FFFFFF'>";
	if(strlen($rsn) > 50) $rsn = substr($rsn,0,50).'...';
	echo "<td colspan='4'><span class='bold'>Encounter Dt / Rsn: </span>".
			"<span class='detail'>".text(substr($dt,0,10))." / ".text($rsn).
			"</span></td>";
	echo "<td colspan='5'><span class='bold'>Provider: </span>".
			"<span class='detail'>".text(UserNameFromID($dr))."</span></td>";
 	echo "</tr>\n";	
	$orow++;
}

function PrintEncFooter() {
	global $enc_units, $enc_chg, $enc_pmt, $enc_adj, $enc_bal;
	echo "<tr bgcolor='#DDFFFF'>";
  echo "<td colspan='3'>&nbsp;</td>";
	echo "<td class='detail'>Encounter Balance:</td>";
 	echo "<td class='detail' style='text-align: right;'>".
		text($enc_units)."&nbsp;&nbsp;</td>";
 	echo "<td class='detail' style='text-align: right;'>".
		text(oeFormatMoney($enc_chg))."&nbsp;</td>";
 	echo "<td class='detail' style='text-align: right;'>".
		text(oeFormatMoney($enc_pmt))."&nbsp;</td>";
 	echo "<td class='detail' style='text-align: right;'>".
		text(oeFormatMoney($enc_adj))."&nbsp;</td>";
 	echo "<td class='detail' style='text-align: right;'>".
		text(oeFormatMoney($enc_bal))."&nbsp;</td>";
	echo "</tr>\n";
}

function PrintCreditDetail($detail, $pat, $unassigned=false) {
	global $enc_pmt, $total_pmt, $enc_adj, $total_adj, $enc_bal, $total_bal;
	global $bgcolor, $orow, $enc_units, $enc_chg;
  foreach($detail as $pmt) {
		if($unassigned) {
			if(($pmt['pay_total'] - $pmt['applied']) == 0) continue;
		}
    $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
		$print = "<tr bgcolor='$bgcolor'>";
	  $print .= "<td class='detail'>&nbsp;</td>";
		$method = ListLook($pmt['payment_method'],'payment_method');
		$desc = $pmt['description'];
    $ref = $pmt['reference'];
    if($unassigned) {
      $memo = ListLook($pmt['adjustment_code'],'payment_adjustment_code');
    } else {
		  $memo = $pmt['memo'];
    }
		$description = $method;
		if($ref) {
			if($description) { $description .= ' - '; }
			$description .= $ref;
		}
		if($desc) {
			if($description) { $description .= ': '; }
			$description .= $desc;
		}
		if($memo) {
			if($description) { $description .= ' '; }
			$description .= '['.$memo.']';
		}
		$print .= "<td class='detail' colspan='2'>".
                                      text($description)."&nbsp;</td>";
		$payer = ($pmt['name'] == '') ? 'Patient' : $pmt['name'];
    if($unassigned) {
      $pmt_date = substr($pmt['post_to_date'],0,10);
    } else {
      $pmt_date = substr($pmt['post_time'],0,10);
    }
    $print .= "<td class='detail'>".
            text($pmt_date)."&nbsp;/&nbsp;".text($payer)."</td>";
		$type = ListLook($pmt['payment_type'],'payment_type');
		$print .= "<td class='detail'>".text($type)."&nbsp;</td>";
    if($unassigned) {
		  $pmt_amt = $pmt['pay_total'] - $pmt['applied'];
			$uac_bal = oeFormatMoney($pmt_amt * -1);
			$uac_appl = oeFormatMoney($pmt['applied']);
			$uac_total = oeFormatMoney($pmt['pay_total']);
			$pmt_amt = $pmt['pay_total'];
		  $total_pmt = $total_pmt - $uac_bal;
    } else {
			$uac_total = '';
			$uac_bal = '';
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
		$print .= "<td class='detail' style='text-align: right;'>".text($uac_appl)."&nbsp;</td>";
   	$print .= "<td class='detail' style='text-align: right;'>".text($print_pmt)."&nbsp;</td>";
   	$print .= "<td class='detail' style='text-align: right;'>".text($print_adj)."&nbsp;</td>";
		$print .= "<td class='detail' style='text-align: right;'>".text($uac_bal)."&nbsp;</td>";
    $print .= "</tr>\n";
		echo $print;
		if($pmt['follow_up_note'] != '') {
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

if(!isset($_REQUEST['form_from_date'])) { $_REQUEST['form_from_date'] = ''; }
if(!isset($_REQUEST['form_to_date'])) { $_REQUEST['form_to_date'] = ''; }
if(!isset($_REQUEST['form_facility'])) { $_REQUEST['form_facility'] = ''; }
if(!isset($_REQUEST['form_provider'])) { $_REQUEST['form_provider'] = ''; }
if(!isset($_REQUEST['form_patient'])) { $_REQUEST['form_patient'] = $pid; }
if(!isset($_REQUEST['form_csvexport'])) { $_REQUEST['form_csvexport'] = ''; }
if(!isset($_REQUEST['form_refresh'])) { $_REQUEST['form_refresh'] = ''; }

$last_year = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$form_from_date = date('Y-m-d', $last_year);
if($_REQUEST['form_from_date']) {
  $form_from_date = fixDate($_REQUEST['form_from_date'], $last_year);
}
$form_to_date   = fixDate($_REQUEST['form_to_date']  , date('Y-m-d'));
$form_facility  = $_REQUEST['form_facility'];
$form_provider  = $_REQUEST['form_provider'];
$form_patient   = $_REQUEST['form_patient'];

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
  <td width='70%'>
	<div style='float:left'>
	<table class='text'>
		<tr>
			<td class='label'>
				<?php echo xlt('Facility'); ?>:
			</td>
			<td>
			<?php dropdown_facility($form_facility, 'form_facility', true); ?>
			</td>
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
		</tr><tr>
      <td colspan="2">
        <?php echo xlt('From'); ?>:&nbsp;&nbsp;&nbsp;&nbsp;
        <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr($form_from_date) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla("Click here to choose a date"); ?>'>
      </td>
      <td class='label'>
        <?php echo xlt('To'); ?>:
      </td>
      <td>
        <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr($form_to_date) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla("Click here to choose a date"); ?>'>
      </td>
      <td><span class='label'><?php xl('Patient','e'); ?>:&nbsp;&nbsp;</span>
        <input type='text' name='form_patient' id='form_patient' value="<?php echo $form_patient; ?>" onclick="sel_patient();" />
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

			<?php if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) { ?>
					<div id="controls">
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
</div> <!-- end of parameters -->

<?php
} // end not export
$from_date = $form_from_date . ' 00:00:00';
$to_date = $form_to_date . ' 23:59:59';

if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) {
  $rows = array();
  $sqlBindArray = array();
  $query = "SELECT b.code_type, b.code, b.code_text, b.pid, b.provider_id, ".
				"b.billed, b.payer_id, b.units, b.fee, b.bill_date, b.id, ".
        "ins.name, ".
        "fe.encounter, fe.date, fe.reason, fe.provider_id ".
        "FROM form_encounter AS fe ".
        "LEFT JOIN billing AS b ON b.pid=fe.pid AND b.encounter=fe.encounter ".
        "LEFT JOIN insurance_companies AS ins ON b.payer_id = ins.id ".
        "LEFT OUTER JOIN codes AS c ON c.code = b.code ".
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
	$query .= "AND ( b.code_type LIKE 'CPT%' || b.code_type LIKE 'HCP%' ) ";
	// $query .= "(UPPER(SUBSTR(b.code_type,0,3)) = 'HCP') ) ";
  $query .= "AND activity > 0 ORDER BY fe.date, fe.id ";
  $res = sqlStatement($query,$sqlBindArray);
 
  if ($_REQUEST['form_csvexport']) {
  // CSV headers:
    if (true) {
      echo '"Code/Enc Dt",';
      echo '"Description",';
      echo '"Billed/Who",';
      echo '"Type/Units",';
		  echo '"Chg/Pmt Amount",'."\n";
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
    <td class="title"><?php echo $facility{'addr'}; ?></td>
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
  <th><?php xl('Billed','e'); ?> / <?php xl('Who','e'); ?></th>
  <th><?php xl('Type','e'); ?> / <?php xl('Units','e'); ?></th>
  <th><?php xl('Charge/UAC Appl','e'); ?></th>
	<th><?php xl('Payment/UAC Tot','e'); ?></th>
	<th><?php xl('Adjustment','e'); ?></th>
	<th><?php xl('Balance','e'); ?></th>
 </thead>
 <?php
  }
  $orow = 0;
	$prev_encounter_id = -1;
	$hdr_printed = false;
	$prev_row = array();

  while ($erow = sqlFetchArray($res)) {
    $print = '';
    $csv = '';

		if($erow['encounter'] != $prev_encounter_id) {
			if($prev_encounter_id != -1) {
				$credits = GetAllCredits($prev_encounter_id, $form_patient);
				if(count($credits) > 0) {
					if(!$hdr_printed) {
						PrintEncHeader($prev_row{'date'}, 
												$prev_row{'reason'}, $prev_row{'provider_id'});
					}
					PrintCreditDetail($credits, $form_patient);
				}
				if($hdr_printed) PrintEncFooter();
				$hdr_printed = false;
			}
			$enc_units = $enc_chg = $enc_pmt = $enc_adj = $enc_bal = 0;
		}
		if($erow{'id'}) {
			// Now print an encounter heading line -
			if(!$hdr_printed) {
				PrintEncHeader($erow{'date'}, 
									$erow{'reason'}, $erow{'provider_id'});
				$hdr_printed = true;
			}
	
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
		$credits = GetAllCredits($prev_encounter_id, $form_patient);
		if(count($credits) > 0) {
			if(!$hdr_printed) {
				PrintEncHeader($prev_row{'date'}, 
									$prev_row{'reason'}, $prev_row{'provider_id'});
			}
			PrintCreditDetail($credits, $form_patient);
		}
		if($hdr_printed) PrintEncFooter();
	}
    // This is the end of the encounter/charge loop - 
	$uac = GetAllUnapplied($form_patient,$from_date,$to_date);
	if(count($uac) > 0) {
		if($orow) {
      $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
			echo "<tr bgcolor='#FFFFFF'><td colspan='9'>&nbsp;</td></tr>\n";
    }
		PrintCreditDetail($uac, $form_patient, true);
  }
  if (!$_REQUEST['form_csvexport'] && $orow) {
    echo "<tr bgcolor='#DDFFFF'>\n";
		echo " <td colspan='2'>&nbsp;</td>";
    echo " <td class='bold' colspan='2'>" . xlt("Grand Total") ."</td>\n"; 
    echo " <td class='bold' style='text-align: right;'>". 
						text($total_units) ."&nbsp;&nbsp;</td>\n";
		echo " <td class='bold' style='text-align: right;'>". 
						text(oeFormatMoney($total_chg)) ."&nbsp;</td>\n";
		echo " <td class='bold' style='text-align: right;'>".  
						text(oeFormatMoney($total_pmt)) ."&nbsp;</td>\n";
   	echo " <td class='bold' style='text-align: right;'>".  
						text(oeFormatMoney($total_adj)) ."&nbsp;</td>\n";
    echo " <td class='bold' style='text-align: right;'>". 
						text(oeFormatMoney($total_bal)) . "&nbsp;</td>\n";
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

<!-- stuff for the popup calendar -->

<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
 top.restoreSession();
</script>
</html>
<?php
} // End not csv export
?>
