<?php
/**
 * MedEx Backup Download Handler
 *
 * Allows admins to download backup files
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

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    die('Access denied');
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_GET['csrf_token'] ?? '', 'default')) {
    die('Invalid CSRF token');
}

$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    die('No file specified');
}

// Security: Only allow files from backup directory, no path traversal
$filename = basename($filename); // Strip any path components
$backupDir = $GLOBALS['OE_SITE_DIR'] . '/documents/medex_backups';
$filepath = $backupDir . '/' . $filename;

// Verify file exists and is in backup directory
if (!file_exists($filepath)) {
    die('Backup file not found');
}

$realPath = realpath($filepath);
$realBackupDir = realpath($backupDir);

if (strpos($realPath, $realBackupDir) !== 0) {
    error_log('[MedEx] Attempted unauthorized backup download: ' . $filename);
    die('Access denied');
}

// Send file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filepath);
exit;
