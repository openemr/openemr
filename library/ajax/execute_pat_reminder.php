<?php
/**
 * Process/send clinical reminders.
 *
 * Copyright (C) 2012 Brady Miller <brady@sparmy.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../reminders.php");

//To improve performance and not freeze the session when running this
// report, turn off session writing. Note that php session variables
// can not be modified after the line below. So, if need to do any php
// session work in the future, then will need to remove this line.
session_write_close();

//Remove time limit, since script can take many minutes
set_time_limit(0);

// Set the "nice" level of the process for these reports. When the "nice" level
// is increased, these cpu intensive reports will have less affect on the performance
// of other server activities, albeit it may negatively impact the performance
// of this report (note this is only applicable for linux).
if (!empty($GLOBALS['pat_rem_clin_nice'])) {
  proc_nice($GLOBALS['pat_rem_clin_nice']);
}

//  Start a report, which will be stored in the report_results sql table..
if ( (!empty($_POST['execute_report_id']) && !empty($_POST['process_type'])) && (($_POST['process_type'] == "process"  ) || ($_POST['process_type'] == "process_send")) ) {

  if ($_POST['process_type'] == "process_send") {
    update_reminders_batch_method('','',$_POST['execute_report_id'],TRUE);
  }
  else { // $_POST['process_type'] == "process" 
    update_reminders_batch_method('','',$_POST['execute_report_id']);
  }
}
else {
 echo "ERROR";
}
?>
