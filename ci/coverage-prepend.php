<?php

/**
 * Auto-prepend file for E2E test coverage collection.
 * This file is automatically included before every PHP script execution
 * when E2E coverage is enabled.
 */

// Write marker to prove this file executes
@file_put_contents('/var/www/localhost/htdocs/openemr/coverage/PREPEND_EXECUTED', date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if (!function_exists('xdebug_start_code_coverage')) {
    return;
}

// Start code coverage with path coverage enabled
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
