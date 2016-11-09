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
        '3' => 'training'
    );

    public static $group_participation = array(
        '1' => 'mandatory',
        '2' => 'optional'
    );



    public function  add($groupId = null){

        $data = array();
        $this->therapyGroupModel = $this->loadModel('therapy_groups');

        if(isset($_POST['save'])){

            $filters = array(
                'group_name' => FILTER_SANITIZE_STRING,
                'group_start_date' => FILTER_SANITIZE_SPECIAL_CHARS,
                'group_type' => FILTER_VALIDATE_INT,
                'group_participation' => FILTER_VALIDATE_INT,
                'group_status' => FILTER_VALIDATE_INT,
                'group_notes' => FILTER_SANITIZE_STRING,
                'group_guest_counselors' => FILTER_SANITIZE_STRING,
                'counselors' => array('filter'    => FILTER_VALIDATE_INT,
                                      'flags'     => FILTER_FORCE_ARRAY)
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
        $data['therapyGroups'] = $this->prepareTherapyGroups($therapy_groups, $counselors);

        //Insert static arrays to send to view.
        $data['statuses'] = SELF::$statuses;
        $data['group_types'] = SELF::$group_types;
        $data['group_participation'] = SELF::$group_participation;

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

    private function saveNewGroup($groupData){

        $counselors = !empty($groupData['counselors']) ? $groupData['counselors'] : array();
        unset($groupData['groupId'], $groupData['save'], $groupData['counselors']);

        $groupId = $this->therapyGroupModel->saveNewGroup($groupData);
        $counselors_model = $this->loadModel('Therapy_Groups_Counselors');

        foreach($counselors as $counselorId){
            $counselors_model->save($groupId, $counselorId);
        }
    }



}