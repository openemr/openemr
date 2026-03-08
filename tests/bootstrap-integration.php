<?php

/**
 * PHPUnit bootstrap file for isolated tests.
 *
 * @codeCoverageIgnore Bootstrap files run before coverage instrumentation starts.
 */

declare(strict_types=1);

// make sure this can only be run on the command line.
if (php_sapi_name() !== 'cli') {
    exit;
}

require_once(__DIR__ . "/../vendor/autoload.php");
