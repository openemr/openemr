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

    //list of group statuses
    public static $statuses = array(
        '10' =>   'active',
        '20' => 'deleted',
    );
    //list of group types
    public static $group_types = array(
        '1' => 'closed',
        '2' => 'open',
        '3' => 'training'
    );
    //list of participation types
    public static $group_participation = array(
        '1' => 'mandatory',
        '2' => 'optional'
    );


    /**
     * add / edit therapy group
     * making validation and saving in the match tables.
     * @param null $groupId - must pass when edit group
     */
    public function index($groupId = null){

        $data = array();
        $this->therapyGroupModel = $this->loadModel('therapy_groups');
        $userModel = $this->loadModel('Users');
        $users = $userModel->getAllUsers();
        $data['users'] = $users;
        $data['statuses'] = self::$statuses;
       //print_r($_POST);die;
        if(isset($_POST['save'])){

            // for new group - checking if already exist same name
            if(empty( $_POST['group_id']) && $_POST['save'] != 'save_anyway' && $this->alreadyExist($_POST)){
                $data['message'] = xlt('Failed - already has group with the same name') . '.';
                $data['savingStatus'] = 'exist';
                $data['groupData'] = $_POST;
                $this->loadView('addGroup', $data);
            }

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
            //filter and sanitize all post data.
            $data['groupData'] = filter_var_array($_POST, $filters);
            if(!$data['groupData']){
                $data['message'] = xlt('Failed to create new group') . '.';
                $data['savingStatus'] = 'failed';
            }
            else {

                if(empty( $data['groupData']['group_id'])){
                    // save new group
                    $id = $this->saveNewGroup($data['groupData']);
                    $data['groupData']['group_id'] = $id;
                    $data['message'] = xlt('New group was saved successfully') . '.';
                    $data['savingStatus'] = 'success';
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
                $this->loadView('addGroup', $data);

            } else {
                //for exist group screen
                $data['groupData'] = $this->therapyGroupModel->getGroup($groupId);
                $this->loadView('groupDetailsGeneralData', $data);
            }

        }

    }

    /**
     * check if exist group with the same name and same start date
     * @param $groupData
     * @return bool
     */
    private function alreadyExist($groupData){

        $isExistGroup = $this->therapyGroupModel->existGroup($groupData['group_name'], $groupData['group_start_date']);
        //true / false
        return $isExistGroup;
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
        $data['counselors'] = $this->prepareCounselorsList($counselors);

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
        $users_model = $this->loadModel('Users');

        //Insert groups into a new array.
        foreach ($therapy_groups as $therapy_group) {
            $gid = $therapy_group['group_id'];
            $new_array[$gid] = $therapy_group;
            $new_array[$gid]['counselors'] = array();
        }

        //Insert the counselors into their groups in new array.
        foreach ($counselors as $counselor){
            $counselor_of_group = $counselor['group_id'];
            $counselor_id = $counselor['user_id'];
            $counselor_name = $users_model->getUserNameById($counselor_id);
            array_push($new_array[$counselor_of_group]['counselors'],$counselor_name);
        }

        return $new_array;

    }

    /**
     * Returns a list of counselors without duplicates.
     * @param $counselors
     * @return array
     */
    private function prepareCounselorsList($counselors){

        $new_array = array();
        $users_model = $this->loadModel('Users');

        foreach ($counselors as $counselor){
            $counselor_id = $counselor['user_id'];
            $counselor_name = $users_model->getUserNameById($counselor_id);
            $new_array[$counselor_id] = $counselor_name;
        }

        return $new_array;
    }

    /**
     * insert a new group to therapy_group table and connect between user-counselor to group at therapy_Groups_Counselors table
     * @param $groupData
     * @return int $groupId
     */
    private function saveNewGroup($groupData){

        $counselors = !empty($groupData['counselors']) ? $groupData['counselors'] : array();
        unset($groupData['groupId'], $groupData['save'], $groupData['counselors']);

        $groupId = $this->therapyGroupModel->saveNewGroup($groupData);
        $counselors_model = $this->loadModel('Therapy_Groups_Counselors');

        foreach($counselors as $counselorId){
            $counselors_model->save($groupId, $counselorId);
        }

        return $groupId;
    }



}