<?php
if(!isset($data['form_dt'])) $data['form_dt'] = date('Y-m-d');
$gyn['form_dt'] = $data['form_dt'];

$exists = sqlQuery('SELECT * FROM `form_gyn_exam` WHERE `pid` = ? AND '.
	'`link_id` = ? AND `link_form` = ?', array($pid, $id, $frmdir));
if(!isset($exists{'id'})) $exists{'id'} = '';
if($exists{'id'}) {
	$binds = array($_SESSION['authProvider'], $_SESSION['authUser'],
				$_SESSION['userauthorized']);
 	$q1 = '';
 	foreach ($gyn as $key => $val){
 		$q1 .= "`$key` = ?, ";
		$binds[] = $val;
 	}
	$binds[] = $pid;
	$binds[] = $frmdir;
	$binds[] = $id;
 	sqlStatement('UPDATE `form_gyn_exam` SET `groupname` = ?, `user` = ?, '.
				"`authorized` = ?, `activity` = 1, $q1 `date` = NOW() WHERE `pid` = ? ".
				'AND `link_form` = ? AND `link_id` = ?', $binds);
} else {
	wmtFormSubmit('form_gyn_exam',$gyn,'',$_SESSION['userauthorized'],$pid);
}
?>
