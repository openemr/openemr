<?php

declare(strict_types=1);

/**
 * This will eventually be core application setup for all paths: web, cli, etc.
 *
 * It is only to be used for a small number of critical operations:
 * - Autoloader seup
 * - Standardizing some runtime configuration
 * - Setting up error handling
 *
 * This MUST NOT do anything like the following:
 * - Connect to the database
 * - Interact with sessions
 * - Touch any data derived from HTTP requests
 * - Do anything with or for the "globals" config
 *
 * For now, it's only used in an experimental CLI tool.
 */

chdir(__DIR__);

date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
// Docker wants logs written to stdout. This may need to vary by SAPI.
ini_set('error_log', '/dev/stdout');
ini_set('log_errors', '1');

error_reporting(E_ALL);

require_once 'vendor/autoload.php';

// Load a dotenv file, if it exists
if (file_exists('./.env')) {
    Dotenv\Dotenv::createImmutable('.')->load();
}
