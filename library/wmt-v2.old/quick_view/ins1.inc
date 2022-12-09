<?php
if(!isset($pid)) echo '** No Patient ID Available';
if(!isset($patient)) $patient = '';
if(!is_object($patient)) {
	if(!class_exists('wmtPatData')) include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
	if($pid) $patient = wmtPatData::getPidPatient($pid);
}
if(is_object($patient)) {
	echo htmlspecialchars($patient->primary,ENT_QUOTES,'',FALSE);
}
?>
