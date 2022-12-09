<?php

include_once("../../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : array();
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

use OpenEMR\OemrAd\FeeSheet;

$status = FeeSheet::validateCPTCode($encounter, $pid);

echo json_encode(array(
	'feesheet_code_status' => $status
));