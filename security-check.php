<?php

/**
 * Security check for .inc.php files.
 *
 * Auto-loaded before any PHP execution via .user.ini.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   GPLv3
 */

$requestedFile = $_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['PHP_SELF'] ?? '';

if (preg_match('/\.inc\.php$/i', $requestedFile)) {
    http_response_code(403);
    // Sanitize log output to prevent log injection attacks
    $sanitizedFile = preg_replace('/[\x00-\x1F\x7F]/', '', $requestedFile);
    error_log("OpenEMR Security: Blocked .inc.php access: " . $sanitizedFile);
    exit('Access Denied: Include files cannot be accessed directly');
}
