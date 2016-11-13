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
require_once dirname(__FILE__) . '/therapy_groups_controllers/participants_controller.php';

$method = $_GET['method'];

switch($method){
    case 'addGroup':
        $controller = new TherapyGroupsController();
        $controller->index();
        break;

    case 'listGroups':
        $controller = new TherapyGroupsController();
        $controller->listGroups();
        break;

    case 'groupDetails':
        if(!isset($_GET['group_id'])){
            die('Missing group ID');
        }
        $controller = new TherapyGroupsController();
        $controller->index($_GET['group_id']);
        break;
    case 'groupParticipants':
        $controller = new ParticipantsController();
        $controller->index();
        break;
}
