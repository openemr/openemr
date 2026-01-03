<?php

/**
 * track_portal_events.php
 *
 * @package        OpenEMR
 * @link           https://www.open-emr.org
 * @author         Jerry Padgett <sjpadgett@gmail.com>
 * @copyright      Copyright (c) 2025 <sjpadgett@gmail.com>
 * @license        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
SessionUtil::portalSessionStart();

$sessionAllowWrite = true;
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . '/../../interface/globals.php');
} else {
    SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . '/../../interface/globals.php');
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = 'index.php';
        header('Location: ' . $landingpage);
        exit;
    }
}


use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
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
    $input_json = file_get_contents('php://input');
    $data = json_decode($input_json, true);

    // Verify CSRF token.
    if (!isset($data["csrf_token_form"]) || !CsrfUtils::verifyCsrfToken($data["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $telemetryRepo = new TelemetryRepository();
    $versionService = new VersionService();
    $logger = new SystemLogger();
    $telemetryService = new TelemetryService($telemetryRepo, $versionService, $logger);

    $action = $data['action'] ?? '';
    switch ($action) {
        case 'portalCardClickData':
            $telemetryService->reportClickEvent($data, true);
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "Invalid action"]);
            break;
    }
}

handleRequest();
