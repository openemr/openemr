<?php

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\Reminder;

$ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : array();
$serviceType = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$template = isset($_REQUEST['template']) ? $_REQUEST['template'] : '';

$responce = array(
	'status' => false,
	'message' => 'Failed'
);

if(!empty($ids)) {
	$resData = Reminder::handleApptReportNotifications($ids, $serviceType, $template);

	if(isset($resData) && !empty($resData)) {
		$totalSent = isset($resData['sent_items']) ? $resData['sent_items'] : 0;
		$totalFailed = isset($resData['failed_items']) ? $resData['failed_items'] : 0;

		$responce = array(
			'status' => true,
			'message' => "Total Sent: ".$totalSent. "\n". "Total Failed: " . $totalFailed,
			'sent_items' => $totalSent,
			'failed_items' => $totalFailed,
		);
	}
}

echo json_encode($responce);