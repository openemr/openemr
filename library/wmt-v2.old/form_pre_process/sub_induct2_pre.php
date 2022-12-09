<?php
// $this_module = 'sub_induct2';
$this_table  = 'form_' . $this_module;

if($frmdir == 'sub_induct2') {
	foreach($_POST as $key => $val) {
		if(is_string($val)) $val = trim($val);
	}
} else {
	unset($md);
	$md = array();
	if(isset($_POST['sub_induct2_id'])) {
		$md['sub_induct2_id'] = $_POST['sub_induct2_id'];
		unset($_POST['sub_induct2_id']);
	}
	foreach($_POST as $key => $val) {
		if(is_string($val)) $val = trim($val);
		if(substr($key,0,3) != 'si_') continue; 
		if($key == 'si_form_dt') {
			$md['form_dt'] = $val;
		} else {
			$md[$key] = $val;
		}
		if(substr($key, -3) == '_dt')	{
			$md[$key] = DateToYYYYMMDD($val);
			if($md[$key] == '') $md[$key] = NULL;
			if($md[$key] == '0000-00-00') $md[$key] = NULL;
		}
		unset($_POST[$key]);
	}

	$md['link_id'] = $encounter;
	$md['link_form'] = 'encounter';

	if($md['sub_induct2_id']) {
 		$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
 		$q1 = '';
 		foreach ($md as $key => $val){
			if($key == 'sub_induct2_id') continue;
   		$q1 .= "`$key` = ?, ";
			$binds[] = $val;
 		}
		$binds[] = $md['sub_induct2_id'];
 		sqlInsert('UPDATE `' . $this_table . '` SET `pid` = ?, `groupname` = ?, ' .
			'`user` = ?, `authorized` = ?, `activity` = ?, ' . $q1 . 
			'`date` = NOW() ' . 'WHERE `id` = ?', $binds);
	} else {
		unset($md['sub_induct2_id']);
 		$newid = wmtFormSubmit($this_table,$md,'',$_SESSION['userauthorized'],$pid);
		$md['sub_induct2_id'] = $newid;
	}
}
?>
