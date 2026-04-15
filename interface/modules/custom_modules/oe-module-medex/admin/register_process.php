<?php
/**
 * Legacy registration endpoint compatibility stub.
 *
 * Local MedEx registration is retired. This endpoint is kept only so stale
 * callers get a deterministic response pointing them to the hosted flow.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExConfig.php');
require_once(__DIR__ . '/../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Access denied',
    ]);
    exit;
}

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$redirectUrl = null;

try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $redirectUrl = $api->getSaaSUrl('register', ['site' => $siteId]);
} catch (\Throwable $e) {
    $redirectUrl = null;
}

if ($redirectUrl === null) {
    $redirectUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl()
        . '/index.php?route=account/register&embed=1&site='
        . urlencode($siteId);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'retired' => true,
        'error' => xlt('Local MedEx registration is retired. Continue in the hosted MedEx application.'),
        'redirect_url' => $redirectUrl,
    ]);
    exit;
}

header('Location: ' . $redirectUrl);
exit;
