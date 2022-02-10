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
    $possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
    $sourceTransformations = array(
        "HTTP_X_FORWARDED_HOST" => function ($value) {
            $elements = explode(',', $value);
            return trim(end($elements));
        }
    );
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
$read_only = empty($sessionAllowWrite);
if (session_status() === PHP_SESSION_NONE) {
    //error_log("1. LOCK ".GetCallingScriptName()); // debug start lock
    require_once(__DIR__ . "/../src/Common/Session/SessionUtil.php");
    OpenEMR\Common\Session\SessionUtil::coreSessionStart($web_root, $read_only);
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
                $GLOBALS['login_screen'] = "login_screen.php";
                $srcdir = "../library";
                require_once("$srcdir/auth.inc");
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
    if (empty($tmp) || preg_match('/[^A-Za-z0-9\\-.]/', $tmp)) {
        echo "Invalid URL";
        error_log("Request with site id '" . htmlspecialchars($tmp, ENT_QUOTES) . "' contains invalid characters.");
        die();
    }

    if (isset($_SESSION['site_id']) && ($_SESSION['site_id'] != $tmp)) {
      // This is to prevent using session to penetrate other OpenEMR instances within same multisite module
        session_unset(); // clear session, clean logout
        if (isset($landingpage) && !empty($landingpage)) {
          // OpenEMR Patient Portal use
            header('Location: index.php?site=' . urlencode($tmp));
        } else {
          // Main OpenEMR use
            header('Location: ../login/login.php?site=' . urlencode($tmp)); // Assuming in the interface/main directory
        }

        exit;
    }

    if (!isset($_SESSION['site_id']) || $_SESSION['site_id'] != $tmp) {
        $_SESSION['site_id'] = $tmp;
        // error_log("Session site ID has been set to '$tmp'"); // debugging
    }
}

// Set the site-specific directory path.
$GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . "/" . $_SESSION['site_id'];

// Set a site-specific uri root path.
$GLOBALS['OE_SITE_WEBROOT'] = $web_root . "/sites/" . $_SESSION['site_id'];


// Root directory, relative to the webserver root:
$GLOBALS['rootdir'] = "$web_root/interface";
$rootdir = $GLOBALS['rootdir'];
// Absolute path to the source code include and headers file directory (Full path):
$GLOBALS['srcdir'] = "$webserver_root/library";
// Absolute path to the location of documentroot directory for use with include statements:
$GLOBALS['fileroot'] = "$webserver_root";
// Absolute path to the location of interface directory for use with include statements:
$include_root = "$webserver_root/interface";
// Absolute path to the location of documentroot directory for use with include statements:
$GLOBALS['webroot'] = $web_root;

// Static assets directory, relative to the webserver root.
// (it is very likely that this path will be changed in the future))
$GLOBALS['assets_static_relative'] = "$web_root/public/assets";

// Relative themes directory, relative to the webserver root.
$GLOBALS['themes_static_relative'] = "$web_root/public/themes";

// Relative images directory, relative to the webserver root.
$GLOBALS['images_static_relative'] = "$web_root/public/images";

// Static images directory, absolute to the webserver root.
$GLOBALS['images_static_absolute'] = "$webserver_root/public/images";

//Composer vendor directory, absolute to the webserver root.
$GLOBALS['vendor_dir'] = "$webserver_root/vendor";
$GLOBALS['fonts_dir'] = "{$web_root}/public/fonts";
$GLOBALS['template_dir'] = $GLOBALS['fileroot'] . "/templates/";
$GLOBALS['incdir'] = $include_root;
// Location of the login screen file
$GLOBALS['login_screen'] = $GLOBALS['rootdir'] . "/login_screen.php";

// Variable set for Eligibility Verification [EDI-271] path
$GLOBALS['edi_271_file_path'] = $GLOBALS['OE_SITE_DIR'] . "/documents/edi/";

//  Check necessary writable paths (add them if do not exist)
if (! is_dir($GLOBALS['OE_SITE_DIR'] . '/documents/smarty/gacl')) {
    if (!mkdir($concurrentDirectory = $GLOBALS['OE_SITE_DIR'] . '/documents/smarty/gacl', 0755, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}
if (! is_dir($GLOBALS['OE_SITE_DIR'] . '/documents/smarty/main')) {
    if (!mkdir($concurrentDirectory = $GLOBALS['OE_SITE_DIR'] . '/documents/smarty/main', 0755, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

//  Set and check that necessary writeable path exist for mPDF tool
$GLOBALS['MPDF_WRITE_DIR'] = $GLOBALS['OE_SITE_DIR'] . '/documents/mpdf/pdf_tmp';
if (! is_dir($GLOBALS['MPDF_WRITE_DIR'])) {
    if (!mkdir($concurrentDirectory = $GLOBALS['MPDF_WRITE_DIR'], 0755, true) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

// Includes composer autoload
// Note this also brings in following library files:
//  library/htmlspecialchars.inc.php - Include convenience functions with shorter names than "htmlspecialchars" (for security)
//  library/formdata.inc.php - Include sanitization/checking functions (for security)
//  library/sanitize.inc.php - Include sanitization/checking functions (for security)
//  library/formatting.inc.php - Includes functions for date/time internationalization and formatting
//  library/date_functions.php - Includes functions for date internationalization
//  library/validation/validate_core.php - Includes functions for page validation
//  library/translation.inc.php - Includes translation functions
require_once $GLOBALS['vendor_dir'] . "/autoload.php";

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
$GLOBALS["log_level"] = "OFF";

try {
    /** @var Kernel */
    $GLOBALS["kernel"] = new Kernel();
} catch (\Exception $e) {
    error_log(errorLogEscape($e->getMessage()));
    die();
}

// This will open the openemr mysql connection.
require_once(__DIR__ . "/../library/sql.inc");

// Include the version file
require_once(__DIR__ . "/../version.php");

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
$GLOBALS['weight_loss_clinic'] = false;
$GLOBALS['ippf_specific'] = false;

// Defaults for drugs and products.
$GLOBALS['inhouse_pharmacy'] = false;
$GLOBALS['sell_non_drug_products'] = 0;

$glrow = sqlQueryNoLog("SHOW TABLES LIKE 'globals'");
if (!empty($glrow)) {
  // Collect user specific settings from user_settings table.
  //
    $gl_user = array();
  // Collect the user id first
    $temp_authuserid = '';
    if (!empty($_SESSION['authUserID'])) {
      //Set the user id from the session variable
        $temp_authuserid = $_SESSION['authUserID'];
    } else {
        if (!empty($_POST['authUser'])) {
            $temp_sql_ret = sqlQueryNoLog("SELECT `id` FROM `users` WHERE BINARY `username` = ?", array($_POST['authUser']));
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
            array($temp_authuserid)
        );
        for ($iter = 0; $row = sqlFetchArray($glres_user); $iter++) {
          //remove global_ prefix from label
            $row['setting_label'] = substr($row['setting_label'], 7);
            $gl_user[$iter] = $row;
        }
    }

  // Set global parameters from the database globals table.
  // Some parameters require custom handling.
  //
    $GLOBALS['language_menu_show'] = array();
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
            $GLOBALS[$gl_name] = $web_root . '/public/themes/' . attr($gl_value) . '?v=' . $v_js_includes;
            $GLOBALS['compact_header'] = $web_root . '/public/themes/compact_' . attr($gl_value) . '?v=' . $v_js_includes;
            $compact_header = $GLOBALS['compact_header'];
            $css_header = $GLOBALS[$gl_name];
            $temp_css_theme_name = $gl_value;
        } elseif ($gl_name == 'weekend_days') {
            $GLOBALS[$gl_name] = explode(',', $gl_value);
        } elseif ($gl_name == 'specific_application') {
            if ($gl_value == '2') {
                $GLOBALS['ippf_specific'] = true;
            } elseif ($gl_value == '3') {
                $GLOBALS['weight_loss_clinic'] = true;
            }
        } elseif ($gl_name == 'inhouse_pharmacy') {
            if ($gl_value) {
                $GLOBALS['inhouse_pharmacy'] = true;
            }

            if ($gl_value == '2') {
                $GLOBALS['sell_non_drug_products'] = 1;
            } elseif ($gl_value == '3') {
                $GLOBALS['sell_non_drug_products'] = 2;
            }
        } elseif ($gl_name == 'gbl_time_zone') {
          // The default PHP time zone is set here if it was specified, and is used
          // as source data for the MySQL time zone here and in some other places
          // where MySQL connections are opened.
            if ($gl_value) {
                date_default_timezone_set($gl_value);
            }

          // Synchronize MySQL time zone with PHP time zone.
            sqlStatementNoLog("SET time_zone = ?", array((new DateTime())->format("P")));
        } else {
            $GLOBALS[$gl_name] = $gl_value;
        }
    }

  // Language cleanup stuff.
    $GLOBALS['language_menu_login'] = false;
    if ((count($GLOBALS['language_menu_show']) > 1) || $GLOBALS['language_menu_showall']) {
        $GLOBALS['language_menu_login'] = true;
    }

  // Added this $GLOBALS['concurrent_layout'] set to 3 in order to support legacy forms
  // that may use this; note this global has been removed from the standard codebase.
    $GLOBALS['concurrent_layout'] = 3;

// Additional logic to override theme name.
// For RTL languages we substitute the theme name with the name of RTL-adapted CSS file.
    $rtl_override = false;
    if (isset($_SESSION['language_direction'])) {
        if (
            $_SESSION['language_direction'] == 'rtl' &&
            !strpos($GLOBALS['css_header'], 'rtl')
        ) {
            // the $css_header_value is set above
            $rtl_override = true;
        }
    } elseif (isset($_SESSION['language_choice'])) {
        //this will support the onsite patient portal which will have a language choice but not yet a set language direction
        $_SESSION['language_direction'] = getLanguageDir($_SESSION['language_choice']);
        if (
            $_SESSION['language_direction'] == 'rtl' &&
            !strpos($GLOBALS['css_header'], 'rtl')
        ) {
            // the $css_header_value is set above
            $rtl_override = true;
        }
    } else {
        //$_SESSION['language_direction'] is not set, so will use the default language
        $default_lang_id = sqlQueryNoLog('SELECT lang_id FROM lang_languages WHERE lang_description = ?', array($GLOBALS['language_default']));

        if (getLanguageDir($default_lang_id['lang_id']) === 'rtl' && !strpos($GLOBALS['css_header'], 'rtl')) {
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

    unset($temp_css_theme_name, $new_theme, $rtl_override);
    // end of RTL section

  //
  // End of globals table processing.
} else {
  // Temporary stuff to handle the case where the globals table does not
  // exist yet.  This will happen in sql_upgrade.php on upgrading to the
  // first release containing this table.
    $GLOBALS['language_menu_login'] = true;
    $GLOBALS['language_menu_showall'] = true;
    $GLOBALS['language_menu_show'] = array('English (Standard)','Swedish');
    $GLOBALS['language_default'] = "English (Standard)";
    $GLOBALS['translate_layout'] = true;
    $GLOBALS['translate_lists'] = true;
    $GLOBALS['translate_gacl_groups'] = true;
    $GLOBALS['translate_form_titles'] = true;
    $GLOBALS['translate_document_categories'] = true;
    $GLOBALS['translate_appt_categories'] = true;
    $GLOBALS['timeout'] = 7200;
    $openemr_name = 'OpenEMR';
    $css_header = "$web_root/public/themes/style_default.css";
    $GLOBALS['css_header'] = $css_header;
    $compact_header = "$web_root/public/themes/style_default.css";
    $GLOBALS['compact_header'] = $compact_header;
    $GLOBALS['schedule_start'] = 8;
    $GLOBALS['schedule_end'] = 17;
    $GLOBALS['calendar_interval'] = 15;
    $GLOBALS['phone_country_code'] = '1';
    $GLOBALS['disable_non_default_groups'] = true;
    $GLOBALS['ippf_specific'] = false;
}

// Migrated this to populate after the standard globals in order to support globals that require
//  more security.
require_once($GLOBALS['OE_SITE_DIR'] . "/config.php");

// Resolve server globals (use the manual override if set already in globals)
if (empty($GLOBALS['site_addr_oath'])) {
    $GLOBALS['site_addr_oath'] = $ResolveServerHost();
}
if (empty($GLOBALS['qualified_site_addr'])) {
    $GLOBALS['qualified_site_addr'] = rtrim($GLOBALS['site_addr_oath'] . trim($GLOBALS['webroot']), "/");
}

// Need to utilize a session since library/sql.inc is established before there are any globals established yet.
//  This means that the first time, it will be skipped even if the global is turned on. However,
//  after that it will then be turned on via the session.
// Also important to note that changes to this global setting will not take effect during the same
//  session (ie. user needs to logout) since not worth it to use resources to open session and write to it
//  for every call to interface/globals.php .
$_SESSION["enable_database_connection_pooling"] = $GLOBALS["enable_database_connection_pooling"] ?? null;

// If >0 this will enforce a separate PHP session for each top-level
// browser window.  You must log in separately for each.  This is not
// thoroughly tested yet and some browsers might have trouble with it,
// so make it 0 if you must.  Alternatively, you can set it to 2 to be
// notified when the session ID changes.
$GLOBALS['restore_sessions'] = 1; // 0=no, 1=yes, 2=yes+debug

// Theme definition.  All this stuff should be moved to CSS.
//
$top_bg_line = ' bgcolor="#dddddd" ';
$GLOBALS['style']['BGCOLOR2'] = "#dddddd";
$logocode = "<img class='img-responsive' src='" . $GLOBALS['OE_SITE_WEBROOT'] . "/images/login_logo.gif' />";
// optimal size for the tiny logo is height 43 width 86 px
// inside the open emr they will be auto reduced
$tinylogocode1 = "<img class='img-responsive d-block mx-auto' src='" . $GLOBALS['OE_SITE_WEBROOT'] . "/images/logo_1.png'>";
$tinylogocode2 = "<img class='img-responsive d-block mx-auto' src='" . $GLOBALS['OE_SITE_WEBROOT'] . "/images/logo_2.png'>";

$GLOBALS['style']['BGCOLOR1'] = "#cccccc";
// The height in pixels of the Title bar:
$GLOBALS['titleBarHeight'] = 50;

// The assistant word, MORE printed next to titles that can be clicked:
//   Note this label gets translated here via the xl function
//    -if you don't want it translated, then strip the xl function away
$tmore = xl('(More)');
// The assistant word, BACK printed next to titles that return to previous screens:
//   Note this label gets translated here via the xl function
//    -if you don't want it translated, then strip the xl function away
$tback = xl('(Back)');

$srcdir = $GLOBALS['srcdir'];
$login_screen = $GLOBALS['login_screen'];
$GLOBALS['backpic'] = $backpic ?? '';

// 1 = send email message to given id for Emergency Login user activation,
// else 0.
$GLOBALS['Emergency_Login_email'] = empty($GLOBALS['Emergency_Login_email_id']) ? 0 : 1;

//set include_de_identification to enable De-identification (currently de-identification works fine only with linux machines)
//Run de_identification_upgrade.php script to upgrade OpenEMR database to include procedures,
//functions, tables for de-identification(Mysql root user and password is required for successful
//execution of the de-identification upgrade script)
$GLOBALS['include_de_identification'] = 0;
// Include the authentication module code here, but the rule is
// if the file has the word "login" in the source code file name,
// don't include the authentication module - we do this to avoid
// include loops.

if (($ignoreAuth_onsite_portal === true) && ($GLOBALS['portal_onsite_two_enable'] == 1)) {
    $ignoreAuth = true;
}

if (!$ignoreAuth) {
    require_once("$srcdir/auth.inc");
}

// This is the background color to apply to form fields that are searchable.
// Currently it is applicable only to the "Search or Add Patient" form.
$GLOBALS['layout_search_color'] = '#ff9919';

// EMAIL SETTINGS
$SMTP_Auth = !empty($GLOBALS['SMTP_USER']);

// module configurations
// upgrade fails for versions prior to 4.2.0 since no modules table
// so perform this check to avoid sql error
if (!file_exists($webserver_root . "/interface/modules/")) {
    error_log("The modules directory does not exist thus not loading modules.");
} else {
    $GLOBALS['baseModDir'] = "interface/modules/"; //default path of modules
    $GLOBALS['customModDir'] = "custom_modules"; //non zend modules
    $GLOBALS['zendModDir'] = "zend_modules"; //zend modules

    try {
        // load up the modules system and bootstrap them.
        // This has to be fast, so any modules that tie into the bootstrap must be kept lightweight
        // registering event listeners, etc.
        // TODO: why do we have 3 different directories we need to pass in for the zend dir path. shouldn't zendModDir already have all the paths set up?
        /** @var ModulesApplication */
        $GLOBALS['modules_application'] = new ModulesApplication(
            $GLOBALS["kernel"],
            $GLOBALS['fileroot'],
            $GLOBALS['baseModDir'],
            $GLOBALS['zendModDir']
        );
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

// global interface function to format text length using ellipses
function strterm($string, $length)
{
    if (strlen($string) >= ($length - 3)) {
        return substr($string, 0, $length - 3) . "...";
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
$GLOBALS['temporary_files_dir'] = rtrim(sys_get_temp_dir(), '/');

error_reporting(error_reporting() & ~E_USER_DEPRECATED & ~E_USER_WARNING);
// user debug mode
if (!empty($GLOBALS['user_debug']) && ((int) $GLOBALS['user_debug'] > 1)) {
    error_reporting(error_reporting() & ~E_WARNING & ~E_NOTICE & ~E_USER_WARNING & ~E_USER_DEPRECATED);
    ini_set('display_errors', 1);
}
