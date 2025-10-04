<?php

/**
 * Global Front Controller for OpenEMR.
 *
 * Routes requests to appropriate PHP files, blocks .inc.php access,
 * preserves multisite selection. Target files handle auth/sessions/errors.
 *
 * AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR, Inc.
 * @copyright Copyright (c) 2025 OpenCoreEMR, Inc.
 * @license   GPLv3
 */

use OpenCoreEMR\FrontController\Router;
use OpenCoreEMR\FrontController\SecurityValidator;
use OpenEMR\Events\FrontController\FrontControllerEvent;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// Load .env variables using Dotenv library
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Check feature flag - when disabled, provide transparent pass-through
if ((getenv('OPENEMR__ENABLE_FRONT_CONTROLLER') ?: '0') !== '1') {
    // Extract target route from query parameter
    $route = $_GET['_ROUTE'] ?? 'index.php';

    // Security: Prevent directory traversal and null byte injection
    if (strpos($route, '..') !== false || strpos($route, "\0") !== false) {
        http_response_code(404);
        exit('Not Found');
    }

    // Resolve and validate target file
    $targetFile = __DIR__ . '/' . ltrim($route, '/');

    // Block .inc.php files even when front controller is disabled
    if (preg_match('/\.inc\.php$/i', $route)) {
        http_response_code(403);
        exit('Access Denied: Include files cannot be accessed directly');
    }

    // Verify file exists
    if (!file_exists($targetFile) || !is_file($targetFile)) {
        http_response_code(404);
        exit('Not Found');
    }

    // Clean up query string - remove _ROUTE parameter for transparent pass-through
    unset($_GET['_ROUTE']);
    $_SERVER['QUERY_STRING'] = http_build_query($_GET);

    // Fix __FILE__ and __DIR__ constants by updating $_SERVER superglobals
    // This ensures target files see correct paths in their execution context
    $_SERVER['SCRIPT_FILENAME'] = $targetFile;
    $_SERVER['SCRIPT_NAME'] = '/' . $route;
    $_SERVER['PHP_SELF'] = '/' . $route;

    // Change to target file's directory to preserve relative path behavior
    chdir(dirname($targetFile));

    // Include target file - it now executes with correct context
    require $targetFile;
    exit;
}

// Early extension point via event system
// Modules can listen to FrontControllerEvent::EVENT_EARLY for early initialization
// See https://github.com/adunsulag/oe-module-custom-skeleton for module development
if (isset($GLOBALS['kernel']) && method_exists($GLOBALS['kernel'], 'getEventDispatcher')) {
    $event = new FrontControllerEvent($_GET['_ROUTE'] ?? '', $_GET['site'] ?? 'default');
    $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, FrontControllerEvent::EVENT_EARLY);
}

// Initialize router
$router = new Router(__DIR__);

// Determine multisite and route
$router->determineSiteId();
$route = $router->extractRoute();

// Handle trailing slash
$router->handleTrailingSlash();

// Deny forbidden paths
if ($router->isForbiddenPath()) {
    http_response_code(404);
    SecurityValidator::logSecurityEvent("Denied path: $route");
    exit('Not Found');
}

// Admin-only paths
if ($router->requiresAdmin()) {
    $_SERVER['REQUIRE_ADMIN'] = true;
}

// Block .inc.php files
if (SecurityValidator::isIncludeFile($route)) {
    http_response_code(403);
    SecurityValidator::logSecurityEvent("Blocked .inc.php access: $route");
    exit(
        SecurityValidator::getDebugMessage(
            'Access Denied',
            'Access Denied: Include files cannot be accessed directly'
        )
    );
}

// Resolve and validate target file
$targetFile = $router->resolveTargetFile();

if ($targetFile === null) {
    http_response_code(404);
    SecurityValidator::logSecurityEvent("Invalid or non-existent path: $route");
    exit('Not Found');
}

// Route to target file with error handling and shutdown function registration
// Register shutdown function to dispatch late event even if target file exits early
register_shutdown_function(function () use ($route, $router) {
    if (isset($GLOBALS['kernel']) && method_exists($GLOBALS['kernel'], 'getEventDispatcher')) {
        $error = error_get_last();
        $context = [
            'completed_normally' => $error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]),
            'error' => $error,
        ];
        $event = new FrontControllerEvent($route, $router->getSiteId(), $context);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, FrontControllerEvent::EVENT_LATE);
    }
});

try {
    require $targetFile;
} catch (\Throwable $e) {
    // Log exception and dispatch late event with error context
    error_log("Front Controller: Exception in target file $targetFile: " . $e->getMessage());

    if (isset($GLOBALS['kernel']) && method_exists($GLOBALS['kernel'], 'getEventDispatcher')) {
        $context = [
            'completed_normally' => false,
            'exception' => $e,
        ];
        $event = new FrontControllerEvent($route, $router->getSiteId(), $context);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, FrontControllerEvent::EVENT_LATE);
    }

    // Re-throw to preserve normal error handling
    throw $e;
}
