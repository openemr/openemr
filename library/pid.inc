<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__). "/../interface/globals.php");
require_once(dirname(__FILE__)."/log.inc");

// Function called to set the global session variable for patient id (pid) number.
function setpid($new_pid)
{
    global $pid, $encounter;

  // Escape $new_pid by forcing it to an integer to protect from sql injection
    $new_pid_int = intval($new_pid);
  // If the $new_pid was not an integer, then send an error to error log
    if (!is_numeric($new_pid)) {
        error_log("Critical OpenEMR Error: Attempt to set pid to following non-integer value was denied: ".$new_pid, 0);
        error_log("Requested pid ".$new_pid, 0);
        error_log("Returned pid ".$new_pid_int, 0);
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

    newEvent("view", $_SESSION["authUser"], $_SESSION["authProvider"], 1, '', $pid);
}
