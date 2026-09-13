<?php

/**
 * FHIR API Routes
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Config\RestConfig;
use OpenEMR\RestControllers\FHIR\FhirAllergyIntoleranceRestController;
use OpenEMR\RestControllers\FHIR\FhirAppointmentRestController;
use OpenEMR\RestControllers\FHIR\FhirCarePlanRestController;
use OpenEMR\RestControllers\FHIR\FhirCareTeamRestController;
use OpenEMR\RestControllers\FHIR\FhirCoverageRestController;
use OpenEMR\RestControllers\FHIR\FhirDeviceRestController;
use OpenEMR\RestControllers\FHIR\FhirDiagnosticReportRestController;
use OpenEMR\RestControllers\FHIR\FhirDocumentReferenceRestController;
use OpenEMR\RestControllers\FHIR\FhirEncounterRestController;
use OpenEMR\RestControllers\FHIR\FhirGenericRestController;
use OpenEMR\RestControllers\FHIR\FhirGoalRestController;
use OpenEMR\RestControllers\FHIR\FhirGroupRestController;
use OpenEMR\RestControllers\FHIR\FhirImmunizationRestController;
use OpenEMR\RestControllers\FHIR\FhirLocationRestController;
use OpenEMR\RestControllers\FHIR\FhirMediaRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationDispenseRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRestController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\FHIR\FhirPersonRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRoleRestController;
use OpenEMR\RestControllers\FHIR\FhirProcedureRestController;
use OpenEMR\RestControllers\FHIR\FhirProvenanceRestController;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireResponseRestController;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireRestController;
use OpenEMR\RestControllers\FHIR\FhirServiceRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirSpecimenRestController;
use OpenEMR\RestControllers\FHIR\FhirValueSetRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationDefinitionRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationDocRefRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationExportRestController;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\RestControllers\SMART\SMARTConfigurationController;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Services\FHIR\FhirAppointmentService;
use OpenEMR\Services\FHIR\FhirCarePlanService;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirCoverageService;
use OpenEMR\Services\FHIR\FhirDeviceService;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\FHIR\FhirGoalService;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use OpenEMR\Services\FHIR\FhirMedicationService;
use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\Services\FHIR\FhirPersonService;
use OpenEMR\Services\FHIR\FhirPractitionerRoleService;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\FhirQuestionnaireResponseService;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Services\FHIR\Questionnaire\FhirQuestionnaireFormService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// Note that the fhir route includes both user role and patient role
//  (there is a mechanism in place to ensure patient role is binded
//   to only see the data of the one patient)
return [
    "GET /fhir/AllergyIntolerance" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController($request))->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/AllergyIntolerance/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/AllergyIntolerance" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirAllergyIntoleranceService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("AllergyIntolerance");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/AllergyIntolerance/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirAllergyIntoleranceService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("AllergyIntolerance");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "POST /fhir/Appointment" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirAppointmentService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("Appointment");
        $controller->addAclRestrictions("patients", "appt");
        return $controller->post($data);
    },

    "GET /fhir/Appointment" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAppointmentRestController($request))->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "appt");
            $return = (new FhirAppointmentRestController($request))->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Appointment/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAppointmentRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "appt");
            $return = (new FhirAppointmentRestController($request))->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/CarePlan" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCarePlanRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirCarePlanRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/CarePlan/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCarePlanRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirCarePlanRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/CarePlan" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirCarePlanService(), $globalsBag);
        $controller->setExpectedResourceType("CarePlan");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/CarePlan/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirCarePlanService(), $globalsBag);
        $controller->setExpectedResourceType("CarePlan");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/CareTeam" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $getParams = $request->getQueryParams();
        $restController = new FhirCareTeamRestController();
        $restController->setOEGlobals($globalsBag);
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = $restController->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = $restController->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/CareTeam/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCareTeamRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirCareTeamRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/CareTeam" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirCareTeamService(), $globalsBag);
        $controller->setExpectedResourceType("CareTeam");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/CareTeam/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirCareTeamService(), $globalsBag);
        $controller->setExpectedResourceType("CareTeam");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Condition" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getAll();
    },
    "GET /fhir/Condition/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getOne($uuid);
    },

    "POST /fhir/Condition" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->setExpectedResourceType("Condition");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/Condition/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->setExpectedResourceType("Condition");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Coverage" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCoverageRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirCoverageRestController())->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/Coverage/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCoverageRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirCoverageRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/Coverage" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirCoverageService(), $globalsBag);
        $controller->setExpectedResourceType("Coverage");
        $controller->addAclRestrictions("admin", "super");
        return $controller->post($data);
    },

    "PUT /fhir/Coverage/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirCoverageService(), $globalsBag);
        $controller->setExpectedResourceType("Coverage");
        $controller->addAclRestrictions("admin", "super");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Device" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDeviceRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirDeviceRestController())->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/Device/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDeviceRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirDeviceRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/Device" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirDeviceService(), $globalsBag);
        $controller->setExpectedResourceType("Device");
        $controller->addAclRestrictions("admin", "super");
        return $controller->post($data);
    },

    "PUT /fhir/Device/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirDeviceService(), $globalsBag);
        $controller->setExpectedResourceType("Device");
        $controller->addAclRestrictions("admin", "super");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/DiagnosticReport" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        $controller = new FhirDiagnosticReportRestController($request);
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = $controller->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = $controller->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/DiagnosticReport/:uuid" => function (string $uuid, HttpRestRequest $request) {
        $controller = new FhirDiagnosticReportRestController($request);
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = $controller->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = $controller->getOne($uuid);
        }

        return $return;
    },
    'GET /fhir/DocumentReference' => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDocumentReferenceRestController($request))->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirDocumentReferenceRestController($request))->getAll($getParams);
        }

        return $return;
    },
    'POST /fhir/DocumentReference/$docref' => function (HttpRestRequest $request) {

        // NOTE: The order of this route is IMPORTANT as it needs to come before the DocumentReference single request.
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirOperationDocRefRestController($request))->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            // Non-patient callers hit `$docref` in system/administrative
            // context (bulk export precursor, aggregate authorship /
            // provenance queries). Mirror the sibling GET /fhir/DocumentReference
            // (:247-258) which requires `admin/super` so the ACL requirement
            // matches the other administrative DocumentReference operations.
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirOperationDocRefRestController($request))->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/DocumentReference/:uuid" => function (string $uuid, HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDocumentReferenceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirDocumentReferenceRestController($request))->getOne($uuid);
        }

        return $return;
    },
    'GET /fhir/Binary/:id' => function ($documentId, HttpRestRequest $request) {
        $docController = new \OpenEMR\RestControllers\FHIR\FhirDocumentRestController($request);

        if ($request->isPatientRequest()) {
            $response = $docController->downloadDocument($documentId, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "users");
            $response = $docController->downloadDocument($documentId);
        }

        return $response;
    },
    "GET /fhir/Encounter" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirEncounterRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "encounters", "auth_a");
            $return = (new FhirEncounterRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Encounter/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirEncounterRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirEncounterRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/Encounter" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirEncounterService(), $globalsBag);
        $controller->setExpectedResourceType("Encounter");
        $controller->addAclRestrictions("encounters", "auth_a");
        return $controller->post($data);
    },

    "PUT /fhir/Encounter/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirEncounterService(), $globalsBag);
        $controller->setExpectedResourceType("Encounter");
        $controller->addAclRestrictions("encounters", "auth_a");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Goal" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGoalRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirGoalRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Goal/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGoalRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirGoalRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/Goal" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirGoalService(), $globalsBag);
        $controller->setExpectedResourceType("Goal");
        $controller->addAclRestrictions("admin", "super");
        return $controller->post($data);
    },

    "PUT /fhir/Goal/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirGoalService(), $globalsBag);
        $controller->setExpectedResourceType("Goal");
        $controller->addAclRestrictions("admin", "super");
        return $controller->put($uuid, $data);
    },

    'GET /fhir/Group' => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGroupRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            $return = (new FhirGroupRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Group/:uuid" => function (string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGroupRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            $return = (new FhirGroupRestController())->getOne($uuid);
        }

        return $return;
    },
    'GET /fhir/Group/:id/$export' => function ($groupId, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $exportParams = $request->getQueryParams();
        $exportParams['groupId'] = $groupId;
        $return = $fhirExportService->processExport(
            $exportParams,
            'Group',
            $request->getHeader('Accept')[0] ?? '',
            $request->getHeader('Prefer')[0] ?? ''
        );

        return $return;
    },

    // Group writes are not implemented. OpenEMR's FHIR Group is a virtual/computed
    // aggregation of patients by provider; there is no persistent group table to
    // write to. POST/PUT return 405 with a FHIR OperationOutcome.
    "POST /fhir/Group" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'Group',
        'FHIR Group is a computed aggregation in OpenEMR (e.g. patients-by-provider); it has no persistent storage and cannot be written directly.'
    ),
    "PUT /fhir/Group/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'Group',
        'FHIR Group is a computed aggregation in OpenEMR (e.g. patients-by-provider); it has no persistent storage and cannot be written directly.'
    ),

    "GET /fhir/Immunization" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirImmunizationRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirImmunizationRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Immunization/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirImmunizationRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirImmunizationRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/Immunization" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirImmunizationService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("Immunization");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/Immunization/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirImmunizationService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("Immunization");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Location" => function (HttpRestRequest $request) {
        $return = (new FhirLocationRestController($request))->getAll($request->getQueryParams(), $request->getPatientUUIDString());

        return $return;
    },
    "GET /fhir/Location/:uuid" => function (string $uuid, HttpRestRequest $request) {
        $return = (new FhirLocationRestController($request))->getOne($uuid, $request->getPatientUUIDString());

        return $return;
    },

    // Location writes are deliberately not implemented. In OpenEMR, Location is a virtual
    // projection over patient_data, users, and facility — there is no single underlying
    // entity to write to. A POST or PUT here would either need to discriminate the target
    // table based on identifier conventions (fragile and unspecified by FHIR) or duplicate
    // patient/user/facility write paths. Both options are out of scope for this PR.
    // See PR #11507 follow-up tracking.
    "POST /fhir/Location" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'Location',
        'FHIR Location is a virtual projection over patient_data/users/facility in OpenEMR; writes are not supported. Create the underlying Patient, Practitioner, or Organization instead.'
    ),
    "PUT /fhir/Location/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'Location',
        'FHIR Location is a virtual projection over patient_data/users/facility in OpenEMR; writes are not supported. Update the underlying Patient, Practitioner, or Organization instead.'
    ),

    "GET /fhir/Media" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        $controller = new FhirMediaRestController($request);
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = $controller->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
            $return = $controller->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Media/:uuid" => function (string $uuid, HttpRestRequest $request) {
        // Mirror the sibling `GET /fhir/Media` list route (:413-424) so the
        // non-patient (user-scope / core-session) branch runs an explicit
        // ACL check and the patient-scope branch explicitly binds the
        // caller's puuid. Without this branching, non-patient callers hit
        // the endpoint with `$request->getPatientUUIDString()` returning
        // `null` and the compartment filter has nothing to constrain on.
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMediaRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
            // Non-patient callers already passed the ACL gate above; pass
            // null puuid so the service does not attempt to bind a
            // compartment that does not exist for this caller shape.
            $return = (new FhirMediaRestController($request))->getOne($uuid, null);
        }
        return $return;
    },
    "GET /fhir/Medication" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new FhirMedicationRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/Medication/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRestController())->getOne($uuid);
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/Medication" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        // Medication writes touch the global `drugs` master table — the UI gates
        // add/edit on `admin/drugs`, so the FHIR write surface must require the
        // same privilege, not the broader `patients/med` clinical-staff ACL.
        RestConfig::request_authorization_check($request, "admin", "drugs");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirMedicationService(), $globalsBag);
        $controller->setExpectedResourceType("Medication");
        $controller->addAclRestrictions("admin", "drugs");
        return $controller->post($data);
    },

    "PUT /fhir/Medication/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        // See POST /fhir/Medication — master drug edits require admin/drugs.
        RestConfig::request_authorization_check($request, "admin", "drugs");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirMedicationService(), $globalsBag);
        $controller->setExpectedResourceType("Medication");
        $controller->addAclRestrictions("admin", "drugs");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/MedicationDispense" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationDispenseRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationDispenseRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/MedicationDispense/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationDispenseRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationDispenseRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/MedicationRequest" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRequestRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationRequestRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/MedicationRequest/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRequestRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationRequestRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/MedicationRequest" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirMedicationRequestService(), $globalsBag);
        $controller->setExpectedResourceType("MedicationRequest");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/MedicationRequest/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirMedicationRequestService(), $globalsBag);
        $controller->setExpectedResourceType("MedicationRequest");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Observation" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirObservationService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getAll();
    },
    "GET /fhir/Observation/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirObservationService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getOne($uuid);
    },
    "GET /fhir/Organization" => function (HttpRestRequest $request) {
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        }
        $return = (new FhirOrganizationRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/Organization/:uuid" => function (string $uuid, HttpRestRequest $request) {
        $patientUUID = null;
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        } else {
            $patientUUID = $request->getPatientUUIDString();
        }
        $return = (new FhirOrganizationRestController())->getOne($uuid, $patientUUID);


        return $return;
    },
    "GET /fhir/Specimen" => function (HttpRestRequest $request) {
        $getParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirSpecimenRestController())->getAll($getParams, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirSpecimenRestController())->getAll($getParams);
        }

        return $return;
    },
    "GET /fhir/Specimen/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirSpecimenRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirSpecimenRestController())->getOne($uuid);
        }

        return $return;
    },
    "POST /fhir/Organization" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $return = (new FhirOrganizationRestController())->post($data);

        return $return;
    },
    "PUT /fhir/Organization/:uuid" => function (string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $return = (new FhirOrganizationRestController())->patch($uuid, $data);

        return $return;
    },
    "POST /fhir/Patient" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $restController = new FhirPatientRestController();
        $restController->setOEGlobals($globalsBag);
        $return = $restController->post($data);

        return $return;
    },
    "PUT /fhir/Patient/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $restController = new FhirPatientRestController();
        $restController->setOEGlobals($globalsBag);
        $return = $restController->put($uuid, $data);

        return $return;
    },
    "GET /fhir/Patient" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $params = $request->getQueryParams();
        // we could set the fhir version here if we want... but the controller is already doing that
        $controller = new FhirPatientRestController();
        $controller->setOEGlobals($globalsBag);
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            //  Note in Patient context still have to return a bundle even if it is just one resource. (ie.
            //   need to use getAll rather than getOne)
            $params['_id'] = $request->getPatientUUIDString();
            $return = $controller->getAll($params, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
            $return = $controller->getAll($params);
        }

        return $return;
    },

    // we have to have the bulk fhir export operation here otherwise it will match $export to the patient $id
    'GET /fhir/Patient/$export' => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $return = $fhirExportService->processExport(
            $request->getQueryParams(),
            'Patient',
            $request->getHeader('Accept')[0] ?? '',
            $request->getHeader('Prefer')[0] ?? ''
        );

        return $return;
    },
    "GET /fhir/Patient/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            if (empty($uuid) || ($uuid != $request->getPatientUUIDString())) {
                throw new AccessDeniedException("patients", "demo", "patient id invalid");
            }
            $uuid = $request->getPatientUUIDString() ?? '';
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
        }
        $controller = new FhirPatientRestController();
        $controller->setOEGlobals($globalsBag);
        $return = $controller->getOne($uuid);

        return $return;
    },
    "GET /fhir/Person" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // Person is backed by the `users` table (staff records only) and
            // is not a US Core profile. Patient callers get provider directory
            // information from /Practitioner and /PractitionerRole instead.
            throw new AccessDeniedHttpException('Person is not available to patient-scoped callers.');
        }
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPersonRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/Person/:uuid" => function (string $uuid, HttpRestRequest $request) {
        // The self-branch is retained for staff tokens whose requestUserUUID
        // is a `users` row (so their own Person lookup resolves). For
        // patient-scoped callers the request user is not in `users`, so the
        // self-branch never matches and the patient path falls to the deny
        // below.
        if ($request->getRequestUserUUIDString() == $uuid) {
            return (new FhirPersonRestController())->getOne($uuid);
        }
        if ($request->isPatientRequest()) {
            throw new AccessDeniedHttpException('Person is not available to patient-scoped callers.');
        }
        RestConfig::request_authorization_check($request, "admin", "users");
        return (new FhirPersonRestController())->getOne($uuid);
    },

    "POST /fhir/Person" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirPersonService(), $globalsBag);
        $controller->setExpectedResourceType("Person");
        $controller->addAclRestrictions("admin", "users");
        return $controller->post($data);
    },

    "PUT /fhir/Person/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirPersonService(), $globalsBag);
        $controller->setExpectedResourceType("Person");
        $controller->addAclRestrictions("admin", "users");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Practitioner" => function (HttpRestRequest $request) {
        // Patient-scoped callers legitimately need care-team lookups (name +
        // NPI + work phone) via US Core Practitioner. The service applies
        // an allowlist over both search parameters and returned columns
        // when patient-caller view is on, so home address / home phone /
        // cell / non-work email are dropped before the FHIR builder runs.
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
            return (new FhirPractitionerRestController())->getAll($request->getQueryParams());
        }
        $service = new FhirPractitionerService();
        $service->setPatientCallerView(true);
        return (new FhirPractitionerRestController($service))->getAll($request->getQueryParams());
    },
    "GET /fhir/Practitioner/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
            return (new FhirPractitionerRestController())->getOne($uuid);
        }
        $service = new FhirPractitionerService();
        $service->setPatientCallerView(true);
        return (new FhirPractitionerRestController($service))->getOne($uuid);
    },
    "POST /fhir/Practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $return = (new FhirPractitionerRestController())->post($data);

        return $return;
    },
    "PUT /fhir/Practitioner/:uuid" => function (string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $return = (new FhirPractitionerRestController())->patch($uuid, $data);

        return $return;
    },
    "GET /fhir/PractitionerRole" => function (HttpRestRequest $request) {
        // PractitionerRole is the US Core surface for provider specialty /
        // facility / role affiliations. The service reads only work-labeled
        // telecoms (phonew1, fax, url, and the users.email column emitted
        // as use=work) and never touches street/city/zip or the home
        // telecom columns, so no per-caller field filter is needed.
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        }
        return (new FhirPractitionerRoleRestController())->getAll($request->getQueryParams());
    },
    "GET /fhir/PractitionerRole/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        }
        return (new FhirPractitionerRoleRestController())->getOne($uuid);
    },

    "POST /fhir/PractitionerRole" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirPractitionerRoleService(), $globalsBag);
        $controller->setExpectedResourceType("PractitionerRole");
        $controller->addAclRestrictions("admin", "users");
        return $controller->post($data);
    },

    "PUT /fhir/PractitionerRole/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirPractitionerRoleService(), $globalsBag);
        $controller->setExpectedResourceType("PractitionerRole");
        $controller->addAclRestrictions("admin", "users");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Procedure" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProcedureRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirProcedureRestController())->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/RelatedPerson" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirRelatedPersonService(), $globalsBag);
        $controller->addAclRestrictions("patients", "demo");
        return $controller->getAll();
    },
    "GET /fhir/RelatedPerson/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirRelatedPersonService(), $globalsBag);
        $controller->addAclRestrictions("patients", "demo");
        return $controller->getOne($uuid);
    },

    "POST /fhir/RelatedPerson" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirRelatedPersonService(), $globalsBag);
        $controller->setExpectedResourceType("RelatedPerson");
        $controller->addAclRestrictions("patients", "demo");
        return $controller->post($data);
    },

    "PUT /fhir/RelatedPerson/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirRelatedPersonService(), $globalsBag);
        $controller->setExpectedResourceType("RelatedPerson");
        $controller->addAclRestrictions("patients", "demo");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/ServiceRequest" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirServiceRequestRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirServiceRequestRestController())->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/ServiceRequest/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirServiceRequestRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirServiceRequestRestController())->getOne($uuid);
        }

        return $return;
    },

    "POST /fhir/ServiceRequest" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirServiceRequestService(), $globalsBag);
        $controller->setExpectedResourceType("ServiceRequest");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/ServiceRequest/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirServiceRequestService(), $globalsBag);
        $controller->setExpectedResourceType("ServiceRequest");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/Procedure/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProcedureRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirProcedureRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/Provenance/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProvenanceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirProvenanceRestController($request))->getOne($uuid);
        }

        return $return;
    },

    // Federated multi-service resources: DiagnosticReport, DocumentReference,
    // MedicationDispense, Procedure. Each is read as a union over multiple sub-services
    // (e.g. DiagnosticReport reads from both Laboratory and ClinicalNotes domains).
    // Writes need an explicit routing strategy per sub-service which is out of scope
    // for this PR; deferred to follow-up work. POST/PUT return 405 with a FHIR
    // OperationOutcome explaining the unsupported state.

    "POST /fhir/DiagnosticReport" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'DiagnosticReport',
        'FHIR DiagnosticReport writes are not yet supported. The OpenEMR read path federates Laboratory and ClinicalNotes sub-services; writing requires choosing a target sub-service per request (typically via category code) which is being designed in a follow-up PR.'
    ),
    "PUT /fhir/DiagnosticReport/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'DiagnosticReport',
        'FHIR DiagnosticReport writes are not yet supported. See POST /fhir/DiagnosticReport for the rationale.'
    ),

    "POST /fhir/DocumentReference" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'DocumentReference',
        'FHIR DocumentReference writes are not yet supported. The OpenEMR read path federates three sub-services (clinical notes, patient documents, advance care directives); writing requires routing per sub-service which is being designed in a follow-up PR. The existing $docref operation remains available.'
    ),
    "PUT /fhir/DocumentReference/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'DocumentReference',
        'FHIR DocumentReference writes are not yet supported. See POST /fhir/DocumentReference for the rationale.'
    ),

    "POST /fhir/MedicationDispense" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'MedicationDispense',
        'FHIR MedicationDispense writes are not yet supported. Pharmacy dispensary persistence in OpenEMR varies by deployment (drug_inventory, pharmacy module, external dispensary integrations); writing needs a per-deployment design that is being scoped in a follow-up PR.'
    ),
    "PUT /fhir/MedicationDispense/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'MedicationDispense',
        'FHIR MedicationDispense writes are not yet supported. See POST /fhir/MedicationDispense for the rationale.'
    ),

    "POST /fhir/Procedure" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'Procedure',
        'FHIR Procedure writes are not yet supported. The OpenEMR read path federates clinical procedures (procedure_order/procedure_order_code) with surgery procedures; writing overlaps with ServiceRequest writes already provided. Use POST /fhir/ServiceRequest with intent=order for procedure orders; standalone Procedure writes are being designed in a follow-up PR.'
    ),
    "PUT /fhir/Procedure/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'Procedure',
        'FHIR Procedure writes are not yet supported. See POST /fhir/Procedure for the rationale.'
    ),

    // FHIR Provenance in OpenEMR is synthesized at read time from other domain
    // resources; there is no underlying provenance table to write to. The FHIR
    // spec itself permits Provenance writes — this is an OpenEMR implementation
    // limitation, not a spec restriction. POST/PUT return 405 with a FHIR
    // OperationOutcome.
    "POST /fhir/Provenance" => RestControllerHelper::fhirWriteNotImplemented(
        'POST',
        'Provenance',
        'FHIR Provenance is a derived audit-trail resource; it is synthesized from other domain resources and cannot be written directly.'
    ),
    "PUT /fhir/Provenance/:uuid" => RestControllerHelper::fhirWriteNotImplemented(
        'PUT',
        'Provenance',
        'FHIR Provenance is a derived audit-trail resource; it is synthesized from other domain resources and cannot be written directly.'
    ),

    // NOTE: this GET request only supports requests with an _id parameter.  FHIR inferno test tool requires the 'search'
    // property to support which is why this endpoint exists.
    "GET /fhir/Provenance" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProvenanceRestController($request))->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            // TODO: it seems like regular users should be able to grab authorship / provenance information
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirProvenanceRestController($request))->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/Questionnaire" => function (HttpRestRequest $request) {
        $logger = ServiceContainer::getLogger();
        $fhirQuestionnaireService = new FhirQuestionnaireService();
        $fhirFormService = new FhirQuestionnaireFormService();
        $fhirQuestionnaireService->addMappedService($fhirFormService);
        $return = (new FhirQuestionnaireRestController($logger, $fhirQuestionnaireService))->list($request);
        return $return;
    },
    "GET /fhir/Questionnaire/:uuid" => function (string $uuid, HttpRestRequest $request) {
        // Matches the sibling list route's posture: Questionnaire is definitional rather than
        // patient data, and the list route carries no ACL of its own.
        $logger = ServiceContainer::getLogger();
        $fhirQuestionnaireService = new FhirQuestionnaireService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireFormService());
        $return = (new FhirQuestionnaireRestController($logger, $fhirQuestionnaireService))->one($request, $uuid);
        return $return;
    },

    "POST /fhir/Questionnaire" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        // The questionnaire repository is a definitional resource shared by every patient, so
        // authoring one is gated the same way the other definitional writes are.
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirQuestionnaireService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("Questionnaire");
        $controller->addAclRestrictions("admin", "super");
        return $controller->post($data);
    },

    "PUT /fhir/Questionnaire/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirQuestionnaireService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("Questionnaire");
        $controller->addAclRestrictions("admin", "super");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/QuestionnaireResponse" => function (HttpRestRequest $request) {
        // Non-patient callers (user-scope OAuth tokens or APICSRFTOKEN
        // core-session paths) previously reached the controller with no
        // route-level ACL gate. Apply the same `patients/med` gate the
        // controller had documented but commented out; the controller
        // continues to branch on isPatientRequest() to bind the patient
        // compartment for patient-scope tokens.
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "patients", "med");
        }
        $fhirQuestionnaireService = new FhirQuestionnaireResponseService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireResponseFormService());
        $return = (new FhirQuestionnaireResponseRestController($fhirQuestionnaireService))->list($request);
        return $return;
    },
    "GET /fhir/QuestionnaireResponse/:uuid" => function (string $uuid, HttpRestRequest $request) {
        // See sibling list route above — same ACL rationale.
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "patients", "med");
        }
        $fhirQuestionnaireService = new FhirQuestionnaireResponseService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireResponseFormService());
        $return = (new FhirQuestionnaireResponseRestController($fhirQuestionnaireService))->one($request, $uuid);
        return $return;
    },
    "POST /fhir/QuestionnaireResponse" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        // Same gate the sibling read routes use; the generic controller rejects patient-scope
        // tokens for writes outright.
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirQuestionnaireResponseService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("QuestionnaireResponse");
        $controller->addAclRestrictions("patients", "med");
        return $controller->post($data);
    },

    "PUT /fhir/QuestionnaireResponse/:uuid" => function (string $uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = RestControllerHelper::parseJsonRequestBody($request, true);
        if ($data instanceof Response) {
            return $data;
        }
        $controller = new FhirGenericRestController($request, new FhirQuestionnaireResponseService($request->getApiBaseFullUrl()), $globalsBag);
        $controller->setExpectedResourceType("QuestionnaireResponse");
        $controller->addAclRestrictions("patients", "med");
        return $controller->put($uuid, $data);
    },

    "GET /fhir/ValueSet" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $return = (new FhirValueSetRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/ValueSet/:uuid" => function (string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $return = (new FhirValueSetRestController())->getOne($uuid);

        return $return;
    },

    // other endpoints
    "GET /fhir/metadata" => function (\OpenEMR\Common\Http\HttpRestRequest $request) {
        $return = (new FhirMetaDataRestController())->getMetaData();
        return $return;
    },
    "GET /fhir/.well-known/smart-configuration" => function () {
        $return = (new SMARTConfigurationController())->getConfig();

        return $return;
    },
    "GET /fhir/OperationDefinition" => function (HttpRestRequest $request) {
        // for now we will just hard code the custom resources
        $operationDefinitionController = new FhirOperationDefinitionRestController();
        $return = $operationDefinitionController->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/OperationDefinition/:operation" => function ($operation, HttpRestRequest $request) {
        // for now we will just hard code the custom resources
        $operationDefinitionController = new FhirOperationDefinitionRestController();
        $return = $operationDefinitionController->getOne($operation);

        return $return;
    },

    // FHIR root level operations
    'GET /fhir/$export' => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $return = $fhirExportService->processExport(
            $request->getQueryParams(),
            'System',
            $request->getHeader('Accept')[0] ?? '',
            $request->getHeader('Prefer')[0] ?? ''
        );

        return $return;
    },

    // these two operations are adopted based on the documentation used in the IBM FHIR Server
    // we'd reference cerner or epic but we couldn't find any documentation about those (Jan 30th 2021)
    // @see https://ibm.github.io/FHIR/guides/FHIRBulkOperations/
    'GET /fhir/$bulkdata-status' => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $jobUuidString = $request->getQueryParam('job');
        // if we were truly async we would return 202 here to say we are in progress with a JSON response
        // since OpenEMR data is so small we just return the JSON from the database
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $return = $fhirExportService->processExportStatusRequestForJob($jobUuidString);

        return $return;
    },
    'DELETE /fhir/$bulkdata-status' => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $job = $request->getQueryParam('job');
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $return = $fhirExportService->processDeleteExportForJob($job);

        return $return;
    },
];
