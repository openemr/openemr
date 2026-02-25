<?php

/**
 * FhirSpecimenRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Services\FHIR\FhirSpecimenService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirSpecimenRestController
{
    private readonly FhirSpecimenService $fhirSpecimenService;
    private readonly FhirResourcesService $fhirService;

    public function __construct()
    {
        $this->fhirSpecimenService = new FhirSpecimenService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR specimen resource by FHIR id
     *
     * @param string $fhirId The FHIR specimen resource id (uuid)
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/Specimen/{uuid}",
        description: "Returns a single Specimen resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Specimen resource.",
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
                            "id" => "95e9d3fb-fe7b-448a-aa60-d40b11b486a5",
                            "meta" => [
                                "versionId" => "1",
                                "lastUpdated" => "2025-10-10T17:20:14+00:00",
                            ],
                            "resourceType" => "Specimen",
                            "identifier" => [
                                [
                                    "system" => "https://example.org/specimen-id",
                                    "value" => "SPEC-2025-001",
                                ],
                            ],
                            "accessionIdentifier" => [
                                "system" => "https://example.org/accession",
                                "value" => "ACC-2025-12345",
                            ],
                            "status" => "available",
                            "type" => [
                                "coding" => [
                                    [
                                        "system" => "http://snomed.info/sct",
                                        "code" => "122555007",
                                        "display" => "Venous blood specimen",
                                    ],
                                ],
                            ],
                            "subject" => [
                                "reference" => "Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf",
                                "type" => "Patient",
                            ],
                            "receivedTime" => "2025-10-10T10:30:00+00:00",
                            "collection" => [
                                "collectedDateTime" => "2025-10-10T09:00:00+00:00",
                                "quantity" => [
                                    "value" => 10,
                                    "unit" => "mL",
                                    "system" => "http://unitsofmeasure.org",
                                    "code" => "mL",
                                ],
                                "bodySite" => [
                                    "coding" => [
                                        [
                                            "system" => "http://snomed.info/sct",
                                            "code" => "368208006",
                                            "display" => "Left arm",
                                        ],
                                    ],
                                ],
                            ],
                            "container" => [
                                [
                                    "type" => [
                                        "coding" => [
                                            [
                                                "system" => "http://snomed.info/sct",
                                                "code" => "702281005",
                                                "display" => "Evacuated blood collection tube with heparin",
                                            ],
                                        ],
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
    public function getOne($fhirId, $puuidBind = null): Response
    {
        $processingResult = $this->fhirSpecimenService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR specimen resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * - identifier
     * - accession
     * - type
     * - collected
     * - status
     *
     * @param array $searchParams
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return JsonResponse|Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/Specimen",
        description: "Returns a list of Specimen resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the Specimen resource.",
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
                name: "patient",
                in: "query",
                description: "The uuid for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "accession",
                in: "query",
                description: "The accession identifier of the Specimen resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "The type of the Specimen resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "collected",
                in: "query",
                description: "The collection datetime of the Specimen resource.",
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
                                "lastUpdated" => "2025-10-10T09:13:51",
                            ],
                            "resourceType" => "Bundle",
                            "type" => "collection",
                            "total" => 0,
                            "link" => [
                                [
                                    "relation" => "self",
                                    "url" => "https://localhost:9300/apis/default/fhir/Specimen",
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
    public function getAll($searchParams, $puuidBind = null): JsonResponse|Response
    {
        $processingResult = $this->fhirSpecimenService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' => $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Specimen', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
