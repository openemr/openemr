<?php

include_once("../../../globals.php");

$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : array();
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

function getBillingInfo($encounter) {
	$results = array();

	$result = sqlStatement("SELECT bl.* FROM `billing` AS bl WHERE bl.encounter = ? AND bl.activity = 1 ",array($encounter));
	while ($row = sqlFetchArray($result)) {
		$results[] = $row;
	}

	return $results;
}

function isCodeExists($type, $code, $items = array()) {
	$status = false;
	foreach ($items as $key => $item) {
		if(isset($item['code_type']) && $item['code_type'] == $type && $item['code'] == $code) {
			$status = true;
		}
	}

	return $status;
}

function decodeCode($codeStr) {
	$codes = array();

	if(!empty($codeStr)) {
		$codeItems = explode(":", $codeStr);

		foreach ($codeItems as $key => $codeItem) {
			if(!empty($codeItem)) {
				$codeValues = explode("|", $codeItem);
				if(!empty($codeValues)) {
					$codes[] = array( 'code_type' => $codeValues[0], 'code' => $codeValues[1]);
				}
			}
		}
	}

	return $codes;
}

function validateCPTCode($encounter, $pid) {
	//print_r($encounter);

	$billingData = getBillingInfo($encounter);
	$codeItems = array();
	$validationStatus = true;

	foreach ($billingData as $key => $item) {
		if(isset($item['code_type']) && !empty($item['code_type'])) {
			$codeItems[] = array( 'code_type' => $item['code_type'], 'code' => $item['code']);
		}
	}

	foreach ($billingData as $key => $bItem) {
		if(isset($bItem['code_type']) && (substr($bItem['code_type'], 0, 3 ) == "CPT" || substr($bItem['code_type'], 0, 5 ) == "HCPCS")) {
			if(isset($bItem['justify']) && empty($bItem['justify'])) {
				$validationStatus = false;
			}

			if(isset($bItem['justify']) && !empty($bItem['justify'])) {
				$codeValues = decodeCode($bItem['justify']);
				$icdStatus = false;

				foreach ($codeValues as $key => $jCode) {
					if(isset($jCode['code_type']) && substr($jCode['code_type'], 0, 3 ) == "ICD") {
						$codeValueStatus = isCodeExists($jCode['code_type'], $jCode['code'], $codeItems);
						if($codeValueStatus === true) {
							$icdStatus = true;
						}
					}
				}
				$validationStatus = $icdStatus;
			}
		}
	}

	return $validationStatus;
}

$status = validateCPTCode($encounter, $pid);

echo json_encode(array(
	'feesheet_code_status' => $status
));