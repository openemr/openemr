<?php
$fake_register_globals=false;
$sanitize_all_escapes=true;
$frmn = 'form_dashboard';
$frmdir = 'dashboard';
include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['srcdir'].'/calendar.inc');
include_once($GLOBALS['srcdir'].'/lists.inc');
include_once($GLOBALS['srcdir'].'/pnotes.inc');
include_once($GLOBALS['srcdir'].'/classes/Document.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtform.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtvitals.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/lifestyle.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/favorites.inc');


use OpenEMR\Core\Header;

$pat_display=checkSettingMode('wmt::db_pat_display');
if(!$pat_display) $pat_display = 'block';
$form_event_logging=checkSettingMode('wmt::form_logging','',$frmdir);
$social_display=checkSettingMode('wmt::db_social_history');
$expanded_sh = '';
$use_lifestyle = $use_cessation = $use_ace = $use_t_ace = false;
$use_audit = $use_coffee = $expanded_sh = false;
if($social_display) {
	$use_lifestyle = checkSettingMode('wmt::use_lifestyle','',$frmdir);
	if($use_lifestyle) {
		$use_cessation = checkSettingMode('wmt::smoking_cessation','',$frmdir);
		$use_ace = checkSettingMode('wmt::alcohol_ace','',$frmdir);
		$use_t_ace = checkSettingMode('wmt::alcohol_t_ace','',$frmdir);
		$use_audit = checkSettingMode('wmt::alcohol_audit','',$frmdir);
	}
	$use_coffee = checkSettingMode('wmt::sh_coffee','',$frmdir);
	$expanded_sh = checkSettingMode('wmt::sh_expanded','',$frmdir);
}
$wellness_display = checkSettingMode('wmt::db_wellness');
$medication_display = checkSettingMode('wmt::db_medications');
$medhist_display = checkSettingMode('wmt::db_medication_history');
$otc_display = checkSettingMode('wmt::db_otc');
$allergy_display = checkSettingMode('wmt::db_allergies');
$dated_doc_display = checkSettingMode('wmt::db_dated_documents');
$pp_display = checkSettingMode('wmt::db_ob_history');
$surgery_display = checkSettingMode('wmt::db_surgeries');
$mh_display = checkSettingMode('wmt::db_medical_history');
$fh_display = checkSettingMode('wmt::db_family_history');
if($fh_display) {
	$fh_options = LoadList('Family_History_Choices','active');
	$fh_options_unused = LoadList('Family_History_Choices','active');
	$num_fh_options = count($fh_options);
	$fh_old_style = false;
	if(!$num_fh_options) {
		$opt = checkSettingMode('wmt::fh_options','',$frmdir);
		$fh_options = explode('|', $opt);
		$num_fh_options = count($fh_options);
		$fh_old_style = true;
	}
}
$repo_display = checkSettingMode('wmt::db_reproductive1');
if($repo_display) {
	$repo = sqlQuery("SELECT * FROM form_reproductive1 WHERE pid = ? ORDER BY form_dt DESC LIMIT 1", array($pid));
	if(!$repo) {
		$flds = sqlListFields('form_reproductive1');
		$flds = array_slice($flds, 10);
		$repo = array();
		foreach($flds as $lbl) { $repo[$lbl] = ''; }
	}
}
$immunization_display = checkSettingMode('wmt::db_immunizations');
$image_display = checkSettingMode('wmt::db_images');
$birth_history_display = checkSettingMode('wmt::db_birth_history');
$admission_display = checkSettingMode('wmt::db_admissions');
$ultrasound_display = checkSettingMode('wmt::db_ultrasounds');
$pap_display = checkSettingMode('wmt::db_pap_track');
$bd_display = checkSettingMode('wmt::db_bone_density');
$ped_diet_display = checkSettingMode('wmt::db_pediatric_diet');
$hepc_display = checkSettingMode('wmt::db_hepc');
$ros_display = checkSettingMode('wmt::db_ros');
$hepc_print_option = true;
$hide_ped_diet_clear_button = true;
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
$diagnosis_display = checkSettingMode('wmt::db_diagnosis');
$client_id = checkSettingMode('wmt::client_id');
if(isset($GLOBALS['wmt::client_id'])) $client_id = $GLOBALS['wmt::client_id'];
$ros_module = checkSettingMode('wmt::ee1_ros_module');
if($ros_module == 'ros2') $ros_options = LoadList('Ext_ROS_Keys');
$form_focus = '';
$max_med_hist = false;
$tst = checkSettingMode('wmt::max_med_hist');
if($tst) $max_med_hist = $tst;
$max_med = false;
$tst = checkSettingMode('wmt::max_med');
if($tst) $max_med = $tst;
if(!class_exists('wmtDashboard')) include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
$warn_popup = false;

if($pap_display) include_once($GLOBALS['srcdir'].'/wmt-v2/pap_track.inc');
if($ultrasound_display) include_once($GLOBALS['srcdir'].'/wmt-v2/us_track.inc');
if($bd_display) include_once($GLOBALS['srcdir'].'/wmt-v2/bd_track.inc');
$patient = wmtPatData::getPidPatient($pid);
$pat_sex = strtolower(substr($patient->sex,0,1));
$dashboard = wmtDashboard::getPidDashboard($pid);
$id = $dashboard->id;
$diagnosis_type='ICD9';
if(isset($GLOBALS['wmt::default_diag_type'])) $diagnosis_type = $GLOBALS['wmt::default_diag_type'];
$portal_enabled = false;
if(isset($GLOBALS['wmt::pat_entry_portal'])) $portal_enabled = $GLOBALS['wmt::pat_entry_portal'];
$show_ob_totals=false;
if(isset($GLOBALS['wmt::show_ob_totals'])) $show_ob_totals = $GLOBALS['wmt::show_ob_totals'];
$lab_enabled = false;
if(isset($GLOBALS['wmt_lab_enable'])) $lab_enabled = $GLOBALS['wmt_lab_enable'];
$use_sequence = $continue = $pop_form = false;
if(isset($_GET['continue'])) $continue = strip_tags($_GET['continue']);
$first_pass = true;
$no_unlink = true;
$diag_bar_bottom = false;
$form_mode = $wrap_mode = 'update'; 
if(isset($_SESSION['pid'])) $pid = strip_tags($_SESSION['pid']);
if(isset($_GET['id'])) $id = strip_tags($_GET['id']);
if(isset($_GET['mode']) || $continue) { 
	$form_mode = $_GET['mode'];
	$first_pass = false;
}
if($pid == '' || $pid == 0) ReportMissingPID();
$base_action = "../../../interface/forms/dashboard/new.php?pid=$pid";
if($id) $base_action .= "&id=$id";

$dt = array();
$rs = array();
$unlink_allow = $delete_allow = false;


// Load any patient-entered data for the doctor to view
$pat_entries_exist = false;
if($portal_enabled) {
	$pat_entries = array();
	$sql = "SELECT * FROM wmt_portal_data WHERE pid=? AND form_name=?";
	$binds = array($pid, 'portal');
	$fres= sqlStatement($sql, $binds);
	for($iter=0; $frow=sqlFetchArray($fres); $iter++) {
		$pat_entries[$frow{'field_name'}] = $frow;
	}
	if($iter) $pat_entries_exist = true;
}

if($form_mode == 'update' || $form_mode == 'new') {
	foreach($dashboard as $key => $val) { 
		if($key == 'id' || $key == 'pid') continue;
		$dt[$key] = $val;
		// Delete this on new clients
		if($key == 'db_hcg') $dt['db_HCG'] = $val;
	}
	foreach($patient as $key => $val) { 
		if($key == 'id' || $key == 'pid') continue;
		$dt['pat_'.$key] = $val;
	}
	if($repo_display) {
		$dt['tmp_form_dt'] = 'None on File';
		if($repo['form_dt']) $dt['tmp_form_dt'] = $repo['form_dt'];
		$repo = array_slice($repo, 11);
		foreach($repo as $key => $val) { $dt[$key] = $val; }
		$dt['tmp_rp1_chc_y'] = $repo['rp1_bc_chc'];
		$dt['tmp_rp1_chc_n'] = $repo['rp1_bc_chc'];
		$dt['tmp_rp1_bc_y'] = $repo['rp1_bc'];
		$dt['tmp_rp1_bc_n'] = $repo['rp1_bc'];
	}
	// FIX!! Link ROS of either type into the dashboard yet
}

$save_style = "/forms/dashboard/new.php?mode=save&id=$id&pid=$pid";
if($form_mode == 'save') {
	unset($ros);
	unset($data);
	unset($dt);
	unset($life);
	unset($prgm);
	$pat['blood_type'] = $pat['rh_factor'] = '';
	$fh_extra_yes = explode('|', $dashboard->db_fh_extra_yes);
	$fh_extra_no = explode('|', $dashboard->db_fh_extra_no);
	$lifestyle_data_entered = false;
	$max_diags= $max_pap= $max_pp= $max_us= $max_bd= $max_otc= 0;

	foreach ($_POST as $k => $var) {
  	if($var == 'YYYY-MM-DD') $var = '';
		if(is_string($var)) $var = trim($var);
		if(substr($k,0,7) == 'ee1_rs_') {
      $rs[$k] = $var;
		} else {
     	$dt[$k] = $var;
		}
  	if(($k != 'pname') && ($k != 'pid') && ($k != 'date') &&
     	(substr($k,0,3) != 'fh_') && (substr($k,0,3) != 'pp_') &&
     	(substr($k,0,4) != 'all_') && (substr($k,0,3) != 'ps_') &&
     	(substr($k,0,4) != 'med_') && (substr($k,0,4) != 'pmh_') &&
			(substr($k,0,4) != 'rto_') && (substr($k,0,3) != 'pt_') &&
			(substr($k,0,4) != 'tmp_') && (substr($k,0,3) != 'dg_') &&
			(substr($k,0,4) != 'imm_') && (substr($k,0,4) != 'img_') &&
			(substr($k,0,3) != 'bd_') && (substr($k,0,3) != 'us_') &&
			(substr($k,0,5) != 'hosp_') && (substr($k,0,9) != 'pad_diet_') &&
			(substr($k,0,7) != 'ee1_rs_') && (substr($k,0,4) != 'pat_') && 
			($k != 'blood_type') && ($k != 'rh_factor') &&
			(substr($k,0,7) != 'db_pat_') && (substr($k,0,9) != 'db_sh_ex_') && 
			(substr($k,0,4) != 'rp1_') && (substr($k,0,6) != 'db_lf_') && 
			(substr($k,0,5) != 'prgm_') && (substr($k,0,5) != 'ddoc_') && 
     	(substr($k,0,3) != 'fyi') && (substr($k,0,6) != 'db_vid')) {
      	$data[$k] = $var;
  	}
  	if(substr($k,0,4) == 'tmp_') $tmp[$k] = $var;
  	if(substr($k,0,7) == 'ee1_rs_') $ros[$k] = $var;
  	if(substr($k,0,8) == 'med_hist') $mhist[$k] = $var;
  	if(substr($k,0,4) == 'med_' && substr($k,0,5) != 'med_h') $med[$k] = $var;
  	if(substr($k,0,9) == 'tmp_hepc_') $hepc_risks[] = $var;
  	if(substr($k,0,4) == 'all_') $allergy[$k] = $var;
  	if(substr($k,0,3) == 'pp_') $pp[$k] = $var;
  	if(substr($k,0,3) == 'ps_') $surg[$k] = $var;
  	if(substr($k,0,4) == 'pmh_') $pmh[$k] = $var;
		// An exception for blood type which is in the wellness module but stored
		// in patient information
		if(substr($k,0,7) == 'db_pat_') $k = substr($k,3);
		if(substr($k,0,9) == 'db_sh_ex_') $sh_ex[substr($k,9)] = $var;
		if(substr($k,0,6) == 'db_lf_') {
			$life[substr($k,3)] = $var;
			if($var != '') $lifestyle_data_entered = true;
		}
  	if(substr($k,0,4) == 'pat_') {
			$pat[substr($k,4)] = $var;
			// echo "Setting ",substr($k,4),"  To $var<br>\n";
		}
  	if(substr($k,0,9) == 'pad_diet_') {
			// echo "Setting $k Value: ",$dt[$k],"<br>\n";
			if($k == 'pad_diet_form') { 
				$data['db_ped_diet_f'] = $dt[$k];
				$ad['db_ped_diet_f'] = $dt[$k];
			} else if($k == 'pad_diet_fed') {
				$data['db_ped_diet_b'] = $dt[$k];
				$ad['db_ped_diet_b'] = $dt[$k];
			} else if($k == 'pad_diet_oth') {
				$data['db_ped_diet_o'] = $dt[$k];
				$ad['db_ped_diet_o'] = $dt[$k];
			} else if($k == 'pad_diet_require') {
				$data['db_ped_diet_special'] = $dt[$k];
				$ad['db_ped_diet_special'] = $dt[$k];
			} else if($k == 'pad_diet_nt') {
				$data['db_ped_diet_other'] = $dt[$k];
				$ad['db_ped_diet_other'] = $dt[$k];
			} else if($k == 'pad_diet_type_nt') {
				$data['db_ped_diet_nt'] = $dt[$k];
				$ad['db_ped_diet_nt'] = $dt[$k];
			} else {
				$key = 'db_ped_'.substr($k,4);
				$data[$key] = $dt[$k];
				$ad[$key] = $dt[$k];
			}
  	} 
  	// FAMILY HISTORY 
  	if(substr($k,0,3) == 'fh_') $fh[$k] = $var;
		// CHECK LIST FOR SAVED OPTIONS RESPECTING OTHER ENTRIES THAT MAY BE THERE
		if(substr($k,0,10) == 'tmp_fh_rs_') {
			$opt = substr($k,10);
			if(substr($opt, -3) == '_nt') { 
				ProcessROSKeyComment($pid, $id, $frmdir, 'fh_rs_'.$opt, $var);
			} else { 
				if(strtolower(substr($var,0,1)) == 'y') {
					if($fh_old_style) {
					} else if(TRUE) {
						if(!in_array($opt, $fh_extra_yes)) $fh_extra_yes[] = $opt;
						$tst = array_search($opt, $fh_extra_no);
						if($tst !== FALSE) unset($fh_extra_no[$tst]);
					// THIS WAS THE OLD PROCESSING STYLE, JUST SAVED HERE
					} else {
						if(!isset($data['db_fh_extra_yes'])) $data['db_fh_extra_yes'] = '';
						$data['db_fh_extra_yes'] = AppendItem($data['db_fh_extra_yes'], substr($k,10), false, '|');
					}
				} else if(strtolower($var) == 'n') {
					if($fh_old_style) {
					} else if(TRUE) {
						if(!in_array($opt, $fh_extra_no)) $fh_extra_no[] = $opt;
						$tst = array_search($opt, $fh_extra_yes);
						if($tst !== FALSE) unset($fh_extra_yes[$tst]);
					} else {
						if(!isset($data['db_fh_extra_no'])) $data['db_fh_extra_no'] = '';
						$data['db_fh_extra_no'] = AppendItem($data['db_fh_extra_no'], substr($k,10), false, '|');
					}
				// HERE WE NEED TO MAKE SURE IT'S NOT IN EITHER LIST
				} else {
					$tst = array_search($opt, $fh_extra_yes);
					if($tst !== FALSE) unset($fh_extra_yes[$tst]);
					$tst = array_search($opt, $fh_extra_no);
					if($tst !== FALSE) unset($fh_extra_no[$tst]);
				}
			}
		}
  	if(substr($k,0,3) == 'pt_') $pap[$k] = $var;
  	if(substr($k,0,4) == 'imm_') $imm[$k] = $var;
  	if(substr($k,0,4) == 'otc_') $otc[$k] = $var;
  	if(substr($k,0,4) == 'img_') $img[$k] = $var;
  	if(substr($k,0,3) == 'us_') $us[$k] = $var;
  	if(substr($k,0,3) == 'bd_') $bd[$k] = $var;
  	if(substr($k,0,5) == 'hosp_') $hosp[$k] = $var;
  	if(substr($k,0,3) == 'dg_') $diag[$k] = $var;
  	if(substr($k,0,3) == 'fyi') $fyi[$k] = $var;
  	if(substr($k,0,5) == 'ddoc_') $ddoc[$k] = $var;
		if($k == 'tmp_diag_cnt') $max_diags=$var;
		if($k == 'tmp_pap_cnt') $max_pap=$var;
		if($k == 'tmp_pp_cnt') $max_pp=$var;
		if($k == 'tmp_us_cnt') $max_us=$var;
		if($k == 'tmp_bd_cnt') $max_bd=$var;
		if($k == 'blood_type') $pat[$k]=$var;
		if($k == 'rh_factor') $pat[$k]=$var;
	}
	// A couple things just to keep errors out of the log
	if(!isset($data['db_pregnancies'])) $data['db_pregnancies'] = '';
	if(!isset($data['db_deliveries'])) $data['db_deliveries'] = '';
	if(!isset($data['db_live_births'])) $data['db_live_births'] = '';

	if($allergy_display) {
		if($allergy_add_allowed) {
 			$all_id=AddAllergy($pid,$allergy['all_begdate'],$allergy['all_title'],
						$allergy['all_comm'],$allergy['all_react']);
			$dt['all_begdate']=$dt['all_title']=$dt['all_react']= $dt['all_comm']= '';
		}
	}
	if($use_meds_not_rx) {
		if(isset($med['med_begdate'])) {
  		$med_id = AddMedication($pid,$med['med_begdate'],$med['med_title'],
					$med['med_enddate'],$med['med_dest'],$med['med_comm']);
			if($med_id) LinkListEntry($pid, $med_id, $encounter, 'medication');
			$dt['med_begdate'] = $dt['med_title'] = $dt['med_enddate'] = '';
			$dt['med_dest'] = $dt['med_comm'] = '';
		}
	}

	if(isset($dt['otc_begdate'])) {
		$extra = '';
		if(is_array($dt['otc_when'])) $extra = implode('^|', $dt['otc_when']);
  	$med_id = AddMedication($pid,$dt['otc_begdate'],$dt['otc_title'],
				$dt['otc_enddate'],'',$dt['otc_comm'],'1','medication_otc',$extra,
				$dt['otc_referredby'],$dt['otc_type']);
		if($med_id) LinkListEntry($pid, $med_id, $encounter, 'medication_otc');
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

	if(isset($surg['ps_title'])) {
  	$surg_id=AddSurgery($pid,$surg['ps_begdate'],$surg['ps_title'],
			$surg['ps_comments'],$surg['ps_referredby'],$surg['ps_hospitalized']);
		$dt['ps_begdate']=$dt['ps_title']=$dt['ps_hospitalized']='';
		$dt['ps_comments']=$dt['ps_referredby']='';
	}

	if(isset($fh['fh_who'])) {
  	$fh_id=AddFamilyHistory($pid,$fh['fh_who'],$fh['fh_type'],$fh['fh_nt'],
			$fh['fh_dead'],$fh['fh_age'],$fh['fh_age_dead']);
		$dt['fh_who']= $dt['fh_dead']= $dt['fh_age']= $dt['fh_age_dead']= '';
		$dt['fh_type']= $dt['fh_nt']= '';
	}

	if(isset($pmh['pmh_type'])) {
  	$mh_id=AddMedicalHistory($pid,$pmh['pmh_type'],'',$pmh['pmh_nt'],$pmh['pmh_hospitalized'],1);
		$dt['pmh_type']= $dt['pmh_nt']=$dt['pmh_hospitalized']='';
	}

	if(isset($img['img_type'])) {
  	$img_id=AddImageHistory($pid,$img['img_type'],$img['img_dt'],$img['img_nt']);
		$dt['img_dt']= $dt['img_type']= $dt['img_nt']= '';
	}

	if(isset($hosp['hosp_dt'])) {
  	$hosp_id=AddHospitalization($pid,$hosp['hosp_dt'],
			 $hosp['hosp_why'],$hosp['hosp_type']);
		$dt['hosp_nt']= $dt['hosp_why']= $dt['hosp_type']= $dt['hosp_dt']= '';
	}
	if(isset($ddoc['ddoc_doc_id'])) {
  	AddDatedDocument($pid, $ddoc['ddoc_type'], $ddoc['ddoc_dt'], $ddoc['ddoc_title'], $ddoc['ddoc_nt'], $ddoc['ddoc_doc_id']);
	}

	if($pat_sex == 'f') {
		if($pap_display) {
			if(!isset($pap['pt_hpv_result'])) $pap['pt_hpv_result'] = '';
			$pap_id=AddPap($pid,$pap['pt_date'],$pap['pt_lab'],$pap['pt_test'],
					$pap['pt_result_text'],$pap['pt_result_nt'],$pap['pt_hpv_result']);
			$dt['pt_date']= $dt['pt_lab']= $dt['pt_test']='';
			$dt['pt_result_nt']= $dt['pt_result_text']=$dt['pt_hpv_result']='';
			$cnt=1;
			while($cnt <= $max_pap) {
				UpdatePap($pid,$pap['pt_id_'.$cnt],$pap['pt_date_'.$cnt],
							$pap['pt_lab_'.$cnt],$pap['pt_test_'.$cnt],
							$pap['pt_result_text_'.$cnt],$pap['pt_result_nt_'.$cnt],
							$pap['pt_hpv_result_'.$cnt]);
				$cnt++;
			}
		}
	
		if(isset($us['us_date'])) {
			$ultra_id=AddUltrasound($pid,$us['us_date'],$us['us_type'],
				$us['us_comm'], $us['us_rev']);
			$dt['us_rev']= $dt['us_comm']= $dt['us_type']= $dt['us_date']= '';
		}

		if(isset($bd['bd_date'])) {
			AddBoneDensity($pid,$bd['bd_date'],$bd['bd_result'],
																				 	$bd['bd_comm'],$bd['bd_rev']);
			$dt['bd_date']= $dt['bd_result']= $dt['bd_comm']= $dt['bd_rev']= '';
		}

		if(isset($pp['pp_ga_weeks'])) {
			$pp_id=AddPP($pid,$pp['pp_date_of_pregnancy'],$pp['pp_ga_weeks'],
				$pp['pp_labor_length'],$pp['pp_weight_lb'],$pp['pp_weight_oz'],
				$pp['pp_sex'],$pp['pp_delivery'],$pp['pp_anes'],$pp['pp_place'],
				$pp['pp_preterm'],$pp['pp_comment'],$pp['pp_doc'],$pp['pp_conception']);
			$dt['pp_id']= $dt['pp_date_of_pregnancy']= $dt['pp_ga_weeks'] = '';
			$dt['pp_labor_length']= $dt['pp_weight_lb']= $dt['pp_weight_oz']= '';
			$dt['pp_sex']= $dt['pp_delivery']= $dt['pp_anes']= $dt['pp_place']= '';
			$dt['pp_preterm']=$dt['pp_comment']=$dt['pp_doc']=$dt['pp_conception']='';
		}
	}

	if($diagnosis_display) {
 		AddDiagnosis($pid,'',$diag['dg_type'],$diag['dg_code'],
			$diag['dg_title'],$diag['dg_plan'],$diag['dg_begdt'],
			$diag['dg_enddt'],'',$diag['dg_referredby']);
 		$dt['dg_code'] = $dt['dg_title'] = $dt['dg_plan']= $dt['dg_type']= '';
		$dt['dg_begdt'] = $dt['dg_enddt'] = $dt['tmp_dg_desc'] = '';
		$dt['dg_referredby'] = '';
		// Now check for any changes to the other diags
		// echo "Updating Diags $max_diags<br/>\n";
		$cnt=1;
		while($cnt <= $max_diags) {
  		UpdateDiagnosis($pid,$diag['dg_id_'.$cnt],$diag['dg_code_'.$cnt],
			 $diag['dg_title_'.$cnt],$diag['dg_plan_'.$cnt],$diag['dg_begdt_'.$cnt],
			 $diag['dg_enddt_'.$cnt],$diag['dg_type_'.$cnt],$diag['dg_remain_'.$cnt],
				'','',$diag['dg_referredby_'.$cnt]);
			$cnt++;
		}
	}
	if($hepc_display) {
		$data['db_hepc_risk_factors'] = implode('|',$hepc_risks);
		$dt['db_hepc_risk_factors'] = implode('|',$hepc_risks);
	}

	$fy= wmtFYI::getPidFYI($pid);
	foreach($fyi as $key => $val) { $fy->$key = $val; }
	$fy->update();

	$db = wmtDashboard::getPidDashboard($pid);
	foreach($data as $key => $val) { $db->$key = $val; }
	$db->db_fh_extra_yes = implode('|', $fh_extra_yes);
	$db->db_fh_extra_no = implode('|', $fh_extra_no);
	$db->update();

	$patient = wmtPatData::getPidPatient($pid);
	foreach($pat as $key => $val) { $patient->$key = $val; }
	$patient->update();

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
		}
	}
	if($use_lifestyle) {
		// CREATE OR UPDATE THE LIFESTYLE DATA RECORD
		echo "Doing A Lifestyle Dashboard ID : (",$db->id,") <br>\n";
		$lifestyle = wmtLifestyle::getFormLifestyle($pid, $frmdir, $db->id);
		if($lifestyle->id || $lifestyle_data_entered) {
			echo "Doing A Lifestyle Update: (",$lifestyle->id,") <br>\n";
			if(!$lifestyle->id) {
				$lifestyle->pid = $pid;
				$lifestyle->link_name = $frmdir;
				$lifestyle->link_id = $db->id;
				$lfid = $lifestyle->insert($lifestyle);
				$lifestyle->id = $lfid;
			}
			$lifestyle->link_name = $frmdir;
			$lifestyle->link_id = $db->id;
			$lifestyle->form_dt = $data['form_dt'];
			foreach($life as $key => $val) { 
				$lifestyle->$key = $val;
			}
			$lifestyle->update();
		}
		$log = "INSERT $frmdir Form Mode ($form_mode) and Wrap ($wrap_mode) ".
			"and Continue ($continue) - Finished Save, Lifestyle and PQRS";
		if($form_event_logging) auditSQLEvent($log, TRUE);
	}

	// Save the view settings for this user
	if(strtolower(substr($patient->sex,0,1)) == 'f') {
		saveSettingMode('wmt::db_pat_display', $tmp['tmp_pat_info_ob_disp_mode']);
	} else {
		saveSettingMode('wmt::db_pat_display', $tmp['tmp_pat_info_fyi_disp_mode']);
	}
	saveSettingMode('wmt::db_dated_documents', $tmp['tmp_ddoc_disp_mode']);
	saveSettingMode('wmt::db_social_history', $tmp['tmp_sh_disp_mode']);
	saveSettingMode('wmt::db_wellness', $tmp['tmp_well_full_disp_mode']);
	saveSettingMode('wmt::db_allergies', $tmp['tmp_all_disp_mode']);
	saveSettingMode('wmt::db_medications', $tmp['tmp_med_disp_mode']);
	saveSettingMode('wmt::db_ob_history', $tmp['tmp_pp_disp_mode']);
	saveSettingMode('wmt::db_surgeries', $tmp['tmp_ps_disp_mode']);
	saveSettingMode('wmt::db_medical_history', $tmp['tmp_pmh_disp_mode']);
	saveSettingMode('wmt::db_family_history', $tmp['tmp_fh_disp_mode']);
	saveSettingMode('wmt::db_immunizations', $tmp['tmp_imm_disp_mode']);
	saveSettingMode('wmt::db_images', $tmp['tmp_img_disp_mode']);
	saveSettingMode('wmt::db_birth_history', $tmp['tmp_birth_disp_mode']);
	saveSettingMode('wmt::db_reproductive1', $tmp['tmp_repo_disp_mode']);
	saveSettingMode('wmt::db_admissions', $tmp['tmp_hosp_disp_mode']);
	saveSettingMode('wmt::db_ultrasounds', $tmp['tmp_us_disp_mode']);
	saveSettingMode('wmt::db_pap_track', $tmp['tmp_pap_disp_mode']);
	saveSettingMode('wmt::db_bone_density', $tmp['tmp_bd_disp_mode']);
	saveSettingMode('wmt::db_pediatric_diet', $tmp['tmp_ped_diet_disp_mode']);
	saveSettingMode('wmt::db_hepc', $tmp['tmp_hepc_disp_mode']);
	saveSettingMode('wmt::db_diagnosis', $tmp['tmp_diag_disp_mode']);
	saveSettingMode('wmt::db_ros', $tmp['tmp_ros_disp_mode']);

	echo "\n<script type='text/javascript'>top.restoreSession();";
	echo "parent.left_nav.setPatient('".
		htmlspecialchars($patient->full_name,ENT_QUOTES, '', FALSE)."','".
		$patient->pid."','".$patient->pubpid.
		"','','DOB: ". $patient->DOB . " AGE: " .
		getPatientAge($patient->DOB) . 
		// These lines are for OB customers that have Blood Type
		// "&nbsp;&nbsp;BT: <span style=\"font-weight: normal;\">".
		// $patient->blood_type.$patient->rh_factor."</span>".
		// These lines are for any customers that want doctor initials 
		// "&nbsp;&nbsp;DR: <span style=\"font-weight: normal;\">".
		// $patient->doctor_initials."</span>".
		// These lines are for OB customers that have pregnancies and deliveries 
		// "&nbsp;&nbsp;G: <span style=\"font-weight: normal;\">".
		// $data['db_pregnancies']."</span>&nbsp;&nbsp;P: ".
		// "<span style=\"font-weight: normal;\">".$data['db_deliveries']."</span>".
		"','".ucfirst($patient->language)."');";
	if($continue) {
		echo "</script>\n";
	} else {
		echo "window.location='../../patient_file/summary/demographics.php';</script>\n";
	}

} else if($form_mode != 'update') {
	// All the other add/update pass handling is in this compartment
	// First set the arrays back from POST
	unset($ad);
	unset($dt);
	unset($rs);
	foreach($_POST as $k => $var) { 
		if(is_string($var)) $var = trim($var);
		if(substr($k,0,7) == 'ee1_rs_') {
      $rs[$k] = $var;
		} else {
			if(substr($k,0,9) == 'pad_diet_') {
				$ad[$k]=$var;
			} else {
				$dt[$k]=$var;
			}
		}
	}
	if(!isset($ad['pad_diet_form'])) $ad['pad_diet_form'] = 0;
	if(!isset($ad['pad_diet_fed'])) $ad['pad_diet_fed'] = 0;
	if(!isset($ad['pad_diet_oth'])) $ad['pad_diet_oth'] = 0;

	if($pat_sex == 'f') {
		if($pap_display) {
			$cnt=1;
			while($cnt <= $dt['tmp_pap_cnt']) {
				UpdatePap($pid,$dt['pt_id_'.$cnt],$dt['pt_date_'.$cnt],
						$dt['pt_lab_'.$cnt],$dt['pt_test_'.$cnt],
						$dt['pt_result_text_'.$cnt],$dt['pt_result_nt_'.$cnt],
						$dt['pt_hpv_result_'.$cnt]);
				$cnt++;
			}
		}

		if($bd_display) {
			$cnt=1;
			while($cnt <= $dt['tmp_bd_cnt']) {
				UpdateBoneDensity($pid,$dt['bd_id_'.$cnt],$dt['bd_dt_'.$cnt],
					$dt['bd_result_'.$cnt],$dt['bd_comm_'.$cnt],$dt['bd_rev_'.$cnt]);
				$cnt++;
			}
		}
	}

	if($diagnosis_display) {
		$cnt=1;
		while($cnt <= $dt['tmp_diag_cnt']) {
  		UpdateDiagnosis($pid,$dt['dg_id_'.$cnt],$dt['dg_code_'.$cnt],
					$dt['dg_title_'.$cnt],$dt['dg_plan_'.$cnt],$dt['dg_begdt_'.$cnt],
					$dt['dg_enddt_'.$cnt],$dt['dg_type_'.$cnt],$dt['dg_remain_'.$cnt],
					'','',$dt['dg_referredby_'.$cnt]);
			$cnt++;
		}
	}

	if($form_mode == 'addmed') {
  	$med_id = AddMedication($pid,$dt['med_begdate'],$dt['med_title'],
				$dt['med_enddate'],$dt['med_dest'],$dt['med_comm']);
		if($med_id) LinkListEntry($pid, $med_id, $encounter, 'medication');
		$dt['med_begdate'] = $dt['med_title'] = $dt['med_enddate'] = '';
		$dt['med_dest'] = $dt['med_comm'] = '';
		$form_focus = 'med_begdate';

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

	} else if($form_mode == 'medwindow') {
		if(isset($_GET['disp'])) $dt['tmp_med_window_mode'] = trim($_GET['disp']);

	} else if($form_mode == 'addmedhist') {
  	$mhist_id = AddMedication($pid,$dt['med_hist_begdate'],
			$dt['med_hist_title'],$dt['med_hist_enddate'],
			$dt['med_hist_dest'],$dt['med_hist_comm']);
		if($mhist_id) LinkListEntry($pid, $mhist_id, $encounter, 'medication');
		$dt['med_hist_begdate'] = $dt['med_hist_title'] = '';
		$dt['med_hist_enddate'] = $dt['med_hist_dest'] = $dt['med_hist_comm'] = '';

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

	} else if($form_mode == 'addotc') {
  	$med_id = AddMedication($pid,$dt['otc_begdate'],$dt['otc_title'],
				$dt['otc_enddate'],'',$dt['otc_comments'],'1','medication_otc',
				$dt['otc_extrainfo'],$dt['otc_referredby'],$dt['otc_type']);
		if($med_id) LinkListEntry($pid, $med_id, $encounter, 'medication_otc');
		$dt['otc_begdate'] = $dt['otc_title'] = $dt['otc_enddate'] = '';
		$dt['otc_type'] = $dt['otc_comments'] = $dt['otc_referredby'] = '';
		$form_focus = 'otc_begdate';

	} else if($form_mode == 'updateotc') {
		$cnt = trim($_GET['itemID']);
  	UpdateMedication($pid,$dt['otc_id_'.$cnt],$dt['otc_begdate_'.$cnt],
			$dt['otc_title_'.$cnt],$dt['otc_enddate_'.$cnt],'',
			$dt['otc_comments_'.$cnt],'1','medication_otc',$dt['otc_extrainfo_'.$cnt],
			$dt['otc_referredby_'.$cnt],$dt['otc_injury_type_'.$cnt]);
	
	} else if($form_mode == 'delotc') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['otc_id_'.$cnt],
													$dt['otc_num_links_'.$cnt],'medication_otc');
	
	} else if($form_mode == 'all') {
 		$all_id=AddAllergy($pid,$dt['all_begdate'],$dt['all_title'],
						$dt['all_comm'],$dt['all_react'],$dt['all_occur']);
		if($all_id) LinkListEntry($pid, $all_id, $encounter, 'allergy');
		$dt['all_begdate'] = $dt['all_title'] = $dt['all_comm'] = '';
		$dt['all_react'] = $dt['all_occur'] = '';
		$form_focus='all_begdate';

	} else if($form_mode == 'updateall') {
		$cnt=trim($_GET['itemID']);
  	UpdateAllergy($pid,$dt['all_id_'.$cnt],$dt['all_comments_'.$cnt],
			$dt['all_begdate_'.$cnt],$dt['all_title_'.$cnt],$dt['all_react_'.$cnt],
			$dt['all_occur_'.$cnt]);

	} else if($form_mode == 'delall') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['all_id_'.$cnt],
											$dt['all_num_links_'.$cnt],'allergy');
	
	} else if($form_mode == 'pp') {
		$pp_id=AddPP($pid,$dt['pp_date_of_pregnancy'],$dt['pp_ga_weeks'],
			$dt['pp_labor_length'],$dt['pp_weight_lb'],$dt['pp_weight_oz'],
			$dt['pp_sex'],$dt['pp_delivery'],$dt['pp_anes'],$dt['pp_place'],
			$dt['pp_preterm'],$dt['pp_comment'],$dt['pp_doc'],$dt['pp_conception']);
		$dt['pp_id'] = $dt['pp_date_of_pregnancy']= $dt['pp_ga_weeks'] = '';
		$dt['pp_labor_length']= $dt['pp_weight_lb']= $dt['pp_weight_oz']= '';
		$dt['pp_sex']= $dt['pp_delivery']= $dt['pp_anes']= $dt['pp_place']= '';
		$dt['pp_preterm']=$dt['pp_comment']=$dt['pp_doc']=$dt['pp_conception']='';

	} else if($form_mode == 'updatepp') {
		$cnt=trim($_GET['itemID']);
		UpdatePP($pid,$dt['pp_id_'.$cnt],$dt['pp_date_of_pregnancy_'.$cnt],
			$dt['pp_ga_weeks_'.$cnt],$dt['pp_labor_length_'.$cnt],
			$dt['pp_weight_lb_'.$cnt],$dt['pp_weight_oz_'.$cnt],$dt['pp_sex_'.$cnt],
			$dt['pp_delivery_'.$cnt],$dt['pp_anes_'.$cnt],$dt['pp_place_'.$cnt],
			$dt['pp_preterm_'.$cnt],$dt['pp_comment_'.$cnt],$dt['pp_doc_'.$cnt],
			$dt['pp_conception_'.$cnt]);

	} else if($form_mode == 'delpp') {
		$cnt=trim($_GET['itemID']);
		DeletePP($pid,$dt['pp_id_'.$cnt],$dt['pp_num_links_'.$cnt]);
	
	} else if($form_mode == 'surg') {
  	$surg_id=AddSurgery($pid,$dt['ps_begdate'],$dt['ps_title'],
				$dt['ps_comments'],$dt['ps_referredby'],$dt['ps_hospitalized']);
		$dt['ps_title']='';
		$dt['ps_begdate']='';
		$dt['ps_comments']='';
		$dt['ps_referredby']='';
		$dt['ps_hospitalized']='';
		$form_focus='ps_begdate';
	
	} else if($form_mode == 'updatesurg') {
		$cnt=trim($_GET['itemID']);
  	UpdateSurgery($pid,$dt['ps_id_'.$cnt],$dt['ps_begdate_'.$cnt],$dt['ps_title_'.$cnt],$dt['ps_comments_'.$cnt],$dt['ps_referredby_'.$cnt],$dt['ps_hospitalized_'.$cnt]);
	
	} else if($form_mode == 'delsurg') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['ps_id_'.$cnt],
														$dt['ps_num_links_'.$cnt],'surgery');
	
	} else if($form_mode == 'pmh') {
  	$mh_id=AddMedicalHistory($pid,$dt['pmh_type'],'',$dt['pmh_nt'],$dt['pmh_hospitalized']);
		$dt['pmh_type']='';
		$dt['pmh_nt']='';
		$dt['pmh_hospitalized']='';
		$form_focus='pmh_type';
	
	} else if($form_mode == 'updatepmh') {
		$cnt=trim($_GET['itemID']);
  	UpdateMedicalHistory($pid,$dt['pmh_id_'.$cnt],$dt['pmh_type_'.$cnt],'',$dt['pmh_nt_'.$cnt],$dt['pmh_hospitalized_'.$cnt]);
	
	} else if($form_mode == 'delpmh') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['pmh_id_'.$cnt],
														$dt['pmh_num_links_'.$cnt],'wmt_med_history');

	} else if($form_mode == 'fh') {
  	$fh_id=AddFamilyHistory($pid,$dt['fh_who'],$dt['fh_type'],$dt['fh_nt'],
				$dt['fh_dead'],$dt['fh_age'],$dt['fh_age_dead']);
		$dt['fh_type']='';
		$dt['fh_nt']='';
		$form_focus='fh_who';
	
	} else if($form_mode == 'delfh') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['fh_id_'.$cnt],
												$dt['fh_num_links_'.$cnt],'wmt_family_history');
	
	} else if($form_mode == 'updatefh') {
		$cnt=trim($_GET['itemID']);
  	UpdateFamilyHistory($pid,$dt['fh_id_'.$cnt],$dt['fh_who_'.$cnt],$dt['fh_type_'.$cnt],$dt['fh_nt_'.$cnt],$dt['fh_dead_'.$cnt],$dt['fh_age_'.$cnt],$dt['fh_age_dead_'.$cnt]);
	
	} else if($form_mode == 'pap') {
		$pt_id = AddPap($pid,$dt['pt_date'],$dt['pt_lab'],$dt['pt_test'],$dt['pt_result_text'],$dt['pt_result_nt'],$dt['pt_hpv_result']);
		$dt['pt_date']='';
		$dt['pt_lab']='';
		$dt['pt_test']='';
		$dt['pt_result_text']='';
		$dt['pt_hpv_result']='';
		$dt['pt_result_nt']='';
		$form_focus='pt_date';
	
	} else if($form_mode == 'updatepap') {
		$cnt=trim($_GET['itemID']);
		UpdatePap($pid,$dt['pt_id_'.$cnt],$dt['pt_date_'.$cnt],$dt['pt_lab_'.$cnt],$dt['pt_test_'.$cnt],$dt['pt_result_text_'.$cnt],$dt['pt_result_nt_'.$cnt],$dt['pt_hpv_result_'.$cnt]);

	} else if($form_mode == 'delpap') {
		$cnt=trim($_GET['itemID']);
		DeletePap($pid,$dt['pt_id_'.$cnt],$dt['pt_num_links_'.$cnt]);
	
	} else if($form_mode == 'updateimm') {
		$cnt=trim($_GET['itemID']);
  	UpdateImmunization($pid,$dt['imm_id_'.$cnt],$dt['imm_comments_'.$cnt]);
	
	} else if($form_mode == 'img') {
  	$img_id=AddImageHistory($pid,$dt['img_type'],$dt['img_dt'],$dt['img_nt']);
		if($img_id) LinkListEntry($pid, $img_id, $encounter, 'wmt_img_history');
		if(($dt['img_dt'] != '0000-00-00') && 
				$dt['img_dt'] && checkSettingMode('wmt::auto_create_bd')) {
			$irow = sqlQuery("SELECT * FROM list_options WHERE list_id=? ".
				"AND option_id=?", array('Image_types',$dt['img_type']));
			if(!isset($irow{'codes'})) $irow{'codes'} = '';
			if($irow{'codes'} == 'bone_density_link') {
				$dt['db_last_bone'] = $dt['img_dt'];
			}
		}
		$dt['img_type']='';
		$dt['img_dt']='';
		$dt['img_nt']='';
		$form_focus='img_type';
	
	} else if($form_mode == 'updateimg') {
		$cnt=trim($_GET['itemID']);
  	UpdateImageHistory($pid,$dt['img_id_'.$cnt],$dt['img_type_'.$cnt],$dt['img_dt_'.$cnt],$dt['img_nt_'.$cnt]);

	} else if($form_mode == 'delimg') {
		$cnt=trim($_GET['itemID']);
		DeleteListItem($pid,$dt['img_id_'.$cnt],
														$dt['img_num_links_'.$cnt],'wmt_img_history');
	
	} else if($form_mode == 'hosp') {
  	$hosp_id=AddHospitalization($pid,$dt['hosp_dt'],$dt['hosp_why'],
				$dt['hosp_type'],$dt['hosp_nt']);
		if($hosp_id) { LinkListEntry($pid, $hosp_id, $encounter, 'hospitalization'); }
		$dt['hosp_dt']='';
		$dt['hosp_type']='';
		$dt['hosp_why']='';
		$dt['hosp_nt']='';
		$form_focus='hosp_dt';
	
	} else if($form_mode == 'updatehosp') {
		$cnt=trim($_GET['itemID']);
  	UpdateHospitalization($pid,$dt['hosp_id_'.$cnt],$dt['hosp_dt_'.$cnt],$dt['hosp_why_'.$cnt],$dt['hosp_type_'.$cnt],$dt['hosp_nt_'.$cnt]);

	} else if($form_mode == 'delhosp') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['hosp_id_'.$cnt],
												$dt['hosp_num_links_'.$cnt],'hospitalization');

	} else if($form_mode == 'ultra') {
  	$ultra_id= AddUltrasound($pid,$dt['us_date'],$dt['us_type'],$dt['us_comm'],$dt['us_rev']);
		$dt['us_date']='';
		$dt['us_type']='';
		$dt['us_comm']='';
		$dt['us_rev']='';
		$form_focus='us_date';
	
	} else if($form_mode == 'updateultra') {
		$cnt=trim($_GET['itemID']);
  	UpdateUltrasound($pid,$dt['us_id_'.$cnt],$dt['us_dt_'.$cnt],$dt['us_type_'.$cnt],$dt['us_comm_'.$cnt],$dt['us_rev_'.$cnt]);
	
	} else if($form_mode == 'delultra') {
		$cnt=trim($_GET['itemID']);
  	DeleteListItem($pid,$dt['us_id_'.$cnt],
														$dt['us_num_links_'.$cnt],'hospitalization');

	} else if($form_mode == 'bone') {
		if($dt['bd_date'] && ($dt['bd_date'] > $dt['db_last_bone'])) $dt['db_last_bone'] = $dt['bd_date'];
		AddBoneDensity($pid,$dt['bd_date'],$dt['bd_result'],$dt['bd_comm'],$dt['bd_rev']);
		$dt['bd_date']='';
		$dt['bd_result']='';
		$dt['bd_comm']='';
		$dt['bd_rev']='';
		$form_focus='bd_date';

	} else if($form_mode == 'updatebone') {
		$cnt=trim($_GET['itemID']);
		UpdateBoneDensity($pid,$dt['bd_id_'.$cnt],$dt['bd_dt_'.$cnt],$dt['bd_result_'.$cnt],$dt['bd_comm_'.$cnt],$dt['bd_rev_'.$cnt]);
	
	} else if($form_mode == 'delbone') {
		$cnt=trim($_GET['itemID']);
		DeleteBoneDensity($pid,$dt['bd_id_'.$cnt],$dt['bd_num_links_'.$cnt]);

	} else if($form_mode == 'diag') {
  	AddDiagnosis($pid,$encounter,$dt['dg_type'],$dt['dg_code'],$dt['dg_title'],$dt['dg_plan'],$dt['dg_begdt'],$dt['dg_enddt'],'',$dt['dg_referredby']);
		$dt['dg_code']='';
		$dt['dg_type']='';
		$dt['dg_title']='';
		$dt['dgbegdt']='';
		$dt['dg_enddt']='';
		$dt['dg_plan']='';
		$dt['tmp_dg_desc']='';
		$dt['dg_referredby']='';
		$form_focus='dg_code';
	
	} else if($form_mode == 'deldiag') {
		$cnt=trim($_GET['itemID']);
  	DeleteDiagnosis($pid,$dt['dg_id_'.$cnt]);
	
	} else if($form_mode == 'updatediag') {
		$cnt=trim($_GET['itemID']);
  	UpdateDiagnosis($pid,$dt['dg_id_'.$cnt],$dt['dg_code_'.$cnt],$dt['dg_title_'.$cnt],$dt['dg_plan_'.$cnt],$dt['dg_begdt_'.$cnt],$dt['dg_enddt_'.$cnt],$dt['dg_type_'.$cnt],$dt['dg_remain_'.$cnt],'','',$dt['dg_referredby_'.$cnt]);
	
	} else if($form_mode == 'window') {
		if(isset($_GET['disp'])) $dt['tmp_diag_window_mode']=trim($_GET['disp']);
	
	} else if($form_mode == 'fav') {
		$cnt = 0;
		if(isset($_GET['itemID'])) $cnt=trim($_GET['itemID']);
		if($cnt) {	
			$test = AddFavorite($dt['dg_type_'.$cnt],$dt['dg_code_'.$cnt],
				$dt['dg_plan_'.$cnt]);
		} else {
			$test = AddFavorite($dt['dg_type'],$dt['dg_code'],$dt['dg_plan']);
		}

	} else if($form_mode == 'ddoc') {
  	AddDatedDocument($pid,$dt['ddoc_type'],$dt['ddoc_dt'],$dt['ddoc_title'],$dt['ddoc_nt'],$dt['ddoc_doc_id']);
		$dt['ddoc_type'] = '';
		$dt['ddoc_dt'] = '';
		$dt['ddoc_title'] = '';
		$dt['ddoc_nt'] = '';
		$dt['ddoc_doc_id'] = '';
		$dt['ddoc_num_links'] = '';
		$form_focus='ddoc_dt';
	
	} else if($form_mode == 'delddoc') {
		$cnt = trim($_GET['itemID']);
  	DeleteDatedDocument($pid, $dt['ddoc_id_'.$cnt], 
			$dt['ddoc_num_links_'.$cnt]);
	
	} else if($form_mode == 'updateddoc') {
		$cnt=trim($_GET['itemID']);
  	UpdateDatedDocument($pid,$dt['ddoc_type_'.$cnt],$dt['ddoc_dt_'.$cnt],$dt['ddoc_title_'.$cnt],$dt['ddoc_nt_'.$cnt]);
	
	} else if($form_mode == 'ddoc_window') {
		if(isset($_GET['disp'])) $dt['tmp_ddoc_window_mode'] = trim($_GET['disp']);

	// Unknown Mode??  throw an error - shouldn't get here!
	} else {
		echo "<h>Unknown Mode, called with (",$form_mode,")</h><br/>\n";
		echo "<h>Exiting</h><br/>\n";
		exit;
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>Dashboard</title>

	<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
	<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt-v2/wmt.default.css" type="text/css">
	<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script>

	<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/init_ajax.inc.js"></script>
	<script type="text/javascript">
		var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
	</script>
</head>

<?php
$history = getHistoryData($pid);
// echo "Past the header, first pass setting is: $first_pass<br>\n";
if($first_pass) {
	$dt['us_rev']= $dt['us_comm']= $dt['us_type']= $dt['us_date']= '';
	$dt['bd_date']= $dt['bd_result']= $dt['bd_comm']= $dt['bd_rev']= '';
	$dt['tmp_hepc_trans'] = $dt['tmp_hepc_dia'] = $dt['tmp_hepc_use'] = '';
	$dt['tmp_hepc_drug'] = $dt['tmp_hepc_tat'] = $dt['tmp_hepc_hepc'] = '';
	$dt['tmp_hepc_sex'] = $dt['tmp_hepc_risk'] = $dt['tmp_hepc_jail'] = '';
	$dt['tmp_hepc_hiv'] = $dt['tmp_hepc_combat'] = $dt['tmp_hepc_oth'] = '';
	$dt['tmp_hepc_job'] = '';

	$dt['tmp_pat_info_fyi_disp_mode']=$pat_display;
	$dt['tmp_pat_info_ob_disp_mode']=$pat_display;
	$dt['tmp_sh_disp_mode']= $social_display;
	$dt['tmp_repo_disp_mode']= $repo_display;
	$dt['tmp_well_full_disp_mode']= $wellness_display;
	$dt['tmp_med_disp_mode']= $medication_display;
	$dt['tmp_otc_disp_mode']= $otc_display;
	$dt['tmp_med_window_mode'] = 'all';
	if($max_med) { $dt['tmp_med_window_mode'] = 'limit'; }
	$dt['tmp_hepc_disp_mode']= $hepc_display;
	$dt['tmp_all_disp_mode']= $allergy_display;
	$dt['tmp_ddoc_disp_mode']= $dated_doc_display;
	$dt['tmp_pp_disp_mode']= $pp_display;
	$dt['tmp_ps_disp_mode']= $surgery_display;
	$dt['tmp_pmh_disp_mode']= $mh_display;
	$dt['tmp_fh_disp_mode']= $fh_display;
	$dt['tmp_ros_disp_mode']= $ros_display;
	$dt['tmp_pap_disp_mode']= $pap_display;
	$dt['tmp_bd_disp_mode']= $bd_display;
	$dt['tmp_us_disp_mode']= $ultrasound_display;
	$dt['tmp_img_disp_mode']= $image_display;
	$dt['tmp_ped_diet_disp_mode']= $ped_diet_display;
	$dt['tmp_birth_disp_mode']= $birth_history_display;
	$dt['tmp_hosp_disp_mode']= $admission_display;
	$dt['tmp_imm_disp_mode']= $immunization_display;
	$dt['tmp_diag_disp_mode']= $diagnosis_display;
	$dt['tmp_diag_window_mode']='all';
	$dt['tmp_scroll_top']='';

	$ad = array();
	unset($ad);
	$ad['pad_diet_form'] = $dt['db_ped_diet_f'];
	$ad['pad_diet_fed'] = $dt['db_ped_diet_b'];
	$ad['pad_diet_oth'] = $dt['db_ped_diet_o'];
	$ad['pad_diet_type_nt'] = $dt['db_ped_diet_nt'];
	$ad['pad_diet_tube'] = $dt['db_ped_diet_tube'];
	$ad['pad_diet_ttype'] = $dt['db_ped_diet_ttype'];
	$ad['pad_diet_tsize'] = $dt['db_ped_diet_tsize'];
	$ad['pad_diet_chc'] = $dt['db_ped_diet_chc'];
	$ad['pad_diet_amt'] = $dt['db_ped_diet_amt'];
	$ad['pad_diet_rate'] = $dt['db_ped_diet_rate'];
	$ad['pad_diet_require'] = $dt['db_ped_diet_special'];
	$ad['pad_diet_nt'] = $dt['db_ped_diet_other'];
	$dt['tmp_yes_disp_mode']='none; ';
	$dt['tmp_no_disp_mode']='none; ';
	if(!isset($dt['rp1_want_kids'])) $dt['rp1_want_kids'] = '';
	if(strtolower(substr($dt{'rp1_want_kids'},0,1)) == 'y') $dt['tmp_yes_disp_mode'] = 'block; ';
	if(strtolower(substr($dt{'rp1_want_kids'},0,1)) == 'n') $dt['tmp_no_disp_mode'] = 'block; ';

	// FIX HERE - THIS IS JUST FOR A TEST
	$fyi = wmtFYI::getPidFYI($pid);
	foreach($fyi as $key => $val) {
		if($key != 'id' && $key != 'pid' && $key != 'last_touch') $dt[$key] = $val;
	}
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
		
	$dt['db_pat_blood_type']= $patient->blood_type;
	$dt['db_pat_rh_factor']= $patient->rh_factor;

/* NOW IN THE FAMILY HISTORY ROS INCLUDE
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
	foreach($fh_options as $opt) {
		$dt['tmp_fh_ros_'.$opt['option_id'].'_nt'] = 
								GetROSKeyComment($id,'',$opt['option_id'],$pid);	
	}
*/
}

if($first_pass || $continue) {
	if($hepc_display) {
		// Split out the Hep C Risk Factors
		if(!isset($dt['db_hepc_risk_factors'])) { $dt['db_hepc_risk_factors'] = ''; }
		$risks = explode('|',$dt{'db_hepc_risk_factors'});
		$dt['tmp_hepc_trans'] = (in_array('bld_trans',$risks)) ? 'bld_trans' : '';
		$dt['tmp_hepc_dia'] = (in_array('dialysis',$risks)) ? 'dialysis' : '';
		$dt['tmp_hepc_use'] = (in_array('drug_use',$risks)) ? 'drug_use' : '';
		$dt['tmp_hepc_drug'] = (in_array('history_drug',$risks)) ? 'history_drug' : '';
		$dt['tmp_hepc_tat'] = (in_array('tattoo',$risks)) ? 'tattoo' : '';
		$dt['tmp_hepc_hepc'] = (in_array('hepc',$risks)) ? 'hepc' : '';
		$dt['tmp_hepc_sex'] = (in_array('sex',$risks)) ? 'sex' : '';
		$dt['tmp_hepc_risk'] = (in_array('risk_sex',$risks)) ? 'risk_sex' : '';
		$dt['tmp_hepc_jail'] = (in_array('jail',$risks)) ? 'jail' : '';
		$dt['tmp_hepc_hiv'] = (in_array('hiv',$risks)) ? 'hiv' : '';
		$dt['tmp_hepc_combat'] = (in_array('combat',$risks)) ? 'combat' : '';
		$dt['tmp_hepc_job'] = (in_array('job',$risks)) ? 'job' : '';
		$dt['tmp_hepc_oth'] = (in_array('other',$risks)) ? 'other' : '';
	}
	if(!isset($dt['db_sex_active'])) { $dt['db_sex_active'] = ''; }
	if(!isset($dt['db_pflow'])) { $dt['db_pflow'] = ''; }
	if(!isset($dt['db_pfreq'])) { $dt['db_pfreq'] = ''; }
}

if($continue && $ped_diet_display) {
	$ad = array();
	unset($ad);
	if(!isset($dt['pad_diet_form'])) { $dt['pad_diet_form'] = ''; }
	if(!isset($dt['pad_diet_fed'])) { $dt['pad_diet_fed'] = ''; }
	if(!isset($dt['pad_diet_oth'])) { $dt['pad_diet_oth'] = ''; }
	$ad['pad_diet_form'] = $dt['pad_diet_form'];
	$ad['pad_diet_fed'] = $dt['pad_diet_fed'];
	$ad['pad_diet_oth'] = $dt['pad_diet_oth'];
	$ad['pad_diet_type_nt'] = $dt['pad_diet_type_nt'];
	$ad['pad_diet_tube'] = $dt['pad_diet_tube'];
	$ad['pad_diet_ttype'] = $dt['pad_diet_ttype'];
	$ad['pad_diet_tsize'] = $dt['pad_diet_tsize'];
	$ad['pad_diet_chc'] = $dt['pad_diet_chc'];
	$ad['pad_diet_amt'] = $dt['pad_diet_amt'];
	$ad['pad_diet_rate'] = $dt['pad_diet_rate'];
	$ad['pad_diet_require'] = $dt['pad_diet_require'];
	$ad['pad_diet_nt'] = $dt['pad_diet_nt'];
}
	
// Check for the most recent vitals
$vrec = $vid = '';
$vital_timestamp = '';
$vitals = wmtVitals::getVitalsByPatient($pid);
if($vitals->vital_id) {
	$vid = $vitals->vital_id;
	$dt['db_height']=$vitals->height;
	$dt['db_weight']=$vitals->weight;
	$dt['db_bps']=$vitals->bps;
	$dt['db_bpd']=$vitals->bpd;
	$dt['db_pulse']=$vitals->pulse;
	$dt['db_BMI']=$vitals->BMI;
	$dt['db_BMI_status']=$vitals->BMI_status;
	$vital_timestamp= $vitals->timestamp;
}

if(checkSettingMode('wmt::db_wellness_hearing')) {
	// Check for the most recent hearing screen
	$hrec = $hid='';
	$hearing_timestamp='Not On Record';
	$sql = "SELECT * FROM form_vitals WHERE pid=? AND ".
			"UPPER(hearing_screen)='YES' ORDER BY date DESC LIMIT 1";
	$binds = array($pid);
	$fres=sqlQuery($sql, $binds);
	if($fres{'id'}) {
		$dt['db_hear_screen']=$fres{'hearing_screen'};
		$dt['db_hearl_result']=$fres{'left_ear'};
		$dt['db_hearr_result']=$fres{'right_ear'};
		$hearing_timestamp= $fres{'date'};
	}
}

// Load all list entries, no visit attached
if($image_display) $img=GetImageHistory($pid);
if($surgery_display) $surg=GetList($pid, 'surgery');
if($admission_display) $hosp=GetList($pid, 'hospitalization');
if($fh_display) $fh=GetFamilyHistory($pid);
if($fh_display) $fh_defaults=GetFamilyHistoryDefaults($pid);
if($immunization_display) $imm=GetAllImmunizationsbyPatient($pid);
if($mh_display) $pmh=GetMedicalHistory($pid);
if($allergy_display) $allergies=GetList($pid, 'allergy');
if($pp_display) $obhist=GetPastPregnancies($pid);
if($pap_display) $pap_data=getAllPaps($pid);
if($otc_display) $otc = GetList($pid, 'medication_otc', '', '', 'show_all');
if($medication_display) {
	if($use_meds_not_rx) {
 		$meds = GetList($pid, 'medication');
 		$med_hist = GetList($pid, 'med_hitory');
	} else {
 		$meds = getPrescriptionsByPatient($pid, "*", '= 1');
 		$med_hist = getPrescriptionsByPatient($pid, "*", '< 1');
	}
}
if($ultrasound_display) $ultra=GetList($pid,'ultrasound');
if($bd_display) $bone= GetList($pid,'bonedensity');
if($diagnosis_display) $diag=GetProblemsWithDiags($pid,$dt['tmp_diag_window_mode']);
$load = $scroll_point = '';
if($dt['tmp_scroll_top']) $scroll_point = $dt['tmp_scroll_top'];
if($scroll_point) $load .= " window.scrollTo(0,$scroll_point);";
if($form_focus && !$scroll_point) $load .= " AdjustFocus('$form_focus');";
if($continue == 'hepc') $load .= " print_section('hepc');";
?>

<?php include($GLOBALS['srcdir'].'/wmt-v2/floating_menu.inc.php'); ?>
<body class="body_top" onLoad="<?php echo $load; ?>">
<div id="overDiv" style="position:absolute; visibility: hidden; z-index:1000;"></div>
<form action="<?php echo $rootdir.$save_style; ?>" method="post" enctype="multipart/form-data" name="db_form">
<div style="margin: 50px 0px 0px 0px;">

<!-- The paitent info module we'll call through the new modes -->
<?php 
$field_prefix = 'pat_';
echo "<div class='wmtMainContainer'>\n";
$use_bottom_bar = false;
if($pat_sex == 'f') {
	$display_toggle = 'tmp_pat_info_ob_disp_mode';
	generateChapter('Patient Information', 'pat_info_ob', $dt[$display_toggle],
			'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
  echo "	<div id='pat_info_obBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
	include($GLOBALS['srcdir'].'/wmt-v2/form_modules/pat_info_ob_module.inc.php');
} else {
	$display_toggle = 'tmp_pat_info_fyi_disp_mode';
	generateChapter('Patient Information', 'pat_info_fyi', $dt[$display_toggle],
			'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
  echo "	<div id='pat_info_fyiBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
	include($GLOBALS['srcdir'].'/wmt-v2/form_modules/pat_info_fyi_module.inc.php');
}
echo "	</div>\n";
echo "</div>\n";
?>

<?php if($social_display) {
	$field_prefix = 'db_';
	$display_toggle = 'tmp_sh_disp_mode';
	echo "<div class='wmtMainContainer'>\n";
	$use_bottom_bar = false;
	generateChapter('Social History', 'sh', $dt[$display_toggle],
			'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
  	echo "	<div id='shBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
	include($GLOBALS['srcdir'].'/wmt-v2/form_modules/sh_module.inc.php');
	echo "	</div>\n";
	echo "</div>\n";
} ?>

<!-- The wellness module we'll call through the new modes -->
<?php 
$field_prefix = 'db_';
$display_toggle = 'tmp_well_full_disp_mode';
echo "<div class='wmtMainContainer'>\n";
$use_bottom_bar = false;
generateChapter('Wellness', 'well_full', $dt[$display_toggle],
		'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
  echo "	<div id='well_fullBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
include($GLOBALS['srcdir'].'/wmt-v2/form_modules/well_full_module.inc.php');
echo "	</div>\n";
echo "</div>\n";
$field_prefix = '';
?>

<!-- The dated document module we'll call through the new modes -->
<?php 
/*
$field_prefix = 'ddoc_';
$display_toggle = 'tmp_ddoc_disp_mode';
echo "<div class='wmtMainContainer'>\n";
$use_bottom_bar = false;
$module['option_id'] = 'ddoc';
$chp_options[0] = 'dated_documents';
generateChapter('Dated Documents', 'ddoc', $dt[$display_toggle],
		'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
$target_container = 'ddocBox';
  echo "	<div id='ddocBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
include($GLOBALS['srcdir'].'/wmt-v2/dated_documents.inc.php');
echo "	</div>\n";
echo "</div>\n";
unset($target_container);
$field_prefix = '';
 */
?>

<!-- THe reproductive planning questionnaire -->
<?php if($repo_display && $pat_sex == 'f') {
$field_prefix = '';
$display_toggle = 'tmp_repo_disp_mode';
echo "<div class='wmtMainContainer'>\n";
$use_bottom_bar = false;
generateChapter('Reproductive Life Plan', 'repo', $dt[$display_toggle],
		'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
  echo "	<div id='repoBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
include($GLOBALS['srcdir'].'/wmt-v2/form_modules/reproductive1_module.inc.php');
echo "	</div>\n";
echo "</div>\n";
$field_prefix = '';
} ?>

<?php if($hepc_display) { ?>
<div class="wmtMainContainer"><!-- Start of the Hep C Box -->
  <div class="wmtCollapseBar" id="HepcCollapseBar" style="border-bottom: <?php echo (($dt['tmp_hepc_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('HepcBox','HepcImageL','HepcImageR','HepcCollapseBar','','tmp_hepc_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
			<?php
			if($dt['tmp_hepc_disp_mode']=='block') {
      	echo "		<td><img id='HepcImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "		<td class='wmtChapter'>Hepatitis C Questionnaire</td>\n";
    		echo "		<td style='text-align: right'><img id='HepcImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "		<td><img id='HepcImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "		<td class='wmtChapter'>Hepatitis C Questionnaire</td>\n";
    		echo "		<td style='text-align: right'><img id='HepcImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
    </table>
  </div>

  <div id="HepcBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_hepc_disp_mode']=='block')?'block':'none'); ?>; ">
		<?php include("../../../library/wmt-v2/hepc_questions.inc.php"); ?> 
	</div>
</div>
<?php } ?>


<?php if($ped_diet_display) { ?>
<div class="wmtMainContainer"><!-- Start of the Diet Exam Box -->
  <div class="wmtCollapseBar" id="PedDietCollapseBar" style="border-bottom: <?php echo (($dt['tmp_ped_diet_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('PedDietBox','PedDietImageL','PedDietImageR','PedDietCollapseBar','','tmp_ped_diet_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
			<?php
			if($dt['tmp_ped_diet_disp_mode']=='block') {
      	echo "		<td><img id='PedDietImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "		<td class='wmtChapter'>Pediatric Diet</td>\n";
    		echo "		<td style='text-align: right'><img id='PedDietImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "		<td><img id='PedDietImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "		<td class='wmtChapter'>Pediatric Diet</td>\n";
    		echo "		<td style='text-align: right'><img id='PedDietImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
    </table>
  </div>

  <div id="PedDietBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_ped_diet_disp_mode']=='block')?'block':'none'); ?>">
		<?php include("../../../library/wmt-v2/ped_diet.inc.php"); ?> 
	</div>
</div>

<?php } ?>

<?php if($medication_display) { ?>
<div class="wmtMainContainer">
	<div class="wmtCollapseBar" id="DBMedsCollapseBar" style="border-bottom: <?php echo (($dt['tmp_med_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBMedsBox','DBMedsImageL','DBMedsImageR','DBMedsCollapseBar','','tmp_med_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="wmtColorBar">
			<?php
			if($dt['tmp_med_disp_mode']=='block') {
				echo "<td style='text-align: left'><img id='DBMedsImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Medications</td>\n";
				echo "<td style='text-align: right'><img id='DBMedsImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td style='text-align: left'><img id='DBMedsImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Medications</td>\n";
				echo "<td style='text-align: right'><img id='DBMedsImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div><!-- End of the Medication Collapse Bar -->
	<div id='DBMedsBox' class='wmtCollapseBoxWhite' style="display: <?php echo $dt['tmp_med_disp_mode']; ?>">
		<?php 
		if($use_meds_not_rx) {
			include("../../../library/wmt-v2/medications_add.inc.php");
		} else {
			include("../../../library/wmt-v2/medications_erx.inc.php");
		}
		?>
	</div>
</div><!-- End of the Medications Box -->
<?php } ?>

<?php if($otc_display) {
	$field_prefix = '';
	$display_toggle = 'tmp_otc_disp_mode';
	echo "<div class='wmtMainContainer'>\n";
	$use_bottom_bar = false;
	generateChapter('OTC Medications', 'otc', $dt[$display_toggle],
			'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
  	echo "	<div id='otcBox' class='wmtCollapseBoxWhite' style='display: ".$dt[$display_toggle].";'>\n";
	include($GLOBALS['srcdir'].'/wmt-v2/medications_otc.inc.php');
	echo "	</div>\n";
	echo "</div>\n";
} ?>

<?php if($allergy_display) { ?>
<div class="wmtMainContainer">
	<div class="wmtCollapseBar" id="DBAllergyCollapseBar" style="border-bottom: <?php echo (($dt['tmp_all_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBAllergyBox','DBAllergyImageL','DBAllergyImageR','DBAllergyCollapseBar','','tmp_all_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="wmtColorBar">
			<?php
			if($dt['tmp_all_disp_mode']=='block') {
				echo "<td style='text-align: left'><img id='DBAllergyImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Allergies</td>\n";
				echo "<td style='text-align: right'><img id='DBAllergyImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td style='text-align: left'><img id='DBAllergyImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Allergies</td>\n";
				echo "<td style='text-align: right'><img id='DBAllergyImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div><!-- End of the Allergies Collapse Bar -->
	<div id="DBAllergyBox" class="wmtCollapseBoxWhite" style="display: <?php echo $dt['tmp_all_disp_mode']; ?>">
	<?php include("../../../library/wmt-v2/allergies.inc.php"); ?>
	</div>
</div><!-- End of the Allergies Box -->
<?php } ?>

<?php
if($pat_sex == 'f') {
	if($pp_display) {
?>
<div class="wmtMainContainer"><!-- Start of the Past Pregnancy Box -->
  <div class="wmtCollapseBar" id="DBPPCollapseBar" style="border-bottom: <?php echo (($dt['tmp_pp_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBPPBox','DBPPImageL','DBPPImageR','DBPPCollapseBar','','tmp_pp_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
			<?php
			if($dt['tmp_pp_disp_mode']=='block') {
      	echo "		<td><img id='DBPPImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "		<td class='wmtChapter'>Obstetrical History</td>\n";
    		echo "		<td style='text-align: right'><img id='DBPPImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "		<td><img id='DBPPImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "		<td class='wmtChapter'>Obstetrical History</td>\n";
    		echo "		<td style='text-align: right'><img id='DBPPImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
    </tr>
    </table>
  </div><!-- End of the Past Pregnancy Collapse Bar -->
  <div id="DBPPBox" class="wmtCollapseBoxWhite" style="display: <?php echo $dt['tmp_pp_disp_mode']; ?>">
      <?php include("../../../library/wmt-v2/past_pregnancies.inc.php"); ?>
  </div>
</div><!-- The end of the past pregnancy div -->
<?php
	}
}
?>

<?php if($surgery_display) { ?>
<div class="wmtMainContainer">
	<div class="wmtCollapseBar" id="DBPrevSurgCollapseBar" style="border-bottom: <?php echo (($dt['tmp_ps_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBPrevSurgBox','DBPrevSurgImageL','DBPrevSurgImageR','DBPrevSurgCollapseBar','','tmp_ps_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="wmtColorBar">
			<?php
			if($dt['tmp_ps_disp_mode']=='block') {
				echo "<td style='text-align: left'><img id='DBPrevSurgImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Surgeries</td>\n";
				echo "<td style='text-align: right'><img id='DBPrevSurgImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td><img id='DBPrevSurgImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Surgeries</td>\n";
				echo "<td style='text-align: right'><img id='DBPrevSurgImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div>
	<div class="wmtCollapseBoxWhite" id="DBPrevSurgBox" style="display: <?php echo (($dt['tmp_ps_disp_mode']=='block')?'block':'none'); ?>">
	<?php include("../../../library/wmt-v2/previous_surgeries.inc.php"); ?>
	</div>
</div><!-- The end of the previous surgeries div -->
<?php } ?>

<?php if($mh_display) { ?>
<div class="wmtMainContainer"><!-- Start of the Past Medical History Box -->
  <div class="wmtCollapseBar" id="DBMHCollapseBar" style="border-bottom: <?php echo (($dt['tmp_pmh_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBMHBox','DBMHImageL','DBMHImageR','DBMHCollapseBar','','tmp_pmh_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
			<?php
			if($dt['tmp_pmh_disp_mode']=='block') {
      	echo "<td style='text-align: left'><img id='DBMHImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Medical History</td>\n";
    		echo "<td style='text-align: left'><img id='DBMHImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "<td style='text-align: left'><img id='DBMHImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Medical History</td>\n";
    		echo "<td style='text-align: right'><img id='DBMHImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
    </table>
  </div><!-- End of the Past Medical History Collapse Bar -->
  <div id="DBMHBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_pmh_disp_mode']=='block')?'block':'none'); ?>;">
		<?php include("../../../library/wmt-v2/past_med_history.inc.php"); ?>
  </div>
</div><!-- This is the end of the past medical history box -->
<?php } ?>

<?php if($fh_display) { ?>
<div class="wmtMainContainer"><!-- Start of the Family History Box -->
  <div class="wmtCollapseBar" id="DBFHCollapseBar" style="border-bottom: <?php echo (($dt['tmp_fh_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBFHBox','DBFHImageL','DBFHImageR','DBFHCollapseBar','','tmp_fh_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
			<?php
			if($dt['tmp_fh_disp_mode']=='block') {
      	echo "<td style='text-align: left'><img id='DBFHImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Family History</td>\n";
    		echo "<td style='text-align: left'><img id='DBFHImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "<td style='text-align: left'><img id='DBFHImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Family History</td>\n";
    		echo "<td style='text-align: right'><img id='DBFHImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
    </table>
  </div><!-- End of the Family History Collapse Bar -->
  <div id="DBFHBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_fh_disp_mode']=='block')?'block':'none'); ?>;">
	<?php include("../../../library/wmt-v2/family_history.inc.php"); ?>
  </div>
</div><!-- End of the family history box -->
<?php } ?>

<?php 
if($pat_sex == 'f') {
	if($pap_display) {
?>
<div class="wmtMainContainer"><!-- Start of the Pap Tracking Box -->
  <div class="wmtCollapseBar" id="DBPapCollapseBar" style="border-bottom: <?php echo (($dt['tmp_pap_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBPapBox','DBPapImageL','DBPapImageR','DBPapCollapseBar','','tmp_pap_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
			<?php
			if($dt['tmp_pap_disp_mode']=='block') {
      	echo "<td><img id='DBPapImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Pap Tracking</td>\n";
    		echo "<td style='text-align: left'><img id='DBPapImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "<td><img id='DBPapImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Pap Tracking</td>\n";
    		echo "<td style='text-align: right'><img id='DBPapImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
    </table>
  </div><!-- End of the Pap Tracking Collapse Bar -->
  <div id="DBPapBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_pap_disp_mode']=='block')?'block':'none'); ?>">
	<?php include("../../../library/wmt-v2/pap_track.inc.php"); ?>
	</div>
</div>
<?php
	}
}
?>

<?php if($immunization_display) { ?>	
<div class="wmtMainContainer">
	<div class="wmtCollapseBar" id="DBImmCollapseBar" style="border-bottom: <?php echo (($dt['tmp_imm_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBImmBox','DBImmImageL','DBImmImageR','DBImmCollapseBar','','tmp_imm_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="wmtColorBar">
			<?php
			if($dt['tmp_imm_disp_mode']=='block') {
				echo "<td style='text-align: left'><img id='DBImmImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Immunizations</td>\n";
				echo "<td style='text-align: left'><img id='DBImmImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td style='text-align: left'><img id='DBImmImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Immunizations</td>\n";
				echo "<td style='text-align: right'><img id='DBImmImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div>
	<div class="wmtCollapseBoxWhite" id="DBImmBox" style="display: <?php echo (($dt['tmp_imm_disp_mode']=='block')?'block':'none'); ?>">
		<?php include("../../../library/wmt-v2/immunizations.inc.php"); ?>
	</div>
</div><!-- The end of the immunizations div -->
<?php } ?>

<?php if($image_display) { ?>
<div class="wmtMainContainer">
	<div class="wmtCollapseBar" id="DBImageCollapseBar" style="border-bottom: <?php echo (($dt['tmp_img_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBImageBox','DBImageImageL','DBImageImageR','DBImageCollapseBar','','tmp_img_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="wmtColorBar">
			<?php
			if($dt['tmp_img_disp_mode']=='block') {
				echo "<td style='text-align: left'><img id='DBImageImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Images</td>\n";
				echo "<td style='text-align: right'><img id='DBImageImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td style='text-align: left'><img id='DBImageImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Images</td>\n";
				echo "<td style='text-align: right'><img id='DBImageImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div><!-- End of the Images Collapse Bar -->
	<div id="DBImageBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_img_disp_mode']=='block')?'block':'none'); ?>">
	<?php include("../../../library/wmt-v2/image_summary.inc.php"); ?>
	</div>
</div><!-- End of the Images Box -->
<?php } ?>

<?php if($birth_history_display) { ?>
<div class="wmtMainContainer">
	<div class="wmtCollapseBar" id="BirthCollapseBar" style="border-bottom: <?php echo (($dt['tmp_birth_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('BirthBox','BirthImageL','BirthImageR','BirthCollapseBar','','tmp_birth_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="wmtColorBar">
			<?php
			if($dt['tmp_birth_disp_mode']=='block') {
				echo "<td style='text-align: left'><img id='BirthImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Birth History</td>\n";
				echo "<td style='text-align: right'><img id='BirthImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td style='text-align: left'><img id='BirthImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Birth History</td>\n";
				echo "<td style='text-align: right'><img id='BirthImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div><!-- End of the Birth History Collapse Bar -->
	<div id="BirthBox" class="wmtCollapseBoxWhite" style="display: <?php echo $dt['tmp_birth_disp_mode']; ?>">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Notes:</td>
			</tr>
			<tr>
				<td><textarea name="db_birth_nt" id="db_birth_nt" class="wmtFullInput" rows="5"><?php echo $dt{'db_birth_nt'}; ?></textarea></td>
			</tr>
		</table>
	</div>
</div><!-- End of the Birth History Box -->
<?php } ?>

<?php if($admission_display) { ?>
<div class="wmtMainContainer"><!-- Start of the Hospitalizations Box -->
  <div class="wmtCollapseBar" id="DBPHCollapseBar" style="border-bottom: <?php echo (($dt['tmp_hosp_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBPHBox','DBPHImageL','DBPHImageR','DBPHCollapseBar','','tmp_hosp_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
			<?php
			if($dt['tmp_hosp_disp_mode']=='block') {
      	echo "<td style='text-align: left;'><img id='DBPHImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Admissions</td>\n";
    		echo "<td style='text-align: right'><img id='DBPHImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "<td style='text-align: left'><img id='DBPHImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "<td class='wmtChapter'>Admissions</td>\n";
    		echo "<td style='text-align: right'><img id='DBPHImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
    </table>
  </div><!-- End of the Hospitalizations Collapse Bar -->
  <div id="DBPHBox" class="wmtCollapseBoxWhite" style="display: <?php echo (($dt['tmp_hosp_disp_mode']=='block')?'block':'none'); ?>">
			<?php include("../../../library/wmt-v2/hospitalizations.inc.php"); ?>
		</table>
  </div>
</div><!-- This is the end of the hospitalizations box -->
<?php } ?>

<?php 
if($pat_sex == 'f') {
	if($ultrasound_display) {
?>
<div class="wmtMainContainer">
  <div class="wmtCollapseBar" id="DBUsCollapseBar" style="border-bottom: <?php echo (($dt['tmp_us_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBUsBox','DBUsImageL','DBUsImageR','DBUsCollapseBar','','tmp_us_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
		<?php 
		if($dt['tmp_us_disp_mode']=='block') {
      echo "<td style='text-align: left'><img id='DBUsImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    	echo "<td class='wmtChapter'>Ultrasounds</td>\n";
    	echo "<td style='text-align: right'><img id='DBUsImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
		} else {
      echo "<td style='text-align: left'><img id='DBUsImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    	echo "<td class='wmtChapter'>Ultrasounds</td>\n";
    	echo "<td style='text-align: right'><img id='DBUsImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
		}
		?>
		</tr>
    </table>
  </div><!-- End of the Ultrasound Collapse Bar -->
  <div id="DBUsBox" class="wmtCollapseBoxWhite" style="display: <?php echo $dt['tmp_us_disp_mode']; ?>">
		<?php include("../../../library/wmt-v2/us_track.inc.php"); ?>
	</div>
</div>
<?php } ?>

<?php if($bd_display) { ?>
<div class="wmtMainContainer">
  <div class="wmtCollapseBar" id="DBBdCollapseBar" style="border-bottom: <?php echo (($dt['tmp_bd_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBBdBox','DBBdImageL','DBBdImageR','DBBdCollapseBar','','tmp_bd_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
		<?php 
		if($dt['tmp_bd_disp_mode']=='block') {
      echo "<td><img id='DBBdImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    	echo "<td class='wmtChapter'>Bone Density</td>\n";
    	echo "<td style='text-align: right'><img id='DBBdImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
		} else {
      echo "<td><img id='DBBdImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    	echo "<td class='wmtChapter'>Bone Density</td>\n";
    	echo "<td style='text-align: right'><img id='DBBdImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
		}
		?>
		</tr>
    </table>
  </div><!-- End of the Bone Density Collapse Bar -->
	<div id="DBBdBox" class="wmtCollapseBoxWhite" style="display: <?php echo $dt['tmp_bd_disp_mode']; ?>">
		<?php include("../../../library/wmt-v2/bd_track.inc.php"); ?>
	</div>
</div>
<?php
	}
}
?>

<?php if($diagnosis_display) { ?>
<div class="wmtMainContainer"><!-- Beginning of the Diag Box -->
  <div class="wmtCollapseBar" id="DBDiagCollapseBar" style="border-bottom: <?php echo (($dt['tmp_diag_disp_mode']=='block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBDiagBox','DBDiagImageL','DBDiagImageR','DBDiagCollapseBar','','tmp_diag_disp_mode')">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="wmtColorBar">
			<?php
			if($dt['tmp_diag_disp_mode']=='block') {
      	echo "				<td style='text-align: left'><img id='DBDiagImageL' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "				<td class='wmtChapter'>Diagnosis / Problems</td>\n";
    		echo "				<td style='text-align: right'><img id='DBDiagImageR' src='../../../library/wmt-v2/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
      	echo "				<td style='text-align: left'><img id='DBDiagImageL' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
    		echo "				<td class='wmtChapter'>Diagnosis / Problems</td>\n";
    		echo "				<td style='text-align: right'><img id='DBDiagImageR' src='../../../library/wmt-v2/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
    </tr>
    </table>
  </div><!-- End of the Lab Collapse Bar -->
  <div id="DBDiagBox" class="wmtCollapseBoxWhite" style="display: <?php echo $dt['tmp_diag_disp_mode']; ?>">
		<?php $target_container = 'DBDiagBox'; ?>
		<?php include("../../../library/wmt-v2/diagnosis.inc.php"); ?>
		<?php unset($target_container); ?>
	</div>
</div>		
<?php } ?>

<div style="display: none"><!-- For the hidden PP Ids and order fields -->
<input name="db_vid" id="db_vid" type="hidden" value="<?php echo $vid; ?>" />
<input name="tmp_scroll_top" id="tmp_scroll_top" type="hidden" value="<?php echo $dt['tmp_scroll_top']; ?>" />
<input name="tmp_pat_info_fyi_disp_mode" id="tmp_pat_info_fyi_disp_mode" type="hidden" value="<?php echo $dt['tmp_pat_info_fyi_disp_mode']; ?>" />
<input name="tmp_pat_info_ob_disp_mode" id="tmp_pat_info_ob_disp_mode" type="hidden" value="<?php echo $dt['tmp_pat_info_ob_disp_mode']; ?>" />
<input name="tmp_sh_disp_mode" id="tmp_sh_disp_mode" type="hidden" value="<?php echo $dt['tmp_sh_disp_mode']; ?>" />
<input name="tmp_repo_disp_mode" id="tmp_repo_disp_mode" type="hidden" value="<?php echo $dt['tmp_repo_disp_mode']; ?>" />
<input name="tmp_well_full_disp_mode" id="tmp_well_full_disp_mode" type="hidden" value="<?php echo $dt['tmp_well_full_disp_mode']; ?>" />
<input name="tmp_med_disp_mode" id="tmp_med_disp_mode" type="hidden" value="<?php echo $dt['tmp_med_disp_mode']; ?>" />
<input name="tmp_med_window_mode" id="tmp_med_window_mode" type="hidden" value="<?php echo $dt['tmp_med_window_mode']; ?>" />
<input name="tmp_otc_window_mode" id="tmp_otc_window_mode" type="hidden" value="<?php echo $dt['tmp_otc_window_mode']; ?>" />
<input name="tmp_hepc_disp_mode" id="tmp_hepc_disp_mode" type="hidden" value="<?php echo $dt['tmp_hepc_disp_mode']; ?>" />
<input name="tmp_all_disp_mode" id="tmp_all_disp_mode" type="hidden" value="<?php echo $dt['tmp_all_disp_mode']; ?>" />
<input name="tmp_pp_disp_mode" id="tmp_pp_disp_mode" type="hidden" value="<?php echo $dt['tmp_pp_disp_mode']; ?>" />
<input name="tmp_ps_disp_mode" id="tmp_ps_disp_mode" type="hidden" value="<?php echo $dt['tmp_ps_disp_mode']; ?>" />
<input name="tmp_pmh_disp_mode" id="tmp_pmh_disp_mode" type="hidden" value="<?php echo $dt['tmp_pmh_disp_mode']; ?>" />
<input name="tmp_fh_disp_mode" id="tmp_fh_disp_mode" type="hidden" value="<?php echo $dt['tmp_fh_disp_mode']; ?>" />
<input name="tmp_ros_disp_mode" id="tmp_ros_disp_mode" type="hidden" value="<?php echo $dt['tmp_ros_disp_mode']; ?>" />
<input name="tmp_pap_disp_mode" id="tmp_pap_disp_mode" type="hidden" value="<?php echo $dt['tmp_pap_disp_mode']; ?>" />
<input name="tmp_imm_disp_mode" id="tmp_imm_disp_mode" type="hidden" value="<?php echo $dt['tmp_imm_disp_mode']; ?>" />
<input name="tmp_bd_disp_mode" id="tmp_bd_disp_mode" type="hidden" value="<?php echo $dt['tmp_bd_disp_mode']; ?>" />
<input name="tmp_us_disp_mode" id="tmp_us_disp_mode" type="hidden" value="<?php echo $dt['tmp_us_disp_mode']; ?>" />
<input name="tmp_img_disp_mode" id="tmp_img_disp_mode" type="hidden" value="<?php echo $dt['tmp_img_disp_mode']; ?>" />
<input name="tmp_ped_diet_disp_mode" id="tmp_ped_diet_disp_mode" type="hidden" value="<?php echo $dt['tmp_ped_diet_disp_mode']; ?>" />
<input name="tmp_birth_disp_mode" id="tmp_birth_disp_mode" type="hidden" value="<?php echo $dt['tmp_birth_disp_mode']; ?>" />
<input name="tmp_hosp_disp_mode" id="tmp_hosp_disp_mode" type="hidden" value="<?php echo $dt['tmp_hosp_disp_mode']; ?>" />
<input name="tmp_diag_disp_mode" id="tmp_diag_disp_mode" type="hidden" value="<?php echo $dt['tmp_diag_disp_mode']; ?>" />
<input name="tmp_diag_window_mode" id="tmp_diag_window_mode" type="hidden" value="<?php echo $dt['tmp_diag_window_mode']; ?>" />
<input name="tmp_yes_disp_mode" id="tmp_yes_disp_mode" type="hidden" value="<?php echo $dt['tmp_yes_disp_mode']; ?>" />
<input name="tmp_no_disp_mode" id="tmp_no_disp_mode" type="hidden" value="<?php echo $dt['tmp_no_disp_mode']; ?>" />
<?php 
// This builds the defaults for javascript to reference
// if they are adding to family history
if($fh_display) {
	foreach($fh_defaults as $who => $what) {
  	echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."' type='hidden' value='".$what{'fhm_who'}."' />\n";
  	echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."_dead' type='hidden' value='".$what{'fhm_deceased'}."' />\n";
  	echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."_age' type='hidden' value='".$what{'fhm_age'}."' />\n";
  	echo "<input name='fh_def_".$who."[]' id='fh_def_".$who."_age_dead' type='hidden' value='".$what{'fhm_age_dead'}."' />\n";
	}
}
?>
</div>

</br>
<table width="100%" border="0">
	<tr>
		<td><a href="javascript:top.restoreSession(); document.db_form.submit();" class="css_button"><span>Save Data &amp; Return</span></a></td>

		<td><div style="float: right"><a href="../../patient_file/summary/demographics.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> class="css_button" onclick="top.restoreSession()"><span>Cancel &amp; Return</a></span></div></td>
	</tr>
</table>
</div><!-- This is the end of the overall margin div -->
</form>
</body>
<script type="text/javascript" src="../../../library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/wmt-v2/wmtportal.js"></script>
<script src="<?php echo $webroot; ?>/library/wmt-v2/wmtstandard.popup.js" type="text/javascript"></script>
<?php
	if($bd_display) echo "<script type='text/javascript' src='../../../library/wmt-v2/bd_track.popup.js'></script>\n";
	if($pap_display) echo "<script type='text/javascript' src='../../../library/wmt-v2/pap_track.popup.js'></script>\n";
	if($ultrasound_display) echo "<script type='text/javascript' src='../../../library/wmt-v2/us_track.popup.js'></script>\n";
	if($hepc_display) echo "<script type='text/javascript' src='../../../library/wmt-v2/hepc.js'></script>\n";
	if($diagnosis_display) echo "<script type='text/javascript' src='../../../library/wmt-v2/diagnosis.js'></script>\n";
?>
<script type="text/javascript">

function cancelClicked() {
<?php if($warn_popup && strtolower($dt[$cancel_field]) != $cancel_compare) { ?>
	response=confirm("<?php echo $cancel_warning; ?>");
	if(response == true) {
	<?php if(!$pop_form) { ?>
	<?php } ?>
		if(typeof top.restoreSession === "function") {
			top.restoreSession();
			return true;
		}
	} else {
		return false;
	}
<?php } else { ?>
	return true;
<?php } ?>
}

<?php if($social_display) { ?>
Calendar.setup({inputField:"db_smoking_dt", ifFormat:"%Y-%m-%d", button:"img_smoking_dt"});
Calendar.setup({inputField:"db_alcohol_dt", ifFormat:"%Y-%m-%d", button:"img_alcohol_dt"});
Calendar.setup({inputField:"db_drug_dt", ifFormat:"%Y-%m-%d", button:"img_drug_dt"});
<?php } ?>

<?php if(false) { ?>
	<?php if(checkSettingMode('wmt::db_wellness_blood')) { ?>
	Calendar.setup({inputField:"db_last_chol", ifFormat:"%Y-%m-%d", button:"img_last_chol_dt"});
	<?php if($client_id != 'cffm') { ?>
	Calendar.setup({inputField:"db_last_hepc", ifFormat:"%Y-%m-%d", button:"img_last_hepc_dt"});
	Calendar.setup({inputField:"db_last_lipid", ifFormat:"%Y-%m-%d", button:"img_last_lipid_dt"});
	Calendar.setup({inputField:"db_last_lipo", ifFormat:"%Y-%m-%d", button:"img_last_lipo_dt"});
	Calendar.setup({inputField:"db_last_tri", ifFormat:"%Y-%m-%d", button:"img_last_tri_dt"});
	<?php } ?>
	Calendar.setup({inputField:"db_last_urine_alb", ifFormat:"%Y-%m-%d", button:"img_last_urine_alb_dt"});
	Calendar.setup({inputField:"db_last_hgba1c", ifFormat:"%Y-%m-%d", button:"img_last_hgba1c_dt"});
	<?php } ?>
	<?php if(checkSettingMode('wmt::db_wellness_cardio')) { ?>
	Calendar.setup({inputField:"db_last_ekg", ifFormat:"%Y-%m-%d", button:"img_last_ekg_dt"});
	Calendar.setup({inputField:"db_last_pft", ifFormat:"%Y-%m-%d", button:"img_last_pft_dt"});
	<?php } ?>
	<?php if(checkSettingMode('wmt::db_wellness_colon')) { ?>
	Calendar.setup({inputField:"db_last_colon", ifFormat:"%Y-%m-%d", button:"img_last_colon_dt"});
	Calendar.setup({inputField:"db_last_fecal", ifFormat:"%Y-%m-%d", button:"img_last_fecal_dt"});
	<?php if($client_id != 'cffm') { ?>
	Calendar.setup({inputField:"db_last_barium", ifFormat:"%Y-%m-%d", button:"img_last_barium_dt"});
	Calendar.setup({inputField:"db_last_sigmoid", ifFormat:"%Y-%m-%d", button:"img_last_sigmoid_dt"});
	<?php } ?>
		<?php if($pat_sex == 'm') { ?>
		Calendar.setup({inputField:"db_last_psa", ifFormat:"%Y-%m-%d", button:"img_last_psa_dt"});
		Calendar.setup({inputField:"db_last_rectal", ifFormat:"%Y-%m-%d", button:"img_last_rectal_dt"});
		<?php } ?>
	<?php } ?>
	<?php if(checkSettingMode('wmt::db_wellness_diabetes')) { ?>
	Calendar.setup({inputField:"db_last_db_screen", ifFormat:"%Y-%m-%d", button:"img_last_db_screen_dt"});
	Calendar.setup({inputField:"db_last_db_eye", ifFormat:"%Y-%m-%d", button:"img_last_db_eye_dt"});
	Calendar.setup({inputField:"db_last_db_foot", ifFormat:"%Y-%m-%d", button:"img_last_db_foot_dt"});
	Calendar.setup({inputField:"db_last_glaucoma", ifFormat:"%Y-%m-%d", button:"img_last_glaucoma_dt"});
	Calendar.setup({inputField:"db_last_db_dbsmt", ifFormat:"%Y-%m-%d", button:"img_last_db_dbsmt_dt"});
	<?php } ?>
	<?php if(checkSettingMode('wmt::wellness_gyn', '', $frmdir)) { ?>
		<?php if($pat_sex == 'f') { ?>
		// Calendar.setup({inputField:"db_last_mp", ifFormat:"%Y-%m-%d", button:"img_db_last_mp_dt"});
		// Calendar.setup({inputField:"db_last_bone", ifFormat:"%Y-%m-%d", button:"img_last_bone_dt"});
		// Calendar.setup({inputField:"db_last_mamm", ifFormat:"%Y-%m-%d", button:"img_last_mamm_dt"});
		// Calendar.setup({inputField:"db_last_hpv", ifFormat:"%Y-%m-%d", button:"img_last_hpv_dt"});
		// Calendar.setup({inputField:"db_last_pap", ifFormat:"%Y-%m-%d", button:"img_last_pap_dt"});
		<?php } ?>
	<?php } ?>
<?php } ?>

function set_item(code, desc, itemfield)
{
 if(code) {
  var s = document.getElementById(itemfield);
  s.options[s.options.length]= new Option(desc, code, true);
  s.options[s.options.length - 1].selected = true;
  s.value = code;
 }
}

// This invokes the find-code popup.
function add_item(itemField,itemType)
{
 dlgopen('../../../custom/add_list_entry_popup.php?thisItem='+itemField+'&thisList='+itemType, '_blank', 500, 300);
}

function get_family_defaults() {
	var thisPerson=document.getElementById('fh_who').value;
	var thisField='fh_def_'+thisPerson+'[]';
	var traits=document.forms[0].elements[thisField];
	// alert("Number of Traits: "+traits.length);
	// for (var cnt=0; cnt < traits.length; cnt++) alert("This Trait: "+traits[cnt].value);
	document.forms[0].elements['fh_dead'].value=traits[1].value;
	document.forms[0].elements['fh_age'].value=traits[2].value;
	document.forms[0].elements['fh_age_dead'].value=traits[3].value;
}

// Partial Saving
function AutoSave(base, wrap, field, formID)
{
	var myAction = base+'&mode=save&wrap='+wrap+'&continue=true';
	if(field != '') myAction = myAction+'&focusfield='+field;
	if(formID != '') myAction = myAction+'&id='+formID;
	SetScrollTop();
	document.forms[0].action = myAction;
	document.forms[0].submit();
}

function PopRTO() {
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=yes&pid=<?php echo $pid; ?>', '_blank', 1200, 400, 1);
}

function submit_print_section(base,wrap,section) {
	SetScrollTop();
	var myAction = base+'&mode=save&wrap='+wrap+'&continue='+section;
	// alert("My New Action: "+myAction);
	document.forms[0].action = myAction;
	document.forms[0].submit();
}

// Saving and print instructions
function print_section(section)
{
	// alert("Section is set: "+section);
	var target = '';
	if(section == 'hepc') {
		target = '../../forms/dashboard/print_hepc.php?&patient_id=<?php echo $pid; ?>&pop=pop';
	}
	if(target) {
		wmtOpen(target, '_blank', 600, 800);
	}
}

function toggleROStoNo()
{
  var i;
  var l = document.db_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.db_form.elements[i].type.indexOf('select') != -1) {
      if(document.db_form.elements[i].name.indexOf('ee1_rs_') != -1) {
        document.db_form.elements[i].selectedIndex = '2';
      }
    }
    if(document.db_form.elements[i].type.indexOf('check') != -1) {
      if(document.db_form.elements[i].name.indexOf('_hpi') != -1) {
        document.db_form.elements[i].checked= false;
      }
		}
  }
}

function toggleROStoNull()
{
  var i;
  var l = document.db_form.elements.length;
  for (i=0; i<l; i++) {
    if(document.db_form.elements[i].type.indexOf('select') != -1) {
      if(document.db_form.elements[i].name.indexOf('ee1_rs_') != -1) {
        document.db_form.elements[i].selectedIndex = '0';
      }
    }
  }
}

</script>
</html>
