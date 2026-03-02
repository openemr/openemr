<?php

/**
 * FhirMediaRestController
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
use OpenEMR\Services\FHIR\FhirMediaService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\Response;

class FhirMediaRestController
{
    /**
     * @var FhirMediaService
     */
    private readonly FhirMediaService $fhirMediaService;


    public function __construct(HttpRestRequest $request)
    {
        $this->fhirMediaService = new FhirMediaService();
        $this->fhirMediaService->setSession($request->getSession());
    }

    /**
     * Queries for FHIR Media resources using various search parameters.
     * @param string[] $fhirSearchParameters The FHIR search parameters
     * @param string|null $puuidBind The patient uuid to bind to the search, if applicable
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/Media',
        description: 'Returns a search bundle of Media resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the Media resource.',
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
                name: 'content-type',
                in: 'query',
                description: 'The Content-Type of the Media resource.',
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
                                    'url' => 'https://localhost:9300/apis/default/fhir/Media',
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
    public function getAll(array $fhirSearchParameters, ?string $puuidBind = null): Response
    {
        $processingResult = $this->fhirMediaService->getAll($fhirSearchParameters, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = (new FhirResourcesService())->createBundle('Media', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }

    /**
     * Queries for a single FHIR Media resource by FHIR id
     * @param $fhirId The FHIR Media resource id (uuid)
     * @param $patientUuid The patient uuid to filter by
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/Media/{uuid}',
        description: 'Returns a single Media resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Media resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'patient',
                in: 'query',
                description: 'The uuid for the Patient resource to filter Media references by patient.',
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
                            'id' => 'a037abc5-7ebb-43a1-9e0f-b57586dc6d25',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2025-10-27T20:00:54-04:00',
                            ],
                            'resourceType' => 'Media',
                            'status' => 'completed',
                            'subject' => [
                                'reference' => 'Patient/96506861-511f-4f6d-bc97-b65a78cf1995',
                                'type' => 'Patient',
                            ],
                            'content' => [
                                'contentType' => 'application/dicom',
                                'url' => '/fhir/Binary/a037abc5-7ebb-43a1-9e0f-b57586dc6d25',
                                'title' => 'MR000021.dcm',
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
    public function getOne($fhirId, $patientUuid): Response
    {
        $processingResult = $this->fhirMediaService->getOne($fhirId, $patientUuid);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
