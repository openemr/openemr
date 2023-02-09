<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once(dirname( __FILE__, 2 ) . "/interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>Conrjob - Email</title>
</head>
<body>
<?php } ?>
<?php

/*Fetch Incoming Email*/
$responce = EmailMessage::fetchNewIncomingEmail();

if($responce['status'] == "false") {
	echo $responce['error'];
}
?>
<?php if(isCommandLineInterface() === false) { ?>
</body>
</html>
<?php
}