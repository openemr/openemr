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
	<title>Conrjob - Prepare Notification</title>
</head>
<body>
<?php } ?>

<?php
$configid_param = "";
$eventid_param = "";

if(isCommandLineInterface() === true) {
	if (!empty($argv[1])) {
		$eventid_param = $argv[1];
	}

	if (!empty($argv[2])) {
		$configid_param = $argv[2];
	}
} else {
	if (isset($_GET['config_id']) && !empty($_GET['config_id'])) {
		$configid_param = $_GET['config_id'];
	}

	if (isset($_GET['event_id']) && !empty($_GET['event_id'])) {
		$eventid_param = $_GET['event_id'];
	}
}
?>

<?php
	//Cron lock file
	$cron_lock = fopen("./cron_idempierewebservice.lock", "w+");
	if (flock($cron_lock, LOCK_EX | LOCK_NB)) { // do an exclusive lock
		
		//Reminder::writeLog("Applied cron lock");
		Reminder::writeSqlLog("Acquire lock", $uniqId);

		$ide_responce = IdempiereWebservice::prepareNotificationData('both', $eventid_param, $configid_param);
				
		echo "Total Prepared Items: ". ($ide_responce['total_prepared_item'] );

		//$statusMsg = Reminder::prepareStatusMsg(array($notification_responce, $ide_responce));
		//Reminder::writeLog($statusMsg);

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