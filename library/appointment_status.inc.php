<?php
// Copyright (C) 2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is called to update the appointment status for a specified patient
// with an encounter on the specified date. It does nothing unless the
// feature to auto-update appointment statuses is enabled.
function updateAppointmentStatus($pid, $encdate, $newstatus) {
  if (empty($GLOBALS['gbl_auto_update_appt_status'])) return;
  // Find appointment and set appointment status as appropriate.
  // This makes some assumptions about what the status IDs are.
  $query = "SELECT pc_eid, pc_apptstatus " .
    "FROM openemr_postcalendar_events WHERE " .
    "pc_pid = '$pid' AND pc_recurrtype = 0 AND " .
    "pc_eventDate = '$encdate' " .
    "ORDER BY pc_startTime DESC, pc_eid DESC LIMIT 1";
  $tmp = sqlQuery($query);
  if (!empty($tmp['pc_eid'])) {
    $appt_eid = $tmp['pc_eid'];
    $appt_status = $tmp['pc_apptstatus'];
    // Some tests for illogical changes.
    if ($appt_status == '$') return;
    if ($newstatus == '<' && $appt_status == '>') return;
    sqlStatement("UPDATE openemr_postcalendar_events SET " .
      "pc_apptstatus = '$newstatus' WHERE pc_eid = '$appt_eid'");
  }
}

?>