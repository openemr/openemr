<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 12:01
 */

require dirname(__FILE__) . '/therapy_groups_controllers/therapy_groups_controller.php';

$method = $_GET['method'];

switch($method){
    case 'add':
        $a = new TherapyGroupsController();
        $a->add();
        break;

}
