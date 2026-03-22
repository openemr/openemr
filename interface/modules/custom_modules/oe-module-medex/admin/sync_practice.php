<?php

/**
 * MedEx Module - Sync Practice Data
 *
 * Handles manual sync of practice data to MedEx server
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Load MedEx API and Services
require_once(__DIR__ . '/../src/MedExAPI.php');
require_once(__DIR__ . '/../src/Services/PracticeService.php');

try {
    // Create API instance
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();

    // Check if enabled
    if (!$api->isEnabled()) {
        echo json_encode([
            'success' => false,
            'error' => 'MedEx is disabled. Enable it in Global Configuration.'
        ]);
        exit;
    }

    // Check if configured
    if (!$api->isConfigured()) {
        echo json_encode([
            'success' => false,
            'error' => 'MedEx is not configured. Please register first.'
        ]);
        exit;
    }

    // Create practice service
    $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);

    // Perform sync
    $result = $practiceService->performInitialSync();

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Sync error: ' . $e->getMessage()
    ]);
}
