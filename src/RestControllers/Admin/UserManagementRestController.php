<?php

/**
 * Admin User Management REST Controller — handles /api/admin/users endpoints.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\Admin;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Database\QueryPagination;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\Admin\UserManagementService;
use Psr\Http\Message\ResponseInterface;

class UserManagementRestController
{
    private readonly UserManagementService $service;

    private const WHITELISTED_FIELDS = [
        'fname',
        'lname',
        'mname',
        'email',
        'npi',
        'active',
        'username',
        'specialty',
        'facility_id',
        'authorized',
    ];

    /**
     * Wires the admin user management service.
     */
    public function __construct()
    {
        $this->service = new UserManagementService();
    }

    /**
     * @param array<string, mixed> $search
     */
    #[OA\Get(
        path: '/api/admin/users',
        description: 'Retrieves a list of users with admin-level detail (username, authorized, ACL groups)',
        tags: ['standard-admin'],
        parameters: [
            new OA\Parameter(name: 'fname', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'lname', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'username', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'authorized', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: '_limit',
                in: 'query',
                description: 'Maximum number of records to return (`_count` and `_maxresults` are accepted aliases). Clamped to QueryPagination::MAX_LIMIT. Omit for the unpaged list.',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: '_offset',
                in: 'query',
                description: 'Number of records to skip. Only applied when _limit is supplied.',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(HttpRestRequest $request, array $search = []): ResponseInterface
    {
        $validKeys = array_combine(self::WHITELISTED_FIELDS, self::WHITELISTED_FIELDS);
        $validSearchFields = array_intersect_key($search, $validKeys);
        $processingResult = $this->service->searchUsers($validSearchFields, true, self::buildPagination($search));
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }

    /**
     * Build the pagination for a list request from the `_limit` / `_offset` query parameters.
     *
     * `_count` and `_maxresults` are accepted as aliases for `_limit`: QueryPagination::getLinks()
     * emits `_count` in the first/next links, so a client following those links has to land on the
     * same paging behaviour it asked for.
     *
     * Returns null when no positive limit was supplied, which leaves the query unpaged.
     * QueryPagination clamps the limit to its own MAX_LIMIT.
     *
     * @param array<string, mixed> $search
     */
    private static function buildPagination(array $search): ?QueryPagination
    {
        $limit = 0;
        foreach (['_limit', '_maxresults', '_count'] as $limitParam) {
            if (isset($search[$limitParam]) && is_numeric($search[$limitParam])) {
                $limit = (int)$search[$limitParam];
                break;
            }
        }
        if ($limit <= 0) {
            return null;
        }
        $offset = isset($search['_offset']) && is_numeric($search['_offset']) ? (int)$search['_offset'] : 0;

        return new QueryPagination($limit, max(0, $offset));
    }

    /**
     * Returns a single user by UUID, or 404 when no record matches.
     *
     * @param string $uuid UUID of the requested user
     */
    #[OA\Get(
        path: '/api/admin/users/{uuid}',
        description: 'Retrieves a single user by UUID with admin-level detail',
        tags: ['standard-admin'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', description: 'User not found'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne(string $uuid, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->service->getOneByUuid($uuid);
        /** @var list<mixed> $data */
        $data = $processingResult->getData();
        if (!$processingResult->hasErrors() && count($data) === 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * @param array<string, mixed> $data
     */
    #[OA\Post(
        path: '/api/admin/users',
        description: 'Creates a new user (requires admin/super ACL)',
        tags: ['standard-admin'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password', 'admin_password', 'fname', 'lname', 'access_group'],
                properties: [
                    new OA\Property(property: 'username', type: 'string'),
                    new OA\Property(property: 'password', type: 'string', description: 'Password for the new user'),
                    new OA\Property(property: 'admin_password', type: 'string', description: 'Current password of the authenticated admin performing this action'),
                    new OA\Property(property: 'fname', type: 'string'),
                    new OA\Property(property: 'lname', type: 'string'),
                    new OA\Property(property: 'mname', type: 'string'),
                    new OA\Property(property: 'suffix', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'authorized', type: 'integer', enum: [0, 1]),
                    new OA\Property(property: 'facility_id', type: 'integer'),
                    new OA\Property(property: 'billing_facility_id', type: 'integer'),
                    new OA\Property(property: 'npi', type: 'string'),
                    new OA\Property(property: 'taxonomy', type: 'string'),
                    new OA\Property(property: 'specialty', type: 'string'),
                    new OA\Property(property: 'calendar', type: 'integer', enum: [0, 1]),
                    new OA\Property(property: 'portal_user', type: 'integer', enum: [0, 1]),
                    new OA\Property(property: 'federaltaxid', type: 'string'),
                    new OA\Property(property: 'state_license_number', type: 'string'),
                    new OA\Property(property: 'federaldrugid', type: 'string'),
                    new OA\Property(property: 'upin', type: 'string'),
                    new OA\Property(property: 'access_group', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'groupname', type: 'string', description: 'Group name (defaults to "Default")'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: '201', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(array $data, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->service->createUser($data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }
}
