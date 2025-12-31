<?php

declare(strict_types=1);

// Setup for ALL paths - web, CLI, etc. Do not add code that is sensitive to
// the request context.

chdir(__DIR__);

date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('error_log', '/dev/stdout'); // Docker wants logs written to stdout
ini_set('log_errors', '1');

error_reporting(E_ALL);
error_reporting(E_ALL & ~E_USER_DEPRECATED);

require_once 'vendor/autoload.php';
