<?php

/**
 * FhirPersonRestController.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson;
use OpenEMR\Services\FHIR\FhirPersonService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

/**
 * Supports REST interactions with the FHIR practitioner resource
 */
class FhirPersonRestController
{
    /**
     * @var FhirPersonService
     */
    private $fhirPersonService;

    private $fhirService;
    private $fhirValidate;
    private $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->fhirService = new FhirResourcesService();
        $this->fhirPersonService = new FhirPersonService();
        $this->fhirValidate = new FhirValidationService();
    }

    /**
     * Queries for a single FHIR person resource by FHIR id
     * @param string $fhirId The FHIR person resource id (uuid)
     * @param string|null $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/Person/{uuid}",
        description: "Returns a single Person resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Person resource.",
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
                            "id" => "960c7cd6-187a-4119-8cd4-85389d80efb9",
                            "meta" => [
                                "versionId" => "1",
                                "lastUpdated" => "2022-04-13T08:57:32+00:00",
                            ],
                            "resourceType" => "Person",
                            "text" => [
                                "status" => "generated",
                                "div" => "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Administrator Administrator</p></div>",
                            ],
                            "name" => [
                                [
                                    "use" => "official",
                                    "family" => "Administrator",
                                    "given" => [
                                        "Administrator",
                                        "Larry",
                                    ],
                                ],
                            ],
                            "telecom" => [
                                [
                                    "system" => "phone",
                                    "value" => "1234567890",
                                    "use" => "home",
                                ],
                                [
                                    "system" => "phone",
                                    "value" => "1234567890",
                                    "use" => "work",
                                ],
                                [
                                    "system" => "phone",
                                    "value" => "1234567890",
                                    "use" => "mobile",
                                ],
                                [
                                    "system" => "email",
                                    "value" => "hey@hey.com",
                                    "use" => "home",
                                ],
                            ],
                            "address" => [
                                [
                                    "line" => [
                                        "123 Lane Street",
                                    ],
                                    "city" => "Bellevue",
                                    "state" => "WA",
                                    "period" => [
                                        "start" => "2021-04-13T08:57:32.146+00:00",
                                    ],
                                ],
                            ],
                            "active" => true,
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
    public function getOne(string $fhirId, ?string $puuidBind = null)
    {
        $this->logger->debug("FhirPersonRestController->getOne(fhirId)", ["fhirId" => $fhirId]);
        $processingResult = $this->fhirPersonService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR person resources using various search parameters.
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
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/Person",
        description: "Returns a list of Person resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the Person resource.",
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
                description: "The name of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "active",
                in: "query",
                description: "The active status of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address",
                in: "query",
                description: "The address of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-city",
                in: "query",
                description: "The address-city of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-postalcode",
                in: "query",
                description: "The address-postalcode of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-state",
                in: "query",
                description: "The address-state of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "email",
                in: "query",
                description: "The email of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "family",
                in: "query",
                description: "The family name of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "given",
                in: "query",
                description: "The given name of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "phone",
                in: "query",
                description: "The phone number of the Person resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "telecom",
                in: "query",
                description: "The fax number of the Person resource.",
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
                                    "url" => "https://localhost:9300/apis/default/fhir/Person",
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
        $processingResult = $this->fhirPersonService->getAll($searchParams);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle(FhirPersonService::RESOURCE_NAME, $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
