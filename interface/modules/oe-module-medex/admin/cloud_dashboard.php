<?php
/**
 * Local wrapper page for cloud-hosted MedEx dashboard.
 * Keeps OpenEMR tab title stable ("MedEx Admin") while embedding api.hipaabank.net content.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo "<html><body>Access denied</body></html>";
    exit;
}

$site = $_GET['site'] ?? 'default';
$innerUrl = ($GLOBALS['webroot'] ?? '')
    . '/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=' . urlencode((string)$site) . '&cloud_only=1';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MedEx Admin</title>
    <style>
        html, body { margin: 0; height: 100%; overflow: hidden; background: #f5f8fc; }
        #medex-cloud-frame { width: 100%; height: 100%; border: 0; display: block; background: #fff; }
    </style>
</head>
<body>
<iframe
    id="medex-cloud-frame"
    src="<?php echo attr($innerUrl); ?>"
    title="MedEx Admin"
    allow="payment *; clipboard-read *; clipboard-write *"
></iframe>
</body>
</html>
