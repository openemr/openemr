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

$surg=GetList($pid, 'surgery', $encounter);
$hosp=GetList($pid, 'hospitalization', $encounter);
$fh=GetFamilyHistory($pid, $encounter);
$imm=GetAllImmunizationsbyPatient($pid, $encounter);
$pmh=GetMedicalHistory($pid, $encounter);
$img=GetImageHistory($pid, $encounter);
$allergies=GetList($pid, 'allergy', $encounter);
$med_add_allowed=checkSettingMode('wmt::db_meds_add');
if($use_meds_not_rx) {
 	$meds = GetList($pid, 'medication', $encounter);
 	$med_hist = GetList($pid, 'med_history', $encounter);
} else {
 	$meds = getLinkedPrescriptionsByPatient($pid, $encounter, '= 1');
 	$med_hist = getLinkedPrescriptionsByPatient($pid, $encounter, '< 1');
}
$pat_sex=strtolower(substr($patient->sex,0,1));
$diag=GetProblemsWithDiags($pid,'encounter',$encounter);
$dashboard = wmtDashboard::getPidDashboard($pid);
$fyi = wmtFYI::getPidFYI($pid);
$vitals = new wmtVitals($dt{'vid'});
if($vitals->BMI == '0.0') $vitals->BMI = '';

echo "<br>\n";
$chp_printed=false;
$nt=trim($dt{'cc'});
if($client_id == 'hcardio') {
	if(!empty($nt)) {
		$chp_printed=PrintChapterPlainStyle('Diagnoses',$chp_printed);
		PrintOverhead('',$nt);
	}
} else {
	if(!empty($nt)) {
		$chp_printed=PrintChapterPlainStyle('Chief Complaint',$chp_printed);
		PrintOverhead('',$nt);
	}
}
if($chp_printed) CloseChapterPlainStyle();
$chp_printed=false;

$nt=trim($dt{'hpi'});
if(!empty($nt)) {
	$chp_printed=PrintChapterPlainStyle('History of Present Illness',$chp_printed);
	PrintOverhead('',$nt);
}
if($chp_printed) CloseChapterPlainStyle();
$chp_printed=false;

$vitals->wmtVitalsReport();
?>

<br>

<?php
if($use_meds_not_rx) {
	include("../../../library/wmt-v2/medications_add.plain.print.php");
} else {
	include("../../../library/wmt-v2/medications_erx.plain.print.php");
}

include("../../../library/wmt-v2/allergies.plain.print.php");

include("../../../library/wmt-v2/past_med_history.plain.print.php");

include("../../../library/wmt-v2/form_views/sh_view.print.inc.php");

$include_plans = true;
include("../../../library/wmt-v2/diagnosis.plain.print.php");

$chp_printed=false;
$nt=trim($dt{'plan'});
if(!empty($nt)) {
	$chp_printed=PrintChapterPlainStyle('Other Plan Notes',$chp_printed);
	PrintSingleLine('',$nt);
}
if($chp_printed) CloseChapterPlainStyle();

$chp_printed=false;
$nt=trim($dt{'assess'});
if(!empty($nt)) {
	$chp_printed=PrintChapterPlainStyle('Assessment',$chp_printed);
	PrintSingleLine('',$nt);
}
if($chp_printed) CloseChapterPlainStyle();
?>

</div><!-- This is the end of the overall margin div -->
