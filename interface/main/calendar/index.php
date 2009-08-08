<?php
// File: $Id$ $Name$
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of this file: Francisco Burzi
// Purpose of this file: Directs to the start page as defined in config.php
// ----------------------------------------------------------------------

// include base api

//$ignoreAuth = true;
include_once("../../globals.php");
include_once("$srcdir/calendar.inc");
include_once("$srcdir/patient.inc");
include "includes/pnre.inc.php";
include 'includes/pnAPI.php';

// From Michael Brinson 2006-09-19:
if ($_POST['pc_username']) $_SESSION['pc_username'] = $_POST['pc_username'];

//(CHEMED) Facility filter
if ($_POST['all_users']) $_SESSION['pc_username'] = $_POST['all_users'];

// bug fix to allow default selection of a provider
// added 'if..POST' check -- JRM
if ($_GET['pc_username']) $_SESSION['pc_username'] = $_GET['pc_username'];
if ($_POST['pc_username']) $_SESSION['pc_username'] = $_POST['pc_username'];

// (CHEMED) Get the width of vieport
if ($_GET['framewidth']) $_SESSION['pc_framewidth'] = $_GET['framewidth'];

// FACILITY FILTERING (lemonsoftware) (CHEMED)
$_SESSION['pc_facility'] = 0;

/*********************************************************************
if ($_POST['pc_facility'])  $_SESSION['pc_facility'] = $_POST['pc_facility'];
*********************************************************************/
if (isset($_COOKIE['pc_facility']) && $GLOBALS['set_facility_cookie']) $_SESSION['pc_facility'] = $_COOKIE['pc_facility'];
// override the cookie if the user doesn't have access to that facility any more
if ($_SESSION['userauthorized'] != 1 && $GLOBALS['restrict_user_facility']) { 
  $facilities = getUserFacilities($_SESSION['authId']);
  // use the first facility the user has access to, unless...
  $_SESSION['pc_facility'] = $facilities[0]['id']; 
  // if the cookie is in the users' facilities, use that.
  foreach ($facilities as $facrow) {
    if (($facrow['id'] == $_COOKIE['pc_facility']) && $GLOBALS['set_facility_cookie'])
      $_SESSION['pc_facility'] = $_COOKIE['pc_facility'];
  }
}
if (isset($_POST['pc_facility']))  $_SESSION['pc_facility'] = $_POST['pc_facility'];
/********************************************************************/

if ($_GET['pc_facility'])  $_SESSION['pc_facility'] = $_GET['pc_facility'];
if ($GLOBALS['set_facility_cookie'] && ($_SESSION['pc_facility'] > 0)) setcookie("pc_facility", $_SESSION['pc_facility'], time() + (3600 * 365));

// allow tracking of current viewtype -- JRM
if ($_GET['viewtype']) $_SESSION['viewtype'] = $_GET['viewtype'];
if ($_POST['viewtype']) $_SESSION['viewtype'] = $_POST['viewtype'];


//if (empty($_GET['no_nav'])) {
//        $_SESSION['last_calendar_page'] = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
//}
/*
print_r($_POST);
print_r($_GET);
print_r($_SESSION);
die;
*/
//print_r($_SESSION);
// start PN
pnInit();

// Get variables
list($module,
     $func,
     $op,
     $name,
     $file,
     $type,) = pnVarCleanFromInput('module',
                                  'func',
                                  'op',
                                  'name',
                                  'file',
                                  'type');

// Defaults for variables
if (isset($catid)) { pnVarCleanFromInput('catid'); }

// check requested module and set to start module if not present
if (empty($name)) {
    $name = pnConfigGetVar('startpage');
    // fixed for the new style of loading modules and set start page for them [class007]
    if (empty($module)) { $module = $name; }
}

// get module information
$modinfo = pnModGetInfo(pnModGetIDFromName($module));

if ($modinfo['type'] == 2)
{
    // New-new style of loading modules
    if (empty($type)) { $type = 'user'; }
    if (empty($func)) { $func="main"; }

    // it should be $module not $name [class007]
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
        $output->Text('Failed to load module ' . $module .' ( At function: "'.$func.'" )');
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
} else {
    // Old-old style of loading modules
    if (empty($op)) {
        $op = "modload";
    }
    if (empty($file)) {
        $file="index";
    }

    include 'includes/legacy.php';
    switch ($op) {
        case 'modload':

            define("LOADED_AS_MODULE","1");
            // added for the module/system seperation [class007]
            if (file_exists('modules/' . pnVarPrepForOS($name) . '/' . pnVarPrepForOS($file) . '.php')) {
                include 'modules/' . pnVarPrepForOS($name) . '/'  . pnVarPrepForOS($file) . '.php';
            } else {
                // Failed to load the module
                $output = new pnHTML();
                //$output->StartPage();
                $output->Text('Failed to load module ' . $module);
                $output->EndPage();
                $output->PrintPage();
                exit;
            }
            break;
        default:
            // Failed to load the module
            $output = new pnHTML();
            //$output->StartPage();
            $output->Text('Sorry, you cannot access this file directly...');
            $output->EndPage();
            $output->PrintPage();
            break;
    }

}

?>
