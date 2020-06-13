<?php

/**
 * interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php contains the main controller for therapy groups.
 *
 * This is the main controller for the therapy group views and functionality.
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

require_once dirname(__FILE__) . '/base_controller.php';
require_once("{$GLOBALS['srcdir']}/appointments.inc.php");
require_once("{$GLOBALS['srcdir']}/pid.inc");

use OpenEMR\Common\Session\SessionUtil;

class TherapyGroupsController extends BaseController
{

    public $therapyGroupModel;

    /* Note: Created functions to return arrays so that xl method can be used in array rendering. */

    //list of group statuses
    public static function prepareStatusesList()
    {
        $statuses = array(
            '10' => xl('Active'),
            '20' => xl('Finished'),
            '30' => xl('Canceled')
        );
        return $statuses;
    }

    //list of participant statuses
    public static function prepareParticipantStatusesList()
    {
        $participant_statuses = array(
                '10' => xl('Active'),
                '20' => xl('Not active')
        );
        return $participant_statuses;
    }

    //list of group types
    public static function prepareGroupTypesList()
    {
        $group_types = array(
            '1' => xl('Closed'),
            '2' => xl('Open'),
            '3' => xl('Training')
        );
        return $group_types;
    }

    //list of participation types
    public static function prepareGroupParticipationList()
    {
        $group_participation = array(
            '1' => xl('Mandatory'),
            '2' => xl('Optional')
        );
        return $group_participation;
    }

    //Max length of notes preview in groups list
    private $notes_preview_proper_length = 30;


    /**
     * add / edit therapy group
     * making validation and saving in the match tables.
     * @param null $groupId - must pass when edit group
     */
    public function index($groupId = null)
    {

        $data = array();
        if ($groupId) {
            self::setSession($groupId);
        }

        //Load models
        $this->therapyGroupModel = $this->loadModel('therapy_groups');
        $this->counselorsModel = $this->loadModel('Therapy_Groups_Counselors');
        $eventsModel = $this->loadModel('Therapy_Groups_Events');
        $userModel = $this->loadModel('Users');

        //Get group events
        if ($groupId) {
            $events = $eventsModel->getGroupEvents($groupId);
            $data['events'] = $events;
        }

        //Get users
        $users = $userModel->getAllUsers();
        $data['users'] = $users;

        //Get statuses
        $data['statuses'] = self::prepareStatusesList();

        $_POST['group_start_date'] = DateToYYYYMMDD($_POST['group_start_date']);
        $_POST['group_end_date'] = DateToYYYYMMDD($_POST['group_end_date']);

        if (isset($_POST['save'])) {
            $isEdit = empty($_POST['group_id']) ? false : true;

            // for new group - checking if already exist same name
            if ($_POST['save'] != 'save_anyway' && $this->alreadyExist($_POST, $isEdit)) {
                $data['message'] = xlt('Failed - already has group with the same name') . '.';
                $data['savingStatus'] = 'exist';
                $data['groupData'] = $_POST;
                if ($isEdit) {
                    $this->loadView('groupDetailsGeneralData', $data);
                } else {
                    $this->loadView('addGroup', $data);
                }
            }

            $filters = array(
                'group_name' => FILTER_DEFAULT,
                'group_start_date' => FILTER_SANITIZE_SPECIAL_CHARS,
                'group_type' => FILTER_VALIDATE_INT,
                'group_participation' => FILTER_VALIDATE_INT,
                'group_status' => FILTER_VALIDATE_INT,
                'group_notes' => FILTER_DEFAULT,
                'group_guest_counselors' => FILTER_DEFAULT,
                'counselors' => array('filter'    => FILTER_VALIDATE_INT,
                                      'flags'     => FILTER_FORCE_ARRAY)
            );
            if ($isEdit) {
                $filters['group_end_date'] = FILTER_SANITIZE_SPECIAL_CHARS;
                $filters['group_id'] = FILTER_VALIDATE_INT;
            }

            //filter and sanitize all post data.
            $data['groupData'] = filter_var_array($_POST, $filters);
            if (!$data['groupData']) {
                $data['message'] = xlt('Failed to create new group') . '.';
                $data['savingStatus'] = 'failed';
            } else {
                if (!$isEdit) {
                    // save new group
                    $id = $this->saveNewGroup($data['groupData']);
                    $data['groupData']['group_id'] = $id;
                    $data['message'] = xlt('New group was saved successfully') . '.';
                    $data['savingStatus'] = 'success';
                    self::setSession($id);
                    $events = $eventsModel->getGroupEvents($id);
                    $data['events'] = $events;
                    $data['readonly'] = 'disabled';

                    $this->loadView('groupDetailsGeneralData', $data);
                } else {
                    //update group
                    $this->updateGroup($data['groupData']);
                    $data['message'] = xlt("Detail's group was saved successfully") . '.';
                    $data['savingStatus'] = 'success';
                    $data['readonly'] = 'disabled';
                    $this->loadView('groupDetailsGeneralData', $data);
                }
            }

        // before saving
        } else {
            if (is_null($groupId)) {
                //for new form
                $data['groupData'] = array('group_name' => null,
                    'group_start_date' => date('Y-m-d'),
                    'group_type' => null,
                    'group_participation' => null,
                    'group_notes' => null,
                    'group_guest_counselors' => null,
                    'group_status' => null
                );
                $this->loadView('addGroup', $data);
            } else {
                //for exist group screen
                $data['groupData'] = $this->therapyGroupModel->getGroup($groupId);
                $data['groupData']['counselors'] = $this->counselorsModel->getCounselors($groupId);
                $data['readonly'] = isset($_GET['editGroup']) ? '' : 'disabled';

                $this->loadView('groupDetailsGeneralData', $data);
            }
        }
    }

    /**
     * check if exist group with the same name and same start date
     * @param $groupData
     * @param $isEdit type of testing
     * @return bool
     */
    private function alreadyExist($groupData, $isEdit = false)
    {

        if ($isEdit) {
            //return false if not touched on name and date
            $databaseData = $this->therapyGroupModel->getGroup($groupData['group_id']);
            if ($databaseData['group_name'] == $groupData['group_name'] && $databaseData['group_start_date'] == $groupData['group_start_date']) {
                return false;
            }
        }

        $isExistGroup = $this->therapyGroupModel->existGroup($groupData['group_name'], $groupData['group_start_date'], $isEdit ? $groupData['group_id'] : null);
        //true / false
        return $isExistGroup;
    }

    /**
     * Controller for loading the therapy groups to be listed in 'listGroups' view.
     */
    public function listGroups()
    {

        //If deleting a group
        if ($_GET['deleteGroup'] == 1) {
            $group_id = $_GET['group_id'];
            $deletion_response = $this->deleteGroup($group_id);
            $data['deletion_try'] = 1;
            $data['deletion_response'] = $deletion_response;
        }

        //Load therapy groups from DB.
        $therapy_groups_model = $this->loadModel('Therapy_Groups');
        $therapy_groups = $therapy_groups_model->getAllGroups();

        //Load counselors from DB.
        $counselors_model = $this->loadModel('Therapy_Groups_Counselors');
        $counselors = $counselors_model->getAllCounselors();

        //Merge counselors with matching groups and prepare array for view.
        $data['therapyGroups'] = $this->prepareGroups($therapy_groups, $counselors);

        //Insert static arrays to send to view.
        $data['statuses'] = self::prepareStatusesList();
        $data['group_types'] = self::prepareGroupTypesList();
        $data['group_participation'] = self::prepareGroupParticipationList();
        $data['counselors'] = $this->prepareCounselorsList($counselors);

        //Send groups array to view.
        $this->loadView('listGroups', $data);
    }

    /**
     * Prepares the therapy group list that will be sent to view.
     * @param $therapy_groups
     * @param $counselors
     * @return array
     */
    private function prepareGroups($therapy_groups, $counselors)
    {

        $new_array = array();
        $users_model = $this->loadModel('Users');

        //Insert groups into a new array and shorten notes for preview in list
        foreach ($therapy_groups as $therapy_group) {
            $gid = $therapy_group['group_id'];
            $new_array[$gid] = $therapy_group;
            $new_array[$gid]['group_notes'] = $this->shortenNotes($therapy_group['group_notes']);
            $new_array[$gid]['counselors'] = array();
        }

        //Insert the counselors into their groups in new array.
        foreach ($counselors as $counselor) {
            $group_id_of_counselor = $counselor['group_id'];
            $counselor_id = $counselor['user_id'];
            $counselor_name = $users_model->getUserNameById($counselor_id);
            if (is_array($new_array[$group_id_of_counselor])) {
                array_push($new_array[$group_id_of_counselor]['counselors'], $counselor_name);
            }
        }

        return $new_array;
    }

    private function shortenNotes($notes)
    {

        $length = strlen($notes);
        if ($length > $this->notes_preview_proper_length) {
            $notes = mb_substr($notes, 0, 50) . '...';
        }

        return $notes;
    }

    /**
     * Returns a list of counselors without duplicates.
     * @param $counselors
     * @return array
     */
    private function prepareCounselorsList($counselors)
    {

        $new_array = array();
        $users_model = $this->loadModel('Users');

        foreach ($counselors as $counselor) {
            $counselor_id = $counselor['user_id'];
            $counselor_name = $users_model->getUserNameById($counselor_id);
            $new_array[$counselor_id] = $counselor_name;
        }

        return $new_array;
    }

    /**
     * Change group status to 'deleted'. Can be done only if group has no encounters.
     * @param $group_id
     * @return array
     */
    private function deleteGroup($group_id)
    {

        $response = array();

        //If group has encounters cannot delete the group.
        $group_has_encounters = $this->checkIfHasApptOrEncounter($group_id);
        if ($group_has_encounters) {
            $response['success'] = 0;
            $response['message'] = xl("Deletion failed because group has appointments or encounters");
        } else {
            //Change group status to 'canceled'.
            $therapy_groups_model = $this->loadModel('Therapy_Groups');
            $therapy_groups_model->changeGroupStatus($group_id, 30);
            $response['success'] = 1;
        }

        return $response;
    }

    /**
     * Checks if group has upcoming  appointments or encounters
     * @param $group_id
     * @return bool
     */
    private function checkIfHasApptOrEncounter($group_id)
    {
        $therapy_groups_events_model = $this->loadModel('Therapy_Groups_Events');
        $therapy_groups_encounters_model = $this->loadModel('Therapy_Groups_Encounters');
        $events = $therapy_groups_events_model->getGroupEvents($group_id);
        $encounters = $therapy_groups_encounters_model->getGroupEncounters($group_id);
        if (empty($events) && empty($encounters)) {
            return false; //no appts or encounters so can delete
        }

        return true; //appts or encounters exist so can't delete
    }



    /**
     * insert a new group to therapy_group table and connect between user-counselor to group at therapy_Groups_Counselors table
     * @param $groupData
     * @return int $groupId
     */
    private function saveNewGroup($groupData)
    {

        $counselors = !empty($groupData['counselors']) ? $groupData['counselors'] : array();
        unset($groupData['groupId'], $groupData['save'], $groupData['counselors']);

        $groupId = $this->therapyGroupModel->saveNewGroup($groupData);

        foreach ($counselors as $counselorId) {
            $this->counselorsModel->save($groupId, $counselorId);
        }

        return $groupId;
    }

    /**
     * update group in therapy_group table and the connection between user-counselor to group at therapy_Groups_Counselors table
     * @param $groupData
     * @return int $groupId
     */
    private function updateGroup($groupData)
    {

        $counselors = !empty($groupData['counselors']) ? $groupData['counselors'] : array();
        unset($groupData['save'], $groupData['counselors']);

        $this->therapyGroupModel->updateGroup($groupData);

        $this->counselorsModel->remove($groupData['group_id']);
        foreach ($counselors as $counselorId) {
            $this->counselorsModel->save($groupData['group_id'], $counselorId);
        }
    }

    static function setSession($groupId)
    {

        setpid(0);
        if ($_SESSION['therapy_group'] != $groupId) {
            SessionUtil::setSession('therapy_group', $groupId);
        }
    }
}
