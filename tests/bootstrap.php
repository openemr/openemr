<?php

/**
 * PHPUnit bootstrap file for integration tests.
 *
 * @codeCoverageIgnore Bootstrap files run before coverage instrumentation starts.
 */

declare(strict_types=1);

use OpenEMR\PHPUnit\Extension;

// make sure this can only be run on the command line.
if (php_sapi_name() !== 'cli') {
    exit(1);
}

if (!class_exists(Extension::class)) {
    throw new \Exception('Safety-net extension not found');
}

register_shutdown_function(function (): void {
    if (!Extension::isBootstrapped()) {
        // @phpstan-ignore openemr.forbiddenErrorLog (System logger not available here)
        error_log("CRITICAL ERROR: Safety-net bootstrap did not load, unsafe test run");
        exit(70);
    }
});

$_GET['site'] = 'default';
$ignoreAuth = true;
require_once(__DIR__ . "/../interface/globals.php");
