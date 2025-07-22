<?php

declare(strict_types=1);

// make sure this can only be run on the command line.
if (php_sapi_name() !== 'cli') {
    exit;
}

$_GET['site'] = 'default';
$ignoreAuth = true;

// Reduce logging level during tests to prevent memory issues
// SystemLogger uses this global to determine log level
$GLOBALS['system_error_logging'] = 'WARNING';

require_once(__DIR__ . "/../interface/globals.php");
