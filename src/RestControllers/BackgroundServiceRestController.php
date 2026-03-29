<?php

/**
 * REST controller for background service listing and execution.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://www.opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers;

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
            'locked', 'skipped' => Response::HTTP_CONFLICT,
            default => Response::HTTP_OK,
        };
        return new JsonResponse(['service' => $result['name'], 'status' => $result['status']], $statusCode);
    }
}
