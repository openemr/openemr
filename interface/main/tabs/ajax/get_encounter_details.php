<?php

require_once("../../../globals.php");

$requestData = json_decode(file_get_contents('php://input'), true);
$encounterData = isset($requestData['encounter']) ? $requestData['encounter'] : array();
$response = array(
	'providers' => array()
);

$results = array();
if(!empty($encounterData)) {

	$whereStr  = "";
	if(is_array($pid)) {
		foreach ($pid as $value) {
			if(!empty($value)) {
				if(!empty($whereStr)) {
					$whereStr .= "OR ";
				}

				$whereStr .= "forms.encounter = " . $value . " ";
			}
		}

		if(!empty($whereStr)) {
			$whereStr = ' AND ('.$whereStr.') ';
		}
	}

	if(!empty($encounterData)) {
		$whereStr .= ' AND forms.encounter IN ('. implode(",", $encounterData) . ') ';
	}

	$res = sqlStatement("SELECT forms.encounter, form_encounter.date, u.lname, u.fname FROM forms, form_encounter LEFT JOIN users AS u ON (form_encounter.provider_id = u.id) WHERE form_encounter.encounter = forms.encounter " . $whereStr );

	while ($result = sqlFetchArray($res)) {
		$results['enc_'.$result['encounter']] = $result;
	}
}

foreach ($encounterData as $key => $encounterId) {

	$providerName = "";
	if(isset($results['enc_'.$encounterId])) {
		$encounterResult = ($results['enc_'.$encounterId]);

		if(!empty($encounterResult['lname'])) {
			$providerName = $encounterResult['lname'] .", ".$encounterResult['fname'];
		}
	}

	$response['providers'][] = $providerName;
}

echo json_encode($response);