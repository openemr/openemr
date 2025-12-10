<?php

/**
 * reportTelemetryTask function (used by background service)
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ServiceLocator;
use OpenEMR\Telemetry\TelemetryServiceInterface;

function reportTelemetryTask(): void
{
    // This function is called by the background task manager.
    // It will report usage data to the remote endpoint.
    // The telemetry service will handle the actual reporting.

    $telemetryService = ServiceLocator::get(TelemetryServiceInterface::class);
    $telemetryService->reportUsageData();
}
