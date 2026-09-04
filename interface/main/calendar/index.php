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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PostCalendar\CalendarFacilityResolver;
use Symfony\Component\HttpFoundation\Request;

require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/patient.inc.php");
require_once 'includes/pnAPI.php';

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = Request::createFromGlobals();

// these will be used in below SessionUtil::setSession to set applicable session variables
$sessionSetArray = [];

// From Michael Brinson 2006-09-19:
if (isset($_POST['pc_username'])) {
    $sessionSetArray['pc_username'] = $_POST['pc_username'];
}

//(CHEMED) Facility filter
if (isset($_POST['all_users'])) {
    $sessionSetArray['pc_username'] = $request->request->all()['all_users'];
}

// bug fix to allow default selection of a provider
// added 'if..POST' check -- JRM
if (isset($_REQUEST['pc_username']) && $_REQUEST['pc_username']) {
    $sessionSetArray['pc_username'] = $_REQUEST['pc_username'];
}

// FACILITY FILTERING (lemonsoftware) (CHEMED)
$loginIntoFacility = OEGlobalsBag::getInstance()->getBoolean('login_into_facility');
$facilityCookieEnabled = OEGlobalsBag::getInstance()->getBoolean('set_facility_cookie');
$restrictUserFacility = $session->get('userauthorized') != 1
    && OEGlobalsBag::getInstance()->getBoolean('restrict_user_facility');
$facilities = $restrictUserFacility ? getUserFacilities($session->get('authUserID')) : [];
/** @var list<int|string> $allowedFacilityIds */
$allowedFacilityIds = [];
if (is_array($facilities)) {
    foreach ($facilities as $facility) {
        if (!is_array($facility)) {
            continue;
        }

        $facilityId = $facility['id'] ?? null;
        if (is_int($facilityId) || is_string($facilityId)) {
            $allowedFacilityIds[] = $facilityId;
        }
    }
}

$sessionSetArray['pc_facility'] = CalendarFacilityResolver::resolve(
    $session->get('pc_facility'),
    $request->request->all()['pc_facility'] ?? null,
    $request->query->all()['pc_facility'] ?? null,
    $loginIntoFacility,
    $session->get('facilityId'),
    $facilityCookieEnabled,
    $_COOKIE['pc_facility'] ?? null,
    $restrictUserFacility,
    $allowedFacilityIds
);

// Simplifying by just using request variable instead of checking for both post and get - KHY
if (isset($_REQUEST['viewtype'])) {
    $sessionSetArray['viewtype'] = $_REQUEST['viewtype'];
}

// Set the session variables
SessionUtil::setSession($sessionSetArray);

if ($facilityCookieEnabled && !$loginIntoFacility && $sessionSetArray['pc_facility'] !== null) {
    // Persist the value selected by this request, including 0 (All Facilities).
    setcookie("pc_facility", (string) $sessionSetArray['pc_facility'], [
        'expires' => time() + (3600 * 365),
        'path' => OEGlobalsBag::getInstance()->getWebRoot(),
    ]);
}

// start PN
pnInit();

// Get variables
[$module, $func, $type] = pnVarCleanFromInput('module', 'func', 'type');

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
        !in_array($func, ["modifyconfig", "clearCache", "testSystem", "categories", "categoriesConfirm", "categoriesUpdate"])
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
    $body  = $output->generateStartPage();
    $body .= $output->generateText('Failed to load module ' . text($module) . ' ( At function: "' . text($func) . '" )');
    $body .= $output->generateEndPage();
    $output->PrintPage($body);
    exit;
} elseif (strlen((string) $return) > 1) {
    // Text
    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $body = $output->generateText($return);
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->PrintPage($body);
}

// $return === true means "finished"; fall through to exit.
exit;
