<?php
/**
 * MedEx Module - Disconnect (clear stored credentials and session)
 *
 * Called via POST from the Settings panel disconnect button.
 * Wipes ME_username, ME_api_key, session_token, session_token_expiry, and status
 * so the module returns to the unregistered/unlinked state.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', $session)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

// Remove legacy globals-based credentials
sqlStatement("DELETE FROM globals WHERE gl_name IN ('medex_api_key', 'medex_practice_id', 'medex_enable')");

// Wipe all MedEx prefs (credentials, session, cache, preferences)
sqlStatement("DELETE FROM medex_prefs");

error_log('[MedEx] Disconnected — all credentials and prefs wiped by admin.');

echo json_encode(['success' => true, 'redirect' => 'reconnect.php']);
