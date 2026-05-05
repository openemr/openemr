<?php
/**
 * AJAX endpoint to save a new recall
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . "/../../src/Services/RecallsBoardService.php");

use OpenEMR\Modules\MedEx\Services\RecallsBoardService;
use OpenEMR\Common\Acl\AclMain;

// Set JSON response header
header('Content-Type: application/json');

// Enable error logging for debugging
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is authenticated
if (!isset($_SESSION['authId'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user has permission to add recalls
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Handle both form-data and JSON input
$input = [];
if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $input = $jsonData ?? [];
} else {
    $input = $_POST;
}

// Log received data for debugging
error_log("Recall save attempt - User: " . $_SESSION['authId'] . ", Data: " . print_r($input, true));

// Validate required fields
$required = ['recall_pid', 'recall_date', 'recall_provider', 'recall_facility'];
$missing = [];
foreach ($required as $field) {
    if (empty($input[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    error_log("Missing required fields: " . implode(', ', $missing));
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => "Missing required fields: " . implode(', ', $missing),
        'received_fields' => array_keys($input)
    ]);
    exit;
}

// Prepare data for database
$data = [
    'pid' => $input['recall_pid'],
    'r_eventDate' => $input['recall_date'],
    'r_reason' => $input['recall_reason'] ?? '',
    'r_provider' => $input['recall_provider'],
    'r_facility' => $input['recall_facility']
];

try {
    $service = new RecallsBoardService();
    $recallId = $service->saveRecall($data);
    
    if ($recallId) {
        error_log("Recall created successfully - ID: $recallId");
        echo json_encode([
            'success' => true,
            'recall_id' => $recallId,
            'message' => 'Recall created successfully'
        ]);
    } else {
        error_log("Failed to create recall - saveRecall returned null");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create recall - database insert did not return an ID'
        ]);
    }
} catch (Exception $e) {
    error_log("Error saving recall: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
