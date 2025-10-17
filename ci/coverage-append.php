<?php

/**
 * Auto-append file for E2E test coverage collection.
 * This file is automatically included after every PHP script execution
 * when E2E coverage is enabled.
 */

define('WEBROOT', dirname(__DIR__));
define('COVERAGE_DIR', WEBROOT . '/coverage');
define('E2E_COVERAGE_DIR', COVERAGE_DIR . '/e2e');

// Write marker to prove this file executes
$marker = COVERAGE_DIR . '/APPEND_EXECUTED';
$data = date('Y-m-d H:i:s') . " - append executed\n";
if (file_put_contents($marker, $data, FILE_APPEND | LOCK_EX) === false) {
    throw new RuntimeException("COVERAGE DEBUG: Failed to write append marker to $marker");
}

if (!function_exists('xdebug_get_code_coverage')) {
    throw new RuntimeException("Append: Required function xdebug_get_code_coverage is missing");
}

$coverage = xdebug_get_code_coverage();
xdebug_stop_code_coverage();

if (empty($coverage)) {
    throw new RuntimeException("Coverage is unexpectedly empty");
    return;
}

// Create unique filename based on request time and random component
if (!is_dir(E2E_COVERAGE_DIR)) {
    @mkdir(E2E_COVERAGE_DIR, 0777, true);
}

$filename = sprintf(
    '%s/coverage.e2e.%s.%s.cov',
    E2E_COVERAGE_DIR,
    date('YmdHis'),
    bin2hex(random_bytes(8))
);

// Save coverage data in PHP_CodeCoverage format
require_once WEBROOT . '/vendor/autoload.php';

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\XdebugDriver;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP;

// Create a CodeCoverage object and set the data
$codeCoverageFilter = new Filter();  // a filter is required, but an empty filter includes everything
$codeCoverageDriver = new XdebugDriver($codeCoverageFilter);
$codeCoverage = new CodeCoverage($codeCoverageDriver, $codeCoverageFilter);
$codeCoverage->setData($coverage);

// Save using the PHP writer
$writer = new PHP();
$writer->process($codeCoverage, $filename);
