<?php

/**
 * FhirCareTeamRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;

class FhirCareTeamRestController
{
    /**
     * @var FhirCareTeamService
     */
    private $fhirCareTeamService;
    private $fhirService;

    private ?OEGlobalsBag $oeGlobalsBag = null;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
    }

    public function getOEGlobals(): OEGlobalsBag
    {
        if (!isset($this->oeGlobalsBag)) {
            $this->oeGlobalsBag = new OEGlobalsBag();
        }
        return $this->oeGlobalsBag;
    }

    public function setOEGlobals(OEGlobalsBag $oeGlobals): void
    {
        $this->oeGlobalsBag = $oeGlobals;
    }

    public function getFhirCareTeamService(): FhirCareTeamService
    {
        if (!isset($this->fhirCareTeamService)) {
            $this->fhirCareTeamService = new FhirCareTeamService();
            $globals = $this->getOEGlobals();
            $defaultVersion = $globals->getString(GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value, FhirCareTeamService::PROFILE_VERSION_8_0_0);
            $this->fhirCareTeamService->setHighestCompatibleUSCoreProfileVersion($defaultVersion);
            if (isset($this->systemLogger)) {
                $this->fhirCareTeamService->setSystemLogger($this->systemLogger);
            }
        }
        return $this->fhirCareTeamService;
    }

    /**
     * Queries for a single FHIR CareTeam resource by FHIR id
     * @param $fhirId The FHIR CareTeam resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/CareTeam/{uuid}',
        description: 'Returns a single CareTeam resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the CareTeam resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: '_lastUpdated',
                in: 'query',
                description: 'Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)',
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
                            'id' => '94682f09-69fe-4ada-8ea6-753a52bd1516',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2021-09-16T01:07:22+00:00',
                            ],
                            'resourceType' => 'CareTeam',
                            'status' => 'active',
                            'subject' => [
                                'reference' => 'Patient/94682ef5-b0e3-4289-b19a-11b9592e9c92',
                                'type' => 'Patient',
                            ],
                            'participant' => [
                                [
                                    'role' => [
                                        [
                                            'coding' => [
                                                [
                                                    'system' => 'http://nucc.org/provider-taxonomy',
                                                    'code' => '102L00000X',
                                                    'display' => 'Psychoanalyst',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'member' => [
                                        'reference' => 'Practitioner/94682c68-f712-4c39-9158-ff132a08f26b',
                                        'type' => 'Practitioner',
                                    ],
                                    'onBehalfOf' => [
                                        'reference' => 'Organization/94682c62-b801-4498-84a1-13f158bb2a18',
                                        'type' => 'Organization',
                                    ],
                                ],
                                [
                                    'role' => [
                                        [
                                            'coding' => [
                                                [
                                                    'system' => 'http://terminology.hl7.org/CodeSystem/data-absent-reason',
                                                    'code' => 'unknown',
                                                    'display' => 'Unknown',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'member' => [
                                        'reference' => 'Organization/94682c62-b801-4498-84a1-13f158bb2a18',
                                        'type' => 'Organization',
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
        $processingResult = $this->getFhirCareTeamService()->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR CareTeam resources using various search parameters.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/CareTeam',
        description: 'Returns a list of CareTeam resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the CareTeam resource.',
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
                name: 'status',
                in: 'query',
                description: 'The status of the CarePlan resource.',
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
                                    'url' => 'https://localhost:9300/apis/default/fhir/CareTeam',
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
        $processingResult = $this->getFhirCareTeamService()->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('CareTeam', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
