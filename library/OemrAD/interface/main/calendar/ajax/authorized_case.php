<?php

require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$pid = $_REQUEST['pid'] ? $_REQUEST['pid'] : '';
$case_id = $_REQUEST['case_id'] ? $_REQUEST['case_id'] : '';
$start_date = $_REQUEST['start_date'] ? $_REQUEST['start_date'] : '';
$type = $_REQUEST['type'] ? $_REQUEST['type'] : '';
$provider = $_REQUEST['provider'] ? $_REQUEST['provider'] : '';
$encounter = $_REQUEST['encounter'] ? $_REQUEST['encounter'] : '';

$response = array(
	'status' => true,
	'message' => array()
);

if($type == "appt") {
	if(!empty($case_id) && !empty($pid)) {
		$caseData = Caselib::getCaseData($case_id);
		$linked_encounters = Caselib::getLinkedApptByCase($pid, $case_id);
		$total_linked_enc = count($linked_encounters);

		$auth_start_date = isset($caseData['auth_start_date']) ? $caseData['auth_start_date'] : "";
		$auth_end_date = isset($caseData['auth_end_date']) ? $caseData['auth_end_date'] : "";

		if(isset($caseData['auth_req']) && $caseData['auth_req'] == true) {
			if(isset($caseData['auth_num_visit']) && $caseData['auth_num_visit'] != "") {
				if($total_linked_enc >= $caseData['auth_num_visit']) {
					$response['status'] = false;
					$response['message'][] = "Warning - This appointment would exceed the number of appointments authorized in this case";
				}
			}


			if($start_date != "" ) {
				$start_date_unix = strtotime($start_date);
				$date_status = true;

				if($auth_start_date != "") {
					if(strtotime($auth_end_date) < $start_date_unix) {
						$date_status = false;
					}

					if(strtotime($auth_start_date) > $start_date_unix) {
						$date_status = false;
					}				
				}

				if($date_status === false) {
					$response['status'] = false;
					$response['message'][] = "Warning - This appointment is not within the authorized dates of service for this case.";
				}
			}

			if(isset($caseData['auth_provider']) && !empty($caseData['auth_provider'])) {
				if($caseData['auth_provider'] != $provider) {
					$response['status'] = false;
					$response['message'][] = "Warning - This provider is not authorized for this case.";
				}
			}
		}
	}
} else if($type == "encounter") {
	if(!empty($case_id)) {
		$caseData = Caselib::getCaseData($case_id);
		$linked_encounters = Caselib::getLinkedEncounterByCase($case_id);
		$total_linked_enc = count($linked_encounters);
		$encounterData = Caselib::getEncounterById($encounter);

		$case_auth_provider = isset($caseData['auth_provider']) ? $caseData['auth_provider'] : "";
		$enc_provider = isset($encounterData['provider_id']) ? $encounterData['provider_id'] : "";

		$auth_start_date = isset($caseData['auth_start_date']) ? $caseData['auth_start_date'] : "";
		$auth_end_date = isset($caseData['auth_end_date']) ? $caseData['auth_end_date'] : "";

		if(isset($caseData['auth_req']) && $caseData['auth_req'] == true) {
			if(isset($caseData['auth_num_visit']) && $caseData['auth_num_visit'] != "") {
				if($total_linked_enc > $caseData['auth_num_visit']) {
					$response['status'] = false;
					$response['message'][] = "Warning - This encounter would exceed the number of encounters authorized in this case.";
				}
			}


			if($start_date != "" ) {
				$start_date_unix = strtotime($start_date);
				$date_status = true;

				if($auth_start_date != "") {
					if(strtotime($auth_end_date) < $start_date_unix) {
						$date_status = false;
					}

					if(strtotime($auth_start_date) > $start_date_unix) {
						$date_status = false;
					}				
				}

				if($date_status === false) {
					$response['status'] = false;
					$response['message'][] = "Warning - This encounter is not within the authorized dates of service for this case.";
				}
			}

			if(isset($case_auth_provider) && !empty($case_auth_provider)) {
				if($case_auth_provider != $enc_provider) {
					$response['status'] = false;
					$response['message'][] = "Warning - This provider is not authorized for this case.";
				}
			}
		}
	}
}

echo json_encode($response);