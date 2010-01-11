<?php
/* $Id$ */
//
//  ------------------------------------------------------------------------ //
//                OpenEMR Electronic Medical Records System                  //
//                   Copyright (c) 2005-2008 oemr.org                        //
//                       <http://www.oemr.org/>                              //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

// Is this windows or non-windows? Create a boolean definition.
if (!defined('IS_WINDOWS'))
 define('IS_WINDOWS', (stripos(PHP_OS,'WIN') === 0));

// Some important php.ini overrides. Defaults for these values are often
// too small.  You might choose to adjust them further.
//
ini_set('memory_limit', '64M');
ini_set('session.gc_maxlifetime', '14400');

// Emulates register_globals = On.  Moved to here from the bottom of this file
// to address security issues.  Need to change everything requiring this!
$ps = strpos($_SERVER['REQUEST_URI'],"myadmin");
if ($ps === false) {
  extract($_GET);
  extract($_POST);
}

require_once(dirname(__FILE__) . "/../includes/config.php");
// Global variable file in which colors and paths are set for the interface.
///////////////////////////////////////////////////////////////////
// THESE VALUES MUST BE SET BEFORE OPENEMR WILL FUNCTION:
///////////////////////////////////////////////////////////////////
// Set this to the full absolute directory path for openemr:
$webserver_root = "/var/www/openemr";

// Set this to the relative html path, ie. what you would type into the web
// browser after the server address to get to OpenEMR.  For example, if you
// type "http://127.0.0.1/clinic/openemr/"  to load OpenEMR, set $web_root
// to "/clinic/openemr" without the trailing slash.
$web_root = "/openemr";

///////////////////////////////////////////////////////////////////

// Collecting the utf8 disable flag from the sqlconf.php file in order
// to set the correct html encoding. utf8 vs iso-8859-1. If flag is set
// then set to iso-8859-1.
require_once(dirname(__FILE__) . "/../library/sqlconf.php");
if (!$disable_utf8_flag) {    
 ini_set('default_charset', 'utf-8');
}
else {
 ini_set('default_charset', 'iso-8859-1');
}

// This is the return mail address used when sending prescriptions by email:
$GLOBALS['practice_return_email_path'] = "prescription_mail@example.com";

// Root directory, relative to the webserver root:
$GLOBALS['rootdir'] = "$web_root/interface";
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
$GLOBALS['login_screen'] = "$rootdir/login_screen.php";

//
// Operating system specific settings
//  Currently used in the Adminstration->Backup page within OpenEMR
//  -Note the temporary file directory parameter is only used when
//    php version is < 5.2.1 (otherwise the temporary directory that
//    is set within php is used)
//
// WINDOWS Specific Settings
$GLOBALS['mysql_bin_dir_win'] = "C:/xampp/mysql/bin";
$GLOBALS['perl_bin_dir_win'] = "C:/xampp/perl/bin";
$GLOBALS['temporary_files_dir_win'] = "C:/windows/temp";
//
// LINUX (non-Windows) Specific Settings
$GLOBALS['mysql_bin_dir_linux'] = "/usr/bin";
$GLOBALS['perl_bin_dir_linux'] = "/usr/bin";
$GLOBALS['temporary_files_dir_linux'] = "/tmp";
//
// Print command for spooling to printers, used by statements.inc.php
// This is the command to be used for printing (without the filename).
// The word following "-P" should be the name of your printer.  This
// example is designed for 8.5x11-inch paper with 1-inch margins,
// 10 CPI, 6 LPI, 65 columns, 54 lines per page.
//
// IF lpr services are installed on Windows this setting will be similar
// Otherwise configure it as needed (print /d:PRN) might be an option for Windows parallel printers
$GLOBALS['print_command'] = "lpr -P HPLaserjet6P -o cpi=10 -o lpi=6 -o page-left=72 -o page-top=72";

//
// Language Translations Control Section
//

//  Current supported languages:    // Allow capture of term for translation:
//   Arabic                         // xl('Arabic')
//   Armenian                       // xl('Armenian')
//   Bahasa Indonesia               // xl('Bahasa Indonesia')
//   Chinese                        // xl('Chinese')
//   Dutch                          // xl('Dutch')
//   English (Indian)               // xl('English (Indian)')
//   English (Standard)             // xl('English (Standard)')
//   French                         // xl('French')
//   German                         // xl('German')
//   Greek                          // xl('Greek')
//   Hebrew                         // xl('Hebrew')
//   Norwegian                      // xl('Norwegian')
//   Portuguese (Brazilian)         // xl('Portuguese (Brazilian)')
//   Portuguese (European)          // xl('Portuguese (European)')
//   Russian                        // xl('Russian')
//   Slovak                         // xl('Slovak')
//   Spanish                        // xl('Spanish')
//   Swedish                        // xl('Swedish')

// Login Menu Language Translation Configuration
//
//  'language_menu_login' toggle
//    -If set to true then will allow language selection on login
//    -If set to false then will not show menu in login and will use default (see below)
$GLOBALS['language_menu_login'] = true;
//
//  'language_menu_all' toggle
//    -If set to true then show all languages in login menu
//    -If set to false then only show chosen (see below) languages in login menu
$GLOBALS['language_menu_showall'] = true;
//
//  'language_menu_show' array
//    -ONLY pertinent if above 'language_menu_all' toggle is set to false
//    -Displays these chosen languages in the login menu
$GLOBALS['language_menu_show'] = array('English (Standard)','Swedish');
//
//  'language_default'
//    -Sets the default language
//    -If login menu is on, then it will be the 'Default' choice in menu
//    -If login menu is off, then it will choose this language
$GLOBALS['language_default'] = "English (Standard)";

// Language translation options
//  -The globals below allow granular control to turn off translation of
//   several specific parts of OpenEMR.
//
//  'translate_layout'
//    -If true, then will translate the layout information.
//    -If false, will not translate the layout information.
//      If false, then most of the demographics and patient data
//       entry forms will not be translated.
$GLOBALS['translate_layout'] = true;
//
//  'translate_lists'
//    -If true, then will translate the lists information.
//    -If false, will not translate the lists information.
//      If false, then many lists of information in forms
//       and templates will be untranslated.
$GLOBALS['translate_lists'] = true;
//
//  'translate_gacl_groups'
//    -If true, then will translate the access control group names.
//    -If false, will not translate the access control group names.
$GLOBALS['translate_gacl_groups'] = true;
//
//  'translate_note_titles'
//    -If true, then will translate the patient Form (note) titles.
//    -If false, will not translate the patient Form (note) titles.
$GLOBALS['translate_form_titles'] = true;
//
//  'translate_document_categories'
//    -If true, then will translate the document categories.
//    -If false, will not translate the document categories.
$GLOBALS['translate_document_categories'] = true;
//
//  'translate_appt_categories'
//    -If true, then will translate the appt categories.
//    -If false, will not translate the appt categories.
$GLOBALS['translate_appt_categories'] = true;

// Include the translation engine. This will also call sql.inc to
//  open the openemr mysql connection.
include_once (dirname(__FILE__) . "/../library/translation.inc.php");

//
// Lists and Layouts Control Section
//
//
// 'state_custom_addlist_widget'
//  - If true, then will display a customized addlist widget for
//    state list entries (will ask for title and abbreviation)
$GLOBALS['state_custom_addlist_widget'] = true;
//
// Data type options. This will set data types in forms that are not
//  covered by a layout.
//   1  = single-selection list 
//   2  = text field
//   26 = single-selection list with ability to add to the list (addlist widget)
//   (the list entries below are only pertinent for data types 1 or 26)
//
// 'state_data_type'
$GLOBALS['state_data_type'] = 26;
$GLOBALS['state_list'] = "state";
//
// 'country_data_type'
$GLOBALS['country_data_type'] = 26;
$GLOBALS['country_list'] = "country";

// Vitals form and growth chart units (US and-or metrics)
//   1 = Show both US and metric (main unit is US)
//   2 = Show both US and metric (main unit is metric)
//   3 = Show US only
//   4 = Show metric only
$GLOBALS['units_of_measurement'] = 1;

// Flag to not show the old deprecated metric form in
// the unregistered section of the admin->forms module.
//  (since 3.1.0, metric units are now used along with US units
//   in the main vitals form; controlled by above setting)
$GLOBALS['disable_deprecated_metrics_form'] = true;

// Flags to turn off/on specific OpenEMR modules
$GLOBALS['disable_calendar'] = false;
$GLOBALS['disable_chart_tracker'] = false; 
$GLOBALS['disable_immunizations'] = false; 
$GLOBALS['disable_prescriptions'] = false;

// Option to set the top default window. By default, it is set
// to the calendar screen. The starting directory is
// interface/main/ , hence:
//    The calendar screen is 'main_info.php' .
//    The patient search/add screen is '../new/new.php' .
$GLOBALS['default_top_pane'] = 'main_info.php';

// Option to set the 'Online Support' link. By default, it is
// set to the Sourceforge support forums. Note you can also remove
// the link entirely by simple commenting out below line.
$GLOBALS['online_support_link'] = 'http://sourceforge.net/projects/openemr/support';

include_once (dirname(__FILE__) . "/../library/date_functions.php");
include_once (dirname(__FILE__) . "/../library/classes/Filtreatment_class.php");

// Default category for find_patient screen
$GLOBALS['default_category'] = 5;
$GLOBALS['default_event_title'] = 'Office Visit';

// The session name appears in cookies stored in the browser.  If you have
// multiple OpenEMR installations running on the same server, you should
// customize this name so they cannot interfere with each other.
//
// Also, if modify session_name, then need to place the identical name in
// the phpmyadmin file here: openemr/phpmyadmin/libraries/session.inc.php
// at line 71. This was required after embedded new phpmyadmin version on
// 05-12-2009 by Brady. Hopefully will figure out a more appropriate fix.
session_name("OpenEMR");

session_start();

// Set this to 1 or 2 to activate support for the new frame layout.
// 0 = Old-style layout
// 1 = Navigation menu consists of pairs of radio buttons
// 2 = Navigation menu is a tree view
//
$GLOBALS['concurrent_layout'] = 2;

// If >0 this will enforce a separate PHP session for each top-level
// browser window.  You must log in separately for each.  This is not
// thoroughly tested yet and some browsers might have trouble with it,
// so make it 0 if you must.  Alternatively, you can set it to 2 to be
// notified when the session ID changes.
$GLOBALS['restore_sessions'] = 1; // 0=no, 1=yes, 2=yes+debug

// used in Add new event for multiple providers
$GLOBALS['select_multi_providers'] = false;

// NOT functional. Always keep this value FALSE.
//  Plan to remove when this functionally has been completely
//   removed from code.
$GLOBALS['dutchpc'] = FALSE;

// Theme definition:
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
$login_body_line = ' background="'.$rootdir.'/pic/aquabg.gif" ';
$css_header = "$rootdir/themes/style_sky_blue.css";
$logocode="<img src='$rootdir/pic/logo_sky.gif'>";
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
$GLOBALS['titleBarHeight'] = 20;

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
else {
  // Max Idle Time in seconds before logout.  Default 7200 (2 hours):
  $timeout = 7200;
}

//Version tags

$v_major = '3';
$v_minor = '2';
$v_patch = '0';
$tag = '-dev'; // minor revision number, should be empty for production releases

// This name appears on the login page and in the title bar of most windows.
// It's nice to customize this to be the name of your clinic.
$openemr_name = 'OpenEMR';

$openemr_version = "$v_major.$v_minor.$v_patch".$tag;	// Version tag used by program

$rootdir = $GLOBALS['rootdir'];
$srcdir = $GLOBALS['srcdir'];
$login_screen = $GLOBALS['login_screen'];
$GLOBALS['css_header'] = $css_header;
$GLOBALS['backpic'] = $backpic;
$GLOBALS['rootdir'] = $rootdir;

// change these to reflect when the daily view should start to display times
// as well as it should end. ex schedule_start = 9 schedule_end = 17
// start end times in hours
$GLOBALS['schedule_start'] = 8;
$GLOBALS['schedule_end'] = 17;

// This is the time granularity of the calendar and the smallest interval
// in minutes for an appointment slot:
$GLOBALS['calendar_interval'] = 15;

// Include the authentication module code here, but the rule is
// if the file has the word "login" in the source code file name,
// don't include the authentication module - we do this to avoid
// include loops.

if (!$ignoreAuth) {
  include_once("$srcdir/auth.inc");
}

// If you do not want your accounting system to have a customer added to it
// for each insurance company, then set this to true.  SQL-Ledger currently
// (2005-03-21) does nothing useful with insurance companies as customers.
$GLOBALS['insurance_companies_are_not_customers'] = true;

// If OpenEMR is being used by an athletic team rather than in a traditional
// clinical setting, set this to true.
$GLOBALS['athletic_team'] = false;

// True if this is a weight loss clinic:
$GLOBALS['weight_loss_clinic'] = false;

// The telephone country code of this installation.  1 = USA.
// See http://www.wtng.info/ for a list.
$GLOBALS['phone_country_code'] = '1';

// This determines how appointments display on the calendar:
// 1 = lastname; 2 = last,first; 3 = last,first(title);
// 4 = last,first(title: description)
$GLOBALS['calendar_appt_style'] = 2;

// Make this true if you want providers to see all appointments by default
// and not just their own.
$GLOBALS['docs_see_entire_calendar'] = false;

// Set this to true if you want the drug database and support for in-house
// prescription dispensing.
$GLOBALS['inhouse_pharmacy'] = false;

// Make this nonzero if you want the ability to sell products other than
// prescription drugs.  Also requires inhouse_pharmacy to be true.
// This allows selection of products from the Fee Sheet.
// Set this to 2 if you want a simplified interface (no templates, no
// prescription drugs), otherwise to 1.
$GLOBALS['sell_non_drug_products'] = 0;

// True to omit insurance and some other things from the demographics form:
$GLOBALS['simplified_demographics'] = false;

// True to omit form, route and interval which then become part of dosage:
$GLOBALS['simplified_prescriptions'] = false;

// True to omit method of payment from the copay panel:
$GLOBALS['simplified_copay'] = false;

// You may put text here as the default complaint in the New Patient form:
$GLOBALS['default_chief_complaint'] = '';

// This was added for sports teams needing to fill out injury forms, but might
// have other applications.
$GLOBALS['default_new_encounter_form'] = '';

// If you want a new encounter to be automatically created when appointment
// status is set to "@" (arrived), then make this true.
$GLOBALS['auto_create_new_encounters'] = true;

// If you don't want employer information, country, title in patient demographics.
$GLOBALS['omit_employers'] = false;

// This is for insurance billing and is specific to Medicare.  Make it true
// to force the referring provider to be the same as the rendering provider,
// instead of coming from the patient demographics.
$GLOBALS['MedicareReferrerIsRenderer'] = false;

// You can set this to the category name of a document to link to from the
// patient summary page.  Normally this is the category for insurance cards.
// This lets you click on the patient's name to see their ID card.
$GLOBALS['patient_id_category_name'] = '';

// Traditionally OpenEMR has allowed creation of user groups (not the same
// as access control groups).  However this has never done anything very
// useful and creates confusion.  Make this false if you really want it.
$GLOBALS['disable_non_default_groups'] = true;

// These are flags for some installation-specific customizations for which
// we have not yet figured out better parameters.
$GLOBALS['ippf_specific'] = false;
$GLOBALS['cene_specific'] = false;

// True to support discounts in the Checkout form by dollars instead of percentage.
$GLOBALS['discount_by_money'] = false;

// Set this to false if you want the doctors to be prompted to authorize
// patient notes created by others.
$GLOBALS['ignore_pnotes_authorization'] = true;

// This turns on the option of creating a new patient using the complete
// layout of the demographics form as well as a built-in search feature.
// Everyone should want this, but for now it's optional.
$GLOBALS['full_new_patient_form'] = true;

// This can be used to enable the old Charges panel for entering billing
// codes and payments.  It is not recommended, as it was obsoleted by the
// Fee Sheet which is more complete and comprehensive.
$GLOBALS['use_charges_panel'] = false;

// This was added for Wellcare EDI which can accept a special kind of claim
// containing diagnoses but not requiring procedures or charges.  If you
// don't know what this is about then you don't want it!
$GLOBALS['support_encounter_claims'] = false;

// Multi-facility Configuration
//
// Restrict non-authorized users to the "Schedule Facilities" (aka user_facilities table)
// set in User admin.
$GLOBALS['restrict_user_facility'] = false;
//
// Set a facility cookie, so browser keeps a default selected facility between logins.
$GLOBALS['set_facility_cookie'] = false;

// Make this true to add options for configuration export and import to the
// Backup page.
$GLOBALS['configuration_import_export'] = false;

// If you want Hylafax support then uncomment and customize the following
// statements, and also customize custom/faxcover.txt:
//
// $GLOBALS['hylafax_server']   = 'localhost';
// $GLOBALS['hylafax_basedir']  = '/var/spool/fax';
// $GLOBALS['hylafax_enscript'] = 'enscript -M Letter -B -e^ --margins=36:36:36:36';

// For scanner support, uncomment and customize the following.  This is
// the directory in which scanned-in documents may be found, and may for
// example be a smbfs-mounted share from the PC supporting the scanner:
//
// $GLOBALS['scanner_output_directory'] = '/mnt/scan_docs';

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

// OS specific configuration (do not modify this)
$GLOBALS['mysql_bin_dir'] = IS_WINDOWS ? $GLOBALS['mysql_bin_dir_win'] : $GLOBALS['mysql_bin_dir_linux'];
$GLOBALS['perl_bin_dir'] = IS_WINDOWS ? $GLOBALS['perl_bin_dir_win'] : $GLOBALS['perl_bin_dir_linux'];
if (version_compare(phpversion(), "5.2.1", ">=")) {
 $GLOBALS['temporary_files_dir'] = rtrim(sys_get_temp_dir(),'/'); // only works in PHP >= 5.2.1
}
else {
 $GLOBALS['temporary_files_dir'] = IS_WINDOWS ?  $GLOBALS['temporary_files_dir_win'] : $GLOBALS['temporary_files_dir_linux'];
}

// turn off PHP compatibility warnings
ini_set("session.bug_compat_warn","off");

//settings for cronjob
// SEND SMS NOTIFICATION BEFORE HH HOUR
$SMS_NOTIFICATION_HOUR = 50;
// SEND EMAIL NOTIFICATION BEFORE HH HOUR
$EMAIL_NOTIFICATION_HOUR = 50;
$SMS_GATEWAY_USENAME     = 'SMS_GATEWAY_USENAME';
$SMS_GATEWAY_PASSWORD    = 'SMS_GATEWAY_PASSWORD';
$SMS_GATEWAY_APIKEY      = 'SMS_GATEWAY_APIKEY';
?>
