<?php

/**
 * Auto-prepend file for E2E test coverage collection.
 * This file is automatically included before every PHP script execution
 * when E2E coverage is enabled.
 */

// Write marker to prove this file executes
$marker = '/var/www/localhost/htdocs/openemr/coverage/PREPEND_EXECUTED';
$data = date('Y-m-d H:i:s') . " - prepend executed\n";
if (file_put_contents($marker, $data, FILE_APPEND | LOCK_EX) === false) {
    throw new RuntimeException("COVERAGE DEBUG: Failed to write prepend marker to $marker");
}

if (!function_exists('xdebug_start_code_coverage')) {
    throw new RuntimeException("Prepend: Required function xdebug_get_code_coverage is missing");
}

// Start code coverage with path coverage enabled
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
