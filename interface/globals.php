<?php
/* $Id$ */
//  ------------------------------------------------------------------------ //
//                OpenEMR Electronic Medical Records System                  //
//                    Copyright (c) 2005 oemr.org                            //
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

require_once(dirname(__FILE__) . "/../includes/config.php");
// Global variable file in which colors and paths are set for the interface.
///////////////////////////////////////////////////////////////////
// THESE VALUES MUST BE SET BEFORE OPENEMR WILL FUNCTION:
///////////////////////////////////////////////////////////////////
// Set this to the full absolute directory path for openemr:
$webserver_root = "/mnt/htdocs/openemr";

// Set this to the relative html path, ie. what you would type into the web
// browser after the server address to get to OpenEMR.  For example, if you
// type "http://127.0.0.1/clinic/openemr/"  to load OpenEMR, set $web_root
// to "/clinic/openemr" without the trailing slash.
$web_root = "/openemr";

///////////////////////////////////////////////////////////////////

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

// Language Control Section (will add toggling)
// English:1, Swedish:2, Spanish:3, German:4, Dutch:5, Hebrew:6
define ('LANGUAGE',1);
include_once (dirname(__FILE__) . "/../library/translation.inc.php");

include_once (dirname(__FILE__) . "/../library/date_functions.php");

// Default category for find_patient screen
$GLOBALS['default_category'] = 5;
$GLOBALS['default_event_title'] = 'Office Visit';

// The session name appears in cookies stored in the browser.  If you have
// multiple OpenEMR installations running on the same server, you should
// customize this name so they cannot interfere with each other.
session_name("OpenEMR");

session_start();

// Set this to true to activate support for the new frame layout.
//
$GLOBALS['concurrent_layout'] = true;

// used in Add new event for multiple providers
$GLOBALS['select_multi_providers'] = false;

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
$tmore = "(More)";
// The assistant word, BACK printed next to titles that return to previous screens:
$tback = "(Back)";

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

$v_major = '2';
$v_minor = '8';
$v_patch = '3';
$tag = '-dev'; // release candidate, e.g. '-rc1'

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

// You can set this to the category name of a document to link to from the
// patient summary page.  Normally this is the category for insurance cards.
// This lets you click on the patient's name to see their ID card.
$GLOBALS['patient_id_category_name'] = '';

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

// Customize these if you are using SQL-Ledger with OpenEMR.
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

// required for normal operation because of recent changes in PHP:
$ps = strpos($_SERVER['REQUEST_URI'],"myadmin");
if ($ps === false) {
	extract($_GET);
	extract($_POST);
}

// turn off PHP compatibility warnings
ini_set("session.bug_compat_warn","off");
?>
