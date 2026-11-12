<?php

/**
 * interface/therapy_groups/index.php routing for therapy groups
 *
 * Contains the routing for therapy groups controllers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../globals.php';
require_once __DIR__ . '/therapy_groups_controllers/therapy_groups_controller.php';
require_once __DIR__ . '/therapy_groups_controllers/participants_controller.php';

use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\TherapyGroupSessionUtil;

$request = CurrentRequest::get();
$method = $request->query->getString('method');

switch ($method) {
    case 'addGroup':
        $controller = new TherapyGroupsController($request);
        $controller->index();
        break;

    case 'listGroups':
        $controller = new TherapyGroupsController($request);
        $controller->listGroups();
        break;

    case 'groupDetails':
        if (!$request->query->has('group_id')) {
            die('Missing group ID');
        }

        $groupId = $request->query->getString('group_id') === 'from_session'
            ? TherapyGroupSessionUtil::getGroupId()
            : $request->query->getInt('group_id');
        $controller = new TherapyGroupsController($request);
        $controller->index($groupId);
        break;
    case 'groupParticipants':
        if (!$request->query->has('group_id')) {
            die('Missing group ID');
        }

        $controller = new ParticipantsController($request);
        $controller->index($request->query->getInt('group_id'));
        break;
    case 'addParticipant':
        if (!$request->query->has('group_id')) {
            die('Missing group ID');
        }

        $controller = new ParticipantsController($request);
        $controller->add($request->query->getInt('group_id'));
        break;
}
