<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?>" topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_rosform", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/rosform/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Review of Systems</span><Br><br>
<span class=bold>General</span><br>
<span class=text>Fever: </span><input type=entry name="fever" value="<?echo $obj{"fever"};?>" ><br>
<span class=text>Chills: </span><input type=entry name="chills" value="<?echo $obj{"chills"};?>" ><br>
<span class=bold>Skin</span><br>
<span class=text>Rash: </span><input type=entry name="rash" value="<?echo $obj{"rash"};?>" ><br>
<span class=text>Cancer: </span><input type=entry name="cancer" value="<?echo $obj{"cancer"};?>" ><br>
<span class=text>Acne: </span><input type=entry name="acne" value="<?echo $obj{"acne"};?>" ><br>
<span class=bold>Eyes</span><br>
<span class=text>Diplopia: </span><input type=entry name="eyes_diplopia" value="<?echo $obj{"eyes_diplopia"};?>" ><br>
<span class=text>Cataracts: </span><input type=entry name="eyes_cataracts" value="<?echo $obj{"eyes_cataracts"};?>" ><br>
<span class=text>Glaucoma: </span><input type=entry name="eyes_glaucoma" value="<?echo $obj{"eyes_glaucoma"};?>" ><br>
<span class=bold>Head</span><br>
<span class=text>Dizziness: </span><input type=entry name="head_dizziness" value="<?echo $obj{"head_dizziness"};?>" ><br>
<span class=text>Syncope: </span><input type=entry name="head_syncope" value="<?echo $obj{"head_syncope"};?>" ><br>
<span class=text>Headache: </span><input type=entry name="head_headache" value="<?echo $obj{"head_headache"};?>" ><br>
<span class=bold>Ear</span><br>
<span class=text>Pain: </span><input type=entry name="ear_pain" value="<?echo $obj{"ear_pain"};?>" ><br>
<span class=text>Loss: </span><input type=entry name="ear_loss" value="<?echo $obj{"ear_loss"};?>" ><br>
<span class=bold>Nose</span><br>
<span class=text>Congestion: </span><input type=entry name="nose_congestion" value="<?echo $obj{"nose_congestion"};?>" ><br>
<span class=text>Epitaxis: </span><input type=entry name="nose_epitaxis" value="<?echo $obj{"nose_epitaxis"};?>" ><br>
<span class=bold>Throat</span><br>
<span class=text>Sore: </span><input type=entry name="throat_sore" value="<?echo $obj{"throat_sore"};?>" ><br>
<span class=text>Dysphagia: </span><input type=entry name="throat_dysphagia" value="<?echo $obj{"throat_dysphagia"};?>" ><br>
<span class=text>Swollen Glands: </span><input type=entry name="throat_swollen_glands" value="<?echo $obj{"throat_swollen_glands"};?>" ><br>
<span class=bold>Respiratory</span><br>
<span class=text>Dyspnea: </span><input type=entry name="respiratory_dyspnea" value="<?echo $obj{"respiratory_dyspnea"};?>" ><br>
<span class=text>At Rest: </span><input type=entry name="respiratory_rest" value="<?echo $obj{"respiratory_rest"};?>" ><br>
<span class=text>At Exertion: </span><input type=entry name="respiratory_exertion" value="<?echo $obj{"respiratory_exertion"};?>" ><br>
<span class=text>Cough: </span><input type=entry name="respiratory_cough" value="<?echo $obj{"respiratory_cough"};?>" ><br>
<span class=text>Asthma: </span><input type=entry name="respiratory_asthma" value="<?echo $obj{"respiratory_asthma"};?>" ><br>
<span class=bold>Cardiac</span><br>
<span class=text>Palpitation: </span><input type=entry name="cardiac_palpitation" value="<?echo $obj{"cardiac_palpitation"};?>" ><br>
<span class=text>Heart Murmur: </span><input type=entry name="cardiac_heart_murmur" value="<?echo $obj{"cardiac_heart_murmur"};?>" ><br>
<span class=text>Chest Pain: </span><input type=entry name="cardiac_chest_pain" value="<?echo $obj{"cardiac_chest_pain"};?>" ><br>
<span class=text>Pleuritic: </span><input type=entry name="cardiac_pleuritic" value="<?echo $obj{"cardiac_pleuritic"};?>" ><br>
<span class=text>HTN: </span><input type=entry name="cardiac_htm" value="<?echo $obj{"cardiac_htm"};?>" ><br>
<span class=text>NTG Use: </span><input type=entry name="cardiac_ntg_use" value="<?echo $obj{"cardiac_ntg_use"};?>" ><br>
<span class=bold>HEM LYMPH</span><br>
<span class=text>Anemia: </span><input type=entry name="hem_lymph_anemia" value="<?echo $obj{"hem_lymph_anemia"};?>" ><br>
<span class=text>Nightsweats: </span><input type=entry name="hem_lymph_nightsweats" value="<?echo $obj{"hem_lymph_nightsweats"};?>" ><br>
<span class=bold>GI</span><br>
<span class=text>Nausea: </span><input type=entry name="gi_nausea" value="<?echo $obj{"gi_nausea"};?>" ><br>
<span class=text>Vomiting: </span><input type=entry name="gi_vomiting" value="<?echo $obj{"gi_vomiting"};?>" ><br>
<span class=text>Diarrhea: </span><input type=entry name="gi_diarrhea" value="<?echo $obj{"gi_diarrhea"};?>" ><br>
<span class=text>Constipation: </span><input type=entry name="gi_constipation" value="<?echo $obj{"gi_constipation"};?>" ><br>
<span class=text>Black Stools: </span><input type=entry name="gi_black_stools" value="<?echo $obj{"gi_black_stools"};?>" ><br>
<span class=text>Blood in Stools: </span><input type=entry name="gi_blood_stools" value="<?echo $obj{"gi_blood_stools"};?>" ><br>
<span class=text>Pain: </span><input type=entry name="gi_pain" value="<?echo $obj{"gi_pain"};?>" ><br>
<span class=text>Abdominal Pain: </span><input type=entry name="gi_abdominal_pain" value="<?echo $obj{"gi_abdominal_pain"};?>" ><br>
<span class=bold>GU</span><br>
<span class=text>Nocturia: </span><input type=entry name="gu_nocturia" value="<?echo $obj{"gu_nocturia"};?>" ><br>
<span class=text>Stream: </span><input type=entry name="gu_stream" value="<?echo $obj{"gu_stream"};?>" ><br>
<span class=text>Hematuria: </span><input type=entry name="gu_hematuria" value="<?echo $obj{"gu_hematuria"};?>" ><br>
<span class=text>Pain: </span><input type=entry name="gu_pain" value="<?echo $obj{"gu_pain"};?>" ><br>
<span class=text>Incontinence: </span><input type=entry name="gu_incontinence" value="<?echo $obj{"gu_incontinence"};?>" ><br>
<span class=text>Frequency: </span><input type=entry name="gu_frequency" value="<?echo $obj{"gu_frequency"};?>" ><br>
<span class=bold>GYN</span><br>
<span class=text>Lump: </span><input type=entry name="gyn_lump" value="<?echo $obj{"gyn_lump"};?>" ><br>
<span class=text>Checkup: </span><input type=entry size=10 name=gyn_checkup value="<?if ($obj{"gyn_checkup"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($obj{"gyn_checkup"}));} else {echo "YYYY-MM-DD";}?>"><br>
<span class=text>Mammogram: </span><input type=entry size=10 name=gyn_mammogram value="<?if ($obj{"gyn_mammogram"} != "0000-00-00 00:00:00") {echo date("Y-m-d",strtotime($obj{"gyn_mammogram"}));} else {echo "YYYY-MM-DD";}?>"><br>
<span class=text>Sexual: </span><input type=entry name="sexual" value="<?echo $obj{"sexual"};?>" ><br>
<span class=bold>Ortho</span><br>
<span class=text>Pain: </span><input type=entry name="ortho_pain" value="<?echo $obj{"ortho_pain"};?>" ><br>
<span class=text>Trauma: </span><input type=entry name="ortho_trauma" value="<?echo $obj{"ortho_trauma"};?>" ><br>
<span class=text>Neuro: </span><input type=entry name="ortho_neuro" value="<?echo $obj{"ortho_neuro"};?>" ><br>
<span class=bold>Endo</span><br>
<span class=text>Diabetes: </span><input type=entry name="endo_diabetes" value="<?echo $obj{"endo_diabetes"};?>" ><br>
<span class=text>Thyroid: </span><input type=entry name="endo_thyroid" value="<?echo $obj{"endo_thyroid"};?>" ><br>
<span class=bold>Psych</span><br>
<span class=text>Sleep Problems: </span><input type=entry name="psych_sleep_problems" value="<?echo $obj{"psych_sleep_problems"};?>" ><br>
<span class=text>Memory Loss: </span><input type=entry name="psych_memory_loss" value="<?echo $obj{"psych_memory_loss"};?>" ><br>
<span class=bold>Leg</span><br>
<span class=text>Swelling: </span><input type=entry name="leg_swelling" value="<?echo $obj{"leg_swelling"};?>" ><br>
<span class=text>Numbness: </span><input type=entry name="leg_numbness" value="<?echo $obj{"leg_numbness"};?>" ><br>
<span class=bold>Appearance</span><br>
<input type=checkbox name="appearance_nad"  <?if ($obj{"appearance_nad"} == "on") {echo "checked";};?>><span class=text>NAD</span><br>
<input type=checkbox name="appearance_mild"  <?if ($obj{"appearance_mild"} == "on") {echo "checked";};?>><span class=text>Mild</span><br>
<input type=checkbox name="appearance_moderate"  <?if ($obj{"appearance_moderate"} == "on") {echo "checked";};?>><span class=text>Moderate</span><br>
<input type=checkbox name="appearance_severe_distress"  <?if ($obj{"appearance_severe_distress"} == "on") {echo "checked";};?>><span class=text>Severe Distress</span><br>
<span class=bold>Skin</span><br>
<span class=text>Moles: </span><input type=entry name="skin_moles" value="<?echo $obj{"skin_moles"};?>" ><br>
<span class=text>Rash: </span><input type=entry name="skin_rash" value="<?echo $obj{"skin_rash"};?>" ><br>
<span class=bold>Head</span><br>
<input type=checkbox name="head_ent_nl_inspection"  <?if ($obj{"head_ent_nl_inspection"} == "on") {echo "checked";};?>><span class=text>ENT nl inspection</span><br>
<input type=checkbox name="head_pharynx_nl"  <?if ($obj{"head_pharynx_nl"} == "on") {echo "checked";};?>><span class=text>Pharynx nl</span><br>
<input type=checkbox name="head_abn_tm"  <?if ($obj{"head_abn_tm"} == "on") {echo "checked";};?>><span class=text>ABN TM</span><br>
<input type=checkbox name="head_scleral_icterus"  <?if ($obj{"head_scleral_icterus"} == "on") {echo "checked";};?>><span class=text>Scleral Icterus</span><br>
<span class=bold>Eyes</span><br>
<span class=text>Pupils Right: </span><input type=entry name="eyes_pupils_right" value="<?echo $obj{"eyes_pupils_right"};?>" ><br>
<span class=text>Pupils Left: </span><input type=entry name="eyes_pupils_left" value="<?echo $obj{"eyes_pupils_left"};?>" ><br>
<span class=text>Fundi Right: </span><input type=entry name="fundi_right" value="<?echo $obj{"fundi_right"};?>" ><br>
<span class=text>Fundi Left: </span><input type=entry name="fundi_left" value="<?echo $obj{"fundi_left"};?>" ><br>
<span class=bold>Visual Fields</span><br>
<input type=checkbox name="vision_fields_normal"  <?if ($obj{"vision_fields_normal"} == "on") {echo "checked";};?>><span class=text>Normal</span><br>
<input type=checkbox name="vision_fields_abnormal"  <?if ($obj{"vision_fields_abnormal"} == "on") {echo "checked";};?>><span class=text>Abnormal</span><br>
<span class=bold>Ears</span><br>
<span class=text>Right TM: </span><input type=entry name="ears_right" value="<?echo $obj{"ears_right"};?>" ><br>
<span class=text>Left TM: </span><input type=entry name="ears_left" value="<?echo $obj{"ears_left"};?>" ><br>
<span class=text>Hearing Deficit: </span><input type=entry name="ears_hearing_deficit" value="<?echo $obj{"ears_hearing_deficit"};?>" ><br>
<span class=bold>Nose</span><br>
<span class=text>Erythema: </span><input type=entry name="nose_erythema" value="<?echo $obj{"nose_erythema"};?>" ><br>
<span class=text>Discharge: </span><input type=entry name="nose_discharge" value="<?echo $obj{"nose_discharge"};?>" ><br>
<span class=bold>Throat</span><br>
<input type=checkbox name="throat_nl"  <?if ($obj{"throat_nl"} == "on") {echo "checked";};?>><span class=text>NL</span><br>
<input type=checkbox name="throat_thyroid_nl"  <?if ($obj{"throat_thyroid_nl"} == "on") {echo "checked";};?>><span class=text>Thyroid nl</span><br>
<input type=checkbox name="throat_thyromegaly"  <?if ($obj{"throat_thyromegaly"} == "on") {echo "checked";};?>><span class=text>Thyromegaly</span><br>
<input type=checkbox name="throat_erytheomatous"  <?if ($obj{"throat_erytheomatous"} == "on") {echo "checked";};?>><span class=text>Erytheomatous</span><br>
<span class=bold>Lymph Nodes</span><br>
<span class=text>Cervical Right: </span><input type=entry name="lymph_cervical_right" value="<?echo $obj{"lymph_cervical_right"};?>" ><br>
<span class=text>Cervical Left: </span><input type=entry name="lymph_cervical_left" value="<?echo $obj{"lymph_cervical_left"};?>" ><br>
<span class=text>Axillary Right: </span><input type=entry name="lymph_axillary_right" value="<?echo $obj{"lymph_axillary_right"};?>" ><br>
<span class=text>Axillary Left: </span><input type=entry name="lymph_axillary_left" value="<?echo $obj{"lymph_axillary_left"};?>" ><br>
<span class=text>Supraclav Right: </span><input type=entry name="lymph_supraclav_right" value="<?echo $obj{"lymph_supraclav_right"};?>" ><br>
<span class=text>Supraclav Left: </span><input type=entry name="lymph_supraclav_left" value="<?echo $obj{"lymph_supraclav_left"};?>" ><br>
<span class=text>Inguinal Right: </span><input type=entry name="lymph_inguinal_right" value="<?echo $obj{"lymph_inguinal_right"};?>" ><br>
<span class=text>Inguinal Left: </span><input type=entry name="lymph_inguinal_left" value="<?echo $obj{"lymph_inguinal_left"};?>" ><br>
<span class=text>Carotid Bruits: </span><input type=entry name="carotid_bruits" value="<?echo $obj{"carotid_bruits"};?>" ><br>
<span class=bold>Respiratory</span><br>
<input type=checkbox name="respiratory_no_distress"  <?if ($obj{"respiratory_no_distress"} == "on") {echo "checked";};?>><span class=text>No disress</span><br>
<input type=checkbox name="chest_non_tender"  <?if ($obj{"chest_non_tender"} == "on") {echo "checked";};?>><span class=text>Chest non-tender</span><br>
<input type=checkbox name="respiratory_distress"  <?if ($obj{"respiratory_distress"} == "on") {echo "checked";};?>><span class=text>Respiratory distress</span><br>
<input type=checkbox name="respiratory_clear_to_ausc"  <?if ($obj{"respiratory_clear_to_ausc"} == "on") {echo "checked";};?>><span class=text>Clear to ausc</span><br>
<input type=checkbox name="respiratory_splinting"  <?if ($obj{"respiratory_splinting"} == "on") {echo "checked";};?>><span class=text>Splinting</span><br>
<input type=checkbox name="respiratory_rales"  <?if ($obj{"respiratory_rales"} == "on") {echo "checked";};?>><span class=text>Rales</span><br>
<input type=checkbox name="respiratory_rhonchi"  <?if ($obj{"respiratory_rhonchi"} == "on") {echo "checked";};?>><span class=text>Rhonchi</span><br>
<input type=checkbox name="respiratory_wheezing"  <?if ($obj{"respiratory_wheezing"} == "on") {echo "checked";};?>><span class=text>Wheezing</span><br>
<span class=bold>Heart</span><br>
<input type=checkbox name="heart_regular_rate"  <?if ($obj{"heart_regular_rate"} == "on") {echo "checked";};?>><span class=text>Regular rate</span><br>
<input type=checkbox name="heart_irregular_rate"  <?if ($obj{"heart_irregular_rate"} == "on") {echo "checked";};?>><span class=text>Irregular rate</span><br>
<input type=checkbox name="heart_murmur"  <?if ($obj{"heart_murmur"} == "on") {echo "checked";};?>><span class=text>Murmur</span><br>
<input type=checkbox name="heart_gallop"  <?if ($obj{"heart_gallop"} == "on") {echo "checked";};?>><span class=text>Gallop</span><br>
<input type=checkbox name="heart_rub"  <?if ($obj{"heart_rub"} == "on") {echo "checked";};?>><span class=text>Rub</span><br>
<input type=checkbox name="heart_tachy_brady"  <?if ($obj{"heart_tachy_brady"} == "on") {echo "checked";};?>><span class=text>Tachy/Brady</span><br>
<input type=checkbox name="heart_jvd"  <?if ($obj{"heart_jvd"} == "on") {echo "checked";};?>><span class=text>JVD present</span><br>
<input type=checkbox name="heart_grade"  <?if ($obj{"heart_grade"} == "on") {echo "checked";};?>><span class=text>Grade</span><br>
<input type=checkbox name="heart_sys_dias"  <?if ($obj{"heart_sys_dias"} == "on") {echo "checked";};?>><span class=text>Sys/Dias</span><br>
<span class=bold>Breasts</span><br>
<span class=text>Right Cystic: </span><input type=entry name="breast_right_cystic" value="<?echo $obj{"breast_right_cystic"};?>" ><br>
<span class=text>Left Cystic: </span><input type=entry name="breast_left_cystic" value="<?echo $obj{"breast_left_cystic"};?>" ><br>
<span class=bold>Abdomen</span><br>
<input type=checkbox name="abdomen_non_tender"  <?if ($obj{"abdomen_non_tender"} == "on") {echo "checked";};?>><span class=text>Non-tender</span><br>
<input type=checkbox name="abdomen_no_organomegaly"  <?if ($obj{"abdomen_no_organomegaly"} == "on") {echo "checked";};?>><span class=text>No organomegaly</span><br>
<input type=checkbox name="abdomen_guarding"  <?if ($obj{"abdomen_guarding"} == "on") {echo "checked";};?>><span class=text>Guarding</span><br>
<input type=checkbox name="abdomen_rebound"  <?if ($obj{"abdomen_rebound"} == "on") {echo "checked";};?>><span class=text>Rebound</span><br>
<input type=checkbox name="abdomen_bowel_sounds"  <?if ($obj{"abdomen_bowel_sounds"} == "on") {echo "checked";};?>><span class=text>Abn bowel sounds</span><br>
<input type=checkbox name="abdomen_hepatomegaly"  <?if ($obj{"abdomen_hepatomegaly"} == "on") {echo "checked";};?>><span class=text>Hepatomegaly</span><br>
<span class=bold>Rectal</span><br>
<span class=text>Prostate: </span><input type=entry name="rectal_prostate" value="<?echo $obj{"rectal_prostate"};?>" ><br>
<span class=text>Hemmocult: </span><input type=entry name="rectal_hemmocult" value="<?echo $obj{"rectal_hemmocult"};?>" ><br>
<input type=checkbox name="rectal_tender"  <?if ($obj{"rectal_tender"} == "on") {echo "checked";};?>><span class=text>Rectal tender</span><br>
<input type=checkbox name="rectal_hemmorrhoids"  <?if ($obj{"rectal_hemmorrhoids"} == "on") {echo "checked";};?>><span class=text>Hemmorrhiods</span><br>
<span class=bold>Genitalia</span><br>
<span class=text>Hernia: </span><input type=entry name="genitalia_hernia" value="<?echo $obj{"genitalia_hernia"};?>" ><br>
<span class=text>Ext. Vagina: </span><input type=entry name="genitalia_ext_vagina" value="<?echo $obj{"genitalia_ext_vagina"};?>" ><br>
<span class=text>Male: </span><input type=entry name="genitalia_male" value="<?echo $obj{"genitalia_male"};?>" ><br>
<span class=text>Speculum: </span><input type=entry name="speculum" value="<?echo $obj{"speculum"};?>" ><br>
<span class=text>Palpation: </span><input type=entry name="palpation" value="<?echo $obj{"palpation"};?>" ><br>
<span class=text>Uterus: </span><input type=entry name="uterus" value="<?echo $obj{"uterus"};?>" ><br>
<span class=text>Adnexa Right: </span><input type=entry name="adnexa_right" value="<?echo $obj{"adnexa_right"};?>" ><br>
<span class=text>Adnexa Left: </span><input type=entry name="adnexa_left" value="<?echo $obj{"adnexa_left"};?>" ><br>
<span class=bold>Neuro Exam</span><br>
<span class=text>CN's: </span><input type=entry name="neuro_exam_cns" value="<?echo $obj{"neuro_exam_cns"};?>" ><br>
<span class=text>Oriented: </span><input type=entry name="neuro_exam_oriented" value="<?echo $obj{"neuro_exam_oriented"};?>" ><br>
<span class=text>Confused: </span><input type=entry name="neuro_exam_confused" value="<?echo $obj{"neuro_exam_confused"};?>" ><br>
<span class=text>MMSE: </span><input type=entry name="neuro_exam_mmse" value="<?echo $obj{"neuro_exam_mmse"};?>" ><br>
<span class=text>Muscle Strength: </span><input type=entry name="muscle_strength" value="<?echo $obj{"muscle_strength"};?>" ><br>
<span class=bold>Reflexes</span><br>
<span class=text>RUE: </span><input type=entry name="reflexes_rue" value="<?echo $obj{"reflexes_rue"};?>" ><br>
<span class=text>RLE: </span><input type=entry name="reflexes_rle" value="<?echo $obj{"reflexes_rle"};?>" ><br>
<span class=text>LUE: </span><input type=entry name="reflexes_lue" value="<?echo $obj{"reflexes_lue"};?>" ><br>
<span class=text>LLE: </span><input type=entry name="reflexes_lle" value="<?echo $obj{"reflexes_lle"};?>" ><br>
<span class=text>Vibration: </span><input type=entry name="vibration" value="<?echo $obj{"vibration"};?>" ><br>
<span class=text>Sensation: </span><input type=entry name="sensation" value="<?echo $obj{"sensation"};?>" ><br>
<span class=text>Babinski: </span><input type=entry name="babinski" value="<?echo $obj{"babinski"};?>" ><br>
<span class=text>Edema: </span><input type=entry name="edema" value="<?echo $obj{"edema"};?>" ><br>
<span class=text>Varicosities: </span><input type=entry name="varicosities" value="<?echo $obj{"varicosities"};?>" ><br>
<span class=text>Nails: </span><input type=entry name="nails" value="<?echo $obj{"nails"};?>" ><br>
<span class=bold>Joints</span><br>
<span class=text>Neck: </span><input type=entry name="joints_neck" value="<?echo $obj{"joints_neck"};?>" ><br>
<span class=text>Shoulder Right: </span><input type=entry name="joints_shoulder_right" value="<?echo $obj{"joints_shoulder_right"};?>" ><br>
<span class=text>Shoulder Left: </span><input type=entry name="joints_sholder_left" value="<?echo $obj{"joints_sholder_left"};?>" ><br>
<span class=text>Elbow Right: </span><input type=entry name="joints_elbow_right" value="<?echo $obj{"joints_elbow_right"};?>" ><br>
<span class=text>Elbow Left: </span><input type=entry name="joints_elbow_left" value="<?echo $obj{"joints_elbow_left"};?>" ><br>
<span class=text>Wrist Right: </span><input type=entry name="joints_wrist_right" value="<?echo $obj{"joints_wrist_right"};?>" ><br>
<span class=text>Wrist Left: </span><input type=entry name="joints_wrist_left" value="<?echo $obj{"joints_wrist_left"};?>" ><br>
<span class=text>Hand Right: </span><input type=entry name="joints_hand_right" value="<?echo $obj{"joints_hand_right"};?>" ><br>
<span class=text>Hand Left: </span><input type=entry name="joints_hand_left" value="<?echo $obj{"joints_hand_left"};?>" ><br>
<span class=text>Hip Right: </span><input type=entry name="joints_hip_right" value="<?echo $obj{"joints_hip_right"};?>" ><br>
<span class=text>Hip Left: </span><input type=entry name="joints_hip_left" value="<?echo $obj{"joints_hip_left"};?>" ><br>
<span class=text>Back Right: </span><input type=entry name="joints_back_right" value="<?echo $obj{"joints_back_right"};?>" ><br>
<span class=text>Back Left: </span><input type=entry name="joints_back_left" value="<?echo $obj{"joints_back_left"};?>" ><br>
<span class=text>SLR Right: </span><input type=entry name="joints_slr_right" value="<?echo $obj{"joints_slr_right"};?>" ><br>
<span class=text>SLR Left: </span><input type=entry name="joints_slr_left" value="<?echo $obj{"joints_slr_left"};?>" ><br>
<span class=text>Knee Right: </span><input type=entry name="joints_knee_right" value="<?echo $obj{"joints_knee_right"};?>" ><br>
<span class=text>Knee Left: </span><input type=entry name="joints_knee_left" value="<?echo $obj{"joints_knee_left"};?>" ><br>
<span class=text>Ankle Right: </span><input type=entry name="joints_ankle_right" value="<?echo $obj{"joints_ankle_right"};?>" ><br>
<span class=text>Ankle Left: </span><input type=entry name="joints_ankle_left" value="<?echo $obj{"joints_ankle_left"};?>" ><br>
<span class=text>Foot Right: </span><input type=entry name="joints_foot_right" value="<?echo $obj{"joints_foot_right"};?>" ><br>
<span class=text>Foot Left: </span><input type=entry name="joints_foot_left" value="<?echo $obj{"joints_foot_left"};?>" ><br>
<span class=bold>Conclusions</span><br>
<span class=text>Impression: </span><br><textarea cols=40 rows=8 wrap=virtual name="conclusions_impression" ><?echo $obj{"conclusions_impression"};?></textarea><br>
<span class=text>Discussion: </span><br><textarea cols=40 rows=8 wrap=virtual name="conclusions_discussion" ><?echo $obj{"conclusions_discussion"};?></textarea><br>
<span class=text>Treatment: </span><br><textarea cols=40 rows=8 wrap=virtual name="conclusions_treatment" ><?echo $obj{"conclusions_treatment"};?></textarea><br>
<input type=checkbox name="conclusions_breast_self_exam"  <?if ($obj{"conclusions_breast_self_exam"} == "on") {echo "checked";};?>><span class=text>Breast Self Exam</span><br>
<input type=checkbox name="conclusions_flex_sig_colonoscopy"  <?if ($obj{"conclusions_flex_sig_colonoscopy"} == "on") {echo "checked";};?>><span class=text>Flex Sig/Colonoscopy</span><br>
<input type=checkbox name="conclusions_mammography"  <?if ($obj{"conclusions_mammography"} == "on") {echo "checked";};?>><span class=text>Mammography</span><br>
<input type=checkbox name="conclusions_cholesterol_teaching"  <?if ($obj{"conclusions_cholesterol_teaching"} == "on") {echo "checked";};?>><span class=text>Cholesterol Teaching</span><br>
<input type=checkbox name="conclusions_advance_directive"  <?if ($obj{"conclusions_advance_directive"} == "on") {echo "checked";};?>><span class=text>Advance Directive</span><br>
<span class=text>Follow-up: </span><br><textarea cols=40 rows=8 wrap=virtual name="follow_up" ><?echo $obj{"follow_up"};?></textarea><br>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
