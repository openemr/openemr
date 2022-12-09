<?php
include_once($GLOBALS['srcdir'].'/amc.php');
if(!isset($encounter)) $encounter = 0;
if(!isset($frmdir)) $frmdir = '';
if(!isset($pat_data)) $pat_data = '';
if(!isset($patient)) $patient = '';
if(!isset($visit)) $visit = '';
$object_type = ($frmdir == 'dashboard') ? 'pat_summary' : 'form_encounter';
amcAdd('med_reconc_amc', TRUE, $pid, $object_type, $encounter);
if(checkSettingMode('wmt::auto_add_med_hcpcs','',$frmdir)) {
	include_once($GLOBALS['srcdir'].'/billing.inc');
	include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
	include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
	include_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
	$age = $provider = 0;
	if(isset($pat_age)) $age = $pat_age;
	if(is_object($patient)) {
		if(isset($patient->age)) $age = $patient->age;
	}
	if(is_object($pat_data)) {
		if(isset($pat_data->age)) $age = $pat_data->age;
	}
	if(is_object($visit)) $provider = $visit->provider_id;
	$age_tmp = explode(' ', $age);
	if(isset($age_tmp[1])) {
		if(strtolower(substr($age_tmp[1],0,3)) == 'mon') $age = 1;
	}
	$type = 'HCPCS';
	if($age && $age > 17) { 
		$code = 'G8427';
		$desc = lookup_code_descriptions($type . ':' . $code);
	} else if($age) {
		$code = 'G8430';
		$desc = lookup_code_descriptions($type . ':' . $code);
	}
	if($age) {
		if(!billingExists($type, $code, $pid, $encounter)) {
			addBilling($encounter, $type, $code, $desc, $pid, '1', 
						$provider, '', '1');
		}
	}
}
?>
