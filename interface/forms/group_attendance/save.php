<?php

/**
 * interface/forms/group_attendance/save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("functions.php");

use OpenEMR\Common\Acl\AclMain;

// Save only if has permission to edit
$can_edit = AclMain::aclCheckCore("groups", "gadd", false, 'write');
if (!$can_edit) {
    formJump();
}

// Get relevant data from group appt (the appt that created the group encounter)
$appt_data = get_appt_data($encounter);

// Get relevant data from group encounter
$group_encounter_data = get_group_encounter_data($encounter);

// If saving new form
if ($_GET['mode'] == 'new') {
    // Get the number that should be the new form's id
    $newid = largest_id_plus_one('form_group_attendance');

    // Insert into 'forms' table
    addForm($encounter, "Group Attendance Form", $newid, "group_attendance", null, $userauthorized);

    // Insert into form_group_attendance table
    $sql_for_table_ftga = "INSERT INTO form_group_attendance (id, date, group_id, user, groupname, authorized, encounter_id, activity) " .
        "VALUES(?,NOW(),?,?,?,?,?,?);";
    $sqlBindArray = array();
    array_push($sqlBindArray, $newid, $therapy_group, $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $encounter, '1');
    sqlStatement($sql_for_table_ftga, $sqlBindArray);

    // Database insertions for participants
    participant_insertions($newid, $therapy_group, $group_encounter_data, $appt_data);
} elseif ($_GET['mode'] == 'update') { // If editing a form
    // Update form_group_attendance table
    $id = $_GET['id'];
    $sql_for_form_tga = "UPDATE form_group_attendance SET date = NOW(), user = ?, groupname = ?, authorized = ? WHERE id = ?;";
    $sqlBindArray = array();
    array_push($sqlBindArray, $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $id);
    sqlStatement($sql_for_form_tga, $sqlBindArray);

    // Delete from therapy_groups_participant_attendance table
    $sql_delete_from_table_tgpa = "DELETE FROM therapy_groups_participant_attendance WHERE form_id = ?;";
    sqlStatement($sql_delete_from_table_tgpa, array($id));

    // Database insertions for participants
    participant_insertions($id, $therapy_group, $group_encounter_data, $appt_data);
}

formJump();
