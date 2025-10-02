<?php

/**
 * Global Front Controller for OpenEMR.
 *
 * Routes requests to appropriate PHP files, blocks .inc.php access,
 * preserves multisite selection. Target files handle auth/sessions/errors.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2025 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// CLI not supported
if (php_sapi_name() === 'cli') {
    http_response_code(404);
    exit('Front controller is web-only. Use direct file access for CLI scripts.');
}

// Load .env variables
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

// Check feature flag
if (!getenv('OPENEMR_ENABLE_FRONT_CONTROLLER') || getenv('OPENEMR_ENABLE_FRONT_CONTROLLER') !== '1') {
    http_response_code(404);
    exit('Front controller disabled');
}

// Early extension hook
if (file_exists(__DIR__ . '/custom/front_controller_early.php')) {
    require __DIR__ . '/custom/front_controller_early.php';
}

// Multisite selection
$site_id = '';
if (!empty($_GET['site'])) {
    $site_id = $_GET['site'];
} elseif (is_dir("sites/" . ($_SERVER['HTTP_HOST'] ?? 'default'))) {
    $site_id = ($_SERVER['HTTP_HOST'] ?? 'default');
} else {
    $site_id = 'default';
}
$_GET['site'] = $site_id;

// Extract route
$route = $_GET['_ROUTE'] ?? '';
if (($pos = strpos($route, '?')) !== false) {
    $route = substr($route, 0, $pos);
}

// Remove trailing slash (redirect)
if ($route !== '' && substr($route, -1) === '/') {
    header('Location: ' . rtrim($route, '/'), true, 301);
    exit;
}

// Deny always-forbidden paths
if (
    preg_match('#^portal/patient/fwk/libs/#', $route) ||
    preg_match('#^sites/[^/]+/documents/#', $route)
) {
    http_response_code(404);
    error_log("OpenEMR Front Controller: Denied path: $route");
    exit('Not Found');
}

// Admin-only paths (require authentication check in target file)
if (preg_match('#^(admin|setup|rector|phpstan_panther_alias|acl_setup|acl_upgrade|sl_convert|sql_upgrade|gacl/setup|ippf_upgrade|sql_patch)\.php$#', $route)) {
    // Target file must verify admin access
    $_SERVER['REQUIRE_ADMIN'] = true;
}

// Block .inc.php files
if (preg_match('/\.inc\.php$/i', $route)) {
    http_response_code(403);
    error_log("OpenEMR Front Controller: Blocked .inc.php access: $route");
    exit(getenv('OPENEMR_FC_LOG_LEVEL') === 'debug' ?
        "Access Denied: Include files cannot be accessed directly" : 'Access Denied');
}

// Resolve target file
$targetFile = realpath(__DIR__ . '/' . $route);
$baseDir = realpath(__DIR__);

// Prevent path traversal
if ($targetFile === false || strpos($targetFile, $baseDir) !== 0) {
    http_response_code(404);
    error_log("OpenEMR Front Controller: Invalid path: $route");
    exit('Not Found');
}

// Verify file exists
if (!file_exists($targetFile) || !is_file($targetFile)) {
    http_response_code(404);
    error_log("OpenEMR Front Controller: File not found: $route");
    exit('Not Found');
}

// Only route .php files
if (pathinfo($targetFile, PATHINFO_EXTENSION) !== 'php') {
    http_response_code(404);
    error_log("OpenEMR Front Controller: Non-PHP file: $route");
    exit('Not Found');
}

// Late extension hook
if (file_exists(__DIR__ . '/custom/front_controller_late.php')) {
    require __DIR__ . '/custom/front_controller_late.php';
}

// Route to target file
require $targetFile;
