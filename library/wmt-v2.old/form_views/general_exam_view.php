<?php
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
$nt=trim($dt{'ee1_ge_dictate'});
if(!empty($nt)) {
	EE1_PrintNote($nt,$chp_title,'General Exam Notes:');
}

$hdr_printed=false;
$nt=trim($dt{'ee1_diagnostics_nt'});
if(!empty($nt)) {
	EE1_PrintNote($nt,$chp_title,'Diagnostic Tests:');
}

$hdr_printed=false;
// First Pass, set to '0' is for anything with comments only.
// Second pass will print no choices, then third pass no choices with comments
// fourth pass is yes choices, then last (fifth) pass is yes with comments.
$hdr = 'General:';
if($dt{'ee1_ge_gen_norm_exam'} == '1') {
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
		EE1_GenPrintCheck('Normal Habitus', $dt{'ee1_ge_gen_norm'}, 
			$dt{'ee1_ge_gen_norm_nt'});
		EE1_GenPrintCheck('Well Developed', $dt{'ee1_ge_gen_dev'}, 
			$dt{'ee1_ge_gen_dev_nt'});
		EE1_GenPrintCheck('Well Groomed', $dt{'ee1_ge_gen_groom'}, 
			$dt{'ee1_ge_gen_groom_nt'});
		EE1_GenPrintCheck('No Acute Distress', $dt{'ee1_ge_gen_dis'}, 
			$dt{'ee1_ge_gen_dis_nt'});

		EE1_GenPrintChoice('Jaundice: ', $dt{'ee1_ge_gen_jaun'}, 
				$dt{'ee1_ge_gen_jaun_nt'});
		EE1_GenPrintChoice('Wasting: ', $dt{'ee1_ge_gen_waste'}, 
				$dt{'ee1_ge_gen_waste_nt'});
		EE1_GenPrintChoice('Sleep Pattern: ', $dt{'ee1_ge_gen_sleep'}, 
				$dt{'ee1_ge_gen_sleep_nt'},'a','n', 'NormAbnorm');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$nt=trim($dt{'ee1_ge_gen_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$hdr = 'Head:';
if($dt{'ee1_ge_hd_norm_exam'} == '1') {
	$nt = 'Atraumatic, normocephalic, midline, symmetric.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Atraumatic', $dt{'ee1_ge_hd_atra'}, 
			$dt{'ee1_ge_hd_atra_nt'});
		EE1_GenPrintCheck('Normocephalic', $dt{'ee1_ge_hd_norm'}, 
			$dt{'ee1_ge_hd_norm_nt'});

		EE1_GenPrintChoice('Facial Features: ', $dt{'ee1_ge_hd_feat'}, 
			$dt{'ee1_ge_hd_feat_nt'}, 's', 'd', 'Facial_Features');
		EE1_GenPrintChoice('Anterior Fontanel: ', $dt{'ee1_ge_hd_ant'}, 
			$dt{'ee1_ge_hd_ant_nt'}, 'o~s', '', 'ant_font');

		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$hd_chks = explode('|', $dt{'ee1_ge_hd_chks'});
$nt = BuildPrintList($hd_chks);
if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }

$nt=trim($dt{'ee1_ge_hd_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Eyes:';
$sub = 'Fundoscopic:';
if($dt{'ee1_ge_eye_norm_exam'} == '1') {
	$nt = 'PERRL and EOMI bilaterally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$chk = $prnt = '';
	$chc=ListLook($dt{'ee1_ge_eye_pupil'},'EE1_Pupil');
	if(!empty($chc) || !empty($dt{'ee1_ge_eye_pupil_nt'})) { $chk='Pupils'; }
	EE1_PrintGE($chk,$chc,$dt{'ee1_ge_eye_pupil_nt'},$hdr);
	// Fundoscopic section can be combined
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Hemorrhage: ', $dt{'ee1_ge_eye_hem'}, 
					$dt{'ee1_ge_eye_hem_nt'});
		EE1_GenPrintChoice('Exudate: ', $dt{'ee1_ge_eye_exu'}, 
					$dt{'ee1_ge_eye_exu_nt'});
		EE1_GenPrintChoice('AV Nicking: ', $dt{'ee1_ge_eye_av'}, 
					$dt{'ee1_ge_eye_av_nt'});
		EE1_GenPrintChoice('Papilledema: ', $dt{'ee1_ge_eye_pap'}, 
					$dt{'ee1_ge_eye_pap_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}

	$sub_printed=false;
	$sub = 'Right Eye';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		if($client_id != 'sfa') {
			EE1_GenPrintChoice('Abnormalities: ', $dt{'ee1_ge_eyer_norm'}, 
					$dt{'ee1_ge_eyer_norm_nt'}, 'a', 'n', 'NormAbnorm');
		} else {
			EE1_GenPrintCheck('No Abnormalities', $dt{'ee1_ge_eyer_norm'}, 
				$dt{'ee1_ge_eyer_norm_nt'});
		}
		EE1_GenPrintChoice('Exophthalmos: ', $dt{'ee1_ge_eyer_exo'}, 
					$dt{'ee1_ge_eyer_exo_nt'});
		EE1_GenPrintChoice('Stare: ', $dt{'ee1_ge_eyer_stare'}, 
					$dt{'ee1_ge_eyer_stare_nt'});
		EE1_GenPrintChoice('Lid Lag: ', $dt{'ee1_ge_eyer_lag'}, 
					$dt{'ee1_ge_eyer_lag_nt'});
		EE1_GenPrintCheck('No Scleral Injection', $dt{'ee1_ge_eyer_scleral'}, 
					$dt{'ee1_ge_eyer_scleral_nt'});
		EE1_GenPrintChoice('EOMI: ', $dt{'ee1_ge_eyer_eomi'}, 
					$dt{'ee1_ge_eyer_eomi_nt'});
		EE1_GenPrintChoice('PERRL: ', $dt{'ee1_ge_eyer_perrl'}, 
					$dt{'ee1_ge_eyer_perrl_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
	
	$sub_printed=false;
	$sub = 'Left Eye';
	//	$chc=ListLook($dt{'ee1_ge_eyel_norm'},'NormAbnorm');
	$cnt = 1;
	EE1_GenPrintCheck('No Abnormalities', $dt{'ee1_ge_eyel_norm'}, 
			$dt{'ee1_ge_eyel_norm_nt'});
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		if($client_id != 'sfa') {
			EE1_GenPrintChoice('Abnormalities: ', $dt{'ee1_ge_eyel_norm'}, 
					$dt{'ee1_ge_eyel_norm_nt'}, 'a', 'n', 'NormAbnorm');
		} else {
			EE1_GenPrintCheck('No Abnormalities', $dt{'ee1_ge_eyel_norm'}, 
				$dt{'ee1_ge_eyel_norm_nt'});
		}
		EE1_GenPrintChoice('Exophthalmos: ', $dt{'ee1_ge_eyel_exo'}, 
					$dt{'ee1_ge_eyel_exo_nt'});
		EE1_GenPrintChoice('Stare: ', $dt{'ee1_ge_eyel_stare'}, 
					$dt{'ee1_ge_eyel_stare_nt'});
		EE1_GenPrintChoice('Lid Lag: ', $dt{'ee1_ge_eyel_lag'}, 
					$dt{'ee1_ge_eyel_lag_nt'});
		EE1_GenPrintCheck('No Scleral Injection', $dt{'ee1_ge_eyel_scleral'}, 
					$dt{'ee1_ge_eyel_scleral_nt'});
		EE1_GenPrintChoice('EOMI: ', $dt{'ee1_ge_eyel_eomi'}, 
					$dt{'ee1_ge_eyel_eomi_nt'});
		EE1_GenPrintChoice('PERRL: ', $dt{'ee1_ge_eyel_perrl'}, 
					$dt{'ee1_ge_eyel_perrl_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_eye_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed = false;
$sub_printed = false;
$hdr = 'Ears:';
$sub = 'Right Ear';
if($dt{'ee1_ge_ear_norm_exam'} == '1') {
	$nt = 'Auditory canals and TMs clear bilaterally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$chk = '';
	$cnt = 4;
	if(!empty($dt{'ee1_ge_earr_tym_nt'})) { 
		$chk='Tympanic Membrane';
		EE1_GenPrintCheck($chk, 1, $dt{'ee1_ge_earr_tym_nt'});
	}
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clear: ', $dt{'ee1_ge_earr_clear'}, 
					$dt{'ee1_ge_earr_clear_nt'});
		EE1_GenPrintChoice('Perforation: ', $dt{'ee1_ge_earr_perf'}, 
					$dt{'ee1_ge_earr_perf_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ee1_ge_earr_ret'}, 
					$dt{'ee1_ge_earr_ret_nt'});
		EE1_GenPrintChoice('Bulging: ', $dt{'ee1_ge_earr_bulge'}, 
					$dt{'ee1_ge_earr_bulge_nt'});
		EE1_GenPrintChoice('Drainage: ', $dt{'ee1_ge_earr_pus'}, 
			$dt{'ee1_ge_earr_pus_nt'}, '', 'c~p~b', 'ear_drain');
		EE1_GenPrintChoice('Cerumen: ', $dt{'ee1_ge_earr_ceru'}, 
					$dt{'ee1_ge_earr_ceru_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}

	$sub_printed=false;
	$sub = 'Left Ear';
	$chk = '';
	$cnt = 4;
	if(!empty($dt{'ee1_ge_earl_tym_nt'})) { 
		$chk='Tympanic Membrane';
		EE1_GenPrintCheck($chk, 1, $dt{'ee1_ge_earl_tym_nt'});
	}
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clear: ', $dt{'ee1_ge_earl_clear'}, 
					$dt{'ee1_ge_earl_clear_nt'});
		EE1_GenPrintChoice('Perforation: ', $dt{'ee1_ge_earl_perf'}, 
					$dt{'ee1_ge_earl_perf_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ee1_ge_earl_ret'}, 
					$dt{'ee1_ge_earl_ret_nt'});
		EE1_GenPrintChoice('Bulging: ', $dt{'ee1_ge_earl_bulge'}, 
					$dt{'ee1_ge_earl_bulge_nt'});
		EE1_GenPrintChoice('Drainage: ', $dt{'ee1_ge_earl_pus'}, 
			$dt{'ee1_ge_earl_pus_nt'}, '', 'c~p~b', 'ear_drain');
		EE1_GenPrintChoice('Cerumen: ', $dt{'ee1_ge_earl_ceru'}, 
					$dt{'ee1_ge_earl_ceru_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr, $sub); }
		$cnt++;
	}
}

$nt = trim($dt{'ee1_ge_ear_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Nose:';
$sub = 'Nasal Mucosa';
if($dt{'ee1_ge_nose_norm_exam'} == '1') {
	$nt = 'Patent.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Erythema: ', $dt{'ee1_ge_nose_ery'}, 
					$dt{'ee1_ge_nose_ery_nt'});
		EE1_GenPrintChoice('Swelling: ', $dt{'ee1_ge_nose_swell'}, 
					$dt{'ee1_ge_nose_swell_nt'});
		EE1_GenPrintChoice('Pallor: ', $dt{'ee1_ge_nose_pall'}, 
					$dt{'ee1_ge_nose_pall_nt'});
		EE1_GenPrintChoice('Polyps: ', $dt{'ee1_ge_nose_polps'}, 
					$dt{'ee1_ge_nose_polps_nt'});
		EE1_GenPrintChoice('Septum: ', $dt{'ee1_ge_nose_sept'}, 
					$dt{'ee1_ge_nose_sept_nt'}, 'd', 'm', 'EE1_Septum');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_nose_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Mouth:';
$sub = '';
if($dt{'ee1_ge_mouth_norm_exam'} == '1') {
	$nt = 'Moist mucus membranes and normal exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Moist Mucus Membranes: ', $dt{'ee1_ge_mouth_moist'},
				 $dt{'ee1_ge_mouth_moist_nt'});
		EE1_GenPrintCheck('Clear of Suspicious Lesions: ', $dt{'ee1_ge_mouth_les'},
				 $dt{'ee1_ge_mouth_les_nt'});
		EE1_GenPrintChoice('Dentition: ', $dt{'ee1_ge_mouth_dent'}, 
					$dt{'ee1_ge_mouth_dent_nt'}, 'c~d', 'n', 'EE1_Denture');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Gums';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Reddened: ', $dt{'ee1_ge_mouth_gm_red'}, 
					$dt{'ee1_ge_mouth_gm_red_nt'});
		EE1_GenPrintChoice('Swollen: ', $dt{'ee1_ge_mouth_gm_swell'}, 
					$dt{'ee1_ge_mouth_gm_swell_nt'});
		EE1_GenPrintChoice('Bleeding: ', $dt{'ee1_ge_mouth_gm_bld'}, 
					$dt{'ee1_ge_mouth_gm_bld_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Teeth';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Caries: ', $dt{'ee1_ge_mouth_th_car'}, 
					$dt{'ee1_ge_mouth_th_car_nt'});
		EE1_GenPrintChoice('Poor Dentition: ', 
					strtolower($dt{'ee1_ge_mouth_th_pd'}), $dt{'ee1_ge_mouth_th_pd_nt'});
		EE1_GenPrintChoice('Erupting: ', $dt{'ee1_ge_mouth_th_er'}, 
					$dt{'ee1_ge_mouth_th_er_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$mouth_chks = explode('|', $dt{'ee1_ge_mouth_chks'});
$nt = BuildPrintList($mouth_chks);
if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }

$nt=trim($dt{'ee1_ge_mouth_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Throat:';
$sub = '';
if($dt{'ee1_ge_thrt_norm_exam'} == '1') {
	$nt = 'Tonsils not enlarged, no erythema, no exudate.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('No Erythema: ', $dt{'ee1_ge_thrt_ery'},
				 $dt{'ee1_ge_thrt_ery_nt'});
		EE1_GenPrintCheck('No Exudate: ', $dt{'ee1_ge_thrt_exu'},
				 $dt{'ee1_ge_thrt_exu_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Tonsils';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Exudate: ', $dt{'ee1_ge_thrt_ton_exu'}, 
					$dt{'ee1_ge_thrt_ton_exu_nt'});
		EE1_GenPrintChoice('Enlarged Size: ', $dt{'ee1_ge_thrt_ton_en'}, 
					$dt{'ee1_ge_thrt_ton_en_nt'}, '1~2~3~4~5', '', 'tonsil_size');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Uvula';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Midline: ', $dt{'ee1_ge_thrt_uvu_mid'}, 
					$dt{'ee1_ge_thrt_uvu_mid_nt'});
		EE1_GenPrintChoice('Swollen: ', $dt{'ee1_ge_thrt_uvu_swell'}, 
					$dt{'ee1_ge_thrt_uvu_swell_nt'});
		EE1_GenPrintChoice('Deviated: ', $dt{'ee1_ge_thrt_uvu_dev'}, 
					$dt{'ee1_ge_thrt_uvu_dev_nt'}, 'l~r', '', 'left_right');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$sub_printed = false;
	$sub = 'Palate';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Swelling: ', $dt{'ee1_ge_thrt_pal_swell'}, 
					$dt{'ee1_ge_thrt_pal_swell_nt'});
		EE1_GenPrintChoice('Petechiae: ', $dt{'ee1_ge_thrt_pal_pet'}, 
					$dt{'ee1_ge_thrt_pal_pet_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$thrt_chks = explode('|', $dt{'ee1_ge_thrt_chks'});
$nt = BuildPrintList($thrt_chks);
if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }

$nt=trim($dt{'ee1_ge_thrt_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','','Throat:');
	EE1_PrintNote($nt,$chp_title,'Throat:');
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Neck:';
$sub = '';
if($dt{'ee1_ge_nk_norm_exam'} == '1') {
	$nt = 'Supple, no bruits, JVP or lymphadenopathy, trachea midline.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Supple: ', $dt{'ee1_ge_nk_sup'}, '');
		EE1_GenPrintChoice('Bruit: ', $dt{'ee1_ge_nk_brit'}, '');
		EE1_GenPrintChoice('JVP: ', $dt{'ee1_ge_nk_jvp'}, '');
		EE1_GenPrintChoice('Lymphadenopathy: ', $dt{'ee1_ge_nk_lymph'}, '');
		EE1_GenPrintCheck('Trachea Midline: ', $dt{'ee1_ge_nk_trach'}, '');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_nk_brit_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Thyroid:';
$sub = '';
if($dt{'ee1_ge_thy_norm_exam'} == '1') {
	$nt = 'Thyroid midline, not enlarged.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$nt = $chc = '';
	if($dt{'ee1_ge_thy_norm'}) { 
		$nt = 'Normal Size:';
		$chc = 'Yes';
	}
	EE1_PrintGE($nt, $chc, $dt{'ee1_ge_thy_norm_nt'}, $hdr);
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Nodules: ', $dt{'ee1_ge_thy_nod'}, 
				$dt{'ee1_ge_thy_nod_nt'});
		EE1_GenPrintChoice('Bruit: ', $dt{'ee1_ge_thy_brit'}, 
				$dt{'ee1_ge_thy_brit_nt'});
		EE1_GenPrintChoice('Tenderness: ', $dt{'ee1_ge_thy_tnd'}, 
				$dt{'ee1_ge_thy_tnd_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_thy_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Lymphadenopathy:';
$sub = '';
if($dt{'ee1_ge_lym_norm_exam'} == '1') {
	$nt = 'No palpable lymph nodes.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Cervical: ', $dt{'ee1_ge_lym_cerv'}, 
				$dt{'ee1_ge_lym_cerv_nt'});
		EE1_GenPrintChoice('Supraclavicular: ', $dt{'ee1_ge_lym_sup'}, 
				$dt{'ee1_ge_lym_sup_nt'});
		EE1_GenPrintChoice('Axillary: ', $dt{'ee1_ge_lym_ax'}, 
				$dt{'ee1_ge_lym_ax_nt'});
		EE1_GenPrintChoice('Inguinal: ', $dt{'ee1_ge_lym_in'}, 
				$dt{'ee1_ge_lym_in_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_lym_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Breasts:';
$sub = '';
if($dt{'ee1_ge_br_norm_exam'} == '1') {
	$nt = 'Symmetric, without abnormalities.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$chc = $dt{'ee1_ge_br_sym'};
	$nt = trim($dt{'ee1_ge_br_sym_nt'});
	if($chc || $nt) {
		EE1_PrintGE_YN('Symmetrical',$chc,$nt,$hdr);
	}

	$sub = 'Right Breast';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Axillary Nodes: ', $dt{'ee1_ge_brr_axil'}, 
				$dt{'ee1_ge_brr_axil_nt'});
		EE1_GenPrintChoice('Mass/Lesion: ', $dt{'ee1_ge_brr_mass'}, 
				$dt{'ee1_ge_brr_mass_nt'});
		EE1_GenPrintChoice('Tanner: ', $dt{'ee1_ge_brr_tan'}, 
					$dt{'ee1_ge_brr_tan_nt'}, '1~2~3~4~5', '', 'one_to_five');
		EE1_GenPrintChoice('Skin Changes: ', $dt{'ee1_ge_brr_chng'}, 
				$dt{'ee1_ge_brr_chng_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr, $sub); }
		$cnt++;
	}
	$nt=trim($dt{'ee1_ge_brr_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}
	
	$sub_printed=false;
	$sub = 'Right Nipple';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Everted: ', $dt{'ee1_ge_nipr_ev'}, 
				$dt{'ee1_ge_nipr_ev_nt'});
		EE1_GenPrintChoice('Inverted: ', $dt{'ee1_ge_nipr_in'}, 
				$dt{'ee1_ge_nipr_in_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ee1_ge_nipr_mass'}, 
				$dt{'ee1_ge_nipr_mass_nt'});
		EE1_GenPrintChoice('Dischage: ', $dt{'ee1_ge_nipr_dis'}, 
				$dt{'ee1_ge_nipr_dis_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ee1_ge_nipr_ret'}, 
				$dt{'ee1_ge_nipr_ret_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
	$nt=trim($dt{'ee1_ge_nipr_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}

	$sub = 'Left Breast';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Axillary Nodes: ', $dt{'ee1_ge_brl_axil'}, 
				$dt{'ee1_ge_brl_axil_nt'});
		EE1_GenPrintChoice('Mass/Lesion: ', $dt{'ee1_ge_brl_mass'}, 
				$dt{'ee1_ge_brl_mass_nt'});
		EE1_GenPrintChoice('Tanner: ', $dt{'ee1_ge_brl_tan'}, 
					$dt{'ee1_ge_brl_tan_nt'}, '1~2~3~4~5', '', 'one_to_five');
		EE1_GenPrintChoice('Skin Changes: ', $dt{'ee1_ge_brl_chng'}, 
				$dt{'ee1_ge_brl_chng_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt, $hdr, $sub); }
		$cnt++;
	}
	$nt=trim($dt{'ee1_ge_brl_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}
	
	$sub_printed=false;
	$sub = 'Left Nipple';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Everted: ', $dt{'ee1_ge_nipl_ev'}, 
				$dt{'ee1_ge_nipl_ev_nt'});
		EE1_GenPrintChoice('Inverted: ', $dt{'ee1_ge_nipl_in'}, 
				$dt{'ee1_ge_nipl_in_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ee1_ge_nipl_mass'}, 
				$dt{'ee1_ge_nipl_mass_nt'});
		EE1_GenPrintChoice('Dischage: ', $dt{'ee1_ge_nipl_dis'}, 
				$dt{'ee1_ge_nipl_dis_nt'});
		EE1_GenPrintChoice('Retraction: ', $dt{'ee1_ge_nipl_ret'}, 
				$dt{'ee1_ge_nipl_ret_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
	$nt=trim($dt{'ee1_ge_nipl_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Notes:','','',$hdr,$sub);
		EE1_PrintNote($nt,$chp_title,$hdr,$sub);
	}
}
	
$hdr_printed=false;
$sub_printed=false;
$hdr = 'Cardiovascular:';
$sub = '';
if($dt{'ee1_ge_cr_norm_exam'} == '1') {
	$nt = 'Regular rate and rhythm, no murmurs, gallops or rubs.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Regular Rate &amp; Rhythm: ', $dt{'ee1_ge_cr_norm'}, 
				$dt{'ee1_ge_cr_norm_nt'});
		$dtl = ListLook($dt{'ee1_ge_cr_mur_dtl'}, 'one_to_six');
		if($dtl) $dtl .= '/6';
		if($dtl && $dt{'ee1_ge_cr_mur_nt'}) $dtl .= ' - ';
		EE1_GenPrintChoice('Murmur: ', $dt{'ee1_ge_cr_mur'}, 
				$dtl.$dt{'ee1_ge_cr_mur_nt'});
		EE1_GenPrintChoice('Gallops: ', $dt{'ee1_ge_cr_gall'}, 
				$dt{'ee1_ge_cr_gall_nt'});
		EE1_GenPrintChoice('Clicks: ', $dt{'ee1_ge_cr_click'}, 
				$dt{'ee1_ge_cr_click_nt'});
		EE1_GenPrintChoice('Rubs: ', $dt{'ee1_ge_cr_rubs'}, 
				$dt{'ee1_ge_cr_rubs_nt'});
		EE1_GenPrintChoice('Extra Sound: ', $dt{'ee1_ge_cr_extra'}, 
				$dt{'ee1_ge_cr_extra_nt'});
		EE1_GenPrintChoice('PMI in 5th ICS in MCL: ', $dt{'ee1_ge_cr_pmi'}, 
				$dt{'ee1_ge_cr_pmi_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_cr_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}
	
$hdr_printed=false;
$sub_printed=false;
$hdr = 'Pulmonary:';
$sub='';
if($dt{'ee1_ge_pul_norm_exam'} == '1') {
	$nt = 'Clear to auscultation bilaterally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clear to Auscultation: ', $dt{'ee1_ge_pul_clear'}, '');
		EE1_GenPrintChoice('Rales: ', $dt{'ee1_ge_pul_rales'}, '');
		EE1_GenPrintChoice('Wheezes: ', $dt{'ee1_ge_pul_whz'}, '');
		EE1_GenPrintChoice('Wheezes: ', $dt{'ee1_ge_pul_ron'}, '');
		EE1_GenPrintChoice('Decreased Breathing Sounds: ',$dt{'ee1_ge_pul_ron'},'');
		EE1_GenPrintChoice('Crackles: ',$dt{'ee1_ge_pul_crack'},'');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_pul_rales_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Gastrointestinal:';
$sub='';
if($dt{'ee1_ge_gi_norm_exam'} == '1') {
	$nt = 'Soft, non-tender, non-distended. NI bowel sounds, no organomegaly or masses.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt= '';
		EE1_GenPrintChoice('Soft: ', $dt{'ee1_ge_gi_soft'}, 
					$dt{'ee1_ge_gi_soft_nt'});
		$dtl = ListLook($dt{'ee1_ge_gi_tend_loc'},'EE1_GI_Location');
		if($dtl && $dt{'ee1_ge_gi_tend_nt'}) $dtl .= ' - ';
		EE1_GenPrintChoice('Tender: ', $dt{'ee1_ge_gi_tend'}.$dtl, 
					$dt{'ee1_ge_gi_tend_nt'});
		EE1_GenPrintChoice('Distended: ', $dt{'ee1_ge_gi_dis'}, 
					$dt{'ee1_ge_gi_dis_nt'}, 'd', 'n', 'EE1_Distended');
		EE1_GenPrintChoice('Scar(s): ', $dt{'ee1_ge_gi_scar'}, 
					$dt{'ee1_ge_gi_scar_nt'});
		EE1_GenPrintChoice('Ascites: ', $dt{'ee1_ge_gi_asc'}, 
					$dt{'ee1_ge_gi_asc_nt'});
		EE1_GenPrintChoice('Point Tenderness: ', $dt{'ee1_ge_gi_pnt'}, 
					$dt{'ee1_ge_gi_pnt_nt'});
		EE1_GenPrintChoice('Guarding: ', $dt{'ee1_ge_gi_grd'}, 
					$dt{'ee1_ge_gi_grd_nt'});
		EE1_GenPrintChoice('Rebound: ', $dt{'ee1_ge_gi_reb'}, 
					$dt{'ee1_ge_gi_reb_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ee1_ge_gi_mass'}, 
					$dt{'ee1_ge_gi_mass_nt'});
		$hernia_chks = explode('|', $dt{'ee1_ge_gi_her_dtl'});
		$nt = BuildPrintList($hernia_chks);
		if($nt) $nt .= ' - ';
		EE1_GenPrintChoice('Hernia: ', $dt{'ee1_ge_gi_hern'}, 
					$nt.$dt{'ee1_ge_gi_hern_nt'});
		$dtl = ListLook($dt{'ee1_ge_gi_bwl_dtl'},'bowel_detail');
		if($dtl && $dt{'ee1_ge_gi_bowel_nt'}) $dtl .= ' - ';
		EE1_GenPrintChoice('Bowel Sounds: ', $dt{'ee1_ge_gi_bowel'}, 
					$dtl.$dt{'ee1_ge_gi_bowel_nt'});
		EE1_GenPrintChoice('Hepatomegaly: ', $dt{'ee1_ge_gi_hepa'}, 
					$dt{'ee1_ge_gi_hepa_nt'});
		EE1_GenPrintChoice('Splenomegaly: ', $dt{'ee1_ge_gi_spleno'}, 
					$dt{'ee1_ge_gi_spleno_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_gi_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Neurological:';
$sub = '';
if($dt{'ee1_ge_neu_norm_exam'} == '1') {
	$nt = 'Alert and oriented x 3. CN II - XII intact, reflexes, strength and tone all normal.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt = 4;
	$chk = ''; 
	$chc = ListLook($dt{'ee1_ge_neu_ao'},'EE1_AO');
	if(!empty($chc)) { $chk='Alert&nbsp;&amp;&nbsp;Oriented'; }
	EE1_PrintGE($chk,$chc,$dt{'ee1_ge_neu_ao_nt'},$hdr);
	$chk = '';
	$chc=$dt{'ee1_ge_neu_cn'};
	if(!empty($chc)) { $chk='Cranial Nerves II-XII Grossly Intact'; }
	EE1_PrintGE_YN($chk,$chc,$dt{'ee1_ge_neu_cn_nt'},$hdr);

	// For this section print any with commments and build an output of those
	// with a choice but no comment for the end.
	$prnt='';
	$sub = 'DTR\'s';
	$chc=ListLook($dt{'ee1_ge_neu_bicr'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_bicr_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Bicep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Bicep: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_bicl'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_bicl_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Bicep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Bicep: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_trir'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_trir_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Tricep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Tricep: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_tril'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_tril_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Tricep',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Tricep: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_brar'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_brar_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Brachioradialis',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Brachioradialis: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_bral'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_bral_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Brachioradialis',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Brachioradialis: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_patr'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_patr_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Patella',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Patella: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_patl'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_patl_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Patella',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Patella: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_achr'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_achr_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Right Achilles',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Right Achilles: '.$chc); }
	}
	$chc=ListLook($dt{'ee1_ge_neu_achl'},'EE1_DTR');
	$nt=trim($dt{'ee1_ge_neu_achl_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Left Achilles',$chc,$nt,$hdr,$sub);
	} else {
		if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Left Achilles: '.$chc); }
	}
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }

	$sub_printed=false;
	$sub = 'Strength';
	$prnt='';
	$chc=ListLook($dt{'ee1_ge_neu_pup'},'Zero_to_5');
	$nt = trim($dt{'ee1_ge_neu_pup_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Proximal Upper:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Proximal Upper: '.$chc); }
	$chc=ListLook($dt{'ee1_ge_neu_plow'},'Zero_to_5');
	$nt = trim($dt{'ee1_ge_neu_plow_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Proximal Lower:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Proximal Lower: '.$chc); }
	$chc=ListLook($dt{'ee1_ge_neu_dup'},'Zero_to_5');
	$nt = trim($dt{'ee1_ge_neu_dup_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Distal Upper:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Distal Upper: '.$chc); }
	$chc=ListLook($dt{'ee1_ge_neu_dlow'},'Zero_to_5');
	$nt = trim($dt{'ee1_ge_neu_dlow_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Distal Lower:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Distal Lower: '.$chc); }
	$chc=ListLook($dt{'ee1_ge_neu_tn'},'neuro_tone');
	$nt = trim($dt{'ee1_ge_neu_tn_nt'});
	if(!empty($nt)) { 
		EE1_PrintGE('Tone:',$chc,$nt,$hdr,$sub);
	} else if($chc != '') { $prnt=EE1_AppendItem($prnt,'Tone: '.$chc); }

	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,'Strength'); }

	$sub_printed = false;
	$sub = 'Coordination / Cerebellar';
	if($dt{'ee1_ge_neu_cc_norm'}) {
		$nt = 'No Abnormalities';
		EE1_PrintNote($nt, $chp_title, $hdr);
	} else {
		$cnt=0;
		while($cnt < 5) {
			$prnt= '';
			EE1_GenPrintChoice('Finger / Nose: ', $dt{'ee1_ge_neu_cc_fn'}, 
						$dt{'ee1_ge_neu_cc_fn_nt'}, 'a', 'n', 'NormAbnorm');
			EE1_GenPrintChoice('Heel / Shin: ', $dt{'ee1_ge_neu_cc_hs'}, 
						$dt{'ee1_ge_neu_cc_hs_nt'}, 'a', 'n', 'NormAbnorm');
			EE1_GenPrintChoice('Rapid Alternating: ', $dt{'ee1_ge_neu_cc_ra'}, 
						$dt{'ee1_ge_neu_cc_ra_nt'}, 'a', 'n', 'NormAbnorm');
			EE1_GenPrintChoice('Romberg: ', $dt{'ee1_ge_neu_cc_rm'}, 
						$dt{'ee1_ge_neu_cc_rm_nt'}, 'a', 'n', 'NormAbnorm');
			EE1_GenPrintChoice('Pronator Drift: ', $dt{'ee1_ge_neu_cc_pd'}, 
						$dt{'ee1_ge_neu_cc_pd_nt'}, 'a', 'n', 'NormAbnorm');
			if($client_id != 'cffm') {
				EE1_GenPrintChoice('Sensation: ', $dt{'ee1_ge_neu_sns_chc'}, 
								$dt{'ee1_ge_neu_sns_chc_nt'}, 'a', 'n', 'NormAbnorm');
			}
			if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
			$cnt++;
		}
	}

	$sub = '';
	$nt=trim($dt{'ee1_ge_neu_sense'});
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
if($dt{'ee1_ge_ms_norm_exam'} == '1') {
	$nt = 'Spine straight and moves all extremities well and equally.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt= '';
		EE1_GenPrintChoice('Intact w/o Atrophy: ', $dt{'ee1_ge_ms_intact'}, 
					$dt{'ee1_ge_ms_intact_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ee1_ge_ms_mass'}, 
					$dt{'ee1_ge_ms_mass_nt'});
		EE1_GenPrintChoice('Tenderness: ', $dt{'ee1_ge_ms_tnd'}, 
					$dt{'ee1_ge_ms_tnd_nt'});
		EE1_GenPrintChoice('Scoliosis: ', $dt{'ee1_ge_ms_scl'}, 
					$dt{'ee1_ge_ms_scl_nt'});
		EE1_GenPrintChoice('CVA Tenderness on L: ', $dt{'ee1_ge_ms_cval'}, 
					$dt{'ee1_ge_ms_cval_nt'});
		EE1_GenPrintChoice('CVA Tenderness on R: ', $dt{'ee1_ge_ms_cvar'}, 
					$dt{'ee1_ge_ms_cvar_nt'});
		EE1_GenPrintChoice('ROM Limited: ', $dt{'ee1_ge_ms_lim'}, 
					$dt{'ee1_ge_ms_lim_nt'});
		EE1_GenPrintChoice('Deformity: ', $dt{'ee1_ge_ms_def'}, 
					$dt{'ee1_ge_ms_def_nt'});
		EE1_GenPrintChoice('ROM Full: ', $dt{'ee1_ge_ms_full'}, 
					$dt{'ee1_ge_ms_full_nt'});
		EE1_GenPrintChoice('Gait: ', $dt{'ee1_ge_ms_gait'}, 
					$dt{'ee1_ge_ms_gait_nt'}, 'n', 'a', 'NormAbnorm');
		$nt = '';
		if($dt{'ee1_ge_ms_norm'}) {
		 $nt = 'Moves all extremities well and equally: ';
		}
		EE1_GenPrintCheck('',$dt{'ee1_ge_ms_norm'},$nt);
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$ms_chks = explode('|', $dt{'ee1_ge_ms_chks'});
	$nt = BuildPrintList($ms_chks);
	if($nt) { EE1_PrintNote($nt,$chp_title,$hdr); }
}

$nt=trim($dt{'ee1_ge_ms_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Extremities:';
$sub = '';
if($dt{'ee1_ge_ext_norm_exam'} == '1') {
	$nt = 'Well perfused, no clubbing, cyanosis or edema.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$dtl = ListLook($dt{'ee1_ge_ext_edema_chc'},'Edema');
	if($dtl && $dt{'ee1_ge_ext_edema_nt'}) $dtl .= ' - ';
	$chc = ListLook($dt{'ee1_ge_ext_edema'},'Yes_No');
	$prnt = '';
	if($dt{'ee1_ge_ext_edema'} != '') { $prnt='Edema'; }
	EE1_PrintGE($prnt,$chc,$dtl.$dt{'ee1_ge_ext_edema_nt'},$hdr);
	// Append all the pulses on one print line
	$sub = 'Pulses';
	$prnt = '';
	$chc=ListLook($dt{'ee1_ge_ext_pls_rad'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Radial: '.$chc); 
	}
	$chc=ListLook($dt{'ee1_ge_ext_pls_dors'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Dosalis Pedis: '.$chc);
	}
	$chc=ListLook($dt{'ee1_ge_ext_pls_post'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Posterior Tibial: '.$chc);
	}
	$chc=ListLook($dt{'ee1_ge_ext_pls_pop'},'Zero_to_4');
	if($chc != '') { 
		$prnt=EE1_AppendItem($prnt,'Popliteal: '.$chc);
	}
	$chc=ListLook($dt{'ee1_ge_ext_pls_fem'},'Zero_to_4');
	if(!empty($chc)) { $prnt=EE1_AppendItem($prnt,'Femoral: '.$chc); }
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
	
	$sub_printed=false;
	$sub = 'Capillary Refill';
	$chk='';
	$chc=ListLook($dt{'ee1_ge_ext_refill'},'Yes_No');
	if(!empty($chc)) { $chk='Less Than 3 Seconds'; }
	EE1_PrintGE('',$chc,$chk,$hdr,$sub);
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Clubbing: ', $dt{'ee1_ge_ext_club'}, 
					$dt{'ee1_ge_ext_club_nt'});
		EE1_GenPrintChoice('Cyanosis: ', $dt{'ee1_ge_ext_cyan'}, 
					$dt{'ee1_ge_ext_cyan_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_ext_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Diabetic Foot';
$sub = '';
if($dt{'ee1_ge_db_norm_exam'} == '1') {
	$nt = 'Normal exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$prnt = '';
	$chc=ListLook($dt{'ee1_ge_db_prop'},'NormAbnorm');
	$nt=trim($dt{'ee1_ge_db_prop_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Proprioception',$chc,$nt,$hdr);
	} else if($chc) { $prnt=EE1_AppendItem($prnt,'Proprioception: '.$chc); }
	$chc=ListLook($dt{'ee1_ge_db_vib'},'NormAbnorm');
	$nt=trim($dt{'ee1_ge_db_vib_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Vibration Sense',$chc,$nt,$hdr);
	} else if($chc) { $prnt=EE1_AppendItem($prnt,'Vibration Sense: '.$chc); }
	$chc=ListLook($dt{'ee1_ge_db_sens'},'NormAbnorm');
	$nt=trim($dt{'ee1_ge_db_sens_nt'});
	if(!empty($nt)) {
		EE1_PrintGE('Sensation to Monofilament Testing',$chc,$nt,$hdr);
	} else if($chc) { $prnt=EE1_AppendItem($prnt,'Sensation to Monofilment Testing: '.$chc); }
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
}
$nt=trim($dt{'ee1_ge_db_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Testicular:';
$hdr = ($pat_sex == 'm') ? 'Testicular:' : 'Genitalia:';
$sub = '';
if($dt{'ee1_ge_te_norm_exam'} == '1') {
	$nt = 'Normal exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$sub = ($pat_sex == 'm') ? 'Penile' : 'Vulva';
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('Circumcised: ', $dt{'ee1_ge_te_cir'}, 
					$dt{'ee1_ge_te_cir_nt'});
		EE1_GenPrintChoice('Lesions: ', $dt{'ee1_ge_te_les'}, 
					$dt{'ee1_ge_te_les_nt'});
		EE1_GenPrintChoice('Discharge: ', $dt{'ee1_ge_te_dis'}, 
					$dt{'ee1_ge_te_dis_nt'});
		EE1_GenPrintChoice('Testes Size: ', $dt{'ee1_ge_te_size'}, 
						$dt{'ee1_ge_te_size_nt'}, 's', 'n', 'EE1_Testes_Size');
		EE1_GenPrintChoice('Palpitation: ', $dt{'ee1_ge_te_palp'}, 
						$dt{'ee1_ge_te_palp_nt'}, 'h~s', '', 'HardSoft');
		EE1_GenPrintChoice('Mass: ', $dt{'ee1_ge_te_mass'}, 
					$dt{'ee1_ge_te_mass_nt'});
		EE1_GenPrintChoice('Tender: ', $dt{'ee1_ge_te_tend'}, 
					$dt{'ee1_ge_te_tend_nt'});
		EE1_GenPrintChoice('Erythema: ', $dt{'ee1_ge_te_ery'}, 
					$dt{'ee1_ge_te_ery_nt'});
		EE1_GenPrintChoice('Labia Majora: ', $dt{'ee1_ge_te_lmaj'}, 
						$dt{'ee1_ge_te_lmaj_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Labia Minora: ', $dt{'ee1_ge_te_lmin'}, 
						$dt{'ee1_ge_te_lmin_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Introitus: ', $dt{'ee1_ge_te_intro'}, 
						$dt{'ee1_ge_te_intro_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Urethra: ', $dt{'ee1_ge_te_urethra'}, 
						$dt{'ee1_ge_te_urethra_nt'}, 'a', 'n', 'NormAbnorm');
		EE1_GenPrintChoice('Clitorus: ', $dt{'ee1_ge_te_clit'}, 
						$dt{'ee1_ge_te_clit_nt'}, 'a', 'n', 'NormAbnorm');
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr,$sub); }
		$cnt++;
	}
}

$nt=trim($dt{'ee1_ge_te_nt'});
if(!empty($nt)) {
	EE1_PrintGE('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Rectal:';
$sub = '';
if($dt{'ee1_ge_rc_norm_exam'} == '1') {
	$nt = 'Normal sphincter tone and remainder of exam.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$nt = trim($dt{'ee1_ge_rc_tone_nt'});
	$chc = ListLook($dt{'ee1_ge_rc_tone'}, 'EE1_Tone');
	if($chc && $nt) {
		$nt = $chc.' - '.$nt;
	} else if($chc) {
		$nt = $chc;
	}
	$cnt=0;
	EE1_GenPrintChoice('Tone: ', '', $nt);
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintChoice('External Hemorrhoid: ', $dt{'ee1_ge_rc_ext'}, 
						$dt{'ee1_ge_rc_ext_nt'});
		EE1_GenPrintChoice('Prostate: ', $dt{'ee1_ge_rc_pro'}, 
						$dt{'ee1_ge_rc_pro_nt'}, 'e', 'n', 'EE1_Prostate');
		EE1_GenPrintChoice('Boggy: ', $dt{'ee1_ge_rc_bog'}, 
						$dt{'ee1_ge_rc_bog_nt'});
		EE1_GenPrintChoice('Hard: ', $dt{'ee1_ge_rc_hard'}, 
						$dt{'ee1_ge_rc_hard_nt'});
		EE1_GenPrintChoice('Mass: ', $dt{'ee1_ge_rc_mass'}, 
						$dt{'ee1_ge_rc_mass_nt'});
		EE1_GenPrintChoice('Tender: ', $dt{'ee1_ge_rc_tend'}, 
						$dt{'ee1_ge_rc_tend_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
	$chk = '';
	$chc = ListLook($dt{'ee1_ge_rc_color'},'EE1_Stool_Color');
	if($client_id == 'cffm') {
		if($chc || !empty($dt{'ee1_ge_rc_color_nt'})) { $chk='Stool GWIAC'; }
	} else {
		if($chc || !empty($dt{'ee1_ge_rc_color_nt'})) { $chk='Stool'; }
	}
	EE1_PrintGE($chk,$chc,$dt{'ee1_ge_rc_color_nt'},$hdr);
}

$nt=trim($dt{'ee1_ge_rc_nt'});
if(!empty($nt)) {
	EE1_PrintGE_YN('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Skin:';
$sub = '';
if($dt{'ee1_ge_skin_norm_exam'} == '1') {
	$nt = 'Skin clear.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$cnt=0;
	while($cnt < 5) {
		$prnt = '';
		EE1_GenPrintCheck('Normal Appendages: ',$dt{'ee1_ge_skin_app'},
						$dt{'ee1_ge_skin_app_nt'});
		EE1_GenPrintCheck('No Suspicious Lesions Noted: ',$dt{'ee1_ge_skin_les'},
						$dt{'ee1_ge_skin_les_nt'});
		EE1_GenPrintChoice('Veracities: ', $dt{'ee1_ge_skin_ver'}, 
						$dt{'ee1_ge_skin_ver_nt'});
		EE1_GenPrintChoice('Jaundice: ', $dt{'ee1_ge_skin_jau'}, 
						$dt{'ee1_ge_skin_jau_nt'});
		EE1_GenPrintChoice('Contusion: ', $dt{'ee1_ge_skin_con'}, 
						$dt{'ee1_ge_skin_con_nt'});
		EE1_GenPrintChoice('Ecchymosis: ', $dt{'ee1_ge_skin_ecc'}, 
						$dt{'ee1_ge_skin_ecc_nt'});
		EE1_GenPrintChoice('Rash: ', $dt{'ee1_ge_skin_rash'}, 
						$dt{'ee1_ge_skin_rash_nt'});
		EE1_GenPrintChoice('Abscess/Cellulitis: ', $dt{'ee1_ge_skin_abs'}, 
						$dt{'ee1_ge_skin_abs_nt'});
		EE1_GenPrintChoice('Laceration/Abrasion: ', $dt{'ee1_ge_skin_lac'}, 
						$dt{'ee1_ge_skin_lac_nt'});
		if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,$hdr); }
		$cnt++;
	}
}
$nt=trim($dt{'ee1_ge_skin_nt'});
if(!empty($nt)) {
	EE1_PrintGE_YN('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

$hdr_printed=false;
$sub_printed=false;
$hdr = 'Psychiatric:';
$sub = $nt = $chk = '';
if($dt{'ee1_ge_psych_norm_exam'} == '1') {
	$nt = 'Assessment of judgement/insight: Appropriate. Orientation to time, place, person: Appropriate. Assessment of memory (recent/remoter): Appropriate. Assessment of mood/affect: Appropriate.';
	EE1_PrintNote($nt, $chp_title, $hdr);
} else {
	$prnt = '';
	$nt=trim($dt{'ee1_ge_psych_judge_nt'});
	if($dt{'ee1_ge_psych_judge'} == 1) { $chk='Assessment of Judgement/Insight'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else { 
		$sub = $chk;
		EE1_PrintNote($nt, $chp_title, $hdr, $sub);
	}
	$chk =	'';
	$sub_printed = false;
	$nt=trim($dt{'ee1_ge_psych_orient_nt'});
	if($dt{'ee1_ge_psych_orient'} == 1) { $chk='Orientation to Time, Place, Person'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else { 
		$sub = $chk;
		EE1_PrintNote($nt, $chp_title, $hdr, $sub);
	}
	$chk='';
	$sub_printed = false;
	$nt=trim($dt{'ee1_ge_psych_memory_nt'});
	if($dt{'ee1_ge_psych_memory'} == 1) { $chk='Assessment of Memory (Recent/Remoter)'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else {
		$sub = $chk;
		EE1_PrintNote($nt,$chp_title, $hdr, $sub);
	}
	$chk='';
	$sub_printed = false;
	$nt=trim($dt{'ee1_ge_psych_mood_nt'});
	if($dt{'ee1_ge_psych_mood'} == 1) { $chk='Assessment of Mood/Affect'; }
	if(empty($nt)) {
		$prnt=EE1_AppendItem($prnt,$chk);
	} else {
		$sub = $chk;
		EE1_PrintNote($nt,$chp_title, $hdr, $sub);
	}
	if(!empty($prnt)) { EE1_PrintCompoundGE($prnt,'Psychiatric:'); }
}

$nt=trim($dt{'ee1_ge_psych_nt'});
if(!empty($nt)) {
	EE1_PrintGE_YN('Notes:','','',$hdr);
	EE1_PrintNote($nt,$chp_title,$hdr);
}

?>
