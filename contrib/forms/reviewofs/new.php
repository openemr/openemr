<?php
/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  andres_paglayan <andres_paglayan>
 * @author  cfapress <cfapress>
 * @author  sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: reviewofs");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/reviewofs/save.php?mode=new" name="my_form">
<span class="title">Review of Systems Checks</span><br><br>

<table><tr><td valign=top>

<span class=bold>General</span><br>
<input type=checkbox name='fever'  ><span class=text>Fever</span><br>
<input type=checkbox name='chills'  ><span class=text>Chills</span><br>
<input type=checkbox name='night_sweats'  ><span class=text>Night Sweats</span><br>
<input type=checkbox name='weight_loss'  ><span class=text>Weight Loss</span><br>
<input type=checkbox name='poor_appetite'  ><span class=text>Poor Appetite</span><br>
<input type=checkbox name='insomnia'  ><span class=text>Insomnia</span><br>
<input type=checkbox name='fatigued'  ><span class=text>Fatigued</span><br>
<input type=checkbox name='depressed'  ><span class=text>Depressed</span><br>
<input type=checkbox name='hyperactive'  ><span class=text>Hyperactive</span><br>
<input type=checkbox name='exposure_to_foreign_countries'  ><span class=text>Exposure to Foreign Countries</span><br>
<span class=bold>Skin</span><br>
<input type=checkbox name='rashes'  ><span class=text>Rashes</span><br>
<input type=checkbox name='infections'  ><span class=text>Infections</span><br>
<input type=checkbox name='ulcerations'  ><span class=text>Ulcerations</span><br>
<input type=checkbox name='pemphigus'  ><span class=text>Pemphigus</span><br>
<input type=checkbox name='herpes'  ><span class=text>Herpes</span><br>

</td><td valign=top>

<span class=bold>HEENT</span><br>
<input type=checkbox name='cataracts'  ><span class=text>Cataracts</span><br>
<input type=checkbox name='cataract_surgery'  ><span class=text>Cataract Surgery</span><br>
<input type=checkbox name='glaucoma'  ><span class=text>Glaucoma</span><br>
<input type=checkbox name='double_vision'  ><span class=text>Double Vision</span><br>
<input type=checkbox name='blurred_vision'  ><span class=text>Blurred Vision</span><br>
<input type=checkbox name='poor_hearing'  ><span class=text>Poor Hearing</span><br>
<input type=checkbox name='headaches'  ><span class=text>Headaches</span><br>
<input type=checkbox name='ringing_in_ears'  ><span class=text>Ringing in Ears</span><br>
<input type=checkbox name='bloody_nose'  ><span class=text>Bloody Nose</span><br>
<input type=checkbox name='sinusitis'  ><span class=text>Sinusitis</span><br>
<input type=checkbox name='sinus_surgery'  ><span class=text>Sinus Surgery</span><br>
<input type=checkbox name='dry_mouth'  ><span class=text>Dry Mouth</span><br>
<input type=checkbox name='strep_throat'  ><span class=text>Strep Throat</span><br>
<input type=checkbox name='tonsillectomy'  ><span class=text>Tonsillectomy</span><br>
<input type=checkbox name='swollen_lymph_nodes'  ><span class=text>Swollen Lymph Nodes</span><br>
<input type=checkbox name='throat_cancer'  ><span class=text>Throat Cancer</span><br>
<input type=checkbox name='throat_cancer_surgery'  ><span class=text>Throat Cancer Surgery</span><br>

</td><td valign=top>

<span class=bold>Cardiovascular</span><br>
<input type=checkbox name='heart_attack'  ><span class=text>Heart Attack</span><br>
<input type=checkbox name='irregular_heart_beat'  ><span class=text>Irregular Heart Beat</span><br>
<input type=checkbox name='chest_pains'  ><span class=text>Chest Pains</span><br>
<input type=checkbox name='shortness_of_breath'  ><span class=text>Shortness of Breath</span><br>
<input type=checkbox name='high_blood_pressure'  ><span class=text>High Blood Pressure</span><br>
<input type=checkbox name='heart_failure'  ><span class=text>Heart Failure</span><br>
<input type=checkbox name='poor_circulation'  ><span class=text>Poor Circulation</span><br>
<input type=checkbox name='vascular_surgery'  ><span class=text>Vascular Surgery</span><br>
<input type=checkbox name='cardiac_catheterization'  ><span class=text>Cardiac Catheterization</span><br>
<input type=checkbox name='coronary_artery_bypass'  ><span class=text>Coronary Artery Bypass</span><br>
<input type=checkbox name='heart_transplant'  ><span class=text>Heart Transplant</span><br>
<input type=checkbox name='stress_test'  ><span class=text>Stress Test</span><br>
<span class=bold>Endocrine</span><br>
<input type=checkbox name='insulin_dependent_diabetes'  ><span class=text>Insulin Dependent Diabetes</span><br>
<input type=checkbox name='noninsulin_dependent_diabetes'  ><span class=text>Non-Insulin Dependent Diabetes</span><br>
<input type=checkbox name='hypothyroidism'  ><span class=text>Hypothyroidism</span><br>
<input type=checkbox name='hyperthyroidism'  ><span class=text>Hyperthyroidism</span><br>
<input type=checkbox name='cushing_syndrom'  ><span class=text>Cushing Syndrom</span><br>
<input type=checkbox name='addison_syndrom'  ><span class=text>Addison Syndrom</span><br>

</td><td valign=top>

<span class=bold>Pulmonary</span><br>
<input type=checkbox name='emphysema'  ><span class=text>Emphysema</span><br>
<input type=checkbox name='chronic_bronchitis'  ><span class=text>Chronic Bronchitis</span><br>
<input type=checkbox name='interstitial_lung_disease'  ><span class=text>Interstitial Lung Disease</span><br>
<input type=checkbox name='shortness_of_breath_2'  ><span class=text>Shortness of Breath</span><br>
<input type=checkbox name='lung_cancer'  ><span class=text>Lung Cancer</span><br>
<input type=checkbox name='lung_cancer_surgery'  ><span class=text>Lung Cancer Surgery</span><br>
<input type=checkbox name='pheumothorax'  ><span class=text>Pheumothorax</span><br>
<span class=bold>Genitourinary</span><br>
<input type=checkbox name='kidney_failure'  ><span class=text>Kidney Failure</span><br>
<input type=checkbox name='kidney_stones'  ><span class=text>Kidney Stones</span><br>
<input type=checkbox name='kidney_cancer'  ><span class=text>Kidney Cancer</span><br>
<input type=checkbox name='kidney_infections'  ><span class=text>Kidney Infections</span><br>
<input type=checkbox name='bladder_infections'  ><span class=text>Bladder Infections</span><br>
<input type=checkbox name='bladder_cancer'  ><span class=text>Bladder Cancer</span><br>
<input type=checkbox name='prostate_problems'  ><span class=text>Prostate Problems</span><br>
<input type=checkbox name='prostate_cancer'  ><span class=text>Prostate Cancer</span><br>
<input type=checkbox name='kidney_transplant'  ><span class=text>Kidney Transplant</span><br>
<input type=checkbox name='sexually_transmitted_disease'  ><span class=text>Sexually Transmitted Disease</span><br>
<input type=checkbox name='burning_with_urination'  ><span class=text>Burning with Urination</span><br>
<input type=checkbox name='discharge_from_urethra'  ><span class=text>Discharge From Urethra</span><br>

</td><td valign=top>

<span class=bold>Gastrointestinal</span><br>
<input type=checkbox name='stomach_pains'  ><span class=text>Stomach Pains</span><br>
<input type=checkbox name='peptic_ulcer_disease'  ><span class=text>Peptic Ulcer Disease</span><br>
<input type=checkbox name='gastritis'  ><span class=text>Gastritis</span><br>
<input type=checkbox name='endoscopy'  ><span class=text>Endoscopy</span><br>
<input type=checkbox name='polyps'  ><span class=text>Polyps</span><br>
<input type=checkbox name='colonoscopy'  ><span class=text>colonoscopy</span><br>
<input type=checkbox name='colon_cancer'  ><span class=text>Colon Cancer</span><br>
<input type=checkbox name='colon_cancer_surgery'  ><span class=text>Colon Cancer Surgery</span><br>
<input type=checkbox name='ulcerative_colitis'  ><span class=text>Ulcerative Colitis</span><br>
<input type=checkbox name='crohns_disease'  ><span class=text>Crohn's Disease</span><br>
<input type=checkbox name='appendectomy'  ><span class=text>Appendectomy</span><br>
<input type=checkbox name='divirticulitis'  ><span class=text>Divirticulitis</span><br>
<input type=checkbox name='divirticulitis_surgery'  ><span class=text>Divirticulitis Surgery</span><br>
<input type=checkbox name='gall_stones'  ><span class=text>Gall Stones</span><br>
<input type=checkbox name='cholecystectomy'  ><span class=text>Cholecystectomy</span><br>
<input type=checkbox name='hepatitis'  ><span class=text>Hepatitis</span><br>
<input type=checkbox name='cirrhosis_of_the_liver'  ><span class=text>Cirrhosis of the Liver</span><br>
<input type=checkbox name='splenectomy'  ><span class=text>Splenectomy</span><br>

</td><td valign=top>

<span class=bold>Musculoskeletal</span><br>
<input type=checkbox name='osetoarthritis'  ><span class=text>Osetoarthritis</span><br>
<input type=checkbox name='rheumotoid_arthritis'  ><span class=text>Rheumotoid Arthritis</span><br>
<input type=checkbox name='lupus'  ><span class=text>Lupus</span><br>
<input type=checkbox name='ankylosing_sondlilitis'  ><span class=text>Ankylosing Sondlilitis</span><br>
<input type=checkbox name='swollen_joints'  ><span class=text>Swollen Joints</span><br>
<input type=checkbox name='stiff_joints'  ><span class=text>Stiff Joints</span><br>
<input type=checkbox name='broken_bones'  ><span class=text>Broken Bones</span><br>
<input type=checkbox name='neck_problems'  ><span class=text>Neck Problems</span><br>
<input type=checkbox name='back_problems'  ><span class=text>Back Problems</span><br>
<input type=checkbox name='back_surgery'  ><span class=text>Back Surgery</span><br>
<input type=checkbox name='scoliosis'  ><span class=text>Scoliosis</span><br>
<input type=checkbox name='herniated_disc'  ><span class=text>Herniated Disc</span><br>
<input type=checkbox name='shoulder_problems'  ><span class=text>Shoulder Problems</span><br>
<input type=checkbox name='elbow_problems'  ><span class=text>Elbow Problems</span><br>
<input type=checkbox name='wrist_problems'  ><span class=text>Wrist Problems</span><br>
<input type=checkbox name='hand_problems'  ><span class=text>Hand Problems</span><br>
<input type=checkbox name='hip_problems'  ><span class=text>Hip Problems</span><br>
<input type=checkbox name='knee_problems'  ><span class=text>Knee Problems</span><br>
<input type=checkbox name='ankle_problems'  ><span class=text>Ankle Problems</span><br>
<input type=checkbox name='foot_problems'  ><span class=text>Foot Problems</span><br>
</td>
</tr>
</table>

<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ></textarea><br>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
