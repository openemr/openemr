<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 12:16
 */
require dirname(__FILE__) . '/base_controller.php';

class TherapyGroupsController extends BaseController{

    public $statuses = array(
      '10' =>   'active'
    );

    public function  add(){

        $data = array();
        $userModel = $this->loadModel('Users');
        $users = $userModel->getAllUsers();
        $data['users'] = $users;
        $data['statuses'] = $this->statuses;

        $this->loadView('addGroup', $data);

    }

    public function listGroups(){

        $therapy_groups_model = $this->loadModel('Therapy_Groups');
        $therapy_groups = $therapy_groups_model->getAllTherapyGroups();

        $counselors_model = $this->loadModel('Therapy_Groups_Counselors');
        $counselors = $counselors_model->getAllCounselors();

        $data = $this->prepareTherapyGroups($therapy_groups, $counselors);

        $this->loadView('listGroups', $data);
    }

    private function prepareTherapyGroups($therapy_groups){

    }


}