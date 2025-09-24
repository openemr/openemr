<?php

/**
 * Service configuration for ServiceLocator
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Telemetry\TelemetryServiceInterface;
use OpenEMR\Telemetry\TelemetryService;

return [
    'services' => [
        // Pre-configured service instances
    ],
    'factories' => [
        // Service factories for dependency injection
        TelemetryServiceInterface::class => fn($container) => new TelemetryService(),
    ],
    'invokables' => [
        // Simple services without dependencies
    ],
    'aliases' => [
        // Service aliases for convenience
        'telemetry' => TelemetryServiceInterface::class,
    ]
];
