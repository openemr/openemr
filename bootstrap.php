<?php

/**
 * This will eventually be core application setup for all paths: web, cli, etc.
 *
 * It is only to be used for a small number of critical operations:
 * - Autoloader seup
 * - Environment reading and normalization
 * - Standardizing some runtime configuration
 * - Setting up error handling
 * - Preparing DI tooling
 *
 * This MUST NOT do anything like the following:
 * - Connect to the database
 * - Interact with sessions
 * - Touch any data derived from HTTP requests
 * - Do anything with or for the "globals" config
 *
 * For now, it's only used in an experimental CLI tool.
 */

declare(strict_types=1);

use Dotenv\Dotenv;
use Firehed\Container\{
    AutoDetect,
    Builder,
    Compiler,
};

chdir(__DIR__);

date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
// Docker wants logs written to stdout. This may need to vary by SAPI.
ini_set('error_log', '/dev/stdout');
ini_set('log_errors', '1');

error_reporting(E_ALL);

require_once 'vendor/autoload.php';

// class_exists check is because dotenv should be a dev dependency and not
// installed in prod deployments, though as of writing that's not the case.
if (class_exists(Dotenv::class) && file_exists('.env')) {
    // For the moment, detection needs putenv to occur.
    Dotenv::createUnsafeImmutable('.')->load();
}

// TODO: this should be streamlined further

$container = AutoDetect::instance('config/di');
