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

        if(isset($_POST['save'])){

            $alreadyRegistered = $this->groupParticipantsModel->isAlreadyRegistered($_POST['pid'], $groupId);
            if($alreadyRegistered){
                $this->index($groupId, array('addStatus' => 'failed','message' => xlt('The patient already registered to the group')));
            }

            $filters = array(
                'participant_name' => FILTER_SANITIZE_STRING,
                'participant_start' => FILTER_SANITIZE_STRING,
                'group_patient_end' => FILTER_SANITIZE_SPECIAL_CHARS,
                'group_patient_comment' => FILTER_SANITIZE_SPECIAL_CHARS,
            );
        }

        $this->index($groupId);
    }

}
