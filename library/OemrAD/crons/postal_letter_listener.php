<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once(dirname( __FILE__, 2 ) . "/interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\PostalLetter;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>Conrjob - Postal Letter</title>
</head>
<body>
<?php } ?>
<?php

/*Fetch Incoming PostalLetter*/
$responce = PostalLetter::getLatestLetterStatus();

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