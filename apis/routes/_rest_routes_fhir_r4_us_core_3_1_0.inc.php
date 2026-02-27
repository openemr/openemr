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
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
use OpenEMR\RestControllers\FHIR\FhirImmunizationRestController;
use OpenEMR\RestControllers\FHIR\FhirGoalRestController;
use OpenEMR\RestControllers\FHIR\FhirGroupRestController;
use OpenEMR\RestControllers\FHIR\FhirLocationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationDispenseRestController;
use OpenEMR\RestControllers\FHIR\FhirMedicationRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\FHIR\FhirPersonRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRoleRestController;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\RestControllers\FHIR\FhirProcedureRestController;
use OpenEMR\RestControllers\FHIR\FhirProvenanceRestController;
use OpenEMR\RestControllers\FHIR\FhirServiceRequestRestController;
use OpenEMR\RestControllers\FHIR\FhirValueSetRestController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationExportRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationDocRefRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationDefinitionRestController;
use OpenEMR\RestControllers\SMART\SMARTConfigurationController;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use OpenEMR\Services\FHIR\Questionnaire\FhirQuestionnaireFormService;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireRestController;
use OpenEMR\Services\FHIR\FhirQuestionnaireResponseService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireResponseRestController;
use OpenEMR\RestControllers\FHIR\FhirSpecimenRestController;
use OpenEMR\RestControllers\FHIR\FhirMediaRestController;
use OpenEMR\RestControllers\FHIR\FhirGenericRestController;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;

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
    "GET /fhir/AllergyIntolerance/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid);
        }

        return $return;
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
    "GET /fhir/Appointment/:uuid" => function ($uuid, HttpRestRequest $request) {
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
    "GET /fhir/CarePlan/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCarePlanRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirCarePlanRestController())->getOne($uuid);
        }

        return $return;
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
    "GET /fhir/CareTeam/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCareTeamRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirCareTeamRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/Condition" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getAll();
    },
    "GET /fhir/Condition/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getOne($uuid);
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
    "GET /fhir/Coverage/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirCoverageRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirCoverageRestController())->getOne($uuid);
        }

        return $return;
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
    "GET /fhir/Device/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirDeviceRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirDeviceRestController())->getOne($uuid);
        }

        return $return;
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
    "GET /fhir/DiagnosticReport/:uuid" => function ($uuid, HttpRestRequest $request) {
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
            // TODO: it seems like regular users should be able to grab authorship / provenance information
            RestConfig::request_authorization_check($request, "patients", "demo");
            $return = (new FhirOperationDocRefRestController($request))->getAll($request->getQueryParams());
        }

        return $return;
    },
    "GET /fhir/DocumentReference/:uuid" => function ($uuid, HttpRestRequest $request) {
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
    "GET /fhir/Encounter/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirEncounterRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirEncounterRestController())->getOne($uuid);
        }

        return $return;
    },
<<<<<<< HEAD
=======

    /**
     *  @OA\Get(
     *      path="/fhir/Goal",
     *      description="Returns a list of Goal resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Goal resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="_lastUpdated",
     *          in="query",
     *          description="Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="patient",
     *          in="query",
     *          description="The uuid for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard Response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="json object",
     *                      description="FHIR Json object.",
     *                      type="object"
     *                  ),
     *                  example={
     *                      "meta": {
     *                          "lastUpdated": "2021-09-14T09:13:51"
     *                      },
     *                      "resourceType": "Bundle",
     *                      "type": "collection",
     *                      "total": 0,
     *                      "link": {
     *                          {
     *                              "relation": "self",
     *                              "url": "https://localhost:9300/apis/default/fhir/Goal"
     *                          }
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
>>>>>>> master
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
    "GET /fhir/Goal/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirGoalRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirGoalRestController())->getOne($uuid);
        }

        return $return;
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
    "GET /fhir/Group/:uuid" => function ($uuid, HttpRestRequest $request) {
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
    "GET /fhir/Immunization/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirImmunizationRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirImmunizationRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/Location" => function (HttpRestRequest $request) {
        $return = (new FhirLocationRestController($request))->getAll($request->getQueryParams(), $request->getPatientUUIDString());

        return $return;
    },
    "GET /fhir/Location/:uuid" => function ($uuid, HttpRestRequest $request) {
        $return = (new FhirLocationRestController($request))->getOne($uuid, $request->getPatientUUIDString());

        return $return;
    },
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
    "GET /fhir/Media/:uuid" => function ($uuid, HttpRestRequest $request) {
        $return = (new FhirMediaRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        return $return;
    },
    "GET /fhir/Medication" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new FhirMedicationRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/Medication/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRestController())->getOne($uuid);
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationRestController())->getOne($uuid);
        }

        return $return;
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
    "GET /fhir/MedicationDispense/:uuid" => function ($uuid, HttpRestRequest $request) {
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
    "GET /fhir/MedicationRequest/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRequestRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationRequestRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/Observation" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirObservationService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getAll();
    },
    "GET /fhir/Observation/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
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
    "GET /fhir/Organization/:uuid" => function ($uuid, HttpRestRequest $request) {
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
    "GET /fhir/Specimen/:uuid" => function ($uuid, HttpRestRequest $request) {
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
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->post($data);

        return $return;
    },
    "PUT /fhir/Organization/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->patch($uuid, $data);

        return $return;
    },
    "POST /fhir/Patient" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $restController = new FhirPatientRestController();
        $restController->setOEGlobals($globalsBag);
        $return = $restController->post($data);

        return $return;
    },
    "PUT /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
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
    "GET /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            if (empty($uuid) || ($uuid != $request->getPatientUUIDString())) {
                throw new AccessDeniedException("patients", "demo", "patient id invalid");
            }
            $uuid = $request->getPatientUUIDString();
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
        }
        $controller = new FhirPatientRestController();
        $controller->setOEGlobals($globalsBag);
        $return = $controller->getOne($uuid);

        return $return;
    },
    "GET /fhir/Person" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPersonRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/Person/:uuid" => function ($uuid, HttpRestRequest $request) {
        // if the api user is requesting their own user we need to let it through
        // this is because the /Person endpoint needs to be responsive to the fhirUser return value
        // for the currently logged in user
        if ($request->getRequestUserUUIDString() == $uuid) {
            $return = (new FhirPersonRestController())->getOne($uuid);
        } elseif (!$request->isPatientRequest()) {
            // not a patient ,make sure we have access to the users ACL
            RestConfig::request_authorization_check($request, "admin", "users");
            $return = (new FhirPersonRestController())->getOne($uuid);
        } else {
            // if we are a patient bound request we need to make sure we are only bound to the patient
            $return = (new FhirPersonRestController())->getOne($uuid);
        }


        return $return;
    },
    "GET /fhir/Practitioner" => function (HttpRestRequest $request) {

        // TODO: @adunsulag talk with brady.miller about patients needing access to any practitioner resource
        // that is referenced in connected patient resources -- such as AllergyIntollerance.
        // I don't believe patients are assigned to a particular practitioner
        // should we allow just open api access to admin information?  Should we restrict particular pieces
        // of data in the practitioner side (phone number, address information) based on a permission set?
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        }
        $return = (new FhirPractitionerRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/Practitioner/:uuid" => function ($uuid, HttpRestRequest $request) {
        // TODO: @adunsulag talk with brady.miller about patients needing access to any practitioner resource
        // that is referenced in connected patient resources -- such as AllergyIntollerance.
        // I don't believe patients are assigned to a particular practitioner
        // should we allow just open api access to admin information?  Should we restrict particular pieces
        // of data in the practitioner side (phone number, address information) based on a permission set?
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        }
        $return = (new FhirPractitionerRestController())->getOne($uuid);

        return $return;
    },
    "POST /fhir/Practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->post($data);

        return $return;
    },
    "PUT /fhir/Practitioner/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->patch($uuid, $data);

        return $return;
    },
    "GET /fhir/PractitionerRole" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/PractitionerRole/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getOne($uuid);

        return $return;
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
    "GET /fhir/ServiceRequest/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirServiceRequestRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirServiceRequestRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/Procedure/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProcedureRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirProcedureRestController())->getOne($uuid);
        }

        return $return;
    },
    "GET /fhir/Provenance/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirProvenanceRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "admin", "super");
            $return = (new FhirProvenanceRestController($request))->getOne($uuid);
        }

        return $return;
    },

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
        $logger = new SystemLogger();
        $fhirQuestionnaireService = new FhirQuestionnaireService();
        $fhirFormService = new FhirQuestionnaireFormService();
        $fhirQuestionnaireService->addMappedService($fhirFormService);
        $return = (new FhirQuestionnaireRestController($logger, $fhirQuestionnaireService))->list($request);
        return $return;
    },
    "GET /fhir/QuestionnaireResponse" => function (HttpRestRequest $request) {
        $fhirQuestionnaireService = new FhirQuestionnaireResponseService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireResponseFormService());
        $return = (new FhirQuestionnaireResponseRestController($fhirQuestionnaireService))->list($request);
        return $return;
    },
    "GET /fhir/QuestionnaireResponse/:uuid" => function (string $uuid, HttpRestRequest $request) {
        $fhirQuestionnaireService = new FhirQuestionnaireResponseService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireResponseFormService());
        $return = (new FhirQuestionnaireResponseRestController($fhirQuestionnaireService))->one($request, $uuid);
        return $return;
    },
    "GET /fhir/ValueSet" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $return = (new FhirValueSetRestController())->getAll($request->getQueryParams());

        return $return;
    },
    "GET /fhir/ValueSet/:uuid" => function ($uuid, HttpRestRequest $request) {
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
