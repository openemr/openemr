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
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Yash Raj Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\RestControllers\Config\RestConfig;

/**
 *  @OA\Info(title="OpenEMR API", version="7.0.4")
 *  @OA\Server(url="/apis/default/")
 *  @OA\SecurityScheme(
 *      securityScheme="openemr_auth",
 *      type="oauth2",
 *      @OA\Flow(
 *          authorizationUrl="/oauth2/default/authorize",
 *          tokenUrl="/oauth2/default/token",
 *          refreshUrl="/oauth2/default/token",
 *          flow="authorizationCode",
 *          scopes={
 *              "openid": "Generic mandatory scope",
 *              "offline_access": "Will signal server to provide a refresh token",
 *              "launch/patient": "Will provide a patient selector when logging in as an OpenEMR user (required for testing patient/* scopes in swagger if not logging in as a patient)",
 *              "api:fhir": "FHIR R4 API",
 *              "patient/AllergyIntolerance.read": "Read allergy intolerance resources for the current patient (api:fhir)",
 *              "patient/Appointment.read": "Read appointment resources for the current patient (api:fhir)",
 *              "patient/Binary.read": "Read binary document resources for the current patient (api:fhir)",
 *              "patient/CarePlan.read": "Read care plan resources for the current patient (api:fhir)",
 *              "patient/CareTeam.read": "Read care team resources for the current patient (api:fhir)",
 *              "patient/Condition.read": "Read condition resources for the current patient (api:fhir)",
 *              "patient/Coverage.read": "Read coverage resources for the current patient (api:fhir)",
 *              "patient/Device.read": "Read device resources for the current patient (api:fhir)",
 *              "patient/DiagnosticReport.read": "Read diagnostic report resources for the current patient (api:fhir)",
 *              "patient/DocumentReference.read": "Read document reference resources for the current patient (api:fhir)",
 *              "patient/DocumentReference.$docref" : "Generate a document for the current patient or returns the most current Clinical Summary of Care Document (CCD)",
 *              "patient/Encounter.read": "Read encounter resources for the current patient (api:fhir)",
 *              "patient/Goal.read": "Read goal resources for the current patient (api:fhir)",
 *              "patient/Immunization.read": "Read immunization resources for the current patient (api:fhir)",
 *              "patient/Location.read": "Read location resources for the current patient (api:fhir)",
 *              "patient/Medication.read": "Read medication resources for the current patient (api:fhir)",
 *              "patient/MedicationRequest.read": "Read medication request resources for the current patient (api:fhir)",
 *              "patient/Observation.read": "Read observation resources for the current patient (api:fhir)",
 *              "patient/Organization.read": "Read organization resources for the current patient (api:fhir)",
 *              "patient/Patient.read": "Read patient resource for the current patient (api:fhir)",
 *              "patient/Person.read": "Read person resources for the current patient (api:fhir)",
 *              "patient/Practitioner.read": "Read practitioner resources for the current patient (api:fhir)",
 *              "patient/Procedure.read": "Read procedure resources for the current patient (api:fhir)",
 *              "patient/Provenance.read": "Read provenance resources for the current patient (api:fhir)",
 *              "system/AllergyIntolerance.read": "Read all allergy intolerance resources in the system (api:fhir)",
 *              "system/Binary.read": "Read all binary document resources in the system (api:fhir)",
 *              "system/CarePlan.read": "Read all care plan resources in the system (api:fhir)",
 *              "system/CareTeam.read": "Read all care team resources in the system (api:fhir)",
 *              "system/Condition.read": "Read all condition resources in the system (api:fhir)",
 *              "system/Coverage.read": "Read all coverage resources in the system (api:fhir)",
 *              "system/Device.read": "Read all device resources in the system (api:fhir)",
 *              "system/DiagnosticReport.read": "Read all diagnostic report resources in the system (api:fhir)",
 *              "system/DocumentReference.read": "Read all document reference resources in the system (api:fhir)",
 *              "system/DocumentReference.$docref" : "Generate a document for any patient in the system or returns the most current Clinical Summary of Care Document (CCD)",
 *              "system/Encounter.read": "Read all encounter resources in the system (api:fhir)",
 *              "system/Goal.read": "Read all goal resources in the system (api:fhir)",
 *              "system/Group.read": "Read all group resources in the system (api:fhir)",
 *              "system/Immunization.read": "Read all immunization resources in the system (api:fhir)",
 *              "system/Location.read": "Read all location resources in the system (api:fhir)",
 *              "system/Medication.read": "Read all medication resources in the system (api:fhir)",
 *              "system/MedicationRequest.read": "Read all medication request resources in the system (api:fhir)",
 *              "system/Observation.read": "Read all observation resources in the system (api:fhir)",
 *              "system/Organization.read": "Read all organization resources in the system (api:fhir)",
 *              "system/Patient.read": "Read all patient resources in the system (api:fhir)",
 *              "system/Person.read": "Read all person resources in the system (api:fhir)",
 *              "system/Practitioner.read": "Read all practitioner resources in the system (api:fhir)",
 *              "system/PractitionerRole.read": "Read all practitioner role resources in the system (api:fhir)",
 *              "system/Procedure.read": "Read all procedure resources in the system (api:fhir)",
 *              "system/Provenance.read": "Read all provenance resources in the system (api:fhir)",
 *              "system/ValueSet.read": "Read all valueSet resources in the system (api:fhir)",
 *              "user/AllergyIntolerance.read": "Read all allergy intolerance resources the user has access to (api:fhir)",
 *              "user/Binary.read" : "Read all binary documents the user has access to (api:fhir)",
 *              "user/CarePlan.read": "Read all care plan resources the user has access to (api:fhir)",
 *              "user/CareTeam.read": "Read all care team resources the user has access to (api:fhir)",
 *              "user/Condition.read": "Read all condition resources the user has access to (api:fhir)",
 *              "user/Coverage.read": "Read all coverage resources the user has access to (api:fhir)",
 *              "user/Device.read": "Read all device resources the user has access to (api:fhir)",
 *              "user/DiagnosticReport.read": "Read all diagnostic report resources the user has access to (api:fhir)",
 *              "user/DocumentReference.read": "Read all document reference resources the user has access to (api:fhir)",
 *              "user/DocumentReference.$docref" : "Generate a document for any patient the user has access to or returns the most current Clinical Summary of Care Document (CCD) (api:fhir)",
 *              "user/Encounter.read": "Read all encounter resources the user has access to (api:fhir)",
 *              "user/Goal.read": "Read all goal resources the user has access to (api:fhir)",
 *              "user/Immunization.read": "Read all immunization resources the user has access to (api:fhir)",
 *              "user/Location.read": "Read all location resources the user has access to (api:fhir)",
 *              "user/Medication.read": "Read all medication resources the user has access to (api:fhir)",
 *              "user/MedicationRequest.read": "Read all medication request resources the user has access to (api:fhir)",
 *              "user/Observation.read": "Read all observation resources the user has access to (api:fhir)",
 *              "user/Organization.read": "Read all organization resources the user has access to (api:fhir)",
 *              "user/Organization.write": "Write all organization resources the user has access to (api:fhir)",
 *              "user/Patient.read": "Read all patient resources the user has access to (api:fhir)",
 *              "user/Patient.write": "Write all patient resources the user has access to (api:fhir)",
 *              "user/Person.read": "Read all person resources the user has access to (api:fhir)",
 *              "user/Practitioner.read": "Read all practitioner resources the user has access to (api:fhir)",
 *              "user/Practitioner.write": "Write all practitioner resources the user has access to (api:fhir)",
 *              "user/PractitionerRole.read": "Read all practitioner role resources the user has access to (api:fhir)",
 *              "user/Procedure.read": "Read all procedure resources the user has access to (api:fhir)",
 *              "user/Provenance.read": "Read all provenance resources the user has access to (api:fhir)",
 *              "user/ValueSet.read": "Read all valueSet resources the user has access to (api:fhir)",
 *              "api:oemr": "Standard OpenEMR API",
 *              "user/allergy.read": "Read allergies the user has access to (api:oemr)",
 *              "user/allergy.write": "Write allergies the user has access to for (api:oemr)",
 *              "user/appointment.read": "Read appointments the user has access to (api:oemr)",
 *              "user/appointment.write": "Write appointments the user has access to for (api:oemr)",
 *              "user/dental_issue.read": "Read dental issues the user has access to (api:oemr)",
 *              "user/dental_issue.write": "Write dental issues the user has access to (api:oemr)",
 *              "user/document.read": "Read documents the user has access to (api:oemr)",
 *              "user/document.write": "Write documents the user has access to (api:oemr)",
 *              "user/drug.read": "Read drugs the user has access to (api:oemr)",
 *              "user/employer.read": "Read patient employer demographics the user has access to (api:oemr)",
 *              "user/encounter.read": "Read encounters the user has access to (api:oemr)",
 *              "user/encounter.write": "Write encounters the user has access to (api:oemr)",
 *              "user/facility.read": "Read facilities the user has access to (api:oemr)",
 *              "user/facility.write": "Write facilities the user has access to (api:oemr)",
 *              "user/immunization.read": "Read immunizations the user has access to (api:oemr)",
 *              "user/insurance.read": "Read insurances the user has access to (api:oemr)",
 *              "user/insurance.write": "Write insurances the user has access to (api:oemr)",
 *              "user/insurance_company.read": "Read insurance companies the user has access to (api:oemr)",
 *              "user/insurance_company.write": "Write insurance companies the user has access to (api:oemr)",
 *              "user/insurance_type.read": "Read insurance types the user has access to (api:oemr)",
 *              "user/list.read": "Read lists the user has access to (api:oemr)",
 *              "user/medical_problem.read": "Read medical problems the user has access to (api:oemr)",
 *              "user/medical_problem.write": "Write medical problems the user has access to (api:oemr)",
 *              "user/medication.read": "Read medications the user has access to (api:oemr)",
 *              "user/medication.write": "Write medications the user has access to (api:oemr)",
 *              "user/message.write": "Read messages the user has access to (api:oemr)",
 *              "user/patient.read": "Read patients the user has access to (api:oemr)",
 *              "user/patient.write": "Write patients the user has access to (api:oemr)",
 *              "user/practitioner.read": "Read practitioners the user has access to (api:oemr)",
 *              "user/practitioner.write": "Write practitioners the user has access to (api:oemr)",
 *              "user/prescription.read": "Read prescriptions the user has access to (api:oemr)",
 *              "user/procedure.read": "Read procedures the user has access to (api:oemr)",
*               "user/product.read": "Read the email registration status of OpenEMR (api:oemr)",
 *              "user/soap_note.read": "Read soap notes the user has access to (api:oemr)",
 *              "user/soap_note.write": "Write soap notes the user has access to (api:oemr)",
 *              "user/surgery.read": "Read surgeries the user has access to (api:oemr)",
 *              "user/surgery.write": "Write surgeries the user has access to (api:oemr)",
 *              "user/transaction.read": "Read transactions the user has access to (api:oemr)",
 *              "user/transaction.write": "Write transactions the user has access to (api:oemr)",
 *              "user/user.read": "Read users the current user has access to (api:oemr)",
 *              "user/vital.read": "Read vitals the user has access to (api:oemr)",
 *              "user/vital.write": "Write vitals the user has access to (api:oemr)",
 *              "api:port": "Standard Patient Portal OpenEMR API",
 *              "patient/encounter.read": "Read encounters the patient has access to (api:port)",
 *              "patient/patient.read": "Write encounters the patient has access to (api:port)",
 *              "patient/appointment.read": "Read appointments the patient has access to (api:port)"
 *          }
 *      )
 *  )
 *  @OA\Tag(
 *      name="fhir",
 *      description="FHIR R4 API"
 *  )
 *  @OA\Tag(
 *      name="standard",
 *      description="Standard OpenEMR API"
 *  )
 *  @OA\Tag(
 *      name="standard-patient",
 *      description="Standard Patient Portal OpenEMR API"
 *  )
 *  @OA\Parameter(
 *          name="_sort",
 *          in="query",
 *          parameter="_sort",
 *          description="The sort criteria specified in comma separated order with Descending order being specified by a dash before the search parameter name. (Example: name,-category)",
 *          required=false,
 *          @OA\Schema(
 *              type="string"
 *          )
 *  )
 *  @OA\Parameter(
 *          name="_lastUpdated",
 *          in="query",
 *          parameter="_lastUpdated",
 *          description="The date the resource was last updated.",
 *          required=false,
 *          @OA\Schema(
 *              type="string"
 *          )
 *  )
 *  @OA\Response(
 *      response="standard",
 *      description="Standard Response",
 *      @OA\MediaType(
 *          mediaType="application/json",
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="validationErrors",
 *                  description="Validation errors.",
 *                  type="array",
 *                  @OA\Items(
 *                      type="object",
 *                  ),
 *              ),
 *              @OA\Property(
 *                  property="internalErrors",
 *                  description="Internal errors.",
 *                  type="array",
 *                  @OA\Items(
 *                      type="object",
 *                  ),
 *              ),
 *              @OA\Property(
 *                  property="data",
 *                  description="Returned data.",
 *                  type="array",
 *                  @OA\Items(
 *                      type="object",
 *                  ),
 *              ),
 *              example={
 *                  "validationErrors": {},
 *                  "error_description": {},
 *                  "data": {}
 *              }
 *          )
 *      )
 *  )
 *  @OA\Response(
 *      response="badrequest",
 *      description="Bad Request",
 *      @OA\MediaType(
 *          mediaType="application/json",
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="validationErrors",
 *                  description="Validation errors.",
 *                  type="object"
 *              ),
 *              example={
 *                  "validationErrors":
 *                  {
 *                      "_id": "The search field argument was invalid, improperly formatted, or could not be parsed.  Inner message: UUID columns must be a valid UUID string"
 *                  }
 *              }
 *          )
 *      )
 *  )
 *  @OA\Response(
 *      response="unauthorized",
 *      description="Unauthorized",
 *      @OA\MediaType(
 *          mediaType="application/json",
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="error",
 *                  description="The error.",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="error_description",
 *                  description="The description of the error.",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="hint",
 *                  description="More specific information on the error.",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="message",
 *                  description="Message regarding the error.",
 *                  type="string"
 *              ),
 *              example={
 *                  "error": "access_denied",
 *                  "error_description": "The resource owner or authorization server denied the request.",
 *                  "hint": "Missing ""Authorization"" header",
 *                  "message": "The resource owner or authorization server denied the request."
 *              }
 *          )
 *      )
 *  )
 *  @OA\Response(
 *      response="uuidnotfound",
 *      description="Not Found",
 *      @OA\MediaType(
 *          mediaType="application/json",
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="empty",
 *                  description="empty",
 *                  type="object"
 *              ),
 *              example={}
 *          )
 *      )
 *  )
 */

// Lets keep our controller classes with the routes.
//

// Note some Http clients may not send auth as json so a function
// is implemented to determine and parse encoding on auth route's.

// Note that the api route is only for users role
//  (there is a mechanism in place to ensure only user role can access the api route)

/**
 * @see apis/routes/_rest_routes_standard.inc.php
 * @see apis/routes/_rest_routes_standard_user.inc.php
 */
RestConfig::$ROUTE_MAP = array_merge(
    require_once __DIR__ . "/apis/routes/_rest_routes_standard.inc.php",
    require_once __DIR__ . '/apis/routes/_rest_routes_standard_user.inc.php',
);

RestConfig::$FHIR_ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php";

RestConfig::$PORTAL_ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_portal.inc.php";
