<?php
/**
 * AJAX endpoint to get patient data for recall form
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . "/../../src/Services/RecallsBoardService.php");

use OpenEMR\Modules\MedEx\Services\RecallsBoardService;

// Check if user is authenticated
if (!isset($_SESSION['authId'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$pid = $_POST['pid'] ?? null;

if (!$pid) {
    echo json_encode(['success' => false, 'message' => 'Patient ID required']);
    exit;
}

$service = new RecallsBoardService();
$patient = $service->getPatientData($pid);

if ($patient) {
    echo json_encode([
        'success' => true,
        'patient' => $patient
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Patient not found'
    ]);
}
