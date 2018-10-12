<?php
/**
 * rest_controller
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */


///////////////////////////////
// Uncomment for development //
// $ignoreAuth = true;       //
///////////////////////////////

require_once("interface/globals.php");

use OpenEMR\Services\FacilityService;
use OpenEMR\Services\VersionService;
use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Services\PatientService;
use OpenEMR\Common\Http\HttpRestRouteHandler;


// TODO: Need to handle auth and tokens here

$routes = array(
    'GET /facility' => function() {
        return (new FacilityService())->getAll();
    },
    'GET /facility/:id' => function($id) {
        return (new FacilityService())->getById($id);
    },
    'POST /facility' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        $facilityService = new FacilityService();
        $validationResult = $facilityService->validate($data);

        if (!$validationResult->isValid()) {
            http_response_code(400);
            return $validationResult->getMessages();
        }

        return $facilityService->insert($data);
    },
    'PUT /facility/:id' => function($id) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        $data['fid'] = $id;
        $facilityService = new FacilityService();
        $validationResult = $facilityService->validate($data);

        if (!$validationResult->isValid()) {
            http_response_code(400);
            return $validationResult->getMessages();
        }

        return $facilityService->update($data);
    },
    'GET /patient' => function() {
        return (new PatientService())->getAll();
    },
    'GET /patient/:pid' => function($pid) {
        $service = new PatientService();
        $service->setPid($pid);
        return $service->getOne();
    },
    'GET /patient/:pid/encounter' => function($pid) {
        $service = new PatientService();
        $service->setPid($pid);
        return $service->getEncounters();
    },
    'GET /patient/:pid/encounter/:eid' => function($pid, $eid) {
        $service = new PatientService();
        $service->setPid($pid);
        return $service->getEncounter($eid);
    },
    'GET /version' => function() {
        return (new VersionService())->fetch()->toSerializedObject();
    },
    'GET /product' => function() {
        return array("status" => (new ProductRegistrationService())->getProductStatus()->getStatusAsString());
    }
);


HttpRestRouteHandler::handle($routes, $_GET['resource'], $_SERVER['REQUEST_METHOD']);