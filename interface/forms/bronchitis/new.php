<!-- Form created by Nikolai Vitsyn: 2004/01/23  -->
<!--                          Update 2004/01/29  -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: bronchitis");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<?php html_header_show();?>
<SCRIPT LANGUAGE="JavaScript">
<!-- 

   function onset_check (form)   {
        
	var d, s = "Today's date is: "; //Declare variables.
	d = new Date(); //Create Date object.
	s += (d.getMonth() + 1) + "-"; //Get month
	s += d.getDate() + "-"; //Get day
	s += d.getYear(); //Get year.
	        
    onset_str = form.bronchitis_date_of_illness.value;
    if (onset_str == "") {
    alert("No valid date into Onset of illness field!!! Enter date as YYYY-MM-DD");
    alert(d);

	return;
      }
    if (onset_str.length != 10) {
     alert("Your date should be 10 characters");
	return;
      }
    alert("OK, Bye!!!");
    return;
   }
</SCRIPT>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<form method=post action="<?php echo $rootdir;?>/forms/bronchitis/save.php?mode=new" name="my_form">
<br></br>
<span class="title" ><?php xl('Bronchitis Form','e'); ?></span>
<br></br>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save','e'); ?>]</a>
<br></br>
<span class="text" ><?php xl('Onset of Illness:','e'); ?> </span><input type="entry" name="bronchitis_date_of_illness" value=""></input>
<br></br>

<span class="text" ><?php xl('HPI:','e'); ?>:</span><br></br>
<textarea name="bronchitis_hpi" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<table ><th colspan="5"><?php xl('Other Pertinent Symptoms:','e'); ?></th>
<tr>
<td width="80" align="right"><?php xl('Fever:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_fever"></input></td>
<td width="100" align="right"><?php xl('Cough:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_cough"></input></td>
<td width="60" align="right"><?php xl('Dizziness:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_dizziness"></input></td>
</tr>
<tr>
<td width="80" align="right"><?php xl('Chest Pain:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_chest_pain"></input></td>
<td width="100" align="right"><?php xl('Dyspnea:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_dyspnea"></input></td>
<td width="60" align="right"><?php xl('Sweating:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_sweating"></input></td>
</tr>
<tr>
<td width="80" align="right"><?php xl('Wheezing:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_wheezing"></input></td>
<td width="100" align="right"><?php xl('Malaise:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_malaise"></input></td>
</tr>
<tr>
<td width="80" align="right"><?php xl('Sputum:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_sputum"></input></td>
<td width="100" align="right"><?php xl('Appearance:','e'); ?></td>
<td><input type="text" name="bronchitis_ops_appearance" size="10" value="<?php xl('none','e'); ?>"></input></td>
</tr>
</table>

<table>
<tr>
<td width="205" align="right"><?php xl('All Reviewed and Negative:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_ops_all_reviewed"></input></td>
</tr>
</table>
<br></br>


<table >
<tr>
<td width="60"><?php xl('Review of PMH:','e'); ?></td>
<td align="right"></td>
<td><input type="checkbox" name="bronchitis_review_of_pmh"></input></td>
<td align="right"><?php xl('Medications:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_medications"></input></td>
<td align="right"><?php xl('Allergies:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_allergies"></input></td>
<td align="right"><?php xl('Social History:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_sh"></input></td>
<td align="right"><?php xl('Family History:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_review_of_fh"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60"><?php xl('TM\'S:','e'); ?></td>
<td align="right"><?php xl('Normal Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_normal_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_normal_left"></input></td>

<td width="80"><?php xl('NARES:','e'); ?> </td>
<td align="right"><?php xl('Normal Right','e'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_normal_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_normal_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('Thickened Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_thickened_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_thickened_left"></input></td>

<td width="80"></td>
<td align="right"><?php xl('Swelling Right','e'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_swelling_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_swelling_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('A/F Level Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_af_level_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_af_level_left"></input></td>

<td width="80"></td>
<td align="right"><?php xl('Discharge Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_discharge_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_nares_discharge_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('Retracted Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_retracted_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_retracted_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('Bulging Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_bulging_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_bulging_left"></input></td>

</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('Perforated Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_perforated_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_perforated_left"></input></td>
</tr>
</table>

<table>
<tr>
<td width="220" align="right"><?php xl('Not Examined:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_tms_nares_not_examined"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="90"><?php xl('SINUS TENDERNESS:','e'); ?></td>
<td align="right"><?php xl('No Sinus Tenderness:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_no_sinus_tenderness"></input></td>
<td align="right"></td>

<td width="90"><?php xl('OROPHARYNX: ','e'); ?></td>
<td align="right"><?php xl('Normal Oropharynx:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_normal"></input></td>
<td align="right"></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('Frontal Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_frontal_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_frontal_left"></input></td>
<td align="right"><?php xl('Erythema:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_erythema"></input></td>
<td align="right"><?php xl('Exudate:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_exudate"></input></td>
<td align="right"><?php xl('Abcess:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_abcess"></input></td>
<td align="right"><?php xl('Ulcers:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_oropharynx_ulcers"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right"><?php xl('Maxillary Right:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_maxillary_right"></input></td>
<td align="right"><?php xl('Left:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_maxillary_left"></input></td>

<td width="120" align="right"><?php xl('Appearance:','e'); ?></td>
<td><input type="text" name="bronchitis_oropharynx_appearance" size="10" value="normal"></input></td>
</tr>
</table>

<table>
<tr>
<td width="238" align="right" ><?php xl('Not Examined:','e'); ?> </td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_not_examined"></input></td>
<td width="268" align="right" ><?php xl('Not Examined:','e'); ?> </td>
<td><input type="checkbox" name="bronchitis_oropharynx_not_examined"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60"><?php xl('HEART:','e');?></td>
<td align="right"><?php xl('laterally displaced PMI:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_pmi"></input></td>
<td align="right"><?php xl('S3:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_s3"></input></td>
<td align="right"><?php xl('S4:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_s4"></input></td>
</tr>
<tr>
<td width="60"></td>
<td align="right"><?php xl('Click:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_click"></input></td>
<td align="right"><?php xl('Rub:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_rub"></input></td>
</tr>
</table>

<table>
<tr>
<td width="60"></td>
<td><?php xl('Murmur:','e'); ?></td>
<td><input type="text" name="bronchitis_heart_murmur" size="10" value="none"></input></td>
<td><?php xl('Grade:','e'); ?></td>
<td><input type="text" name="bronchitis_heart_grade" size="10" value="n/a"></input></td>
<td><?php xl('Location:','e'); ?></td>
<td><input type="text" name="bronchitis_heart_location" size="10" value="n/a"></input></td>
</tr>
</table>

<table>
<tr>
<td width="203" align="right" ><?php xl('Normal Cardiac Exam: ','e'); ?></td>
<td><input type="checkbox" name="bronchitis_heart_normal"></input></td>
<td width="93" align="right"><?php xl('Not Examined:','e'); ?> </td>
<td><input type="checkbox" name="bronchitis_heart_not_examined"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60"><?php xl('LUNGS:','e'); ?></td>
<td width="106"><?php xl('Breath Sounds:','e'); ?></td>
<td align="right"> <?php xl('normal:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_bs_normal"></input></td>
<td align="right"><?php xl('reduced:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_bs_reduced"></input></td>
<td align="right"><?php xl('increased:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_bs_increased"></input></td>
</tr>

<tr>
<td width="60"></td>
<td><?php xl('Crackles:','e'); ?></td>
<td align="right"><?php xl('LLL:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_lll"></input></td>
<td align="right"><?php xl('RLL:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_rll"></input></td>
<td align="right"><?php xl('Bilateral:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_bll"></input></td>
</tr>

<tr>
<td width="60"></td>
<td><?php xl('Rubs:','e'); ?></td>
<td align="right"><?php xl('LLL:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_lll"></input></td>
<td align="right"><?php xl('RLL:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_rll"></input></td>
<td align="right"><?php xl('Bilateral:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_bll"></input></td>
</tr>

<tr>
<td width="60"></td>
<td><?php xl('Wheezes:','e'); ?></td>
<td align="right"><?php xl('LLL:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_lll"></input></td>
<td align="right"><?php xl('RLL:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_rll"></input></td>
<td align="right"><?php xl('Bilateral:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_bll"></input></td>
<td align="right"><?php xl('Diffuse:','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_dll"></input></td>
</tr>
</table>

<table>
<tr>
<td width="218" align="right" ><?php xl('Normal Lung Exam: ','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_normal_exam"></input></td>
<td width="140" align="right" ><?php xl('Not Examined: ','e'); ?></td>
<td><input type="checkbox" name="bronchitis_lungs_not_examined"></input></td>
</tr>
</table>
<br></br>

<span class="text" ><?php xl('Diagnostic Tests:','e'); ?></span><br></br>
<textarea name="bronchitis_diagnostic_tests" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<span class="text" ><?php xl('Diagnosis:','e'); ?> </span>
<table><tr>
   <td>
   <select name="diagnosis1_bronchitis_form" >
      <option value="None"><?php xl('None','e'); ?></option>
      <option value="465.9, URI"><?php xl('465.9, URI','e'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php xl('466.0, Bronchitis, Acute NOS','e'); ?></option>
      <option value="493.92, Astma, Acute Exac."><?php xl('493.92, Asthma, Acute Exac.','e'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php xl('491.8, Bronchitis, Chronic','e'); ?></option>
      <option value="496.0, COPD"><?php xl('496.0, COPD','e'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php xl('491.21, COPD Exacerbation','e'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php xl('486.0, Pneumonia, Acute','e'); ?></option>
      <option value="519.7, Bronchospasm"><?php xl('519.7, Bronchospasm','e'); ?></option>
      <br><br>
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis2_bronchitis_form">
      <option value="None"><?php xl('None','e'); ?></option>
      <option value="465.9, URI"><?php xl('465.9, URI','e'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php xl('466.0, Bronchitis, Acute NOS','e'); ?></option>
      <option value="493.92, Asthma, Acute Exac."><?php xl('493.92, Asthma, Acute Exac.','e'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php xl('491.8, Bronchitis, Chronic','e'); ?></option>
      <option value="496.0, COPD"><?php xl('496.0, COPD','e'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php xl('491.21, COPD Exacerbation','e'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php xl('486.0, Pneumonia, Acute','e'); ?></option>
      <option value="519.7, Bronchospasm"><?php xl('519.7, Bronchospasm','e'); ?></option>
      <br><br>
   </select>
   </td>
</tr>   
<tr>
   <td>
   <select name="diagnosis3_bronchitis_form">
      <option value="None"><?php xl('None','e'); ?></option>
      <option value="465.9, URI"><?php xl('465.9, URI','e'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php xl('466.0, Bronchitis, Acute NOS','e'); ?></option>
      <option value="493.92, Asthma, Acute Exac."><?php xl('493.92, Asthma, Acute Exac.','e'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php xl('491.8, Bronchitis, Chronic','e'); ?></option>
      <option value="496.0, COPD"><?php xl('496.0, COPD','e'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php xl('491.21, COPD Exacerbation','e'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php xl('486.0, Pneumonia, Acute','e'); ?></option>
      <option value="519.7, Bronchospasm"><?php xl('519.7, Bronchospasm','e'); ?></option>
     <br><br>
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis4_bronchitis_form">
      <option value="None"><?php xl('None','e'); ?></option>
      <option value="465.9, URI"><?php xl('465.9, URI','e'); ?></option>
      <option value="466.0, Bronchitis, Acute NOS"><?php xl('466.0, Bronchitis, Acute NOS','e'); ?></option>
      <option value="493.92, Asthma, Acute Exac."><?php xl('493.92, Asthma, Acute Exac.','e'); ?></option>
      <option value="491.8, Bronchitis, Chronic"><?php xl('491.8, Bronchitis, Chronic','e'); ?></option>
      <option value="496.0, COPD"><?php xl('496.0, COPD','e'); ?></option>
      <option value="491.21,COPD Exacerbation"><?php xl('491.21, COPD Exacerbation','e'); ?></option>
      <option value="486.0, Pneumonia, Acute"><?php xl('486.0, Pneumonia, Acute','e'); ?></option>
      <option value="519.7, Bronchospasm"><?php xl('519.7, Bronchospasm','e'); ?></option>
     <br><br>
   </select>
   </td>
</tr>       
<table>   
<br></br>

<span class="text" ><?php xl('Additional Diagnosis:','e'); ?> </span><br></br>
<textarea name="bronchitis_additional_diagnosis" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<span class="text" ><?php xl('Treatment:','e'); ?> </span><br></br>
<textarea name="bronchitis_treatment" rows="4" cols="67" wrap="virtual name"></textarea>

<br></br>
<input type="Button" value="<?php xl('Check Input Data','e'); ?>" style="color: #483D8B" onClick = "onset_check(my_form)"<br> 
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save','e'); ?>]</a>
</form>

<?php
formFooter();
?>
