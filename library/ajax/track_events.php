<?php

/**
 * track_events.php
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\VersionService;
use OpenEMR\Telemetry\TelemetryRepository;
use OpenEMR\Telemetry\TelemetryService;

header("Content-Type: application/json");

/**
 * Main request handler that reads input, verifies the CSRF token, and delegates
 * to the appropriate telemetry service method.
 */
function handleRequest(): void
{
    // Read JSON payload.
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    // Verify CSRF token.
    if (!isset($data["csrf_token_form"]) || !CsrfUtils::verifyCsrfToken($data["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $telemetryRepo = new TelemetryRepository();
    $versionService = new VersionService();
    $telemetryService = new TelemetryService($telemetryRepo, $versionService);

    $action = $data['action'] ?? '';
    switch ($action) {
        case 'reportMenuClickData':
            $telemetryService->reportClickEvent($data);
            break;
        case 'reportUsageData':
            $result = $telemetryService->reportUsageData();
            echo json_encode($result);
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "Invalid action"]);
            break;
    }
}

handleRequest();
