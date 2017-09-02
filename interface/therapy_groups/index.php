<?php
/**
 * interface/therapy_groups/index.php routing for therapy groups
 *
 * Contains the routing for therapy groups controllers.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */


require_once dirname(__FILE__) . '/../globals.php';
require_once dirname(__FILE__) . '/therapy_groups_controllers/therapy_groups_controller.php';
require_once dirname(__FILE__) . '/therapy_groups_controllers/participants_controller.php';

$method = $_GET['method'];

switch ($method) {
    case 'addGroup':
        $controller = new TherapyGroupsController();
        $controller->index();
        break;

    case 'listGroups':
        $controller = new TherapyGroupsController();
        $controller->listGroups();
        break;

    case 'groupDetails':
        if (!isset($_GET['group_id'])) {
            die('Missing group ID');
        }

        $controller = new TherapyGroupsController();
        if ($_GET['group_id'] == 'from_session') {
            $controller->index($therapy_group);
        } else {
            $controller->index($_GET['group_id']);
        }
        break;
    case 'groupParticipants':
        if (!isset($_GET['group_id'])) {
            die('Missing group ID');
        }

        $controller = new ParticipantsController();
        $controller->index($_GET['group_id']);
        break;
    case 'addParticipant':
        $controller = new ParticipantsController();
        $controller->add($_GET['group_id']);
        break;
}
