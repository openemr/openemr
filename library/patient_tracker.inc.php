<?php

/**
* library/patient_tracker.inc.php Functions used in the Patient Flow Board.
*
* Functions for use in the Patient Flow Board and Patient Flow Board Reports.
*
*
* Copyright (C) 2015 Terry Hill <terry@lillysystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
*
* @package OpenEMR
* @author Terry Hill <terry@lillysystems.com>
* @link https://www.open-emr.org
*
* Please help the overall project by sending changes you make to the author and to the OpenEMR community.
*
*/

require_once(dirname(__FILE__) . '/appointments.inc.php');

use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\PatientTrackerService;

function get_Tracker_Time_Interval($tracker_from_time, $tracker_to_time, $allow_sec = false)
{
    return PatientTrackerService::get_Tracker_Time_Interval($tracker_from_time, $tracker_to_time, $allow_sec);
}

function fetch_Patient_Tracker_Events($from_date, $to_date, $provider_id = null, $facility_id = null, $form_apptstatus = null, $form_apptcat = null, $form_patient_name = null, $form_patient_id = null)
{
    // TODO: refactor this method to use the PatientTrackerService  There is a whole heirarchy of inner function calls
    // inside the fetchAppointments method scattered across several different files which will require creating lots more classes
    // (ie a bigger undertaking).  Leaving this method alone until we can tackle this work at a later date in time.
    # used to determine which providers to display in the Patient Tracker
    if ($provider_id == 'ALL') {
        //set null to $provider id if it's 'all'
        $provider_id = null;
    }

    $events = fetchAppointments($from_date, $to_date, $form_patient_id, $provider_id, $facility_id, $form_apptstatus, null, null, $form_apptcat, true, 0, null, $form_patient_name);
    return $events;
}

#check to see if a status code exist as a check in
function is_checkin($option)
{
    return AppointmentService::isCheckInStatus($option);
}

#check to see if a status code exist as a check out
function is_checkout($option)
{
    return AppointmentService::isCheckOutStatus($option);
}


# This function will return false for both below scenarios:
#   1. The tracker item does not exist
#   2. If the tracker item does exist, but the encounter has not been set
function is_tracker_encounter_exist($apptdate, $appttime, $pid, $eid)
{
    return PatientTrackerService::is_tracker_encounter_exist($apptdate, $appttime, $pid, $eid);
}

 # this function will return the tracker id that is managed
 # or will return false if no tracker id was managed (in the case of a recurrent appointment)
function manage_tracker_status($apptdate, $appttime, $eid, $pid, $user, $status = '', $room = '', $enc_id = '')
{
    $patientTrackerService = new PatientTrackerService();
    return $patientTrackerService->manage_tracker_status($apptdate, $appttime, $eid, $pid, $user, $status, $room, $enc_id);
}

# This is used to break apart the information contained in the notes field of
#list_options. Currently the color and alert time are the only items stored
function collectApptStatusSettings($option)
{
    return PatientTrackerService::collectApptStatusSettings($option);
}

# This is used to collect the tracker elements for the Patient Flow Board Report
# returns the elements in an array
function collect_Tracker_Elements($trackerid)
{
    return PatientTrackerService::collect_Tracker_Elements($trackerid);
}

#used to determine check in time
function collect_checkin($trackerid)
{
    return PatientTrackerService::collect_checkin($trackerid);
}

#used to determine check out time
function collect_checkout($trackerid)
{
    return PatientTrackerService::collect_checkout($trackerid);
}

/* get information the statuses of the appointments*/
function getApptStatus($appointments)
{
    return PatientTrackerService::getApptStatus($appointments);
}
