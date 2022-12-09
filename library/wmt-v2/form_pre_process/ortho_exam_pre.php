<?php
unset($oe);
$oe = array();
$multi_labels = array('post_api', 'neu_sense', 'orth_cerv', 'orth_lum', 
	'orth_sac', 'orth_hip', 'orth_shou', 'orth_elbow', 'orth_wrist', 'orth_knee',
	'orth_ankle', 'msc_derm', 'msc_neck', 'msc_scm', 'msc_inter', 'msc_fing_ex', 
	'msc_wrist_ex', 'msc_tri', 'msc_cuff', 'msc_delt', 'msc_lat', 'msc_fing_fl', 
	'msc_wrist_fl', 'msc_bi', 'msc_hip', 'msc_pec', 'msc_psoas', 'msc_tfl',
	'msc_glut_med', 'msc_quad', 'msc_glut_max', 'msc_ham', 'msc_tib', 'msc_per', 
	'msc_ext', 'msc_gastroc', 'tnd_pat', 'tnd_ham', 'tnd_ach', 'tnd_bi', 
	'tnd_tri', 'tnd_rad');

foreach($multi_labels as $lbl) {
	if(isset($_POST['oe_' . $lbl])) {
		$oe[$lbl] = implode('^|', $_POST['oe_' . $lbl]);
		unset($_POST['oe_' . $lbl]);
	} else {
		$oe[$lbl] = '';
	}
}

foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	if($key == 'ortho_exam_id') {
		$oe[$key] = $val;
		unset($_POST[$key]);
	}
	if(substr($key,0,3) != 'oe_') continue;
	$oe[substr($key, 3)] = $val;
	unset($_POST[$key]);
}

if($frmdir == 'ortho_exam') {
	$oe['ortho_exam_id'] = $id;
} else {
	$oe['link_id'] = $encounter;
	$oe['link_form'] = 'encounter';
}

if($oe['ortho_exam_id']) {
 	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
					$_SESSION['userauthorized'], 1);
 	$q1 = '';
 	foreach ($oe as $key => $val){
		if($key == 'ortho_exam_id') continue;
   	$q1 .= "`$key` = ?, ";
		$binds[] = $val;
 	}
	$binds[] = $oe['ortho_exam_id'];
 	sqlInsert('UPDATE `form_ortho_exam` SET `pid` = ?, `groupname` = ?, ' .
		'`user`=?, `authorized` = ?, `activity` = ?, ' . $q1 . '`date` = NOW() ' .
		'WHERE `id`=?', $binds);
} else {
	unset($oe['ortho_exam_id']);
 	$newid = 
		wmtFormSubmit('form_ortho_exam',$oe,'',$_SESSION['userauthorized'],$pid);
	if($frmdir == 'ortho_exam') {
 		addForm($encounter,$ftitle,$newid,$frmdir,$pid,$_SESSION['userauthorized']);
		$id = $newid;
	}
	$oe['ortho_exam_id'] = $newid;
}
?>
