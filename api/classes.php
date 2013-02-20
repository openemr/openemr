<?php

$fake_register_globals = false;
$sanitize_all_escapes = true;

include_once(dirname(dirname(__FILE__)) . "/interface/globals.php");
require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/log.inc");
require_once("$srcdir/appointments.inc.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/acl.inc");
include("includes/class.database.php");
include("includes/class.arraytoxml.php");
include("includes/class.phpmailer.php");
//include 'includes/aes.class.php';

//var_dump($GLOBALS['rest_api_server']);

if(!$GLOBALS['rest_api_server']){
    echo "<openemr>
            <status>-1</status>
            <reason>Please check the REST API server settings in Administration/Globals/Connectors</reason>
        </openemr>";
    exit;
}

$site = 'default';
//$sitesDir = $_SERVER['DOCUMENT_ROOT'] . "/sites/";
$sitesDir = dirname(dirname(__FILE__)) . "/sites/";
//$sitesDir = $_SERVER['DOCUMENT_ROOT'] . "/medmasteropenemr/sites/";
//echo basename(dirname(dirname(__FILE__)));exit;

$url = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
if ($_SERVER["SERVER_PORT"] != "80") {
    $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER['REQUEST_URI'];
    $url1 = str_replace("medmasterproapi/openemr", '', pathinfo($url, PATHINFO_DIRNAME));
} else {
    $url .= $_SERVER["SERVER_NAME"] . $_SERVER['REQUEST_URI'];
    $url1 = str_replace("medmasterproapi/openemr", '', pathinfo($url, PATHINFO_DIRNAME));
}

$sitesUrl = $url1 . 'sites/';
$openemrUrl = $url1;

$openemrDirName = basename(dirname(dirname(__FILE__)));

//echo "url : ".pathinfo($url,PATHINFO_DIRNAME)."<br>";
//echo "SIte Dir : ".$sitesDir."<br>";
//echo "sitesUrl : ".$sitesUrl."<br>";
//echo "openemrUrl : ".$openemrUrl."<br>";
//echo $openemrDirName."<br />";
//
//exit;

/**
 * above some variables are used in functions file
 */
include("includes/functions.php");
?>