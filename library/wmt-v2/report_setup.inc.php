<?php
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/lifestyle.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/rto.inc');
$ftitle = getNameOrNickName($frmdir);

$dt = sqlQuery("SELECT * FROM $frmn WHERE id=?",array($id));
$agedt = str_replace('-', '', $dt{'form_dt'});

$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
$patient = wmtPrintPat::getPatient($pid, $agedt);
$pat_sex = strtolower(substr($patient->sex,0,1));
$facility = wmtPrintFacility::getFacility($visit->facility_id);
$dashboard = wmtDashboard::getPidDisplayDashboard($pid);
foreach($dashboard as $key => $val) {
	if(!isset($dt[$key])) $dt[$key] = $val;
}
$fyi = wmtFYI::getPidDisplayFYI($pid);
foreach($fyi as $key => $val) {
	if(!isset($dt[$key])) $dt[$key] = $val;
}
$include_patient_info = checkSettingMode('wmt::include_pat_print_info','',$frmdir);
$modules = LoadList($frmdir . '_modules', 'active');

if(!isset($dt{'form_complete'})) $dt{'form_complete'} = '';
if(strtolower($dt{'form_complete'}) == 'a' && !$create) 
		$content = GetFormFromRepository($pid, $encounter, $id, $frmn);
?>
