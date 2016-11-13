<?php

require_once dirname(__FILE__) . '/base_controller.php';
require_once dirname(__FILE__) . '/therapy_groups_controller.php';

class ParticipantsController extends BaseController{

    public function index($groupId){

        if(isset($_POST['save'])){

            echo "<pre>";print_r($_POST);die;
        }

        $data = array();
        $data['readonly'] = 'disabled';
        $this->groupParticipantsModel = $this->loadModel('therapy_groups_participants');
        $data['participants'] = $this->groupParticipantsModel->getParticipants($groupId);
        $data['statuses'] = TherapyGroupsController::$statuses;
        $data['groupId'] = $groupId;

        if(isset($_GET['editParticipants'])){
            $data['readonly'] = '';
        }

        $this->loadView('groupDetailsParticipants', $data);

    }

}
