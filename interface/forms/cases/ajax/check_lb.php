<?php

require_once("../../../globals.php");
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

$ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : array();
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

$status = wmtCase::checkIsLiableForPiCase($ids, $pid);

echo json_encode(array(
	'status' => $status
));