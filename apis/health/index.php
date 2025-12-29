<?php

/**
 * Health Check Entry Point
 *
 * Provides liveness and readiness probe endpoints for Kubernetes and Docker.
 * These endpoints are restricted to localhost access via .htaccess.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Load autoloader
require_once __DIR__ . "/../../vendor/autoload.php";

use OpenEMR\Health\HealthChecker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// Always return JSON
header('Content-Type: application/json');

try {
    // Parse the request path to determine which probe was requested
    $request = Request::createFromGlobals();
    $pathInfo = $request->getPathInfo();

    // Handle /livez - minimal check, just verify PHP is running
    if (str_ends_with($pathInfo, '/livez') || $pathInfo === '/livez') {
        echo json_encode(['status' => 'alive']);
        exit(0);
    }

    // Handle /readyz - full health check with component status
    if (str_ends_with($pathInfo, '/readyz') || $pathInfo === '/readyz') {
        // Set up site context - default to "default" site
        $_GET['site'] = 'default';

        // Bootstrap OpenEMR globals (this sets up database connection)
        require_once __DIR__ . "/../../interface/globals.php";

        // Run health checks
        $checker = new HealthChecker();
        $results = $checker->getResultsArray();

        echo json_encode($results);
        exit(0);
    }

    // Unknown endpoint - return 404
    http_response_code(404);
    echo json_encode([
        'error' => 'Not found',
        'message' => 'Valid endpoints are /livez and /readyz'
    ]);
} catch (\Throwable $e) {
    // Even on error, return 200 with error details in body
    // This ensures the probe doesn't fail just because of an exception
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
