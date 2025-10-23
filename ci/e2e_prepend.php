<?php

/**
 * Auto-prepend file for E2E testing
 *
 * This file is automatically included before every PHP script execution.
 * (If enabled in the environment.)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!getenv('OPENEMR_E2E_ENABLE_CI_PHP')) {
    error_log('Tried to run ci/e2e_prepend without setting OPENEMR_E2E_ENABLE_CI_PHP in the environment.');
    return;
}

const PREPEND_MARKER = '/tmp/openemr-e2e-PREPEND_EXECUTED';
const SHUTDOWN_MARKER = '/tmp/openemr-e2e-SHUTDOWN_EXECUTED';

const COVERAGE_DIR = '/tmp/openemr-coverage';
define('E2E_COVERAGE_DIR', COVERAGE_DIR . '/e2e');
define('E2E_COVERAGE_ENABLED', getenv('ENABLE_COVERAGE') === 'true');

// Write marker to prove this file executes (only once)
if (!file_exists(PREPEND_MARKER)) {
    $data = date('Y-m-d H:i:s') . " - prepend executed\n";
    if (file_put_contents(PREPEND_MARKER, $data, LOCK_EX) === false) {
        error_log('E2E DEBUG: Failed to write prepend marker to ' . PREPEND_MARKER);
    }
}

if (E2E_COVERAGE_ENABLED) {
    if (function_exists('xdebug_start_code_coverage')) {
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    } else {
        error_log("Prepend: Required function xdebug_start_code_coverage is missing");
    }
}



function e2e_shutdown_handler(): void
{
    if (!getenv('OPENEMR_E2E_ENABLE_CI_PHP')) {
        error_log('Tried to run e2e shutdown without setting OPENEMR_E2E_ENABLE_CI_PHP in the environment.');
        return;
    }
    if (!file_exists(SHUTDOWN_MARKER)) {
        $data = date('Y-m-d H:i:s') . " - shutdown executed\n";
        if (file_put_contents(SHUTDOWN_MARKER, $data, LOCK_EX) === false) {
            error_log('E2E DEBUG: Failed to write shutdown marker to ' . SHUTDOWN_MARKER);
        }
    }

    // Only collect coverage if enabled
    if (!E2E_COVERAGE_ENABLED) {
        return;
    }

    if (!function_exists('xdebug_get_code_coverage')) {
        error_log("Append: Required function xdebug_get_code_coverage is missing");
        return;
    }

    $coverage = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();

    if (!is_dir(E2E_COVERAGE_DIR)) {
        mkdir(E2E_COVERAGE_DIR, 0777, true);
    }

    // Create unique filename based on request time and random component
    $filename = sprintf(
        '%s/coverage.e2e.%s.%s.raw.php',
        E2E_COVERAGE_DIR,
        date('YmdHis'),
        bin2hex(random_bytes(8))
    );

    // Save the raw Xdebug coverage data directly to avoid memory exhaustion
    // This will be processed later when merging coverage files
    // Format: just the raw array from xdebug_get_code_coverage()
    $exported = var_export($coverage, true);
    file_put_contents($filename, "<?php\nreturn " . $exported . ";\n");
}

register_shutdown_function('e2e_shutdown_handler');
