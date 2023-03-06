<?php
require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$case_id = isset($_REQUEST['case_id']) ? $_REQUEST['case_id'] : '';

$caseD = Caselib::activateCase($pid, $case_id);

$status = false;
if(!empty($caseD)) {
	$status = true;
}

echo json_encode(array(
	'status' => $status 
));