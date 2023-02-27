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
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/billing.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');
$hold_approve=false;
set_time_limit(0);

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'patient' => 'lower(patient_data.lname), lower(patient_data.fname), form_encounter.date',
  'pubpid'  => 'lower(patient_data.pubpid), form_encounter.date',
  'time'    => 'form_encounter.date',
	'doctor'  => 'lower(ulast), lower(ufirst), form_encounter.date'
);
$pop_forms= getFormsByType(array('pop_form'));
$archive_list= getFormsByType(array('archive_form'));
$referral_list= getFormsByType(array('referral_form'));
$lock_list= getFormsByType(array('lock_form'));
$approve_dt_catalog = array();
$pop_used= checkSettingMode('wmt::form_popup');

foreach($archive_list as $form) {
	// Build a catalog of the field list for each form
	if($form['form_name'] != 'mc_wellsub') {
		$flds = sqlListFields('form_'.$form['form_name']);
	} else {
		unset($flds);
		$flds = array();
	}
	if(in_array('approved_dt', $flds)) $approve_dt_catalog[] = $form['form_name'];
	if($form['form_name']=='wu_foot_exam' || $form['form_name']=='diabetes_self'
		|| $form['form_name'] == 'abc_SHarvey') {
		include_once("../../forms/".$form['form_name']."/archive.php");
	} else if($form['form_name'] == '') {
		// Never mind these
	} else {
		include_once("../../forms/".$form['form_name']."/report.php");
	}
}

foreach($referral_list as $form) {
	if($form['form_name'] == '') {
		// Never mind these
	} else {
		include_once("../../forms/".$form['form_name']."/referral.php");
	}
}

$last_month = mktime(0,0,0,date('m'),date('d')-7,date('Y'));
$form_from_date= date('Y-m-d', $last_month);
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
if(isset($_POST['hold'])) { $hold_approve = $_POST['hold']; }

echo "From: $form_from_date\n";
echo "To: $form_to_date\n";
$form_provider = '';
$form_supervisor= '';
$form_facility = '';
$form_name = '';
$form_status= 'c';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_supervisor'])) $form_supervisor = $_POST['form_supervisor'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_status'])) $form_status= $_POST['form_status'];
$form_details   = "1";

$orderby = $ORDERHASH['time'];
$form_orderby='time';

$query = "SELECT " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
	"form_encounter.supervisor_id, form_encounter.provider_id, ".
	"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, ".
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid, patient_data.pid FROM forms " .
	"LEFT JOIN form_encounter USING (encounter) ".
  "LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
  "LEFT JOIN users AS u ON form_encounter.provider_id = u.id " .
  "WHERE " .
  "forms.deleted != '1' AND ";
	$first = true;
	if($archive_list && (count($archive_list) > 0)) {
		$query .= "( ";	
		foreach($archive_list as $frm) {
			if(!$first) { $query .= "OR "; }
			$query .= "forms.formdir = '".$frm['form_name']."' ";
			$first = false;
		}
	}
	if($lock_list && (count($lock_list) > 0)) {
		if($first) { $query .= "( "; }
		foreach($lock_list as $frm) {
			if(!$first) { $query .= "OR "; }
			$query .= "forms.formdir = '".$frm['form_name']."' ";
			$first = false;
		}
	}
	if(!$first) { $query .= ") "; }
if ($form_to_date) {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_from_date 23:59:59' ";
}
if ($form_facility) {
  $query .= "AND form_encounter.facility_id = '$form_facility' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
if ($form_supervisor !== '') {
  $query .= "AND form_encounter.supervisor_id = '$form_supervisor' ";
}
if ($form_name) {
  $query .= "AND forms.formdir = '$form_name' ";
}
$query .= "ORDER BY $orderby";

echo "Query: $query\n";
$res = sqlStatement($query);
?>



<html>
<head>
<title><?php xl('Auto Approved Forms','e'); ?></title>
<link rel=stylesheet href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

</head>
<body class="body_top">

<div id="report_results">
<table>

 <thead>
	<th>
		<?php xl('Approve','e'); ?>
	</th>
  <th>
		<?php xl('Provider','e'); ?>
  </th>
  <th>
		<?php xl('Date','e'); ?>
  </th>
  <th>
		<?php xl('Patient','e'); ?>
  </th>
  <th>
		<?php xl('ID','e'); ?>
  </th>
  <th>
   <?php  xl('Status','e'); ?>
  </th>
  <th>
   <?php  xl('Encounter','e'); ?>
  </th>
  <th>
   <?php  xl('Form','e'); ?>
  </th>
 </thead>
 <tbody>


<?php
$item=1;
while($row = sqlFetchArray($res)) {
  $this_id = $row{'form_id'};
  $this_form = 'form_'.$row['formdir'];
	$frmdir = $row['formdir'];
	$frm_table = 'form_'.$row['formdir'];
	if($row['formdir'] == 'mc_wellsub') {
		$frm_table = 'form_mc_wellness';
	}
	if(!$this_id || !$frmdir) continue;
	echo "This One: ";
	print_r($row);
	echo "\n";
	continue;
  $sql = "SELECT id, date, pid, form_complete, form_priority, approved_by ";
	$sql .= "FROM $frm_table WHERE id='$this_id'";
  $fdata = sqlStatement($sql);
  $farray = sqlFetchArray($fdata);
  $fstatus = strtolower($farray['form_complete']);
  $fbill= strtolower($farray['form_priority']);
	if($fstatus == 'a') continue;
  if (($fstatus != $form_status) && ($form_status != '')) continue;
	$item++;

	$approval_id = ($row{'supervisor_id'}) ? $row{'supervisor_id'} : $row{'provider_id'};
	// echo "Encounter ID Is: ",$row{'encounter'},"\n";
	// echo "Provider ID Is: ",$row{'provider_id'},"\n";
	// echo "Supervisor ID Is: ",$row{'supervisor_id'},"\n";
	// echo "Approval ID Is: $approval_id\n";
	$sql = "SELECT id, username FROM users WHERE id=?";
	$fdata = sqlStatement($sql, array($approval_id));
	$frow = sqlFetchArray($fdata);
	$approved_by = trim($frow{'username'});
	if($approved_by == '') { $approved_by = 'Unspecified'; }

	$sql = "UPDATE $frm_table SET form_complete='a'";
	if(!$hold_approve || ($farray{'approved_by'} == '')) {
		$sql .= ", approved_by='$approved_by'";
	}
	// For the forms that have the most recent method
	if(in_array($frmdir, $approve_dt_catalog)) {
		if(!$hold_approve) {
			$sql .= ", approved_dt=NOW()";
		}
	}
	$sql .= " WHERE id='$this_id'";
	$test=sqlInsert($sql);
	// Here we have to handle the forms that are ready for going to 
	// the repository, as well as forms that use a different archive
	// function. 
	if($frmdir == 'wu_foot_exam' || $frmdir == 'diabetes_self'
					|| $frmdir == 'abc_SHarvey') {
		$tst=FormInRepository($row{'pid'}, $row{'encounter'}, $this_id, $frm_table);
		if(!$tst) {
			ob_start();
			$rpt_function= $frmdir.'_archive';
			$rpt_function($row{'pid'}, $row{'encounter'}, $this_id);
			// Generate the html output
			$content=ob_get_contents();
			ob_end_clean();
			AddFormToRepository($farray{'pid'}, $row{'encounter'}, $this_id, $frm_table, $content);
		}
	} else if(SearchMultiArray($frmdir, $archive_list) !== false) {
		$tst=FormInRepository($row{'pid'}, $row{'encounter'}, $this_id, $frm_table);
		if(!$tst) {
			ob_start();
			$rpt_function= $frmdir.'_report';
			$rpt_function($row{'pid'}, $row{'encounter'}, "*", $this_id, true);
			// Generate the html output
			$content=ob_get_contents();
			ob_end_clean();
			AddFormToRepository($farray{'pid'}, $row{'encounter'}, $this_id, $frm_table, $content);
		}
	}
	if(SearchMultiArray($frmdir, $referral_list) !== false) {
		$tst=FormInRepository($row{'pid'}, $row{'encounter'}, $this_id, $frm_table.'_referral');
		if(!$tst) {
			ob_start();
			$rpt_function= $frmdir.'_referral';
			$rpt_function($row{'pid'}, $row{'encounter'}, "*", $this_id, true);
			// Generate the html output
			$content=ob_get_contents();
			ob_end_clean();
			AddFormToRepository($farray{'pid'}, $row{'encounter'}, $this_id, $frm_table.'_referral', $content);
		}
	}
?>

 <tr>
	<td>
  <td>
   <?php echo $row['ulast'].', '.$row['ufirst']; ?>&nbsp;
  </td>
  <td>
   <?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;
  </td>
  <td>
   <?php echo $row['lname'].', '.$row['fname'].' '.$row['mname']; ?>&nbsp;
  </td>
  <td>
   <?php echo $row['pubpid']; ?>&nbsp;
  </td>
  <td>
   <?php echo ListLook($fstatus,'Form_Status'); ?>&nbsp;
  </td>
  <td>
   <?php echo substr($row['reason'],0,50); ?>&nbsp;
  </td>
  <td>
   <?php echo $row['form_name']; ?>&nbsp;</a>
  </td>
 </tr>
</tbody>
</table>
<?php
}
?>


</div>  <!-- end encresults -->
</form>
</body>
</html>
