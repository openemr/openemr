<?php
/**
 * MedEx Module - Reset Connection
 *
 * Completely removes all MedEx credentials and settings
 * Allows starting fresh as a new customer
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', $session)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    // Delete MedEx credentials from globals (but keep server URL with default)
    sqlStatement("DELETE FROM globals WHERE gl_name IN ('medex_api_key', 'medex_practice_id', 'medex_enable')");

    // Ensure server URL is set to default
    sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_server_url', 0, 'http://localhost/cart/upload')");

    // Clear all medex_prefs data
    sqlStatement("DELETE FROM medex_prefs");

    // Background services removed; no action required to reset background service.

    echo json_encode([
        'success' => true,
        'message' => 'All MedEx credentials and settings have been cleared. You can now register as a new customer.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Reset failed: ' . $e->getMessage()
    ]);
}
