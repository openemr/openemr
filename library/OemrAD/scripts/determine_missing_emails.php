<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once("../interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

function getPICaseManageData($case_id) {
	$resultItem = array();

	if(!empty($case_id)) {
		$result = sqlStatement("SELECT * FROM vh_pi_case_management_details WHERE case_id = ? AND field_name = 'lp_contact' ", array($case_id));

		while ($row = sqlFetchArray($result)) {
			$resultItem[] = $row;
		}
	}

	return $resultItem;
}

function getPICaseManageData1() {
	$resultItem = array();
	$result = sqlStatement("SELECT vpcmd.case_id, vpcmd.field_name, GROUP_CONCAT(vpcmd.field_value) as field_values, GROUP_CONCAT(u.email) as emails, fc.notes as case_emails, pd.pubpid from vh_pi_case_management_details vpcmd, users u, form_cases fc, patient_data pd where vpcmd.case_id = fc.id and pd.pid = fc.pid and u.id = vpcmd.field_value and field_name = 'lp_contact' group by vpcmd.case_id", array());

	while ($row = sqlFetchArray($result)) {
		$resultItem[] = $row;
	}

	return $resultItem;
}

function insertPICaseManagmentDetails($case_id = '', $data = array()) {
	if(!empty($case_id) && !empty($data)) {
		foreach ($data as $dk => $dItem) {
			if(!empty($dk)) {
				if(is_array($dItem)) {
					foreach ($dItem as $diK => $dsItem) {
						if(!empty($dsItem)) {
							//Insert Items
							$insertSql = "INSERT INTO `vh_pi_case_management_details` (case_id, field_name, field_index, field_value) VALUES (?, ?, ?, ?) ";
							sqlInsert($insertSql, array(
								$case_id,
								$dk,
								$diK,
								$dsItem
							));
						}
					}
				} else {
					//Insert Items
					$insertSql = "INSERT INTO `vh_pi_case_management_details` (case_id, field_name, field_index, field_value) VALUES (?, ?, ?, ?) ";
					sqlInsert($insertSql, array(
						$case_id,
						$dk,
						0,
						$dItem
					));
				}
			}	
		}
	}
}

function getCaseData() {
	$dataSet = array();
	$result = sqlStatement("SELECT pd.pubpid, fc.* FROM form_cases fc left join patient_data pd on pd.pid = fc.pid WHERE fc.notes  != ''");
	while ($row = sqlFetchArray($result)) {
		$dataSet[] = $row;
	}

	return $dataSet;
}

function getCaseData1() {
	$dataSet = array();
	$result = sqlStatement("SELECT pd.pubpid, fc.* FROM form_cases fc left join patient_data pd on pd.pid = fc.pid WHERE fc.notes  != '' and fc.closed = 0");
	while ($row = sqlFetchArray($result)) {
		$dataSet[] = $row;
	}

	return $dataSet;
}

function getAbookData($email) {
	if(!empty($email)) {
		return sqlQuery("SELECT * from users u where email = ?", array($email));
	}

	return false;
}

function validateEmail($email) {
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    else {
        return false;
    }
}

/*
LINKED_EMAIL_LIST=1
UNLINKED_EMAIL_LIST=2
NOT_EMAIL_LIST=3
DATA MIGRATION=4
CHECK DUPLICATE DATA=5
*/
$actionType = 1;

$aBookTypeList = array(
	'Attorney',
);

if($actionType === 1 || $actionType === 2 || $actionType === 3) {
	$caseData = getCaseData1();
} else {
	$caseData = getCaseData();
}

$cnt = 0;
$cntTotal = 0;
$cntMTotal = 0;
$cntMNTotal = 0;
$cntSTotal = 0;
$cntATotal = 0;

if($actionType === 5) {
	$cmData = getPICaseManageData1();
	$fp = fopen('file.csv', 'wb');

	foreach ($cmData as $cmk => $cmItem) {
		$cmEmails = isset($cmItem['emails']) && !empty($cmItem['emails']) ? explode(",", $cmItem['emails']) : [];
		$csEmails = isset($cmItem['case_emails']) && !empty($cmItem['case_emails']) ? explode(",", $cmItem['case_emails']) : [];
		$csEmails = array_map('trim',$csEmails);
		$csEmails = array_map(function ($a) { return strtolower($a); }, $csEmails);

		$cmEmailCount = array_count_values($cmEmails);
		$csEmailCount = array_count_values($csEmails);
		$notMatchedList = array();

		$c_id = isset($cmItem['case_id']) ? $cmItem['case_id'] : '';
		$c_pubpid = isset($cmItem['pubpid']) ? $cmItem['pubpid'] : '';

		foreach ($cmEmailCount as $cmK => $cmC) {
			if(isset($csEmailCount[$cmK])) {
				if($cmC !== $csEmailCount[$cmK]) {
					$notMatchedList[] = $cmK;
				}
			} else {
				$notMatchedList[] = $cmK;
			}
		}

		if(!empty($notMatchedList)) {
			$cnt++;
			fputcsv($fp, array($cnt, $c_id, $c_pubpid, implode(",", $notMatchedList)));
		}
	}

	exit();
}

if(isset($caseData) && !empty($caseData)) {
	$fp = fopen('file.csv', 'wb');

	foreach ($caseData as $cdk => $cdkItem) {
		$c_notes = isset($cdkItem['notes']) ? $cdkItem['notes'] : '';
		$c_id = isset($cdkItem['id']) ? $cdkItem['id'] : '';
		$c_pubpid = isset($cdkItem['pubpid']) ? $cdkItem['pubpid'] : '';

		if(!empty($c_notes)) {
			$c_emails = explode(",",$c_notes);
			$c_validEmails = array();
			
			if(!empty($c_emails)) {
				foreach ($c_emails as $cek => $ceItem) {
					$cEmail = isset($ceItem) ? trim($ceItem) : "";

					if(validateEmail($cEmail)) {
						$abookData = getAbookData($cEmail);

						if(!empty($abookData)) {
							$c_validEmails[] = array(
								'email' => $cEmail,
								'abook_id' => $abookData['id'],
								'abook_type' => $abookData['abook_type']
							);
							
							if($actionType === 1) {
								$cnt++;
								fputcsv($fp, array($cnt, $c_id, $c_pubpid, $cEmail, $abookData['id']));
							}
						}

						//Unlinked email list
						if(empty($abookData)) {
							if($actionType === 2) {
								echo  $cnt . ". " . $c_id . " - " . $cEmail . "\n";
								$cnt++;
								fputcsv($fp, array($cnt, $c_id, $c_pubpid, $cEmail));
							}
						}
					} else {
						//Not email list
						if($actionType === 3) {
							$cnt++;
							fputcsv($fp, array($cnt, $c_id, $c_pubpid, $cEmail));
							echo  $cnt . ". " . $c_id . " - " . $cEmail . "\n";
						}
					}
				}

				if($actionType === 4) {
					if(!empty($c_validEmails) && !empty($c_id)) {
						$dataEmail = array(
							'lp_contact' => array()
						);
						$logData = array();
						$col3 = '';

						foreach ($c_validEmails as $cve => $cveItem) {
							if(!empty($cveItem['abook_id'])) {
								if(in_array($cveItem['abook_type'], $aBookTypeList)) {
									$dataEmail['lp_contact'][] = $cveItem['abook_id'];
									$logData[] = array(
										'email' => $cveItem['email'],
										'abook_id' => $cveItem['abook_id']
									);
								} else {
									$cntMNTotal++;
								}
							}

							$cntTotal++;
						}

						//Data Migration
						if(!empty($dataEmail['lp_contact'])) {

							$piCaseData = getPICaseManageData($c_id);
							
							if(empty($piCaseData)) {
								//Save Email Addresses
								$caseLib->savePICaseManagmentDetails($c_id, $dataEmail);
								
								$cntMTotal = $cntMTotal + count($dataEmail['lp_contact']);
							} else {
								$lpContactData = $piCaseData;
								$lpList1 = array();
								$lpList2 = array();

								foreach ($lpContactData as $lpck => $lpcItem) {
									if(isset($lpcItem['field_value']) && !empty($lpcItem['field_value'])) {
										$lpList1[] = $lpcItem['field_value'];
									}
								}

								if(isset($dataEmail['lp_contact']) && !empty($dataEmail['lp_contact'])) {
									$lpList2 = $dataEmail['lp_contact'];
								}

								$diff1 = $caseLib->getArrayValDeff($lpList1, $lpList2);

								$diff2 = $caseLib->getArrayValDeff($lpList2, $lpList1);
								$diffa2 = $caseLib->getAbookData($diff2);
								$t_emails = array('lp_contact' => array());
								$lastIndex = count($lpList1);

								$col3 = count($diff2) > 0 ? count($diff2) : '';

								foreach ($diff2 as $dak2 => $daI2) {
									if(isset($diffa2['id_'.$daI2]) && !empty($diffa2['id_'.$daI2])) {
										$daItem2 = $diffa2['id_'.$daI2];

										if(isset($daItem2['id']) && !empty($daItem2['email'])) {
											$t_emails['lp_contact'][$lastIndex] = $daItem2['id'];
											$lastIndex++;
										}
									}
								}

								if(!empty($t_emails)) {
									//Add New Info
									insertPICaseManagmentDetails($c_id, $t_emails);
								}

								$cntATotal = $cntATotal + (count($t_emails['lp_contact']));
								$cntSTotal = $cntSTotal + (count($dataEmail['lp_contact']) - count($t_emails['lp_contact']));
							}

							$cnt++;
							fputcsv($fp, array($cnt, $c_id, $c_pubpid, json_encode($logData), $col3));
						}
					}
				}
			}
		}
	}

	if($actionType === 4) {
		//Data Migration
		fputcsv($fp, array("Total migrated", $cntMTotal));
		fputcsv($fp, array("Total filtered", $cntMNTotal));
		fputcsv($fp, array("Total skipped", $cntSTotal));
		fputcsv($fp, array("Total added", $cntATotal));
		fputcsv($fp, array("Total", $cntTotal));
	}

	fclose($fp);

	echo "Total Count: " . $cnt;
}

