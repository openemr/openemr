<?php
require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$case_id = isset($_REQUEST['case_id']) ? $_REQUEST['case_id'] : '';

$recentCase = Caselib::mostRecentCase($pid, $case_id);

$status = false;
if($recentCase !== false) {
	if($recentCase['closed'] == "1") {
		$status = true;
	}
}

echo json_encode(array(
	'status' => $status 
));