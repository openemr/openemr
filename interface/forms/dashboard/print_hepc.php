<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/wmt/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt/printpat.class.php');
include_once($GLOBALS['srcdir'].'/wmt/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt/fyi.class.php');

$frmdir='dashboard';
$frmn='form_dashboard';
$pid= $_GET['patient_id'];
$encounter='';
if(isset($_GET['pop'])) { $pop= strip_tags($_GET['pop']); }
$patient = wmtPrintPat::getPatient($pid);
$fyi = wmtFYI::getPidFYI($pid);
$visit = wmtPrintVisit::getMostRecent($pid);
$facility = wmtPrintFacility::getFacility($visit->facility_id);
$fres = sqlStatement("SELECT * FROM $frmn WHERE pid=?",array($pid));
$dt = sqlFetchArray($fres);

// Split out the Hep C Risk Factors
$risks = explode('|',$dt{'db_hepc_risk_factors'});
$dt['tmp_hepc_trans'] = (in_array('bld_trans',$risks)) ? 'bld_trans' : '';
$dt['tmp_hepc_dia'] = (in_array('dialysis',$risks)) ? 'dialysis' : '';
$dt['tmp_hepc_use'] = (in_array('drug_use',$risks)) ? 'drug_use' : '';
$dt['tmp_hepc_drug'] = (in_array('history_drug',$risks)) ? 'history_drug' : '';
$dt['tmp_hepc_tat'] = (in_array('tattoo',$risks)) ? 'tattoo' : '';
$dt['tmp_hepc_hepc'] = (in_array('hepc',$risks)) ? 'hepc' : '';
$dt['tmp_hepc_sex'] = (in_array('sex',$risks)) ? 'sex' : '';
$dt['tmp_hepc_risk'] = (in_array('risk_sex',$risks)) ? 'risk_sex' : '';
$dt['tmp_hepc_jail'] = (in_array('jail',$risks)) ? 'jail' : '';
$dt['tmp_hepc_hiv'] = (in_array('hiv',$risks)) ? 'hiv' : '';
$dt['tmp_hepc_combat'] = (in_array('combat',$risks)) ? 'combat' : '';
$dt['tmp_hepc_job'] = (in_array('job',$risks)) ? 'job' : '';
$dt['tmp_hepc_oth'] = (in_array('other',$risks)) ? 'other' : '';
?>

<html>
<head>
<link rel="stylesheet" href="../../../library/wmt/wmtprint.css" type="text/css">
<title>Hepatitis C Risk Assessment for <?php echo $patient->full_name; ?> DOB: <?php echo $patient->dob; ?></title>
</head>
<body>


<div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="wmtPrnC" style="font-size: 30px; font-weight: bold"><?php echo $facility->facility; ?></td>
  </tr>
  <tr>
    <td class="wmtPrnC" style="font-weight: bold"><?php echo $facility->addr; ?></td>
  </tr>
  <tr>
    <td class="wmtPrnC" style="font-weight: bold"><?php echo $facility->csz; ?></td>
	</tr>
	<tr>
    <td class="wmtPrnC" style="font-weight: bold"><?php echo $facility->phone; ?></td>
  </tr>
</table>
</div>
<br>
<br>
<br>
<!-- fieldset style="width: 30%; border: solid 1px black; padding: 5px; margin: 0px;" -->
<table border="0" cellspacing="2" cellpadding="2">
  <tr>
		<td class="wmtPrnBody"><?php echo $patient->full_name; ?></td>
	</tr>
  <tr>
		<td class="wmtPrnBody"><?php echo $patient->addr; ?></td>
	</tr>
  <tr>
		<td class="wmtPrnBody"><?php echo $patient->csz; ?></td>
	</tr>
</table>
<br>
<br>


<div style="border: solid 2px black; ">
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
		<td class="wmtPrnLabel">Date:</td>
		<td class="wmtPrnBody"><?php echo date('y-m-d'); ?></td>
    <td class="wmtPrnLabel">Patient:</td>
    <td class="wmtPrnBody"><?php echo $patient->full_name; ?></td>
    <td class="wmtPrnLabel">DOB: </td>
    <td class="wmtPrnBody"><?php echo $patient->dob; ?></td>
    <td class="wmtPrnLabel">Age:</td>
    <td class="wmtPrnBody"><?php echo $patient->age; ?></td>
    <td class="wmtPrnLabel">Sex:</td>
    <td class="wmtPrnBody"><?php echo $patient->sex; ?></td>
    <td class="wmtPrnLabel">ID:</td>
    <td class="wmtPrnBody"><?php echo $pid; ?></td>
  </tr>
</table>
</div>
<br />

<fieldset style="border: solid 1px black; padding: 5px; margin: 0px;"><legend class="wmtPrnHeader">&nbsp;&nbsp;Hepatitis C Risk Assessment Questionnaire&nbsp;&nbsp;</legend>

<table width="98%" border="0" cellspacing="0" cellpadding="0" style="margin: 6px;">
	<tr>
		<td colspan="3"><span class="wmtPrnLabel" style="vertical-align: middle;">Have you ever been tested for Hepatitis C:&nbsp;&nbsp;&nbsp;</span>
		<input name="db_hepc_test" id="db_hepc_test_yes" type="checkbox" value="1" <?php echo (($dt{'db_hepc_test'} == '1')?' checked ':''); ?> /><span class="wmtPrnBody" style="vertical-align: middle;">&nbsp;Yes&nbsp;&nbsp;&nbsp;</span>
			<input name="db_hepc_test" id="db_hepc_test_no" type="checkbox" value="2" <?php echo (($dt{'db_hepc_test'} == '2')?' checked ':''); ?> /><span class="wmtPrnBody" style="vertical-align: middle;">&nbsp;No</span></td>
  </tr>
	<tr>
		<td class="wmtPrnLabel" colspan="3">Please review the following list of risk factors and check all that apply:</td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><input name="tmp_hepc_trans" id="tmp_hepc_trans" type="checkbox" value="bld_trans" <?php echo (($dt{'tmp_hepc_trans'} == 'bld_trans')?' checked ':''); ?> />&nbsp;Blood transfusion before 1992</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_dia" id="tmp_hepc_dia" type="checkbox" value="dialysis" <?php echo (($dt{'tmp_hepc_dia'} == 'dialysis')?' checked ':''); ?> />&nbsp;Long term dialysis</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_use" id="tmp_hepc_use" type="checkbox" value="drug_use" <?php echo (($dt{'tmp_hepc_use'} == 'drug_use')?' checked ':''); ?> />&nbsp;Injectable drug use <span style="text-decoration: underline;">even once</span></td> 
	</tr>
	<tr>
		<td class="wmtPrnBody"><input name="tmp_hepc_drug" id="tmp_hepc_drug" type="checkbox" value="history_drug" <?php echo (($dt{'tmp_hepc_drug'} == 'history_drug')?' checked ':''); ?> />&nbsp;History of drug use</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_tat" id="tmp_hepc_tat" type="checkbox" value="tattoo" <?php echo (($dt{'tmp_hepc_tat'} == 'tattoo')?' checked ':''); ?> />&nbsp;Tattoos or body piercings</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_hepc" id="tmp_hepc_hepc" type="checkbox" value="hepc" <?php echo (($dt{'tmp_hepc_hepc'} == 'hepc')?' checked ':''); ?> />&nbsp;Close contact with an individual with Hepatitis C</td> 
	</tr>
	<tr>
		<td class="wmtPrnBody"><input name="tmp_hepc_sex" id="tmp_hepc_sex" type="checkbox" value="sex" <?php echo (($dt{'tmp_hepc_sex'} == 'sex')?' checked ':''); ?> />&nbsp;Sex for drugs or money</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_risk" id="tmp_hepc_risk" type="checkbox" value="risk_sex" <?php echo (($dt{'tmp_hepc_risk'} == 'risk_sex')?' checked ':''); ?> />&nbsp;History of high risk sex</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_jail" id="tmp_hepc_jail" type="checkbox" value="jail" <?php echo (($dt{'tmp_hepc_jail'} == 'jail')?' checked ':''); ?> />&nbsp;Incarceration lasting longer than 6 months</td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><input name="tmp_hepc_hiv" id="tmp_hepc_hiv" type="checkbox" value="hiv" <?php echo (($dt{'tmp_hepc_hiv'} == 'hiv')?' checked ':''); ?> />&nbsp;HIV positive</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_combat" id="tmp_hepc_combat" type="checkbox" value="combat" <?php echo (($dt{'tmp_hepc_combat'} == 'combat')?' checked ':''); ?> />&nbsp;Fist Fighting or combat experience</td> 
		<td class="wmtPrnBody"><input name="tmp_hepc_job" id="tmp_hepc_job" type="checkbox" value="job" <?php echo (($dt{'tmp_hepc_job'} == 'job')?' checked ':''); ?> />&nbsp;Job Related</td>
	</tr>
	<tr>
		<td class="wmtPrnBody" colspan="2"><input name="tmp_hepc_oth" id="tmp_hepc_oth" type="checkbox" value="other" <?php echo (($dt{'tmp_hepc_oth'} == 'other')?' checked ':''); ?> />&nbsp;Other (provide details in the box below)</td>
		<td>&nbsp;</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<?php if($dt{'db_hepc_other'}) { ?>	
	<tr><td class="wmtPrnBody">Other Details:</td></td>
	<tr>
		<td colspan="3" class="wmtPrnBody" rows="3"><?php echo $dt{'db_hepc_other'}; ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<?php } ?>
	<tr>
		<td class="wmtPrnLabel" colspan="3">Please indicate below the best method for contacting you regarding your lab results.</td>
	</tr>
	<tr>
		<td colspan="3">
			<input name="db_hepc_method_ph" id="db_hepc_method_ph" type="checkbox" value="1" <?php echo (($dt{'db_hepc_method_ph'} == '1')?' checked ':''); ?> /><span class="wmtPrnBody" style="vertical-align: middle;">&nbsp;Phone call at:&nbsp;&nbsp;<?php echo (($dt{'db_hepc_phone'}) ? $dt{'db_hepc_phone'} : 'Not Provided'); ?>&nbsp;&nbsp;May we leave a message asking you to call back?&nbsp;</span>
			<input name="db_hepc_callback" id="db_hepc_callback_yes" type="checkbox" value="1" <?php echo (($dt{'db_hepc_callback'} == '1')?' checked ':''); ?> /><span class="wmtPrnBody" style="vertical-align: middle;">&nbsp;Yes&nbsp;&nbsp;&nbsp;</span>
			<input name="db_hepc_callback" id="db_hepc_callback_no" type="checkbox" value="2" <?php echo (($dt{'db_hepc_callback'} == '2')?' checked ':''); ?> /><span class="wmtPrnBody" style="vertical-align: middle;">&nbsp;No</span></td>
	</tr>
	<tr>
		<td class="wmtPrnBody"><input name="db_hepc_method_ml" id="db_hepc_method_ml" type="checkbox" value="1" <?php echo (($dt{'db_hepc_method_ml'} == '1')?' checked ':''); ?> />&nbsp;Written reminder mailed to:</td>
		<td colspan="2" class="wmtPrnBody"><?php echo $dt{'db_hepc_addr'}; ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td colspan="3"><span class="wmtPrnLabel">Data Collected On:&nbsp;</span><span class="wmtPrnBody">&nbsp;<?php echo $dt{'db_hepc_dt'}; ?></span</td>
	</tr>
	<?php if($dt{'db_hepc_nt'}) { ?>
	<tr>
		<td class="wmtPrnLabel" colspan="3">Comments:</td>
	</tr>
	<tr>
		<td colspan="3" class="wmtPrnBody"><?php echo $dt{'db_hepc_nt'}; ?></td>
	</tr>
	<?php } ?>
	<tr><td colspan="3">&nbsp;</td></tr>
	<tr>
		<td colspan="3"><span class="wmtPrnLabel" style="padding-right: 4px;">I have requested Hepatitis C screening. I understand that Hepatitis C is a reportable disease, that Hepatitis C testing involves a blood test and that this test, while confidential, will not be provided to individuals seeking anonymous testing. Your responses to the above are confidential, and HepC Alliance or authorized researchers may use the information you provide in research; however, no information that would make it possible to identify you will be included in any reports. In addition I authorize the County Health Department, Testing Site, and the HepC Alliance to contact me regarding the results of my lab tests.</span></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td class="wmtPrnBorder1B"><span class="wmtPrnLabel"><i>Signature on File</i></span><span class="wmtPrnR wmtPrnBody" style="float: right;"><?php echo $dt{'db_hepc_pt_dt'}; ?></span></td>
		<td>&nbsp;</td>
		<td class="wmtPrnBorder1B"><span class="wmtPrnLabel"><i>Signature on File</i></span><span class="wmtPrnR wmtPrnBody" style="float: right;"><?php echo $dt{'db_hepc_tester_dt'}; ?></span></td>
	</tr>
	<tr>
		<td><span class="wmtPrnBody">SSN:&nbsp;&nbsp;</span><span class="wmtPrnBody"><?php echo (($dt{'db_hepc_pt_ssn'} != '') ? $dt{'db_hepc_pt_ssn'} : 'Not Provided'); ?></span></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>

</fieldset>
<br/>

</body>
</html>
<script language="javascript">window.print();</script>
