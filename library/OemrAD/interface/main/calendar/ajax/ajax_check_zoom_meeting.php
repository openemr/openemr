<?php

require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\ZoomIntegration;

$apptsCategorys = array_map('trim', explode(",", $GLOBALS['zoom_appt_category']));
$apptsFacilitys = array_map('trim', explode(",", $GLOBALS['zoom_appt_facility']));

$zoom_mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
$appt_id = isset($_REQUEST['appt_id']) ? $_REQUEST['appt_id'] : "";
$appt_provider = isset($_REQUEST['appt_provider']) ? $_REQUEST['appt_provider'] : "";
$appt_date = isset($_REQUEST['appt_date']) ? $_REQUEST['appt_date'] : "";
$appt_hour = isset($_REQUEST['appt_hour']) ? $_REQUEST['appt_hour'] : "";
$appt_minute = isset($_REQUEST['appt_minute']) ? $_REQUEST['appt_minute'] : "";
$appt_ampm = isset($_REQUEST['appt_ampm']) ? $_REQUEST['appt_ampm'] : "";
$appt_duration = isset($_REQUEST['appt_duration']) ? $_REQUEST['appt_duration'] : "";
$appt_facility = isset($_REQUEST['appt_facility']) ? $_REQUEST['appt_facility'] : "";
$appt_category = isset($_REQUEST['appt_category']) ? $_REQUEST['appt_category'] : "";
$performStatus = false;

$responce = array(
	'status' => false,
	'message' => ''
);

if($zoom_mode == "check") {
	$apptStartTime = "";

	if(!empty($appt_hour) && !empty($appt_minute) && !empty($appt_ampm) && !empty($appt_date)) {
		$appt_date = DateToYYYYMMDD($appt_date);
		$tmph = $appt_hour + 0;
		$tmpm = $appt_minute + 0;
		if ($appt_ampm == '2' && $tmph < 12) {
		    $tmph += 12;
		}

		$duration = abs($appt_duration); // fixes #395

		$apptStartTime = $appt_date ." $tmph:$tmpm:00";

		$tmpm += $duration;
	    while ($tmpm >= 60) {
	        $tmpm -= 60;
	        ++$tmph;
	    }

	    $endtime = $appt_date ." $tmph:$tmpm:00";
	}


	if (!empty($appt_facility) && in_array($appt_facility, $apptsFacilitys)) {
		$performStatus = true;
	}

	if (!empty($appt_category) && in_array($appt_category, $apptsCategorys)) {
		$performStatus = true;
	}

	if ($performStatus === true) {
		if(!empty($apptStartTime) && !empty($appt_provider)) {
			$pResultSet = ZoomIntegration::getProviderMeeting($apptStartTime, $endtime, $appt_provider, $appt_id);

			if(isset($pResultSet) && is_array($pResultSet) && count($pResultSet) > 0) {
				$responce['message'] = 'Appointment already exists at the same time. Please select different time.';
			}
		}
	}
} else if($zoom_mode == "recreate") {
	if(!empty($appt_id) && !empty($appt_category)) {
		$zRecreateRes = ZoomIntegration::recreateZoomMeeting($appt_id, $appt_category, true );

		if(isset($zRecreateRes) && $zRecreateRes === true) {
			$responce = array(
				'status' => true,
				'message' => 'Success'
			);
		} else {
			$responce = array(
				'status' => false,
				'message' => 'Error: Something went wrong.'
			);
		}
	}
} else if($zoom_mode == "delete") {
	if(!empty($appt_id)) {
		$zRecreateRes = ZoomIntegration::handleZoomApptDeleteEvent($appt_id);

		$responce = array(
			'status' => true,
			'message' => 'Deleted'
		);
	}
}


echo json_encode($responce);