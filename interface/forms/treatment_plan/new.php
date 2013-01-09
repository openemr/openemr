<?php
/**
 *
 * Copyright (C) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Naina Mohamed <naina@capminds.com>
 * @link    http://www.open-emr.org
 */
 
//SANITIZE ALL ESCAPES
 $sanitize_all_escapes=true;

 //STOP FAKE REGISTER GLOBALS
 $fake_register_globals=false;
 
include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
formHeader("Form:Treatment Planning");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$obj = $formid ? formFetch("form_treatment_plan", $formid) : array();


// Get the providers list.
 $ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE " .
  "authorized != 0 AND active = 1 ORDER BY lname, fname");
?>
<html><head>
<?php html_header_show();?>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<p><span class="forms-title"><?php echo xlt('Treatment Planning'); ?></span></p>
</br>
<?php
echo "<form method='post' name='my_form' " .
  "action='$rootdir/forms/treatment_plan/save.php?id=" . attr($formid) ."'>\n";
?>

<table  border="0">

<tr>
<td align="left" class="forms" class="forms"><?php echo xlt('Client Name' ); ?>:</td>
		<td class="forms">
			<label class="forms-data"> <?php if (is_numeric($pid)) {
    
    $result = getPatientData($pid, "fname,lname,squad");
   echo text($result['fname'])." ".text($result['lname']);}
   $patient_name=($result['fname'])." ".($result['lname']);
   ?>
   </label>
   <input type="hidden" name="client_name" value="<?php echo attr($patient_name);?>">
		</td>
		<td align="left"  class="forms"><?php echo xlt('DOB'); ?>:</td>
		<td class="forms">
		<label class="forms-data"> <?php if (is_numeric($pid)) {
    
    $result = getPatientData($pid, "*");
   echo text($result['DOB']);}
   $dob=($result['DOB']);
   ?>
   </label>
     <input type="hidden" name="DOB" value="<?php echo attr($dob);?>">
		</td>
		</tr>
	<tr>
 	
	
	
		<td align="left"  class="forms"><?php echo xlt('Client Number'); ?>:</td>
		<td class="forms">
			<label class="forms-data" > <?php if (is_numeric($pid)) {
    
    $result = getPatientData($pid, "*");
   echo text($result['pid']);}
   $patient_id=$result['pid'];
   ?>
   </label>
    <input type="hidden" name="client_number" value="<?php echo attr($patient_id);?>">
		</td>


		<td align="left" class="forms"><?php echo xlt('Admit Date'); ?>:</td>
		<td class="forms">
			   <input type='text' size='10' name='admit_date' id='admission_date' <?php echo attr($disabled) ?>;
			   value='<?php echo attr($obj{"admit_date"}); ?>'   
			   title='<?php echo xla('yyyy-mm-dd Date of service'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_admission_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php echo xla('Click here to choose a date'); ?>'>
		</td> 
		
		</tr>
		<tr>
		<td align="left" class="forms"><?php echo xlt('Provider'); ?>:</td>
		 <td class="forms" width="280px">
 <?php

    echo "<select name='provider' style='width:60%' />";
    while ($urow = sqlFetchArray($ures)) {
      echo "    <option value='" . attr($urow['lname']) . "'";
      if ($urow['lname'] == attr($obj{"provider"})) echo " selected";
      echo ">" . text($urow['lname']);
      if ($urow['fname']) echo ", " . text($urow['fname']);
      echo "</option>\n";
    }
    echo "</select>";
?>
		</td>
			
		</tr>
	
	<tr>
	
  <td colspan='3' nowrap style='font-size:8pt'>
   &nbsp;
	</td>
	</tr>
		
	<tr>
		<td align="left" class="forms"><?php echo xlt('Presenting Issue(s)'); ?>:</td>
		<td colspan="3"><textarea name="presenting_issues" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"presenting_issues"});?></textarea></td>
		
	</tr>
	<tr>
		<td align="left" class="forms"><?php echo xlt('Patient History'); ?>:</td>
		<td colspan="3"><textarea name="patient_history" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"patient_history"});?></textarea></td>
		
	</tr>
	<tr>
		
		<td align="left" class="forms"><?php echo xlt('Medications'); ?>:</td>
		<td colspan="3"><textarea name="medications" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"medications"});?></textarea></td>
		
		
	</tr>
	<tr>
		<td align="left" class="forms"><?php echo xlt('Anyother Relevant Information'); ?>:</td>
		<td colspan="3"><textarea name="anyother_relevant_information" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"anyother_relevant_information"});?></textarea></td>
		
	</tr>
	<tr>
		<td align="left" class="forms"><?php echo xlt('Diagnosis'); ?>:</td>
		<td colspan="3"><textarea name="diagnosis" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"diagnosis"});?></textarea></td>
		
	</tr>
	<tr>
		<td align="left" class="forms"><?php echo xlt('Treatment Received'); ?>:</td>
		<td colspan="3"><textarea name="treatment_received" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"treatment_received"});?></textarea></td>
		
	</tr>
	<tr>
		<td align="left" class="forms"><?php echo xlt('Recommendation For Follow Up'); ?>:</td>
		<td colspan="3"><textarea name="recommendation_for_follow_up" rows="2" cols="60" wrap="virtual name"><?php echo text($obj{"recommendation_for_follow_up"});?></textarea></td>
		
	</tr>
	<tr>
		<td align="left colspan="3" style="padding-bottom:7px;"></td>
	</tr>
	<tr>
		<td align="left colspan="3" style="padding-bottom:7px;"></td>
	</tr>
	<tr>
		<td></td>
    <td><input type='submit'  value='<?php echo xlt('Save');?>' class="button-css">&nbsp;
	<input type='button'  value="Print" onclick="window.print()" class="button-css">&nbsp;
	<input type='button' class="button-css" value='<?php echo xlt('Cancel');?>'
 onclick="top.restoreSession();location='<?php echo "$rootdir/patient_file/encounter/$returnurl" ?>'" /></td>
	</tr>
</table>
</form>
<script language="javascript">
/* required for popup calendar */
Calendar.setup({inputField:"admission_date", ifFormat:"%Y-%m-%d", button:"img_admission_date"});

</script>
<?php
formFooter();
?>
