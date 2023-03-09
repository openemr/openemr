<?php

require_once("../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\CoverageCheck;

if(!isset($_POST{'cnt'})) $_POST['cnt'] = '';
if(!isset($_POST{'case_id'})) $_POST['case_id'] = '';
if(!isset($_POST{'ins_id'})) $_POST['ins_id'] = '';
if(!isset($_POST{'pid'})) $_POST['pid'] = '';
if(!isset($_POST{'provider_id'})) $_POST['provider_id'] = '';

/* Set Request data */
$cnt = strip_tags($_POST['cnt']);
$case_id = strip_tags($_POST['case_id']);
$ins_id = strip_tags($_POST['ins_id']);
$pid = strip_tags($_POST['pid']);
$provider_id = strip_tags($_POST['provider_id']);

$response = array(
	'success' => 0 , 'error' => 'Something wrong'
);

$userId = '';
if(isset($_SESSION['authUserID'])) {
	$userId = $_SESSION['authUserID'];
}

if($pid != '' && $ins_id != '') {
	$providerId = CoverageCheck::getProviderId($provider_id, $ins_id, $pid);

	/* Get Insurance Data*/
	$returnData = CoverageCheck::getInsuranceDataById($pid, $ins_id, $providerId);
	
	if($returnData && is_array($returnData) && count($returnData) > 0) {
		/* Call Verification API and check coverage eligbility */
		$responseData = CoverageCheck::handleCallVerificationAPI($returnData);

		$response = prepareData($ins_id, $cnt, $case_id, $returnData, $responseData);
		if($case_id != '' && $response['success'] == "1") {
			CoverageCheck::manageUpdateData($pid, $userId, $case_id, $cnt, $response);
		} else if($case_id == ''){
			$response['action'] = "1";
		}
	}
}

function prepareData($ins_id, $cnt, $case_id, $insuranceData, $response) {
	if(is_array($response) && is_array($insuranceData)) {
		$response['ins_id'] = $ins_id;
		$response['cnt'] = $cnt;
		$response['case_id'] = $case_id;
		$response['ic_id'] = isset($insuranceData[0]['ic_id']) ? $insuranceData[0]['ic_id'] : '';
	}
	return $response;
}

echo json_encode($response); 
