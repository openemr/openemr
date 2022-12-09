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

$inActiveCases = Caselib::getInactiveCaseData();
$totalCases = 0;
$outputStr = '';

foreach ($inActiveCases as $ck => $case) {
	$ids = array();
	for ($i=1; $i <= 3 ; $i++) { 
		$ids[] = $case['ins_data_id'.$i];
	}

	$checkData = Caselib::checkIsExists($ids, $case['pid']);
	$lb_date =  date('Y-m-d', strtotime('-7 days'));
	$lb_note = "System generated for feature rollout";

	if($checkData === true) {
		$totalCases++;

		Caselib::updateCaseLiabilityData($case['id'], $lb_date, $lb_note);
		$outputStr .= "CaseId: ".$case['id']. ", Date: ".$lb_date.", Note: ".$lb_note." \n";
	}
}

echo "\nTotal Case Records Found: ".$totalCases."\n";
echo "--------------------------------------------\n\n";
echo $outputStr;