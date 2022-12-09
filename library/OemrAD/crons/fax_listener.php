<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once("../interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\FaxMessage;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>Conrjob - Fax</title>
</head>
<body>
<?php } ?>
<?php

/*Fetch Latest Status*/
$responce = FaxMessage::getLatestFaxStatus();

$error = '';
$message = '';
if(isset($responce['error'])) {
	$error = $responce['error'];
}

if(isset($responce['message'])) {
	$message = $responce['message'];
}


if(isCommandLineInterface() === false) { 
	$fullMsg = $error ."\n". implode($message, "\n");
	echo nl2br($fullMsg);
} else {
	$fullMsg = $error ."\n". implode($message, "\n");
	echo $fullMsg;
}

?>
<?php if(isCommandLineInterface() === false) { ?>
</body>
</html>
<?php
}