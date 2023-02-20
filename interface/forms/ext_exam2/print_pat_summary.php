<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/sql.inc');
include_once($GLOBALS['srcdir'].'/amc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/rto.inc');

$frmdir = 'ext_exam2';
$frmn = 'form_'.$frmdir;
$ftitle = 'Patient Visit Summary';
$id = strip_tags($_GET['id']);
$pid = strip_tags($_GET['pid']);
$pop = false;
$encounter = '';
if(isset($GLOBALS['encounter'])) $encounter = $GLOBALS['encounter'];
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_GET['pop'])) $pop = strip_tags($_GET['pop']);
$patient = wmtPrintPat::getPatient($pid);
$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
$facility = wmtPrintFacility::getFacility($visit->facility_id);
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');

if(isset($GLOBALS['wmt::client_id'])) {
	$client_id = $GLOBALS['wmt::client_id'];
} else $client_id = checkSettingMode('wmt::client_id');
$dt = sqlQuery("SELECT * FROM $frmn WHERE id=?", array($id));
$fyi = wmtFYI::getPidFYI($pid);
if($use_meds_not_rx) {
	$meds = GetList($pid, 'medication', $encounter);
} else {
	$meds = getLinkedPrescriptionsByPatient($pid, $encounter, '= 1');
}
$allergies = GetList($pid, 'allergy', $encounter);
$diag = GetProblemsWithDiags($pid, 'all', $encounter);
$imm = GetAllImmunizationsbyPatient($pid, $encounter);
$rto_data = getRTObyStatus($pid, 'p', 'ASC');

amcAdd('provide_sum_pat_amc', true, $pid, 'form_encounter', $encounter);
?>

<html>
<?php include($GLOBALS['srcdir'].'/wmt-v2/printHeader.bkk.php'); ?>
<body>

<?php
// This processing loop will print the sections in whatever order the practice
// prefers - 
$cc = trim($dt{'cc'});
if(!$cc) $cc = 'Not Specified';
$pat_instructions = trim($dt{'instruct'});
if(!$pat_instructions) $pat_instructions = 'Not Specified';
if(!$visit->full_reason) $visit->full_reason = 'Not Specified';
if(!$visit->next_appt_reason || !$visit->next_appt_dt) $visit->next_appt_reason = 'No Future Appointments on File';
$empty_vitals = 'Not recorded for this visit';

$sections = getListOptions('WMT_Pat_Summary_Sections');
foreach($sections as $pane) {
	if($pane{'seq'} < 0) { continue; }
	$summary_section = $pane{'option_id'}.'.style2.print.php';
	$pane_printed = false;
	$pane_title = trim($pane['title']);
	include($GLOBALS['srcdir'].'/wmt-v2/'.$summary_section);
	if($pane_printed) { echo "<br>\n"; }
}

include($GLOBALS['srcdir'].'/wmt-v2/report_signature.inc.php');
?>

</body>
</html>
<script type="text/javascript">window.print();</script>
