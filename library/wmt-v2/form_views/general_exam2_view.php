<?php
loadFormComments($dt, 'general_exam2', $id, $frmn, $pid);
$chp_printed = $hdr_printed = false;
$nt='';
$include_exam_vitals = checkSettingMode('wmt::exam_vitals','',$frmdir);
if($include_exam_vitals != '') {
	$vitals_module = 'vitals_'.$include_exam_vitals.'_view.php';
	if(is_file("./$vitals_module")) {
		include("./$vitals_module");
	} else if(is_file($GLOBALS['srcdir'].'/wmt-v2/form_views/'.$vitals_module)) {
		include($GLOBALS['srcdir'].'/wmt-v2/form_views/'.$vitals_module);
	}
}

if($hdr_printed) {
	echo "	</table>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
}

$hdr_printed=false;
$nt=trim($dt{'ge_dictate'});
if(!empty($nt)) {
	EE1_PrintNote($nt,$chp_title,'General Exam Notes:');
}

$hdr_printed=false;
$nt=trim($dt{'diagnostics_nt'});
if(!empty($nt)) {
	EE1_PrintNote($nt,$chp_title,'Diagnostic Tests:');
}

$hdr_printed=false;
// First Pass, set to '0' is for anything with comments only.
// Second pass will print no choices, then third pass no choices with comments
// fourth pass is yes choices, then last (fifth) pass is yes with comments.
$hdr = 'General:';
if($dt{'ge_gen_norm_exam'} == '1') {
	$nt = 'Normal habitus, well developed, well groomed and no acute distress.';
	EE1_PrintNote($nt, $chp_title, $hdr);
	if($client_id != 'sfa') {
		$nt = 'No Jaundice, No Wasting and Sleep Patterns Normal';
		EE1_PrintNote($nt, $chp_title, $hdr);
	}
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Normal Habitus', $dt{'ge_gen_norm'}, 
			$dt{'ge_gen_norm_nt'});
		EE1_GenPrintCheck('Well Developed', $dt{'ge_gen_dev'}, 
			$dt{'ge_gen_dev_nt'});
		EE1_GenPrintCheck('Well Groomed', $dt{'ge_gen_groom'}, 
			$dt{'ge_gen_groom_nt'});
		EE1_GenPrintCheck('No Acute Distress', $dt{'ge_gen_dis'}, 
			$dt{'ge_gen_dis_nt'});

		EE1_GenPrintChoice('Jaundice: ', $dt{'ge_gen_jaun'}, 
				$dt{'ge_gen_jaun_nt'});
		EE1_GenPrintChoice('Wasting: ', $dt{'ge_gen_waste'}, 
				$dt{'ge_gen_waste_nt'});
		EE1_GenPrintChoice('Sleep Pattern: ', $dt{'ge_gen_sleep'}, 
				$dt{'ge_gen_sleep_nt'},'a','n', 'NormAbnorm');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$nt=trim($dt{'ge_gen_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$hdr = 'Head:';
if($dt{'ge_hd_norm_exam'} == '1') {
	$nt = 'Atraumatic, normocephalic, midline, symmetric.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Atraumatic', $dt{'ge_hd_atra'}, 
			$dt{'ge_hd_atra_nt'});
		EE1_GenPrintCheck('Normocephalic', $dt{'ge_hd_norm'}, 
			$dt{'ge_hd_norm_nt'});

		EE1_GenPrintChoice('Facial Features: ', $dt{'ge_hd_feat'}, 
			$dt{'ge_hd_feat_nt'}, 's', 'd', 'Facial_Features');
		EE1_GenPrintChoice('Anterior Fontanel: ', $dt{'ge_hd_ant'}, 
			$dt{'ge_hd_ant_nt'}, 'o~s', '', 'ant_font');

		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$hd_chks = explode('|', $dt{'ge_hd_chks'});
$nt = BuildPrintList($hd_chks);
if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }

$nt=trim($dt{'ge_hd_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Eyes:';
$sub = 'Fundoscopic:';
if($dt{'ge_eye_norm_exam'} == '1') {
	$nt = 'PERRL and EOMI bilaterally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$chk = $prnt = '';
	$chc=ListLook($dt{'ge_eye_pupil'},'EE1_Pupil');
	if(!empty($chc) || !empty($dt{'ge_eye_pupil_nt'})) { $chk='Pupils'; }
	EE1_PrintGE($chk,$chc,$dt{'ge_eye_pupil_nt'},$hdr);
	// Fundoscopic section can be combined
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Hemorrhage: ', $dt{'ge_eye_hem'}, 
					$dt{'ge_eye_hem_nt'});
		EE1_GenPrintChoice('Exudate: ', $dt{'ge_eye_exu'}, 
					$dt{'ge_eye_exu_nt'});
		EE1_GenPrintChoice('AV Nicking: ', $dt{'ge_eye_av'}, 
					$dt{'ge_eye_av_nt'});
		EE1_GenPrintChoice('Papilledema: ', $dt{'ge_eye_pap'}, 
					$dt{'ge_eye_pap_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}

	$sub_printed=false;
	$sub = 'Right Eye';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		if($client_id != 'sfa') {
			EE1_GenPrintChoice('Abnormalities: ', $dt{'ge_eyer_norm'}, 
					$dt{'ge_eyer_norm_nt'}, 'a', 'n', 'NormAbnorm');
		} else {
			EE1_GenPrintCheck('No Abnormalities', $dt{'ge_eyer_norm'}, 
				$dt{'ge_eyer_norm_nt'});
		}
		EE1_GenPrintChoice('Exophthalmos: ', $dt{'ge_eyer_exo'}, 
					$dt{'ge_eyer_exo_nt'});
		EE1_GenPrintChoice('Stare: ', $dt{'ge_eyer_stare'}, 
					$dt{'ge_eyer_stare_nt'});
		EE1_GenPrintChoice('Lid Lag: ', $dt{'ge_eyer_lag'}, 
					$dt{'ge_eyer_lag_nt'});
		EE1_GenPrintCheck('No Scleral Injection', $dt{'ge_eyer_scleral'}, 
					$dt{'ge_eyer_scleral_nt'});
		EE1_GenPrintChoice('EOMI: ', $dt{'ge_eyer_eomi'}, 
					$dt{'ge_eyer_eomi_nt'});
		EE1_GenPrintChoice('PERRL: ', $dt{'ge_eyer_perrl'}, 
					$dt{'ge_eyer_perrl_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
	
	$sub_printed=false;
	$sub = 'Left Eye';
	//	$chc=ListLook($dt{'ge_eyel_norm'},'NormAbnorm');
	$cnt = 1;
	EE1_GenPrintCheck('No Abnormalities', $dt{'ge_eyel_norm'}, 
			$dt{'ge_eyel_norm_nt'});
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		if($client_id != 'sfa') {
			EE1_GenPrintChoice('Abnormalities: ', $dt{'ge_eyel_norm'}, 
					$dt{'ge_eyel_norm_nt'}, 'a', 'n', 'NormAbnorm');
		} else {
			EE1_GenPrintCheck('No Abnormalities', $dt{'ge_eyel_norm'}, 
				$dt{'ge_eyel_norm_nt'});
		}
		EE1_GenPrintChoice('Exophthalmos: ', $dt{'ge_eyel_exo'}, 
					$dt{'ge_eyel_exo_nt'});
		EE1_GenPrintChoice('Stare: ', $dt{'ge_eyel_stare'}, 
					$dt{'ge_eyel_stare_nt'});
		EE1_GenPrintChoice('Lid Lag: ', $dt{'ge_eyel_lag'}, 
					$dt{'ge_eyel_lag_nt'});
		EE1_GenPrintCheck('No Scleral Injection', $dt{'ge_eyel_scleral'}, 
					$dt{'ge_eyel_scleral_nt'});
		EE1_GenPrintChoice('EOMI: ', $dt{'ge_eyel_eomi'}, 
					$dt{'ge_eyel_eomi_nt'});
		EE1_GenPrintChoice('PERRL: ', $dt{'ge_eyel_perrl'}, 
					$dt{'ge_eyel_perrl_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_eye_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed = false;
$sub_printed = false;
$hdr = 'Ears:';
$sub = 'Right Ear';
if($dt{'ge_ear_norm_exam'} == '1') {
	$nt = 'Auditory canals and TMs clear bilaterally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$chk = '';
	$cnt = 4;
	if(!empty($dt{'ge_earr_tym_nt'})) { 
		$chk='Tympanic Membrane';
		EE1_GenPrintCheck($chk, 1, $dt{'ge_earr_tym_nt'});
	}
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clear: ', $dt{'ge_earr_clear'}, 
					$dt{'ge_earr_clear_nt'});
		EE1_GenPrintChoice('Perforation: ', $dt{'ge_earr_perf'}, 
					$dt{'ge_earr_perf_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ge_earr_ret'}, 
					$dt{'ge_earr_ret_nt'});
		EE1_GenPrintChoice('Bulging: ', $dt{'ge_earr_bulge'}, 
					$dt{'ge_earr_bulge_nt'});
		EE1_GenPrintChoice('Drainage: ', $dt{'ge_earr_pus'}, 
			$dt{'ge_earr_pus_nt'}, '', 'c~p~b', 'ear_drain');
		EE1_GenPrintChoice('Cerumen: ', $dt{'ge_earr_ceru'}, 
					$dt{'ge_earr_ceru_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}

	$sub_printed=false;
	$sub = 'Left Ear';
	$chk = '';
	$cnt = 4;
	if(!empty($dt{'ge_earl_tym_nt'})) { 
		$chk='Tympanic Membrane';
		EE1_GenPrintCheck($chk, 1, $dt{'ge_earl_tym_nt'});
	}
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clear: ', $dt{'ge_earl_clear'}, 
					$dt{'ge_earl_clear_nt'});
		EE1_GenPrintChoice('Perforation: ', $dt{'ge_earl_perf'}, 
					$dt{'ge_earl_perf_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ge_earl_ret'}, 
					$dt{'ge_earl_ret_nt'});
		EE1_GenPrintChoice('Bulging: ', $dt{'ge_earl_bulge'}, 
					$dt{'ge_earl_bulge_nt'});
		EE1_GenPrintChoice('Drainage: ', $dt{'ge_earl_pus'}, 
			$dt{'ge_earl_pus_nt'}, '', 'c~p~b', 'ear_drain');
		EE1_GenPrintChoice('Cerumen: ', $dt{'ge_earl_ceru'}, 
					$dt{'ge_earl_ceru_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr, $sub); }
		$cnt++;
	}
}

$nt = trim($dt{'ge_ear_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Nose:';
$sub = 'Nasal Mucosa';
if($dt{'ge_nose_norm_exam'} == '1') {
	$nt = 'Patent.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Erythema: ', $dt{'ge_nose_ery'}, 
					$dt{'ge_nose_ery_nt'});
		EE1_GenPrintChoice('Swelling: ', $dt{'ge_nose_swell'}, 
					$dt{'ge_nose_swell_nt'});
		EE1_GenPrintChoice('Pallor: ', $dt{'ge_nose_pall'}, 
					$dt{'ge_nose_pall_nt'});
		EE1_GenPrintChoice('Polyps: ', $dt{'ge_nose_polps'}, 
					$dt{'ge_nose_polps_nt'});
		EE1_GenPrintChoice('Septum: ', $dt{'ge_nose_sept'}, 
					$dt{'ge_nose_sept_nt'}, 'd', 'm', 'EE1_Septum');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_nose_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Mouth:';
$sub = '';
if($dt{'ge_mouth_norm_exam'} == '1') {
	$nt = 'Moist mucus membranes and normal exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Moist Mucus Membranes: ', $dt{'ge_mouth_moist'},
				 $dt{'ge_mouth_moist_nt'});
		EE1_GenPrintCheck('Clear of Suspicious Lesions: ', $dt{'ge_mouth_les'},
				 $dt{'ge_mouth_les_nt'});
		EE1_GenPrintChoice('Dentition: ', $dt{'ge_mouth_dent'}, 
					$dt{'ge_mouth_dent_nt'}, 'c~d', 'n', 'EE1_Denture');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Gums';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Reddened: ', $dt{'ge_mouth_gm_red'}, 
					$dt{'ge_mouth_gm_red_nt'});
		EE1_GenPrintChoice('Swollen: ', $dt{'ge_mouth_gm_swell'}, 
					$dt{'ge_mouth_gm_swell_nt'});
		EE1_GenPrintChoice('Bleeding: ', $dt{'ge_mouth_gm_bld'}, 
					$dt{'ge_mouth_gm_bld_nt'});
		if(!empty($prnt)) EE1_PrintCompoundGE($prnt,$hdr);
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Teeth';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Caries: ', $dt{'ge_mouth_th_car'}, 
					$dt{'ge_mouth_th_car_nt'});
		EE1_GenPrintChoice('Poor Dentition: ', 
					strtolower($dt{'ge_mouth_th_pd'}), $dt{'ge_mouth_th_pd_nt'});
		EE1_GenPrintChoice('Erupting: ', $dt{'ge_mouth_th_er'}, 
					$dt{'ge_mouth_th_er_nt'});
		if(!empty($prnt)) EE1_PrintCompoundGE($prnt,$hdr);
		$cnt++;
	}
}
$mouth_chks = explode('|', $dt{'ge_mouth_chks'});
$nt = BuildPrintList($mouth_chks);
if($nt) EE1_PrintNote($nt,$chp_title,$hdr);

$nt=trim($dt{'ge_mouth_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Throat:';
$sub = '';
if($dt{'ge_thrt_norm_exam'} == '1') {
	$nt = 'Tonsils not enlarged, no erythema, no exudate.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('No Erythema: ', $dt{'ge_thrt_ery'},
				 $dt{'ge_thrt_ery_nt'});
		EE1_GenPrintCheck('No Exudate: ', $dt{'ge_thrt_exu'},
				 $dt{'ge_thrt_exu_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Tonsils';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Exudate: ', $dt{'ge_thrt_ton_exu'}, 
					$dt{'ge_thrt_ton_exu_nt'});
		EE1_GenPrintChoice('Enlarged Size: ', $dt{'ge_thrt_ton_en'}, 
					$dt{'ge_thrt_ton_en_nt'}, '1~2~3~4~5', '', 'tonsil_size');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Uvula';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Midline: ', $dt{'ge_thrt_uvu_mid'}, 
					$dt{'ge_thrt_uvu_mid_nt'});
		EE1_GenPrintChoice('Swollen: ', $dt{'ge_thrt_uvu_swell'}, 
					$dt{'ge_thrt_uvu_swell_nt'});
		EE1_GenPrintChoice('Deviated: ', $dt{'ge_thrt_uvu_dev'}, 
					$dt{'ge_thrt_uvu_dev_nt'}, 'l~r', '', 'left_right');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Palate';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Swelling: ', $dt{'ge_thrt_pal_swell'}, 
					$dt{'ge_thrt_pal_swell_nt'});
		EE1_GenPrintChoice('Petechiae: ', $dt{'ge_thrt_pal_pet'}, 
					$dt{'ge_thrt_pal_pet_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$thrt_chks = explode('|', $dt{'ge_thrt_chks'});
$nt = BuildPrintList($thrt_chks);
if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }

$nt=trim($dt{'ge_thrt_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','','Throat:');
	EE1_PrintNote($nt,$chp_title,'Throat:');
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Neck:';
$sub = '';
if($dt{'ge_nk_norm_exam'} == '1') {
	$nt = 'Supple, no bruits, JVP or lymphadenopathy, trachea midline.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Supple: ', $dt{'ge_nk_sup'}, '');
		EE1_GenPrintChoice('Bruit: ', $dt{'ge_nk_brit'}, '');
		EE1_GenPrintChoice('JVP: ', $dt{'ge_nk_jvp'}, '');
		EE1_GenPrintChoice('Lymphadenopathy: ', $dt{'ge_nk_lymph'}, '');
		EE1_GenPrintCheck('Trachea Midline: ', $dt{'ge_nk_trach'}, '');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_nk_brit_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Thyroid:';
$sub = '';
if($dt{'ge_thy_norm_exam'} == '1') {
	$nt = 'Thyroid midline, not enlarged.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$nt = $chc = '';
	if($dt{'ge_thy_norm'}) { 
		$nt = 'Normal Size:';
		$chc = 'Yes';
	}
	EE1_PrintGE($nt, $chc, $dt{'ge_thy_norm_nt'}, $hdr);
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Nodules: ', $dt{'ge_thy_nod'}, 
				$dt{'ge_thy_nod_nt'});
		EE1_GenPrintChoice('Bruit: ', $dt{'ge_thy_brit'}, 
				$dt{'ge_thy_brit_nt'});
		EE1_GenPrintChoice('Tenderness: ', $dt{'ge_thy_tnd'}, 
				$dt{'ge_thy_tnd_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_thy_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Lymphadenopathy:';
$sub = '';
if($dt{'ge_lym_norm_exam'} == '1') {
	$nt = 'No palpable lymph nodes.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Cervical: ', $dt{'ge_lym_cerv'}, 
				$dt{'ge_lym_cerv_nt'});
		EE1_GenPrintChoice('Supraclavicular: ', $dt{'ge_lym_sup'}, 
				$dt{'ge_lym_sup_nt'});
		EE1_GenPrintChoice('Axillary: ', $dt{'ge_lym_ax'}, 
				$dt{'ge_lym_ax_nt'});
		EE1_GenPrintChoice('Inguinal: ', $dt{'ge_lym_in'}, 
				$dt{'ge_lym_in_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_lym_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Breasts:';
$sub = '';
if($dt{'ge_br_norm_exam'} == '1') {
	$nt = 'Symmetric, without abnormalities.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$chc = $dt{'ge_br_sym'};
	$nt = trim($dt{'ge_br_sym_nt'});
	if($chc || $nt) {
		EE1_PrintGE_YN('Symmetrical',$chc,$nt,$hdr);
	}

	$sub = 'Right Breast';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Axillary Nodes: ', $dt{'ge_brr_axil'}, 
				$dt{'ge_brr_axil_nt'});
		EE1_GenPrintChoice('Mass/Lesion: ', $dt{'ge_brr_mass'}, 
				$dt{'ge_brr_mass_nt'});
		EE1_GenPrintChoice('Tanner: ', $dt{'ge_brr_tan'}, 
					$dt{'ge_brr_tan_nt'}, '1~2~3~4~5', '', 'one_to_five');
		EE1_GenPrintChoice('Skin Changes: ', $dt{'ge_brr_chng'}, 
				$dt{'ge_brr_chng_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr, $sub); }
		$cnt++;
	}
	$nt=trim($dt{'ge_brr_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}
	
	$sub_printed=false;
	$sub = 'Right Nipple';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Everted: ', $dt{'ge_nipr_ev'}, 
				$dt{'ge_nipr_ev_nt'});
		EE1_GenPrintChoice('Inverted: ', $dt{'ge_nipr_in'}, 
				$dt{'ge_nipr_in_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ge_nipr_mass'}, 
				$dt{'ge_nipr_mass_nt'});
		EE1_GenPrintChoice('Dischage: ', $dt{'ge_nipr_dis'}, 
				$dt{'ge_nipr_dis_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ge_nipr_ret'}, 
				$dt{'ge_nipr_ret_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
	$nt=trim($dt{'ge_nipr_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}

	$sub_printed = false;
	$sub = 'Left Breast';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Axillary Nodes: ', $dt{'ge_brl_axil'}, 
				$dt{'ge_brl_axil_nt'});
		EE1_GenPrintChoice('Mass/Lesion: ', $dt{'ge_brl_mass'}, 
				$dt{'ge_brl_mass_nt'});
		EE1_GenPrintChoice('Tanner: ', $dt{'ge_brl_tan'}, 
					$dt{'ge_brl_tan_nt'}, '1~2~3~4~5', '', 'one_to_five');
		EE1_GenPrintChoice('Skin Changes: ', $dt{'ge_brl_chng'}, 
				$dt{'ge_brl_chng_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr, $sub); }
		$cnt++;
	}
	$nt=trim($dt{'ge_brl_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}
	
	$sub_printed=false;
	$sub = 'Left Nipple';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Everted: ', $dt{'ge_nipl_ev'}, 
				$dt{'ge_nipl_ev_nt'});
		EE1_GenPrintChoice('Inverted: ', $dt{'ge_nipl_in'}, 
				$dt{'ge_nipl_in_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ge_nipl_mass'}, 
				$dt{'ge_nipl_mass_nt'});
		EE1_GenPrintChoice('Dischage: ', $dt{'ge_nipl_dis'}, 
				$dt{'ge_nipl_dis_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ge_nipl_ret'}, 
				$dt{'ge_nipl_ret_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
	$nt=trim($dt{'ge_nipl_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}
}
	
$hdr_printed=false;
$sub_printed=false;
$hdr = 'Cardiovascular:';
$sub = '';
if($dt{'ge_cr_norm_exam'} == '1') {
	$nt = 'Regular rate and rhythm, no murmurs, gallops or rubs.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Regular Rate &amp; Rhythm: ', $dt{'ge_cr_norm'}, 
				$dt{'ge_cr_norm_nt'});
		$dtl = ListLook($dt{'ge_cr_mur_dtl'}, 'one_to_six');
		if($dtl) $dtl .= '/6';
		if($dtl && $dt{'ge_cr_mur_nt'}) $dtl .= ' - ';
		EE1_GenPrintChoice('Murmur: ', $dt{'ge_cr_mur'}, 
				$dtl.$dt{'ge_cr_mur_nt'});
		EE1_GenPrintChoice('Gallops: ', $dt{'ge_cr_gall'}, 
				$dt{'ge_cr_gall_nt'});
		EE1_GenPrintChoice('Clicks: ', $dt{'ge_cr_click'}, 
				$dt{'ge_cr_click_nt'});
		EE1_GenPrintChoice('Rubs: ', $dt{'ge_cr_rubs'}, 
				$dt{'ge_cr_rubs_nt'});
		EE1_GenPrintChoice('Extra Sound: ', $dt{'ge_cr_extra'}, 
				$dt{'ge_cr_extra_nt'});
		EE1_GenPrintChoice('PMI in 5th ICS in MCL: ', $dt{'ge_cr_pmi'}, 
				$dt{'ge_cr_pmi_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_cr_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}
	
$hdr_printed=false;
$sub_printed=false;
$hdr = 'Pulmonary:';
$sub='';
if($dt{'ge_pul_norm_exam'} == '1') {
	$nt = 'Clear to auscultation bilaterally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clear to Auscultation: ', $dt{'ge_pul_clear'}, '');
		EE1_GenPrintChoice('Rales: ', $dt{'ge_pul_rales'}, '');
		EE1_GenPrintChoice('Wheezes: ', $dt{'ge_pul_whz'}, '');
		EE1_GenPrintChoice('Rhonchi: ', $dt{'ge_pul_ron'}, '');
		EE1_GenPrintChoice('Decreased Breathing Sounds: ',$dt{'ge_pul_dec'},'');
		EE1_GenPrintChoice('Crackles: ',$dt{'ge_pul_crack'},'');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_pul_rales_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Gastrointestinal:';
$sub='';
if($dt{'ge_gi_norm_exam'} == '1') {
	$nt = 'Soft, non-tender, non-distended. NI bowel sounds, no organomegaly or masses.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt= '';
		EE1_GenPrintChoice('Soft: ', $dt{'ge_gi_soft'}, 
					$dt{'ge_gi_soft_nt'});
		$dtl = ListLook($dt{'ge_gi_tend_loc'},'EE1_GI_Location');
		$nt = '';
		if($dt{'ge_gi_tend_nt'}) {
			$nt = $dtl . ' - ' . $dt{'ge_gi_tend_nt'};
		} else {
			$nt = $dtl;
		}
		if($client_id == 'sfa') {
			EE1_GenPrintChoice('Tender: ', $dt{'ge_gi_tend'}, $nt);
		} else {
			EE1_GenPrintChoice('Tender: ', $dt{'ge_gi_tend'}, 
					$nt, 't', 'n', 'EE1_Tender');
		}
		EE1_GenPrintChoice('Distended: ', $dt{'ge_gi_dis'}, 
					$dt{'ge_gi_dis_nt'}, 'd', 'n', 'EE1_Distended');
		EE1_GenPrintChoice('Scar(s): ', $dt{'ge_gi_scar'}, 
					$dt{'ge_gi_scar_nt'});
		EE1_GenPrintChoice('Ascites: ', $dt{'ge_gi_asc'}, 
					$dt{'ge_gi_asc_nt'});
		EE1_GenPrintChoice('Point Tenderness: ', $dt{'ge_gi_pnt'}, 
					$dt{'ge_gi_pnt_nt'});
		EE1_GenPrintChoice('Guarding: ', $dt{'ge_gi_grd'}, 
					$dt{'ge_gi_grd_nt'});
		EE1_GenPrintChoice('Rebound: ', $dt{'ge_gi_reb'}, 
					$dt{'ge_gi_reb_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ge_gi_mass'}, 
					$dt{'ge_gi_mass_nt'});
		$hernia_chks = explode('|', $dt{'ge_gi_her_dtl'});
		$nt = BuildPrintList($hernia_chks);
		if($nt) $nt .= ' - ';
		EE1_GenPrintChoice('Hernia: ', $dt{'ge_gi_hern'}, 
					$nt.$dt{'ge_gi_hern_nt'});
		$dtl = ListLook($dt{'ge_gi_bwl_dtl'},'bowel_detail');
		if($dtl && $dt{'ge_gi_bowel_nt'}) $dtl .= ' - ';
		EE1_GenPrintChoice('Bowel Sounds: ', $dt{'ge_gi_bowel'}, 
					$dtl.$dt{'ge_gi_bowel_nt'});
		EE1_GenPrintChoice('Hepatomegaly: ', $dt{'ge_gi_hepa'}, 
					$dt{'ge_gi_hepa_nt'});
		EE1_GenPrintChoice('Splenomegaly: ', $dt{'ge_gi_spleno'}, 
					$dt{'ge_gi_spleno_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_gi_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Neurological:';
$sub = '';
if($dt{'ge_neu_norm_exam'} == '1') {
	$nt = 'Alert and oriented x 3. CN II - XII intact, reflexes, strength and tone all normal.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt = 4;
	$chk = ''; 
	$chc = ListLook($dt{'ge_neu_ao'},'EE1_AO');
	if(!empty($chc)) { $chk='Alert&nbsp;&amp;&nbsp;Oriented'; }
	EE1_PrintGE($chk,$chc,$dt{'ge_neu_ao_nt'},$hdr);
	$chk = '';
	$chc=$dt{'ge_neu_cn'};
	if(!empty($chc)) { $chk='Cranial Nerves II-XII Grossly Intact'; }
	EE1_PrintGE_YN($chk,$chc,$dt{'ge_neu_cn_nt'},$hdr);

	// For this section print any with commments and build an output of those
	// with a choice but no comment for the end.
	$prnt='';
	$sub = 'DTR\'s';
	$chc=ListLook($dt{'ge_neu_bicr'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_bicr_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Bicep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Bicep: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_bicl'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_bicl_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Bicep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Bicep: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_trir'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_trir_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Tricep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Tricep: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_tril'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_tril_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Tricep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Tricep: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_brar'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_brar_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Brachioradialis',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Brachioradialis: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_bral'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_bral_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Brachioradialis',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Brachioradialis: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_patr'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_patr_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Patella',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Patella: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_patl'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_patl_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Patella',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Patella: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_achr'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_achr_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Achilles',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Achilles: '.$chc); }
	}
	$chc=ListLook($dt{'ge_neu_achl'},'EE1_DTR');
	$nt=trim($dt{'ge_neu_achl_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Achilles',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Achilles: '.$chc); }
	}
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }

	$sub_printed=false;
	$sub = 'Strength';
	$prnt='';
	$chc=ListLook($dt{'ge_neu_pup'},'Zero_to_5');
	$nt = trim($dt{'ge_neu_pup_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Proximal Upper:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Proximal Upper: '.$chc); }
	$chc=ListLook($dt{'ge_neu_plow'},'Zero_to_5');
	$nt = trim($dt{'ge_neu_plow_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Proximal Lower:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Proximal Lower: '.$chc); }
	$chc=ListLook($dt{'ge_neu_dup'},'Zero_to_5');
	$nt = trim($dt{'ge_neu_dup_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Distal Upper:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Distal Upper: '.$chc); }
	$chc=ListLook($dt{'ge_neu_dlow'},'Zero_to_5');
	$nt = trim($dt{'ge_neu_dlow_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Distal Lower:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Distal Lower: '.$chc); }
	$chc=ListLook($dt{'ge_neu_tn'},'neuro_tone');
	$nt = trim($dt{'ge_neu_tn_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Tone:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Tone: '.$chc); }

	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,'Strength'); }

	$sub_printed = false;
	$sub = 'Coordination / Cerebellar';
	if($dt{'ge_neu_cc_norm'}) {
		$nt = 'No Abnormalities';
		EE1_PrintNote($nt, $chp_title, $hdr);
	}
	$cnt=0;
	while($cnt < 5) {
		$prnt= '';
		EE1_GenPrintChoice('Finger / Nose: ', $dt{'ge_neu_cc_fn'}, 
					$dt{'ge_neu_cc_fn_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Heel / Shin: ', $dt{'ge_neu_cc_hs'}, 
					$dt{'ge_neu_cc_hs_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Rapid Alternating: ', $dt{'ge_neu_cc_ra'}, 
					$dt{'ge_neu_cc_ra_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Romberg: ', $dt{'ge_neu_cc_rm'}, 
					$dt{'ge_neu_cc_rm_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Pronator Drift: ', $dt{'ge_neu_cc_pd'}, 
					$dt{'ge_neu_cc_pd_nt'}, 'a', 'n', 'NormAbnorm');
		if($client_id != 'cffm') {
			EE1_GenPrintChoice('Sensation: ', $dt{'ge_neu_sns_chc'}, 
							$dt{'ge_neu_sns_chc_nt'}, 'a', 'n', 'NormAbnorm');
		}
		if(!empty($prnt)) EE1_PrintCompoundGE($prnt,$hdr);
		$cnt++;
	}

	$sub = '';
	$nt = trim($dt{'ge_neu_sense'});
	$lbl = ($client_id != 'cffm') ? 'Notes:' : 'Sensation:';
	$lbl = 'Notes:';
	if(!empty($nt)) {
		EE1_PrintGE($lbl,'','',$hdr);
		EE1_PrintNote($nt,$chp_title,$hdr);
	}
}

$hdr_printed=false;
$hdr = 'Musculoskeletal:';
$sub = '';
if($dt{'ge_ms_norm_exam'} == '1') {
	$nt = 'Spine straight and moves all extremities well and equally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt= '';
		EE1_GenPrintChoice('Intact w/o Atrophy: ', $dt{'ge_ms_intact'}, 
					$dt{'ge_ms_intact_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ge_ms_mass'}, 
					$dt{'ge_ms_mass_nt'});
		EE1_GenPrintChoice('Tenderness: ', $dt{'ge_ms_tnd'}, 
					$dt{'ge_ms_tnd_nt'});
		EE1_GenPrintChoice('Scoliosis: ', $dt{'ge_ms_scl'}, 
					$dt{'ge_ms_scl_nt'});
		EE1_GenPrintChoice('CVA Tenderness on L: ', $dt{'ge_ms_cval'}, 
					$dt{'ge_ms_cval_nt'});
		EE1_GenPrintChoice('CVA Tenderness on R: ', $dt{'ge_ms_cvar'}, 
					$dt{'ge_ms_cvar_nt'});
		EE1_GenPrintChoice('ROM Limited: ', $dt{'ge_ms_lim'}, 
					$dt{'ge_ms_lim_nt'});
		EE1_GenPrintChoice('Deformity: ', $dt{'ge_ms_def'}, 
					$dt{'ge_ms_def_nt'});
		EE1_GenPrintChoice('ROM Full: ', $dt{'ge_ms_full'}, 
					$dt{'ge_ms_full_nt'});
		EE1_GenPrintChoice('Gait: ', $dt{'ge_ms_gait'}, 
					$dt{'ge_ms_gait_nt'}, 'n', 'a', 'NormAbnorm');
		$nt = '';
		if($dt{'ge_ms_norm'}) {
		 $nt = 'Moves all extremities well and equally: ';
		}
		EE1_GenPrintCheck('',$dt{'ge_ms_norm'},$nt);
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$ms_chks = explode('|', $dt{'ge_ms_chks'});
	$nt = BuildPrintList($ms_chks);
	if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }
}

$nt=trim($dt{'ge_ms_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Extremities:';
$sub = '';
if($dt{'ge_ext_norm_exam'} == '1') {
	$nt = 'Well perfused, no clubbing, cyanosis or edema.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$dtl = ListLook($dt{'ge_ext_edema_chc'},'Edema');
	if($dtl && $dt{'ge_ext_edema_nt'}) $dtl .= ' - ';
	$chc = ListLook($dt{'ge_ext_edema'},'Yes_No');
	$prnt = '';
	if($dt{'ge_ext_edema'} != '') { $prnt='Edema'; }
	EE1_PrintGE($prnt,$chc,$dtl.$dt{'ge_ext_edema_nt'},$hdr);
	// Append all the pulses on one print line
	$sub = 'Pulses';
	$prnt = '';
	$chc=ListLook($dt{'ge_ext_pls_rad'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Radial: '.$chc); 
	}
	$chc=ListLook($dt{'ge_ext_pls_dors'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Dosalis Pedis: '.$chc);
	}
	$chc=ListLook($dt{'ge_ext_pls_post'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Posterior Tibial: '.$chc);
	}
	$chc=ListLook($dt{'ge_ext_pls_pop'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Popliteal: '.$chc);
	}
	$chc=ListLook($dt{'ge_ext_pls_fem'},'Zero_to_4');
	if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Femoral: '.$chc); }
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
	
	$sub_printed=false;
	$sub = 'Capillary Refill';
	$chk='';
	$chc=ListLook($dt{'ge_ext_refill'},'Yes_No');
	if(!empty($chc)) { $chk='Less Than 3 Seconds'; }
	EE1_PrintGE('',$chc,$chk,$hdr,$sub);
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clubbing: ', $dt{'ge_ext_club'}, 
					$dt{'ge_ext_club_nt'});
		EE1_GenPrintChoice('Cyanosis: ', $dt{'ge_ext_cyan'}, 
					$dt{'ge_ext_cyan_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_ext_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Diabetic Foot';
$sub = '';
if($dt{'ge_db_norm_exam'} == '1') {
	$nt = 'Normal exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$prnt = '';
	$chc=ListLook($dt{'ge_db_prop'},'NormAbnorm');
	$nt=trim($dt{'ge_db_prop_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Proprioception',$chc,$nt,$hdr);
	} else if($chc) { $prnt=EE1_AppendItem($prnt,'Proprioception: '.$chc); }
	$chc=ListLook($dt{'ge_db_vib'},'NormAbnorm');
	$nt=trim($dt{'ge_db_vib_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Vibration Sense',$chc,$nt,$hdr);
	} else if($chc) { $prnt=EE1_AppendItem($prnt,'Vibration Sense: '.$chc); }
	$chc=ListLook($dt{'ge_db_sens'},'NormAbnorm');
	$nt=trim($dt{'ge_db_sens_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Sensation to Monofilament Testing',$chc,$nt,$hdr);
	} else if($chc) { $prnt=EE1_AppendItem($prnt,'Sensation to Monofilment Testing: '.$chc); }
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
}
$nt=trim($dt{'ge_db_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Testicular:';
$hdr = ($pat_sex == 'm') ? 'Testicular:' : 'Genitalia:';
$sub = '';
if($dt{'ge_te_norm_exam'} == '1') {
	$nt = 'Normal exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$sub = ($pat_sex == 'm') ? 'Penile' : 'Vulva';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Circumcised: ', $dt{'ge_te_cir'}, 
					$dt{'ge_te_cir_nt'});
		EE1_GenPrintChoice('Lesions: ', $dt{'ge_te_les'}, 
					$dt{'ge_te_les_nt'});
		EE1_GenPrintChoice('Discharge: ', $dt{'ge_te_dis'}, 
					$dt{'ge_te_dis_nt'});
		EE1_GenPrintChoice('Testes Size: ', $dt{'ge_te_size'}, 
						$dt{'ge_te_size_nt'}, 's', 'n', 'EE1_Testes_Size');
		EE1_GenPrintChoice('Palpitation: ', $dt{'ge_te_palp'}, 
						$dt{'ge_te_palp_nt'}, 'h~s', '', 'HardSoft');
		EE1_GenPrintChoice('Mass: ', $dt{'ge_te_mass'}, 
					$dt{'ge_te_mass_nt'});
		EE1_GenPrintChoice('Tender: ', $dt{'ge_te_tend'}, 
					$dt{'ge_te_tend_nt'});
		EE1_GenPrintChoice('Erythema: ', $dt{'ge_te_ery'}, 
					$dt{'ge_te_ery_nt'});
		EE1_GenPrintChoice('Labia Majora: ', $dt{'ge_te_lmaj'}, 
						$dt{'ge_te_lmaj_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Labia Minora: ', $dt{'ge_te_lmin'}, 
						$dt{'ge_te_lmin_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Introitus: ', $dt{'ge_te_intro'}, 
						$dt{'ge_te_intro_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Urethra: ', $dt{'ge_te_urethra'}, 
						$dt{'ge_te_urethra_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Clitorus: ', $dt{'ge_te_clit'}, 
						$dt{'ge_te_clit_nt'}, 'a', 'n', 'NormAbnorm');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
}

$nt=trim($dt{'ge_te_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Rectal:';
$sub = '';
if($dt{'ge_rc_norm_exam'} == '1') {
	$nt = 'Normal sphincter tone and remainder of exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$nt = trim($dt{'ge_rc_tone_nt'});
	$chc = ListLook($dt{'ge_rc_tone'}, 'EE1_Tone');
	if($chc && $nt) {
		$nt = $chc.' - '.$nt;
	} else if($chc) {
		$nt = $chc;
	}
	$cnt=0;
	EE1_GenPrintChoice('Tone: ', '', $nt);
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('External Hemorrhoid: ', $dt{'ge_rc_ext'}, 
						$dt{'ge_rc_ext_nt'});
		EE1_GenPrintChoice('Prostate: ', $dt{'ge_rc_pro'}, 
						$dt{'ge_rc_pro_nt'}, 'e', 'n', 'EE1_Prostate');
		EE1_GenPrintChoice('Boggy: ', $dt{'ge_rc_bog'}, 
						$dt{'ge_rc_bog_nt'});
		EE1_GenPrintChoice('Hard: ', $dt{'ge_rc_hard'}, 
						$dt{'ge_rc_hard_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ge_rc_mass'}, 
						$dt{'ge_rc_mass_nt'});
		EE1_GenPrintChoice('Tender: ', $dt{'ge_rc_tend'}, 
						$dt{'ge_rc_tend_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$chk = '';
	$chc = ListLook($dt{'ge_rc_color'},'EE1_Stool_Color');
	if($client_id == 'cffm') {
		if($chc || !empty($dt{'ge_rc_color_nt'})) { $chk='Stool GWIAC'; }
	} else {
		if($chc || !empty($dt{'ge_rc_color_nt'})) { $chk='Stool'; }
	}
	EE1_PrintGE($chk,$chc,$dt{'ge_rc_color_nt'},$hdr);
}

$nt=trim($dt{'ge_rc_nt'});
if(!empty($nt)) {
	EE1_PrintGE_YN('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Skin:';
$sub = '';
if($dt{'ge_skin_norm_exam'} == '1') {
	$nt = 'Skin clear.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Normal Appendages: ',$dt{'ge_skin_app'},
						$dt{'ge_skin_app_nt'});
		EE1_GenPrintCheck('No Suspicious Lesions Noted: ',$dt{'ge_skin_les'},
						$dt{'ge_skin_les_nt'});
		EE1_GenPrintChoice('Veracities: ', $dt{'ge_skin_ver'}, 
						$dt{'ge_skin_ver_nt'});
		EE1_GenPrintChoice('Jaundice: ', $dt{'ge_skin_jau'}, 
						$dt{'ge_skin_jau_nt'});
		EE1_GenPrintChoice('Contusion: ', $dt{'ge_skin_con'}, 
						$dt{'ge_skin_con_nt'});
		EE1_GenPrintChoice('Ecchymosis: ', $dt{'ge_skin_ecc'}, 
						$dt{'ge_skin_ecc_nt'});
		EE1_GenPrintChoice('Rash: ', $dt{'ge_skin_rash'}, 
						$dt{'ge_skin_rash_nt'});
		EE1_GenPrintChoice('Abscess/Cellulitis: ', $dt{'ge_skin_abs'}, 
						$dt{'ge_skin_abs_nt'});
		EE1_GenPrintChoice('Laceration/Abrasion: ', $dt{'ge_skin_lac'}, 
						$dt{'ge_skin_lac_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$nt=trim($dt{'ge_skin_nt'});
if(!empty($nt)) {
	EE1_PrintGE_YN('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Psychiatric:';
$sub = $nt = $chk = '';
if($dt{'ge_psych_norm_exam'} == '1') {
	$nt = 'Assessment of judgement/insight: Appropriate. Orientation to time, place, person: Appropriate. Assessment of memory (recent/remoter): Appropriate. Assessment of mood/affect: Appropriate.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$prnt = '';
	$nt=trim($dt{'ge_psych_judge_nt'});
	if($dt{'ge_psych_judge'} == 1) { $chk='Assessment of Judgement/Insight'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else { 
		$sub = $chk;
		EE1_PrintNote($nt, $chp_title, $hdr, $sub);
	}
	$chk =	'';
	$sub_printed = false;
	$nt=trim($dt{'ge_psych_orient_nt'});
	if($dt{'ge_psych_orient'} == 1) { $chk='Orientation to Time, Place, Person'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else { 
		$sub = $chk;
		EE1_PrintNote($nt, $chp_title, $hdr, $sub);
	}
	$chk='';
	$sub_printed = false;
	$nt=trim($dt{'ge_psych_memory_nt'});
	if($dt{'ge_psych_memory'} == 1) { $chk='Assessment of Memory (Recent/Remoter)'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else {
		$sub = $chk;
		EE1_PrintNote($nt,$chp_title, $hdr, $sub);
	}
	$chk='';
	$sub_printed = false;
	$nt=trim($dt{'ge_psych_mood_nt'});
	if($dt{'ge_psych_mood'} == 1) { $chk='Assessment of Mood/Affect'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else {
		$sub = $chk;
		EE1_PrintNote($nt,$chp_title, $hdr, $sub);
	}
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,'Psychiatric:'); }
}

$nt=trim($dt{'ge_psych_nt'});
if(!empty($nt)) {
	EE1_PrintGE_YN('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

?>
