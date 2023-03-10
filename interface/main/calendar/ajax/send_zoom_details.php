<?php

require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\ZoomIntegration;

$eid = $_REQUEST['eid'];
$responce = array(
	'status' => false,
	'message' => 'Failed'
);

if(!empty($eid)) {
	
	$resData = ZoomIntegration::handleSendZoomDetails($eid, $_REQUEST);

	if(isset($resData) && $resData['status'] === true) {
		if($resData['total_sent_item'] == 0) {
			$responce = array(
				'status' => true,
				'message' => 'No item found for send'
			);
		} else {
			$responce = array(
				'status' => true,
				'message' => 'Sent'
			);
		}
	} else if(isset($resData) && $resData['status'] === false) {
		$responce = array(
			'status' => true
		);

		if(!empty($resData['message'])) {
			$responce['message'] = $resData['message'];
		} else {
			$errorMsg = !empty($resData['status_msg']) ? "\n\n" . implode("\n", $resData['status_msg']) : "";
			$responce['message'] = "Sent: ". $resData['total_sent_item'] . " Failed: " . $resData['total_failed_item'] . $errorMsg;
		}
	}
}

echo json_encode($responce);