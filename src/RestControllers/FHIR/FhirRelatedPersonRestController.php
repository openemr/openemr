<?php

/**
 * FhirRelatedPersonRestController.php
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Supports REST interactions with the FHIR RelatedPerson resource
 */
class FhirRelatedPersonRestController
{
    /**
     * @var FhirRelatedPersonService
     */
    private $fhirRelatedPersonService;

    private $fhirService;
    private $fhirValidate;
    private $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->fhirService = new FhirResourcesService();
        $this->fhirRelatedPersonService = new FhirRelatedPersonService();
    }

    /**
     * Queries for a single FHIR person resource by FHIR id
     *
     * @param   string $fhirId The FHIR person resource id (uuid)
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: '/fhir/RelatedPerson/{uuid}',
        description: 'Returns a single RelatedPerson resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the RelatedPerson resource.',
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
                            'id' => 'c266a919-6b22-4d7b-b169-b96adc5be3ef',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2025-10-25T21:15:11-04:00',
                                'profile' => [
                                    'http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson',
                                    'http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson|7.0.0',
                                    'http://hl7.org/fhir/us/core/StructureDefinition/us-core-relatedperson|8.0.0',
                                ],
                            ],
                            'resourceType' => 'RelatedPerson',
                            'active' => true,
                            'patient' => [
                                'reference' => 'Patient/96506861-511f-4f6d-bc97-b65a78cf1995',
                                'type' => 'Patient',
                            ],
                            'relationship' => [
                                [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/role-code',
                                            'code' => 'FAMMEMB',
                                            'display' => 'Family Member',
                                        ],
                                    ],
                                ],
                            ],
                            'name' => [
                                [
                                    'use' => 'official',
                                    'family' => 'Doe',
                                    'given' => [
                                        'John',
                                    ],
                                ],
                            ],
                            'telecom' => [
                                [
                                    'system' => 'phone',
                                    'value' => '(555) 555-5555',
                                    'use' => 'work',
                                ],
                                [
                                    'system' => 'phone',
                                    'value' => '(333) 333-3333',
                                    'use' => 'home',
                                ],
                                [
                                    'system' => 'email',
                                    'value' => 'example@open-emr.org',
                                    'use' => 'home',
                                ],
                            ],
                            'address' => [
                                [
                                    'line' => [
                                        '123 example street',
                                    ],
                                    'city' => 'Somewhere',
                                    'state' => 'CA',
                                    'period' => [
                                        'start' => '2024-10-25T21:15:11.737-04:00',
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
    public function getOne(string $fhirId): Response
    {
        $this->logger->debug("FhirPersonRestController->getOne(fhirId)", ["fhirId" => $fhirId]);
        $processingResult = $this->fhirRelatedPersonService->getOne($fhirId);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR person resources using various search parameters.
     * Search parameters include:
     * - active (active)
     * - address (street, zip, city, or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - email
     * - family
     * - given (first name or middle name)
     * - name (title, first name, middle name, last name)
     * - phone (phone, work, cell)
     * - telecom (email, phone)
     *
     * @return JsonResponse|Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: '/fhir/RelatedPerson',
        description: 'Returns a list of RelatedPerson resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the RelatedPerson resource.',
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
                                'lastUpdated' => '2025-09-30T09:13:51',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/RelatedPerson',
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
    public function getAll($searchParams): JsonResponse|Response
    {
        $processingResult = $this->fhirRelatedPersonService->getAll($searchParams);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle(FhirRelatedPersonService::RESOURCE_NAME, $bundleEntries, false);
        return RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
    }
}
