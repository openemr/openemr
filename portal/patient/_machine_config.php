<?php

/**
 * @package Patient
 *
 * MACHINE-SPECIFIC CONFIGURATION SETTINGS
 *
 * The configuration settings in this file can be changed to suit the
 * machine on which the app is running (ex. local, staging or production).
 *
 * This file should not be added to version control, rather a template
 * file should be added instead and then copied for each install
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

/* */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && (isset($_SESSION['patient_portal_onsite_two']) || $_SESSION['register'] === true)) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    GlobalConfig::$PORTAL = 1;
    require_once(__DIR__ . "/../../interface/globals.php");
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    GlobalConfig::$PORTAL = 0;
    $ignoreAuth = false;
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
}

require_once 'verysimple/Phreeze/ConnectionSetting.php';
require_once("verysimple/HTTP/RequestUtil.php");

/**
 * database connection settings
 */
GlobalConfig::$CONNECTION_SETTING = new ConnectionSetting();
GlobalConfig::$CONNECTION_SETTING->ConnectionString = $GLOBALS['host'] . ":" . $GLOBALS['port'];
GlobalConfig::$CONNECTION_SETTING->DBName = $GLOBALS['dbase'];
GlobalConfig::$CONNECTION_SETTING->Username = $GLOBALS['login'];
GlobalConfig::$CONNECTION_SETTING->Password = $GLOBALS['pass'];
GlobalConfig::$CONNECTION_SETTING->Type = "MySQLi";
if (!$disable_utf8_flag) {
    if (!empty($sqlconf["db_encoding"]) && ($sqlconf["db_encoding"] == "utf8mb4")) {
        GlobalConfig::$CONNECTION_SETTING->Charset = "utf8mb4";
    } else {
        GlobalConfig::$CONNECTION_SETTING->Charset = "utf8";
    }
}

GlobalConfig::$CONNECTION_SETTING->Multibyte = true;
// Turn off STRICT SQL
GlobalConfig::$CONNECTION_SETTING->BootstrapSQL = "SET sql_mode = '', time_zone = '" .
    (new DateTime())->format("P") . "'";

/**
 * the root url of the application with trailing slash, for example http://localhost/patient/
 * default is relative base address
 */
GlobalConfig::$WEB_ROOT = $GLOBALS['qualified_site_addr'];
if ($GLOBALS['portal_onsite_two_basepath']) {
    GlobalConfig::$ROOT_URL = GlobalConfig::$WEB_ROOT . '/portal/patient/';
} else {
    GlobalConfig::$ROOT_URL = $GLOBALS['web_root'] . '/portal/patient/';
}


/**
 * timezone
 */
// date_default_timezone_set("UTC");

// if Multibyte support is specified then we need to check if multibyte functions are available
// if you receive this error then either install multibyte extensions or set Multibyte to false
if (GlobalConfig::$CONNECTION_SETTING->Multibyte && !function_exists('mb_strlen')) {
    die('<html>Multibyte extensions are not installed but Multibyte is set to true in _machine_config.php</html>');
}
