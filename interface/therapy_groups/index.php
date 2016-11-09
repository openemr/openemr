<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 12:01
 */

require_once dirname(__FILE__) . '/../globals.php';
require_once(dirname(__FILE__) . '/../../library/sql.inc');
require_once dirname(__FILE__) . '/therapy_groups_controllers/therapy_groups_controller.php';

$method = $_GET['method'];

switch($method){
    case 'addGroup':
        $a = new TherapyGroupsController();
        $a->add();
        break;

    case 'listTherapyGroups':
        $controller = new TherapyGroupsController();
        $controller->listTherapyGroups();
        break;
}
