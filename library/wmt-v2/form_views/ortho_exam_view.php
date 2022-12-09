<?php 
if(!isset($frmdir)) $frmdir = '';
if(!isset($encounter)) $encounter = '';
if(!isset($field_prefix)) $field_prefix = '';
$data_exists = FALSE;

if($frmdir == 'ortho_exam') {
	// WAS CALLED AS A STAND-ALONE
	$sql = 'SELECT * FROM form_ortho_exam WHERE id = ? AND pid = ?';
	$binds = array($id, $pid);
} else {
	// GET ENCOUNTER LEVEL FORM
	$sql = 'SELECT * FROM form_ortho_exam WHERE link_id = ? AND link_form = ? '.
		'AND pid = ?';
	$binds = array($encounter, 'encounter', $pid);
}
$oe_sections = array('ge', 'post', 'neu', 'orth', 'palp', 
	'rom', 'msc', 'tnd', 'myo');
unset($section_data_exists);
$section_data_exists = array();
foreach($oe_sections as $s) {
	$section_data_exists[$s] = FALSE;
}

$oe = sqlQuery($sql, $binds);
$slice = 8;
if($frmdir != 'ortho_exam') $slice = 14;
if($oe) {
	$oe = array_slice($oe, $slice);
	foreach($oe as $key => $val) {
		if(is_string($val)) $val = trim($val);
		$dt[$field_prefix . $key] = $val;
		if($val && $val != '0') {
			$tmp = explode('_', $key);
			$section_data_exists[$tmp[0]] = TRUE;
			$data_exists = TRUE;
		}
	}

	$multi_labels = array('post_api', 'neu_sense', 'orth_cerv', 'orth_lum', 
		'orth_sac', 'orth_hip', 'orth_shou', 'orth_elbow', 'orth_wrist', 
		'orth_knee', 'orth_ankle', 'msc_derm', 'msc_neck', 'msc_scm', 'msc_inter', 
		'msc_fing_ex', 'msc_wrist_ex', 'msc_tri', 'msc_cuff', 'msc_delt', 
		'msc_lat', 'msc_fing_fl', 'msc_wrist_fl', 'msc_bi', 'msc_hip', 'msc_pec', 
		'msc_psoas', 'msc_tfl', 'msc_glut_med', 'msc_quad', 'msc_glut_max', 
		'msc_ham', 'msc_tib', 'msc_per', 'msc_ext', 'msc_gastroc', 'tnd_pat', 
		'tnd_ham', 'tnd_ach', 'tnd_bi', 'tnd_tri', 'tnd_rad');

	foreach($multi_labels as $lbl) {
		if(isset($dt[$field_prefix . $lbl])) {
			$dt[$field_prefix . $lbl] = explode('^|', $dt[$field_prefix . $lbl]);
		}
	}
}
if($data_exists) {
	$chp_printed = PrintChapter('Orthopedic Examination');
	$nt = $dt[$field_prefix . 'dictate'];
	PrintOverhead('General Notes:', $nt);
	if($nt) echo "<tr><td>&nbsp;</td></tr>\n";

	if($section_data_exists['ge']) {
		$hdr_printed = PrintHeader('General Appearance', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'Distress:', ListLook($dt['oe_ge_distress'],'Distress',''));
		$out = BuildPrintLine($out, 'Station:', ListLook($dt['oe_ge_station'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Gait:', ListLook($dt['oe_ge_gait'],'NormAbnorm',''));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('General Appearance Notes:', $dt['oe_ge_nt'], 3, '', TRUE);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['post']) {
		$hdr_printed = PrintHeader('Posture', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'Cervical Rotation:',ListLook($dt['oe_post_cr'],'left_right',''));
		$out = BuildPrintLine($out, 'Cervical Shift:',ListLook($dt['oe_post_cs'],'left_right',''));
		$out = BuildPrintLine($out, 'Cervical Tilt:',ListLook($dt['oe_post_ct'],'left_right',''));
		$out = BuildPrintLine($out, 'Elevated Shoulder on the:',ListLook($dt['oe_post_es'],'left_right',''));
		$out = BuildPrintLine($out, 'Antalgic Lean:',ListLook($dt['oe_post_al'],'left_right',''));
		$out = BuildPrintLine($out, 'Elevated Hip on the:',ListLook($dt['oe_post_eh'],'left_right',''));
		$out = BuildPrintLine($out, 'Abnormal Posture Indicators:',MultPrint($dt['oe_post_api'],'NormAbnormPosture'));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Posture Notes:', $dt['oe_post_nt'], 3, '', TRUE);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['neu']) {
		$hdr_printed = PrintHeader('Neurological', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'CN 2 - 12:',ListLook($dt['oe_neu_cn_2_12'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Sharp Sensation:',MultPrint($dt['oe_neu_sense'],'LRSpine'));
		$out = BuildPrintLine($out, 'Lower Ext. Touch Sensation:',ListLook($dt['oe_neu_low'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Upper Ext. Touch Sensation:',ListLook($dt['oe_neu_up'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Proprioception:',ListLook($dt['oe_neu_prop'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Alertness:',ListLook($dt['oe_neu_alert'],'YesNo',''));
		$out = BuildPrintLine($out, 'Attention Span - Concentration:',ListLook($dt['oe_neu_attn'],'YesNo',''));
		$out = BuildPrintLine($out, 'Fundamental Knowledge:',ListLook($dt['oe_neu_fund'],'YesNo',''));
		$out = BuildPrintLine($out, 'Language:',ListLook($dt['oe_neu_lang'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Coordination (finger/nose):',ListLook($dt['oe_neu_coor_f'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Coordination (heel/shin):',ListLook($dt['oe_neu_coor_h'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Memory:',ListLook($dt['oe_neu_mem'],'NormAbnorm',''));
		$out = BuildPrintLine($out, 'Muscle Atrophy:',ListLook($dt['oe_neu_atr'],'Abs_Pres',''));
		$out = BuildPrintLine($out, 'Orientation of Time, Place...',ListLook($dt['oe_neu_orient'],'YesNo',''));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Neurological Notes:', $dt['oe_neu_nt'], 3, '', TRUE);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['orth']) {
		$hdr_printed = PrintHeader('Orthopedic Tests', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'Cervical:',MultPrint($dt['oe_orth_cerv'],'OrthCervChc'));
		$out = BuildPrintLine($out, 'Lumbar:',MultPrint($dt['oe_orth_lum'],'OrthLumChc'));
		$out = BuildPrintLine($out, 'Sacrum:',MultPrint($dt['oe_orth_sac'],'OrthSacChc'));
		$out = BuildPrintLine($out, 'Hip:',MultPrint($dt['oe_orth_hip'],'OrthHipChc'));
		$out = BuildPrintLine($out, 'Shoulder:',MultPrint($dt['oe_orth_shou'],'OrthShChc'));
		$out = BuildPrintLine($out, 'Elbow:',MultPrint($dt['oe_orth_elbow'],'OrthElbChc'));
		$out = BuildPrintLine($out, 'Wrist:',MultPrint($dt['oe_orth_wrist'],'OrthWristChc'));
		$out = BuildPrintLine($out, 'Knee:',MultPrint($dt['oe_orth_knee'],'OrthKneeChc'));
		$out = BuildPrintLine($out, 'Ankle:',MultPrint($dt['oe_orth_ankle'],'OrthAnkChc'));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Orthopedic Notes:', $dt['oe_orth_nt'], 3, '', TRUE);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['palp']) {
		$hdr_printed = PrintHeader('Palpation', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out,'Cervical Alignment:',ListLook($dt['oe_palp_cerv'],'NormAbnorm',''));
		$out = BuildPrintLine($out,'Thoracic Alignment:',ListLook($dt['oe_palp_thor'],'NormAbnorm',''));
		$out = BuildPrintLine($out,'Lumbar Alignment:',ListLook($dt['oe_palp_lum'],'NormAbnorm'));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Palpation Notes:', $dt['oe_palp_nt'], 3, '', $out);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['rom']) {
		$hdr_printed = PrintHeader('Range of Motion', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out,'Cervical Flexion:',ListLook($dt['oe_rom_cerv_fl'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Cervical Flexion Pain:',ListLook($dt['oe_rom_cerv_fl_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Cervical Extension:',ListLook($dt['oe_rom_cerv_ex'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Cervical Extension Pain:',ListLook($dt['oe_rom_cerv_ex_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Cervical Right Lateral Flexion:',ListLook($dt['oe_rom_cerv_rlfl'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Cervical Right Lateral Flexion Pain:',ListLook($dt['oe_rom_cerv_rlfl_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Cervical Left Lateral Flexion:',ListLook($dt['oe_rom_cerv_llfl'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Cervical Left Lateral Flexion Pain:',ListLook($dt['oe_rom_cerv_llfl_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Cervical Right Rotation:',ListLook($dt['oe_rom_cerv_rr'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Cervical Right Rotation Pain:',ListLook($dt['oe_rom_cerv_rr_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Cervical Left Rotation:',ListLook($dt['oe_rom_cerv_lr'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Cervical Left Rotation Pain:',ListLook($dt['oe_rom_cerv_lr_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Lumbar Flexion:',ListLook($dt['oe_rom_lum_fl'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Lumbar Flexion Pain:',ListLook($dt['oe_rom_lum_fl_p'],'OrthoPain',''));
		$out = BuildPrintLine($out,'Lumbar Extension:',ListLook($dt['oe_rom_lum_ex'],'OrthoROM',''));
		$out = BuildPrintLine($out,'Lumbar Extension Pain:',ListLook($dt['oe_rom_lum_ex_p'],'OrthoPain',''));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Range of Motion Notes:', $dt['oe_rom_nt']);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['msc']) {
		$hdr_printed = PrintHeader('Muscle Testing', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'Dermatomes:',MultPrint($dt['oe_msc_derm'],'OrthoDerm'));
		$out = BuildPrintLine($out, 'Neck Flex/Ext:',MultPrint($dt['oe_msc_neck'],'MuscleTest'));
		$out = BuildPrintLine($out, 'SCM:',MultPrint($dt['oe_msc_scm'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Interossei:',MultPrint($dt['oe_msc_inter'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Finger Extensor:',MultPrint($dt['oe_msc_fing_ex'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Wrist Extensor:',MultPrint($dt['oe_msc_wrist_ex'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Tricep:',MultPrint($dt['oe_msc_tri'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Rotator Cuff:',MultPrint($dt['oe_msc_cuff'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Deltoid:',MultPrint($dt['oe_msc_delt'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Latissimus:',MultPrint($dt['oe_msc_lat'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Finger Flexor:',MultPrint($dt['oe_msc_fing_fl'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Wrist Flexor:',MultPrint($dt['oe_msc_wrist_fl'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Bicep:',MultPrint($dt['oe_msc_bi'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Hip Flexor:',MultPrint($dt['oe_msc_hip'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Pectoralis:',MultPrint($dt['oe_msc_pec'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Psoas:',MultPrint($dt['oe_msc_psoas'],'MuscleTest'));
		$out = BuildPrintLine($out, 'TFL:',MultPrint($dt['oe_msc_tfl'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Gluteus Med:',MultPrint($dt['oe_msc_glut_med'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Quadricep:',MultPrint($dt['oe_msc_quad'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Gluteus Max:',MultPrint($dt['oe_msc_glut_max'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Hamstring:',MultPrint($dt['oe_msc_ham'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Tibialis Ant:',MultPrint($dt['oe_msc_tib'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Peronei:',MultPrint($dt['oe_msc_per'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Ext Hallicus:',MultPrint($dt['oe_msc_ext'],'MuscleTest'));
		$out = BuildPrintLine($out, 'Gastroc:',MultPrint($dt['oe_msc_gastroc'],'MuscleTest'));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Muscle Testing Notes:', $dt['oe_msc_nt'], 3, '', TRUE);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['tnd']) {
		$hdr_printed = PrintHeader('Deep Tendon Reflexes', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'Patellar:',MultPrint($dt['oe_tnd_pat'],'OrthoDTR'));
		$out = BuildPrintLine($out, 'Hamstring:',MultPrint($dt['oe_tnd_ham'],'OrthoDTR'));
		$out = BuildPrintLine($out, 'Achilles:',MultPrint($dt['oe_tnd_ach'],'OrthoDTR'));
		$out = BuildPrintLine($out, 'Biceps:',MultPrint($dt['oe_tnd_bi'],'OrthoDTR'));
		$out = BuildPrintLine($out, 'Triceps:',MultPrint($dt['oe_tnd_tri'],'OrthoDTR'));
		$out = BuildPrintLine($out, 'Radial:',MultPrint($dt['oe_tnd_rad'],'OrthoDTR'));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Deep Tendon Reflex Notes:', $dt['oe_tnd_nt']);
	}
	$hdr_printed = FALSE;

	if($section_data_exists['myo']) {
		$hdr_printed = PrintHeader('Myofascial Trigger Points', $hdr_printed);
		$out = '';
		$out = BuildPrintLine($out, 'Suboccipital:',ListLook($dt['oe_myo_sub'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Cervical:',ListLook($dt['oe_myo_cerv'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Scalene:',ListLook($dt['oe_myo_scal'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Sternocleidomastoid:',ListLook($dt['oe_myo_stern'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Trapezius:',ListLook($dt['oe_myo_trap'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Levator Scapulae:',ListLook($dt['oe_myo_lev'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Supraspinatus:',ListLook($dt['oe_myo_supra'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Thoracic Paraspinal:',ListLook($dt['oe_myo_thor'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Middle Trapezius:',ListLook($dt['oe_myo_mid'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Teres:',ListLook($dt['oe_myo_teres'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Rhomboid:',ListLook($dt['oe_myo_rhom'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Lumbar Erector Spinae:',ListLook($dt['oe_myo_lum'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Quadratus Lumborum:',ListLook($dt['oe_myo_quad'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Gluteal:',ListLook($dt['oe_myo_glut'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Piriformis:',ListLook($dt['oe_myo_piri'],'left_right_bi',''));
		$out = BuildPrintLine($out, 'Psoas:',ListLook($dt['oe_myo_psoas'],'left_right_bi',''));
		if($out) PrintSingleLine('', $out);
		PrintOverhead('Myofascial Notes:', $dt['oe_myo_nt']);
	}
	$hdr_printed = FALSE;

} // END OF DRAW DISPLAY
?>
