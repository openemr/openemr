<?php

/**
 * Isolated Test Bootstrap
 *
 * This bootstrap file loads only the minimum dependencies needed for unit testing
 * without connecting to databases or other external services.
 */

declare(strict_types=1);

// Ensure we're running from CLI
if (php_sapi_name() !== 'cli') {
    exit('This bootstrap can only be run from the command line.');
}

// Load Composer autoloader only
$vendorDir = dirname(__DIR__) . '/vendor';
if (!file_exists($vendorDir . '/autoload.php')) {
    throw new RuntimeException('Composer autoloader not found. Run "composer install" first.');
}

require_once $vendorDir . '/autoload.php';

// Set up minimal globals that some classes might expect
$GLOBALS['webserver_root'] = dirname(__DIR__);
$GLOBALS['OE_SITES_BASE'] = $GLOBALS['webserver_root'] . '/sites';
$GLOBALS['vendor_dir'] = $vendorDir;

// Prevent any database connections
$GLOBALS['disable_database_connection'] = true;

// Set basic configuration without requiring database
$GLOBALS['HTML_CHARSET'] = 'UTF-8';
ini_set('default_charset', 'utf-8');
mb_internal_encoding('UTF-8');
