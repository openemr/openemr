<?php
unset($gyn);
$gyn = array();
reset($_POST);
foreach($_POST as $k => $var) {
	if(substr($k,0,4) != 'gyn_') continue;
	if(is_string($var)) $var = trim($var);
	$gyn[$k] = $var;
	unset($_POST[$k]);
}

?>
