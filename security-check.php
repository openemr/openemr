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
    error_log("OpenEMR Security: Blocked .inc.php access: " . $requestedFile);
    exit('Access Denied: Include files cannot be accessed directly');
}
