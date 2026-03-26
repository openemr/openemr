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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\MedEx\MedExConfig;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    QueryUtils::sqlStatementThrowException("DELETE FROM globals WHERE gl_name IN ('medex_api_key', 'medex_practice_id', 'medex_bad_actor_until', 'medex_bad_actor_message')");
    QueryUtils::sqlStatementThrowException("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_enable', 0, '0')");
    QueryUtils::sqlStatementThrowException("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_server_url', 0, ?)", [MedExConfig::publicBaseUrl()]);
    QueryUtils::sqlStatementThrowException("DELETE FROM medex_prefs");

    $prefsCount = (int)(QueryUtils::querySingleRow("SELECT COUNT(*) AS c FROM medex_prefs", [])['c'] ?? 0);
    $apiKeyRow = QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_api_key' LIMIT 1", []);
    $practiceIdRow = QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_practice_id' LIMIT 1", []);
    $isClean = ($prefsCount === 0) && empty($apiKeyRow['gl_value']) && empty($practiceIdRow['gl_value']);

    if (!$isClean) {
        echo json_encode([
            'success' => false,
            'error' => 'Disconnect incomplete. Credentials still present.',
            'details' => [
                'prefs_count' => $prefsCount,
                'has_api_key' => !empty($apiKeyRow['gl_value']),
                'has_practice_id' => !empty($practiceIdRow['gl_value']),
            ]
        ]);
        exit;
    }

    error_log('[MedEx] Disconnected — all credentials and prefs wiped by admin.');
    echo json_encode(['success' => true, 'redirect' => 'reconnect.php', 'details' => ['prefs_count' => 0]]);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Disconnect failed: ' . $e->getMessage()]);
}
