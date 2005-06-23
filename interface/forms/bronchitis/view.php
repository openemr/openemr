<!-- Form created by Nikolai Vitsyn by 2004/01/23 -->
<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_bronchitis", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/bronchitis/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Bronchitis Form</span><br><br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>
<br></br>

<span class=text>Onset of Ilness: </span><input type=entry name="bronchitis_date_of_illness" value="<?echo stripslashes($obj{"bronchitis_date_of_illness"});?>" ><br></br>

<span class=text>HPI: </span><br><textarea cols=67 rows=8 wrap=virtual name="bronchitis_hpi" ><?echo stripslashes($obj{"bronchitis_hpi"});?></textarea><br></br>


<table><th colspan="5">"Other Pertinent Symptoms":</th>
<tr>
<td width="60" align="right">Fever:</td>
<td><input type=checkbox name="bronchitis_ops_fever" <?if ($obj{"bronchitis_ops_fever"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td width="140" align="right">Cough:</td>
<td><input type=checkbox name="bronchitis_ops_cough" <?if ($obj{"bronchitis_ops_cough"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td width="170" align="right">Dizziness:</td>
<td><input type=checkbox name="bronchitis_ops_dizziness" <?if ($obj{"bronchitis_ops_dizziness"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60" align="right">Chest Pain:</td>
<td><input type=checkbox name="bronchitis_ops_chest_pain" <?if ($obj{"bronchitis_ops_chest_pain"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="130" align="right">Dyspnea:</td>
<td><input type=checkbox name="bronchitis_ops_dyspnea" <?if ($obj{"bronchitis_ops_dyspnea"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="180" align="right">Sweating:</td>
<td><input type=checkbox name="bronchitis_ops_sweating" <?if ($obj{"bronchitis_ops_sweating"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60" align="right">Wheezing:</td>
<td><input type=checkbox name="bronchitis_ops_wheezing" <?if ($obj{"bronchitis_ops_wheezing"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td width="130" align="right">Malaise:</td>
<td><input type=checkbox name="bronchitis_ops_malaise" <?if ($obj{"bronchitis_ops_malaise"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60" align="right">Sputum:</td>
<td><input type=checkbox name="bronchitis_ops_sputum" <?if ($obj{"bronchitis_ops_sputum"} == "on")
{echo "checked";};?>><span class=text></span><br></td>

<td width="130" align="right">Appearance: <span class="text"></span></td>
<td><input type=entry name="bronchitis_ops_appearance" value="<?echo
stripslashes($obj{"bronchitis_ops_appearance"});?>" size="15"></td> 
</tr>
</table>

<table><tr>
<td width="227" align="right">All Reviewed and Negative:</td>
<td><input type=checkbox name="bronchitis_ops_all_reviewed" <?if ($obj{"bronchitis_ops_all_reviewed"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>
<br></br>

<table >
<tr>
<td width="60" align="right">Review of PMH:</td>
<td><input type=checkbox name="bronchitis_review_of_pmh" <?if ($obj{"bronchitis_review_of_pmh"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right">Medications:</td>
<td><input type=checkbox name="bronchitis_review_of_medications" <?if ($obj{"bronchitis_review_of_medications"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right">Allergies:</td>
<td><input type=checkbox name="bronchitis_review_of_allergies" <?if ($obj{"bronchitis_review_of_allergies"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right">Social History:</td>
<td><input type=checkbox name="bronchitis_review_of_sh" <?if ($obj{"bronchitis_review_of_sh"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right">Family History:</td>
<td><input type=checkbox name="bronchitis_review_of_fh" <?if ($obj{"bronchitis_review_of_fh"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
</tr>
</table>
<br></br>


<table>
<tr>
<td width="60">TM'S:</td>
<td align="right">Normal Right:</td>
<td><input type=checkbox name="bronchitis_tms_normal_right" <?if ($obj{"bronchitis_tms_normal_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_tms_normal_left" <?if ($obj{"bronchitis_tms_normal_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">NARES: Normal Right</td>
<td><input type=checkbox name="bronchitis_nares_normal_right" <?if ($obj{"bronchitis_nares_normal_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left: </td>
<td><input type=checkbox name="bronchitis_nares_normal_left" <?if ($obj{"bronchitis_nares_normal_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"> Thickened Right:</td>
<td><input type=checkbox name="bronchitis_tms_thickened_right" <?if ($obj{"bronchitis_tms_thickened_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_tms_thickened_left" <?if ($obj{"bronchitis_tms_thickened_left"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">Swelling Right</td>
<td><input type=checkbox name="bronchitis_nares_swelling_right" <?if ($obj{"bronchitis_nares_swelling_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left: </td>
<td><input type=checkbox name="bronchitis_nares_swelling_left" <?if ($obj{"bronchitis_nares_swelling_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right">A/F Level Right:</td>
<td><input type=checkbox name="bronchitis_tms_af_level_right" <?if ($obj{"bronchitis_tms_af_level_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_tms_af_level_left" <?if ($obj{"bronchitis_tms_af_level_left"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">Discharge Right</td>
<td><input type=checkbox name="bronchitis_nares_discharge_right" <?if ($obj{"bronchitis_nares_discharge_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left: </td>
<td><input type=checkbox name="bronchitis_nares_discharge_left" <?if ($obj{"bronchitis_nares_discharge_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right">Retracted Right:</td>
<td><input type=checkbox name="bronchitis_tms_retracted_right" <?if ($obj{"bronchitis_tms_retracted_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_tms_retracted_left" <?if ($obj{"bronchitis_tms_retracted_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right">Bulging Right:</td>
<td><input type=checkbox name="bronchitis_tms_bulging_right" <?if ($obj{"bronchitis_tms_bulging_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_tms_bulging_left" <?if ($obj{"bronchitis_tms_bulging_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right">Perforated Right:</td>
<td><input type=checkbox name="bronchitis_tms_perforated_right" <?if ($obj{"bronchitis_tms_perforated_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_tms_perforated_left" <?if ($obj{"bronchitis_tms_perforated_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>

<table><tr>
<td width="127"></td>
<td align="right">Not Examined:</td>
<td><input type=checkbox name="bronchitis_tms_nares_not_examined" <?if ($obj{"bronchitis_tms_nares_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr></table>
<br></br>

<table>
<tr>
<td width="90">SINUS TENDERNESS:</td>
<td align="right">No Sinus Tenderness:</td>
<td><input type=checkbox name="bronchitis_no_sinus_tenderness" <?if ($obj{"bronchitis_no_sinus_tenderness"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="90">OROPHARYNX: </td>
<td align="right">Normal Oropharynx:</td>
<td><input type=checkbox name="bronchitis_oropharynx_normal"<?if ($obj{"bronchitis_oropharynx_normal"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="90" align="right">Frontal Right: </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_frontal_right" <?if ($obj{"bronchitis_sinus_tenderness_frontal_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_frontal_left" <?if ($obj{"bronchitis_sinus_tenderness_frontal_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Erythema:</td>
<td><input type=checkbox name="bronchitis_oropharynx_erythema" <?if ($obj{"bronchitis_oropharynx_erythema"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Exudate:</td>
<td><input type=checkbox name="bronchitis_oropharynx_exudate" <?if ($obj{"bronchitis_oropharynx_exudate"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Abcess:</td>
<td><input type=checkbox name="bronchitis_oropharynx_abcess" <?if ($obj{"bronchitis_oropharynx_abcess"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Ulcers:</td>
<td><input type=checkbox name="bronchitis_oropharynx_ulcers" <?if ($obj{"bronchitis_oropharynx_ulcers"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width ="90" align="right">Maxillary Right:</td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_maxillary_right" <?if ($obj{"bronchitis_sinus_tenderness_maxillary_right"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right">Left:</td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_maxillary_left" <?if ($obj{"bronchitis_sinus_tenderness_maxillary_left"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td width="130" align="right">Appearance: <span class="text"></span></td>
<td><input type=entry name="bronchitis_oropharynx_appearance" value="<?echo
stripslashes($obj{"bronchitis_oropharynx_appearance"});?>" size="15"></td> 
</tr>
</table>

<table>
<tr>
<td width="256" align="right">Not Examined: </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_not_examined" <?if ($obj{"bronchitis_sinus_tenderness_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="208" align="right">Not Examined: </td>
<td><input type=checkbox name="bronchitis_oropharynx_not_examined" <?if ($obj{"bronchitis_oropharynx_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60">HEART:</td>
<td align="right">laterally displaced PMI:</td>
<td><input type=checkbox name="bronchitis_heart_pmi" <?if ($obj{"bronchitis_heart_pmi"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">S3:</td>
<td><input type=checkbox name="bronchitis_heart_s3" <?if ($obj{"bronchitis_heart_s3"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">S4:</td>
<td><input type=checkbox name="bronchitis_heart_s4" <?if ($obj{"bronchitis_heart_s4"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right">Click:</td>
<td><input type=checkbox name="bronchitis_heart_click" <?if ($obj{"bronchitis_heart_click"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right">Rub:</td>
<td><input type=checkbox name="bronchitis_heart_rub" <?if ($obj{"bronchitis_heart_rub"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>

<table><tr>
<td width="200" align="right">Murmur: <span class="text"></span></td>
<td><input type=entry name="bronchitis_heart_murmur" value="<?echo
stripslashes($obj{"bronchitis_heart_murmur"});?>" size="15"></td> 

<td><span class="text">Grade: </span></td><td>
<input type=entry name="bronchitis_heart_grade" value="<?echo
stripslashes($obj{"bronchitis_heart_grade"});?>" size="15"></td> 

<td><span class="text">Location: </span></td><td>
<input type=entry name="bronchitis_heart_location" value="<?echo
stripslashes($obj{"bronchitis_heart_location"});?>" size="15"></td> 
</tr>
</table>

<table><tr>
<td width="205" align="right">Normal Cardiac Exam: </td>
<td><input type=checkbox name="bronchitis_heart_normal" <?if ($obj{"bronchitis_heart_normal"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="95" align="right">Not Examined: </td>
<td><input type=checkbox name="bronchitis_heart_not_examined" <?if ($obj{"bronchitis_heart_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr></table>
<br></br>

<table><tr>
<td width="60">Lungs:</td>
<td width="106">Breath Sounds:</td>
<td align="right"> normal:</td>
<td><input type=checkbox name="bronchitis_lungs_bs_normal" <?if ($obj{"bronchitis_lungs_bs_normal"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">reduced:</td>
<td><input type=checkbox name="bronchitis_lungs_bs_reduced" <?if ($obj{"bronchitis_lungs_bs_reduced"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">increased:</td>
<td><input type=checkbox name="bronchitis_lungs_bs_increased" <?if ($obj{"bronchitis_lungs_bs_increased"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td>Crackles:</td>
<td align="right"> LLL:</td>
<td><input type=checkbox name="bronchitis_lungs_crackles_lll" <?if ($obj{"bronchitis_lungs_crackles_lll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">RLL:</td>
<td><input type=checkbox name="bronchitis_lungs_crackles_rll" <?if ($obj{"bronchitis_lungs_crackles_rll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">Bilateral:</td>
<td><input type=checkbox name="bronchitis_lungs_crackles_bll" <?if ($obj{"bronchitis_lungs_crackles_bll"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td>Rubs:</td>
<td align="right">LLL:</td>
<td><input type=checkbox name="bronchitis_lungs_rubs_lll" <?if ($obj{"bronchitis_lungs_rubs_lll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">RLL:</td>
<td><input type=checkbox name="bronchitis_lungs_rubs_rll" <?if ($obj{"bronchitis_lungs_rubs_rll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">Bilateral:</td>
<td><input type=checkbox name="bronchitis_lungs_rubs_bll" <?if ($obj{"bronchitis_lungs_rubs_bll"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td>Wheezes:</td>
<td align="right">LLL:</td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_lll" <?if ($obj{"bronchitis_lungs_wheezes_lll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">RLL:</td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_rll" <?if ($obj{"bronchitis_lungs_wheezes_rll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">Bilateral:</td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_bll" <?if ($obj{"bronchitis_lungs_wheezes_bll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right">Diffuse:</td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_dll" <?if ($obj{"bronchitis_lungs_wheezes_dll"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>


<table><tr>
<td width="218" align="right">Normal Lung Exam: </td>
<td><input type=checkbox name="bronchitis_lungs_normal_exam" <?if ($obj{"bronchitis_lungs_normal_exam"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="140" align="right">Not Examined</td>
<td><input type=checkbox name="bronchitis_lungs_not_examined" <?if ($obj{"bronchitis_lungs_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr></table>
<br></br>

<span class="text">Diagnostic Tests:</span></br>
<textarea name="bronchitis_diagnostic_tests" cols ="67" rows="4"  wrap="virtual name">
<?echo stripslashes($obj{"bronchitis_diagnostic_tests"});?></textarea>
<br></br>

<table><tr>
<span class="text">Diagnosis: </span>
<br><input type=entry name="diagnosis1_bronchitis_form" value="<?echo
stripslashes($obj{"diagnosis1_bronchitis_form"});?>" size="40"><br> 
</tr>

<tr>
<input type=entry name="diagnosis2_bronchitis_form" value="<?echo
stripslashes($obj{"diagnosis2_bronchitis_form"});?>" size="40"><br> 
</tr>   

<tr>
<input type=entry name="diagnosis3_bronchitis_form" value="<?echo
stripslashes($obj{"diagnosis3_bronchitis_form"});?>" size="40"><br> 
</tr>

<tr>
<input type=entry name="diagnosis4_bronchitis_form" value="<?echo
 stripslashes($obj{"diagnosis4_bronchitis_form"});?>" size="40"><br> 
</tr>

<table>   
<br>
<span class="text">Additional Diagnosis: </span></br>
<textarea name="bronchitis_additional_diagnosis" rows="4" cols="67" wrap="virtual name">
<?echo stripslashes($obj{"bronchitis_additional_diagnosis"});?></textarea>
<br></br>

<span class="text">Treatment: </span></br>
<textarea name="bronchitis_treatment" rows="4" cols="67" wrap="virtual name">
<?echo stripslashes($obj{"bronchitis_treatment"});?></textarea>
</br>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>

</form>
<?php
formFooter();
?>
