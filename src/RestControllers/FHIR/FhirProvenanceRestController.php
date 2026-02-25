<?php

/**
 * FhirProvenanceRestController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Validators\ProcessingResult;

class FhirProvenanceRestController
{
    private readonly FhirResourcesService $fhirService;

    /**
     * @var FhirProvenanceService
     */
    private readonly FhirProvenanceService $provenanceService;

    private readonly FhirServiceLocator $serviceLocator;

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirService = new FhirResourcesService();
        // TODO: @adunsulag should we actually force this to be a method of HttpRestRequest? Since my plan is to possibly refactor this, I'm not sure if this is the best place for it.
        $serviceLocator = $request->attributes->get('_serviceLocator');
        if (!$serviceLocator instanceof FhirServiceLocator) {
            throw new \InvalidArgumentException('FhirServiceLocator must be set in the request attributes');
        }
        $this->serviceLocator = $serviceLocator;
        $this->provenanceService = new FhirProvenanceService();
        $this->provenanceService->setSession($request->getSession());
        $this->provenanceService->setServiceLocator($serviceLocator);
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/Provenance/{uuid}",
        description: "Returns a single Provenance resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The id for the Provenance resource. Format is <resource name>:<uuid> (Example: AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145).",
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
                            "id" => "AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145",
                            "resourceType" => "Provenance",
                            "target" => [
                                [
                                    "reference" => "AllergyIntolerance/95ea43f3-1066-4bc7-b224-6c23b985f145",
                                    "type" => "AllergyIntolerance",
                                ],
                            ],
                            "recorded" => "2022-03-26T22:43:30+00:00",
                            "agent" => [
                                [
                                    "type" => [
                                        "coding" => [
                                            [
                                                "system" => "http://terminology.hl7.org/CodeSystem/provenance-participant-type",
                                                "code" => "author",
                                                "display" => "Author",
                                            ],
                                        ],
                                    ],
                                    "who" => [
                                        "reference" => "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
                                        "type" => "Organization",
                                    ],
                                    "onBehalfOf" => [
                                        "reference" => "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
                                        "type" => "Organization",
                                    ],
                                ],
                                [
                                    "type" => [
                                        "coding" => [
                                            [
                                                "system" => "http://hl7.org/fhir/us/core/CodeSystem/us-core-provenance-participant-type",
                                                "code" => "transmitter",
                                                "display" => "Transmitter",
                                            ],
                                        ],
                                    ],
                                    "who" => [
                                        "reference" => "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
                                        "type" => "Organization",
                                    ],
                                    "onBehalfOf" => [
                                        "reference" => "Organization/95e8d810-7e55-44aa-bb48-fecd5b0d88c7",
                                        "type" => "Organization",
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
        $processingResult = $this->provenanceService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR location resources using various search parameters.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/Provenance",
        description: "Returns a list of Provenance resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The id for the Provenance resource. Format is <resource name>:<uuid> (Example: AllergyIntolerance:95ea43f3-1066-4bc7-b224-6c23b985f145).",
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
                                    "url" => "https://localhost:9300/apis/default/fhir/Provenance",
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
        $processingResult = $this->provenanceService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('CarePlan', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
