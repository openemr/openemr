<?php

/**
 * Standard API Routes
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

use OpenApi\Annotations as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\RestControllers\AllergyIntoleranceRestController;
use OpenEMR\RestControllers\AppointmentRestController;
use OpenEMR\RestControllers\ConditionRestController;
use OpenEMR\RestControllers\Config\RestConfig;
use OpenEMR\RestControllers\DocumentRestController;
use OpenEMR\RestControllers\DrugRestController;
use OpenEMR\RestControllers\EmployerRestController;
use OpenEMR\RestControllers\EncounterRestController;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\RestControllers\ImmunizationRestController;
use OpenEMR\RestControllers\InsuranceCompanyRestController;
use OpenEMR\RestControllers\InsuranceRestController;
use OpenEMR\RestControllers\ListRestController;
use OpenEMR\RestControllers\MessageRestController;
use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\RestControllers\PractitionerRestController;
use OpenEMR\RestControllers\PrescriptionRestController;
use OpenEMR\RestControllers\ProcedureRestController;
use OpenEMR\RestControllers\ProductRegistrationRestController;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\RestControllers\TransactionRestController;
use OpenEMR\RestControllers\VersionRestController;
use OpenEMR\Services\Search\SearchQueryConfig;

return [
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
    "GET /api/facility" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FacilityRestController())->getAll($request, $_GET);
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
    "GET /api/facility/:fuuid" => function ($fuuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new FacilityRestController())->getOne($fuuid, $request);

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
    "POST /api/facility" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new FacilityRestController())->post($data, $request);

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
    "PUT /api/facility/:fuuid" => function ($fuuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "super");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return =  (new FacilityRestController())->patch($fuuid, $data, $request);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient",
     *      description="Retrieves a list of patients",
     *      tags={"standard"},
     *      @OA\Parameter(
     *        ref="#/components/parameters/_sort"
     *      ),
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
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="The date this patient resource was last modified.",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="_offset",
     *          in="query",
     *          description="The number of records to offset from this index in the search result.",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="_limit",
     *          in="query",
     *          description="The maximum number of resources to return in the result set. 0 means unlimited.",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *              ,minimum=0
     *              ,maximum=200
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
    "GET /api/patient" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $config = SearchQueryConfig::createConfigFromQueryParams($request->query->all());
        $return = (new PatientRestController())->getAll($request, $request->query->all(), $config);

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
    "POST /api/patient" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->post($data, $request);

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
    "PUT /api/patient/:puuid" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PatientRestController())->put($puuid, $data, $request);

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
    "GET /api/patient/:puuid" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo");
        $return = (new PatientRestController())->getOne($puuid, $request);

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
    "GET /api/patient/:puuid/encounter" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $return = (new EncounterRestController($request->getSession()))->getAll($puuid);

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
    "POST /api/patient/:puuid/encounter" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->post($puuid, $data, $request);

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
    "PUT /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->put($puuid, $euuid, $data);

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
    "GET /api/patient/:puuid/encounter/:euuid" => function ($puuid, $euuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "auth_a");
        $return = (new EncounterRestController($request->getSession()))->getOne($puuid, $euuid);

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
    "GET /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getSoapNotes($pid, $eid);

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
    "POST /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController($request->getSession()))->postVital($pid, $eid, $data);

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
    "PUT /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $return = (new EncounterRestController($request->getSession()))->putVital($pid, $eid, $vid, $data);

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
    "GET /api/patient/:pid/encounter/:eid/vital" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getVitals($pid, $eid);

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
    "GET /api/patient/:pid/encounter/:eid/vital/:vid" => function ($pid, $eid, $vid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getVital($pid, $eid, $vid);

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
    "GET /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new EncounterRestController($request->getSession()))->getSoapNote($pid, $eid, $sid);

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
    "POST /api/patient/:pid/encounter/:eid/soap_note" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->postSoapNote($pid, $eid, $data);

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
    "PUT /api/patient/:pid/encounter/:eid/soap_note/:sid" => function ($pid, $eid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new EncounterRestController($request->getSession()))->putSoapNote($pid, $eid, $sid, $data);

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
    "GET /api/practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new PractitionerRestController())->getAll($request, $request->query->all());
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
    "GET /api/practitioner/:pruuid" => function ($pruuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $return = (new PractitionerRestController())->getOne($pruuid, $request);
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
    "POST /api/practitioner" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->post($data, $request);
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
    "PUT /api/practitioner/:pruuid" => function ($pruuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new PractitionerRestController())->patch($pruuid, $data, $request);
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
    "GET /api/medical_problem" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new ConditionRestController())->getAll();

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
    "GET /api/medical_problem/:muuid" => function ($muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new ConditionRestController())->getOne($muuid);

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
    "GET /api/patient/:puuid/medical_problem" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "encounters", "notes");
        $return = (new ConditionRestController())->getAll(['puuid' => $puuid]);
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
    "GET /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ConditionRestController())->getAll(['puuid' => $puuid, 'condition_uuid' => $muuid]);

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
    "POST /api/patient/:puuid/medical_problem" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->post($puuid, $data);

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
    "PUT /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ConditionRestController())->put($puuid, $muuid, $data);

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
    "DELETE /api/patient/:puuid/medical_problem/:muuid" => function ($puuid, $muuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ConditionRestController())->delete($puuid, $muuid);

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
    "GET /api/allergy" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll();

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
    "GET /api/allergy/:auuid" => function ($auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getOne($auuid);

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
    "GET /api/patient/:puuid/allergy" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid]);

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
    "GET /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->getAll(['lists.pid' => $puuid, 'lists.id' => $auuid]);

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
    "POST /api/patient/:puuid/allergy" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->post($puuid, $data);

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
    "PUT /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AllergyIntoleranceRestController())->put($puuid, $auuid, $data);

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
    "DELETE /api/patient/:puuid/allergy/:auuid" => function ($puuid, $auuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new AllergyIntoleranceRestController())->delete($puuid, $auuid);

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
    "GET /api/patient/:pid/medication" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getAll($pid, "medication");

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
    "POST /api/patient/:pid/medication" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "medication", $data);

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
    "PUT /api/patient/:pid/medication/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $mid, "medication", $data);

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
    "GET /api/patient/:pid/medication/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getOne($pid, "medication", $mid);

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
    "DELETE /api/patient/:pid/medication/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->delete($pid, $mid, "medication");

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
    "GET /api/patient/:pid/surgery" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getAll($pid, "surgery");

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
    "GET /api/patient/:pid/surgery/:sid" => function ($pid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getOne($pid, "surgery", $sid);

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
    "DELETE /api/patient/:pid/surgery/:sid" => function ($pid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->delete($pid, $sid, "surgery");

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
    "POST /api/patient/:pid/surgery" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "surgery", $data);

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
    "PUT /api/patient/:pid/surgery/:sid" => function ($pid, $sid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $sid, "surgery", $data);

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
    "GET /api/patient/:pid/dental_issue" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getAll($pid, "dental");

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
    "GET /api/patient/:pid/dental_issue/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->getOne($pid, "dental", $did);

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
    "DELETE /api/patient/:pid/dental_issue/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ListRestController())->delete($pid, $did, "dental");

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
    "POST /api/patient/:pid/dental_issue" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->post($pid, "dental", $data);

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
    "PUT /api/patient/:pid/dental_issue/:did" => function ($pid, $did, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new ListRestController())->put($pid, $did, "dental", $data);

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
    "GET /api/patient/:pid/appointment" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getAllForPatient($pid);

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
    "POST /api/patient/:pid/appointment" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new AppointmentRestController())->post($pid, $data);

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
    "GET /api/appointment" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getAll();

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
    "GET /api/appointment/:eid" => function ($eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);

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
    "DELETE /api/patient/:pid/appointment/:eid" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->delete($eid);

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
    "GET /api/patient/:pid/appointment/:eid" => function ($pid, $eid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "appt");
        $return = (new AppointmentRestController())->getOne($eid);

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
    "GET /api/list/:list_name" => function ($list_name, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "lists", "default");
        $return = (new ListRestController())->getOptions($list_name);

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
    "GET /api/version" => function (HttpRestRequest $request) {
        $return = (new VersionRestController())->getOne();

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
    "GET /api/product" => function (HttpRestRequest $request) {
        $return = (new ProductRegistrationRestController())->getOne();

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
    "GET /api/insurance_company" => function (HttpRestRequest $request) {
        $return = (new InsuranceCompanyRestController())->getAll();

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
    "GET /api/insurance_company/:iid" => function ($iid, HttpRestRequest $request) {
        $return = (new InsuranceCompanyRestController())->getOne($iid);

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
    "GET /api/insurance_type" => function (HttpRestRequest $request) {
        $return = (new InsuranceCompanyRestController())->getInsuranceTypes();

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
    "POST /api/insurance_company" => function (HttpRestRequest $request) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->post($data);

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
    "PUT /api/insurance_company/:iid" => function ($iid, HttpRestRequest $request) {
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceCompanyRestController())->put($iid, $data);

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
    "POST /api/patient/:pid/document" => function ($pid, HttpRestRequest $request) {
        $return = (new DocumentRestController())->postWithPath($pid, $_GET['path'], $_FILES['document']);

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
    "GET /api/patient/:pid/document" => function ($pid, HttpRestRequest $request) {
        $return = (new DocumentRestController())->getAllAtPath($pid, $_GET['path']);

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
    "GET /api/patient/:pid/document/:did" => function ($pid, $did, HttpRestRequest $request) {
        $return = (new DocumentRestController())->downloadFile($pid, $did);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/employer",
     *      description="Retrieves all the employer data for a patient. Returns an array of the employer data for the patient.",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
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
     *      security={{"openemr_auth":{"user/employer.read", "patient/employer.read"}}}
     *  )
     */
    "GET /api/patient/:puuid/employer" => function ($puuid, HttpRestRequest $request) {
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            $errorReturn = [
                'validationErrors' => [ 'uuid' => ['Invalid UUID format']]
            ];
            return RestControllerHelper::responseHandler($errorReturn, null, 400);
        }

        $searchParams = $request->getQueryParams();
        if ($request->isPatientRequest()) {
            // For patient portal users, force the UUID to match the authenticated patient.
            $searchParams['puuid'] = $request->getPatientUUIDString();
        } else {
            // For staff users, verify they have permission to view demographic data.
            RestConfig::request_authorization_check($request, "patients", "demo");
            $searchParams['puuid'] = $puuid;
        }

        // Try to get the data. The service layer will handle non-existent UUIDs.
        $return = (new EmployerRestController())->getAll($searchParams);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/insurance",
     *      description="Retrieves all insurances for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
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
    "GET /api/patient/:puuid/insurance" => function ($puuid, HttpRestRequest $request) {
        $searchParams = $request->getQueryParams();
        $searchParams['puuid'] = $puuid;
        if ($request->isPatientRequest()) {
            $searchParams['puuid'] = $request->getPatientUUIDString();
        }
        $return = (new InsuranceRestController())->getAll($searchParams);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/insurance/$swap-insurance",
     *      description="Updates the insurance for the passed in uuid to be a policy of type `type` and updates (if one exists) the current or most recent insurance for the passed in `type` for a patient to be the `type` of the insurance for the given `uuid`. Validations on the swap operation are performed to make sure the effective `date` of the src and target policies being swapped can be received in each given policy `type` as a policy `type` and `date` must together be unique per patient.",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
     *          in="path",
     *          description="The uuid for the patient.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="The type or category of OpenEMR insurance policy, 'primary', 'secondary', or 'tertiary'.",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="uuid",
     *          in="query",
     *          description="The insurance uuid that will be swapped into the list of insurances for the type query parameter",
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
    'GET /api/patient/:puuid/insurance/$swap-insurance' => function ($puuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            $puuid = $request->getPatientUUIDString();
        }
        $type = $request->getQueryParam('type');
        $insuranceUuid = $request->getQueryParam('uuid');

        $return = (new InsuranceRestController())->operationSwapInsurance($puuid, $type, $insuranceUuid);

        return $return;
    },

    /**
     *  @OA\Get(
     *      path="/api/patient/{puuid}/insurance/{uuid}",
     *      description="Retrieves all insurances for a patient",
     *      tags={"standard"},
     *      @OA\Parameter(
     *          name="pid",
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
    "GET /api/patient/:puuid/insurance/:uuid" => function ($puuid, $uuid, HttpRestRequest $request) {
        if ($request->isPatientRequest()) {
            $puuid = $request->getPatientUUIDString();
        }
        $return = (new InsuranceRestController())->getOne($uuid, $puuid);

        return $return;
    },

    /**
     * Schema for the insurance request.  Note the following additional validation checks on the request.
     * If the subscriber_relationship value is of type 'self' then the subscriber_fname and subscriber_lname fields
     * must match the patient's first and last name or a patient's previous first and last name.
     *
     * If the subscriber_relationship value is of type 'self' then the subscriber_ss field must match the patient's
     * social security number.
     *
     * If the subscriber_relationship value is not of type 'self' then the subscriber_ss field MUST not be the current patient's social security number.
     *
     * If the system's global configuration permits only a single insurance type option then any insurance rquest where the type is NOT 'primary' will fail.
     *
     * An insurance is considered the current policy for the policy type if the policy date_end field is null.  Only one of these records per policy type can exist for a patient.
     *  @OA\Schema(
     *      schema="api_insurance_request",
     *      @OA\Property(
     *          property="provider",
     *          description="The insurance company id.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="plan_name",
     *          description="The plan name of insurance. (2-255 characters)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="policy_number",
     *          description="The policy number of insurance. (2-255 characters)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="group_number",
     *          description="The group number of insurance.(2-255 characters)",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="subscriber_lname",
     *          description="The subscriber last name of insurance.(2-255 characters).",
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
     *          description="The subscriber relationship of insurance. `subscriber_relationship` can be found by querying `resource=/api/list/subscriber_relationship`",
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
     *          description="The effective date of insurance in YYYY-MM-DD format.  This value cannot be after the date_end property and cannot be the same date as any other insurance policy for the same insurance type ('primary, 'secondary', etc).",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="date_end",
     *          description="The effective end date of insurance in YYYY-MM-DD format.  This value cannot be before the date property. If it is null then this policy is the current policy for this policy type for the patient.  There can only be one current policy per type and the request will fail if there is already a current policy for this type.",
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
     *          description="The 837p list of policy types for an insurance.  See src/Billing/InsurancePolicyType.php for the list of valid values.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="type",
     *          description="The type or category of OpenEMR insurance policy, 'primary', 'secondary', or 'tertiary'. If this field is missing it will default to 'primary'.",
     *          type="string"
     *      ),
     *      required={"provider", "policy_number", "subscriber_fname", "subscriber_lname", "subscriber_relationship", "subscriber_ss", "subscriber_DOB", "subscriber_street", "subscriber_postal_code", "subscriber_city", "subscriber_state", "subscriber_sex", "accept_assignment"},
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
     *          "policy_type": "a",
     *          "type": "primary"
     *      }
     *  )
     */

    /**
     *  @OA\Put(
     *      path="/api/patient/{puuid}/insurance/{insuranceUuid}",
     *      description="Edit a specific patient insurance policy. Requires the patients/demo/write ACL to call. This method is the preferred method for updating a patient insurance policy. The {insuranceId} can be found by querying /api/patient/{pid}/insurance",
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
     *          name="insuranceUuid",
     *          in="path",
     *          description="The insurance policy uuid for the patient.",
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
    "PUT /api/patient/:puuid/insurance/:insuranceUuid" => function ($puuid, $insuranceUuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo", 'write');
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->put($puuid, $insuranceUuid, $data);

        return $return;
    },

    /**
     *  @OA\Post(
     *      path="/api/patient/{puuid}/insurance",
     *      description="Submits a new patient insurance.",
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
    "POST /api/patient/:puuid/insurance" => function ($puuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "demo", ['write','addonly']);
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new InsuranceRestController())->post($puuid, $data);

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
    "POST /api/patient/:pid/message" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->post($pid, $data);

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

    "GET /api/patient/:pid/transaction" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "trans");
        $cont = new TransactionRestController();
        $return = (new TransactionRestController())->GetPatientTransactions($pid);

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
    "POST /api/patient/:pid/transaction" => function ($pid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "trans");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new TransactionRestController())->CreateTransaction($pid, $data);

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
    "PUT /api/transaction/:tid" => function ($tid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "trans");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new TransactionRestController())->UpdateTransaction($tid, $data);

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
    "PUT /api/patient/:pid/message/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "notes");
        $data = (array) (json_decode(file_get_contents("php://input")));
        $return = (new MessageRestController())->put($pid, $mid, $data);

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
     *          name="mid",
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
    "DELETE /api/patient/:pid/message/:mid" => function ($pid, $mid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "notes");
        $return = (new MessageRestController())->delete($pid, $mid);

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
    "GET /api/immunization" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ImmunizationRestController())->getAll($_GET);

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
    "GET /api/immunization/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ImmunizationRestController())->getOne($uuid);

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
    "GET /api/procedure" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ProcedureRestController())->getAll();

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
    "GET /api/procedure/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new ProcedureRestController())->getOne($uuid);

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
    "GET /api/drug" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new DrugRestController())->getAll();

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
    "GET /api/drug/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new DrugRestController())->getOne($uuid);

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
    "GET /api/prescription" => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new PrescriptionRestController())->getAll();

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
    "GET /api/prescription/:uuid" => function ($uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "patients", "med");
        $return = (new PrescriptionRestController())->getOne($uuid);

        return $return;
    }
];
