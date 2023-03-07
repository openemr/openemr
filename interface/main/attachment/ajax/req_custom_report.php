<?php

// Set session 
if (session_status() === PHP_SESSION_NONE) {
	if(isset($_POST['SESSION_DATA']) && !empty($_POST['SESSION_DATA'])) {
		session_name('OpenEMR');
		session_start();

		foreach ($_POST['SESSION_DATA'] as $skey => $svalue) {
			$_SESSION[$skey] = $svalue;
		}

		unset($_POST['SESSION_DATA']);
	}
}

//Include global
include_once("../../../globals.php");

$temp_pdf_output = $GLOBALS['pdf_output'];
$_POST['pdf'] = "1";

$GLOBALS['pdf_output'] = "S";

//Change Dir
$currentDir = getcwd();
chdir($GLOBALS['fileroot'].'/interface/patient_file/report/');

$doNotPrintField = true;

ob_start();
include $GLOBALS['fileroot'].'/interface/patient_file/report/custom_report.php' ;
$filecontent = ob_get_clean();

$doNotPrintField = false;

//Set Original Dir
chdir($currentDir);

$GLOBALS['pdf_output'] = $temp_pdf_output;

echo $content;