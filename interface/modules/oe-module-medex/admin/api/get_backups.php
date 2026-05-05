<?php
/**
 * Get Backups Tab Content
 *
 * Returns native HTML for backup management (no iframe)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' . xlt('Access denied') . '</div>';
    exit;
}

// Load dependencies
require_once(__DIR__ . '/../../src/UpdateManager.php');
$updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();

// Get backup data
$backups = $updateManager->getBackups();
$currentVersion = $updateManager->getCurrentVersion();
$hasWritePermissions = $updateManager->hasWritePermissions();
$updateInfo = $updateManager->checkForUpdates();

?>

<style>
/* Backup-specific styles */
.backup-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 968px) {
    .backup-grid {
        grid-template-columns: 1fr;
    }
}

.version-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 16px;
    font-weight: 600;
    font-size: 14px;
    background: #0f4b8f;
    color: white;
    font-family: 'Courier New', monospace;
}

.backup-table {
    width: 100%;
    border-collapse: collapse;
}

.backup-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    font-size: 13px;
    border-bottom: 2px solid #dbe5ee;
}

.backup-table td {
    padding: 12px;
    border-bottom: 1px solid #dbe5ee;
    font-size: 13px;
}

.backup-table tr:hover {
    background: #f8fbff;
}

.backup-actions-cell {
    display: flex;
    gap: 8px;
}

.btn-icon {
    padding: 6px 12px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.update-card {
    background: linear-gradient(135deg, #0f4b8f 0%, #0a3460 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.update-card h4 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.update-card p {
    margin: 0 0 15px 0;
    opacity: 0.95;
}
</style>

<div class="backup-grid">
    <div class="panel">
        <h3><i class="fa fa-info-circle"></i> <?php echo xlt('Current Version'); ?></h3>
        <p style="margin-bottom: 15px;">
            <span class="version-badge">v<?php echo text($currentVersion); ?></span>
        </p>

        <?php if (!$hasWritePermissions): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <?php echo xlt('Write permissions required to create backups or install updates'); ?>
            </div>
        <?php endif; ?>

        <button onclick="createBackup()" class="btn btn-primary" <?php echo !$hasWritePermissions ? 'disabled' : ''; ?>>
            <i class="fa fa-plus"></i> <?php echo xlt('Create Backup Now'); ?>
        </button>
    </div>

    <?php if (!empty($updateInfo['update_available'])): ?>
    <div class="panel">
        <div class="update-card">
            <h4><i class="fa fa-download"></i> <?php echo xlt('Update Available'); ?></h4>
            <p>
                <strong><?php echo xlt('Version'); ?> <?php echo text($updateInfo['latest_version']); ?></strong><br>
                <?php echo text($updateInfo['description'] ?? ''); ?>
            </p>
            <button onclick="installUpdate('<?php echo attr($updateInfo['download_url']); ?>')"
                    class="btn btn-light"
                    <?php echo !$hasWritePermissions ? 'disabled' : ''; ?>>
                <i class="fa fa-download"></i> <?php echo xlt('Install Update'); ?>
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="panel">
    <h3><i class="fa fa-archive"></i> <?php echo xlt('Available Backups'); ?></h3>

    <?php if (empty($backups)): ?>
        <p style="color: #666; text-align: center; padding: 40px 0;">
            <i class="fa fa-inbox" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 15px;"></i>
            <?php echo xlt('No backups found'); ?>
        </p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="backup-table">
                <thead>
                    <tr>
                        <th><?php echo xlt('Version'); ?></th>
                        <th><?php echo xlt('Date'); ?></th>
                        <th><?php echo xlt('Size'); ?></th>
                        <th><?php echo xlt('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td>
                            <span style="font-family: 'Courier New', monospace; font-weight: 600;">
                                v<?php echo text($backup['version']); ?>
                            </span>
                        </td>
                        <td><?php echo text(date('M j, Y g:i A', $backup['date'])); ?></td>
                        <td><?php echo text(round($backup['size'] / 1024 / 1024, 2)); ?> MB</td>
                        <td>
                            <div class="backup-actions-cell">
                                <button onclick="rollbackTo('<?php echo attr($backup['filename']); ?>')"
                                        class="btn btn-sm btn-outline btn-icon"
                                        title="<?php echo xla('Rollback to this version'); ?>">
                                    <i class="fa fa-undo"></i> <?php echo xlt('Rollback'); ?>
                                </button>
                                <button onclick="deleteBackup('<?php echo attr($backup['filename']); ?>')"
                                        class="btn btn-sm btn-danger btn-icon"
                                        title="<?php echo xla('Delete this backup'); ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function createBackup() {
    if (!confirm('<?php echo xla('Create a new backup of the current module version?'); ?>')) {
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo attr(CsrfUtils::collectCsrfToken()); ?>');
    formData.append('action', 'create');

    fetch('../admin/backup_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('Backup created successfully', 'success');
            }
            // Reload backups tab
            if (window.loadTab) {
                window.loadTab('backups');
            }
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error creating backup', 'error');
        }
    });
}

function rollbackTo(filename) {
    if (!confirm('<?php echo xla('Rollback to this version? This will replace current files with the backup.'); ?>')) {
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo attr(CsrfUtils::collectCsrfToken()); ?>');
    formData.append('action', 'rollback');
    formData.append('backup_file', filename);

    fetch('../admin/backup_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('Rollback successful! Please reload the page.', 'success');
            }
            setTimeout(() => location.reload(), 2000);
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error during rollback', 'error');
        }
    });
}

function deleteBackup(filename) {
    if (!confirm('<?php echo xla('Delete this backup? This cannot be undone.'); ?>')) {
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo attr(CsrfUtils::collectCsrfToken()); ?>');
    formData.append('action', 'delete');
    formData.append('backup_file', filename);

    fetch('../admin/backup_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('Backup deleted', 'success');
            }
            // Reload backups tab
            if (window.loadTab) {
                window.loadTab('backups');
            }
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error deleting backup', 'error');
        }
    });
}

function installUpdate(downloadUrl) {
    if (!confirm('<?php echo xla('Install this update? A backup will be created automatically.'); ?>')) {
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo attr(CsrfUtils::collectCsrfToken()); ?>');
    formData.append('action', 'install_update');
    formData.append('download_url', downloadUrl);

    if (window.showToast) {
        window.showToast('Installing update...', 'info');
    }

    fetch('../admin/backup_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('Update installed successfully! Reloading...', 'success');
            }
            setTimeout(() => location.reload(), 2000);
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error installing update', 'error');
        }
    });
}
</script>
