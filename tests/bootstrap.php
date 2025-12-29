<?php

declare(strict_types=1);

// make sure this can only be run on the command line.
if (php_sapi_name() !== 'cli') {
    exit;
}

$_GET['site'] = 'default';
$ignoreAuth = true;

// Disable translation engine during tests to reduce database overhead.
// Tests verify logic, not translated strings, so this is safe.
// See: https://github.com/openemr/openemr/issues/9999
$GLOBALS['disable_translation'] = true;

require_once(__DIR__ . "/../interface/globals.php");
