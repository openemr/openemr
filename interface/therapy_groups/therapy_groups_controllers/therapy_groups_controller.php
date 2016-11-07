<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 12:16
 */
require dirname(__FILE__) . '/base_controller.php';

class TherapyGroupsController extends BaseController{

    public function  add(){

        $groupModel = $this->loadModel('Group');
        $data = array('form' => 'yyy');
        $this->loadView('addGroup', $data);

    }


}