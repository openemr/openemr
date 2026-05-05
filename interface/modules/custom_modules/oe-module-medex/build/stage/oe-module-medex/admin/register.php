<?php
/**
 * MedEx SaaS registration launcher.
 *
 * This replaces the legacy local registration form. Existing links may still
 * point here, but the actual registration flow is hosted by MedEx SaaS.
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
    echo 'Access denied';
    exit;
}

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$stage = (string)($_GET['stage'] ?? '1');
$minimal = !empty($_GET['minimal']);
$localOnly = !empty($_GET['local']);
$webroot = (string)($GLOBALS['webroot'] ?? '');
$fallbackSettingsUrl = $webroot
    . '/interface/modules/custom_modules/oe-module-medex/admin/index.php?site='
    . urlencode($siteId)
    . '&tab=settings&local=1';
$fallbackSplashUrl = $webroot
    . '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?site='
    . urlencode($siteId)
    . '&local=1';

$targetUrl = null;
$connected = false;

try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $connected = $api->isConfigured() && $api->isActive();
    $targetUrl = $connected
        ? $api->getSaaSUrl('dashboard', ['site' => $siteId])
        : $api->getSaaSUrl('register', ['site' => $siteId, 'stage' => $stage]);
} catch (\Throwable $e) {
    $targetUrl = null;
}

if ($targetUrl === null) {
    $targetUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl()
        . '/index.php?route=' . ($connected ? 'account/account' : 'account/register')
        . '&embed=1&site=' . urlencode($siteId)
        . '&stage=' . urlencode($stage);
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
    <title>MedEx Registration</title>
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
    <h1><?php echo text($connected ? xlt('Opening MedEx Dashboard') : xlt('Opening MedEx Registration')); ?></h1>
    <p>
        <?php echo text(xlt('The local MedEx registration form has been retired. Account setup now runs in the hosted MedEx application.')); ?>
    </p>
    <p><?php echo text(xlt('If redirect does not happen automatically, use one of the links below.')); ?></p>
    <div class="actions">
        <a class="btn btn-primary" href="<?php echo attr($targetUrl); ?>">
            <?php echo text($connected ? xlt('Open Dashboard') : xlt('Start Registration')); ?>
        </a>
        <a class="btn btn-secondary" href="<?php echo attr($fallbackSettingsUrl); ?>">
            <?php echo text(xlt('Open Local Settings')); ?>
        </a>
        <a class="btn btn-secondary" href="<?php echo attr($fallbackSplashUrl); ?>">
            <?php echo text(xlt('Open Launcher')); ?>
        </a>
    </div>
    <div class="meta">
        <?php echo text(xlt('Site')); ?>: <?php echo text($siteId); ?> |
        <?php echo text(xlt('Stage')); ?>: <?php echo text($stage); ?>
    </div>
</div>
</body>
</html>
