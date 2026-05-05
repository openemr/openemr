<?php

/**
 * MedEx TeleHealth Page - Iframe Wrapper
 *
 * Embeds MedEx TeleMedEx (TM) interface via iframe with SSO authentication.
 * Shows Waiting Room, In Session, Invite, and History tabs from MedEx SaaS.
 *
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once dirname(__FILE__, 6) . '/globals.php';
require_once __DIR__ . '/../src/MedExAPI.php';

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

// Check authentication
if (!AclMain::aclCheckCore('encounters', 'notes')) {
    echo xlt('Access denied');
    exit;
}

// Get parameters
$pid = $_GET['pid'] ?? ($_SESSION['pid'] ?? 0);
$encounter = $_GET['encounter'] ?? ($_SESSION['encounter'] ?? 0);

$telehealthApi = new \OpenEMR\Modules\MedEx\MedExAPI();
$telehealthEnabled = $telehealthApi->hasAnyServiceEntitlement(['TeleHealth', 'telehealth']);
$prefs = sqlQuery("SELECT MedEx_id FROM medex_prefs LIMIT 1");
$practiceId = $prefs['MedEx_id'] ?? '';

if (!$telehealthEnabled) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo xlt('TeleHealth Not Enabled'); ?></title>
        <?php Header::setupHeader(['common']); ?>
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-warning text-center">
                <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                <h3><?php echo xlt('TeleHealth Service Not Enabled'); ?></h3>
                <p><?php echo xlt('Please enable TeleHealth in your MedEx subscription.'); ?></p>
                <a href="<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/status.php" class="btn btn-primary">
                    <i class="fa fa-cog"></i> <?php echo xlt('Check Subscription'); ?>
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Build MedEx URL for iframe — must be browser-accessible (public HTTPS endpoint).
// MedExConfig::publicBaseUrl() handles internal k8s hostname translation.
require_once __DIR__ . '/../src/MedExConfig.php';
$medexUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl() . '/';

// Login to MedEx to get a session token (SSO)
require_once(__DIR__ . '/../src/MedExAPI.php');
$medexApi = new \OpenEMR\Modules\MedEx\MedExAPI();

try {
    $medexApi->login();
    $sessionToken = $medexApi->getSessionToken();
    error_log('[MedEx TeleHealth] Login successful, got session token');
} catch (\Exception $e) {
    error_log('[MedEx TeleHealth] Login failed: ' . $e->getMessage());
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo xlt('TeleHealth Error'); ?></title>
        <?php Header::setupHeader(['common']); ?>
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-danger">
                <h3><?php echo xlt('MedEx Authentication Error'); ?></h3>
                <p><strong><?php echo xlt('Error'); ?>:</strong> <?php echo text($e->getMessage()); ?></p>
                <p><?php echo xlt('Please check your MedEx configuration.'); ?></p>
                <a href="<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/settings.php" class="btn btn-primary">
                    <i class="fa fa-cog"></i> <?php echo xlt('Settings'); ?>
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Build iframe URL to MedEx TeleMedEx (TM) hub
$iframeUrl = $medexUrl . 'index.php?route=information/TM';
$iframeUrl .= '&token=' . urlencode($sessionToken);
$iframeUrl .= '&customer_id=' . urlencode($practiceId);

// Pass patient context if available
if ($pid) {
    $iframeUrl .= '&pid=' . urlencode($pid);
}

// Determine trusted origin for postMessage
$parsed = parse_url($medexUrl);
$medexOrigin = '';
if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
    $medexOrigin = $parsed['scheme'] . '://' . $parsed['host'];
    if (!empty($parsed['port'])) {
        $medexOrigin .= ':' . $parsed['port'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('MedEx TeleHealth'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        #medex-telehealth-iframe {
            width: 100%;
            height: calc(100vh - 10px);
            border: none;
            display: block;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 1000;
        }
        .loading-overlay.hidden {
            display: none;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e0e0e0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-text {
            margin-top: 20px;
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text"><?php echo xlt('Loading TeleMedEx...'); ?></div>
    </div>

    <iframe 
        id="medex-telehealth-iframe" 
        src="<?php echo attr($iframeUrl); ?>"
        allow="camera; microphone; display-capture; fullscreen"
        allowfullscreen
        title="<?php echo xla('MedEx TeleHealth'); ?>">
    </iframe>

    <script>
        var iframe = document.getElementById('medex-telehealth-iframe');
        var loadingOverlay = document.getElementById('loadingOverlay');
        var loadTimeout;

        // Hide loading after iframe loads or timeout
        loadTimeout = setTimeout(function() {
            loadingOverlay.classList.add('hidden');
            console.log('[MedEx TeleHealth] Timeout - hiding loader');
        }, 8000);

        iframe.onload = function() {
            clearTimeout(loadTimeout);
            loadingOverlay.classList.add('hidden');
            console.log('[MedEx TeleHealth] Iframe loaded successfully');
        };

        iframe.onerror = function() {
            clearTimeout(loadTimeout);
            loadingOverlay.innerHTML = '<div class="alert alert-danger">Failed to load TeleMedEx. Please try again.</div>';
            console.error('[MedEx TeleHealth] Iframe failed to load');
        };

        // Listen for messages from MedEx iframe
        window.addEventListener('message', function(event) {
            var trustedOrigin = '<?php echo $medexOrigin; ?>';
            if (trustedOrigin && event.origin !== trustedOrigin) {
                return;
            }
            
            if (event.data && event.data.type) {
                switch (event.data.type) {
                    case 'telehealth_session_started':
                        console.log('[MedEx TeleHealth] Session started:', event.data);
                        break;
                    case 'telehealth_session_ended':
                        console.log('[MedEx TeleHealth] Session ended:', event.data);
                        break;
                    case 'patient_joined':
                        console.log('[MedEx TeleHealth] Patient joined:', event.data);
                        break;
                }
            }
        });
    </script>
</body>
</html>
