<?php
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

include_once("../../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

$doNotPrintField = true;
$GLOBALS['pdf_output'] = "S";

ob_start();
include $GLOBALS['fileroot'].'/interface/patient_file/report/custom_report.php';
$f = ob_get_clean();

echo $content;