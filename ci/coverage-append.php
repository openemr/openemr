<?php

/**
 * Auto-append file for E2E test coverage collection.
 * This file is automatically included after every PHP script execution
 * when E2E coverage is enabled.
 */

if (!function_exists('xdebug_get_code_coverage')) {
    return;
}

$coverage = xdebug_get_code_coverage();
xdebug_stop_code_coverage();

if (empty($coverage)) {
    return;
}

// Create unique filename based on request time and random component
$coverageDir = '/var/www/localhost/htdocs/openemr/coverage/e2e';
if (!is_dir($coverageDir)) {
    @mkdir($coverageDir, 0777, true);
}

$filename = sprintf(
    '%s/coverage.e2e.%s.%s.cov',
    $coverageDir,
    date('YmdHis'),
    bin2hex(random_bytes(8))
);

// Serialize and save coverage data
file_put_contents($filename, serialize($coverage));
