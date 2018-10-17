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


///////////////////////////////
// Uncomment for development //
$ignoreAuth = true;       //
///////////////////////////////

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

// TODO: Need to handle auth and tokens here

$routes = array(
    'GET /api/facility' => function() {
        return (new FacilityRestController())->getAll();
    },
    'GET /api/facility/:fid' => function($fid) {
        return (new FacilityRestController())->getOne($fid);
    },
    'POST /api/facility' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new FacilityRestController())->post($data);
    },
    'PUT /api/facility/:fid' => function($fid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        $data['fid'] = $fid;
        return (new FacilityRestController())->put($data);
    },
    'GET /api/provider' => function() {
        return (new ProviderRestController())->getAll();
    },
    'GET /api/provider/:prid' => function($prid) {
        return (new ProviderRestController())->getOne($prid);
    },
    'GET /api/patient' => function() {
        return (new PatientRestController(null))->getAll($_GET);
    },
    'POST /api/patient' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new PatientRestController(null))->post($data);
    },
    'GET /api/patient/:pid' => function($pid) {
        return (new PatientRestController($pid))->getOne();
    },
    'GET /api/patient/:pid/encounter' => function($pid) {
        return (new EncounterRestController())->getAll($pid);
    },
    'GET /api/patient/:pid/encounter/:eid' => function($pid, $eid) {
        return (new EncounterRestController())->getOne($pid, $eid);
    },
    'GET /api/patient/:pid/encounter/:eid/soap_note' => function($pid, $eid) {
        return (new EncounterRestController())->getSoapNotes($pid, $eid);
    },
    'GET /api/patient/:pid/encounter/:eid/soap_note/:sid' => function($pid, $eid, $sid) {
        return (new EncounterRestController())->getSoapNote($pid, $eid, $sid);
    },
    'POST /api/patient/:pid/encounter/:eid/soap_note' => function($pid, $eid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new EncounterRestController())->postSoapNote($pid, $eid, $data);
    },
    'PUT /api/patient/:pid/encounter/:eid/soap_note/:sid' => function($pid, $eid, $sid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new EncounterRestController())->putSoapNote($pid, $eid, $sid, $data);
    },
    'GET /api/patient/:pid/medical_problem' => function($pid) {
        return (new ListRestController())->getAll($pid, 'medical_problem');
    },
    'GET /api/patient/:pid/medical_problem/:mid' => function($pid, $mid) {
        return (new ListRestController())->getOne($pid, 'medical_problem', $mid);
    },
    'POST /api/patient/:pid/medical_problem' => function($pid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'medical_problem', $data);
    },
    'PUT /api/patient/:pid/medical_problem/:aid' => function($pid, $aid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $aid, 'medical_problem', $data);
    },
    'GET /api/patient/:pid/allergy' => function($pid) {
        return (new ListRestController())->getAll($pid, 'allergy');
    },
    'GET /api/patient/:pid/allergy/:aid' => function($pid, $aid) {
        return (new ListRestController())->getOne($pid, 'allergy', $aid);
    },
    'POST /api/patient/:pid/allergy' => function($pid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'allergy', $data);
    },
    'PUT /api/patient/:pid/allergy/:aid' => function($pid, $aid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $aid, 'allergy', $data);
    },
    'GET /api/patient/:pid/medication' => function($pid) {
        return (new ListRestController())->getAll($pid, 'medication');
    },
    'POST /api/patient/:pid/medication' => function($pid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'medication', $data);
    },
    'PUT /api/patient/:pid/medication/:mid' => function($pid, $mid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $mid, 'medication', $data);
    },
    'GET /api/patient/:pid/medication/:mid' => function($pid, $mid) {
        return (new ListRestController())->getOne($pid, 'medication', $mid);
    },
    'GET /api/patient/:pid/surgery' => function($pid) {
        return (new ListRestController())->getAll($pid, 'surgery');
    },
    'GET /api/patient/:pid/surgery/:sid' => function($pid, $sid) {
        return (new ListRestController())->getOne($pid, 'surgery', $sid);
    },
    'POST /api/patient/:pid/surgery' => function($pid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'surgery', $data);
    },
    'PUT /api/patient/:pid/surgery/:sid' => function($pid, $sid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $sid, 'surgery', $data);
    },
    'GET /api/patient/:pid/dental_issue' => function($pid) {
        return (new ListRestController())->getAll($pid, 'dental');
    },
    'GET /api/patient/:pid/dental_issue/:did' => function($pid, $did) {
        return (new ListRestController())->getOne($pid, 'dental', $did);
    },
    'POST /api/patient/:pid/dental_issue' => function($pid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->post($pid, 'dental', $data);
    },
    'PUT /api/patient/:pid/dental_issue/:did' => function($pid, $did) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ListRestController())->put($pid, $did, 'dental', $data);
    },
    'GET /api/list/:list_name' => function($list_name) {
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