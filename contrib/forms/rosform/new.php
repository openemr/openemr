<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: rosform");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/rosform/save.php?mode=new" name="my_form">
<span class="title">Review of Systems</span><br><br>
<span class=bold>General</span><br>
<span class=text>Fever: </span><input type=entry name="fever" value="" ><br>
<span class=text>Chills: </span><input type=entry name="chills" value="" ><br>
<span class=bold>Skin</span><br>
<span class=text>Rash: </span><input type=entry name="rash" value="" ><br>
<span class=text>Cancer: </span><input type=entry name="cancer" value="" ><br>
<span class=text>Acne: </span><input type=entry name="acne" value="" ><br>
<span class=bold>Eyes</span><br>
<span class=text>Diplopia: </span><input type=entry name="eyes_diplopia" value="" ><br>
<span class=text>Cataracts: </span><input type=entry name="eyes_cataracts" value="" ><br>
<span class=text>Glaucoma: </span><input type=entry name="eyes_glaucoma" value="" ><br>
<span class=bold>Head</span><br>
<span class=text>Dizziness: </span><input type=entry name="head_dizziness" value="" ><br>
<span class=text>Syncope: </span><input type=entry name="head_syncope" value="" ><br>
<span class=text>Headache: </span><input type=entry name="head_headache" value="" ><br>
<span class=bold>Ear</span><br>
<span class=text>Pain: </span><input type=entry name="ear_pain" value="" ><br>
<span class=text>Loss: </span><input type=entry name="ear_loss" value="" ><br>
<span class=bold>Nose</span><br>
<span class=text>Congestion: </span><input type=entry name="nose_congestion" value="" ><br>
<span class=text>Epitaxis: </span><input type=entry name="nose_epitaxis" value="" ><br>
<span class=bold>Throat</span><br>
<span class=text>Sore: </span><input type=entry name="throat_sore" value="" ><br>
<span class=text>Dysphagia: </span><input type=entry name="throat_dysphagia" value="" ><br>
<span class=text>Swollen Glands: </span><input type=entry name="throat_swollen_glands" value="" ><br>
<span class=bold>Respiratory</span><br>
<span class=text>Dyspnea: </span><input type=entry name="respiratory_dyspnea" value="" ><br>
<span class=text>At Rest: </span><input type=entry name="respiratory_rest" value="" ><br>
<span class=text>At Exertion: </span><input type=entry name="respiratory_exertion" value="" ><br>
<span class=text>Cough: </span><input type=entry name="respiratory_cough" value="" ><br>
<span class=text>Asthma: </span><input type=entry name="respiratory_asthma" value="" ><br>
<span class=bold>Cardiac</span><br>
<span class=text>Palpitation: </span><input type=entry name="cardiac_palpitation" value="" ><br>
<span class=text>Heart Murmur: </span><input type=entry name="cardiac_heart_murmur" value="" ><br>
<span class=text>Chest Pain: </span><input type=entry name="cardiac_chest_pain" value="" ><br>
<span class=text>Pleuritic: </span><input type=entry name="cardiac_pleuritic" value="" ><br>
<span class=text>HTN: </span><input type=entry name="cardiac_htm" value="" ><br>
<span class=text>NTG Use: </span><input type=entry name="cardiac_ntg_use" value="" ><br>
<span class=bold>HEM LYMPH</span><br>
<span class=text>Anemia: </span><input type=entry name="hem_lymph_anemia" value="" ><br>
<span class=text>Nightsweats: </span><input type=entry name="hem_lymph_nightsweats" value="" ><br>
<span class=bold>GI</span><br>
<span class=text>Nausea: </span><input type=entry name="gi_nausea" value="" ><br>
<span class=text>Vomiting: </span><input type=entry name="gi_vomiting" value="" ><br>
<span class=text>Diarrhea: </span><input type=entry name="gi_diarrhea" value="" ><br>
<span class=text>Constipation: </span><input type=entry name="gi_constipation" value="" ><br>
<span class=text>Black Stools: </span><input type=entry name="gi_black_stools" value="" ><br>
<span class=text>Blood in Stools: </span><input type=entry name="gi_blood_stools" value="" ><br>
<span class=text>Pain: </span><input type=entry name="gi_pain" value="" ><br>
<span class=text>Abdominal Pain: </span><input type=entry name="gi_abdominal_pain" value="" ><br>
<span class=bold>GU</span><br>
<span class=text>Nocturia: </span><input type=entry name="gu_nocturia" value="" ><br>
<span class=text>Stream: </span><input type=entry name="gu_stream" value="" ><br>
<span class=text>Hematuria: </span><input type=entry name="gu_hematuria" value="" ><br>
<span class=text>Pain: </span><input type=entry name="gu_pain" value="" ><br>
<span class=text>Incontinence: </span><input type=entry name="gu_incontinence" value="" ><br>
<span class=text>Frequency: </span><input type=entry name="gu_frequency" value="" ><br>
<span class=bold>GYN</span><br>
<span class=text>Lump: </span><input type=entry name="gyn_lump" value="" ><br>
<span class=text>Checkup: </span><input type=entry name='gyn_checkup' size=10 value='YYYY-MM-DD' ><br>
<span class=text>Mammogram: </span><input type=entry name='gyn_mammogram' size=10 value='YYYY-MM-DD' ><br>
<span class=text>Sexual: </span><input type=entry name="sexual" value="" ><br>
<span class=bold>Ortho</span><br>
<span class=text>Pain: </span><input type=entry name="ortho_pain" value="" ><br>
<span class=text>Trauma: </span><input type=entry name="ortho_trauma" value="" ><br>
<span class=text>Neuro: </span><input type=entry name="ortho_neuro" value="" ><br>
<span class=bold>Endo</span><br>
<span class=text>Diabetes: </span><input type=entry name="endo_diabetes" value="" ><br>
<span class=text>Thyroid: </span><input type=entry name="endo_thyroid" value="" ><br>
<span class=bold>Psych</span><br>
<span class=text>Sleep Problems: </span><input type=entry name="psych_sleep_problems" value="" ><br>
<span class=text>Memory Loss: </span><input type=entry name="psych_memory_loss" value="" ><br>
<span class=bold>Leg</span><br>
<span class=text>Swelling: </span><input type=entry name="leg_swelling" value="" ><br>
<span class=text>Numbness: </span><input type=entry name="leg_numbness" value="" ><br>
<span class=bold>Appearance</span><br>
<input type=checkbox name='appearance_nad'  ><span class=text>NAD</span><br>
<input type=checkbox name='appearance_mild'  ><span class=text>Mild</span><br>
<input type=checkbox name='appearance_moderate'  ><span class=text>Moderate</span><br>
<input type=checkbox name='appearance_severe_distress'  ><span class=text>Severe Distress</span><br>
<span class=bold>Skin</span><br>
<span class=text>Moles: </span><input type=entry name="skin_moles" value="" ><br>
<span class=text>Rash: </span><input type=entry name="skin_rash" value="" ><br>
<span class=bold>Head</span><br>
<input type=checkbox name='head_ent_nl_inspection'  ><span class=text>ENT nl inspection</span><br>
<input type=checkbox name='head_pharynx_nl'  ><span class=text>Pharynx nl</span><br>
<input type=checkbox name='head_abn_tm'  ><span class=text>ABN TM</span><br>
<input type=checkbox name='head_scleral_icterus'  ><span class=text>Scleral Icterus</span><br>
<span class=bold>Eyes</span><br>
<span class=text>Pupils Right: </span><input type=entry name="eyes_pupils_right" value="" ><br>
<span class=text>Pupils Left: </span><input type=entry name="eyes_pupils_left" value="" ><br>
<span class=text>Fundi Right: </span><input type=entry name="fundi_right" value="" ><br>
<span class=text>Fundi Left: </span><input type=entry name="fundi_left" value="" ><br>
<span class=bold>Visual Fields</span><br>
<input type=checkbox name='vision_fields_normal'  ><span class=text>Normal</span><br>
<input type=checkbox name='vision_fields_abnormal'  ><span class=text>Abnormal</span><br>
<span class=bold>Ears</span><br>
<span class=text>Right TM: </span><input type=entry name="ears_right" value="" ><br>
<span class=text>Left TM: </span><input type=entry name="ears_left" value="" ><br>
<span class=text>Hearing Deficit: </span><input type=entry name="ears_hearing_deficit" value="" ><br>
<span class=bold>Nose</span><br>
<span class=text>Erythema: </span><input type=entry name="nose_erythema" value="" ><br>
<span class=text>Discharge: </span><input type=entry name="nose_discharge" value="" ><br>
<span class=bold>Throat</span><br>
<input type=checkbox name='throat_nl'  ><span class=text>NL</span><br>
<input type=checkbox name='throat_thyroid_nl'  ><span class=text>Thyroid nl</span><br>
<input type=checkbox name='throat_thyromegaly'  ><span class=text>Thyromegaly</span><br>
<input type=checkbox name='throat_erytheomatous'  ><span class=text>Erytheomatous</span><br>
<span class=bold>Lymph Nodes</span><br>
<span class=text>Cervical Right: </span><input type=entry name="lymph_cervical_right" value="" ><br>
<span class=text>Cervical Left: </span><input type=entry name="lymph_cervical_left" value="" ><br>
<span class=text>Axillary Right: </span><input type=entry name="lymph_axillary_right" value="" ><br>
<span class=text>Axillary Left: </span><input type=entry name="lymph_axillary_left" value="" ><br>
<span class=text>Supraclav Right: </span><input type=entry name="lymph_supraclav_right" value="" ><br>
<span class=text>Supraclav Left: </span><input type=entry name="lymph_supraclav_left" value="" ><br>
<span class=text>Inguinal Right: </span><input type=entry name="lymph_inguinal_right" value="" ><br>
<span class=text>Inguinal Left: </span><input type=entry name="lymph_inguinal_left" value="" ><br>
<span class=text>Carotid Bruits: </span><input type=entry name="carotid_bruits" value="" ><br>
<span class=bold>Respiratory</span><br>
<input type=checkbox name='respiratory_no_distress'  ><span class=text>No disress</span><br>
<input type=checkbox name='chest_non_tender'  ><span class=text>Chest non-tender</span><br>
<input type=checkbox name='respiratory_distress'  ><span class=text>Respiratory distress</span><br>
<input type=checkbox name='respiratory_clear_to_ausc'  ><span class=text>Clear to ausc</span><br>
<input type=checkbox name='respiratory_splinting'  ><span class=text>Splinting</span><br>
<input type=checkbox name='respiratory_rales'  ><span class=text>Rales</span><br>
<input type=checkbox name='respiratory_rhonchi'  ><span class=text>Rhonchi</span><br>
<input type=checkbox name='respiratory_wheezing'  ><span class=text>Wheezing</span><br>
<span class=bold>Heart</span><br>
<input type=checkbox name='heart_regular_rate'  ><span class=text>Regular rate</span><br>
<input type=checkbox name='heart_irregular_rate'  ><span class=text>Irregular rate</span><br>
<input type=checkbox name='heart_murmur'  ><span class=text>Murmur</span><br>
<input type=checkbox name='heart_gallop'  ><span class=text>Gallop</span><br>
<input type=checkbox name='heart_rub'  ><span class=text>Rub</span><br>
<input type=checkbox name='heart_tachy_brady'  ><span class=text>Tachy/Brady</span><br>
<input type=checkbox name='heart_jvd'  ><span class=text>JVD present</span><br>
<input type=checkbox name='heart_grade'  ><span class=text>Grade</span><br>
<input type=checkbox name='heart_sys_dias'  ><span class=text>Sys/Dias</span><br>
<span class=bold>Breasts</span><br>
<span class=text>Right Cystic: </span><input type=entry name="breast_right_cystic" value="" ><br>
<span class=text>Left Cystic: </span><input type=entry name="breast_left_cystic" value="" ><br>
<span class=bold>Abdomen</span><br>
<input type=checkbox name='abdomen_non_tender'  ><span class=text>Non-tender</span><br>
<input type=checkbox name='abdomen_no_organomegaly'  ><span class=text>No organomegaly</span><br>
<input type=checkbox name='abdomen_guarding'  ><span class=text>Guarding</span><br>
<input type=checkbox name='abdomen_rebound'  ><span class=text>Rebound</span><br>
<input type=checkbox name='abdomen_bowel_sounds'  ><span class=text>Abn bowel sounds</span><br>
<input type=checkbox name='abdomen_hepatomegaly'  ><span class=text>Hepatomegaly</span><br>
<span class=bold>Rectal</span><br>
<span class=text>Prostate: </span><input type=entry name="rectal_prostate" value="" ><br>
<span class=text>Hemmocult: </span><input type=entry name="rectal_hemmocult" value="" ><br>
<input type=checkbox name='rectal_tender'  ><span class=text>Rectal tender</span><br>
<input type=checkbox name='rectal_hemmorrhoids'  ><span class=text>Hemmorrhiods</span><br>
<span class=bold>Genitalia</span><br>
<span class=text>Hernia: </span><input type=entry name="genitalia_hernia" value="" ><br>
<span class=text>Ext. Vagina: </span><input type=entry name="genitalia_ext_vagina" value="" ><br>
<span class=text>Male: </span><input type=entry name="genitalia_male" value="" ><br>
<span class=text>Speculum: </span><input type=entry name="speculum" value="" ><br>
<span class=text>Palpation: </span><input type=entry name="palpation" value="" ><br>
<span class=text>Uterus: </span><input type=entry name="uterus" value="" ><br>
<span class=text>Adnexa Right: </span><input type=entry name="adnexa_right" value="" ><br>
<span class=text>Adnexa Left: </span><input type=entry name="adnexa_left" value="" ><br>
<span class=bold>Neuro Exam</span><br>
<span class=text>CN's: </span><input type=entry name="neuro_exam_cns" value="" ><br>
<span class=text>Oriented: </span><input type=entry name="neuro_exam_oriented" value="" ><br>
<span class=text>Confused: </span><input type=entry name="neuro_exam_confused" value="" ><br>
<span class=text>MMSE: </span><input type=entry name="neuro_exam_mmse" value="" ><br>
<span class=text>Muscle Strength: </span><input type=entry name="muscle_strength" value="" ><br>
<span class=bold>Reflexes</span><br>
<span class=text>RUE: </span><input type=entry name="reflexes_rue" value="" ><br>
<span class=text>RLE: </span><input type=entry name="reflexes_rle" value="" ><br>
<span class=text>LUE: </span><input type=entry name="reflexes_lue" value="" ><br>
<span class=text>LLE: </span><input type=entry name="reflexes_lle" value="" ><br>
<span class=text>Vibration: </span><input type=entry name="vibration" value="" ><br>
<span class=text>Sensation: </span><input type=entry name="sensation" value="" ><br>
<span class=text>Babinski: </span><input type=entry name="babinski" value="" ><br>
<span class=text>Edema: </span><input type=entry name="edema" value="" ><br>
<span class=text>Varicosities: </span><input type=entry name="varicosities" value="" ><br>
<span class=text>Nails: </span><input type=entry name="nails" value="" ><br>
<span class=bold>Joints</span><br>
<span class=text>Neck: </span><input type=entry name="joints_neck" value="" ><br>
<span class=text>Shoulder Right: </span><input type=entry name="joints_shoulder_right" value="" ><br>
<span class=text>Shoulder Left: </span><input type=entry name="joints_sholder_left" value="" ><br>
<span class=text>Elbow Right: </span><input type=entry name="joints_elbow_right" value="" ><br>
<span class=text>Elbow Left: </span><input type=entry name="joints_elbow_left" value="" ><br>
<span class=text>Wrist Right: </span><input type=entry name="joints_wrist_right" value="" ><br>
<span class=text>Wrist Left: </span><input type=entry name="joints_wrist_left" value="" ><br>
<span class=text>Hand Right: </span><input type=entry name="joints_hand_right" value="" ><br>
<span class=text>Hand Left: </span><input type=entry name="joints_hand_left" value="" ><br>
<span class=text>Hip Right: </span><input type=entry name="joints_hip_right" value="" ><br>
<span class=text>Hip Left: </span><input type=entry name="joints_hip_left" value="" ><br>
<span class=text>Back Right: </span><input type=entry name="joints_back_right" value="" ><br>
<span class=text>Back Left: </span><input type=entry name="joints_back_left" value="" ><br>
<span class=text>SLR Right: </span><input type=entry name="joints_slr_right" value="" ><br>
<span class=text>SLR Left: </span><input type=entry name="joints_slr_left" value="" ><br>
<span class=text>Knee Right: </span><input type=entry name="joints_knee_right" value="" ><br>
<span class=text>Knee Left: </span><input type=entry name="joints_knee_left" value="" ><br>
<span class=text>Ankle Right: </span><input type=entry name="joints_ankle_right" value="" ><br>
<span class=text>Ankle Left: </span><input type=entry name="joints_ankle_left" value="" ><br>
<span class=text>Foot Right: </span><input type=entry name="joints_foot_right" value="" ><br>
<span class=text>Foot Left: </span><input type=entry name="joints_foot_left" value="" ><br>
<span class=bold>Conclusions</span><br>
<span class=text>Impression: </span><br><textarea cols=40 rows=8 wrap=virtual name="conclusions_impression" ></textarea><br>
<span class=text>Discussion: </span><br><textarea cols=40 rows=8 wrap=virtual name="conclusions_discussion" ></textarea><br>
<span class=text>Treatment: </span><br><textarea cols=40 rows=8 wrap=virtual name="conclusions_treatment" ></textarea><br>
<input type=checkbox name='conclusions_breast_self_exam'  ><span class=text>Breast Self Exam</span><br>
<input type=checkbox name='conclusions_flex_sig_colonoscopy'  ><span class=text>Flex Sig/Colonoscopy</span><br>
<input type=checkbox name='conclusions_mammography'  ><span class=text>Mammography</span><br>
<input type=checkbox name='conclusions_cholesterol_teaching'  ><span class=text>Cholesterol Teaching</span><br>
<input type=checkbox name='conclusions_advance_directive'  ><span class=text>Advance Directive</span><br>
<span class=text>Follow-up: </span><br><textarea cols=40 rows=8 wrap=virtual name="follow_up" ></textarea><br>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
