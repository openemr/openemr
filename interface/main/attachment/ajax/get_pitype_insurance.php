<?php

include_once("../../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

function getCaseData($pid) {
	$dataSet = array();

	if(empty($pid)) {
		return $dataSet;
	}

	$result = sqlStatement("SELECT pd.pubpid, fc.* FROM form_cases fc left join patient_data pd on pd.pid = fc.pid WHERE fc.pid  = ? and fc.closed = 0", $pid);
	while ($row = sqlFetchArray($result)) {
		$dataSet[] = $row;
	}

	return $dataSet;
}

$piTypeCases = array();
$cases = getCaseData($pid);

foreach ($cases as $ck => $case) {
	$liableData = Caselib::isLiablePiCaseByCase($case['id'], $pid, $case);

	if($liableData === true) {
		$piTypeCases[] = $case;
	}
}

echo json_encode($piTypeCases);