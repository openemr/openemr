<?php

require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\CoverageCheck;

$ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : array();
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$employer = isset($_REQUEST['employer']) ? $_REQUEST['employer'] : '';

$caseStatus = true;
$caseEmployerStatus = true;

foreach ($ids as $key => $id) {
	$insData = CoverageCheck::getInsuranceDataById($pid, $id);
	$insData = !empty($insData) && count($insData) > 0 ? $insData[0] : array();

	if(isset($insData)) {
		if(($insData['subscriber_fname'] == "" || $insData['subscriber_lname'] == "") || $insData['subscriber_relationship'] == "") {
			$caseStatus = false;
		}

		if(isset($insData['ins_type_code']) && $insData['ins_type_code'] == "25") {
			if($employer == "" || $employer == "0") {
				$caseEmployerStatus = false;
			}
		}
	}
}

echo json_encode(array(
	'case_form_status' => $caseStatus,
	'case_employer_status' => $caseEmployerStatus
));