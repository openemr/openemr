<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../interface/globals.php");
include_once($GLOBALS['srcdir'].'/sql.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/rto.inc');

$frmdir = '';
$frmn = '';
$id = '';
$encounter = '';
$cc_col = '';
$instruct_col = '';
$pid = $_GET['pid'];
$ftitle = 'Patient Visit Summary';
// if(isset($GLOBALS['encounter'])) $encounter = $GLOBALS['encounter'];
$pop = false;
$include_patient_info = false;
if(isset($_GET['id'])) $id = strip_tags($_GET['id']);
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_GET['pop'])) $pop = strip_tags($_GET['pop']);
if(isset($_GET['frmdir'])) $frmdir = strip_tags($_GET['frmdir']);
if(isset($_GET['cc'])) $cc_col = strip_tags($_GET['cc']);
if(isset($_GET['instruct'])) $instruct_col = strip_tags($_GET['instruct']);
$client_id = checkSettingMode('wmt::client_id');
if($frmdir != '') $frmn = 'form_'.$frmdir;
$patient = wmtPrintPat::getPatient($pid);
$vital_mode = '';
if(!$encounter) $vital_mode = 'recent';
$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir, $vital_mode);
$facility = wmtPrintFacility::getFacility($visit->facility_id);
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$fyi = wmtFYI::getPidFYI($pid);
if($use_meds_not_rx) {
	$meds = GetList($pid, 'medication');
} else {
	$meds = getPrescriptionsByPatient($pid, "*", '= 1');
}
$allergies = GetList($pid, 'allergy', $encounter);
$diag = GetProblemsWithDiags($pid,'all',$encounter);
$imm = GetAllImmunizationsbyPatient($pid, $encounter);
$rto_data = getRTObyStatus($pid, 'p', 'ASC');
$dt = array();
if($frmn && $id) $dt = sqlQuery("SELECT * FROM $frmn WHERE id=$id");

$cc = '';
if($cc_col != '') {
	$cc = trim($dt{$cc_col});
	// if(!$cc) $cc = 'No Chief Complaint Was Specified';
}

$pat_instructions = '';
if($instruct_col != '') {
	$pat_instructions = trim($dt{$instruct_col});
	if(!$pat_instructions) $pat_instructions = 'No Instructions Specified';
}
if(!$visit->next_appt_reason || !$visit->next_appt_dt) $visit->next_appt_reason = 'No Future Appointments on File';
if($encounter) {
	if(!$visit->full_reason) $visit->full_reason = 'Not Specified';
	$empty_vitals = 'Not recorded for this visit';
} else {
	$empty_vitals = 'No Vitals On File';
}
?>

<html>
<head>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['srcdir']; ?>/wmtprint.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['srcdir']; ?>/wmtprint.bkk.css" type="text/css">
<title><?php echo "Patient Summary for $patient->full_name DOB ($patient->dob) on ".date('Y-m-d'); ?></title>
</head>
<body>
<?php include($GLOBALS['srcdir'].'/wmt-v2/printHeader.bkk.php'); ?>

<div style="padding-left: 8px;">
<table width="100%" border="0" cellspacing="2" cellpadding="2">
	<tr>
    <td class="wmtPrnLabel">Patient:</td>
    <td class="wmtPrnBody"><?php echo $patient->full_name; ?></td>
    <td class="wmtPrnLabel">DOB:</td>
    <td class="wmtPrnBody"><?php echo $patient->dob; ?></td>
    <td class="wmtPrnLabel">Facility: </td>
    <td class="wmtPrnBody"><?php echo $facility->facility; ?></td>
		<?php if($encounter) { ?>
    <td class="wmtPrnLabel">Visit:</td>
    <td class="wmtPrnBody"><?php echo $visit->encounter_date; ?></td>
		<?php } ?>
  </tr>
</table>
</div>
<br>

<?php
// This processing loop will print the sections in whatever order the practice
// prefers - 
//
// Some information comes from form specific name, since programmers can't 
// actually see the future and predict all uses and needs.

$sections = getListOptions('WMT_Pat_Summary_Sections');
foreach($sections as $pane) {
	if($pane{'seq'} < 0) continue;
	$summary_section = $pane{'option_id'}.'.style2.print.php';
	$pane_printed = false;
	$pane_title = trim($pane['title']);
	include($GLOBALS['srcdir'].'/wmt-v2/'.$summary_section);
	if($pane_printed) echo "<br>\n";
}

if($encounter) {
?>
<span class="wmtPrnLabel"><?php echo $visit->signed_by; ?></span>
<?php if($visit->approved_by) { ?>
<br>
<span class="wmtPrnLabel"><?php echo $visit->approved_by; ?></span>
<?php 
	}
}
?>
</body>
</html>
<script language="javascript">window.print();</script>
