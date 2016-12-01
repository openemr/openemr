<?php
/** group.inc includes methods for groups  */

require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once("{$GLOBALS['include_root']}/therapy_groups/therapy_groups_models/therapy_groups_model.php");
require_once("{$GLOBALS['include_root']}/therapy_groups/therapy_groups_models/group_statuses_model.php");
require_once("{$GLOBALS['include_root']}/therapy_groups/therapy_groups_models/therapy_groups_counselors_model.php");
require_once("{$GLOBALS['include_root']}/therapy_groups/therapy_groups_models/users_model.php");
require_once("{$GLOBALS['include_root']}/therapy_groups/therapy_groups_models/therapy_groups_participants_model.php");
require_once("{$GLOBALS['include_root']}/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php");


//Fetches groups data by given search parameter (used in popup search when in add_edit_event for groups)
function getGroup($gid){
    $model = new Therapy_Groups();
    $result = $model->getGroup($gid);
    return $result;
}

//Fetches groups data by given search parameter (used in popup search when in add_edit_event for groups)
function getGroupData($search_params, $result_columns, $column){
    $model = new Therapy_Groups();
    $result = $model->getGroupData($search_params, $result_columns, $column);
    return $result;
}

//Fetches group statuses from group status list
function getGroupStatuses(){
    $model = new Group_Statuses();
    $result = $model->getGroupStatuses();
    return $result;
}

//Fetches counselors for specific group
function getCounselors($gid){
    $model = new Therapy_Groups_Counselors();
    $result = $model->getCounselors($gid);
    return $result;
}

//Fetches participants of group
function getParticipants($gid){
    $model = new Therapy_groups_participants();
    $result = $model->getParticipants($gid);
    return $result;
}

//Fetches group status name by status key
function getStatusName($key){
    $statuses_array = TherapyGroupsController::$statuses;
    $status_name = $statuses_array[$key];
    return $status_name;
}

//Fetches providers for a specific group event
function getProvidersOfEvent($eid){
    $model = new Users();
    $result = $model->getProvidersOfEvent($eid);
    return $result;
}

//Fetches name of user by his id
function  getUserNameById($uid){
    $model = new Users();
    $result = $model->getUserNameById($uid);
    return $result;
}

function getGroupCounselorsNames($gid){

    $model = new Therapy_Groups_Counselors();
    $result = $model->getAllCounselorsNames($gid);
    return $result;
}

function unsetGroup(){

    unset($_SESSION['therapy_group']);
}


?>