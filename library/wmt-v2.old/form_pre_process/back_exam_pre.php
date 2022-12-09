<?php
unset($normal);
$normal = array();
unset($motor);
$motor = array();
unset($sense);
$light = array();
unset($pin);
$pin = array();
unset($dtr);
$dtr = array();
unset($pathr);
$pathr = array();
unset($skin);
$skin = array();
unset($leg);
$leg = array();
unset($rom);
$rom = array();
unset($stable);
$stable = array();
unset($fabere);
$fabere = array();
unset($vascular);
$vascular = array();
unset($lymph);
$lymph = array();
unset($waddell);
$waddell = array();
unset($be);
$be = array();
foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	if(substr($key,0,3) != 'be_') continue;
	if(substr($key,0,7) == 'be_mtr_') {
		$motor[] = substr($key,7) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,13) == 'be_sns_light_') {
		$light[] = substr($key,13) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,11) == 'be_sns_pin_') {
		$pin[] = substr($key,11) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,11) == 'be_rfx_dtr_') {
		$dtr[] = substr($key,11) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,12) == 'be_rfx_path_') {
		$pathr[] = substr($key,12) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,8) == 'be_skin_') {
		$skin[] = substr($key,8) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,12) == 'be_misc_leg_') {
		$leg[] = substr($key,12) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,12) == 'be_misc_rom_') {
		$rom[] = substr($key,12) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,15) == 'be_misc_stable_') {
		$stable[] = substr($key,15) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,14) == 'be_misc_faber_') {
		$fabere[] = substr($key,14) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,12) == 'be_vascular_') {
		$vascular[] = substr($key,12) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,9) == 'be_lymph_') {
		$lymph[] = substr($key,9) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,0,11) == 'be_waddell_') {
		$waddell[] = substr($key,11) . $GLOBALS['wmt::secondary_parse'] . $val;
	} else if(substr($key,-10) == '_norm_exam') {
 		$normal[$key] = $val;
	} else {
		$be[$key] = $val;
	}
	unset($_POST[$key]);
}
$be['be_sec_norm'] = implode($GLOBALS['wmt::primary_parse'], $normal);
$be['be_motor'] = implode($GLOBALS['wmt::primary_parse'], $motor);
$be['be_sns_light'] = implode($GLOBALS['wmt::primary_parse'], $light);
$be['be_sns_pin'] = implode($GLOBALS['wmt::primary_parse'], $pin);
$be['be_rfx_dtr'] = implode($GLOBALS['wmt::primary_parse'], $dtr);
$be['be_rfx_path'] = implode($GLOBALS['wmt::primary_parse'], $pathr);
$be['be_skin'] = implode($GLOBALS['wmt::primary_parse'], $skin);
$be['be_misc_leg_raise'] = implode($GLOBALS['wmt::primary_parse'], $leg);
$be['be_misc_rom'] = implode($GLOBALS['wmt::primary_parse'], $rom);
$be['be_misc_stable'] = implode($GLOBALS['wmt::primary_parse'], $stable);
$be['be_misc_faber'] = implode($GLOBALS['wmt::primary_parse'], $fabere);
$be['be_vascular'] = implode($GLOBALS['wmt::primary_parse'], $vascular);
$be['be_lymph'] = implode($GLOBALS['wmt::primary_parse'], $lymph);
$be['be_waddell'] = implode($GLOBALS['wmt::primary_parse'], $waddell);

if($frmdir == 'back_exam') {
	$be['be_id'] = $id;
} else {
	$be['link_id'] = $encounter;
	$be['link_name'] = 'encounter';
}

if($be['be_id']) {
 	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
					$_SESSION['userauthorized'], 1);
 	$q1 = '';
 	foreach ($be as $key => $val){
		if($key == 'be_id') continue;
   	$q1 .= "`$key` = ?, ";
		$binds[] = $val;
 	}
	$binds[] = $be['be_id'];
 	sqlInsert('UPDATE `form_back_exam` SET `pid` = ?, `groupname` = ?, ' .
		'`user`=?, `authorized` = ?, `activity` = ?, ' . $q1 . '`date` = NOW() ' .
		'WHERE `id`=?', $binds);
} else {
	unset($be['be_id']);
 	$newid = 
		wmtFormSubmit('form_back_exam',$be,'',$_SESSION['userauthorized'],$pid);
	if($frmdir == 'back_exam') 
 		addForm($encounter,$ftitle,$newid,$frmdir,$pid,$_SESSION['userauthorized']);
	$id = $newid;
	$be['be_id'] = $newid;
}
?>
