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
use OpenEMR\Common\Uuid\UuidRegistry;
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
//
RestConfig::$ROUTE_MAP = array(
    "GET /api/facility" => function () {
        RestConfig::scope_check("user", "facility", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FacilityRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::scope_check("user", "facility", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FacilityRestController())->getOne($fuuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/facility" => function () {
        RestConfig::scope_check("user", "facility", "write");
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new FacilityRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::scope_check("user", "facility", "write");
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return =  (new FacilityRestController())->patch($fuuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient" => function () {
        RestConfig::scope_check("user", "patient", "read");
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient" => function () {
        RestConfig::scope_check("user", "patient", "write");
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid" => function ($puuid) {
        RestConfig::scope_check("user", "patient", "write");
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->put($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:puuid" => function ($puuid) {
        RestConfig::scope_check("user", "patient", "read");
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getOne($puuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::scope_check("user", "encounter", "read");
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new EncounterRestController())->getAll($puuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::scope_check("user", "encounter", "write");
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::scope_check("user", "encounter", "write");
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->put($puuid, $euuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::scope_check("user", "encounter", "read");
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new EncounterRestController())->getOne($puuid, $euuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::scope_check("user", "soap_note", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getSoapNotes($pid, $eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::scope_check("user", "vital", "write");
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->postVital($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::scope_check("user", "vital", "write");
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->putVital($pid, $eid, $vid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::scope_check("user", "vital", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getVitals($pid, $eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::scope_check("user", "vital", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getVital($pid, $eid, $vid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::scope_check("user", "soap_note", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getSoapNote($pid, $eid, $sid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::scope_check("user", "soap_note", "write");
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->postSoapNote($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::scope_check("user", "soap_note", "write");
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->putSoapNote($pid, $eid, $sid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/practitioner" => function () {
        RestConfig::scope_check("user", "practitioner", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new PractitionerRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/practitioner/:prid" => function ($prid) {
        RestConfig::scope_check("user", "practitioner", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new PractitionerRestController())->getOne($prid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/practitioner" => function () {
        RestConfig::scope_check("user", "practitioner", "write");
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/practitioner/:prid" => function ($prid) {
        RestConfig::scope_check("user", "practitioner", "write");
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->patch($prid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/medical_problem" => function () {
        RestConfig::scope_check("user", "medical_problem", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/medical_problem/:muuid" => function ($muuid) {
        RestConfig::scope_check("user", "medical_problem", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getOne($muuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::scope_check("user", "medical_problem", "read");
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getAll($puuid, "medical_problem");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::scope_check("user", "medical_problem", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ConditionRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $muuid]);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::scope_check("user", "medical_problem", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::scope_check("user", "medical_problem", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->put($puuid, $muuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "DELETE /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::scope_check("user", "medical_problem", "write");
        RestConfig::authorization_check("patients", "med");
        $return = (new ConditionRestController())->delete($puuid, $muuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/allergy" => function () {
        RestConfig::scope_check("user", "allergy", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/allergy/:auuid" => function ($auuid) {
        RestConfig::scope_check("user", "allergy", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getOne($auuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::scope_check("user", "allergy", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid]);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::scope_check("user", "allergy", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $auuid]);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::scope_check("user", "allergy", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::scope_check("user", "allergy", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->put($puuid, $auuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "DELETE /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::scope_check("user", "allergy", "write");
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->delete($puuid, $auuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/medication" => function ($pid) {
        RestConfig::scope_check("user", "medication", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "medication");
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/medication" => function ($pid) {
        RestConfig::scope_check("user", "medication", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "medication", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::scope_check("user", "medication", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $mid, "medication", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::scope_check("user", "medication", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "medication", $mid);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::scope_check("user", "medication", "write");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $mid, "medication");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::scope_check("user", "surgery", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "surgery");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::scope_check("user", "surgery", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "surgery", $sid);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::scope_check("user", "surgery", "write");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $sid, "surgery");
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::scope_check("user", "surgery", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "surgery", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::scope_check("user", "surgery", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $sid, "surgery", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::scope_check("user", "dental_issue", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "dental");
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::scope_check("user", "dental_issue", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "dental", $did);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::scope_check("user", "dental_issue", "write");
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $did, "dental");
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::scope_check("user", "dental_issue", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "dental", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::scope_check("user", "dental_issue", "write");
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $did, "dental", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::scope_check("user", "appointment", "read");
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getAllForPatient($pid);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::scope_check("user", "appointment", "write");
        RestConfig::authorization_check("patients", "appt");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AppointmentRestController())->post($pid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /api/appointment" => function () {
        RestConfig::scope_check("user", "appointment", "read");
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/appointment/:eid" => function ($eid) {
        RestConfig::scope_check("user", "appointment", "read");
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "DELETE /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::scope_check("user", "appointment", "write");
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->delete($eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::scope_check("user", "appointment", "read");
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/list/:list_name" => function ($list_name) {
        RestConfig::scope_check("user", "list", "read");
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
        RestConfig::scope_check("user", "insurance_company", "read");
        $return = (new InsuranceCompanyRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/insurance_company/:iid" => function ($iid) {
        RestConfig::scope_check("user", "insurance_company", "read");
        $return = (new InsuranceCompanyRestController())->getOne($iid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/insurance_type" => function () {
        RestConfig::scope_check("user", "insurance_type", "read");
        $return = (new InsuranceCompanyRestController())->getInsuranceTypes();
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/insurance_company" => function () {
        RestConfig::scope_check("user", "insurance_company", "write");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/insurance_company/:iid" => function ($iid) {
        RestConfig::scope_check("user", "insurance_company", "write");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->put($iid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "POST /api/patient/:pid/document" => function ($pid) {
        RestConfig::scope_check("user", "document", "write");
        $return = (new DocumentRestController())->postWithPath($pid, $_GET['path'], $_FILES['document']);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/document" => function ($pid) {
        RestConfig::scope_check("user", "document", "read");
        $return = (new DocumentRestController())->getAllAtPath($pid, $_GET['path']);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/document/:did" => function ($pid, $did) {
        RestConfig::scope_check("user", "document", "read");
        $return = (new DocumentRestController())->downloadFile($pid, $did);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/insurance" => function ($pid) {
        RestConfig::scope_check("user", "insurance", "read");
        $return = (new InsuranceRestController())->getAll($pid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        RestConfig::scope_check("user", "insurance", "read");
        $return = (new InsuranceRestController())->getOne($pid, $type);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        RestConfig::scope_check("user", "insurance", "write");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->post($pid, $type, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        RestConfig::scope_check("user", "insurance", "write");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->put($pid, $type, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "POST /api/patient/:pid/message" => function ($pid) {
        RestConfig::scope_check("user", "message", "write");
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->post($pid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::scope_check("user", "message", "write");
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->put($pid, $mid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "DELETE /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::scope_check("user", "message", "write");
        RestConfig::authorization_check("patients", "notes");
        $return = (new MessageRestController())->delete($pid, $mid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/immunization" => function () {
        RestConfig::scope_check("user", "immunization", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ImmunizationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/immunization/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "immunization", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ImmunizationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/procedure" => function () {
        RestConfig::scope_check("user", "procedure", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ProcedureRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/procedure/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "procedure", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new ProcedureRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/drug" => function () {
        RestConfig::scope_check("user", "drug", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new DrugRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/drug/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "drug", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new DrugRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/prescription" => function () {
        RestConfig::scope_check("user", "prescription", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new PrescriptionRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /api/prescription/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "prescription", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new PrescriptionRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

);

use OpenEMR\RestControllers\FHIR\FhirAllergyIntoleranceRestController;
use OpenEMR\RestControllers\FHIR\FhirCareTeamRestController;
use OpenEMR\RestControllers\FHIR\FhirConditionRestController;
use OpenEMR\RestControllers\FHIR\FhirEncounterRestController;
use OpenEMR\RestControllers\FHIR\FhirObservationRestController;
use OpenEMR\RestControllers\FHIR\FhirImmunizationRestController;
use OpenEMR\RestControllers\FHIR\FhirLocationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRoleRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\RestControllers\FHIR\FhirProcedureRestController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;

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
    "POST /fhir/Patient" => function () {
        RestConfig::scope_check("user", "Patient", "write");
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /fhir/Patient/:id" => function ($id) {
        RestConfig::scope_check("user", "Patient", "write");
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->put($id, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /fhir/Patient" => function () {
        RestConfig::scope_check("user", "Patient", "read");
        RestConfig::authorization_check("patients", "demo");
        $return = (new FhirPatientRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Patient/:id" => function ($id) {
        RestConfig::scope_check("user", "Patient", "read");
        RestConfig::authorization_check("patients", "demo");
        $return = (new FhirPatientRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Encounter" => function () {
        RestConfig::scope_check("user", "Encounter", "read");
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new FhirEncounterRestController(null))->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Encounter/:id" => function ($id) {
        RestConfig::scope_check("user", "Encounter", "read");
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new FhirEncounterRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Practitioner" => function () {
        RestConfig::scope_check("user", "Practitioner", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Practitioner/:id" => function ($id) {
        RestConfig::scope_check("user", "Practitioner", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /fhir/Practitioner" => function () {
        RestConfig::scope_check("user", "Practitioner", "write");
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /fhir/Practitioner/:id" => function ($id) {
        RestConfig::scope_check("user", "Practitioner", "write");
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->patch($id, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /fhir/Organization" => function () {
        RestConfig::scope_check("user", "Organization", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirOrganizationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Organization/:id" => function ($id) {
        RestConfig::scope_check("user", "Organization", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirOrganizationRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "POST /fhir/Organization" => function () {
        RestConfig::scope_check("user", "Organization", "write");
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /fhir/Organization/:id" => function ($id) {
        RestConfig::scope_check("user", "Organization", "write");
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->patch($id, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "GET /fhir/PractitionerRole" => function () {
        RestConfig::scope_check("user", "PractitionerRole", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/PractitionerRole/:id" => function ($id) {
        RestConfig::scope_check("user", "PractitionerRole", "read");
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/AllergyIntolerance" => function () {
        RestConfig::scope_check("user", "AllergyIntolerance", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirAllergyIntoleranceRestController(null))->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/AllergyIntolerance/:id" => function ($id) {
        RestConfig::scope_check("user", "AllergyIntolerance", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirAllergyIntoleranceRestController(null))->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Observation" => function () {
        RestConfig::scope_check("user", "Observation", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirObservationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Observation/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "Observation", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirObservationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Immunization" => function () {
        RestConfig::scope_check("user", "Immunization", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirImmunizationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Immunization/:id" => function ($id) {
        RestConfig::scope_check("user", "Immunization", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirImmunizationRestController())->getOne($id);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Condition" => function () {
        RestConfig::scope_check("user", "Condition", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirConditionRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Condition/:id" => function ($uuid) {
        RestConfig::scope_check("user", "Condition", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirConditionRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Procedure" => function () {
        RestConfig::scope_check("user", "Procedure", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirProcedureRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Procedure/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "Procedure", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirProcedureRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/MedicationRequest" => function () {
        RestConfig::scope_check("user", "MedicationRequest", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRequestRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/MedicationRequest/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "MedicationRequest", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRequestRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Medication" => function () {
        RestConfig::scope_check("user", "Medication", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Medication/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "Medication", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Location" => function () {
        RestConfig::scope_check("user", "Location", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirLocationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/Location/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "Location", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirLocationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/CareTeam" => function () {
        RestConfig::scope_check("user", "CareTeam", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirCareTeamRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /fhir/CareTeam/:uuid" => function ($uuid) {
        RestConfig::scope_check("user", "CareTeam", "read");
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirCareTeamRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    }
);

// Patient portal api routes
RestConfig::$PORTAL_ROUTE_MAP = array(
    "GET /portal/patient" => function () {
        RestConfig::scope_check("patient", "patient", "read");
        $return = (new PatientRestController())->getOne(UuidRegistry::uuidToString($_SESSION['puuid']));
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /portal/patient/encounter" => function () {
        RestConfig::scope_check("patient", "encounter", "read");
        $return = (new EncounterRestController())->getAll(UuidRegistry::uuidToString($_SESSION['puuid']));
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /portal/patient/encounter/:euuid" => function ($euuid) {
        RestConfig::scope_check("patient", "encounter", "read");
        $return = (new EncounterRestController())->getOne(UuidRegistry::uuidToString($_SESSION['puuid']), $euuid);
        RestConfig::apiLog($return);
        return $return;
    }
);

// Patient portal fhir api routes
RestConfig::$PORTAL_FHIR_ROUTE_MAP = array(
    "GET /portalfhir/Patient" => function () {
        RestConfig::scope_check("patient", "Patient", "read");
        $return = (new FhirPatientRestController())->getOne(UuidRegistry::uuidToString($_SESSION['puuid']));
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /portalfhir/Encounter" => function () {
        RestConfig::scope_check("patient", "Encounter", "read");
        $return = (new FhirEncounterRestController(null))->getAll(['patient' => UuidRegistry::uuidToString($_SESSION['puuid'])]);
        RestConfig::apiLog($return);
        return $return;
    },
    "GET /portalfhir/Encounter/:id" => function ($id) {
        RestConfig::scope_check("patient", "Encounter", "read");
        $return = (new FhirEncounterRestController(null))->getAll(['_id' => $id, 'patient' => UuidRegistry::uuidToString($_SESSION['puuid'])]);
        RestConfig::apiLog($return);
        return $return;
    }
);
