<?php
if(!isset($_SERVER['SERVER_NAME']) || empty($_SERVER['SERVER_NAME'])) {
	$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
	$_SERVER['SERVER_NAME']='localhost';
	$_SERVER['HTTP_HOST'] = 'localhost';
	$_GET['site'] = 'default';
}
$backpic = "";
$ignoreAuth=1;

require_once(dirname( __FILE__, 3 ) . "/interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Reminder;
use OpenEMR\OemrAd\IdempiereWebservice;

$uniqId = time().'_'.rand();

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>CronJob - Send Notification</title>
</head>
<body>
<?php } ?>
<?php
$type_param = 'both';
$event_type = 0;
$configid_param = "";
$eventid_param = "";

if(isCommandLineInterface() === true) {
	if (!empty($argv[1])) {
		$type_param = $argv[1];
	}

	if (!empty($argv[2])) {
		$eventid_param = $argv[2];
	}

	if (!empty($argv[3])) {
		$configid_param = $argv[3];
	}
} else {
	if (isset($_GET['type']) && !empty($_GET['type'])) {
		$type_param = $_GET['type'];
	}

	if (isset($_GET['config_id']) && !empty($_GET['config_id'])) {
		$configid_param = $_GET['config_id'];
	}

	if (isset($_GET['event_id']) && !empty($_GET['event_id'])) {
		$eventid_param = $_GET['event_id'];
	}
}

if($type_param == 'both') {
	$event_type = 0;
} else if($type_param == 'time') {
	$event_type = 1;
} else if($type_param == 'trigger') {
	$event_type = 2;
}
?>
<?php
	//Cron lock file
	$cron_lock = fopen("./cron_idempierewebservice.lock", "w+");
	if (flock($cron_lock, LOCK_EX | LOCK_NB)) { // do an exclusive lock

		//Reminder::writeLog("Applied cron lock");
		Reminder::writeSqlLog("Acquire lock", $uniqId);

		$isEventIdExists = Reminder::isSpecificEventIdExists($eventid_param, $configid_param);

		if(isset($isEventIdExists) && $isEventIdExists !== false) {
			$sync_info_responce = @IdempiereWebservice::syncInfoToIdempiereByEvent($event_type, $eventid_param, $configid_param);
		} else {
			$sync_info_responce = @IdempiereWebservice::syncInfoToIdempiere($event_type);
		}

		$total_items = 0;
		$total_sent_item = 0;

		if($sync_info_responce['total_items'] > 0) {
			$total_items += $sync_info_responce['total_items'];
		}

		if($sync_info_responce['total_sent_item'] > 0) {
			$total_sent_item += $sync_info_responce['total_sent_item'];
		}

		echo "Total Items: ".$total_items .", Total Sent Items: ".$total_sent_item;

		//Reminder::writeLog("Sent Items:\n-"."Total Items: ".$total_items .", Total Sent Items: ".$total_sent_item);

		flock($cron_lock, LOCK_UN); // release the lock

		//Reminder::writeLog("Released cron lock");
		Reminder::writeSqlLog("Release lock", $uniqId);
	
	} else {
		echo "Cron Already Running.";

		//Reminder::writeLog("Cron Already Running");
		Reminder::writeSqlLog("Cron Already Running", $uniqId);
	}

	fclose($cron_lock);
	
	if(isset($GLOBALS['adodb']['db'])) {
		$GLOBALS['adodb']['db']->Disconnect();
	}
?>

<?php if(isCommandLineInterface() === false) { ?>
</body>
</html>
<?php
}