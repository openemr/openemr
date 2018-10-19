<?php
/**
 * rest_router
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


$ignoreAuth = true;

require_once("interface/globals.php");

use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\RestControllers\VersionRestController;
use OpenEMR\RestControllers\ProductRegistrationRestController;
use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\RestControllers\EncounterRestController;
use OpenEMR\RestControllers\ProviderRestController;
use OpenEMR\RestControllers\ListRestController;
use OpenEMR\RestControllers\InsuranceCompanyRestController;
use OpenEMR\RestControllers\AppointmentRestController;
use OpenEMR\RestControllers\AuthRestController;

$authRestController = new AuthRestController();
$token = $_SERVER['HTTP_X_API_TOKEN'];

if ($_GET['resource'] !== "/api/auth") {
    if (!$authRestController->isValidToken($token)) {
        http_response_code(401);
        exit;
    } else {
        $authRestController->optionallyAddMoreTokenTime($token);
    }
}

function authorization_check($section, $value) {
    $authRestController = new AuthRestController();
    $result = $authRestController->aclCheck($_SERVER['HTTP_X_API_TOKEN'], $section, $value);

    if (!$result) {
        http_response_code(401);
        exit;
    }
}

$routes = array(
    'POST /api/auth' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new AuthRestController())->authenticate($data);
    },
    'GET /api/facility' => function() {
        authorization_check("admin", "users");
        return (new FacilityRestController())->getAll();
    },
    'GET /api/facility/:fid' => function($fid) {
        authorization_check("admin", "users");
        return (new FacilityRestController())->getOne($fid);
    },
    'POST /api/facility' => function() {
        authorization_check("admin", "super");
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new FacilityRestController())->post($data);
    },
    'PUT /api/facility/:fid' => function($fid) {
        authorization_check("admin", "super");
        $data = (array)(json_decode(file_get_contents('php://input')));
        $data['fid'] = $fid;
        return (new FacilityRestController())->put($data);
    },
    'GET /api/provider' => function() {
        authorization_check("admin", "users");
        return (new ProviderRestController())->getAll();
    },
    'GET /api/provider/:prid' => function($prid) {
        authorization_check("admin", "users");
        return (new ProviderRestController())->getOne($prid);
    },
    'GET /api/patient' => function() {
        authorization_check('patients', 'demo');
        return (new PatientRestController(null))->getAll($_GET);
    },
    'POST /api/patient' => function() {
        authorization_check('patients', 'demo');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new PatientRestController(null))->post($data);
    },
    'GET /api/patient/:pid' => function($pid) {
        authorization_check('patients', 'demo');
        return (new PatientRestController($pid))->getOne();
    },
    'GET /api/patient/:pid/encounter' => function($pid) {
        authorization_check('encounters', 'auth_a');
        return (new EncounterRestController())->getAll($pid);
    },
    'GET /api/patient/:pid/encounter/:eid' => function($pid, $eid) {
        authorization_check('encounters', 'auth_a');
        return (new EncounterRestController())->getOne($pid, $eid);
    },
    'GET /api/patient/:pid/encounter/:eid/soap_note' => function($pid, $eid) {
        authorization_check('encounters', 'notes');
        return (new EncounterRestController())->getSoapNotes($pid, $eid);
    },
    'GET /api/patient/:pid/encounter/:eid/soap_note/:sid' => function($pid, $eid, $sid) {
        authorization_check('encounters', 'notes');
        return (new EncounterRestController())->getSoapNote($pid, $eid, $sid);
    },
    'POST /api/patient/:pid/encounter/:eid/soap_note' => function($pid, $eid) {
        authorization_check('encounters', 'notes');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new EncounterRestController())->postSoapNote($pid, $eid, $data);
    },
    'PUT /api/patient/:pid/encounter/:eid/soap_note/:sid' => function($pid, $eid, $sid) {
        authorization_check('encounters', 'notes');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new EncounterRestController())->putSoapNote($pid, $eid, $sid, $data);
    },
    'GET /api/patient/:pid/medical_problem' => function($pid) {
        authorization_check('encounters', 'notes');
        return (new ListRestController())->getAll($pid, 'medical_problem');
    },
    'GET /api/patient/:pid/medical_problem/:mid' => function($pid, $mid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getOne($pid, 'medical_problem', $mid);
    },
    'POST /api/patient/:pid/medical_problem' => function($pid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'medical_problem', $data);
    },
    'PUT /api/patient/:pid/medical_problem/:aid' => function($pid, $aid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $aid, 'medical_problem', $data);
    },
    'GET /api/patient/:pid/allergy' => function($pid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getAll($pid, 'allergy');
    },
    'GET /api/patient/:pid/allergy/:aid' => function($pid, $aid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getOne($pid, 'allergy', $aid);
    },
    'POST /api/patient/:pid/allergy' => function($pid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'allergy', $data);
    },
    'PUT /api/patient/:pid/allergy/:aid' => function($pid, $aid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $aid, 'allergy', $data);
    },
    'GET /api/patient/:pid/medication' => function($pid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getAll($pid, 'medication');
    },
    'POST /api/patient/:pid/medication' => function($pid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'medication', $data);
    },
    'PUT /api/patient/:pid/medication/:mid' => function($pid, $mid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $mid, 'medication', $data);
    },
    'GET /api/patient/:pid/medication/:mid' => function($pid, $mid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getOne($pid, 'medication', $mid);
    },
    'GET /api/patient/:pid/surgery' => function($pid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getAll($pid, 'surgery');
    },
    'GET /api/patient/:pid/surgery/:sid' => function($pid, $sid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getOne($pid, 'surgery', $sid);
    },
    'POST /api/patient/:pid/surgery' => function($pid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'surgery', $data);
    },
    'PUT /api/patient/:pid/surgery/:sid' => function($pid, $sid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $sid, 'surgery', $data);
    },
    'GET /api/patient/:pid/dental_issue' => function($pid) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getAll($pid, 'dental');
    },
    'GET /api/patient/:pid/dental_issue/:did' => function($pid, $did) {
        authorization_check('patients', 'med');
        return (new ListRestController())->getOne($pid, 'dental', $did);
    },
    'POST /api/patient/:pid/dental_issue' => function($pid) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'dental', $data);
    },
    'PUT /api/patient/:pid/dental_issue/:did' => function($pid, $did) {
        authorization_check('patients', 'med');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $did, 'dental', $data);
    },
    'GET /api/patient/:pid/appointment' => function($pid) {
        authorization_check('patients', 'appt');
        return (new AppointmentRestController())->getAllForPatient($pid);
    },
    'POST /api/patient/:pid/appointment' => function($pid) {
        authorization_check('patients', 'appt');
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new AppointmentRestController())->post($pid, $data);
    },
    'GET /api/appointment' => function() {
        authorization_check('patients', 'appt');
        return (new AppointmentRestController())->getAll();
    },
    'GET /api/appointment/:eid' => function($eid) {
        authorization_check('patients', 'appt');
        return (new AppointmentRestController())->getOne($eid);
    },
    'DELETE /api/patient/:pid/appointment/:eid' => function($pid, $eid) {
        authorization_check('patients', 'appt');
        return (new AppointmentRestController())->delete($eid);
    },
    'GET /api/patient/:pid/appointment/:eid' => function($pid, $eid) {
        authorization_check('patients', 'appt');
        return (new AppointmentRestController())->getOne($eid);
    },
    'GET /api/list/:list_name' => function($list_name) {
        authorization_check('lists', 'default');
        return (new ListRestController())->getOptions($list_name);
    },
    'GET /api/version' => function() {
        return (new VersionRestController())->getOne();
    },
    'GET /api/product' => function() {
        return (new ProductRegistrationRestController())->getOne();
    },
    'GET /api/insurance_company' => function() {
        return (new InsuranceCompanyRestController())->getAll();
    }
);

HttpRestRouteHandler::handle($routes, $_GET['resource'], $_SERVER['REQUEST_METHOD']);