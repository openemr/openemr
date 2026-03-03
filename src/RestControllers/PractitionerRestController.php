<?php

/**
 * PractitionerRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\PractitionerService;
use OpenEMR\RestControllers\RestControllerHelper;

class PractitionerRestController
{
    private $practitionerService;

    /**
     * White list of practitioner search fields
     */
    private const WHITELISTED_FIELDS = [
        "title",
        "fname",
        "lname",
        "mname",
        "federaltaxid",
        "federaldrugid",
        "upin",
        "facility_id",
        "facility",
        "npi",
        "email",
        "specialty",
        "billname",
        "url",
        "assistant",
        "organization",
        "valedictory",
        "street",
        "streetb",
        "city",
        "state",
        "zip",
        "phone",
        "fax",
        "phonew1",
        "phonecell",
        "notes",
        "state_license_number",
        "username"
    ];

    public function __construct()
    {
        $this->practitionerService = new PractitionerService();
    }

    /**
     * Fetches a single practitioner resource by id.
     * @param $uuid- The practitioner uuid identifier in string format.
     * @param HttpRestRequest $request - The HTTP request object.
     */
    #[OA\Get(
        path: '/api/practitioner/{pruuid}',
        description: 'Retrieves a single practitioner by their uuid',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pruuid',
                in: 'path',
                description: 'The uuid for the practitioner.',
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
        $processingResult = $this->practitionerService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns practitioner resources which match an optional search criteria.
     * @param HttpRestRequest $request - The HTTP request object.
     * @param array $search - An array of search fields to filter the results.
     */
    #[OA\Get(
        path: '/api/practitioner',
        description: 'Retrieves a list of practitioners',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'title',
                in: 'query',
                description: 'The title for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'fname',
                in: 'query',
                description: 'The first name for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'lname',
                in: 'query',
                description: 'The last name for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'mname',
                in: 'query',
                description: 'The middle name for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'federaltaxid',
                in: 'query',
                description: 'The federal tax id for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'federaldrugid',
                in: 'query',
                description: 'The federal drug id for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'upin',
                in: 'query',
                description: 'The upin for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility_id',
                in: 'query',
                description: 'The facility id for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility',
                in: 'query',
                description: 'The facility for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'npi',
                in: 'query',
                description: 'The npi for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'email',
                in: 'query',
                description: 'The email for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'specialty',
                in: 'query',
                description: 'The specialty for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'billname',
                in: 'query',
                description: 'The billname for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'url',
                in: 'query',
                description: 'The url for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'assistant',
                in: 'query',
                description: 'The assistant for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'organization',
                in: 'query',
                description: 'The organization for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'valedictory',
                in: 'query',
                description: 'The valedictory for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'street',
                in: 'query',
                description: 'The street for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'streetb',
                in: 'query',
                description: 'The street (line 2) for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'city',
                in: 'query',
                description: 'The city for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'state',
                in: 'query',
                description: 'The state for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'zip',
                in: 'query',
                description: 'The zip for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phone',
                in: 'query',
                description: 'The phone for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'fax',
                in: 'query',
                description: 'The fax for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phonew1',
                in: 'query',
                description: 'The phonew1 for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phonecell',
                in: 'query',
                description: 'The phonecell for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'notes',
                in: 'query',
                description: 'The notes for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'state_license_number2',
                in: 'query',
                description: 'The state license number for the practitioner.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'username',
                in: 'query',
                description: 'The username for the practitioner.',
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
        $validSearchFields = $this->practitionerService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->practitionerService->getAll($validSearchFields);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }

    /**
     * Process a HTTP POST request used to create a practitioner record.
     * @param $data - array of practitioner fields.
     * @param HttpRestRequest $request - The HTTP request object.
     */
    #[OA\Post(
        path: '/api/practitioner',
        description: 'Submits a new practitioner',
        tags: ['standard'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['fname', 'lname', 'npi'],
                    properties: [
                        new OA\Property(property: 'title', description: 'The title for the practitioner.', type: 'string'),
                        new OA\Property(property: 'fname', description: 'The first name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'mname', description: 'The middle name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'lname', description: 'The last name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'federaltaxid', description: 'The federal tax id for the practitioner.', type: 'string'),
                        new OA\Property(property: 'federaldrugid', description: 'The federal drug id for the practitioner.', type: 'string'),
                        new OA\Property(property: 'upin', description: 'The upin for the practitioner.', type: 'string'),
                        new OA\Property(property: 'facility_id', description: 'The facility_id for the practitioner.', type: 'string'),
                        new OA\Property(property: 'facility', description: 'The facility name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'npi', description: 'The npi for the practitioner.', type: 'string'),
                        new OA\Property(property: 'email', description: 'The email for the practitioner.', type: 'string'),
                        new OA\Property(property: 'specialty', description: 'The specialty for the practitioner.', type: 'string'),
                        new OA\Property(property: 'billname', description: 'The billname for the practitioner.', type: 'string'),
                        new OA\Property(property: 'url', description: 'The url for the practitioner.', type: 'string'),
                        new OA\Property(property: 'assistant', description: 'The assistant for the practitioner.', type: 'string'),
                        new OA\Property(property: 'valedictory', description: 'The valedictory for the practitioner.', type: 'string'),
                        new OA\Property(property: 'street', description: 'The street address for the practitioner.', type: 'string'),
                        new OA\Property(property: 'streetb', description: 'The streetb address for the practitioner.', type: 'string'),
                        new OA\Property(property: 'city', description: 'The city for the practitioner.', type: 'string'),
                        new OA\Property(property: 'state', description: 'The state for the practitioner.', type: 'string'),
                        new OA\Property(property: 'zip', description: 'The zip for the practitioner.', type: 'string'),
                        new OA\Property(property: 'phone', description: 'The phone for the practitioner.', type: 'string'),
                        new OA\Property(property: 'fax', description: 'The fax for the practitioner.', type: 'string'),
                        new OA\Property(property: 'phonew1', description: 'The phonew1 for the practitioner.', type: 'string'),
                        new OA\Property(property: 'phonecell', description: 'The phonecell for the practitioner.', type: 'string'),
                        new OA\Property(property: 'notes', description: 'The notes for the practitioner.', type: 'string'),
                        new OA\Property(property: 'state_license_number', description: 'The state license number for the practitioner.', type: 'string'),
                        new OA\Property(property: 'username', description: 'The username for the practitioner.', type: 'string'),
                    ],
                    example: [
                        'title' => 'Mrs.',
                        'fname' => 'Eduardo',
                        'mname' => 'Kathy',
                        'lname' => 'Perez',
                        'federaltaxid' => '',
                        'federaldrugid' => '',
                        'upin' => '',
                        'facility_id' => '3',
                        'facility' => 'Your Clinic Name Here',
                        'npi' => '12345678901',
                        'email' => 'info@pennfirm.com',
                        'specialty' => '',
                        'billname' => null,
                        'url' => null,
                        'assistant' => null,
                        'organization' => null,
                        'valedictory' => null,
                        'street' => '789 Third Avenue',
                        'streetb' => '123 Cannaut Street',
                        'city' => 'San Diego',
                        'state' => 'CA',
                        'zip' => '90210',
                        'phone' => '(619) 555-9827',
                        'fax' => null,
                        'phonew1' => '(619) 555-7822',
                        'phonecell' => '(619) 555-7821',
                        'notes' => null,
                        'state_license_number' => '123456',
                        'username' => 'eduardoperez',
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'validationErrors',
                                description: 'Validation errors.',
                                type: 'array',
                                items: new OA\Items(type: 'object')
                            ),
                            new OA\Property(
                                property: 'internalErrors',
                                description: 'Internal errors.',
                                type: 'array',
                                items: new OA\Items(type: 'object')
                            ),
                            new OA\Property(
                                property: 'data',
                                description: 'Returned data.',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', description: 'practitioner id', type: 'integer'),
                                        new OA\Property(property: 'uuid', description: 'practitioner uuid', type: 'string'),
                                    ]
                                )
                            ),
                        ],
                        example: [
                            'validationErrors' => [],
                            'error_description' => [],
                            'data' => [
                                'id' => 7,
                                'uuid' => '90d453fb-0248-4c0d-9575-d99d02b169f5',
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post($data, HttpRestRequest $request): ResponseInterface
    {
        $filteredData = $this->practitionerService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->practitionerService->insert($filteredData);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Processes a HTTP PATCH request used to update an existing practitioner record.
     * @param $uuid - The practitioner uuid identifier in string format.
     * @param $data - array of practitioner fields (full resource).
     * @param HttpRestRequest $request - The HTTP request object.
     */
    #[OA\Put(
        path: '/api/practitioner/{pruuid}',
        description: 'Edit a practitioner',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pruuid',
                in: 'path',
                description: 'The uuid for the practitioner.',
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
                        new OA\Property(property: 'title', description: 'The title for the practitioner.', type: 'string'),
                        new OA\Property(property: 'fname', description: 'The first name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'mname', description: 'The middle name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'lname', description: 'The last name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'federaltaxid', description: 'The federal tax id for the practitioner.', type: 'string'),
                        new OA\Property(property: 'federaldrugid', description: 'The federal drug id for the practitioner.', type: 'string'),
                        new OA\Property(property: 'upin', description: 'The upin for the practitioner.', type: 'string'),
                        new OA\Property(property: 'facility_id', description: 'The facility_id for the practitioner.', type: 'string'),
                        new OA\Property(property: 'facility', description: 'The facility name for the practitioner.', type: 'string'),
                        new OA\Property(property: 'npi', description: 'The npi for the practitioner.', type: 'string'),
                        new OA\Property(property: 'email', description: 'The email for the practitioner.', type: 'string'),
                        new OA\Property(property: 'specialty', description: 'The specialty for the practitioner.', type: 'string'),
                        new OA\Property(property: 'billname', description: 'The billname for the practitioner.', type: 'string'),
                        new OA\Property(property: 'url', description: 'The url for the practitioner.', type: 'string'),
                        new OA\Property(property: 'assistant', description: 'The assistant for the practitioner.', type: 'string'),
                        new OA\Property(property: 'valedictory', description: 'The valedictory for the practitioner.', type: 'string'),
                        new OA\Property(property: 'street', description: 'The street address for the practitioner.', type: 'string'),
                        new OA\Property(property: 'streetb', description: 'The streetb address for the practitioner.', type: 'string'),
                        new OA\Property(property: 'city', description: 'The city for the practitioner.', type: 'string'),
                        new OA\Property(property: 'state', description: 'The state for the practitioner.', type: 'string'),
                        new OA\Property(property: 'zip', description: 'The zip for the practitioner.', type: 'string'),
                        new OA\Property(property: 'phone', description: 'The phone for the practitioner.', type: 'string'),
                        new OA\Property(property: 'fax', description: 'The fax for the practitioner.', type: 'string'),
                        new OA\Property(property: 'phonew1', description: 'The phonew1 for the practitioner.', type: 'string'),
                        new OA\Property(property: 'phonecell', description: 'The phonecell for the practitioner.', type: 'string'),
                        new OA\Property(property: 'notes', description: 'The notes for the practitioner.', type: 'string'),
                        new OA\Property(property: 'state_license_number', description: 'The state license number for the practitioner.', type: 'string'),
                        new OA\Property(property: 'username', description: 'The username for the practitioner.', type: 'string'),
                    ],
                    example: [
                        'title' => 'Mr',
                        'fname' => 'Baz',
                        'mname' => '',
                        'lname' => 'Bop',
                        'street' => '456 Tree Lane',
                        'zip' => '08642',
                        'city' => 'FooTown',
                        'state' => 'FL',
                        'phone' => '123-456-7890',
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'validationErrors',
                                description: 'Validation errors.',
                                type: 'array',
                                items: new OA\Items(type: 'object')
                            ),
                            new OA\Property(
                                property: 'internalErrors',
                                description: 'Internal errors.',
                                type: 'array',
                                items: new OA\Items(type: 'object')
                            ),
                            new OA\Property(
                                property: 'data',
                                description: 'Returned data.',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', description: 'practitioner id', type: 'string'),
                                        new OA\Property(property: 'uuid', description: 'practitioner uuid', type: 'string'),
                                        new OA\Property(property: 'title', description: 'practitioner title', type: 'string'),
                                        new OA\Property(property: 'fname', description: 'practitioner fname', type: 'string'),
                                        new OA\Property(property: 'lname', description: 'practitioner lname', type: 'string'),
                                        new OA\Property(property: 'mname', description: 'practitioner mname', type: 'string'),
                                        new OA\Property(property: 'federaltaxid', description: 'practitioner federaltaxid', type: 'string'),
                                        new OA\Property(property: 'federaldrugid', description: 'practitioner federaldrugid', type: 'string'),
                                        new OA\Property(property: 'upin', description: 'practitioner upin', type: 'string'),
                                        new OA\Property(property: 'facility_id', description: 'practitioner facility_id', type: 'string'),
                                        new OA\Property(property: 'facility', description: 'practitioner facility', type: 'string'),
                                        new OA\Property(property: 'npi', description: 'practitioner npi', type: 'string'),
                                        new OA\Property(property: 'email', description: 'practitioner email', type: 'string'),
                                        new OA\Property(property: 'active', description: 'practitioner active setting', type: 'string'),
                                        new OA\Property(property: 'specialty', description: 'practitioner specialty', type: 'string'),
                                        new OA\Property(property: 'billname', description: 'practitioner billname', type: 'string'),
                                        new OA\Property(property: 'url', description: 'practitioner url', type: 'string'),
                                        new OA\Property(property: 'assistant', description: 'practitioner assistant', type: 'string'),
                                        new OA\Property(property: 'organization', description: 'practitioner organization', type: 'string'),
                                        new OA\Property(property: 'valedictory', description: 'practitioner valedictory', type: 'string'),
                                        new OA\Property(property: 'street', description: 'practitioner street', type: 'string'),
                                        new OA\Property(property: 'streetb', description: 'practitioner streetb', type: 'string'),
                                        new OA\Property(property: 'city', description: 'practitioner city', type: 'string'),
                                        new OA\Property(property: 'state', description: 'practitioner state', type: 'string'),
                                        new OA\Property(property: 'zip', description: 'practitioner zip', type: 'string'),
                                        new OA\Property(property: 'phone', description: 'practitioner phone', type: 'string'),
                                        new OA\Property(property: 'fax', description: 'fax', type: 'string'),
                                        new OA\Property(property: 'phonew1', description: 'practitioner phonew1', type: 'string'),
                                        new OA\Property(property: 'phonecell', description: 'practitioner phonecell', type: 'string'),
                                        new OA\Property(property: 'notes', description: 'practitioner notes', type: 'string'),
                                        new OA\Property(property: 'state_license_number', description: 'practitioner state license number', type: 'string'),
                                        new OA\Property(property: 'abook_title', description: 'practitioner abook title', type: 'string'),
                                        new OA\Property(property: 'physician_title', description: 'practitioner physician title', type: 'string'),
                                        new OA\Property(property: 'physician_code', description: 'practitioner physician code', type: 'string'),
                                    ]
                                )
                            ),
                        ],
                        example: [
                            'validationErrors' => [],
                            'error_description' => [],
                            'data' => [
                                'id' => 7,
                                'uuid' => '90d453fb-0248-4c0d-9575-d99d02b169f5',
                                'title' => 'Mr',
                                'fname' => 'Baz',
                                'lname' => 'Bop',
                                'mname' => '',
                                'federaltaxid' => '',
                                'federaldrugid' => '',
                                'upin' => '',
                                'facility_id' => '3',
                                'facility' => 'Your Clinic Name Here',
                                'npi' => '0123456789',
                                'email' => 'info@pennfirm.com',
                                'active' => '1',
                                'specialty' => '',
                                'billname' => '',
                                'url' => '',
                                'assistant' => '',
                                'organization' => '',
                                'valedictory' => '',
                                'street' => '456 Tree Lane',
                                'streetb' => '123 Cannaut Street',
                                'city' => 'FooTown',
                                'state' => 'FL',
                                'zip' => '08642',
                                'phone' => '123-456-7890',
                                'fax' => '',
                                'phonew1' => '(619) 555-7822',
                                'phonecell' => '(619) 555-7821',
                                'notes' => '',
                                'state_license_number' => '123456',
                                'abook_title' => null,
                                'physician_title' => null,
                                'physician_code' => null,
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function patch($uuid, $data, HttpRestRequest $request): ResponseInterface
    {
        $filteredData = $this->practitionerService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->practitionerService->update($uuid, $filteredData);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }
}
