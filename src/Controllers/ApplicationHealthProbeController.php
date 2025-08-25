<?php

/**
 * Health Probe Controller for OpenEMR
 *
 * Provides liveness and readiness probe endpoints using Symfony HTTP Foundation.
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEmr Inc.
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers;

use OpenEMR\Controllers\ApplicationHealthProbe\LivenessProbeService;
use OpenEMR\Controllers\ApplicationHealthProbe\ReadinessProbeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationHealthProbeController
{
    private LivenessProbeService $livenessService;
    private ReadinessProbeService $readinessService;

    public function __construct(
        ?LivenessProbeService $livenessService = null,
        ?ReadinessProbeService $readinessService = null
    ) {
        $this->livenessService = $livenessService ?? new LivenessProbeService();
        $this->readinessService = $readinessService ?? new ReadinessProbeService();
    }

    /**
     * Liveness probe endpoint
     *
     * Checks if the PHP application processor is working.
     * This endpoint should always return 200 OK if PHP is functioning.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function liveness(Request $request): JsonResponse
    {
        $result = $this->livenessService->check();

        return new JsonResponse(
            [
                'status' => $result['status'],
                'timestamp' => $result['timestamp']
            ],
            $result['http_code'],
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Readiness probe endpoint
     *
     * Checks if the application is ready to serve traffic by verifying
     * database connectivity and core system availability.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function readiness(Request $request): JsonResponse
    {
        $result = $this->readinessService->check();

        return new JsonResponse(
            [
                'status' => $result['status'],
                'message' => $result['message'],
                'timestamp' => $result['timestamp']
            ],
            $result['http_code'],
            ['Content-Type' => 'application/json']
        );
    }
}
