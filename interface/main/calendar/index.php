<?php
/**
 * POST-NUKE Content Management System
 * Based on:
 * PHP-NUKE Web Portal System - http://phpnuke.org/
 * Thatware - http://thatware.org/
 *
 * Purpose of this file: Directs to the start page as defined in config.php
 *
 * @author    Francisco Burzi
 * @author    Post-Nuke Development Team
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2001 by the Post-Nuke Development Team <http://www.postnuke.com/>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/patient.inc");
require_once 'includes/pnAPI.php';
require_once("$srcdir/acl.inc");

// From Michael Brinson 2006-09-19:
if (isset($_POST['pc_username'])) {
    $_SESSION['pc_username'] = $_POST['pc_username'];
}

//(CHEMED) Facility filter
if (isset($_POST['all_users'])) {
    $_SESSION['pc_username'] = $_POST['all_users'];
}

// bug fix to allow default selection of a provider
// added 'if..POST' check -- JRM
if (isset($_REQUEST['pc_username']) && $_REQUEST['pc_username']) {
    $_SESSION['pc_username'] = $_REQUEST['pc_username'];
}

// (CHEMED) Get the width of vieport
if (isset($_GET['framewidth'])) {
    $_SESSION['pc_framewidth'] = $_GET['framewidth'];
}

// FACILITY FILTERING (lemonsoftware) (CHEMED)
$_SESSION['pc_facility'] = 0;

/*********************************************************************
if ($_POST['pc_facility'])  $_SESSION['pc_facility'] = $_POST['pc_facility'];
*********************************************************************/
if ($GLOBALS['login_into_facility']) {
    $_SESSION['pc_facility'] = $_SESSION['facilityId'];
} else {
    if (isset($_COOKIE['pc_facility']) && $GLOBALS['set_facility_cookie']) {
        $_SESSION['pc_facility'] = $_COOKIE['pc_facility'];
    }
}

// override the cookie if the user doesn't have access to that facility any more
if ($_SESSION['userauthorized'] != 1 && $GLOBALS['restrict_user_facility']) {
    $facilities = getUserFacilities($_SESSION['authId']);
    // use the first facility the user has access to, unless...
    $_SESSION['pc_facility'] = $facilities[0]['id'];
    // if the cookie is in the users' facilities, use that.
    foreach ($facilities as $facrow) {
        if (($facrow['id'] == $_COOKIE['pc_facility']) && $GLOBALS['set_facility_cookie']) {
            $_SESSION['pc_facility'] = $_COOKIE['pc_facility'];
        }
    }
}

if (isset($_POST['pc_facility'])) {
    $_SESSION['pc_facility'] = $_POST['pc_facility'];
}

/********************************************************************/

if (isset($_GET['pc_facility'])) {
    $_SESSION['pc_facility'] = $_GET['pc_facility'];
}

if ($GLOBALS['set_facility_cookie']) {
    if (!$GLOBALS['login_into_facility'] && $_SESSION['pc_facility'] > 0) {
        // If login_into_facility is turn on $_COOKIE['pc_facility'] was saved in the login process.
        // In the case that login_into_facility is turn on you don't want to save different facility than the selected in the login screen.
        setcookie("pc_facility", $_SESSION['pc_facility'], time() + (3600 * 365));
    }
}

// Simplifying by just using request variable instead of checking for both post and get - KHY
if (isset($_REQUEST['viewtype'])) {
    $_SESSION['viewtype'] = $_REQUEST['viewtype'];
}

// start PN
pnInit();

// Get variables
list($module,
     $func,
     $type) = pnVarCleanFromInput(
         'module',
         'func',
         'type'
     );

if ($module != "PostCalendar") {
    // exit if not using PostCalendar module
    exit;
}

if ($type == "admin") {
    if (!acl_check('admin', 'calendar')) {
        // exit if do not have access
        exit;
    }
    if (($func != "modifyconfig") &&
        ($func != "clearCache") &&
        ($func != "testSystem") &&
        ($func != "categories") &&
        ($func != "categoriesConfirm") &&
        ($func != "categoriesUpdate")) {
        // only support certain functions in admin use
        exit;
    }
}

if (empty($type)) {
    $type = 'user';
}

if ($type == "user") {
    if (($func != "view") &&
        ($func != "search")) {
        // only support view and search functions in for non-admin use
        exit;
    }
}

if (($type != "user") && ($type != "admin")) {
    // only support admin and user type
    exit;
}

// Defaults for variables
if (isset($catid)) {
    pnVarCleanFromInput('catid');
}

if (pnModAvailable($module)) {
    if (pnModLoad($module, $type)) {
        // Run the function
        $return = pnModFunc($module, $type, $func);
    } else {
        $return = false;
    }
} else {
    $return = false;
}

// Sort out return of function.  Can be
// true - finished
// false - display error msg
// text - return information
if ((empty($return)) || ($return == false)) {
    // Failed to load the module
    $output = new pnHTML();
    $output->StartPage();
    $output->Text('Failed to load module ' . text($module) .' ( At function: "' . text($func) . '" )');
    $output->EndPage();
    $output->PrintPage();
    exit;
} elseif (strlen($return) > 1) {
    // Text
    $output = new pnHTML();
    //$output->StartPage();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text($return);
    $output->SetInputMode(_PNH_PARSEINPUT);
    //$output->EndPage();
    $output->PrintPage();
} else {
    // duh?
}

exit;
