<?php

/**
 * REST controller for background service listing and execution.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\Background\BackgroundServiceRegistry;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BackgroundServiceRestController
{
    private readonly BackgroundServiceRegistry $registry;
    private readonly BackgroundServiceRunner $runner;

    public function __construct(
        ?BackgroundServiceRegistry $registry = null,
        ?BackgroundServiceRunner $runner = null,
    ) {
        $this->registry = $registry ?? new BackgroundServiceRegistry();
        $this->runner = $runner ?? new BackgroundServiceRunner();
    }

    #[OA\Get(
        path: '/api/background_service',
        description: 'Retrieves all registered background services',
        tags: ['standard'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'List of registered background services',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'title', type: 'string'),
                            new OA\Property(property: 'active', type: 'boolean'),
                            new OA\Property(property: 'running', type: 'boolean'),
                            new OA\Property(property: 'execute_interval', type: 'integer'),
                            new OA\Property(property: 'next_run', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function listAll(): Response
    {
        $definitions = $this->registry->list();
        // Return only client-safe fields, omitting function/require_once
        $data = array_map(
            fn($def) => [
                'name' => $def->name,
                'title' => $def->title,
                'active' => $def->active,
                'running' => $def->running,
                'execute_interval' => $def->executeInterval,
                'next_run' => $def->nextRun,
            ],
            $definitions,
        );
        return new JsonResponse($data);
    }

    #[OA\Get(
        path: '/api/background_service/{name}',
        description: 'Retrieves a single background service by name',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'path',
                description: 'The registered name of the background service.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Background service details',
                content: new OA\JsonContent(
                    required: ['name', 'title', 'active', 'running', 'execute_interval', 'next_run'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'active', type: 'boolean'),
                        new OA\Property(property: 'running', type: 'boolean'),
                        new OA\Property(property: 'execute_interval', type: 'integer'),
                        new OA\Property(property: 'next_run', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(
                response: '404',
                description: 'Background service not found',
                content: new OA\JsonContent(
                    required: ['error'],
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Service not found'),
                    ]
                )
            ),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne(string $name): Response
    {
        $definition = $this->registry->get($name);
        if ($definition === null) {
            return new JsonResponse(['error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse([
            'name' => $definition->name,
            'title' => $definition->title,
            'active' => $definition->active,
            'running' => $definition->running,
            'execute_interval' => $definition->executeInterval,
            'next_run' => $definition->nextRun,
        ]);
    }

    #[OA\Post(
        path: '/api/background_service/$run',
        description: 'Advances any background services that are due to run. Cannot force-run a specific service and cannot bypass intervals. Returns HTTP 200 with a per-service results array regardless of individual outcomes — the runner is always correct to invoke, and `not_due` / `already_running` / `skipped` are expected states, not errors.',
        tags: ['standard'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Per-service results (may be empty if no services were due)',
                content: new OA\JsonContent(
                    required: ['results'],
                    properties: [
                        new OA\Property(
                            property: 'results',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                required: ['name', 'status'],
                                properties: [
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(
                                        property: 'status',
                                        type: 'string',
                                        enum: ['executed', 'skipped', 'already_running', 'not_due', 'error'],
                                    ),
                                ],
                            ),
                        ),
                    ],
                    example: [
                        'results' => [
                            ['name' => 'phimail', 'status' => 'executed'],
                            ['name' => 'Email_Service', 'status' => 'not_due'],
                        ],
                    ],
                ),
            ),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]],
    )]
    public function runAllDue(): Response
    {
        $results = $this->runner->run(null, false);
        return new JsonResponse(['results' => $results]);
    }

    /**
     * @param array<mixed> $data
     */
    #[OA\Post(
        path: '/api/background_service/{name}/run',
        description: 'Triggers execution of a background service',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'path',
                description: 'The registered name of the background service.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'force',
                            description: 'Force execution even if the service is not due to run.',
                            type: 'boolean',
                        ),
                    ],
                    example: ['force' => true]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Service executed successfully',
                content: new OA\JsonContent(
                    required: ['service', 'status'],
                    properties: [
                        new OA\Property(property: 'service', type: 'string'),
                        new OA\Property(property: 'status', type: 'string'),
                    ],
                    example: ['service' => 'patient-reminder', 'status' => 'executed']
                )
            ),
            new OA\Response(
                response: '400',
                description: 'Invalid JSON payload',
                content: new OA\JsonContent(
                    required: ['error'],
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                    example: ['error' => 'Invalid JSON payload']
                )
            ),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(
                response: '404',
                description: 'Background service not found',
                content: new OA\JsonContent(
                    required: ['error'],
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                    example: ['error' => 'Service not found']
                )
            ),
            new OA\Response(
                response: '409',
                description: 'Service is already running, not due, or skipped',
                content: new OA\JsonContent(
                    required: ['service', 'status'],
                    properties: [
                        new OA\Property(property: 'service', type: 'string'),
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            enum: ['already_running', 'not_due', 'skipped']
                        ),
                    ],
                    example: ['service' => 'patient-reminder', 'status' => 'already_running']
                )
            ),
            new OA\Response(
                response: '500',
                description: 'Service execution failed',
                content: new OA\JsonContent(
                    required: ['service', 'status'],
                    properties: [
                        new OA\Property(property: 'service', type: 'string'),
                        new OA\Property(property: 'status', type: 'string', enum: ['error']),
                    ],
                    example: ['service' => 'patient-reminder', 'status' => 'error']
                )
            ),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function runService(string $name, array $data = []): Response
    {
        $force = isset($data['force']) && $data['force'] === true;
        $results = $this->runner->run($name, $force);

        if ($results === [] || $results[0]['status'] === 'not_found') {
            return new JsonResponse(['error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }

        $result = $results[0];
        $statusCode = match ($result['status']) {
            'error' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'already_running', 'not_due', 'skipped' => Response::HTTP_CONFLICT,
            default => Response::HTTP_OK,
        };
        return new JsonResponse(['service' => $result['name'], 'status' => $result['status']], $statusCode);
    }
}
