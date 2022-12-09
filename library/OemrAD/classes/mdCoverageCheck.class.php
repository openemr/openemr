<?php

namespace OpenEMR\OemrAd;

include_once(__DIR__ . '/../configs/coverage_settings.php');

class CoverageCheck {

	/*Constructor*/
	public function __construct() {
	}

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->client = isset($GLOBALS['availity_client_id']) ? $GLOBALS['availity_client_id'] : "";
		$returnList->secret = isset($GLOBALS['availity_client_secret']) ? $GLOBALS['availity_client_secret'] : "";

		return $returnList;
	}

	// public static function getGreenSVG() {
	// 	return '<svg height="19pt" viewBox="0 0 512 512" width="19pt" xmlns="http://www.w3.org/2000/svg"><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#4caf50"/><path d="m385.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0" fill="#fafafa"/></svg>';
	// }

	// public static function getRegSVG() {
	// 	return '<svg height="19pt" viewBox="0 0 512 512" width="19pt" xmlns="http://www.w3.org/2000/svg"><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#f44336"/><path d="m350.273438 320.105469c8.339843 8.34375 8.339843 21.824219 0 30.167969-4.160157 4.160156-9.621094 6.25-15.085938 6.25-5.460938 0-10.921875-2.089844-15.082031-6.25l-64.105469-64.109376-64.105469 64.109376c-4.160156 4.160156-9.621093 6.25-15.082031 6.25-5.464844 0-10.925781-2.089844-15.085938-6.25-8.339843-8.34375-8.339843-21.824219 0-30.167969l64.109376-64.105469-64.109376-64.105469c-8.339843-8.34375-8.339843-21.824219 0-30.167969 8.34375-8.339843 21.824219-8.339843 30.167969 0l64.105469 64.109376 64.105469-64.109376c8.34375-8.339843 21.824219-8.339843 30.167969 0 8.339843 8.34375 8.339843 21.824219 0 30.167969l-64.109376 64.105469zm0 0" fill="#fafafa"/></svg>';
	// }

	public static function getInfoSVG() {
		return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" width="12pt" height="12pt" style="enable-background:new 0 0 512 512;" xml:space="preserve"> <g> <g> <path d="M437.02,74.98C388.667,26.629,324.38,0,256,0S123.333,26.629,74.98,74.98C26.629,123.333,0,187.62,0,256 s26.629,132.667,74.98,181.02C123.333,485.371,187.62,512,256,512s132.667-26.629,181.02-74.98 C485.371,388.667,512,324.38,512,256S485.371,123.333,437.02,74.98z M256,70c30.327,0,55,24.673,55,55c0,30.327-24.673,55-55,55 c-30.327,0-55-24.673-55-55C201,94.673,225.673,70,256,70z M326,420H186v-30h30V240h-30v-30h110v180h30V420z"/> </g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg>';
	}

	public static function getValidIcon() {
		return '<i class="fa fa-check-circle" aria-hidden="true" style="color:#059862;"></i>';
	}

	public static function getInValidIcon() {
		return '<i class="fa fa-times-circle" aria-hidden="true" style="color:#F44336;"></i>';
	}

	/* Get Insurance related data from datatable by passing different parameters */
	public static function getInsuranceDataById($pid, $ins_id, $provider_id = '', $order_by = '`date` DESC', $type = '') {
			if(!$pid || !$ins_id) {
			 	return false;
			}
			$binds = array();
			$query = 'SELECT ins.*, ic.`id` AS ic_id, ic.`name`, ic.`attn`, ic.`cms_id`, ic.`alt_cms_id`, ic.`ins_type_code`, ad.`line1`, ad.`line2`,  '.
				'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
				'ph.`prefix`, ph.`number`';
			if($provider_id) {
				$query .= ', us.`id` AS pr_id, us.`fname` AS pr_fname, us.`lname` AS pr_lname, us.`federaltaxid` AS pr_federaltaxid, us.`upin` AS pr_upin, us.`npi` AS pr_npi, us.`facility_id` AS pr_facility_id';
			}	

			$query .= ' FROM insurance_data AS ins '.
				'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
				'LEFT JOIN phone_numbers AS ph ON '.
				'(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
				'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) ';
			
			if($provider_id) {
				$query .= 'LEFT JOIN users AS us ON us.`id` = ? ';
				$binds[] = $provider_id;
			}

			$query .= '	WHERE ins.`id` = ? AND ins.`pid` = ? ';	
			$binds[] = $ins_id;
			$binds[] = $pid;
			if($type) {
				$query .= ' AND ins.`type` = ? ';
				$binds[] = $type;
			}
			$query .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 '.
				'AND ins.`date` IS NOT NULL AND ins.`date` != "0000-00-00" '.
				'AND ins.`date` != "" ';
			$query .= 'ORDER BY ' . $order_by;
			
			
			$fres = sqlStatement($query, $binds);
			$data = array();
			while($row = sqlFetchArray($fres)) {
				$data[] = $row;
			}
			return $data;		
	}

	/* Get Insurance related data from datatable by passing different parameters */
	public static function getCoverageEligibilityHistoryData($pid, $case_id, $cnt, $ins_id = '', $policy_number = '', $group_number = '', $effective_date ='', $provider_id = '', $order_by = '`created_at` DESC') {
		$response = array();

		if(!empty($pid)  || !empty($cnt) || !empty($case_id)) {
			$binds = array();
			$query = 'SELECT ceh.*, ic.`name` ic_name, us.`fname` AS pr_fname, us.`lname` AS pr_lname FROM coverage_eligibility_history AS ceh ';
			$query .='LEFT JOIN insurance_companies AS ic ON ceh.`ins_id` = ic.`id` ';
			$query .='LEFT JOIN users AS us ON ceh.`provider_id` = us.`id` ';
			$query .= ' WHERE ceh.`pid` = ? AND ceh.`case_id` = ? AND ceh.`cnt` = ? ';
			$binds[] = $pid;
			$binds[] = $case_id;
			$binds[] = $cnt;

			if($policy_number) {
				$query .= ' AND ins_id = ? ';
				$binds[] = $ins_id;
			}

			if($policy_number) {
				$query .= ' AND policy_number = ? ';
				$binds[] = $policy_number;
			}

			if($group_number) {
				$query .= ' AND group_number = ? ';
				$binds[] = $group_number;
			}

			if($effective_date) {
				$query .= ' AND effective_date = ? ';
				$binds[] = $effective_date;
			}

			if($provider_id) {
				$query .= ' AND provider_id = ? ';
				$binds[] = $provider_id;
			}
			$query .= 'ORDER BY ' . $order_by;

			$data = sqlStatement($query, $binds);
			$response = array();
			while($row = sqlFetchArray($data)) {
				$row['coverage_data_obj'] = (array) json_decode($row['coverage_data']);
				$row['coverage_data'] = (array) json_decode($row['coverage_data']);
				$response[] = $row;
			}
		}

		return $response;
	}

	/* Get Coverage Eligbility verification data from datatable by passing different parameters */
	public static function getCaseEligibilityVerificationData($pid, $case_id, $cnt, $ins_id = '', $policy_number = '', $group_number = '', $effective_date ='', $provider_id = '') {
		$response = array();

		if(!empty($pid)  || !empty($cnt) || !empty($case_id)) {
			$binds = array();
			$query = 'SELECT * FROM coverage_eligibility WHERE pid = ? AND case_id = ? AND cnt = ? ';
			$binds[] = $pid;
			$binds[] = $case_id;
			$binds[] = $cnt;

			if($policy_number) {
				$query .= ' AND ins_id = ? ';
				$binds[] = $ins_id;
			}

			if($policy_number) {
				$query .= ' AND policy_number = ? ';
				$binds[] = $policy_number;
			}

			if($group_number) {
				$query .= ' AND group_number = ? ';
				$binds[] = $group_number;
			}

			if($effective_date) {
				$query .= ' AND effective_date = ? ';
				$binds[] = $effective_date;
			}

			if($provider_id) {
				$query .= ' AND provider_id = ? ';
				$binds[] = $provider_id;
			}

			$data = sqlStatement($query, $binds);
			$response = array();
			while($row = sqlFetchArray($data)) {
				$row['dataRaw'] = (array) json_decode($row['raw_data']); 
				$response[] = $row;
			}
		}

		return $response;
	}

	/* Help to call verification api */
	public static function handleCallVerificationAPI($returnData) {
		$response = array(
			'success' => 0 , 'error' => 'Something wrong'
		);

		$i = 0;
		do {
		    $response = self::callVerificationAPI($returnData);
	        $checkStatus = isset($response['status']) &&  $response['status'] == "In Progress" ? true : false;

	        if($checkStatus == true) {
	        	sleep(3);
	        }
		    $i++;
		} while ($i < 4 && $checkStatus === true);

		return $response;
	}

	/* Help to call verification api for cron */
	public static function cronHandleCallVerificationAPI($returnData) {
		$response = array(
			'success' => 0 , 'error' => 'Something wrong'
		);

		$response = self::callVerificationAPI($returnData);

		return $response;
	}

	public static function replaceSpecialCharacters($value) {
		return str_replace('-', '', $value);
	}

	/* Help to call verification api and gives the eligibility status */
	public static function callVerificationAPI($insData) {
		$configList = self::getConfigVars();
		$response = array(
			'success' => 0 , 'error' => 'Something wrong'
		);

		if(isset($insData) && is_array($insData) && !empty(trim($insData[0]['alt_cms_id']))) {

			$verificationTokenResponce = self::getVerificationToken($configList->client, $configList->secret);

			if($verificationTokenResponce && $verificationTokenResponce['success'] == 1) {
				$apiType = attr($verificationTokenResponce['data']->token_type);
				$apiToken = attr($verificationTokenResponce['data']->access_token);

				/*Uncomment code for pass gender code*/
				// if($insData[0]['subscriber_sex'] == "Male") {
				// 	$insData[0]['patientGenderCode'] = 'M';
				// } else if($insData[0]['subscriber_sex'] == "Female") {
				// 	$insData[0]['patientGenderCode'] = 'F';
				// }

				if(!isset($insData[0]['serviceType'])) {
					$insData[0]['serviceType'] = $GLOBALS['serviceType'];
				}

				$insData[0]['raw_policy_number'] = str_replace('-', '', self::getFieldValue($insData[0], 'policy_number'));
				$insData[0]['raw_group_number'] = self::replaceSpecialCharacters(self::getFieldValue($insData[0], 'group_number'));

				/*Get Coverage response from api*/
				$data = self::getVerificationDataByAPI($apiType.' '.$apiToken, $insData[0]);

				if($data['success'] == 1) {
					/*Get reponce with eligbility status*/
					$response = self::verifiyData($insData[0], $data['data'], $apiType.' '.$apiToken);
				} else if($data['success'] == 2) {
					/*Get reponce with eligbility status*/
					$response = self::verifiyErrorData($insData[0], $data['errorObj']);
				} else {
					$response = $data;
				}
			} else {
				$response = $verificationTokenResponce;
			}
		} else {
			$response = array(
				'success' => 0 , 
				'error' => "Eligibility check can't perform, Because payer id is empty.",
				'not_performed' => true,
			);
		}

		return $response;
	}

	/*Get coverage data by id*/
	public static function getCoverageById($token, $coverage_id) {
		if(!$token || empty($coverage_id)) {
			return array('success' => 0 , 'error' => 'Something went wrong');
		}

		$url = "https://api.availity.com/availity/v1/coverages/".$coverage_id;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "authorization: ".$token
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		$responseData = json_decode($response);
		if ($err) {
		  	return array('success' => 0 , 'error' => $err);
		} else if(isset($responseData) && isset($responseData->errors) && !empty($responseData->errors)) {
			return array('success' => 0 , 'error' => $responseData->userMessage, 'errorObj' => $responseData);
		} else {
		  	return array('success' => 1 , 'data' => $responseData);
		}
	}

	/*Get verification token*/
	public static function getVerificationDataByAPI($token, $obj) {
		
		if(!$token || !$obj || empty($obj)) {
			return array('success' => 0 , 'error' => 'Something went wrong');
		}

		$mapFieldList = array(
			'payerId' => 'alt_cms_id', 
			'providerNpi' => 'pr_npi',  
			'asOfDate' => 'asOfDate',
			'toDate' => 'toDate',
			'memberId' => 'raw_policy_number', 
			'patientLastName' => 'subscriber_lname', 
			'patientFirstName' => 'subscriber_fname', 
			'patientBirthDate' => 'subscriber_DOB', 
			'patientGender' => 'patientGenderCode', 
			'groupNumber' => 'raw_group_number',
			'serviceType' => 'serviceType'
		);

		if(isset($obj['alt_cms_id']) && !empty($obj['alt_cms_id'])) {
			$isTaxIdReq = self::isTaxIdRequired($obj['alt_cms_id']);

			if($isTaxIdReq === true) {
				$mapFieldList['providerTaxId'] = 'pr_federaltaxid';
			}
		}

		$qtrObj = array();
		foreach ($mapFieldList as $key => $field) {
			if(isset($obj[$field]) && !empty($obj[$field])) {
				if ($key == "patientBirthDate") {
					$date = date("Y-m-d",strtotime($obj[$field]));
					$time = date("H:i",strtotime($obj[$field]));
					$qtrObj[$key] = $date;
				} else {
					$qtrObj[$key] = $obj[$field];
				} 
			}  else if($key == "asOfDate") {
				$date = date("Y-m-d");
				$time = date("H:i");
				$qtrObj[$key] = $date."T".$time;
			} else if($key == "toDate") {
				$date = date("Y-m-d");
				$time = date("H:i");
				$qtrObj[$key] = $date."T".$time;
			}
		}

		$queryString = http_build_query($qtrObj);
		$url = "https://api.availity.com/availity/v1/coverages?".$queryString;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "authorization: ".$token
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		$responseData = json_decode($response);

		if ($err) {
		  	return array('success' => 0 , 'error' => $err);
		} else if(isset($responseData) && self::checkIsRequestCompleted($responseData) !== false) {
			return array('success' => 2 , 'error' => "Validation Error", 'errorObj' => self::checkIsRequestCompleted($responseData));
		} else if(isset($responseData) && isset($responseData->errors) && !empty($responseData->errors)) {
			return array('success' => 2 , 'error' => $responseData->userMessage, 'errorObj' => $responseData);
		} else {
		  	return array('success' => 1 , 'data' => $responseData);
		}
	}

	/*Get verification token*/
	public static function getVerificationToken($client_id, $client_secret, $scope = 'hipaa') {
		
		if(!$client_id || !$client_secret) {
			return array('success' => 0 , 'error' => 'Something went wrong');
		}

		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.availity.com/availity/v1/token",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=".$client_id."&client_secret=".$client_secret."&scope=".$scope."",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return array('success' => 0 , 'error' => $err);
		} else {
		  return array('success' => 1 , 'data' => json_decode($response));
		}
	}

	public static function checkIsValidationErrors($data) {
		if(is_object($data) || is_array($data)) {
			$coverageObj = (array)$data;
			if(isset($coverageObj['statusCode']) && ($coverageObj['statusCode'] !== "3" && $coverageObj['statusCode'] !== "4")) {
				if(isset($coverageObj['validationMessages']) && count($coverageObj['validationMessages']) > 0) {
					return true;
				}
			}
		}

		return false;
	}

	public static function checkIsRequestCompleted($data) {
		if(is_object($data) || is_array($data)) {
			$dataObj = (array)$data;

			if(isset($dataObj['coverages']) && count($dataObj['coverages']) > 0) {
				$coverageObj = (array)$dataObj['coverages'][0];

				if(self::checkIsValidationErrors($coverageObj) === true) {
					return $dataObj['coverages'][0];
				}
			}

		}

		return false;
	}

	/*Help to generate error string*/
	public static function generateErrorString($errors, $flage = true) {
		$newLine = $flage == true ? "\n" : "<br/>";
		$openLine = $flage == false ? '<div class="errors-container">' : "";
		$closeLine = $flage == false ? '</div>' : "";
		$errorStr = "";

		if(is_object($errors) || is_array($errors)) {
			$errorObj = (array)$errors;

			if(isset($errorObj)) {
				if(isset($errorObj['userMessage'])) {
					$errorStr = "Error Message: ".$errorObj['userMessage'].$newLine;
				}

				if(isset($errorObj['errors'])) {
					if(is_array($errorObj['errors'])) {
						$errorStr .= $openLine;
						$errorsObj = (array)$errorObj['errors'];
						foreach ($errorsObj as $key => $item) {
							$itemArray = (array)$item;
							$errorStr .= ($key+1).". ".$itemArray['field']." - ".$itemArray['errorMessage'].$newLine;
						}
						$errorStr .= $closeLine;
					} else {
						$errorStr .= json_encode($errorObj['errors']);
					}
				}

				if(isset($errorObj['validationMessages'])) {
					if(is_array($errorObj['validationMessages'])) {
						foreach ($errorObj['validationMessages'] as $key => $validationMsg) {
							if(is_object($validationMsg) || is_array($validationMsg)) {
								$validationMsgObj = (array)$validationMsg;
								$errorStr .= $openLine;
								$errorStr .= ($key+1).". ".$validationMsgObj['errorMessage']." (Code ".$validationMsgObj['code'].")".$newLine;
								$errorStr .= $closeLine;
							}
						}
					} else {
						$errorStr .= json_encode($errorObj['validationMessages']);
					}
				}
			}
		} else {
			$errorStr = "Error: ".json_encode($errors);
		}

		return $errorStr;
	} 

	/*Help to verifiy error data*/
	public static function verifiyErrorData($sourceData, $responceData) {
		$erroObj = isset($responceData) ? $responceData : "";
		$response = array(
			'success' => 1 ,
			'policy_number' => isset($sourceData['policy_number']) ? $sourceData['policy_number'] : '',
			'group_number' => isset($sourceData['group_number']) ? $sourceData['group_number'] : '',
			'effective_date' => isset($sourceData['date']) ? $sourceData['date'] : '',
			'provider_id' => isset($sourceData['pr_id']) ? $sourceData['pr_id'] : '',
			'data' => 'Sucess',
			'coverage_id' => '',
			'coverage_data' => $responceData,
			'plan_raw' => '',
			'plan' => '',
			'plan_status' => '',
			'eligibility_status' => self::eligibilityStatus(),
			'userMessage' => self::generateErrorString($erroObj)
		);

		return $response;
	}

	/*Help to verifiy coverage data*/
	public static function verifiyData($sourceData, $responceData, $token) {
		$response = array(
			'success' => 0 ,
			'error' => 'Something Wrong'
		);

		if(isset($sourceData) && isset($responceData)) {
			if(isset($responceData->coverages) && is_array($responceData->coverages)) {
				if(count($responceData->coverages) > 0) {
					$currentCoverage = isset($responceData->coverages[0]) ? $responceData->coverages[0] : array();

					$response = array(
						'success' => 1 ,
						'policy_number' => isset($sourceData['policy_number']) ? $sourceData['policy_number'] : '',
						'group_number' => isset($sourceData['group_number']) ? $sourceData['group_number'] : '',
						'effective_date' => isset($sourceData['date']) ? $sourceData['date'] : '',
						'provider_id' => isset($sourceData['pr_id']) ? $sourceData['pr_id'] : '',
						'data' => 'Sucess',
						'coverage_id' => $currentCoverage->id,
						'coverage_data' => $currentCoverage,
						'plan_raw' => '',
						'plan' => '',
						'plan_status' => '',
						'eligibility_status' => self::eligibilityStatus()
					);

					if($currentCoverage->statusCode == "3" || $currentCoverage->statusCode == "4") {
						foreach ($currentCoverage->plans as $k => $plan) {
							//$pGroupNumber = isset($plan->groupNumber) ? self::replaceSpecialCharacters($plan->groupNumber) : "";
							//if($sourceData['raw_group_number'] == $pGroupNumber) {
								$planRaw = $plan;
								$plan_name = isset($plan->status) ? $plan->status : '';
								$plan_status = isset($plan->statusCode) ? $plan->statusCode : '';
								
								$response = array(
									'success' => 1 ,
									'policy_number' => isset($sourceData['policy_number']) ? $sourceData['policy_number'] : '',
									'group_number' => isset($sourceData['group_number']) ? $sourceData['group_number'] : '',
									'effective_date' => isset($sourceData['date']) ? $sourceData['date'] : '',
									'coverage_id' => $currentCoverage->id,
									'provider_id' => isset($sourceData['pr_id']) ? $sourceData['pr_id'] : '',
									'data' => 'Sucess',
									'plan_raw' => $planRaw,
									'plan' => $plan_name,
									'plan_status' => $plan_status,
								);

								$response['eligibility_status'] = self::eligibilityStatus((array)$response);

								if($response['eligibility_status'] == "eligible" && !empty($currentCoverage->id)) {
									$coverageData = self::getCoverageById($token, $currentCoverage->id);
									$response['coverage_data'] = isset($coverageData['success']) && $coverageData['success'] == 1 ? $coverageData['data'] : array();
								} else {
									$response['coverage_data'] = $currentCoverage;
								}

								break;
							//}
						}
					} else {
						$response['status'] = $currentCoverage->status;
						$response['statusCode'] = $currentCoverage->statusCode;
						$response['statusMsg'] = 'Coverage Status: '.$currentCoverage->status.'';
						$response['coverage_data'] = $currentCoverage;
					}
				}
			}
		}

		return $response;
	}

	/*Manage Operation for update and add coverage verification data*/
	public static function manageHistoryUpdate($pid, $case_id, $preparedData, $mode) {
		$cnt = self::getFieldValue($preparedData, 'cnt');
		$field_ins_id = self::getFieldValue($preparedData, 'ic_id');
		$field_policy_number = self::getFieldValue($preparedData, 'policy_number');
		$field_group_number = self::getFieldValue($preparedData, 'group_number');
		$field_provider_id = self::getFieldValue($preparedData, 'provider_id');
		$field_effective_date = self::getFieldValue($preparedData, 'effective_date');
		$field_coverage_id = self::getFieldValue($preparedData, 'coverage_id');
		$field_coverage_data = self::getFieldValue($preparedData, 'coverage_data');
		$field_plan = self::getFieldValue($preparedData, 'plan');
		$field_plan_status = self::getFieldValue($preparedData, 'plan_status');
		$field_uid = self::getFieldValue($preparedData, 'uid');

		try {

			if($cnt != '' && $field_ins_id != '' && $field_coverage_data != '') {
				$preparedData = array(
					'pid' => $pid,
					'uid' => $field_uid,
					'case_id' => $case_id,
					'ins_id' => $field_ins_id,
					'cnt' => $cnt,
					'policy_number' => $field_policy_number,
					'group_number' => $field_group_number,
					'provider_id' => $field_provider_id,
					'effective_date' => $field_effective_date,
					'coverage_id' => $field_coverage_id,
					'coverage_data' => $field_coverage_data, 
					'plan' => $field_plan, 
					'plan_status' => $field_plan_status
				);
				self::insertElgbHistoryData($preparedData);
			}

		} catch (Exception $e) {
	    	return false;
		}
	}

	/*Manage Operation for update and add coverage verification data*/
	public static function manageUpdateData($pid, $runBy, $case_id, $cnt, $response) {
		try {
			if(is_array($response) && $case_id != "" && $cnt != '' && $pid != '') {
				$celgvData = self::getCaseEligibilityVerificationData($pid, $case_id, $cnt);
				$mode = ($celgvData && is_array($celgvData) && count($celgvData) > 0) ? 'update' : 'new';
				$raw_data = self::getFieldValue($response, 'plan_raw');

				$preparedData = array(
					'pid' => $pid,
					'uid' => $runBy,
					'case_id' => $case_id,
					'ins_id' => self::getFieldValue($response, 'ins_id'),
					'ic_id' => self::getFieldValue($response, 'ic_id'),
					'cnt' => $cnt,
					'policy_number' =>  self::getFieldValue($response, 'policy_number'),
					'group_number' =>  self::getFieldValue($response, 'group_number'),
					'provider_id' => self::getFieldValue($response, 'provider_id'),
					'effective_date' => self::getFieldValue($response, 'effective_date'),
					'coverage_id' => self::getFieldValue($response, 'coverage_id'),
					'raw_data' => !empty($raw_data) ? json_encode($raw_data) : '',
					'coverage_data' => json_encode(self::getFieldValue($response, 'coverage_data')),
					'plan' => self::getFieldValue($response, 'plan'), 
					'plan_status' => self::getFieldValue($response, 'plan_status'),
					'updated_at' => date("Y-m-d H:i:s")
				);

				self::manageOperations($pid, $case_id, $preparedData, $mode);
				self::manageHistoryUpdate($pid, $case_id, $preparedData, $mode);
			}
		} catch (Exception $e) {
	    	return false;
		}
	}

	/*Help to manage operation and prepare data set for database related operations*/
	public function updateCaseInsuranceEligibilityData($pid, $case_id, $data) {
		$cnt = 1;

		while($cnt < 4) {

				$isExists = isset($data['ins_raw_data'.$cnt]) ? true : false;
				$dataResponse = isset($data['ins_raw_data'.$cnt]) ? (array) json_decode($data['ins_raw_data'.$cnt]) : array();

				if(($isExists == false) || (isset($dataResponse['action']) && $dataResponse['action'] == "1")) {
					self::manageUpdateData($pid, '', $case_id, $cnt, $dataResponse);
				}

			$cnt++;
		}
	}

	/*Check field value*/
	public static function getFieldValue($field, $name) {
		if(isset($field[$name]) && !empty($field[$name]) && $field[$name] != '') {
			return $field[$name];
		}
		return '';
	}

	/*Manage Database related operations for save and updated eligibility data*/
	public static function manageOperations($pid, $case_id, $data, $mode = 'new') {
		try {

			$field_ins_id = self::getFieldValue($data, 'ins_id');
			$field_policy_number = self::getFieldValue($data, 'policy_number');
			$field_group_number = self::getFieldValue($data, 'group_number');
			$field_provider_id = self::getFieldValue($data, 'provider_id');
			$field_effective_date = self::getFieldValue($data, 'effective_date');
			$field_coverage_id = self::getFieldValue($data, 'coverage_id');
			$field_raw_data = self::getFieldValue($data, 'raw_data');
			$field_plan = self::getFieldValue($data, 'plan');
			$field_plan_status = self::getFieldValue($data, 'plan_status');
			$field_uid = self::getFieldValue($data, 'uid');

			if($mode == 'update') {
				if($field_ins_id != '') {
					$preparedData = array(
						'uid' => $field_uid,
						'ins_id' => $field_ins_id,
						'policy_number' => $field_policy_number,
						'group_number' => $field_group_number, 
						'provider_id' => $field_provider_id,
						'effective_date' => $field_effective_date,
						'coverage_id' => $field_coverage_id,
						'raw_data' => $field_raw_data, 
						'plan' => $field_plan, 
						'plan_status' => $field_plan_status
					);

					if(isset($data['updated_at'])) {
						$preparedData['updated_at'] = $data['updated_at'];
					}

					self::updateElgbData($pid, $case_id, $data['cnt'], $preparedData);
				} else {
					self::deleteElgbData($pid, $case_id, $data['cnt']);
				}

			} else {
				if($field_ins_id != '') {
					$preparedData = array(
						'pid' => $pid,
						'uid' => $field_uid,
						'case_id' => $case_id,
						'ins_id' => $field_ins_id,
						'cnt' => isset($data['cnt']) ? $data['cnt'] : '',
						'policy_number' => $field_policy_number,
						'group_number' => $field_group_number,
						'provider_id' => $field_provider_id,
						'effective_date' => $field_effective_date,
						'coverage_id' => $field_coverage_id,
						'raw_data' => $field_raw_data, 
						'plan' => $field_plan, 
						'plan_status' => $field_plan_status
					);
					self::insertElgbData($preparedData);
				}
			}

		} catch (Exception $e) {
	    	return false;
		}
	}

	/*Updated Action for coverage elibility data*/
	public static function updateElgbData($pid, $case_id, $cnt, $data ) {
		if(!empty($pid) && !empty($case_id) && !empty($cnt)) {
			$sql = "UPDATE coverage_eligibility SET ";
			$bind = array();

			$i = 0;
			foreach ($data as $key => $value) {
				if($i == 0) {
					$sql .= $key." = ? ";
					$bind[] = $value;
				} else {
					$sql .= ", ".$key." = ? ";
					$bind[] = $value;
				}
				
				$i++;
			}

			$sql .= "WHERE pid = ? AND case_id = ? AND cnt = ? ";
			$bind[] = $pid;
			$bind[] = $case_id;
			$bind[] = $cnt;

			return sqlQuery($sql, $bind);
		}

		return false;
	}

	/*Insert Action for coverage elibility data*/
	public static function insertElgbHistoryData($data) {
		$sql = "INSERT INTO coverage_eligibility_history ( ";
		$val = " VALUES (";

		$bind = array();

		$i = 0;
		foreach ($data as $key => $value) {
			if($i == 0) {
				$sql .= $key." ";
				$val .= " ?";
				$bind[] = $value;
			} else {
				$sql .= ", ".$key." ";
				$val .= ",?";
				$bind[] = $value;
			}
			
			$i++;
		}

		$sql .= " )";
		$val .= " )";

		$sql = $sql.$val;

		return sqlQuery($sql, $bind);
	}

	/*Insert Action for coverage elibility data*/
	public static function insertElgbData($data) {
		$sql = "INSERT INTO coverage_eligibility ( ";
		$val = " VALUES (";

		$bind = array();

		$i = 0;
		foreach ($data as $key => $value) {
			if($i == 0) {
				$sql .= $key." ";
				$val .= " ?";
				$bind[] = $value;
			} else {
				$sql .= ", ".$key." ";
				$val .= ",?";
				$bind[] = $value;
			}
			
			$i++;
		}

		$sql .= " )";
		$val .= " )";

		$sql = $sql.$val;

		return sqlQuery($sql, $bind);
	}

	/*Delete Action for coverage elibility data*/
	public static function deleteElgbData($pid, $case_id, $cnt) {
		if(!empty($pid) && !empty($case_id) && !empty($cnt)) {
			$sql = "DELETE FROM coverage_eligibility WHERE pid = ? AND case_id = ? AND cnt = ? ";
			$bind = array($pid, $case_id, $cnt);
			return sqlQuery($sql, $bind);
		}

		return false;
	}

	/*Help to get calendar events data*/
	public static function getPostcalendar_events($date = '', $between = false) {
		$response = array();

		if(!empty($date)) {

			$event_date = !empty($date) ? $date : date('Y-m-d', strtotime("tomorrow"));

			$binds = array();
			$query = 'SELECT pv.`pc_eid`, pv.`pc_catid`, pv.`pc_pid`, pv.`pc_title`, pv.`pc_eventDate`, pv.`pc_duration`, pv.`pc_startTime`, pv.`pc_endTime`, pv.`pc_case`, fc.`case_description`, fc.`facility`, fc.`ins_data_id1`, fc.`ins_data_id2`, fc.`ins_data_id3`, fc.`provider_id`'.
			'FROM openemr_postcalendar_events AS pv '.
			'LEFT JOIN form_cases AS fc ON pv.`pc_case` = fc.`id` ';
			
			if($between === false) {
				$query .='WHERE pv.`pc_eventDate` = ? ';
				$binds[] = $event_date;
			} else {
				$query .='WHERE pv.`pc_eventDate` between ? AND ? ';
				$binds[] = $event_date[0];
				$binds[] = $event_date[1];
			}

			$data = sqlStatement($query, $binds);
			$response = array();
			while($row = sqlFetchArray($data)) {
				$response[] = $row;
			}
		}

		return $response;
	}

	public static function getEligiblityAsText($status) {
		if($status == "eligible") {
			return "Eligible";
		} else {
			return "Not Eligible";
		}
	}

	public static function getCoverageEligibilityDataForCalender($pc_case='') {
		if(!$pc_case || empty($pc_case)) {
		 	return array();
		}

		$query = 'SELECT * FROM coverage_eligibility WHERE case_id IN ('.$pc_case.')';
		$data = sqlStatement($query);
		$response = array();
		while($row = sqlFetchArray($data)) {
			$row['dataRaw'] = (array) json_decode($row['raw_data']); 
			$response['case_'.$row['case_id']]['cnt'.$row['cnt']] = $row;
		}

		return $response;
	}

	public static function getInsuranceDataForCalender($ins_id='') {
		if(!$ins_id || empty($ins_id)) {
		 	return array();
		}

		$query = "SELECT ins.*, ic.`id` AS ic_id, ic.`name` FROM insurance_data AS ins ";
		$query .= "LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` ";
		$query .= "WHERE ins.`id` IN (".$ins_id.") AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 ".
				"AND ins.`date` IS NOT NULL AND ins.`date` != '0000-00-00' ".
				"AND ins.`date` != ''";
		$fres = sqlStatement($query);
		$data = array();
		while($row = sqlFetchArray($fres)) {
			$data['ins_'.$row['id']] = $row;
		}
		return $data;
	}

	public static function getEligibilityDataForPostCalender($id) {
		$response = array();

		if(!empty($id)) {
			$binds = array();
			$query = 'SELECT pv.`pc_eid`, pv.`pc_catid`, pv.`pc_pid`, pv.`pc_title`, pv.`pc_eventDate`, pv.`pc_duration`, pv.`pc_startTime`, pv.`pc_endTime`, pv.`pc_case`, fc.`case_description`, fc.`facility`, fc.`ins_data_id1`, fc.`ins_data_id2`, fc.`ins_data_id3`, fc.`provider_id` '.
			'FROM openemr_postcalendar_events AS pv '.
			'LEFT JOIN form_cases AS fc ON pv.`pc_case` = fc.`id` '.
			'LEFT JOIN users AS us ON us.`id` = fc.`provider_id` '.
			'WHERE pv.`pc_eid` IN ('.$id.') ';

			$data = sqlStatement($query);
			$response = array('events' => array());
			$ids = array();
			$pc_cases = array();

			while($row = sqlFetchArray($data)) {
				$cnt = 1;
				while($cnt < 4) {
					if(isset($row['ins_data_id'.$cnt]) && !empty($row['ins_data_id'.$cnt]) && !in_array($row['ins_data_id'.$cnt], $ids)) {
						$ids[] = $row['ins_data_id'.$cnt];
					}
					$cnt++;
				}

				if(isset($row['pc_case']) && !empty($row['pc_case']) && !in_array($row['pc_case'], $pc_cases)){
					$pc_cases[] = $row['pc_case'];
				}
				
				$response['events']['event'.$row['pc_eid']] = $row;
			}

			$ids_str = implode(",", $ids);
			$response['ins_data'] = self::getInsuranceDataForCalender($ids_str);

			$pc_cases_str = implode(",", $pc_cases);
			$response['coverage_eligibility'] = self::getCoverageEligibilityDataForCalender($pc_cases_str);
		}

		return $response;
	}

	public static function getEleContentForPostCalender($event, $data) {
		$returnData = array();
		if(!empty($event)) {
			if(isset($event['eid']) && isset($data['events']) && isset($data['events']['event'.$event['eid']])) {
				$cEvent = $data['events']['event'.$event['eid']];
				if($cEvent['pc_pid'] == $event['pid']) {
					$proId = $cEvent['provider_id'] != 0 ? $cEvent['provider_id'] : '';
					
					$cnt = 1;
					while($cnt < 4) {
						if(!empty($proId) && isset($data['ins_data']) && isset($data['ins_data']['ins_'.$cEvent['ins_data_id'.$cnt]])) {
							$cInsData = $data['ins_data']['ins_'.$cEvent['ins_data_id'.$cnt]];

							if(!empty($cInsData)) {
								$effective_date = strtotime($cInsData['date']);
								$cEligibilityData = array();

								if(isset($data['coverage_eligibility'])) {
									$celgData = $data['coverage_eligibility'];
									if(isset($celgData['case_'.$cEvent['pc_case']]) && $celgData['case_'.$cEvent['pc_case']]['cnt'.$cnt]) {
										$eligibilityData = $celgData['case_'.$cEvent['pc_case']]['cnt'.$cnt];
										$effective_date1 = strtotime($eligibilityData['effective_date']);

										if($eligibilityData['ins_id'] == $cEvent['ins_data_id'.$cnt] && $eligibilityData['policy_number'] == $cInsData['policy_number'] && $eligibilityData['group_number'] == $cInsData['group_number'] && $effective_date1 == $effective_date) {

											$cEligibilityData = $eligibilityData;
										}
									}

									$verificationStatus = "not_eligible";
									if($cEligibilityData && is_array($cEligibilityData) && count($cEligibilityData) > 0) {
										$verificationStatus = self::initStatusVerification($cEligibilityData);
									}

									$cEvent['insuranceData'][] = array(
										'id' => $cInsData['id'],
										'name' => $cInsData['name'],
										'policy_number' => $cInsData['policy_number'],
										'group_number' => $cInsData['group_number'],
										'date' => $cInsData['date'],
										'eligibility_status' => $verificationStatus,
										'updated_at' => isset($cEligibilityData['updated_at']) ? $cEligibilityData['updated_at'] : ''
									);
								}
							}
						}
						$cnt++;
					}
				}

				$returnData[] = $cEvent;
			}
		}

		return $returnData;
	}

	public static function avabilityHTMLContent($data) {
		$innerHTML = '';
		$titleAttr = '';

		foreach ($data as $index => $obj) {
			$eleId = date('Ymd', strtotime($obj['pc_eventDate']));
			$eleId .= '-'.$obj['pc_eid'].'-';
			$icount = 0;
			foreach ($obj['insuranceData'] as $objKey => $ins) {
				$updated_at = $ins['updated_at'];
				$ins_name = htmlspecialchars($ins['name'], ENT_QUOTES);

				$innerHTML .= $icount != 0 ? ',<span>' : '<span>';
				$innerHTML .= self::getEligibilityText($ins['eligibility_status']);
				$innerHTML .= '<span>'.$ins_name.'</span><span style="display:none"> ('.$ins['policy_number'].')</span>';

				if(!empty($updated_at)) {
					$innerHTML .= '<i> ('.$updated_at.')</i>';
				}

				$innerHTML .= '</span>';

				$titleAttr .= $ins_name." - ".$ins['policy_number']." - (".self::getEligiblityAsText($ins['eligibility_status']).") ".$updated_at."\n";
			}
		}

		$finalHTML = "";
		if($innerHTML != "") {
			$finalHTML = '<span> -</span><span class="elg_container">'.$innerHTML.'</span>';
		}

		$finalTitle = "";
		if($titleAttr != "") {
			$finalTitle = "\n\s\n--Eligiblity Info--\n";
			$finalTitle .= $titleAttr;
		}

		return array('content' => $finalHTML, 'title' => $finalTitle);
	}

	/*Help to set eligbility content for calendar events*/
	public function eligbContentLoading($date = '') {
		$currentDate = date('Y-m-d', strtotime($date));
		$eventData = self::getPostcalendar_events($currentDate);

		$returnObj = array();
		if(!empty($eventData) && count($eventData) > 0) {
			$returnObj = self::getEligbilityContentForCalendar($eventData);
		}

		?>
		<style type="text/css">
			.elg_container {
				margin-bottom: 5px;
				vertical-align: text-top;
				line-height: 21px;
			}

			.elg_container .svg-correct,
			.elg_container .svg-incorrect {
				display: inline-block;
			    height: 14px;
			    vertical-align: top;
			    margin-top: 1px;
			}

			.elg_container .svg-correct > svg,
			.elg_container .svg-incorrect > svg {
				height: 11pt!important;
				vertical-align: top;
			}
			.infoText svg {
				vertical-align: middle;
			}
		</style>
		<script type="text/javascript">
		<?php
			foreach ($returnObj as $index => $obj) {
				$eleId = date('Ymd', strtotime($obj['pc_eventDate']));
				$eleId .= '-'.$obj['pc_eid'].'-';
				
				$innerHTML = '';
				$titleAttr = '';
				$icount = 0;
				foreach ($obj['insuranceData'] as $objKey => $ins) {
					$updated_at = $ins['updated_at'];
					$ins_name = htmlspecialchars($ins['name'], ENT_QUOTES);

					$innerHTML .= $icount != 0 ? ',<span>' : '<span>';
					$innerHTML .= self::getEligibilityText($ins['eligibility_status']);
					$innerHTML .= '<span>'.$ins_name.'</span><span style="display:none"> ('.$ins['policy_number'].')</span>';

					if(!empty($updated_at)) {
						$innerHTML .= '<i> ('.$updated_at.')</i>';
					}

					$innerHTML .= '</span>';

					$titleAttr .= ''.$ins_name.' - '.$ins['policy_number'].' - ('.self::getEligiblityAsText($ins['eligibility_status']).') '.$updated_at.'\n';

					$icount++;
					
				}

				?>
					var linkTitle = $('#<?php echo $eleId ?>').find('span.appointment a:nth-child(2)');
					if(linkTitle.length > 0) {
						var titleStr = linkTitle.attr('title');
						titleStr += '\n\n';
						titleStr += '--Eligiblity Info--\n';
						titleStr += '<?php echo $titleAttr ?>';
						linkTitle.attr("title", titleStr);
					}

					$('#<?php echo $eleId ?>').append('<span> - <a href="#" class="infoText" title="<?php echo $titleAttr ?>"><?php echo self::getInfoSVG(); ?></a> -</span><span class="elg_container" id="elg_container<?php echo $eleId ?>"><?php echo $innerHTML ?></span>');
				<?php
			}
		?>
		</script>
		<?php
	}

	/*Help to get eligibility content for calendar*/
	public static function getEligbilityContentForCalendar($data) {
		$returnData = array();

		if(!empty($data)) {
			foreach ($data as $index => $item) {
				$innerData = $item;

				$cnt = 1;
				while($cnt < 4) {
					if(isset($item['pc_pid']) && isset($item['ins_data_id'.$cnt])) {
						$proId = $item['provider_id'] != 0 ? $item['provider_id'] : '';
						$insuranceData = self::getInsuranceDataById($item['pc_pid'], $item['ins_data_id'.$cnt], $proId);
						
						if($insuranceData && is_array($insuranceData) && count($insuranceData) > 0) {

							$effective_date = date('Y-m-d H:i:s', strtotime($insuranceData[0]['date']));
							$eligibilityData = self::getCaseEligibilityVerificationData($item['pc_pid'], $item['pc_case'], $cnt, $item['ins_data_id'.$cnt], $insuranceData[0]['policy_number'], $insuranceData[0]['group_number'], $effective_date);

							$verificationStatus = "not_eligible";
							if($eligibilityData && is_array($eligibilityData) && count($eligibilityData) > 0) {
								$verificationStatus = self::initStatusVerification($eligibilityData[0]);
							}

							$innerData['insuranceData'][] = array(
								'id' => $insuranceData[0]['id'],
								'name' => $insuranceData[0]['name'],
								'policy_number' => $insuranceData[0]['policy_number'],
								'group_number' => $insuranceData[0]['group_number'],
								'date' => $insuranceData[0]['date'],
								'eligibility_status' => $verificationStatus,
								'updated_at' => isset($eligibilityData[0]['updated_at']) ? $eligibilityData[0]['updated_at'] : ''
							);
						}
					}
					$cnt++;
				}

				$returnData[] = $innerData;
			}
		}
		return $returnData;
	}

	public static function lessThanOneMonthFromNow($unixTime, $lastUpdateDate) {
   		if(empty($unixTime)) {
   			return false;
   		}

   		if(empty($lastUpdateDate)) {
   			return true;
   		}
   		
   		$unixTimeMonthYear = date("m-Y", $unixTime);
   		$lastUpdateDateMonthYear = date("m-Y", $lastUpdateDate);

   		if($unixTimeMonthYear != $lastUpdateDateMonthYear && $lastUpdateDate < $unixTime) {
   			return true;
   		}

   		return false;
	}

	/*Is need to check eligibility status*/
	public static function checkIsNeedToCheck($insurenceData, $coverageData, $eventData = array()) {
		$cCoverage = is_array($coverageData) && count($coverageData) > 0 ? $coverageData[0] : array();
		$cInsurenceData = is_array($insurenceData) && count($insurenceData) > 0 ? $insurenceData[0] : array();
		$lastUpdateDate = isset($cCoverage['updated_at']) ? strtotime(date("Y-m-d", strtotime($cCoverage['updated_at']))) : "";
		
		$checkMonth = self::lessThanOneMonthFromNow(strtotime($eventData['pc_eventDate']), $lastUpdateDate);

		$responce = false;

		if($checkMonth == true) {
			$responce = true;
		} else if(!empty($lastUpdateDate)) {
			$status = self::eligibilityStatus($cCoverage);
			if($status == "not_eligible" || $status == "eligible") {
				$ins_ins_id = self::getFieldValue($cInsurenceData, 'id');
				$ins_policy_number = self::getFieldValue($cInsurenceData, 'policy_number');
				$ins_group_number = self::getFieldValue($cInsurenceData, 'group_number');
				$cov_ins_id = self::getFieldValue($cCoverage, 'ins_id');
				$cov_policy_number = self::getFieldValue($cCoverage, 'policy_number');
				$cov_group_number = self::getFieldValue($cCoverage, 'group_number');

				if($ins_ins_id != $cov_ins_id || $ins_policy_number != $cov_policy_number || $ins_group_number != $cov_group_number) {
					$responce = true;
				}
			}
		} else {
			$responce = true;
		}

		return $responce;
	}

	public static function generateDateForAPI($date) {
		$date = date("Y-m-d", strtotime($date));
		$time = date("H:i", strtotime($date));
		return $date."T".$time;;
	}

	/*Manage Cron operation to check eligiblity*/
	public static function cronUpdateCaseData($data) {
		$returnData = array(
			'records' => array(),
			'total_records' => 0,
			'total_updated_records' => 0,
			'eligibility_not_performed_records' => 0
		);


		$itemList = array();
		if(!empty($data)) {
			foreach ($data as $index => $item) {
				$innerData = $item;

				$cnt = 1;
				while($cnt < 4) {
					if($item['ins_data_id'.$cnt] != "" && $item['ins_data_id'.$cnt] != 0) {
						$celgvData = self::getCaseEligibilityVerificationData($item['pc_pid'], $item['pc_case'], $cnt);
						$currentDate = strtotime(date("Y-m-d"));

						if((is_array($celgvData) && count($celgvData) == 0) || (is_array($celgvData) && count($celgvData) > 0)) {

							$proId = $item['provider_id'] != 0 ? $item['provider_id'] : '';
							$providerId = self::getProviderId($proId, $item['ins_data_id'.$cnt], $item['pc_pid']);

							$insuranceData = self::getInsuranceDataById($item['pc_pid'], $item['ins_data_id'.$cnt], $providerId);

							$needToUpdate = self::checkIsNeedToCheck($insuranceData, $celgvData, $item);

							if($needToUpdate === true && $insuranceData && is_array($insuranceData) && count($insuranceData) > 0) {

								$eventDate = self::generateDateForAPI($item['pc_eventDate']);
								$insuranceData[0]['asOfDate'] = $eventDate;
								$insuranceData[0]['toDate'] = $eventDate;

								$itemId = $item['pc_case'] ."_". $item['ins_data_id'.$cnt] . "_" . $cnt;
								$itemList[$itemId] = array(
									'cnt' => $cnt,
									'event' => $item,
									'insurance_data' => $insuranceData
								);

								// $itemList[] = array(
								//	'cnt' => $cnt,
								// 	'event' => $item,
								// 	'insurance_data' => $insuranceData
								// );
							}
						}
					}

					$cnt++;
				}

				$returnData['records'][] = $innerData;
			}
		}

		$i = 0;
		do {

			$checkStatus = false;
			foreach ($itemList as $index => $item) {
				if(isset($item['insurance_data'])) {
					if(isset($item['isInProgress']) && $item['isInProgress'] == false) {
						continue;
					}

					$apiResponceData = self::cronHandleCallVerificationAPI($item['insurance_data']);
					$isInProgress = isset($apiResponceData['status']) &&  $apiResponceData['status'] == "In Progress" ? true : false;

					if($isInProgress == true) {
						$checkStatus = true;
					}

					$itemList[$index]['isInProgress'] = $isInProgress;
					$itemList[$index]['apiResponceData'] = $apiResponceData;
				}
			}

	        if($checkStatus == true) {
	        	sleep(30);
	        }

		    $i++;
		} while ($i < 2 && $checkStatus === true);


		foreach ($itemList as $index => $fitem) {
			$apiResponceData = $fitem['apiResponceData'];
			$item = $fitem['event'];
			$insuranceData = $fitem['insurance_data'];
			$cnt = $fitem['cnt'];

			if(is_array($apiResponceData) && $apiResponceData['success'] == 1) {
				$apiResponceData['ins_id'] = isset($item['ins_data_id'.$cnt]) ? $item['ins_data_id'.$cnt] : '';
				$apiResponceData['cnt'] = $cnt;
				$apiResponceData['case_id'] = $item['pc_case'];
				$apiResponceData['ic_id'] = isset($insuranceData[0]['ic_id']) ? $insuranceData[0]['ic_id'] : '';

				$oprResponce = self::manageUpdateData($item['pc_pid'], 'CRON_JOB', $item['pc_case'], $cnt, $apiResponceData);
				
				if($oprResponce !== false) {
					$returnData['total_updated_records'] = $returnData['total_updated_records'] + 1;
				}

				if(is_array($apiResponceData) && isset($apiResponceData['not_performed']) && $apiResponceData['not_performed'] == true) {
					$returnData['eligibility_not_performed_records'] = $returnData['eligibility_not_performed_records'] + 1;
				}

				$returnData['total_records'] = $returnData['total_records'] + 1;
			}
		}

		return $returnData;
	}

	/*Help to write log*/
	public static function WriteLog($data)
	{
	    global $log_folder_path;
	    
	    $filename = dirname( __FILE__, 2 )."/log/coverage_cronlog.log";
	    if (!$fp = fopen($filename, 'a')) {
	            print "Cannot open file ($filename)";
	            exit;
	    }
	    
	    $sdata = "";
	    
	    if (!fwrite($fp, $sdata.$data.$sdata)) {
	        print "Cannot write to file ($filename)";
	        exit;
	    }
	    
	    fclose($fp);
	}

	/*Init verification*/
	public static function initStatusVerification($caseEligbtData) {
		$status = self::eligibilityStatus($caseEligbtData);
		return $status;
	}

	/*Help to get eligibility status based on plan status*/
	public static function eligibilityStatus($data = null) {
		if(isset($data) && is_array($data)) {
			if(isset($data['plan_status']) && $data['plan_status'] == "1")  {
				return 'eligible';
			}
		}
		return 'not_eligible';
	}

	/*Help to get elibility text based on eligibility status*/
	public static function getEligibilityText($status) {
		if($status == "eligible") {
			return self::getValidIcon();
		} else if($status == "not_eligible") {
			return self::getInValidIcon();
		}
	}

	/*Help to check is taxid required*/
	public static function isTaxIdRequired($value = null) {
		if(isset($GLOBALS['rq_taxidforinsurance']) && !empty($GLOBALS['rq_taxidforinsurance']) && !empty($GLOBALS['rq_taxidforinsurance'])) {
			$req_taxidforinsurance = explode(",",$GLOBALS['rq_taxidforinsurance']);

			if($value != null && !empty($value) && !empty($req_taxidforinsurance) && is_array($req_taxidforinsurance)  && in_array($value, $req_taxidforinsurance)) {
				return true;
			}
		}
		return false;
	}

	/*Help to get provider id*/
	public static function getProviderId($value = null, $ins_id = null, $pid = null) {
		$ins_alt_cms_id = null;
		if(isset($ins_id) && $ins_id != null && !empty($ins_id) && !empty($pid) && $pid != null) {
			$ins_data = self::getInsuranceDataById($pid, $ins_id);
			if($ins_data && is_array($ins_data) && count($ins_data) > 0) {
				if(!empty($ins_data[0]['alt_cms_id'])) {
					$ins_alt_cms_id = $ins_data[0]['alt_cms_id'];
				}
			}
		}

		if(isset($GLOBALS['req_provider_payer']) && !empty($GLOBALS['req_provider_payer']) && !empty($GLOBALS['req_provider_payer']) && ($ins_alt_cms_id != null && !empty($ins_alt_cms_id)) && array_key_exists($ins_alt_cms_id, $GLOBALS['req_provider_payer'])) {
			return $GLOBALS['req_provider_payer'][$ins_alt_cms_id];
		} else if(isset($GLOBALS['default_provider']) && !empty($GLOBALS['default_provider']) && $GLOBALS['default_provider'] != null) {
			return $GLOBALS['default_provider'];
		} else if(isset($value) && !empty($value) && $value != "") {
			return $value;
		} else {
			return $GLOBALS['blank_provider'];
		}
	}

	/*Get HTML Content For Verification*/
	public static function getHtmlContent($pid, $case_id, $cnt, $ins_data_id, $provider_id, $data) {

		$effective_date = date('Y-m-d H:i:s', strtotime($data['date']));
		$returnData = self::getCaseEligibilityVerificationData($pid, $case_id, $cnt, $ins_data_id, $data['policy_number'], $data['group_number'], $effective_date);

		$ins_raw_data = '';
		$ins_plan = '';
		$ins_plan_status = '';
		$ins_eligibility_start_date = '';
		$ins_eligibility_end_date = '';
		$ins_coverage_id = '';
		$ins_provider_id = '';

		$verificationStatus = "not_eligible";
		if($returnData && is_array($returnData) && count($returnData) > 0) {
			$verificationStatus = self::initStatusVerification($returnData[0]);
			$ins_raw_data = $returnData[0]['raw_data'];
			$ins_plan = $returnData[0]['plan'];
			$ins_plan_status = $returnData[0]['plan_status'];
			$ins_coverage_id = $returnData[0]['coverage_id'];
			$ins_provider_id = $returnData[0]['provider_id'];
			$updated_at = isset($returnData[0]['updated_at']) ? $returnData[0]['updated_at'] : '';
			$runBy = self::getRunByUserName($returnData[0]['uid']);
		}

		/*Get Eligbility related text based on status*/
		$verificationText = self::getEligibilityText($verificationStatus);

		?>
		<div class="mt-2 eligibilityElementContainer">
			<div class="hidden">
				<textarea id="ins_raw_data<?php echo $cnt ?>" name="ins_raw_data<?php echo $cnt ?>" style="display:none;"></textarea>
			</div>
			<?php if(isset($data['alt_cms_id']) && !empty(trim($data['alt_cms_id']))) { ?>
			<div class="mb-1 statusContainer">
				<span id="statusText<?php echo $cnt ?>"><?php echo $verificationText; ?></span>
				<span class="c-font-size-sm" id="statusTime<?php echo $cnt ?>">
					<?php if(!empty($updated_at)) { ?>
						<i>(<?php echo $updated_at; ?>)</i> <?php echo $runBy; ?>
					<?php } else { ?>
						<i>None</i>
					<?php } ?>
				</span>
			</div>
			<div class="ins-coverage-container">
				<button type="button" class="btn btn-primary btn-sm" id="checkEligibilityBtn<?php echo $cnt; ?>" onClick="coveragelibObj.handleEligibilityVerification(this,'<?php echo $cnt; ?>', '<?php echo $pid; ?>', '<?php echo $case_id; ?>', '<?php echo $ins_data_id; ?>', '<?php echo $provider_id; ?>')"><?php echo xl('Eligibility Verification'); ?></button>
				<button type="button" class="btn btn-primary btn-sm" onclick="coveragelibObj.handleHistory('<?php echo $pid; ?>', '<?php echo $case_id; ?>', '<?php echo $cnt; ?>')"><?php echo xl('Verification History'); ?></button>
			</div>
			<?php } else { ?>
			<div class="ins-coverage-container">
			<button type="button" class="btn btn-primary btn-sm" onclick="coveragelibObj.handleHistory('<?php echo $pid; ?>', '<?php echo $case_id; ?>', '<?php echo $cnt; ?>')"><?php echo xl('Verification History'); ?></button>
			</div>
			<?php } ?>
		</div>
		<?php
	}

	/*Get username of user b id*/
	public static function getRunByUserName($id) {
		if(isset($id) && !empty($id) && $id != "CRON_JOB") {
			$sql = "SELECT CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name' ";
			$sql .= "FROM `users` us ";
			$sql .= "WHERE us.`id` = ? ";
			$userData = sqlQuery($sql , array($id));

			if(isset($userData) && isset($userData['user_name'])) {
				return $userData['user_name'];
			}
		} else if($id == "CRON_JOB") {
			return "Cron Job";
		}

		return "Unknown";
	}
}
