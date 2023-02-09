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
use OpenEMR\OemrAd\ActionEvent;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>Conrjob - Prepare ActionEvent</title>
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
	$cron_lock = fopen(dirname( __FILE__, 1 )."/cron_actionevent.lock", "w+");
	if (flock($cron_lock, LOCK_EX | LOCK_NB)) { // do an exclusive lock
		
		$ide_responce = ActionEvent::prepareNotificationData('both', $eventid_param, $configid_param);
				
		echo "Total Prepared Items: ". ($ide_responce['total_prepared_item'] );
		echo "Total Failed Items: ". ($ide_responce['total_failed_item'] );

		flock($cron_lock, LOCK_UN); // release the lock
	
	} else {
		echo "Cron Already Running.";
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