<?php
/**
 * MedEx Module Backup Manager
 *
 * View, manage, and rollback from backups
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    die('Access denied. Admin access required.');
}

// Load dependencies
require_once(__DIR__ . '/../src/MedExAPI.php');
require_once(__DIR__ . '/../src/UpdateManager.php');
$updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '', 'default')) {
        die('Invalid CSRF token');
    }

    switch ($action) {
        case 'rollback':
            $backupFile = $_POST['backup_file'] ?? '';
            $result = $updateManager->rollback($backupFile, true);

            if ($result['success']) {
                $messageType = 'success';
                $message = xlt('Successfully rolled back to version') . ' ' . text($result['rolled_back_version']);
            } else {
                $messageType = 'error';
                $message = xlt('Rollback failed') . ': ' . text($result['error']);
            }
            break;

        case 'delete':
            $backupFile = $_POST['backup_file'] ?? '';
            if ($updateManager->deleteBackup($backupFile)) {
                $messageType = 'success';
                $message = xlt('Backup deleted successfully');
            } else {
                $messageType = 'error';
                $message = xlt('Failed to delete backup');
            }
            break;

        case 'create':
            $result = $updateManager->createBackup();
            if ($result['success']) {
                $messageType = 'success';
                $message = xlt('Backup created successfully') . ' (' . round($result['backup_size'] / 1024 / 1024, 2) . ' MB)';
            } else {
                $messageType = 'error';
                $message = xlt('Backup failed') . ': ' . text($result['error']);
            }
            break;

        case 'install_update':
            $downloadUrl = $_POST['download_url'] ?? '';
            if (empty($downloadUrl)) {
                $messageType = 'error';
                $message = xlt('No download URL provided');
                break;
            }

            $result = $updateManager->installUpdate($downloadUrl, true);
            if ($result['success']) {
                $messageType = 'success';
                $message = xlt('Update installed successfully') . '! ' . xlt('Version') . ': ' . text($result['new_version']);
                if (!empty($result['requires_reload'])) {
                    $message .= ' - ' . xlt('Please reload the page');
                }
            } else {
                $messageType = 'error';
                $message = xlt('Update failed') . ': ' . text($result['error']);
            }
            break;
    }
}

// Get list of backups
$backups = $updateManager->getBackups();
$currentVersion = $updateManager->getCurrentVersion();
$hasWritePermissions = $updateManager->hasWritePermissions();

// Check for updates
$updateInfo = $updateManager->checkForUpdates();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Backup Manager'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
            background: #f8f9fa;
            margin: 0;
        }
        .backup-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .backup-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .backup-header h1 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 28px;
        }
        .current-version {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 14px;
            background: #667eea;
            color: white;
            font-family: 'Courier New', monospace;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .backup-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
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
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .backups-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .backups-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .backups-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .backups-table tr:hover {
            background: #f8f9fa;
        }
        .version-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
            background: #e2e8f0;
            color: #475569;
            font-family: 'Courier New', monospace;
        }
        .current-badge {
            background: #10b981;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            padding: 30px;
        }
        .modal-header {
            margin-bottom: 20px;
        }
        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }
        .modal-body {
            margin: 20px 0;
            color: #666;
        }
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="backup-container" style="padding: 20px 30px;">
        <div style="margin-bottom: 15px;">
            <?php echo xlt('Current Version'); ?>: <span class="current-version">v<?php echo text($currentVersion); ?></span>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo attr($messageType); ?>">
                <i class="fa fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!$hasWritePermissions): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <div>
                    <strong><?php echo xlt('Insufficient Permissions'); ?></strong><br>
                    <?php echo xlt('The module directory is not writable. Rollback operations will fail.'); ?><br>
                    <small><?php echo xlt('Directory'); ?>: <code><?php echo text($updateManager->getModuleDir()); ?></code></small>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($updateInfo && $updateInfo['update_available']): ?>
            <?php
            $alertClass = 'alert-info';
            $icon = 'fa-info-circle';
            if ($updateInfo['priority'] === 'CRITICAL') {
                $alertClass = 'alert-danger';
                $icon = 'fa-exclamation-circle';
            } elseif ($updateInfo['priority'] === 'SECURITY') {
                $alertClass = 'alert-warning';
                $icon = 'fa-shield-alt';
            } elseif ($updateInfo['priority'] === 'IMPORTANT') {
                $alertClass = 'alert-warning';
                $icon = 'fa-exclamation-triangle';
            }
            ?>
            <div class="alert <?php echo attr($alertClass); ?>" style="margin-bottom: 20px;">
                <i class="fa <?php echo attr($icon); ?>" style="font-size: 20px; vertical-align: middle;"></i>
                <strong style="font-size: 16px;"><?php echo xlt('Update Available'); ?>: v<?php echo text($updateInfo['latest_version']); ?></strong>
                <span style="float: right; font-size: 12px; opacity: 0.8;"><?php echo xlt('Current'); ?>: v<?php echo text($updateInfo['current_version']); ?> | <?php echo xlt('Priority'); ?>: <?php echo text($updateInfo['priority']); ?></span>
                <br>
                <?php if (!empty($updateInfo['critical_message'])): ?>
                    <div style="margin-top: 12px; padding: 12px; background: rgba(255,255,255,0.4); border-radius: 6px; font-weight: 600;">
                        <?php echo text($updateInfo['critical_message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($updateInfo['changelog'])): ?>
                    <div style="margin-top: 10px; font-size: 13px; line-height: 1.6;">
                        <strong><?php echo xlt('Changelog'); ?>:</strong><br>
                        <?php echo nl2br(text($updateInfo['changelog'])); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($updateInfo['download_url'])): ?>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary" onclick="if(confirm('<?php echo xla('Create backup before updating?'); ?>')) { document.getElementById('update-form').submit(); }">
                            <i class="fa fa-download"></i> <?php echo xlt('Install Update Now'); ?>
                        </button>
                        <small style="margin-left: 10px; opacity: 0.7;">
                            <?php if (!empty($updateInfo['release_date'])): ?>
                                <?php echo xlt('Released'); ?>: <?php echo text(date('M d, Y', strtotime($updateInfo['release_date']))); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <form id="update-form" method="post" style="display: none;">
                        <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                        <input type="hidden" name="action" value="install_update">
                        <input type="hidden" name="download_url" value="<?php echo attr($updateInfo['download_url']); ?>">
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="backup-actions">
            <div>
                <strong><?php echo xlt('Backups'); ?>:</strong> <?php echo count($backups); ?> <?php echo xlt('available'); ?>
                <?php if (!empty($backups)): ?>
                    | <?php echo xlt('Total Size'); ?>: <?php echo text(array_sum(array_column($backups, 'size_mb'))); ?> MB
                <?php endif; ?>
            </div>
            <div>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                    <input type="hidden" name="action" value="create">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> <?php echo xlt('Create Backup Now'); ?>
                    </button>
                </form>
                <a href="../public/status.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Status'); ?>
                </a>
            </div>
        </div>

        <?php if (empty($backups)): ?>
            <div class="empty-state">
                <i class="fa fa-archive"></i>
                <h3><?php echo xlt('No Backups Found'); ?></h3>
                <p><?php echo xlt('Backups are created automatically before each update.'); ?><br>
                <?php echo xlt('You can also create manual backups using the button above.'); ?></p>
            </div>
        <?php else: ?>
            <table class="backups-table">
                <thead>
                    <tr>
                        <th><?php echo xlt('Version'); ?></th>
                        <th><?php echo xlt('Date Created'); ?></th>
                        <th><?php echo xlt('Size'); ?></th>
                        <th><?php echo xlt('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td>
                                <span class="version-badge <?php echo $backup['version'] === $currentVersion ? 'current-badge' : ''; ?>">
                                    v<?php echo text($backup['version']); ?>
                                </span>
                                <?php if ($backup['version'] === $currentVersion): ?>
                                    <small style="color: #10b981; font-weight: 600; margin-left: 8px;">
                                        <i class="fa fa-check-circle"></i> <?php echo xlt('Current'); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fa fa-clock"></i> <?php echo text(date('F j, Y g:i A', $backup['date'])); ?>
                            </td>
                            <td>
                                <?php echo text($backup['size_mb']); ?> MB
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($backup['version'] !== $currentVersion && $hasWritePermissions): ?>
                                        <button type="button" class="btn btn-primary btn-sm"
                                                onclick="showRollbackModal('<?php echo attr($backup['file']); ?>', '<?php echo attr($backup['version']); ?>')">
                                            <i class="fa fa-undo"></i> <?php echo xlt('Rollback'); ?>
                                        </button>
                                    <?php endif; ?>
                                    <a href="download_backup.php?file=<?php echo urlencode(basename($backup['file'])); ?>&csrf_token=<?php echo urlencode(CsrfUtils::collectCsrfToken()); ?>"
                                       class="btn btn-secondary btn-sm">
                                        <i class="fa fa-download"></i> <?php echo xlt('Download'); ?>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="showDeleteModal('<?php echo attr($backup['file']); ?>', '<?php echo attr($backup['version']); ?>')">
                                        <i class="fa fa-trash"></i> <?php echo xlt('Delete'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <div>
                <strong><?php echo xlt('About Backups'); ?>:</strong><br>
                <?php echo xlt('Backups are automatically created before every update. They contain a complete copy of the module files at that version.'); ?>
                <?php echo xlt('You can rollback to any previous version if issues occur after an update.'); ?>
                <?php echo xlt('Rolling back will backup the current version first, so you can always go forward again.'); ?>
            </div>
        </div>
    </div>

    <!-- Rollback Confirmation Modal -->
    <div id="rollbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa fa-undo"></i> <?php echo xlt('Confirm Rollback'); ?></h3>
            </div>
            <div class="modal-body">
                <p><?php echo xlt('Are you sure you want to rollback to version'); ?> <strong id="rollbackVersion"></strong>?</p>
                <p><?php echo xlt('This will:'); ?></p>
                <ul>
                    <li><?php echo xlt('Backup the current version first'); ?></li>
                    <li><?php echo xlt('Replace all module files with the selected backup'); ?></li>
                    <li><?php echo xlt('Clear the update cache'); ?></li>
                </ul>
                <p style="color: #f59e0b; font-weight: 600;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?php echo xlt('Note: Database changes from newer versions are NOT rolled back automatically.'); ?>
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" id="rollbackForm">
                    <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                    <input type="hidden" name="action" value="rollback">
                    <input type="hidden" name="backup_file" id="rollbackFile">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('rollbackModal')">
                        <?php echo xlt('Cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-undo"></i> <?php echo xlt('Rollback Now'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa fa-trash"></i> <?php echo xlt('Confirm Delete'); ?></h3>
            </div>
            <div class="modal-body">
                <p><?php echo xlt('Are you sure you want to delete the backup for version'); ?> <strong id="deleteVersion"></strong>?</p>
                <p style="color: #f59e0b; font-weight: 600;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?php echo xlt('This action cannot be undone.'); ?>
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="backup_file" id="deleteFile">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">
                        <?php echo xlt('Cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> <?php echo xlt('Delete Backup'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function showRollbackModal(file, version) {
        document.getElementById('rollbackFile').value = file;
        document.getElementById('rollbackVersion').textContent = 'v' + version;
        document.getElementById('rollbackModal').classList.add('show');
    }

    function showDeleteModal(file, version) {
        document.getElementById('deleteFile').value = file;
        document.getElementById('deleteVersion').textContent = 'v' + version;
        document.getElementById('deleteModal').classList.add('show');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    </script>
</body>
</html>
