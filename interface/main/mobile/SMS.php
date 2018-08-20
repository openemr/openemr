<?php

require_once('../../globals.php');
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once $GLOBALS['srcdir']."/../vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php";
require_once("m_functions.php");
require_once("$srcdir/MedEx/API.php");

use OpenEMR\Core\Header;

$MedEx = new MedExApi\MedEx('medexbank.com');

$detect = new Mobile_Detect;
$device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version = $detect->getScriptVersion();

$desktop ="";
$categories = array();
$display="cam";
$doc =array();

if (!empty($_GET['desktop'])) {
    $desktop = $_GET['desktop'];
}

// If “Go to full website” link is clicked, redirect mobile user to main website
if (!empty($_SESSION['desktop']) || ($device_type == 'computer') ) {
    $desktop_url = $GLOBALS['webroot']."/interface/main/tabs/main.php";
    header("Location:" . $desktop_url);
}

if ($GLOBALS['medex_enable'] == '1') {
    $logged_in = $MedEx->login();
    $MedEx->display->SMS_bot($logged_in);
    exit;
} else {

}
?>