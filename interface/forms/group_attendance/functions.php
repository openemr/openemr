<?php

include_once("statics.php");

function insert_into_tgpa_table($form_id, $pid, $participantData){
    $sql_for_table_tgpa = "INSERT INTO therapy_groups_participant_attendance (form_id, pid, meeting_patient_comment, meeting_patient_status) " .
        "VALUES(?,?,?,?);";
    sqlInsert($sql_for_table_tgpa, array($form_id, $pid, $participantData['comment'], $participantData['status']));
}

function insert_new_participant($form_id, $new_participant_id, $new_comment){
    $sql_for_table_tgpa = "INSERT INTO therapy_groups_participant_attendance (form_id, pid, meeting_patient_comment, meeting_patient_status) " .
        "VALUES(?,?,?,?);";
    sqlInsert($sql_for_table_tgpa, array($form_id, $new_participant_id, $new_comment, 20));
}

function insert_patient_appt($pid, $gid, $pc_aid, $pc_eventDate, $pc_startTime, $participantData){
    $select_sql = "SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_pid = ? AND pc_gid = ? AND pc_eventDate = ? AND pc_startTime = ?;";
    $result = sqlStatement($select_sql, array($pid, $gid, $pc_eventDate, $pc_startTime));
    $result_array = sqlFetchArray($result);
    $converted_status = convertStatus($participantData['status']);
    if($result_array){
        $insert_sql = "UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid = ?;";
        sqlInsert($insert_sql, array($converted_status, $result_array['pc_eid']));
    }
    else{
        $insert_sql =
            "INSERT INTO openemr_postcalendar_events " .
            "(pc_catid, pc_aid, pc_pid, pc_gid, pc_title, pc_informant, pc_eventDate, pc_recurrspec, pc_startTime, pc_sharing, pc_apptstatus) ".
            "VALUES (1000, ?, ?, ?, 'Group Therapy', 1, ?, ?, ?, 0, ?); ";
        $recurrspec = 'a:6:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";s:6:"exdate";s:0:"";}';
        $sqlBindArray = array();
        array_push($sqlBindArray, $pc_aid, $pid, $gid, $pc_eventDate, $recurrspec, $pc_startTime, $converted_status);
        sqlInsert($insert_sql, $sqlBindArray);
    }
}

function insert_patient_encounter($pid, $gid, $group_encounter_date, $participantData){
    $select_sql = "SELECT id FROM form_encounter WHERE pid = ? AND external_id = ? AND pc_catid = ? AND date = ?; ";
    $result = sqlStatement($select_sql, array($pid, $gid, 1000, $group_encounter_date));
    $result_array = sqlFetchArray($result);
    if($result_array){

    }
}

function get_appt_data($encounter_id){
    $sql =
        "SELECT ope.pc_aid, ope.pc_eventDate, ope.pc_startTime FROM form_groups_encounter as fge " .
        "JOIN openemr_postcalendar_events as ope ON fge.appt_id = ope.pc_eid " .
        "WHERE fge.encounter = ?;";
    $result = sqlStatement($sql, array($encounter_id));
    $result_array = sqlFetchArray($result);
    return $result_array;
}

function get_group_encounter_data($encounter_id){
    $sql = "SELECT date FROM form_groups_encounter WHERE encounter = ?";
    $result = sqlStatement($sql, array($encounter_id));
    $result_array = sqlFetchArray($result);
    return $result_array;
}

function if_to_create_for_patient($status){
    if($status == 20 || $status == 30 || $status == 40)
        return true;
    return false;
}

function convertStatus($status){
    global $statuses_in_meeting_for_appt;
    $converted_status = $statuses_in_meeting_for_appt[$status];
    return $converted_status;
}

function jumpToEdit($form_id){
    $url = "{$GLOBALS['rootdir']}/patient_file/encounter/view_form.php?formname=group_attendance&id=$form_id";
    echo "\n<script language='Javascript'>top.restoreSession();window.location='$url';</script>\n";
    exit;
}

?>