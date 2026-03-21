<?php

/**
 * AJAX endpoint to save a new recall
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@medfetch.com>
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\RecallService;

header('Content-Type: application/json');

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_REQUEST['csrf_token_form'] ?? '', session: $session)) {
    CsrfUtils::csrfNotVerified();
}

// Check authentication
if (!$session->get('authUserID')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => xl('Unauthorized')]);
    exit;
}

// Check permissions
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => xl('Permission denied')]);
    exit;
}

// Handle both form-data and JSON input
$input = [];
$rawContentType = $_SERVER['CONTENT_TYPE'] ?? '';
$contentType = is_string($rawContentType) ? $rawContentType : '';
if ($contentType !== '' && str_contains($contentType, 'application/json')) {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $input = $jsonData ?? [];
} else {
    $input = $_POST;
}

// Log received data
(new SystemLogger())->debug("Recall save attempt", ['user' => $session->get('authUserID'), 'data' => $input]);

// Validate required fields
$required = ['recall_pid', 'recall_date', 'recall_provider', 'recall_facility'];
$missing = [];
foreach ($required as $field) {
    if (empty($input[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    (new SystemLogger())->warning("Missing required fields", ['fields' => $missing]);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => xl('Missing required fields') . ": " . implode(', ', $missing),
    ]);
    exit;
}

// Prepare data for service
$data = [
    'pid' => $input['recall_pid'],
    'r_eventDate' => $input['recall_date'],
    'r_reason' => $input['recall_reason'] ?? '',
    'r_provider' => $input['recall_provider'],
    'r_facility' => $input['recall_facility']
];

try {
    $recallService = new RecallService();
    $recallId = $recallService->createRecall($data);

    if ($recallId) {
        (new SystemLogger())->info("Recall created successfully", ['id' => $recallId]);
        echo json_encode([
            'success' => true,
            'recall_id' => $recallId,
            'message' => xl('Recall created successfully')
        ]);
    } else {
        (new SystemLogger())->error("Failed to create recall - createRecall returned false");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => xl('Failed to create recall')
        ]);
    }
} catch (\Throwable $e) {
    (new SystemLogger())->error("Error saving recall", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => xl('An error occurred while saving the recall')
    ]);
}
