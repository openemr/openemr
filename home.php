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
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// Load .env variables using Dotenv library
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Check feature flag
if (!getenv('OPENEMR_ENABLE_FRONT_CONTROLLER') || getenv('OPENEMR_ENABLE_FRONT_CONTROLLER') !== '1') {
    http_response_code(404);
    exit('Front controller disabled');
}

// Early extension point via event system
// Modules can listen to 'front_controller.early' event for early initialization
// See https://github.com/adunsulag/oe-module-custom-skeleton for module development
$GLOBALS['kernel']->getEventDispatcher()->dispatch(
    new \Symfony\Component\EventDispatcher\GenericEvent(),
    'front_controller.early'
);

// Initialize router
$router = new Router(__DIR__);

// Determine multisite and route
// Note: Using $_SERVER['HTTP_HOST'] as fallback for site detection
// This allows multisite to work when site parameter is not explicitly provided
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

// Route to target file
require $targetFile;

// Late extension point via event system (after content is loaded)
// Modules can listen to 'front_controller.late' event for post-processing
$GLOBALS['kernel']->getEventDispatcher()->dispatch(
    new \Symfony\Component\EventDispatcher\GenericEvent(),
    'front_controller.late'
);
