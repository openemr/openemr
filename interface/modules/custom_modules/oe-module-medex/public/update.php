<?php
/**
 * MedEx Module Update Handler
 *
 * Handles module update installation requests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    die('Access denied. Admin access required.');
}

// Load UpdateManager
require_once(__DIR__ . '/../src/UpdateManager.php');
$updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();

$action = $_GET['action'] ?? '';
$downloadUrl = $_GET['url'] ?? '';
$isReconcileRequest = $action === 'install' && $downloadUrl === '';

// Handle AJAX installation request
if ($action === 'install' && (!empty($downloadUrl) || $isReconcileRequest)) {
    // Verify CSRF token for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '', 'default')) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Invalid CSRF token']));
        }
    }

    // Start update installation or package reconcile
    $result = $isReconcileRequest
        ? $updateManager->reconcilePackages(true, true)
        : $updateManager->installUpdate($downloadUrl, true);

    // Return JSON response if AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // Redirect back to status page with result
    $status = $result['success'] ? 'success' : 'error';
    $message = urlencode($result['message'] ?? $result['error'] ?? 'Update completed');
    header('Location: status.php?update_status=' . $status . '&message=' . $message);
    exit;
}

// Check for updates
$updateInfo = $updateManager->checkForUpdates(true); // Force fresh check
$hasWritePermissions = $updateManager->hasWritePermissions();
$reconcilePlan = is_array($updateInfo['reconcile_plan'] ?? null) ? $updateInfo['reconcile_plan'] : null;
$hasPackageDrift = !empty($reconcilePlan['requires_reconcile']);
$installActionUrl = 'update.php?action=install';
if (!empty($updateInfo['download_url']) && !$hasPackageDrift) {
    $installActionUrl .= '&url=' . urlencode((string)$updateInfo['download_url']);
}
$installButtonLabel = $hasPackageDrift ? xlt('Apply Package Changes') : xlt('Install Update Now');
$installConfirmText = $hasPackageDrift
    ? xlj('This will reconcile subscribed MedEx packages for this installation. A backup will be created automatically. Continue?')
    : xlj('This will update the MedEx module. A backup will be created automatically. Continue?');
$installVerb = $hasPackageDrift ? xlj('Reconciling...') : xlj('Installing...');
$installSuccessLabel = $hasPackageDrift ? xlj('Reconcile Complete') : xlj('Update Complete');
$installFailureLabel = $hasPackageDrift ? xlj('Reconcile Failed') : xlj('Update Failed');
$installStartLog = $hasPackageDrift ? xlj('Starting package reconcile...') : xlj('Starting update installation...');
$installBackupLog = $hasPackageDrift ? xlj('Creating backup before package reconcile...') : xlj('Creating backup of current version...');

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Module Updates'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
            background: #f8f9fa;
            margin: 0;
        }
        .update-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .update-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        .update-header h1 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 28px;
        }
        .version-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 14px;
            background: #e2e8f0;
            color: #475569;
            font-family: 'Courier New', monospace;
        }
        .warning-box {
            background: #fffbeb;
            border: 2px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box strong {
            color: #92400e;
        }
        .success-box {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #065f46;
        }
        .error-box {
            background: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #7f1d1d;
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
            padding: 10px 0;
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
            text-align: right;
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
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
            display: none;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }
        .progress-fill.failed {
            background: #ef4444;
        }
        #installLog {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin: 20px 0;
            display: none;
        }
        #installLog div {
            margin: 5px 0;
        }
        #installGuide {
            display: none;
        }
        .install-step-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .install-step-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            color: #334155;
            font-size: 13px;
        }
        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #cbd5e1;
            flex: 0 0 10px;
        }
        .install-step-list li.ok .step-dot {
            background: #16a34a;
        }
        .install-step-list li.failed .step-dot {
            background: #ef4444;
        }
        .install-step-list li.pending .step-dot {
            background: #cbd5e1;
        }
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="update-container">
        <div class="update-header">
            <h1><i class="fa fa-sync-alt"></i> <?php echo xlt('MedEx Module Updates'); ?></h1>
            <div style="margin-top: 10px;">
                <span class="version-badge">v<?php echo text(\OpenEMR\Modules\MedEx\UpdateManager::CURRENT_VERSION); ?></span>
            </div>
        </div>

        <?php if (!$hasWritePermissions): ?>
            <div class="error-box">
                <strong><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Insufficient Permissions'); ?></strong><br>
                <?php echo xlt('The module directory is not writable. Updates cannot be installed automatically.'); ?><br>
                <br>
                <strong><?php echo xlt('Directory'); ?>:</strong> <code><?php echo text($updateManager->getModuleDir()); ?></code><br>
                <br>
                <?php echo xlt('Please contact your system administrator or manually update the module files.'); ?>
            </div>
        <?php endif; ?>

        <?php if ($updateInfo && $updateInfo['update_available']): ?>
            <!-- Update Available -->
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Current Version'); ?>:</span>
                    <span class="info-value"><code><?php echo text($updateInfo['current_version']); ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Latest Version'); ?>:</span>
                    <span class="info-value"><code><?php echo text($updateInfo['latest_version']); ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Priority'); ?>:</span>
                    <span class="info-value"><strong><?php echo text($updateInfo['priority']); ?></strong></span>
                </div>
                <?php if (!empty($updateInfo['release_date'])): ?>
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Release Date'); ?>:</span>
                    <span class="info-value"><?php echo text(date('F j, Y', strtotime($updateInfo['release_date']))); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($hasPackageDrift): ?>
                <div class="info-row">
                    <span class="info-label"><?php echo xlt('Package Changes'); ?>:</span>
                    <span class="info-value">
                        <?php
                        echo text(sprintf(
                            'Install %d, Update %d, Remove %d',
                            (int)($reconcilePlan['counts']['install'] ?? 0),
                            (int)($reconcilePlan['counts']['update'] ?? 0),
                            (int)($reconcilePlan['counts']['remove'] ?? 0)
                        ));
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($updateInfo['changelog'])): ?>
                <div class="info-section">
                    <h3 style="margin-top: 0;"><?php echo xlt('What\'s New'); ?></h3>
                    <?php echo nl2br(text($updateInfo['changelog'])); ?>
                </div>
            <?php endif; ?>

            <?php if ($updateInfo['requires_manual_steps']): ?>
                <div class="warning-box">
                    <strong><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Manual Steps Required'); ?></strong><br>
                    <?php echo nl2br(text($updateInfo['manual_steps'])); ?>
                </div>
            <?php endif; ?>

            <div class="progress-bar" id="progressBar">
                <div class="progress-fill" id="progressFill">0%</div>
            </div>

            <div id="installGuide" class="info-section">
                <h3 style="margin-top: 0;"><?php echo xlt('Update Progress'); ?></h3>
                <ul class="install-step-list" id="installStepList">
                    <li data-step="backup" class="pending"><span class="step-dot"></span><span><?php echo xlt('Backup created'); ?></span></li>
                    <li data-step="download" class="pending"><span class="step-dot"></span><span><?php echo xlt('Update package downloaded'); ?></span></li>
                    <li data-step="verify" class="pending"><span class="step-dot"></span><span><?php echo xlt('Update package verified'); ?></span></li>
                    <li data-step="install" class="pending"><span class="step-dot"></span><span><?php echo xlt('Files installed'); ?></span></li>
                    <li data-step="migrate" class="pending"><span class="step-dot"></span><span><?php echo xlt('Database migrations completed'); ?></span></li>
                    <li data-step="cache" class="pending"><span class="step-dot"></span><span><?php echo xlt('Update cache cleared'); ?></span></li>
                </ul>
            </div>

            <div id="installLog"></div>

            <div class="action-buttons">
                <?php if ($hasWritePermissions && (!empty($updateInfo['download_url']) || $hasPackageDrift)): ?>
                    <button type="button" class="btn btn-success" id="installBtn" onclick="startInstallation()">
                        <i class="fa fa-download"></i> <?php echo text($installButtonLabel); ?>
                    </button>
                <?php endif; ?>
                <a href="status.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Status'); ?>
                </a>
            </div>

        <?php elseif ($updateInfo): ?>
            <!-- No Updates Available -->
            <div class="success-box">
                <strong><i class="fa fa-check-circle"></i> <?php echo xlt('You\'re Up to Date!'); ?></strong><br>
                <?php echo xlt('MedEx module is running the latest version.'); ?>
            </div>
            <div class="action-buttons">
                <a href="status.php" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Status'); ?>
                </a>
                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                    <i class="fa fa-sync"></i> <?php echo xlt('Check Again'); ?>
                </button>
            </div>

        <?php else: ?>
            <!-- Unable to Check for Updates -->
            <div class="error-box">
                <strong><i class="fa fa-times-circle"></i> <?php echo xlt('Unable to Check for Updates'); ?></strong><br>
                <?php
                $error = $updateManager->getLastError();
                echo $error ? text($error) : xlt('Could not connect to MedEx update server.');
                ?>
            </div>
            <div class="action-buttons">
                <a href="status.php" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Status'); ?>
                </a>
                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                    <i class="fa fa-sync"></i> <?php echo xlt('Retry'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function startInstallation() {
        const btn = document.getElementById('installBtn');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const installLog = document.getElementById('installLog');
        const installGuide = document.getElementById('installGuide');
        const stepItems = Array.from(document.querySelectorAll('#installStepList li[data-step]'));

        if (!confirm(<?php echo $installConfirmText; ?>)) {
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + <?php echo $installVerb; ?>;
        progressBar.style.display = 'block';
        installGuide.style.display = 'block';
        installLog.style.display = 'block';
        stepItems.forEach((li) => {
            li.classList.remove('ok', 'failed');
            li.classList.add('pending');
        });

        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 5;
            if (progress <= 90) {
                progressFill.style.width = progress + '%';
                progressFill.textContent = progress + '%';
            }
        }, 500);

        // Log messages
        function addLog(message) {
            const div = document.createElement('div');
            div.textContent = '[' + new Date().toLocaleTimeString() + '] ' + message;
            installLog.appendChild(div);
            installLog.scrollTop = installLog.scrollHeight;
        }

        addLog(<?php echo $installStartLog; ?>);
        addLog(<?php echo $installBackupLog; ?>);

        function setProgressFailed() {
            progressFill.style.width = '100%';
            progressFill.textContent = <?php echo $installFailureLabel; ?>;
            progressFill.classList.add('failed');
        }

        function markStep(stepKey, status) {
            const el = stepItems.find((li) => li.dataset.step === stepKey);
            if (!el) {
                return;
            }
            el.classList.remove('pending', 'ok', 'failed');
            el.classList.add(status);
        }

        function applyServerSteps(data) {
            if (Array.isArray(data.steps)) {
                data.steps.forEach((step) => {
                    if (step && step.key) {
                        markStep(step.key, 'ok');
                    }
                });
            }
            if (!data.success) {
                if (data.failed_step) {
                    const mapped = ['preflight', 'exception'].includes(data.failed_step) ? 'install' : data.failed_step;
                    markStep(mapped, 'failed');
                } else {
                    markStep('install', 'failed');
                }
            }
        }

        // Make AJAX request to install
        fetch('<?php echo attr($installActionUrl); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf_token=<?php echo urlencode(CsrfUtils::collectCsrfToken()); ?>'
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);

            if (data.success) {
                applyServerSteps(data);
                progressFill.style.width = '100%';
                progressFill.textContent = '100%';
                progressFill.classList.remove('failed');
                addLog(<?php echo xlj('Operation completed successfully.'); ?>);
                if (data.new_version) {
                    addLog('New version: ' + data.new_version);
                }
                if (data.backup_file) {
                    addLog('Backup saved: ' + data.backup_file);
                }
                btn.innerHTML = '<i class="fa fa-check"></i> ' + <?php echo $installSuccessLabel; ?>;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');

                setTimeout(() => {
                    window.location.href = 'status.php?update_status=success';
                }, 2000);
            } else {
                applyServerSteps(data);
                addLog('✗ ' + (data.error || 'Unknown error'));
                btn.innerHTML = '<i class="fa fa-times"></i> ' + <?php echo $installFailureLabel; ?>;
                btn.disabled = false;
                setProgressFailed();
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            markStep('install', 'failed');
            addLog('✗ Installation error: ' + error.message);
            btn.innerHTML = '<i class="fa fa-times"></i> ' + <?php echo $installFailureLabel; ?>;
            btn.disabled = false;
            setProgressFailed();
        });
    }
    </script>
</body>
</html>
