<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 12:16
 */
require dirname(__FILE__) . '/base_controller.php';

class TherapyGroupsController extends BaseController{

    public $therapyGroupModel;

    public static $statuses = array(
      '10' =>   'active'
    );

    public static $group_types = array(
        '1' => 'closed',
        '2' => 'open',
        '3' => 'train'
    );

    public static $group_participation = array(
      '1' => 'mandatory',
      '2' => 'optional'
    );



    public function  add($groupId = null){

        $data = array();
        $this->therapyGroupModel = $this->loadModel('therapy_groups');

        if(isset($_POST['save'])){
            print_r($_POST);
            $filters = array(
                'group_name' => FILTER_SANITIZE_STRING,
                'group_start_date' => FILTER_SANITIZE_SPECIAL_CHARS,
                'group_type' => FILTER_VALIDATE_INT,
                'group_participation' => FILTER_VALIDATE_INT,
                'group_status' => FILTER_VALIDATE_INT,
                'group_notes' => FILTER_SANITIZE_STRING,
                'group_guest_counselors' => FILTER_SANITIZE_STRING,
            );

            $data['groupData'] = filter_var_array($_POST, $filters);

            if($this->alreadyExist($data['groupData'])){
                $data['message'] = xlt('Failed - already has group with the same name') . '.';
                $data['status'] = 'failed';
            } else {

                if(empty( $data['groupData']['group_id'])){
                    // save new group
                    $id = $this->saveNewGroup($data['groupData']);
                    $data['groupData']['group_id'] = $id;
                    $data['message'] = xlt('New group was saved successfully') . '.';
                    $data['status'] = 'success';
                } else {
                    //update group
                }

            }
        // before saving
        } else {

            if(is_null($groupId)){
                //for new form
                $data['groupData'] = array('group_name' => null,
                    'group_start_date' => date('Y-m-d'),
                    'group_type' => null,
                    'group_participation' => null,
                    'group_notes' => null,
                    'group_guest_counselors' => null,
                    'group_status' => null
                );
            } else {
                $data['groupData'] = $this->therapyGroupModel->getGroup($groupId);
            }

        }

        $userModel = $this->loadModel('Users');
        $users = $userModel->getAllUsers();
        $data['users'] = $users;
        $data['statuses'] = self::$statuses;

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

    private function alreadyExist($groupData){

        return false;
    }

    private function saveNewGroup($groupData){

        $counselors = $groupData['counselors'];
        unset($groupData['groupId'], $groupData['save'], $groupData['counselors']);

        $groupId = $this->therapyGroupModel->saveNewGroup($groupData);
        foreach($counselors as $counselorId){

        }

    }


}