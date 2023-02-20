<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');

$frmdir = 'ext_exam2';
$frmn = 'form_'.$frmdir;
$ftitle = 'Patient Instructions';
$id = strip_tags($_GET['id']);
$pid = strip_tags($_GET['pid']);
$pop = false;
$encounter = '';
if(isset($GLOBALS['encounter'])) $encounter = $GLOBALS['encounter'];
if(isset($_GET['enc'])) $encounter= strip_tags($_GET['enc']);
if(isset($_GET['pop'])) $pop= strip_tags($_GET['pop']);
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
if(isset($GLOBALS['wmt::client_id'])) {
	$client_id = $GLOBALS['wmt::client_id'];
} else $client_id = checkSettingMode('wmt::client_id');
$patient = wmtPrintPat::getPatient($pid);
$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
$facility = wmtPrintFacility::getFacility($visit->facility_id);
$flds = sqlListFields($frmn);
$sql = 'SELECT id, form_complete, form_dt, instruct ';
if(in_array('cc', $flds)) $sql .= ', cc ';
if(in_array('vid', $flds)) $sql .= ', vid ';
$sql .= "FROM $frmn WHERE id=?";
$fdata = sqlQuery($sql, array($id));
if(!isset($fdata{'cc'})) $fdata{'cc'} = '';
if(!isset($fdata{'vid'})) $fdata{'vid'} = '';
$vitals = false;
if($fdata{'vid'}) $vitals = new wmtVitals($fdata{'vid'});
$form_date = $fdata['form_dt'];
$fyi = wmtFYI::getPidFYI($pid);
if($use_meds_not_rx) {
	$meds = GetList($pid, 'medication', $encounter);
} else {
	$meds = getLinkedPrescriptionsByPatient($pid, $encounter, '= 1');
}
$diag = GetProblemsWithDiags($pid, 'all', $encounter);
?>

<html>

<?php include($GLOBALS['srcdir'].'/wmt-v2/printHeader.bkk.php'); ?>
<br>

<?php if($visit->full_reason) { ?>
<fieldset style="border: solid 1px black;"><legend class="wmtPrnHeader">&nbsp;Reason for this Visit&nbsp;</legend>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
		<td class="wmtPrnLabel">Brief Description:</td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><?php echo $visit->full_reason; ?></td>
	</tr>
</table>
</fieldset>
<br>
<br>
<?php } ?>

<?php if($fdata{'cc'}) { ?>
<fieldset style="border: solid 1px black;"><legend class="wmtPrnHeader">&nbsp;Chief Complaint&nbsp;</legend>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td class="wmtPrnBody"><?php echo $fdata{'cc'}; ?></td>
	</tr>
</table>
</fieldset>
<br>
<br>
<?php } ?>

<?php 
if($vitals) {
	$vitals->wmtVitalsReport();
} else {
	$visit->patReportVitals();
}
?>
<br>
<br>

<?php 
if($use_meds_not_rx) {
	include_once($GLOBALS['srcdir'].'/wmt-v2/medications_add.style2.print.php');
} else {
	include_once($GLOBALS['srcdir'].'/wmt-v2/medications_erx.style2.print.php');
}
?>
<br>
<br>
<fieldset style="border: solid 1px black;"><legend class="wmtPrnHeader">&nbsp;Patient Instructions&nbsp;</legend>
	<span class="wmtPrnBody" style="width: 100%; white-space: pre-wrap;"><?php echo $fdata{'instruct'} ?></span>
	<br>
	<ul>
	<li class="wmtPrnBody">Go to an emergency room for any emergencies.</li>
	<li class="wmtPrnBody"><?php echo ($client_id == 'sfa') ? 'Please' : 'Have your pharmacy'; ?> contact the office if refills are needed.</li>
	<li class="wmtPrnBody">Please provide at least 24 hours notice to cancel or reschedule an appointment.</li>
	<li class="wmtPrnBody">Please notify the office of changes in address, phone number, or insurance.</li>
	</ul>
</fieldset>
<br>
<br>

<?php
$pane_title = 'Problems / Diagnoses';
include_once($GLOBALS['srcdir'].'/wmt-v2/diagnosis.style2.print.php');
?>
<br>

<span class="wmtPrnLabel"><?php echo $visit->signed_by; ?></span>
</body>
</html>
<script type="text/javascript">window.print();</script>
