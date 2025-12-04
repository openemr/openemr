<?php

/**
 * FHIR API Routes
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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
use OpenEMR\RestControllers\FHIR\FhirObservationRestController;
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
use OpenEMR\RestControllers\FHIR\FhirRelatedPersonRestController;
use OpenEMR\RestControllers\FHIR\FhirGenericRestController;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirObservationService;

// Note that the fhir route includes both user role and patient role
//  (there is a mechanism in place to ensure patient role is binded
//   to only see the data of the one patient)
return [
    /**
     *  @OA\Get(
     *      path="/fhir/AllergyIntolerance",
     *      description="Returns a list of AllergyIntolerance resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the AllergyIntolerance resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/AllergyIntolerance"
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

    /**
     *  @OA\Get(
     *      path="/fhir/AllergyIntolerance/{uuid}",
     *      description="Returns a single AllergyIntolerance resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the AllergyIntolerance resource.",
     *          required=true,
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
     *                      "id": "94682fe5-f383-4885-9505-64b02e34906f",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-16T00:27:32+00:00"
     *                      },
     *                      "resourceType": "AllergyIntolerance",
     *                      "text": {
     *                          "status": "additional",
     *                          "div": "<div xmlns='http://www.w3.org/1999/xhtml'>penicillin</div>"
     *                      },
     *                      "clinicalStatus": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical",
     *                                  "code": "active",
     *                                  "display": "Active"
     *                              }
     *                          }
     *                      },
     *                      "verificationStatus": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/allergyintolerance-verification",
     *                                  "code": "confirmed",
     *                                  "display": "Confirmed"
     *                              }
     *                          }
     *                      },
     *                      "category": {
     *                          "medication"
     *                      },
     *                      "criticality": "low",
     *                      "code": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/data-absent-reason",
     *                                  "code": "unknown",
     *                                  "display": "Unknown"
     *                              }
     *                          }
     *                      },
     *                      "patient": {
     *                          "reference": "Patient/94682ef5-b0e3-4289-b19a-11b9592e9c92"
     *                      },
     *                      "reaction": {
     *                          {
     *                              "manifestation": {
     *                                  {
     *                                      "coding": {
     *                                          {
     *                                              "system": "http://snomed.info/sct",
     *                                              "code": "422587007",
     *                                              "display": "Nausea"
     *                                          }
     *                                      },
     *                                      "text": "Nausea"
     *                                  }
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Appointment",
     *      description="Returns a list of Appointment resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Appointment resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/AllergyIntolerance"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Appointment/{uuid}",
     *      description="Returns a single Appointment resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Appointment resource.",
     *          required=true,
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
     *                  example={}
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/CarePlan",
     *      description="Returns a list of CarePlan resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the CarePlan resource.",
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
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="The category of the CarePlan resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/CarePlan"
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

    /**
     *  @OA\Get(
     *      path="/fhir/CarePlan/{uuid}",
     *      description="Returns a single CarePlan resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the CarePlan resource.",
     *          required=true,
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
     *                      "id": "94682f08-8fbc-451e-b1ec-f922d765c38f_1",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-16T00:54:18+00:00"
     *                      },
     *                      "resourceType": "CarePlan",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns=""http://www.w3.org/1999/xhtml""><p>Treat flu.</p></div>"
     *                      },
     *                      "status": "active",
     *                      "intent": "plan",
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://hl7.org/fhir/us/core/CodeSystem/careplan-category",
     *                                      "code": "assess-plan"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "description": "Treat flu.",
     *                      "subject": {
     *                          "reference": "Patient/94682ef5-b0e3-4289-b19a-11b9592e9c92",
     *                          "type": "Patient"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/CareTeam",
     *      description="Returns a list of CareTeam resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the CareTeam resource.",
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
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="The status of the CarePlan resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/CareTeam"
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
     * )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/CareTeam/{uuid}",
     *      description="Returns a single CareTeam resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the CareTeam resource.",
     *          required=true,
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
     *                      "id": "94682f09-69fe-4ada-8ea6-753a52bd1516",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-16T01:07:22+00:00"
     *                      },
     *                      "resourceType": "CareTeam",
     *                      "status": "active",
     *                      "subject": {
     *                          "reference": "Patient/94682ef5-b0e3-4289-b19a-11b9592e9c92",
     *                          "type": "Patient"
     *                      },
     *                      "participant": {
     *                          {
     *                              "role": {
     *                                  {
     *                                      "coding": {
     *                                          {
     *                                              "system": "http://nucc.org/provider-taxonomy",
     *                                              "code": "102L00000X",
     *                                              "display": "Psychoanalyst"
     *                                          }
     *                                      }
     *                                  }
     *                              },
     *                              "member": {
     *                                  "reference": "Practitioner/94682c68-f712-4c39-9158-ff132a08f26b",
     *                                  "type": "Practitioner"
     *                              },
     *                              "onBehalfOf": {
     *                                  "reference": "Organization/94682c62-b801-4498-84a1-13f158bb2a18",
     *                                  "type": "Organization"
     *                              }
     *                          },
     *                          {
     *                              "role": {
     *                                  {
     *                                      "coding": {
     *                                          {
     *                                              "system": "http://terminology.hl7.org/CodeSystem/data-absent-reason",
     *                                              "code": "unknown",
     *                                              "display": "Unknown"
     *                                          }
     *                                      }
     *                                  }
     *                              },
     *                              "member": {
     *                                  "reference": "Organization/94682c62-b801-4498-84a1-13f158bb2a18",
     *                                  "type": "Organization"
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Condition",
     *      description="Returns a list of Condition resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Condition resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Condition"
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
    "GET /fhir/Condition" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getAll();
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Condition/{uuid}",
     *      description="Returns a single Condition resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Condition resource.",
     *          required=true,
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
     *                      "id": "94682c68-e5bb-4c5c-859a-cebaa5a1e582",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-16T02:41:53+00:00"
     *                      },
     *                      "resourceType": "Condition",
     *                      "clinicalStatus": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/condition-clinical",
     *                                  "code": "inactive",
     *                                  "display": "Inactive"
     *                              }
     *                          }
     *                      },
     *                      "verificationStatus": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/condition-ver-status",
     *                                  "code": "unconfirmed",
     *                                  "display": "Unconfirmed"
     *                              }
     *                          }
     *                      },
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/condition-category",
     *                                      "code": "problem-list-item",
     *                                      "display": "Problem List Item"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "code": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://snomed.info/sct",
     *                                  "code": "444814009",
     *                                  "display": ""
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/94682c62-d37e-48b5-8018-c5f6f3566609"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/Condition/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirConditionService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getOne($uuid);
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Coverage",
     *      description="Returns a list of Coverage resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Coverage resource.",
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
     *      @OA\Parameter(
     *          name="payor",
     *          in="query",
     *          description="The payor of the Coverage resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Coverage"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Coverage/{uuid}",
     *      description="Returns a single Coverage resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Coverage resource.",
     *          required=true,
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
     *                      "id": "960d5f10-edc6-4c65-a6d4-39a1e1da87a8",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-04-14T07:58:45+00:00"
     *                      },
     *                      "resourceType": "Coverage",
     *                      "status": "active",
     *                      "beneficiary": {
     *                          "reference": "Patient/960d5f08-9fdf-4bdc-9108-84a149e28bac"
     *                      },
     *                      "relationship": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/subscriber-relationship",
     *                                  "code": ""
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Device",
     *      description="Returns a list of Device resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Device resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Device"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Device/{uuid}",
     *      description="Returns a single Device resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Device resource.",
     *          required=true,
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
     *                      "id": "946dce19-c80a-402c-862a-eadf3f2377f0",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-18T19:28:59+00:00"
     *                      },
     *                      "resourceType": "Device",
     *                      "udiCarrier": {
     *                          {
     *                              "deviceIdentifier": "08717648200274",
     *                              "carrierHRF": "=/08717648200274=,000025=A99971312345600=>014032=}013032&,1000000000000XYZ123"
     *                          }
     *                      },
     *                      "distinctIdentifier": "A99971312345600",
     *                      "manufactureDate": "2013-02-01",
     *                      "expirationDate": "2014-02-01",
     *                      "lotNumber": "000000000000XYZ123",
     *                      "serialNumber": "000025",
     *                      "type": {
     *                          "extension": {
     *                              {
     *                                  "valueCode": "unknown",
     *                                  "url": "http://hl7.org/fhir/StructureDefinition/data-absent-reason"
     *                              }
     *                          }
     *                      },
     *                      "patient": {
     *                          "reference": "Patient/946da619-c631-431a-a282-487cd6fb7802",
     *                          "type": "Patient"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/DiagnosticReport",
     *      description="Returns a list of DiagnosticReport resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the DiagnosticReport resource.",
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
     *      @OA\Parameter(
     *          name="code",
     *          in="query",
     *          description="The code of the DiagnosticReport resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="The category of the DiagnosticReport resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="The datetime of the DiagnosticReport resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/DiagnosticReport"
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

    /**
     *  @OA\Get(
     *      path="/fhir/DiagnosticReport/{uuid}",
     *      description="Returns a single DiagnosticReport resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the DiagnosticReport resource.",
     *          required=true,
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
     *                      "id": "93fb2d6a-77ac-48ca-a12d-1a17e40007e3",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-18T20:52:34+00:00"
     *                      },
     *                      "resourceType": "DiagnosticReport",
     *                      "status": "final",
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://loinc.org",
     *                                      "code": "LP7839-6",
     *                                      "display": "Pathology"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "code": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://loinc.org",
     *                                  "code": "11502-2",
     *                                  "display": "Laboratory report"
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/9353b8f5-0a87-4e2a-afd4-25341fdb0fbc",
     *                          "type": "Patient"
     *                      },
     *                      "encounter": {
     *                          "reference": "Encounter/93540818-cb5f-49df-b73b-83901bb793b6",
     *                          "type": "Encounter"
     *                      },
     *                      "effectiveDateTime": "2015-06-22T00:00:00+00:00",
     *                      "issued": "2015-06-22T00:00:00+00:00",
     *                      "performer": {
     *                          {
     *                              "reference": "Organization/935249b5-0ba6-4b5b-8863-a7a27d4c6350",
     *                              "type": "Organization"
     *                          }
     *                      },
     *                      "presentedForm": {
     *                          {
     *                              "contentType": "text/plain",
     *                              "data": "TXMgQWxpY2UgTmV3bWFuIHdhcyB0ZXN0ZWQgZm9yIHRoZSBVcmluYW5hbHlzaXMgbWFjcm8gcGFuZWwgYW5kIHRoZSByZXN1bHRzIGhhdmUgYmVlbiBmb3VuZCB0byBiZSANCm5vcm1hbC4="
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/DocumentReference",
     *      description="Returns a list of DocumentReference resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the DocumentReference resource.",
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
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="The type of the DocumentReference resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="The category of the DocumentReference resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="The datetime of the DocumentReference resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/DocumentReference"
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

    /**
     *  @OA\POST(
     *      path="/fhir/DocumentReference/$docref",
     *      description="The $docref operation is used to request the server generates a document based on the specified parameters. If no additional parameters are specified then a DocumentReference to the patient's most current Clinical Summary of Care Document (CCD) is returned. The document itself is retrieved using the DocumentReference.content.attachment.url element.  See <a href='http://hl7.org/fhir/us/core/OperationDefinition-docref.html' target='_blank' rel='noopener'>http://hl7.org/fhir/us/core/OperationDefinition-docref.html</a> for more details.",
     *      tags={"fhir"},
     *      @OA\ExternalDocumentation(description="Detailed documentation on this operation", url="https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API"),
     *      @OA\Parameter(
     *          name="patient",
     *          in="query",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="start",
     *          in="query",
     *          description="The datetime refers to care dates not record currency dates.  All records relating to care provided in a certain date range.  If no start date is provided then all documents prior to the end date are in scope.  If no start and end date are provided, the most recent or current document is in scope.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="end",
     *          in="query",
     *          description="The datetime refers to care dates not record currency dates.  All records relating to care provided in a certain date range.  If no end date is provided then all documents subsequent to the start date are in scope.  If no start and end date are provided, the most recent or current document is in scope.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="The type refers to the document type.  This is a LOINC code from the valueset of <a href='http://hl7.org/fhir/R4/valueset-c80-doc-typecodes.html' target='_blank' rel='noopener'>http://hl7.org/fhir/R4/valueset-c80-doc-typecodes.html</a>. The server currently only supports the LOINC code of 34133-9 (Summary of episode node).",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="A search bundle of DocumentReferences is returned"
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

    /**
     *  @OA\Get(
     *      path="/fhir/DocumentReference/{uuid}",
     *      description="Returns a single DocumentReference resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the DocumentReference resource.",
     *          required=true,
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
     *                      "id": "946e7553-1aaa-49f8-8f81-ae15ccaa9165",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-19T03:17:51+00:00"
     *                      },
     *                      "resourceType": "DocumentReference",
     *                      "identifier": {
     *                          {
     *                              "value": "946e7553-1aaa-49f8-8f81-ae15ccaa9165"
     *                          }
     *                      },
     *                      "status": "current",
     *                      "type": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/v3-NullFlavor",
     *                                  "code": "UNK",
     *                                  "display": "unknown"
     *                              }
     *                          }
     *                      },
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "https://localhost:9300/apis/default/fhir/ValueSet/openemr-document-types",
     *                                      "code": "openemr-document",
     *                                      "display": "OpenEMR Document"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/946da619-c631-431a-a282-487cd6fb7802",
     *                          "type": "Patient"
     *                      },
     *                      "date": "2021-09-19T03:15:56+00:00",
     *                      "author": {
     *                          null
     *                      },
     *                      "content": {
     *                          {
     *                              "attachment": {
     *                                  "contentType": "image/gif",
     *                                  "url": "https://localhost:9300/apis/default/fhir/Binary/7"
     *                              },
     *                              "format": {
     *                                  "system": "http://ihe.net/fhir/ValueSet/IHE.FormatCode.codesystem",
     *                                  "code": "urn:ihe:iti:xds:2017:mimeTypeSufficient",
     *                                  "display": "mimeType Sufficient"
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Binary/{id}",
     *      description="Used for downloading binary documents generated either with BULK FHIR Export or with the $docref CCD export operation.  Documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The id for the Document.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The documentation for working with BULK FHIR or $docref document exports can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Encounter",
     *      description="Returns a list of Encounter resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Encounter resource.",
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
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="The datetime of the Encounter resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Encounter"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Encounter/{uuid}",
     *      description="Returns a single Encounter resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Encounter resource.",
     *          required=true,
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
     *                      "id": "946da61d-6b95-4f8e-abe5-534a25913b71",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-19T06:27:41+00:00"
     *                      },
     *                      "resourceType": "Encounter",
     *                      "identifier": {
     *                          {
     *                              "system": "urn:ietf:rfc:3986",
     *                              "value": "946da61d-6b95-4f8e-abe5-534a25913b71"
     *                          }
     *                      },
     *                      "status": "finished",
     *                      "class": {
     *                          "system": "http://terminology.hl7.org/CodeSystem/v3-ActCode",
     *                          "code": "AMB",
     *                          "display": "ambulatory"
     *                      },
     *                      "type": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://snomed.info/sct",
     *                                      "code": "185349003",
     *                                      "display": "Encounter for check up (procedure)"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/946da61b-626b-4f88-81e2-adfb88f4f0fe",
     *                          "type": "Patient"
     *                      },
     *                      "participant": {
     *                          {
     *                              "type": {
     *                                  {
     *                                      "coding": {
     *                                          {
     *                                              "system": "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
     *                                              "code": "PPRF",
     *                                              "display": "Primary Performer"
     *                                          }
     *                                      }
     *                                  }
     *                              },
     *                              "period": {
     *                                  "start": "2012-08-13T00:00:00+00:00"
     *                              },
     *                              "individual": {
     *                                  "reference": "Practitioner/946da61d-ac5f-4fdc-b3f2-7b58dc49976b",
     *                                  "type": "Practitioner"
     *                              }
     *                          }
     *                      },
     *                      "period": {
     *                          "start": "2012-08-13T00:00:00+00:00"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Goal",
     *      description="Returns a list of Condition resources.",
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

    /**
     *  @OA\Get(
     *      path="/fhir/Goal/{uuid}",
     *      description="Returns a single Goal resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Goal resource.",
     *          required=true,
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
     *                      "id": "946da61d-6b88-4d54-bdd6-4029e2ad9e3f_1",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-19T06:45:58+00:00"
     *                      },
     *                      "resourceType": "Goal",
     *                      "lifecycleStatus": "active",
     *                      "description": {
     *                          "text": "Eating more vegetables."
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/946da619-c631-431a-a282-487cd6fb7802",
     *                          "type": "Patient"
     *                      },
     *                      "target": {
     *                          {
     *                              "measure": {
     *                                  "extension": {
     *                                      {
     *                                          "valueCode": "unknown",
     *                                          "url": "http://hl7.org/fhir/StructureDefinition/data-absent-reason"
     *                                      }
     *                                  }
     *                              },
     *                              "detailString": "Eating more vegetables.",
     *                              "dueDate": "2021-09-09"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Group",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Group resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Group"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Group/{uuid}",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Group resource.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Group/{id}/$export",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The id for the Group resource.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Immunization",
     *      description="Returns a list of Immunization resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Immunization resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Immunization"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Immunization/{uuid}",
     *      description="Returns a single Immunization resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Immunization resource.",
     *          required=true,
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
     *                      "id": "95e8d8b7-e3e2-4e03-8eb1-31e1d9097d8f",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-03-26T05:42:59+00:00"
     *                      },
     *                      "resourceType": "Immunization",
     *                      "status": "completed",
     *                      "vaccineCode": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://hl7.org/fhir/sid/cvx",
     *                                  "code": "207",
     *                                  "display": "SARS-COV-2 (COVID-19) vaccine, mRNA, spike protein, LNP, preservative free, 100 mcg/0.5mL dose"
     *                              }
     *                          }
     *                      },
     *                      "patient": {
     *                          "reference": "Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf"
     *                      },
     *                      "occurrenceDateTime": "2022-03-26T05:35:00+00:00",
     *                      "recorded": "2022-03-26T05:42:26+00:00",
     *                      "primarySource": false
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Location",
     *      description="Returns a list of Location resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Location resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Location"
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
    "GET /fhir/Location" => function (HttpRestRequest $request) {
        $return = (new FhirLocationRestController($request))->getAll($request->getQueryParams(), $request->getPatientUUIDString());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Location/{uuid}",
     *      description="Returns a single Location resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Location resource.",
     *          required=true,
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
     *                      "id": "946da61d-c4f2-4f03-a2a7-b571f6a24b65",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-19T08:14:58+00:00"
     *                      },
     *                      "resourceType": "Location",
     *                      "status": "active",
     *                      "name": "Your Clinic Name Here",
     *                      "telecom": {
     *                          {
     *                              "system": "phone",
     *                              "value": "000-000-0000"
     *                          },
     *                          {
     *                              "system": "fax",
     *                              "value": "000-000-0000"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/Location/:uuid" => function ($uuid, HttpRestRequest $request) {
        $return = (new FhirLocationRestController($request))->getOne($uuid, $request->getPatientUUIDString());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Media",
     *      description="Returns a search bundle of Media resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Media resource.",
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
     *      @OA\Parameter(
     *          name="content-type",
     *          in="query",
     *          description="The Content-Type of the Media resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Media"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Media/{uuid}",
     *      description="Returns a single Media resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Media resource.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="patient",
     *          in="query",
     *          description="The uuid for the Patient resource to filter Media references by patient.",
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
     *                      "id": "a037abc5-7ebb-43a1-9e0f-b57586dc6d25",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2025-10-27T20:00:54-04:00"
     *                      },
     *                      "resourceType": "Media",
     *                      "status": "completed",
     *                      "subject": {
     *                          "reference": "Patient/96506861-511f-4f6d-bc97-b65a78cf1995",
     *                          "type": "Patient"
     *                      },
     *                      "content": {
     *                          "contentType": "application/dicom",
     *                          "url": "/fhir/Binary/a037abc5-7ebb-43a1-9e0f-b57586dc6d25",
     *                          "title": "MR000021.dcm"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/Media/:uuid" => function ($uuid, HttpRestRequest $request) {
        $return = (new FhirMediaRestController($request))->getOne($uuid, $request->getPatientUUIDString());
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Medication",
     *      description="Returns a list of Medication resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Medication resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Medication"
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
    "GET /fhir/Medication" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new FhirMedicationRestController())->getAll($request->getQueryParams());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Medication/{uuid}",
     *      description="Returns a single Medication resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Medication resource.",
     *          required=true,
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
     *                      "id": "961aa334-9348-4145-8252-de665e3c4afa",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-04-19T23:42:14+00:00"
     *                      },
     *                      "resourceType": "Medication",
     *                      "code": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://www.nlm.nih.gov/research/umls/rxnorm",
     *                                  "code": 153165
     *                              }
     *                          }
     *                      },
     *                      "status": "active",
     *                      "batch": {
     *                          "lotNumber": "132",
     *                          "expirationDate": "0000-00-00"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/Medication/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirMedicationRestController())->getOne($uuid, $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "med");
            $return = (new FhirMedicationRestController())->getOne($uuid);
        }

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/MedicationDispense",
     *      description="Returns a list of MedicationDispense resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the MedicationDispense resource.",
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
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="The status of the MedicationDispense resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="The type of the MedicationDispense resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/MedicationDispense"
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

    /**
     *  @OA\Get(
     *      path="/fhir/MedicationDispense/{uuid}",
     *      description="Returns a single MedicationDispense resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the MedicationDispense resource.",
     *          required=true,
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
     *                      "id": "946da61d-9cff-4416-8d27-805f19f9d7d8",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-20T04:03:14+00:00"
     *                      },
     *                      "resourceType": "MedicationDispense",
     *                      "status": "completed",
     *                      "medicationCodeableConcept": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://www.nlm.nih.gov/research/umls/rxnorm",
     *                                  "code": "1738139",
     *                                  "display": "Acetaminophen 325 MG Oral Tablet"
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/946da617-1a4a-4b2c-ae66-93b84377cb1e",
     *                          "type": "Patient"
     *                      },
     *                      "context": {
     *                          "reference": "Encounter/946da61d-ac5f-4fdc-b3f2-7b58dc49976b",
     *                          "type": "Encounter"
     *                      },
     *                      "authorizingPrescription": {
     *                          {
     *                              "reference": "MedicationRequest/946da61d-ac5f-4fdc-b3f2-7b58dc49976b",
     *                              "type": "MedicationRequest"
     *                          }
     *                      },
     *                      "type": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/ValueSet/v3-ActPharmacySupplyType",
     *                                  "code": "FF",
     *                                  "display": "Final Fill"
     *                              }
     *                          }
     *                      },
     *                      "quantity": {
     *                          "value": 30,
     *                          "unit": "tablet"
     *                      },
     *                      "whenHandedOver": "2021-09-18T00:00:00+00:00"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/MedicationRequest",
     *      description="Returns a list of MedicationRequest resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the MedicationRequest resource.",
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
     *      @OA\Parameter(
     *          name="intent",
     *          in="query",
     *          description="The intent of the MedicationRequest resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="The status of the MedicationRequest resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/MedicationRequest"
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

    /**
     *  @OA\Get(
     *      path="/fhir/MedicationRequest/{uuid}",
     *      description="Returns a single MedicationRequest resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the MedicationRequest resource.",
     *          required=true,
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
     *                      "id": "946da61d-9cff-4416-8d27-805f19f9d7d8",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-20T04:03:14+00:00"
     *                      },
     *                      "resourceType": "MedicationRequest",
     *                      "status": "active",
     *                      "intent": "order",
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/medicationrequest-category",
     *                                      "code": "community",
     *                                      "display": "Home/Community"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "reportedBoolean": false,
     *                      "medicationCodeableConcept": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://www.nlm.nih.gov/research/umls/rxnorm",
     *                                  "code": "1738139",
     *                                  "display": "Acetaminophen 325 MG Oral Tablet"
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/946da617-1a4a-4b2c-ae66-93b84377cb1e",
     *                          "type": "Patient"
     *                      },
     *                      "authoredOn": "2021-09-18T00:00:00+00:00",
     *                      "requester": {
     *                          "reference": "Practitioner/946da61d-ac5f-4fdc-b3f2-7b58dc49976b",
     *                          "type": "Practitioner"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Observation",
     *      summary="Returns a list of Observation resources.",
     *      description="Returns a list of Observation resources. Returns the following types of Observation resources, Advance Directives, Care Experience Preferences, Occupation, Social Determinants of Health, Laboratory, Simple Observations, Social History, Questionnaire Responses, Treatment Intervention Preferences, Vital Signs.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Observation resource.",
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
     *      @OA\Parameter(
     *          name="code",
     *          in="query",
     *          description="The code of the Observation resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="The category of the Observation resource. Taken from one of these valid category codes http://terminology.hl7.org/CodeSystem/observation-category",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="The datetime of the Observation resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Observation"
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
    "GET /fhir/Observation" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirObservationService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getAll();
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Observation/{uuid}",
     *      description="Returns a single Observation resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Observation resource.",
     *          required=true,
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
     *                      "id": "946da61e-0597-485e-9dfd-a87205ea56b3",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-20T04:12:16+00:00"
     *                      },
     *                      "resourceType": "Observation",
     *                      "status": "final",
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/observation-category",
     *                                      "code": "vital-signs"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "code": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://loinc.org",
     *                                  "code": "85354-9",
     *                                  "display": "Blood pressure systolic and diastolic"
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/946da619-c631-431a-a282-487cd6fb7802",
     *                          "type": "Patient"
     *                      },
     *                      "effectiveDateTime": "2015-08-31T00:00:00+00:00",
     *                      "component": {
     *                          {
     *                              "code": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://loinc.org",
     *                                          "code": "8480-6",
     *                                          "display": "Systolic blood pressure"
     *                                      }
     *                                  }
     *                              },
     *                              "valueQuantity": {
     *                                  "value": 122,
     *                                  "unit": "mm[Hg]",
     *                                  "system": "http://unitsofmeasure.org",
     *                                  "code": "mm[Hg]"
     *                              }
     *                          },
     *                          {
     *                              "code": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://loinc.org",
     *                                          "code": "8462-4",
     *                                          "display": "Diastolic blood pressure"
     *                                      }
     *                                  }
     *                              },
     *                              "valueQuantity": {
     *                                  "value": 77,
     *                                  "unit": "mm[Hg]",
     *                                  "system": "http://unitsofmeasure.org",
     *                                  "code": "mm[Hg]"
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/Observation/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        $controller = new FhirGenericRestController($request, new FhirObservationService(), $globalsBag);
        $controller->addAclRestrictions("patients", "med");
        return $controller->getOne($uuid);
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Organization",
     *      description="Returns a list of Organization resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Organization resource.",
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
     *          name="name",
     *          in="query",
     *          description="The name of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="telecom",
     *          in="query",
     *          description="The telecom of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="The address of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-city",
     *          in="query",
     *          description="The address-city of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-postalcode",
     *          in="query",
     *          description="The address-postalcode of the Organization resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-state",
     *          in="query",
     *          description="The address-state of the Organization resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Organization"
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
    "GET /fhir/Organization" => function (HttpRestRequest $request) {
        if (!$request->isPatientRequest()) {
            RestConfig::request_authorization_check($request, "admin", "users");
        }
        $return = (new FhirOrganizationRestController())->getAll($request->getQueryParams());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Organization/{uuid}",
     *      description="Returns a single Organization resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Organization resource.",
     *          required=true,
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
     *                      "id": "95f0e672-be37-4c73-95c9-649c2d200018",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-03-30T07:43:23+00:00"
     *                      },
     *                      "resourceType": "Organization",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>"
     *                      },
     *                      "identifier": {
     *                          {
     *                              "system": "http://hl7.org/fhir/sid/us-npi",
     *                              "value": "1234567890"
     *                          }
     *                       },
     *                      "active": true,
     *                      "type": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/organization-type",
     *                                      "code": "prov",
     *                                      "display": "Healthcare Provider"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "name": "Your Clinic Name Here",
     *                      "telecom": {
     *                          {
     *                              "system": "phone",
     *                              "value": "000-000-0000",
     *                             "use": "work"
     *                          },
     *                          {
     *                              "system": "fax",
     *                              "value": "000-000-0000",
     *                              "use": "work"
     *                          }
     *                      },
     *                      "address": {
     *                          null
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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
    /**
     *  @OA\Get(
     *      path="/fhir/Specimen",
     *      description="Returns a list of Specimen resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Specimen resource.",
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
     *      @OA\Parameter(
     *          name="accession",
     *          in="query",
     *          description="The accession identifier of the Specimen resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="The type of the Specimen resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="collected",
     *          in="query",
     *          description="The collection datetime of the Specimen resource.",
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
     *                          "lastUpdated": "2025-10-10T09:13:51"
     *                      },
     *                      "resourceType": "Bundle",
     *                      "type": "collection",
     *                      "total": 0,
     *                      "link": {
     *                          {
     *                              "relation": "self",
     *                              "url": "https://localhost:9300/apis/default/fhir/Specimen"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Specimen/{uuid}",
     *      description="Returns a single Specimen resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Specimen resource.",
     *          required=true,
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
     *                      "id": "95e9d3fb-fe7b-448a-aa60-d40b11b486a5",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2025-10-10T17:20:14+00:00"
     *                      },
     *                      "resourceType": "Specimen",
     *                      "identifier": {
     *                          {
     *                              "system": "https://example.org/specimen-id",
     *                              "value": "SPEC-2025-001"
     *                          }
     *                      },
     *                      "accessionIdentifier": {
     *                          "system": "https://example.org/accession",
     *                          "value": "ACC-2025-12345"
     *                      },
     *                      "status": "available",
     *                      "type": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://snomed.info/sct",
     *                                  "code": "122555007",
     *                                  "display": "Venous blood specimen"
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf",
     *                          "type": "Patient"
     *                      },
     *                      "receivedTime": "2025-10-10T10:30:00+00:00",
     *                      "collection": {
     *                          "collectedDateTime": "2025-10-10T09:00:00+00:00",
     *                          "quantity": {
     *                              "value": 10,
     *                              "unit": "mL",
     *                              "system": "http://unitsofmeasure.org",
     *                              "code": "mL"
     *                          },
     *                          "bodySite": {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://snomed.info/sct",
     *                                      "code": "368208006",
     *                                      "display": "Left arm"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "container": {
     *                          {
     *                              "type": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://snomed.info/sct",
     *                                          "code": "702281005",
     *                                          "display": "Evacuated blood collection tube with heparin"
     *                                      }
     *                                  }
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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
    /**
     *  @OA\Post(
     *      path="/fhir/Organization",
     *      description="Adds a Organization resource.",
     *      tags={"fhir"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  description="The json object for the Organization resource.",
     *                  type="object"
     *              ),
     *              example={
     *                  "id": "95f0e672-be37-4c73-95c9-649c2d200018",
     *                  "meta": {
     *                      "versionId": "1",
     *                      "lastUpdated": "2022-03-30T07:43:23+00:00"
     *                  },
     *                  "resourceType": "Organization",
     *                  "text": {
     *                      "status": "generated",
     *                      "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>"
     *                  },
     *                  "identifier": {
     *                      {
     *                          "system": "http://hl7.org/fhir/sid/us-npi",
     *                          "value": "1234567890"
     *                      }
     *                   },
     *                  "active": true,
     *                  "type": {
     *                      {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/organization-type",
     *                                  "code": "prov",
     *                                  "display": "Healthcare Provider"
     *                              }
     *                          }
     *                      }
     *                  },
     *                  "name": "Your Clinic Name Here Hey",
     *                  "telecom": {
     *                      {
     *                          "system": "phone",
     *                          "value": "000-000-0000",
     *                          "use": "work"
     *                      },
     *                      {
     *                          "system": "fax",
     *                          "value": "000-000-0000",
     *                          "use": "work"
     *                      }
     *                  },
     *                  "address": {
     *                      null
     *                  }
     *              }
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
     *                      "id": "95f0e672-be37-4c73-95c9-649c2d200018",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-03-30T07:43:23+00:00"
     *                      },
     *                      "resourceType": "Organization",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>"
     *                      },
     *                      "identifier": {
     *                          {
     *                              "system": "http://hl7.org/fhir/sid/us-npi",
     *                              "value": "1234567890"
     *                          }
     *                       },
     *                      "active": true,
     *                      "type": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/organization-type",
     *                                      "code": "prov",
     *                                      "display": "Healthcare Provider"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "name": "Your Clinic Name Here Now",
     *                      "telecom": {
     *                          {
     *                              "system": "phone",
     *                              "value": "000-000-0000",
     *                             "use": "work"
     *                          },
     *                          {
     *                              "system": "fax",
     *                              "value": "000-000-0000",
     *                              "use": "work"
     *                          }
     *                      },
     *                      "address": {
     *                          null
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
    "POST /fhir/Organization" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->post($data);

        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/fhir/Organization/{uuid}",
     *      description="Modifies a Organization resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the organization.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  description="The json object for the Organization resource.",
     *                  type="object"
     *              ),
     *              example={
     *                  "id": "95f0e672-be37-4c73-95c9-649c2d200018",
     *                  "meta": {
     *                      "versionId": "1",
     *                      "lastUpdated": "2022-03-30T07:43:23+00:00"
     *                  },
     *                  "resourceType": "Organization",
     *                  "text": {
     *                      "status": "generated",
     *                      "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>"
     *                  },
     *                  "identifier": {
     *                      {
     *                          "system": "http://hl7.org/fhir/sid/us-npi",
     *                          "value": "1234567890"
     *                      }
     *                   },
     *                  "active": true,
     *                  "type": {
     *                      {
     *                          "coding": {
     *                              {
     *                                  "system": "http://terminology.hl7.org/CodeSystem/organization-type",
     *                                  "code": "prov",
     *                                  "display": "Healthcare Provider"
     *                              }
     *                          }
     *                      }
     *                  },
     *                  "name": "Your Clinic Name Here",
     *                  "telecom": {
     *                      {
     *                          "system": "phone",
     *                          "value": "000-000-0000",
     *                          "use": "work"
     *                      },
     *                      {
     *                          "system": "fax",
     *                          "value": "000-000-0000",
     *                          "use": "work"
     *                      }
     *                  },
     *                  "address": {
     *                      null
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="201",
     *          description="Standard Response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  example={
     *                      "id": 14,
     *                      "uuid": "95f217c1-258c-44ca-bf11-909dce369574"
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
    "PUT /fhir/Organization/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->patch($uuid, $data);

        return $return;
    },

    /**
     *  @OA\Post(
     *      path="/fhir/Patient",
     *      description="Adds a Patient resource.",
     *      tags={"fhir"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  description="The json object for the Patient resource.",
     *                  type="object"
     *              ),
     *              example={
     *                  "id": "95f22ff4-dd25-4290-8b52-1dd2fedf8e54",
     *                  "meta": {
     *                      "versionId": "1",
     *                      "lastUpdated": "2022-03-31T02:48:28+00:00"
     *                  },
     *                  "resourceType": "Patient",
     *                  "text": {
     *                      "status": "generated",
     *                      "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Brenda Smith</p></div>"
     *                  },
     *                  "extension": {
     *                      {
     *                          "valueCode": "F",
     *                          "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex"
     *                      },
     *                      {
     *                          "extension": {
     *                              {
     *                                  "valueCoding": {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/v3-NullFlavor",
     *                                      "code": "UNK",
     *                                      "display": "Unknown"
     *                                  },
     *                                  "url": "ombCategory"
     *                              },
     *                              {
     *                                  "valueString": "Unknown",
     *                                  "url": "text"
     *                              }
     *                          },
     *                          "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-race"
     *                      }
     *                  },
     *                  "identifier": {
     *                      {
     *                          "use": "official",
     *                          "type": {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                                      "code": "PT"
     *                                  }
     *                              }
     *                          },
     *                         "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                         "value": "1"
     *                      }
     *                  },
     *                  "active": true,
     *                  "name": {
     *                      {
     *                          "use": "official",
     *                          "family": "Smith",
     *                          "given": {
     *                              "Brenda"
     *                          }
     *                      }
     *                  },
     *                  "gender": "female",
     *                  "birthDate": "2017-03-10",
     *                  "communication": {
     *                      {
     *                          "language": {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/data-absent-reason",
     *                                      "code": "unknown",
     *                                      "display": "Unknown"
     *                                  }
     *                              }
     *                          }
     *                      }
     *                  }
     *              }
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
     *                      "id": "95f22ff4-dd25-4290-8b52-1dd2fedf8e54",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-03-31T02:48:28+00:00"
     *                      },
     *                      "resourceType": "Patient",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Brenda Smith</p></div>"
     *                      },
     *                      "extension": {
     *                          {
     *                              "valueCode": "F",
     *                              "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex"
     *                          },
     *                          {
     *                              "extension": {
     *                                  {
     *                                      "valueCoding": {
     *                                          "system": "http://terminology.hl7.org/CodeSystem/v3-NullFlavor",
     *                                          "code": "UNK",
     *                                          "display": "Unknown"
     *                                      },
     *                                      "url": "ombCategory"
     *                                  },
     *                                  {
     *                                      "valueString": "Unknown",
     *                                      "url": "text"
     *                                  }
     *                              },
     *                              "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-race"
     *                          }
     *                      },
     *                      "identifier": {
     *                          {
     *                              "use": "official",
     *                              "type": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                                          "code": "PT"
     *                                      }
     *                                  }
     *                              },
     *                             "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                             "value": "1"
     *                          }
     *                      },
     *                      "active": true,
     *                      "name": {
     *                          {
     *                              "use": "official",
     *                              "family": "Smith",
     *                              "given": {
     *                                  "Brenda"
     *                              }
     *                          }
     *                      },
     *                      "gender": "female",
     *                      "birthDate": "2017-03-10",
     *                      "communication": {
     *                          {
     *                              "language": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://terminology.hl7.org/CodeSystem/data-absent-reason",
     *                                          "code": "unknown",
     *                                          "display": "Unknown"
     *                                      }
     *                                  }
     *                              }
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
    "POST /fhir/Patient" => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $restController = new FhirPatientRestController();
        $restController->setOEGlobals($globalsBag);
        $return = $restController->post($data);

        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/fhir/Patient/{uuid}",
     *      description="Modifies a Patient resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Patient resource.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  description="The json object for the Patient resource.",
     *                  type="object"
     *              ),
     *              example={
     *                  "id": "95f22ff4-dd25-4290-8b52-1dd2fedf8e54",
     *                  "meta": {
     *                      "versionId": "1",
     *                      "lastUpdated": "2022-03-31T02:48:28+00:00"
     *                  },
     *                  "resourceType": "Patient",
     *                  "text": {
     *                      "status": "generated",
     *                      "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Brenda Smith</p></div>"
     *                  },
     *                  "extension": {
     *                      {
     *                          "valueCode": "F",
     *                          "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex"
     *                      },
     *                      {
     *                          "extension": {
     *                              {
     *                                  "valueCoding": {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/v3-NullFlavor",
     *                                      "code": "UNK",
     *                                      "display": "Unknown"
     *                                  },
     *                                  "url": "ombCategory"
     *                              },
     *                              {
     *                                  "valueString": "Unknown",
     *                                  "url": "text"
     *                              }
     *                          },
     *                          "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-race"
     *                      }
     *                  },
     *                  "identifier": {
     *                      {
     *                          "use": "official",
     *                          "type": {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                                      "code": "PT"
     *                                  }
     *                              }
     *                          },
     *                         "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                         "value": "1"
     *                      }
     *                  },
     *                  "active": true,
     *                  "name": {
     *                      {
     *                          "use": "official",
     *                          "family": "Smith",
     *                          "given": {
     *                              "Brenda"
     *                          }
     *                      }
     *                  },
     *                  "gender": "female",
     *                  "birthDate": "2017-03-10",
     *                  "communication": {
     *                      {
     *                          "language": {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://terminology.hl7.org/CodeSystem/data-absent-reason",
     *                                      "code": "unknown",
     *                                      "display": "Unknown"
     *                                  }
     *                              }
     *                          }
     *                      }
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="201",
     *          description="Standard Response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  example={
     *                      "id": 2,
     *                      "uuid": "95f2ad04-5834-4243-8838-e396a7faadbf"
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
    "PUT /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $restController = new FhirPatientRestController();
        $restController->setOEGlobals($globalsBag);
        $return = $restController->put($uuid, $data);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Patient",
     *      description="Returns a list of Patient resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Patient resource.",
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
     *          name="identifier",
     *          in="query",
     *          description="The identifier of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="The name of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="birthdate",
     *          in="query",
     *          description="The birthdate of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="gender",
     *          in="query",
     *          description="The gender of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="The address of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-city",
     *          in="query",
     *          description="The address-city of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-postalcode",
     *          in="query",
     *          description="The address-postalcode of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *          type="string"
     *      )
     *      ),
     *      @OA\Parameter(
     *          name="address-state",
     *          in="query",
     *          description="The address-state of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="family",
     *          in="query",
     *          description="The family name of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="given",
     *          in="query",
     *          description="The given name of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone number of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="telecom",
     *          in="query",
     *          description="The fax number of the Patient resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *        ref="#/components/parameters/_lastUpdated"
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Patient"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Patient/$export",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Patient/{uuid}",
     *      description="Returns a single Patient resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Patient resource.",
     *          required=true,
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
     *                      "id": "946da617-1a4a-4b2c-ae66-93b84377cb1e",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-21T17:08:03+00:00"
     *                      },
     *                      "resourceType": "Patient",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns=""http://www.w3.org/1999/xhtml""> <p>Aurore252 Von197</p></div>"
     *                      },
     *                      "extension": {
     *                          {
     *                              "valueCode": "F",
     *                              "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex"
     *                          },
     *                          {
     *                              "extension": {
     *                                  {
     *                                      "valueCoding": {
     *                                          "system": "urn:oid:2.16.840.1.113883.6.238",
     *                                          "code": "1006-6",
     *                                          "display": "Abenaki"
     *                                      },
     *                                      "url": "ombCategory"
     *                                  },
     *                                  {
     *                                      "valueString": "Abenaki",
     *                                      "url": "text"
     *                                  }
     *                              },
     *                              "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-race"
     *                          },
     *                          {
     *                              "extension": {
     *                                  {
     *                                      "valueString": "Declined To Specify",
     *                                      "url": "text"
     *                                  }
     *                              },
     *                              "url": "http://hl7.org/fhir/us/core/StructureDefinition/us-core-ethnicity"
     *                          }
     *                      },
     *                      "identifier": {
     *                          {
     *                              "use": "official",
     *                              "type": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                                          "code": "PT"
     *                                      }
     *                                  }
     *                              },
     *                              "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
     *                              "value": "1"
     *                          }
     *                      },
     *                      "active": true,
     *                      "name": {
     *                          {
     *                              "use": "official",
     *                              "family": "Von197",
     *                              "given": {
     *                                  "Aurore252"
     *                              }
     *                          }
     *                      },
     *                      "gender": "female",
     *                      "birthDate": "1970-07-03",
     *                      "address": {
     *                          {
     *                              "line": {
     *                                  "245 Crona Wall"
     *                              },
     *                              "city": "Boston",
     *                              "state": "Massachusetts",
     *                              "postalCode": "02215",
     *                              "period": {
     *                                  "start": "2020-09-21T17:08:03.532+00:00"
     *                              }
     *                          }
     *                      },
     *                      "communication": {
     *                          {
     *                              "language": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://terminology.hl7.org/CodeSystem/data-absent-reason",
     *                                          "code": "unknown",
     *                                          "display": "Unknown"
     *                                      }
     *                                  }
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Person",
     *      description="Returns a list of Person resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Person resource.",
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
     *          name="name",
     *          in="query",
     *          description="The name of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="active",
     *          in="query",
     *          description="The active status of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="The address of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-city",
     *          in="query",
     *          description="The address-city of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-postalcode",
     *          in="query",
     *          description="The address-postalcode of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-state",
     *          in="query",
     *          description="The address-state of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="family",
     *          in="query",
     *          description="The family name of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="given",
     *          in="query",
     *          description="The given name of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone number of the Person resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="telecom",
     *          in="query",
     *          description="The fax number of the Person resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Person"
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
    "GET /fhir/Person" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPersonRestController())->getAll($request->getQueryParams());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Person/{uuid}",
     *      description="Returns a single Person resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Person resource.",
     *          required=true,
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
     *                      "id": "960c7cd6-187a-4119-8cd4-85389d80efb9",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-04-13T08:57:32+00:00"
     *                      },
     *                      "resourceType": "Person",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Administrator Administrator</p></div>"
     *                      },
     *                      "name": {
     *                          {
     *                              "use": "official",
     *                              "family": "Administrator",
     *                              "given": {
     *                                  "Administrator",
     *                                  "Larry"
     *                              }
     *                          }
     *                      },
     *                      "telecom": {
     *                          {
     *                              "system": "phone",
     *                              "value": "1234567890",
     *                              "use": "home"
     *                          },
     *                          {
     *                              "system": "phone",
     *                              "value": "1234567890",
     *                              "use": "work"
     *                          },
     *                          {
     *                              "system": "phone",
     *                              "value": "1234567890",
     *                              "use": "mobile"
     *                          },
     *                          {
     *                              "system": "email",
     *                              "value": "hey@hey.com",
     *                              "use": "home"
     *                          }
     *                      },
     *                      "address": {
     *                          {
     *                              "line": {
     *                                  "123 Lane Street"
     *                              },
     *                              "city": "Bellevue",
     *                              "state": "WA",
     *                              "period": {
     *                                  "start": "2021-04-13T08:57:32.146+00:00"
     *                              }
     *                          }
     *                      },
     *                      "active": true
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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
            $return = (new FhirPersonRestController())->getOne($uuid, $request->getPatientUUIDString());
        }


        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Practitioner",
     *      description="Returns a list of Practitioner resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Practitioner resource.",
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
     *          name="name",
     *          in="query",
     *          description="The name of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="active",
     *          in="query",
     *          description="The active status of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="The address of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-city",
     *          in="query",
     *          description="The address-city of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-postalcode",
     *          in="query",
     *          description="The address-postalcode of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address-state",
     *          in="query",
     *          description="The address-state of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="family",
     *          in="query",
     *          description="The family name of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="given",
     *          in="query",
     *          description="The given name of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone number of the Practitioner resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="telecom",
     *          in="query",
     *          description="The fax number of the Practitioner resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Practitioner"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Practitioner/{uuid}",
     *      description="Returns a single Practitioner resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Practitioner resource.",
     *          required=true,
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
     *                      "id": "9473b0cf-e969-4eaa-8044-51037767fa4f",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-21T17:41:57+00:00"
     *                      },
     *                      "resourceType": "Practitioner",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns=""http://www.w3.org/1999/xhtml""> <p>Billy Smith</p></div>"
     *                      },
     *                      "identifier": {
     *                          {
     *                              "system": "http://hl7.org/fhir/sid/us-npi",
     *                              "value": "11223344554543"
     *                          }
     *                      },
     *                      "active": true,
     *                      "name": {
     *                          {
     *                              "use": "official",
     *                              "family": "Smith",
     *                              "given": {
     *                                  "Billy"
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Post(
     *      path="/fhir/Practitioner",
     *      description="Adds a Practitioner resources.",
     *      tags={"fhir"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  description="The json object for the Practitioner resource.",
     *                  type="object"
     *              ),
     *              example={
     *                  "id": "9473b0cf-e969-4eaa-8044-51037767fa4f",
     *                  "meta": {
     *                      "versionId": "1",
     *                      "lastUpdated": "2021-09-21T17:41:57+00:00"
     *                  },
     *                  "resourceType": "Practitioner",
     *                  "text": {
     *                      "status": "generated",
     *                      "div": "<div xmlns=""http://www.w3.org/1999/xhtml""> <p>Billy Smith</p></div>"
     *                  },
     *                  "identifier": {
     *                      {
     *                          "system": "http://hl7.org/fhir/sid/us-npi",
     *                          "value": "11223344554543"
     *                      }
     *                  },
     *                  "active": true,
     *                  "name": {
     *                      {
     *                          "use": "official",
     *                          "family": "Smith",
     *                          "given": {
     *                              "Danny"
     *                          }
     *                      }
     *                  }
     *              }
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
     *                      "id": "9473b0cf-e969-4eaa-8044-51037767fa4f",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2021-09-21T17:41:57+00:00"
     *                      },
     *                      "resourceType": "Practitioner",
     *                      "text": {
     *                          "status": "generated",
     *                          "div": "<div xmlns=""http://www.w3.org/1999/xhtml""> <p>Billy Smith</p></div>"
     *                      },
     *                      "identifier": {
     *                          {
     *                              "system": "http://hl7.org/fhir/sid/us-npi",
     *                              "value": "11223344554543"
     *                          }
     *                      },
     *                      "active": true,
     *                      "name": {
     *                          {
     *                              "use": "official",
     *                              "family": "Smith",
     *                              "given": {
     *                                  "Danny"
     *                              }
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
    "POST /fhir/Practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->post($data);

        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/fhir/Practitioner/{uuid}",
     *      description="Modify a Practitioner resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Practitioner resource.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  description="The json object for the Practitioner resource.",
     *                  type="object"
     *              ),
     *              example={
     *                  "id": "9473b0cf-e969-4eaa-8044-51037767fa4f",
     *                  "meta": {
     *                      "versionId": "1",
     *                      "lastUpdated": "2021-09-21T17:41:57+00:00"
     *                  },
     *                  "resourceType": "Practitioner",
     *                  "text": {
     *                      "status": "generated",
     *                      "div": "<div xmlns=""http://www.w3.org/1999/xhtml""> <p>Billy Smith</p></div>"
     *                  },
     *                  "identifier": {
     *                      {
     *                          "system": "http://hl7.org/fhir/sid/us-npi",
     *                          "value": "11223344554543"
     *                      }
     *                  },
     *                  "active": true,
     *                  "name": {
     *                      {
     *                          "use": "official",
     *                          "family": "Smith",
     *                          "given": {
     *                              "Billy"
     *                          }
     *                      }
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="201",
     *          description="Standard Response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  example={
     *                      "id": 5,
     *                      "uuid": "95f294d7-e14c-441d-81a6-309fe369ee21"
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
    "PUT /fhir/Practitioner/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->patch($uuid, $data);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/PractitionerRole",
     *      description="Returns a list of PractitionerRole resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the PractitionerRole resource.",
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
     *          name="specialty",
     *          in="query",
     *          description="The specialty of the PractitionerRole resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="practitioner",
     *          in="query",
     *          description="The practitioner of the PractitionerRole resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/PractitionerRole"
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
    "GET /fhir/PractitionerRole" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getAll($request->getQueryParams());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/PractitionerRole/{uuid}",
     *      description="Returns a single PractitionerRole resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the PractitionerRole resource.",
     *          required=true,
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
     *                      "id": "960c806f-9463-482e-b228-67b5be1fed55",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-04-13T06:18:17+00:00"
     *                      },
     *                      "resourceType": "PractitionerRole",
     *                      "practitioner": {
     *                          "reference": "Practitioner/960c7cd6-187a-4119-8cd4-85389d80efb9",
     *                          "display": "Administrator Administrator"
     *                      },
     *                      "organization": {
     *                          "reference": "Organization/960c7cc6-b4ae-49bc-877b-1a2913271c43",
     *                          "display": "Your Clinic Name Here"
     *                      },
     *                      "code": {
     *                          {
     *                              "coding": {
     *                                  "102L00000X"
     *                              },
     *                              "text": "Psychoanalyst"
     *                          },
     *                          {
     *                              "coding": {
     *                                  "101Y00000X"
     *                              },
     *                              "text": "Counselor"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/PractitionerRole/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getOne($uuid);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Procedure",
     *      description="Returns a list of Procedure resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the Procedure resource.",
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
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="The datetime of the Procedure resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Procedure"
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

    /**
     *  @OA\Get(
     *      path="/fhir/RelatedPerson",
     *      description="Returns a list of RelatedPerson resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the RelatedPerson resource.",
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
     *                          "lastUpdated": "2025-09-30T09:13:51"
     *                      },
     *                      "resourceType": "Bundle",
     *                      "type": "collection",
     *                      "total": 0,
     *                      "link": {
     *                          {
     *                              "relation": "self",
     *                              "url": "https://localhost:9300/apis/default/fhir/RelatedPerson"
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
    "GET /fhir/RelatedPerson" => function (HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            $return = (new FhirRelatedPersonRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
            $return = (new FhirRelatedPersonRestController())->getAll($request->getQueryParams());
        }
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/RelatedPerson/{uuid}",
     *      description="Returns a single RelatedPerson resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the RelatedPerson resource.",
     *          required=true,
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
     *                       "id": "c266a919-6b22-4d7b-b169-b96adc5be3ef",
     *                       "meta": {
     *                           "versionId": "1",
     *                           "lastUpdated": "2025-10-25T21:15:11-04:00",
     *                           "profile": [
     *                               "http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson",
     *                               "http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson|7.0.0",
     *                               "http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson|8.0.0"
     *                           ]
     *                       },
     *                       "resourceType": "RelatedPerson",
     *                       "active": true,
     *                       "patient": {
     *                           "reference": "Patient/96506861-511f-4f6d-bc97-b65a78cf1995",
     *                           "type": "Patient"
     *                       },
     *                       "relationship": [
     *                           {
     *                               "coding": [
     *                                   {
     *                                       "system": "http://terminology.hl7.org/CodeSystem/role-code",
     *                                       "code": "FAMMEMB",
     *                                       "display": "Family Member"
     *                                   }
     *                               ]
     *                           }
     *                       ],
     *                       "name": [
     *                           {
     *                               "use": "official",
     *                               "family": "Doe",
     *                               "given": [
     *                                   "John"
     *                               ]
     *                           }
     *                       ],
     *                       "telecom": [
     *                           {
     *                               "system": "phone",
     *                               "value": "(555) 555-5555",
     *                               "use": "work"
     *                           },
     *                           {
     *                               "system": "phone",
     *                               "value": "(333) 333-3333",
     *                               "use": "home"
     *                           },
     *                           {
     *                               "system": "email",
     *                               "value": "example@open-emr.org",
     *                               "use": "home"
     *                           }
     *                       ],
     *                       "address": [
     *                           {
     *                               "line": [
     *                                   "123 example street"
     *                               ],
     *                               "city": "Somewhere",
     *                               "state": "CA",
     *                               "period": {
     *                                   "start": "2024-10-25T21:15:11.737-04:00"
     *                               }
     *                           }
     *                       ]
     *                   }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/RelatedPerson/:uuid" => function (string $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // resource is part of the patient compartment so will be bound to patient anyways
            $return = (new FhirRelatedPersonRestController())->getOne($uuid);
        } else {
            RestConfig::request_authorization_check($request, "patients", "demo");
            $return = (new FhirRelatedPersonRestController())->getOne($uuid);
        }
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/ServiceRequest",
     *      description="Returns a list of ServiceRequest resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the ServiceRequest resource.",
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
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="The category/type of the ServiceRequest (laboratory, imaging, etc).",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="code",
     *          in="query",
     *          description="The code of the ServiceRequest resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="authored",
     *          in="query",
     *          description="The authored date of the ServiceRequest resource.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="The status of the ServiceRequest resource.",
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
     *                          "lastUpdated": "2025-09-30T09:13:51"
     *                      },
     *                      "resourceType": "Bundle",
     *                      "type": "collection",
     *                      "total": 0,
     *                      "link": {
     *                          {
     *                              "relation": "self",
     *                              "url": "https://localhost:9300/apis/default/fhir/ServiceRequest"
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

    /**
     *  @OA\Get(
     *      path="/fhir/ServiceRequest/{uuid}",
     *      description="Returns a single ServiceRequest resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the ServiceRequest resource.",
     *          required=true,
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
     *                      "id": "95e9d3fb-fe7b-448a-aa60-d40b11b486a5",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2025-03-26T17:20:14+00:00"
     *                      },
     *                      "resourceType": "ServiceRequest",
     *                      "status": "active",
     *                      "intent": "order",
     *                      "category": {
     *                          {
     *                              "coding": {
     *                                  {
     *                                      "system": "http://snomed.info/sct",
     *                                      "code": "108252007",
     *                                      "display": "Laboratory procedure"
     *                                  }
     *                              }
     *                          }
     *                      },
     *                      "code": {
     *                          "coding": {
     *                              {
     *                                  "system": "http://loinc.org",
     *                                  "code": "24356-8",
     *                                  "display": "Urinalysis complete"
     *                              }
     *                          }
     *                      },
     *                      "subject": {
     *                          "reference": "Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf",
     *                          "type": "Patient"
     *                      },
     *                      "authoredOn": "2025-03-26T00:00:00+00:00"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Procedure/{uuid}",
     *      description="Returns a single Procedure resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the Procedure resource.",
     *          required=true,
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
     *                      "id": "95e9d3fb-fe7b-448a-aa60-d40b11b486a5",
     *                      "meta": {
     *                          "versionId": "1",
     *                          "lastUpdated": "2022-03-26T17:20:14+00:00"
     *                      },
     *                      "resourceType": "Procedure",
     *                      "status": "in-progress",
     *                      "subject": {
     *                          "reference": "Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf",
     *                          "type": "Patient"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Provenance/{uuid}",
     *      description="Returns a single Provenance resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The id for the Provenance resource. Format is \<resource name\>:\<uuid\> (Example: AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145).",
     *          required=true,
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
     *                      "id": "AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145",
     *                      "resourceType": "Provenance",
     *                      "target": {
     *                          {
     *                              "reference": "AllergyIntolerance/95ea43f3-1066-4bc7-b224-6c23b985f145",
     *                              "type": "AllergyIntolerance"
     *                          }
     *                      },
     *                      "recorded": "2022-03-26T22:43:30+00:00",
     *                      "agent": {
     *                          {
     *                              "type": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://terminology.hl7.org/CodeSystem/provenance-participant-type",
     *                                          "code": "author",
     *                                          "display": "Author"
     *                                      }
     *                                  }
     *                              },
     *                              "who": {
     *                                  "reference": "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
     *                                  "type": "Organization"
     *                              },
     *                              "onBehalfOf": {
     *                                  "reference": "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
     *                                  "type": "Organization"
     *                              }
     *                          },
     *                          {
     *                              "type": {
     *                                  "coding": {
     *                                      {
     *                                          "system": "http://hl7.org/fhir/us/core/CodeSystem/us-core-provenance-participant-type",
     *                                          "code": "transmitter",
     *                                          "display": "Transmitter"
     *                                      }
     *                                  }
     *                              }
     *                          },
     *                          "who": {
     *                              "reference": "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
     *                              "type": "Organization"
     *                          },
     *                          "onBehalfOf": {
     *                              "reference": "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
     *                              "type": "Organization"
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/fhir/Provenance",
     *      description="Returns a list of Provenance resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The id for the Provenance resource. Format is \<resource name\>:\<uuid\> (Example: AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145).",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Provenance"
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

    /**
     *  @OA\Get(
     *      path="/fhir/Questionnaire",
     *      description="Returns a list of Questionnaire resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The id for the Questionnaire resource. ",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/Questionnaire"
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
    "GET /fhir/Questionnaire" => function (HttpRestRequest $request) {
        $logger = new SystemLogger();
        $fhirQuestionnaireService = new FhirQuestionnaireService();
        $fhirFormService = new FhirQuestionnaireFormService();
        $fhirQuestionnaireService->addMappedService($fhirFormService);
        $return = (new FhirQuestionnaireRestController($logger, $fhirQuestionnaireService))->list($request);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/QuestionnaireResponse",
     *      description="Returns a list of QuestionnaireResponse resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The id for the QuestionnaireResponse resource. ",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/QuestionnaireResponse"
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
    "GET /fhir/QuestionnaireResponse" => function (HttpRestRequest $request) {
        $fhirQuestionnaireService = new FhirQuestionnaireResponseService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireResponseFormService());
        $return = (new FhirQuestionnaireResponseRestController($fhirQuestionnaireService))->list($request);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/QuestionnaireResponse/{uuid}",
     *      description="Returns a single QuestionnaireResponse resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *           name="uuid",
     *           in="path",
     *           description="The id for the QuestionnaireResponse resource. Format is \<resource name\>:\<uuid\> (Example: AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145).",
     *           required=true,
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *                              "url": "https://localhost:9300/apis/default/fhir/QuestionnaireResponse"
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
    "GET /fhir/QuestionnaireResponse/:uuid" => function (string $uuid, HttpRestRequest $request) {
        $fhirQuestionnaireService = new FhirQuestionnaireResponseService();
        $fhirQuestionnaireService->addMappedService(new FhirQuestionnaireResponseFormService());
        $return = (new FhirQuestionnaireResponseRestController($fhirQuestionnaireService))->one($request, $uuid);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/ValueSet",
     *      description="Returns a list of ValueSet resources.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="_id",
     *          in="query",
     *          description="The uuid for the ValueSet resource.",
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
     *                              "url": "https://localhost:9300/apis/default/fhir/ValueSet"
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
    "GET /fhir/ValueSet" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $return = (new FhirValueSetRestController())->getAll($request->getQueryParams());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/ValueSet/{uuid}",
     *      description="Returns a single ValueSet resource.",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the ValueSet resource.",
     *          required=true,
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
     *                      "resourceType": "ValueSet",
     *                      "id": "appointment-type",
     *                      "compose": {
     *                          "include": {
     *                              {
     *                                  "concept": {
     *                                      {
     *                                          "code": "no_show",
     *                                          "display": "No Show"
     *                                      },
     *                                      {
     *                                          "code": "office_visit",
     *                                          "display": "Office Visit"
     *                                      },
     *                                      {
     *                                          "code": "established_patient",
     *                                          "display": "Established Patient"
     *                                      },
     *                                      {
     *                                          "code": "new_patient",
     *                                          "display": "New Patient"
     *                                      },
     *                                      {
     *                                          "code": "health_and_behavioral_assessment",
     *                                          "display": "Health and Behavioral Assessment"
     *                                      },
     *                                      {
     *                                          "code": "preventive_care_services",
     *                                          "display": "Preventive Care Services"
     *                                      },
     *                                      {
     *                                          "code": "ophthalmological_services",
     *                                          "display": "Ophthalmological Services"
     *                                      }
     *                                  }
     *                              }
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
     *      @OA\Response(
     *          response="404",
     *          ref="#/components/responses/uuidnotfound"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /fhir/ValueSet/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $return = (new FhirValueSetRestController())->getOne($uuid);

        return $return;
    },

    // other endpoints

    /**
     *  @OA\Get(
     *      path="/fhir/metadata",
     *      description="Returns metadata (ie. CapabilityStatement resource) of the fhir server.",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="Return CapabilityStatement resource of the fhir server"
     *      )
     *  )
     */
    "GET /fhir/metadata" => function (\OpenEMR\Common\Http\HttpRestRequest $request) {
        $return = (new FhirMetaDataRestController())->getMetaData();
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/.well-known/smart-configuration",
     *      description="Returns smart configuration of the fhir server.",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="Return smart configuration of the fhir server"
     *      )
     *  )
     */
    "GET /fhir/.well-known/smart-configuration" => function () {
        $return = (new SMARTConfigurationController())->getConfig();

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/OperationDefinition",
     *      description="Returns a list of the OperationDefinition resources that are specific to this OpenEMR installation",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="Return list of OperationDefinition resources"
     *      )
     *  )
     */
    "GET /fhir/OperationDefinition" => function (HttpRestRequest $request) {
        // for now we will just hard code the custom resources
        $operationDefinitionController = new FhirOperationDefinitionRestController();
        $return = $operationDefinitionController->getAll($request->getQueryParams());

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/OperationDefinition/{operation}",
     *      description="Returns a single OperationDefinition resource that is specific to this OpenEMR installation",
     *      tags={"fhir"},
     *      @OA\Parameter(
     *          name="operation",
     *          in="path",
     *          description="The name of the operation to query. For example $bulkdata-status",
     *          required=true,
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
     *                      "resourceType": "OperationDefinition",
     *                      "name": "$bulkdata-status",
     *                      "status": "active",
     *                      "kind": "operation",
     *                      "parameter": {
     *                      {
     *                          "name": "job",
     *                          "use": "in",
     *                          "min": 1,
     *                          "max": 1,
     *                          "type": {
     *                              "system": "http://hl7.org/fhir/data-types",
     *                              "code": "string",
     *                              "display": "string"
     *                          },
     *                          "searchType": {
     *                              "system": "http://hl7.org/fhir/ValueSet/search-param-type",
     *                              "code": "string",
     *                              "display": "string"
     *                          }
     *                      }
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *  )
     */
    "GET /fhir/OperationDefinition/:operation" => function ($operation, HttpRestRequest $request) {
        // for now we will just hard code the custom resources
        $operationDefinitionController = new FhirOperationDefinitionRestController();
        $return = $operationDefinitionController->getOne($operation);

        return $return;
    },

    // FHIR root level operations

    /**
     *  @OA\Get(
     *      path="/fhir/$export",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
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

    /**
     *  @OA\Get(
     *      path="/fhir/$bulkdata-status",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
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
    'GET /fhir/$bulkdata-status' => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $jobUuidString = $request->getQueryParam('job');
        // if we were truly async we would return 202 here to say we are in progress with a JSON response
        // since OpenEMR data is so small we just return the JSON from the database
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $return = $fhirExportService->processExportStatusRequestForJob($jobUuidString);

        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/fhir/$bulkdata-status",
     *      description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>",
     *      tags={"fhir"},
     *      @OA\Response(
     *          response="200",
     *          description="The BULK FHIR Exports documentation can be found at <a href='https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API' target='_blank' rel='noopener'>https://www.open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#API</a>"
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
    'DELETE /fhir/$bulkdata-status' => function (HttpRestRequest $request, OEGlobalsBag $globalsBag) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $job = $request->getQueryParam('job');
        $fhirExportService = new FhirOperationExportRestController($request, $globalsBag);
        $return = $fhirExportService->processDeleteExportForJob($job);

        return $return;
    },
];
