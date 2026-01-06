<?php

declare(strict_types=1);

// Setup for ALL paths - web, CLI, etc. Do not add code that is sensitive to
// the request context.

chdir(__DIR__);

date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
// Docker wants logs written to stdout. This may need to vary by SAPI.
ini_set('error_log', '/dev/stdout');
ini_set('log_errors', '1');

error_reporting(E_ALL);

require_once 'vendor/autoload.php';
