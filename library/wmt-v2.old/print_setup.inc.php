<?php
include_once($GLOBALS['srcdir'].'/translation.inc.php');
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/rto.inc');
$ftitle = getNameOrNickName($frmdir);

if(isset($_SESSION['pid'])) $pid = strip_tags($_SESSION['pid']);
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(isset($_SESSION['encounter'])) $encounter = $_SESSION['encounter'];
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_GET['id'])) $id = strip_tags($_GET['id']);

$dt = sqlQuery("SELECT * FROM $frmn WHERE id=?", array($id));
$agedt = str_replace('-', '', $dt{'form_dt'});
$form_date = $dt['form_dt'];

$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
$patient = wmtPrintPat::getPatient($pid, $agedt);
$pat_sex = strtolower(substr($patient->sex,0,1));
$facility = wmtPrintFacility::getFacility($visit->facility_id);
$dashboard = wmtDashboard::getPidDashboard($pid);
$include_patient_info = checkSettingMode('wmt::include_pat_print_info','',$frmdir);
$include_patient_info = 
	$include_patient_info == '' ? TRUE : $include_patient_info;
$modules = LoadList($frmdir . '_modules', 'active');

if(strtolower($dt{'form_complete'}) == 'a') 
	$content = GetFormFromRepository($pid, $encounter, $id, $frmn);
?>
