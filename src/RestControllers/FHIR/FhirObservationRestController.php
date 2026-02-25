<?php

/**
 * FhirObservationRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirObservationService;
use OpenEMR\Services\FHIR\FhirResourcesService;

/**
 * @deprecated use FhirGenericRestController
 */
class FhirObservationRestController
{
    private $fhirObservationService;
    private $fhirService;

    public function __construct()
    {
        $this->fhirObservationService = new FhirObservationService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR observation resource by FHIR id
     * @param $fhirId The FHIR observation resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/Observation/{uuid}",
        description: "Returns a single Observation resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Observation resource.",
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
                            "id" => "946da61e-0597-485e-9dfd-a87205ea56b3",
                            "meta" => [
                                "versionId" => "1",
                                "lastUpdated" => "2021-09-20T04:12:16+00:00",
                            ],
                            "resourceType" => "Observation",
                            "status" => "final",
                            "category" => [
                                [
                                    "coding" => [
                                        [
                                            "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                                            "code" => "vital-signs",
                                        ],
                                    ],
                                ],
                            ],
                            "code" => [
                                "coding" => [
                                    [
                                        "system" => "http://loinc.org",
                                        "code" => "85354-9",
                                        "display" => "Blood pressure systolic and diastolic",
                                    ],
                                ],
                            ],
                            "subject" => [
                                "reference" => "Patient/946da619-c631-431a-a282-487cd6fb7802",
                                "type" => "Patient",
                            ],
                            "effectiveDateTime" => "2015-08-31T00:00:00+00:00",
                            "component" => [
                                [
                                    "code" => [
                                        "coding" => [
                                            [
                                                "system" => "http://loinc.org",
                                                "code" => "8480-6",
                                                "display" => "Systolic blood pressure",
                                            ],
                                        ],
                                    ],
                                    "valueQuantity" => [
                                        "value" => 122,
                                        "unit" => "mm[Hg]",
                                        "system" => "http://unitsofmeasure.org",
                                        "code" => "mm[Hg]",
                                    ],
                                ],
                                [
                                    "code" => [
                                        "coding" => [
                                            [
                                                "system" => "http://loinc.org",
                                                "code" => "8462-4",
                                                "display" => "Diastolic blood pressure",
                                            ],
                                        ],
                                    ],
                                    "valueQuantity" => [
                                        "value" => 77,
                                        "unit" => "mm[Hg]",
                                        "system" => "http://unitsofmeasure.org",
                                        "code" => "mm[Hg]",
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
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->fhirObservationService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR observation resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/Observation",
        summary: "Returns a list of Observation resources.",
        description: "Returns a list of Observation resources. Returns the following types of Observation resources, Advance Directives, Care Experience Preferences, Occupation, Social Determinants of Health, Laboratory, Simple Observations, Social History, Questionnaire Responses, Treatment Intervention Preferences, Vital Signs.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the Observation resource.",
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
                name: "code",
                in: "query",
                description: "The code of the Observation resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "category",
                in: "query",
                description: "The category of the Observation resource. Taken from one of these valid category codes http://terminology.hl7.org/CodeSystem/observation-category",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "date",
                in: "query",
                description: "The datetime of the Observation resource.",
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
                                    "url" => "https://localhost:9300/apis/default/fhir/Observation",
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
        $processingResult = $this->fhirObservationService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Observation', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
