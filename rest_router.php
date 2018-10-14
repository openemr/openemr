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

// TODO: Need to handle auth and tokens here

$routes = array(
    'GET /facility' => function() {
        return (new FacilityRestController())->getAll();
    },
    'GET /facility/:fid' => function($fid) {
        return (new FacilityRestController())->getOne($fid);
    },
    'POST /facility' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new FacilityRestController())->post($data);
    },
    'PUT /facility/:fid' => function($fid) {
        $data = (array)(json_decode(file_get_contents('php://input')));
        $data['fid'] = $fid;
        return (new FacilityRestController())->put($data);
    },
    'GET /provider' => function() {
        return (new ProviderRestController())->getAll();
    },
    'GET /provider/:prid' => function($prid) {
        return (new ProviderRestController())->getOne($prid);
    },
    'POST /provider' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new ProviderRestController())->post($data);
    },
    'GET /patient' => function() {
        return (new PatientRestController(null))->getAll();
    },
    'POST /patient' => function() {
        $data = (array)(json_decode(file_get_contents('php://input')));
        return (new PatientRestController(null))->post($data);
    },
    'GET /patient/:pid' => function($pid) {
        return (new PatientRestController($pid))->getOne();
    },
    'GET /patient/:pid/encounter' => function($pid) {
        return (new EncounterRestController())->getAll($pid);
    },
    'GET /patient/:pid/medical_problem' => function($pid) {
        return (new ListRestController())->getAll($pid, 'medical_problem');
    },
    'GET /patient/:pid/medical_problem/:mid' => function($pid, $mid) {
        return (new ListRestController())->getOne($pid, 'medical_problem', $mid);
    },
    'GET /patient/:pid/allergy' => function($pid) {
        return (new ListRestController())->getAll($pid, 'allergy');
    },
    'GET /patient/:pid/allergy/:aid' => function($pid, $aid) {
        return (new ListRestController())->getOne($pid, 'allergy', $aid);
    },
    'GET /patient/:pid/medication' => function($pid) {
        return (new ListRestController())->getAll($pid, 'medication');
    },
    'GET /patient/:pid/medication/:mid' => function($pid, $mid) {
        return (new ListRestController())->getOne($pid, 'medication', $mid);
    },
    'GET /patient/:pid/surgery' => function($pid) {
        return (new ListRestController())->getAll($pid, 'surgery');
    },
    'GET /patient/:pid/surgery/:sid' => function($pid, $sid) {
        return (new ListRestController())->getOne($pid, 'surgery', $sid);
    },
    'GET /patient/:pid/dental_issue' => function($pid) {
        return (new ListRestController())->getAll($pid, 'dental');
    },
    'GET /patient/:pid/dental_issue/:did' => function($pid, $did) {
        return (new ListRestController())->getOne($pid, 'dental', $did);
    },
    'GET /patient/:pid/encounter/:eid' => function($pid, $eid) {
        return (new EncounterRestController())->getOne($pid, $eid);
    },
    'GET /version' => function() {
        return (new VersionRestController())->getOne();
    },
    'GET /product' => function() {
        return (new ProductRegistrationRestController())->getOne();
    }
);

HttpRestRouteHandler::handle($routes, $_GET['resource'], $_SERVER['REQUEST_METHOD']);