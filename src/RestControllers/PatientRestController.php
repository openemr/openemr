<?php

/**
 * PatientRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\SearchQueryConfig;
use Psr\Http\Message\ResponseInterface;

#[OA\Schema(
    schema: "api_patient_request",
    required: ["fname", "lname", "DOB", "sex"],
    properties: [
        new OA\Property(property: "title", description: "The title of patient.", type: "string"),
        new OA\Property(property: "fname", description: "The first name of patient.", type: "string"),
        new OA\Property(property: "mname", description: "The middle name of patient.", type: "string"),
        new OA\Property(property: "lname", description: "The last name of patient.", type: "string"),
        new OA\Property(property: "street", description: "The street address of patient.", type: "string"),
        new OA\Property(property: "postal_code", description: "The postal code of patient.", type: "string"),
        new OA\Property(property: "city", description: "The city of patient.", type: "string"),
        new OA\Property(property: "state", description: "The state of patient.", type: "string"),
        new OA\Property(property: "country_code", description: "The country code of patient.", type: "string"),
        new OA\Property(property: "phone_contact", description: "The phone contact of patient.", type: "string"),
        new OA\Property(property: "DOB", description: "The date of birth of patient.", type: "string"),
        new OA\Property(property: "sex", description: "The sex of patient.", type: "string"),
        new OA\Property(property: "race", description: "The race of patient.", type: "string"),
        new OA\Property(property: "ethnicity", description: "The ethnicity of patient.", type: "string"),
    ]
)]
#[OA\Schema(
    schema: "api_patient_response",
    properties: [
        new OA\Property(property: "validationErrors", description: "Validation errors.", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "internalErrors", description: "Internal errors.", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "data", description: "Returned data.", type: "array", items: new OA\Items(type: "object")),
    ]
)]
class PatientRestController
{
    private readonly PatientService $patientService;

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = [
        "fname",
        "lname",
        "ss",
        "street",
        "postal_code",
        "city",
        "state",
        "phone_home",
        "phone_biz",
        "phone_cell",
        'postal_contact',
        'sex',
        'country_code',
        "email",
        "DOB",
    ];

    public function __construct()
    {
        $this->patientService = new PatientService();
    }

    /**
     * Process a HTTP POST request used to create a patient record.
     * @param $data - array of patient fields.
     * @return ResponseInterface 201/Created status code and the patient identifier if successful.
     */
    #[OA\Post(
        path: "/api/patient",
        description: "Creates a new patient",
        tags: ["standard"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_patient_request")
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "validationErrors",
                                description: "Validation errors.",
                                type: "array",
                                items: new OA\Items(type: "object")
                            ),
                            new OA\Property(
                                property: "internalErrors",
                                description: "Internal errors.",
                                type: "array",
                                items: new OA\Items(type: "object")
                            ),
                            new OA\Property(
                                property: "data",
                                description: "Returned data.",
                                type: "array",
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(
                                            property: "pid",
                                            description: "patient pid",
                                            type: "integer"
                                        ),
                                    ]
                                )
                            ),
                        ],
                        example: [
                            "validationErrors" => [],
                            "error_description" => [],
                            "data" => [
                                "pid" => 1,
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($data, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->patientService->insert($data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Processes a HTTP PUT request used to update an existing patient record.
     * @param $puuidString - The patient uuid identifier in string format.
     * @param $data - array of patient fields (full resource).
     * @return ResponseInterface 200/Ok status code and the patient resource.
     */
    #[OA\Put(
        path: "/api/patient/{puuid}",
        description: "Updates a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "puuid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_patient_request")
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(ref: "#/components/schemas/api_patient_response")
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put($puuidString, $data, HttpRestRequest $request)
    {
        $processingResult = $this->patientService->update($puuidString, $data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Fetches a single patient resource by id.
     * @param $puuidString - The patient uuid identifier in string format.
     */
    #[OA\Get(
        path: "/api/patient/{puuid}",
        description: "Retrieves a single patient by their uuid",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "puuid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(ref: "#/components/schemas/api_patient_response")
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Get(
        path: "/portal/patient",
        description: "Returns the patient.",
        tags: ["standard-patient"],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(ref: "#/components/schemas/api_patient_response")
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getOne($puuidString, HttpRestRequest $request)
    {
        $processingResult = $this->patientService->getOne($puuidString);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     * @param HttpRestRequest $request - The HTTP request object.
     * @param array $search - An array of search fields to filter the results.
     * @param SearchQueryConfig $config - The search query configuration object.
     */
    #[OA\Get(
        path: "/api/patient",
        description: "Retrieves a list of patients",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/_sort"),
            new OA\Parameter(
                name: "fname",
                in: "query",
                description: "The first name for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "lname",
                in: "query",
                description: "The last name for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "ss",
                in: "query",
                description: "The social security number for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "street",
                in: "query",
                description: "The street for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "postal_code",
                in: "query",
                description: "The postal code for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "city",
                in: "query",
                description: "The city for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "state",
                in: "query",
                description: "The state for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "phone_home",
                in: "query",
                description: "The home phone for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "phone_biz",
                in: "query",
                description: "The business phone for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "phone_cell",
                in: "query",
                description: "The cell phone for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "postal_contact",
                in: "query",
                description: "The postal_contact for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sex",
                in: "query",
                description: "The gender for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "country_code",
                in: "query",
                description: "The country code for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "email",
                in: "query",
                description: "The email for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "DOB",
                in: "query",
                description: "The DOB for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "date",
                in: "query",
                description: "The date this patient resource was last modified.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "_offset",
                in: "query",
                description: "The number of records to offset from this index in the search result.",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "_limit",
                in: "query",
                description: "The maximum number of resources to return in the result set. 0 means unlimited.",
                required: false,
                schema: new OA\Schema(type: "integer", minimum: 0, maximum: 200)
            ),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll(HttpRestRequest $request, array $search, SearchQueryConfig $config)
    {
        $validSearchFields = array_filter(
            $search,
            fn($key): bool => in_array($key, self::SUPPORTED_SEARCH_FIELDS),
            ARRAY_FILTER_USE_KEY
        );
        $processingResult = $this->patientService->getAll($validSearchFields, true, null, $config);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }
}
