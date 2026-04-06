<?php
/**
 * MedEx Module - Update A La Carte Recharge Settings
 *
 * Proxies recharge setting updates through OpenEMR so we always use a fresh
 * MedEx session token server-side (avoids stale token failures in browser JS).
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();

    $payload = [
        'recharge_on' => isset($_POST['recharge_on']) ? (int)(bool)$_POST['recharge_on'] : 0,
        'recharge_point' => isset($_POST['recharge_point']) ? round((float)$_POST['recharge_point'], 2) : 0,
        'recharge_amt' => isset($_POST['recharge_amt']) ? (int)$_POST['recharge_amt'] : 0
    ];

    $result = $api->makeRequest('index.php?route=api/oemr/updaterecharge', $payload, 'POST');

    if (!is_array($result)) {
        throw new \RuntimeException('Invalid response from MedEx API');
    }

    echo json_encode($result);
} catch (\Throwable $e) {
    error_log('[MedEx] update_recharge proxy error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save recharge settings',
        'message' => $e->getMessage()
    ]);
}

