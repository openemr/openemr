<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

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
    $sql_for_form_tga = "INSERT INTO form_therapy_groups_attendance (id, date, group_id, user, groupname, authorized, encounter_id, activity) " .
        "VALUES(?,NOW(),?,?,?,?,?,?);";
    $sqlBindArray = array();
    array_push($sqlBindArray, $newid, $therapy_group, $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $encounter, '1');
    sqlInsert($sql_for_form_tga, $sqlBindArray);
}
//If editing a form
elseif ($_GET['mode'] == 'update'){

    $id = $_GET['id'];
    $sql_for_form_tga = "UPDATE form_therapy_groups_attendance SET date = NOW(), user = ?, groupname = ?, authorized = ? WHERE id = ?;";
    $sqlBindArray = array();
    array_push($sqlBindArray,  $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $id);
    sqlInsert($sql_for_form_tga, $sqlBindArray);
}


//$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>