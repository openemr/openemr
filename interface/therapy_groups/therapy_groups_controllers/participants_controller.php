<?php

/**
 * interface/therapy_groups/therapy_groups_controllers/participants_controller.php contains the participants controller for therapy groups.
 *
 * This is the controller for the groups' participant view.
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
require_once dirname(__FILE__) . '/therapy_groups_controller.php';
require_once("{$GLOBALS['srcdir']}/pid.inc");

class ParticipantsController extends BaseController
{
    public function __construct()
    {
        $this->groupParticipantsModel = $this->loadModel('therapy_groups_participants');
        $this->groupEventsModel = $this->loadModel('Therapy_Groups_Events');
        $this->groupModel = $this->loadModel('therapy_groups');
    }

    public function index($groupId, $data = array())
    {

        if (isset($_POST['save'])) {
            for ($k = 0; $k < count($_POST['pid']); $k++) {
                $patient['pid'] = $_POST['pid'][$k];
                $patient['group_patient_status'] = $_POST['group_patient_status'][$k];
                $patient['group_patient_start'] = DateToYYYYMMDD($_POST['group_patient_start'][$k]);
                $patient['group_patient_end'] = DateToYYYYMMDD($_POST['group_patient_end'][$k]);
                $patient['group_patient_comment'] = $_POST['group_patient_comment'][$k];

                $filters = array(
                    'group_patient_status' => FILTER_VALIDATE_INT,
                    'group_patient_start' => FILTER_DEFAULT,
                    'group_patient_end' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'group_patient_comment' => FILTER_DEFAULT,
                );
                //filter and sanitize all post data.
                $participant = filter_var_array($patient, $filters);
                $this->groupParticipantsModel->updateParticipant($participant, $patient['pid'], $_POST['group_id']);
                unset($_GET['editParticipants']);
            }
        }

        if (isset($_GET['deleteParticipant'])) {
            $this->groupParticipantsModel->removeParticipant($_GET['group_id'], $_GET['pid']);
        }

        $data['events'] = $this->groupEventsModel->getGroupEvents($groupId);
        $data['readonly'] = 'disabled';
        $data['participants'] = $this->groupParticipantsModel->getParticipants($groupId);
        $statuses = array();
        $names = array();
        foreach ($data['participants'] as $key => $row) {
            $statuses[$key]  = $row['group_patient_status'];
            $names[$key] = $row['lname'] . ' ' . $row['fname'];
        }

        array_multisort($statuses, SORT_ASC, $names, SORT_ASC, $data['participants']);

        $data['statuses'] = TherapyGroupsController::prepareParticipantStatusesList();
        $data['groupId'] = $groupId;
        $groupData = $this->groupModel->getGroup($groupId);
        $data['groupName'] = $groupData['group_name'];

        if (isset($_GET['editParticipants'])) {
            $data['readonly'] = '';
        }

        TherapyGroupsController::setSession($groupId);

        $this->loadView('groupDetailsParticipants', $data);
    }


    public function add($groupId)
    {

        if (isset($_POST['save_new'])) {
            $_POST['group_patient_start'] = DateToYYYYMMDD($_POST['group_patient_start']);

            $alreadyRegistered = $this->groupParticipantsModel->isAlreadyRegistered($_POST['pid'], $groupId);
            if ($alreadyRegistered) {
                $this->index($groupId, array('participant_data' => $_POST, 'addStatus' => 'failed','message' => xlt('The patient already registered to the group')));
            }

            // adding group id to $_POST
            $_POST = array('group_id' => $groupId) + $_POST;

            $filters = array(
                'group_id' => FILTER_VALIDATE_INT,
                'pid' => FILTER_VALIDATE_INT,
                'group_patient_start' => FILTER_DEFAULT,
                'group_patient_comment' => FILTER_DEFAULT,
            );

            $participant_data = filter_var_array($_POST, $filters);

            $participant_data['group_patient_status'] = 10;
            $participant_data['group_patient_end'] = 'NULL';

            $this->groupParticipantsModel->saveParticipant($participant_data);
        }

        $this->index($groupId, array('participant_data' => null));
    }
}
