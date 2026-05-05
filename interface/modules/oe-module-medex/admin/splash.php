<?php
/**
 * MedEx Module - Professional Splash Page
 *
 * This is the first page potential customers see when the module is
 * installed but not yet configured.
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "Access denied";
    exit;
}

// Check if minimal mode (for gear icon modal - hide marketing content)
$isMinimal = isset($_GET['minimal']) && $_GET['minimal'] == '1';

// Check if there's an existing email (for reconnect vs new registration)
$existingEmail = null;
$prefs = sqlQuery("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1");
if (!empty($prefs['ME_username'])) {
    $existingEmail = $prefs['ME_username'];
}

// Legacy/live connection guard:
// if credentials already exist, route to dashboard instead of forcing reconnect.
$hasConnectedAccount = false;
$hasActiveSubscriptions = false;
try {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    $medexApi = new \OpenEMR\Modules\MedEx\MedExAPI();
    $hasConnectedAccount = $medexApi->isConfigured();
    if ($hasConnectedAccount && $medexApi->isActive()) {
        $enabledServices = $medexApi->getEnabledServices();
        $hasActiveSubscriptions = !empty($enabledServices);
    }
} catch (\Throwable $e) {
    $hasConnectedAccount = false;
    $hasActiveSubscriptions = false;
}

// Live readiness checks shown before onboarding proceeds.
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$proto = (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
$httpsRaw = (string)($_SERVER['HTTPS'] ?? '');
$isHttps = ($proto === 'https') || (!empty($httpsRaw) && strtolower($httpsRaw) !== 'off');
$isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
$isPrivateIp = $isIp && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
$looksPublicHost = !empty($host) && stripos($host, 'localhost') === false && stripos($host, '.local') === false && !$isPrivateIp;
$urlReady = $isHttps && $looksPublicHost;
$detectedUrl = ($isHttps ? 'https://' : 'http://') . $host;

$providerCount = (int)(sqlQuery("SELECT COUNT(*) AS c FROM users WHERE authorized = 1 AND active = 1")['c'] ?? 0);
$facilityCount = (int)(sqlQuery("SELECT COUNT(*) AS c FROM facility WHERE service_location = 1")['c'] ?? 0);
$practiceDataReady = ($providerCount > 0 && $facilityCount > 0);

// Admin-only visual demo mode for failed readiness rendering.
$readinessDemoFail = (isset($_GET['readiness_demo']) && $_GET['readiness_demo'] === 'fail');
if ($readinessDemoFail) {
    $isHttps = false;
    $looksPublicHost = false;
    $urlReady = false;
    $detectedUrl = 'http://10.2.1.95';
    $practiceDataReady = false;
    $providerCount = 0;
    $facilityCount = 0;
}

if ($isHttps && $looksPublicHost) {
    $urlFailureDetail = '';
} elseif (!$isHttps && !$looksPublicHost) {
    $urlFailureDetail = xlt('Current URL is not HTTPS and is not publicly reachable');
} elseif (!$isHttps) {
    $urlFailureDetail = xlt('Current URL is not HTTPS');
} else {
    $urlFailureDetail = xlt('Current URL is not publicly reachable');
}

$urlFixParts = [];
if (!$isHttps) {
    $urlFixParts[] = xlt('Fix HTTPS: use an https:// URL with a valid TLS certificate');
}
if (!$looksPublicHost) {
    $urlFixParts[] = xlt('Fix reachability: use a public FQDN, point DNS to your external IP, and allow inbound 443 through firewall/NAT');
}
$urlDetailLines = [];
if ($urlReady) {
    $urlDetailLines[] = xlt('Verified URL') . ': ' . $detectedUrl;
} else {
    $urlDetailLines[] = $urlFailureDetail . ': ' . $detectedUrl;
    foreach ($urlFixParts as $fixPart) {
        $urlDetailLines[] = $fixPart;
    }
}

$readinessChecklist = [
    [
        'label' => xlt('Production OpenEMR URL: https:// and is publicly reachable'),
        'ok' => $urlReady,
        'detail_lines' => $urlDetailLines,
    ],
    [
        'label' => xlt('At least one provider and one facility are configured'),
        'ok' => $practiceDataReady,
        'detail_lines' => [xlt('Providers') . ': ' . $providerCount . ' | ' . xlt('Facilities') . ': ' . $facilityCount],
    ],
];

$allReadinessPassed = true;
foreach ($readinessChecklist as $checkItem) {
    if (empty($checkItem['ok'])) {
        $allReadinessPassed = false;
        break;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("MedEx Onboarding"); ?></title>
    <?php Header::setupHeader(['jquery-min-3-7-1', 'fontawesome']); ?>
    <style>
        :root {
            --medex-blue: #0f4b8f;
            --medex-light-blue: #f0f7ff;
            --text-dark: #2c3e50;
            --text-muted: #64748b;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .hero-section {
            background: white;
            padding: 60px 20px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            height: 60px;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--medex-blue);
            margin-bottom: 16px;
            letter-spacing: -0.025em;
        }

        .subtitle {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 32px;
            line-height: 1.6;
        }

        .cta-container {
            margin-bottom: 40px;
        }

        .btn-get-started {
            background: var(--medex-blue);
            color: white;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.125rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(15, 75, 143, 0.2), 0 2px 4px -1px rgba(15, 75, 143, 0.1);
        }

        .btn-get-started:hover {
            background: #0a3460;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(15, 75, 143, 0.3);
            color: white;
        }

        .btn-disabled {
            background: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            pointer-events: none;
        }

        .readiness-list {
            margin: 10px 0 0;
            padding: 0;
            list-style: none;
        }

        .readiness-list li {
            margin: 8px 0;
            padding-left: 0;
            line-height: 1.4;
        }

        .readiness-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .readiness-row.ok {
            color: #1f2937;
        }

        .readiness-row.ok i {
            color: #16a34a;
        }

        .readiness-row.fail {
            color: #1f2937;
        }

        .readiness-row.fail i {
            color: #dc2626;
        }

        .readiness-detail {
            margin-left: 24px;
            color: #4b5563;
            font-size: 13px;
        }

        .readiness-detail.ok {
            color: #16a34a;
            font-weight: 500;
        }

        .readiness-detail.fail {
            color: #b45309;
            font-weight: 600;
        }

        .features-grid {
            max-width: 1000px;
            margin: -40px auto 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 0 20px;
        }

        .feature-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--medex-blue);
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f8fafc;
            font-weight: 500;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-item i {
            color: #10b981;
            font-size: 1.1rem;
        }

        .channel-icon {
            width: 32px;
            height: 32px;
            background: var(--medex-light-blue);
            color: var(--medex-blue);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .stats-banner {
            background: var(--medex-blue);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .stat-item h2 { font-size: 2.5rem; margin: 0; font-weight: 800; }
        .stat-item p { margin: 8px 0 0; opacity: 0.8; font-weight: 500; }

        footer {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .features-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; gap: 24px; }
            h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <section class="hero-section">
        <h1><?php echo xlt("MedEx Onboarding"); ?></h1>
        <p class="subtitle">
            <?php echo xlt("A HIPAA-compliant, secure SaaS platform for real-time information exchange."); ?>
        </p>
        <div style="max-width: 760px; margin: 0 auto 24px; text-align: left; background:#f8fafc; border:1px solid #e2e8f0; color:#1f2937; border-radius:10px; padding:14px 16px;">
            <div style="font-weight:700;"><?php echo xlt("Live Readiness Checklist"); ?></div>
            <ul class="readiness-list">
                <?php foreach ($readinessChecklist as $checkItem): ?>
                    <li>
                        <div class="readiness-row <?php echo $checkItem['ok'] ? 'ok' : 'fail'; ?>">
                            <i class="fa <?php echo $checkItem['ok'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <span><?php echo text($checkItem['label']); ?></span>
                        </div>
                        <div class="readiness-detail <?php echo $checkItem['ok'] ? 'ok' : 'fail'; ?>">
                            <?php foreach (($checkItem['detail_lines'] ?? []) as $detailLine): ?>
                                <div><?php echo text($detailLine); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (!$allReadinessPassed): ?>
                <div style="margin-top:10px; font-weight:700;">
                    <i class="fa fa-wrench"></i> <?php echo xlt("Fix the failed items above before proceeding."); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="cta-container">
            <?php if ($hasConnectedAccount): ?>
                <?php if ($hasActiveSubscriptions): ?>
                    <a href="index.php?site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" class="btn-get-started">
                        <i class="fa fa-tachometer"></i> <?php echo xlt("Open Dashboard"); ?>
                    </a>
                <?php else: ?>
                    <a href="onboarding.php?step=2&amp;site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" class="btn-get-started">
                        <i class="fa fa-arrow-right"></i> <?php echo xlt("Continue Onboarding"); ?>
                    </a>
                <?php endif; ?>
                <a href="help_center.php?site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" target="_blank" rel="noopener noreferrer" class="btn-get-started" style="margin-left:10px;background:#ffffff;color:var(--medex-blue);border:1px solid #bfdbfe;">
                    <i class="fa fa-life-ring"></i> <?php echo xlt("Help"); ?>
                </a>
                <p style="margin-top: 12px; font-size: 14px; color: #64748b;">
                    <?php echo xlt("Connected account:"); ?> <strong><?php echo text((string)$existingEmail); ?></strong>
                </p>
                <?php if (!$hasActiveSubscriptions): ?>
                    <p style="margin-top: 4px; font-size: 13px; color: #b45309;">
                        <?php echo xlt("No active services yet. Complete Step 2 to activate at least one service."); ?>
                    </p>
                <?php endif; ?>
            <?php elseif ($existingEmail): ?>
                <a href="reconnect.php?site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" class="btn-get-started">
                    <i class="fa fa-refresh"></i> <?php echo xlt("Reconnect Account"); ?>
                </a>
                <a href="help_center.php?site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" target="_blank" rel="noopener noreferrer" class="btn-get-started" style="margin-left:10px;background:#ffffff;color:var(--medex-blue);border:1px solid #bfdbfe;">
                    <i class="fa fa-life-ring"></i> <?php echo xlt("Help"); ?>
                </a>
                <p style="margin-top: 12px; font-size: 14px; color: #64748b;">
                    <?php echo xlt("Found existing account:"); ?> <strong><?php echo text($existingEmail); ?></strong>
                </p>
            <?php else: ?>
                <a href="<?php echo $allReadinessPassed ? ('onboarding.php?step=1&amp;site=' . urlencode((string)($_GET['site'] ?? 'default'))) : '#'; ?>" class="btn-get-started <?php echo $allReadinessPassed ? '' : 'btn-disabled'; ?>" onclick="<?php echo $allReadinessPassed ? "setTimeout(function() { window.parent.document.getElementById('medexStatusModal')?.remove(); }, 500);" : "return false;"; ?>">
                    <?php echo xlt("Get Started"); ?> <i class="fa fa-arrow-right" style="margin-left: 8px;"></i>
                </a>
                <a href="help_center.php?site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" target="_blank" rel="noopener noreferrer" class="btn-get-started" style="margin-left:10px;background:#ffffff;color:var(--medex-blue);border:1px solid #bfdbfe;">
                    <i class="fa fa-life-ring"></i> <?php echo xlt("Help"); ?>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!$isMinimal): ?>
    <div class="features-grid">
        <div class="feature-card">
            <h3><i class="fa fa-bullseye"></i> <?php echo xlt("Services Offered"); ?></h3>
            <div class="feature-list">
                <div class="feature-item">
                    <i class="fa fa-check-circle"></i>
                    <span><?php echo xlt("Appointment Reminders"); ?></span>
                </div>
                <div class="feature-item">
                    <i class="fa fa-check-circle"></i>
                    <span><?php echo xlt("Patient Recalls & Follow-ups"); ?></span>
                </div>
                <div class="feature-item">
                    <i class="fa fa-check-circle"></i>
                    <span><?php echo xlt("Office Announcements"); ?></span>
                </div>
            </div>
        </div>

        <div class="feature-card">
            <h3><i class="fa fa-paper-plane"></i> <?php echo xlt("Omnichannel Delivery"); ?></h3>
            <div class="feature-list">
                <div class="feature-item">
                    <div class="channel-icon"><i class="fa fa-commenting"></i></div>
                    <span><?php echo xlt("Two-Way SMS Messaging"); ?></span>
                </div>
                <div class="feature-item">
                    <div class="channel-icon"><i class="fa fa-phone"></i></div>
                    <span><?php echo xlt("Automated Voice Calls"); ?></span>
                </div>
                <div class="feature-item">
                    <div class="channel-icon"><i class="fa fa-envelope"></i></div>
                    <span><?php echo xlt("Professional E-mail Campaigns"); ?></span>
                </div>
            </div>
        </div>
    </div>

    <section class="stats-banner">
        <div class="stats-grid">
            <div class="stat-item">
                <h2>35%</h2>
                <p><?php echo xlt("Average No-Show Reduction"); ?></p>
            </div>
            <div class="stat-item">
                <h2>10x</h2>
                <p><?php echo xlt("Faster Patient Response Time"); ?></p>
            </div>
            <div class="stat-item">
                <h2>100%</h2>
                <p><?php echo xlt("HIPAA Compliant"); ?></p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> MedEx Communication Hub. <?php echo xlt("All rights reserved."); ?></p>
        <p style="margin-top: 10px; font-size: 0.75rem;">
            <?php echo xlt("Integrated with OpenEMR"); ?>
        </p>
    </footer>
    <?php endif; // !$isMinimal ?>
</body>
</html>
