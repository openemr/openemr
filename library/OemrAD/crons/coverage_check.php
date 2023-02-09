<?php

set_time_limit(0);

$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once(dirname( __FILE__, 2 ) . "/interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\CoverageCheck;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>Conrjob - Coverage</title>
</head>
<body>
<?php } ?>
<?php
//$tomorrow = date('Y-m-d', strtotime("tomorrow"));
$plusOne = date('Y-m-d',strtotime('+1 days'));
$plusTwo = date('Y-m-d',strtotime('+2 days'));

$eventData = CoverageCheck::getPostcalendar_events(array($plusOne, $plusTwo), true);
/* Check Event Data */
if(!empty($eventData) && count($eventData) > 0) {
	/*Check Coverage Eligibility*/
	$cronData = CoverageCheck::cronUpdateCaseData($eventData);
	$strMsg = "\n".date("Y-m-d H:i:s")." - EVENT_DATE:".$plusOne.", ".$plusTwo .", TOTAL_EVENTS:".count($eventData).", TOTAL_RECORDS:".$cronData['total_records'].", TOTAL_UPDATED_RECORDS:".$cronData['total_updated_records']."";
	echo $strMsg;
	echo "\n\n";
	/* Write cron log inside log file*/
	CoverageCheck::WriteLog($strMsg);
}
?>
<?php if(isCommandLineInterface() === false) { ?>
</body>
</html>
<?php
}