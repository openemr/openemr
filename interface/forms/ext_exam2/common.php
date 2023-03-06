<?php
// Version 1.0 - initial release with all views
// Version 2.0 - Updates as of 7/10/2012, new surgery window, auto-save, 
//               additional fields. Made save.php part of this file to 
//               allow periodic save and 1 line addition of surgeriesr,
//               family history, past medical history, etc.
// echo "<br><br>\n";

use OpenEMR\Core\Header;

function getSeqArray($data = array()) {
	$tmp_array = array();
	foreach ($data as $field => $value) {
		$fieldTA = explode('_', $field);
		$tmpfieldTA = $fieldTA;
		$fieldTAI = end($fieldTA);
		$tmpI = '';
		$tmpfieldName = '';

		if(is_numeric($fieldTAI)) {
			$tmpI = ($fieldTAI - 1);
			array_pop($tmpfieldTA);
			$tmpfieldName = implode("_", $tmpfieldTA);
		}

		if($tmpI >= 0 && !empty($tmpfieldName)) {
			$tmp_array[$tmpI][$tmpfieldName] = $value;
		}
	}

	return $tmp_array;
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

$max_med_hist = false;
$tst = checkSettingMode('wmt::max_med_hist','',$frmdir);
if($tst) $max_med_hist = $tst;
$max_med = false;
$tst = checkSettingMode('wmt::max_med','',$frmdir);
if($tst) $max_med = $tst;
$cc_column = 'cc';
$instruction_column = 'instruct';
$form_event_logging = checkSettingMode('wmt::form_logging','',$frmdir);

$log = "insert FORM $frmdir Form Mode ($form_mode) and Wrap ($wrap_mode) no_dup flag [$no_dup] common.php called";
if($form_event_logging) auditSQLEvent($log, TRUE);

$dt = array();
$rs = array();
$data = array();
$ros = array();
$ros_head = array();
$wmt_ros = array();
$gyn = array();
$allergy = array();
$med_hist = array();
$edata = array();
$fh_yes = array();
$fh_no = array();
$hd_chks = array();
$mouth_chks = array();
$thrt_chks = array();
$hrn_chks = array();
$ms_chks = array();
$proc_choices = array();
$pass_modes = array('save', 'update', 'new');
$fh_options = LoadList('Family_History_Choices','active');
$fh_options_unused = LoadList('Family_History_Choices','inactive');
$num_fh_options = count($fh_options);
$fh_old_style = false;
if(!$num_fh_options) {
	$opt=checkSettingMode('wmt::fh_options','',$frmdir);
	$fh_options = explode('|', $opt);
	$num_fh_options = count($fh_options);
	$fh_old_style = true;
}
$diagnosis_type = 'ICD9';
if(isset($GLOBALS['wmt::default_diag_type'])) 
				$diagnosis_type = $GLOBALS['wmt::default_diag_type'];
$tst = checkSettingMode('wmt::diag_type','',$frmdir);
if($tst) $diagnosis_type = $tst;
$row = sqlQuery("SELECT * FROM list_options WHERE list_id=? AND " .
	"option_id LIKE '%ros%' AND seq >= 0", array($frmdir.'_modules'));
$ros_module = $row{'option_id'};
if($ros_module == 'ros2' || $ros_module == 'ent_ros') {
	include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
	$ros_options = LoadList('Ext_ROS_Keys', 'active');
	$ros_unused = LoadList('Ext_ROS_Keys', 'inactive');
}
$row = sqlQuery("SELECT * FROM list_options WHERE list_id=? AND option_id LIKE '%exam%' AND seq >= 0", array($frmdir.'_modules'));
$exam_module = $row{'option_id'};
$modules = LoadList($frmdir.'_modules','active');
$load = '';

global $dt, $ros_options, $wmt_ros, $rs;
global $hd_chks, $mouth_chks, $thrt_chks, $hrn_chks, $ms_chks;

// INITIALIZE THE DATA ARRAY SO NO PHP ERRORS ON CHECKBOXES
$flds = sqlListFields($frmn);
$flds = array_slice($flds,7);
foreach($flds as $key => $fld) {
	$dt[$fld] = '';
	$data[$fld] = '';
}
if($ros_module == 'ros2' || $ros_module == 'ent_ros') {
	foreach($ros_options as $o) {
		$rs[$o['option_id']] = '';
		$rs[$o['option_id'].'_nt'] = '';
	}
	$flds = sqlListFields('form_wmt_ros');
	$flds = array_slice($flds,7);
	foreach($flds as $key => $fld) { 
		$wmt_ros[$fld]='';
		$ros_parent[$fld]='';
	}
} else {
	$flds = sqlListFields('form_ext_ros');
	$flds = array_slice($flds,7);
	foreach($flds as $key => $fld) { 
		$rs[$fld] = '';
		$ros[$fld]='';
	}
}
$use_tasks=checkSettingMode('wmt::use_tasks','',$frmdir);
if($use_tasks) include_once($GLOBALS['srcdir'].'/wmt-v2/rto.class.php');
$hpi_override = checkSettingMode('wmt::hpi_clear_ros','',$frmdir);
$include_pat_summary = checkSettingMode('wmt::include_pat_summary','',$frmdir);
$noload = checkSettingMode('wmt::noload_form_history','',$frmdir);
$warn_popup = checkSettingMode('wmt::cancel_warning_on','',$frmdir);
$approve_popup = checkSettingMode('wmt::approve_warning_on','',$frmdir);
$use_charges = checkSettingMode('wmt::use_charges','',$frmdir);
$auto_post_pqrs = checkSettingMode('wmt::auto_post_pqrs','',$frmdir);
$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
$use_cessation = checkSettingMode('wmt::smoking_cessation','',$frmdir);
$expanded_sh = checkSettingMode('wmt::sh_expanded','',$frmdir);
$med_add_allowed = checkSettingMode('wmt::db_meds_add');
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$med_list_type = ($use_meds_not_rx) ? 'medication' : 'prescriptions';
$unlink_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
$unlink_all_rx_history = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$unlink_all_allergies = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$unlink_all_meds = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'med');
$client_id = strtolower($GLOBALS['wmt::client_id']);
$approval = canApproveForms();
$dtr_default = '';
$patient = wmtPatData::getPidPatient($pid);
$pat_sex = strtolower(substr($patient->sex,0,1));
$visit = wmtPrintVisit::getEncounter($encounter);
$use_diag_link_checkbox = checkSettingMode('wmt::diag_use_checkbox','',$frmdir);
if(!isset($GLOBALS['wmt::pat_entry_portal'])) 
			$GLOBALS['wmt::pat_entry_portal'] = false;
$portal_enabled = $GLOBALS['wmt::pat_entry_portal'];
if(checkSettingMode('wmt::portal_override','',$frmdir)) $portal_enabled = false;

// LOAD ANY PATIENT-ENTERED DATA (PORTAL) FOR THE DOCTOR TO VIEW
$pat_entries_exist = false;
if($portal_enabled) {
	$pat_entries = array();
	$sql = "SELECT * FROM wmt_portal_data WHERE pid=? AND form_name=?";
	$binds = array($pid, 'portal');
	$fres = sqlStatement($sql, $binds);
	for($iter=0; $frow=sqlFetchArray($fres); $iter++) {
		$pat_entries[$frow{'field_name'}] = $frow;
	}
	if($iter) $pat_entries_exist = true;
}

if($form_mode == 'update') {
  $fres=sqlStatement("SELECT * FROM $frmn WHERE id='$id'");
  if($fres) $dt = sqlFetchArray($fres);
	if(strtolower($dt{'form_complete'}) == 'a') {
		echo "You should not be here, it's not safe!<br/>\n";
		exit;
	}
	loadROSAndChecks($dt, $ros_module, $id, $frmn);
	// loadFormComments($dt, 'general_exam2', $id, $frmn, $pid);

	$img=GetImageHistory($pid, $encounter);
	$surg=GetList($pid, 'surgery', $encounter);
	$hosp=GetList($pid, 'hospitalization', $encounter);
	$inj=GetList($pid, 'wmt_inj_history', $encounter);
	$fh=GetFamilyHistory($pid, $encounter);
	$imm=GetAllImmunizationsbyPatient($pid,$encounter);
	$pmh=GetMedicalHistory($pid, $encounter);
	$allergies=GetList($pid, 'allergy', $encounter);
	if($use_meds_not_rx) { 
		$meds = GetList($pid, 'medication', $encounter);
		$med_hist = GetList($pid, 'med_history', $encounter);
	} else {
 		$meds = getLinkedPrescriptionsByPatient($pid,$encounter,'= 1');
 		$med_hist = getLinkedPrescriptionsByPatient($pid,$encounter,'< 1');
	}
	if($use_tasks) {
		$tasks = wmtFormTasks::getFormTasks($pid, $frmdir, $id, 'DESC', 'task');
	}

} else if($form_mode == 'new') {
	// LOAD ALL THE LIST ENTRIES ON THE INITIAL LOAD
	$img=GetImageHistory($pid);
	$surg=GetList($pid, 'surgery');
	$hosp=GetList($pid, 'hospitalization');
	$inj=GetList($pid, 'wmt_inj_history');
	$fh=GetFamilyHistory($pid);
	$imm=GetAllImmunizationsbyPatient($pid);
	$pmh=GetMedicalHistory($pid);
	$allergies=GetList($pid, 'allergy');
	if($use_meds_not_rx) {
		$meds = GetList($pid, 'medication');
		$med_hist = GetList($pid, 'med_history');
	} else {
  	$meds=getPrescriptionsByPatient($pid, "*", '= 1');
  	$med_hist=getPrescriptionsByPatient($pid, "*", '< 1');
	}
	if(!isset($_GET['type'])) $_GET['type'] = '';
	$default_user_exam = checkSettingMode('wmt::default_user_exam','',$frmdir);
	if(!$default_user_exam) $default_user_exam = 'acute';
	if($_GET['type']) {
  	$old = sqlQuery("SELECT form_id, formdir, encounter, form_type FROM " .
			"forms LEFT JOIN $frmn ON (form_id = $frmn.id) WHERE ".
			"formdir=? AND forms.pid=? AND deleted=? AND form_type=? ORDER BY ".
			"forms.date DESC LIMIT 1", array($frmdir, $pid, 0, $_GET['type']));
		$noload = FALSE;
		if(!$old) {
			$load .= "alert('No Prior Form of that type found. No changes made.'); ";
		}
	} else {
		if($default_user_exam) {
  		$old = sqlQuery("SELECT form_id, formdir, encounter, form_type FROM " .
				"forms LEFT JOIN $frmn ON (form_id = $frmn.id) WHERE ".
				"formdir=? AND forms.pid=? AND deleted=? AND form_type=? ORDER BY ".
				"forms.date DESC LIMIT 1", array($frmdir, $pid, 0, $default_user_exam));
		} else {
  		$old = sqlQuery("SELECT form_id, formdir, encounter FROM forms WHERE ".
				"formdir=? AND forms.pid=? AND deleted=? ORDER BY ".
				"date DESC LIMIT 1", array($frmdir, $pid, 0));
		}
	}
	$prev_enc = '';
  if ($old) {
   	$fid = $old{'form_id'};
		$prev_enc = $old{'encounter'};
   	if(!$noload || $noload == '') { 
    	unset($dt);
			$dt = formFetch($frmn, $fid);
			loadROSAndChecks($dt, $ros_module, $fid, $frmn);
			// loadFormComments($dt, 'general_exam2', $fid, $frmn, $pid);
		}
  } else {
		if(checkSettingMode('wmt::load_prev_assessment','',$frmdir)) {
  		$old = sqlQuery('SELECT form_id, formdir, encounter, ee1_hpi, ' .
				'ee1_assess FROM forms LEFT JOIN form_ext_exam1 ON ' .
				'(form_id = form_ext_exam1.id) '.
				'WHERE formdir="ext_exam1" AND forms.pid=? AND deleted="0" ORDER BY '.
				'forms.date DESC LIMIT 1', array($pid));
			if($old) {
				$dt{'hpi'} = $old{'ee1_hpi'};
				$dt{'assess'} = $old{'ee1_assess'};
				$prev_enc = $old{'encounter'};
			}
		}
		if($dt{'form_type'} == '') $dt{'form_type'} = $default_user_exam;
		if($_GET['type']) $dt['form_type'] = $_GET['type'];
	}

	if(!$prev_enc) {
  	$old = sqlQuery("SELECT form_id, formdir, encounter FROM forms WHERE ".
				"formdir=? AND pid=? AND deleted='0' ORDER BY ".
				"date DESC LIMIT 1", array('ext_exam1', $pid));
  	if ($old) $prev_enc = $old{'encounter'};
	}
	// Here we can go link any diags from the last encounter to this encounter
	if(checkSettingMode('wmt::link_prev_diags','',$frmdir)) {
		if($prev_enc && $encounter) {
			$sql="SELECT lists.id, lists.type, issue_encounter.seq FROM ".
				"issue_encounter LEFT JOIN lists ON list_id=lists.id WHERE ".
				"encounter=? AND ".
				"issue_encounter.pid=? AND lists.type = 'medical_problem'";
			$fres=sqlStatementNoLog($sql, array($prev_enc, $pid));
			while($frow=sqlFetchArray($fres)) {
				LinkDiagnosis($pid,$frow{'id'},$encounter,$frow{'seq'});
			}
		}
	}
	// Here we link all outstanding images into this encounter
	foreach($img as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'wmt_img_history');
	}
	foreach($allergies as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'allergy');
	}
	foreach($surg as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'surgery');
	}
	$cnt = 1;
	foreach($meds as $prev) { 
		if(!$max_med || ($cnt <= $max_med)) {
			LinkListEntry($pid, $prev['id'], $encounter, $med_list_type);
		}
		$cnt++;
	}
	$cnt = 1;
	foreach($med_hist as $prev) { 
		if(!$max_med_hist || ($cnt <= $max_med_hist)) {
			LinkListEntry($pid, $prev['id'], $encounter, $med_list_type);
		}
		$cnt++;
	}
	foreach($imm as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'immunizations');
	}
	foreach($hosp as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'hospitalization');
	}
	foreach($fh as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'wmt_family_history');
	}
	foreach($pmh as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'wmt_med_history');
	}

} else if($form_mode == 'cancel') {
	formJump();

// RPG - 
// THIS IS THE SAVE OPTION AND ALSO ALL OTHER Add/Update/Delete/Link OPTIONS
// NOW THIS WILL HAPPEN EVERY TIME IT'S NOT A NEW OR UPDATE OPEN ACTION
} else {
	$max_diags=0;
	$pqrs_chks = array();
	$pqrs_selected = array();
	$lifestyle_data_entered = false;
	$db_extra['db_fh_extra_yes'] = '';
	$db_extra['db_fh_extra_no'] = '';
	if($form_mode != 'save') $continue = 'true';
	$draw_display = FALSE;
	foreach($modules as $module) {
		unset($chp_options);
		$chp_options = array();
		if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
		if(!isset($chp_options[0])) $chp_options[0] = '';
		$this_module = $module['option_id'];
		if($chp_options[0]) $this_module = $chp_options[0];
		$this_module .= '_pre.php';
		// IS THERE A FORM SPECIFIC MODULE?
		if(is_file('./pre_process/'.$this_module)) {
			include('./pre_process/'.$this_module);
		} else if(is_file($GLOBALS['srcdir'].
					'/wmt-v2/form_pre_process/'.$this_module)) {
			include($GLOBALS['srcdir'].'/wmt-v2/form_pre_process/'.$this_module);
		}
	}
	$draw_display = TRUE;

	/* OEMR - Changes */
	//Date Pre Processing
	$fieldList = array(
		'img_dt',
		'cp_img_dt',
		'ps_begdate',
		'cp_ps_begdate',
		'dg_begdt',
		'cp_dg_begdt',
		'hosp_dt',
		'cp_hosp_dt',
		'ps_begdate',
		'cp_ps_begdate',
		'dg_enddt',
		'cp_dg_enddt'
	);

	foreach ($_POST as $k => $var) {
		foreach ($fieldList as $fv) {
			if(substr($k,0,strlen($fv)) == $fv) {
				$_POST[$k] = getFormatedDate('Y-m-d', $var);
			}
		}
	}
	/* End */

	// $flds = sqlListFields($frmn);
	foreach ($_POST as $k => $var) {
  	if(is_string($var)) $var = trim($var);
  	if($var == 'YYYY-MM-DD') $var = '';
		if((substr($k,0,7) == 'ee1_rs_') || (substr($k,0,3) == 'rs_')) {
			$rs[$k] = $var;
		} else if((substr($k,0,4) == 'ros_')) {
			$wmt_ros[$k] = $var;
		} else {
			$dt[$k] = $var;
			// if(in_array($k, $db_dates)) $dt[$k] = dateToYYYYMMDD($var);
		}

	/* OEMR - Changes */
	//Process form variable
	$ge_sections1 = array('gen', 'head', 'eyes', 'ears', 'nose', 'mouth', 'throat', 'neck', 'thyroid', 'lymph', 'breast', 'cardio', 'pulmo', 'gastro', 'neuro', 'musc', 'ext', 'dia', 'test', 'rectal', 'skin', 'psych');

	foreach($ge_sections1 as $section) {
		if('tmp_ge_'.$section == $k) {
			$data[$k] = $var;
		}
	}
	/* End */
	
  	if(($k != 'pname') && ($k != 'pid') && ($k != 'date') && 
			(substr($k,0,3) != 'dg_') && (substr($k,0,6) != 'chiro_') &&
			(substr($k,0,4) != 'all_') && (substr($k,0,7) != 'ee1_rs_') &&
			(substr($k,0,3) != 'rs_') && (substr($k,0,4) != 'ros_') &&
			(substr($k,0,4) != 'med_') && (substr($k,0,3) != 'ps_') && 
			(substr($k,0,3) != 'fh_') && (substr($k,0,8) != 'coff') && 
			(substr($k,0,3) != 'db_') && ($k != 'activity') && 
			(substr($k,0,5) != 'hosp_') && (substr($k,0,4) != 'imm_') &&
			(substr($k,0,4) != 'img_') && (substr($k,0,4) != 'fyi_') && 
			(substr($k,0,3) != 'lf_') && (substr($k,0,9) != 'db_sh_ex_') && 
			(substr($k,0,4) != 'pat_') && (substr($k,0,6) != 'vital_') && 
			(substr($k,0,3) != 'cb_') && (substr($k,0,4) != 'inj_') && 
			(substr($k,0,3) != 'ce_') && (substr($k,0,4) != 'gyn_') && 
			($k != 'ge_id') && 
			(substr($k,0,4) != 'tmp_') && (substr($k,0,4) != 'pmh_')) {
				/*
				$tmp = substr($k,0,-3);
				if(substr($k,0,3) == 'ge_' && (substr($k,-3) == '_nt') &&
					in_array($tmp, $flds)) {
					$notes[$k] = $var;
				} else {
				}
				*/
				$data[$k] = $var;
		}
		// PROCESS ALL THE MULTI-CHECK BOX LINES
  	if(substr($k,0,10) == 'tmp_ge_hd_') $hd_chks[] = $var;
  	if((substr($k,0,13) == 'tmp_ge_mouth_') && (substr($k,-5) != '_disp')) $mouth_chks[] = $var;
  	if(substr($k,0,12) == 'tmp_ge_thrt_') $thrt_chks[] = $var;
  	if(substr($k,0,14) == 'tmp_ge_gi_her_') $hrn_chks[] = $var;
  	if(substr($k,0,10) == 'tmp_ge_ms_') $ms_chks[] = $var;
  	if(substr($k,0,9) == 'tmp_proc_') $proc_choices[] = $var;
  	if(substr($k,0,7) == 'ee1_rs_') $ros[$k] = $var;
  	if(substr($k,0,3) == 'rs_') $ros[$k] = $var;
  	if(substr($k,0,4) == 'ros_') $ros_parent[$k] = $var;
  	if(substr($k,0,3) == 'ps_') $surg[$k] = $var;
  	if(substr($k,0,4) == 'imm_') $imm[$k] = $var;
  	if(substr($k,0,4) == 'all_') $allergy[$k] = $var;
  	if(substr($k,0,5) == 'hosp_') $hosp[$k] = $var;
  	if(substr($k,0,4) == 'inj_') $inj[$k] = $var;
  	if(substr($k,0,9) == 'med_hist_') $mhist[$k] = $var;
  	if(substr($k,0,4) == 'med_' && substr($k,0,5) != 'med_h') $med[$k] = $var;
  	if(substr($k,0,3) == 'fh_') $fh[$k] = $var;
	// Set up the family history extras from the yes and no list
		if(substr($k,0,10) == 'tmp_fh_rs_') {
			if(strtolower($var) == 'y') {
				if($fh_old_style) {
					$data['ee1_fh_extra_yes'] = AppendItem($data['ee1_fh_extra_yes'], $k, false, '|');
				} else {
					$db_extra['db_fh_extra_yes'] = AppendItem($db_extra['db_fh_extra_yes'], substr($k,10), false, '|');
				}
			}
			if(strtolower($var) == 'n') {
				if($fh_old_style) {
					$data['ee1_fh_extra_no'] = AppendItem($data['ee1_fh_extra_no'], $k, false, '|');
				} else {
					$db_extra['db_fh_extra_no'] = AppendItem($db_extra['db_fh_extra_no'], substr($k,10), false, '|');
				}
			}
		}

		/* OEMR - Changes */
		//Process form variable
		if(!isset($cp_img)) {
			$cp_img = array();
		}

		if(!isset($cp_surg)) {
			$cp_surg = array();
		}

		if(!isset($cp_hosp)) {
			$cp_hosp = array();
		}

		if(!isset($cp_pmh)) {
			$cp_pmh = array();
		}

		if(!isset($cp_fh)) {
			$cp_fh = array();
		}

		if(!isset($cp_diag)) {
			$cp_diag = array();
		}
		
		if(substr($k,0,7) == 'cp_img_') { 
			$img[$k] = $var;
			$cp_img[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,6) == 'cp_ps_') {
			$surg[$k] = $var;
			$cp_surg[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,8) == 'cp_hosp_') {
			$hosp[$k] = $var;
			$cp_hosp[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,7) == 'cp_pmh_') {
			$pmh[$k] = $var;
			$cp_pmh[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,6) == 'cp_fh_') {
			$fh[$k] = $var;
			$cp_fh[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,6) == 'cp_dg_') {
			$diag[$k] = $var;
			$cp_diag[$k] = $var;
			unset($data[$k]);
		}

		if(substr($k,0,7) == 'cp_tmp_') {
			unset($data[$k]);
		}
		/* End */

  	if(substr($k,0,4) == 'pmh_') $pmh[$k] = $var;
  	if(substr($k,0,4) == 'img_') $img[$k] = $var;
  	if(substr($k,0,3) == 'db_') {
			if(substr($k,0,6) == 'db_lf_') {
				$life[substr($k,3)] = $var;
				if($var != '') $lifestyle_data_entered = true;
			} else if(substr($k,0,7) == 'db_pat_') {
				$pat_data[substr($k,7)]=$var;
			} else if(substr($k,0,9) == 'db_sh_ex_') {
				$sh_ex[substr($k,9)]=$var;
			} else {
				$db_extra[$k]=$var;
			}
		}
  	if(substr($k,0,4) == 'fyi_') {
			if(strpos($k, '_portal_') !== false) {
				echo "Saving, we have portal data!<br>\n";
			}
     	$fy[$k] = $var;
  	} 
  	if(substr($k,0,9) == 'tmp_pqrs_' && substr($k,-5) != '_mode') {
				$pqrs_chks[] = $var;
				$pqrs_selected[] = $var;
		}
  	if(substr($k,0,3) == 'dg_') $diag[$k] = $var;
		if($k == 'tmp_diag_cnt') $max_diags = $var;
	}
	// Set the form date to today if empty
	if($data['form_dt'] == '' || $data['form_dt'] == 0) {
		$data['form_dt']=date('Y-m-d');
	}

	/* OEMR - Changes */
	//perform action
	$cp_img_list = getSeqArray($cp_img);
	foreach ($cp_img_list as $cp_img_key => $cp_img_item) {
		$img_id=AddImageHistory($pid, $cp_img_item['cp_img_type'], $cp_img_item['cp_img_dt'], $cp_img_item['cp_img_nt']);
		if($img_id) LinkListEntry($pid, $img_id, $encounter, 'wmt_img_history');
	}

	$cp_surg_list = getSeqArray($cp_surg);
	foreach ($cp_surg_list as $cp_surg_key => $cp_surg_item) {
		if(isset($cp_surg_item['cp_ps_title'])) {
			$surg_id=AddSurgery($pid,$cp_surg_item['cp_ps_begdate'],$cp_surg_item['cp_ps_title'], $cp_surg_item['cp_ps_comments'],$cp_surg_item['cp_ps_referredby'], $cp_surg_item['cp_ps_hospitalized']);
			if($surg_id) LinkListEntry($pid, $surg_id, $encounter, 'surgery');
		}
	}

	$cp_hosp_list = getSeqArray($cp_hosp);
	foreach ($cp_hosp_list as $cp_hosp_key => $cp_hosp_item) {
		if(isset($cp_hosp_item['cp_hosp_why'])) {
				$hosp_id=AddHospitalization($pid,$cp_hosp_item['cp_hosp_dt'], $cp_hosp_item['cp_hosp_why'],$cp_hosp_item['cp_hosp_type'], $cp_hosp_item['cp_hosp_nt']);
				if($hosp_id) LinkListEntry($pid, $hosp_id, $encounter, 'hospitalization');
			}
	}

	$cp_pmh_list = getSeqArray($cp_pmh);
	foreach ($cp_pmh_list as $cp_pmh_key => $cp_pmh_item) {
		if(isset($cp_pmh_item['cp_pmh_type'])) {
	  		$mh_id=AddMedicalHistory($pid,$cp_pmh_item['cp_pmh_type'],'',$cp_pmh_item['cp_pmh_nt']);
			if($mh_id) LinkListEntry($pid, $mh_id, $encounter, 'wmt_med_history');
		}
	}

	$cp_fh_list = getSeqArray($cp_fh);
	foreach ($cp_fh_list as $cp_fh_key => $cp_fh_item) {
		if(isset($cp_fh_item['cp_fh_who'])) {
	  		$fh_id=AddFamilyHistory($pid,$cp_fh_item['cp_fh_who'],$cp_fh_item['cp_fh_type'],$cp_fh_item['cp_fh_nt'], $cp_fh_item['cp_fh_dead'],$cp_fh_item['cp_fh_age'],$cp_fh_item['cp_fh_age_dead']);
			if($fh_id) LinkListEntry($pid, $fh_id, $encounter, 'wmt_family_history');
		}
	}

	$cp_dg_list = getSeqArray($cp_diag);
	foreach ($cp_dg_list as $cp_dg_key => $cp_dg_item) {
		if(isset($cp_dg_item['cp_dg_code'])) {
			AddDiagnosis($pid,$encounter,$cp_dg_item['cp_dg_type'],$cp_dg_item['cp_dg_code'], $cp_dg_item['cp_dg_title'],$cp_dg_item['cp_dg_plan'],$cp_dg_item['cp_dg_begdt'], $cp_dg_item['cp_dg_enddt'],$cp_dg_item['cp_dg_seq']);
		}
	}
	/* End */

	if($use_meds_not_rx) {
		if(isset($med['med_begdate'])) {
  		$med_id = AddMedication($pid,$med['med_begdate'],$med['med_title'],
					$med['med_enddate'],$med['med_dest'],$med['med_comm']);
			if($med_id) LinkListEntry($pid, $med_id, $encounter, 'medication');
			$dt['med_begdate'] = $dt['med_title'] = $dt['med_enddate'] = '';
			$dt['med_dest'] = $dt['med_comm'] = '';
		}
	}

	if($use_meds_not_rx) {
		if(isset($mhist['med_hist_begdate'])) {
  		$mhist_id = AddMedication($pid,$mhist['med_hist_begdate'],
				$mhist['med_hist_title'],$mhist['med_hist_enddate'],
				$mhist['med_hist_dest'],$mhist['med_hist_comm']);
			if($mhist_id) LinkListEntry($pid, $mhist_id, $encounter, 'medication');
			$dt['med_hist_begdate'] = $dt['med_hist_title'] = '';
			$dt['med_hist_enddate'] = $dt['med_hist_dest'] = '';
			$dt['med_hist_comm'] = '';
		}
	}

	if(isset($img['img_type'])) {
  	$img_id=AddImageHistory($pid,$img['img_type'],$img['img_dt'],$img['img_nt']);
  	$dt['img_type'] = $dt['img_dt'] = $dt['img_nt'] = '';
		if($img_id) LinkListEntry($pid, $img_id, $encounter, 'wmt_img_history');
	}

	if(isset($inj['inj_title'])) {
  	$inj_id=AddInjury($pid,$inj['inj_title'],$inj['inj_begdate'],
			$inj['inj_hospitalized'], $inj['inj_comments']);
  	$dt['inj_begdate'] = $dt['inj_title'] = $dt['inj_hospitalized'] = '';
		$dt['inj_comments'] = '';
		if($inj_id) LinkListEntry($pid, $inj_id, $encounter, 'wmt_inj_history');
	}
	
	if(isset($surg['ps_title'])) {
  	$surg_id=AddSurgery($pid,$surg['ps_begdate'],$surg['ps_title'],
			$surg['ps_comments'],$surg['ps_referredby'], $surg['ps_hospitalized']);
  	$dt['ps_begdate'] = $dt['ps_title'] = $dt['ps_comments'] = '';
		$dt['ps_referredby'] = $dt['ps_hospitalized'] = '';
		if($surg_id) LinkListEntry($pid, $surg_id, $encounter, 'surgery');
	}

	if(isset($hosp['hosp_why'])) {
  	$hosp_id=AddHospitalization($pid,$hosp['hosp_dt'],
											$hosp['hosp_why'],$hosp['hosp_type'], $hosp['hosp_nt']);
  	$dt['hosp_dt'] = $dt['hosp_why'] = $dt['hosp_type'] = $dt['hosp_nt'] = '';
		if($hosp_id) LinkListEntry($pid, $hosp_id, $encounter, 'hospitalization');
	}

	if($allergy_add_allowed) {
		if(isset($allergy['all_begdate'])) {
 			$all_id=AddAllergy($pid,$allergy['all_begdate'],$allergy['all_title'],
					$allergy['all_comm'],$allergy['all_react']);
 			$dt['all_begdate'] = $dt['all_title'] = $dt['all_comm'] = '';
			$dt['all_react'] = '';
			if($all_id) LinkListEntry($pid, $all_id, $encounter, 'allergy');
		}
	}

	if(isset($fh['fh_who'])) {
  	$fh_id=AddFamilyHistory($pid,$fh['fh_who'],$fh['fh_type'],$fh['fh_nt'],
			$fh['fh_dead'],$fh['fh_age'],$fh['fh_age_dead']);
  	$dt['fh_who'] = $dt['fh_type'] = $dt['fh_nt'] = '';
		$dt['fh_dead'] = $dt['fh_age'] = $dt['fh_age_dead'] = '';
		if($fh_id) LinkListEntry($pid, $fh_id, $encounter, 'wmt_family_history');
	}

	if(isset($pmh['pmh_type'])) {
  	$mh_id=AddMedicalHistory($pid,$pmh['pmh_type'],'',$pmh['pmh_nt']);
  	$dt['pmh_type'] = $dt['pmh_nt'] = '';
		if($mh_id) LinkListEntry($pid, $mh_id, $encounter, 'wmt_med_history');
	}

	if(isset($diag['dg_code'])) {
 		AddDiagnosis($pid,$encounter,$diag['dg_type'],$diag['dg_code'],
			$diag['dg_title'],$diag['dg_plan'],$diag['dg_begdt'],
			$diag['dg_enddt'],$diag['dg_seq']);
 		$dt['dg_code'] = $dt['dg_title'] = $dt['dg_plan'] = '';
		$dt['dg_begdt'] = $dt['dg_enddt'] = $dt['dg_seq'] = $dt['dg_type'] = '';
		$dt['tmp_dg_desc'] = '';
	}

	// ALWAYS UPDATE ALL DIAGNOSIS CODES
	$cnt=1;
	while($cnt <= $max_diags) {
		if($use_diag_link_checkbox) {
			if(!isset($diag['dg_link_'.$cnt])) $diag['dg_link_'.$cnt] = '';
			if($diag['dg_link_'.$cnt]) { 
  			LinkDiagnosis($pid,$diag['dg_id_'.$cnt],$encounter,$diag['dg_seq_'.$cnt]);
			} else {
  			UnLinkDiagnosis($pid,$diag['dg_id_'.$cnt],$encounter);
			}
		}
  	UpdateDiagnosis($pid,$diag['dg_id_'.$cnt],$diag['dg_code_'.$cnt],
			$diag['dg_title_'.$cnt],$diag['dg_plan_'.$cnt],$diag['dg_begdt_'.$cnt],
			$diag['dg_enddt_'.$cnt],$diag['dg_type_'.$cnt],$diag['dg_remain_'.$cnt],
			$diag['dg_seq_'.$cnt],$encounter);
		$cnt++;
	}
	if(strtolower($data['form_complete']) == 'a') {
		$data['approved_by'] = $_SESSION['authUser'];
		$data['approved_dt'] = date('Y-m-d H:i:s');
	}

	$data['ge_hd_chks'] = implode('|', $hd_chks);
	$data['ge_mouth_chks'] = implode('|', $mouth_chks);
	$data['ge_thrt_chks'] = implode('|', $thrt_chks);
	$data['ge_gi_her_dtl'] = implode('|', $hrn_chks);
	$data['ge_ms_chks'] = implode('|', $ms_chks);
	$data['proc_choices'] = implode('|', $proc_choices);
	$dt['proc_choices'] = implode('|', $proc_choices);

	$log = "INSERT $frmdir Form Mode ($form_mode) and Wrap ($wrap_mode) ".
		"and Continue ($continue) - Saving ID ($id)";
	if($form_event_logging) auditSQLEvent($log, TRUE);
	if(!$id || $id=='') {
  	if ($encounter == '') $encounter = date('Ymd');
  	$newid = wmtFormSubmit($frmn,$data,'',$_SESSION['userauthorized'], $pid);
		$id = $newid;
  	addForm($encounter,$ftitle,$id,$frmdir,$pid,$_SESSION['userauthorized']);
		$_SESSION['encounter'] = $encounter;
		if($ros_module == 'ros2' || $ros_module == 'ent_ros') {
			$ros_parent['link_id']=$newid;
			$ros_parent['link_name']=$frmn;
			$ros_parent['ros_yes'] = '';
			$ros_parent['ros_no'] = '';
			foreach($ros as $key => $val) {
				if(substr($key,-3) == '_nt') {
					$key = substr($key,0,-3);
					ProcessROSKeyComment($pid, $newid, $frmn, $key, $val);
				} else {
					if(strtolower($val) == 'y') { 
						$ros_parent['ros_yes'] = 
								AppendItem($ros_parent['ros_yes'], $key, false, '|');
					}
					if(strtolower($val) == 'n') { 
						$ros_parent['ros_no'] = 
								AppendItem($ros_parent['ros_no'], $key, false, '|');
					}
				}	
			}
  		$rosid = wmtFormSubmit('form_wmt_ros',$ros_parent,'',$_SESSION['userauthorized'],$pid);
		} else {
			$ros['ee1_link_id']=$newid;
			$ros['ee1_link_name']=$frmn;
  		$rosid = 
				wmtFormSubmit('form_ext_ros',$ros,'',$_SESSION['userauthorized'],$pid);
		}
		if(count($pqrs_chks) > 0) {
			unset($pqrs);
			$pqrs = array();
			$pqrs['link_id']=$newid;
			$pqrs['link_name']=$frmdir;
			$pqrs['link_date']=$data['py1_form_dt'];
			$pqrs['pqrs_choices'] = implode('|', $pqrs_chks);
  		$pqid = wmtFormSubmit('wmt_pqrs',$pqrs,'',
																		$_SESSION['userauthorized'],$pid);
		}
  	$_SESSION['encounter'] = $encounter;

	} elseif ($id) {
		// THIS IS A QUICK APPROVAL CHECK, CAN CATCH MULTIPLE USERS
		$test=sqlQuery("SELECT form_complete, id FROM $frmn WHERE id=?",array($id));
		if($test{'id'} == $id && strtolower($test{'form_complete'}) == 'a') {
			echo "This form has already been approved!<br/>\n";
			echo "Another user has already sealed/locked this form, any of these changes will be discarded<br/>\n";
			exit;	
		}
  	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
  	$q1 = '';
  	foreach ($data as $key => $val){
    	$q1 .= "$key=?, ";
			$binds[] = $val;
  	}
		$binds[] = $id;
		$ros['ee1_link_id']=$id;
		$ros['ee1_link_name']=$frmn;
  	//print $q1; exit;
  	sqlInsert("UPDATE $frmn SET pid =?, groupname=?, user=?, ".
					"authorized=?, activity=?, $q1 date = NOW() WHERE id=?", $binds);

  	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);

  	$q1 = '';
		if($ros_module == 'ros2' || $ros_module == 'ent_ros') {
			$ros_parent['link_id']=$id;
			$ros_parent['link_name']=$frmn;
			$ros_parent['ros_yes'] = '';
			$ros_parent['ros_no'] = '';
			foreach($ros as $key => $val) {
				if(substr($key,-3) == '_nt') {
					$key = substr($key,0,-3);
					ProcessROSKeyComment($pid, $id, $frmn, $key, $val);
				} else {
					if(strtolower($val) == 'y') { 
						$ros_parent['ros_yes'] = 
										AppendItem($ros_parent['ros_yes'], $key, false, '|');
					}
					if(strtolower($val) == 'n') { 
						$ros_parent['ros_no'] = 
										AppendItem($ros_parent['ros_no'], $key, false, '|');
					}
				}	
			}
  		foreach ($ros_parent as $key => $val){
    		$q1 .= "$key=?, ";
				$binds[] = $val;
  		}
			$binds[] = $id;
			$binds[] = $frmn;
  		sqlInsert("UPDATE form_wmt_ros SET pid =?, groupname=?, user=?, ".
				"authorized=?, activity=?, $q1 date = NOW() WHERE ".
				"link_id=? AND link_name=?", $binds);
		} else {
  		foreach ($ros as $key => $val){
    		$q1 .= "$key=?, ";
				$binds[] = $val;
  		}
			$binds[] = $id;
			$binds[] = $frmn;
  		sqlInsert("UPDATE form_ext_ros SET pid =?, groupname=?, user=?, ".
				"authorized=?, activity=?, $q1 date = NOW() WHERE ".
				"ee1_link_id=? AND ee1_link_name=?", $binds);
		}
	} // END OF HAS AN ID

	$justify = autoJustify($pid, $encounter, $frmdir, $visit);
	$draw_display = FALSE;
	// NOW PROCESS ALL THE COMMENTS
	// foreach($notes as $key => $nt) {
		// ProcessROSKeyComment($pid, $id, $frmn, $key, $nt);
	// }
	

	$draw_display = FALSE;
	foreach($modules as $module) {
		unset($chp_options);
		$chp_options = array();
		if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
		if(!isset($chp_options[0])) $chp_options[0] = '';
		$this_module = $module['option_id'];
		if($chp_options[0]) $this_module = $chp_options[0];
		$this_module .= '_post.php';
		// IS THERE A FORM SPECIFIC MODULE?
		if(is_file('./post_process/'.$this_module)) {
			include('./post_process/'.$this_module);
		} else if(is_file($GLOBALS['srcdir'].
					'/wmt-v2/form_post_process/'.$this_module)) {
			include($GLOBALS['srcdir'].'/wmt-v2/form_post_process/'.$this_module);
		}
	}
	$draw_display = TRUE;

	// Chiro Exam
	// $chiroController = FormWmt::initForm('form_chiro_exam', $id, $frmn);
	// $chiroController::$command->update();

	// include($GLOBALS['srcdir'].'/wmt-v2/form_modules/gyn_exam_module.inc.php');

	$draw_display = TRUE;

	// IF THERE IS A PQRS ENTRY, UPDATE IT
	if(count($pqrs_chks) > 0) {
		if($auto_post_pqrs && !$is_billed) {
			// echo "In the PQRS Check: ";
			// print_r($pqrs_chks);
			// echo "<br>\n";
			$sql = 'SELECT id, date FROM billing WHERE pid=? AND encounter=? '.
					'AND code_type=? AND code=?';
			foreach($pqrs_chks as $key => $pqrs_cpt) {
				// TO SPLIT OUT AN INCLUDED MODIFIER
				$mod = '';
				if(strlen($pqrs_cpt) > 5) {
					$mod = substr($pqrs_cpt,-2);
					$pqrs_cpt = substr($pqrs_cpt,0,5);
				}	
				$binds = array($pid, $encounter, 'CPT4', $pqrs_cpt);
				$frow = sqlQuery($sql, $binds);
				if(!$frow{'id'}) {
					$desc = lookup_code_descriptions('CPT4:'.$pqrs_cpt);
					$fee = getFee('CPT4', $pqrs_cpt, $patient{'pricelevel'});
					addBilling($encounter, 'CPT4', $pqrs_cpt, $desc, $pid, 
						$_SESSION['userauthorized'], $visit->provider_id, $mod, '1', $fee);
				}
			}
		}
		$pqrs_choices = implode('|', $pqrs_chks);
  	$sql = "INSERT INTO wmt_pqrs (date, pid, user, groupname, authorized, ".
        "activity, link_id, link_name, pqrs_choices) VALUES ".
				"(NOW(), ?, ?, ?, ?, '1', ?, ?, ?) ON DUPLICATE KEY UPDATE ".
				"pqrs_choices=?, date=NOW(), user=?, authorized=?";
		$binds=array($pid, $_SESSION['authUser'], $_SESSION['authProvider'],
			$_SESSION['userauthorized'], $id, $frmdir, $pqrs_choices, $pqrs_choices,
			$_SESSION['authUser'], $_SESSION['userauthorized']);
  	sqlInsert($sql, $binds);
	}

	$lifestyle = wmtLifestyle::getFormLifestyle($pid, $frmdir, $id);
	if($lifestyle->id || $lifestyle_data_entered) {
		if(!$lifestyle->id) {
			$lifestyle->pid = $pid;
			$lfid = $lifestyle->insert($lifestyle);
			$lifestyle->id = $lfid;
		}
		$lifestyle->link_name = $frmdir;
		$lifestyle->link_id = $id;
		$lifestyle->form_dt = $data['form_dt'];
		foreach($life as $key => $val) { 
			$lifestyle->$key = $val;
		}
		$lifestyle->update();
	}
	$log = "INSERT $frmdir Form Mode ($form_mode) and Wrap ($wrap_mode) ".
		"and Continue ($continue) - Finished Save, Lifestyle and PQRS";
	if($form_event_logging) auditSQLEvent($log, TRUE);
	
	if(!isset($pat_data)) $pat_data = array();
	if(count($pat_data) > 0) {
		foreach($pat_data as $key => $val) { 
			$patient->updateThis($key, $val); 
		}
	}

	$db = wmtDashboard::getPidDashboard($pid);
	foreach($db_extra as $key => $val) {
		if($key != 'db_fyi') $db->$key = $val;
	}
	$db->db_form_dt = $data['form_dt'];
	$db->update();

	$fyi = wmtFYI::getPidFYI($pid);
	foreach($fyi as $key => $val) {
		if(strpos($key, '_portal_') !== false) continue;
		if(strpos($key, '_pp_') !== false) continue;
		if(substr($key,-3) == '_nt') {
			if(isset($fy[$key])) $fyi->$key = $fy[$key];	
		}
	}
	$fyi->update();

	if($expanded_sh) {
		$sql = "INSERT INTO wmt_sh_data (pid, form_name, field_name, staff_touch, ".
			"doc_touch, content) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ".
			"content=?";
		$doc_touch = $staff_touch = NULL;
		if(IsDoctor()) {
			$doc_touch = date('Y-m-d H:i:s');
		} else $staff_touch = date('Y-m-d H:i:s');
		foreach($sh_ex as $key => $val) {
			$binds = array($pid,$frmdir,$key,$staff_touch,$doc_touch,$val,$val);
			sqlInsert($sql,$binds);
			$binds = array($pid,'dashboard',$key,$staff_touch,$doc_touch,$val,$val);
			sqlInsert($sql,$binds);
		}
	}

	$apt_fail_text = $apt_success_text = '';
	if($appt_stat = checkSettingMode('wmt::mark_appt_billed','',$frmdir)) {
		$use_tracker = sqlQuery("SHOW TABLES LIKE 'patient_tracker_element'");
		if(isset($GLOBALS['disable_pat_trkr'])) {
			if($GLOBALS['disable_pat_trkr']) $use_tracker = false;
		}
		$sql = "SELECT pc_eid, pc_pid, pc_eventDate, pc_startTime FROM ".
				"openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate=?";
		$fres = sqlStatement($sql, array($pid, $dt['form_dt']));
		$appts = array();
		while($frow = sqlFetchArray($fres)) {
			$appts[] = $frow;
		}
		$sql = "UPDATE openemr_postcalendar_events SET pc_apptstatus=? WHERE ".
				"pc_pid=? AND pc_eventDate=?";
		if(count($appts) > 1) {
			$apt_fail_text = 'Multiple Appointments Found For Date ('. 
				$dt['form_dt'].") PID [$pid] - All Will Marked as Billed";
			sqlStatement($sql, array($appt_stat, $pid, $dt['form_dt']));
		} else if(count($appts) == 1) {
			$apt_success_text = "Appointment For PID [$pid] On Date (".
					$dt['form_dt'].") Marked as Billed";
			sqlStatement($sql, array($appt_stat, $pid, $dt['form_dt']));
		} else {
			$apt_fail_text = 'No Appointment Found For Date ('. 
				$dt['form_dt'].") PID [$pid] - Could Not Mark As Billed";
		}
		if($use_tracker && function_exists('manage_tracker_status')) {
			foreach($appts as $ap) {
				if(isset($GLOBALS['wmt::mark_appt_checkout'])) {
					if(strtolower($GLOBALS['wmt::mark_appt_checkout']) == 'before') {
						manage_tracker_status($ap['pc_eventDate'],$ap['pc_startTime'],
							$ap['pc_eid'],$pid,$_SESSION['authUser'],'>','',$encounter);
					}
				}
				manage_tracker_status($ap['pc_eventDate'], $ap['pc_startTime'],
					$ap['pc_eid'],$pid,$_SESSION['authUser'],$appt_stat,'',$encounter);
				if(isset($GLOBALS['wmt::mark_appt_checkout'])) {
					if(strtolower($GLOBALS['wmt::mark_appt_checkout']) == 'after') {
						manage_tracker_status($ap['pc_eventDate'],$ap['pc_startTime'],
							$ap['pc_eid'], $pid, $_SESSION['authUser'],'>','',$encounter);
					}
				}
			}
		}
	}

	if(strtolower($data['form_complete']) == 'a') {
		ob_start();
		ext_exam2_report($pid, $encounter, "*", $id, true);
		$content=ob_get_contents();
		ob_end_clean();
		AddFormToRepository($pid, $encounter, $id, $frmn, $content);
		ob_start();
		ext_exam2_referral($pid, $encounter, "*", $id, true);
		$content=ob_get_contents();
		ob_end_clean();
		AddFormToRepository($pid, $encounter, $id, $frmn.'_referral', $content);
	}

	if(!$continue) {
		$alert_success=checkSettingMode('wmt::alert_appt_bill_success','',$frmdir);
		$alert_fail = checkSettingMode('wmt::alert_appt_bill_fail','',$frmdir);
		if($wrap_mode == 'new') {
		}
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>Redirecting.....</title>\n";
		echo "Popping ($pop_form)<br>\n";
		echo "\n<script type='text/javascript'>";
		if($pop_form) {
			echo "function showNewReport() {\n";
			echo "  if(null == opener) {\n";
			echo "		ploc = top.location;\n";
			echo "		return false;\n";
			echo "	}\n";
			echo "  var ploc = opener.location;\n";
			echo "  var res = String(ploc).match(/patient_file\/encounter\/forms.php/);\n";
			echo "	if(null != res) opener.location.reload();\n";
			echo "}\n";
			echo "showNewReport();\n";
			if(checkSettingMode('wmt::mark_appt_billed','',$frmdir)) {
				if($apt_fail_text && $alert_fail) {
					echo "alert(\"$apt_fail_text\");";
				}
				if($apt_success_text && $alert_success) {
					echo "alert(\"$apt_success_text\");";
				}
			}
			echo "window.close();\n";
			echo "</script>\n";
			echo "</head>\n";
			echo "</html>\n";
			exit;
		} else {
			if(checkSettingMode('wmt::mark_appt_billed','',$frmdir)) {
				if($apt_fail_text && $alert_fail) {
					echo "alert(\"$apt_fail_text\");";
				}
				if($apt_success_text && $alert_success) {
					echo "alert(\"$apt_success_text\");";
				}
			}
			echo "</script>\n";
			echo "</head>\n";
			echo "</html>\n";
			formJump();
		}
	}
} // END OF THE SAVE SECTION

	$mode_exists = GetListTitleByKey($form_mode, 'Exam_Form_Visit_Types');
	if($form_mode == 'addmed') {
  	// $med_id = AddMedication($pid,$dt['med_begdate'],$dt['med_title'],
				// $dt['med_enddate'],$dt['med_dest'],$dt['med_comm']);
		// if($med_id) LinkListEntry($pid, $med_id, $encounter, 'medication');
		// $dt['med_begdate'] = $dt['med_title'] = $dt['med_enddate'] = '';
		// $dt['med_dest'] = $dt['med_comm'] = '';
		// $form_focus = 'med_begdate';

	} else if($form_mode == 'updatemed') {
		$cnt=trim($_GET['itemID']);
		if($use_meds_not_rx) {
  		UpdateMedication($pid,$dt['med_id_'.$cnt],$dt['med_begdate_'.$cnt],
				$dt['med_title_'.$cnt],$dt['med_enddate_'.$cnt],$dt['med_dest_'.$cnt],
				$dt['med_comments_'.$cnt]);
		} else {
  		UpdatePrescription($pid,$dt['med_id_'.$cnt],$dt['med_comments_'.$cnt]);
		}
	
	} else if($form_mode == 'delmed') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['med_id_'.$cnt],
											$dt['med_num_links_'.$cnt],'medication');
	
	} else if($form_mode == 'linkmed') {
		$cnt=trim($_GET['itemID']);
  	LinkListEntry($pid,$dt['med_id_'.$cnt],$encounter,$med_list_type);
	
	} else if($form_mode == 'unlinkmed') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['med_id_'.$cnt],$encounter,$med_list_type);
	
	} else if($form_mode == 'unlinkallmeds') {
		if($use_meds_not_rx) {
			$unlink = GetList($pid, 'medication', $encounter);
		} else {
			$unlink = getLinkedPrescriptionsByPatient($pid, $encounter, '=1');
		}
		foreach($unlink as $rx) {
  		UnlinkListEntry($pid,$rx['id'],$encounter,$med_list_type);
		}
	
	} else if($form_mode == 'medwindow') {
		if(isset($_GET['disp'])) $dt['tmp_med_window_mode']=trim($_GET['disp']);

	} else if($form_mode == 'addmedhist') {
  	// $mhist_id = AddMedication($pid,$dt['med_hist_begdate'],
			// $dt['med_hist_title'],$dt['med_hist_enddate'],
			// $dt['med_hist_dest'],$dt['med_hist_comm']);
		// if($mhist_id) LinkListEntry($pid, $mhist_id, $encounter, 'medication');
		// $dt['med_hist_begdate'] = $dt['med_hist_title'] = '';
		// $dt['med_hist_enddate'] = $dt['med_hist_dest'] = $dt['med_hist_comm'] = '';

	} else if($form_mode == 'updatemedhist') {
		$cnt=trim($_GET['itemID']);
		if($use_meds_not_rx) {
  		UpdateMedication($pid,$dt['med_hist_id_'.$cnt],$dt['mhist_begdate_'.$cnt],
				$dt['med_hist_title_'.$cnt],$dt['med_hist_enddate_'.$cnt],
				$dt['med_hist_dest_'.$cnt], $dt['med_hist_comm_'.$cnt]);
		} else {
  		UpdatePrescription($pid,$dt['med_hist_id_'.$cnt],
																	$dt['med_hist_comments_'.$cnt]);
		}
	
	} else if($form_mode == 'delmedhist') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['med_hist_id_'.$cnt],
															$dt['med_hist_num_links_'.$cnt],'medication');
	
	} else if($form_mode == 'linkmedhist') {
		$cnt=trim($_GET['itemID']);
  	LinkListEntry($pid,$dt['med_hist_id_'.$cnt],$encounter,$med_list_type);
	
	} else if($form_mode == 'unlinkmedhist') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['med_hist_id_'.$cnt],$encounter,$med_list_type);
	
	} else if($form_mode == 'unlinkallmedhist') {
		if($use_meds_not_rx) {
			$unlink = GetList($pid, 'med_history', $encounter);
		} else {
			$unlink = getLinkedPrescriptionsByPatient($pid, $encounter, '<=0');
		}
		foreach($unlink as $rx) {
  		UnlinkListEntry($pid,$rx['id'],$encounter,$med_list_type);
		}
	
	} else if($form_mode == 'medhistwindow') {
		if(isset($_GET['disp'])) $dt['tmp_mhist_window_mode'] = trim($_GET['disp']);

	} else if($form_mode == 'updateimm') {
		$cnt=trim($_GET['itemID']);
  	UpdateImmunization($pid,$dt['imm_id_'.$cnt],$dt['imm_comments_'.$cnt]);
		$form_focus='imm_comments_'.$cnt;
	
	} else if($form_mode == 'unlinkimm') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['imm_id_'.$cnt],$encounter,'immunizations');
	
	} else if($form_mode == 'all') {
 		// $all_id=AddAllergy($pid,$dt['all_begdate'],$dt['all_title'],
						// $dt['all_comm'],$dt['all_react'],$dt['all_occur']);
		// if($all_id) LinkListEntry($pid, $all_id, $encounter, 'allergy');
		// $dt['all_begdate'] = $dt['all_title'] = $dt['all_comm'] = '';
		// $dt['all_react'] = $dt['all_occur'] = '';
		// $form_focus='all_begdate';

	} else if($form_mode == 'updateall') {
		$cnt=trim($_GET['itemID']);
  	UpdateAllergy($pid,$dt['all_id_'.$cnt],$dt['all_comments_'.$cnt],
			$dt['all_begdate_'.$cnt],$dt['all_title_'.$cnt],$dt['all_react_'.$cnt],
			$dt['all_occur_'.$cnt]);

	} else if($form_mode == 'delall') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['all_id_'.$cnt],
											$dt['all_num_links_'.$cnt],'allergy');
	
	} else if($form_mode == 'unlinkall') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['all_id_'.$cnt],$encounter,'allergy');
	
	} else if($form_mode == 'unlinkallall') {
		$max = 0;
		if(isset($dt['tmp_allergy_cnt'])) { $max= $dt['tmp_allergy_cnt']; }
		$cnt=1;
		while($cnt <= $max) {
  		UnLinkListEntry($pid,$dt['all_id_'.$cnt],$encounter,'allergy');
			$cnt++;
		}
	
	} else if($form_mode == 'surg') {
  	// $surg_id=AddSurgery($pid,$dt['ps_begdate'],$dt['ps_title'],
				// $dt['ps_comments'],$dt['ps_referredby'], $dt['ps_hospitalized']);
		// if($surg_id) LinkListEntry($pid, $surg_id, $encounter, 'surgery');
		// $dt['ps_title']='';
		// $dt['ps_begdate']='';
		// $dt['ps_comments']='';
		// $dt['ps_referredby']='';
		// $dt['ps_hospitalized']='';
		// $form_focus='ps_begdate';
	
	} else if($form_mode == 'updatesurg') {
		$cnt=trim($_GET['itemID']);
  	UpdateSurgery($pid,$dt['ps_id_'.$cnt],$dt['ps_begdate_'.$cnt],$dt['ps_title_'.$cnt],$dt['ps_comments_'.$cnt],$dt['ps_referredby_'.$cnt],$dt['ps_hospitalized_'.$cnt]);
	
	} else if($form_mode == 'delsurg') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['ps_id_'.$cnt],
											$dt['ps_num_links_'.$cnt],'surgery');
	
	} else if($form_mode == 'unlinksurg') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['ps_id_'.$cnt],$encounter,'surgery');
	
	// FIX - This is where we can add tasks
	} else if($form_mode == 'rto') {
  	// $mh_id=AddMedicalHistory($pid,$dt['pmh_type'],'',$dt['pmh_nt']);
		// if($mh_id) LinkListEntry($pid, $mh_id, $encounter, 'wmt_med_history');
		// $dt['rto_date'] = ''
	
	} else if($form_mode == 'pmh') {
  	// $mh_id=AddMedicalHistory($pid,$dt['pmh_type'],'',$dt['pmh_nt']);
		// if($mh_id) LinkListEntry($pid, $mh_id, $encounter, 'wmt_med_history');
		// $dt['pmh_type'] = $dt['pmh_nt'] = '';
		// $form_focus='pmh_type';
	
	} else if($form_mode == 'updatepmh') {
		$cnt=trim($_GET['itemID']);
  	UpdateMedicalHistory($pid,$dt['pmh_id_'.$cnt],$dt['pmh_type_'.$cnt],'',$dt['pmh_nt_'.$cnt]);
	
	} else if($form_mode == 'delpmh') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['pmh_id_'.$cnt],
														$dt['pmh_num_links_'.$cnt],'wmt_med_history');
	
	} else if($form_mode == 'unlinkpmh') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['pmh_id_'.$cnt],$encounter,'wmt_med_history');
	
	} else if($form_mode == 'img') {
  	// $img_id=AddImageHistory($pid,$dt['img_type'],$dt['img_dt'],$dt['img_nt']);
		// if($img_id) { LinkListEntry($pid, $img_id, $encounter, 'wmt_img_history'); }
		// $dt['img_type'] = $dt['img_dt'] = $dt['img_nt'] = '';
		// $form_focus = 'img_type';
	
	} else if($form_mode == 'updateimg') {
		$cnt=trim($_GET['itemID']);
  	UpdateImageHistory($pid,$dt['img_id_'.$cnt],$dt['img_type_'.$cnt],$dt['img_dt_'.$cnt],$dt['img_nt_'.$cnt]);
	
	} else if($form_mode == 'unlinkimg') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['img_id_'.$cnt],$encounter,'wmt_img_history');

	} else if($form_mode == 'delimg') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['img_id_'.$cnt],
														$dt['img_num_links_'.$cnt],'wmt_img_history');
	
	} else if($form_mode == 'inj') {
  	// $inj_id=AddInjury($pid,$dt['inj_title'],$dt['inj_begdate'],
			// $dt['inj_hospitalized'], $dt['inj_comments']);
		// if($inj_id) { LinkListEntry($pid, $inj_id, $encounter, 'wmt_inj_history'); }
		// $dt['inj_title'] = $dt['inj_begdate'] = $dt['inj_hospitalized'] = '';
		// $dt['inj_comments'] = '';
		// $form_focus = 'inj_title';
	
	} else if($form_mode == 'updateinj') {
		$cnt=trim($_GET['itemID']);
  	UpdateInjury($pid,$dt['inj_id_'.$cnt],$dt['inj_title_'.$cnt],
			$dt['inj_begdate_'.$cnt],$dt['inj_hospitalized_'.$cnt],
			$dt['inj_comments_'.$cnt]);
	
	} else if($form_mode == 'unlinkinj') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['inj_id_'.$cnt],$encounter,'wmt_inj_history');

	} else if($form_mode == 'delinj') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['inj_id_'.$cnt],
														$dt['inj_num_links_'.$cnt],'wmt_inj_history');
	
	} else if($form_mode == 'hosp') {
  	// $hosp_id=AddHospitalization($pid,$dt['hosp_dt'],$dt['hosp_why'],
				// $dt['hosp_type'],$dt['hosp_nt']);
		// if($hosp_id) LinkListEntry($pid, $hosp_id, $encounter, 'hospitalization');
		// $dt['hosp_dt'] = $dt['hosp_type'] = $dt['hosp_why'] = $dt['hosp_nt'] = '';
		// $form_focus='hosp_dt';
	
	} else if($form_mode == 'updatehosp') {
		$cnt=trim($_GET['itemID']);
  	UpdateHospitalization($pid,$dt['hosp_id_'.$cnt],$dt['hosp_dt_'.$cnt],$dt['hosp_why_'.$cnt],$dt['hosp_type_'.$cnt],$dt['hosp_nt_'.$cnt]);
	
	} else if($form_mode == 'delhosp') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['hosp_id_'.$cnt],
														$dt['hosp_num_links_'.$cnt],'hospitalization');
	
	} else if($form_mode == 'unlinkhosp') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['hosp_id_'.$cnt],$encounter,'hospitalization');
	
	} else if($form_mode == 'fh') {
  	// $fh_id=AddFamilyHistory($pid,$dt['fh_who'],$dt['fh_type'],$dt['fh_nt'],
				// $dt['fh_dead'],$dt['fh_age'],$dt['fh_age_dead']);
		// if($fh_id) LinkListEntry($pid, $fh_id, $encounter, 'wmt_family_history');
		// $dt['fh_type'] = $dt['fh_nt'] = '';
		// $form_focus='fh_who';
	
	} else if($form_mode == 'delfh') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['fh_id_'.$cnt],
										$dt['fh_num_links_'.$cnt],'wmt_family_history');
	
	} else if($form_mode == 'updatefh') {
		$cnt=trim($_GET['itemID']);
  	UpdateFamilyHistory($pid,$dt['fh_id_'.$cnt],$dt['fh_who_'.$cnt],
				$dt['fh_type_'.$cnt],$dt['fh_nt_'.$cnt],$dt['fh_dead_'.$cnt],
				$dt['fh_age_'.$cnt],$dt['fh_age_dead_'.$cnt]);
	
	} else if($form_mode == 'unlinkfh') {
		$cnt=trim($_GET['itemID']);
  	UnlinkListEntry($pid,$dt['fh_id_'.$cnt],$encounter,'wmt_family_history');
	
	} else if($form_mode == 'diag') {
  	// AddDiagnosis($pid,$encounter,$dt['dg_type'],$dt['dg_code'],$dt['dg_title'],
			// $dt['dg_plan'],$dt['dg_begdt'],$dt['dg_enddt'],$dt['dg_seq']);
		// Clear the variables to input another
		// $dt['dg_seq'] = $dt['dg_type'] = $dt['dg_code'] = $dt['dg_title'] = '';
		// $dt['dg_begdt'] = $dt['dg_enddt'] = $dt['dg_plan'] = '';
		// $dt['tmp_dg_desc'] = '';
		// $form_focus='dg_code';
		// $scroll_point='tmp_dg_desc';
	
	} else if($form_mode == 'deldiag') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['dg_id_'.$cnt],'','medical_problem');
	
	} else if($form_mode == 'linkdiag') {
		$cnt=trim($_GET['itemID']);
  	LinkDiagnosis($pid,$dt['dg_id_'.$cnt],$encounter,$dt['dg_seq_'.$cnt]);
	
	} else if($form_mode == 'unlinkdiag') {
		$cnt=trim($_GET['itemID']);
  	UnLinkDiagnosis($pid,$dt['dg_id_'.$cnt],$encounter);
	
	} else if($form_mode == 'unlinkalldiags') {
		$max= $dt['tmp_diag_cnt'];
		$cnt=1;
		while($cnt <= $max) {
  		UnLinkDiagnosis($pid,$dt['dg_id_'.$cnt],$encounter);
			$cnt++;
		}
	
	} else if($form_mode == 'updatediag') {
		// $cnt=trim($_GET['itemID']);
  	// UpdateDiagnosis($pid,$dt['dg_id_'.$cnt],$dt['dg_code_'.$cnt],$dt['dg_title_'.$cnt],$dt['dg_plan_'.$cnt],$dt['dg_begdt_'.$cnt],$dt['dg_enddt_'.$cnt],$dt['dg_type_'.$cnt],$dt['dg_remain_'.$cnt],$dt['dg_seq_'.$cnt],$encounter);
	
	} else if($form_mode == 'window') {
		if(isset($_GET['disp'])) $dt['tmp_diag_window_mode']=trim($_GET['disp']);
		$max= $dt['tmp_diag_cnt'];
		$cnt=1;
		while($cnt <= $max) {
			unset($dt['dg_link_'.$cnt]);
			$cnt++;
		}
	
	} else if($form_mode == 'fav') {
		$cnt = 0;
		if(isset($_GET['itemID'])) $cnt=trim($_GET['itemID']);
		if($cnt) {	
			$test = AddFavorite($dt['dg_type_'.$cnt],$dt['dg_code_'.$cnt],
				$dt['dg_plan_'.$cnt]);
		} else {
			$test = AddFavorite($dt['dg_type'],$dt['dg_code'],$dt['dg_plan']);
		}
	
	} else if($form_mode == 'auto') {
	
	} else if($form_mode == 'relink') {
		// sleep(2);
	
	} else if($mode_exists) {
		// HANDLE LOADING THE VARIOUS EXAM TYPES
		// LOAD THE MOST RECENT EXAM OF THE DESIRED TYPE
		$binds = array($frmdir, $pid, $form_mode, 0);
		$exam = array();
  	$sql = "SELECT form_id, formdir, form_type FROM forms ".
			"LEFT JOIN $frmn ON $frmn.id = form_id WHERE formdir=? ".
			"AND forms.pid=? AND form_type=? AND deleted=? ".
			"ORDER BY forms.date DESC LIMIT 1";
		$old = sqlQuery($sql, $binds);
		if(!isset($old{'form_id'})) $old{'form_id'} = '';;
  	if($old{'form_id'}) {
			$exam = wmtFormFetch($frmn, $old{'form_id'}, $pid);
			$exam = array_slice($exam, 9);
			foreach($exam as $key => $val) {
				if(substr($key,0,9) == 'approved_') {
					$dt{$key} = '';
				} else if($key == 'vid' || $key == 'form_dt' || $key == 'form_type') {
					// KEEP THE VITALS AND DATE ALREADY ATTACHED
 				} else { 
					$dt[$key] = $val;
				}
			}
		} else {
			$flds = sqlListFields($frmn);
			$flds = array_slice($flds, 9);
			foreach($flds as $fld) {
				if(substr($fld,0,9) == 'approved_') {
					$dt{$fld} = '';
				} else if($fld == 'vid' || $fld == 'form_dt' || $fld == 'form_type') {
					// KEEP THE VITALS AND DATE ALREADY ATTACHED
 				} else { 
					$dt[$fld] = '';
				}
			}
		}
		loadROSAndChecks($dt, $ros_module, $old{'form_id'}, $frmn);
		loadFormComments($dt, 'general_exam2', $old{'form_id'}, $frmn, $pid);
	} else if(!in_array($form_mode, $pass_modes)) {
			echo "<h>Unknown Mode, Called with ($form_mode)</h><br>\n";
			echo "<h>Exiting</h><br>\n";
			exit;
	}
	$img=GetImageHistory($pid, $encounter);
	$surg=GetList($pid, 'surgery', $encounter);
	$hosp=GetList($pid, 'hospitalization', $encounter);
	$inj=GetList($pid, 'wmt_inj_history', $encounter);
	$fh=GetFamilyHistory($pid,$encounter);
	$imm=GetAllImmunizationsbyPatient($pid,$encounter);
	$pmh=GetMedicalHistory($pid, $encounter);
	$allergies=GetList($pid, 'allergy', $encounter);
	if($use_meds_not_rx) { 
		$meds = GetList($pid, 'medication', $encounter);
		$med_hist = GetList($pid, 'med_history', $encounter);
	} else {
  	$meds = getLinkedPrescriptionsByPatient($pid, $encounter, '= 1');
  	$med_hist = getLinkedPrescriptionsByPatient($pid, $encounter, '< 1');
	}
	$save_style="/forms/$frmdir/new.php?mode=save&wrap=$wrap_mode&id=$id".
				"&enc=$encounter&pid=$pid";
	if($use_tasks) {
		$tasks = wmtFormTasks::getFormTasks($pid, $frmdir, $id, 'DESC', 'task');
	}
	// $vitals = new wmtVitals($dt{'vid'}, $suppress_decimal);
	$flds = sqlListFields('form_wmt_ros');
	//  THIS IS JUST FOR EMPTY CHECK BOXES TO KEEP THE ERROR LOG CLEAR
	foreach($flds as $lbl) {
		if(substr($lbl,-4) == '_hpi' || substr($lbl,-5) == '_none') {
			if(!isset($dt[$lbl])) $dt[$lbl] = '';
		}
	}
// RPG - NOT AN ELSE ANY MORE
// }
?>

<html>
<head>
<title><?php echo $ftitle; ?></title>
<!-- <style type="text/css">@import url(../../../library/dynarch_calendar.css);</style> -->
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.default.css" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<!-- link rel="stylesheet" href="<?php // echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtblue.css" type="text/css" -->
<!-- <link rel="stylesheet" href="<?php //echo $GLOBALS['assets_static_relative']; ?>/bootstrap-multiselect/dist/css/bootstrap-custom.css" type="text/css"> -->
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-multiselect/dist/css/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/init_ajax.inc.js"></script>

<script type="text/javascript">
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

// SECTION COLLAPSE CONTROL
$(document).ready(function() {
	// this routine opens and closes sections
	$('.wmtCollapseBar, .wmtBottomBar').click(function() {
		var key = $(this).attr('id');
		key = key.replace('BottomBar','');
		key = key.replace('Bar','');
		var id = '#' + key; 
		var toggle = '#tmp_'+key+'_disp_mode';
		if ($(id+'Box').is(':visible')) {
			$(id+'Box').hide();
			$(id+'Bar').addClass("wmtBarClosed");
			$(id+'Bar').children('img').attr("src","<?php echo $webroot;?>/library/wmt-v2/fill-270.png");
			$(id+'BottomBar').addClass("wmtBarClosed");
			$(id+'BottomBar').children('img').attr("src","<?php echo $webroot;?>/library/wmt-v2/fill-270.png");
			$(toggle).val('none');
		} else {
			$(toggle).val('block');
			$(id+'Bar').removeClass("wmtBarClosed");
			$(id+'Bar').children('img').attr("src","<?php echo $webroot;?>/library/wmt-v2/fill-090.png");
			$(id+'BottomBar').removeClass("wmtBarClosed");
			$(id+'BottomBar').children('img').attr("src","<?php echo $webroot;?>/library/wmt-v2/fill-090.png");
			$(id+'Box').show();
		}
	});
});

// Saving and print the whole form
function print_form()
{
	var target = '../../forms/<?php echo $frmdir; ?>/printable.php?id=<?php echo $id; ?>&pid=<?php echo $pid; ?>&pop=pop&enc=<?php echo $encounter; ?>';
	wmtOpen(target, '_blank', 600, 800);
}

// Saving and print instructions
function print_pat_problems()
{
	var target = '../../forms/<?php echo $frmdir; ?>/print_pat_problems.php?enc=<?php echo $encounter; ?>&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>&pop=pop';
	wmtOpen(target, '_blank', 600, 800);
}

// Saving and print instructions
function print_pat_instruct()
{
	var target = '../../forms/<?php echo $frmdir; ?>/print_instructions.php?enc=<?php echo $encounter; ?>&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>&pop=pop';
	wmtOpen(target, '_blank', 600, 800);
}

// Saving and print the patient summary 
function print_pat_summary()
{
	var target = '../../forms/<?php echo $frmdir; ?>/print_pat_summary.php?enc=<?php echo $encounter; ?>&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>&pop=pop&frmdir=<?php echo $frmdir; ?>';
	var target = '<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/print_pat_summary.php?enc=<?php echo $encounter; ?>&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>&pop=pop&frmdir=<?php echo $frmdir; ?>';
	<?php if($instruction_column) { ?>
	target += '&instruct=<?php echo $instruction_column; ?>';
	<?php } ?>
	<?php if($cc_column) { ?>
	target += '&cc=<?php echo $cc_column; ?>';
	<?php } ?>
	wmtOpen(target, '_blank', 600, 800);
}

// Saving and print referral letter 
function submit_ref_print(base, wrap, field, formID)
{
	var myAction = base+'&mode=save&wrap='+wrap+'&continue=ref_letter';
	if(formID != '' && formID != 0) myAction = myAction+'&id='+formID;
	document.forms[0].action = myAction;
	document.forms[0].submit();
}

// Saving and print referral letter
function print_referral_letter()
{
	var target = '../../forms/<?php echo $frmdir; ?>/print_referral.php?enc=<?php echo $encounter; ?>&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>&pop=pop';
	wmtOpen(target, '_blank', 600, 800);
}

function data_convert()
{
 wmtOpen('../../../interface/forms/<?php echo $frmdir; ?>/convert.php', '_blank', 500, 300);
}

// This is for callback by the HPI look-up  popup.
function set_hpi(hpiValue) {
 var f = document.forms[0];
 var decodedHpi = window.atob(hpiValue);
 if (decodedHpi) {
   f.elements['hpi'].value = decodedHpi;
 }
}

// This invokes the find-code popup.
function get_hpi() {
 wmtOpen('../../../custom/hpi_choice_popup.php', '_blank', 800, 400);
}

// This the 'Plan Favorites' popup window.
function get_favorite(plan_field, code_field) {
	var code = document.forms[0].elements[code_field].value;
	var type = '<?php echo $diagnosis_type; ?>';
	if(arguments.length > 2) {
		type = document.forms[0].elements[arguments[2]].value;
	}
	if(!code || code == '') {
		alert("Please choose a diagnosis code before searching for a plan");
		return false;
	}
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/favorites/new.php?choose=yes&ctype='+type+'&code='+code+'&target='+plan_field, '_blank', 800, 600);
}

// This is for callback by the Favorite look-up  popup.
function set_plan(plan_field, plan) {
 var decodedPlan = window.atob(plan);
 if (decodedPlan) {
   document.forms[0].elements[plan_field].value = decodedPlan;
 }
}

// This the Order/RTO popup window.
function PopRTO() {
 wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=yes&pid=<?php echo $pid; ?>', '_blank', 1200, 400);
}

function PopLinkUtility(linkHref) {
 alert("Link Action: "+linkHref);
}

// This invokes the refresh popup.
function refresh_links() {
 var link_base = '<?php echo $GLOBALS['webroot']; ?>/custom/link_refresh_popup.php?encounter=<?php echo $encounter; ?>&pid=<?php echo $pid; ?>';
 for (var cnt = 0; cnt < arguments.length; cnt++ ) {
		link_base = link_base + '&' + arguments[cnt] + '=true';
 }
 SetScrollTop();
 wmtOpen(link_base, '_blank', 400, 300);
 var refresh_action='<?php echo $base_action; ?>&mode=relink&wrap=<?php echo $wrap_mode; ?>';
 <?php
 if($id) {
	echo "refresh_action = refresh_action + '&id=$id';\n";
 }
 ?>
 document.forms[0].action= refresh_action;
 delayedHideDiv();
 document.forms[0].submit();
 window.location.reload;
}

function cancelClicked() {
	<?php
	if($warn_popup && strtolower($dt['form_complete']) != 'a') {
	?>
	response=confirm("Are you sure you wish to exit and discard your changes? Click 'OK' to continue to exit, or 'Cancel' to save or continue working on this form.");
	if(response == true) {
		<?php
		if(!$pop_form) {
		?>
		top.restoreSession();
		<?php
		}
		?>
		return true;
	} else {
		return false;
	}
	<?php
	} else {
	?>
	return true;
	<?php
	}
	?>
}

</script>

<!-- OEMR - Change -->
<style type="text/css">
	.wmtColorBar .wmtRight, .wmtColorBar .wmtLeft {
		width: 150px;
		max-width: 150px;
	}
	.configLink {
		text-transform: none!important;
		margin-right: 10px;
	}
	.global_copy_container {
		display: inline-block;
			float: right;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		var eleList = [
			'dg_begdt',
			'cp_dg_begdt',
			'dg_enddt',
			'cp_dg_enddt',
			'hosp_dt',
			'ps_begdate',
			'img_dt'
		];

		$.each(eleList, function( index, id ) {
			var ele = $("[name^='"+id+"']");
			if(ele.length > 0) {
				$.each(ele, function(inx, c_ele){
					$(c_ele).addClass('datepicker');
				});
			}
		});

		$('.datepicker').datetimepicker(Calendar.getParam({ifFormat:"%Y-%m-%d"}));

	});


	// This invokes the find-addressbook popup.
	function add_doc_popup(bar_id = '', tmp_barId = '', frmdir = '', encounter = '') {

		var url = '<?php echo $GLOBALS['webroot']."/interface/forms/ext_exam2/select_encounter.php?pid=". $pid; ?>'+'&bar_id='+bar_id+'&tmp_barId='+tmp_barId+'&frmdir='+frmdir+'&encounter='+encounter;
	  	let title = '<?php echo xlt('Select Encounter'); ?>';
	  	dlgopen(url, 'selectEncounter', 600, 400, '', title);
	}

	async function globalCopy(event, frmdir, encounter) {
		event.preventDefault();
        	event.stopPropagation();

        	add_doc_popup('global', 'global', frmdir, encounter);
	}

	async function copyConfig(event, tmp_barId, bar_id, frmdir, encounter) {
		event.preventDefault();
        	event.stopPropagation();

        	add_doc_popup(bar_id, tmp_barId, frmdir, encounter);
	}

	async function setEncounter(tmp_barId, bar_id, encounter_id, form_id, c_action) {
		//console.log(form_id+':'+encounter_id);
		
		await fetchExtExam(encounter_id, form_id, '<?php echo $pid; ?>', bar_id, c_action);

		if(tmp_barId != 'global' && tmp_barId != '') {
			var ele = $('#'+tmp_barId);
			if(ele.length > 0) {
				if($(ele).hasClass('wmtBarClosed') || !$('#'+bar_id+'Box').is(':visible')) {
					$(ele).click();
				}
			}
		}
	}

	async function fetchExtExam(encounterId, id, pid, bar_id, c_action) {
		var msg = "Load data from selected encounter exam form into this form? \n\n Current Data in the form will be overwritten.";

		if(bar_id != 'global') {
			var msg = "Load data from selected encounter exam form into this form section? \n\n Current Data in this form section will be overwritten.";
		}

		var confirmBox = confirm(msg);

		if(confirmBox != true) {
			return false;
		}

		if(bar_id != 'global') {
			var inputVals = $('#'+bar_id+'_request_data').val();
		} else {
			var inputVals = $('#global_request_data').val();
		}

		var valObj = {};
		if(inputVals != '') {
			valObj = JSON.parse(inputVals);
		}

		if(bar_id == 'global') {
			valObj['bar_id'] = 'global';
		}

		valObj['encounter_id'] = encounterId;
		valObj['e_id'] = id;
		valObj['pid'] = pid;
		valObj['c_action'] = c_action;

		const result = await $.ajax({
			type: "POST",
			url: "<?php echo $GLOBALS['webroot'].'/interface/forms/ext_exam2/ajax/fetch_ext_exam2.php'; ?>",
			datatype: "json",
			data: valObj
		});

		if(result != '' && confirmBox == true) {
			var resultObj = JSON.parse(result);
			
			if(bar_id != 'global') {
				extexam[bar_id](resultObj['formData'], bar_id);
			} else {
				var sectionList = [
					'cc',
					'hpi',
					'ros2',
					'ortho_exam',
					'general_exam2',
					'gyn_exam',
					'instruct',
					'assess',
					'diag'
				];
				$.each(extexam, function(i, fun){
					if(sectionList.includes(i)) {
						fun(resultObj['formData'], i);
					}
				});

				alert('Global Copy Done, Please Save');
			}
		}

		$('.datepicker').datetimepicker({
				<?php $datetimepicker_timepicker = false; ?>
	  		<?php $datetimepicker_showseconds = false; ?>
	 			<?php $datetimepicker_formatInput = true; ?>
	  		<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
	  		<?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
	}

	var extexam = {};

	extexam.cc = function(data, id) {
		setFielValue(data, id);
	}

	extexam.hpi = function(data, id) {
		setFielValue(data, id);
	}

	extexam.img = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box [name=img_dt]:last-child');
				if(ele.length > 0) {
					var row = ele.parent().parent();
					appendElement(row, row, item, id, (ind + 1));
				}
			});
		}
		
		setFielValue(data, id);
	}

	extexam.sh = function(data, id) {
		setFielValue(data, id);
	}

	extexam.all = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box span.all_title');
				if(ele.length > 0) {
					$.each(ele, function(inx, titleEle){
						var titleText = $(titleEle).html();
						var cnt = $(titleEle).data('id');
						if(item['all_title'] == titleText) {
							$.each(item, function(item_field, field_value){
								var setEle = $('#'+item_field+'_'+cnt);
								setInputVal(setEle, field_value);
							});
						}
					});
				}
			});
		}

		setFielValue(data, id);
	}

	extexam.ps = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box [name=ps_begdate]:last-child');
				if(ele.length > 0) {
					var row = ele.parent().parent();
					appendElement(row, row, item, id, (ind + 1));
				}
			});
		}
		setFielValue(data, id);
	}

	extexam.meds = function(data, id) {
		setFielValue(data, id);
	}

	extexam.med_hist = function(data, id) {
		setFielValue(data, id);
	}

	extexam.imm = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box span.cvx_code');
				if(ele.length > 0) {
					$.each(ele, function(inx, cEle){
						var cvxText = $(cEle).data('cvx');
						var cnt = $(cEle).data('id');
						if(item['imm_cvx_code'] == cvxText) {
							$.each(item, function(item_field, field_value){
								var setEle = $('#'+item_field+'_'+cnt);
								setInputVal(setEle, field_value);
							});
						}
					});
				}
			});
		}

		setFielValue(data, id);
	}

	extexam.well_full = function(data, id) {
		setFielValue(data, id);
	}

	extexam.hosp = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box [name=hosp_dt]:last-child');
				if(ele.length > 0) {
					var row = ele.parent().parent();
					appendElement(row, row, item, id, (ind + 1));
				}
			});
		}

		setFielValue(data, id);
	}

	extexam.pmh = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box [name=pmh_type]:last-child');
				if(ele.length > 0) {
					var row = ele.parent().parent();
					appendElement(row, row, item, id, (ind + 1));
				}
			});
		}
		
		setFielValue(data, id);
	}

	extexam.fh = function(data, id) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			$.each(data[id]['items'], function(ind, item){
				var ele = $('#'+id+'Box [name=fh_who]:last-child');
				if(ele.length > 0) {
					var row = ele.parent().parent();
					appendElement(row, row, item, id, (ind + 1));
				}
			});
		}
		
		setFielValue(data, id);
	}

	extexam.ros2 = function(data, id) {
		setFielValue(data, id);
	}

	extexam.ortho_exam = function(data, id) {
		setFielValue(data, id);
		if(data[id]['multi_list']) {
			var multi_list = data[id]['multi_list'];
			$.each(multi_list, function(field, values){
				var ele = $('#'+field);
				if(ele.length > 0) {
					ele.val(values);
				}
			});

			$(".select-picker").multiselect('destroy');
			$(".select-picker").multiselect({ maxWidth: '200px'});
		}
	}

	extexam.review_nt = function(data, id) {
		setFielValue(data, id);
	}

	extexam.general_exam2 = function(data, id) {
		if(data && data[id]) {
			$.each(data[id], function(field, value){
				if(field.startsWith("tmp_ge_")) {
					if(value == '1') {
						$('#'+field+'_disp').css("display", "block");
					} else {
						$('#'+field+'_disp').css("display", "none");
					}
				}
			});
		}
		setFielValue(data, id);
	}

	extexam.instruct = function(data, id) {
		setFielValue(data, id);
	}

	extexam.assess = function(data, id) {
		setFielValue(data, id);
	}

	extexam.diag = function(data, id, estatus = false) {

		if(data[id]['deleted_list']) {
			deleteList(id, data[id]['deleted_list']);
		}

		if(data[id]['items']) {
			var totalFields = $('#'+id+'Box .dg_code_field');
			$.each(data[id]['items'], function(ind, item){
				var cnt = ind + 1;
				if(estatus === true && totalFields.length > 0) {
					cnt = totalFields.length + cnt;
				}

				var ele = $('#'+id+'Box [name=dg_code]:last-child');
				if(ele.length > 0) {
					var row = ele.parent().parent();
					appendElement(row, row, item, id, cnt, true);
				}

				var ele1 = $('#'+id+'Box [name=dg_plan]:last-child');
				if(ele1.length > 0) {
					var row1 = ele1.parent().parent();
					appendElement(row1, row, item, id, cnt);
				}

				var ele2 = $('#'+id+'Box [name=dg_goal]:last-child');
				if(ele2.length > 0) {
					var row2 = ele2.parent().parent();
					appendElement(row2, row, item, id, cnt);
				}
			});
		}

		var tableEle = $('#'+id+'Box [name^="dg_code"], #'+id+'Box [name^="cp_dg_code"]');
		$.each(tableEle, function(ti, tr) {
			$(tr).parent().prev().html("&nbsp;"+(ti+1)+"&nbsp;).&nbsp;");
		});

		setFielValue(data, id);
	}

	function deleteList(bar_id, delList) {
		$.each(delList, function(i, item){
			$.each(item, function(ik, id){
				var ele = $("#"+bar_id+"Box [name^='"+i+"_'][value='"+id+"']");
				if(ele.length > 0) {
					if(bar_id == 'diag') {
						var row = $(ele).parent().parent();
						var row1 = $(row).next();
						var row2 = $(row1).next();

						$(row).remove();
						$(row1).remove();
						$(row2).remove();
					} else {
						$(ele).parent().parent().remove();
					}
				}
			});
		});
	}

	var setFielValue = function(data, id) {
		if(data && data[id]) {
			$.each(data[id], function(field, value){
				var ele = $('#'+id+'Box [name='+field+']');
				setInputVal(ele, value);
			});
		}
	}

	var appendElement = function(ele, insert, item, id = '', cnt = '1', seq = true) {
		var row = ele;
		var rowClone = row.clone();

		var cloneEles = rowClone.find('[name]');
		$.each(cloneEles, function(inx, cEle){
			var cName = $(cEle).attr('name');
			$(cEle).attr('name', 'cp_'+cName+'_'+(cnt));

			var cId = $(cEle).attr('id');
			$(cEle).attr('id', 'cp_'+cId+'_'+(cnt));

			if(cName == "dg_code") {
				$(cEle).attr("onclick",'get_diagnosis1("cp_dg_code_'+cnt+'","cp_tmp_dg_desc_'+cnt+'","cp_dg_begdt_'+cnt+'","cp_dg_title_'+cnt+'","cp_dg_type_'+cnt+'")');
				$(cEle).addClass("dg_code_field");
			}

			if(item.hasOwnProperty(cName)) {
				setInputVal($(cEle), item[cName]);
			}
		});

		if(id == 'diag') {
			var ele1 = rowClone.find('a.css_button_small');
			$.each(ele1, function(inx, cele1){
				if($(cele1).text() == "Save Plan" || $(cele1).text() == "Save Goal") {
					$(cele1).hide();
				}
			});
		}

		rowClone.insertBefore(insert);
	}

	var setInputVal = function(ele, eValue) {
		var value = eValue != "" ? decodeHtmlspecialChars(eValue) : "";

		if(ele.length > 0) {
			if($(ele).is("input:text")) {
				ele.val(value);
			} else if($(ele).is("select")) {
				$(ele).val(value);
				//$(ele).val(value).change();
			} else if($(ele).is("textarea")) {
				$(ele).val(value);
			} else if($(ele).is("input:checkbox")) {
				$.each(ele, function(inx, c_ele){
					if($(c_ele).val() == value) {
						$(c_ele).prop( "checked", true );
					} else {
						$(c_ele).prop( "checked", false );
					}
				});

				var eleName = $(ele).attr('name');
				if(eleName == 'db_sex_active') {
					TogglePair('sex_active_yes','sex_active_no');
				}
			}
		}
	}

	function set_selected_diag(items) {
		var frmdir = "<?php echo $frmdir; ?>";
		var boxId = "diag";

		if(frmdir == "dashboard") {
			boxId = "DBDiag";
		}

		var generateList = [];
		$.each(items, function(index, item) {
		    generateList.push({
		    	dg_begdt: "<?php echo date("m/d/Y") ?>",
				dg_code: item.itercode,
				dg_enddt: "",
				dg_id: "",
				dg_plan: "",
				dg_seq: "",
				tmp_dg_desc : item.title,
				dg_title: item.itercode+' - '+item.title,
		    });
		});
		

		if(generateList.length > 0) {
			var finalList = {};
			finalList[boxId] = {items : generateList};
			extexam.diag(finalList, boxId, true);
		}
	}

	function decodeHtmlspecialChars(text) {
		if(typeof text != "string") {
			return text;
		}

	    var map = {
	        '&amp;': '&',
	        '&#038;': "&",
	        '&lt;': '<',
	        '&gt;': '>',
	        '&quot;': '"',
	        '&#039;': "'",
	        '&#8217;': "’",
	        '&#8216;': "‘",
	        '&#8211;': "–",
	        '&#8212;': "—",
	        '&#8230;': "…",
	        '&#8221;': '”'
	    };

	    return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
	};
</script>
<!-- End -->

</head>

<?php
// Set all the window modes and the the diagnosis display the first time 
$relink_list = '';
foreach($modules as $module) {
	if($relink_list != '') $relink_list .= ',';
	$relink_list .= "'do_" . $module['option_id'] . "'";
}
$set_time_on_load='';
$save_notification_display = '';
$ge_sections = array('gen', 'head', 'eyes', 'ears', 'nose', 'mouth', 'throat',
	'neck', 'thyroid', 'lymph', 'breast', 'cardio', 'pulmo', 'gastro', 'neuro',
	'musc', 'ext', 'dia', 'test', 'rectal', 'skin', 'psych');
if($first_pass) {
	foreach($fh_options as $opt) {
		if($fh_old_style) {
			$dt['tmp_fh_rs_'.$opt] = '';
		} else {
			$dt['tmp_fh_rs_'.$opt['option_id']] = '';
		}
	}
	foreach($modules as $module) {
		$display_toggle = 'tmp_'.$module['option_id'].'_disp_mode';
		$dt[$display_toggle] = checkSettingMode('wmt::'.$display_toggle,'',$frmdir);
		if($dt[$display_toggle] == '') $dt[$display_toggle] = 'none';
	}
	$dt['tmp_med_window_mode']='all';
	if($max_med) $dt['tmp_med_window_mode'] = 'limit';
	$dt['tmp_med_link_mode']='link';
	$dt['tmp_mhist_window_mode']='all';
	if($max_med_hist) $dt['tmp_mhist_window_mode'] = 'limit';
	foreach($ge_sections as $section) {
		if($dt['tmp_ge_'.$section] == 1) {
			$dt['tmp_ge_'.$section.'_disp'] = 'block';
		} else {
			$dt['tmp_ge_'.$section.'_disp'] = 'none';
		}
		$dt['tmp_ge_'.$section.'_button_disp'] = 'none';
	}
	$dt['tmp_cessation_disp_mode'] = 'none';
	$dt['tmp_diag_window_mode']='encounter';
	$dt['tmp_scroll_top'] = '';
	$save_notification_display = 'display: none;';
	$form_focus = 'cc';
 	$dt['rto_num']= $dt['rto_frame']= $dt['rto_nt']= '';
	// SET THESE DEFAULTS ON THE FIRST PASS THROUGH A NEW FORM
	if($form_mode == 'new') {
 		$dt['form_dt']= date('Y-m-d');
 		$dt['form_dt']= $visit->encounter_date;
		$dt['rec_review']=1;
		$dt['form_complete']= $dt['form_priority']='';
		if($client_id == 'hcardio') { 
			$dt{'hpi'} = '';
			$dt['instruct'] = '';
			$dt{'assess'} = '';
			$dt{'plan'} = '';
		}
		// $vitals = wmtVitals::getVitalsByEncounter($encounter, $pid, $suppress_decimal);
		// $dt['vid'] = $vitals->vital_id;
	} else {
		// Not a new form, grab the related vitals
		// $vitals = new wmtVitals($dt{'vid'}, $suppress_decimal);
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
	// Set up the family history extras from the yes and no list
	if($dt['db_fh_extra_yes']) {
		$fh_yes = explode('|', $dt['db_fh_extra_yes']);
		foreach($fh_options as $opt) {
			if($fh_old_style) {
				if(in_array('tmp_fh_rs_'.$opt, $fh_yes)) $dt['tmp_fh_rs_'.$opt] = 'y';
			} else {
				if(in_array($opt['option_id'], $fh_yes)) $dt['tmp_fh_rs_'.$opt['option_id']] = 'y';
			}
		}
	}
	if($dt['db_fh_extra_no']) {
		$fh_no = explode('|', $dt['db_fh_extra_no']);
		foreach($fh_options as $opt) {
			if($fh_old_style) {
				if(in_array('tmp_fh_rs_'.$opt, $fh_no)) $dt['tmp_fh_rs_'.$opt] = 'n';
			} else {
				if(in_array($opt['option_id'], $fh_no)) $dt['tmp_fh_rs_'.$opt['option_id']] = 'n';
			}
		}
	}
	$dt['db_pat_ref_provderID'] = $patient->ref_providerID;
	$fyi= wmtFYI::getPidFYI($pid);
	// RPG - THIS IS THE FYI FIX AREA
	foreach($fyi as $key => $val) {
		if(substr($key,-3) == '_nt') $dt[$key] = $val;
	}
	$pqrs_selected = array();
  $pqrs = sqlQuery("SELECT * FROM wmt_pqrs WHERE link_id=? AND ".
				"link_name=?", array($id, $frmdir));
	if($pqrs{'pqrs_choices'}) $pqrs_selected = explode('|',$pqrs{'pqrs_choices'});
	$lifestyle = wmtLifestyle::getFormLifestyle($pid, $frmdir, $id);
	if(!$lifestyle->id) {
		$lifestyle = wmtLifestyle::getRecentLifestyle($pid);
	}
	if($lifestyle->id) {
		foreach($lifestyle as $key => $val) {
			if($key != 'id' && $key != 'pid' && $key != 'link_form' && 
					$key != 'link_id' && $key != 'form_dt') $dt['db_'.$key] = $val;
		}
	}
	// Some patient file things we have to grab
	$dt['db_pat_blood_type'] = $patient->blood_type;
	$dt['db_pat_rh_factor'] = $patient->rh_factor;
	$dt['db_pat_ref_providerID'] = $patient->ref_providerID;
}

$fh_defaults=GetFamilyHistoryDefaults($pid);
$diag=GetProblemsWithDiags($pid,$dt['tmp_diag_window_mode'],$encounter);
foreach($ge_sections as $section) {
	if(!isset($dt['tmp_ge_'.$section])) $dt['tmp_ge_'.$section] = 0;
}

$scroll_point = ''; 
if($dt['tmp_scroll_top']) $scroll_point = $dt['tmp_scroll_top'];
if($scroll_point) $load .= " window.scrollTo(0,$scroll_point);";
if($form_focus) $load .= " AdjustFocus('$form_focus');";
if($continue && $pop_form) $load .= " refreshVisitSummary();";
if($continue == 'instruct') $load .= " print_pat_instruct($id);";
if($continue == 'summary') $load .= " print_pat_summary($id);";
if($continue == 'problems') $load .= " print_pat_problems($id);";
if($continue == 'ref_letter') $load .= " print_referral_letter($id);";
if($continue == 'print') $load .= " print_form($id);";
if(!$save_notification_display) $load .= " delayedHideDiv();";

//Process After Save
ext_process_after_fetch($pid);

?>

<?php include($GLOBALS['srcdir'].'/wmt-v2/floating_menu.inc.php'); ?>
<body class="formBodyLight" onload="<?php echo $load; ?>">
<?php include($GLOBALS['srcdir'].'/wmt-v2/processing_msg.inc.php'); ?>
<?php include($GLOBALS['srcdir'].'/wmt-v2/task_msg.inc.php'); ?>

<div id="overDiv" style="position:absolute; visibility: hidden; z-index:1000;"></div>
<form action="<?php echo $GLOBALS['rootdir'].$save_style; ?>" method="post" enctype="multipart/form-data" name="<?php echo $frmdir; ?>">
<div class="formBodyLight" style="margin: 0px; padding: 10px; width: 100%">
<div style="border: solid 1px red; margin: 34px 0px 0px 0px; padding: 4px;">
<table width="100%"  border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td class="wmtLabel" style="width: 50px">Date:</td>
		<td style="width: 100px"><input name="form_dt" type="text" style="width: 100px" class="InputBordB" value="<?php echo $dt{'form_dt'}; ?>" /></td>
    <td class="wmtLabel" style="width: 100px">Patient Name:</td>
		<td><input name="pname" type="text" class="InputBordB" style="width: 95%" disabled="disabled" value="<?php echo $patient->full_name; ?>" /></td>
    <td class="wmtLabel" style="width: 50px">ID No:</td>
		<td style="width: 100px"><input name="pid" type="text" class="InputBordB" style="width: 100px" disabled="disabled"  value="<?php echo $patient->pubpid; ?>"></td>
  </tr>
	<tr>
		<td colspan="2" class="wmtLabel">Referring Physician:</td>
		<?php if(checkSettingMode('wmt::allow_referral_update','',$frmdir)) { ?>
		<td colspan="2"><select name="db_pat_ref_providerID" id="db_pat_ref_providerID" class="InputBordB">
			<?php ReferringSelect($dt{'db_pat_ref_providerID'}); ?>
		</select></td>
		<?php } else { ?>
		<td colspan="2" class="wmtBody"><?php echo $patient->referral_full_name; ?></td>
		<?php } ?>
		<td class="wmtLabel">Occupation:</td>
		<?php if(checkSettingMode('wmt::allow_occupation_update','',$frmdir) || true) { ?>
		<td><input name="db_pat_occupation" id="db_pat_occupation" class="InputBordB" style="width: 98%;" type="text" value="<?php echo htmlspecialchars($patient->occupation,ENT_QUOTES,'',FALSE); ?>" />
		</td>
		<?php } else { ?>
		<td class="wmtBody"><?php echo $patient->occupation; ?></td>
		<?php } ?>
	</tr>
</table>
</div>


<?php
include($GLOBALS['srcdir'].'/wmt-v2/exam_type.inc.php');
//  Create the exam sections from the master list
include($GLOBALS['srcdir'].'/wmt-v2/form_loop.inc.php');
?>

<div style="display: none"><!-- For hidden fields -->
<input name="tmp_scroll_top" id="tmp_scroll_top" type="hidden" value="<?php echo $dt['tmp_scroll_top']; ?>" />
<?php 

foreach($ge_sections as $section) {
	$disp_section = 'tmp_ge_'.$section.'_disp';
	$disp_button = 'tmp_ge_'.$section.'_button_disp';
	echo "<input name='$disp_section' id='$disp_section' type='hidden' value='",$dt[$disp_section],"' />\n";
	echo "<input name='$disp_button' id='$disp_button' type='hidden' value='",$dt[$disp_button],"' />\n";
}
?>
<input name="tmp_med_window_mode" id="tmp_med_window_mode" type="hidden" value="<?php echo $dt['tmp_med_window_mode']; ?>" />
<input name="tmp_med_link_mode" id="tmp_med_link_mode" type="hidden" value="<?php echo $dt['tmp_med_link_mode']; ?>" />
<input name="tmp_mhist_window_mode" id="tmp_mhist_window_mode" type="hidden" value="<?php echo $dt['tmp_mhist_window_mode']; ?>" />
<input name="tmp_diag_window_mode" id="tmp_diag_window_mode" type="hidden" value="<?php echo $dt['tmp_diag_window_mode']; ?>" />
<?php 
// This builds the defaults for javascript to reference
// if they are adding to family history
foreach($fh_defaults as $who => $what) {
  echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."' type='hidden' value='".$what{'fhm_who'}."' />\n";
  echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."_dead' type='hidden' value='".$what{'fhm_deceased'}."' />\n";
  echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."_age' type='hidden' value='".$what{'fhm_age'}."' />\n";
  echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."_age_dead' type='hidden' value='".$what{'fhm_age_dead'}."' />\n";
}
?>
</div>

<?php if($client_id == 'uimda') { ?>
<table width="100%" border="0">
	<tr>
		<td class="wmtLabel"><input name="reviewed" id="reviewed" type="checkbox" value="1" <?php echo (($dt{'reviewed'} == '1')?' checked ':''); ?> />The information above was thoroughly reviewed with the patient.</td>
	</tr>
	<tr>
    <td class="wmtLabel">RTO:&nbsp;&nbsp;<input name="return_chk" id="return_chk" type="checkbox" value="1" <?php echo (($dt{'return_chk'} == '1')?' checked ':''); ?> /><label for="return_chk" class="wmtBody">&nbsp;Return as Needed</label>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;OR&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<select name="rto_num" id="rto_num" class="Input"><?php ListSel($dt{'rto_num'}, 'RTO_Number'); ?></select>&nbsp;&nbsp;<select name="rto_frame" id="rto_fram" class="Input"><?php ListSel($dt{'rto_frame'}, 'RTO_Frame'); ?></select><span class="wmtBody">&nbsp;&nbsp;Sooner if Worsens or PRN</span></td>
		<td class="wmtLabel"><div style="float: right; padding-right: 15px;"><input name="lab_pend" id="lab_pend" type="checkbox" value="1" <?php echo (($dt{'lab_pend'} == '1')?' checked ':''); ?> />&nbsp;Pending Test Results</div></td>
	</tr>
</table>
<?php } ?>
<table width="100%" border="0">
  <tr>
   	<td><a href="javascript:<?php echo $pop_form ? '' : 'top.restoreSession(); '; ?>document.forms[0].submit();" tabindex="-1" class="css_button"><span>Save Data</span></a> </td>
   	<td><a href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>');" tabindex="-1" class="css_button" <?php echo $pop_form ? "" : "onclick='top.restoreSession();'"; ?> ><span>Printable Form</span></a></td>

    <td class="wmtLabel">Form Status:&nbsp;
      <select name="form_complete" id="form_complete" class="wmtInput1" onChange="VerifyApproveForm('form_complete','<?php echo $approve_popup; ?>');">
        <?php ApprovalSelect($dt['form_complete'],'Form_Status',$id,'i',$approval['allowed']); ?>
      </select>
    </td>
    <td class="wmtLabel">Form Priority:&nbsp;
      <select name="form_priority" id="form_priority" class="wmtInput1">
        <?php FlagListSel($dt['form_priority'],'Form_Priority',$id,'n'); ?>
      </select>
    </td>

    <td><div style="float: right; padding-right: 10px;"><a href="<?php echo (($pop_form)?'javascript:window.close();':$GLOBALS['form_exit_url']); ?>" class="css_button" tabindex="-1" onclick="return cancelClicked()" ><span>Cancel</span></a></div></td>
  </tr>
	<tr>
		<td colspan="4" class="LabelRed"><?php echo $visit->signed_by; ?></td>
	</tr>
</table>
</div><!-- This is the end of the overall margin div -->
</form>
</body>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ee2form.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/approve.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.popup.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.forms.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/diagnosis.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/form_js/ortho_exam.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js" type="text/javascript"></script>
<?php if($portal_enabled) { ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtportal.js" type="text/javascript"></script>
<?php } ?>
<?php if(checkSettingMode('wmt::use_mce_edit','',$frmdir)) { ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/tinymce/tinymce.min.js" type="text/javascript"></script>
<script type="text/javascript">
tinymce.init({
	selector: "textarea.mce"
});
</script>
<?php } ?>
<?php if($ros_module == 'ros2' || $ros_module == 'ent_ros') { ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ros_functions.js" type="text/javascript"></script>
<?php } else { ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/old_ros.js" type="text/javascript"></script>
<?php } ?>
<?php if(!$pop_form) { ?>
<script>
		<?php include_once($GLOBALS['srcdir']."/restoreSession.php"); ?> 
</script>
<?php } ?>
<script language="javascript">
// var dt = document.getElementsByTagName('img');
// for (i = 0; i < dt.length; i++) {
	// if(dt[i].id.indexOf('img_') == 0) {
		// if(dt[i].id.indexOf('_dt') != -1) {
			// var target = dt[i].id.slice(4, -3);
			// alert("Build Calendar Element: "+dt[i].id+"   For Target: "+target);
			// Calendar.setup({inputField:target, ifFormat:"%Y-%m-%d", button:dt[i].id});
		// }
	// }
// }
</script>
</html>
