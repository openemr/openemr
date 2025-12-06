<?php

/**
 * Default values for optional variables that are allowed to be set by callers.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Checks if the server's PHP version is compatible with OpenEMR:
require_once(__DIR__ . "/../src/Common/Compatibility/Checker.php");
$response = OpenEMR\Common\Compatibility\Checker::checkPhpVersion();
if ($response !== true) {
    die(htmlspecialchars($response));
}

use Dotenv\Dotenv;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\ModulesApplication;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;

// Throw error if the php openssl module is not installed.
if (!(extension_loaded('openssl'))) {
    error_log("OPENEMR ERROR: OpenEMR is not working since the php openssl module is not installed.", 0);
    die("OpenEMR Error : OpenEMR is not working since the php openssl module is not installed.");
}
// Throw error if the openssl aes-256-cbc cipher is not available.
if (!(in_array('aes-256-cbc', openssl_get_cipher_methods()))) {
    error_log("OPENEMR ERROR: OpenEMR is not working since the openssl aes-256-cbc cipher is not available.", 0);
    die("OpenEMR Error : OpenEMR is not working since the openssl aes-256-cbc cipher is not available.");
}


//This is to help debug the ssl mysql connection. This will send messages to php log to show if mysql connections have a cipher set up.
$GLOBALS['debug_ssl_mysql_connection'] = false;

// Unless specified explicitly, apply Auth functions
if (!isset($ignoreAuth)) {
    $ignoreAuth = false;
}

// Same for onsite
if (!isset($ignoreAuth_onsite_portal)) {
    $ignoreAuth_onsite_portal = false;
}

// Is this windows or non-windows? Create a boolean definition.
if (!defined('IS_WINDOWS')) {
    define('IS_WINDOWS', (stripos(PHP_OS, 'WIN') === 0));
}

// The webserver_root and web_root are now automatically collected.
// If not working, can set manually below.
// Auto collect the full absolute directory path for openemr.
$webserver_root = dirname(__FILE__, 2);
if (IS_WINDOWS) {
 //convert windows path separators
    $webserver_root = str_replace("\\", "/", $webserver_root);
}

// Collect the apache server document root (and convert to windows slashes, if needed)
$server_document_root = realpath($_SERVER['DOCUMENT_ROOT']);
if (IS_WINDOWS) {
 //convert windows path separators
    $server_document_root = str_replace("\\", "/", $server_document_root);
}

// Auto collect the relative html path, i.e. what you would type into the web
// browser after the server address to get to OpenEMR.
// This removes the leading portion of $webserver_root that it has in common with the web server's document
// root and assigns the result to $web_root. In addition to the common case where $webserver_root is
// /var/www/openemr and document root is /var/www, this also handles the case where document root is
// /var/www/html and there is an Apache "Alias" command that directs /openemr to /var/www/openemr.
$web_root = substr($webserver_root, strspn($webserver_root ^ $server_document_root, "\0"));
// Ensure web_root starts with a path separator
if (preg_match("/^[^\/]/", $web_root)) {
    $web_root = "/" . $web_root;
}

// The webserver_root and web_root are now automatically collected in
//  real time per above code. If above is not working, can uncomment and
//  set manually here:
//   $webserver_root = "/var/www/openemr";
//   $web_root =  "/openemr";

$ResolveServerHost = static function () {
    $scheme = ($_SERVER['REQUEST_SCHEME'] ?? 'https') . "://";
    $possibleHostSources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];
    $sourceTransformations = [
        "HTTP_X_FORWARDED_HOST" => function ($value) {
            $elements = explode(',', $value);
            return trim(end($elements));
        }
    ];
    $host = '';
    foreach ($possibleHostSources as $source) {
        if (!empty($host)) {
            break;
        }
        if (empty($_SERVER[$source])) {
            continue;
        }
        $host = $_SERVER[$source];
        if (array_key_exists($source, $sourceTransformations)) {
            $host = $sourceTransformations[$source]($host);
        }
    }
    return rtrim(trim($scheme . $host), "/");
};

// Debug function. Can expand for longer trace or file info.
function GetCallingScriptName()
{
    $e = new Exception();
    return $e->getTrace()[1]['file'];
}

// This is the directory that contains site-specific data.  Change this
// only if you have some reason to.
$GLOBALS['OE_SITES_BASE'] = "$webserver_root/sites";

//Composer vendor directory, absolute to the webserver root.
$GLOBALS['vendor_dir'] = "$webserver_root/vendor";

// Includes composer autoload
// Note this is skipped in special cases where the autoload has already been performed
// Note this also brings in following library files:
//  library/htmlspecialchars.inc.php - Include convenience functions with shorter names than "htmlspecialchars" (for security)
//  library/formdata.inc.php - Include sanitization/checking functions (for security)
//  library/sanitize.inc.php - Include sanitization/checking functions (for security)
//  library/formatting.inc.php - Includes functions for date/time internationalization and formatting
//  library/date_functions.php - Includes functions for date internationalization
//  library/validation/validate_core.php - Includes functions for page validation
//  library/translation.inc.php - Includes translation functions
if (empty($GLOBALS['already_autoloaded'])) {
    require_once $GLOBALS['vendor_dir'] . "/autoload.php";
}

/*
* If a session does not yet exist, then will start the core OpenEMR session.
* If a session already exists, then this means portal or oauth2 or api is being used, which
*   has already created a portal session/cookie, so will bypass setting of
*   the core OpenEMR session/cookie.
* $sessionAllowWrite = 1 | true | string then normal operation
* $sessionAllowWrite = undefined | null | 0  session start for read only then auto
*   immediate session_write_close.
* Unless $sessionAllowWrite is true, ensure no session writes are used within the calling
*   scope of this globals instance. Goal is to unlock session file as quickly as possible
*   instead of waiting for calling script to complete before releasing flock.
 */
if (empty($restRequest)) {
    $restRequest = HttpRestRequest::createFromGlobals();
}
if (empty($globalsBag)) {
    $globalsBag = new OeGlobalsBag([], true);
}
$globalsBag->set('webserver_root', $webserver_root);
$globalsBag->set('web_root', $web_root);
$globalsBag->set('vendor_dir', $globalsBag->get('vendor_dir', $GLOBALS['vendor_dir'] ?? null));
$globalsBag->set('restRequest', $restRequest);
$globalsBag->set('OE_SITES_BASE', $globalsBag->get('OE_SITES_BASE', $GLOBALS['OE_SITES_BASE'] ?? null));
$globalsBag->set('debug_ssl_mysql_connection', $globalsBag->get('debug_ssl_mysql_connection', $GLOBALS['debug_ssl_mysql_connection'] ?? null));
$globalsBag->set('eventDispatcher', $eventDispatcher ?? null);
$globalsBag->set('ignoreAuth_onsite_portal', $ignoreAuth_onsite_portal);
$read_only = empty($sessionAllowWrite);
if (session_status() === PHP_SESSION_NONE) {
    //error_log("1. LOCK ".GetCallingScriptName()); // debug start lock
    SessionUtil::coreSessionStart($web_root, $read_only);
    //error_log("2. FREE ".GetCallingScriptName()); // debug unlocked
}

// Set the site ID if required.  This must be done before any database
// access is attempted.
if (empty($_SESSION['site_id']) || !empty($_GET['site'])) {
    if (!empty($_GET['site'])) {
        $tmp = $_GET['site'];
    } else {
        if (empty($ignoreAuth) && empty($ignoreAuth_onsite_portal)) {
            // mdsupport - Don't die if logout menu link is called from expired session.
            // Eliminate this code when close method is available for session management.
            if ((isset($_GET['auth'])) && ($_GET['auth'] == "logout")) {
                $globalsBag->set('login_screen', "login_screen.php");
                $srcdir = "../library";
                $globalsBag->set('srcdir', $srcdir);
                require_once("$srcdir/auth.inc.php");
            }
            die("Site ID is missing from session data!");
        }

        $tmp = $_SERVER['HTTP_HOST'];
        if (!is_dir($GLOBALS['OE_SITES_BASE'] . "/$tmp")) {
            $tmp = "default";
        }
    }

    // for both REST API and browser access we can't proceed unless we have a valid site id.
    // since this is user provided content we need to escape the value but we use htmlspecialchars instead
    // of text() as our helper functions are loaded in later on in this file.
    if (empty($tmp) || preg_match('/[^A-Za-z0-9\\-.]/', (string) $tmp)) {
        echo "Invalid URL";
        error_log("Request with site id '" . htmlspecialchars((string) $tmp, ENT_QUOTES) . "' contains invalid characters.");
        die();
    }

    if (isset($_SESSION['site_id']) && ($_SESSION['site_id'] != $tmp)) {
      // This is to prevent using session to penetrate other OpenEMR instances within same multisite module
        session_unset(); // clear session, clean logout
        if (isset($landingpage) && !empty($landingpage)) {
          // OpenEMR Patient Portal use
            header('Location: index.php?site=' . urlencode((string) $tmp));
        } else {
          // Main OpenEMR use
            header('Location: ../login/login.php?site=' . urlencode((string) $tmp)); // Assuming in the interface/main directory
        }

        exit;
    }

    if (!isset($_SESSION['site_id']) || $_SESSION['site_id'] != $tmp) {
        $_SESSION['site_id'] = $tmp;
        // error_log("Session site ID has been set to '$tmp'"); // debugging
    }
}

// Set the site-specific directory path.
$globalsBag->set('OE_SITE_DIR', $globalsBag->getString('OE_SITES_BASE') . "/" . $_SESSION['site_id']);

// Set a site-specific uri root path.
$globalsBag->set('OE_SITE_WEBROOT', $web_root . "/sites/" . $_SESSION['site_id']);


// Root directory, relative to the webserver root:
$globalsBag->set('rootdir', "$web_root/interface");
$rootdir = $globalsBag->getString('rootdir');
// Absolute path to the source code include and headers file directory (Full path):
$globalsBag->set('srcdir', "$webserver_root/library");
// Absolute path to the location of documentroot directory for use with include statements:
$globalsBag->set('fileroot', $webserver_root);
// Absolute path to the location of interface directory for use with include statements:
$include_root = "$webserver_root/interface";
$globalsBag->set('include_root', $include_root);
// Absolute path to the location of documentroot directory for use with include statements:
$globalsBag->set('webroot', $web_root);

// Static assets directory, relative to the webserver root.
// (it is very likely that this path will be changed in the future))
$globalsBag->set('assets_static_relative', "$web_root/public/assets");

// Relative themes directory, relative to the webserver root.
$globalsBag->set('themes_static_relative', "$web_root/public/themes");

// Relative images directory, relative to the webserver root.
$globalsBag->set('images_static_relative', "$web_root/public/images");

// Static images directory, absolute to the webserver root.
$globalsBag->set('images_static_absolute', "$webserver_root/public/images");

$globalsBag->set('template_dir', $globalsBag->getString('fileroot') . "/templates/");
$globalsBag->set('incdir', $include_root);
// Location of the login screen file
$globalsBag->set('login_screen', $globalsBag->getString('rootdir') . "/login_screen.php");

// Variable set for Eligibility Verification [EDI-271] path
$globalsBag->set('edi_271_file_path', $globalsBag->getString('OE_SITE_DIR') . "/documents/edi/");

//  Check necessary writable paths (add them if do not exist)
if (! is_dir($globalsBag->getString('OE_SITE_DIR') . '/documents/smarty/gacl')) {
    if (!mkdir($concurrentDirectory = $globalsBag->getString('OE_SITE_DIR') . '/documents/smarty/gacl', 0755, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}
if (! is_dir($globalsBag->getString('OE_SITE_DIR') . '/documents/smarty/main')) {
    if (!mkdir($concurrentDirectory = $globalsBag->getString('OE_SITE_DIR') . '/documents/smarty/main', 0755, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

//  Set and check that necessary writeable path exist for mPDF tool
$GLOBALS['MPDF_WRITE_DIR'] = $globalsBag->getString('OE_SITE_DIR') . '/documents/mpdf/pdf_tmp';
if (! is_dir($GLOBALS['MPDF_WRITE_DIR'])) {
    if (!mkdir($concurrentDirectory = $GLOBALS['MPDF_WRITE_DIR'], 0755, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

/**
 * @var Dotenv Allow a `.env` file to be read in and applied as $_SERVER variables.
 *
 * This allows to define a "development" environment which can then load up
 * different variables and reporting/debugging functionality. Should be used in
 * development only, not for production
 *
 * @link http://open-emr.org/wiki/index.php/Dotenv_Usage
 */
if (file_exists("{$webserver_root}/.env")) {
    $dotenv = Dotenv::createImmutable($webserver_root);
    $dotenv->load();
}

// The logging level for common/logging/logger.php
// Value can be TRACE, DEBUG, INFO, WARN, ERROR, or OFF:
//    - DEBUG/INFO are great for development
//    - INFO/WARN/ERROR are great for production
//    - TRACE is useful when debugging hard to spot bugs
// @deprecated log_level doesn't appear to be used anywhere
$GLOBALS["log_level"] = "OFF";

try {
    // we inject the eventDispatcher if we have one setup already
    // TODO: @adunsulag is there a better way to do this?
    /** @var Kernel */
    $globalsBag->set("kernel", new Kernel($globalsBag->get('eventDispatcher')));
} catch (\Exception $e) {
    error_log(errorLogEscape($e->getMessage()));
    die();
}

// This will open the openemr mysql connection.
require_once(__DIR__ . "/../library/sql.inc.php");
$globalsBag->set("adodb", $GLOBALS['adodb'] ?? null);
$globalsBag->set("dbh", $GLOBALS['dbh'] ?? null);
$globalsBag->set("disable_utf8_flag", $disable_utf8_flag ?? false);

// Include the version file
require_once(__DIR__ . "/../version.php");
$globalsBag->set("v_major", $v_major ?? null);
$globalsBag->set("v_minor", $v_minor ?? null);
$globalsBag->set("v_patch", $v_patch ?? null);
$globalsBag->set("v_tag", $v_tag ?? null);
$globalsBag->set("v_realpatch", $v_realpatch ?? null);
$globalsBag->set("v_database", $v_database ?? null);
$globalsBag->set("v_acl", $v_acl ?? null);
$globalsBag->set("v_js_includes", $v_js_includes ?? null);

// Collecting the utf8 disable flag from the sqlconf.php file in order
// to set the correct html encoding. utf8 vs iso-8859-1. If flag is set
// then set to iso-8859-1.
if (!$disable_utf8_flag) {
    ini_set('default_charset', 'utf-8');
    $HTML_CHARSET = "UTF-8";
    mb_internal_encoding('UTF-8');
} else {
    ini_set('default_charset', 'iso-8859-1');
    $HTML_CHARSET = "ISO-8859-1";
    mb_internal_encoding('ISO-8859-1');
}

// Defaults for specific applications.
$globalsBag->set('weight_loss_clinic', false);
$globalsBag->set('ippf_specific', false);

// Defaults for drugs and products.
$globalsBag->set('inhouse_pharmacy', false);
$globalsBag->set('sell_non_drug_products', 0);

$glrow = sqlQueryNoLog("SHOW TABLES LIKE 'globals'");
if (!empty($glrow)) {
  // Collect user specific settings from user_settings table.
  //
    $gl_user = [];
  // Collect the user id first
    $temp_authuserid = '';
    if (!empty($_SESSION['authUserID'])) {
      //Set the user id from the session variable
        $temp_authuserid = $_SESSION['authUserID'];
    } else {
        if (!empty($_POST['authUser'])) {
            $temp_sql_ret = sqlQueryNoLog("SELECT `id` FROM `users` WHERE BINARY `username` = ?", [$_POST['authUser']]);
            if (!empty($temp_sql_ret['id'])) {
              //Set the user id from the login variable
                $temp_authuserid = $temp_sql_ret['id'];
            }
        }
    }

    if (!empty($temp_authuserid)) {
        $glres_user = sqlStatementNoLog(
            "SELECT `setting_label`, `setting_value` " .
            "FROM `user_settings` " .
            "WHERE `setting_user` = ? " .
            "AND `setting_label` LIKE 'global:%'",
            [$temp_authuserid]
        );
        for ($iter = 0; $row = sqlFetchArray($glres_user); $iter++) {
          //remove global_ prefix from label
            $row['setting_label'] = substr((string) $row['setting_label'], 7);
            $gl_user[$iter] = $row;
        }
    }

  // Set global parameters from the database globals table.
  // Some parameters require custom handling.
  //
    $GLOBALS['language_menu_show'] = [];
    $glres = sqlStatementNoLog(
        "SELECT gl_name, gl_index, gl_value FROM globals " .
        "ORDER BY gl_name, gl_index"
    );
    while ($glrow = sqlFetchArray($glres)) {
        $gl_name  = $glrow['gl_name'];
        $gl_value = $glrow['gl_value'];
      // Adjust for user specific settings
        if (!empty($gl_user)) {
            foreach ($gl_user as $setting) {
                if ($gl_name == $setting['setting_label']) {
                    $gl_value = $setting['setting_value'];
                }
            }
        }

        if ($gl_name == 'language_menu_other') {
            $GLOBALS['language_menu_show'][] = $gl_value;
        } elseif ($gl_name == 'css_header') {
            //Escape css file name using 'attr' for security (prevent XSS).
            if (!file_exists($webserver_root . '/public/themes/' . attr($gl_value))) {
                $gl_value = 'style_light.css';
            }
            $globalsBag->set($gl_name, $web_root . '/public/themes/' . attr($gl_value) . '?v=' . $v_js_includes);
            $globalsBag->set('compact_header', $web_root . '/public/themes/compact_' . attr($gl_value) . '?v=' . $v_js_includes);
            $compact_header = $globalsBag->getString('compact_header');
            $css_header = $globalsBag->get($gl_name);
            $globalsBag->set('css_header', $css_header);
            $temp_css_theme_name = $gl_value;
            $globalsBag->set('temp_css_theme_name', $gl_value);
        } elseif ($gl_name == 'portal_css_header' && $ignoreAuth_onsite_portal) {
            // does patient have a portal theme selected?
            $current_theme = sqlQueryNoLog(
                "SELECT `setting_value` FROM `patient_settings` " .
                "WHERE setting_patient = ? AND `setting_label` = ?",
                [$_SESSION['pid'] ?? 0, 'portal_theme']
            )['setting_value'] ?? null;
            $gl_value = $current_theme ?? null ?: $gl_value;
            $GLOBALS[$gl_name] = $web_root . '/public/themes/' . attr($gl_value) . '?v=' . $v_js_includes;
            $portal_css_header = $GLOBALS[$gl_name];
            $portal_temp_css_theme_name = $gl_value;
        } elseif ($gl_name == 'weekend_days') {
            $globalsBag->set($gl_name, explode(',', (string) $gl_value));
        } elseif ($gl_name == 'specific_application') {
            if ($gl_value == '2') {
                $globalsBag->set('ippf_specific', true);
            } elseif ($gl_value == '3') {
                $globalsBag->set('weight_loss_clinic', true);
            }
        } elseif ($gl_name == 'inhouse_pharmacy') {
            if ($gl_value) {
                $globalsBag->set('inhouse_pharmacy', true);
            }

            if ($gl_value == '2') {
                $globalsBag->set('sell_non_drug_products', 1);
            } elseif ($gl_value == '3') {
                $globalsBag->set('sell_non_drug_products', 2);
            }
        } elseif ($gl_name == 'gbl_time_zone') {
          // The default PHP time zone is set here if it was specified, and is used
          // as source data for the MySQL time zone here and in some other places
          // where MySQL connections are opened.
            if ($gl_value) {
                date_default_timezone_set($gl_value);
            }

          // Synchronize MySQL time zone with PHP time zone.
            sqlStatementNoLog("SET time_zone = ?", [(new DateTime())->format("P")]);
        } else {
            $globalsBag->set($gl_name, $gl_value);
        }
    }
    // Set any user settings that are not also in GLOBALS.
    // This is for modules support.
    foreach ($gl_user as $setting) {
        if (!array_key_exists($setting['setting_label'], $GLOBALS)) {
            $globalsBag->set($setting['setting_label'], $setting['setting_value']);
        }
    }

    // Language cleanup stuff.
    $globalsBag->set('language_menu_login', false);
    if ((!empty($globalsBag->get('language_menu_show')) && count($globalsBag->get('language_menu_show')) > 1) || $globalsBag->get('language_menu_showall')) {
        $globalsBag->set('language_menu_login', true);
    }

    // Added this $GLOBALS['concurrent_layout'] set to 3 in order to support legacy forms
    // that may use this; note this global has been removed from the standard codebase.
    $globalsBag->set('concurrent_layout', 3);

    // Additional logic to override theme name.
    // For RTL languages we substitute the theme name with the name of RTL-adapted CSS file.
    $rtl_override = false;
    $rtl_portal_override = false;
    if (isset($_SESSION['language_direction']) && empty($_SESSION['patient_portal_onsite_two'])) {
        if (
            $_SESSION['language_direction'] == 'rtl' &&
            !strpos((string) $globalsBag->get('css_header', ''), 'rtl')
        ) {
            // the $css_header_value is set above
            $rtl_override = true;
        }
    } elseif (isset($_SESSION['language_choice'])) {
        //this will support the onsite patient portal which will have a language choice but not yet a set language direction
        $_SESSION['language_direction'] = getLanguageDir($_SESSION['language_choice']);
        if (
            $_SESSION['language_direction'] == 'rtl' &&
            !strpos((string) $globalsBag->get('portal_css_header', ''), 'rtl')
        ) {
            // the $css_header_value is set above
            $rtl_portal_override = true;
        }
    } else {
        //$_SESSION['language_direction'] is not set, so will use the default language
        $default_lang_id = sqlQueryNoLog('SELECT lang_id FROM lang_languages WHERE lang_description = ?', [$GLOBALS['language_default'] ?? '']);
        $globalsBag->set('default_lang_id', $default_lang_id);
        if (getLanguageDir($default_lang_id['lang_id'] ?? '') === 'rtl' && !strpos((string) $GLOBALS['css_header'], 'rtl')) {
// @todo eliminate 1 SQL query
            $rtl_override = true;
        }
    }


    // change theme name, if the override file exists.
    if ($rtl_override) {
        // the $css_header_value is set above
        $new_theme = 'rtl_' . $temp_css_theme_name;

        // Check file existance
        if (file_exists($webserver_root . '/public/themes/' . $new_theme)) {
            //Escape css file name using 'attr' for security (prevent XSS).
            $GLOBALS['css_header'] = $web_root . '/public/themes/' . attr($new_theme) . '?v=' . $v_js_includes;
            $css_header = $GLOBALS['css_header'];
            $GLOBALS['compact_header'] = $web_root . '/public/themes/rtl_compact_' . attr($temp_css_theme_name) . '?v=' . $v_js_includes;
            $compact_header = $GLOBALS['compact_header'];
        } else {
            // throw a warning if rtl'ed file does not exist.
            error_log("Missing theme file " . errorLogEscape($webserver_root) . '/public/themes/' . errorLogEscape($new_theme));
        }
    }

    // change portal theme name, if the override file exists.
    if ($rtl_portal_override) {
        // the $css_header_value is set above
        $new_theme = 'rtl_' . $portal_temp_css_theme_name;

        // Check file existance
        if (file_exists($webserver_root . '/public/themes/' . $new_theme)) {
            //Escape css file name using 'attr' for security (prevent XSS).
            $GLOBALS['portal_css_header'] = $web_root . '/public/themes/' . attr($new_theme) . '?v=' . $v_js_includes;
            $globalsBag->set('portal_css_header', $GLOBALS['portal_css_header']);
            $portal_css_header = $globalsBag->getString('portal_css_header');
        } else {
            // throw a warning if rtl'ed file does not exist.
            error_log("Missing theme file " . errorLogEscape($webserver_root) . '/public/themes/' . errorLogEscape($new_theme));
        }
    }
    unset($temp_css_theme_name, $new_theme, $rtl_override, $rtl_portal_override, $portal_temp_css_theme_name);
    // end of RTL section

  //
  // End of globals table processing.
} else {
  // Temporary stuff to handle the case where the globals table does not
  // exist yet.  This will happen in sql_upgrade.php on upgrading to the
  // first release containing this table.
    $globalsBag->set('language_menu_login', true);
    $globalsBag->set('language_menu_showall', true);
    $globalsBag->set('language_menu_show', ['English (Standard)','Swedish']);
    $globalsBag->set('language_default', "English (Standard)");
    $globalsBag->set('translate_layout', true);
    $globalsBag->set('translate_lists', true);
    $globalsBag->set('translate_gacl_groups', true);
    $globalsBag->set('translate_form_titles', true);
    $globalsBag->set('translate_document_categories', true);
    $globalsBag->set('translate_appt_categories', true);
    $globalsBag->set('timeout', 7200);
    $openemr_name = 'OpenEMR';
    $css_header = "$web_root/public/themes/style_default.css";
    $globalsBag->set('openemr_name', $openemr_name);
    $globalsBag->set('css_header', $css_header);
    $compact_header = "$web_root/public/themes/style_default.css";
    $globalsBag->set('compact_header', $compact_header);
    $globalsBag->set('schedule_start', 8);
    $globalsBag->set('schedule_end', 17);
    $globalsBag->set('calendar_interval', 15);
    $globalsBag->set('phone_country_code', '1');
    $globalsBag->set('disable_non_default_groups', true);
    $globalsBag->set('ippf_specific', false);
}

// Migrated this to populate after the standard globals in order to support globals that require
//  more security.
require_once($globalsBag->getString('OE_SITE_DIR') . "/config.php");

// Resolve server globals (use the manual override if set already in globals)
if (empty($globalsBag->getString('site_addr_oath'))) {
    $globalsBag->set('site_addr_oath', $ResolveServerHost());
}
if (empty($globalsBag->getString('qualified_site_addr'))) {
    $globalsBag->set(
        'qualified_site_addr',
        rtrim($globalsBag->getString('site_addr_oath') . trim((string) $globalsBag->getString('webroot')), "/")
    );
}

// Need to utilize a session since library/sql.inc.php is established before there are any globals established yet.
//  This means that the first time, it will be skipped even if the global is turned on. However,
//  after that it will then be turned on via the session.
// Also important to note that changes to this global setting will not take effect during the same
//  session (ie. user needs to logout) since not worth it to use resources to open session and write to it
//  for every call to interface/globals.php .
$_SESSION["enable_database_connection_pooling"] = $globalsBag->get("enable_database_connection_pooling", null);

// If >0 this will enforce a separate PHP session for each top-level
// browser window.  You must log in separately for each.  This is not
// thoroughly tested yet and some browsers might have trouble with it,
// so make it 0 if you must.  Alternatively, you can set it to 2 to be
// notified when the session ID changes.
$globalsBag->set('restore_sessions', 1); // 0=no, 1=yes, 2=yes+debug

// Theme definition.  All this stuff should be moved to CSS.
//
$top_bg_line = ' bgcolor="#dddddd" ';
$globalsStyle = $globalsBag->get('style', []);
$globalsStyle['BGCOLOR2'] = "#dddddd";
$globalsStyle['BGCOLOR1'] = "#cccccc";
$globalsBag->set('style', $globalsStyle);
$logocode = "<img class='img-responsive' src='" . $GLOBALS['OE_SITE_WEBROOT'] . "/images/login_logo.gif' />";
// optimal size for the tiny logo is height 43 width 86 px
// inside the open emr they will be auto reduced
$tinylogocode1 = "<img class='img-responsive d-block mx-auto' src='" . $GLOBALS['OE_SITE_WEBROOT'] . "/images/logo_1.png'>";
$tinylogocode2 = "<img class='img-responsive d-block mx-auto' src='" . $GLOBALS['OE_SITE_WEBROOT'] . "/images/logo_2.png'>";
$globalsBag->set('logocode', $logocode);
$globalsBag->set('tinylogocode1', $tinylogocode1);
$globalsBag->set('tinylogocode2', $tinylogocode2);

// The height in pixels of the Title bar:
$globalsBag->set('titleBarHeight', 50);

// The assistant word, MORE printed next to titles that can be clicked:
//   Note this label gets translated here via the xl function
//    -if you don't want it translated, then strip the xl function away
$tmore = xl('(More)');
// The assistant word, BACK printed next to titles that return to previous screens:
//   Note this label gets translated here via the xl function
//    -if you don't want it translated, then strip the xl function away
$tback = xl('(Back)');
$globalsBag->set('tmore', $tmore);
$globalsBag->set('tback', $tback);

$srcdir = $globalsBag->getString('srcdir');
$login_screen = $globalsBag->getString('login_screen');
$globalsBag->set('backpic', $backpic ?? '');

// 1 = send email message to given id for Emergency Login user activation,
// else 0.
$globalsBag->set('Emergency_Login_email', empty($GLOBALS['Emergency_Login_email_id']) ? 0 : 1);

//set include_de_identification to enable De-identification (currently de-identification works fine only with linux machines)
//Run de_identification_upgrade.php script to upgrade OpenEMR database to include procedures,
//functions, tables for de-identification(Mysql root user and password is required for successful
//execution of the de-identification upgrade script)
$globalsBag->set('include_de_identification', 0);
// Include the authentication module code here, but the rule is
// if the file has the word "login" in the source code file name,
// don't include the authentication module - we do this to avoid
// include loops.

// EMAIL SETTINGS
$globalsBag->set('SMTP_Auth', !empty($globalsBag->get('SMTP_USER', null)));

if (($ignoreAuth_onsite_portal === true) && ($globalsBag->getInt('portal_onsite_two_enable', 0) == 1)) {
    $ignoreAuth = true;
}

if (!$ignoreAuth) {
    require_once("$srcdir/auth.inc.php");
    $globalsBag->set('incoming_site_id', $GLOBALS['incoming_site_id'] ?? null);
}

// This is the background color to apply to form fields that are searchable.
// Currently it is applicable only to the "Search or Add Patient" form.
$globalsBag->set('layout_search_color', '#ff9919');

// module configurations
// upgrade fails for versions prior to 4.2.0 since no modules table
try {
    $checkModulesTableExists = sqlQueryNoLog('SELECT 1 FROM `modules`', false, true);
} catch (\Exception $ex) {
    error_log(errorLogEscape($ex->getMessage() . $ex->getTraceAsString()));
}

if (!empty($checkModulesTableExists)) {
    $globalsBag->set('baseModDir', "interface/modules/"); //default path of modules
    $globalsBag->set('customModDir', "custom_modules"); //non zend modules
    $globalsBag->set('zendModDir', "zend_modules"); //zend modules

    try {
        // load up the modules system and bootstrap them.
        // This has to be fast, so any modules that tie into the bootstrap must be kept lightweight
        // registering event listeners, etc.
        // TODO: why do we have 3 different directories we need to pass in for the zend dir path. shouldn't zendModDir already have all the paths set up?
        /** @var ModulesApplication */
        $globalsBag->set('modules_application', new ModulesApplication(
            $globalsBag->get('kernel'),
            $globalsBag->getString('fileroot'),
            $globalsBag->getString('baseModDir'),
            $globalsBag->getString('zendModDir')
        ));
    } catch (\OpenEMR\Common\Acl\AccessDeniedException $accessDeniedException) {
        // this occurs when the current SCRIPT_PATH is to a module that is not currently allowed to be accessed
        http_response_code(401);
        error_log(errorLogEscape($accessDeniedException->getMessage() . $accessDeniedException->getTraceAsString()));
    } catch (\Exception $ex) {
        error_log(errorLogEscape($ex->getMessage() . $ex->getTraceAsString()));
        die();
    }
}

// Don't change anything below this line. ////////////////////////////

$encounter = empty($_SESSION['encounter']) ? 0 : $_SESSION['encounter'];

if (!empty($_GET['pid']) && empty($_SESSION['pid'])) {
    OpenEMR\Common\Session\SessionUtil::setSession('pid', $_GET['pid']);
} elseif (!empty($_POST['pid']) && empty($_SESSION['pid'])) {
    OpenEMR\Common\Session\SessionUtil::setSession('pid', $_POST['pid']);
}

$pid = empty($_SESSION['pid']) ? 0 : $_SESSION['pid'];
$userauthorized = empty($_SESSION['userauthorized']) ? 0 : $_SESSION['userauthorized'];
$groupname = empty($_SESSION['authProvider']) ? 0 : $_SESSION['authProvider'];

//This is crucial for therapy groups and patients mechanisms to work together properly
$attendant_type = (empty($pid) && isset($_SESSION['therapy_group'])) ? 'gid' : 'pid';
$therapy_group = (empty($pid) && isset($_SESSION['therapy_group'])) ? $_SESSION['therapy_group'] : 0;
$globalsBag->set('encounter', $encounter);
$globalsBag->set('pid', $pid);
$globalsBag->set('userauthorized', $userauthorized);
$globalsBag->set('groupname', $groupname);
$globalsBag->set('attendant_type', $attendant_type);
$globalsBag->set('groupname', $groupname);

// global interface function to format text length using ellipses
function strterm($string, $length)
{
    if (strlen((string) $string) >= ($length - 3)) {
        return substr((string) $string, 0, $length - 3) . "...";
    } else {
        return $string;
    }
}

// Helper function to generate an image URL that defeats browser/proxy caching when needed.
function UrlIfImageExists($filename, $append = true)
{
    global $webserver_root, $web_root;
    $path = "sites/" . $_SESSION['site_id'] . "/images/$filename";
    // @ in next line because a missing file is not an error.
    if ($stat = @stat("$webserver_root/$path")) {
        if ($append) {
            return "$web_root/$path?v=" . $stat['mtime'];
        } else {
            return "$web_root/$path";
        }
    }
    return '';
}

// Override temporary_files_dir
$globalsBag->set('temporary_files_dir', rtrim(sys_get_temp_dir(), '/'));

error_reporting(error_reporting() & ~E_USER_DEPRECATED & ~E_USER_WARNING);
// user debug mode
if ($globalsBag->getInt('user_debug', 0) > 1) {
    error_reporting(error_reporting() & ~E_WARNING & ~E_NOTICE & ~E_USER_WARNING & ~E_USER_DEPRECATED);
    ini_set('display_errors', 1);
}
EventAuditLogger::instance()->logHttpRequest();

return $globalsBag; // if anyone wants to use the global bag they can just use the return value
