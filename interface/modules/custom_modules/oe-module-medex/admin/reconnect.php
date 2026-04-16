<?php
/**
 * MedEx local reconnect launcher.
 *
 * Reconnect should stay inside the OpenEMR module dashboard rather than
 * redirecting to the hosted legacy MedEx site.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExAPI.php');
use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$requestedEmail = trim((string)($_GET['email'] ?? ''));
$prefs = sqlQuery("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1");
$existingEmail = $requestedEmail !== '' ? $requestedEmail : (string)($prefs['ME_username'] ?? '');
$webroot = (string)($GLOBALS['webroot'] ?? '');
$hasCredentials = false;

try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $hasCredentials = $api->isConfigured();
} catch (\Throwable $e) {
    $hasCredentials = false;
}

$targetPath = $hasCredentials
    ? '/interface/modules/custom_modules/oe-module-medex/admin/index.php'
    : '/interface/modules/custom_modules/oe-module-medex/admin/splash.php';
$targetUrl = $webroot . $targetPath . '?site=' . urlencode($siteId) . '&reconnect=1';
if ($existingEmail !== '') {
    $targetUrl .= '&email=' . urlencode($existingEmail);
}

if ($targetUrl !== '') {
    header('Location: ' . $targetUrl);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="1;url=<?php echo attr($targetUrl); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MedEx Reconnect</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background: #f6f9fc;
            color: #0f172a;
        }
        .wrap {
            max-width: 680px;
            margin: 72px auto;
            padding: 28px;
            background: #fff;
            border: 1px solid #dbe5ee;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(15, 75, 143, 0.08);
        }
        h1 {
            margin: 0 0 10px;
            font-size: 28px;
            color: #0f4b8f;
        }
        p {
            margin: 0 0 14px;
            line-height: 1.5;
            color: #334155;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border: 1px solid #cbd5e1;
        }
        .btn-primary {
            background: #0f4b8f;
            border-color: #0f4b8f;
            color: #fff;
        }
        .btn-secondary {
            background: #fff;
            color: #0f4b8f;
        }
        .meta {
            margin-top: 12px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1><?php echo xlt('Opening MedEx Reconnect'); ?></h1>
    <p><?php echo xlt('Reconnect is routed through the MedEx module dashboard entry point, not the local legacy dashboard.'); ?></p>
    <?php if ($existingEmail !== ''): ?>
        <p><strong><?php echo xlt('Account Email'); ?>:</strong> <?php echo text($existingEmail); ?></p>
    <?php endif; ?>
    <p><?php echo xlt('If redirect does not happen automatically, use the link below.'); ?></p>
    <div class="actions">
        <a class="btn btn-primary" href="<?php echo attr($targetUrl); ?>"><?php echo xlt('Open MedEx Dashboard'); ?></a>
    </div>
    <p class="meta"><?php echo xlt('This launcher keeps reconnect inside the local MedEx module.'); ?></p>
</div>
</body>
</html>
