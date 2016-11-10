<?php

require_once dirname(__FILE__) . '/base_controller.php';

class ParticipantsController extends BaseController{

    public function index(){

        $this->groupParticipantsModel = $this->loadModel('therapy_groups_participants');

        $this->loadView('groupDetailsParticipants');

    }

}
