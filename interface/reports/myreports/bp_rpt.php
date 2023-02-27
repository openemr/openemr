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
require_once("$srcdir/../custom/code_types.inc.php");
require_once("$srcdir/wmt-v2/wmtSettings.inc");
require_once("$srcdir/classes/InsuranceCompany.class.php");

use OpenEMR\Core\Header;

if(!isset($GLOBALS['wmt::link_appt_ins'])) 
		$GLOBALS['wmt::link_appt_ins'] = '';
if(!isset($GLOBALS['wmt::suppress_ins_sel::bp_rpt'])) 
		$GLOBALS['wmt::suppress_ins_sel::bp_rpt'] = '';
if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';

if($GLOBALS['date_display_format'] == 1) {
	$date_img_fmt = '%m/%d/%Y';
	$date_title_fmt = 'MM/DD/YYYY';
} else if($GLOBALS['date_display_format'] == 2) {
	$date_img_fmt = '%d/%m/%Y';
	$date_title_fmt = 'DD/MM/YYYY';
} else {
	$date_img_fmt = '%Y-%m-%d';
	$date_title_fmt = 'YYYY-MM-DD';
}
// THIS IS USED FOR THE INSURANCE TYPE DROP DOWN
$ins = new InsuranceCompany();

$rpt_lines = 0;
$vital_fields = sqlListFields('form_vitals');
set_time_limit(0);

function ListSel($thisField, $thisList, $empty_label = '') {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE list_id=? AND ".
		"seq >= 0 ORDER BY seq, title",array($thisList));
	if($empty_label) {
  	echo "<option value=''";
  	echo ">$empty_label&nbsp;</option>";
	}
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

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function build_lists_subquery() {
	global $binds, $form_from_date, $form_to_date, $diags;
	$part = 'AND l.activity > 0 AND l.type = "medical_problem" AND ('; 
	$first = true;
	foreach($diags as $diag) {
		if(!$first) $part .= 'OR ';
		$part .= 'l.diagnosis LIKE "%'.$diag.'%" ';
		$first = false;
	}
	$part .= ') AND (l.enddate IS NULL OR l.enddate = "0000-00-00" ';
	/* RON HAS THIS MARKED AS WRONG - BAD LOGIC I THINK
	if($form_from_date) {
		$part .= ' AND (l.enddate <= ? ';
		$binds[] = $form_from_date;
	}
	if($form_to_date) {
		$part .= ' AND l.enddate >= ?)';
		$binds[] = $form_to_date;
	} else {
		$part .= ' AND l.enddate >= ?)';
		$binds[] = $form_from_date;
	}
	*/
	$part .= ')';
	return $part;
}

function build_clinical_rule_subquery() {
	global $binds, $form_from_date, $form_to_date;
	$part = 'LEFT JOIN (SELECT pid AS rpid, result, date ' .
		'FROM rule_patient_data WHERE item = "act_hemo_a1c" ';
	if($_POST['form_min_a1c'] != '') {
		$part .= 'AND CAST(result AS DECIMAL) >= ? ';
		$binds[] = $_POST['form_min_a1c'];
	}
	if($_POST['form_max_a1c'] != '') {
		$part .= 'AND CAST(result AS DECIMAL) <= ? ';
		$binds[] = $_POST['form_max_a1c'];
	}
	if($_POST['form_min_a1c'] != '' && $_POST['form_max_a1c'] != '') {
		if($form_from_date) {
			$part .= ' AND date >= ? ';
			$binds[] = $form_from_date;
		}
		if($form_to_date) {
			$part .= ' AND date <= ?';
			$binds[] = $form_to_date;
		} else {
			$part .= ' AND date <= ?';
			$binds[] = $form_from_date;
		}
	}
	$part .= 'AND complete="YES" ORDER BY date DESC) AS rpd ' .
		'ON rpd.rpid = p.pid ';
	return $part;
}

function build_lab_result_subquery() {
	global $binds, $from_date, $to_date;
	$sql = 'SELECT option_id FROM list_options WHERE list_id = ?';
	$res = sqlStatement($sql, array('A1c_Report_Lab_Result'));
	$item = '';
	while($row = sqlFetchArray($res)) {
		$item[] = $row{'option_id'};
	}
	if(!$item) return '';
	
	$part = 'LEFT JOIN (SELECT result, result_text, date, date_ordered, '.
		'procedure_report_id, procedure_result_id, po.patient_id '.
		'result_code, FROM procedure_result '.
		'LEFT JOIN procedure_report AS pr USING (procedure_report_id) '.
		'LEFT JOIN procedure_order AS po ON (pr.procedure_order_id = '.
		'po.procedure_order_id) LEFT JOIN procedure_order_code AS '.
		'pc ON (pc.procedure_order_id = pc.procedure_order_id) WHERE ';
	$first = true;
	foreach($item AS $test_code) {
		if($first) $part .= '(';
		if(!$first) $part .= ' OR ';
		$part .= 'result_code = ?';
		$binds[] = $test_code;
		$first = false;
	}
	$part .= ') ';
	if($_POST['form_min_a1c'] != '') {
		$part .= 'AND CAST(result AS DECIMAL) >= ? ';
		$binds[] = $_POST['form_min_a1c'];
	}
	if($_POST['form_max_a1c'] != '') {
		$part .= 'AND CAST(result AS DECIMAL) <= ? ';
		$binds[] = $_POST['form_max_a1c'];
	}
	if($from_date) {
		$part .= ' AND date_ordered >= ? ';
		$binds[] = $from_date;
	}
	if($to_date) {
		$part .= ' AND date_ordered <= ?';
		$binds[] = $to_date;
	} else {
		$part .= ' AND date_ordered <= ?';
		$binds[] = $from_date;
	}
	$part .= ') AS lab ON (lab.patient_id = p.pid) ';
	return $part;
}

function build_description_and_value($row) {
	global $desc, $val;
	if($row{'bps'} || $row{'bpd'}) {
		if( ($_POST['form_min_bps'] != '' && $row{'bps'} >= $_POST['form_min_bps']) ||
	 	($_POST['form_max_bps'] != '' && $row{'bps'} <= $_POST['form_max_bps']) ||
	 	($_POST['form_min_bpd'] != '' && $row{'bpd'} >= $_POST['form_min_bpd']) ||
	 	($_POST['form_max_bpd'] != '' && $row{'bpd'} <= $_POST['form_max_bpd']) ) {
			$desc = 'BP';
			$val = $row{'bps'} . '/' . $row{'bpd'};
		}
	}
	if($row{'BMI'} && $row{'BMI'} != '0' && $row{'BMI'} != 0.0) {
		if( ($_POST['form_min_bmi'] != '' && $row{'BMI'} >= $_POST['form_min_bmi']) ||
	 	($_POST['form_max_bmi'] != '' && $row{'BMI'} <= $_POST['form_max_bmi']) ) {
			if($desc != '') $desc .= ', ';
			if($val != '') $val .= ', ';
			$desc .= 'BMI';
			$val .= $row{'BMI'};
		}
	}
	if(!isset($row{'v_a1c'})) $row{'v_a1c'} = '';
	if(!isset($row{'l_a1c'})) $row{'l_a1c'} = '';
	if($row{'l_a1c'}) {
		if( ($_POST['form_min_a1c'] != '' && $row{'l_a1c'} >= $_POST['form_min_a1c']) ||
	 	($_POST['form_max_a1c'] != '' && $row{'l_a1c'} <= $_POST['form_max_a1c']) ) {
			if($desc != '') $desc .= ', ';
			if($val != '') $val .= ', ';
			$desc .= 'A1c';
			$val .= $row{'l_a1c'};
		}
	} else if($row{'v_a1c'}) {
		if( ($_POST['form_min_a1c'] != '' && $row{'v_a1c'} >= $_POST['form_min_a1c']) ||
	 	($_POST['form_max_a1c'] != '' && $row{'v_a1c'} <= $_POST['form_max_a1c']) ) {
			if($desc != '') $desc .= ', ';
			if($val != '') $val .= ', ';
			$desc .= 'A1c';
			$val .= $row{'v_a1c'};
		}
	/*
	} else if($row{'a1c'}) {
		if( ($_POST['form_min_a1c'] != '' && $row{'a1c'} >= $_POST['form_min_a1c']) ||
	 	($_POST['form_max_a1c'] != '' && $row{'a1c'} <= $_POST['form_max_a1c']) ) {
			if($desc != '') $desc .= ', ';
			if($val != '') $val .= ', ';
			$desc .= 'A1c';
			$val .= $row{'a1c'};
		}
		*/
	}
}

$last_year = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$default_date = date('Y-m-d', $last_year);
if(!isset($_POST['form_from_date'])) {
	$_POST['form_from_date'] = $default_date;
} else $_POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
if(!isset($_POST['form_to_date'])) {
	$_POST['form_to_date'] = date('Y-m-d');
} else $_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_min_bpd'])) $_POST['form_min_bpd'] = '';
if(!isset($_POST['form_max_bpd'])) $_POST['form_max_bpd'] = '';
if(!isset($_POST['form_min_bps'])) $_POST['form_min_bps'] = '';
if(!isset($_POST['form_max_bps'])) $_POST['form_max_bps'] = '';
if(!isset($_POST['form_min_age'])) $_POST['form_min_age'] = '';
if(!isset($_POST['form_max_age'])) $_POST['form_max_age'] = '';
if(!isset($_POST['form_min_bmi'])) $_POST['form_min_bmi'] = '';
if(!isset($_POST['form_max_bmi'])) $_POST['form_max_bmi'] = '';
if(!isset($_POST['form_min_a1c'])) $_POST['form_min_a1c'] = '';
if(!isset($_POST['form_max_a1c'])) $_POST['form_max_a1c'] = '';
if(!isset($_POST['form_ins_type'])) $_POST['form_ins_type'] = '';
if(!isset($_POST['form_diags'])) $_POST['form_diags'] = '';
if(!isset($_POST['form_pat_sex'])) $_POST['form_pat_sex'] = '';
if(!isset($_POST['form_pat_race'])) $_POST['form_pat_race'] = '';
if(!isset($_POST['form_pat_ethnicity'])) $_POST['form_pat_ethnicity'] = '';
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_provider = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_provider_type = isset($_POST['form_provider_type']) ? $_POST['form_provider_type'] : 'pat';
$form_csvexport = $_POST['form_csvexport'];
$form_diags = $_POST['form_diags'];
$tmp_diag_desc = '';
$diags = array();
if($form_diags != '') $diags = explode(';',$form_diags);
if(count($diags)) {
	foreach($diags as $diag) {
		list($type, $code) = explode(':', $diag);
		if($tmp_diag_desc) $tmp_diag_desc .= ',';	
		$tmp_diag_desc .= $code;
	}
}
$form_details = true;

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=analytics_data.csv");
  header("Content-Description: File Transfer");
  // CSV COLUMN HEADERS:
  if ($form_details) {
		echo '"PID",';
		echo '"Patient Name",';
		echo '"DOB",';
		echo '"AGE",';
		echo '"Phone",';
		echo '"Provider",';
		echo '"Insurance",';
		echo '"Facility",';
		echo '"Diag",';
    echo '"Date",';
		echo '"Description",';
    echo '"Value"' . "\n";
  } else {
  }
	// END OF EXPORT LOGIC
} else {
?>
<html>
<head>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<style type="text/css">
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

@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}
</style>

<title><?php echo xl('Patient Analytics Report') ?></title>
</head>

<body class="body_top">

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Patient Analytics'); ?></span>

<form method='post' action='bp_rpt.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='80%'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'><?php echo xl('From'); ?>:</td>
			<td colspan="2"><input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter date in <?php echo $date_title_fmt; ?> format' />&nbsp;&nbsp;<img src='../../pic/show_calendar.gif' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' title='<?php echo xl('Click here to choose a date'); ?>'/></td>
			<td colspan="2" class='label' style="text-align: left; border-right: solid 1px black;"><?php echo xl('To'); ?>:&nbsp;
			<input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter date in <?php echo $date_title_fmt; ?> format' />&nbsp;&nbsp;<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' title='<?php echo xl('Click here to choose a date'); ?>' /></td>
			<td class="text" colspan="2">
				<input name="form_provider_type" id="form_provider_type_pat" type="radio" value="pat" <?php echo $form_provider_type == 'pat' ? 'checked="checked"' : ''; ?> /><label for="form_provider_type_pat">Patient Dr</label>&nbsp;or&nbsp;
				<input name="form_provider_type" id="form_provider_type_visit" type="radio" value="visit" <?php echo $form_provider_type == 'visit' ? 'checked="checked"' : ''; ?> /><label for="form_provider_type_visit">Visit Dr</label></td>
      <td class="label">Select:</td>
			<td><?php
      $query = "SELECT id, username, lname, fname FROM users " .
				"WHERE authorized=1 AND username!='' AND active='1' ".
				"AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
				"UPPER(specialty) LIKE '%SUPERVISOR%') ".
				"ORDER BY lname, fname";
      $ures = sqlStatement($query);

      echo "   <select name='form_provider'>\n";
      echo "    <option value=''";
			if($form_provider == '') { echo " selected"; }
			echo ">-- " . xl('ALL') . " --</option>\n";

      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $form_provider) echo " selected";
        echo ">" . htmlspecialchars($urow['lname'] . ", " . $urow['fname'], ENT_QUOTES) . "</option>\n";
      }
      echo "   </select>\n";
      ?></td>
		</tr>
		<tr>
			<td class='label'><?php echo xl('BPS'); ?>:</td>
			<td class='label'><?php echo xl('Values'); ?> &gt;= </td>
			<td><input name="form_min_bps" id="form_min_bps" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_min_bps'], ENT_QUOTES); ?>" title="Enter the lowest value you wish to include in the report" /></td>
			<td class='label'><?php echo xl('And'); ?> &lt;= </td>
			<td style="border-right: solid 1px black;"><input name="form_max_bps" id="form_max_bps" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_max_bps'], ENT_QUOTES); ?>" title="Enter the highest value you wish to include in the report" /></td>

			<td class="label" style="text-align: left;">Ethnicity:&nbsp;</td>
			<td><select name="form_pat_ethnicity" id="form_pat_ethnicity">
				<?php ListSel($_POST['form_pat_ethnicity'], 'ethnicity', '-- ALL --'); ?>
			</select></td>
			<td class="label">Patient Race:&nbsp;</td>
			<td><select name="form_pat_race" id="form_pat_race">
				<?php ListSel($_POST['form_pat_race'], 'race', '-- ALL --'); ?>
			</select></td>

		</tr>
		<tr>
			<td class='label'><?php echo xl('BPD'); ?>:</td>
			<td class='label'><?php echo xl('Values'); ?> &gt;= </td>
			<td><input name="form_min_bpd" id="form_min_bpd" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_min_bpd'], ENT_QUOTES); ?>" title="Enter the lowest value you wish to include in the report" /></td>
			<td class='label'><?php echo xl('And'); ?> &lt;= </td>
			<td style="border-right: solid 1px black;"><input name="form_max_bpd" id="form_max_bpd" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_max_bpd'], ENT_QUOTES); ?>" title="Enter the highest value you wish to include in the report" /></td>

			<td class="label" style="text-align: left;">Gender:&nbsp;</td>
			<td><select name="form_pat_sex" id="form_pat_sex">
				<option value="" <?php echo $_POST['form_pat_sex'] == '' ? 'selected="selected"' : ''; ?> >- ALL -</option>
				<option value="Male" <?php echo $_POST['form_pat_sex'] == 'Male' ? 'selected="selected"' : ''; ?> >Male</option>
				<option value="Femaie" <?php echo $_POST['form_pat_sex'] == 'Female' ? 'selected="selected"' : ''; ?> >Female</option>
			</select></td>
			<?php if(!$GLOBALS['wmt::suppress_ins_sel::bp_rpt']) { ?>
			<td class="label">Insurance Type:&nbsp;</td>
			<td><select name="form_ins_type" id="form_ins_type">
				<?php foreach($ins->ins_claim_type_array as $key => $code) {
					$desc = $ins->ins_type_code_array[$key];
					if($desc == '') $desc = '-- ALL --';
					echo '<option value="' . $code . '"';
					if($code == $_POST['form_ins_type']) echo ' selected="selected"';
					echo '>' . $desc . '</option>';
				} ?>
			</select></td>
			<?php } else { ?>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<?php } ?>
		</tr>
		<tr>
			<td class='label'><?php echo xl('BMI'); ?>:</td>
			<td class='label'><?php echo xl('Values'); ?> &gt;= </td>
			<td><input name="form_min_bmi" id="form_min_bmi" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_min_bmi'], ENT_QUOTES); ?>" title="Enter the lowest value you wish to include in the report" /></td>
			<td class='label'><?php echo xl('And'); ?> &lt;= </td>
			<td style="border-right: solid 1px black;"><input name="form_max_bmi" id="form_max_bmi" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_max_bmi'], ENT_QUOTES); ?>" title="Enter the highest value you wish to include in the report" /></td>

			<td class="label" colspan="2" style="text-align: left;">Type A Diagnosis to Include:</td>
			<td><input name="tmp_diag_chc" id="tmp_diag_chc" type="text" style="width: 80px;" title="Type in a diagnosis code" /></td>
			<td><div style="float: left; margin-left: 8px;"><a href="javascript:;" tabindex="-1" class="css_button" onclick="include_diag();"><span>Add</span></a>
			<div style="float: right; margin-right: 8px;"><a href="javascript:;" tabindex="-1" class="css_button" onclick="get_diagnosis()"><span>Search</span></a></td>
		</tr>
		<!-- RON HAS THIS OUT AT SFA TOO tr>
			<td class='label'><?php echo xl('A1c'); ?>:</td>
			<td class='label'><?php echo xl('Values'); ?> &gt;= </td>
			<td><input name="form_min_a1c" id="form_min_a1c" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_min_a1c'], ENT_QUOTES); ?>" title="Enter the lowest value you wish to include in the report" /></td>
			<td class='label'><?php echo xl('And'); ?> &lt;= </td>
			<td style="border-right: solid 1px black;"><input name="form_max_a1c" id="form_max_a1c" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_max_a1c'], ENT_QUOTES); ?>" title="Enter the highest value you wish to include in the report" /></td>
		</tr -->
		<tr>
			<td class='label'><?php echo xl('Age'); ?>:</td>
			<td class='label'><?php echo xl('Values'); ?> &gt;= </td>
			<td><input name="form_min_age" id="form_min_age" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_min_age'], ENT_QUOTES); ?>" title="Enter the lowest value you wish to include in the report" /></td>
			<td class='label'><?php echo xl('And'); ?> &lt;= </td>
			<td style="border-right: solid 1px black;"><input name="form_max_age" id="form_max_age" type="text" style="width: 80px;" value="<?php echo htmlspecialchars($_POST['form_max_age'], ENT_QUOTES); ?>" title="Enter the highest value you wish to include in the report" /></td>
			<td colspan="5" rowspan="5" class="bold"><textarea name="tmp_diag_desc" id="tmp_diag_desc" readonly="readonly" style="width: 96%; border: none; background: transparent; margin-left: 1%; margin-right: 1%;" rows="3"><?php echo $tmp_diag_desc; ?></textarea></td>
			<input name="form_diags" id="form_diags" type="hidden" value="<?php echo $form_diags; ?>" /></td>
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
	<div id="report_results" style="width: 100%;">
	<table style="width: 100%;">
 	<thead>
  <th> <?php echo xl('PID'); ?> </th>
  <th> <?php echo xl('Patient Name'); ?> </th>
  <th> <?php echo xl('DOB'); ?> </th>
  <th> <?php echo xl('Age'); ?> </th>
  <th> <?php echo xl('Phone'); ?> </th>
  <th> <?php echo xl('Provider'); ?> </th>
  <th> <?php echo xl('Facility'); ?> </th>
  <th> <?php echo xl('Diag'); ?> </th>
  <th> <?php echo xl('Date'); ?> </th>
  <th> <?php echo xl('Description'); ?> </th>
  <th> <?php echo xl('Value'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date . ' 00:00:00';
  $to_date   = $form_to_date . ' 23:59:59';
	$binds = array();

	$sql = 'SELECT f.form_id, fe.encounter, SUBSTRING(fe.date,1,10) AS dos, ' .
		'fe.provider_id AS serv_dr, v.bpd, v.bps, v.BMI, ';
		// THIS WOULD ONLY BE USED IF THE CLINICAL RULE SUB-QUERY IS ENABLED
		// 'rpd.date AS a1c_date, rpd.result AS a1c, ';
	if(count($diags)) $sql .= 'l.diagnosis, ';
	// SAVE IN CASE TAKING THE QUERY TO ANOTHER 
	if($_POST['form_min_a1c'] != '' || $_POST['form_max_a1c'] != '') {
		$sql .= 'lab.result AS l_a1c, ';
	}
	if(in_array('HgbA1c', $vital_fields)) $sql .= 'v.HgbA1c AS v_a1c, ';
	if($GLOBALS['wmt::client_id'] == '5rivers') $sql .= 'v.hemoglobin AS v_a1c, ';
	$sql .= 'p.lname, p.fname, p.mname, DOB, phone_home, pubpid, p.pid, sex, '.
		'u.lname AS drlname, u.fname AS drfname, u.mname AS drmname, '.
		'vdr.lname AS vdrlname, vdr.fname AS vdrfname, vdr.mname AS vdrmname, '.
		'facility.name AS facility_name, '.
		'ic.name AS ins_name '.
		'FROM forms AS f LEFT JOIN form_encounter AS fe USING(encounter) '.
		'LEFT JOIN facility ON (facility_id = facility.id) '.
		'LEFT JOIN form_vitals AS v ON (f.form_id = v.id) '.
		'LEFT JOIN patient_data AS p ON (fe.pid = p.pid) ';

	if($GLOBALS['wmt::link_appt_ins']) {
		$sql .= 'LEFT JOIN appointment_encounter AS ae ON '.
			'(f.encounter = ae.encounter) LEFT JOIN openemr_postcalendar_events '.
			'AS oe ON (ae.eid = oe.pc_eid) LEFT JOIN insurance_companies AS ic '.
			'ON (oe.pc_insurance = ic.id) ';
	} else {
		$ins_fields = sqlListFields('insurance_data');
		$sql .= 'LEFT JOIN insurance_data AS id ON (id.id = (SELECT i.id '.
			'FROM insurance_data AS i WHERE fe.pid = i.pid '.
			'AND i.type = "primary" AND i.date <= SUBSTRING(fe.date,1,10) '.
			'AND i.provider AND i.date != "0000-00-00" ';
		if(in_array('termination_date', $ins_fields)) {
			$sql .= 'AND (termination_date = "" OR termination_date IS NULL '.
				'OR termination_date = "0000-00-00" OR termination_date > '.
				'SUBSTRING(fe.date,1,10)) ';
		}
		$sql .= 'ORDER BY date DESC LIMIT 1) ) '.
			'LEFT JOIN insurance_companies AS ic ON (id.provider = ic.id) ';
	}

	// BUILD THE QUERY PART TO PULL THE AiC FROM LAB RESULTS HERE
	if($_POST['form_min_a1c'] != '' || $_POST['form_max_a1c'] != '') {
		$part = build_lab_result_subquery();
		$sql .= $part;
	}
	// $part = build_clinical_rule_subquery();
	// $sql .= $part;

	$sql .= 'LEFT JOIN users AS u ON (p.providerID = u.id) '.
		'LEFT JOIN users AS vdr ON (fe.provider_id = vdr.id) ';
	if(count($diags)) $sql .= 'RIGHT JOIN lists AS l ON (fe.pid = l.pid) ';
	$sql .= 'WHERE (fe.date >= ? AND fe.date <= ?) AND f.deleted = 0 ';
	$binds[] = $from_date;
	$binds[] = $to_date;
	if(count($diags)) {
		$part = build_lists_subquery();
		$sql .= $part;
	}
	$sql .= ' AND f.formdir = "vitals" ';
	if($_POST['form_min_bps'] != '') {
		$sql .= 'AND CAST(bps AS UNSIGNED) >= ? ';
		$binds[] = $_POST['form_min_bps'];
	}
	if($_POST['form_max_bps'] != '') {
		$sql .= 'AND CAST(bps AS UNSIGNED) <= ? ';
		$binds[] = $_POST['form_max_bps'];
	}
	if($_POST['form_min_bpd'] != '') {
		$sql .= 'AND CAST(bpd AS UNSIGNED) >= ? ';
		$binds[] = $_POST['form_min_bpd'];
	}
	if($_POST['form_max_bpd'] != '') {
		$sql .= 'AND CAST(bpd AS UNSIGNED) <= ? ';
		$binds[] = $_POST['form_max_bpd'];
	}
	if($_POST['form_min_bmi'] != '') {
		$sql .= 'AND bmi >= ? ';
		$binds[] = $_POST['form_min_bmi'];
	}
	if($_POST['form_max_bmi'] != '') {
		$sql .= 'AND bmi <= ? ';
		$binds[] = $_POST['form_max_bmi'];
	}
	if($_POST['form_min_a1c'] != '') {
		$sql .= 'AND CAST(lab.result AS DECIMAL) >= ? ';
		$binds[] = $_POST['form_min_a1c'];
	}
	if($_POST['form_max_a1c'] != '') {
		$sql .= 'AND CAST(lab.result AS DECIMAL) <= ? ';
		$binds[] = $_POST['form_max_a1c'];
	}
	/***
	if(in_array('HgbA1c', $vital_fields)) {
		if($_POST['form_min_a1c'] != '') {
			$sql .= 'AND CAST(v.HgbA1c AS DECIMAL) >= ? ';
			$binds[] = $_POST['form_min_a1c'];
		}
		if($_POST['form_max_a1c'] != '') {
			$sql .= 'AND CAST(v.HgbA1c AS DECIMAL) <= ? ';
			$binds[] = $_POST['form_max_a1c'];
		}
	}
	if($GLOBALS['wmt::client_id'] == '5rivers') {
		if($_POST['form_min_a1c'] != '') {
			$sql .= 'AND CAST(v.hemoglobin AS DECIMAL) >= ? ';
			$binds[] = $_POST['form_min_a1c'];
		}
		if($_POST['form_max_a1c'] != '') {
			$sql .= 'AND CAST(v.hemoglobin AS DECIMAL) <= ? ';
			$binds[] = $_POST['form_max_a1c'];
		}
	}
	***/
	if($form_provider) {
		if($form_provider_type == 'pat') {
			$sql .= 'AND providerID = ? ';
		} else {
			$sql .= 'AND provider_id = ? ';
		}
		$binds[] = $form_provider;
	}
	if($_POST['form_pat_sex'] != '') {
		$sql .= 'AND sex = ? ';
		$binds[] = $_POST['form_pat_sex'];
	}
	if($_POST['form_pat_race'] != '') {
		$sql .= 'AND race = ? ';
		$binds[] = $_POST['form_pat_race'];
	}
	if($_POST['form_pat_ethnicity'] != '') {
		$sql .= 'AND ethnicity = ? ';
		$binds[] = $_POST['form_pat_ethnicity'];
	}
	if($_POST['form_ins_type'] != '') {
		$sql .= 'AND ic.ins_type_code = ? ';
		$binds[] = $_POST['form_ins_type'];
	}
	$sql .= 'GROUP BY pubpid ORDER BY pubpid ASC';

	// echo "Query: $sql<br>\n";
	// echo "Binds: ";
	// print_r($binds);
	// echo "<br>\n";


	$fres = sqlStatement($sql, $binds);

	if($_POST['form_min_a1c'] != '' || $_POST['form_max_a1c'] != '') {
		if(checkSettingMode('wmt::bp_rpt::use_labs')) {
			$sql = 'SELECT option_id FROM list_options WHERE list_id = ?';
			$res = sqlStatement($sql, array('A1c_Report_Lab_Result'));
			$item = '';
			while($row = sqlFetchArray($res)) {
				$item[] = $row{'option_id'};
			}
			if(!$item) return '';
			
			$sql = 'SELECT result, result_text, date, '.
				'procedure_report_id, procedure_result_id, po.patient_id '.
				'FROM procedure_result '.
				'LEFT JOIN procedure_report AS pr USING (procedure_report_id) '.
				'LEFT JOIN procedure_order AS po ON (pr.procedure_order_id = '.
				'po.procedure_order_id) WHERE ';
			$first = true;
			foreach($item AS $test_code) {
				if($first) $part .= '(';
				if(!$first) $part .= ' OR ';
				$sql .= 'result_code = ?';
				$binds[] = $test_code;
				$first = false;
			}
			$sql .= ') ';
			if($_POST['form_min_a1c'] != '') {
				$sql .= 'AND CAST(result AS DECIMAL) >= ? ';
				$binds[] = $_POST['form_min_a1c'];
			}
			if($_POST['form_max_a1c'] != '') {
				$sql .= 'AND CAST(result AS DECIMAL) <= ? ';
				$binds[] = $_POST['form_max_a1c'];
			}
			if($from_date) {
				$sql .= ' AND date >= ? ';
				$binds[] = $from_date;
			}
			if($form_to_date) {
				$sql .= ' AND date <= ?';
				$binds[] = $to_date;
			} else {
				$sql .= ' AND date <= ?';
				$binds[] = $from_date;
			}
			$sql .= '';
		}
	}

	// echo "Query: $sql<br>\n";
	// echo "Binds: ";
	// print_r($binds);
	// echo "<br>\n";
	
  $bgcolor = '';
	$dtl_line = $rpt_line = 0;
	$desc = $val = '';
	global $desc, $val;

	while($dtl = sqlFetchArray($fres)) {
		$desc = $val = '';
		// echo "Row: ";
		// print_r($dtl);
		// echo "<br>\n";
		$age = getPatientAge($dtl{'DOB'});
		$age_dtl = explode(' ', $age);
		if(!isset($age_dtl[1])) $age_dtl[1] = 'year';
		if(strtolower($age_dtl[1]) != 'year') {
			$age = 1;
			if($age_detail[0] > 12) $age = 2;
		}
		if($_POST['form_min_age'] && $age < $_POST['form_min_age']) continue;
		if($_POST['form_max_age'] && $age > $_POST['form_max_age']) continue;
		if(!isset($dtl{'diagnosis'})) $dtl{'diagnosis'} = '';
		if($form_details) {
			build_description_and_value($dtl);
			if($form_csvexport) {
      	echo '"'.display_desc($dtl{'pubpid'}).'","';
      	echo display_desc($dtl{'fname'} . ' ' . $dtl{'lname'}) . '","';
      	echo oeFormatShortDate($dtl{'DOB'}) . '","';
				echo $age . '","';
      	echo display_desc($dtl{'phone_home'}) . '","';
				if($form_provider_type == 'pat') {
      		echo display_desc($dtl{'drfname'} . ' ' . $dtl{'drlname'}) . '","';
				} else {
      		echo display_desc($dtl{'vdrfname'} . ' ' . $dtl{'vdrlname'}) . '","';
				}
      	echo display_desc($dtl{'ins_name'}) . '","';
      	echo display_desc($dtl{'facility_name'}) . '","';
      	echo display_desc($dtl{'diagnosis'}) . '","';
      	echo oeFormatShortDate(substr($dtl{'dos'},0,10)) . '","';
      	echo display_desc($desc) . '","';
      	echo display_desc($val) . '","';
				echo '"' . "\n";
			} else {
				$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";
			?>
				<tr bgcolor="<?php echo $bgcolor; ?>">
  				<td class="detail"><?php echo display_desc($dtl{'pubpid'}); ?>&nbsp;</td>
  				<td class="detail"><a href="javascript: goPid('<?php echo $dtl{'pid'}; ?>');" ><?php echo display_desc($dtl{'fname'} . ' ' . $dtl{'lname'}); ?>&nbsp;</a></td>
  				<td class="detail"><?php echo oeFormatShortDate($dtl{'DOB'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo $age; ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($dtl['phone_home']); ?>&nbsp;</td>
					<?php if($form_provider_type == 'pat') { ?>
  				<td class="detail"><?php echo display_desc($dtl{'drfname'} . ' ' . $dtl{'drlname'}); ?>&nbsp;</td>
					<?php } else { ?>
  				<td class="detail"><?php echo display_desc($dtl{'vdrfname'} . ' ' . $dtl{'vdrlname'}); ?>&nbsp;</td>
					<?php } ?>
  				<td class="detail"><?php echo display_desc($dtl{'facility_name'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($dtl{'diagnosis'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo oeFormatShortDate(substr($dtl{'dos'},0,10)); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($desc); ?>&nbsp;</td>
      		<td class="detail"><?php echo display_desc($val); ?>&nbsp;</td>
 				</tr>
			<?php
			}
		}
		$rpt_lines++;
	}

	if(!$form_csvexport && $rpt_line) {
	?>
 	<tr bgcolor="#ddffff">
 	 <td class="detail" colspan="8"><?php echo xl('Total Number of Patients'); ?> </td>
 	 <td align="right"><?php echo $rpt_line; ?></td>
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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.forms.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>

<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});

function get_diagnosis() {
	var srch = document.getElementById('tmp_diag_chc').value;
	var target = '<?php echo $GLOBALS['webroot']; ?>/custom/diag_code_popup.php?thisdiag=tmp_diag_chc&codetype=ICD10';
	if(srch) target += '&bn_search=search&search_term=' + srch;
	wmtOpen(target, '_blank', 700, 800);
}

function set_diag(type, code) {
	if(type == '' || code == '') return false;
	var val = type + ':' + code
	AddOrRemoveThis('form_diags',val,';');
	AddOrRemoveThis('tmp_diag_desc',code,',');
}

function include_diag() {
	var val = document.getElementById('tmp_diag_chc');
	if(val == '' || !val || val == null) return;
	val = document.getElementById('tmp_diag_chc').value;
	if(val == '' || !val || val == null) return;
	AddOrRemoveThis('form_diags','ICD10:' + val,';');
	AddOrRemoveThis('tmp_diag_desc',val,',');
	document.getElementById('tmp_diag_chc').value = '';
	return true;
}

function goPid(pid) {
	if( (window.opener) && (window.opener.setPatient) ) {
		window.opener.loadFrame('RTop', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	} else if( (parent.left_nav) && (parent.left_nav.loadFrame) ) {
		parent.left_nav.loadFrame('RTop', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	} else {
		var newWin = window.open('../../main/main_screen.php?patientID=' + pid);
	}
}

function toencounter(rawdata, pid) {
//This is called in the on change event of the Encounter list.
//It opens the corresponding pages.
  var parts = rawdata.split("~");
  var enc = parts[0];
  var datestr = parts[1];
  var f = opener.document.forms[0];
	frame = 'RBot';
  if (!f.cb_bot.checked) {
		frame = 'RTop'; 
	} else if (!f.cb_top.checked) frame = 'RBot';

  opener.top.restoreSession();
	var target_form = '';
	var target_id = '';
	if(arguments.length > 2) {
		target_form = arguments[2];
		target_id = arguments[3];
	}
<?php if ($GLOBALS['concurrent_layout']) { ?>
	document.getElementById('pat-change-notification').style.display = 'block';
	opener.loadFrame('dem1', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	// alert('Load with alert?');
	// opener.parent.frames[frame].location.href = '../../patient_file/summary/demographics.php?set_pid=' + pid;
	DelayedHideDiv('pat-change-notification', 1000);
	document.getElementById('enc-change-notification').style.display = 'block';
	window.setTimeout(setEncounter(datestr, enc, frame), 4000);
  opener.parent.frames[frame].location.href  = 'patient_file/encounter/encounter_top.php?set_encounter=' + enc;
	DelayedHideDiv('enc-change-notification', 1000);
	if(target_form != '') window.setTimeout(goEncounter(target_form, target_id, frame), 2000);
<?php } else { ?>
	// alert('NOT concurrent');
	// opener.loadFrame('dem1', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
  opener.parent.Title.location.href = '../../patient_file/encounter/encounter_title.php?set_encounter='   + enc;
  openemr.parent.Main.location.href  = '../../patient_file/encounter/patient_encounter.php?set_encounter=' + enc;
  if(target_form != '') opener.parent.Main.location.href  = '../../forms/' + target_form + 'new.php?id=' + target_id;
<?php } ?>
}

</script>

</html>
<?php
} // End not csv export
?>
