<?php
/**
 * Legacy onboarding payment endpoint compatibility stub.
 *
 * Local MedEx payment processing is retired. Checkout now continues in the
 * hosted MedEx application.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExConfig.php');
require_once(__DIR__ . '/../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$redirectUrl = null;

try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $redirectUrl = $api->getSaaSUrl('billing', ['site' => $siteId]);
} catch (\Throwable $e) {
    $redirectUrl = null;
}

if ($redirectUrl === null) {
    $redirectUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl()
        . '/index.php?route=account/subscription&embed=1&site='
        . urlencode($siteId);
}

echo json_encode([
    'success' => false,
    'retired' => true,
    'error' => xlt('Local MedEx payment processing is retired. Continue in the hosted MedEx application.'),
    'redirect_url' => $redirectUrl,
]);
exit;
