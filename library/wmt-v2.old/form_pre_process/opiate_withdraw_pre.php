<?php
$this_module = 'opiate_withdraw';
$this_table = 'form_opiate_withdraw';
unset($sm);
$sm = array();
if(isset($_POST['opiate_withdraw_id'])) {
	$sm['opiate_withdraw_id'] = $_POST['opiate_withdraw_id'];
	unset($_POST['opiate_withdraw_id']);
}
foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	if(substr($key,0,5) != 'cows_') continue;
	$sm[$key] = $val;
	unset($_POST[$key]);
}

if($frmdir == 'opiate_withdraw') {
	$sm['opiate_withdraw_id'] = $id;
} else {
	$sm['link_id'] = $encounter;
	$sm['link_form'] = 'encounter';
}

if($sm['opiate_withdraw_id']) {
 	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
					$_SESSION['userauthorized'], 1);
 	$q1 = '';
 	foreach ($sm as $key => $val){
		if($key == 'opiate_withdraw_id') continue;
   	$q1 .= "`$key` = ?, ";
		$binds[] = $val;
 	}
	$binds[] = $sm['opiate_withdraw_id'];
 	sqlInsert('UPDATE `' . $this_table . '` SET `pid` = ?, `groupname` = ?, ' .
		'`user` = ?, `authorized` = ?, `activity` = ?, ' . $q1 . 
		'`date` = NOW() ' . 'WHERE `id` = ?', $binds);
} else {
	unset($sm['opiate_withdraw_id']);
 	$newid = wmtFormSubmit($this_table, $sm,'',$_SESSION['userauthorized'],$pid);
	if($frmdir == 'opiate_withdraw') 
 		addForm($encounter,$ftitle,$newid,$frmdir,$pid,$_SESSION['userauthorized']);
	$id = $newid;
	$sm['opiate_withdraw_id'] = $newid;
}
?>
