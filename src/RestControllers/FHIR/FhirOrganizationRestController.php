<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\Serialization\FhirOrganizationSerializer;
use Symfony\Component\HttpFoundation\Response;

/**
 * FHIR Organization Service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirOrganizationRestController
{
    use SystemLoggerAwareTrait;

    /**
     * @var FhirOrganizationService
     */
    private FhirOrganizationService $fhirOrganizationService;
    private FhirResourcesService $fhirService;
    private FhirValidationService $fhirValidationService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirOrganizationService = new FhirOrganizationService();
        $this->fhirValidationService = new FhirValidationService();
    }

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->fhirOrganizationService->setSystemLogger($systemLogger);
        $this->systemLogger = $systemLogger;
    }


    /**
     * Queries for FHIR organization resources using various search parameters.
     * Search parameters include:
     * - address (street, postal_code, city, country_code or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - email
     * - name
     * - phone (work)
     * - telecom (email, phone)
     * @return Response The http response object containing the FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/Organization',
        description: 'Returns a list of Organization resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the Organization resource.',
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
                name: 'name',
                in: 'query',
                description: 'The name of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'email',
                in: 'query',
                description: 'The email of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phone',
                in: 'query',
                description: 'The phone of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'telecom',
                in: 'query',
                description: 'The telecom of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'address',
                in: 'query',
                description: 'The address of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'address-city',
                in: 'query',
                description: 'The address-city of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'address-postalcode',
                in: 'query',
                description: 'The address-postalcode of the Organization resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'address-state',
                in: 'query',
                description: 'The address-state of the Organization resource.',
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
                                    'url' => 'https://localhost:9300/apis/default/fhir/Organization',
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
    public function getAll($searchParams): Response
    {
        $processingResult = $this->fhirOrganizationService->getAll($searchParams);
        $bundleEntries = [];
        // TODO: adunsulag why isn't this work done in the fhirService->createBundle?
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            $bundleEntries[] = $fhirBundleEntry;
        }
        $bundleSearchResult = $this->fhirService->createBundle('Organization', $bundleEntries, false);
        return RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
    }


    /**
     * Queries for a single FHIR organization resource by FHIR id
     * @param $fhirId string The FHIR organization resource id (uuid)
     * @param $puuidBind string|null Optional to restrict visibility of the organization to the one with this puuid.
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/Organization/{uuid}',
        description: 'Returns a single Organization resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Organization resource.',
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
                            'id' => '95f0e672-be37-4c73-95c9-649c2d200018',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2022-03-30T07:43:23+00:00',
                            ],
                            'resourceType' => 'Organization',
                            'text' => [
                                'status' => 'generated',
                                'div' => "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>",
                            ],
                            'identifier' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/us-npi',
                                    'value' => '1234567890',
                                ],
                            ],
                            'active' => true,
                            'type' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                            'code' => 'prov',
                                            'display' => 'Healthcare Provider',
                                        ],
                                    ],
                                ],
                            ],
                            'name' => 'Your Clinic Name Here',
                            'telecom' => [
                                [
                                    'system' => 'phone',
                                    'value' => '000-000-0000',
                                    'use' => 'work',
                                ],
                                [
                                    'system' => 'fax',
                                    'value' => '000-000-0000',
                                    'use' => 'work',
                                ],
                            ],
                            'address' => [
                                null,
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
    public function getOne(string $fhirId, ?string $puuidBind = null): Response
    {
        $processingResult = $this->fhirOrganizationService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Creates a new FHIR organization resource
     * @param $fhirJson array The FHIR organization resource
     * @returns Response 201 if the resource is created, 400 if the resource is invalid
     */
    #[OA\Post(
        path: '/fhir/Organization',
        description: 'Adds a Organization resource.',
        tags: ['fhir'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    description: 'The json object for the Organization resource.',
                    type: 'object'
                ),
                example: [
                    'id' => '95f0e672-be37-4c73-95c9-649c2d200018',
                    'meta' => [
                        'versionId' => '1',
                        'lastUpdated' => '2022-03-30T07:43:23+00:00',
                    ],
                    'resourceType' => 'Organization',
                    'text' => [
                        'status' => 'generated',
                        'div' => "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>",
                    ],
                    'identifier' => [
                        [
                            'system' => 'http://hl7.org/fhir/sid/us-npi',
                            'value' => '1234567890',
                        ],
                    ],
                    'active' => true,
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                    'code' => 'prov',
                                    'display' => 'Healthcare Provider',
                                ],
                            ],
                        ],
                    ],
                    'name' => 'Your Clinic Name Here Hey',
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '000-000-0000',
                            'use' => 'work',
                        ],
                        [
                            'system' => 'fax',
                            'value' => '000-000-0000',
                            'use' => 'work',
                        ],
                    ],
                    'address' => [
                        null,
                    ],
                ]
            )
        ),
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
                            'id' => '95f0e672-be37-4c73-95c9-649c2d200018',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2022-03-30T07:43:23+00:00',
                            ],
                            'resourceType' => 'Organization',
                            'text' => [
                                'status' => 'generated',
                                'div' => "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>",
                            ],
                            'identifier' => [
                                [
                                    'system' => 'http://hl7.org/fhir/sid/us-npi',
                                    'value' => '1234567890',
                                ],
                            ],
                            'active' => true,
                            'type' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                            'code' => 'prov',
                                            'display' => 'Healthcare Provider',
                                        ],
                                    ],
                                ],
                            ],
                            'name' => 'Your Clinic Name Here Now',
                            'telecom' => [
                                [
                                    'system' => 'phone',
                                    'value' => '000-000-0000',
                                    'use' => 'work',
                                ],
                                [
                                    'system' => 'fax',
                                    'value' => '000-000-0000',
                                    'use' => 'work',
                                ],
                            ],
                            'address' => [
                                null,
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
    public function post(array $fhirJson): Response
    {
        $fhirValidationService = $this->fhirValidationService->validate($fhirJson);
        if (!empty($fhirValidationService)) {
            return RestControllerHelper::responseHandler($fhirValidationService, null, 400);
        }

        $organization = $this->createOrganizationFromJSON($fhirJson);
        $processingResult = $this->fhirOrganizationService->insert($organization);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR organization resource
     * @param $fhirId string The FHIR organization resource id (uuid)
     * @param $fhirJson array The updated FHIR organization resource (complete resource)
     * @returns Response 200 if the resource is created, 400 if the resource is invalid
     */
    #[OA\Put(
        path: '/fhir/Organization/{uuid}',
        description: 'Modifies a Organization resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the organization.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    description: 'The json object for the Organization resource.',
                    type: 'object'
                ),
                example: [
                    'id' => '95f0e672-be37-4c73-95c9-649c2d200018',
                    'meta' => [
                        'versionId' => '1',
                        'lastUpdated' => '2022-03-30T07:43:23+00:00',
                    ],
                    'resourceType' => 'Organization',
                    'text' => [
                        'status' => 'generated',
                        'div' => "<div xmlns='http://www.w3.org/1999/xhtml'> <p>Your Clinic Name Here</p></div>",
                    ],
                    'identifier' => [
                        [
                            'system' => 'http://hl7.org/fhir/sid/us-npi',
                            'value' => '1234567890',
                        ],
                    ],
                    'active' => true,
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                                    'code' => 'prov',
                                    'display' => 'Healthcare Provider',
                                ],
                            ],
                        ],
                    ],
                    'name' => 'Your Clinic Name Here',
                    'telecom' => [
                        [
                            'system' => 'phone',
                            'value' => '000-000-0000',
                            'use' => 'work',
                        ],
                        [
                            'system' => 'fax',
                            'value' => '000-000-0000',
                            'use' => 'work',
                        ],
                    ],
                    'address' => [
                        null,
                    ],
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: '201',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        example: [
                            'id' => 14,
                            'uuid' => '95f217c1-258c-44ca-bf11-909dce369574',
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function patch(string $fhirId, array $fhirJson): Response
    {
        $fhirValidationService = $this->fhirValidationService->validate($fhirJson);
        if (!empty($fhirValidationService)) {
            return RestControllerHelper::responseHandler($fhirValidationService, null, 400);
        }

        $organization = $this->createOrganizationFromJSON($fhirJson);
        $processingResult = $this->fhirOrganizationService->update($fhirId, $organization);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    private function createOrganizationFromJSON($fhirJson): FHIROrganization
    {
        return FhirOrganizationSerializer::deserialize($fhirJson);
    }
}
