<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once("../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/wmt-v2/wmt.msg.inc");
//include_once(dirname( __FILE__, 4 )."/core.php");


function getBillingNotesVal($value = "") {
	$fieldHtml = "";

	if(!empty($value)) {
		$nq_filter = ' AND title = "'.$value.'"';
		$listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);

		if(!empty($listOptions)) {
			$fieldHtml = $listOptions[0] && isset($listOptions[0]['option_id']) ? $listOptions[0]['option_id'] : "";
		}
	}

	return $fieldHtml;
}

function getlogData($case_id = "", $bc_note = "", $bc_note_dec = "", $msg ="") {
	return "CaseId: " . $case_id . ", Bc Note: " . $bc_note . ", Bc Note Dec: " . $bc_note_dec . ", Meessage: " . $msg;
}

$result = sqlStatement("SELECT fc.id as case_id, fc.bc_date, cfvl.* from form_cases fc left join case_form_value_logs cfvl on cfvl.id = (SELECT cfvl2.id from case_form_value_logs cfvl2 where cfvl2.case_id = fc.id ORDER BY cfvl2.created_date DESC LIMIT 1 ) WHERE cfvl.id is not null and (fc.bc_date is null or fc.bc_date = '')");

$data = array();
$logData = array();
$updateCount = 0;

while ($row = sqlFetchArray($result)) {
	if(!empty($row) && empty($row['bc_date'])) {
		$noteValue = isset($row['notes']) ? explode(" - ", $row['notes']) : array();
		$updateData = array(
			'case_id' => $row['case_id'],
			'bc_date' => $row['delivery_date'],
			'bc_notes' => '',
			'bc_notes_dsc' => ''
		);

		if(count($noteValue) == 1) {
			$bc_notes_val = getBillingNotesVal($noteValue[0]);
			if(!empty($bc_notes_val)) {
				$updateData['bc_notes'] = $bc_notes_val;
				$logData[] = getlogData($row['case_id'], $noteValue[0], $noteValue[1], "1 first item is bc note status.");
			} else {
				$updateData['bc_notes_dsc'] = $noteValue[0];
				$logData[] = getlogData($row['case_id'], $noteValue[0], $noteValue[1], "1 first item is bc note.");
			}

		} else if(count($noteValue) == 2) {
			$bc_notes_val = getBillingNotesVal($noteValue[0]);
			$updateData['bc_notes'] = $bc_notes_val;
			$updateData['bc_notes_dsc'] = isset($noteValue[1]) ? $noteValue[1] : "";

			if(empty($bc_notes_val)) {
				$logData[] = getlogData($row['case_id'], $noteValue[0], $noteValue[1], "Bc note status data is not found.");
			}
		} 

		if(!empty($updateData) && isset($updateData['case_id']) && !empty($updateData['case_id'])) {
			$updateStatus = sqlStatement("UPDATE form_cases SET `bc_date` = ?, `bc_notes` = ?, `bc_notes_dsc` = ? WHERE `id` = ?", array($updateData['bc_date'], $updateData['bc_notes'], $updateData['bc_notes_dsc'], $updateData['case_id'])
			);

			if($updateStatus) {
				$updateCount++;
			}
		}
		
		//print_r($row);
		//print_r($noteValue);
		//print_r($updateData);
		//print_r($logData);
	}
	$data[] = $row;
}

echo '<pre>';
echo count($data) .  "-" . $updateCount . "-" . count($logData);
print_r($logData);
echo '</pre>';