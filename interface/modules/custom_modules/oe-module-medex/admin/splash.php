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

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("Welcome to MedEx"); ?></title>
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
        <div style="font-size: 3rem; font-weight: 800; color: var(--medex-blue); margin-bottom: 16px;">
            MedEx
        </div>
        <h1><?php echo xlt("Elevate Your Practice Communication"); ?></h1>
        <p class="subtitle">
            <?php echo xlt("MedEx provides comprehensive patient communication and practice management tools integrated directly with OpenEMR. Reduce no-shows, improve collections, and engage your patients across every channel."); ?>
        </p>
        <div class="cta-container">
            <?php if ($existingEmail): ?>
                <a href="reconnect.php" class="btn-get-started">
                    <i class="fa fa-refresh"></i> <?php echo xlt("Reconnect Account"); ?>
                </a>
                <p style="margin-top: 12px; font-size: 14px; color: #64748b;">
                    <?php echo xlt("Found existing account:"); ?> <strong><?php echo text($existingEmail); ?></strong>
                </p>
            <?php else: ?>
                <a href="onboarding.php?step=1" class="btn-get-started" target="_blank" onclick="setTimeout(function() { window.parent.document.getElementById('medexStatusModal')?.remove(); }, 500);">
                    <?php echo xlt("Get Started"); ?> <i class="fa fa-arrow-right" style="margin-left: 8px;"></i>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!$isMinimal): ?>
    <div class="features-grid">
        <div class="feature-card">
            <h3><i class="fa fa-bullseye"></i> <?php echo xlt("Communication Targets"); ?></h3>
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
                <div class="feature-item">
                    <i class="fa fa-check-circle"></i>
                    <span><?php echo xlt("Patient Satisfaction Surveys"); ?></span>
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
                <div class="feature-item">
                    <div class="channel-icon"><i class="fa fa-map-marker"></i></div>
                    <span><?php echo xlt("Physical Postcards & Address Labels"); ?></span>
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
