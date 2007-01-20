<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: reviewofs");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/reviewofs/save.php?mode=new" name="my_form">
<span class="title"><?php xl('Review of Systems Checks','e'); ?></span><br><br>

<table><tr><td valign=top>

<span class=bold><?php xl('General','e'); ?></span><br>
<input type=checkbox name='fever'  ><span class=text><?php xl('Fever','e'); ?></span><br>
<input type=checkbox name='chills'  ><span class=text><?php xl('Chills','e'); ?></span><br>
<input type=checkbox name='night_sweats'  ><span class=text><?php xl('Night Sweats','e'); ?></span><br>
<input type=checkbox name='weight_loss'  ><span class=text><?php xl('Weight Loss','e'); ?></span><br>
<input type=checkbox name='poor_appetite'  ><span class=text><?php xl('Poor Appetite','e'); ?></span><br>
<input type=checkbox name='insomnia'  ><span class=text><?php xl('Insomnia','e'); ?></span><br>
<input type=checkbox name='fatigued'  ><span class=text><?php xl('Fatigued','e'); ?></span><br>
<input type=checkbox name='depressed'  ><span class=text><?php xl('Depressed','e'); ?></span><br>
<input type=checkbox name='hyperactive'  ><span class=text><?php xl('Hyperactive','e'); ?></span><br>
<input type=checkbox name='exposure_to_foreign_countries'  ><span class=text><?php xl('Exposure to Foreign Countries','e'); ?></span><br>
<span class=bold><?php xl('Skin','e'); ?></span><br>
<input type=checkbox name='rashes'  ><span class=text><?php xl('Rashes','e'); ?></span><br>
<input type=checkbox name='infections'  ><span class=text><?php xl('Infections','e'); ?></span><br>
<input type=checkbox name='ulcerations'  ><span class=text><?php xl('Ulcerations','e'); ?></span><br>
<input type=checkbox name='pemphigus'  ><span class=text><?php xl('Pemphigus','e'); ?></span><br>
<input type=checkbox name='herpes'  ><span class=text><?php xl('Herpes','e'); ?></span><br>

</td><td valign=top>

<span class=bold><?php xl('HEENT','e'); ?></span><br>
<input type=checkbox name='cataracts'  ><span class=text><?php xl('Cataracts','e'); ?></span><br>
<input type=checkbox name='cataract_surgery'  ><span class=text><?php xl('Cataract Surgery','e'); ?></span><br>
<input type=checkbox name='glaucoma'  ><span class=text><?php xl('Glaucoma','e'); ?></span><br>
<input type=checkbox name='double_vision'  ><span class=text><?php xl('Double Vision','e'); ?></span><br>
<input type=checkbox name='blurred_vision'  ><span class=text><?php xl('Blurred Vision','e'); ?></span><br>
<input type=checkbox name='poor_hearing'  ><span class=text><?php xl('Poor Hearing','e'); ?></span><br>
<input type=checkbox name='headaches'  ><span class=text><?php xl('Headaches','e'); ?></span><br>
<input type=checkbox name='ringing_in_ears'  ><span class=text><?php xl('Ringing in Ears','e'); ?></span><br>
<input type=checkbox name='bloody_nose'  ><span class=text><?php xl('Bloody Nose','e'); ?></span><br>
<input type=checkbox name='sinusitis'  ><span class=text><?php xl('Sinusitis','e'); ?></span><br>
<input type=checkbox name='sinus_surgery'  ><span class=text><?php xl('Sinus Surgery','e'); ?></span><br>
<input type=checkbox name='dry_mouth'  ><span class=text><?php xl('Dry Mouth','e'); ?></span><br>
<input type=checkbox name='strep_throat'  ><span class=text><?php xl('Strep Throat','e'); ?></span><br>
<input type=checkbox name='tonsillectomy'  ><span class=text><?php xl('Tonsillectomy','e'); ?></span><br>
<input type=checkbox name='swollen_lymph_nodes'  ><span class=text><?php xl('Swollen Lymph Nodes','e'); ?></span><br>
<input type=checkbox name='throat_cancer'  ><span class=text><?php xl('Throat Cancer','e'); ?></span><br>
<input type=checkbox name='throat_cancer_surgery'  ><span class=text><?php xl('Throat Cancer Surgery','e'); ?></span><br>

</td><td valign=top>

<span class=bold><?php xl('Cardiovascular','e'); ?></span><br>
<input type=checkbox name='heart_attack'  ><span class=text><?php xl('Heart Attack','e'); ?></span><br>
<input type=checkbox name='irregular_heart_beat'  ><span class=text><?php xl('Irregular Heart Beat','e'); ?></span><br>
<input type=checkbox name='chest_pains'  ><span class=text><?php xl('Chest Pains','e'); ?></span><br>
<input type=checkbox name='shortness_of_breath'  ><span class=text><?php xl('Shortness of Breath','e'); ?></span><br>
<input type=checkbox name='high_blood_pressure'  ><span class=text><?php xl('High Blood Pressure','e'); ?></span><br>
<input type=checkbox name='heart_failure'  ><span class=text><?php xl('Heart Failure','e'); ?></span><br>
<input type=checkbox name='poor_circulation'  ><span class=text><?php xl('Poor Circulation','e'); ?></span><br>
<input type=checkbox name='vascular_surgery'  ><span class=text><?php xl('Vascular Surgery','e'); ?></span><br>
<input type=checkbox name='cardiac_catheterization'  ><span class=text><?php xl('Cardiac Catheterization','e'); ?></span><br>
<input type=checkbox name='coronary_artery_bypass'  ><span class=text><?php xl('Coronary Artery Bypass','e'); ?></span><br>
<input type=checkbox name='heart_transplant'  ><span class=text><?php xl('Heart Transplant','e'); ?></span><br>
<input type=checkbox name='stress_test'  ><span class=text><?php xl('Stress Test','e'); ?></span><br>
<span class=bold><?php xl('Endocrine','e'); ?></span><br>
<input type=checkbox name='insulin_dependent_diabetes'  ><span class=text><?php xl('Insulin Dependent Diabetes','e'); ?></span><br>
<input type=checkbox name='noninsulin_dependent_diabetes'  ><span class=text><?php xl('Non-Insulin Dependent Diabetes','e'); ?></span><br>
<input type=checkbox name='hypothyroidism'  ><span class=text><?php xl('Hypothyroidism','e'); ?></span><br>
<input type=checkbox name='hyperthyroidism'  ><span class=text><?php xl('Hyperthyroidism','e'); ?></span><br>
<input type=checkbox name='cushing_syndrom'  ><span class=text><?php xl('Cushing Syndrom','e'); ?></span><br>
<input type=checkbox name='addison_syndrom'  ><span class=text><?php xl('Addison Syndrom','e'); ?></span><br>

</td><td valign=top>

<span class=bold><?php xl('Pulmonary','e'); ?></span><br>
<input type=checkbox name='emphysema'  ><span class=text><?php xl('Emphysema','e'); ?></span><br>
<input type=checkbox name='chronic_bronchitis'  ><span class=text><?php xl('Chronic Bronchitis','e'); ?></span><br>
<input type=checkbox name='interstitial_lung_disease'  ><span class=text><?php xl('Interstitial Lung Disease','e'); ?></span><br>
<input type=checkbox name='shortness_of_breath_2'  ><span class=text><?php xl('Shortness of Breath','e'); ?></span><br>
<input type=checkbox name='lung_cancer'  ><span class=text><?php xl('Lung Cancer','e'); ?></span><br>
<input type=checkbox name='lung_cancer_surgery'  ><span class=text><?php xl('Lung Cancer Surgery','e'); ?></span><br>
<input type=checkbox name='pheumothorax'  ><span class=text><?php xl('Pheumothorax','e'); ?></span><br>
<span class=bold><?php xl('Genitourinary','e'); ?></span><br>
<input type=checkbox name='kidney_failure'  ><span class=text><?php xl('Kidney Failure','e'); ?></span><br>
<input type=checkbox name='kidney_stones'  ><span class=text><?php xl('Kidney Stones','e'); ?></span><br>
<input type=checkbox name='kidney_cancer'  ><span class=text><?php xl('Kidney Cancer','e'); ?></span><br>
<input type=checkbox name='kidney_infections'  ><span class=text><?php xl('Kidney Infections','e'); ?></span><br>
<input type=checkbox name='bladder_infections'  ><span class=text><?php xl('Bladder Infections','e'); ?></span><br>
<input type=checkbox name='bladder_cancer'  ><span class=text><?php xl('Bladder Cancer','e'); ?></span><br>
<input type=checkbox name='prostate_problems'  ><span class=text><?php xl('Prostate Problems','e'); ?></span><br>
<input type=checkbox name='prostate_cancer'  ><span class=text><?php xl('Prostate Cancer','e'); ?></span><br>
<input type=checkbox name='kidney_transplant'  ><span class=text><?php xl('Kidney Transplant','e'); ?></span><br>
<input type=checkbox name='sexually_transmitted_disease'  ><span class=text><?php xl('Sexually Transmitted Disease','e'); ?></span><br>
<input type=checkbox name='burning_with_urination'  ><span class=text><?php xl('Burning with Urination','e'); ?></span><br>
<input type=checkbox name='discharge_from_urethra'  ><span class=text><?php xl('Discharge From Urethra','e'); ?></span><br>

</td><td valign=top>

<span class=bold><?php xl('Gastrointestinal','e'); ?></span><br>
<input type=checkbox name='stomach_pains'  ><span class=text><?php xl('Stomach Pains','e'); ?></span><br>
<input type=checkbox name='peptic_ulcer_disease'  ><span class=text><?php xl('Peptic Ulcer Disease','e'); ?></span><br>
<input type=checkbox name='gastritis'  ><span class=text><?php xl('Gastritis','e'); ?></span><br>
<input type=checkbox name='endoscopy'  ><span class=text><?php xl('Endoscopy','e'); ?></span><br>
<input type=checkbox name='polyps'  ><span class=text><?php xl('Polyps','e'); ?></span><br>
<input type=checkbox name='colonoscopy'  ><span class=text><?php xl('colonoscopy','e'); ?></span><br>
<input type=checkbox name='colon_cancer'  ><span class=text><?php xl('Colon Cancer','e'); ?></span><br>
<input type=checkbox name='colon_cancer_surgery'  ><span class=text><?php xl('Colon Cancer Surgery','e'); ?></span><br>
<input type=checkbox name='ulcerative_colitis'  ><span class=text><?php xl('Ulcerative Colitis','e'); ?></span><br>
<input type=checkbox name='crohns_disease'  ><span class=text><?php xl('Crohn\'s Disease','e'); ?></span><br>
<input type=checkbox name='appendectomy'  ><span class=text><?php xl('Appendectomy','e'); ?></span><br>
<input type=checkbox name='divirticulitis'  ><span class=text><?php xl('Divirticulitis','e'); ?></span><br>
<input type=checkbox name='divirticulitis_surgery'  ><span class=text><?php xl('Divirticulitis Surgery','e'); ?></span><br>
<input type=checkbox name='gall_stones'  ><span class=text><?php xl('Gall Stones','e'); ?></span><br>
<input type=checkbox name='cholecystectomy'  ><span class=text><?php xl('Cholecystectomy','e'); ?></span><br>
<input type=checkbox name='hepatitis'  ><span class=text><?php xl('Hepatitis','e'); ?></span><br>
<input type=checkbox name='cirrhosis_of_the_liver'  ><span class=text><?php xl('Cirrhosis of the Liver','e'); ?></span><br>
<input type=checkbox name='splenectomy'  ><span class=text><?php xl('Splenectomy','e'); ?></span><br>

</td><td valign=top>

<span class=bold><?php xl('Musculoskeletal','e'); ?></span><br>
<input type=checkbox name='osetoarthritis'  ><span class=text><?php xl('Osetoarthritis','e'); ?></span><br>
<input type=checkbox name='rheumotoid_arthritis'  ><span class=text><?php xl('Rheumotoid Arthritis','e'); ?></span><br>
<input type=checkbox name='lupus'  ><span class=text><?php xl('Lupus','e'); ?></span><br>
<input type=checkbox name='ankylosing_sondlilitis'  ><span class=text><?php xl('Ankylosing Sondlilitis','e'); ?></span><br>
<input type=checkbox name='swollen_joints'  ><span class=text><?php xl('Swollen Joints','e'); ?></span><br>
<input type=checkbox name='stiff_joints'  ><span class=text><?php xl('Stiff Joints','e'); ?></span><br>
<input type=checkbox name='broken_bones'  ><span class=text><?php xl('Broken Bones','e'); ?></span><br>
<input type=checkbox name='neck_problems'  ><span class=text><?php xl('Neck Problems','e'); ?></span><br>
<input type=checkbox name='back_problems'  ><span class=text><?php xl('Back Problems','e'); ?></span><br>
<input type=checkbox name='back_surgery'  ><span class=text><?php xl('Back Surgery','e'); ?></span><br>
<input type=checkbox name='scoliosis'  ><span class=text><?php xl('Scoliosis','e'); ?></span><br>
<input type=checkbox name='herniated_disc'  ><span class=text><?php xl('Herniated Disc','e'); ?></span><br>
<input type=checkbox name='shoulder_problems'  ><span class=text><?php xl('Shoulder Problems','e'); ?></span><br>
<input type=checkbox name='elbow_problems'  ><span class=text><?php xl('Elbow Problems','e'); ?></span><br>
<input type=checkbox name='wrist_problems'  ><span class=text><?php xl('Wrist Problems','e'); ?></span><br>
<input type=checkbox name='hand_problems'  ><span class=text><?php xl('Hand Problems','e'); ?></span><br>
<input type=checkbox name='hip_problems'  ><span class=text><?php xl('Hip Problems','e'); ?></span><br>
<input type=checkbox name='knee_problems'  ><span class=text><?php xl('Knee Problems','e'); ?></span><br>
<input type=checkbox name='ankle_problems'  ><span class=text><?php xl('Ankle Problems','e'); ?></span><br>
<input type=checkbox name='foot_problems'  ><span class=text><?php xl('Foot Problems','e'); ?></span><br>
</td>
</tr>
</table>






<span class=text><?php xl('Additional Notes: ','e'); ?></span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ></textarea><br>
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php xl('Save','e');?>]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link">[<?php xl('Don\'t Save','e');?>]</a>
</form>
<?php
formFooter();
?>
