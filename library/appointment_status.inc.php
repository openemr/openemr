<?php

// Copyright (C) 2011, 2016 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is called to update the appointment status for a specified patient
// with an encounter on the specified date. It does nothing unless the
// feature to auto-update appointment statuses is enabled.

// See sample code in: interface/patient_tracker/patient_tracker_status.php
// This updates the patient tracker board as well as the appointment.

require_once(dirname(__FILE__) . '/patient_tracker.inc.php');

function updateAppointmentStatus($pid, $encdate, $newstatus)
{
    if (empty($GLOBALS['gbl_auto_update_appt_status'])) {
        return;
    }

    $query = "SELECT pc_eid, pc_aid, pc_catid, pc_apptstatus, pc_eventDate, pc_startTime, " .
    "pc_hometext, pc_facility, pc_billing_location, pc_room " .
    "FROM openemr_postcalendar_events WHERE " .
    "pc_pid = ? AND pc_recurrtype = 0 AND pc_eventDate = ? " .
    "ORDER BY pc_startTime DESC, pc_eid DESC LIMIT 1";
    $tmp = sqlQuery($query, array($pid, $encdate));
    if (!empty($tmp['pc_eid'])) {
        $appt_eid = $tmp['pc_eid'];
        $appt_status = $tmp['pc_apptstatus'];
        // Some tests for illogical changes.
        if ($appt_status == '$') {
            return;
        }

        if ($newstatus == '<' && $appt_status == '>') {
            return;
        }

        $encounter = todaysEncounterCheck(
            $pid,
            $tmp['pc_eventDate'],
            $tmp['pc_hometext'],
            $tmp['pc_facility'],
            $tmp['pc_billing_location'],
            $tmp['pc_aid'],
            $tmp['pc_catid'],
            false
        );
        manage_tracker_status(
            $tmp['pc_eventDate'],
            $tmp['pc_startTime'],
            $appt_eid,
            $pid,
            $_SESSION["authUser"],
            $newstatus,
            $tmp['pc_room'],
            $encounter
        );
    }
}
