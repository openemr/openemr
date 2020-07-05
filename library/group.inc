<?php

/**
 * interface/therapy_groups/index.php routing for therapy groups
 *
 * group.inc includes methods for groups
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

require_once(dirname(__FILE__) . "/sql.inc");
require_once(dirname(__FILE__) . "/../interface/therapy_groups/therapy_groups_models/therapy_groups_model.php");
require_once(dirname(__FILE__) . "/../interface/therapy_groups/therapy_groups_models/group_statuses_model.php");
require_once(dirname(__FILE__) . "/../interface/therapy_groups/therapy_groups_models/therapy_groups_counselors_model.php");
require_once(dirname(__FILE__) . "/../interface/therapy_groups/therapy_groups_models/users_model.php");
require_once(dirname(__FILE__) . "/../interface/therapy_groups/therapy_groups_models/therapy_groups_participants_model.php");
require_once(dirname(__FILE__) . "/../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php");

use OpenEMR\Common\Session\SessionUtil;

//Fetches groups data by given search parameter (used in popup search when in add_edit_event for groups)
function getGroup($gid)
{
    $model = new Therapy_Groups();
    $result = $model->getGroup($gid);
    return $result;
}

//Fetches groups data by given search parameter (used in popup search when in add_edit_event for groups)
function getGroupData($search_params, $result_columns, $column)
{
    $model = new Therapy_Groups();
    $result = $model->getGroupData($search_params, $result_columns, $column);
    return $result;
}

//Fetches group statuses from 'groupstat' list
function getGroupStatuses()
{
    $model = new Group_Statuses();
    $result = $model->getGroupStatuses();
    return $result;
}

//Fetches group attendance statuses from 'attendstat' list
function getGroupAttendanceStatuses()
{
    $model = new Group_Statuses();
    $result = $model->getGroupAttendanceStatuses();
    return $result;
}

//Fetches counselors for specific group
function getCounselors($gid)
{
    $model = new Therapy_Groups_Counselors();
    $result = $model->getCounselors($gid);
    return $result;
}

//Fetches participants of group
function getParticipants($gid, $onlyActive = false)
{
    $model = new Therapy_groups_participants();
    $result = $model->getParticipants($gid, $onlyActive);
    return $result;
}

//Fetches group status name by status key
function getTypeName($key)
{
    $types_array = TherapyGroupsController::prepareGroupTypesList();
    $types_name = $types_array[$key];
    return $types_name;
}

//Fetches providers for a specific group event
function getProvidersOfEvent($eid)
{
    $model = new Users();
    $result = $model->getProvidersOfEvent($eid);
    return $result;
}

//Fetches name of user by his id
function getUserNameById($uid)
{
    $model = new Users();
    $result = $model->getUserNameById($uid);
    return $result;
}

function getGroupCounselorsNames($gid)
{

    $model = new Therapy_Groups_Counselors();
    $result = $model->getAllCounselorsNames($gid);
    return $result;
}

function unsetGroup()
{
    SessionUtil::unsetSession('therapy_group');
}
