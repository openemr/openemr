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
 *  @OA\Info(title="OpenEMR API", version="8.0.1")
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
 *              "patient/AllergyIntolerance.rs": "Read,Search allergy intolerance resources for the current patient (api:fhir)",
 *              "patient/Appointment.rs": "Read,Search appointment resources for the current patient (api:fhir)",
 *              "patient/Binary.rs": "Read,Search binary document resources for the current patient (api:fhir)",
 *              "patient/CarePlan.rs": "Read,Search care plan resources for the current patient (api:fhir)",
 *              "patient/CareTeam.rs": "Read,Search care team resources for the current patient (api:fhir)",
 *              "patient/Condition.rs": "Read,Search condition resources for the current patient (api:fhir)",
 *              "patient/Coverage.rs": "Read,Search coverage resources for the current patient (api:fhir)",
 *              "patient/Device.rs": "Read,Search device resources for the current patient (api:fhir)",
 *              "patient/DiagnosticReport.rs": "Read,Search diagnostic report resources for the current patient (api:fhir)",
 *              "patient/DocumentReference.rs": "Read,Search document reference resources for the current patient (api:fhir)",
 *              "patient/DocumentReference.$docref" : "Generate a document for the current patient or returns the most current Clinical Summary of Care Document (CCD)",
 *              "patient/Encounter.rs": "Read,Search encounter resources for the current patient (api:fhir)",
 *              "patient/Goal.rs": "Read,Search goal resources for the current patient (api:fhir)",
 *              "patient/Immunization.rs": "Read,Search immunization resources for the current patient (api:fhir)",
 *              "patient/Location.rs": "Read,Search location resources for the current patient (api:fhir)",
 *              "patient/Media.rs": "Read,Search media resources for the current patient (api:fhir)",
 *              "patient/Medication.rs": "Read,Search medication resources for the current patient (api:fhir)",
 *              "patient/MedicationRequest.rs": "Read,Search medication request resources for the current patient (api:fhir)",
 *              "patient/MedicationDispense.rs": "Read,Search medication dispense resources for the current patient (api:fhir)",
 *              "patient/Observation.rs": "Read,Search observation resources for the current patient (api:fhir)",
 *              "patient/Organization.rs": "Read,Search organization resources for the current patient (api:fhir)",
 *              "patient/Patient.rs": "Read,Search patient resource for the current patient (api:fhir)",
 *              "patient/Person.rs": "Read,Search person resources for the current patient (api:fhir)",
 *              "patient/Practitioner.rs": "Read,Search practitioner resources for the current patient (api:fhir)",
 *              "patient/Procedure.rs": "Read,Search procedure resources for the current patient (api:fhir)",
 *              "patient/Provenance.rs": "Read,Search provenance resources for the current patient (api:fhir)",
 *              "patient/QuestionnaireResponse.rs": "Read,Search responses to questionnaire resources for the current patient (api:fhir)",
 *              "patient/RelatedPerson.rs": "Read,Search related person resources for the current patient (api:fhir)",
 *              "patient/ServiceRequest.rs": "Read,Search the service request resources for the current patient (api:fhir)",
 *              "patient/Specimen.rs": "Read,Search specimen resources for the current patient (api:fhir)",
 *              "system/AllergyIntolerance.rs": "Read,Search all allergy intolerance resources in the system (api:fhir)",
 *              "system/Binary.rs": "Read,Search all binary document resources in the system (api:fhir)",
 *              "system/CarePlan.rs": "Read,Search all care plan resources in the system (api:fhir)",
 *              "system/CareTeam.rs": "Read,Search all care team resources in the system (api:fhir)",
 *              "system/Condition.rs": "Read,Search all condition resources in the system (api:fhir)",
 *              "system/Coverage.rs": "Read,Search all coverage resources in the system (api:fhir)",
 *              "system/Device.rs": "Read,Search all device resources in the system (api:fhir)",
 *              "system/DiagnosticReport.rs": "Read,Search all diagnostic report resources in the system (api:fhir)",
 *              "system/DocumentReference.rs": "Read,Search all document reference resources in the system (api:fhir)",
 *              "system/DocumentReference.$docref" : "Generate a document for any patient in the system or returns the most current Clinical Summary of Care Document (CCD)",
 *              "system/Encounter.rs": "Read,Search all encounter resources in the system (api:fhir)",
 *              "system/Goal.rs": "Read,Search all goal resources in the system (api:fhir)",
 *              "system/Group.rs": "Read,Search all group resources in the system (api:fhir)",
 *              "system/Immunization.rs": "Read,Search all immunization resources in the system (api:fhir)",
 *              "system/Location.rs": "Read,Search all location resources in the system (api:fhir)",
 *              "system/Media.rs": "Read,Search all media resources in the system (api:fhir)",
 *              "system/Medication.rs": "Read,Search all medication resources in the system (api:fhir)",
 *              "system/MedicationRequest.rs": "Read,Search all medication request resources in the system (api:fhir)",
 *              "system/MedicationDispense.rs": "Read,Search all medication dispense resources in the system (api:fhir)",
 *              "system/Observation.rs": "Read,Search all observation resources in the system (api:fhir)",
 *              "system/Organization.rs": "Read,Search all organization resources in the system (api:fhir)",
 *              "system/Patient.rs": "Read,Search all patient resources in the system (api:fhir)",
 *              "system/Person.rs": "Read,Search all person resources in the system (api:fhir)",
 *              "system/Practitioner.rs": "Read,Search all practitioner resources in the system (api:fhir)",
 *              "system/PractitionerRole.rs": "Read,Search all practitioner role resources in the system (api:fhir)",
 *              "system/Procedure.rs": "Read,Search all procedure resources in the system (api:fhir)",
 *              "system/Provenance.rs": "Read,Search all provenance resources in the system (api:fhir)",
 *              "system/ValueSet.rs": "Read,Search all valueSet resources in the system (api:fhir)",
 *              "system/Questionnaire.rs": "Read,Search all questionnaire resources in the system (api:fhir)",
 *              "system/QuestionnaireResponse.rs": "Read,Search all responses to questionnaire resources in the system (api:fhir)",
 *              "system/RelatedPerson.rs": "Read,Search all related person resources in the system (api:fhir)",
 *              "system/ServiceRequest.rs": "Read,Search all the service request resources in the system (api:fhir)",
 *              "system/Specimen.rs": "Read,Search all specimen resources in the system (api:fhir)",
 *              "user/AllergyIntolerance.rs": "Read,Search all allergy intolerance resources the user has access to (api:fhir)",
 *              "user/Binary.rs" : "Read,Search all binary documents the user has access to (api:fhir)",
 *              "user/CarePlan.rs": "Read,Search all care plan resources the user has access to (api:fhir)",
 *              "user/CareTeam.rs": "Read,Search all care team resources the user has access to (api:fhir)",
 *              "user/Condition.rs": "Read,Search all condition resources the user has access to (api:fhir)",
 *              "user/Coverage.rs": "Read,Search all coverage resources the user has access to (api:fhir)",
 *              "user/Device.rs": "Read,Search all device resources the user has access to (api:fhir)",
 *              "user/DiagnosticReport.rs": "Read,Search all diagnostic report resources the user has access to (api:fhir)",
 *              "user/DocumentReference.rs": "Read,Search all document reference resources the user has access to (api:fhir)",
 *              "user/DocumentReference.$docref" : "Generate a document for any patient the user has access to or returns the most current Clinical Summary of Care Document (CCD) (api:fhir)",
 *              "user/Encounter.rs": "Read,Search all encounter resources the user has access to (api:fhir)",
 *              "user/Goal.rs": "Read,Search all goal resources the user has access to (api:fhir)",
 *              "user/Immunization.rs": "Read,Search all immunization resources the user has access to (api:fhir)",
 *              "user/Location.rs": "Read,Search all location resources the user has access to (api:fhir)",
 *              "user/Media.rs": "Read,Search media resources the user has access to (api:fhir)",
 *              "user/Medication.rs": "Read,Search all medication resources the user has access to (api:fhir)",
 *              "user/MedicationRequest.rs": "Read,Search all medication request resources the user has access to (api:fhir)",
 *              "user/MedicationDispense.rs": "Read,Search medication dispense resources the user has access to (api:fhir)",
 *              "user/Observation.rs": "Read,Search all observation resources the user has access to (api:fhir)",
 *              "user/Organization.rs": "Read,Search all organization resources the user has access to (api:fhir)",
 *              "user/Patient.rs": "Read,Search all patient resources the user has access to (api:fhir)",
 *              "user/Person.rs": "Read,Search all person resources the user has access to (api:fhir)",
 *              "user/Practitioner.rs": "Read,Search all practitioner resources the user has access to (api:fhir)",
 *              "user/PractitionerRole.rs": "Read,Search all practitioner role resources the user has access to (api:fhir)",
 *              "user/Procedure.rs": "Read,Search all procedure resources the user has access to (api:fhir)",
 *              "user/Provenance.rs": "Read,Search all provenance resources the user has access to (api:fhir)",
 *              "user/ValueSet.rs": "Read,Search all valueSet resources the user has access to (api:fhir)",
 *              "user/Questionnaire.rs": "Read,Search all questionnaire resources the user has access to (api:fhir)",
 *              "user/QuestionnaireResponse.rs": "Read,Search all responses to questionnaire resources the user has access to (api:fhir)",
 *              "user/RelatedPerson.rs": "Read,Search all related person resources the user has access to (api:fhir)",
 *              "user/ServiceRequest.rs": "Read,Search all the service request resources the user has access to (api:fhir)",
 *              "user/Specimen.rs": "Read,Search all specimen resources the user has access to (api:fhir)",
 *              "api:oemr": "Standard OpenEMR API",
 *              "user/allergy.cruds": "Create,Read,Update,Delete,Search allergies the user has access to (api:oemr)",
 *              "user/appointment.cruds": "Create,Read,Update,Delete,Search appointments the user has access to (api:oemr)",
 *              "user/dental_issue.cruds": "Create,Read,Update,Delete,Search dental issues the user has access to (api:oemr)",
 *              "user/document.crs": "Create,Read,Search documents the user has access to (api:oemr)",
 *              "user/drug.rs": "Read,Search drugs the user has access to (api:oemr)",
 *              "user/employer.s": "Search patient employer demographics the user has access to (api:oemr)",
 *              "user/encounter.crus": "Create,Read,Update,Search encounters the user has access to (api:oemr)",
 *              "user/facility.crus": "Create,Read,Update,Search facilities the user has access to (api:oemr)",
 *              "user/immunization.rs": "Read,Search immunizations the user has access to (api:oemr)",
 *              "user/insurance.crus": "Create,Read,Update,Search insurances the user has access to (api:oemr)",
 *              "user/insurance_company.crus": "Create,Read,Update,Search insurance companies the user has access to (api:oemr)",
 *              "user/insurance_type.s": "Search insurance types the user has access to (api:oemr)",
 *              "user/list.r": "Read lists the user has access to (api:oemr)",
 *              "user/medical_problem.cruds": "Create,Read,Update,Delete,Search medical problems the user has access to (api:oemr)",
 *              "user/medication.cruds": "Create,Read,Update,Delete,Search medications the user has access to (api:oemr)",
 *              "user/message.cud": "Create,Update,Delete messages the user has access to (api:oemr)",
 *              "user/patient.crus": "Create,Read,Update,Search patients the user has access to (api:oemr)",
 *              "user/practitioner.crus": "Create,Read,Update,Search practitioners the user has access to (api:oemr)",
 *              "user/prescription.rs": "Read,Search prescriptions the user has access to (api:oemr)",
 *              "user/procedure.rs": "Read,Search procedures the user has access to (api:oemr)",
*               "user/product.s": "Search the email registration status of OpenEMR (api:oemr)",
 *              "user/soap_note.crus": "Create,Read,Update,Search soap notes the user has access to (api:oemr)",
 *              "user/surgery.cruds": "Create,Read,Update,Delete,Search surgeries the user has access to (api:oemr)",
 *              "user/transaction.cuds": "Create,Update,Delete,Search transactions the user has access to (api:oemr)",
 *              "user/user.rs": "Read,Search users the current user has access to (api:oemr)",
 *              "user/version.s": "Search the software version information the user has access to (api:oemr)",
 *              "user/vital.crus": "Create,Read,Update,Search vitals the user has access to (api:oemr)",
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
RestConfig::$ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_standard.inc.php";

RestConfig::$FHIR_ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php";

RestConfig::$PORTAL_ROUTE_MAP = require_once __DIR__ . "/apis/routes/_rest_routes_portal.inc.php";
