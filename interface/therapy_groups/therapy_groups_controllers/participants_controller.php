<?php

/**
 * interface/therapy_groups/therapy_groups_controllers/participants_controller.php contains the participants controller for therapy groups.
 *
 * This is the controller for the groups' participant view.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/base_controller.php';
require_once __DIR__ . '/therapy_groups_controller.php';

use OpenEMR\Common\Session\TherapyGroupSessionUtil;
use Symfony\Component\HttpFoundation\Request;

class ParticipantsController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->groupParticipantsModel = $this->loadModel('therapy_groups_participants');
        $this->groupEventsModel = $this->loadModel('Therapy_Groups_Events');
        $this->groupModel = $this->loadModel('therapy_groups');
    }

    /**
     * Show the group's participants after applying a posted update or a
     * delete link; both writes need the CSRF token. Renders and exits.
     */
    public function index(int $groupId, $data = [])
    {

        if (isset($_POST['save'])) {
            TherapyGroupSessionUtil::requireCsrfToken($this->request);
            for ($k = 0; $k < count($_POST['pid']); $k++) {
                $patient['pid'] = $_POST['pid'][$k];
                $patient['group_patient_status'] = $_POST['group_patient_status'][$k];
                $patient['group_patient_start'] = DateToYYYYMMDD($_POST['group_patient_start'][$k]);
                $patient['group_patient_end'] = DateToYYYYMMDD($_POST['group_patient_end'][$k]);
                $patient['group_patient_comment'] = $_POST['group_patient_comment'][$k];

                $filters = [
                    'group_patient_status' => FILTER_VALIDATE_INT,
                    'group_patient_start' => FILTER_UNSAFE_RAW,
                    'group_patient_end' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'group_patient_comment' => FILTER_UNSAFE_RAW,
                ];
                //filter and sanitize all post data.
                $participant = filter_var_array($patient, $filters);
                $this->groupParticipantsModel->updateParticipant($participant, $patient['pid'], $_POST['group_id']);
                unset($_GET['editParticipants']);
            }
        }

        if (isset($_GET['deleteParticipant'])) {
            TherapyGroupSessionUtil::requireCsrfToken($this->request);
            $this->groupParticipantsModel->removeParticipant($_GET['group_id'], $_GET['pid']);
        }

        $data['events'] = $this->groupEventsModel->getGroupEvents($groupId);
        $data['readonly'] = 'disabled';
        $data['participants'] = $this->groupParticipantsModel->getParticipants($groupId);
        $statuses = [];
        $names = [];
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

        TherapyGroupsController::setSession($groupId, $this->request);

        $this->loadView('groupDetailsParticipants', $data);
    }


    /**
     * Register the posted patient in the group, which needs the CSRF token,
     * then show the participant list. Renders and exits.
     */
    public function add(int $groupId)
    {

        if (isset($_POST['save_new'])) {
            TherapyGroupSessionUtil::requireCsrfToken($this->request);
            $_POST['group_patient_start'] = DateToYYYYMMDD($_POST['group_patient_start']);

            $alreadyRegistered = $this->groupParticipantsModel->isAlreadyRegistered($_POST['pid'], $groupId);
            if ($alreadyRegistered) {
                $this->index($groupId, ['participant_data' => $_POST, 'addStatus' => 'failed','message' => xlt('The patient already registered to the group')]);
            }

            // adding group id to $_POST
            $_POST = ['group_id' => $groupId] + $_POST;

            $filters = [
                'group_id' => FILTER_VALIDATE_INT,
                'pid' => FILTER_VALIDATE_INT,
                'group_patient_start' => FILTER_UNSAFE_RAW,
                'group_patient_comment' => FILTER_UNSAFE_RAW,
            ];

            $participant_data = filter_var_array($_POST, $filters);

            $participant_data['group_patient_status'] = 10;
            $participant_data['group_patient_end'] = 'NULL';

            $this->groupParticipantsModel->saveParticipant($participant_data);
        }

        $this->index($groupId, ['participant_data' => null]);
    }
}
