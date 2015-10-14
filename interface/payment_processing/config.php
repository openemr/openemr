<?php
/**
 * This file contains config info for the sample app.
 */

// Adjust this to point to the Authorize.Net PHP SDK

//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once('../../globals.php');
require_once('anet_php_sdk/AuthorizeNet.php');

if($GLOBALS['enable_authoriz_net'] = '1'){


//$METHOD_TO_USE = "AIM";
$METHOD_TO_USE = "DIRECT_POST";         // Uncomment this line to test DPM

$a = $GLOBALS['aapi_login_id'];
$b = $GLOBALS['atransaction_key'];

define("AUTHORIZENET_API_LOGIN_ID","$a");    // Add your API LOGIN ID
define("AUTHORIZENET_TRANSACTION_KEY","$b"); // Add your API transaction key
define("AUTHORIZENET_SANDBOX",true);       // Set to false to test against production
define("TEST_REQUEST", "FALSE");           // You may want to set to true if testing against production

$c = $GLOBALS['authorizenet_md5_setting'];

// You only need to adjust the two variables below if testing DPM
define("AUTHORIZENET_MD5_SETTING","$c");                // Add your MD5 Setting.
$site_root = $_SERVER['REQUEST_SCHEME'] ."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ; // Add the URL to your site


if (AUTHORIZENET_API_LOGIN_ID == "" || AUTHORIZENET_MD5_SETTING == "") {
     //echo $site_root."<BR>";
    die('Enter your merchant credentials in Globals before running the sample app.');
  }
}else{
  die('Enable CC Gateway in Administration -> Globals');
}