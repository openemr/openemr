<?php

/**
 * interface/forms/group_attendance/functions.php functions for form
 *
 * Contains the functions used for the group attendance form
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
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
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/../../../library/api.inc");
require_once(dirname(__FILE__) . "/../../../library/forms.inc");
require_once(dirname(__FILE__) . "/../../../library/patient_tracker.inc.php");

/**
 * Returns form_id of an existing attendance form for group encounter (if one already exists);
 * @param $encounter
 * @param $group_id
 * @return array|null
 */
function get_form_id_of_existing_attendance_form($encounter, $group_id)
{
    $sql = "SELECT form_id FROM forms WHERE encounter = ? AND formdir = 'group_attendance' AND therapy_group_id = ? AND deleted = 0;";
    $result = sqlQuery($sql, array($encounter, $group_id));
    return $result;
}

/**
 * Inserts participant data into DB
 * @param $form_id
 * @param $therapy_group
 * @param $group_encounter_data
 * @param $appt_data
 */
function participant_insertions($form_id, $therapy_group, $group_encounter_data, $appt_data)
{
    $patientData = $_POST['patientData'];
    foreach ($patientData as $pid => $patient) {
        //Insert into therapy_groups_participants_attendance table
        insert_into_tgpa_table($form_id, $pid, $patient);

        //Check if to create appt and encounter for each patient (if has certain status and 'bottom' submit was pressed, not 'add_patient' submit).
        $create_for_patient = if_to_create_for_patient($patient['status']);
        if ($create_for_patient) {
            //Create encounter for each patient
            $encounter_id = insert_patient_encounter($pid, $therapy_group, $group_encounter_data['date'], $patient, $appt_data['pc_aid']);

            //Create appt for each patient (if there is appointment connected to encounter)
            if (!empty($appt_data)) {
                $pc_eid = insert_patient_appt($pid, $therapy_group, $appt_data['pc_aid'], $appt_data['pc_eventDate'], $appt_data['pc_startTime'], $patient);
                manage_tracker_status($appt_data['pc_eventDate'], $appt_data['pc_startTime'], $pc_eid, $pid, $appt_data['pc_aid'], $patient['status'], $appt_data['pc_room'], $encounter_id);
            }
        }
    }
}

/**
 * Inserts data into therapy_groups_participant_attendance table
 * @param $form_id
 * @param $pid
 * @param $participantData
 */
function insert_into_tgpa_table($form_id, $pid, $participantData)
{
    $sql_for_table_tgpa = "INSERT INTO therapy_groups_participant_attendance (form_id, pid, meeting_patient_comment, meeting_patient_status) " .
        "VALUES(?,?,?,?);";
    sqlInsert($sql_for_table_tgpa, array($form_id, $pid, $participantData['comment'], $participantData['status']));
}

/**
 * Creates an appointment for patient from attendance form
 * @param $pid
 * @param $gid
 * @param $pc_aid
 * @param $pc_eventDate
 * @param $pc_startTime
 * @param $participantData
 */
function insert_patient_appt($pid, $gid, $pc_aid, $pc_eventDate, $pc_startTime, $participantData)
{
    $select_sql = "SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_pid = ? AND pc_gid = ? AND pc_eventDate = ? AND pc_startTime = ?;";
    $result = sqlStatement($select_sql, array($pid, $gid, $pc_eventDate, $pc_startTime));
    $result_array = sqlFetchArray($result);
    if ($result_array) {
        $insert_sql = "UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid = ?;";
        sqlStatement($insert_sql, array($participantData['status'], $result_array['pc_eid']));
        return $result_array['pc_eid'];
    } else {
        $insert_sql =
            "INSERT INTO openemr_postcalendar_events " .
            "(pc_catid, pc_aid, pc_pid, pc_gid, pc_title, pc_informant, pc_eventDate, pc_recurrspec, pc_startTime, pc_sharing, pc_apptstatus) ".
            "VALUES (?, ?, ?, ?, 'Group Therapy', 1, ?, ?, ?, 0, ?); ";
        $recurrspec = 'a:6:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";s:6:"exdate";s:0:"";}';
        $sqlBindArray = array();
        array_push($sqlBindArray, get_groups_cat_id(), $pc_aid, $pid, $gid, $pc_eventDate, $recurrspec, $pc_startTime, $participantData['status']);
        $pc_eid = sqlInsert($insert_sql, $sqlBindArray);
        return $pc_eid;
    }
}

/**
 * Creates an encounter for patient from attendance form
 * @param $pid
 * @param $gid
 * @param $group_encounter_date
 * @param $participantData
 * @param $pc_aid
 */
function insert_patient_encounter($pid, $gid, $group_encounter_date, $participantData, $pc_aid)
{
    $select_sql = "SELECT id, encounter FROM form_encounter WHERE pid = ? AND external_id = ? AND pc_catid = ? AND date = ?; ";
    $result = sqlStatement($select_sql, array($pid, $gid, get_groups_cat_id(), $group_encounter_date));
    $result_array = sqlFetchArray($result);
    if ($result_array) {
        $insert_sql = "UPDATE form_encounter SET reason = ? WHERE id = ?;";
        sqlStatement($insert_sql, array($participantData['comment'], $result_array['id']));
        return $result_array['encounter'];
    } else {
        $insert_encounter_sql =
            "INSERT INTO form_encounter (date, reason, pid, encounter, pc_catid, provider_id, external_id) ".
            "VALUES (?, ?, ?, ?, ?, ?, ?);";
        $enc_id = generate_id();
        $sqlBindArray = array();
        $user = (is_null($pc_aid)) ? $_SESSION["authId"] : $pc_aid;
        array_push($sqlBindArray, $group_encounter_date, $participantData['comment'], $pid, $enc_id, get_groups_cat_id(), $user, $gid);
        $form_id = sqlInsert($insert_encounter_sql, $sqlBindArray);

        global $userauthorized;

        addForm($enc_id, "New Patient Encounter", $form_id, "newpatient", $pid, $userauthorized, $group_encounter_date, '', '', null);

        return $enc_id;
    }
}

/**
 * If the group encounter was created in relation to a group appointment, fetches the appointment relevant data.
 * @param $encounter_id
 * @return array
 */
function get_appt_data($encounter_id)
{
    $sql =
        "SELECT ope.pc_aid, ope.pc_eventDate, ope.pc_startTime, ope.pc_room FROM form_groups_encounter as fge " .
        "JOIN openemr_postcalendar_events as ope ON fge.appt_id = ope.pc_eid " .
        "WHERE fge.encounter = ?;";
    $result = sqlQuery($sql, array($encounter_id));
    return $result;
}

/**
 * Gets group encounter data
 * @param $encounter_id
 * @return array
 */
function get_group_encounter_data($encounter_id)
{
    $sql = "SELECT date FROM form_groups_encounter WHERE encounter = ?";
    $result = sqlQuery($sql, array($encounter_id));
    return $result;
}

/**
 * Checks if to create appointment and encounter for patient himself based on the status in the attendance form.
 * [Note: `toggle_setting_1` in table `list_options` is used as a flag to know the statuses for which an appt or encounter should be created.]
 * @param $status
 * @return bool
 */
function if_to_create_for_patient($status)
{
    $sql = 'SELECT toggle_setting_1 FROM list_options WHERE list_id = \'attendstat\' AND toggle_setting_1 = 1 AND option_id = ?';
    $to_create = sqlQuery($sql, array($status));
    return $to_create;
}

/**
 * Returns the number after the greatest id number in the table
 * @param $table
 * @return int
 */
function largest_id_plus_one($table)
{
    $maxId = largest_id($table);
    if ($maxId) {
        $newid = $maxId + 1;
    } else {
        $newid = 1;
    }

    return $newid;
}

/**
 * Returns greatest id number in the table
 * @param $table
 * @return mixed
 */
function largest_id($table)
{
    $res = sqlStatement("SELECT MAX(id) as largestId FROM `" . escape_table_name($table) . "`");
    $getMaxid = sqlFetchArray($res);
    return $getMaxid['largestId'];
}


function get_groups_cat_id()
{
    $result = sqlQuery('SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_cattype = 3 AND pc_active = 1 LIMIT 1');
    return !empty($result) ? $result['pc_catid'] : 0;
}
