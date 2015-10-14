<?php
/**
 * Tests for the AuthorizeNet PHP SDK
 */

/**
 * Enter your test account credentials to run tests against sandbox.
 */
define("AUTHORIZENET_API_LOGIN_ID", "");
define("AUTHORIZENET_TRANSACTION_KEY", "");
define("AUTHORIZENET_MD5_SETTING", "");
/**
 * Enter your live account credentials to run tests against production gateway.
 */
define("MERCHANT_LIVE_API_LOGIN_ID", "");
define("MERCHANT_LIVE_TRANSACTION_KEY", "");

/**
 * Card Present Sandbox Credentials
 */
define("CP_API_LOGIN_ID", "");
define("CP_TRANSACTION_KEY", "");


define("AUTHORIZENET_LOG_FILE", dirname(__FILE__) . "/log");
// Clear logfile
file_put_contents(AUTHORIZENET_LOG_FILE, "");





if (!function_exists('curl_init')) {
    throw new Exception('AuthorizeNetSDK needs the CURL PHP extension.');
}


if (!function_exists('simplexml_load_file')) {
  throw new Exception('The AuthorizeNet SDK requires the SimpleXML PHP extension.');
}

require_once dirname(dirname(__FILE__)) . '/AuthorizeNet.php';
require_once 'PHPUnit/Framework.php';

if (AUTHORIZENET_API_LOGIN_ID == "") {
    die('Enter your merchant credentials in '.__FILE__.' before running the test suite.');
}
