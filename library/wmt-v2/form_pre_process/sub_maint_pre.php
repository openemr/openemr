<?php
$this_module = 'sub_maint';
$this_table  = 'form_sub_maint';
unset($sm);
$sm = array();
if(isset($_POST['sub_maint_id'])) {
	$sm['sub_maint_id'] = $_POST['sub_maint_id'];
	unset($_POST['sub_maint_id']);
}
foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	if(substr($key,0,3) != 'sm_') continue; 
	$sm[$key] = $val;
	unset($_POST[$key]);
}

if($frmdir == 'sub_maint') {
	$sm['sub_maint_id'] = $id;
} else {
	$sm['link_id'] = $encounter;
	$sm['link_form'] = 'encounter';
	
	if($sm['sub_maint_id']) {
 		$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
 		$q1 = '';
 		foreach ($sm as $key => $val){
			if($key == 'sub_maint_id') continue;
   		$q1 .= "`$key` = ?, ";
			$binds[] = $val;
 		}
		$binds[] = $sm['sub_maint_id'];
 		sqlInsert('UPDATE `' . $this_table . '` SET `pid` = ?, `groupname` = ?, ' .
			'`user` = ?, `authorized` = ?, `activity` = ?, ' . $q1 . 
			'`date` = NOW() ' . 'WHERE `id` = ?', $binds);
	} else {
		unset($sm['sub_maint_id']);
 		$newid = wmtFormSubmit($this_table,$sm,'',$_SESSION['userauthorized'],$pid);
		$sm['sub_maint_id'] = $newid;
	}
}
?>
