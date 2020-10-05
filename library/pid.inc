<?php

/**
 * pid.inc
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionUtil;

// Function called to set the global session variable for patient id (pid) number.
function setpid($new_pid)
{
    global $pid, $encounter;

    // Escape $new_pid by forcing it to an integer to protect from sql injection
    $new_pid_int = intval($new_pid);
    // If the $new_pid was not an integer, then send an error to error log
    if (!is_numeric($new_pid)) {
        error_log("Critical OpenEMR Error: Attempt to set pid to following non-integer value was denied: " . errorLogEscape($new_pid), 0);
        error_log("Requested pid " . errorLogEscape($new_pid), 0);
        error_log("Returned pid " . errorLogEscape($new_pid_int), 0);
    }

    // these will be used in below SessionUtil::setUnsetSession to modify applicable session variables
    $sessionSetArray = [];
    $sessionUnsetArray = [];

    // Be careful not to clear the encounter unless the pid is really changing.
    if (!isset($_SESSION['pid']) || $pid != $new_pid_int || $pid != $_SESSION['pid']) {
        $encounter = 0;
        $sessionSetArray['encounter'] = 0;
    }

    // unset therapy_group session when set session for patient
    if (isset($_SESSION['pid']) && ($_SESSION['pid'] != 0) && isset($_SESSION['therapy_group'])) {
        $sessionUnsetArray[] = 'therapy_group';
    }

    // Set pid to the escaped pid and update the session variables
    $sessionSetArray['pid'] = $new_pid_int;
    SessionUtil::setUnsetSession($sessionSetArray, $sessionUnsetArray);
    $pid = $new_pid_int;
    EventAuditLogger::instance()->newEvent("view", $_SESSION["authUser"], $_SESSION["authProvider"], 1, '', $pid);
}
