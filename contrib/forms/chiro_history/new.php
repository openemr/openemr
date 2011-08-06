<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
# $patient = formNanak("patient_data", $_GET["id"]);  
//#Use the formnanak function from api.inc to get values for manjit
formHeader("Form: chiro_history");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'> var mypcc = '1'; </script>

<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <? xl("[do not save]",'e') ?> </a>
<form method=post action="<?echo $rootdir;?>/forms/chiro_history/save.php?mode=new" name="chiro_history" onsubmit="return top.restoreSession()">
<hr>
<h1> <? xl("Chiropractic History",'e') ?> </h1>
<hr>
<input type="submit" name="submit form" value="submit form" /><br>
<br>
<h3> <? xl("History",'e') ?> </h3>
<hr />

<table id="HIS" style="width:80%;margin-right:auto;margin-left:20px;"><tbody class="data" style="padding:2px 0px 0px 2px;margin:0px;line-height:10px;">
<tr>

<td><table><tbody class="data">
<tr>
<td><? xl("Past","e") ?><br /></td><td colspan=2><? xl("Present","e") ?><br /><? xl("General","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="general_condit['allergy'][0]" value = "allergy" /></td>
<td><input type="checkbox" name="general_condit['allergy'][1]" value = "allergy" /></td>
<td><? xl("Allergy", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="general_condit['convulsion'][0]" value = "convulsion" /></td>
<td><input type="checkbox" name="general_condit['convulsion'][1]" value = "convulsion" /></td>
<td><? xl("Convulsion", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="general_condit['dizziness'][0]" value = "dizziness" /></td>
<td><input type="checkbox" name="general_condit['dizziness'][1]" value = "dizziness" /></td>
<td><? xl("Dizziness", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="general_condit['fainting'][0]" value = "fainting" /></td>
<td><input type="checkbox" name="general_condit['fainting'][1]" value = "fainting" /></td>
<td><? xl("Fainting", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="general_condit['fatigue'][0]" value = "fatigue" /></td>
<td><input type="checkbox" name="general_condit['fatigue'][1]" value = "fatigue" /></td>
<td><? xl("Fatigue", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="general_condit['depression'][0]" value = "depression" /></td>
<td><input type="checkbox" name="general_condit['depression'][1]" value = "depression" /></td>
<td><? xl("Depression", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="general_condit['headache'][0]" value = "headache" /></td>
<td><input type="checkbox" name="general_condit['headache'][1]" value = "headache" /></td>
<td><? xl("Headache", "e") ?> </td></tr>
</tbody></table></td>

<td><table><tbody class="data">
<tr>
<td><? xl("Past","e") ?><br /></td><td colspan=2><? xl("Present","e") ?><br /><? xl("Gastro-Intestinal","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="gastrointest['constipation'][0]" value = "constipation" /></td>
<td><input type="checkbox" name="gastrointest['constipation'][1]" value = "constipation" /></td>
<td><? xl("Constipation", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="gastrointest['diarrhea'][0]" value = "diarrhea" /></td>
<td><input type="checkbox" name="gastrointest['diarrhea'][1]" value = "diarrhea" /></td>
<td><? xl("Diarrhea", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['digestion_trouble'][0]" value = "digestion_trouble" /></td>
<td><input type="checkbox" name="gastrointest['digestion_trouble'][1]" value = "digestion_trouble" /></td>
<td><? xl("Difficult/Painful Digestion", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['post_meal_short_breath'][0]" value = "post_meal_short_breath" /></td>
<td><input type="checkbox" name="gastrointest['post_meal_short_breath'][1]" value = "post_meal_short_breath" /></td>
<td><? xl("Shortness of Breathing after Meals", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['ulcers'][0]" value = "ulcers" /></td>
<td><input type="checkbox" name="gastrointest['ulcers'][1]" value = "ulcers" /></td>
<td><? xl("Ulcers", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['intestinal_gas'][0]" value = "intestinal_gas" /></td>
<td><input type="checkbox" name="gastrointest['intestinal_gas'][1]" value = "intestinal_gas" /></td>
<td><? xl("Intestinal Gas", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['hemorrhoids'][0]" value = "hemorrhoids" /></td>
<td><input type="checkbox" name="gastrointest['hemorrhoids'][1]" value = "hemorrhoids" /></td>
<td><? xl("Hemorrhoids", "e") ?> </td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['heartburn'][0]" value = "heartburn" /></td>
<td><input type="checkbox" name="gastrointest['heartburn'][1]" value = "heartburn" /></td>
<td><? xl("Heartburn", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="gastrointest['histal_hernia'][0]" value = "histal_hernia" /></td>
<td><input type="checkbox" name="gastrointest['histal_hernia'][1]" value = "histal_hernia" /></td>
<td><? xl("Histal Hernia", "e") ?> </td></tr>
</tbody></table></td>

<td><table><tbody class="data">
<tr>
<td><? xl("Past","e") ?><br /></td><td colspan=2><? xl("Present","e") ?><br /><? xl("Eyes,Ears,Nose&ampThroat","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="head_neck['eye_pain'][0]" value = "eye_pain" /></td>
<td><input type="checkbox" name="head_neck['eye_pain'][1]" value = "eye_pain" /></td>
<td><? xl("Eye Pain", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="head_neck['nearorfar_sighted'][0]" value = "nearorfar_sighted" /></td>
<td><input type="checkbox" name="head_neck['nearorfar_sighted'][1]" value = "nearorfar_sighted" /></td>
<td><? xl("Near or Far Sighted", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="head_neck['light_sensitivity'][0]" value = "light_sensitivity" /></td>
<td><input type="checkbox" name="head_neck['light_sensitivity'][1]" value = "light_sensitivity" /></td>
<td><? xl("Light Bothers Eyes", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="head_neck['deafness'][0]" value = "deafness" /></td>
<td><input type="checkbox" name="head_neck['deafness'][1]" value = "deafness" /></td>
<td><? xl("Deafness", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="head_neck['earaches'][0]" value = "earaches" /></td>
<td><input type="checkbox" name="head_neck['earaches'][1]" value = "earaches" /></td>
<td><? xl("Earaches", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="head_neck['ear_noises'][0]" value = "ear_noises" /></td>
<td><input type="checkbox" name="head_neck['ear_noises'][1]" value = "ear_noises" /></td>
<td><? xl("Ear Noises", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="head_neck['sinus'][0]" value = "sinus" /></td>
<td><input type="checkbox" name="head_neck['sinus'][1]" value = "sinus" /></td>
<td><? xl("Sinus", "e") ?> </td></tr>
</tbody></table></td>

</tr>
<tr>

<td><table><tbody class="data">
<tr>
<td /><td colspan=2><? xl("Muscle &amp Joints","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="muscle_joint['jaw_problems'][0]" value = "jaw_problems" /></td>
<td><input type="checkbox" name="muscle_joint['jaw_problems'][1]" value = "jaw_problems" /></td>
<td><? xl("Jaw Problems", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="muscle_joint['grindclench_teeth'][0]" value = "grindclench_teeth" /></td>
<td><input type="checkbox" name="muscle_joint['grindclench_teeth'][1]" value = "grindclench_teeth" /></td>
<td><? xl("Grinding or Clenching Teeth", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="muscle_joint['lowback_pain'][0]" value = "lowback_pain" /></td>
<td><input type="checkbox" name="muscle_joint['lowback_pain'][1]" value = "lowback_pain" /></td>
<td><? xl("Lower Back Pain", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="muscle_joint['betweenshoulder_pain'][0]" value = "betweenshoulder_pain" /></td>
<td><input type="checkbox" name="muscle_joint['betweenshoulder_pain'][1]" value = "betweenshoulder_pain" /></td>
<td><? xl("Pain Between Shoulders", "e") ?></td></tr>
</tbody></table></td>

<td><table><tbody class="data">
<tr>
<td /><td colspan=2><? xl("Genito-Urinary","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="genitouro['bed_wetting'][0]" value = "bed_wetting" /></td>
<td><input type="checkbox" name="genitouro['bed_wetting'][1]" value = "bed_wetting" /></td>
<td><? xl("Bed Wetting", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="genitouro['blood_urine'][0]" value = "blood_urine" /></td>
<td><input type="checkbox" name="genitouro['blood_urine'][1]" value = "blood_urine" /></td>
<td><? xl("Blood in Urine", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="genitouro['frequent_urine'][0]" value = "frequent_urine" /></td>
<td><input type="checkbox" name="genitouro['frequent_urine'][1]" value = "frequent_urine" /></td>
<td><? xl("Frequent Urination", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="genitouro['no_kidney_control'][0]" value = "no_kidney_control" /></td>
<td><input type="checkbox" name="genitouro['no_kidney_control'][1]" value = "no_kidney_control" /></td>
<td><? xl("Inability to Control Kidneys", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="genitouro['kidney_infectionorstones'][0]" value = "kidney_infectionorstones" /></td>
<td><input type="checkbox" name="genitouro['kidney_infectionorstones'][1]" value = "kidney_infectionorstones" /></td>
<td><? xl("Kidney Infection or Stones", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="genitouro['painful_urination'][0]" value = "painful_urination" /></td>
<td><input type="checkbox" name="genitouro['painful_urination'][1]" value = "painful_urination" /></td>
<td><? xl("Painful Urination", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="genitouro['prostate_trouble'][0]" value = "prostate_trouble" /></td>
<td><input type="checkbox" name="genitouro['prostate_trouble'][1]" value = "prostate_trouble" /></td>
<td><? xl("Prostate Trouble", "e") ?> </td></tr>
<tr>
<td><input type="checkbox" name="genitouro['urine_pus'][0]" value = "urine_pus" /></td>
<td><input type="checkbox" name="genitouro['urine_pus'][1]" value = "urine_pus" /></td>
<td><? xl("Pus in Urine", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="genitouro['bladder_infection'][0]" value = "bladder_infection" /></td>
<td><input type="checkbox" name="genitouro['bladder_infection'][1]" value = "bladder_infection" /></td>
<td><? xl("Bladder Infection", "e") ?> </td></tr>
</tbody></table></td>

<td><table><tbody class="data">
<tr>
<td /><td colspan=2><? xl("Cardiovascular","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="cardiovascular['high_bpressure'][0]" value = "high_bpressure" /></td>
<td><input type="checkbox" name="cardiovascular['high_bpressure'][1]" value = "high_bpressure" /></td>
<td><? xl("High Blood Pressure", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="cardiovascular['low_bpressure'][0]" value = "low_bpressure" /></td>
<td><input type="checkbox" name="cardiovascular['low_bpressure'][1]" value = "low_bpressure" /></td>
<td><? xl("Low Blood Pressure", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="cardiovascular['poor_circulation'][0]" value = "poor_circulation" /></td>
<td><input type="checkbox" name="cardiovascular['poor_circulation'][1]" value = "poor_circulation" /></td>
<td><? xl("Poor Circulation", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="cardiovascular['rapid_heartbeat'][0]" value = "rapid_heartbeat" /></td>
<td><input type="checkbox" name="cardiovascular['rapid_heartbeat'][1]" value = "rapid_heartbeat" /></td>
<td><? xl("Rapid Heartbeat", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="cardiovascular['slow_heartbeat'][0]" value = "slow_heartbeat" /></td>
<td><input type="checkbox" name="cardiovascular['slow_heartbeat'][1]" value = "slow_heartbeat" /></td>
<td><? xl("Slow Heartbeat", "e") ?></td></tr>
</tbody></table></td>

</tr>
<tr>

<td><table><tbody class="data">
<tr>
<td /><td colspan=2><? xl("Pain or Numbness In:","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['neck'][0]" value = "neck" /></td>
<td><input type="checkbox" name="pain_numb_locat['neck'][1]" value = "neck" /></td>
<td><? xl("Neck", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['shoulder'][0]" value = "shoulder" /></td>
<td><input type="checkbox" name="pain_numb_locat['shoulder'][1]" value = "shoulder" /></td>
<td><? xl("Shoulder", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['arm'][0]" value = "arm" /></td>
<td><input type="checkbox" name="pain_numb_locat['arm'][1]" value = "arm" /></td>
<td><? xl("Arm", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['elbow'][0]" value = "elbow" /></td>
<td><input type="checkbox" name="pain_numb_locat['elbow'][1]" value = "elbow" /></td>
<td><? xl("Elbow", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['hands'][0]" value = "hands" /></td>
<td><input type="checkbox" name="pain_numb_locat['hands'][1]" value = "hands" /></td>
<td><? xl("Hands", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['hip'][0]" value = "hip" /></td>
<td><input type="checkbox" name="pain_numb_locat['hip'][1]" value = "hip" /></td>
<td><? xl("Hip", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['legs'][0]" value = "legs" /></td>
<td><input type="checkbox" name="pain_numb_locat['legs'][1]" value = "legs" /></td>
<td><? xl("Legs", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['knees'][0]" value = "knees" /></td>
<td><input type="checkbox" name="pain_numb_locat['knees'][1]" value = "knees" /></td>
<td><? xl("Knees", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="pain_numb_locat['feet'][0]" value = "feet" /></td>
<td><input type="checkbox" name="pain_numb_locat['feet'][1]" value = "feet" /></td>
<td><? xl("Feet", "e") ?></td></tr>
</tbody></table></td>

<td><table><tbody class="data">
<tr>
<td /><td colspan=2><? xl("Respiratory","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="respiratory['chronic_cough'][0]" value = "chronic_cough" /></td>
<td><input type="checkbox" name="respiratory['chronic_cough'][1]" value = "chronic_cough" /></td>
<td><? xl("Chronic Cough", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="respiratory['chest_pain'][0]" value = "chest_pain" /></td>
<td><input type="checkbox" name="respiratory['chest_pain'][1]" value = "chest_pain" /></td>
<td><? xl("Chest Pain", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="respiratory['difficulty_breathing'][0]" value = "difficulty_breathing" /></td>
<td><input type="checkbox" name="respiratory['difficulty_breathing'][1]" value = "difficulty_breathing" /></td>
<td><? xl("Difficulty Breathing", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="respiratory['blood_spitting'][0]" value = "blood_spitting" /></td>
<td><input type="checkbox" name="respiratory['blood_spitting'][1]" value = "blood_spitting" /></td>
<td><? xl("Spitting up Blood", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="respiratory['wheezing'][0]" value = "wheezing" /></td>
<td><input type="checkbox" name="respiratory['wheezing'][1]" value = "wheezing" /></td>
<td><? xl("Wheezing", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="respiratory['asthma'][0]" value = "asthma" /></td>
<td><input type="checkbox" name="respiratory['asthma'][1]" value = "asthma" /></td>
<td><? xl("Asthma", "e") ?></td></tr>
</tbody></table></td>

<td><table><tbody class="data">
<tr>
<td /><td colspan=2><? xl("Women Only","e") ?></td>
</tr>
<tr>
<td><input type="checkbox" name="women_health['congested_breasts'][0]" value = "congested_breasts" /></td>
<td><input type="checkbox" name="women_health['congested_breasts'][1]" value = "congested_breasts" /></td>
<td><? xl("Congested Breasts", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="women_health['cramps_backache'][0]" value = "cramps_backache" /></td>
<td><input type="checkbox" name="women_health['cramps_backache'][1]" value = "cramps_backache" /></td>
<td><? xl("Cramps or Backache", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['excessive_menses'][0]" value = "excessive_menses" /></td>
<td><input type="checkbox" name="women_health['excessive_menses'][1]" value = "excessive_menses" /></td>
<td><? xl("Excessive Menstruation", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['menopausal_signs'][0]" value = "menopausal_signs" /></td>
<td><input type="checkbox" name="women_health['menopausal_signs'][1]" value = "menopausal_signs" /></td>
<td><? xl("Menopausal Symptoms", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['irregular_cycle'][0]" value = "irregular_cycle" /></td>
<td><input type="checkbox" name="women_health['irregular_cycle'][1]" value = "irregular_cycle" /></td>
<td><? xl("Irregular Cycle", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['lumps'][0]" value = "lumps" /></td>
<td><input type="checkbox" name="women_health['lumps'][1]" value = "lumps" /></td>
<td><? xl("Lumps in Breasts", "e") ?></td> </tr>
<tr>
<td><input type="checkbox" name="women_health['painful_menses'][0]" value = "painful_menses" /></td>
<td><input type="checkbox" name="women_health['painful_menses'][1]" value = "painful_menses" /></td>
<td><? xl("Painful Menstruation", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['vaginal_discharge'][0]" value = "vaginal_discharge" /></td>
<td><input type="checkbox" name="women_health['vaginal_discharge'][1]" value = "vaginal_discharge" /></td>
<td><? xl("Vaginal Discharge", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['endometriosis'][0]" value = "endometriosis" /></td>
<td><input type="checkbox" name="women_health['endometriosis'][1]" value = "endometriosis" /></td>
<td><? xl("Endometriosis", "e") ?></td></tr>
<tr>
<td><input type="checkbox" name="women_health['premenstrual_syndrome'][0]" value = "premenstrual_syndrome" /></td>
<td><input type="checkbox" name="women_health['premenstrual_syndrome'][1]" value = "premenstrual_syndrome" /></td>
<td><? xl("Premenstrual Syndrome", "e") ?></td></tr>
</tbody></table></td>

</tr>
<tr>
<td colspan=3><hr /><table style="width:100%;"><tbody class="data"><tr>
<td><input type="checkbox" name="alcoholism" value = "alcoholism" /> <? xl("Alcoholism", "e") ?><br />
<input type="checkbox" name="anemia" value = "anemia" /> <? xl("Anemia", "e") ?><br />
<input type="checkbox" name="arthritis" value = "arthritis" /> <? xl("Arthritis", "e") ?><br />
<input type="checkbox" name="cancer" value = "cancer" /> <? xl("Cancer", "e") ?><br />
<input type="checkbox" name="herpes" value = "herpes" /> <? xl("Herpes", "e") ?><br />
<input type="checkbox" name="hypoglycemia" value = "hypoglycemia" /> <? xl("Hypoglycemia", "e") ?><br />
</td>
<td><input type="checkbox" name="diabetes" value = "diabetes" /> <? xl("Diabetes", "e") ?><br />
<input type="checkbox" name="eczema" value = "eczema" /> <? xl("Eczema", "e") ?><br />
<input type="checkbox" name="emphysema" value = "emphysema" /> <? xl("Emphysema", "e") ?><br />
<input type="checkbox" name="goiter" value = "goiter" /> <? xl("Goiter", "e") ?><br />
<input type="checkbox" name="ulcers" value = "ulcers" /> <? xl("Ulcers", "e") ?><br />
</td>
<td><input type="checkbox" name="gout" value = "gout" /> <? xl("Gout", "e") ?><br />
<input type="checkbox" name="heart_disease" value = "heart_disease" /> <? xl("Heart Disease/Attack", "e") ?><br />
<input type="checkbox" name="rheumetic_fever" value = "rheumetic_fever" /> <? xl("Rheumatic Fever", "e") ?><br />
<input type="checkbox" name="miscarriage" value = "miscarriage" /> <? xl("Miscarriage", "e") ?><br />
<input type="checkbox" name="polio" value = "polio" /> <? xl("Polio", "e") ?><br />
</td>
<td><input type="checkbox" name="pneumonia" value = "pneumonia" /> <? xl("Pneumonia", "e") ?><br />
<input type="checkbox" name="atherosclerosis" value = "atherosclerosis" /> <? xl("Atherosclerosis", "e") ?><br />
<input type="checkbox" name="multiple_sclerosis" value = "multiple_sclerosis" /> <? xl("Multiple Sclerosis", "e") ?><br />
<input type="checkbox" name="stroke" value = "stroke" /> <? xl("Stroke", "e") ?><br />
<input type="checkbox" name="tuberculosis" value = "tuberculosis" /> <? xl("Tuberculosis", "e") ?><br />
</td>
<td><input type="checkbox" name="epilepsy" value = "epilepsy" /> <? xl("Epilepsy", "e") ?><br />
<input type="checkbox" name="measles" value = "measles" /> <? xl("Measles", "e") ?><br />
<input type="checkbox" name="mumps" value = "mumps" /> <? xl("Mumps", "e") ?><br />
<input type="checkbox" name="chicken_pox" value = "chicken_pox" /> <? xl("Chicken Pox", "e") ?><br />
<input type="checkbox" name="venereal_disease" value = "venereal_disease" /> <? xl("Venereal Disease", "e") ?><br />
</td>
</tr></tbody></table><hr /></td>
</tr>

<tr>
<td colspan=3><table><tbody class="data">
<tr>
<td colspan=2><? xl("Please Indicate If You Have/Had Trouble With Any Glands/Organs", "e")?><br />
</td>
<td colspan=2><? xl("Family History: Have Your Mother or Father Had Any of These Disorders?", "e")?><br />
</td>
</tr>
<tr>

<td><label><input type="checkbox" name="organ_gland[]" value="pituitary" /><? xl("Pituitary", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="thyroid" /><? xl("Thyroid", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="pancreas" /><? xl("Pancreas", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="adrenal" /><? xl("Adrenal", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="ovary_teste" /><? xl("Ovary/Teste", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="liver" /><? xl("Liver", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="gall_bladder" /><? xl("Gall Bladder", "e") ?></label></td>

<td><label><input type="checkbox" name="organ_gland[]" value="stomach" /><? xl("Stomach", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="small_intestine" /><? xl("Small Intestine", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="large_intestine" /><? xl("Large Intestine", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="bladder" /><? xl("Bladder", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="lungs" /><? xl("Lungs", "e") ?></lable><br />
<label><input type="checkbox" name="organ_gland[]" value="prostate_uterus" /><? xl("Prostate/Uterus", "e") ?></label></td>

<td><label><input type="checkbox" name="family_history[]" value="arthritis" /><? xl("Arthritis", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="atherosclerosis" /><? xl("Atherosclerosis", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="osteoprosis" /><? xl("Osteoporosis", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="allergies" /><? xl("Allergies", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="cancer" /><? xl("Cancer", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="obesity" /><? xl("Obesity", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="hemorrhoids" /><? xl("Hemorrhoids", "e") ?></label></td>

<td><label><input type="checkbox" name="family_history[]" value="stroke" /><? xl("Stroke", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="alcoholism" /><? xl("Alcoholism", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="high_bpressure" /><? xl("High Blood Pressure", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="heart_disease" /><? xl("Heart Trouble", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="kidney_disease" /><? xl("Kidney Disorders", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="nervous_disorder" /><? xl("Other Nervous Systems", "e") ?></lable><br />
<label><input type="checkbox" name="family_history[]" value="spinal_disorder" /><? xl("Spinal Problems", "e") ?></label></td>

</tr>
</tbody></table></td>
</tr>

<tr><td colspan=3><hr />
<? xl("Check Which Meals That You Eat Regularly: ", "e")?>
<input type="checkbox" name="regular_meals" value="breakfast" /><? xl(" Breakfast", "e")?>
<input type="checkbox" name="regular_meals" value="lunch" /><? xl(" Lunch", "e")?>
<input type="checkbox" name="regular_meals" value="dinner" /><? xl(" Dinner", "e")?>
<input type="checkbox" name="regular_meals" value="between_meals" /><? xl(" Between Meals", "e")?>
<input type="checkbox" name="regular_meals" value="before_bed" /><? xl(" Before Bed", "e")?>
</td></tr>
</tbody></table>

<input type="submit" name="submit form" value="submit form" />
</form>
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <? xl("[do not save]",'e') ?> </a>
<?php
formFooter();
?>
