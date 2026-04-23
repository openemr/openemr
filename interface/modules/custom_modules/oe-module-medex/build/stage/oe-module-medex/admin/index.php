<?php
/**
 * MedEx Admin entry point.
 *
 * Production module behavior is SaaS-first. This local page is only a thin,
 * same-origin shell so OpenEMR can label the MedEx tab correctly while the
 * actual dashboard runs in the hosted MedEx application inside an iframe.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

require_once(__DIR__ . '/../src/MedExAPI.php');

function medexResolveOpenEmrBaseUrlAdmin(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }

    $basePath = '';
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $interfacePos = strpos($scriptName, '/interface/');
    if ($interfacePos !== false) {
        $basePath = rtrim(substr($scriptName, 0, $interfacePos), '/');
    }

    if ($basePath === '') {
        $webRoot = trim((string)($GLOBALS['webroot'] ?? ''), '/');
        if ($webRoot !== '') {
            $basePath = '/' . $webRoot;
        }
    }

    if ($basePath === '') {
        $siteAddr = trim((string)($GLOBALS['site_addr_oath'] ?? ''));
        if ($siteAddr !== '') {
            $siteParts = parse_url($siteAddr);
            $sitePath = trim((string)($siteParts['path'] ?? ''), '/');
            if ($sitePath !== '') {
                $basePath = '/' . $sitePath;
            }
        }
    }

    return $scheme . '://' . $host . $basePath;
}

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$tab = trim((string)($_GET['tab'] ?? 'overview'));
if ($tab === '') {
    $tab = 'overview';
}
$reconnect = !empty($_GET['reconnect']);
$email = trim((string)($_GET['email'] ?? ''));
$webroot = (string)($GLOBALS['webroot'] ?? '');

$api = null;
$isConfigured = false;
try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $isConfigured = $api->isConfigured();
} catch (\Throwable $e) {
    error_log('[MedEx Admin] Failed to initialize MedExAPI: ' . $e->getMessage());
}

if (!$isConfigured || !$api) {
    $splashUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?site=' . urlencode($siteId);
    header('Location: ' . $splashUrl);
    exit;
}

$cloudUrl = '';
$errorMessage = '';
try {
    $loginData = $api->login(false);
    $sessionToken = trim((string)($loginData['token'] ?? ''));
    $practiceId = trim((string)($loginData['practice_id'] ?? ($loginData['practice']['P_PID'] ?? '')));
    if ($practiceId === '') {
        $pref = sqlQuery("SELECT MedEx_id FROM medex_prefs WHERE MedEx_id IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
        $practiceId = trim((string)($pref['MedEx_id'] ?? ''));
    }

    if ($sessionToken !== '' && $practiceId !== '') {
        $openEmrBaseUrl = medexResolveOpenEmrBaseUrlAdmin();
        $callbackTokenRow = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_callback_token' LIMIT 1");
        $callbackToken = trim((string)($callbackTokenRow['gl_value'] ?? ''));
        $payload = [
            'practice_id' => $practiceId,
            'session_token' => $sessionToken,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16)),
            'source' => 'openemr_dashboard',
            'openemr_base_url' => $openEmrBaseUrl,
            'site' => $siteId,
        ];
        if ($callbackToken !== '') {
            $payload['callback_token'] = $callbackToken;
        }
        if ($reconnect) {
            $payload['reconnect'] = 1;
        }
        if ($email !== '') {
            $payload['email'] = $email;
        }
        $encodedPayload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedPayload, $callbackToken);

        $cloudUrl = 'https://api.hipaabank.net/cart/upload/dashboard_sso.php'
            . '?site=' . urlencode($siteId)
            . '&tab=' . urlencode($tab)
            . '&sso_token=' . urlencode($encodedPayload)
            . '&sso_sig=' . urlencode($signature);
    }

    $errorMessage = xlt('Unable to create a valid MedEx dashboard session.');
} catch (\Throwable $e) {
    $errorMessage = $e->getMessage();
    error_log('[MedEx Admin] SaaS dashboard handoff failed: ' . $e->getMessage());
}

$reconnectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/reconnect.php?site=' . urlencode($siteId);
if ($email !== '') {
    $reconnectUrl .= '&email=' . urlencode($email);
}
$onboardingUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?site=' . urlencode($siteId);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MedEx Dashboard</title>
    <style>
        html, body { height: 100%; }
        body { margin: 0; background: #f5f8fc; color: #0f172a; font-family: "Segoe UI", Tahoma, Arial, sans-serif; }
        .title { display: none; }
        .app-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .frame-wrap { flex: 1 1 auto; min-height: 0; background: #eaf1f8; }
        .frame-wrap iframe { display: block; width: 100%; height: 100%; min-height: 100vh; border: 0; background: #fff; }
        .shell { max-width: 760px; margin: 72px auto; padding: 28px; background: #fff; border: 1px solid #dbe5ee; border-radius: 14px; box-shadow: 0 14px 36px rgba(15, 75, 143, 0.08); }
        h1 { margin: 0 0 10px; font-size: 30px; color: #0f4b8f; }
        p { margin: 0 0 14px; line-height: 1.55; color: #334155; }
        .error { margin: 16px 0; padding: 14px 16px; border-radius: 10px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 18px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 11px 16px; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .btn-primary { background: #0f4b8f; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body>
<div class="title"><?php echo xlt('MedEx Dashboard'); ?></div>
<?php if ($cloudUrl !== ''): ?>
<div class="app-shell">
    <div class="frame-wrap">
        <iframe
            src="<?php echo attr($cloudUrl); ?>"
            title="<?php echo attr(xlt('MedEx Dashboard')); ?>"
            referrerpolicy="strict-origin-when-cross-origin"
        ></iframe>
    </div>
</div>
<?php else: ?>
<div class="shell">
    <h1><?php echo xlt('MedEx Admin'); ?></h1>
    <p><?php echo xlt('The legacy local dashboard has been removed from this module.'); ?></p>
    <div class="error"><?php echo text($errorMessage !== '' ? $errorMessage : xlt('Unable to open the MedEx SaaS dashboard.')); ?></div>
    <div class="actions">
        <a class="btn btn-primary" href="<?php echo attr($reconnectUrl); ?>"><?php echo xlt('Reconnect MedEx'); ?></a>
        <a class="btn btn-secondary" href="<?php echo attr($onboardingUrl); ?>"><?php echo xlt('Open Onboarding'); ?></a>
    </div>
</div>
<?php endif; ?>
</body>
</html>
