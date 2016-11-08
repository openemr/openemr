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

    public function  add($groupId = null){

        $data = array();
        if(isset($_POST['save'])){


            $filters = array(
                'group_name' => FILTER_SANITIZE_STRING,
                'group_start_date' => FILTER_SANITIZE_SPECIAL_CHARS,
                'group_type' => FILTER_SANITIZE_STRING,
                'group_participation' => FILTER_SANITIZE_STRING,
                'notes' => FILTER_SANITIZE_STRING,
                'guest_counselors' => FILTER_SANITIZE_STRING,
                'group_status' => FILTER_VALIDATE_INT
            );

            $data['groupData'] = filter_var_array($_POST, $filters);

            if($this->alredyExist($data['groupData'])){
                $data['message'] = xlt('Failed - already has group with the same name.');
                $data['status'] = 'failed';
            } else {
                $this->saveGroup($data['groupData']);
            }
        }


        $userModel = $this->loadModel('Users');
        $users = $userModel->getAllUsers();
        $data['users'] = $users;
        $data['statuses'] = $this->statuses;

        $this->loadView('addGroup', $data);

    }


}