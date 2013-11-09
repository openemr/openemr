<?php

// Is this windows or non-windows? Create a boolean definition.
if (!defined('IS_WINDOWS'))
 define('IS_WINDOWS', (stripos(PHP_OS,'WIN') === 0));

// Some important php.ini overrides. Defaults for these values are often
// too small.  You might choose to adjust them further.
//
ini_set('session.gc_maxlifetime', '14400');

/* If the includer didn't specify, assume they want us to "fake" register_globals. */
if (!isset($fake_register_globals)) {
	$fake_register_globals = TRUE;
}

/* Pages with "myadmin" in the URL don't need register_globals. */
$fake_register_globals =
	$fake_register_globals && (strpos($_SERVER['REQUEST_URI'],"myadmin") === FALSE);

// Emulates register_globals = On.  Moved to here from the bottom of this file
// to address security issues.  Need to change everything requiring this!
if ($fake_register_globals) {
  extract($_GET);
  extract($_POST);
}

// This is for sanitization of all escapes.
//  (ie. reversing magic quotes if it's set)
if (isset($sanitize_all_escapes) && $sanitize_all_escapes) {
  if (get_magic_quotes_gpc()) {
    function undoMagicQuotes($array, $topLevel=true) {
      $newArray = array();
      foreach($array as $key => $value) {
        if (!$topLevel) {
          $key = stripslashes($key);
        }
        if (is_array($value)) {
          $newArray[$key] = undoMagicQuotes($value, false);
        }
        else {
          $newArray[$key] = stripslashes($value);
        }
      }
      return $newArray;
    }
    $_GET = undoMagicQuotes($_GET);
    $_POST = undoMagicQuotes($_POST);
    $_COOKIE = undoMagicQuotes($_COOKIE);
    $_REQUEST = undoMagicQuotes($_REQUEST);
  }
}

//
// The webserver_root and web_root are now automatically collected.
// If not working, can set manually below.
// Auto collect the full absolute directory path for openemr.
$webserver_root = dirname(dirname(__FILE__));
if (IS_WINDOWS) {
 //convert windows path separators
 $webserver_root = str_replace("\\","/",$webserver_root); 
}
// Auto collect the relative html path, i.e. what you would type into the web
// browser after the server address to get to OpenEMR.
$web_root = substr($webserver_root, strlen($_SERVER['DOCUMENT_ROOT']));
// Ensure web_root starts with a path separator
if (preg_match("/^[^\/]/",$web_root)) {
 $web_root = "/".$web_root;
}
// The webserver_root and web_root are now automatically collected in
//  real time per above code. If above is not working, can uncomment and
//  set manually here:
//   $webserver_root = "/var/www/openemr";
//   $web_root =  "/openemr";
//

// This is the directory that contains site-specific data.  Change this
// only if you have some reason to.
$GLOBALS['OE_SITES_BASE'] = "$webserver_root/sites";

// The session name names a cookie stored in the browser.
// If you modify session_name, then need to place the identical name in
// the phpmyadmin file here: openemr/phpmyadmin/libraries/session.inc.php
// at line 71. This was required after embedded new phpmyadmin version on
// 05-12-2009 by Brady. Hopefully will figure out a more appropriate fix.
// Now that restore_session() is implemented in javaScript, session IDs are
// effectively saved in the top level browser window and there is no longer
// any need to change the session name for different OpenEMR instances.
session_name("OpenEMR");

session_start();

// Set the site ID if required.  This must be done before any database
// access is attempted.
if (empty($_SESSION['site_id']) || !empty($_GET['site'])) {
  if (!empty($_GET['site'])) {
    $tmp = $_GET['site'];
  }
  else {
    if (!$ignoreAuth) die("Site ID is missing from session data!");
    $tmp = $_SERVER['HTTP_HOST'];
    if (!is_dir($GLOBALS['OE_SITES_BASE'] . "/$tmp")) $tmp = "default";
  }
  if (empty($tmp) || preg_match('/[^A-Za-z0-9\\-.]/', $tmp))
    die("Site ID '". htmlspecialchars($tmp,ENT_NOQUOTES) . "' contains invalid characters.");
  if (isset($_SESSION['site_id']) && ($_SESSION['site_id'] != $tmp)) {
    // This is to prevent using session to penetrate other OpenEMR instances within same multisite module
    session_unset(); // clear session, clean logout
    if (isset($landingpage) && !empty($landingpage)) {
      // OpenEMR Patient Portal use
      header('Location: index.php?site='.$tmp);
    }
    else {
      // Main OpenEMR use
      header('Location: ../login/login_frame.php?site='.$tmp); // Assuming in the interface/main directory
    }
    exit;
  }
  if (!isset($_SESSION['site_id']) || $_SESSION['site_id'] != $tmp) {
    $_SESSION['site_id'] = $tmp;
    //error_log("Session site ID has been set to '$tmp'"); // debugging
  }
}

// Set the site-specific directory path.
$GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . "/" . $_SESSION['site_id'];

require_once($GLOBALS['OE_SITE_DIR'] . "/config.php");

// Collecting the utf8 disable flag from the sqlconf.php file in order
// to set the correct html encoding. utf8 vs iso-8859-1. If flag is set
// then set to iso-8859-1.
require_once(dirname(__FILE__) . "/../library/sqlconf.php");
if (!$disable_utf8_flag) {    
 ini_set('default_charset', 'utf-8');
 $HTML_CHARSET = "UTF-8";
}
else {
 ini_set('default_charset', 'iso-8859-1');
 $HTML_CHARSET = "ISO-8859-1";
}

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

$GLOBALS['template_dir'] = $GLOBALS['fileroot'] . "/templates/";
$GLOBALS['incdir'] = $include_root;
// Location of the login screen file
$GLOBALS['login_screen'] = $GLOBALS['rootdir'] . "/login_screen.php";

// Variable set for Eligibility Verification [EDI-271] path 
$GLOBALS['edi_271_file_path'] = $GLOBALS['OE_SITE_DIR'] . "/edi/";

// Include the translation engine. This will also call sql.inc to
//  open the openemr mysql connection.
include_once (dirname(__FILE__) . "/../library/translation.inc.php");

// Include convenience functions with shorter names than "htmlspecialchars" (for security)
require_once (dirname(__FILE__) . "/../library/htmlspecialchars.inc.php");

// Include sanitization/checking functions (for security)
require_once (dirname(__FILE__) . "/../library/formdata.inc.php");

// Include sanitization/checking function (for security)
require_once (dirname(__FILE__) . "/../library/sanitize.inc.php");

// Includes functions for date internationalization
include_once (dirname(__FILE__) . "/../library/date_functions.php");

// Defaults for specific applications.
$GLOBALS['athletic_team'] = false;
$GLOBALS['weight_loss_clinic'] = false;
$GLOBALS['ippf_specific'] = false;
$GLOBALS['cene_specific'] = false;

// Defaults for drugs and products.
$GLOBALS['inhouse_pharmacy'] = false;
$GLOBALS['sell_non_drug_products'] = 0;

$glrow = sqlQuery("SHOW TABLES LIKE 'globals'");
if (!empty($glrow)) {
  // Collect user specific settings from user_settings table.
  //
  $gl_user = array();
  if (!empty($_SESSION['authUserID'])) {
    $glres_user = sqlStatement("SELECT `setting_label`, `setting_value` " .
      "FROM `user_settings` " .
      "WHERE `setting_user` = ? " .
      "AND `setting_label` LIKE 'global:%'", array($_SESSION['authUserID']) );
    for($iter=0; $row=sqlFetchArray($glres_user); $iter++) {
      //remove global_ prefix from label
      $row['setting_label'] = substr($row['setting_label'],7);
      $gl_user[$iter]=$row;
    }
  }
  // Set global parameters from the database globals table.
  // Some parameters require custom handling.
  //
  $GLOBALS['language_menu_show'] = array();
  $glres = sqlStatement("SELECT gl_name, gl_index, gl_value FROM globals " .
    "ORDER BY gl_name, gl_index");
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
    }
    else if ($gl_name == 'css_header') {
      $GLOBALS[$gl_name] = "$rootdir/themes/" . $gl_value;
    }
    else if ($gl_name == 'specific_application') {
      if      ($gl_value == '1') $GLOBALS['athletic_team'] = true;
      else if ($gl_value == '2') $GLOBALS['ippf_specific'] = true;
      else if ($gl_value == '3') $GLOBALS['weight_loss_clinic'] = true;
    }
    else if ($gl_name == 'inhouse_pharmacy') {
      if ($gl_value) $GLOBALS['inhouse_pharmacy'] = true;
      if ($gl_value == '2') $GLOBALS['sell_non_drug_products'] = 1;
      else if ($gl_value == '3') $GLOBALS['sell_non_drug_products'] = 2;
    }
    else {
      $GLOBALS[$gl_name] = $gl_value;
    }
  }
  // Language cleanup stuff.
  $GLOBALS['language_menu_login'] = false;
  if ((count($GLOBALS['language_menu_show']) >= 1) || $GLOBALS['language_menu_showall']) {
    $GLOBALS['language_menu_login'] = true;
  }
  //
  // End of globals table processing.
}
else {
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
  $GLOBALS['concurrent_layout'] = 2;
  $timeout = 7200;
  $openemr_name = 'OpenEMR';
  $css_header = "$rootdir/themes/style_default.css";
  $GLOBALS['css_header'] = $css_header;
  $GLOBALS['schedule_start'] = 8;
  $GLOBALS['schedule_end'] = 17;
  $GLOBALS['calendar_interval'] = 15;
  $GLOBALS['phone_country_code'] = '1';
  $GLOBALS['disable_non_default_groups'] = true;
  $GLOBALS['ippf_specific'] = false;
}

// If >0 this will enforce a separate PHP session for each top-level
// browser window.  You must log in separately for each.  This is not
// thoroughly tested yet and some browsers might have trouble with it,
// so make it 0 if you must.  Alternatively, you can set it to 2 to be
// notified when the session ID changes.
$GLOBALS['restore_sessions'] = 1; // 0=no, 1=yes, 2=yes+debug

// Theme definition.  All this stuff should be moved to CSS.
//
if ($GLOBALS['concurrent_layout']) {
 $top_bg_line = ' bgcolor="#dddddd" ';
 $GLOBALS['style']['BGCOLOR2'] = "#dddddd";
 $bottom_bg_line = $top_bg_line;
 $title_bg_line = ' bgcolor="#bbbbbb" ';
 $nav_bg_line = ' bgcolor="#94d6e7" ';
} else {
 $top_bg_line = ' bgcolor="#94d6e7" ';
 $GLOBALS['style']['BGCOLOR2'] = "#94d6e7";
 $bottom_bg_line = ' background="'.$rootdir.'/pic/aquabg.gif" ';
 $title_bg_line = ' bgcolor="#aaffff" ';
 $nav_bg_line = ' bgcolor="#94d6e7" ';
}
$login_filler_line = ' bgcolor="#f7f0d5" ';
$logocode = "<img src='$web_root/sites/" . $_SESSION['site_id'] . "/images/login_logo.gif'>";
$linepic = "$rootdir/pic/repeat_vline9.gif";
$table_bg = ' bgcolor="#cccccc" ';
$GLOBALS['style']['BGCOLOR1'] = "#cccccc";
$GLOBALS['style']['TEXTCOLOR11'] = "#222222";
$GLOBALS['style']['HIGHLIGHTCOLOR'] = "#dddddd";
$GLOBALS['style']['BOTTOM_BG_LINE'] = $bottom_bg_line;
// The height in pixels of the Logo bar at the top of the login page:
$GLOBALS['logoBarHeight'] = 110;
// The height in pixels of the Navigation bar:
$GLOBALS['navBarHeight'] = 22;
// The height in pixels of the Title bar:
$GLOBALS['titleBarHeight'] = 40;

// The assistant word, MORE printed next to titles that can be clicked:
//   Note this label gets translated here via the xl function
//    -if you don't want it translated, then strip the xl function away
$tmore = xl('(More)');
// The assistant word, BACK printed next to titles that return to previous screens:
//   Note this label gets translated here via the xl function
//    -if you don't want it translated, then strip the xl function away
$tback = xl('(Back)');

// This is the idle logout function:
// if a page has not been refreshed within this many seconds, the interface
// will return to the login page
if (!empty($special_timeout)) {
  $timeout = intval($special_timeout);
}

//Version tag
require_once(dirname(__FILE__) . "/../version.php");
$patch_appending = "";
if ( ($v_realpatch != '0') && (!(empty($v_realpatch))) ) {
$patch_appending = " (".$v_realpatch.")";
}
$openemr_version = "$v_major.$v_minor.$v_patch".$v_tag.$patch_appending;

$srcdir = $GLOBALS['srcdir'];
$login_screen = $GLOBALS['login_screen'];
$GLOBALS['css_header'] = $css_header;
$GLOBALS['backpic'] = $backpic;

// 1 = send email message to given id for Emergency Login user activation,
// else 0.
$GLOBALS['Emergency_Login_email'] = $GLOBALS['Emergency_Login_email_id'] ? 1 : 0;

//set include_de_identification to enable De-identification (currently de-identification works fine only with linux machines)
//Run de_identification_upgrade.php script to upgrade OpenEMR database to include procedures,  
//functions, tables for de-identification(Mysql root user and password is required for successful
//execution of the de-identification upgrade script)
$GLOBALS['include_de_identification']=0;
// Include the authentication module code here, but the rule is
// if the file has the word "login" in the source code file name,
// don't include the authentication module - we do this to avoid
// include loops.

if (!isset($ignoreAuth) || !$ignoreAuth) {
  include_once("$srcdir/auth.inc");
}

// If you do not want your accounting system to have a customer added to it
// for each insurance company, then set this to true.  SQL-Ledger currently
// (2005-03-21) does nothing useful with insurance companies as customers.
$GLOBALS['insurance_companies_are_not_customers'] = true;

// This is the background color to apply to form fields that are searchable.
// Currently it is applicable only to the "Search or Add Patient" form.
$GLOBALS['layout_search_color'] = '#ffff55';

//EMAIL SETTINGS
$SMTP_Auth = !empty($GLOBALS['SMTP_USER']);

// Customize these if you are using SQL-Ledger with OpenEMR, or if you are
// going to run sl_convert.php to convert from SQL-Ledger.
//
$sl_cash_acc    = '1060';       // sql-ledger account number for checking account
$sl_ar_acc      = '1200';       // sql-ledger account number for accounts receivable
$sl_income_acc  = '4320';       // sql-ledger account number for medical services income
$sl_services_id = 'MS';         // sql-ledger parts table id for medical services
$sl_dbname      = 'sql-ledger'; // sql-ledger database name
$sl_dbuser      = 'sql-ledger'; // sql-ledger database login name
$sl_dbpass      = 'secret';     // sql-ledger database login password
//////////////////////////////////////////////////////////////////

//module configurations
$GLOBALS['baseModuleDir'] 	= "interface/modules/"; //default path of modules
$GLOBALS['customDir'] 		= "custom_modules";		//non zend modules
$GLOBALS['zendModDir']	 	= "zend_modules";		//zend modules

// Don't change anything below this line. ////////////////////////////

$encounter = empty($_SESSION['encounter']) ? 0 : $_SESSION['encounter'];

if (!empty($_GET['pid']) && empty($_SESSION['pid'])) {
  $_SESSION['pid'] = $_GET['pid'];
}
elseif (!empty($_POST['pid']) && empty($_SESSION['pid'])) {
  $_SESSION['pid'] = $_POST['pid'];
}
$pid = empty($_SESSION['pid']) ? 0 : $_SESSION['pid'];
$userauthorized = empty($_SESSION['userauthorized']) ? 0 : $_SESSION['userauthorized'];
$groupname = empty($_SESSION['authProvider']) ? 0 : $_SESSION['authProvider'];

// global interface function to format text length using ellipses
function strterm($string,$length) {
  if (strlen($string) >= ($length-3)) {
    return substr($string,0,$length-3) . "...";
  } else {
    return $string;
  }
}

// Override temporary_files_dir if PHP >= 5.2.1.
if (version_compare(phpversion(), "5.2.1", ">=")) {
 $GLOBALS['temporary_files_dir'] = rtrim(sys_get_temp_dir(),'/');
}

// turn off PHP compatibility warnings
ini_set("session.bug_compat_warn","off");

//////////////////////////////////////////////////////////////////
?>
