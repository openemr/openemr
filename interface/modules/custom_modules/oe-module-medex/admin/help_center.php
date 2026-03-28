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
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExConfig;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

$siteId = $_GET['site'] ?? 'default';
$tutorialUrl = trim((string) MedExConfig::tutorialUrl());
$hasTutorial = $tutorialUrl !== '';
$startUrl = 'onboarding.php?step=1&site=' . urlencode((string) $siteId);
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
        }
        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        .hero {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 10px 24px rgba(15, 75, 143, 0.08);
        }
        .title {
            font-size: 34px;
            font-weight: 800;
            color: var(--brand);
            margin: 0 0 10px;
        }
        .subtitle {
            margin: 0 0 18px;
            color: var(--muted);
            line-height: 1.5;
            font-size: 16px;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        .btn-secondary {
            background: #fff;
            color: var(--brand);
            border-color: #bfdbfe;
        }
        .video-box {
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            background: #0b2340;
            min-height: 260px;
            overflow: hidden;
        }
        .video-box iframe {
            width: 100%;
            height: 100%;
            min-height: 260px;
            border: 0;
        }
        .video-fallback {
            color: #dbeafe;
            padding: 24px;
            line-height: 1.5;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .step {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
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
        .step p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }
        .checklist {
            margin-top: 16px;
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
        @media (max-width: 1000px) {
            .hero {
                grid-template-columns: 1fr;
            }
            .grid {
                grid-template-columns: 1fr 1fr;
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
        <section class="panel">
            <h1 class="title"><?php echo xlt('MedEx Help Center'); ?></h1>
            <p class="subtitle">
                <?php echo xlt('Use this quick visual guide to onboard safely, verify your practice identity, and activate services without breaking your live workflow.'); ?>
            </p>
            <div class="actions">
                <a class="btn btn-primary" href="<?php echo attr($startUrl); ?>">
                    <i class="fa fa-rocket"></i> <?php echo xlt('Start Onboarding'); ?>
                </a>
                <?php if ($hasTutorial): ?>
                    <a class="btn btn-secondary" href="<?php echo attr($tutorialUrl); ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-play-circle"></i> <?php echo xlt('Open Full Tutorial Video'); ?>
                    </a>
                <?php endif; ?>
            </div>
            <span class="pill"><?php echo xlt('For practice administrators only'); ?></span>
        </section>
        <section class="panel">
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
        </section>
    </div>

    <section class="grid">
        <article class="step">
            <span class="step-num">1</span>
            <h3><?php echo xlt('Confirm Server URL'); ?></h3>
            <p><?php echo xlt('Use the exact URL MedEx can reach from the internet. Local/private URLs are routed to manual review.'); ?></p>
        </article>
        <article class="step">
            <span class="step-num">2</span>
            <h3><?php echo xlt('Verify Admin Identity'); ?></h3>
            <p><?php echo xlt('Use one-time passcode verification by email or SMS to confirm administrative control before activation.'); ?></p>
        </article>
        <article class="step">
            <span class="step-num">3</span>
            <h3><?php echo xlt('Choose Services'); ?></h3>
            <p><?php echo xlt('Select only the services you need for testing. Additional services can be enabled later from your dashboard.'); ?></p>
        </article>
        <article class="step">
            <span class="step-num">4</span>
            <h3><?php echo xlt('Go Live'); ?></h3>
            <p><?php echo xlt('After activation, run sync and verify one patient reminder flow end-to-end before broad rollout.'); ?></p>
        </article>
    </section>

    <section class="checklist">
        <h3><?php echo xlt('Live Readiness Checklist'); ?></h3>
        <ul>
            <li><?php echo xlt('Production OpenEMR URL uses HTTPS and is publicly reachable'); ?></li>
            <li><?php echo xlt('At least one provider and one facility are configured'); ?></li>
            <li><?php echo xlt('Practice administrator email and verification channel are valid'); ?></li>
            <li><?php echo xlt('You reviewed Terms, BAA, and Privacy Policy before activation'); ?></li>
        </ul>
    </section>
</div>
</body>
</html>
