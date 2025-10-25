<?php

/**
 * Auto-prepend file for coverage collection during CI testing
 *
 * This file is automatically included before every PHP script execution
 * when enabled in the environment. It supports coverage collection for
 * both E2E (browser-based) and API (HTTP request) tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!getenv('OPENEMR_ENABLE_CI_PHP')) {
    error_log('Tried to run ci/auto_prepend without setting OPENEMR_ENABLE_CI_PHP in the environment.');
    return;
}

const PREPEND_MARKER = '/tmp/openemr-autoprepend-PREPEND_EXECUTED';
const SHUTDOWN_MARKER = '/tmp/openemr-autoprepend-SHUTDOWN_EXECUTED';

const COVERAGE_DIR = '/tmp/openemr-coverage';

// Detect test type based on the request URI or environment
// Priority order: inferno > api > e2e
// Inferno tests are identified by INFERNO_TEST environment variable
// API tests use /apis/ endpoints, E2E tests use other routes
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (getenv('INFERNO_TEST') === 'true') {
    $testType = 'inferno';
} elseif (strpos($requestUri, '/apis/') !== false) {
    $testType = 'api';
} else {
    $testType = 'e2e';
}

define('TEST_TYPE', $testType);
define('COVERAGE_SUBDIR', COVERAGE_DIR . '/' . $testType);
define('COVERAGE_ENABLED', getenv('ENABLE_COVERAGE') === 'true');

// Write marker to prove this file executes (only once)
if (!file_exists(PREPEND_MARKER)) {
    $data = date('Y-m-d H:i:s') . " - prepend executed\n";
    if (file_put_contents(PREPEND_MARKER, $data, LOCK_EX) === false) {
        error_log('CI DEBUG: Failed to write prepend marker to ' . PREPEND_MARKER);
    }
}

if (COVERAGE_ENABLED) {
    if (function_exists('xdebug_start_code_coverage')) {
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    } else {
        error_log("Prepend: Required function xdebug_start_code_coverage is missing");
    }
}



function coverage_shutdown_handler(): void
{
    if (!getenv('OPENEMR_ENABLE_CI_PHP')) {
        error_log('Tried to run shutdown handler without setting OPENEMR_ENABLE_CI_PHP in the environment.');
        return;
    }
    if (!file_exists(SHUTDOWN_MARKER)) {
        $data = date('Y-m-d H:i:s') . " - shutdown executed\n";
        if (file_put_contents(SHUTDOWN_MARKER, $data, LOCK_EX) === false) {
            error_log('CI DEBUG: Failed to write shutdown marker to ' . SHUTDOWN_MARKER);
        }
    }

    // Only collect coverage if enabled
    if (!COVERAGE_ENABLED) {
        return;
    }

    if (!function_exists('xdebug_get_code_coverage')) {
        error_log("Shutdown: Required function xdebug_get_code_coverage is missing");
        return;
    }

    $coverage = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();

    if (!is_dir(COVERAGE_SUBDIR)) {
        mkdir(COVERAGE_SUBDIR, 0777, true);
    }

    // Create unique filename based on test type, request time and random component
    $filename = sprintf(
        '%s/coverage.%s.%s.%s.raw.php',
        COVERAGE_SUBDIR,
        TEST_TYPE,
        date('YmdHis'),
        bin2hex(random_bytes(8))
    );

    // Save the raw Xdebug coverage data directly to avoid memory exhaustion
    // This will be processed later when merging coverage files
    // Format: just the raw array from xdebug_get_code_coverage()
    $exported = var_export($coverage, true);
    file_put_contents($filename, "<?php\nreturn " . $exported . ";\n");
}

register_shutdown_function('coverage_shutdown_handler');
