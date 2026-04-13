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

    public function __construct()
    {
        $this->registry = new BackgroundServiceRegistry();
        $this->runner = new BackgroundServiceRunner();
    }

    #[OA\Get(
        path: '/api/background_service',
        description: 'Retrieves all registered background services',
        tags: ['standard'],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
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
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
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
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
            new OA\Response(response: '409', description: 'Service is already running, not due, or skipped'),
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
