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
use OpenEMR\RestControllers\AuthRestController;
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
    "POST /api/auth" => function () {
        $data = (array) RestConfig::getPostData((file_get_contents("php://input")));
        return (new AuthRestController())->authenticate($data);
    },
    "GET /api/facility" => function () {
        RestConfig::authorization_check("admin", "users");
        return (new FacilityRestController())->getAll($_GET);
    },
    "GET /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "users");
        return (new FacilityRestController())->getOne($fuuid);
    },
    "POST /api/facility" => function () {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new FacilityRestController())->post($data);
    },
    "PATCH /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new FacilityRestController())->patch($fuuid, $data);
    },
    "GET /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        return (new PatientRestController())->getAll($_GET);
    },
    "POST /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new PatientRestController())->post($data);
    },
    "PUT /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new PatientRestController())->put($puuid, $data);
    },
    "GET /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        return (new PatientRestController())->getOne($puuid);
    },
    "GET /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        return (new EncounterRestController())->getAll($puuid);
    },
    "POST /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new EncounterRestController())->post($puuid, $data);
    },
    "PUT /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new EncounterRestController())->put($puuid, $euuid, $data);
    },
    "GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        return (new EncounterRestController())->getOne($puuid, $euuid);
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        return (new EncounterRestController())->getSoapNotes($pid, $eid);
    },
    "POST /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new EncounterRestController())->postVital($pid, $eid, $data);
    },
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new EncounterRestController())->putVital($pid, $eid, $vid, $data);
    },
    "GET /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        return (new EncounterRestController())->getVitals($pid, $eid);
    },
    "GET /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        return (new EncounterRestController())->getVital($pid, $eid, $vid);
    },
    "GET /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::authorization_check("encounters", "notes");
        return (new EncounterRestController())->getSoapNote($pid, $eid, $sid);
    },
    "POST /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new EncounterRestController())->postSoapNote($pid, $eid, $data);
    },
    "PUT /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new EncounterRestController())->putSoapNote($pid, $eid, $sid, $data);
    },
    "GET /api/practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        return (new PractitionerRestController())->getAll($_GET);
    },
    "GET /api/practitioner/:prid" => function ($prid) {
        RestConfig::authorization_check("admin", "users");
        return (new PractitionerRestController())->getOne($prid);
    },
    "POST /api/practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new PractitionerRestController())->post($data);
    },
    "PATCH /api/practitioner/:prid" => function ($prid) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new PractitionerRestController())->patch($prid, $data);
    },
    "GET /api/medical_problem" => function () {
        RestConfig::authorization_check("encounters", "notes");
        return (new ConditionRestController())->getAll();
    },
    "GET /api/medical_problem/:muuid" => function ($muuid) {
        RestConfig::authorization_check("encounters", "notes");
        return (new ConditionRestController())->getOne($muuid);
    },
    "GET /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::authorization_check("encounters", "notes");
        return (new ConditionRestController())->getAll($puuid, "medical_problem");
    },
    "GET /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        return (new ConditionRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $muuid]);
    },
    "POST /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ConditionRestController())->post($puuid, $data);
    },
    "PUT /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ConditionRestController())->put($puuid, $muuid, $data);
    },
    "DELETE /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        return (new ConditionRestController())->delete($puuid, $muuid);
    },
    "GET /api/allergy" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new AllergyIntoleranceRestController())->getAll();
    },
    "GET /api/allergy/:auuid" => function ($auuid) {
        RestConfig::authorization_check("patients", "med");
        return (new AllergyIntoleranceRestController())->getOne($auuid);
    },
    "GET /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        return (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid]);
    },
    "GET /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        return (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $auuid]);
    },
    "POST /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new AllergyIntoleranceRestController())->post($puuid, $data);
    },
    "PUT /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new AllergyIntoleranceRestController())->put($puuid, $auuid, $data);
    },
    "DELETE /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        return (new AllergyIntoleranceRestController())->delete($puuid, $auuid);
    },
    "GET /api/patient/:pid/medication" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->getAll($pid, "medication");
    },
    "POST /api/patient/:pid/medication" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ListRestController())->post($pid, "medication", $data);
    },
    "PUT /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ListRestController())->put($pid, $mid, "medication", $data);
    },
    "GET /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->getOne($pid, "medication", $mid);
    },
    "DELETE /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->delete($pid, $mid, "medication");
    },
    "GET /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->getAll($pid, "surgery");
    },
    "GET /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->getOne($pid, "surgery", $sid);
    },
    "DELETE /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->delete($pid, $sid, "surgery");
    },
    "POST /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ListRestController())->post($pid, "surgery", $data);
    },
    "PUT /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ListRestController())->put($pid, $sid, "surgery", $data);
    },
    "GET /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->getAll($pid, "dental");
    },
    "GET /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->getOne($pid, "dental", $did);
    },
    "DELETE /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        return (new ListRestController())->delete($pid, $did, "dental");
    },
    "POST /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ListRestController())->post($pid, "dental", $data);
    },
    "PUT /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new ListRestController())->put($pid, $did, "dental", $data);
    },
    "GET /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::authorization_check("patients", "appt");
        return (new AppointmentRestController())->getAllForPatient($pid);
    },
    "POST /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::authorization_check("patients", "appt");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new AppointmentRestController())->post($pid, $data);
    },
    "GET /api/appointment" => function () {
        RestConfig::authorization_check("patients", "appt");
        return (new AppointmentRestController())->getAll();
    },
    "GET /api/appointment/:eid" => function ($eid) {
        RestConfig::authorization_check("patients", "appt");
        return (new AppointmentRestController())->getOne($eid);
    },
    "DELETE /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::authorization_check("patients", "appt");
        return (new AppointmentRestController())->delete($eid);
    },
    "GET /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::authorization_check("patients", "appt");
        return (new AppointmentRestController())->getOne($eid);
    },
    "GET /api/list/:list_name" => function ($list_name) {
        RestConfig::authorization_check("lists", "default");
        return (new ListRestController())->getOptions($list_name);
    },
    "GET /api/version" => function () {
        return (new VersionRestController())->getOne();
    },
    "GET /api/product" => function () {
        return (new ProductRegistrationRestController())->getOne();
    },
    "GET /api/insurance_company" => function () {
        return (new InsuranceCompanyRestController())->getAll();
    },
    "GET /api/insurance_type" => function () {
        return (new InsuranceCompanyRestController())->getInsuranceTypes();
    },
    "POST /api/insurance_company" => function () {
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new InsuranceCompanyRestController())->post($data);
    },
    "PUT /api/insurance_company/:iid" => function ($iid) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new InsuranceCompanyRestController())->put($iid, $data);
    },
    "POST /api/patient/:pid/document" => function ($pid) {
        return (new DocumentRestController())->postWithPath($pid, $_GET['path'], $_FILES['document']);
    },
    "GET /api/patient/:pid/document" => function ($pid) {
        return (new DocumentRestController())->getAllAtPath($pid, $_GET['path']);
    },
    "GET /api/patient/:pid/document/:did" => function ($pid, $did) {
        return (new DocumentRestController())->downloadFile($pid, $did);
    },
    "GET /api/patient/:pid/insurance" => function ($pid) {
        return (new InsuranceRestController())->getAll($pid);
    },
    "GET /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        return (new InsuranceRestController())->getOne($pid, $type);
    },
    "POST /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new InsuranceRestController())->post($pid, $type, $data);
    },
    "PUT /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new InsuranceRestController())->put($pid, $type, $data);
    },
    "POST /api/patient/:pid/message" => function ($pid) {
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new MessageRestController())->post($pid, $data);
    },
    "PUT /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        return (new MessageRestController())->put($pid, $mid, $data);
    },
    "DELETE /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "notes");
        return (new MessageRestController())->delete($pid, $mid);
    },
    "GET /api/immunization" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new ImmunizationRestController())->getAll($_GET);
    },
    "GET /api/immunization/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new ImmunizationRestController())->getOne($uuid);
    },
    "GET /api/procedure" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new ProcedureRestController())->getAll();
    },
    "GET /api/procedure/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new ProcedureRestController())->getOne($uuid);
    },
    "GET /api/drug" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new DrugRestController())->getAll();
    },
    "GET /api/drug/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new DrugRestController())->getOne($uuid);
    },
    "GET /api/prescription" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new PrescriptionRestController())->getAll();
    },
    "GET /api/prescription/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new PrescriptionRestController())->getOne($uuid);
    },

);

use OpenEMR\RestControllers\FHIR\FhirAllergyIntoleranceRestController;
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
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireResponseController;

RestConfig::$FHIR_ROUTE_MAP = array(
    "POST /fhir/auth" => function () {
        $data = (array) RestConfig::getPostData((file_get_contents("php://input")));
        return (new AuthRestController())->authenticate($data);
    },
    "POST /fhir/Patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirPatientRestController())->post($data);
    },
    "PUT /fhir/Patient/:id" => function ($id) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirPatientRestController())->put($id, $data);
    },
    "PATCH /fhir/Patient/:id" => function ($id) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirPatientRestController())->put($id, $data);
    },
    "GET /fhir/Patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        return (new FhirPatientRestController())->getAll($_GET);
    },
    "GET /fhir/Patient/:id" => function ($id) {
        RestConfig::authorization_check("patients", "demo");
        return (new FhirPatientRestController())->getOne($id);
    },
    "GET /fhir/Encounter" => function () {
        RestConfig::authorization_check("encounters", "auth_a");
        return (new FhirEncounterRestController(null))->getAll($_GET);
    },
    "GET /fhir/Encounter/:id" => function ($id) {
        RestConfig::authorization_check("encounters", "auth_a");
        return (new FhirEncounterRestController())->getOne($id);
    },
    "GET /fhir/Practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        return (new FhirPractitionerRestController())->getAll($_GET);
    },
    "GET /fhir/Practitioner/:id" => function ($id) {
        RestConfig::authorization_check("admin", "users");
        return (new FhirPractitionerRestController())->getOne($id);
    },
    "POST /fhir/Practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirPractitionerRestController())->post($data);
    },
    "PATCH /fhir/Practitioner/:id" => function ($id) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirPractitionerRestController())->patch($id, $data);
    },
    "GET /fhir/Organization" => function () {
        RestConfig::authorization_check("admin", "users");
        return (new FhirOrganizationRestController())->getAll($_GET);
    },
    "GET /fhir/Organization/:id" => function ($id) {
        RestConfig::authorization_check("admin", "users");
        return (new FhirOrganizationRestController())->getOne($id);
    },
    "POST /fhir/Organization" => function () {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirOrganizationRestController())->post($data);
    },
    "PATCH /fhir/Organization/:id" => function ($id) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirOrganizationRestController())->patch($id, $data);
    },
    "GET /fhir/PractitionerRole" => function () {
        RestConfig::authorization_check("admin", "users");
        return (new FhirPractitionerRoleRestController())->getAll($_GET);
    },
    "GET /fhir/PractitionerRole/:id" => function ($id) {
        RestConfig::authorization_check("admin", "users");
        return (new FhirPractitionerRoleRestController())->getOne($id);
    },
    "GET /fhir/AllergyIntolerance" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirAllergyIntoleranceRestController(null))->getAll($_GET);
    },
    "GET /fhir/AllergyIntolerance/:id" => function ($id) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirAllergyIntoleranceRestController(null))->getOne($id);
    },
    "GET /fhir/Observation" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirObservationRestController())->getAll($_GET);
    },
    "GET /fhir/Observation/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirObservationRestController())->getOne($uuid);
    },
    "POST /fhir/QuestionnaireResponse" => function () {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        return (new FhirQuestionnaireResponseController(null))->post($data);
    },
    "GET /fhir/Immunization" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirImmunizationRestController())->getAll($_GET);
    },
    "GET /fhir/Immunization/:id" => function ($id) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirImmunizationRestController())->getOne($id);
    },
    "GET /fhir/Condition" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirConditionRestController())->getAll($_GET);
    },
    "GET /fhir/Condition/:id" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirConditionRestController())->getOne($uuid);
    },
    "GET /fhir/Procedure" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirProcedureRestController())->getAll($_GET);
    },
    "GET /fhir/Procedure/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirProcedureRestController())->getOne($uuid);
    },
    "GET /fhir/MedicationRequest" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirMedicationRequestRestController())->getAll($_GET);
    },
    "GET /fhir/MedicationRequest/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirMedicationRequestRestController())->getOne($uuid);
    },
    "GET /fhir/Medication" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirMedicationRestController())->getAll($_GET);
    },
    "GET /fhir/Medication/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirMedicationRestController())->getOne($uuid);
    },
    "GET /fhir/Location" => function () {
        RestConfig::authorization_check("patients", "med");
        return (new FhirLocationRestController())->getAll($_GET);
    },
    "GET /fhir/Location/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        return (new FhirLocationRestController())->getOne($uuid);
    }
);

// Patient portal api routes
RestConfig::$PORTAL_ROUTE_MAP = array(
    "POST /portal/auth" => function () {
        $data = (array) RestConfig::getPostData((file_get_contents("php://input")));
        return (new AuthRestController())->authenticate($data);
    },
    "GET /portal/patient" => function () {
        return (new PatientRestController())->getOne(UuidRegistry::uuidToString($_SESSION['puuid']));
    },
    "GET /portal/patient/encounter" => function () {
        return (new EncounterRestController())->getAll(UuidRegistry::uuidToString($_SESSION['puuid']));
    },
    "GET /portal/patient/encounter/:euuid" => function ($euuid) {
        return (new EncounterRestController())->getOne(UuidRegistry::uuidToString($_SESSION['puuid']), $euuid);
    }
);

// Patient portal fhir api routes
RestConfig::$PORTAL_FHIR_ROUTE_MAP = array(
    "POST /portalfhir/auth" => function () {
        $data = (array) RestConfig::getPostData((file_get_contents("php://input")));
        return (new AuthRestController())->authenticate($data);
    },
    "GET /portalfhir/Patient" => function () {
        return (new FhirPatientRestController())->getOne(UuidRegistry::uuidToString($_SESSION['puuid']));
    },
    "GET /portalfhir/Encounter" => function () {
        return (new FhirEncounterRestController(null))->getAll(['patient' => UuidRegistry::uuidToString($_SESSION['puuid'])]);
    },
    "GET /portalfhir/Encounter/:id" => function ($id) {
        return (new FhirEncounterRestController(null))->getAll(['_id' => $id, 'patient' => UuidRegistry::uuidToString($_SESSION['puuid'])]);
    }
);
