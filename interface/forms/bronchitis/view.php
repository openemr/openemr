<!-- Form created by Nikolai Vitsyn by 2004/01/23 -->
<?php
include_once("../../globals.php");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_bronchitis", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/bronchitis/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title"><?php xl('Bronchitis Form','e'); ?></span><br><br>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
<br></br>

<span class=text><?php xl('Onset of Ilness: ','e'); ?></span><input type=entry name="bronchitis_date_of_illness" value="<?echo stripslashes($obj{"bronchitis_date_of_illness"});?>" ><br></br>

<span class=text><?php xl('HPI:','e'); ?> </span><br><textarea cols=67 rows=8 wrap=virtual name="bronchitis_hpi" ><?echo stripslashes($obj{"bronchitis_hpi"});?></textarea><br></br>


<table><th colspan="5">"<?php xl('Other Pertinent Symptoms','e'); ?> ":</th>
<tr>
<td width="60" align="right"><?php xl('Fever:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_fever" <?if ($obj{"bronchitis_ops_fever"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td width="140" align="right"><?php xl('Cough:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_cough" <?if ($obj{"bronchitis_ops_cough"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td width="170" align="right"><?php xl('Dizziness:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_dizziness" <?if ($obj{"bronchitis_ops_dizziness"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60" align="right"><?php xl('Chest Pain:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_chest_pain" <?if ($obj{"bronchitis_ops_chest_pain"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="130" align="right"><?php xl('Dyspnea:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_dyspnea" <?if ($obj{"bronchitis_ops_dyspnea"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="180" align="right"><?php xl('Sweating:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_sweating" <?if ($obj{"bronchitis_ops_sweating"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60" align="right"><?php xl('Wheezing:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_wheezing" <?if ($obj{"bronchitis_ops_wheezing"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td width="130" align="right"><?php xl('Malaise:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_malaise" <?if ($obj{"bronchitis_ops_malaise"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60" align="right"><?php xl('Sputum:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_sputum" <?if ($obj{"bronchitis_ops_sputum"} == "on")
{echo "checked";};?>><span class=text></span><br></td>

<td width="130" align="right"><?php xl('Appearance:','e'); ?>  <span class="text"></span></td>
<td><input type=entry name="bronchitis_ops_appearance" value="<?echo
stripslashes($obj{"bronchitis_ops_appearance"});?>" size="15"></td> 
</tr>
</table>

<table><tr>
<td width="227" align="right"><?php xl('All Reviewed and Negative:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_ops_all_reviewed" <?if ($obj{"bronchitis_ops_all_reviewed"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>
<br></br>

<table >
<tr>
<td width="60" align="right"><?php xl('Review of PMH:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_pmh" <?if ($obj{"bronchitis_review_of_pmh"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Medications:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_medications" <?if ($obj{"bronchitis_review_of_medications"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Allergies:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_allergies" <?if ($obj{"bronchitis_review_of_allergies"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Social History:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_sh" <?if ($obj{"bronchitis_review_of_sh"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Family History:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_review_of_fh" <?if ($obj{"bronchitis_review_of_fh"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
</tr>
</table>
<br></br>


<table>
<tr>
<td width="60"><?php xl('TM\'S:','e'); ?> </td>
<td align="right"><?php xl('Normal Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_normal_right" <?if ($obj{"bronchitis_tms_normal_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_normal_left" <?if ($obj{"bronchitis_tms_normal_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('NARES: Normal Right','e'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_normal_right" <?if ($obj{"bronchitis_nares_normal_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_nares_normal_left" <?if ($obj{"bronchitis_nares_normal_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"> <?php xl('Thickened Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_thickened_right" <?if ($obj{"bronchitis_tms_thickened_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_thickened_left" <?if ($obj{"bronchitis_tms_thickened_left"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('Swelling Right','e'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_swelling_right" <?if ($obj{"bronchitis_nares_swelling_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left: ','e'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_swelling_left" <?if ($obj{"bronchitis_nares_swelling_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php xl('A/F Level Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_af_level_right" <?if ($obj{"bronchitis_tms_af_level_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_af_level_left" <?if ($obj{"bronchitis_tms_af_level_left"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('Discharge Right','e'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_discharge_right" <?if ($obj{"bronchitis_nares_discharge_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left: ','e'); ?> </td>
<td><input type=checkbox name="bronchitis_nares_discharge_left" <?if ($obj{"bronchitis_nares_discharge_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php xl('Retracted Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_retracted_right" <?if ($obj{"bronchitis_tms_retracted_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_retracted_left" <?if ($obj{"bronchitis_tms_retracted_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php xl('Bulging Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_bulging_right" <?if ($obj{"bronchitis_tms_bulging_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_bulging_left" <?if ($obj{"bronchitis_tms_bulging_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php xl('Perforated Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_perforated_right" <?if ($obj{"bronchitis_tms_perforated_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_perforated_left" <?if ($obj{"bronchitis_tms_perforated_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>

<table><tr>
<td width="127"></td>
<td align="right"><?php xl('Not Examined:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_tms_nares_not_examined" <?if ($obj{"bronchitis_tms_nares_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr></table>
<br></br>

<table>
<tr>
<td width="90"><?php xl('SINUS TENDERNESS:','e'); ?> </td>
<td align="right"><?php xl('No Sinus Tenderness:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_no_sinus_tenderness" <?if ($obj{"bronchitis_no_sinus_tenderness"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="90"><?php xl('OROPHARYNX: ','e'); ?> </td>
<td align="right"><?php xl('Normal Oropharynx:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_normal"<?if ($obj{"bronchitis_oropharynx_normal"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="90" align="right"><?php xl('Frontal Right:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_frontal_right" <?if ($obj{"bronchitis_sinus_tenderness_frontal_right"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_frontal_left" <?if ($obj{"bronchitis_sinus_tenderness_frontal_left"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Erythema:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_erythema" <?if ($obj{"bronchitis_oropharynx_erythema"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Exudate:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_exudate" <?if ($obj{"bronchitis_oropharynx_exudate"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Abcess:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_abcess" <?if ($obj{"bronchitis_oropharynx_abcess"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Ulcers:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_oropharynx_ulcers" <?if ($obj{"bronchitis_oropharynx_ulcers"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width ="90" align="right"><?php xl('Maxillary Right:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_maxillary_right" <?if ($obj{"bronchitis_sinus_tenderness_maxillary_right"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td align="right"><?php xl('Left:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_maxillary_left" <?if ($obj{"bronchitis_sinus_tenderness_maxillary_left"} == "on")
{echo "checked";};?>><span class=text></span><br></td>
<td width="130" align="right"><?php xl('Appearance:','e'); ?>  <span class="text"></span></td>
<td><input type=entry name="bronchitis_oropharynx_appearance" value="<?echo
stripslashes($obj{"bronchitis_oropharynx_appearance"});?>" size="15"></td> 
</tr>
</table>

<table>
<tr>
<td width="256" align="right"><?php xl('Not Examined:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_sinus_tenderness_not_examined" <?if ($obj{"bronchitis_sinus_tenderness_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="208" align="right"><?php xl('Not Examined:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_oropharynx_not_examined" <?if ($obj{"bronchitis_oropharynx_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>
<br></br>

<table>
<tr>
<td width="60"><?php xl('HEART:','e'); ?> </td>
<td align="right"><?php xl('laterally displaced PMI:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_pmi" <?if ($obj{"bronchitis_heart_pmi"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('S3:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_s3" <?if ($obj{"bronchitis_heart_s3"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('S4:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_s4" <?if ($obj{"bronchitis_heart_s4"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td align="right"><?php xl('Click:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_click" <?if ($obj{"bronchitis_heart_click"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td align="right"><?php xl('Rub:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_heart_rub" <?if ($obj{"bronchitis_heart_rub"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>

<table><tr>
<td width="200" align="right"><?php xl('Murmur:','e'); ?>  <span class="text"></span></td>
<td><input type=entry name="bronchitis_heart_murmur" value="<?echo
stripslashes($obj{"bronchitis_heart_murmur"});?>" size="15"></td> 

<td><span class="text"><?php xl('Grade:','e'); ?>  </span></td><td>
<input type=entry name="bronchitis_heart_grade" value="<?echo
stripslashes($obj{"bronchitis_heart_grade"});?>" size="15"></td> 

<td><span class="text"><?php xl('Location:','e'); ?>  </span></td><td>
<input type=entry name="bronchitis_heart_location" value="<?echo
stripslashes($obj{"bronchitis_heart_location"});?>" size="15"></td> 
</tr>
</table>

<table><tr>
<td width="205" align="right"><?php xl('Normal Cardiac Exam:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_heart_normal" <?if ($obj{"bronchitis_heart_normal"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="95" align="right"><?php xl('Not Examined:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_heart_not_examined" <?if ($obj{"bronchitis_heart_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr></table>
<br></br>

<table><tr>
<td width="60"><?php xl('Lungs:','e'); ?> </td>
<td width="106"><?php xl('Breath Sounds:','e'); ?> </td>
<td align="right"> <?php xl('normal:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_bs_normal" <?if ($obj{"bronchitis_lungs_bs_normal"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('reduced:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_bs_reduced" <?if ($obj{"bronchitis_lungs_bs_reduced"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('increased:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_bs_increased" <?if ($obj{"bronchitis_lungs_bs_increased"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td><?php xl('Crackles:','e'); ?> </td>
<td align="right"><?php xl(' LLL:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_crackles_lll" <?if ($obj{"bronchitis_lungs_crackles_lll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('RLL:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_crackles_rll" <?if ($obj{"bronchitis_lungs_crackles_rll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('Bilateral:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_crackles_bll" <?if ($obj{"bronchitis_lungs_crackles_bll"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td><?php xl('Rubs:','e'); ?> </td>
<td align="right"><?php xl('LLL:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_rubs_lll" <?if ($obj{"bronchitis_lungs_rubs_lll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('RLL:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_rubs_rll" <?if ($obj{"bronchitis_lungs_rubs_rll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('Bilateral:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_rubs_bll" <?if ($obj{"bronchitis_lungs_rubs_bll"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>

<tr>
<td width="60"></td>
<td><?php xl('Wheezes:','e'); ?> </td>
<td align="right"><?php xl('LLL:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_lll" <?if ($obj{"bronchitis_lungs_wheezes_lll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('RLL:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_rll" <?if ($obj{"bronchitis_lungs_wheezes_rll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('Bilateral:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_bll" <?if ($obj{"bronchitis_lungs_wheezes_bll"} == "on")
{echo "checked";};?>><span class=text></span><br>

<td align="right"><?php xl('Diffuse:','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_wheezes_dll" <?if ($obj{"bronchitis_lungs_wheezes_dll"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr>
</table>


<table><tr>
<td width="218" align="right"><?php xl('Normal Lung Exam:','e'); ?>  </td>
<td><input type=checkbox name="bronchitis_lungs_normal_exam" <?if ($obj{"bronchitis_lungs_normal_exam"} == "on")
{echo "checked";};?>><span class=text></span><br>
<td width="140" align="right"><?php xl('Not Examined','e'); ?> </td>
<td><input type=checkbox name="bronchitis_lungs_not_examined" <?if ($obj{"bronchitis_lungs_not_examined"} == "on")
{echo "checked";};?>><span class=text></span><br>
</tr></table>
<br></br>

<span class="text"><?php xl('Diagnostic Tests:','e'); ?> </span></br>
<textarea name="bronchitis_diagnostic_tests" cols ="67" rows="4"  wrap="virtual name">
<?echo stripslashes($obj{"bronchitis_diagnostic_tests"});?></textarea>
<br></br>

<table><tr>
<span class="text"><?php xl('Diagnosis: ','e'); ?> </span>
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
<span class="text"><?php xl('Additional Diagnosis:','e'); ?>  </span></br>
<textarea name="bronchitis_additional_diagnosis" rows="4" cols="67" wrap="virtual name">
<?echo stripslashes($obj{"bronchitis_additional_diagnosis"});?></textarea>
<br></br>

<span class="text"><?php xl('Treatment: ','e'); ?> </span></br>
<textarea name="bronchitis_treatment" rows="4" cols="67" wrap="virtual name">
<?echo stripslashes($obj{"bronchitis_treatment"});?></textarea>
</br>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?> ]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?> ]</a>

</form>
<?php
formFooter();
?>
