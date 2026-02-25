<?php

/**
 * FhirMedicationRequestRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirMedicationRequestRestController
{
    private $fhirService;
    private $fhirMedicationRequestService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirMedicationRequestService = new FhirMedicationRequestService();
    }

    /**
     * Queries for a single FHIR medication resource by FHIR id
     * @param $fhirId The FHIR medication resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/MedicationRequest/{uuid}",
        description: "Returns a single MedicationRequest resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the MedicationRequest resource.",
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
                            "id" => "946da61d-9cff-4416-8d27-805f19f9d7d8",
                            "meta" => [
                                "versionId" => "1",
                                "lastUpdated" => "2021-09-20T04:03:14+00:00",
                            ],
                            "resourceType" => "MedicationRequest",
                            "status" => "active",
                            "intent" => "order",
                            "category" => [
                                [
                                    "coding" => [
                                        [
                                            "system" => "http://terminology.hl7.org/CodeSystem/medicationrequest-category",
                                            "code" => "community",
                                            "display" => "Home/Community",
                                        ],
                                    ],
                                ],
                            ],
                            "reportedBoolean" => false,
                            "medicationCodeableConcept" => [
                                "coding" => [
                                    [
                                        "system" => "http://www.nlm.nih.gov/research/umls/rxnorm",
                                        "code" => "1738139",
                                        "display" => "Acetaminophen 325 MG Oral Tablet",
                                    ],
                                ],
                            ],
                            "subject" => [
                                "reference" => "Patient/946da617-1a4a-4b2c-ae66-93b84377cb1e",
                                "type" => "Patient",
                            ],
                            "authoredOn" => "2021-09-18T00:00:00+00:00",
                            "requester" => [
                                "reference" => "Practitioner/946da61d-ac5f-4fdc-b3f2-7b58dc49976b",
                                "type" => "Practitioner",
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
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->fhirMedicationRequestService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR medication resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/MedicationRequest",
        description: "Returns a list of MedicationRequest resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the MedicationRequest resource.",
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
                name: "intent",
                in: "query",
                description: "The intent of the MedicationRequest resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "The status of the MedicationRequest resource.",
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
                                    "url" => "https://localhost:9300/apis/default/fhir/MedicationRequest",
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
    public function getAll($searchParams, $puuidBind = null)
    {
        $processingResult = $this->fhirMedicationRequestService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Medication', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
