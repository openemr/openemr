<?php
/**
 * interface/forms/group_attendance/save.php
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
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



require_once("../../globals.php");
require_once("functions.php");

//Save only if has permission to edit
$can_edit = acl_check("groups", "gadd", false, 'write');
if (!$can_edit) {
    formJump();
}

//Get relevant data from group appt (the appt that created the group encounter)
$appt_data = get_appt_data($encounter);

//Get relevant data from group encounter
$group_encounter_data = get_group_encounter_data($encounter);

//If saving new form
if ($_GET['mode'] == 'new') {
    //Get the number that should be the new form's id
    $newid = largest_id_plus_one('form_group_attendance');

    //Insert into 'forms' table
    addForm($encounter, "Group Attendance Form", $newid, "group_attendance", null, $userauthorized);

    //Insert into form_group_attendance table
    $sql_for_table_ftga = "INSERT INTO form_group_attendance (id, date, group_id, user, groupname, authorized, encounter_id, activity) " .
        "VALUES(?,NOW(),?,?,?,?,?,?);";
    $sqlBindArray = array();
    array_push($sqlBindArray, $newid, $therapy_group, $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $encounter, '1');
    sqlInsert($sql_for_table_ftga, $sqlBindArray);

    //Database insertions for participants
    participant_insertions($newid, $therapy_group, $group_encounter_data, $appt_data);
} //If editing a form
elseif ($_GET['mode'] == 'update') {
    //Update form_group_attendance table
    $id = $_GET['id'];
    $sql_for_form_tga = "UPDATE form_group_attendance SET date = NOW(), user = ?, groupname = ?, authorized = ? WHERE id = ?;";
    $sqlBindArray = array();
    array_push($sqlBindArray, $_SESSION["authUser"], $_SESSION["authProvider"], $userauthorized, $id);
    sqlInsert($sql_for_form_tga, $sqlBindArray);

    //Delete from therapy_groups_participant_attendance table
    $sql_delete_from_table_tgpa = "DELETE FROM therapy_groups_participant_attendance WHERE form_id = ?;";
    sqlStatement($sql_delete_from_table_tgpa, array($id));

    //Database insertions for participants
    participant_insertions($id, $therapy_group, $group_encounter_data, $appt_data);
}

formJump();
