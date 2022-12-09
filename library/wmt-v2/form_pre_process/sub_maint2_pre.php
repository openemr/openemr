<?php
// $this_module = 'sub_maint2';
$this_table  = 'form_' . $this_module;
if($frmdir == 'sub_maint2') {

} else {
	unset($md);
	$md = array();
	$md['sub_maint2_id'] = '';
	if(isset($_POST['sub_maint2_id'])) {
		$md['sub_maint2_id'] = $_POST['sub_maint2_id'];
		unset($_POST['sub_maint2_id']);
	}
	foreach($_POST as $key => $val) {
		if(is_string($val)) $val = trim($val);
		if(substr($key,0,3) != 'sm_') continue; 
		if($key == 'sm_form_dt') {
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

	if(isset($md['sm_form_dt'])) {
		$md['form_dt'] = $md['sm_form_dt'];
		unset($md['sm_form_dt']);
	}
	$md['link_id'] = $encounter;
	$md['link_form'] = 'encounter';
	
	if($md['sub_maint2_id']) {
 		$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
 		$q1 = '';
 		foreach ($md as $key => $val){
			if($key == 'sub_maint2_id') continue;
   		$q1 .= "`$key` = ?, ";
			$binds[] = $val;
 		}
		$binds[] = $md['sub_maint2_id'];
 		sqlInsert('UPDATE `' . $this_table . '` SET `pid` = ?, `groupname` = ?, ' .
			'`user` = ?, `authorized` = ?, `activity` = ?, ' . $q1 . 
			'`date` = NOW() ' . 'WHERE `id` = ?', $binds);
	} else {
		unset($md['sub_maint2_id']);
 		$newid = wmtFormSubmit($this_table,$md,'',$_SESSION['userauthorized'],$pid);
		$md['sub_maint2_id'] = $newid;
	}
}
?>
