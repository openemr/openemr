<!-- Form created by Nikolai Vitsyn: 2004/01/23  -->
<!--                          Update 2004/01/29  -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: bronchitis");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
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

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?>
topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/bronchitis/save.php?mode=new" name="my_form">
<br></br>
<span class="title" >Bronchitis Form</span>
<br></br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link" style="color: #483D8B">[Don't Save]</a>
<br></br>
<span class="text" >Onset of Illness: </span><input type="entry" name="bronchitis_date_of_illness" value=""></input>
<br></br>

<span class="text" >HPI:</span><br></br>
<textarea name="bronchitis_hpi" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<table ><th colspan="5">"Other Pertinent Symptoms":</th>
<tr>
<td width="80" align="right">Fever:</td>
<td><input type="checkbox" name="bronchitis_ops_fever"></input></td>
<td width="100" align="right">Cough:</td>
<td><input type="checkbox" name="bronchitis_ops_cough"></input></td>
<td width="60" align="right">Dizziness:</td>
<td><input type="checkbox" name="bronchitis_ops_dizziness"></input></td>
</tr>
<tr>
<td width="80" align="right">Chest Pain:</td>
<td><input type="checkbox" name="bronchitis_ops_chest_pain"></input></td>
<td width="100" align="right">Dyspnea:</td>
<td><input type="checkbox" name="bronchitis_ops_dyspnea"></input></td>
<td width="60" align="right">Sweating:</td>
<td><input type="checkbox" name="bronchitis_ops_sweating"></input></td>
</tr>
<tr>
<td width="80" align="right">Wheezing:</td>
<td><input type="checkbox" name="bronchitis_ops_wheezing"></input></td>
<td width="100" align="right">Malaise:</td>
<td><input type="checkbox" name="bronchitis_ops_malaise"></input></td>
</tr>
<tr>
<td width="80" align="right">Sputum:</td>
<td><input type="checkbox" name="bronchitis_ops_sputum"></input></td>
<td width="100" align="right">Appearance:</td>
<td><input type="text" name="bronchitis_ops_appearance" size="10" value="none"></input></td>
</tr>
</table>

<table>
<tr>
<td width="205" align="right">All Reviewed and Negative:</td>
<td><input type="checkbox" name="bronchitis_ops_all_reviewed"></input></td>
</tr>
</table>
<br></br>


<table >
<tr>
<td width="60">Review of PMH:</td>
<td align="right"></td>
<td><input type="checkbox" name="bronchitis_review_of_pmh"></input></td>
<td align="right">Medications:</td>
<td><input type="checkbox" name="bronchitis_review_of_medications"></input></td>
<td align="right">Allergies:</td>
<td><input type="checkbox" name="bronchitis_review_of_allergies"></input></td>
<td align="right">Social History:</td>
<td><input type="checkbox" name="bronchitis_review_of_sh"></input></td>
<td align="right">Family History:</td>
<td><input type="checkbox" name="bronchitis_review_of_fh"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60">TM'S:</td>
<td align="right">Normal Right:</td>
<td><input type="checkbox" name="bronchitis_tms_normal_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_tms_normal_left"></input></td>

<td width="80">NARES: </td>
<td align="right">Normal Right</td>
<td><input type="checkbox" name="bronchitis_nares_normal_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_nares_normal_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right">Thickened Right:</td>
<td><input type="checkbox" name="bronchitis_tms_thickened_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_tms_thickened_left"></input></td>

<td width="80"></td>
<td align="right">Swelling Right</td>
<td><input type="checkbox" name="bronchitis_nares_swelling_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_nares_swelling_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right">A/F Level Right:</td>
<td><input type="checkbox" name="bronchitis_tms_af_level_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_tms_af_level_left"></input></td>

<td width="80"></td>
<td align="right">Discharge Right:</td>
<td><input type="checkbox" name="bronchitis_nares_discharge_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_nares_discharge_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right">Retracted Right:</td>
<td><input type="checkbox" name="bronchitis_tms_retracted_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_tms_retracted_left"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right">Bulging Right:</td>
<td><input type="checkbox" name="bronchitis_tms_bulging_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_tms_bulging_left"></input></td>

</tr>

<tr>
<td width="50"></td>
<td align="right">Perforated Right:</td>
<td><input type="checkbox" name="bronchitis_tms_perforated_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_tms_perforated_left"></input></td>
</tr>
</table>

<table>
<tr>
<td width="220" align="right">Not Examined:</td>
<td><input type="checkbox" name="bronchitis_tms_nares_not_examined"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="90">SINUS TENDERNESS:</td>
<td align="right">No Sinus Tenderness:</td>
<td><input type="checkbox" name="bronchitis_no_sinus_tenderness"></input></td>
<td align="right"></td>

<td width="90">OROPHARYNX: </td>
<td align="right">Normal Oropharynx:</td>
<td><input type="checkbox" name="bronchitis_oropharynx_normal"></input></td>
<td align="right"></td>
</tr>

<tr>
<td width="50"></td>
<td align="right">Frontal Right:</td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_frontal_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_frontal_left"></input></td>
<td align="right">Erythema:</td>
<td><input type="checkbox" name="bronchitis_oropharynx_erythema"></input></td>
<td align="right">Exudate:</td>
<td><input type="checkbox" name="bronchitis_oropharynx_exudate"></input></td>
<td align="right">Abcess:</td>
<td><input type="checkbox" name="bronchitis_oropharynx_abcess"></input></td>
<td align="right">Ulcers:</td>
<td><input type="checkbox" name="bronchitis_oropharynx_ulcers"></input></td>
</tr>

<tr>
<td width="50"></td>
<td align="right">Maxillary Right:</td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_maxillary_right"></input></td>
<td align="right">Left:</td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_maxillary_left"></input></td>

<td width="120" align="right">Appearance:</td>
<td><input type="text" name="bronchitis_oropharynx_appearance" size="10" value="normal"></input></td>
</tr>
</table>

<table>
<tr>
<td width="238" align="right" >Not Examined: </td>
<td><input type="checkbox" name="bronchitis_sinus_tenderness_not_examined"></input></td>
<td width="268" align="right" >Not Examined: </td>
<td><input type="checkbox" name="bronchitis_oropharynx_not_examined"></input></td>
</tr>
</table>
<br></br>

<table >
<tr>
<td width="60">HEART:</td>
<td align="right">laterally displaced PMI:</td>
<td><input type="checkbox" name="bronchitis_heart_pmi"></input></td>
<td align="right">S3:</td>
<td><input type="checkbox" name="bronchitis_heart_s3"></input></td>
<td align="right">S4:</td>
<td><input type="checkbox" name="bronchitis_heart_s4"></input></td>
</tr>
<tr>
<td width="60"></td>
<td align="right">Click:</td>
<td><input type="checkbox" name="bronchitis_heart_click"></input></td>
<td align="right">Rub:</td>
<td><input type="checkbox" name="bronchitis_heart_rub"></input></td>
</tr>
</table>

<table>
<tr>
<td width="60"></td>
<td>Murmur:</td>
<td><input type="text" name="bronchitis_heart_murmur" size="10" value="none"></input></td>
<td>Grade:</td>
<td><input type="text" name="bronchitis_heart_grade" size="10" value="n/a"></input></td>
<td>Location:</td>
<td><input type="text" name="bronchitis_heart_location" size="10" value="n/a"></input></td>
</tr>
</table>

<table>
<tr>
<td width="203" align="right" >Normal Cardiac Exam: </td>
<td><input type="checkbox" name="bronchitis_heart_normal"></input></td>
<td width="93" align="right">Not Examined: </td>
<td><input type="checkbox" name="bronchitis_heart_not_examined"></input></td>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60">LUNGS:</td>
<td width="106">Breath Sounds:</td>
<td align="right"> normal:</td>
<td><input type="checkbox" name="bronchitis_lungs_bs_normal"></input></td>
<td align="right">reduced:</td>
<td><input type="checkbox" name="bronchitis_lungs_bs_reduced"></input></td>
<td align="right">increased:</td>
<td><input type="checkbox" name="bronchitis_lungs_bs_increased"></input></td>
</tr>

<tr>
<td width="60"></td>
<td>Crackles:</td>
<td align="right">LLL:</td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_lll"></input></td>
<td align="right">RLL:</td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_rll"></input></td>
<td align="right">Bilateral:</td>
<td><input type="checkbox" name="bronchitis_lungs_crackles_bll"></input></td>
</tr>

<tr>
<td width="60"></td>
<td>Rubs:</td>
<td align="right">LLL:</td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_lll"></input></td>
<td align="right">RLL:</td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_rll"></input></td>
<td align="right">Bilateral:</td>
<td><input type="checkbox" name="bronchitis_lungs_rubs_bll"></input></td>
</tr>

<tr>
<td width="60"></td>
<td>Wheezes:</td>
<td align="right">LLL:</td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_lll"></input></td>
<td align="right">RLL:</td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_rll"></input></td>
<td align="right">Bilateral:</td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_bll"></input></td>
<td align="right">Diffuse:</td>
<td><input type="checkbox" name="bronchitis_lungs_wheezes_dll"></input></td>
</tr>
</table>

<table>
<tr>
<td width="218" align="right" >Normal Lung Exam: </td>
<td><input type="checkbox" name="bronchitis_lungs_normal_exam"></input></td>
<td width="140" align="right" >Not Examined: </td>
<td><input type="checkbox" name="bronchitis_lungs_not_examined"></input></td>
</tr>
</table>
<br></br>

<span class="text" >Diagnostic Tests:</span><br></br>
<textarea name="bronchitis_diagnostic_tests" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<span class="text" >Diagnosis: </span>
<table><tr>
   <td>
   <select name="diagnosis1_bronchitis_form" >
      <option value="None">None</option>
      <option value="465.9, URI">465.9, URI</option>
      <option value="466.0, Bronchitis, Acute NOS">466.0, Bronchitis, Acute NOS</option>
      <option value="493.92, Astma, Acute Exac.">493.92, Asthma, Acute Exac.</option>
      <option value="491.8, Bronchitis, Chronic">491.8, Bronchitis, Chronic</option>
      <option value="496.0, COPD">496.0, COPD</option>
      <option value="491.21,COPD Exacerbation">491.21, COPD Exacerbation</option>
      <option value="486.0, Pneumonia, Acute">486.0, Pneumonia, Acute</option>
      <option value="519.7, Bronchospasm">519.7, Bronchospasm</option>
      <br><br>
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis2_bronchitis_form">
      <option value="None">None</option>
      <option value="465.9, URI">465.9, URI</option>
      <option value="466.0, Bronchitis, Acute NOS">466.0, Bronchitis, Acute NOS</option>
      <option value="493.92, Asthma, Acute Exac.">493.92, Asthma, Acute Exac.</option>
      <option value="491.8, Bronchitis, Chronic">491.8, Bronchitis, Chronic</option>
      <option value="496.0, COPD">496.0, COPD</option>
      <option value="491.21,COPD Exacerbation">491.21, COPD Exacerbation</option>
      <option value="486.0, Pneumonia, Acute">486.0, Pneumonia, Acute</option>
      <option value="519.7, Bronchospasm">519.7, Bronchospasm</option>
      <br><br>
   </select>
   </td>
</tr>   
<tr>
   <td>
   <select name="diagnosis3_bronchitis_form">
      <option value="None">None</option>
      <option value="465.9, URI">465.9, URI</option>
      <option value="466.0, Bronchitis, Acute NOS">466.0, Bronchitis, Acute NOS</option>
      <option value="493.92, Asthma, Acute Exac.">493.92, Asthma, Acute Exac.</option>
      <option value="491.8, Bronchitis, Chronic">491.8, Bronchitis, Chronic</option>
      <option value="496.0, COPD">496.0, COPD</option>
      <option value="491.21,COPD Exacerbation">491.21, COPD Exacerbation</option>
      <option value="486.0, Pneumonia, Acute">486.0, Pneumonia, Acute</option>
      <option value="519.7, Bronchospasm">519.7, Bronchospasm</option>
     <br><br>
   </select>
   </td>
</tr>
<tr>
   <td>
   <select name="diagnosis4_bronchitis_form">
      <option value="None">None</option>
      <option value="465.9, URI">465.9, URI</option>
      <option value="466.0, Bronchitis, Acute NOS">466.0, Bronchitis, Acute NOS</option>
      <option value="493.92, Asthma, Acute Exac.">493.92, Asthma, Acute Exac.</option>
      <option value="491.8, Bronchitis, Chronic">491.8, Bronchitis, Chronic</option>
      <option value="496.0, COPD">496.0, COPD</option>
      <option value="491.21,COPD Exacerbation">491.21, COPD Exacerbation</option>
      <option value="486.0, Pneumonia, Acute">486.0, Pneumonia, Acute</option>
      <option value="519.7, Bronchospasm">519.7, Bronchospasm</option>
     <br><br>
   </select>
   </td>
</tr>       
<table>   
<br></br>

<span class="text" >Additional Diagnosis: </span><br></br>
<textarea name="bronchitis_additional_diagnosis" rows="4" cols="67" wrap="virtual name"></textarea>
<br></br>

<span class="text" >Treatment: </span><br></br>
<textarea name="bronchitis_treatment" rows="4" cols="67" wrap="virtual name"></textarea>

<br></br>
<input type="Button" value="Check Input Data" style="color: #483D8B" onClick = "onset_check(my_form)"<br> 
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link" style="color: #483D8B">[Don't Save]</a>
</form>

<?php
formFooter();
?>
