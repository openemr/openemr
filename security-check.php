<?php

/**
 * Security check for include/config files and sensitive directories.
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

// Block .inc.php and .config.php files, and any PHP under /includes/ or /inc/
if (preg_match('/\.(?:inc|config)\.php$/i', $requestedFile) || preg_match('#/(?:includes?|inc)/#i', $requestedFile)) {
    http_response_code(403);
    // Sanitize: remove control chars, limit length, preserve only filename
    $sanitizedFile = basename(preg_replace('/[\x00-\x1F\x7F]/', '', $requestedFile));
    $sanitizedFile = substr($sanitizedFile, 0, 255); // Limit length
    error_log("OpenEMR Security: Blocked .inc.php access attempt: " . $sanitizedFile);
    exit('Access Denied');
}
