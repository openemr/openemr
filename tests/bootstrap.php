<?php

/**
 * PHPUnit bootstrap file for integration tests.
 *
 * @codeCoverageIgnore Bootstrap files run before coverage instrumentation starts.
 */

declare(strict_types=1);

// make sure this can only be run on the command line.
if (php_sapi_name() !== 'cli') {
    exit;
}

register_shutdown_function(function() {
    echo new Exception('Shutdown stack trace');
});

$_GET['site'] = 'default';
$ignoreAuth = true;
require_once(__DIR__ . "/../interface/globals.php");
