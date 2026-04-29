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
    foreach (['medex_callback_base_url', 'medex_preview_tunnel_public_base_url'] as $globalName) {
        $candidate = rtrim(trim((string)($GLOBALS[$globalName] ?? '')), '/');
        if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_URL)) {
            return $candidate;
        }
    }

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
            if (!empty($siteParts['host'])) {
                $host = trim((string)$siteParts['host']);
            }
            $sitePath = trim((string)($siteParts['path'] ?? ''), '/');
            if ($sitePath !== '') {
                $basePath = '/' . $sitePath;
            }
        }
    }

    if ($host !== '' && !in_array(strtolower($host), ['localhost', '127.0.0.1'], true)) {
        $scheme = 'https';
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
    $openemrUserFirstName = '';
    $openemrUserDisplayName = '';
    $openemrUserId = (int)($_SESSION['authUserID'] ?? 0);
    if ($openemrUserId > 0) {
        $userRow = sqlQuery("SELECT fname, lname, username FROM users WHERE id = ?", [$openemrUserId]);
        if (is_array($userRow)) {
            $openemrUserFirstName = trim((string)($userRow['fname'] ?? ''));
            $openemrUserDisplayName = trim(
                preg_replace('/\s+/', ' ', (string)(($userRow['fname'] ?? '') . ' ' . ($userRow['lname'] ?? '')))
            );
            if ($openemrUserDisplayName === '') {
                $openemrUserDisplayName = trim((string)($userRow['username'] ?? ''));
            }
        }
    }
    if ($openemrUserDisplayName === '') {
        $openemrUserDisplayName = trim((string)($_SESSION['authUser'] ?? ''));
    }
    if ($openemrUserFirstName === '' && $openemrUserDisplayName !== '') {
        $parts = preg_split('/\s+/', $openemrUserDisplayName);
        $openemrUserFirstName = trim((string)($parts[0] ?? ''));
    }

    $loginData = $api->login(false);
    $sessionToken = trim((string)($loginData['token'] ?? ''));
    $practiceId = trim((string)($loginData['practice_id'] ?? ($loginData['practice']['P_PID'] ?? '')));
    if ($practiceId === '') {
        $pref = sqlQuery("SELECT MedEx_id FROM medex_prefs WHERE MedEx_id IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
        $practiceId = trim((string)($pref['MedEx_id'] ?? ''));
    }

    if ($sessionToken !== '' && $practiceId !== '') {
        $openEmrBaseUrl = medexResolveOpenEmrBaseUrlAdmin();
        $callbackToken = trim((string)($loginData['callback_token'] ?? ''));
        if ($callbackToken === '') {
            $callbackUrl = trim((string)($loginData['callback_url'] ?? ''));
            if ($callbackUrl !== '' && preg_match('/[?&]token=([^&]+)/', $callbackUrl, $match)) {
                $callbackToken = trim((string)rawurldecode($match[1]));
            }
        }
        if ($callbackToken === '') {
            $loginData = $api->login(true);
            $sessionToken = trim((string)($loginData['token'] ?? $sessionToken));
            $practiceId = trim((string)($loginData['practice_id'] ?? $practiceId));
            $callbackToken = trim((string)($loginData['callback_token'] ?? ''));
            if ($callbackToken === '') {
                $callbackUrl = trim((string)($loginData['callback_url'] ?? ''));
                if ($callbackUrl !== '' && preg_match('/[?&]token=([^&]+)/', $callbackUrl, $match)) {
                    $callbackToken = trim((string)rawurldecode($match[1]));
                }
            }
        }
        if ($callbackToken === '') {
            throw new \RuntimeException('Unable to load the current MedEx callback token for dashboard SSO.');
        }

        $payload = [
            'practice_id' => $practiceId,
            'session_token' => $sessionToken,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16)),
            'source' => 'openemr_dashboard',
            'openemr_base_url' => $openEmrBaseUrl,
            'site' => $siteId,
        ];
        if ($reconnect) {
            $payload['reconnect'] = 1;
        }
        if ($email !== '') {
            $payload['email'] = $email;
        }
        if ($openemrUserFirstName !== '') {
            $payload['openemr_user_firstname'] = $openemrUserFirstName;
        }
        if ($openemrUserDisplayName !== '') {
            $payload['openemr_user_display_name'] = $openemrUserDisplayName;
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
$tabsHomeUrl = $webroot . '/interface/main/tabs/main.php';
$mainScreenUrl = $webroot . '/interface/main/main_screen.php';
$cloudOrigin = '';
if ($cloudUrl !== '') {
    $cloudParts = parse_url($cloudUrl);
    if (!empty($cloudParts['scheme']) && !empty($cloudParts['host'])) {
        $cloudOrigin = $cloudParts['scheme'] . '://' . $cloudParts['host'] . (!empty($cloudParts['port']) ? ':' . $cloudParts['port'] : '');
    }
}
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
            id="medexDashboardFrame"
            src="<?php echo attr($cloudUrl); ?>"
            title="<?php echo attr(xlt('MedEx Dashboard')); ?>"
            allow="microphone *; payment *; clipboard-read *; clipboard-write *"
            referrerpolicy="strict-origin-when-cross-origin"
        ></iframe>
    </div>
</div>
<script>
(function() {
    const allowedOrigin = <?php echo json_encode($cloudOrigin, JSON_UNESCAPED_SLASHES); ?>;
    const tabsHomeUrl = <?php echo json_encode($tabsHomeUrl, JSON_UNESCAPED_SLASHES); ?>;
    const mainScreenUrl = <?php echo json_encode($mainScreenUrl, JSON_UNESCAPED_SLASHES); ?>;
    const frame = document.getElementById('medexDashboardFrame');

    function fallbackHomeUrl() {
        return tabsHomeUrl || mainScreenUrl || '/';
    }

    function closeEmbeddedDashboard() {
        const topWin = window.top || window;
        let fallbackTimer = null;

        try {
            if (topWin.history && topWin.history.length > 1) {
                const beforeHref = topWin.location ? topWin.location.href : '';
                fallbackTimer = window.setTimeout(function() {
                    try {
                        const afterHref = topWin.location ? topWin.location.href : '';
                        if (afterHref === beforeHref || afterHref.indexOf('/interface/modules/custom_modules/oe-module-medex/admin/index.php') !== -1) {
                            topWin.location.href = fallbackHomeUrl();
                        }
                    } catch (e) {
                        topWin.location.href = fallbackHomeUrl();
                    }
                }, 350);
                topWin.history.back();
                return;
            }
        } catch (e) {
            if (fallbackTimer) {
                window.clearTimeout(fallbackTimer);
            }
        }

        try {
            topWin.location.href = fallbackHomeUrl();
            return;
        } catch (e) {}

        window.location.href = fallbackHomeUrl();
    }

    function openPopupWindow(targetUrl, fullscreenMode) {
        const width = Math.max(1280, Math.floor((window.screen && window.screen.availWidth) || 1440));
        const height = Math.max(820, Math.floor((window.screen && window.screen.availHeight) || 960));
        const left = Math.max(0, Math.floor((((window.screen && window.screen.availWidth) || width) - width) / 2));
        const top = Math.max(0, Math.floor((((window.screen && window.screen.availHeight) || height) - height) / 2));
        const popup = window.open(
            targetUrl,
            fullscreenMode ? 'MedExDashboardFullscreen' : 'MedExDashboardWindow',
            [
                'popup=yes',
                'resizable=yes',
                'scrollbars=yes',
                'toolbar=no',
                'menubar=no',
                'location=yes',
                'status=no',
                'width=' + width,
                'height=' + height,
                'left=' + left,
                'top=' + top
            ].join(',')
        );
        if (popup && typeof popup.focus === 'function') {
            popup.focus();
        }
        return popup;
    }

    function normalizeTargetUrl(rawUrl) {
        const candidate = rawUrl || (frame ? frame.getAttribute('src') : '');
        if (!candidate) {
            return '';
        }
        try {
            const url = new URL(candidate, window.location.href);
            if (allowedOrigin && url.origin !== allowedOrigin) {
                return '';
            }
            return url.toString();
        } catch (e) {
            return '';
        }
    }

    window.addEventListener('message', function(event) {
        if (allowedOrigin && event.origin !== allowedOrigin) {
            return;
        }
        const data = event.data || {};
        if (data.source !== 'medex-dashboard-shell' || data.type !== 'medex-window-control') {
            return;
        }

        const action = String(data.action || '');
        const targetUrl = normalizeTargetUrl(String(data.url || ''));

        if (action === 'close') {
            closeEmbeddedDashboard();
            return;
        }

        if (action === 'popout') {
            const popup = openPopupWindow(targetUrl, false);
            if (popup) {
                closeEmbeddedDashboard();
            } else {
                window.alert('Popup blocked. Allow popups for this site to open MedEx in its own window.');
            }
            return;
        }

        if (action === 'fullscreen' && targetUrl !== '') {
            const popup = openPopupWindow(targetUrl, true);
            if (popup) {
                closeEmbeddedDashboard();
            } else {
                window.alert('Popup blocked. Allow popups for this site to open MedEx in a fullscreen window.');
            }
        }
    });
})();
</script>
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
