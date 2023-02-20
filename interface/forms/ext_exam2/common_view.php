<div class="wmtPrnContainer">

<?php
if(isset($GLOBALS['wmt::client_id'])) {
	$client_id = $GLOBALS['wmt::client_id'];
} else $client_id = checkSettingMode('wmt::client_id');
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal','',$frmdir);
$med_add_allowed = checkSettingMode('wmt::db_meds_add');

$row = sqlQuery("SELECT * FROM list_options WHERE list_id=? AND option_id LIKE '%exam%' AND seq >= 0", array($frmdir.'_modules'));
$exam_module = $row{'option_id'};
$row = sqlQuery("SELECT * FROM list_options WHERE list_id=? AND option_id LIKE '%ros%' AND seq >= 0", array($frmdir.'_modules'));
$ros_module = $row{'option_id'};
if($ros_module == 'ros2' || $ros_module == 'ent_ros') {
	include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
	$flds = sqlListFields('form_wmt_ros');
	$rs = sqlQuery('SELECT * FROM form_wmt_ros WHERE link_id=? '.
			'AND link_name=?', array($id, $frmn));
} else {
	$flds = sqlListFields('form_ext_ros');
	$rs = sqlQuery('SELECT * FROM form_ext_ros WHERE ee1_link_id=? '.
			'AND ee1_link_name=?', array($id, $frmn));
}
if(!$rs) {
	$flds = array_slice($flds,7);
	foreach($flds as $key => $fld) { $rs[$fld] = ''; }
}

$surg = GetList($pid, 'surgery', $encounter);
$hosp = GetList($pid, 'hospitalization', $encounter);
$inj = GetList($pid, 'wmt_inj_history', $encounter);
$fh = GetFamilyHistory($pid, $encounter);
$imm = GetAllImmunizationsbyPatient($pid, $encounter);
$pmh = GetMedicalHistory($pid, $encounter);
$img = GetImageHistory($pid, $encounter);
$allergies = GetList($pid, 'allergy', $encounter);
if($use_meds_not_rx) {
 	$meds = GetList($pid, 'medication', $encounter);
 	$med_hist = GetList($pid, 'med_history', $encounter);
} else {
 	$meds = getLinkedPrescriptionsByPatient($pid, $encounter, '= 1');
 	$med_hist = getLinkedPrescriptionsByPatient($pid, $encounter, '< 1');
}
$pat_sex = strtolower(substr($patient->sex,0,1));
$diag = GetProblemsWithDiags($pid, 'encounter', $encounter);
$dashboard = wmtDashboard::getPidDashboard($pid);
foreach($dashboard as $key => $val) {
	if($key == 'id' || $key == 'pid' || $key == 'db_form_dt') continue;
	// Don't get vitals from here!!
	if($key == 'db_height' || $key == 'db_weight' || $key == 'db_BMI' ||
		$key == 'db_BMI_status' || $key == 'db_bps' || $key == 'db_bpd' ||
		$key == 'db_pulse') continue;
	$dt[$key] = $val;
}
$dt['db_pat_blood_type'] = $patient->blood_type;
$dt['db_pat_rh_factor'] = $patient->rh_factor;
$fyi = wmtFYI::getPidFYI($pid);
foreach($fyi as $key => $val) {
	if(substr($key,0,3) != 'fyi') continue;
	$dt[$key] = $val;
}

$modules = LoadList($frmdir.'_modules', 'active');
/**
$sql = "SELECT * from list_options WHERE list_id=? AND seq > 0 ".
		"ORDER BY seq ASC";
$mres = sqlStatement($sql, array($frmdir.'_modules'));
for($iter=0; $mrow=sqlFetchArray($mres); $iter++) $modules[$iter] = $mrow;
**/

global $chp_printed, $hdr_printed, $sub_printed, $hdr, $sub, $prnt, $cnt;

include($GLOBALS['srcdir'].'/wmt-v2/print_loop.inc.php');
include($GLOBALS['srcdir'].'/wmt-v2/procedures.print.inc.php');

echo "<table width'100%' border='0' cellspacing='0' cellpadding='0'>\n";
$chk = '';
if($dt{'rto_num'} != 0) $chk = ListLook($dt{'rto_num'}, 'RTO_Number', '');
$chc = ListLook($dt{'rto_frame'}, 'RTO_Frame', '');
if(!empty($chk) || !empty($chc)) {
	echo "<tr><td><span class='wmtPrnLabel'>RTO:&nbsp;&nbsp;</span><span class='wmtPrnBody'>$chk&nbsp;&nbsp;&nbsp;$chc&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='wmtPrnLabel'>Sooner if Worsens or PRN</span></td></tr>\n";
}

if($dt{'reviewed'}) echo "<tr><td><span class='wmtPrnLabel'>The information above was thoroughly reviewed with the patient.</span></td></tr>\n";

if($dt{'return_chk'}) echo "<tr><td><span class='wmtPrnLabel'>Return As Needed</span></td></tr>\n";

if($dt{'lab_pend'}) echo "<tr><td><span class='wmtPrnLabel'>Pending Test Results</span></td></tr>\n";
echo "</table>\n";
?>

</div><!-- This is the end of the overall margin div -->
