<?php

function insert_into_tgpa_table($form_id){
    $sql_for_table_tgpa = "INSERT INTO therapy_groups_participant_attendance (form_id, pid, meeting_patient_comment, meeting_patient_status) " .
        "VALUES(?,?,?,?);";
    $patientData = $_POST['patientData'];
    foreach ($patientData as $key => $patient){
        sqlInsert($sql_for_table_tgpa, array($form_id, $key, $patient['comment'], $patient['status']));
    }
}

function insert_new_participant($form_id){
    $sql_for_table_tgpa = "INSERT INTO therapy_groups_participant_attendance (form_id, pid, meeting_patient_comment, meeting_patient_status) " .
        "VALUES(?,?,?,?);";
    $new_participant_id = $_POST['new_id'];
    $new_comment = $_POST['new_comment'];
    sqlInsert($sql_for_table_tgpa, array($form_id, $new_participant_id, $new_comment, 20));
}

function jumpToEdit($form_id){
    $url = "{$GLOBALS['rootdir']}/patient_file/encounter/view_form.php?formname=group_attendance&id=$form_id";
    echo "\n<script language='Javascript'>top.restoreSession();window.location='$url';</script>\n";
    exit;
}

function insert_patient_appt($pid){

}

function insert_patient_encounter($pid){

}

function get_appt_data($encounter_id){
    $sql = "SELECT ope.pc_aid, ope.pc_eventDate, ope.pc_startTime FROM form_groups_encounter as fge " .
        "JOIN openemr_postcalendar_events as ope ON fge.appt_id = ope.pc_eid " .
        "WHERE fge.encounter = ?;";
    $result = sqlStatement($sql, array($encounter_id));
    $result_array = sqlFetchArray($result);
    return $result_array;
}
?>