<?php

/**
 * FhirPractitionerRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\Serialization\FhirPractitionerSerializer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Supports REST interactions with the FHIR practitioner resource
 */
class FhirPractitionerRestController
{
    use SystemLoggerAwareTrait;

    private FhirPractitionerService $fhirPractitionerService;
    private FhirResourcesService $fhirService;
    private FhirValidationService $fhirValidate;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirPractitionerService = new FhirPractitionerService();
        $this->fhirValidate = new FhirValidationService();
    }

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->fhirPractitionerService->setSystemLogger($systemLogger);
        $this->systemLogger = $systemLogger;
    }

    /**
     * Creates a new FHIR practitioner resource
     * @param array $fhirJson The FHIR practitioner resource
     * @returns Response 201 if the resource is created, 400 if the resource is invalid
     */
    #[OA\Post(
        path: "/fhir/Practitioner",
        description: "Adds a Practitioner resources.",
        tags: ["fhir"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    description: "The json object for the Practitioner resource.",
                    type: "object"
                ),
                example: [
                    "id" => "9473b0cf-e969-4eaa-8044-51037767fa4f",
                    "meta" => [
                        "versionId" => "1",
                        "lastUpdated" => "2021-09-21T17:41:57+00:00",
                    ],
                    "resourceType" => "Practitioner",
                    "text" => [
                        "status" => "generated",
                        "div" => "<div xmlns=\"http://www.w3.org/1999/xhtml\"> <p>Billy Smith</p></div>",
                    ],
                    "identifier" => [
                        [
                            "system" => "http://hl7.org/fhir/sid/us-npi",
                            "value" => "11223344554543",
                        ],
                    ],
                    "active" => true,
                    "name" => [
                        [
                            "use" => "official",
                            "family" => "Smith",
                            "given" => [
                                "Danny",
                            ],
                        ],
                    ],
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
                        ],
                        example: [
                            "id" => "9473b0cf-e969-4eaa-8044-51037767fa4f",
                            "meta" => [
                                "versionId" => "1",
                                "lastUpdated" => "2021-09-21T17:41:57+00:00",
                            ],
                            "resourceType" => "Practitioner",
                            "text" => [
                                "status" => "generated",
                                "div" => "<div xmlns=\"http://www.w3.org/1999/xhtml\"> <p>Billy Smith</p></div>",
                            ],
                            "identifier" => [
                                [
                                    "system" => "http://hl7.org/fhir/sid/us-npi",
                                    "value" => "11223344554543",
                                ],
                            ],
                            "active" => true,
                            "name" => [
                                [
                                    "use" => "official",
                                    "family" => "Smith",
                                    "given" => [
                                        "Danny",
                                    ],
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($fhirJson): Response
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::handleFhirProcessingResult($fhirValidate, 400);
        }

        $object = FhirPractitionerSerializer::deserialize($fhirJson);

        $processingResult = $this->fhirPractitionerService->insert($object);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR practitioner resource
     * @param string $fhirId The FHIR practitioner resource id (uuid)
     * @param array $fhirJson The updated FHIR practitioner resource (complete resource)
     * @returns ResponseInterface 200 if the resource is created, 400 if the resource is invalid
     */
    #[OA\Put(
        path: "/fhir/Practitioner/{uuid}",
        description: "Modify a Practitioner resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Practitioner resource.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    description: "The json object for the Practitioner resource.",
                    type: "object"
                ),
                example: [
                    "id" => "9473b0cf-e969-4eaa-8044-51037767fa4f",
                    "meta" => [
                        "versionId" => "1",
                        "lastUpdated" => "2021-09-21T17:41:57+00:00",
                    ],
                    "resourceType" => "Practitioner",
                    "text" => [
                        "status" => "generated",
                        "div" => "<div xmlns=\"http://www.w3.org/1999/xhtml\"> <p>Billy Smith</p></div>",
                    ],
                    "identifier" => [
                        [
                            "system" => "http://hl7.org/fhir/sid/us-npi",
                            "value" => "11223344554543",
                        ],
                    ],
                    "active" => true,
                    "name" => [
                        [
                            "use" => "official",
                            "family" => "Smith",
                            "given" => [
                                "Billy",
                            ],
                        ],
                    ],
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: "201",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        example: [
                            "id" => 5,
                            "uuid" => "95f294d7-e14c-441d-81a6-309fe369ee21",
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function patch($fhirId, $fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $object = FhirPractitionerSerializer::deserialize($fhirJson);

        $processingResult = $this->fhirPractitionerService->update($fhirId, $object);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for a single FHIR practitioner resource by FHIR id
     * @param string $fhirId The FHIR practitioner resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/Practitioner/{uuid}",
        description: "Returns a single Practitioner resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Practitioner resource.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
                        ],
                        example: [
                            "id" => "9473b0cf-e969-4eaa-8044-51037767fa4f",
                            "meta" => [
                                "versionId" => "1",
                                "lastUpdated" => "2021-09-21T17:41:57+00:00",
                            ],
                            "resourceType" => "Practitioner",
                            "text" => [
                                "status" => "generated",
                                "div" => "<div xmlns=\"http://www.w3.org/1999/xhtml\"> <p>Billy Smith</p></div>",
                            ],
                            "identifier" => [
                                [
                                    "system" => "http://hl7.org/fhir/sid/us-npi",
                                    "value" => "11223344554543",
                                ],
                            ],
                            "active" => true,
                            "name" => [
                                [
                                    "use" => "official",
                                    "family" => "Smith",
                                    "given" => [
                                        "Billy",
                                    ],
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
            new OA\Response(response: "404", ref: "#/components/responses/uuidnotfound"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPractitionerService->getOne($fhirId);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR practitioner resources using various search parameters.
     * Search parameters include:
     * - active (active)
     * - address (street, zip, city, or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - email
     * - family
     * - given (first name or middle name)
     * - name (title, first name, middle name, last name)
     * - phone (phone, work, cell)
     * - telecom (email, phone)
     * @return Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/Practitioner",
        description: "Returns a list of Practitioner resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "_lastUpdated",
                in: "query",
                description: "Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "name",
                in: "query",
                description: "The name of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "active",
                in: "query",
                description: "The active status of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address",
                in: "query",
                description: "The address of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-city",
                in: "query",
                description: "The address-city of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-postalcode",
                in: "query",
                description: "The address-postalcode of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-state",
                in: "query",
                description: "The address-state of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "email",
                in: "query",
                description: "The email of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "family",
                in: "query",
                description: "The family name of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "given",
                in: "query",
                description: "The given name of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "phone",
                in: "query",
                description: "The phone number of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "telecom",
                in: "query",
                description: "The fax number of the Practitioner resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
                        ],
                        example: [
                            "meta" => [
                                "lastUpdated" => "2021-09-14T09:13:51",
                            ],
                            "resourceType" => "Bundle",
                            "type" => "collection",
                            "total" => 0,
                            "link" => [
                                [
                                    "relation" => "self",
                                    "url" => "https://localhost:9300/apis/default/fhir/Practitioner",
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirPractitionerService->getAll($searchParams);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Practitioner', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
