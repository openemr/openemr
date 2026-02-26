<?php

/**
 * FacilityRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FacilityService;
use OpenEMR\RestControllers\RestControllerHelper;

class FacilityRestController
{
    private $facilityService;

    /**
     * White list of facility search fields
     */
    private const WHITELISTED_FIELDS = [
        "name",
        "phone",
        "fax",
        "street",
        "city",
        "state",
        "postal_code",
        "country_code",
        "federal_ein",
        "website",
        "email",
        "domain_identifier",
        "facility_npi",
        "facility_taxonomy",
        "facility_code",
        "billing_location",
        "accepts_assignment",
        "oid",
        "service_location"
    ];

    public function __construct()
    {
        $this->facilityService = new FacilityService();
    }

    /**
     * Fetches a single facility resource by id.
     * @param $uuid - The facility uuid identifier in string format.
     * @param HttpRestRequest $request - The HTTP request object.
     */
    #[OA\Get(
        path: '/api/facility/{fuuid}',
        description: 'Returns a single facility.',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'fuuid',
                in: 'path',
                description: 'The uuid for the facility.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne($uuid, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->facilityService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns facility resources which match an optional search criteria.
     * @param HttpRestRequest $request - The HTTP request object.
     * @param array $search - An array of search fields to filter the results.
     */
    #[OA\Get(
        path: '/api/facility',
        description: 'Returns a single facility.',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'query',
                description: 'The name for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility_npi',
                in: 'query',
                description: 'The facility_npi for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phone',
                in: 'query',
                description: 'The phone for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'fax',
                in: 'query',
                description: 'The fax for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'street',
                in: 'query',
                description: 'The street for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'city',
                in: 'query',
                description: 'The city for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'state',
                in: 'query',
                description: 'The state for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'postal_code',
                in: 'query',
                description: 'The postal_code for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'country_code',
                in: 'query',
                description: 'The country_code for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'federal_ein',
                in: 'query',
                description: 'The federal_ein for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'website',
                in: 'query',
                description: 'The website for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'email',
                in: 'query',
                description: 'The email for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'domain_identifier',
                in: 'query',
                description: 'The domain_identifier for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility_taxonomy',
                in: 'query',
                description: 'The facility_taxonomy for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility_code',
                in: 'query',
                description: 'The facility_code for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'billing_location',
                in: 'query',
                description: 'The billing_location setting for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'accepts_assignment',
                in: 'query',
                description: 'The accepts_assignment setting for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'oid',
                in: 'query',
                description: 'The oid for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'service_location',
                in: 'query',
                description: 'The service_location setting for the facility.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(HttpRestRequest $request, $search = []): ResponseInterface
    {
        $validSearchFields = $this->facilityService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->facilityService->getAll($validSearchFields);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }

    /**
     * Process a HTTP POST request used to create a facility record.
     */
    #[OA\Post(
        path: '/api/facility',
        description: 'Creates a facility in the system',
        tags: ['standard'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'facility_npi'],
                    properties: [
                        new OA\Property(property: 'name', description: 'The name for the facility.', type: 'string'),
                        new OA\Property(property: 'facility_npi', description: 'The facility_npi for the facility.', type: 'string'),
                        new OA\Property(property: 'phone', description: 'The phone for the facility.', type: 'string'),
                        new OA\Property(property: 'fax', description: 'The fax for the facility.', type: 'string'),
                        new OA\Property(property: 'street', description: 'The street for the facility.', type: 'string'),
                        new OA\Property(property: 'city', description: 'The city for the facility.', type: 'string'),
                        new OA\Property(property: 'state', description: 'The state for the facility.', type: 'string'),
                        new OA\Property(property: 'postal_code', description: 'The postal_code for the facility.', type: 'string'),
                        new OA\Property(property: 'country_code', description: 'The country_code for the facility.', type: 'string'),
                        new OA\Property(property: 'federal_ein', description: 'The federal_ein for the facility.', type: 'string'),
                        new OA\Property(property: 'website', description: 'The website for the facility.', type: 'string'),
                        new OA\Property(property: 'email', description: 'The email for the facility.', type: 'string'),
                        new OA\Property(property: 'domain_identifier', description: 'The domain_identifier for the facility.', type: 'string'),
                        new OA\Property(property: 'facility_taxonomy', description: 'The facility_taxonomy for the facility.', type: 'string'),
                        new OA\Property(property: 'facility_code', description: 'The facility_code for the facility.', type: 'string'),
                        new OA\Property(property: 'billing_location', description: 'The billing_location setting for the facility.', type: 'string'),
                        new OA\Property(property: 'accepts_assignment', description: 'The accepts_assignment setting for the facility.', type: 'string'),
                        new OA\Property(property: 'oid', description: 'The oid for the facility.', type: 'string'),
                        new OA\Property(property: 'service_location', description: 'The service_location setting for the facility.', type: 'string'),
                    ],
                    example: [
                        'name' => 'Aquaria',
                        'facility_npi' => '123456789123',
                        'phone' => '808-606-3030',
                        'fax' => '808-606-3031',
                        'street' => '1337 Bit Shifter Ln',
                        'city' => 'San Lorenzo',
                        'state' => 'ZZ',
                        'postal_code' => '54321',
                        'country_code' => 'US',
                        'federal_ein' => '4343434',
                        'website' => 'https://example.com',
                        'email' => 'foo@bar.com',
                        'domain_identifier' => '',
                        'facility_taxonomy' => '',
                        'facility_code' => '',
                        'billing_location' => '1',
                        'accepts_assignment' => '1',
                        'oid' => '',
                        'service_location' => '1',
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post($data, HttpRestRequest $request): ResponseInterface
    {
        $filteredData = $this->facilityService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->facilityService->insert($filteredData);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Processes a HTTP PATCH request used to update an existing facility record.
     */
    #[OA\Put(
        path: '/api/facility/{fuuid}',
        description: 'Updates a facility in the system',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'fuuid',
                in: 'path',
                description: 'The uuid for the facility.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', description: 'The name for the facility.', type: 'string'),
                        new OA\Property(property: 'facility_npi', description: 'The facility_npi for the facility.', type: 'string'),
                        new OA\Property(property: 'phone', description: 'The phone for the facility.', type: 'string'),
                        new OA\Property(property: 'fax', description: 'The fax for the facility.', type: 'string'),
                        new OA\Property(property: 'street', description: 'The street for the facility.', type: 'string'),
                        new OA\Property(property: 'city', description: 'The city for the facility.', type: 'string'),
                        new OA\Property(property: 'state', description: 'The state for the facility.', type: 'string'),
                        new OA\Property(property: 'postal_code', description: 'The postal_code for the facility.', type: 'string'),
                        new OA\Property(property: 'country_code', description: 'The country_code for the facility.', type: 'string'),
                        new OA\Property(property: 'federal_ein', description: 'The federal_ein for the facility.', type: 'string'),
                        new OA\Property(property: 'website', description: 'The website for the facility.', type: 'string'),
                        new OA\Property(property: 'email', description: 'The email for the facility.', type: 'string'),
                        new OA\Property(property: 'domain_identifier', description: 'The domain_identifier for the facility.', type: 'string'),
                        new OA\Property(property: 'facility_taxonomy', description: 'The facility_taxonomy for the facility.', type: 'string'),
                        new OA\Property(property: 'facility_code', description: 'The facility_code for the facility.', type: 'string'),
                        new OA\Property(property: 'billing_location', description: 'The billing_location setting for the facility.', type: 'string'),
                        new OA\Property(property: 'accepts_assignment', description: 'The accepts_assignment setting for the facility.', type: 'string'),
                        new OA\Property(property: 'oid', description: 'The oid for the facility.', type: 'string'),
                        new OA\Property(property: 'service_location', description: 'The service_location setting for the facility.', type: 'string'),
                    ],
                    example: [
                        'name' => 'Aquaria',
                        'facility_npi' => '123456789123',
                        'phone' => '808-606-3030',
                        'fax' => '808-606-3031',
                        'street' => '1337 Bit Shifter Ln',
                        'city' => 'San Lorenzo',
                        'state' => 'ZZ',
                        'postal_code' => '54321',
                        'country_code' => 'US',
                        'federal_ein' => '4343434',
                        'website' => 'https://example.com',
                        'email' => 'foo@bar.com',
                        'domain_identifier' => '',
                        'facility_taxonomy' => '',
                        'facility_code' => '',
                        'billing_location' => '1',
                        'accepts_assignment' => '1',
                        'oid' => '',
                        'service_location' => '1',
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function patch($uuid, $data, HttpRestRequest $request): ResponseInterface
    {
        $filteredData = $this->facilityService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->facilityService->update($uuid, $filteredData);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }
}
