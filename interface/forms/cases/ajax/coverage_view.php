<?php

require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\CoverageCheck;

if(!isset($_POST{'cnt'})) $_POST['cnt'] = '';
if(!isset($_POST{'case_id'})) $_POST['case_id'] = '';
if(!isset($_POST{'ins_id'})) $_POST['ins_id'] = '';
if(!isset($_POST{'pid'})) $_POST['pid'] = '';
if(!isset($_POST{'provider_id'})) $_POST['provider_id'] = '';

$cnt = strip_tags($_POST['cnt']);
$case_id = strip_tags($_POST['case_id']);
$ins_id = strip_tags($_POST['ins_id']);
$pid = strip_tags($_POST['pid']);
$provider_id = strip_tags($_POST['provider_id']);


if($pid != '' && $ins_id != '') {
	$providerId = CoverageCheck::getProviderId($provider_id, $ins_id, $pid);

	/*Get InsuranceData based on insurence copmany details*/
	$returnData = CoverageCheck::getInsuranceDataById($pid, $ins_id, $providerId);
	if($returnData && is_array($returnData) && count($returnData) > 0) {
		if(!empty($returnData[0]['policy_number'])) {
			
			/*Get Html content on page render*/
			echo CoverageCheck::getHtmlContent($pid, $case_id, $cnt, $ins_id, $providerId, $returnData[0]);
		}
	}
}

?>