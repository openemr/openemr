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
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\OemrAd\CoverageCheck;
use OpenEMR\OemrAd\Utility;

// OEMRAD - Changes for get section values.
$flItems = array();
if(isset($_SESSION['authUserID']) && !empty($_SESSION['authUserID'])) {
    //Selection Values
    $flItems = Utility::getSectionValues($_SESSION['authUserID']);
}

// OEMRAD - Set Calender Value
setPreservedValuesForCalander();

// OEMRAD - Preserve changes.
function setPreservedValuesForCalander() {
    global $flItems;
    
    if(isset($flItems['calendar_pc_username']) && !empty($flItems['calendar_pc_username'])) {
        if (!isset($_POST['pc_username'])) {
            $_REQUEST['pc_username'] = $flItems['calendar_pc_username'];
        }
    }
}

// OEMRAD - Preserve facility changes.
function setPreservedPcFacilityValue() {
    global $flItems, $sessionSetArray;

    if(isset($flItems['calendar_pc_facility']) && !empty($flItems['calendar_pc_facility'])) {
        if (!isset($_POST['pc_facility'])) {
            $sessionSetArray['pc_facility'] = $flItems['calendar_pc_facility'];
        }
    }
}

// OEMRAD - Get alert info.
function getAlertInfo($patient_alert_info = '') {
    //$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    if(isset($patient_alert_info) && !empty(trim($patient_alert_info))) {
        return '<span> - <a href="#" class="infoText" title="'.$patient_alert_info.'">'.Utility::getAlertSVG().'</a></span>';
    }

    return "";
}

// OEMRAD - Get alert info title.
function getAlertInfoTitle($patient_alert_info = '') {
    // $result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    if(isset($patient_alert_info) && !empty(trim($patient_alert_info))) {
        return "\n\s\n-- Alert Info -- \n".$patient_alert_info;
    }

    return "";
}

// OEMRAD - Replace space value.
function replaceSpace($text) {
    $breaks = array("\s");  
    return str_ireplace($breaks, "&nbsp;", $text);
}

// OEMRAD - Coverage element css.
function coverageEleCSS() {
    ?>
    <style type="text/css">
        .elg_container {
            margin-bottom: 5px;
            vertical-align: text-top;
            line-height: 21px;
        }

        .elg_container .svg-correct,
        .elg_container .svg-incorrect {
            display: inline-block;
            height: 14px;
            vertical-align: top;
            margin-top: 1px;
        }

        .elg_container .svg-correct > svg,
        .elg_container .svg-incorrect > svg {
            height: 11pt!important;
            vertical-align: top;
        }
        .infoText svg {
            vertical-align: middle;
        }
    </style>
    <?php
}

// OEMRAD - Get coverage data.
function getCoverageData($events) {
    $ids = array();

    foreach ($events as $ei => $eItem) {
        foreach ($eItem as $inx => $event) {
            if(isset($event['eid']) && !empty($event['eid'])) {
                $ids[] = $event['eid'];
            }
        }
    }

    $ids_str = implode(",",$ids);
    return CoverageCheck::getEligibilityDataForPostCalender($ids_str);
}

// OEMRAD - Get coverage content.
function getCoverageContent($event, $data) {
    $data = CoverageCheck::getEleContentForPostCalender($event, $data);
    return CoverageCheck::avabilityHTMLContent($data);
}

// OEMRAD - Get nickname.
function getNickName($event) {
    $result  = getPatientData($event['pid'], "*");
    return $result['nickname33'] ? " \"" . $result['nickname33'] . "\" " : "";
}

// OEMRAD - Preserve Data
if (isset($_POST['pc_username']) && $_POST['pc_username']) {
    Utility::saveSectionValues($_SESSION['authUserID'], 'calendar_pc_username', $_POST['pc_username']);
}

// OEMRAD - Preserve Data
if (isset($_POST['pc_facility'])) {
    Utility::saveSectionValues($_SESSION['authUserID'], 'calendar_pc_facility', $_POST['pc_facility']);
}

// these will be used in below SessionUtil::setSession to set applicable session variables
$sessionSetArray = [];

// From Michael Brinson 2006-09-19:
if (isset($_POST['pc_username'])) {
    $sessionSetArray['pc_username'] = $_POST['pc_username'];
}

//(CHEMED) Facility filter
if (isset($_POST['all_users'])) {
    $sessionSetArray['pc_username'] = $_POST['all_users'];
}

// bug fix to allow default selection of a provider
// added 'if..POST' check -- JRM
if (isset($_REQUEST['pc_username']) && $_REQUEST['pc_username']) {
    $sessionSetArray['pc_username'] = $_REQUEST['pc_username'];
}

// FACILITY FILTERING (lemonsoftware) (CHEMED)
$sessionSetArray['pc_facility'] = 0;

/*********************************************************************
if ($_POST['pc_facility'])  $_SESSION['pc_facility'] = $_POST['pc_facility'];
*********************************************************************/
if ($GLOBALS['login_into_facility']) {
    $sessionSetArray['pc_facility'] = $_SESSION['facilityId'];
} else {
    if (isset($_COOKIE['pc_facility']) && $GLOBALS['set_facility_cookie']) {
        $sessionSetArray['pc_facility'] = $_COOKIE['pc_facility'];
    }
}


// OEMRAD - Set pc facility value
setPreservedPcFacilityValue();

// override the cookie if the user doesn't have access to that facility any more
if ($_SESSION['userauthorized'] != 1 && $GLOBALS['restrict_user_facility']) {
    $facilities = getUserFacilities($_SESSION['authUserID']);
    // use the first facility the user has access to, unless...
    $sessionSetArray['pc_facility'] = $facilities[0]['id'];
    // if the cookie is in the users' facilities, use that.
    foreach ($facilities as $facrow) {
        if (($facrow['id'] == $_COOKIE['pc_facility']) && $GLOBALS['set_facility_cookie']) {
            $sessionSetArray['pc_facility'] = $_COOKIE['pc_facility'];
        }
    }
}

if (isset($_POST['pc_facility'])) {
    $sessionSetArray['pc_facility'] = $_POST['pc_facility'];
}

/********************************************************************/

if (isset($_GET['pc_facility'])) {
    $sessionSetArray['pc_facility'] = $_GET['pc_facility'];
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
    $sessionSetArray['viewtype'] = $_REQUEST['viewtype'];
}

// Set the session variables
SessionUtil::setSession($sessionSetArray);

// start PN
pnInit();

// Get variables
list($module, $func, $type) = pnVarCleanFromInput('module', 'func', 'type');

if ($module != "PostCalendar") {
    // exit if not using PostCalendar module
    exit;
}

if ($type == "admin") {
    if (!AclMain::aclCheckCore('admin', 'calendar')) {
        // exit if do not have access
        exit;
    }
    if (
        ($func != "modifyconfig") &&
        ($func != "clearCache") &&
        ($func != "testSystem") &&
        ($func != "categories") &&
        ($func != "categoriesConfirm") &&
        ($func != "categoriesUpdate")
    ) {
        // only support certain functions in admin use
        exit;
    }
}

if (empty($type)) {
    $type = 'user';
}

if ($type == "user") {
    if (
        ($func != "view") &&
        ($func != "search")
    ) {
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
    $output->Text('Failed to load module ' . text($module) . ' ( At function: "' . text($func) . '" )');
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
