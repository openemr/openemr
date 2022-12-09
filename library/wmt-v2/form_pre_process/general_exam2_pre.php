<?php
$ge_notes = array();
$flds = sqlListFields($frmn);
reset($_POST);
foreach($_POST as $key => $val) {
	if(is_string($val)) $val = trim($val);
	if(substr($key,0,3) != 'ge_') continue;
	if(substr($key,-3) != '_nt') continue;
	$tmp = substr($key,0,-3);
	if(in_array($tmp, $flds)) {
		$ge_notes[$key] = $val;
		unset($_POST[$key]);
	}
}
?>
