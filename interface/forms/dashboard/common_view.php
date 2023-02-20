<div class="wmtPrnContainer">

<?php
if(isset($GLOBALS['wmt::client_id'])) {
	$client_id = $GLOBALS['wmt::client_id'];
} else $client_id = checkSettingMode('wmt::client_id');
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal','',$frmdir);
$med_add_allowed = checkSettingMode('wmt::db_meds_add');

$surg = GetList($pid, 'surgery');
$hosp = GetList($pid, 'hospitalization');
$inj = GetList($pid, 'wmt_inj_history');
$fh = GetFamilyHistory($pid);
$imm = GetAllImmunizationsbyPatient($pid);
$pmh = GetMedicalHistory($pid);
$img = GetImageHistory($pid);
$allergies = GetList($pid, 'allergy');
if($use_meds_not_rx) {
 	$meds = GetList($pid, 'medication');
 	$med_hist = GetList($pid, 'med_history');
} else {
 	$meds = getPrescriptionsByPatient($pid, '*', '= 1');
 	$med_hist = getPrescriptionsByPatient($pid, '*', '< 1');
}
$pat_sex = strtolower(substr($patient->sex,0,1));
$diag = GetProblemsWithDiags($pid, 'current');
$pap_data = getAllPaps($pid);
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
?>
</div><!-- This is the end of the overall margin div -->
