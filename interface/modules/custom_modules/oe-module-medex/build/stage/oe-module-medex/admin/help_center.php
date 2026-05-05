<?php
/**
 * MedEx Help Center (Visual)
 *
 * Video-first onboarding guidance for practice administrators.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\ComponentLoader;
use OpenEMR\Modules\MedEx\MedExConfig;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

$siteId = $_GET['site'] ?? 'default';
$tutorialUrl = 'https://medexbank.com/help/tutorial_box.html';
$hasTutorial = $tutorialUrl !== '';
$startUrl = 'onboarding.php?step=1&site=' . urlencode((string) $siteId);
$topic = trim((string)($_GET['topic'] ?? ''));
$topicHelp = [
    'onboarding_url' => [
        'title' => xlt('Onboarding URL and Callback Guide'),
        'summary' => xlt('Callback verification requires a public HTTPS OpenEMR URL in production.'),
        'points' => [
            xlt('Production: use an HTTPS FQDN that resolves publicly and allows inbound port 443.'),
            xlt('Developer test: enable MedEx onboarding developer mode only for non-production testing.'),
            xlt('For local Docker/localhost, use a tunnel URL (Cloudflare Tunnel or ngrok) as your OpenEMR URL.'),
        ],
        'commands' => [
            "sqlQuery(\"REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_onboarding_dev_mode', 0, '1')\");",
            "cloudflared tunnel --url http://localhost:8300",
            "ngrok http 8300",
        ],
    ],
];
$serviceCatalog = [];
try {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    require_once(__DIR__ . '/../src/ComponentLoader.php');
    $pricing = (new \OpenEMR\Modules\MedEx\MedExAPI())->getPricing(true);
    $serviceCatalog = ComponentLoader::buildServiceCatalog(
        is_array($pricing['services'] ?? null) ? $pricing['services'] : [],
        'help_center.php?site=' . urlencode((string)$siteId)
    );
} catch (\Throwable $e) {
    $serviceCatalog = [];
}

$activeTopic = $topicHelp[$topic] ?? ComponentLoader::helpTopic($topic, $serviceCatalog);
if (is_array($activeTopic)) {
    $decodeHelpValue = static function ($value) use (&$decodeHelpValue) {
        if (is_array($value)) {
            return array_map($decodeHelpValue, $value);
        }
        return html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    };
    $activeTopic = $decodeHelpValue($activeTopic);
}
$hasActiveTopic = is_array($activeTopic);
$leftPanelTitle = $hasActiveTopic ? (string)($activeTopic['title'] ?? xl('Service Help')) : xlt('Onboarding Preview');
$leftPanelSubtitle = $hasActiveTopic
    ? (string)($activeTopic['summary'] ?? xl('Review this MedEx service before enabling it.'))
    : xlt('Preview the onboarding process step by step before account activation.');

$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$proto = (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
$httpsRaw = (string)($_SERVER['HTTPS'] ?? '');
$isHttps = ($proto === 'https') || (!empty($httpsRaw) && strtolower($httpsRaw) !== 'off');
$isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
$isPrivateIp = $isIp && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
$looksPublicHost = !empty($host) && stripos($host, 'localhost') === false && stripos($host, '.local') === false && !$isPrivateIp;
$urlReady = $isHttps && $looksPublicHost;
$detectedUrl = ($isHttps ? 'https://' : 'http://') . $host;

$providerCount = 0;
$facilityCount = 0;
$adminEmail = '';
$isConfigured = false;
try {
    $providerCount = (int)(QueryUtils::querySingleRow("SELECT COUNT(*) AS c FROM users WHERE authorized = 1 AND active = 1", [])['c'] ?? 0);
    $facilityCount = (int)(QueryUtils::querySingleRow("SELECT COUNT(*) AS c FROM facility WHERE service_location = 1", [])['c'] ?? 0);
    $adminEmail = trim((string)(QueryUtils::querySingleRow("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1", [])['ME_username'] ?? ''));
    require_once(__DIR__ . '/../src/MedExAPI.php');
    $isConfigured = (new \OpenEMR\Modules\MedEx\MedExAPI())->isConfigured();
} catch (\Throwable $e) {
    // Keep checklist render resilient.
}

$practiceDataReady = ($providerCount > 0 && $facilityCount > 0);
$readinessChecklist = [
    [
        'label' => xlt('Production OpenEMR URL: https:// and is publicly reachable'),
        'ok' => $urlReady,
        'detail' => $urlReady ? (xlt('Verified URL') . ': ' . $detectedUrl) : (xlt('Current URL is not HTTPS or is not publicly reachable') . ': ' . $detectedUrl)
    ],
    [
        'label' => xlt('At least one provider and one facility are configured'),
        'ok' => $practiceDataReady,
        'detail' => xlt('Providers') . ': ' . $providerCount . ' | ' . xlt('Facilities') . ': ' . $facilityCount
    ],
];
$allReadinessPassed = true;
foreach ($readinessChecklist as $readinessItem) {
    if (empty($readinessItem['ok'])) {
        $allReadinessPassed = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Help Center'); ?></title>
    <?php Header::setupHeader(['fontawesome']); ?>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #dbeafe;
            --brand: #0f4b8f;
            --brand-soft: #eff6ff;
            --ok: #0f766e;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 0 0, #f8fbff 0, #eef6ff 40%, #f8fbff 100%);
            min-height: 100vh;
        }
        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .hero {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 18px;
            align-items: start;
            flex: 1 1 auto;
            min-height: 0;
        }
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 10px 24px rgba(15, 75, 143, 0.08);
        }
        .help-left {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch;
            gap: 12px;
            height: 100%;
        }
        .help-right {
            height: 100%;
            padding: 0;
        }
        .bottom-dock {
            margin-top: auto;
        }
        .title {
            font-size: 32px;
            font-weight: 800;
            color: var(--brand);
            margin: 0 0 6px;
        }
        .section-head {
            font-size: 36px;
            line-height: 1.1;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px;
        }
        .subtitle {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
            font-size: 16px;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .help-left .actions {
            justify-content: flex-start;
        }
        .help-left .pill {
            align-self: flex-start;
            margin-top: 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 9px;
            padding: 12px 16px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: var(--brand);
            color: #fff;
        }
        .btn-disabled {
            background: #cbd5e1;
            color: #475569;
            border-color: #cbd5e1;
            cursor: not-allowed;
            pointer-events: none;
        }
        .btn-secondary {
            background: #fff;
            color: var(--brand);
            border-color: #bfdbfe;
        }
        .video-box {
            border: 0;
            border-radius: 14px;
            background: transparent;
            overflow: hidden;
            height: 100%;
        }
        .detail-doc {
            height: 100%;
            padding: 24px;
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0) 30%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .detail-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--brand);
        }
        .detail-head {
            padding-bottom: 14px;
            border-bottom: 1px solid #dbeafe;
        }
        .detail-title {
            margin: 8px 0 8px;
            font-size: 34px;
            line-height: 1.05;
            font-weight: 800;
            color: #0f172a;
        }
        .detail-summary {
            margin: 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
            max-width: 780px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 18px;
            flex: 1 1 auto;
            min-height: 0;
        }
        .detail-card {
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 18px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 75, 143, 0.05);
        }
        .detail-card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #0f4b8f;
        }
        .detail-list {
            margin: 0;
            padding-left: 18px;
            color: #334155;
            font-size: 14px;
            line-height: 1.6;
        }
        .detail-list li {
            margin: 8px 0;
        }
        .detail-note {
            margin: 0;
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
        }
        .detail-status {
            display: grid;
            gap: 10px;
        }
        .detail-status-row {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 12px 14px;
            background: #f8fbff;
        }
        .detail-status-label {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #0f4b8f;
            margin-bottom: 4px;
        }
        .detail-status-copy {
            color: #334155;
            font-size: 14px;
            line-height: 1.5;
        }
        .video-box iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
        .hero > .panel:last-child {
            display: flex;
        }
        .hero > .panel:last-child .video-box {
            flex: 1 1 auto;
            min-height: 0;
        }
        .video-fallback {
            color: #dbeafe;
            padding: 24px;
            line-height: 1.5;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(220px, 1fr));
            gap: 12px;
            max-width: 980px;
            margin: 0 auto;
            width: 100%;
            align-items: end;
        }
        .step {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .step.active {
            border-color: #0f4b8f;
            box-shadow: 0 0 0 2px #dbeafe inset, 0 6px 14px rgba(15, 75, 143, 0.14);
        }
        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 99px;
            background: var(--brand-soft);
            color: var(--brand);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .step h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }
        .step.active .step-num {
            background: #0f4b8f;
            color: #fff;
            border-color: #0f4b8f;
        }
        .step p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }
        .checklist {
            margin-top: 14px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }
        .checklist h3 {
            margin: 0 0 8px;
        }
        .checklist ul {
            margin: 0;
            padding-left: 20px;
            color: var(--muted);
        }
        .checklist li {
            margin: 6px 0;
        }
        .check-row {
            display: grid;
            grid-template-columns: 26px 1fr;
            gap: 10px;
            align-items: start;
            padding: 10px 0;
            border-bottom: 1px solid #eef4ff;
        }
        .check-row:last-child {
            border-bottom: 0;
        }
        .check-icon {
            font-size: 18px;
            line-height: 1;
            margin-top: 1px;
        }
        .check-icon.ok { color: #059669; }
        .check-icon.bad { color: #dc2626; }
        .check-label {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }
        .check-detail {
            margin-top: 2px;
            font-size: 12px;
            color: #64748b;
        }
        .check-detail.ok {
            color: #059669;
            font-weight: 700;
        }
        .pill {
            display: inline-block;
            margin-top: 8px;
            font-size: 12px;
            background: #ecfdf5;
            color: var(--ok);
            border: 1px solid #99f6e4;
            border-radius: 999px;
            padding: 3px 10px;
            font-weight: 700;
        }
        .topic-panel {
            margin-top: 14px;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 14px;
            background: #f8fbff;
        }
        .topic-panel h3 {
            margin: 0 0 6px;
            color: #0f4b8f;
            font-size: 16px;
        }
        .topic-summary {
            margin: 0 0 10px;
            color: #334155;
            font-size: 13px;
        }
        .topic-points {
            margin: 0;
            padding-left: 18px;
            color: #334155;
            font-size: 13px;
        }
        .topic-points li {
            margin: 5px 0;
        }
        .topic-code {
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid #c7d9ee;
            border-radius: 10px;
            background: #f1f7ff;
            font-family: Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
            color: #17324d;
            overflow-x: auto;
            white-space: pre;
        }
        @media (max-width: 1000px) {
            .hero {
                grid-template-columns: 1fr;
            }
            .grid {
                grid-template-columns: 1fr 1fr;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 700px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <section class="panel help-left">
            <h1 class="title"><?php echo xlt('MedEx Help'); ?></h1>
            <h2 class="section-head"><?php echo text($leftPanelTitle); ?></h2>
            <p class="subtitle">
                <?php echo text($leftPanelSubtitle); ?>
            </p>
            <?php if ($activeTopic): ?>
                <section class="topic-panel">
                    <h3><?php echo text($activeTopic['title']); ?></h3>
                    <p class="topic-summary"><?php echo text($activeTopic['summary']); ?></p>
                    <ul class="topic-points">
                        <?php foreach ($activeTopic['points'] as $point): ?>
                            <li><?php echo text($point); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (!empty($activeTopic['commands']) && is_array($activeTopic['commands'])): ?>
                        <?php foreach ($activeTopic['commands'] as $cmd): ?>
                            <div class="topic-code"><?php echo text($cmd); ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <div class="bottom-dock">
                <section class="checklist" style="margin-bottom: 14px;">
                    <h3><?php echo xlt('Readiness Status'); ?></h3>
                    <div class="check-row">
                        <div class="check-icon <?php echo $allReadinessPassed ? 'ok' : 'bad'; ?>">
                            <i class="fa <?php echo $allReadinessPassed ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </div>
                        <div>
                            <div class="check-label"><?php echo $allReadinessPassed ? xlt('Ready to start onboarding') : xlt('Readiness checks need attention'); ?></div>
                        </div>
                    </div>
                </section>
                <div class="actions">
                    <?php if ($allReadinessPassed): ?>
                        <a class="btn btn-primary" href="<?php echo attr($startUrl); ?>">
                            <i class="fa fa-rocket"></i> <?php echo xlt('Start Onboarding'); ?>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-primary btn-disabled" title="<?php echo attr(xl('Complete all readiness checks to enable onboarding')); ?>">
                            <i class="fa fa-lock"></i> <?php echo xlt('Start Onboarding'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <section class="panel help-right">
            <?php if ($hasActiveTopic): ?>
                <div class="detail-doc">
                    <div class="detail-head">
                        <div class="detail-kicker"><?php echo xlt('Service Help'); ?></div>
                        <h2 class="detail-title"><?php echo text($activeTopic['title']); ?></h2>
                        <p class="detail-summary"><?php echo text($activeTopic['summary']); ?></p>
                    </div>
                    <div class="detail-grid">
                        <section class="detail-card">
                            <h3><?php echo xlt('What This Service Does'); ?></h3>
                            <ul class="detail-list">
                                <?php foreach (($activeTopic['points'] ?? []) as $point): ?>
                                    <li><?php echo text($point); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                        <section class="detail-status">
                            <div class="detail-status-row">
                                <div class="detail-status-label"><?php echo xlt('In Onboarding'); ?></div>
                                <div class="detail-status-copy"><?php echo xlt('Select the service first. If configuration is required, MedEx will expand the extra choices before the cart step.'); ?></div>
                            </div>
                            <div class="detail-status-row">
                                <div class="detail-status-label"><?php echo xlt('After Activation'); ?></div>
                                <div class="detail-status-copy"><?php echo xlt('Detailed behavior, templates, and workflow rules are managed from the MedEx service area after onboarding is complete.'); ?></div>
                            </div>
                            <div class="detail-status-row">
                                <div class="detail-status-label"><?php echo xlt('Need More Detail'); ?></div>
                                <div class="detail-status-copy"><?php echo xlt('Use the service help icon from onboarding whenever you need this service-specific overview again.'); ?></div>
                            </div>
                        </section>
                    </div>
                    <?php if (!empty($activeTopic['commands']) && is_array($activeTopic['commands'])): ?>
                        <section class="detail-card">
                            <h3><?php echo xlt('Reference Commands'); ?></h3>
                            <?php foreach ($activeTopic['commands'] as $cmd): ?>
                                <div class="topic-code"><?php echo text($cmd); ?></div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="video-box">
                    <?php if ($hasTutorial): ?>
                        <iframe src="<?php echo attr($tutorialUrl); ?>" allowfullscreen title="<?php echo attr(xl('MedEx tutorial video')); ?>"></iframe>
                    <?php else: ?>
                        <div class="video-fallback">
                            <h3 style="margin:0 0 8px;color:#fff;"><?php echo xlt('Tutorial Video'); ?></h3>
                            <p style="margin:0;"><?php echo xlt('Video link is not configured yet. Use the step-by-step cards below, then contact support for narrated walkthrough access.'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
</body>
</html>
