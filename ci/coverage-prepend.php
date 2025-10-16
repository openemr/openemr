<?php

/**
 * Auto-prepend file for E2E test coverage collection.
 * This file is automatically included before every PHP script execution
 * when E2E coverage is enabled.
 */

if (!function_exists('xdebug_start_code_coverage')) {
    return;
}

// Start code coverage with path coverage enabled
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
