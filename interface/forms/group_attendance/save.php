<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("functions.php");

//Get relevant data from group appt (the appt that created the group encounter)
$appt_data = get_appt_data($encounter);

//Get relevant data from group encounter
$group_encounter_data = get_group_encounter_data($encounter);

//If saving new form
if($_GET['mode'] == 'new') {

    //Get the number that should be the new form's id
    $res = sqlStatement("SELECT MAX(id) as largestId FROM `form_therapy_groups_attendance`");
    $getMaxid = sqlFetchArray($res);
    if ($getMaxid['largestId']) {
        $newid = $getMaxid['largestId'] + 1;
    } else {
        $newid = 1;
    }

    //Insert into 'forms' table
    addForm($encounter, "Group Attendance Form", $newid, "group_attendance", null, $userauthorized);

    //Insert into form_therapy_groups_attendance table
    $sql_for_table_ftga = "INSERT INTO form_therapy_groups_attendance (id, date, group_id, user, groupname, authorized, encounter_id, activity) " .
        "VALUES(?,NOW(),?,?,?,?,?,?);";
    $sqlBindArray = array();
    array_push($sqlBindArray, $newid, $therapy_group, $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $encounter, '1');
    sqlInsert($sql_for_table_ftga, $sqlBindArray);

    $patientData = $_POST['patientData'];
    foreach ($patientData as $pid => $patient){

        //Insert into therapy_groups_participants_attendance table
        insert_into_tgpa_table($newid, $pid, $patient);

        //Check if to create appt and encounter for each patient (if has certain status and 'bottom' submit was pressed, not 'add_patient' submit).
        $create_for_patient = if_to_create_for_patient($patient['status']);
        if($create_for_patient && empty($_POST['submit_new_patient'])){

            //Create appt for each patient (if there is appointment connected to encounter)
            if(!empty($appt_data)){
                insert_patient_appt($pid, $therapy_group, $appt_data['pc_aid'], $appt_data['pc_eventDate'], $appt_data['pc_startTime'], $patient);
            }

            //Create encounter for each patient
            insert_patient_encounter($pid, $therapy_group, $group_encounter_data['date'], $patient, $appt_data['pc_aid']);

        }

    }

    //If adding a new participant
    if(isset($_POST['submit_new_patient'])){
        $new_participant_id = $_POST['new_id'];
        $new_comment = $_POST['new_comment'];
        insert_new_participant($newid, $new_participant_id, $new_comment);
        jumpToEdit($newid);
    }
    else
        formJump();


}
//If editing a form
elseif ($_GET['mode'] == 'update'){

    //Update form_therapy_groups_attendance table
    $id = $_GET['id'];
    $sql_for_form_tga = "UPDATE form_therapy_groups_attendance SET date = NOW(), user = ?, groupname = ?, authorized = ? WHERE id = ?;";
    $sqlBindArray = array();
    array_push($sqlBindArray,  $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $id);
    sqlInsert($sql_for_form_tga, $sqlBindArray);

    //Delete from therapy_groups_participant_attendance table
    $sql_delete_from_table_tgpa = "DELETE FROM therapy_groups_participant_attendance WHERE form_id = ?;";
    sqlStatement($sql_delete_from_table_tgpa, array($id));

    $patientData = $_POST['patientData'];
    foreach ($patientData as $pid => $patient){

        //Insert into therapy_groups_participants_attendance table
        insert_into_tgpa_table($id, $pid, $patient);

        //Check if to create appt and encounter for each patient (if has certain status and 'bottom' submit was pressed, not 'add_patient' submit).
        $create_for_patient = if_to_create_for_patient($patient['status']);
        if($create_for_patient && empty($_POST['submit_new_patient'])){

            //Create appt for each patient (if there is appointment connected to encounter)
            if(!empty($appt_data)) {
                insert_patient_appt($pid, $therapy_group, $appt_data['pc_aid'], $appt_data['pc_eventDate'], $appt_data['pc_startTime'], $patient);
            }
            //Create encounter for each patient
            insert_patient_encounter($pid, $therapy_group, $group_encounter_data['date'], $patient, $appt_data['pc_aid']);

        }

    }

    //If adding a new participant
    if(isset($_POST['submit_new_patient'])){
        $new_participant_id = $_POST['new_id'];
        $new_comment = $_POST['new_comment'];
        insert_new_participant($id, $new_participant_id, $new_comment);
        jumpToEdit($id);
    }
    else
        formJump();
}

?>