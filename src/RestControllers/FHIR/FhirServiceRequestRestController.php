<?php

/**
 * FhirServiceRequestRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirServiceRequestService;

class FhirServiceRequestRestController
{
    private $fhirServiceRequestService;
    private $fhirService;

    public function __construct()
    {
        $this->fhirServiceRequestService = new FhirServiceRequestService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for a single FHIR ServiceRequest resource by FHIR id
     *
     * @param $fhirId    - The FHIR ServiceRequest resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/ServiceRequest/{uuid}',
        description: 'Returns a single ServiceRequest resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the ServiceRequest resource.',
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
                            'id' => '95e9d3fb-fe7b-448a-aa60-d40b11b486a5',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2025-03-26T17:20:14+00:00',
                            ],
                            'resourceType' => 'ServiceRequest',
                            'status' => 'active',
                            'intent' => 'order',
                            'category' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://snomed.info/sct',
                                            'code' => '108252007',
                                            'display' => 'Laboratory procedure',
                                        ],
                                    ],
                                ],
                            ],
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://loinc.org',
                                        'code' => '24356-8',
                                        'display' => 'Urinalysis complete',
                                    ],
                                ],
                            ],
                            'subject' => [
                                'reference' => 'Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf',
                                'type' => 'Patient',
                            ],
                            'authoredOn' => '2025-03-26T00:00:00+00:00',
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
        $processingResult = $this->fhirServiceRequestService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR ServiceRequest resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * - category (order type)
     * - code (procedure/test code)
     * - authored (order date)
     * - status (order status)
     *
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return \Symfony\Component\HttpFoundation\Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/ServiceRequest',
        description: 'Returns a list of ServiceRequest resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the ServiceRequest resource.',
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
            new OA\Parameter(
                name: 'category',
                in: 'query',
                description: 'The category/type of the ServiceRequest (laboratory, imaging, etc).',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'code',
                in: 'query',
                description: 'The code of the ServiceRequest resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'authored',
                in: 'query',
                description: 'The authored date of the ServiceRequest resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'The status of the ServiceRequest resource.',
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
                                'lastUpdated' => '2025-09-30T09:13:51',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/ServiceRequest',
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
        $processingResult = $this->fhirServiceRequestService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' => OEGlobalsBag::getInstance()->get('site_addr_oath') . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('ServiceRequest', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
    /**
     * Creates a new FHIR ServiceRequest resource.
     * Routed via FhirGenericRestController::post(). This method exists only
     * to provide OpenAPI documentation via attributes.
     *
     * @param array<string, mixed> $fhirJson
     */
    // @codeCoverageIgnoreStart
    #[OA\Post(
        path: '/fhir/ServiceRequest',
        description: 'Creates a new ServiceRequest (procedure / lab / imaging order). Each FHIR code.coding entry becomes one procedure_order_code row. The FHIR R4 1..1 `intent` field is persisted to procedure_order.order_intent; OpenEMR supports the values order/plan/directive/proposal/option directly, and other R4 intents (original-order, reflex-order, filler-order, instance-order) fall back to \'order\' since OpenEMR\'s order workflow has no distinction for those.',
        tags: ['fhir'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(type: 'object')
            )
        ),
        responses: [
            new OA\Response(response: '201', description: 'ServiceRequest resource created'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(array $fhirJson): void
    {
        // Implementation lives in FhirGenericRestController::post()
    }

    /**
     * Updates an existing FHIR ServiceRequest resource.
     * Routed via FhirGenericRestController::put(). This method exists only
     * to provide OpenAPI documentation via attributes.
     *
     * @param array<string, mixed> $fhirJson
     */
    #[OA\Put(
        path: '/fhir/ServiceRequest/{uuid}',
        description: 'Modifies a ServiceRequest. PUT replaces the procedure_order_code rows (FHIR PUT replace semantics). Patient reference cannot be rebound.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the ServiceRequest resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(type: 'object')
            )
        ),
        responses: [
            new OA\Response(response: '200', description: 'ServiceRequest resource updated'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function put(string $fhirId, array $fhirJson): void
    {
        // Implementation lives in FhirGenericRestController::put()
    }
    // @codeCoverageIgnoreEnd
}
