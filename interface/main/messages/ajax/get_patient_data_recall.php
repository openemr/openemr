<?php

/**
 * AJAX endpoint to get patient data for recall form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauranuman <magauran@medfetch.com>
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\RecallService;

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check permissions
if (!AclMain::aclCheckCore('patients', 'demo', '', 'read')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$pid = $_POST['pid'] ?? $_GET['pid'] ?? null;

if (!$pid) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Patient ID required']);
    exit;
}

try {
    $recallService = new RecallService();
    $patient = $recallService->getPatientData($pid);
    
    if ($patient) {
        echo json_encode([
            'success' => true,
            'patient' => $patient
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Patient not found'
        ]);
    }
} catch (\Throwable $e) {
    (new SystemLogger())->error("Error fetching patient data", ['message' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
