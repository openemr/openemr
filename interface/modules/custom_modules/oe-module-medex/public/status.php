<?php
/**
 * MedEx Module - Connection Status Page
 *
 * Simple status display for Module Manager gear icon
 * Shows connection status and link to full settings
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExConfig.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied. Admin access required.') . "</body></html>";
    exit;
}

// Check if module SQL has been installed yet
$notInstalled = false;
try {
    $modRow = sqlQuery("SELECT sql_run, mod_active FROM modules WHERE mod_directory = 'oe-module-medex' LIMIT 1");
    $notInstalled = empty($modRow) || (int)($modRow['sql_run'] ?? 0) === 0;
} catch (\Exception $e) {
    $notInstalled = true;
}

// Pre-install: skip all API/DB checks — tables may not exist yet
$isConfigured = false;
$connectionStatus = null;
$practiceInfo = null;
$errorMessage = null;
$updateInfo = null;
$medex_enable = '0';
$saasRegisterUrl = null;
$saasDashboardUrl = null;
$saasSettingsUrl = null;
$localRegisterUrl = null;

if (!$notInstalled) {
    // Load MedEx API with error handling
    try {
        require_once(__DIR__ . '/../src/MedExAPI.php');
        require_once(__DIR__ . '/../src/UpdateManager.php');
        $api = new \OpenEMR\Modules\MedEx\MedExAPI();
        $updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();
    } catch (\Exception $e) {
        error_log('[MedEx Status] Error loading classes: ' . $e->getMessage());
        $errorMessage = $e->getMessage();
    }

    if (empty($errorMessage)) {
        try {
            // Check if globally enabled FIRST
            $medex_enable_row = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_enable'", []);
            $medex_enable = $medex_enable_row['gl_value'] ?? '0';

            $isConfigured = $api->isConfigured();

            // Only proceed with connection checks if module is enabled
            if ($medex_enable === '1' && $isConfigured) {
                $connectionStatus = $api->testConnection();
                if ($connectionStatus && $connectionStatus['success']) {
                    $practiceInfo = $connectionStatus;
                } else {
                    $errorMessage = $connectionStatus['error'] ?? 'Connection failed';
                }

                // Generate SaaS URLs with SSO tokens
                $saasDashboardUrl = $api->getSaaSUrl('dashboard');
                $saasSettingsUrl = $api->getSaaSUrl('settings');

                // Check for updates
                $updateInfo = $updateManager->checkForUpdates();
            } elseif ($medex_enable === '1' && !$isConfigured) {
                // Not configured - point to built-in module registration flow first.
                $siteId = $_GET['site'] ?? 'default';
                $localRegisterUrl = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/admin/register.php?site=' . urlencode($siteId);
            }
            // If disabled but configured, we don't test connection or generate URLs
        } catch (\Exception $e) {
            error_log('[MedEx Status] Error: ' . $e->getMessage());
            $errorMessage = 'Error loading status: ' . $e->getMessage();
        }
    }
}

$siteId = $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default');
$helpAnchor = '#daily-workflow';
if ($notInstalled) {
    $helpAnchor = '#what-medex-handles';
} elseif ($medex_enable !== '1' || !$isConfigured) {
    $helpAnchor = '#start-here-connection';
}
$helpCenterUrl = ($GLOBALS['webroot'] ?? '')
    . '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?minimal=1&site=' . urlencode((string)$siteId);
$tutorialUrl = \OpenEMR\Modules\MedEx\MedExConfig::tutorialUrl();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Status'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        html, body {
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            height: auto;
            min-height: 100%;
        }
        .status-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .status-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .status-header h2 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        .status-online {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-offline {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-disabled {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-not-registered {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            margin: 5px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
            border: 2px solid #667eea;
        }
        .btn-primary:hover {
            background: #5568d3;
            border-color: #5568d3;
        }
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .update-alert {
            background: #f8f9fa;
            border-radius: 6px;
            margin: 20px 0;
            overflow: hidden;
        }
        .update-alert-header {
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .update-alert-body {
            padding: 15px 20px;
            color: #333;
        }
        .update-critical .update-alert-header { background: #e53e3e; }
        .update-security .update-alert-header { background: #dd6b20; }
        .update-important .update-alert-header { background: #3b82f6; }
        .update-optional .update-alert-header  { background: #10b981; }
        .update-alert h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 700;
        }
        .update-alert p {
            margin: 5px 0;
            font-size: 14px;
        }
        .update-version {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        .help-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .help-footer-links {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
        }
        .help-footer-link {
            color: #485c76;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }
        .help-footer-link:hover {
            color: #0a4f94;
        }
        .btn-medex-update {
            background: #10b981;
            color: white;
            border: 2px solid #10b981;
        }
        .btn-medex-update:hover {
            background: #059669;
            border-color: #059669;
        }
        .btn-medex-critical-update {
            background: #e53e3e;
            color: white;
            border: 2px solid #e53e3e;
            animation: pulse 2s infinite;
        }
        .btn-medex-critical-update:hover {
            background: #c53030;
            border-color: #c53030;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        /* Override OpenEMR core 100vh height when loaded in Module Manager iframe */
        body.in-iframe {
            max-height: fit-content !important;
            overflow: visible !important;
        }
    </style>
    <script>
        // Fix excessive iframe height caused by OpenEMR core configure.phtml setting height:100vh
        (function() {
            // Detect if we're in an iframe (nested: status.php in iframe, inside div with 100vh)
            if (window.parent && window.parent !== window) {
                document.body.classList.add('in-iframe');

                // Wait for content to load, then fix parent container
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        try {
                            // The structure is: iframe (us) -> div.container-xl (100vh) -> td -> tr.config
                            // So we need to access parent's parent's parent
                            let targetDiv = window.frameElement?.parentElement;

                            if (targetDiv && targetDiv.style && targetDiv.style.height === '100vh') {
                                // Found it! This is the div with height:100vh from configure.phtml:203
                                targetDiv.style.height = 'auto';
                                targetDiv.style.maxHeight = '600px';
                                targetDiv.style.overflow = 'auto';
                                console.log('MedEx Status: Fixed 100vh container height');
                            } else {
                                // Fallback: search for container-xl with 100vh in parent document
                                const parentDoc = window.parent.document;
                                const containers = parentDoc.querySelectorAll('.container-xl[style*="100vh"]');
                                for (let container of containers) {
                                    if (container.querySelector('iframe')) {
                                        container.style.height = 'auto';
                                        container.style.maxHeight = '600px';
                                        container.style.overflow = 'auto';
                                        console.log('MedEx Status: Fixed 100vh via fallback search');
                                        break;
                                    }
                                }
                            }
                        } catch (e) {
                            console.log('MedEx Status: Could not access parent context:', e.message);
                        }
                    }, 100); // Small delay to ensure DOM is fully rendered
                });
            }
        })();
    </script>
</head>
<body>
    <div class="status-container">
        <div class="status-header">
            <h2><i class="fa fa-satellite-dish"></i> <?php echo xlt('MedEx Connection Status'); ?></h2>

            <?php if ($notInstalled): ?>
                <span class="status-badge status-not-registered">
                    <i class="fa fa-download"></i> <?php echo xlt('Not Installed'); ?>
                </span>
            <?php elseif ($medex_enable != '1'): ?>
                <span class="status-badge status-disabled">
                    <i class="fa fa-power-off"></i> <?php echo xlt('Disabled'); ?>
                </span>
            <?php elseif (!$isConfigured): ?>
                <span class="status-badge status-not-registered">
                    <i class="fa fa-exclamation-circle"></i> <?php echo xlt('Not Registered'); ?>
                </span>
            <?php elseif ($connectionStatus && $connectionStatus['success']): ?>
                <span class="status-badge status-online">
                    <i class="fa fa-check-circle"></i> <?php echo xlt('Online'); ?>
                </span>
            <?php else: ?>
                <span class="status-badge status-offline">
                    <i class="fa fa-times-circle"></i> <?php echo xlt('Offline'); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($notInstalled): ?>
            <!-- Not Installed - module SQL hasn't run yet -->
            <div class="info-section">
                <p style="text-align: center; margin: 0;">
                    <?php echo xlt('The MedEx module has been registered but not yet installed. Click the Install button in the Module Manager to set up the database tables, then enable the module.'); ?>
                </p>
            </div>
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary" onclick="closeMedexModal()">
                    <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Module Manager'); ?>
                </button>
            </div>
            <script>
            function closeMedexModal() {
                try {
                    var T = window.top;
                    var modal = T.document.getElementById('medexStatusModal');
                    if (modal) modal.remove();
                } catch(e) { window.parent.location.reload(); }
            }
            </script>

        <?php elseif ($medex_enable != '1'): ?>
            <!-- Module Disabled - Show Enable Message -->
            <div class="info-section">
                <p style="text-align: center; margin: 0;">
                    <?php echo xlt('MedEx module is currently disabled. Please enable the module in Module Manager to access MedEx features.'); ?>
                </p>
            </div>
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.parent.location.reload()">
                    <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Module Manager'); ?>
                </button>
            </div>

        <?php elseif (!$isConfigured): ?>
            <!-- Not Registered (enabled) - Show Registration Link -->
            <div class="info-section">
                <p style="text-align: center; margin: 0;">
                    <?php echo xlt('MedEx is enabled but not yet configured. Use the built-in registration flow to connect your practice.'); ?>
                </p>
            </div>
            <div class="action-buttons">
                <a href="<?php echo attr($localRegisterUrl); ?>" class="btn btn-primary">
                    <i class="fa fa-user-plus"></i> <?php echo xlt('Open Registration Setup'); ?>
                </a>
            </div>

        <?php elseif ($updateInfo && $updateInfo['update_available']): ?>
            <!-- Update Available Notification -->
            <?php
            $priorityClass = '';
            $priorityIcon = '';
            $priorityLabel = '';
            $btnClass = 'btn-medex-update';

            switch ($updateInfo['priority']) {
                case 'CRITICAL':
                    $priorityClass = 'update-critical';
                    $priorityIcon = '🚨';
                    $priorityLabel = xlt('CRITICAL SECURITY UPDATE');
                    $btnClass = 'btn-medex-critical-update';
                    break;
                case 'SECURITY':
                    $priorityClass = 'update-security';
                    $priorityIcon = '⚠️';
                    $priorityLabel = xlt('Security Update Available');
                    break;
                case 'IMPORTANT':
                    $priorityClass = 'update-important';
                    $priorityIcon = 'ℹ️';
                    $priorityLabel = xlt('Important Update Available');
                    break;
                default:
                    $priorityClass = 'update-optional';
                    $priorityIcon = '✨';
                    $priorityLabel = xlt('New Version Available');
            }
            ?>
            <div class="update-alert <?php echo $priorityClass; ?>">
                <div class="update-alert-header">
                    <?php echo $priorityIcon; ?> <?php echo $priorityLabel; ?>
                </div>
                <div class="update-alert-body">
                    <div class="info-row">
                        <span class="info-label"><?php echo xlt('Installed'); ?>:</span>
                        <span class="info-value update-version"><?php echo text($updateInfo['current_version']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php echo xlt('Available'); ?>:</span>
                        <span class="info-value update-version"><?php echo text($updateInfo['latest_version']); ?></span>
                    </div>
                    <?php if (!empty($updateInfo['critical_message'])): ?>
                        <div class="info-row">
                            <span style="font-weight: 600; font-size: 13px;"><?php echo text($updateInfo['critical_message']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($updateInfo['changelog'])): ?>
                        <?php
                        $changelog = preg_replace('/^#{1,6}\s*/m', '', $updateInfo['changelog']);
                        $changelog = preg_replace('/^v?\d+\.\d+[^\n]*release notes?[^\n]*/im', '', $changelog);
                        $changelog = trim(preg_replace('/\n{3,}/', "\n", $changelog));
                        ?>
                        <details style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                            <summary style="cursor: pointer; font-size: 13px; font-weight: 600; color: #555; list-style: none; display: flex; align-items: center; gap: 5px;">
                                <i class="fa fa-chevron-right" style="font-size: 10px;"></i> <?php echo xlt("What's New"); ?>
                            </summary>
                            <div style="margin-top: 8px; font-size: 13px; color: #555; line-height: 1.6;">
                                <?php echo nl2br(text($changelog)); ?>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>
            </div>
            <div class="action-buttons">
                <?php if (!empty($updateInfo['download_url'])): ?>
                    <a href="update.php?action=install&url=<?php echo urlencode($updateInfo['download_url']); ?>"
                       class="btn <?php echo $btnClass; ?>"
                       onclick="return confirm('<?php echo xla('This will update the MedEx module. A backup will be created automatically. Continue?'); ?>')">
                        <i class="fa fa-download"></i> <?php echo xlt('Install Update Now'); ?>
                    </a>
                <?php endif; ?>
                <a href="javascript:location.reload()" class="btn btn-secondary">
                    <i class="fa fa-sync"></i> <?php echo xlt('Check Again'); ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($isConfigured && $connectionStatus && $connectionStatus['success'] && !($updateInfo && $updateInfo['update_available'])): ?>
            <!-- Connected Successfully (no pending update) -->
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Practice Name'); ?>:</span>
                    <span class="info-value"><?php echo text($practiceInfo['practice_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Practice ID'); ?>:</span>
                    <span class="info-value"><?php echo text($practiceInfo['practice_id'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Server'); ?>:</span>
                    <span class="info-value"><?php echo text($practiceInfo['server'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="action-buttons">
                <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>" class="btn btn-secondary" onclick="try{window.top.document.getElementById('medexStatusModal').remove();}catch(e){} window.top.location.href=this.href; return false;">
                    <i class="fa fa-tachometer-alt"></i> <?php echo xlt('Open Dashboard'); ?>
                </a>
            </div>

        <?php elseif ($isConfigured && $medex_enable === '1'): ?>
            <!-- Connection Error (Configured, enabled, but offline/error) -->
            <?php if ($errorMessage): ?>
                <div class="error-message">
                    <strong><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Connection Error'); ?>:</strong><br>
                    <?php echo text($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="<?php echo attr($GLOBALS['webroot']); ?>/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>" class="btn btn-secondary" onclick="try{window.top.document.getElementById('medexStatusModal').remove();}catch(e){} window.top.location.href=this.href; return false;">
                    <i class="fa fa-tachometer-alt"></i> <?php echo xlt('Open Dashboard'); ?>
                </a>
                <a href="javascript:location.reload()" class="btn btn-secondary">
                    <i class="fa fa-sync"></i> <?php echo xlt('Retry Connection'); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Help Footer -->
        <div class="help-footer">
            <div class="help-footer-links">
                <a href="<?php echo attr($helpCenterUrl); ?>" class="help-footer-link" onclick="return openProductionReadiness(this.href);" target="_parent">
                    <i class="fa fa-question-circle"></i> <?php echo xlt('Open Help Center'); ?>
                </a>
                <a href="<?php echo attr($tutorialUrl); ?>" class="help-footer-link" target="_blank" rel="noopener">
                    <i class="fa fa-graduation-cap"></i> <?php echo xlt('Interactive Tutorial'); ?>
                </a>
            </div>
        </div>
    </div>
<script>
function openProductionReadiness(url) {
    const msg = 'MedEx onboarding is production-only. Continue to readiness checklist?';
    if (!window.confirm(msg)) {
        return false;
    }
    if (window.top && typeof window.top.restoreSession === 'function') {
        window.top.restoreSession();
    }
    if (window.top && window.top !== window) {
        window.top.location.href = url;
        return false;
    }
    window.location.href = url;
    return false;
}
</script>
</body>
</html>
