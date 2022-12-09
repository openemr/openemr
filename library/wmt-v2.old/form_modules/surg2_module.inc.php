<?php 
if(!isset($frmdir)) $frmdir = '';
if(!isset($encounter)) $encounter = '';
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($draw_display)) $draw_display = TRUE;

if(!isset($client_id)) {
	if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';
	$client_id = $GLOBALS['wmt::client_id'];
}
if($form_mode == 'new' || $form_mode == 'update') {

} else {
	unset($ss2);
	$ss2 = array();
	foreach($_POST as $key => $val) {
		if(is_string($val)) $val = trim($val);
		if(substr($key,0,3) != 'ss2_') continue;
		$ss2[$key] = $val;
		unset($_POST[$key]);
	}

	if($frmdir == 'surg2') {
		$ss2['id'] = $id;
	} else {
		// IN CASE IT'S NOT STAND ALONE
	}
	if($ss2['id']) {
  	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
  	$q1 = '';
  	foreach ($ss2 as $key => $val){
			if($key == 'id') continue;
    	$q1 .= "`$key` = ?, ";
			$binds[] = $val;
  	}
		$binds[] = $ss2['id'];
  	sqlInsert('UPDATE `form_surg2` SET `pid` = ?, `groupname` = ?, ' .
			'`user`=?, `authorized` = ?, `activity` = ?, ' . $q1 . '`date` = NOW() ' .
			'WHERE `id`=?', $binds);
	} else {
		unset($ss2['id']);
  	$newid = 
			wmtFormSubmit('form_surg2',$ss2,'',$_SESSION['userauthorized'],$pid);
		if($frmdir == 'surg2') 
  		addForm($encounter,$ftitle,$newid,$frmdir,$pid,$_SESSION['userauthorized']);
	}
}

if($frmdir == 'surg2') {
	// WAS CALLED AS A STAND-ALONE
	$sql = 'SELECT * FROM form_surg2 WHERE id = ? AND pid = ?';
	$binds = array($id, $pid);
} else {
	// GET ENCOUNTER LEVEL FORM - NOT IN USE
	$sql = 'SELECT * FROM form_surg2 WHERE link_id = ? AND link_name = ? ' .
		'AND pid = ?';
	$binds = array($encounter, 'encounter', $pid);
}
$ss2 = sqlQuery($sql, $binds);
if($ss2) {
	$dt['ss2_id'] = $ss2['id'];
	$ss2 = array_slice($ss2,13);
	foreach($be as $key => $val) {
		$dt[$key] = $val;
	}
} else {
	$ss2 = sqlListFields('form_surg2');
	$ss2 = array_slice($ss2,7);
	foreach($be as $fld) {
		echo "Setting Field [$fld] to nothing<br>\n";
		$dt[$fld] = '';
	}
	$dt['ss2_id' ] = '';
}

if($draw_display) {
	
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
</table>

<?php 
} // END OF DRAW DISPLAY
?>
