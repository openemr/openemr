<?php

include_once("../../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['srcdir'].'/calendar.inc');
include_once($GLOBALS['srcdir'].'/lists.inc');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/ee1form.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/rto.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtform.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/lifestyle.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/favorites.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
include_once($GLOBALS['fileroot']."/interface/forms/$frmdir/report.php");
include_once($GLOBALS['fileroot']."/interface/forms/$frmdir/referral.php");
if(is_file($GLOBALS['srcdir'].'/patient_tracker.inc.php')) 
include_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');


function deleteList() {
	global $encounter, $pid, $bar_id, $formData, $copy_action;

	if($copy_action != 'replace') {
		return true;
	}

	if(!isset($encounter) || $encounter == '') {
		return true;
	}

	if(!isset($pid) || $pid == '') {
		return true;
	}

	if($bar_id == 'img') {
		$img=GetImageHistory($pid, $encounter);
		foreach ($img as $k => $item) {
			if(isset($item['id'])) {
				//DeleteListItem($pid, $item['id'], $item['img_num_links'],'wmt_img_history');
				UnlinkListEntry($pid,$item['id'],$encounter,'wmt_img_history');
				$formData['img']['deleted_list']['img_id'][] = $item['id'];
			}
		}
	}

	if($bar_id == 'all' || $bar_id == 'global') {
		// $allergies=GetList($pid, 'allergy', $encounter);
		// foreach ($allergies as $k => $item) {
		// 	DeleteListItem($pid, $item['id'], $item['num_links'],'allergy');
		// 	$formData['all']['deleted_list']['all_id'][] = $item['id'];
		// }
	}

	if($bar_id == 'ps') {
		$surg=GetList($pid, 'surgery', $encounter);
		foreach ($surg as $k => $item) {
			//DeleteListItem($pid, $item['id'], $item['num_links'],'surgery');
			UnlinkListEntry($pid,$item['id'],$encounter,'surgery');
			$formData['ps']['deleted_list']['ps_id'][] = $item['id'];
		}
	}

	if($bar_id == 'hosp') {
		$hosp=GetList($pid, 'hospitalization', $encounter);
		foreach ($hosp as $k => $item) {
			//DeleteListItem($pid, $item['id'], $item['num_links'], 'hospitalization');
			UnlinkListEntry($pid,$item['id'],$encounter,'hospitalization');
			$formData['hosp']['deleted_list']['hosp_id'][] = $item['id'];
		}
	}

	if($bar_id == 'pmh') {
		$pmh=GetMedicalHistory($pid, $encounter);
		foreach ($pmh as $k => $item) {
			//DeleteListItem($pid,$item['id'], $item['pmh_num_links'],'wmt_med_history');
			UnlinkListEntry($pid,$item['id'],$encounter,'wmt_med_history');
			$formData['pmh']['deleted_list']['pmh_id'][] = $item['id'];
		}
	}

	if($bar_id == 'fh') {
		$fh=GetFamilyHistory($pid,$encounter);
		foreach ($fh as $k => $item) {
			//DeleteListItem($pid,$item['id'],$item['fh_num_links'],'wmt_family_history');
			UnlinkListEntry($pid,$item['id'],$encounter,'wmt_family_history');
			$formData['fh']['deleted_list']['fh_id'][] = $item['id'];
		}
	}

	if($bar_id == 'diag' || $bar_id == 'global') {
		$diag=GetProblemsWithDiags($pid, 'encounter', $encounter);
		foreach ($diag as $k => $item) {
			//DeleteListItem($pid,$item['id'],'','medical_problem');
			UnLinkDiagnosis($pid,$item['id'],$encounter);
			$formData['diag']['deleted_list']['dg_id'][] = $item['id'];
		}
	}
}

function loadROSAndChecks(&$dt, $module, $fid, $fname) {
	global $ros_options, $wmt_ros, $rs;
	global $hd_chks, $mouth_chks, $thrt_chks, $hrn_chks, $ms_chks;

	loadFormROS($module, $fid, $fname);

	// EXPLODE THE MULTIPLE CHOICE CHECK-BOX LISTS INTO SEPARATE ITEMS
	if(!isset($dt{'ge_hd_chks'})) $dt{'ge_hd_chks'} = '';
	$hd_chks = explode('|', $dt{'ge_hd_chks'});
	if(!isset($dt{'ge_mouth_chks'})) $dt{'ge_mouth_chks'} = '';
	$mouth_chks = explode('|', $dt{'ge_mouth_chks'});
	if(!isset($dt{'ge_thrt_chks'})) $dt{'ge_thrt_chks'} = '';
	$thrt_chks = explode('|', $dt{'ge_thrt_chks'});
	if(!isset($dt{'ge_gi_her_dtl'})) $dt{'ge_gi_her_dtl'} = '';
	$hrn_chks = explode('|', $dt{'ge_gi_her_dtl'});
	if(!isset($dt{'ge_ms_chks'})) $dt{'ge_ms_chks'} = '';
	$ms_chks = explode('|', $dt{'ge_ms_chks'});
}

function isAssoc(array $arr){
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

$encounter_id = isset($_REQUEST['encounter_id']) ? $_REQUEST['encounter_id'] : "";
$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : "";
$tmp_encounter = $encounter;
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : "";
$bar_id = isset($_REQUEST['bar_id']) ? $_REQUEST['bar_id'] : "";
$frmn = isset($_REQUEST['frmn']) ? $_REQUEST['frmn'] : "";
$frmdir = isset($_REQUEST['frmdir']) ? $_REQUEST['frmdir'] : "ext_exam2";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$e_id = isset($_REQUEST['e_id']) ? $_REQUEST['e_id'] : "";
$copy_action = isset($_REQUEST['c_action']) ? $_REQUEST['c_action'] : "append";

$formData = array();

deleteList();

$row = sqlQuery("SELECT * FROM list_options WHERE list_id=? AND " .
	"option_id LIKE '%ros%' AND seq >= 0", array($frmdir.'_modules'));
$ros_module = $row{'option_id'};

if($ros_module == 'ros2' || $ros_module == 'ent_ros') {
	include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
	$ros_options = LoadList('Ext_ROS_Keys', 'active');
	$ros_unused = LoadList('Ext_ROS_Keys', 'inactive');
}

$fres=sqlStatement("SELECT fr.encounter, fr.pid, fr.pid, ex2.*  FROM forms As fr ".
			"LEFT JOIN form_ext_exam2 AS ex2 ON fr.form_id = ex2.id ".
			"WHERE fr.encounter = ? AND fr.pid = ? AND fr.formDir = ? ", array($encounter_id, $pid, 'ext_exam2'));
$dt = sqlFetchArray($fres);

loadROSAndChecks($dt, $ros_module, $e_id, $frmn);

loadFormComments($dt, 'general_exam2', $e_id, $frmn, $pid);
//$mode_exists = GetListTitleByKey($form_mode, 'Exam_Form_Visit_Types');
$diag=GetProblemsWithDiags($pid, 'encounter',$encounter_id);
$img=GetImageHistory($pid, $encounter_id);
$surg=GetList($pid, 'surgery', $encounter_id);
$hosp=GetList($pid, 'hospitalization', $encounter_id);
$inj=GetList($pid, 'wmt_inj_history', $encounter_id);
$fh=GetFamilyHistory($pid,$encounter_id);
$imm=GetAllImmunizationsbyPatient($pid,$encounter_id);
$pmh=GetMedicalHistory($pid, $encounter_id);
$allergies=GetList($pid, 'allergy', $encounter_id);
$patient = wmtPatData::getPidPatient($pid);
$pat_sex = strtolower(substr($patient->sex,0,1));
if($use_meds_not_rx) { 
	$meds = GetList($pid, 'medication', $encounter_id);
	$med_hist = GetList($pid, 'med_history', $encounter_id);
} else {
	$meds = getLinkedPrescriptionsByPatient($pid, $encounter_id, '= 1');
	$med_hist = getLinkedPrescriptionsByPatient($pid, $encounter_id, '< 1');
}
$save_style=$GLOBALS['rootdir']."/forms/$frmdir/new.php?mode=save&wrap=$wrap_mode&id=$id".
			"&enc=$encounter&pid=$pid";
if($use_tasks) {
	$tasks = wmtFormTasks::getFormTasks($pid, $frmdir, $id, 'DESC', 'task');
}

// Get the most recent dashboard form
$db = wmtDashboard::getPidDashboard($pid);
foreach($db as $key => $val) {
	if($key == 'id' || $key == 'pid' || $key == 'db_form_dt') continue;
	// Don't get vitals from here!!
	if($key == 'db_height' || $key == 'db_weight' || $key == 'db_BMI' ||
		$key == 'db_BMI_status' || $key == 'db_bps' || $key == 'db_bpd' ||
		$key == 'db_pulse' || $key == 'db_cc' || $key == 'db_hpi') continue;
	$dt[$key] = $val;
	// if(substr($key,0,3) == 'db_') $dt['ee1_'.substr($key,3)] = $val;
}

$fyi= wmtFYI::getPidFYI($pid);
// RPG - THIS IS THE FYI FIX AREA
foreach($fyi as $key => $val) {
	if(substr($key,-3) == '_nt') $dt[$key] = $val;
}

//Ortho Exam;
unset($local_fields);
$local_fields = array();
$flds = sqlListFields('form_ortho_exam');
$flds = array_slice($flds,14);
foreach($flds as $key => $fld) {
	$local_fields[] = $fld;
}
$encounter = $encounter_id;
$field_prefix = 'oe_';
include(FORM_BRICKS . 'module_setup.inc.php');
$this_module = "ortho_exam";
$this_table = 'form_' . $this_module;
$loader_use_prefix = TRUE;
include(FORM_BRICKS . 'module_loader.inc.php');
//var_dump($dt['oe_dictate']);
$loader_use_prefix = FALSE;
$encounter = $tmp_encounter;
$field_prefix = '';

$oe_sections = array('ge', 'post', 'neu', 'orth', 'palp', 
	'rom', 'msc', 'tnd', 'myo');

foreach($oe_sections as $s) {
	if(!isset($dt['tmp_oe_'.$s.'_disp'])) $dt['tmp_oe_'.$s.'_disp'] = 'block';
	if(!isset($dt['tmp_oe_'.$s.'_button_disp'])) $dt['tmp_oe_'.$s.'_button_disp'] = 'block';
}

$multi_labels = array('post_api', 'neu_sense', 'orth_cerv', 'orth_lum', 
	'orth_sac', 'orth_hip', 'orth_shou', 'orth_elbow', 'orth_wrist', 'orth_knee',
	'orth_ankle', 'msc_derm', 'msc_neck', 'msc_scm', 'msc_inter', 'msc_fing_ex', 
	'msc_wrist_ex', 'msc_tri', 'msc_cuff', 'msc_delt', 'msc_lat', 'msc_fing_fl', 
	'msc_wrist_fl', 'msc_bi', 'msc_hip', 'msc_pec', 'msc_psoas', 'msc_tfl',
	'msc_glut_med', 'msc_quad', 'msc_glut_max', 'msc_ham', 'msc_tib', 'msc_per', 
	'msc_ext', 'msc_gastroc', 'tnd_pat', 'tnd_ham', 'tnd_ach', 'tnd_bi', 
	'tnd_tri', 'tnd_rad');

//Process After Save
ext_process_after_fetch($pid);
ext_general_exam2_module($pid);

$fieldList = array(
	'cc' => array(
		'cc'
	),
	'hpi' => array(
		'hpi'
	),
	'img' => array(
		'fyi_img_nt'
	),
	'sh' => array(
		'db_smoking_status',
		'db_smoking',
		'db_smoking_dt',
		'db_alcohol',
		'db_alcohol_note',
		'db_alcohol_dt',
		'db_drug_use',
		'db_drug_note',
		'db_drug_dt',
		'pat_sex',
		'db_sex_orient',
		'db_sex_orient_nt',
		'db_sex_active',
		'db_sex_act_nt',
		'db_sex_nt',
		'db_sex_sti',
		'db_sex_sti_nt',
		'fyi_sh_nt'
	),
	'all' => array(
		'fyi_allergy_nt'
	),
	'ps' => array(
		'ps_begdate',
		'ps_title',
		'ps_hospitalized',
		'ps_comments',
		'ps_referredby',
		'fyi_surg_nt'
	),
	'meds' => array(
		'fyi_med_nt'
	),
	'med_hist' => array(
		'fyi_medhist_nt'
	),
	'imm' => array(
		'fyi_imm_nt'
	),
	'well_full' => array(
		'fyi_well_nt'
	),
	'hosp' => array(
		'fyi_admissions_nt'
	),
	'pmh' => array(
		'fyi_pmh_nt'
	),
	'fh' => array(
		'db_fh_non_contrib',
		'db_fh_adopted',
		'fyi_fh_nt'
	),
	'review_nt' => array(
		'review_nt'
	),
	'general_exam2' => array(
	  0 => 'ge_dictate',
	  1 => 'tmp_ge_gen',
	  2 => 'ge_gen_norm_exam',
	  3 => 'tmp_ge_gen_button_disp',
	  4 => 'ge_gen_norm',
	  5 => 'ge_gen_norm_nt',
	  6 => 'ge_gen_dev',
	  7 => 'ge_gen_dev_nt',
	  8 => 'ge_gen_groom',
	  9 => 'ge_gen_groom_nt',
	  10 => 'ge_gen_dis',
	  11 => 'ge_gen_dis_nt',
	  12 => 'ge_gen_jaun',
	  13 => 'ge_gen_jaun_nt',
	  14 => 'ge_gen_waste',
	  15 => 'ge_gen_waste_nt',
	  16 => 'ge_gen_sleep',
	  17 => 'ge_gen_sleep_nt',
	  18 => 'ge_gen_nt',
	  19 => 'tmp_ge_head',
	  20 => 'ge_hd_norm_exam',
	  21 => 'tmp_ge_head_button_disp',
	  22 => 'ge_hd_atra',
	  23 => 'ge_hd_atra_nt',
	  24 => 'ge_hd_norm',
	  25 => 'ge_hd_norm_nt',
	  26 => 'ge_hd_feat',
	  27 => 'ge_hd_feat_nt',
	  28 => 'ge_hd_ant',
	  29 => 'ge_hd_ant_nt',
	  30 => 'tmp_ge_hd_head_mid-line',
	  31 => 'tmp_ge_hd_deformity',
	  32 => 'tmp_ge_hd_lesion',
	  33 => 'tmp_ge_hd_flaky_scalp',
	  34 => 'tmp_ge_hd_nits_visible',
	  35 => 'tmp_ge_hd_edema',
	  36 => 'tmp_ge_hd_erythema',
	  37 => 'ge_hd_nt',
	  38 => 'tmp_ge_eyes',
	  39 => 'ge_eye_norm_exam',
	  40 => 'tmp_ge_eyes_button_disp',
	  41 => 'ge_eye_pupil',
	  42 => 'ge_eye_hem',
	  43 => 'ge_eye_hem_nt',
	  44 => 'ge_eye_exu',
	  45 => 'ge_eye_exu_nt',
	  46 => 'ge_eye_av',
	  47 => 'ge_eye_av_nt',
	  48 => 'ge_eye_pap',
	  49 => 'ge_eye_pap_nt',
	  50 => 'ge_eyer_norm',
	  51 => 'ge_eyer_norm_nt',
	  52 => 'ge_eyer_exo',
	  53 => 'ge_eyer_exo_nt',
	  54 => 'ge_eyer_stare',
	  55 => 'ge_eyer_stare_nt',
	  56 => 'ge_eyer_lag',
	  57 => 'ge_eyer_lag_nt',
	  58 => 'ge_eyer_scleral',
	  59 => 'ge_eyer_scleral_nt',
	  60 => 'ge_eyer_eomi',
	  61 => 'ge_eyer_eomi_nt',
	  62 => 'ge_eyer_perrl',
	  63 => 'ge_eyer_perrl_nt',
	  64 => 'ge_eyel_norm',
	  65 => 'ge_eyel_norm_nt',
	  66 => 'ge_eyel_exo',
	  67 => 'ge_eyel_exo_nt',
	  68 => 'ge_eyel_stare',
	  69 => 'ge_eyel_stare_nt',
	  70 => 'ge_eyel_lag',
	  71 => 'ge_eyel_lag_nt',
	  72 => 'ge_eyel_scleral',
	  73 => 'ge_eyel_scleral_nt',
	  74 => 'ge_eyel_eomi',
	  75 => 'ge_eyel_eomi_nt',
	  76 => 'ge_eyel_perrl',
	  77 => 'ge_eyel_perrl_nt',
	  78 => 'ge_eye_nt',
	  79 => 'tmp_ge_ears',
	  80 => 'ge_ear_norm_exam',
	  81 => 'tmp_ge_ears_button_disp',
	  82 => 'ge_earr_tym_nt',
	  83 => 'ge_earr_clear',
	  84 => 'ge_earr_clear_nt',
	  85 => 'ge_earr_perf',
	  86 => 'ge_earr_perf_nt',
	  87 => 'ge_earr_ret',
	  88 => 'ge_earr_ret_nt',
	  89 => 'ge_earr_bulge',
	  90 => 'ge_earr_bulge_nt',
	  91 => 'ge_earr_pus',
	  92 => 'ge_earr_pus_nt',
	  93 => 'ge_earr_ceru',
	  94 => 'ge_earr_ceru_nt',
	  95 => 'ge_earl_tym_nt',
	  96 => 'ge_earl_clear',
	  97 => 'ge_earl_clear_nt',
	  98 => 'ge_earl_perf',
	  99 => 'ge_earl_perf_nt',
	  100 => 'ge_earl_ret',
	  101 => 'ge_earl_ret_nt',
	  102 => 'ge_earl_bulge',
	  103 => 'ge_earl_bulge_nt',
	  104 => 'ge_earl_pus',
	  105 => 'ge_earl_pus_nt',
	  106 => 'ge_earl_ceru',
	  107 => 'ge_earl_ceru_nt',
	  108 => 'ge_ear_nt',
	  109 => 'tmp_ge_nose',
	  110 => 'ge_nose_norm_exam',
	  111 => 'tmp_ge_nose_button_disp',
	  112 => 'ge_nose_ery',
	  113 => 'ge_nose_ery_nt',
	  114 => 'ge_nose_swell',
	  115 => 'ge_nose_swell_nt',
	  116 => 'ge_nose_pall',
	  117 => 'ge_nose_pall_nt',
	  118 => 'ge_nose_polps',
	  119 => 'ge_nose_polps_nt',
	  120 => 'ge_nose_sept',
	  121 => 'ge_nose_sept_nt',
	  122 => 'ge_nose_nt',
	  123 => 'tmp_ge_mouth',
	  124 => 'ge_mouth_norm_exam',
	  125 => 'tmp_ge_mouth_button_disp',
	  126 => 'ge_mouth_moist',
	  127 => 'ge_mouth_moist_nt',
	  128 => 'ge_mouth_gm_red',
	  129 => 'ge_mouth_gm_red_nt',
	  130 => 'ge_mouth_gm_swell',
	  131 => 'ge_mouth_gm_swell_nt',
	  132 => 'ge_mouth_gm_bld',
	  133 => 'ge_mouth_gm_bld_nt',
	  134 => 'ge_mouth_th_car',
	  135 => 'ge_mouth_th_car_nt',
	  136 => 'ge_mouth_th_pd',
	  137 => 'ge_mouth_th_pd_nt',
	  138 => 'ge_mouth_th_er',
	  139 => 'ge_mouth_th_er_nt',
	  140 => 'tmp_ge_mouth_sores',
	  141 => 'tmp_ge_mouth_cracked_dry_lips',
	  142 => 'tmp_ge_mouth_cheilosis',
	  143 => 'tmp_ge_mouth_perioral_cyanosis',
	  144 => 'ge_mouth_nt',
	  145 => 'tmp_ge_throat',
	  146 => 'ge_thrt_norm_exam',
	  147 => 'tmp_ge_throat_button_disp',
	  148 => 'ge_thrt_ery',
	  149 => 'ge_thrt_ery_nt',
	  150 => 'ge_thrt_exu',
	  151 => 'ge_thrt_exu_nt',
	  152 => 'ge_thrt_ton_exu',
	  153 => 'ge_thrt_ton_exu_nt',
	  154 => 'ge_thrt_ton_en',
	  155 => 'ge_thrt_ton_en_nt',
	  156 => 'ge_thrt_uvu_mid',
	  157 => 'ge_thrt_uvu_mid_nt',
	  158 => 'ge_thrt_uvu_swell',
	  159 => 'ge_thrt_uvu_swell_nt',
	  160 => 'ge_thrt_uvu_dev',
	  161 => 'ge_thrt_uvu_dev_nt',
	  162 => 'ge_thrt_pal_swell',
	  163 => 'ge_thrt_pal_swell_nt',
	  164 => 'ge_thrt_pal_pet',
	  165 => 'ge_thrt_pal_pet_nt',
	  166 => 'tmp_ge_thrt_peritonsillar_abscess',
	  167 => 'tmp_ge_thrt_cobblestoning',
	  168 => 'tmp_ge_thrt_mucous_visible',
	  169 => 'ge_thrt_nt',
	  170 => 'tmp_ge_neck',
	  171 => 'ge_nk_norm_exam',
	  172 => 'tmp_ge_neck_button_disp',
	  173 => 'ge_nk_sup',
	  174 => 'ge_nk_brit',
	  175 => 'ge_nk_brit_nt',
	  176 => 'ge_nk_jvp',
	  177 => 'ge_nk_lymph',
	  178 => 'ge_nk_trach',
	  179 => 'tmp_ge_thyroid',
	  180 => 'ge_thy_norm_exam',
	  181 => 'tmp_ge_thyroid_button_disp',
	  182 => 'ge_thy_norm',
	  183 => 'ge_thy_norm_nt',
	  184 => 'ge_thy_nod',
	  185 => 'ge_thy_nod_nt',
	  186 => 'ge_thy_brit',
	  187 => 'ge_thy_brit_nt',
	  188 => 'ge_thy_tnd',
	  189 => 'ge_thy_tnd_nt',
	  190 => 'ge_thy_nt',
	  191 => 'tmp_ge_lymph',
	  192 => 'ge_lym_norm_exam',
	  193 => 'tmp_ge_lymph_button_disp',
	  194 => 'ge_lym_cerv',
	  195 => 'ge_lym_cerv_nt',
	  196 => 'ge_lym_sup',
	  197 => 'ge_lym_sup_nt',
	  198 => 'ge_lym_ax',
	  199 => 'ge_lym_ax_nt',
	  200 => 'ge_lym_in',
	  201 => 'ge_lym_in_nt',
	  202 => 'ge_lym_nt',
	  203 => 'tmp_ge_breast',
	  204 => 'ge_br_norm_exam',
	  205 => 'tmp_ge_breast_button_disp',
	  206 => 'ge_brr_axil',
	  207 => 'ge_brr_axil_nt',
	  208 => 'ge_brr_mass',
	  209 => 'ge_brr_mass_nt',
	  210 => 'ge_brr_tan',
	  211 => 'ge_brr_tan_nt',
	  212 => 'ge_brr_chng',
	  213 => 'ge_brr_chng_nt',
	  214 => 'ge_brr_nt',
	  215 => 'ge_nipr_ev',
	  216 => 'ge_nipr_ev_nt',
	  217 => 'ge_nipr_in',
	  218 => 'ge_nipr_in_nt',
	  219 => 'ge_nipr_mass',
	  220 => 'ge_nipr_mass_nt',
	  221 => 'ge_nipr_dis',
	  222 => 'ge_nipr_dis_nt',
	  223 => 'ge_nipr_ret',
	  224 => 'ge_nipr_ret_nt',
	  225 => 'ge_nipr_nt',
	  226 => 'ge_brl_axil',
	  227 => 'ge_brl_axil_nt',
	  228 => 'ge_brl_mass',
	  229 => 'ge_brl_mass_nt',
	  230 => 'ge_brl_tan',
	  231 => 'ge_brl_tan_nt',
	  232 => 'ge_brl_chng',
	  233 => 'ge_brl_chng_nt',
	  234 => 'ge_brl_nt',
	  235 => 'ge_nipl_ev',
	  236 => 'ge_nipl_ev_nt',
	  237 => 'ge_nipl_in',
	  238 => 'ge_nipl_in_nt',
	  239 => 'ge_nipl_mass',
	  240 => 'ge_nipl_mass_nt',
	  241 => 'ge_nipl_dis',
	  242 => 'ge_nipl_dis_nt',
	  243 => 'ge_nipl_ret',
	  244 => 'ge_nipl_ret_nt',
	  245 => 'ge_nipl_nt',
	  246 => 'tmp_ge_cardio',
	  247 => 'ge_cr_norm_exam',
	  248 => 'tmp_ge_cardio_button_disp',
	  249 => 'ge_cr_norm',
	  250 => 'ge_cr_norm_nt',
	  251 => 'ge_cr_mur',
	  252 => 'ge_cr_mur_dtl',
	  253 => 'ge_cr_mur_nt',
	  254 => 'ge_cr_gall',
	  255 => 'ge_cr_gall_nt',
	  256 => 'ge_cr_click',
	  257 => 'ge_cr_click_nt',
	  258 => 'ge_cr_rubs',
	  259 => 'ge_cr_rubs_nt',
	  260 => 'ge_cr_extra',
	  261 => 'ge_cr_extra_nt',
	  262 => 'ge_cr_pmi',
	  263 => 'ge_cr_pmi_nt',
	  264 => 'ge_cr_nt',
	  265 => 'tmp_ge_pulmo',
	  266 => 'ge_pul_norm_exam',
	  267 => 'tmp_ge_pulmo_button_disp',
	  268 => 'ge_pul_clear',
	  269 => 'ge_pul_rales',
	  270 => 'ge_pul_rales_nt',
	  271 => 'ge_pul_whz',
	  272 => 'ge_pul_ron',
	  273 => 'ge_pul_dec',
	  274 => 'ge_pul_crack',
	  275 => 'tmp_ge_gastro',
	  276 => 'ge_gi_norm_exam',
	  277 => 'tmp_ge_gastro_button_disp',
	  278 => 'ge_gi_soft',
	  279 => 'ge_gi_soft_nt',
	  280 => 'ge_gi_tend',
	  281 => 'ge_gi_tend_loc',
	  282 => 'ge_gi_tend_nt',
	  283 => 'ge_gi_dis',
	  284 => 'ge_gi_dis_nt',
	  285 => 'ge_gi_scar',
	  286 => 'ge_gi_scar_nt',
	  287 => 'ge_gi_asc',
	  288 => 'ge_gi_asc_nt',
	  289 => 'ge_gi_pnt',
	  290 => 'ge_gi_pnt_nt',
	  291 => 'ge_gi_grd',
	  292 => 'ge_gi_grd_nt',
	  293 => 'ge_gi_reb',
	  294 => 'ge_gi_reb_nt',
	  295 => 'ge_gi_mass',
	  296 => 'ge_gi_mass_nt',
	  297 => 'ge_gi_hern',
	  298 => 'ge_gi_hern_nt',
	  299 => 'tmp_ge_gi_her_ventral',
	  300 => 'tmp_ge_gi_her_incisional',
	  301 => 'tmp_ge_gi_her_umbilical',
	  302 => 'tmp_ge_gi_her_inguinal',
	  303 => 'ge_gi_bowel',
	  304 => 'ge_gi_bwl_dtl',
	  305 => 'ge_gi_bowel_nt',
	  306 => 'ge_gi_hepa',
	  307 => 'ge_gi_hepa_nt',
	  308 => 'ge_gi_spleno',
	  309 => 'ge_gi_spleno_nt',
	  310 => 'ge_gi_nt',
	  311 => 'tmp_ge_neuro',
	  312 => 'ge_neu_norm_exam',
	  313 => 'tmp_ge_neuro_button_disp',
	  314 => 'ge_neu_ao',
	  315 => 'ge_neu_ao_nt',
	  316 => 'ge_neu_cn',
	  317 => 'ge_neu_cn_nt',
	  318 => 'ge_neu_bicr',
	  319 => 'ge_neu_bicr_nt',
	  320 => 'ge_neu_bicl',
	  321 => 'ge_neu_bicl_nt',
	  322 => 'ge_neu_trir',
	  323 => 'ge_neu_trir_nt',
	  324 => 'ge_neu_tril',
	  325 => 'ge_neu_tril_nt',
	  326 => 'ge_neu_brar',
	  327 => 'ge_neu_brar_nt',
	  328 => 'ge_neu_bral',
	  329 => 'ge_neu_bral_nt',
	  330 => 'ge_neu_patr',
	  331 => 'ge_neu_patr_nt',
	  332 => 'ge_neu_patl',
	  333 => 'ge_neu_patl_nt',
	  334 => 'ge_neu_achr',
	  335 => 'ge_neu_achr_nt',
	  336 => 'ge_neu_achl',
	  337 => 'ge_neu_achl_nt',
	  338 => 'ge_neu_pup',
	  339 => 'ge_neu_pup_nt',
	  340 => 'ge_neu_plow',
	  341 => 'ge_neu_plow_nt',
	  342 => 'ge_neu_dup',
	  343 => 'ge_neu_dup_nt',
	  344 => 'ge_neu_dlow',
	  345 => 'ge_neu_dlow_nt',
	  346 => 'ge_neu_tn',
	  347 => 'ge_neu_tn_nt',
	  348 => 'ge_neu_cc_norm',
	  349 => 'ge_neu_cc_fn',
	  350 => 'ge_neu_cc_fn_nt',
	  351 => 'ge_neu_cc_hs',
	  352 => 'ge_neu_cc_hs_nt',
	  353 => 'ge_neu_cc_ra',
	  354 => 'ge_neu_cc_ra_nt',
	  355 => 'ge_neu_cc_rm',
	  356 => 'ge_neu_cc_rm_nt',
	  357 => 'ge_neu_cc_pd',
	  358 => 'ge_neu_cc_pd_nt',
	  359 => 'ge_neu_sns_chc',
	  360 => 'ge_neu_sns_chc_nt',
	  361 => 'ge_neu_sense',
	  362 => 'tmp_ge_musc',
	  363 => 'ge_ms_norm_exam',
	  364 => 'tmp_ge_musc_button_disp',
	  365 => 'ge_ms_mass',
	  366 => 'ge_ms_mass_nt',
	  367 => 'ge_ms_tnd',
	  368 => 'ge_ms_tnd_nt',
	  369 => 'ge_ms_scl',
	  370 => 'ge_ms_scl_nt',
	  371 => 'ge_ms_cval',
	  372 => 'ge_ms_cval_nt',
	  373 => 'ge_ms_cvar',
	  374 => 'ge_ms_cvar_nt',
	  375 => 'ge_ms_lim',
	  376 => 'ge_ms_lim_nt',
	  377 => 'ge_ms_def',
	  378 => 'ge_ms_def_nt',
	  379 => 'ge_ms_full',
	  380 => 'ge_ms_full_nt',
	  381 => 'ge_ms_gait',
	  382 => 'ge_ms_gait_nt',
	  383 => 'tmp_ge_ms_wheelchair',
	  384 => 'tmp_ge_ms_walker',
	  385 => 'tmp_ge_ms_prosthetics_/_orthotics',
	  386 => 'ge_ms_norm',
	  387 => 'ge_ms_nt',
	  388 => 'tmp_ge_ext',
	  389 => 'ge_ext_norm_exam',
	  390 => 'tmp_ge_ext_button_disp',
	  391 => 'ge_ext_edema',
	  392 => 'ge_ext_edema_chc',
	  393 => 'ge_ext_edema_nt',
	  394 => 'ge_ext_pls_rad',
	  395 => 'ge_ext_pls_dors',
	  396 => 'ge_ext_pls_post',
	  397 => 'ge_ext_pls_pop',
	  398 => 'ge_ext_pls_fem',
	  399 => 'ge_ext_refill',
	  400 => 'ge_ext_club',
	  401 => 'ge_ext_club_nt',
	  402 => 'ge_ext_cyan',
	  403 => 'ge_ext_cyan_nt',
	  404 => 'ge_ext_nt',
	  405 => 'tmp_ge_dia',
	  406 => 'ge_db_norm_exam',
	  407 => 'tmp_ge_dia_button_disp',
	  408 => 'ge_db_prop',
	  409 => 'ge_db_prop_nt',
	  410 => 'ge_db_vib',
	  411 => 'ge_db_vib_nt',
	  412 => 'ge_db_sens',
	  413 => 'ge_db_sens_nt',
	  414 => 'ge_db_nt',
	  415 => 'tmp_ge_test',
	  416 => 'ge_te_norm_exam',
	  417 => 'tmp_ge_test_button_disp',
	  418 => 'ge_te_cir',
	  419 => 'ge_te_cir_nt',
	  420 => 'ge_te_les',
	  421 => 'ge_te_les_nt',
	  422 => 'ge_te_dis',
	  423 => 'ge_te_dis_nt',
	  424 => 'ge_te_size',
	  425 => 'ge_te_size_nt',
	  426 => 'ge_te_palp',
	  427 => 'ge_te_palp_nt',
	  428 => 'ge_te_mass',
	  429 => 'ge_te_mass_nt',
	  430 => 'ge_te_tend',
	  431 => 'ge_te_tend_nt',
	  432 => 'ge_te_ery',
	  433 => 'ge_te_ery_nt',
	  434 => 'ge_te_nt',
	  435 => 'tmp_ge_rectal',
	  436 => 'ge_rc_norm_exam',
	  437 => 'tmp_ge_rectal_button_disp',
	  438 => 'ge_rc_tone',
	  439 => 'ge_rc_tone_nt',
	  440 => 'ge_rc_ext',
	  441 => 'ge_rc_ext_nt',
	  442 => 'ge_rc_pro',
	  443 => 'ge_rc_pro_nt',
	  444 => 'ge_rc_bog',
	  445 => 'ge_rc_bog_nt',
	  446 => 'ge_rc_hard',
	  447 => 'ge_rc_hard_nt',
	  448 => 'ge_rc_mass',
	  449 => 'ge_rc_mass_nt',
	  450 => 'ge_rc_tend',
	  451 => 'ge_rc_tend_nt',
	  452 => 'ge_rc_color',
	  453 => 'ge_rc_color_nt',
	  454 => 'ge_rc_nt',
	  455 => 'tmp_ge_skin',
	  456 => 'ge_skin_norm_exam',
	  457 => 'tmp_ge_skin_button_disp',
	  458 => 'ge_skin_jau',
	  459 => 'ge_skin_jau_nt',
	  460 => 'ge_skin_con',
	  461 => 'ge_skin_con_nt',
	  462 => 'ge_skin_ecc',
	  463 => 'ge_skin_ecc_nt',
	  464 => 'ge_skin_rash',
	  465 => 'ge_skin_rash_nt',
	  466 => 'ge_skin_abs',
	  467 => 'ge_skin_abs_nt',
	  468 => 'ge_skin_lac',
	  469 => 'ge_skin_lac_nt',
	  470 => 'ge_skin_nt',
	  471 => 'tmp_ge_psych',
	  472 => 'ge_psych_norm_exam',
	  473 => 'tmp_ge_psych_button_disp',
	  474 => 'tmp_ge_psych_disp',
	  475 => 'ge_psych_judge',
	  476 => 'ge_psych_judge_nt',
	  477 => 'ge_psych_orient',
	  478 => 'ge_psych_orient_nt',
	  479 => 'ge_psych_memory',
	  480 => 'ge_psych_memory_nt',
	  481 => 'ge_psych_mood',
	  482 => 'ge_psych_mood_nt',
	  483 => 'ge_psych_nt',
	  484 => 'general_exam2_request_data',
	),
	'ortho_exam' => array (
	  0 => 'ortho_exam_id',
	  1 => 'oe_dictate',
	  2 => 'tmp_oe_ge',
	  3 => 'oe_ge_norm_exam',
	  4 => 'tmp_oe_ge_button_disp',
	  5 => 'tmp_oe_ge_disp',
	  6 => 'tmp_oe_ge_button_disp',
	  7 => 'oe_ge_distress',
	  8 => 'oe_ge_nt',
	  9 => 'oe_ge_station',
	  10 => 'oe_ge_gait',
	  11 => 'tmp_oe_post',
	  12 => 'oe_post_norm_exam',
	  13 => 'tmp_oe_post_button_disp',
	  14 => 'tmp_oe_post_disp',
	  15 => 'tmp_oe_post_button_disp',
	  16 => 'oe_post_cr',
	  17 => 'oe_post_nt',
	  18 => 'oe_post_cs',
	  19 => 'oe_post_ct',
	  20 => 'oe_post_es',
	  21 => 'oe_post_al',
	  22 => 'oe_post_eh',
	  23 => 'tmp_oe_neu',
	  24 => 'oe_neu_norm_exam',
	  25 => 'tmp_oe_neu_button_disp',
	  26 => 'tmp_oe_neu_disp',
	  27 => 'tmp_oe_neu_button_disp',
	  28 => 'oe_neu_cn_2_12',
	  29 => 'oe_neu_nt',
	  30 => 'oe_neu_low',
	  31 => 'oe_neu_up',
	  32 => 'oe_neu_prop',
	  33 => 'oe_neu_alert',
	  34 => 'oe_neu_attn',
	  35 => 'oe_neu_fund',
	  36 => 'oe_neu_lang',
	  37 => 'oe_neu_coor_f',
	  38 => 'oe_neu_coor_h',
	  39 => 'oe_neu_mem',
	  40 => 'oe_neu_atr',
	  41 => 'oe_neu_orient',
	  42 => 'tmp_oe_orth',
	  43 => 'oe_orth_norm_exam',
	  44 => 'tmp_oe_orth_button_disp',
	  45 => 'tmp_oe_orth_disp',
	  46 => 'tmp_oe_orth_button_disp',
	  47 => 'oe_orth_nt',
	  48 => 'tmp_oe_palp',
	  49 => 'oe_palp_norm_exam',
	  50 => 'tmp_oe_palp_button_disp',
	  51 => 'tmp_oe_palp_disp',
	  52 => 'tmp_oe_palp_button_disp',
	  53 => 'oe_palp_cerv',
	  54 => 'oe_palp_nt',
	  55 => 'oe_palp_thor',
	  56 => 'oe_palp_lum',
	  57 => 'tmp_oe_rom',
	  58 => 'oe_rom_norm_exam',
	  59 => 'tmp_oe_rom_button_disp',
	  60 => 'tmp_oe_rom_disp',
	  61 => 'tmp_oe_rom_button_disp',
	  62 => 'oe_rom_cerv_fl',
	  63 => 'oe_rom_nt',
	  64 => 'oe_rom_cerv_fl_p',
	  65 => 'oe_rom_cerv_ex',
	  66 => 'oe_rom_cerv_ex_p',
	  67 => 'oe_rom_cerv_rlfl',
	  68 => 'oe_rom_cerv_rlfl_p',
	  69 => 'oe_rom_cerv_llfl',
	  70 => 'oe_rom_cerv_llfl_p',
	  71 => 'oe_rom_cerv_rr',
	  72 => 'oe_rom_cerv_rr_p',
	  73 => 'oe_rom_cerv_lr',
	  74 => 'oe_rom_cerv_lr_p',
	  75 => 'oe_rom_lum_fl',
	  76 => 'oe_rom_lum_fl_p',
	  77 => 'oe_rom_lum_ex',
	  78 => 'oe_rom_lum_ex_p',
	  79 => 'tmp_oe_msc',
	  80 => 'oe_msc_norm_exam',
	  81 => 'tmp_oe_msc_button_disp',
	  82 => 'tmp_oe_msc_disp',
	  83 => 'tmp_oe_msc_button_disp',
	  84 => 'oe_msc_nt',
	  85 => 'tmp_oe_tnd',
	  86 => 'oe_tnd_norm_exam',
	  87 => 'tmp_oe_tnd_button_disp',
	  88 => 'tmp_oe_tnd_disp',
	  89 => 'tmp_oe_tnd_button_disp',
	  90 => 'oe_tnd_nt',
	  91 => 'tmp_oe_myo',
	  92 => 'oe_myo_norm_exam',
	  93 => 'tmp_oe_myo_button_disp',
	  94 => 'tmp_oe_myo_disp',
	  95 => 'tmp_oe_myo_button_disp',
	  96 => 'oe_myo_sub',
	  97 => 'oe_myo_nt',
	  98 => 'oe_myo_cerv',
	  99 => 'oe_myo_scal',
	  100 => 'oe_myo_stern',
	  101 => 'oe_myo_trap',
	  102 => 'oe_myo_lev',
	  103 => 'oe_myo_supra',
	  104 => 'oe_myo_thor',
	  105 => 'oe_myo_mid',
	  106 => 'oe_myo_teres',
	  107 => 'oe_myo_rhom',
	  108 => 'oe_myo_lum',
	  109 => 'oe_myo_quad',
	  110 => 'oe_myo_glut',
	  111 => 'oe_myo_piri',
	  112 => 'oe_myo_psoas',
	  113 => 'ortho_exam_request_data',
	),
	'instruct' => array(
		'instruct'
	),
	'assess' => array(
		'assess'
	),
	'diag' => array(
		'plan'
	)
);

$prevsFieldList = array(
	'img' => array(
		'var' => 'img',
		'fields' => array(
			'img_dt' => 'img_dt',
			'img_type' => 'img_type',
			'img_nt' => 'img_nt'
		)
	),
	'ps' => array(
		'var' => 'surg',
		'fields' => array(
			'ps_begdate' => 'begdate',
			'ps_title' => 'title',
			'ps_hospitalized' => 'extrainfo',
			'ps_comments' => 'comments',
			'ps_referredby' => 'referredby'
		)
	),
	'all' => array(
		'var' => 'allergies',
		'fields' => array(
			'all_begdate' => 'begdate',
			'all_title' => 'title',
			'all_react' => 'reaction',
			'outcome_' => 'outcome',
			'all_comments' => 'comments'
		)
	),
	'imm' => array(
		'var' => 'imm',
		'fields' => array(
			'imm_id' => 'id',
			'imm_cvx_code' => 'cvx_code',
			'imm_administered_date' => 'administered_date',
			'imm_comments' => 'note'
		)
	),
	'hosp' => array(
		'var' => 'hosp',
		'fields' => array(
			'hosp_id' => 'id',
			'hosp_num_links' => 'num_links',
			'hosp_dt' => 'begdate',
			'hosp_type' => 'extrainfo',
			'hosp_why' => 'title',
			'hosp_nt' => 'comments',
			'hosp_why' => 'title'
		)
	),
	'pmh' => array(
		'var' => 'pmh',
		'fields' => array(
			'pmh_id' => 'id',
			'pmh_num_links' => 'num_links',
			'pmh_type' => 'pmh_type',
			'pmh_nt' => 'pmh_nt',
			'pmh_hospitalized' => 'extrainfo'
		)
	),
	'fh' => array(
		'var' => 'fh',
		'fields' => array(
			'fh_id' => 'id',
			'fh_num_links' => 'num_links',
			'fh_who' => 'fh_who',
			'fh_dead' => 'fh_deceased',
			'fh_age' => 'fh_age',
			'fh_age_dead' => 'fh_age_dead',
			'fh_type' => 'fh_type',
			'fh_nt' => 'fh_nt'
		)
	),
	'diag' => array(
		'var' => 'diag',
		'fields' => array(
			'dg_id' => 'id',
			'dg_seq' => 'seq',
			'dg_code' => 'diagnosis',
			'dg_begdt' => 'begdate',
			'dg_enddt' => 'enddate',
			'dg_title' => 'title',
			'dg_plan' => 'comments',
			'dg_goal' => 'plan'
		)
	)
);


$fieldList1 = array(
	'ros2' => array(
		'ros_constitutional_hpi',
		'ros_constitutional_none',
		'ros_ent_hpi',
		'ros_ent_none',
		'ros_respiratory_hpi',
		'ros_respiratory_none',
		'ros_genito_hpi',
		'ros_genito_none',
		'ros_neurologic_hpi',
		'ros_neurologic_none',
		'ros_endocrine_hpi',
		'ros_endocrine_none',
		'ros_eyes_hpi',
		'ros_eyes_none',
		'ros_cardio_hpi',
		'ros_cardio_none',
		'ros_gastro_hpi',
		'ros_gastro_none',
		'ros_muscle_hpi',
		'ros_muscle_none',
		'ros_skin_hpi',
		'ros_skin_none',
		'ros_psychiatric_hpi',
		'ros_psychiatric_none',
		'ros_lymphatic_hpi',
		'ros_lymphatic_none',
		'ros_nt',
	)
);

if(isset($patient)) {
	$formData['sh']['pat_sex'] = $patient->sex;
}

foreach ($fieldList as $section => $sectionItem) {
	foreach ($sectionItem as $fieldKey => $field) {
		$fieldLabel = $field;
		if(isAssoc($sectionItem)) {
			//$fieldLabel = $fieldKey;
		}

		if(isset($dt[$field])) {
			$formData[$section][$fieldLabel] = htmlspecialchars($dt[$field], ENT_QUOTES, '', FALSE);
		}
	}
}

foreach ($fieldList1 as $section => $sectionItem) {
	foreach ($sectionItem as $fieldKey => $field) {
		if(isset($wmt_ros[$field])) {
			$formData[$section][$field] = htmlspecialchars($wmt_ros[$field], ENT_QUOTES, '', FALSE);
		}
	}
}

foreach ($prevsFieldList as $psection => $psectionItem) {
	if(isset($psectionItem['var'])) {
		foreach (${$psectionItem['var']} as $ind => $prev) {
			$titem = array();
			foreach ($psectionItem['fields'] as $fkey => $field) {
				if(isset($prev[$field]) && $field == 'diagnosis') {
					if($pos = strpos($prev[$field],';')) {
						$remainder = trim(substr($prev[$field],($pos+1)));
						$prev_diagnosis = trim(substr($prev[$field],0,$pos));
					}

					if($pos = strpos($prev[$field],':')) {
						// IMPORTANT - KEEP THE TYPE IN A HIDDEN FIELD TO PUT IT BACK
						$code_type = trim(substr($prev[$field],0,$pos));
						$prev_diagnosis = trim(substr($prev[$field],($pos+1)));
					}
					$titem[$fkey] = htmlspecialchars($prev_diagnosis, ENT_QUOTES, '', FALSE);
				} else if(isset($prev[$field])) {
					$titem[$fkey] = htmlspecialchars($prev[$field], ENT_QUOTES, '', FALSE);
				}
			}
			$formData[$psection]['items'][] = $titem;
		}
	}
}

//Ros2 Section
foreach($ros_options as $o) {
	$formData[$section][$o['option_id']] = isset($rs[$o['option_id']]) ? $rs[$o['option_id']] : "";
	$formData[$section][$o['option_id'].'_nt'] = isset($rs[$o['option_id'].'_nt']) ? $rs[$o['option_id'].'_nt'] : "";
}

//Oortho Exam
foreach($multi_labels as $lbl) {
	if(isset($dt['oe_' . $lbl])) {
		$formData['ortho_exam']['multi_list']['oe_' . $lbl] = explode('^|', $dt['oe_' . $lbl]);
	}
}

echo json_encode(array(
	'formData' => $formData
));
