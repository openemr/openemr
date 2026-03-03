<?php

/**
 * FhirAllergyIntoleranceRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirAllergyIntoleranceRestController
{
    private $fhirAllergyIntoleranceService;
    private $fhirService;

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService($request->getApiBaseFullUrl());
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR allergyIntolerance resource by FHIR id
     * @param $fhirId The FHIR allergyIntolerance resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/AllergyIntolerance/{uuid}',
        description: 'Returns a single AllergyIntolerance resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the AllergyIntolerance resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'id' => '94682fe5-f383-4885-9505-64b02e34906f',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2021-09-16T00:27:32+00:00',
                            ],
                            'resourceType' => 'AllergyIntolerance',
                            'text' => [
                                'status' => 'additional',
                                'div' => "<div xmlns='http://www.w3.org/1999/xhtml'>penicillin</div>",
                            ],
                            'clinicalStatus' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                                        'code' => 'active',
                                        'display' => 'Active',
                                    ],
                                ],
                            ],
                            'verificationStatus' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                                        'code' => 'confirmed',
                                        'display' => 'Confirmed',
                                    ],
                                ],
                            ],
                            'category' => ['medication'],
                            'criticality' => 'low',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/data-absent-reason',
                                        'code' => 'unknown',
                                        'display' => 'Unknown',
                                    ],
                                ],
                            ],
                            'patient' => [
                                'reference' => 'Patient/94682ef5-b0e3-4289-b19a-11b9592e9c92',
                            ],
                            'reaction' => [
                                [
                                    'manifestation' => [
                                        [
                                            'coding' => [
                                                [
                                                    'system' => 'http://snomed.info/sct',
                                                    'code' => '422587007',
                                                    'display' => 'Nausea',
                                                ],
                                            ],
                                            'text' => 'Nausea',
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->fhirAllergyIntoleranceService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR allergyIntolerance resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/AllergyIntolerance',
        description: 'Returns a list of AllergyIntolerance resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the AllergyIntolerance resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: '_lastUpdated',
                in: 'query',
                description: 'Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'patient',
                in: 'query',
                description: 'The uuid for the patient.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'meta' => [
                                'lastUpdated' => '2021-09-14T09:13:51',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/AllergyIntolerance',
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll($searchParams, $puuidBind = null)
    {
        $processingResult = $this->fhirAllergyIntoleranceService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('AllergyIntolerance', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
