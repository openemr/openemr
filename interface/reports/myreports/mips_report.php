<?php
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// Created for CrisisPrep
// @author  Ken Chapple <ken@mi-squared.com>
// @link    http://www.mi-squared.com

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/wmt-v2/list_tools.inc");
require_once($GLOBALS['incdir'].'/reports/myreports/mips/mips.inc.php');

use OpenEMR\Core\Header;

set_time_limit(0);
if(!isset($GLOBALS['wmt::mips_report_chunk'])) 
		$GLOBALS['wmt::mips_report_chunk'] = 0;
if(!isset($_REQUEST['exportcsv'])) $_REQUEST['exportcsv'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_min_res'])) $_POST['form_min_res'] = '';
if(!isset($_POST['form_max_res'])) $_POST['form_max_res'] = '';
if(!isset($_POST['form_min_res2'])) $_POST['form_min_res2'] = '';
if(!isset($_POST['form_max_res2'])) $_POST['form_max_res2'] = '';
if(!isset($_POST['form_from_date'])) {
	$_POST['form_from_date'] = '';
} else $_POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
if(!isset($_POST['form_to_date'])) {
	$_POST['form_to_date'] = '';
} else $_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = array();
if(!isset($_REQUEST['form_orderby'])) $_REQUEST['form_orderby'] = 'date';

$vital_fields = sqlListFields('form_vitals');
$ins_fields = sqlListFields('insurance_companies');
$ffs = 'freeb_type';
if(in_array('ins_type_code',$ins_fields)) $ffs = 'ins_type_code';

$form_orderby = 'date';
$report_path = $GLOBALS['incdir'].'/reports/myreports/mips/';
$report_types = LoadList('MIPS_Reports');
$report_type = '';
$report_flags = array();
if(isset($_REQUEST['type'])) $report_type = strip_tags($_REQUEST['type']);
$report_description = 'No Report Loaded';
if($report_type) {
	foreach($report_types as $type) {
		if($type['option_id'] == $report_type) {
			$report_description = $type['title'];
			$report_flags = explode('::', $type['notes']);
		}
	}
}

// WE DEFAULT TO USING THE ENCOUNTER PROVIDER - THE $pre VARIABLE
// IS A PREFIX FOR THE COLUMNS PULLED IN THE QUERY
$form_provider_type = 'enc';
$pre = 'v';
if(isset($_REQUEST['form_pat_provider'])) {
	$form_provider_type = 'pat';
	$pre = '';
} else if(in_array('pat_provider',$report_flags)) {
	$form_provider_type = 'pat';
	$pre = '';
}

$columns = array(
		'Provider Last Name' => $pre.'drlast',
		'Provider First Name' => $pre.'drfirst',
		'Individual NPI' => $pre.'drnpi',
		'TIN' => $pre.'drtin',
		'Last Name' => 'lname',
		'First Name' => 'fname',
		'Middle Name' => 'mname',
		'DOB' => 'DOB',
		'Gender' => 'sex',
		'MRN' => 'pubpid',
		'FFS Medicare' => 'ffs',
		'Visit Date' => 'dos',
);

$ORDERHASH = array(
	'date' => 'dos'
);

$diags = array();
$ranges = array();
$binds = array();

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$diag_to_date = $form_to_date;
$form_provider  = $_POST['form_provider'];
if($_REQUEST['form_orderby']) $form_orderby = $_REQUEST['form_orderby'];
$form_orderby = $ORDERHASH[$form_orderby];

$form_from_date_time = $form_from_date . ' 00:00:00';
$form_to_date_time = $form_to_date . ' 23:59:59';
$standard_query = TRUE;

// THIS SETS UP THE QUERY PASS FOR THE DENOMINATOR
if($report_type) {
	require_once($report_path . $report_type . '/setup.php');
}
if(!isset($_POST['form_min_age'])) $_POST['form_min_age'] = '18';
if(!isset($_POST['form_max_age'])) $_POST['form_max_age'] = '75';

if($standard_query) {
	// THIS QUERY IS THE DENOMINATOR QUERY, GETTING THE LARGER SET
	// THE 'prepass' INCLUDE THEN FILTERS THIS QUERY FOR THE NUMERATOR
	$query = 'SELECT p.lname, p.fname, p.mname, DOB, phone_home, pubpid, '.
		'p.pid, sex, providerID, '.
		'v1.encounter AS v1_enc, v1.provider_id AS v1_dr, '.
		'SUBSTRING(v1.date,1,10) AS v1_dos, '.
		'TIMESTAMPDIFF(YEAR, DOB, SUBSTRING(v1.date, 1, 10)) AS v1_age, '.
		'v2.encounter AS v2_enc, v2.provider_id AS v2_dr, '.
		'SUBSTRING(v2.date,1,10) AS v2_dos, '.
		'TIMESTAMPDIFF(YEAR, DOB, SUBSTRING(v2.date, 1, 10)) AS v2_age, ';
	if(in_array('diag_filter', $report_flags)) {
		$query .= 'l.diagnosis, ';
	}
	if(in_array('rx_filter', $report_flags)) {
		$query .= 'rx.drug, rx.provider_id as rx_dr, rx.start_date AS rx_dt,';
	}
	if(in_array('inr_filter', $report_flags)) {
		$query .= 'f.formdir AS inr, SUBSTRING(f.date,1,10) AS inr_dt,';
	}
	$query .= 'u.lname AS drlast, u.fname AS drfirst, u.mname AS drmiddle, '.
		'u.npi AS drnpi, u.federaltaxid AS drtin, '.
		'v1dr.npi AS v1drnpi, v1dr.federaltaxid AS v1drtin, '.
		'v1dr.lname AS v1drlast, '.
		'v1dr.fname AS v1drfirst, v1dr.mname AS v1drmiddle, '.
		'v2dr.npi AS v2drnpi, v2dr.federaltaxid AS v2drtin, '.
		'v2dr.lname AS v2drlast, '.
		'v2dr.fname AS v2drfirst, v2dr.mname AS v2drmiddle '.
		'FROM patient_data AS p '.
		'LEFT JOIN form_encounter AS v1 ON (v1.id = '.
			'(SELECT fe.id FROM form_encounter AS fe LEFT JOIN forms AS f ON '.
			'(fe.id = f.form_id AND f.formdir = "newpatient") '.
			'WHERE fe.pid = p.pid AND f.deleted = 0 '.
			'AND fe.date >= ? AND fe.date <= ? ORDER BY fe.date ASC LIMIT 1) ) ';
	$binds[] = $form_from_date_time;
	$binds[] = $form_to_date_time;
	$query .= 'LEFT JOIN form_encounter AS v2 ON (v2.id = '.
			'(SELECT fe.id FROM form_encounter AS fe LEFT JOIN forms AS f ON '.
			'(fe.id = f.form_id AND f.formdir = "newpatient") '.
			'WHERE fe.pid = p.pid AND f.deleted = 0 '.
			'AND fe.date >= ? AND fe.date <= ? ORDER BY fe.date DESC LIMIT 1) ) ';
	$binds[] = $form_from_date_time;
	$binds[] = $form_to_date_time;
	$query .= 'LEFT JOIN users AS u ON (p.providerID = u.id) '.
		'LEFT JOIN users AS v1dr ON (v1.provider_id = v1dr.id) '.
		'LEFT JOIN users AS v2dr ON (v2.provider_id = v2dr.id) ';

	if(in_array('diag_filter', $report_flags)) {
		$query .= 'LEFT JOIN lists AS l ON (l.id = (SELECT l.id FROM lists AS l '.
			'WHERE l.pid = p.pid AND l.activity > 0 AND '.
			'l.type = "medical_problem" AND ';
		if($form_from_date) $query .= '( ';
		$query .= '(l.enddate IS NULL OR l.enddate = "0000-00-00") ';
		if($form_from_date) {
			$query .= 'OR l.enddate >= ? ) ';
			$binds[] = $form_from_date;
		}
		if($diag_to_date) {
			$query .= ' AND l.begdate <= ? ';
			$binds[] = $diag_to_date;
		}
		$part = build_lists_where($diags, $ranges, 'l');
		$query .= $part . ' ORDER BY l.id DESC LIMIT 1) ) ';
	}

	if(in_array('rx_filter', $report_flags)) {
		$query .= 'LEFT JOIN prescriptions AS rx ON (rx.id = '.
			'(SELECT pr.id FROM prescriptions AS pr WHERE '.
			'pr.patient_id = p.pid AND pr.active >= 1 ';
		$part = build_rx_where($drugs);
		$query .= $part .'ORDER BY start_date DESC LIMIT 1) ) ';
	}
	if(in_array('inr_filter', $report_flags)) {
		$query .= 'LEFT JOIN forms AS f ON (f.id = '.
			'(SELECT forms.id FROM forms WHERE '.
			'forms.pid = p.pid AND forms.deleted = 0 AND forms.formdir = "inrtrack"'.
			'AND forms.date >= ? AND forms.date <= ? '.
			'ORDER BY start_date DESC LIMIT 1) ) ';
		$binds[] = $form_from_date_time;
		$binds[] = $form_to_date_time;
	}
	
	$query .= 'WHERE 1 ';
	if(in_array('diag_filter', $report_flags)) {
		$query .= 'AND l.diagnosis != "" AND l.diagnosis IS NOT NULL ';
	}
	
	// BUILD THE VISIT DATE/AGE COMBINATION CHECK
	$query .= 'AND ( ( 1 ';
	// EITHER THE FIRST VISIT QUALIFIES
	if($_POST['form_min_age']) {
		$query .= 'AND TIMESTAMPDIFF(YEAR, DOB, SUBSTRING(v1.date, 1, 10)) >= ? ';
		$binds[] = $_POST['form_min_age'];
	}
	if($_POST['form_max_age']) {
		$query .= 'AND TIMESTAMPDIFF(YEAR, DOB, SUBSTRING(v1.date, 1, 10)) <= ? ';
		$binds[] = $_POST['form_max_age'];
	}
	$part = build_doctor_where($form_provider, 'v1.', $form_provider_type);
	$query .= $part;
	// OR THE SECOND VISIT QUALIFIES
	$query .= ') OR ( 1 ';
	if($_POST['form_min_age']) {
		$query .= 'AND TIMESTAMPDIFF(YEAR, DOB, SUBSTRING(v2.date, 1, 10)) >= ? ';
		$binds[] = $_POST['form_min_age'];
	}
	if($_POST['form_max_age']) {
		$query .= 'AND TIMESTAMPDIFF(YEAR, DOB, SUBSTRING(v2.date, 1, 10)) <= ? ';
		$binds[] = $_POST['form_max_age'];
	}
	$part = build_doctor_where($form_provider, 'v2.', $form_provider_type);
	$query .= $part;
	$query .= ') '.
	') ';

	// $query .= 'GROUP BY pubpid ORDER BY pubpid ASC ';
}

 // echo "Query: <br>$query<br>\n";
 // echo "Binds: ";
 // print_r($binds);
 // echo "<br>\n";

$res = NULL;
$first_result = array();
$groupby = 'GROUP BY pubpid ORDER BY pubpid ASC ';

$res = sqlStatement($query.$groupby, $binds);


// SAVE THIS FOR GROUPING IF NECESSARY
/*
$sql = 'SELECT MAX(id) AS max_rec FROM patient_data';
$mrow = sqlQuery($sql);
if($query && ($_POST['form_refresh'] || $_REQUEST['exportcsv'])) {
	$min = 0;
	$max = $GLOBALS['wmt::mips_report_chunk'];
	if(!$max) $max = $mrow{'max_rec'}++;
	while(!$min || $max <= $mrow{'max_rec'}) {
		$append = "AND p.id >= $min AND p.id < $max ";
		// echo "Query: ".$query . $append . $groupby."<br>\n";
		$res = sqlStatement($query.$append.$groupby, $binds);
		while( $row = sqlFetchArray( $res ) ) {
			$first_result[] = $row;
			 // echo "Found Row: ";
			 // print_r($row);
			 // echo "<br>\n";
		}
		$min = $min + $GLOBALS['wmt::mips_report_chunk'];
		$max = $max + $GLOBALS['wmt::mips_report_chunk'];
	}
}
*/

// THIS SETS UP THE QUERY PASS FOR THE NUMERATOR 
if($report_type) {
	include_once($report_path . $report_type . '/prepass.php');
}

// EXPORT AS CSV, START HERE
if(isset($_GET['exportcsv'])) {
	$export_filename = 'mips_' . $report_type . '_' . date('Ymd');
	if($report_mode == 'auto') {
		$export_filename = $webserver_root . '/sites/default/exports/' . 
			$export_filename . '.csv';
		$output = fopen($export_filename, 'w');
	} else {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$export_filename.'.csv');
		$output = fopen('php://output', 'w');
	}
	fputcsv( $output, array_keys( $columns ) );
	foreach( $final_result AS $export_row ) {
		$fields = array();
		foreach ( $columns as $label => $key ) {
		  $fields[] = $export_row[$key];
		}
		fputcsv( $output, $fields );
	}
	exit;
}

?>
<html>
<head>
<?php //html_header_show();?>
<title><?php xl('MIPS Reporting','e'); ?></title>
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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>

<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }
 
 function export_csv()
 {
	document.forms[0].action='mips_report.php?exportcsv=1';
	document.forms[0].submit();
	
 }

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl($report_description,'e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='mips_report.php'>

<div id="report_parameters">
	<table>
		<tr>
			<td>
				<div style='float:left'><table class='text'>
					<tr>
						<td class='label'><?php xl('Report Type','e'); ?>:</td>
						<td colspan="3"><select name="type" id="type" onchange="document.forms[0].submit();">
						<?php ListSel($report_type, 'MIPS_Reports'); ?>
						</select></td>
					<tr>
				<tr>
					<td class='label' rowspan='2'><?php xl('Provider(s)','e'); ?>:</td>
					<td rowspan='4'><select name='form_provider[]' multiple size='4'>
						<option value='all'
						<?php if(count($form_provider) < 1 || $form_provider[0] == 'all') echo 'selected="selected"'; ?>
						>All Providers</option>
					<?php ProviderSelect($form_provider, FALSE, TRUE); ?>
					</select></td>
					<td class='label'><?php xl('From','e'); ?>:</td>
					<td><input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo $date_title_fmt; ?>'>
						<img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
					</td>
					<td class='label'><?php xl('To','e'); ?>:</td>
					<td><input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo $date_title_fmt; ?>'>
					<img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
					</td>
				</tr>
				<?php 
				if($report_type) {
					require_once($report_path . $report_type . '/input.inc');
				}
				?>
			</table></div>
		</td>

		<td align='left' valign='middle' height="100%">
		<table style='border-left:1px solid; width:100%; height:100%' >
			<tr>
				<td>
					<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").attr("action","mips_report.php"); $("#theform").submit();'><span><?php xl('Submit','e'); ?></span></a> 
<?php if ($_POST['form_refresh'] || $_REQUEST['exportcsv']) { ?>
					<a href='#' class='css_button' onclick='window.print()'><span><?php xl('Print','e'); ?></span></a>
					<a href='#' class='css_button' onclick='export_csv()'><span><?php xl('Export as CSV','e'); ?></span></a>
<?php } ?>
					</div>
				</td>
			</tr>
		</table>
		</td>
	</tr>
</table>

</div> <!-- end report_parameters -->

<?php if($_POST['form_refresh'] || $_REQUEST['exportcsv']) { ?>
<div id="report_results">
<table>
<thead>
<?php foreach ( $columns as $label => $key ) { ?>
	<th><?php xl( $label, 'e' ); ?></th>
<?php } ?>
</thead>
<tbody>
<?php foreach ( $final_result AS $row ) { ?>
	<tr bgcolor='<?php echo $bgcolor ?>'>
	<?php 
	foreach ($columns as $label => $key) { 
	?>
		<td><?php echo $row[$key];?></td>
<?php } ?>
	</tr>
<?php } ?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $_REQUEST['form_orderby']; ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type='text/javascript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});
</script>

</html>
