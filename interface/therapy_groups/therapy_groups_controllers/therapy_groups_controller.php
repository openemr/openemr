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

            if($this->alreadyExist($data['groupData'])){
                $data['message'] = xlt('Failed - already has group with the same name') . '.';
                $data['status'] = 'failed';
            } else {

                if(is_null($groupId)){
                    // save new group
                    $id = $this->saveGroup($data['groupData']);
                    $data['groupData']['id'] = $id;
                    $data['message'] = xlt('New group was saved successfully') . '.';
                    $data['status'] = 'success';
                } else {
                    //update group
                }

            }
        } else {
            //for new form
            $data['groupData'] = array('group_name' => null,
                'group_start_date' => null,
                'group_type' => null,
                'group_participation' => null,
                'notes' => null,
                'guest_counselors' => null,
                'group_status' => null
            );
        }

        $userModel = $this->loadModel('Users');
        $users = $userModel->getAllUsers();
        $data['users'] = $users;
        $data['statuses'] = $this->statuses;

        $this->loadView('addGroup', $data);

    }

    private function alreadyExist($groupData){

        return false;
    }

    /**
     * Controller for loading the therapy groups to be listed in 'listTherapyGroups' view.
     */
    public function listTherapyGroups(){

        //Load therapy groups from DB.
        $therapy_groups_model = $this->loadModel('Therapy_Groups');
        $therapy_groups = $therapy_groups_model->getAllTherapyGroups();

        //Load counselors from DB.
        $counselors_model = $this->loadModel('Therapy_Groups_Counselors');
        $counselors = $counselors_model->getAllCounselors();

        //Merge counselors with matching groups and prepare array for view.
        $data = $this->prepareTherapyGroups($therapy_groups, $counselors);

        //Send groups array to view.
        $this->loadView('listTherapyGroups', $data);
    }

    /**
     * Prepares the therapy group list that will be sent to view.
     * @param $therapy_groups
     * @param $counselors
     * @return array
     */
    private function prepareTherapyGroups($therapy_groups, $counselors){

        $new_array = array();

        //Insert groups into a new array.
        foreach ($therapy_groups as $therapy_group) {
            $gid = $therapy_group['group_id'];
            $new_array[$gid] = $therapy_group;
            $new_array[$gid]['counselors'] = array();
        }

        //Insert the counselors into their groups in new array.
        foreach ($counselors as $counselor){
           $counselor_of_group = $counselor['group_id'];
           array_push($new_array[$counselor_of_group]['counselors'],$counselor['user_id']);
        }

        return $new_array;

    }



}