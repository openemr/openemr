<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_reviewofs", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/reviewofs/save.php?mode=update&id=<?php echo $_GET["id"];?>"
 name="my_form" onsubmit="return top.restoreSession()">
<span class="title"><?php xl('Review of Systems Checks','e'); ?></span><Br><br>

<table>
<tr>
<td valign=top>
<span class=bold><?php xl('General','e'); ?></span><br>
<input type=checkbox name="fever"  <?php if ($obj{"fever"} == "on") {echo "checked";};?>><span class=text><?php xl('Fever','e'); ?></span><br>
<input type=checkbox name="chills"  <?php if ($obj{"chills"} == "on") {echo "checked";};?>><span class=text><?php xl('Chills','e'); ?></span><br>
<input type=checkbox name="night_sweats"  <?php if ($obj{"night_sweats"} == "on") {echo "checked";};?>><span class=text><?php xl('Night Sweats','e'); ?></span><br>
<input type=checkbox name="weight_loss"  <?php if ($obj{"weight_loss"} == "on") {echo "checked";};?>><span class=text><?php xl('Weight Loss','e'); ?></span><br>
<input type=checkbox name="poor_appetite"  <?php if ($obj{"poor_appetite"} == "on") {echo "checked";};?>><span class=text><?php xl('Poor Appetite','e'); ?></span><br>
<input type=checkbox name="insomnia"  <?php if ($obj{"insomnia"} == "on") {echo "checked";};?>><span class=text><?php xl('Insomnia','e'); ?></span><br>
<input type=checkbox name="fatigued"  <?php if ($obj{"fatigued"} == "on") {echo "checked";};?>><span class=text><?php xl('Fatigued','e'); ?></span><br>
<input type=checkbox name="depressed"  <?php if ($obj{"depressed"} == "on") {echo "checked";};?>><span class=text><?php xl('Depressed','e'); ?></span><br>
<input type=checkbox name="hyperactive"  <?php if ($obj{"hyperactive"} == "on") {echo "checked";};?>><span class=text><?php xl('Hyperactive','e'); ?></span><br>
<input type=checkbox name="exposure_to_foreign_countries"  <?php if ($obj{"exposure_to_foreign_countries"} == "on") {echo "checked";};?>><span class=text><?php xl('Exposure to Foreign Countries','e'); ?></span><br>
<span class=bold><?php xl('Skin','e'); ?></span><br>
<input type=checkbox name="rashes"  <?php if ($obj{"rashes"} == "on") {echo "checked";};?>><span class=text><?php xl('Rashes','e'); ?></span><br>
<input type=checkbox name="infections"  <?php if ($obj{"infections"} == "on") {echo "checked";};?>><span class=text><?php xl('Infections','e'); ?></span><br>
<input type=checkbox name="ulcerations"  <?php if ($obj{"ulcerations"} == "on") {echo "checked";};?>><span class=text><?php xl('Ulcerations','e'); ?></span><br>
<input type=checkbox name="pemphigus"  <?php if ($obj{"pemphigus"} == "on") {echo "checked";};?>><span class=text><?php xl('Pemphigus','e'); ?></span><br>
<input type=checkbox name="herpes"  <?php if ($obj{"herpes"} == "on") {echo "checked";};?>><span class=text><?php xl('Herpes','e'); ?></span><br>
</td>
<td valign=top>
<span class=bold><?php xl('HEENT','e'); ?></span><br>
<input type=checkbox name="cataracts"  <?php if ($obj{"cataracts"} == "on") {echo "checked";};?>><span class=text><?php xl('Cataracts','e'); ?></span><br>
<input type=checkbox name="cataract_surgery"  <?php if ($obj{"cataract_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Cataract Surgery','e'); ?></span><br>
<input type=checkbox name="glaucoma"  <?php if ($obj{"glaucoma"} == "on") {echo "checked";};?>><span class=text><?php xl('Glaucoma','e'); ?></span><br>
<input type=checkbox name="double_vision"  <?php if ($obj{"double_vision"} == "on") {echo "checked";};?>><span class=text><?php xl('Double Vision','e'); ?></span><br>
<input type=checkbox name="blurred_vision"  <?php if ($obj{"blurred_vision"} == "on") {echo "checked";};?>><span class=text><?php xl('Blurred Vision','e'); ?></span><br>
<input type=checkbox name="poor_hearing"  <?php if ($obj{"poor_hearing"} == "on") {echo "checked";};?>><span class=text><?php xl('Poor Hearing','e'); ?></span><br>
<input type=checkbox name="headaches"  <?php if ($obj{"headaches"} == "on") {echo "checked";};?>><span class=text><?php xl('Headaches','e'); ?></span><br>
<input type=checkbox name="ringing_in_ears"  <?php if ($obj{"ringing_in_ears"} == "on") {echo "checked";};?>><span class=text><?php xl('Ringing in Ears','e'); ?></span><br>
<input type=checkbox name="bloody_nose"  <?php if ($obj{"bloody_nose"} == "on") {echo "checked";};?>><span class=text><?php xl('Bloody Nose','e'); ?></span><br>
<input type=checkbox name="sinusitis"  <?php if ($obj{"sinusitis"} == "on") {echo "checked";};?>><span class=text><?php xl('Sinusitis','e'); ?></span><br>
<input type=checkbox name="sinus_surgery"  <?php if ($obj{"sinus_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Sinus Surgery','e'); ?></span><br>
<input type=checkbox name="dry_mouth"  <?php if ($obj{"dry_mouth"} == "on") {echo "checked";};?>><span class=text><?php xl('Dry Mouth','e'); ?></span><br>
<input type=checkbox name="strep_throat"  <?php if ($obj{"strep_throat"} == "on") {echo "checked";};?>><span class=text><?php xl('Strep Throat','e'); ?></span><br>
<input type=checkbox name="tonsillectomy"  <?php if ($obj{"tonsillectomy"} == "on") {echo "checked";};?>><span class=text><?php xl('Tonsillectomy','e'); ?></span><br>
<input type=checkbox name="swollen_lymph_nodes"  <?php if ($obj{"swollen_lymph_nodes"} == "on") {echo "checked";};?>><span class=text><?php xl('Swollen Lymph Nodes','e'); ?></span><br>
<input type=checkbox name="throat_cancer"  <?php if ($obj{"throat_cancer"} == "on") {echo "checked";};?>><span class=text><?php xl('Throat Cancer','e'); ?></span><br>
<input type=checkbox name="throat_cancer_surgery"  <?php if ($obj{"throat_cancer_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Throat Cancer Surgery','e'); ?></span><br>
</td>
<td valign=top>
<span class=bold><?php xl('Cardiovascular','e'); ?></span><br>
<input type=checkbox name="heart_attack"  <?php if ($obj{"heart_attack"} == "on") {echo "checked";};?>><span class=text><?php xl('Heart Attack','e'); ?></span><br>
<input type=checkbox name="irregular_heart_beat"  <?php if ($obj{"irregular_heart_beat"} == "on") {echo "checked";};?>><span class=text><?php xl('Irregular Heart Beat','e'); ?></span><br>
<input type=checkbox name="chest_pains"  <?php if ($obj{"chest_pains"} == "on") {echo "checked";};?>><span class=text><?php xl('Chest Pains','e'); ?></span><br>
<input type=checkbox name="shortness_of_breath"  <?php if ($obj{"shortness_of_breath"} == "on") {echo "checked";};?>><span class=text><?php xl('Shortness of Breath','e'); ?></span><br>
<input type=checkbox name="high_blood_pressure"  <?php if ($obj{"high_blood_pressure"} == "on") {echo "checked";};?>><span class=text><?php xl('High Blood Pressure','e'); ?></span><br>
<input type=checkbox name="heart_failure"  <?php if ($obj{"heart_failure"} == "on") {echo "checked";};?>><span class=text><?php xl('Heart Failure','e'); ?></span><br>
<input type=checkbox name="poor_circulation"  <?php if ($obj{"poor_circulation"} == "on") {echo "checked";};?>><span class=text><?php xl('Poor Circulation','e'); ?></span><br>
<input type=checkbox name="vascular_surgery"  <?php if ($obj{"vascular_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Vascular Surgery','e'); ?></span><br>
<input type=checkbox name="cardiac_catheterization"  <?php if ($obj{"cardiac_catheterization"} == "on") {echo "checked";};?>><span class=text><?php xl('Cardiac Catheterization','e'); ?></span><br>
<input type=checkbox name="coronary_artery_bypass"  <?php if ($obj{"coronary_artery_bypass"} == "on") {echo "checked";};?>><span class=text><?php xl('Coronary Artery Bypass','e'); ?></span><br>
<input type=checkbox name="heart_transplant"  <?php if ($obj{"heart_transplant"} == "on") {echo "checked";};?>><span class=text><?php xl('Heart Transplant','e'); ?></span><br>
<input type=checkbox name="stress_test"  <?php if ($obj{"stress_test"} == "on") {echo "checked";};?>><span class=text><?php xl('Stress Test','e'); ?></span><br>
<span class=bold><?php xl('Endocrine','e'); ?></span><br>
<input type=checkbox name="insulin_dependent_diabetes"  <?php if ($obj{"insulin_dependent_diabetes"} == "on") {echo "checked";};?>><span class=text><?php xl('Insulin Dependent Diabetes','e'); ?></span><br>
<input type=checkbox name="noninsulin_dependent_diabetes"  <?php if ($obj{"noninsulin_dependent_diabetes"} == "on") {echo "checked";};?>><span class=text><?php xl('Non-Insulin Dependent Diabetes','e'); ?></span><br>
<input type=checkbox name="hypothyroidism"  <?php if ($obj{"hypothyroidism"} == "on") {echo "checked";};?>><span class=text><?php xl('Hypothyroidism','e'); ?></span><br>
<input type=checkbox name="hyperthyroidism"  <?php if ($obj{"hyperthyroidism"} == "on") {echo "checked";};?>><span class=text><?php xl('Hyperthyroidism','e'); ?></span><br>
<input type=checkbox name="cushing_syndrom"  <?php if ($obj{"cushing_syndrom"} == "on") {echo "checked";};?>><span class=text><?php xl('Cushing Syndrom','e'); ?></span><br>
<input type=checkbox name="addison_syndrom"  <?php if ($obj{"addison_syndrom"} == "on") {echo "checked";};?>><span class=text><?php xl('Addison Syndrom','e'); ?></span><br>
</td>
<td valign=top>
<span class=bold><?php xl('Pulmonary','e'); ?></span><br>
<input type=checkbox name="emphysema"  <?php if ($obj{"emphysema"} == "on") {echo "checked";};?>><span class=text><?php xl('Emphysema','e'); ?></span><br>
<input type=checkbox name="chronic_bronchitis"  <?php if ($obj{"chronic_bronchitis"} == "on") {echo "checked";};?>><span class=text><?php xl('Chronic Bronchitis','e'); ?></span><br>
<input type=checkbox name="interstitial_lung_disease"  <?php if ($obj{"interstitial_lung_disease"} == "on") {echo "checked";};?>><span class=text><?php xl('Interstitial Lung Disease','e'); ?></span><br>
<input type=checkbox name="shortness_of_breath_2"  <?php if ($obj{"shortness_of_breath_2"} == "on") {echo "checked";};?>><span class=text><?php xl('Shortness of Breath','e'); ?></span><br>
<input type=checkbox name="lung_cancer"  <?php if ($obj{"lung_cancer"} == "on") {echo "checked";};?>><span class=text><?php xl('Lung Cancer','e'); ?></span><br>
<input type=checkbox name="lung_cancer_surgery"  <?php if ($obj{"lung_cancer_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Lung Cancer Surgery','e'); ?></span><br>
<input type=checkbox name="pheumothorax"  <?php if ($obj{"pheumothorax"} == "on") {echo "checked";};?>><span class=text><?php xl('Pheumothorax','e'); ?></span><br>
<span class=bold><?php xl('Genitourinary','e'); ?></span><br>
<input type=checkbox name="kidney_failure"  <?php if ($obj{"kidney_failure"} == "on") {echo "checked";};?>><span class=text><?php xl('Kidney Failure','e'); ?></span><br>
<input type=checkbox name="kidney_stones"  <?php if ($obj{"kidney_stones"} == "on") {echo "checked";};?>><span class=text><?php xl('Kidney Stones','e'); ?></span><br>
<input type=checkbox name="kidney_cancer"  <?php if ($obj{"kidney_cancer"} == "on") {echo "checked";};?>><span class=text><?php xl('Kidney Cancer','e'); ?></span><br>
<input type=checkbox name="kidney_infections"  <?php if ($obj{"kidney_infections"} == "on") {echo "checked";};?>><span class=text><?php xl('Kidney Infections','e'); ?></span><br>
<input type=checkbox name="bladder_infections"  <?php if ($obj{"bladder_infections"} == "on") {echo "checked";};?>><span class=text><?php xl('Bladder Infections','e'); ?></span><br>
<input type=checkbox name="bladder_cancer"  <?php if ($obj{"bladder_cancer"} == "on") {echo "checked";};?>><span class=text><?php xl('Bladder Cancer','e'); ?></span><br>
<input type=checkbox name="prostate_problems"  <?php if ($obj{"prostate_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Prostate Problems','e'); ?></span><br>
<input type=checkbox name="prostate_cancer"  <?php if ($obj{"prostate_cancer"} == "on") {echo "checked";};?>><span class=text><?php xl('Prostate Cancer','e'); ?></span><br>
<input type=checkbox name="kidney_transplant"  <?php if ($obj{"kidney_transplant"} == "on") {echo "checked";};?>><span class=text><?php xl('Kidney Transplant','e'); ?></span><br>
<input type=checkbox name="sexually_transmitted_disease"  <?php if ($obj{"sexually_transmitted_disease"} == "on") {echo "checked";};?>><span class=text><?php xl('Sexually Transmitted Disease','e'); ?></span><br>
<input type=checkbox name="burning_with_urination"  <?php if ($obj{"burning_with_urination"} == "on") {echo "checked";};?>><span class=text><?php xl('Burning with Urination','e'); ?></span><br>
<input type=checkbox name="discharge_from_urethra"  <?php if ($obj{"discharge_from_urethra"} == "on") {echo "checked";};?>><span class=text><?php xl('Discharge From Urethra','e'); ?></span><br>
</td>
<td valign=top>
<span class=bold><?php xl('Gastrointestinal','e'); ?></span><br>
<input type=checkbox name="stomach_pains"  <?php if ($obj{"stomach_pains"} == "on") {echo "checked";};?>><span class=text><?php xl('Stomach Pains','e'); ?></span><br>
<input type=checkbox name="peptic_ulcer_disease"  <?php if ($obj{"peptic_ulcer_disease"} == "on") {echo "checked";};?>><span class=text><?php xl('Peptic Ulcer Disease','e'); ?></span><br>
<input type=checkbox name="gastritis"  <?php if ($obj{"gastritis"} == "on") {echo "checked";};?>><span class=text><?php xl('Gastritis','e'); ?></span><br>
<input type=checkbox name="endoscopy"  <?php if ($obj{"endoscopy"} == "on") {echo "checked";};?>><span class=text><?php xl('Endoscopy','e'); ?></span><br>
<input type=checkbox name="polyps"  <?php if ($obj{"polyps"} == "on") {echo "checked";};?>><span class=text><?php xl('Polyps','e'); ?></span><br>
<input type=checkbox name="colonoscopy"  <?php if ($obj{"colonoscopy"} == "on") {echo "checked";};?>><span class=text><?php xl('colonoscopy','e'); ?></span><br>
<input type=checkbox name="colon_cancer"  <?php if ($obj{"colon_cancer"} == "on") {echo "checked";};?>><span class=text><?php xl('Colon Cancer','e'); ?></span><br>
<input type=checkbox name="colon_cancer_surgery"  <?php if ($obj{"colon_cancer_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Colon Cancer Surgery','e'); ?></span><br>
<input type=checkbox name="ulcerative_colitis"  <?php if ($obj{"ulcerative_colitis"} == "on") {echo "checked";};?>><span class=text><?php xl('Ulcerative Colitis','e'); ?></span><br>
<input type=checkbox name="crohns_disease"  <?php if ($obj{"crohns_disease"} == "on") {echo "checked";};?>><span class=text><?php xl('Crohn\'s Disease','e'); ?></span><br>
<input type=checkbox name="appendectomy"  <?php if ($obj{"appendectomy"} == "on") {echo "checked";};?>><span class=text><?php xl('Appendectomy','e'); ?></span><br>
<input type=checkbox name="divirticulitis"  <?php if ($obj{"divirticulitis"} == "on") {echo "checked";};?>><span class=text><?php xl('Divirticulitis','e'); ?></span><br>
<input type=checkbox name="divirticulitis_surgery"  <?php if ($obj{"divirticulitis_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Divirticulitis Surgery','e'); ?></span><br>
<input type=checkbox name="gall_stones"  <?php if ($obj{"gall_stones"} == "on") {echo "checked";};?>><span class=text><?php xl('Gall Stones','e'); ?></span><br>
<input type=checkbox name="cholecystectomy"  <?php if ($obj{"cholecystectomy"} == "on") {echo "checked";};?>><span class=text><?php xl('Cholecystectomy','e'); ?></span><br>
<input type=checkbox name="hepatitis"  <?php if ($obj{"hepatitis"} == "on") {echo "checked";};?>><span class=text><?php xl('Hepatitis','e'); ?></span><br>
<input type=checkbox name="cirrhosis_of_the_liver"  <?php if ($obj{"cirrhosis_of_the_liver"} == "on") {echo "checked";};?>><span class=text><?php xl('Cirrhosis of the Liver','e'); ?></span><br>
<input type=checkbox name="splenectomy"  <?php if ($obj{"splenectomy"} == "on") {echo "checked";};?>><span class=text><?php xl('Splenectomy','e'); ?></span><br>
</td>
<td valign=top>
<span class=bold><?php xl('Musculoskeletal','e'); ?></span><br>
<input type=checkbox name="osetoarthritis"  <?php if ($obj{"osetoarthritis"} == "on") {echo "checked";};?>><span class=text><?php xl('Osetoarthritis','e'); ?></span><br>
<input type=checkbox name="rheumotoid_arthritis"  <?php if ($obj{"rheumotoid_arthritis"} == "on") {echo "checked";};?>><span class=text><?php xl('Rheumotoid Arthritis','e'); ?></span><br>
<input type=checkbox name="lupus"  <?php if ($obj{"lupus"} == "on") {echo "checked";};?>><span class=text><?php xl('Lupus','e'); ?></span><br>
<input type=checkbox name="ankylosing_sondlilitis"  <?php if ($obj{"ankylosing_sondlilitis"} == "on") {echo "checked";};?>><span class=text><?php xl('Ankylosing Sondlilitis','e'); ?></span><br>
<input type=checkbox name="swollen_joints"  <?php if ($obj{"swollen_joints"} == "on") {echo "checked";};?>><span class=text><?php xl('Swollen Joints','e'); ?></span><br>
<input type=checkbox name="stiff_joints"  <?php if ($obj{"stiff_joints"} == "on") {echo "checked";};?>><span class=text><?php xl('Stiff Joints','e'); ?></span><br>
<input type=checkbox name="broken_bones"  <?php if ($obj{"broken_bones"} == "on") {echo "checked";};?>><span class=text><?php xl('Broken Bones','e'); ?></span><br>
<input type=checkbox name="neck_problems"  <?php if ($obj{"neck_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Neck Problems','e'); ?></span><br>
<input type=checkbox name="back_problems"  <?php if ($obj{"back_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Back Problems','e'); ?></span><br>
<input type=checkbox name="back_surgery"  <?php if ($obj{"back_surgery"} == "on") {echo "checked";};?>><span class=text><?php xl('Back Surgery','e'); ?></span><br>
<input type=checkbox name="scoliosis"  <?php if ($obj{"scoliosis"} == "on") {echo "checked";};?>><span class=text><?php xl('Scoliosis','e'); ?></span><br>
<input type=checkbox name="herniated_disc"  <?php if ($obj{"herniated_disc"} == "on") {echo "checked";};?>><span class=text><?php xl('Herniated Disc','e'); ?></span><br>
<input type=checkbox name="shoulder_problems"  <?php if ($obj{"shoulder_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Shoulder Problems','e'); ?></span><br>
<input type=checkbox name="elbow_problems"  <?php if ($obj{"elbow_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Elbow Problems','e'); ?></span><br>
<input type=checkbox name="wrist_problems"  <?php if ($obj{"wrist_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Wrist Problems','e'); ?></span><br>
<input type=checkbox name="hand_problems"  <?php if ($obj{"hand_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Hand Problems','e'); ?></span><br>
<input type=checkbox name="hip_problems"  <?php if ($obj{"hip_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Hip Problems','e'); ?></span><br>
<input type=checkbox name="knee_problems"  <?php if ($obj{"knee_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Knee Problems','e'); ?></span><br>
<input type=checkbox name="ankle_problems"  <?php if ($obj{"ankle_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Ankle Problems','e'); ?></span><br>
<input type=checkbox name="foot_problems"  <?php if ($obj{"foot_problems"} == "on") {echo "checked";};?>><span class=text><?php xl('Foot Problems','e'); ?></span><br>
</td>
</tr>
</table>

<span class=text><?php xl('Additional Notes: ','e'); ?></span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?php echo $obj{"additional_notes"};?></textarea><br>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
</form>
<?php
formFooter();
?>
