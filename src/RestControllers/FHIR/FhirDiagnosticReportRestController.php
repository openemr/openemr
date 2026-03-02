<?php

/**
 * FhirDiagnosticReportRestController.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\FhirDiagnosticReportService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirDiagnosticReportRestController
{
    private $fhirService;
    /**
     * @var FhirDiagnosticReportService
     */
    private $service;

    public function __construct(HttpRestRequest $request)
    {
        $this->fhirService = new FhirResourcesService();
        $this->service = new FhirDiagnosticReportService();
        $this->service->setSession($request->getSession());
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/DiagnosticReport/{uuid}',
        description: 'Returns a single DiagnosticReport resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the DiagnosticReport resource.',
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
                            'id' => '93fb2d6a-77ac-48ca-a12d-1a17e40007e3',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2021-09-18T20:52:34+00:00',
                            ],
                            'resourceType' => 'DiagnosticReport',
                            'status' => 'final',
                            'category' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://loinc.org',
                                            'code' => 'LP7839-6',
                                            'display' => 'Pathology',
                                        ],
                                    ],
                                ],
                            ],
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://loinc.org',
                                        'code' => '11502-2',
                                        'display' => 'Laboratory report',
                                    ],
                                ],
                            ],
                            'subject' => [
                                'reference' => 'Patient/9353b8f5-0a87-4e2a-afd4-25341fdb0fbc',
                                'type' => 'Patient',
                            ],
                            'encounter' => [
                                'reference' => 'Encounter/93540818-cb5f-49df-b73b-83901bb793b6',
                                'type' => 'Encounter',
                            ],
                            'effectiveDateTime' => '2015-06-22T00:00:00+00:00',
                            'issued' => '2015-06-22T00:00:00+00:00',
                            'performer' => [
                                [
                                    'reference' => 'Organization/935249b5-0ba6-4b5b-8863-a7a27d4c6350',
                                    'type' => 'Organization',
                                ],
                            ],
                            'presentedForm' => [
                                [
                                    'contentType' => 'text/plain',
                                    'data' => 'TXMgQWxpY2UgTmV3bWFuIHdhcyB0ZXN0ZWQgZm9yIHRoZSBVcmluYW5hbHlzaXMgbWFjcm8gcGFuZWwgYW5kIHRoZSByZXN1bHRzIGhhdmUgYmVlbiBmb3VuZCB0byBiZSANCm5vcm1hbC4=',
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
        $processingResult = $this->service->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR location resources using various search parameters.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/DiagnosticReport',
        description: 'Returns a list of DiagnosticReport resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the DiagnosticReport resource.',
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
                name: 'code',
                in: 'query',
                description: 'The code of the DiagnosticReport resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                description: 'The category of the DiagnosticReport resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'date',
                in: 'query',
                description: 'The datetime of the DiagnosticReport resource.',
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
                                    'url' => 'https://localhost:9300/apis/default/fhir/DiagnosticReport',
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
        $processingResult = $this->service->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('DiagnosticReport', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
