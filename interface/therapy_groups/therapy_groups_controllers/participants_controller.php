<?php

require_once dirname(__FILE__) . '/base_controller.php';
require_once dirname(__FILE__) . '/therapy_groups_controller.php';

class ParticipantsController extends BaseController{

    public function __construct(){
        $this->groupParticipantsModel = $this->loadModel('therapy_groups_participants');
    }

    public function index($groupId ,$data = array()){

        if(isset($_POST['save'])){

            for($k = 0; $k < count($_POST['pid']); $k++){

                $patient['pid'] = $_POST['pid'][$k];
                $patient['group_patient_status'] = $_POST['group_patient_status'][$k];
                $patient['group_patient_start'] = $_POST['group_patient_start'][$k];
                $patient['group_patient_end'] = $_POST['group_patient_end'][$k];
                $patient['group_patient_comment'] = $_POST['group_patient_comment'][$k];

                $filters = array(
                    'group_patient_status' => FILTER_VALIDATE_INT,
                    'group_patient_start' => FILTER_SANITIZE_STRING,
                    'group_patient_end' => FILTER_SANITIZE_SPECIAL_CHARS,
                    'group_patient_comment' => FILTER_SANITIZE_SPECIAL_CHARS,
                );
                //filter and sanitize all post data.
                $participant = filter_var_array($patient, $filters);
                $this->groupParticipantsModel->updateParticipant($participant,$patient['pid'], $_POST['group_id']);
                unset($_GET['editParticipants']);
            }
        }

        if(isset($_GET['deleteParticipant'])){

            $this->groupParticipantsModel->removeParticipant($_GET['group_id'],$_GET['pid']);
        }

        $data['readonly'] = 'disabled';
        $data['participants'] = $this->groupParticipantsModel->getParticipants($groupId);
        $data['statuses'] = TherapyGroupsController::$statuses;
        $data['groupId'] = $groupId;

        if(isset($_GET['editParticipants'])){
            $data['readonly'] = '';
        }

        $this->loadView('groupDetailsParticipants', $data);
    }


    public function add($groupId){

        if(isset($_POST['save_new'])){

            $alreadyRegistered = $this->groupParticipantsModel->isAlreadyRegistered($_POST['pid'], $groupId);
            if($alreadyRegistered){
                $this->index($groupId, array('participant_data' => $_POST, 'addStatus' => 'failed','message' => xlt('The patient already registered to the group')));
            }
            // adding group id to $_POST
            $_POST = array('group_id' => $groupId) + $_POST;

            $filters = array(
                'group_id' => FILTER_VALIDATE_INT,
                'pid' => FILTER_VALIDATE_INT,
                'group_patient_start' => FILTER_SANITIZE_STRING,
                'group_patient_comment' => FILTER_SANITIZE_SPECIAL_CHARS,
            );

            $participant_data = filter_var_array($_POST, $filters);

            $participant_data['group_patient_status'] = 10;
            $participant_data['group_patient_end'] = 'NULL';

            $this->groupParticipantsModel->saveParticipant($participant_data);
        }

        $this->index($groupId, array('participant_data' => null));
    }

}
