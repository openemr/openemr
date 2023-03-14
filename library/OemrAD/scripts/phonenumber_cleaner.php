<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once(dirname( __FILE__, 2 ) . "/interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\MessagesLib;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}

function getPData() {
	$resultItem = array();

	//$result = sqlStatement("SELECT pid, fname, mname, lname, phone_home, phone_biz, phone_contact, phone_cell, secondary_phone_cell from patient_data pd WHERE pd.pid = ? order by id desc", array('14'));
	$result = sqlStatement("SELECT pid, fname, mname, lname, phone_home, phone_biz, phone_contact, phone_cell, secondary_phone_cell from patient_data pd where phone_home != '' or phone_biz != '' or phone_contact != '' or phone_cell != '' or secondary_phone_cell != '' order by id desc", array());

	while ($row = sqlFetchArray($result)) {
		$resultItem[] = $row;
	}

	return $resultItem;
}

function handlePhoneNumber($item = array()) {
	$preparedData = array();
	$pFieldList = array('phone_home', 'phone_biz', 'phone_contact', 'phone_cell', 'secondary_phone_cell');

	$sqlSet = array();
	$pid = isset($item['pid']) ? trim($item['pid']) : '';

	if(empty($pid)) {
		return false;
	}

	foreach ($item as $fieldKey => $fieldValue) {
		if(in_array($fieldKey, $pFieldList)) {
			$phoneVal = !empty(trim($fieldValue)) ? array_filter(array_map('trim', explode(",",$fieldValue))) : array();
			$phoneVal = array_map(function($item) { return MessagesLib::getPhoneNumbers($item); }, $phoneVal);

			$rPhoneVal =  array_filter(array_map(function($item) { return $item['raw_phone']; }, $phoneVal));

			$preparedData[$fieldKey] = implode(",", $rPhoneVal);

			$nPhoneVal = implode(",", $rPhoneVal);

			if(!empty($nPhoneVal)) $sqlSet[] = $fieldKey."='" .implode(",", $rPhoneVal)."'";
		} else {
			$preparedData[$fieldKey] = $fieldValue;
		}
	}

	$sqlSet = implode(", ",$sqlSet);
	if(!empty($sqlSet)) {
		$sqlUpdateQuery = "UPDATE patient_data SET $sqlSet WHERE pid = '".$pid."' and pid != '';";
		sqlStatement($sqlUpdateQuery);
		echo $sqlUpdateQuery . "\n";

		if(isCommandLineInterface() === false) {
			echo "<br>";
		}

	}
}

$patientData = getPData();
foreach ($patientData as $pk => $pItem) {
	handlePhoneNumber($pItem);
}