<?php

/**
 * Auto-prepend file for E2E testing
 *
 * This file is automatically included before every PHP script execution.
 * (If enabled in the environment.)
 * Eventually this will be used to manage code coverage.
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

// Write marker to prove this file executes (only once)
$prepend_marker = '/tmp/openemr-e2e-PREPEND_EXECUTED';
if (!file_exists($prepend_marker)) {
    $data = date('Y-m-d H:i:s') . " - prepend executed\n";
    if (file_put_contents($prepend_marker, $data, LOCK_EX) === false) {
        error_log("E2E DEBUG: Failed to write prepend marker to $prepend_marker");
    }
}

function e2e_shutdown_handler(): void
{
    if (!getenv('OPENEMR_E2E_ENABLE_CI_PHP')) {
        error_log('Tried to run e2e shutdown without setting OPENEMR_E2E_ENABLE_CI_PHP in the environment.');
        return;
    }
    $shutdown_marker = '/tmp/openemr-e2e-SHUTDOWN_EXECUTED';
    if (!file_exists($shutdown_marker)) {
        $data = date('Y-m-d H:i:s') . " - shutdown executed\n";
        if (file_put_contents($shutdown_marker, $data, LOCK_EX) === false) {
            error_log("E2E DEBUG: Failed to write shutdown marker to $shutdown_marker");
        }
    }
}

register_shutdown_function('e2e_shutdown_handler');
