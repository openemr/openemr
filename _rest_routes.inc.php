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
 *  @OA\Info(title="OpenEMR API", version="7.0.0")
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
 *              "patient/CarePlan.read": "Read care plan resources for the current patient (api:fhir)",
 *              "patient/CareTeam.read": "Read care team resources for the current patient (api:fhir)",
 *              "patient/Condition.read": "Read condition resources for the current patient (api:fhir)",
 *              "patient/Coverage.read": "Read coverage resources for the current patient (api:fhir)",
 *              "patient/Device.read": "Read device resources for the current patient (api:fhir)",
 *              "patient/DiagnosticReport.read": "Read diagnostic report resources for the current patient (api:fhir)",
 *              "patient/Document.read": "Read document resources for the current patient (api:fhir)",
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
 *              "system/CarePlan.read": "Read all care plan resources in the system (api:fhir)",
 *              "system/CareTeam.read": "Read all care team resources in the system (api:fhir)",
 *              "system/Condition.read": "Read all condition resources in the system (api:fhir)",
 *              "system/Coverage.read": "Read all coverage resources in the system (api:fhir)",
 *              "system/Device.read": "Read all device resources in the system (api:fhir)",
 *              "system/DiagnosticReport.read": "Read all diagnostic report resources in the system (api:fhir)",
 *              "system/Document.read": "Read all document resources in the system (api:fhir)",
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
 *              "user/AllergyIntolerance.read": "Read all allergy intolerance resources the user has access to (api:fhir)",
 *              "user/CarePlan.read": "Read all care plan resources the user has access to (api:fhir)",
 *              "user/CareTeam.read": "Read all care team resources the user has access to (api:fhir)",
 *              "user/Condition.read": "Read all condition resources the user has access to (api:fhir)",
 *              "user/Coverage.read": "Read all coverage resources the user has access to (api:fhir)",
 *              "user/Device.read": "Read all device resources the user has access to (api:fhir)",
 *              "user/DiagnosticReport.read": "Read all diagnostic report resources the user has access to (api:fhir)",
 *              "user/Document.read" : "Read all documents the user has access to (api:fhir)",
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
 *              "user/soap_note.read": "Read soap notes the user has access to (api:oemr)",
 *              "user/soap_note.write": "Write soap notes the user has access to (api:oemr)",
 *              "user/surgery.read": "Read surgeries the user has access to (api:oemr)",
 *              "user/surgery.write": "Write surgeries the user has access to (api:oemr)",
 *              "user/transaction.read": "Read transactions the user has access to (api:oemr)",
 *              "user/transaction.write": "Write transactions the user has access to (api:oemr)",
 *              "user/vital.read": "Read vitals the user has access to (api:oemr)",
 *              "user/vital.write": "Write vitals the user has access to (api:oemr)",
 *              "api:port": "Standard Patient Portal OpenEMR API",
 *              "patient/encounter.read": "Read encounters the patient has access to (api:port)",
 *              "patient/patient.read": "Write encounters the patient has access to (api:port)"
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
use OpenEMR\RestControllers\TransactionRestController;

// Note some Http clients may not send auth as json so a function
// is implemented to determine and parse encoding on auth route's.

// Note that the api route is only for users role
//  (there is a mechanism in place to ensure only user role can access the api route)
RestConfig::$ROUTE_MAP = array(
    /**
     *  @OA\Get(
     *      path="/api/facility",
     *      description="Returns a single facility.",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="The name for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="facility_npi",
     *          in="query",
     *          description="The facility_npi for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="fax",
     *          in="query",
     *          description="The fax for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="street",
     *          in="query",
     *          description="The street for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="city",
     *          in="query",
     *          description="The city for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="state",
     *          in="query",
     *          description="The state for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="postal_code",
     *          in="query",
     *          description="The postal_code for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="country_code",
     *          in="query",
     *          description="The country_code for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="federal_ein",
     *          in="query",
     *          description="The federal_ein for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="website",
     *          in="query",
     *          description="The website for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email for the facility.",
     *          required=false,
     *          @OA\Schema(
     *           type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="domain_identifier",
     *          in="query",
     *          description="The domain_identifier for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="facility_taxonomy",
     *          in="query",
     *          description="The facility_taxonomy for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="facility_code",
     *          in="query",
     *          description="The facility_code for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="billing_location",
     *          in="query",
     *          description="The billing_location setting for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="accepts_assignment",
     *          in="query",
     *          description="The accepts_assignment setting for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="oid",
     *          in="query",
     *          description="The oid for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="service_location",
     *          in="query",
     *          description="The service_location setting for the facility.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/facility" => function () {
        RestConfig::authorization_check("admin", "users");
        $return = (new FacilityRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/facility/{fuuid}",
     *      description="Returns a single facility.",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="fuuid",
     *          in="path",
     *          description="The uuid for the facility.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "users");
        $return = (new FacilityRestController())->getOne($fuuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Post(
     *      path="/api/facility",
     *      description="Creates a facility in the system",
     *      tags={"standard"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      description="The name for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_npi",
     *                      description="The facility_npi for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="The phone for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fax",
     *                      description="The fax for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="street",
     *                      description="The street for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="city",
     *                      description="The city for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="state",
     *                      description="The state for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="postal_code",
     *                      description="The postal_code for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="country_code",
     *                      description="The country_code for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="federal_ein",
     *                      description="The federal_ein for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="website",
     *                      description="The website for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="The email for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="domain_identifier",
     *                      description="The domain_identifier for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_taxonomy",
     *                      description="The facility_taxonomy for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_code",
     *                      description="The facility_code for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="billing_location",
     *                      description="The billing_location setting for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="accepts_assignment",
     *                      description="The accepts_assignment setting for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="oid",
     *                      description="The oid for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="service_location",
     *                      description="The service_location setting for the facility.",
     *                      type="string"
     *                  ),
     *                  required={"name", "facility_npi"},
     *                  example={
     *                      "name": "Aquaria",
     *                      "facility_npi": "123456789123",
     *                      "phone": "808-606-3030",
     *                      "fax": "808-606-3031",
     *                      "street": "1337 Bit Shifter Ln",
     *                      "city": "San Lorenzo",
     *                      "state": "ZZ",
     *                      "postal_code": "54321",
     *                      "country_code": "US",
     *                      "federal_ein": "4343434",
     *                      "website": "https://example.com",
     *                      "email": "foo@bar.com",
     *                      "domain_identifier": "",
     *                      "facility_taxonomy": "",
     *                      "facility_code": "",
     *                      "billing_location": "1",
     *                      "accepts_assignment": "1",
     *                      "oid": "",
     *                      "service_location": "1"
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/facility" => function () {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new FacilityRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/facility/{fuuid}",
     *      description="Updates a facility in the system",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="fuuid",
     *          in="path",
     *          description="The uuid for the facility.",
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
     *                  @OA\Property(
     *                      property="name",
     *                      description="The name for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_npi",
     *                      description="The facility_npi for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="The phone for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fax",
     *                      description="The fax for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="street",
     *                      description="The street for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="city",
     *                      description="The city for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="state",
     *                      description="The state for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="postal_code",
     *                      description="The postal_code for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="country_code",
     *                      description="The country_code for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="federal_ein",
     *                      description="The federal_ein for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="website",
     *                      description="The website for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="The email for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="domain_identifier",
     *                      description="The domain_identifier for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_taxonomy",
     *                      description="The facility_taxonomy for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_code",
     *                      description="The facility_code for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="billing_location",
     *                      description="The billing_location setting for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="accepts_assignment",
     *                      description="The accepts_assignment setting for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="oid",
     *                      description="The oid for the facility.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="service_location",
     *                      description="The service_location setting for the facility.",
     *                      type="string"
     *                  ),
     *                  example={
     *                      "name": "Aquaria",
     *                      "facility_npi": "123456789123",
     *                      "phone": "808-606-3030",
     *                      "fax": "808-606-3031",
     *                      "street": "1337 Bit Shifter Ln",
     *                      "city": "San Lorenzo",
     *                      "state": "ZZ",
     *                      "postal_code": "54321",
     *                      "country_code": "US",
     *                      "federal_ein": "4343434",
     *                      "website": "https://example.com",
     *                      "email": "foo@bar.com",
     *                      "domain_identifier": "",
     *                      "facility_taxonomy": "",
     *                      "facility_code": "",
     *                      "billing_location": "1",
     *                      "accepts_assignment": "1",
     *                      "oid": "",
     *                      "service_location": "1"
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/facility/:fuuid" => function ($fuuid) {
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return =  (new FacilityRestController())->patch($fuuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient",
     *      description="Retrieves a list of patients",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="fname",
     *          in="query",
     *          description="The first name for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lname",
     *          in="query",
     *          description="The last name for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="ss",
     *          in="query",
     *          description="The social security number for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="street",
     *          in="query",
     *          description="The street for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="postal_code",
     *          in="query",
     *          description="The postal code for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="city",
     *          in="query",
     *          description="The city for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="state",
     *          in="query",
     *          description="The state for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone_home",
     *          in="query",
     *          description="The home phone for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone_biz",
     *          in="query",
     *          description="The business phone for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone_cell",
     *          in="query",
     *          description="The cell phone for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="postal_contact",
     *          in="query",
     *          description="The postal_contact for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sex",
     *          in="query",
     *          description="The gender for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="country_code",
     *          in="query",
     *          description="The country code for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="DOB",
     *          in="query",
     *          description="The DOB for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the patient request
     *
     *  @OA\Schema(
     *      schema="api_patient_request",
     *      @OA\Property(
     *          property="title",
     *          description="The title of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="fname",
     *          description="The fname of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="mname",
     *          description="The mname of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="lname",
     *          description="The lname of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="street",
     *          description="The street address of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="postal_code",
     *          description="The postal code of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="city",
     *          description="The city of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="state",
     *          description="The state of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="country_code",
     *          description="The country code of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="phone_contact",
     *          description="The phone contact of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="DOB",
     *          description="The DOB of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="sex",
     *          description="The lname of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="race",
     *          description="The race of patient.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="ethnicity",
     *          description="The ethnicity of patient.",
     *          type="string"
     *      ),
     *      required={"fname", "lname", "DOB", "sex"},
     *      example={
     *          "title": "Mr",
     *          "fname": "Foo",
     *          "mname": "",
     *          "lname": "Bar",
     *          "street": "456 Tree Lane",
     *          "postal_code": "08642",
     *          "city": "FooTown",
     *          "state": "FL",
     *          "country_code": "US",
     *          "phone_contact": "123-456-7890",
     *          "DOB": "1992-02-02",
     *          "sex": "Male",
     *          "race": "",
     *          "ethnicity": ""
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient",
     *      description="Creates a new patient",
     *      tags={"standard"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_patient_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="validationErrors",
     *                      description="Validation errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="internalErrors",
     *                      description="Internal errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      description="Returned data.",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="pid",
     *                              description="patient pid",
     *                              type="integer",
     *                          )
     *                      ),
     *                  ),
     *                  example={
     *                      "validationErrors": {},
     *                      "error_description": {},
     *                      "data": {
     *                          "pid": 1
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "POST /api/patient" => function () {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * Schema for the patient response
     *
     *  @OA\Schema(
     *      schema="api_patient_response",
     *      @OA\Property(
     *          property="validationErrors",
     *          description="Validation errors.",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Property(
     *          property="internalErrors",
     *          description="Internal errors.",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Property(
     *          property="data",
     *          description="Returned data.",
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  property="id",
     *                  description="patient id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="pid",
     *                  description="patient pid",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="pubpid",
     *                  description="patient public id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  description="patient title",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="fname",
     *                  description="patient first name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="mname",
     *                  description="patient middle name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="lname",
     *                  description="patient last name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="ss",
     *                  description="patient social security number",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="street",
     *                  description="patient street address",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="postal_code",
     *                  description="patient postal code",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="city",
     *                  description="patient city",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="state",
     *                  description="patient state",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="county",
     *                  description="patient county",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="country_code",
     *                  description="patient country code",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="drivers_license",
     *                  description="patient drivers license id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="contact_relationship",
     *                  description="patient contact relationship",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="phone_contact",
     *                  description="patient phone contact",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="phone_home",
     *                  description="patient home phone",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="phone_biz",
     *                  description="patient work phone",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="phone_cell",
     *                  description="patient mobile phone",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  description="patient email",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="DOB",
     *                  description="patient DOB",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="sex",
     *                  description="patient sex (gender)",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="race",
     *                  description="patient race",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="ethnicity",
     *                  description="patient ethnicity",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="patient status",
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *      example={
     *          "validationErrors": {},
     *          "error_description": {},
     *          "data": {
     *              "id": "193",
     *              "pid": "1",
     *              "pubpid": "",
     *              "title": "Mr",
     *              "fname": "Baz",
     *              "mname": "",
     *              "lname": "Bop",
     *              "ss": "",
     *              "street": "456 Tree Lane",
     *              "postal_code": "08642",
     *              "city": "FooTown",
     *              "state": "FL",
     *              "county": "",
     *              "country_code": "US",
     *              "drivers_license": "",
     *              "contact_relationship": "",
     *              "phone_contact": "123-456-7890",
     *              "phone_home": "",
     *              "phone_biz": "",
     *              "phone_cell": "",
     *              "email": "",
     *              "DOB": "1992-02-03",
     *              "sex": "Male",
     *              "race": "",
     *              "ethnicity": "",
     *              "status": ""
     *          }
     *      }
     *  )
     */
    /**
     *  @OA\Put(
     *      path="/api/patient/{puuid}",
     *      description="Updates a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_patient_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_patient_response")
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "PUT /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->put($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}",
     *      description="Retrieves a single patient by their uuid",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_patient_response")
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /api/patient/:puuid" => function ($puuid) {
        RestConfig::authorization_check("patients", "demo");
        $return = (new PatientRestController())->getOne($puuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/encounter",
     *      description="Retrieves a list of encounters for a single patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new EncounterRestController())->getAll($puuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the encounter request
     *
     *  @OA\Schema(
     *      schema="api_encounter_request",
     *      @OA\Property(
     *          property="date",
     *          description="The date of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="onset_date",
     *          description="The onset date of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="reason",
     *          description="The reason of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="facility",
     *          description="The facility of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="pc_catid",
     *          description="The pc_catid of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="facility_id",
     *          description="The facility id of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="billing_facility",
     *          description="The billing facility id of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="sensitivity",
     *          description="The sensitivity of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="referral_source",
     *          description="The referral source of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="pos_code",
     *          description="The pos_code of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="external_id",
     *          description="The external id of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="provider_id",
     *          description="The provider id of encounter.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="class_code",
     *          description="The class_code of encounter.",
     *          type="string"
     *      ),
     *      required={"pc_catid", "class_code"},
     *      example={
     *          "date":"2020-11-10",
     *          "onset_date": "",
     *          "reason": "Pregnancy Test",
     *          "facility": "Owerri General Hospital",
     *          "pc_catid": "5",
     *          "facility_id": "3",
     *          "billing_facility": "3",
     *          "sensitivity": "normal",
     *          "referral_source": "",
     *          "pos_code": "0",
     *          "external_id": "",
     *          "provider_id": "1",
     *          "class_code" : "AMB"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{puuid}/encounter",
     *      description="Creates a new encounter",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_encounter_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="validationErrors",
     *                      description="Validation errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="internalErrors",
     *                      description="Internal errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      description="Returned data.",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="encounter",
     *                              description="encounter id",
     *                              type="integer",
     *                          ),
     *                          @OA\Property(
     *                              property="uuid",
     *                              description="encounter uuid",
     *                              type="string",
     *                          )
     *                      ),
     *                  ),
     *                  example={
     *                      "validationErrors": {},
     *                      "error_description": {},
     *                      "data": {
     *                          "encounter": 1,
     *                          "uuid": "90c196f2-51cc-4655-8858-3a80aebff3ef"
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "POST /api/patient/:puuid/encounter" => function ($puuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * Schema for the encounter response
     *
     *  @OA\Schema(
     *      schema="api_encounter_response",
     *      @OA\Property(
     *          property="validationErrors",
     *          description="Validation errors.",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Property(
     *          property="internalErrors",
     *          description="Internal errors.",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Property(
     *          property="data",
     *          description="Returned data.",
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  property="id",
     *                  description="encounter id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="uuid",
     *                  description="encounter uuid",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  description="encounter date",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="reason",
     *                  description="encounter reason",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="facility",
     *                  description="encounter facility name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="facility_id",
     *                  description="encounter facility id name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="pid",
     *                  description="encounter for patient pid",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="onset_date",
     *                  description="encounter onset date",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="sensitivity",
     *                  description="encounter sensitivity",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="billing_note",
     *                  description="encounter billing note",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="pc_catid",
     *                  description="encounter pc_catid",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="last_level_billed",
     *                  description="encounter last_level_billed",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="last_level_closed",
     *                  description="encounter last_level_closed",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="last_stmt_date",
     *                  description="encounter last_stmt_date",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="stmt_count",
     *                  description="encounter stmt_count",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="provider_id",
     *                  description="provider id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="supervisor_id",
     *                  description="encounter supervisor id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="invoice_refno",
     *                  description="encounter invoice_refno",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="referral_source",
     *                  description="encounter referral source",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="billing_facility",
     *                  description="encounter billing facility id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="external_id",
     *                  description="encounter external id",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="pos_code",
     *                  description="encounter pos_code",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="class_code",
     *                  description="encounter class_code",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="class_title",
     *                  description="encounter class_title",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="pc_catname",
     *                  description="encounter pc_catname",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="billing_facility_name",
     *                  description="encounter billing facility name",
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *      example={
     *          "validationErrors": {},
     *          "error_description": {},
     *          "data": {
     *              "id": "1",
     *              "uuid": "90c196f2-51cc-4655-8858-3a80aebff3ef",
     *              "date": "2019-09-14 00:00:00",
     *              "reason": "Pregnancy Test",
     *              "facility": "Owerri General Hospital",
     *              "facility_id": "3",
     *              "pid": "1",
     *              "onset_date": "2019-04-20 00:00:00",
     *              "sensitivity": "normal",
     *              "billing_note": null,
     *              "pc_catid": "5",
     *              "last_level_billed": "0",
     *              "last_level_closed": "0",
     *              "last_stmt_date": null,
     *              "stmt_count": "0",
     *              "provider_id": "1",
     *              "supervisor_id": "0",
     *              "invoice_refno": "",
     *              "referral_source": "",
     *              "billing_facility": "3",
     *              "external_id": "",
     *              "pos_code": "0",
     *              "class_code": "AMB",
     *              "class_title": "ambulatory",
     *              "pc_catname": "Office Visit",
     *              "billing_facility_name": "Owerri General Hospital"
     *          }
     *      }
     *  )
     */
    /**
     *  @OA\Put(
     *      path="/api/patient/{puuid}/encounter/{euuid}",
     *      description="Modify a encounter",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="euuid",
     *          in="path",
     *          description="The uuid for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_encounter_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_encounter_response")
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "PUT /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->put($puuid, $euuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/encounter/{euuid}",
     *      description="Retrieves a single encounter for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="euuid",
     *          in="path",
     *          description="The uuid for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_encounter_response")
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid) {
        RestConfig::authorization_check("encounters", "auth_a");
        $return = (new EncounterRestController())->getOne($puuid, $euuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/encounter/{eid}/soap_note",
     *      description="Retrieves soap notes from an encounter for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getSoapNotes($pid, $eid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the vital request
     *
     *  @OA\Schema(
     *      schema="api_vital_request",
     *      @OA\Property(
     *          property="bps",
     *          description="The bps of vitals.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="bpd",
     *          description="The bpd of vitals.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="weight",
     *          description="The weight of vitals. (unit is lb)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="height",
     *          description="The height of vitals. (unit is inches)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="temperature",
     *          description="The temperature of temperature. (unit is F)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="temp_method",
     *          description="The temp_method of vitals.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="pulse",
     *          description="The pulse of vitals.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="respiration",
     *          description="The respiration of vitals.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="note",
     *          description="The note (ie. comments) of vitals.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="waist_circ",
     *          description="The waist circumference of vitals. (unit is inches)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="head_circ",
     *          description="The head circumference of vitals. (unit is inches)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="oxygen_saturation",
     *          description="The oxygen_saturation of vitals.",
     *          type="string"
     *      ),
     *      example={
     *          "bps": "130",
     *          "bpd": "80",
     *          "weight": "220",
     *          "height": "70",
     *          "temperature": "98",
     *          "temp_method": "Oral",
     *          "pulse": "60",
     *          "respiration": "20",
     *          "note": "Patient with difficulty standing, which made weight measurement difficult.",
     *          "waist_circ": "37",
     *          "head_circ": "22.2",
     *          "oxygen_saturation": "96"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/encounter/{eid}/vital",
     *      description="Submits a new vitals form",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_vital_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController())->postVital($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/encounter/{eid}/vital/{vid}",
     *      description="Edit a vitals form",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="vid",
     *          in="path",
     *          description="The id for the vitalss form.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_vital_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController())->putVital($pid, $eid, $vid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/encounter/{eid}/vital",
     *      description="Retrieves all vitals from an encounter for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getVitals($pid, $eid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/encounter/{eid}/vital/{vid}",
     *      description="Retrieves a vitals form from an encounter for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *       )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="vid",
     *          in="path",
     *          description="The id for the vitals form.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getVital($pid, $eid, $vid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/encounter/{eid}/soap_note/{sid}",
     *      description="Retrieves a soap note from an encounter for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sid",
     *          in="path",
     *          description="The id for the soap note.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new EncounterRestController())->getSoapNote($pid, $eid, $sid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the soap_note request
     *
     *  @OA\Schema(
     *      schema="api_soap_note_request",
     *      @OA\Property(
     *          property="subjective",
     *          description="The subjective of soap note.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="objective",
     *          description="The objective of soap note.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="assessment",
     *          description="The assessment of soap note.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="plan",
     *          description="The plan of soap note.",
     *          type="string"
     *      ),
     *      example={
     *          "subjective": "The patient with mechanical fall and cut finger.",
     *          "objective": "The patient with finger laceration on exam.",
     *          "assessment": "The patient with finger laceration requiring sutures.",
     *          "plan": "Sutured finger laceration."
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/encounter/{eid}/soap_note",
     *      description="Submits a new soap note",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_soap_note_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->postSoapNote($pid, $eid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/encounter/{eid}/soap_note/{sid}",
     *      description="Edit a soap note",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sid",
     *          in="path",
     *          description="The id for the soap noted.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_soap_note_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid) {
        RestConfig::authorization_check("encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController())->putSoapNote($pid, $eid, $sid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },


    /**
     *  @OA\Get(
     *      path="/api/practitioner",
     *      description="Retrieves a list of practitioners",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="The title for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="fname",
     *          in="query",
     *          description="The first name for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lname",
     *          in="query",
     *          description="The last name for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="mname",
     *          in="query",
     *          description="The middle name for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="federaltaxid",
     *          in="query",
     *          description="The federal tax id for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="federaldrugid",
     *          in="query",
     *          description="The federal drug id for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="upin",
     *          in="query",
     *          description="The upin for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="facility_id",
     *          in="query",
     *          description="The facility id for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="facility",
     *          in="query",
     *          description="The facility for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="npi",
     *          in="query",
     *          description="The npi for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="specialty",
     *          in="query",
     *          description="The specialty for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="billname",
     *          in="query",
     *          description="The billname for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="url",
     *          in="query",
     *          description="The url for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="assistant",
     *          in="query",
     *          description="The assistant for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="organization",
     *          in="query",
     *          description="The organization for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="valedictory",
     *          in="query",
     *          description="The valedictory for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="street",
     *          in="query",
     *          description="The street for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="streetb",
     *          in="query",
     *          description="The street (line 2) for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="city",
     *          in="query",
     *          description="The city for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="state",
     *          in="query",
     *          description="The state for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="The zip for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="fax",
     *          in="query",
     *          description="The fax for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phonew1",
     *          in="query",
     *          description="The phonew1 for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="phonecell",
     *          in="query",
     *          description="The phonecell for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="notes",
     *          in="query",
     *          description="The notes for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="state_license_number2",
     *          in="query",
     *          description="The state license number for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="username",
     *          in="query",
     *          description="The username for the practitioner.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        $return = (new PractitionerRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/practitioner/{pruuid}",
     *      description="Retrieves a single practitioner by their uuid",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pruuid",
     *          in="path",
     *          description="The uuid for the practitioner.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/practitioner/:pruuid" => function ($pruuid) {
        RestConfig::authorization_check("admin", "users");
        $return = (new PractitionerRestController())->getOne($pruuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Post(
     *      path="/api/practitioner",
     *      description="Submits a new practitioner",
     *      tags={"standard"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      description="The title for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fname",
     *                      description="The first name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="mname",
     *                      description="The middle name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="lname",
     *                      description="The last name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="federaltaxid",
     *                      description="The federal tax id for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="federaldrugid",
     *                      description="The federal drug id for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="upin",
     *                      description="The upin for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_id",
     *                      description="The facility_id for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility",
     *                      description="The facility name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="The npi for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="The email for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="specialty",
     *                      description="The specialty for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="billname",
     *                      description="The billname for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="The url for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="assistant",
     *                      description="The assistant for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="valedictory",
     *                      description="The valedictory for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="street",
     *                      description="The street address for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="streetb",
     *                      description="The streetb address for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="city",
     *                      description="The city for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="state",
     *                      description="The state for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="zip",
     *                      description="The zip for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="The phone for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fax",
     *                      description="The fax for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phonew1",
     *                      description="The phonew1 for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phonecell",
     *                      description="The phonecell for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="notes",
     *                      description="The notes for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="state_license_number",
     *                      description="The state license number for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      description="The username for the practitioner.",
     *                      type="string"
     *                  ),
     *                  required={"fname", "lname", "npi"},
     *                  example={
     *                      "title": "Mrs.",
     *                      "fname": "Eduardo",
     *                      "mname": "Kathy",
     *                      "lname": "Perez",
     *                      "federaltaxid": "",
     *                      "federaldrugid": "",
     *                      "upin": "",
     *                      "facility_id": "3",
     *                      "facility": "Your Clinic Name Here",
     *                      "npi": "12345678901",
     *                      "email": "info@pennfirm.com",
     *                      "specialty": "",
     *                      "billname": null,
     *                      "url": null,
     *                      "assistant": null,
     *                      "organization": null,
     *                      "valedictory": null,
     *                      "street": "789 Third Avenue",
     *                      "streetb": "123 Cannaut Street",
     *                      "city": "San Diego",
     *                      "state": "CA",
     *                      "zip": "90210",
     *                      "phone": "(619) 555-9827",
     *                      "fax": null,
     *                      "phonew1": "(619) 555-7822",
     *                      "phonecell": "(619) 555-7821",
     *                      "notes": null,
     *                      "state_license_number": "123456",
     *                      "username": "eduardoperez"
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="validationErrors",
     *                      description="Validation errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="internalErrors",
     *                      description="Internal errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      description="Returned data.",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="id",
     *                              description="practitioner id",
     *                              type="integer",
     *                          ),
     *                          @OA\Property(
     *                              property="uuid",
     *                              description="practitioner uuid",
     *                              type="string",
     *                          ),
     *                      ),
     *                  ),
     *                  example={
     *                      "validationErrors": {},
     *                      "error_description": {},
     *                      "data": {
     *                          "id": 7,
     *                          "uuid": "90d453fb-0248-4c0d-9575-d99d02b169f5"
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "POST /api/practitioner" => function () {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/practitioner/{pruuid}",
     *      description="Edit a practitioner",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pruuid",
     *          in="path",
     *          description="The uuid for the practitioner.",
     *          required=true,
     *          @OA\Schema(
     *          type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      description="The title for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fname",
     *                      description="The first name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="mname",
     *                      description="The middle name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="lname",
     *                      description="The last name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="federaltaxid",
     *                      description="The federal tax id for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="federaldrugid",
     *                      description="The federal drug id for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="upin",
     *                      description="The upin for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility_id",
     *                      description="The facility_id for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="facility",
     *                      description="The facility name for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="The npi for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="The email for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="specialty",
     *                      description="The specialty for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="billname",
     *                      description="The billname for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="The url for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="assistant",
     *                      description="The assistant for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="valedictory",
     *                      description="The valedictory for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="street",
     *                      description="The street address for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="streetb",
     *                      description="The streetb address for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="city",
     *                      description="The city for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="state",
     *                      description="The state for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="zip",
     *                      description="The zip for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="The phone for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fax",
     *                      description="The fax for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phonew1",
     *                      description="The phonew1 for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phonecell",
     *                      description="The phonecell for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="notes",
     *                      description="The notes for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="state_license_number",
     *                      description="The state license number for the practitioner.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      description="The username for the practitioner.",
     *                      type="string"
     *                  ),
     *                  example={
     *                      "title": "Mr",
     *                      "fname": "Baz",
     *                      "mname": "",
     *                      "lname": "Bop",
     *                      "street": "456 Tree Lane",
     *                      "zip": "08642",
     *                      "city": "FooTown",
     *                      "state": "FL",
     *                      "phone": "123-456-7890"
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="validationErrors",
     *                      description="Validation errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="internalErrors",
     *                      description="Internal errors.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      description="Returned data.",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="id",
     *                              description="practitioner id",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="uuid",
     *                              description="practitioner uuid",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="title",
     *                              description="practitioner title",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="fname",
     *                              description="practitioner fname",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="lname",
     *                              description="practitioner lname",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="mname",
     *                              description="practitioner mname",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="federaltaxid",
     *                              description="practitioner federaltaxid",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="federaldrugid",
     *                              description="practitioner federaldrugid",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="upin",
     *                              description="practitioner upin",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="facility_id",
     *                              description="practitioner facility_id",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="facility",
     *                              description="practitioner facility",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="npi",
     *                              description="practitioner npi",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="email",
     *                              description="practitioner email",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="active",
     *                              description="practitioner active setting",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="specialty",
     *                              description="practitioner specialty",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="billname",
     *                              description="practitioner billname",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="url",
     *                              description="practitioner url",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="assistant",
     *                              description="practitioner assistant",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="organization",
     *                              description="practitioner organization",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="valedictory",
     *                              description="practitioner valedictory",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="street",
     *                              description="practitioner street",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="streetb",
     *                              description="practitioner streetb",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="city",
     *                              description="practitioner city",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="state",
     *                              description="practitioner state",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="zip",
     *                              description="practitioner zip",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="phone",
     *                              description="practitioner phone",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="fax",
     *                              description="fax",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="phonew1",
     *                              description="practitioner phonew1",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="phonecell",
     *                              description="practitioner phonecell",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="notes",
     *                              description="practitioner notes",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="state_license_number",
     *                              description="practitioner state license number",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="abook_title",
     *                              description="practitioner abook title",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="physician_title",
     *                              description="practitioner physician title",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="physician_code",
     *                              description="practitioner physician code",
     *                              type="string",
     *                          )
     *                      ),
     *                  ),
     *                  example={
     *                      "validationErrors": {},
     *                      "error_description": {},
     *                      "data": {
     *                          "id": 7,
     *                          "uuid": "90d453fb-0248-4c0d-9575-d99d02b169f5",
     *                          "title": "Mr",
     *                          "fname": "Baz",
     *                          "lname": "Bop",
     *                          "mname": "",
     *                          "federaltaxid": "",
     *                          "federaldrugid": "",
     *                          "upin": "",
     *                          "facility_id": "3",
     *                          "facility": "Your Clinic Name Here",
     *                          "npi": "0123456789",
     *                          "email": "info@pennfirm.com",
     *                          "active": "1",
     *                          "specialty": "",
     *                          "billname": "",
     *                          "url": "",
     *                          "assistant": "",
     *                          "organization": "",
     *                          "valedictory": "",
     *                          "street": "456 Tree Lane",
     *                          "streetb": "123 Cannaut Street",
     *                          "city": "FooTown",
     *                          "state": "FL",
     *                          "zip": "08642",
     *                          "phone": "123-456-7890",
     *                          "fax": "",
     *                          "phonew1": "(619) 555-7822",
     *                          "phonecell": "(619) 555-7821",
     *                          "notes": "",
     *                          "state_license_number": "123456",
     *                          "abook_title": null,
     *                          "physician_title": null,
     *                          "physician_code": null
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "PUT /api/practitioner/:pruuid" => function ($pruuid) {
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->patch($pruuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/medical_problem",
     *      description="Retrieves a list of medical problems",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="query",
     *          description="The uuid for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="condition_uuid",
     *          in="query",
     *          description="The uuid for the medical problem.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="The title for the medical problem.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="begdate",
     *          in="query",
     *          description="The start date for the medical problem.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="enddate",
     *          in="query",
     *          description="The end date for the medical problem.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="diagnosis",
     *          in="query",
     *          description="The diagnosis for the medical problem.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/medical_problem" => function () {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/medical_problem/{muuid}",
     *      description="Retrieves a single medical problem by their uuid",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="muuid",
     *          in="path",
     *          description="The uuid for the medical problem.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/medical_problem/:muuid" => function ($muuid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getOne($muuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/medical_problem",
     *      description="Retrieves all medical problems for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::authorization_check("encounters", "notes");
        $return = (new ConditionRestController())->getAll($puuid, "medical_problem");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/medical_problem/{muuid}",
     *      description="Retrieves a medical problem for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="muuid",
     *          in="path",
     *          description="The uuid for the medical problem.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ConditionRestController())->getAll(['puuid' => $puuid, 'condition_uuid' => $muuid]);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the medical_problem request
     *
     *  @OA\Schema(
     *      schema="api_medical_problem_request",
     *      @OA\Property(
     *          property="title",
     *          description="The title of medical problem.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="begdate",
     *          description="The beginning date of medical problem.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="enddate",
     *          description="The end date of medical problem.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="diagnosis",
     *          description="The diagnosis of medical problem. In format `<codetype>:<code>`",
     *          type="string"
     *      ),
     *      required={"title", "begdate"},
     *      example={
     *          "title": "Dermatochalasis",
     *          "begdate": "2010-10-13",
     *          "enddate": null,
     *          "diagnosis": "ICD10:H02.839"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{puuid}/medical_problem",
     *      description="Submits a new medical problem",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_medical_problem_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:puuid/medical_problem" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{puuid}/medical_problem/{muuid}",
     *      description="Edit a medical problem",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="muuid",
     *          in="path",
     *          description="The uuid for the medical problem.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_medical_problem_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->put($puuid, $muuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{puuid}/medical_problem/{muuid}",
     *      description="Delete a medical problem",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="muuid",
     *          in="path",
     *          description="The uuid for the medical problem.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ConditionRestController())->delete($puuid, $muuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/allergy",
     *      description="Retrieves a list of allergies",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="lists.pid",
     *          in="query",
     *          description="The uuid for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lists.id",
     *          in="query",
     *          description="The uuid for the allergy.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="The title for the allergy.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="begdate",
     *          in="query",
     *          description="The start date for the allergy.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="enddate",
     *          in="query",
     *          description="The end date for the allergy.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="diagnosis",
     *          in="query",
     *          description="The diagnosis for the allergy.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/allergy" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/allergy/{auuid}",
     *      description="Retrieves a single allergy by their uuid",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="auuid",
     *          in="path",
     *          description="The uuid for the allergy.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/allergy/:auuid" => function ($auuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getOne($auuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/allergy",
     *      description="Retrieves all allergies for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid]);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/allergy/{auuid}",
     *      description="Retrieves a allergy for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="auuid",
     *          in="path",
     *          description="The uuid for the allergy.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $auuid]);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the allergy request
     *
     *  @OA\Schema(
     *      schema="api_allergy_request",
     *      @OA\Property(
     *          property="title",
     *          description="The title of allergy.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="begdate",
     *          description="The beginning date of allergy.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="enddate",
     *          description="The end date of allergy.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="diagnosis",
     *          description="The diagnosis of allergy. In format `<codetype>:<code>`",
     *          type="string"
     *      ),
     *      required={"title", "begdate"},
     *      example={
     *          "title": "Iodine",
     *          "begdate": "2010-10-13",
     *          "enddate": null
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{puuid}/allergy",
     *      description="Submits a new allergy",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_allergy_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:puuid/allergy" => function ($puuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->post($puuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{puuid}/allergy/{auuid}",
     *      description="Edit a allergy",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="auuid",
     *          in="path",
     *          description="The uuid for the allergy.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_allergy_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->put($puuid, $auuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{puuid}/allergy/{auuid}",
     *      description="Delete a medical problem",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="puuid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="auuid",
     *          in="path",
     *          description="The uuid for the allergy.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *      )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new AllergyIntoleranceRestController())->delete($puuid, $auuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/medication",
     *      description="Retrieves all medications for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/medication" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "medication");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the medication request
     *
     *  @OA\Schema(
     *      schema="api_medication_request",
     *      @OA\Property(
     *          property="title",
     *          description="The title of medication.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="begdate",
     *          description="The beginning date of medication.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="enddate",
     *          description="The end date of medication.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="diagnosis",
     *          description="The diagnosis of medication. In format `<codetype>:<code>`",
     *          type="string"
     *      ),
     *      required={"title", "begdate"},
     *      example={
     *          "title": "Norvasc",
     *          "begdate": "2013-04-13",
     *          "enddate": null
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/medication",
     *      description="Submits a new medication",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_medication_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/medication" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "medication", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/medication/{mid}",
     *      description="Edit a medication",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="mid",
     *          in="path",
     *          description="The id for the medication.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_medication_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $mid, "medication", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/medication/{mid}",
     *      description="Retrieves a medication for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="mid",
     *          in="path",
     *          description="The id for the medication.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "medication", $mid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{pid}/medication/{mid}",
     *      description="Delete a medication",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="mid",
     *          in="path",
     *          description="The id for the medication.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:pid/medication/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $mid, "medication");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/surgery",
     *      description="Retrieves all surgeries for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "surgery");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/surgery/{sid}",
     *      description="Retrieves a surgery for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sid",
     *          in="path",
     *          description="The id for the surgery.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "surgery", $sid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{pid}/surgery/{sid}",
     *      description="Delete a surgery",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sid",
     *          in="path",
     *          description="The id for the surgery.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $sid, "surgery");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the surgery request
     *
     *  @OA\Schema(
     *      schema="api_surgery_request",
     *      @OA\Property(
     *          property="title",
     *          description="The title of surgery.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="begdate",
     *          description="The beginning date of surgery.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="enddate",
     *          description="The end date of surgery.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="diagnosis",
     *          description="The diagnosis of surgery. In format `<codetype>:<code>`",
     *          type="string"
     *      ),
     *      required={"title", "begdate"},
     *      example={
     *          "title": "Blepharoplasty",
     *          "begdate": "2013-10-14",
     *          "enddate": null,
     *          "diagnosis": "CPT4:15823-50"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/surgery",
     *      description="Submits a new surgery",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_surgery_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/surgery" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "surgery", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/surgery/{sid}",
     *      description="Edit a surgery",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sid",
     *          in="path",
     *          description="The id for the surgery.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_surgery_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/surgery/:sid" => function ($pid, $sid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $sid, "surgery", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/dental_issue",
     *      description="Retrieves all dental issues for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getAll($pid, "dental");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/dental_issue/{did}",
     *      description="Retrieves a dental issue for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="did",
     *          in="path",
     *          description="The id for the dental issue.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->getOne($pid, "dental", $did);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{pid}/dental_issue/{did}",
     *      description="Delete a dental issue",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="did",
     *          in="path",
     *          description="The id for the dental issue.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ListRestController())->delete($pid, $did, "dental");
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the dental_issue request
     *
     *  @OA\Schema(
     *      schema="api_dental_issue_request",
     *      @OA\Property(
     *          property="title",
     *          description="The title of dental issue.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="begdate",
     *          description="The beginning date of dental issue.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="enddate",
     *          description="The end date of dental issue.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="diagnosis",
     *          description="The diagnosis of dental issue. In format `<codetype>:<code>`",
     *          type="string"
     *      ),
     *      required={"title", "begdate"},
     *      example={
     *          "title": "Halitosis",
     *          "begdate": "2015-03-17",
     *          "enddate": null,
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/dental_issue",
     *      description="Submits a new dental issue",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_dental_issue_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/dental_issue" => function ($pid) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "dental", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/dental_issue/{did}",
     *      description="Edit a dental issue",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="did",
     *          in="path",
     *          description="The id for the dental issue.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_dental_issue_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/dental_issue/:did" => function ($pid, $did) {
        RestConfig::authorization_check("patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $did, "dental", $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/appointment",
     *      description="Retrieves all appointments for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getAllForPatient($pid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/appointment",
     *      description="Submits a new appointment",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
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
     *                  @OA\Property(
     *                      property="pc_catid",
     *                      description="The category of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_title",
     *                      description="The title of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_duration",
     *                      description="The duration of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_hometext",
     *                      description="Comments for the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_apptstatus",
     *                      description="use an option from resource=/api/list/apptstat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_eventDate",
     *                      description="The date of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_startTime",
     *                      description="The time of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_facility",
     *                      description="The facility id of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_billing_location",
     *                      description="The billinag location id of the appointment.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="pc_aid",
     *                      description="The provider id for the appointment.",
     *                      type="string"
     *                  ),
     *                  required={"pc_catid", "pc_title", "pc_duration", "pc_hometext", "pc_apptstatus", "pc_eventDate", "pc_startTime", "pc_facility", "pc_billing_location"},
     *                  example={
     *                      "pc_catid": "5",
     *                      "pc_title": "Office Visit",
     *                      "pc_duration": "900",
     *                      "pc_hometext": "Test",
     *                      "pc_apptstatus": "-",
     *                      "pc_eventDate": "2018-10-19",
     *                      "pc_startTime": "09:00",
     *                      "pc_facility": "9",
     *                      "pc_billing_location": "10",
     *                      "pc_aid": "1"
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/appointment" => function ($pid) {
        RestConfig::authorization_check("patients", "appt");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AppointmentRestController())->post($pid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/appointment",
     *      description="Retrieves all appointments",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/appointment" => function () {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/appointment/{eid}",
     *      description="Retrieves an appointment",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The eid for the appointment.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/appointment/:eid" => function ($eid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{pid}/appointment/{eid}",
     *      description="Delete a appointment",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The eid for the appointment.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->delete($eid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/appointment/{eid}",
     *      description="Retrieves a appointment for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The eid for the appointment.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/appointment/:eid" => function ($pid, $eid) {
        RestConfig::authorization_check("patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/list/{list_name}",
     *      description="Retrieves a list",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="list_name",
     *          in="path",
     *          description="The list_id of the list.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/list/:list_name" => function ($list_name) {
        RestConfig::authorization_check("lists", "default");
        $return = (new ListRestController())->getOptions($list_name);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/version",
     *      description="Retrieves the OpenEMR version information",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/version" => function () {
        $return = (new VersionRestController())->getOne();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/product",
     *      description="Retrieves the OpenEMR product registration information",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/product" => function () {
        $return = (new ProductRegistrationRestController())->getOne();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/insurance_company",
     *      description="Retrieves all insurance companies",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/insurance_company" => function () {
        $return = (new InsuranceCompanyRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/insurance_company/{iid}",
     *      description="Retrieves insurance company",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="iid",
     *          in="path",
     *          description="The id of the insurance company.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/insurance_company/:iid" => function ($iid) {
        $return = (new InsuranceCompanyRestController())->getOne($iid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/insurance_type",
     *      description="Retrieves all insurance types",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/insurance_type" => function () {
        $return = (new InsuranceCompanyRestController())->getInsuranceTypes();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the insurance_company request
     *
     *  @OA\Schema(
     *      schema="api_insurance_company_request",
     *      @OA\Property(
     *          property="name",
     *          description="The name of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="attn",
     *          description="The attn of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="cms_id",
     *          description="The cms id of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="ins_type_code",
     *          description="The insurance type code of insurance company. The insurance type code can be found by inspecting the route at (/api/insurance_type).",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="x12_receiver_id",
     *          description="The x12 receiver id of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="x12_default_partner_id",
     *          description="The x12 default partner id of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="alt_cms_id",
     *          description="The alternate cms id of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="line1",
     *          description="The line1 address of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="line2",
     *          description="The line2 address of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="city",
     *          description="The city of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="state",
     *          description="The state of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="zip",
     *          description="The zip of insurance company.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="country",
     *          description="The country of insurance company.",
     *          type="string"
     *      ),
     *      required={"name"},
     *      example={
     *          "name": "Cool Insurance Company",
     *          "attn": null,
     *          "cms_id": null,
     *          "ins_type_code": "2",
     *          "x12_receiver_id": null,
     *          "x12_default_partner_id": null,
     *          "alt_cms_id": "",
     *          "line1": "123 Cool Lane",
     *          "line2": "Suite 123",
     *          "city": "Cooltown",
     *          "state": "CA",
     *          "zip": "12245",
     *          "country": "USA"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/insurance_company",
     *      description="Submits a new insurance company",
     *      tags={"standard"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_insurance_company_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/insurance_company" => function () {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->post($data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/insurance_company/{iid}",
     *      description="Edit a insurance company",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="iid",
     *          in="path",
     *          description="The id for the insurance company.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_insurance_company_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/insurance_company/:iid" => function ($iid) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->put($iid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/document",
     *      description="Submits a new patient document",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="path",
     *          in="query",
     *          description="The category of the document.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="document",
     *                      description="document",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/document" => function ($pid) {
        $return = (new DocumentRestController())->postWithPath($pid, $_GET['path'], $_FILES['document']);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/document",
     *      description="Retrieves all file information of documents from a category for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="path",
     *          in="query",
     *          description="The category of the documents.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/document" => function ($pid) {
        $return = (new DocumentRestController())->getAllAtPath($pid, $_GET['path']);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/document/{did}",
     *      description="Retrieves a document for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="did",
     *          in="path",
     *          description="The id for the patient document.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/document/:did" => function ($pid, $did) {
        $return = (new DocumentRestController())->downloadFile($pid, $did);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/insurance",
     *      description="Retrieves all insurances for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/insurance" => function ($pid) {
        $return = (new InsuranceRestController())->getAll($pid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/insurance/{type}",
     *      description="Retrieves a insurance (by type) for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="path",
     *          description="The insurance type for the patient. (options are 'primary', 'secondary', or 'tertiary')",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $return = (new InsuranceRestController())->getOne($pid, $type);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the insurance request
     *
     *  @OA\Schema(
     *      schema="api_insurance_request",
     *      @OA\Property(
     *          property="provider",
     *          description="The insurance company id.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="plan_name",
     *          description="The plan name of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="policy_number",
     *          description="The policy number of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="group_number",
     *          description="The group number of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_lname",
     *          description="The subscriber last name of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_mname",
     *          description="The subscriber middle name of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_fname",
     *          description="The subscriber first name of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_relationship",
     *          description="The subscriber relationship of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_ss",
     *          description="The subscriber ss number of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_DOB",
     *          description="The subscriber DOB of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_street",
     *          description="The subscriber street address of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_postal_code",
     *          description="The subscriber postal code of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_city",
     *          description="The subscriber city of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_state",
     *          description="The subscriber state of insurance. `state` can be found by querying `resource=/api/list/state`",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_country",
     *          description="The subscriber country of insurance. `country` can be found by querying `resource=/api/list/country`",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_phone",
     *          description="The subscriber phone of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_employer",
     *          description="The subscriber employer of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_employer_street",
     *          description="The subscriber employer street of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_employer_postal_code",
     *          description="The subscriber employer postal code of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_employer_state",
     *          description="The subscriber employer state of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_employer_country",
     *          description="The subscriber employer country of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_employer_city",
     *          description="The subscriber employer city of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="copay",
     *          description="The copay of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="date",
     *          description="The date of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_sex",
     *          description="The subscriber sex of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="accept_assignment",
     *          description="The accept_assignment of insurance.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="policy_type",
     *          description="The policy_type of insurance.",
     *          type="string"
     *      ),
     *      required={"provider", "plan_name", "policy_number", "group_number", "subscriber_fname", "subscriber_lname", "subscriber_relationship", "subscriber_ss", "subscriber_DOB", "subscriber_street", "subscriber_postal_code", "subscriber_city", "subscriber_state", "subscriber_country", "subscriber_phone", "subscriber_sex", "accept_assignment", "policy_type"},
     *      example={
     *          "provider": "33",
     *          "plan_name": "Some Plan",
     *          "policy_number": "12345",
     *          "group_number": "252412",
     *          "subscriber_lname": "Tester",
     *          "subscriber_mname": "Xi",
     *          "subscriber_fname": "Foo",
     *          "subscriber_relationship": "other",
     *          "subscriber_ss": "234231234",
     *          "subscriber_DOB": "2018-10-03",
     *          "subscriber_street": "183 Cool St",
     *          "subscriber_postal_code": "23418",
     *          "subscriber_city": "Cooltown",
     *          "subscriber_state": "AZ",
     *          "subscriber_country": "USA",
     *          "subscriber_phone": "234-598-2123",
     *          "subscriber_employer": "Some Employer",
     *          "subscriber_employer_street": "123 Heather Lane",
     *          "subscriber_employer_postal_code": "23415",
     *          "subscriber_employer_state": "AZ",
     *          "subscriber_employer_country": "USA",
     *          "subscriber_employer_city": "Cooltown",
     *          "copay": "35",
     *          "date": "2018-10-15",
     *          "subscriber_sex": "Female",
     *          "accept_assignment": "TRUE",
     *          "policy_type": "a"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/insurance/{type}",
     *      description="Submits a new patient insurance (with type)",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="path",
     *          description="The insurance type for the patient. (options are 'primary', 'secondary', or 'tertiary')",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_insurance_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->post($pid, $type, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/insurance/{type}",
     *      description="Edit a patient insurance (by type)",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="path",
     *          description="The insurance type for the patient. (options are 'primary', 'secondary', or 'tertiary')",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_insurance_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/insurance/:type" => function ($pid, $type) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->put($pid, $type, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     * Schema for the message request
     *
     *  @OA\Schema(
     *      schema="api_message_request",
     *      @OA\Property(
     *          property="body",
     *          description="The body of message.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="groupname",
     *          description="The group name (usually is 'Default').",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="from",
     *          description="The sender of the message.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="to",
     *          description="The recipient of the message.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="title",
     *          description="use an option from resource=/api/list/note_type",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="message_status",
     *          description="use an option from resource=/api/list/message_status",
     *          type="string"
     *      ),
     *      required={"body", "groupname", "from", "to", "title", "message_status"},
     *      example={
     *          "body": "Test 456",
     *          "groupname": "Default",
     *          "from": "Matthew",
     *          "to": "admin",
     *          "title": "Other",
     *          "message_status": "New"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/message",
     *      description="Submits a pnote message",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_message_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/message" => function ($pid) {
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->post($pid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{pid}/transaction",
     *      description="Get Transactions for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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

    "GET /api/patient/:pid/transaction" => function ($pid) {
        RestConfig::authorization_check("patients", "trans");
        $cont = new TransactionRestController();
        $return = (new TransactionRestController())->GetPatientTransactions($pid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     * Schema for the transaction request
     *
     *  @OA\Schema(
     *      schema="api_transaction_request",
     *      @OA\Property(
     *          property="message",
     *          description="The message of the transaction.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="type",
     *          description="The type of transaction. Use an option from resource=/api/transaction_type",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="groupname",
     *          description="The group name (usually is 'Default').",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="referByNpi",
     *          description="NPI of the person creating the referral.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="referToNpi",
     *          description="NPI of the person getting the referral.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="referDiagnosis",
     *          description="The referral diagnosis.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="riskLevel",
     *          description="The risk level. (Low, Medium, High)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="includeVitals",
     *          description="Are vitals included (0,1)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="referralDate",
     *          description="The date of the referral",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="authorization",
     *          description="The authorization for the referral",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="visits",
     *          description="The number of vists for the referral",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="validFrom",
     *          description="The date the referral is valid from",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="validThrough",
     *          description="The date the referral is valid through",
     *          type="string"
     *      ),
     *      required={"message", "groupname", "title"},
     *      example={
     *          "message": "Message",
     *          "type": "LBTref",
     *          "groupname": "Default",
     *          "referByNpi":"9999999999",
     *          "referToNpi":"9999999999",
     *          "referDiagnosis":"Diag 1",
     *          "riskLevel":"Low",
     *          "includeVitals":"1",
     *          "referralDate":"2022-01-01",
     *          "authorization":"Auth_123",
     *          "visits": "1",
     *          "validFrom": "2022-01-02",
     *          "validThrough": "2022-01-03",
     *          "body": "Reason 1"
     *      }
     *  )
     */
    /**
     *  @OA\Post(
     *      path="/api/patient/{pid}/transaction",
     *      description="Submits a transaction",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The pid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_transaction_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "POST /api/patient/:pid/transaction" => function ($pid) {
        RestConfig::authorization_check("patients", "trans");
         $data = (array) (json_decode(file_get_contents("php://input")));
         $return = (new TransactionRestController())->CreateTransaction($pid, $data);
         RestConfig::apiLog($return, $data);
         return $return;
    },

    /**
     *  @OA\PUT(
     *      path="/api/transaction/{tid}",
     *      description="Updates a transaction",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="tid",
     *          in="path",
     *          description="The id for the transaction.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_transaction_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/transaction/:tid" => function ($tid) {
        RestConfig::authorization_check("patients", "trans");
         $data = (array) (json_decode(file_get_contents("php://input")));
         $return = (new TransactionRestController())->UpdateTransaction($tid, $data);
         RestConfig::apiLog($return, $data);
         return $return;
    },

    /**
     *  @OA\Put(
     *      path="/api/patient/{pid}/message/{mid}",
     *      description="Edit a pnote message",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="mid",
     *          in="path",
     *          description="The id for the pnote message.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_message_request")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "PUT /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->put($pid, $mid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Delete(
     *      path="/api/patient/{pid}/message/{mid}",
     *      description="Delete a pnote message",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The id for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="eid",
     *          in="path",
     *          description="The id for the pnote message.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "DELETE /api/patient/:pid/message/:mid" => function ($pid, $mid) {
        RestConfig::authorization_check("patients", "notes");
        $return = (new MessageRestController())->delete($pid, $mid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/immunization",
     *      description="Retrieves a list of immunizations",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="patient_id",
     *          in="query",
     *          description="The pid for the patient.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="The id for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="uuid",
     *          in="query",
     *          description="The uuid for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="administered_date",
     *          in="query",
     *          description="The administered date for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="immunization_id",
     *          in="query",
     *          description="The immunization list_id for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="cvx_code",
     *          in="query",
     *          description="The cvx code for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="manufacturer",
     *          in="query",
     *          description="The manufacturer for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lot_number",
     *          in="query",
     *          description="The lot number for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="administered_by_id",
     *          in="query",
     *          description="The administered by id for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="administered_by",
     *          in="query",
     *          description="The administered by for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="education_date",
     *          in="query",
     *          description="The education date for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="vis_date",
     *          in="query",
     *          description="The vis date for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="note",
     *          in="query",
     *          description="The note for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="create_date",
     *          in="query",
     *          description="The create date for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="update_date",
     *          in="query",
     *          description="The update date for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="created_by",
     *          in="query",
     *          description="The created_by for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="updated_by",
     *          in="query",
     *          description="The updated_by for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="amount_administered",
     *          in="query",
     *          description="The amount administered for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="amount_administered_unit",
     *          in="query",
     *          description="The amount administered unit for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="expiration_date",
     *          in="query",
     *          description="The expiration date for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="route",
     *          in="query",
     *          description="The route for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="administration_site",
     *          in="query",
     *          description="The administration site for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="added_erroneously",
     *          in="query",
     *          description="The added_erroneously for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="external_id",
     *          in="query",
     *          description="The external_id for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="completion_status",
     *          in="query",
     *          description="The completion status for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="information_source",
     *          in="query",
     *          description="The information source for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="refusal_reason",
     *          in="query",
     *          description="The refusal reason for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="ordering_provider",
     *          in="query",
     *          description="The ordering provider for the immunization.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/immunization" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new ImmunizationRestController())->getAll($_GET);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/immunization/{uuid}",
     *      description="Retrieves a immunization",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the immunization.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/immunization/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ImmunizationRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/procedure",
     *      description="Retrieves a list of all procedures",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/procedure" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new ProcedureRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/procedure/{uuid}",
     *      description="Retrieves a procedure",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the procedure.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/procedure/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new ProcedureRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/drug",
     *      description="Retrieves a list of all drugs",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/drug" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new DrugRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/drug/{uuid}",
     *      description="Retrieves a drug",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the drug.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/drug/:uuid" => function ($uuid) {
        RestConfig::authorization_check("patients", "med");
        $return = (new DrugRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/prescription",
     *      description="Retrieves a list of all prescriptions",
     *      tags={"standard"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /api/prescription" => function () {
        RestConfig::authorization_check("patients", "med");
        $return = (new PrescriptionRestController())->getAll();
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/prescription/{uuid}",
     *      description="Retrieves a prescription",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the prescription.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationExportRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationDocRefRestController;

// Note that the fhir route includes both user role and patient role
//  (there is a mechanism in place to ensure patient role is binded
//   to only see the data of the one patient)
RestConfig::$FHIR_ROUTE_MAP = array(
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirAllergyIntoleranceRestController($request))->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCarePlanRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCarePlanRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirCareTeamRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirCoverageRestController())->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirCoverageRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDeviceRestController())->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDeviceRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDocumentReferenceRestController($request))->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "demo");
            $return = (new FhirOperationDocRefRestController($request))->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
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
     *                                  "url": "https://localhost:9300/apis/default/fhir/Document/7/Binary"
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirDocumentReferenceRestController($request))->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Document/{id}/Binary",
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
    'GET /fhir/Document/:id/Binary' => function ($documentId, HttpRestRequest $request) {
        // TODO: @adunsulag we need to be able to retrieve our CCDA documents this way...
        // currently only allow users with the same permissions as export to take a file out
        // this could be relaxed to allow other types of files ie such as patient access etc.
        RestConfig::authorization_check("admin", "users");

        // Grab the document id
        $docController = new \OpenEMR\RestControllers\FHIR\FhirDocumentRestController($request);
        $response = $docController->downloadDocument($documentId);
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
            RestConfig::authorization_check("encounters", "auth_a");
            $return = (new FhirEncounterRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirEncounterRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirGoalRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirGoalRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
     *  @OA\Get(
     *      path="/fhir/Group/{id}/$export",
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
    'GET /fhir/Group/:id/$export' => function ($groupId, HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirOperationExportRestController($request);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirImmunizationRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirImmunizationRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
        $return = (new FhirLocationRestController())->getAll($request->getQueryParams(), $request->getPatientUUIDString());
        RestConfig::apiLog($return);
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
        $return = (new FhirLocationRestController())->getOne($uuid, $request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Medication",
     *      description="Returns a list of Medication resources.",
     *      tags={"fhir"},
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
        RestConfig::authorization_check("patients", "med");
        $return = (new FhirMedicationRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirMedicationRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirMedicationRequestRestController())->getAll($getParams);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirMedicationRequestRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Observation",
     *      description="Returns a list of Observation resources.",
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
     *          description="The category of the Observation resource.",
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
            RestConfig::authorization_check("admin", "users");
        }
        $return = (new FhirOrganizationRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "users");
        } else {
            $patientUUID = $request->getPatientUUIDString();
        }
        $return = (new FhirOrganizationRestController())->getOne($uuid, $patientUUID);

        RestConfig::apiLog($return);
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
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->post($data);
        RestConfig::apiLog($return, $data);
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
        RestConfig::authorization_check("admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirOrganizationRestController())->patch($uuid, $data);
        RestConfig::apiLog($return, $data);
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
    "POST /fhir/Patient" => function (HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->post($data);
        RestConfig::apiLog($return, $data);
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
    "PUT /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::authorization_check("patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPatientRestController())->put($uuid, $data);
        RestConfig::apiLog($return, $data);
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
    'GET /fhir/Patient/$export' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirOperationExportRestController($request);
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
    "GET /fhir/Patient/:uuid" => function ($uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            // only allow access to data of binded patient
            if (empty($uuid) || ($uuid != $request->getPatientUUIDString())) {
                throw new AccessDeniedException("patients", "demo", "patient id invalid");
            }
            $uuid = $request->getPatientUUIDString();
        } else {
            RestConfig::authorization_check("patients", "demo");
        }
        $return = (new FhirPatientRestController())->getOne($uuid);
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/Person",
     *      description="Returns a list of Person resources.",
     *      tags={"fhir"},
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
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPersonRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
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
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPersonRestController())->getOne($uuid);
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "users");
        }
        $return = (new FhirPractitionerRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "users");
        }
        $return = (new FhirPractitionerRestController())->getOne($uuid);
        RestConfig::apiLog($return);
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
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->post($data);
        RestConfig::apiLog($return, $data);
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
        RestConfig::authorization_check("admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input"), true));
        $return = (new FhirPractitionerRestController())->patch($uuid, $data);
        RestConfig::apiLog($return, $data);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/fhir/PractitionerRole",
     *      description="Returns a list of PractitionerRole resources.",
     *      tags={"fhir"},
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
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getAll($request->getQueryParams());
        RestConfig::apiLog($return);
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
        RestConfig::authorization_check("admin", "users");
        $return = (new FhirPractitionerRoleRestController())->getOne($uuid);
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirProcedureRestController())->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("patients", "med");
            $return = (new FhirProcedureRestController())->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirProvenanceRestController($request))->getOne($uuid);
        }
        RestConfig::apiLog($return);
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
            RestConfig::authorization_check("admin", "super");
            $return = (new FhirProvenanceRestController($request))->getAll($request->getQueryParams());
        }
        RestConfig::apiLog($return);
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
    "GET /fhir/metadata" => function () {
        $return = (new FhirMetaDataRestController())->getMetaData();
        RestConfig::apiLog($return);
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
        $authController = new \OpenEMR\RestControllers\AuthorizationController();
        $return = (new \OpenEMR\RestControllers\SMART\SMARTConfigurationController($authController))->getConfig();
        RestConfig::apiLog($return);
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
    'GET /fhir/$export' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $fhirExportService = new FhirOperationExportRestController($request);
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
    'GET /fhir/$bulkdata-status' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $jobUuidString = $request->getQueryParam('job');
        // if we were truly async we would return 202 here to say we are in progress with a JSON response
        // since OpenEMR data is so small we just return the JSON from the database
        $fhirExportService = new FhirOperationExportRestController($request);
        $return = $fhirExportService->processExportStatusRequestForJob($jobUuidString);
        RestConfig::apiLog($return);
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
    'DELETE /fhir/$bulkdata-status' => function (HttpRestRequest $request) {
        RestConfig::authorization_check("admin", "users");
        $job = $request->getQueryParam('job');
        $fhirExportService = new FhirOperationExportRestController($request);
        $return = $fhirExportService->processDeleteExportForJob($job);
        RestConfig::apiLog($return);
        return $return;
    },
);

// Note that the portal (api) route is only for patient role
//  (there is a mechanism in place to ensure only patient role can access the portal (api) route)
RestConfig::$PORTAL_ROUTE_MAP = array(
    /**
     *  @OA\Get(
     *      path="/portal/patient",
     *      description="Returns the patient.",
     *      tags={"standard-patient"},
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/api_patient_response")
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    "GET /portal/patient" => function (HttpRestRequest $request) {
        $return = (new PatientRestController())->getOne($request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/portal/patient/encounter",
     *      description="Returns encounters for the patient.",
     *      tags={"standard-patient"},
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /portal/patient/encounter" => function (HttpRestRequest $request) {
        $return = (new EncounterRestController())->getAll($request->getPatientUUIDString());
        RestConfig::apiLog($return);
        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/portal/patient/encounter/{euuid}",
     *      description="Returns a selected encounter by its uuid.",
     *      tags={"standard-patient"},
     *      @OA\Parameter(
     *          name="euuid",
     *          in="path",
     *          description="The uuid for the encounter.",
     *          required=true,
     *          @OA\Schema(
     *          type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
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
    "GET /portal/patient/encounter/:euuid" => function ($euuid, HttpRestRequest $request) {
        $return = (new EncounterRestController())->getOne($request->getPatientUUIDString(), $euuid);
        RestConfig::apiLog($return);
        return $return;
    }
);
