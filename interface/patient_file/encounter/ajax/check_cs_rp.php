<?php

require_once("../../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : array();
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

$status = Caselib::getCsRpExists($encounter, $pid);

echo json_encode(array(
	'status' => $status
));