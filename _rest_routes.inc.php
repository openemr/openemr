<?php

/**
 * Routes
 * (All REST routes)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Lets keep our controller classes with the routes.
//
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\AllergyIntoleranceRestController;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\RestControllers\VersionRestController;
use OpenEMR\RestControllers\ProductRegistrationRestController;
use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\RestControllers\EncounterRestController;
use OpenEMR\RestControllers\PractitionerRestController;
use OpenEMR\RestControllers\ListRestController;
use OpenEMR\RestControllers\InsuranceCompanyRestController;
use OpenEMR\RestControllers\AppointmentRestController;
use OpenEMR\RestControllers\ConditionRestController;
use OpenEMR\RestControllers\ONoteRestController;
use OpenEMR\RestControllers\DocumentRestController;
use OpenEMR\RestControllers\DrugRestController;
use OpenEMR\RestControllers\ImmunizationRestController;
use OpenEMR\RestControllers\InsuranceRestController;
use OpenEMR\RestControllers\MessageRestController;
use OpenEMR\RestControllers\PrescriptionRestController;
use OpenEMR\RestControllers\ProcedureRestController;

// Note some Http clients may not send auth as json so a function
// is implemented to determine and parse encoding on auth route's.

// Note that the api route is only for users role
//  (there is a mechanism in place to ensure only user role can access the api route)
RestConfig::$ROUTE_MAP = array(
    "GET /api/facility" => function () {
        RestConfig::authorization_check("admin", "users");
        $return = (new FacilityRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FacilityRestController())->getOne($fuuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/facility" => function () {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new FacilityRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return =  (new FacilityRestController())->patch($fuuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->put($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getOne($puuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new EncounterRestController())->getAll($puuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->put($puuid, $euuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new EncounterRestController())->getOne($puuid, $euuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getSoapNotes($pid, $eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->postVital($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->putVital($pid, $eid, $vid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getVitals($pid, $eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getVital($pid, $eid, $vid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getSoapNote($pid, $eid, $sid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->postSoapNote($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->putSoapNote($pid, $eid, $sid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        $return = (new PractitionerRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/practitioner/:prid" => function ($prid) {
        RestConfig::authorization_check("admin", "users");
        $return = (new PractitionerRestController())->getOne($prid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/practitioner/:prid" => function ($prid) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->patch($prid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/medical_problem" => function () {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/medical_problem/:muuid" => function ($muuid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getOne($muuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getAll($puuid, "medical_problem");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ConditionRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $muuid]);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->put($puuid, $muuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "DELETE /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ConditionRestController())->delete($puuid, $muuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/allergy" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/allergy/:auuid" => function ($auuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getOne($auuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid]);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $auuid]);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->put($puuid, $auuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "DELETE /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->delete($puuid, $auuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/medication" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "medication");
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/medication" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "medication", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $mid, "medication", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "medication", $mid);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $mid, "medication");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "surgery");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "surgery", $sid);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $sid, "surgery");
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "surgery", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $sid, "surgery", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "dental");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "dental", $did);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $did, "dental");
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "dental", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $did, "dental", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getAllForPatient($pid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::authorization_check("patients", "appt");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AppointmentRestController())->post($pid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/appointment" => function () {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/appointment/:eid" => function ($eid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->delete($eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/list/:list_name" => function ($list_name) {
        RestConfig::authorization_check("lists", "default");
        $return = (new ListRestController())->getOptions($list_name);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/version" => function () {
        $return = (new VersionRestController())->getOne();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/product" => function () {
        $return = (new ProductRegistrationRestController())->getOne();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/insurance_company" => function () {
        $return = (new InsuranceCompanyRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/insurance_company/:iid" => function ($iid) {
        $return = (new InsuranceCompanyRestController())->getOne($iid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/insurance_type" => function () {
        $return = (new InsuranceCompanyRestController())->getInsuranceTypes();
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/insurance_company" => function () {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/insurance_company/:iid" => function ($iid) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->put($iid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "POST /api/patient/:pid/document" => function ($pid) {
        $return = (new DocumentRestController())->postWithPath($pid, $_GET['path'], $_FILES['document']);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/document" => function ($pid) {
        $return = (new DocumentRestController())->getAllAtPath($pid, $_GET['path']);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/document/:did" => function ($pid, $did) {
        $return = (new DocumentRestController())->downloadFile($pid, $did);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/insurance" => function ($pid) {
        $return = (new InsuranceRestController())->getAll($pid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $return = (new InsuranceRestController())->getOne($pid, $type);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->post($pid, $type, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->put($pid, $type, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "POST /api/patient/:pid/message" => function ($pid) {
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->post($pid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->put($pid, $mid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "DELETE /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "notes");
        $return = (new MessageRestController())->delete($pid, $mid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/immunization" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new ImmunizationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/immunization/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ImmunizationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/procedure" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new ProcedureRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/procedure/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ProcedureRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/drug" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new DrugRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/drug/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new DrugRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/prescription" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new PrescriptionRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/prescription/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new PrescriptionRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    }
);

use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\RestControllers\FHIR\FhirAllergyIntoleranceRestController;
use OpenEMR\RestControllers\FHIR\FhirCareTeamRestController;
use OpenEMR\RestControllers\FHIR\FhirConditionRestController;
use OpenEMR\RestControllers\FHIR\FhirCoverageRestController;
use OpenEMR\RestControllers\FHIR\FhirEncounterRestController;
use OpenEMR\RestControllers\FHIR\FhirExportRestController;
use OpenEMR\RestControllers\FHIR\FhirObservationRestController;
use OpenEMR\RestControllers\FHIR\FhirImmunizationRestController;
use OpenEMR\RestControllers\FHIR\FhirLocationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\FHIR\FhirPersonRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRoleRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\RestControllers\FHIR\FhirProcedureRestController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;

// Note that the fhir route includes both user role and patient role
//  (there is a mechanism in place to ensure patient role is binded
//   to only see the data of the one patient)
RestConfig::$FHIR_ROUTE_MAP = array(
    "GET /fhir/metadata" => function () {
        $return = (new FhirMetaDataRestController())->getMetaData();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/.well-known/smart-configuration" => function () {
        $authController = new \OpenEMR\RestControllers\AuthorizationController();
        $return = (new \OpenEMR\RestControllers\SMART\SMARTConfigurationController($authController))->getConfig();
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /fhir/Patient" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /fhir/Patient/:id" => function ($id, HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->put($id, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /fhir/Patient" => function (HttpRestRequest $request) {
        $params = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            //  Note in Patient context still have to return a bundle even if it is just one resource. (ie.
            //   need to use getAll rather than getOne)
            $params['_id'] = $request->getPatientUUIDString();
            $return = (new FhirPatientRestController())->getAll($params, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "demo");
            $return = (new FhirPatientRestController())->getAll($params);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    // we have to have the bulk fhir export operation here otherwise it will match $export to the patient $id
    'GET /fhir/Patient/$export' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processExport(
            $_GET,
            'Patient',
            $request->getHeader('Accept'),
            $request->getHeader('Prefer')
        );
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Patient/:id" => function ($id, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            if (empty($id) || ($id != $request->getPatientUUIDString())) {
                throw new AccessDeniedException("patients", "demo", "patient id invalid");
            }
            $id = $request->getPatientUUIDString();
        } else {
            RestConfig::authorization_check("patients", "demo");
        }
        $return = (new FhirPatientRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Encounter" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirEncounterRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("encounters", "auth_a");
            $return = (new FhirEncounterRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Encounter/:id" => function ($id, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirEncounterRestController())->getOne($id, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("encounters", "auth_a");
            $return = (new FhirEncounterRestController())->getOne($id);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Practitioner" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Practitioner/:id" => function ($id, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /fhir/Practitioner" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /fhir/Practitioner/:id" => function ($id, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->patch($id, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /fhir/Organization" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirOrganizationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Organization/:id" => function ($id, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirOrganizationRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /fhir/Organization" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /fhir/Organization/:id" => function ($id, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->patch($id, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /fhir/PractitionerRole" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/PractitionerRole/:id" => function ($id, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/AllergyIntolerance" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirAllergyIntoleranceRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/AllergyIntolerance/:id" => function ($id, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController())->getOne($id, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirAllergyIntoleranceRestController())->getOne($id);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Observation" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirObservationRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirObservationRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Observation/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirObservationRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirObservationRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Immunization" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirImmunizationRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirImmunizationRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Immunization/:id" => function ($id, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirImmunizationRestController())->getOne($id, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirImmunizationRestController())->getOne($id);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Condition" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirConditionRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirConditionRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Condition/:id" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirConditionRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirConditionRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Procedure" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProcedureRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirProcedureRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Procedure/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProcedureRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirProcedureRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/MedicationRequest" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRequestRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirMedicationRequestRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/MedicationRequest/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRequestRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirMedicationRequestRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Medication" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Medication/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Location" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirLocationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Location/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirLocationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/CareTeam" => function (HttpRestRequest $request) {
        $getParams = $_GET;
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCareTeamRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCareTeamRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/CareTeam/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCareTeamRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCareTeamRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Coverage" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $return = (new FhirCoverageRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Coverage/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $return = (new FhirCoverageRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Person" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPersonRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Person/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPersonRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    // Bulk FHIR api endpoints
    'GET /fhir/Document/:id/Binary' => function ($documentId, HttpRestRequest $request) {
        // currently only allow users with the same permissions as export to take a file out
        // this could be relaxed to allow other types of files ie such as patient access etc.
        RestConfig::authorization_check("admin", "users");

        // Grab the document id
        $docController = new \OpenEMR\RestControllers\FHIR\FhirDocumentRestController($request);
        $response = $docController->downloadDocument($documentId, $request->getRequestUserId());
        return $response;
    },
    'GET /fhir/Group/:id/$export' => function ($groupId, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirExportRestController($request);
        $exportParams = $_GET;
        $exportParams['groupId'] = $groupId;
        $return = $fhirExportService->processExport(
            $exportParams,
            'Group',
            $request->getHeader('Accept'),
            $request->getHeader('Prefer')
        );
        RestConfig::apiLog($return);
        return $return;
    },
    'GET /fhir/$export' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processExport(
            $_GET,
            'System',
            $request->getHeader('Accept'),
            $request->getHeader('Prefer')
        );
        RestConfig::apiLog($return);
        return $return;
    },
    // these two operations are adopted based on the documentation used in the IBM FHIR Server
    // we'd reference cerner or epic but we couldn't find any documentation about those (Jan 30th 2021)
    // @see https://ibm.github.io/FHIR/guides/FHIRBulkOperations/
    'GET /fhir/$bulkdata-status' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $jobUuidString = $_GET['job'];
        // if we were truly async we would return 202 here to say we are in progress with a JSON response
        // since OpenEMR data is so small we just return the JSON from the database
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processExportStatusRequestForJob($jobUuidString);
        RestConfig::apiLog($return);
        return $return;
    },
    'DELETE /fhir/$bulkdata-status' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $job = $_GET['job'];
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processDeleteExportForJob($job);
        RestConfig::apiLog($return);
        return $return;
    }
);

// Note that the portal (api) route is only for patient role
//  (there is a mechanism in place to ensure only patient role can access the portal (api) route)
RestConfig::$PORTAL_ROUTE_MAP = array(
    "GET /portal/patient" => function (HttpRestRequest $request) {
        $return = (new PatientRestController())->getOne($request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /portal/patient/encounter" => function (HttpRestRequest $request) {
        $return = (new EncounterRestController())->getAll($request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /portal/patient/encounter/:euuid" => function ($euuid, HttpRestRequest $request) {
        $return = (new EncounterRestController())->getOne($request->getPatientUUIDString(), $euuid);
        RestConfig::apiLog($return);
        return $return;
    }
);
