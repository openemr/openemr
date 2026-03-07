<?php

/**
 * Main info frame.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionWrapperFactory;

require_once("../globals.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// this allows us to keep our viewtype between screens -- JRM calendar_view_type
$viewtype = $GLOBALS['calendar_view_type'];
if ($session->has('viewtype')) {
    $viewtype = $session->get('viewtype');
}

// this allows us to keep our selected providers between screens -- JRM
$pcuStr = "pc_username=" . attr_url($session->get('authUser'));
if ($session->has('pc_username')) {
    $pcuStr = "";
    $pc_username = $session->get('pc_username');
    if (!empty($pc_username) && is_array($pc_username) && count($pc_username) > 1) {
        // loop over the array of values in pc_username to build
        // a list of pc_username HTTP vars
        foreach ($pc_username as $pcu) {
            $pcuStr .= "&pc_username[]=" . attr_url($pcu);
        }
    } else {
        // two possibilities here
        // 1) pc_username is an array with a single element
        // 2) pc_username is just a string, not an array
        if (is_string($pc_username)) {
            $pcuStr .= "&pc_username[]=" . attr_url($pc_username);
        } else {
            $pcuStr .= "&pc_username[]=" . attr_url($pc_username[0]);
        }
    }
}

// different frame source page depending on session vars
$userauthorized = $session->get('userauthorized');
if ($userauthorized && $GLOBALS['docs_see_entire_calendar']) {
    $framesrc = "calendar/index.php?module=PostCalendar&viewtype=" . attr_url($viewtype) . "&func=view";
} elseif ($userauthorized) {
    $framesrc = "calendar/index.php?module=PostCalendar&viewtype=" . attr_url($viewtype) . "&func=view&" . $pcuStr;
} else {
    $framesrc = "calendar/index.php?module=PostCalendar&func=view&viewtype=" . attr_url($viewtype);
}

// Removed frame as it causes framing issues related to height
// This functions completely normally without it
header("Location: " . $framesrc);
