<?php
/**
 * Backup Actions Handler
 *
 * Handles backup operations: create, rollback, delete, install update
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '', 'default')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Load dependencies
require_once(__DIR__ . '/../src/UpdateManager.php');
$updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();

// Helper function to resolve backup filename to full path
function resolveBackupPath(string $filename): string {
    $backupDir = $GLOBALS['OE_SITE_DIR'] . '/documents/medex_backups';
    // Security: only allow .zip files and sanitize the filename
    $filename = basename($filename);
    if (!preg_match('/^medex_v[\d.]+_[\d\-_]+\.zip$/', $filename)) {
        return '';
    }
    return $backupDir . '/' . $filename;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        $result = $updateManager->createBackup();
        echo json_encode($result);
        break;

    case 'rollback':
        $backupFile = resolveBackupPath($_POST['backup_file'] ?? '');
        if (empty($backupFile)) {
            echo json_encode(['success' => false, 'error' => 'Invalid backup filename']);
            break;
        }
        $result = $updateManager->rollback($backupFile, true);
        echo json_encode($result);
        break;

    case 'delete':
        $backupFile = resolveBackupPath($_POST['backup_file'] ?? '');
        if (empty($backupFile)) {
            echo json_encode(['success' => false, 'error' => 'Invalid backup filename']);
            break;
        }
        $success = $updateManager->deleteBackup($backupFile);
        echo json_encode(['success' => $success]);
        break;

    case 'install_update':
        $downloadUrl = $_POST['download_url'] ?? '';
        if (empty($downloadUrl)) {
            echo json_encode(['success' => false, 'error' => 'No download URL provided']);
            break;
        }
        $result = $updateManager->installUpdate($downloadUrl, true);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
