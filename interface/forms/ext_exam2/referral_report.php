<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
function ext_exam2_print_referral($pid, $id, $encounter) {
include_once("../../globals.php");
include_once("../../../library/patient.inc");
include_once("../../../library/wmt-v2/wmtstandard.inc");
include_once("../../../library/wmt-v2/wmtprint.inc");
include_once("../../../library/wmt-v2/approve.inc");
include_once("../../../library/wmt-v2/ee1form.inc");
include_once("../../../library/wmt-v2/fyi.class.php");
include_once("../../../library/wmt-v2/printvisit.class.php");
include_once("../../../library/wmt-v2/printfacility.class.php");
include_once("../../../library/wmt-v2/printpat.class.php");
$frmdir = 'ext_exam2';
$frmn = 'form_'.$frmdir;
$print_clicked = false;
$dt=array();
$rs=array();
if($pid == '' || $pid == 0) ReportMissingPID();
if(!$id) {
	echo "There seems to be an error, no form ID was provided....<br>\n";
	echo "Exiting<br>\n";
	exit;
}

$dt = sqlQuery("SELECT * FROM $frmn WHERE id=?",array($id));
$agedt = substr($dt{'form_dt'},0,4).substr($dt{'form_dt'},5,2).
		substr($dt{'form_dt'},8,2);
$patient = wmtPrintPat::getPatient($pid, $agedt);
$visit = wmtPrintVisit::getEncounterByForm($id, $frmdir);
$facility = wmtPrintFacility::getFacility($visit->facility_id);

if(strtolower($dt{'form_complete'}) == 'a') {
	$content = GetFormFromRepository($pid, $encounter, $id, $frmn.'_referral');
} else {
	echo "There seems to be an error, the system should not allow you to generate a referral letter from an unapproved form....<br>\n";
	echo "Exiting<br>\n";
	exit;
}
if(strpos(strtolower($content),'could not locate archived form') !== false) {
	echo $content;
	return false;
}

?>

<div style="padding-left: 15px;">
<table width="98%"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 25%;" class="wmtPrnLabel">Referral To:</td>
		<td style="width: 25%;">&nbsp;</td>
		<td style="width: 15%;">&nbsp;</td>
		<td class="wmtPrnLabel">From:</td>
	</tr>
  <tr>
		<td class="wmtPrnLabel">&nbsp;<?php echo $visit->referring_full; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
    <td class="wmtPrnLabel">&nbsp;<?php echo $visit->provider_full; ?></td>
  </tr>
  <tr>
		<td class="wmtPrnLabel">&nbsp;<?php echo $visit->referring_addr1; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
    <td class="wmtPrnLabel">&nbsp;<?php echo $facility->facility; ?></td>
  </tr>
  <tr>
		<td class="wmtPrnLabel">&nbsp;<?php echo $visit->referring_csz; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
    <td class="wmtPrnLabel">&nbsp;<?php echo $facility->addr; ?></td>
  </tr>
  <tr>
		<td class="wmtPrnLabel">&nbsp;Fax:&nbsp;<?php echo $visit->referring_fax; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
    <td class="wmtPrnLabel">&nbsp;<?php echo $facility->csz; ?></td>
	</tr>
</table>
</div>
<br/>

<div class="wmtPrnContainer" style="padding-left: 15px;">
<table width="40%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="wmtPrnLabel">Visit Date:</td>
		<td class="wmtPrnBody"><?php echo $dt{'form_dt'}; ?></td>
	</tr>
	<tr>
    <td class="wmtPrnLabel">Patient:</td>
		<td colspan="3" class="wmtPrnBody"><?php echo $patient->full_name; ?></td>
	</tr>
	<tr>
    <td class="wmtPrnLabel">DOB:</td>
		<td class="wmtPrnBody"><?php echo $patient->dob;?></td>
    <td class="wmtPrnLabel">Age:</td>
		<td class="wmtPrnBody"><?php echo $patient->age;?></td>
	</tr>
	<tr>
    <td class="wmtPrnLabel">Sex:</td>
		<td class="wmtPrnBody"><?php echo $patient->sex;?></td>
    <td class="wmtPrnLabel">ID:</td>
		<td class="wmtPrnBody"><?php echo $pid;?></td>
  </tr>
</table>
</div>
<br>
<div class="wmtPrnDottedB"></div>

<?php
if(strtolower($dt{'form_complete'}) == 'a') {
	echo $content;
} else {
	include("referral_view.php");
}
?>

<span class='wmtPrnLabel'>&nbsp;<?php echo $visit->signed_by; ?></span><br>
<?php if($visit->approved_by) { ?>
<span class='wmtPrnLabel'>&nbsp;<?php echo $visit->approved_by; ?></span><br>
<?php } ?>

<?php 
return true;
}
?>
