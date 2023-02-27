<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/patient.inc");
include_once("$srcdir/wmt-v2/wmtstandard.inc");
include_once("$srcdir/wmt-v2/wmt.forms.php");
include_once("$srcdir/wmt-v2/wmtpatient.class.php");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

$last_year= mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$last_month= mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if(!isset($_POST['form_from_date'])) { $_POST['form_from_date'] = '' ; }
if(!isset($_POST['form_to_date'])) { $_POST['form_to_date'] = '' ; }
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_month));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$selected_codes = '';
if(isset($_POST['tmp_test_codes'])) { $selected_codes = $_POST['tmp_test_codes']; }
$create = false;
if(isset($_GET['create'])) { $create = $_GET['create']; }
if(!isset($_GET['mode'])) {
	$selected_codes = '~ALL~';
	$_POST['tmp_test_codes'] = '~ALL~';
}
$columns = array("ID", "Date of Service", "Last Name", "First Name", "Gender", "DOB", "Age", "Result", "Facility", "Insurance", "Race", "Relationship", "Transfusion", "Dialysis", "IDU", "Drugs", "Tattoos", "HCV Contact", "Sex Drugs Money", "HRS", "Incarceration", "HIV", "Fighting", "Job Related", "Other", "Street", "City", "State", "ZIP Code", "Telephone");
// echo "From: $form_from_date<br>\n";
// echo "Thru: $form_to_date<br>\n";
$lab_type = array();
$fres= sqlStatement("SELECT ct_id, ct_key FROM code_types WHERE ct_active=1 ".
		"AND (UPPER(ct_key) LIKE '%LAB%CODE%' OR UPPER(ct_key) LIKE '%LABCODE%')");
while($frow = sqlFetchArray($fres)) {
	$lab_type[] = $frow{'ct_id'};
}
$lab_code_stmt = "SELECT code, code_text, code_text_short FROM ".
				"codes WHERE active=1 AND (";
$lab_desc_stmt = "SELECT code, code_text, code_text_short FROM ".
				"codes WHERE active=1 AND (";
$first = true;
$selected_tests = '';
foreach($lab_type as $type) {
	if(!$first) { 
		$lab_code_stmt .= " OR ";
		$lab_desc_stmt .= " OR ";
	}	
	$lab_code_stmt .= "code_type='$type'";
	$lab_desc_stmt .= "code_type='$type'";
	$first = false;
}
$lab_code_stmt .= ") ORDER BY code";
$lab_desc_stmt .= ") AND code=? LIMIT 1";
// echo "Lab Code Statement is: $lab_code_stmt<br>\n";
// echo "Creation Mode: $create<br>\n";
// echo "Selected codes set to: $selected_codes<br>\n";
if($selected_codes == '~ALL~') {
	// $selected_codes = '';
	$rlist = sqlStatement($lab_code_stmt);
	while($rrow = sqlFetchArray($rlist)) {
		// if($selected_codes != '') { $selected_codes .= '+'; }	
		// $selected_codes .= $rrow{'code'};
		$desc = (($rrow{'code_text_short'} != '') ? $rrow{'code_text_short'} : $rrow{'code_text'});
		$selected_tests = AppendItem($selected_tests, $desc);
	}
} else {
	$list = explode('+', $selected_codes);
	foreach($list as $code) {
		$rlist = sqlStatement($lab_desc_stmt, array($code));
		$rrow = sqlFetchArray($rlist);
		$desc = (($rrow{'code_text_short'} != '') ? $rrow{'code_text_short'} : $rrow{'code_text'});
		$selected_tests = AppendItem($selected_tests, $desc);
	}
}

$tests = explode('+', $selected_codes);
if(isset($_POST['tmp_test_codes'])) { 
	if($_POST['tmp_test_codes'] == '~ALL~') { $selected_tests = 'ALL'; }
}
// print_r($tests);
// echo "<br>\n";

$query = "SELECT " .
	"ri.id, ri.parent_id AS ri_parent_id, observation_label, observation_value, ".
	"r.id AS result_id, r.request_id, r.pid AS r_pid, r.result_datetime, ".
	"oi.id AS oi_id, oi.parent_id AS oi_parent, oi.test_code AS oi_test, ".
	"o.order_datetime, o.id AS order_id, o.pat_first, o.pat_last, ".
	"o.pat_DOB, o.pat_age, o.pat_sex, o.ins_primary_plan, o.pat_street, ".
	"o.pat_city, o.pat_state, o.pat_zip, o.pat_phone, ".
	"p.fname, p.lname, p.DOB, p.sex, p.street, p.city, p.state, p.postal_code, ".
	"p.phone_home, p.race, p.status, ".
	"db.db_hepc_risk_factors ".
	"FROM form_quest_result_item AS ri ".
  "LEFT JOIN form_quest_result AS r ON ri.parent_id=r.id ".
  "LEFT JOIN form_quest_order AS o ON r.request_id=o.id ".
  "LEFT JOIN form_quest_order_item AS oi ON r.request_id=oi.parent_id ".
	"LEFT JOIN patient_data AS p ON r.pid=p.pid ".
	"LEFT JOIN form_dashboard AS db ON r.pid=db.pid ".
  "WHERE ";
$query .= "r.pid > 0 AND UPPER(ri.observation_label) LIKE '%HEP%C%' AND ";
$first = true;
if($_POST['tmp_test_codes'] != '~ALL~') {
	foreach($tests as $chosen) {
		if($first) { $query .= ' ('; }
		if(!$first) { $query .= ' OR '; }
		$query .= "ri.test_code='$chosen'";
		$first=false;
	}
}
if(!$first) { $query .= ") AND "; }

if ($form_to_date) {
  $query .= "(r.result_datetime >= '$form_from_date 00:00:00' AND ".
			"r.result_datetime <= '$form_to_date 23:59:59') ";
} else {
  $query .= "(r.result_datetime >= '$form_from_date 00:00:00' AND ".
			"r.result_datetime <= '$form_from_date 23:59:59') ";
}
// $query .= "GROUP BY order_datetime, pid";
// echo "Query: ",$query,"<br>\n";

$lres=array();
if(isset($_GET['mode'])) { 
	set_time_limit(0);
	$lres = sqlStatement($query);
	$cnt = sqlNumRows($lres);
	// echo "<br/></br>Row Count: $cnt<br/><br/>\n";
}

if($create == 'csv' && $lres) {
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=activity.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columns);
 	while ($row = sqlFetchArray($lres)) {
		// if ($form_to_date) {
  		// if(substr($row{'datetime'},0,10) < $form_from_date ||
								// substr($row{'datetime'},0,10) > $form_to_date) { continue; }
		// } else {
  		// if(substr($row{'datetime'},0,10) < $form_from_date ||
					 			// substr($row{'datetime'},0,10) > $form_from_date) { continue; };
		// }
		$sex = 0;
		$pat_sex = ($row{'pat_sex'} != '') ? $row{'pat_sex'} : $row{'sex'};
		if(strtolower($pat_sex) == 'female') { $sex = 1; }
		$pat_last = ($row{'pat_last'} != '') ? $row{'pat_last'} : $row{'lname'};
		$pat_first = ($row{'pat_first'} != '') ? $row{'pat_first'} : $row{'fname'};
		$pat_street = ($row{'pat_street'} != '') ? $row{'pat_street'} : $row{'street'};
		$pat_city = ($row{'pat_city'} != '') ? $row{'pat_city'} : $row{'city'};
		$pat_state = ($row{'pat_state'} != '') ? $row{'pat_state'} : $row{'state'};
		$pat_zip = ($row{'pat_zip'} != '') ? $row{'pat_zip'} : $row{'postal_code'};
		$pat_dob = ($row{'pat_DOB'} != '') ? $row{'pat_DOB'} : $row{'DOB'};
		$pat_age = ($row{'pat_age'} != '') ? $row{'pat_age'} : getPatientAge($pat_dob);
		$result = '';
		$facility = '';
		$race = '';
		$relation = '';
		if(strtolower($row{'observation_value'}) == 'reactive') { $result = 'POSITIVE'; }
		if(strtolower($row{'observation_value'}) == 'non-reactive') { $result = 'NEGATIVE'; }
		$dob = substr($pat_dob,8,2).'/'.substr($pat_dob,5,2).'/'.substr($pat_dob,0,4);
		if(strtolower($row{'race'}) == 'white') { $race = 1; }
		if(strtolower($row{'race'}) == 'black_or_afri_amer') { $race = 2; }
		if(strtolower($row{'race'}) == 'hispanic') { $race = 3; }
		if(strtolower($row{'race'}) == 'native_hawai_or_pac_island') { $race = 4; }
		if(strtolower($row{'race'}) == 'amer_ind_or_alaska_native') { $race = 5; }
		if(strtolower($row{'status'}) == 'married') { $relation = 1; }
		if(strtolower($row{'status'}) == 'single') { $relation = 2; }
		if(strtolower($row{'status'}) == 'divorced') { $relation = 3; }
		if(strtolower($row{'status'}) == 'widowed') { $relation = 4; }
		if(strtolower($row{'status'}) == 'separated') { $relation = 5; }
		if(strtolower($row{'status'}) == 'domestic partner') { $relation = 6; }
		$risks = explode('|', $row{'db_hepc_risk_factors'});
		
		$sql = "SELECT provider, type, date, name, freeb_type FROM ".
			"insurance_data LEFT JOIN insurance_companies ON ".
			"provider=insurance_companies.id WHERE ".
			"pid=? AND type='primary' ORDER BY date DESC LIMIT 1";
		$ires = sqlStatement($sql, array($row{'r_pid'}));
		$irow = sqlFetchArray($ires);
		$primary_plan = ($row{'ins_primary_plan'} != '') ? $row{'ins_primary_plan'} : $irow{'name'};
		$primary_plan = ($primary_plan != '') ? $primary_plan : 'No Insurance';
		
		// WE CAN GET THE FACILITY DIRECTLY FROM THE ENCOUNTER TIED TO THE ORDER
		if($row{'order_id'}) {
			$sql = "SELECT forms.encounter, form_id, forms.pid, facility, ".
				"facility_id  FROM forms LEFT JOIN form_encounter ON ".
				"forms.encounter=form_encounter.encounter WHERE ".
				"form_id=? AND formdir='form_quest_order' AND ".
				"forms.pid=? AND deleted=0";
			$vres = sqlStatement($sql, array($row{'order_id'}, $row{'r_pid'}));
			$vrow = sqlFetchArray($ires);
			$facility = $vrow{'facility'};
			// $tmp = array('Order Id', $row{'order_id'}, 'Pid', $row{'r_pid'}, 'chosen', $vrow{'facililty'});
			// fputcsv($output, $tmp);
		} else {
			// FIND THE MOST RECENT PRIOR VISIT FOR THE FACILITY
			$date_time = substr($row{'result_datetime'},0,10);
			// $tmp = array('Result', $row{'result_datetime'}, 'Short', $date_time);
			// fputcsv($output, $tmp);
			$sql = "SELECT forms.encounter, form_id, forms.pid, facility, ".
				"facility_id  FROM forms LEFT JOIN form_encounter ON ".
				"forms.encounter=form_encounter.encounter WHERE ".
				"forms.pid=? AND deleted=0 AND ".
				"(SUBSTR(form_encounter.date,1,10) <= '$date_time') ORDER BY ".
				"form_encounter.date DESC LIMIT 1";
			$vres = sqlStatement($sql, array($row{'r_pid'}));
			$vrow = sqlFetchArray($vres);
			$facility = $vrow{'facility'};
		}

		unset($data);
		$data = array($row{'r_pid'}, substr($row{'result_datetime'},0,10), 
				$pat_last, $pat_first, $sex, $dob, $pat_age,
				$result, $facility, $primary_plan, $race, $relation);
		$data[] = (in_array('bld_trans',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('dialysis',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('drug_use',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('history_drug',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('tattoo',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('hepc',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('sex',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('risk_sex',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('jail',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('hiv',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('combat',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('job',$risks)) ? 'yes' : 'no';
		$data[] = (in_array('other',$risks)) ? 'yes' : 'no';
		$data[] = $pat_street;
		$data[] = $pat_city;
		$data[] = $pat_state;
		$data[] = $pat_zip;
		$data[] = $pat_phone;
		fputcsv($output, $data);
	}
	fclose($output);
} else {
?>
<html>
<head>
<title><?php xl('Laboratory Activity Report','e'); ?></title>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

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

<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function refreshme() {
	var my_action = "../../../interface/reports/myreports/hepc_activity.php?mode=search";
	document.forms[0].action = my_action;
	
  document.forms[0].submit();
 }

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Lab Test Activity by Date and Patient','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='hepc_activity.php?mode=search'>

<div id="report_parameters">
<table>
<tr>
  <td>
    <div style='float:left'>
      <table class='text'>
        <tr>
					<td class="label"><?php xl('Choose Tests','e'); ?>:</td>
					<td><select name="tmp_sel_tests" id="tmp_sel_tests" onchange="UpdateSelDescription('tmp_sel_tests','tmp_test_codes','tmp_test_list');">
					<?php
  				$rlist= sqlStatement($lab_code_stmt);
  				echo "<option value='' selected='selected'>",xl('Choose Another','e'),"</option>";
					echo "<option value='~ra~'>Remove All</option>\n";
					echo "<option value='~ALL~'>Select All</option>\n";
  				while ($rrow= sqlFetchArray($rlist)) {
    				echo "<option value='" . $rrow['code'] . "'";
						// Possibly check the hidden field for the value, bold selected ones
    				// if(is_array($thisArray)) {
      				// if(in_array($rrow['option_id'],$thisArray)) echo " selected='selected'";
    				// }
						$desc = (($rrow{'code_text_short'}) ? $rrow{'code_text_short'} : $rrow{'code_text'});
    				echo ">" .$rrow['code'].' - '.$desc;
    				echo "</option>";
  				}
					?>
					</td>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='vertical-align: bottom; cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='vertical-align: bottom; cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
				</tr>
				<tr>
					<td colspan="6"><span class='label' id='tmp_test_list'><?php echo $selected_tests; ?></span></td>
				</tr>
      </table>

    </div>
  </td>
  <td style="vertical-align: middle; text-align: left; height: 100%; width: 120px;">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").attr("action", "../../../interface/reports/myreports/hepc_activity.php?mode=search"); $("#theform").submit();'>
					<span><?php xl('Submit','e'); ?></span></a>

          </div>
        </td>
      </tr>
			<tr>
				<td>
            <?php if(isset($_GET['mode']) ) { ?>
						<div style="margin-left: 15px; ">
            <a href='javascript:;' class='css_button' onclick="formCreateCSV(); ">
						<span><?php xl('Create as CSV','e'); ?></span></a></div>
            <?php } ?>
				</td>
			</tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if (isset($_GET['mode'])) {
?>
<div id="report_results">
<table>
<thead>
	<th>Visit Date</th>
	<th>Pid</th>
	<th>Patient Last</th>
	<th>Patient First</th>
	<th>Observation</th>
	<th>Value</th>
</thead>

<?php
if ($lres) {
  while ($row = sqlFetchArray($lres)) {
		// if ($form_to_date) {
  		// if(substr($row{'datetime'},0,10) < $form_from_date ||
								// substr($row{'datetime'},0,10) > $form_to_date) { continue; }
		// } else {
  		// if(substr($row{'datetime'},0,10) < $form_from_date ||
					 			// substr($row{'datetime'},0,10) > $form_from_date) { continue; };
		// }
		$pat_last = ($row{'pat_last'} != '') ? $row{'pat_last'} : $row{'lname'};
		$pat_first = ($row{'pat_first'} != '') ? $row{'pat_first'} : $row{'fname'};
		echo "<tr>\n";
		echo "<td>".substr($row{'result_datetime'},0,10)."&nbsp;</td>\n";
		echo "<td>".$row{'r_pid'}."&nbsp;</td>\n";
		echo "<td>$pat_last&nbsp;</td>\n";
		echo "<td>$pat_first&nbsp;</td>\n";
		echo "<td>".$row{'observation_label'}."&nbsp;</td>\n";
		echo "<td>".$row{'observation_value'}."&nbsp;</td>\n";
		echo "</tr>\n";
	}
}
?>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<?php if($create == 'csv') { ?>
 	<?php echo xl('No Results, please input revised search criteria above, and click Submit to view results.', 'e' ); ?>
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='tmp_test_codes' id='tmp_test_codes' value="<?php echo $selected_codes; ?>"/>

</form>
</body>

<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

function formCreateCSV() {
	var my_action = "../../../interface/reports/myreports/hepc_activity.php?form_from_date=<?php echo $form_from_date; ?>&form_to_date=<?php echo $form_to_date; ?>&tmp_test_codes=<?php echo $selected_codes; ?>&create=csv&mode=search";
	var my_action = "../../../interface/reports/myreports/hepc_activity.php?create=csv&mode=search";
	document.forms[0].action = my_action;
	document.forms[0].submit();
}
</script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmt.forms.js"></script>

</html>
<?php } ?>
