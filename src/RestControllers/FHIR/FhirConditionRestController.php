<?php

/**
 * FhirConditionRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated use FhirGenericRestController
 */
class FhirConditionRestController
{
    private readonly FhirConditionService $fhirConditionService;
    private readonly FhirResourcesService $fhirService;

    public function __construct()
    {
        $this->fhirConditionService = new FhirConditionService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR condition resource by FHIR id
     * @param string $fhirId The FHIR condition resource id (uuid)
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/Condition/{uuid}',
        description: 'Returns a single Condition resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Condition resource.',
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
                            'id' => '94682c68-e5bb-4c5c-859a-cebaa5a1e582',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2021-09-16T02:41:53+00:00',
                            ],
                            'resourceType' => 'Condition',
                            'clinicalStatus' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                                        'code' => 'inactive',
                                        'display' => 'Inactive',
                                    ],
                                ],
                            ],
                            'verificationStatus' => [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                                        'code' => 'unconfirmed',
                                        'display' => 'Unconfirmed',
                                    ],
                                ],
                            ],
                            'category' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                            'code' => 'problem-list-item',
                                            'display' => 'Problem List Item',
                                        ],
                                    ],
                                ],
                            ],
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://snomed.info/sct',
                                        'code' => '444814009',
                                        'display' => '',
                                    ],
                                ],
                            ],
                            'subject' => [
                                'reference' => 'Patient/94682c62-d37e-48b5-8018-c5f6f3566609',
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
    public function getOne($fhirId, $puuidBind = null): Response
    {
        $processingResult = $this->fhirConditionService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR condition resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * @param array $searchParams
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return JsonResponse|Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/Condition',
        description: 'Returns a list of Condition resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the Condition resource.',
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
                                    'url' => 'https://localhost:9300/apis/default/fhir/Condition',
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
    public function getAll($searchParams, $puuidBind = null): JsonResponse|Response
    {
        $processingResult = $this->fhirConditionService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Condition', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
