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
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * @OA\Info(title="OpenEMR API", version="6.1.0")
 * @OA\Server(url="/apis/default/")
 * @OA\SecurityScheme(
 *   securityScheme="openemr_auth",
 *   type="oauth2",
 *   @OA\Flow(
 *      authorizationUrl="/oauth2/default/authorize",
 *      tokenUrl="/oauth2/default/token",
 *      flow="authorizationCode",
 *      scopes={
 *         "openid": "Generic mandatory scope",
 *         "offline_access": "Will signal server to provide a refresh token",
 *         "api:fhir": "FHIR R4 API",
 *         "patient/AllergyIntolerance.read": "Read allergy intolerance resources for the current patient (api:fhir)",
 *         "patient/CarePlan.read": "Read care plan resources for the current patient (api:fhir)",
 *         "patient/CareTeam.read": "Read care team resources for the current patient (api:fhir)",
 *         "patient/Condition.read": "Read condition resources for the current patient (api:fhir)",
 *         "patient/Device.read": "Read device resources for the current patient (api:fhir)",
 *         "patient/DiagnosticReport.read": "Read diagnostic report resources for the current patient (api:fhir)",
 *         "patient/DocumentReference.read": "Read document reference resources for the current patient (api:fhir)",
 *         "patient/Encounter.read": "Read encounter resources for the current patient (api:fhir)",
 *         "patient/Goal.read": "Read goal resources for the current patient (api:fhir)",
 *         "patient/Immunization.read": "Read immunization resources for the current patient (api:fhir)",
 *         "patient/Location.read": "Read location resources for the current patient (api:fhir)",
 *         "patient/Medication.read": "Read medication resources for the current patient (api:fhir)",
 *         "patient/MedicationRequest.read": "Read medication request resources for the current patient (api:fhir)",
 *         "patient/Observation.read": "Read observation resources for the current patient (api:fhir)",
 *         "patient/Organization.read": "Read organization resources for the current patient (api:fhir)",
 *         "patient/Patient.read": "Read patient resource for the current patient (api:fhir)",
 *         "patient/Person.read": "Read person resources for the current patient (api:fhir)",
 *         "patient/Practitioner.read": "Read practitioner resources for the current patient (api:fhir)",
 *         "patient/Procedure.read": "Read procedure resources for the current patient (api:fhir)",
 *         "patient/Provenance.read": "Read provenance resources for the current patient (api:fhir)",
 *         "system/AllergyIntolerance.read": "Read all allergy intolerance resources in the system (api:fhir)",
 *         "system/CarePlan.read": "Read all care plan resources in the system (api:fhir)",
 *         "system/CareTeam.read": "Read all care team resources in the system (api:fhir)",
 *         "system/Condition.read": "Read all condition resources in the system (api:fhir)",
 *         "system/Coverage.read": "Read all coverage resources in the system (api:fhir)",
 *         "system/Device.read": "Read all device resources in the system (api:fhir)",
 *         "system/DiagnosticReport.read": "Read all diagnostic report resources in the system (api:fhir)",
 *         "system/Document.read": "Read all document resources in the system (api:fhir)",
 *         "system/DocumentReference.read": "Read all document reference resources in the system (api:fhir)",
 *         "system/Encounter.read": "Read all encounter resources in the system (api:fhir)",
 *         "system/Goal.read": "Read all goal resources in the system (api:fhir)",
 *         "system/Group.read": "Read all group resources in the system (api:fhir)",
 *         "system/Immunization.read": "Read all immunization resources in the system (api:fhir)",
 *         "system/Location.read": "Read all location resources in the system (api:fhir)",
 *         "system/Medication.read": "Read all medication resources in the system (api:fhir)",
 *         "system/MedicationRequest.read": "Read all medication request resources in the system (api:fhir)",
 *         "system/Observation.read": "Read all observation resources in the system (api:fhir)",
 *         "system/Organization.read": "Read all organization resources in the system (api:fhir)",
 *         "system/Patient.read": "Read all patient resources in the system (api:fhir)",
 *         "system/Person.read": "Read all person resources in the system (api:fhir)",
 *         "system/Practitioner.read": "Read all practitioner resources in the system (api:fhir)",
 *         "system/PractitionerRole.read": "Read all practitioner role resources in the system (api:fhir)",
 *         "system/Procedure.read": "Read all procedure resources in the system (api:fhir)",
 *         "system/Provenance.read": "Read all provenance resources in the system (api:fhir)",
 *         "user/AllergyIntolerance.read": "Read all allergy intolerance resources the user has access to (api:fhir)",
 *         "user/CarePlan.read": "Read all care plan resources the user has access to (api:fhir)",
 *         "user/CareTeam.read": "Read all care team resources the user has access to (api:fhir)",
 *         "user/Condition.read": "Read all condition resources the user has access to (api:fhir)",
 *         "user/Coverage.read": "Read all coverage resources the user has access to (api:fhir)",
 *         "user/Device.read": "Read all device resources the user has access to (api:fhir)",
 *         "user/DiagnosticReport.read": "Read all diagnostic report resources the user has access to (api:fhir)",
 *         "user/DocumentReference.read": "Read all document reference resources the user has access to (api:fhir)",
 *         "user/Encounter.read": "Read all encounter resources the user has access to (api:fhir)",
 *         "user/Goal.read": "Read all goal resources the user has access to (api:fhir)",
 *         "user/Immunization.read": "Read all immunization resources the user has access to (api:fhir)",
 *         "user/Location.read": "Read all location resources the user has access to (api:fhir)",
 *         "user/Medication.read": "Read all medication resources the user has access to (api:fhir)",
 *         "user/MedicationRequest.read": "Read all medication request resources the user has access to (api:fhir)",
 *         "user/Observation.read": "Read all observation resources the user has access to (api:fhir)",
 *         "user/Organization.read": "Read all organization resources the user has access to (api:fhir)",
 *         "user/Organization.write": "Write all organization resources the user has access to (api:fhir)",
 *         "user/Patient.read": "Read all patient resources the user has access to (api:fhir)",
 *         "user/Patient.write": "Write all patient resources the user has access to (api:fhir)",
 *         "user/Person.read": "Read all person resources the user has access to (api:fhir)",
 *         "user/Practitioner.read": "Read all practitioner resources the user has access to (api:fhir)",
 *         "user/Practitioner.write": "Write all practitioner resources the user has access to (api:fhir)",
 *         "user/PractitionerRole.read": "Read all practitioner role resources the user has access to (api:fhir)",
 *         "user/Procedure.read": "Read all procedure resources the user has access to (api:fhir)",
 *         "user/Provenance.read": "Read all provenance resources the user has access to (api:fhir)",
 *         "api:oemr": "Standard OpenEMR API",
 *         "user/allergy.read": "Read allergies the user has access to (api:oemr)",
 *         "user/allergy.write": "Write allergies the user has access to for (api:oemr)",
 *         "user/appointment.read": "Read appointments the user has access to (api:oemr)",
 *         "user/appointment.write": "Write appointments the user has access to for (api:oemr)",
 *         "user/dental_issue.read": "Read dental issues the user has access to (api:oemr)",
 *         "user/dental_issue.write": "Write dental issues the user has access to (api:oemr)",
 *         "user/document.read": "Read documents the user has access to (api:oemr)",
 *         "user/document.write": "Write documents the user has access to (api:oemr)",
 *         "user/drug.read": "Read drugs the user has access to (api:oemr)",
 *         "user/encounter.read": "Read encounters the user has access to (api:oemr)",
 *         "user/encounter.write": "Write encounters the user has access to (api:oemr)",
 *         "user/facility.read": "Read facilities the user has access to (api:oemr)",
 *         "user/facility.write": "Write facilities the user has access to (api:oemr)",
 *         "user/immunization.read": "Read immunizations the user has access to (api:oemr)",
 *         "user/insurance.read": "Read insurances the user has access to (api:oemr)",
 *         "user/insurance.write": "Write insurances the user has access to (api:oemr)",
 *         "user/insurance_company.read": "Read insurance companies the user has access to (api:oemr)",
 *         "user/insurance_company.write": "Write insurance companies the user has access to (api:oemr)",
 *         "user/insurance_type.read": "Read insurance types the user has access to (api:oemr)",
 *         "user/list.read": "Read lists the user has access to (api:oemr)",
 *         "user/medical_problem.read": "Read medical problems the user has access to (api:oemr)",
 *         "user/medical_problem.write": "Write medical problems the user has access to (api:oemr)",
 *         "user/medication.read": "Read medications the user has access to (api:oemr)",
 *         "user/medication.write": "Write medications the user has access to (api:oemr)",
 *         "user/message.write": "Read messages the user has access to (api:oemr)",
 *         "user/patient.read": "Read patients the user has access to (api:oemr)",
 *         "user/patient.write": "Write patients the user has access to (api:oemr)",
 *         "user/practitioner.read": "Read practitioners the user has access to (api:oemr)",
 *         "user/practitioner.write": "Write practitioners the user has access to (api:oemr)",
 *         "user/prescription.read": "Read prescriptions the user has access to (api:oemr)",
 *         "user/procedure.read": "Read procedures the user has access to (api:oemr)",
 *         "user/soap_note.read": "Read soap notes the user has access to (api:oemr)",
 *         "user/soap_note.write": "Write soap notes the user has access to (api:oemr)",
 *         "user/surgery.read": "Read surgeries the user has access to (api:oemr)",
 *         "user/surgery.write": "Write surgeries the user has access to (api:oemr)",
 *         "user/vital.read": "Read vitals the user has access to (api:oemr)",
 *         "user/vital.write": "Write vitals the user has access to (api:oemr)",
 *         "api:port": "Standard Patient Portal OpenEMR API",
 *         "patient/encounter.read": "Read encounters the patient has access to (api:port)",
 *         "patient/patient.read": "Write encounters the patient has access to (api:port)"
 *      }
 *   )
 * )
 * @OA\Tag(
 *   name="fhir",
 *   description="FHIR R4 API"
 * )
 * @OA\Tag(
 *   name="standard",
 *   description="Standard OpenEMR API"
 * )
 * @OA\Tag(
 *   name="standard-patient",
 *   description="Standard Patient Portal OpenEMR API"
 * )
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
    /**
     * @OA\Get(
     *     path="/api/facility",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Returns a list of facilities"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
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
    /**
     * @OA\Post(
     *     path="/api/facility",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Creates a facility in the system"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "POST /api/facility" => function () {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new FacilityRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    /**
     * @OA\Put(
     *     path="/api/facility",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Updates a facility in the system"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "PUT /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return =  (new FacilityRestController())->patch($fuuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/api/patient",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Retrieves a list of patients"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Post(
     *     path="/api/patient",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Creates a new patient"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "POST /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    /**
     * @OA\Put(
     *     path="/api/patient",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Updates a new patient"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "PUT /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->put($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/api/patient/:puuid",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Retrieves a single patient by their uuid"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getOne($puuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/api/patient/:puuid/encounter",
     *     tags={"standard"},
     *     @OA\Response(
     *      response="200",
     *      description="Retrieves a list of encounters for a single patient"
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
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
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController())->postVital($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
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
        $return = (new ConditionRestController())->getAll(['puuid' => $puuid, 'condition_uuid' => $muuid]);
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
use OpenEMR\RestControllers\FHIR\FhirCarePlanRestController;
use OpenEMR\RestControllers\FHIR\FhirCareTeamRestController;
use OpenEMR\RestControllers\FHIR\FhirConditionRestController;
use OpenEMR\RestControllers\FHIR\FhirCoverageRestController;
use OpenEMR\RestControllers\FHIR\FhirDeviceRestController;
use OpenEMR\RestControllers\FHIR\FhirDiagnosticReportRestController;
use OpenEMR\RestControllers\FHIR\FhirDocumentReferenceRestController;
use OpenEMR\RestControllers\FHIR\FhirEncounterRestController;
use OpenEMR\RestControllers\FHIR\FhirExportRestController;
use OpenEMR\RestControllers\FHIR\FhirObservationRestController;
use OpenEMR\RestControllers\FHIR\FhirImmunizationRestController;
use OpenEMR\RestControllers\FHIR\FhirGoalRestController;
use OpenEMR\RestControllers\FHIR\FhirGroupRestController;
use OpenEMR\RestControllers\FHIR\FhirLocationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\FHIR\FhirPersonRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRoleRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\RestControllers\FHIR\FhirProcedureRestController;
use OpenEMR\RestControllers\FHIR\FhirProvenanceRestController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;

// Note that the fhir route includes both user role and patient role
//  (there is a mechanism in place to ensure patient role is binded
//   to only see the data of the one patient)
RestConfig::$FHIR_ROUTE_MAP = array(
    /**
     * @OA\Get(
     *     path="/fhir/AllergyIntolerance",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200",
     *      description="Returns a list of AllergyIntolerance resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/AllergyIntolerance" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController($request))->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/AllergyIntolerance/{uuid}",
     *     tags={"fhir"},
     *     @OA\Parameter(
     *      name="uuid",
     *      in="path",
     *      description="The uuid for the AllergyIntolerance resource.",
     *      required=true
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Returns a single AllergyIntolerance resource."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/AllergyIntolerance/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/CarePlan",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of CarePlan resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/CarePlan" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCarePlanRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCarePlanRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/CarePlan/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single CarePlan resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the CarePlan resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/CarePlan/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCarePlanRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCarePlanRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/CareTeam",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of CareTeam resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/CareTeam" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/CareTeam/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single CareTeam resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the CareTeam resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/fhir/Condition",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Condition resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Condition" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/Condition/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Condition resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Condition resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Condition/:uuid" => function ($uuid, HttpRestRequest $request) {
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

    /**
     * @OA\Get(
     *     path="/fhir/Coverage",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Coverage resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Coverage" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $return = (new FhirCoverageRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Coverage/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Coverage resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Coverage resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Coverage/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $return = (new FhirCoverageRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Device",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Device resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Device" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDeviceRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDeviceRestController())->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Device/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Device resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Device resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Device/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDeviceRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDeviceRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/DiagnosticReport",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of DiagnosticReport resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/DiagnosticReport" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDiagnosticReportRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDiagnosticReportRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/DiagnosticReport/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single DiagnosticReport resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the DiagnosticReport resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/DiagnosticReport/:uuid" => function ($uuid, HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDiagnosticReportRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDiagnosticReportRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/DocumentReference",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of DocumentReference resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    'GET /fhir/DocumentReference' => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDocumentReferenceRestController($request))->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDocumentReferenceRestController($request))->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/DocumentReference/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single DocumentReference resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the DocumentReference resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/DocumentReference/:uuid" => function ($uuid, HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDocumentReferenceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDocumentReferenceRestController($request))->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * TODO
     */
    'GET /fhir/Document/:id/Binary' => function ($documentId, HttpRestRequest $request) {
        // currently only allow users with the same permissions as export to take a file out
        // this could be relaxed to allow other types of files ie such as patient access etc.
        RestConfig::authorization_check("admin", "users");

        // Grab the document id
        $docController = new \OpenEMR\RestControllers\FHIR\FhirDocumentRestController($request);
        $response = $docController->downloadDocument($documentId, $request->getRequestUserId());
        return $response;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Encounter",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Encounter resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Encounter" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/Encounter/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Encounter resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Encounter resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Encounter/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirEncounterRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirEncounterRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Goal",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Condition resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Goal" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGoalRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirGoalRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Goal/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Goal resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Goal resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Goal/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGoalRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirGoalRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Group",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Group resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    'GET /fhir/Group' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGroupRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            $return = (new FhirGroupRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Group/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Group resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Group resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Group/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGroupRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            $return = (new FhirGroupRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * TODO
     */
    'GET /fhir/Group/:id/$export' => function ($groupId, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirExportRestController($request);
        $exportParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/Immunization",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Immunization resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Immunization" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/Immunization/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Immunization resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Immunization resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Immunization/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirImmunizationRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirImmunizationRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Location",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Location resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Location" => function (HttpRestRequest $request) {
        $return = (new FhirLocationRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Location/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Location resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Location resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Location/:uuid" => function ($uuid, HttpRestRequest $request) {
        $return = (new FhirLocationRestController())->getOne($uuid, $request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Medication",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Medication resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Medication" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Medication/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Medication resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Medication resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Medication/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/MedicationRequest",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of MedicationRequest resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/MedicationRequest" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/MedicationRequest/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single MedicationRequest resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the MedicationRequest resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/fhir/Observation",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Observation resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Observation" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
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

    /**
     * @OA\Get(
     *     path="/fhir/Observation/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Observation resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Observation resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/fhir/Organization",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Organization resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Organization" => function (HttpRestRequest $request) {
        if (!$request->isPatientRequest()) {
            RestConfig::authorization_check("admin", "users");
        }
        $return = (new FhirOrganizationRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Organization/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Organization resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Organization resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Organization/:uuid" => function ($uuid, HttpRestRequest $request) {
        $patientUUID = null;
        if (!$request->isPatientRequest()) {
            RestConfig::authorization_check("admin", "users");
        } else {
            $patientUUID = $request->getPatientUUIDString();
        }
        $return = (new FhirOrganizationRestController())->getOne($uuid, $patientUUID);

        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Post(
     *     path="/fhir/Organization",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Adds a Organization resource."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "POST /fhir/Organization" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Put(
     *     path="/fhir/Organization/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Organization resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="Modifies a organization resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "PUT /fhir/Organization/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->patch($uuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Post(
     *     path="/fhir/Patient",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Adds a Patient resource."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "POST /fhir/Patient" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Put(
     *     path="/fhir/Patient/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Modifies a Patient resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Patient resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "PUT /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->put($uuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Patient",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Patient resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Patient" => function (HttpRestRequest $request) {
        $params = $request->getQueryParams();
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

    /**
     * TODO
     */
    // we have to have the bulk fhir export operation here otherwise it will match $export to the patient $id
    'GET /fhir/Patient/$export' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processExport(
            $request->getQueryParams(),
            'Patient',
            $request->getHeader('Accept'),
            $request->getHeader('Prefer')
        );
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Patient/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Patient resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Patient resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            if (empty($id) || ($id != $request->getPatientUUIDString())) {
                throw new AccessDeniedException("patients", "demo", "patient id invalid");
            }
            $id = $request->getPatientUUIDString();
        } else {
            RestConfig::authorization_check("patients", "demo");
        }
        $return = (new FhirPatientRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Person",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Person resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Person" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPersonRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Person/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Person resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Person resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Person/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPersonRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Practitioner",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Practitioner resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Practitioner" => function (HttpRestRequest $request) {

        // TODO: @adunsulag talk with brady.miller about patients needing access to any practitioner resource
        // that is referenced in connected patient resources -- such as AllergyIntollerance.
        // I don't believe patients are assigned to a particular practitioner
        // should we allow just open api access to admin information?  Should we restrict particular pieces
        // of data in the practitioner side (phone number, address information) based on a permission set?
        if (!$request->isPatientRequest()) {
            RestConfig::authorization_check("admin", "users");
        }
        $return = (new FhirPractitionerRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Practitioner/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Practitioner resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Practitioner resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Practitioner/:uuid" => function ($uuid, HttpRestRequest $request) {
        // TODO: @adunsulag talk with brady.miller about patients needing access to any practitioner resource
        // that is referenced in connected patient resources -- such as AllergyIntollerance.
        // I don't believe patients are assigned to a particular practitioner
        // should we allow just open api access to admin information?  Should we restrict particular pieces
        // of data in the practitioner side (phone number, address information) based on a permission set?
        if (!$request->isPatientRequest()) {
            RestConfig::authorization_check("admin", "users");
        }
        $return = (new FhirPractitionerRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Post(
     *     path="/fhir/Practitioner",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Adds a Practitioner resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "POST /fhir/Practitioner" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Put(
     *     path="/fhir/Practitioner/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Modify a Practitioner resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Practitioner resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "PUT /fhir/Practitioner/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->patch($uuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/PractitionerRole",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of PractitionerRole resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/PractitionerRole" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/PractitionerRole/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single PractitionerRole resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the PractitionerRole resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/PractitionerRole/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Procedure",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Procedure resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Procedure" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProcedureRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirProcedureRestController())->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Procedure/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Procedure resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Procedure resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/fhir/Provenance/{uuid}",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a single Provenance resource."
     *     ),
     *     @OA\Parameter(
     *      name="uuid"
     *      ,in="path"
     *      ,description="The uuid for the Provenance resource."
     *      ,required=true
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /fhir/Provenance/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProvenanceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirProvenanceRestController($request))->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/fhir/Provenance",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a list of Provenance resources."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    // NOTE: this GET request only supports requests with an _id parameter.  FHIR inferno test tool requires the 'search'
    // property to support which is why this endpoint exists.
    "GET /fhir/Provenance" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProvenanceRestController($request))->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            // TODO: it seems like regular users should be able to grab authorship / provenance information
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirProvenanceRestController($request))->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
        return $return;
    },

    // other endpoints

    /**
     * @OA\Get(
     *     path="/fhir/metadata",
     *     tags={"fhir"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns metadata of the fhir server."
     *     )
     * )
     */
    "GET /fhir/metadata" => function () {
        $return = (new FhirMetaDataRestController())->getMetaData();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * TODO
     */
    "GET /fhir/.well-known/smart-configuration" => function () {
        $authController = new \OpenEMR\RestControllers\AuthorizationController();
        $return = (new \OpenEMR\RestControllers\SMART\SMARTConfigurationController($authController))->getConfig();
        RestConfig::apiLog($return);
        return $return;
    },

    // FHIR root level operations

    /**
     * TODO
     */
    'GET /fhir/$export' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processExport(
            $request->getQueryParams(),
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

    /**
     * TODO
     */
    'GET /fhir/$bulkdata-status' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $jobUuidString = $request->getQueryParam('job');
        // if we were truly async we would return 202 here to say we are in progress with a JSON response
        // since OpenEMR data is so small we just return the JSON from the database
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processExportStatusRequestForJob($jobUuidString);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * TODO
     */
    'DELETE /fhir/$bulkdata-status' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $job = $request->getQueryParam('job');
        $fhirExportService = new FhirExportRestController($request);
        $return = $fhirExportService->processDeleteExportForJob($job);
        RestConfig::apiLog($return);
        return $return;
    }
);

// Note that the portal (api) route is only for patient role
//  (there is a mechanism in place to ensure only patient role can access the portal (api) route)
RestConfig::$PORTAL_ROUTE_MAP = array(
    /**
     * @OA\Get(
     *     path="/portal/patient",
     *     tags={"standard-patient"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns the patient."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /portal/patient" => function (HttpRestRequest $request) {
        $return = (new PatientRestController())->getOne($request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/portal/patient/encounter",
     *     tags={"standard-patient"},
     *     @OA\Response(
     *      response="200"
     *      , description="Returns encounters for the patient."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /portal/patient/encounter" => function (HttpRestRequest $request) {
        $return = (new EncounterRestController())->getAll($request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * @OA\Get(
     *     path="/portal/patient/encounter/{euuid}",
     *     tags={"standard-patient"},
     *     @OA\Parameter(
     *      name="euuid"
     *      ,in="path"
     *      ,description="The uuid for the encounter."
     *      ,required=true
     *     ),
     *     @OA\Response(
     *      response="200"
     *      , description="Returns a selected encounter by its uuid."
     *     ),
     *     security={{"openemr_auth":{}}}
     * )
     */
    "GET /portal/patient/encounter/:euuid" => function ($euuid, HttpRestRequest $request) {
        $return = (new EncounterRestController())->getOne($request->getPatientUUIDString(), $euuid);
        RestConfig::apiLog($return);
        return $return;
    }
);
