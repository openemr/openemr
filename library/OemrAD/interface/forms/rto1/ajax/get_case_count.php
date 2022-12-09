<?php
require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

$caseD = Caselib::getCaseCount($pid);

$status = false;
if(!$caseD || $cCount >= 0) {
	$status = true;
}

echo json_encode(array(
	'status' => $status,
	'count' => isset($caseD) && !empty($caseD) && isset($caseD['count']) ? $caseD['count'] : 0
));