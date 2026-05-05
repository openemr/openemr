<?php
/**
 * MedEx local launcher for unconfigured or not-yet-connected accounts.
 *
 * This page must stay inside the module flow. Configured accounts may hand off
 * into the hosted dashboard, but unconfigured accounts must open the local
 * onboarding wizard rather than any hosted legacy registration route.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExConfig.php');

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

require_once(__DIR__ . '/../src/MedExAPI.php');

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$minimal = !empty($_GET['minimal']);
$localOnly = !empty($_GET['local']);
$webroot = (string)($GLOBALS['webroot'] ?? '');
$fallbackStatusUrl = $webroot
    . '/interface/modules/custom_modules/oe-module-medex/public/status.php?site='
    . urlencode($siteId);
$localOnboardingUrl = $webroot
    . '/interface/modules/custom_modules/oe-module-medex/admin/onboarding.php?step=1&force_onboarding=1&site='
    . urlencode($siteId);

$targetUrl = null;
$connected = false;

try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $connected = $api->isConfigured() && $api->isActive();

    if ($connected) {
        $targetUrl = $api->getSaaSUrl('dashboard', ['site' => $siteId]);
    }
} catch (\Throwable $e) {
    $targetUrl = null;
}

if ($targetUrl === null) {
    $targetUrl = $localOnboardingUrl;
}

if (!$localOnly && $targetUrl !== '') {
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
    <title>MedEx</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background: #f6f9fc;
            color: #0f172a;
        }
        .wrap {
            max-width: 680px;
            margin: <?php echo $minimal ? '24px' : '72px auto'; ?>;
            padding: <?php echo $minimal ? '18px' : '28px'; ?>;
            background: #fff;
            border: 1px solid #dbe5ee;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(15, 75, 143, 0.08);
        }
        h1 {
            margin: 0 0 10px;
            font-size: <?php echo $minimal ? '20px' : '28px'; ?>;
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
    <h1><?php echo text($connected ? xlt('Opening MedEx Dashboard') : xlt('Opening MedEx Onboarding')); ?></h1>
    <p>
        <?php echo text($connected
            ? xlt('This local page is now a thin launcher. MedEx account management runs in the hosted MedEx application.')
            : xlt('This local page is now a thin launcher. New MedEx onboarding starts inside this OpenEMR module.')); ?>
    </p>
    <p><?php echo text(xlt('If redirect does not happen automatically, use one of the links below.')); ?></p>
    <div class="actions">
        <a class="btn btn-primary" href="<?php echo attr($targetUrl); ?>">
            <?php echo text($connected ? xlt('Open Dashboard') : xlt('Open Onboarding')); ?>
        </a>
        <a class="btn btn-secondary" href="<?php echo attr($fallbackStatusUrl); ?>">
            <?php echo text(xlt('Open Status')); ?>
        </a>
    </div>
    <div class="meta">
        <?php echo text(xlt('Site')); ?>: <?php echo text($siteId); ?>
    </div>
</div>
</body>
</html>
