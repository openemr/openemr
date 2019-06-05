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


require_once(dirname(__FILE__). "/../interface/globals.php");

use OpenEMR\Common\Logging\EventAuditLogger;

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

  // Be careful not to clear the encounter unless the pid is really changing.
    if (!isset($_SESSION['pid']) || $pid != $new_pid_int || $pid != $_SESSION['pid']) {
        $_SESSION['encounter'] = $encounter = 0;
    }

  // unset therapy_group session when set session for patient
    if ($_SESSION['pid'] != 0 && isset($_SESSION['therapy_group'])) {
        unset($_SESSION['therapy_group']);
    }


  // Set pid to the escaped pid
    $_SESSION['pid'] = $new_pid_int;
    $pid = $new_pid_int;

    EventAuditLogger::instance()->newEvent("view", $_SESSION["authUser"], $_SESSION["authProvider"], 1, '', $pid);
}
