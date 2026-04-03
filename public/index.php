<?php

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface;
use OpenEMR\BC\FallbackRouter;
use Psr\Log\LoggerInterface;

$container = require_once __DIR__ . '/../bootstrap.php';
assert($container instanceof TypedContainerInterface);

// Guard against non-web requests, e.g. PHP_SAPI === 'cli'?

const FRONT_CONTROLLER_USED = true;

$logger = $container->get(LoggerInterface::class);
$logger->debug('Request routed through front-controller');

// Future scope: Put a router ahead of the fallback routing; any well-formed
// new routes will be executed without touching the existing systems. Such new
// routes must rely only on modern conventions (DI, no reliance on globals,
// etc).
// primaryRouter = $container->get(Router::class)
// if router would 404/405, fall back to below?

// Turn off strict-mode error handler for fallback code (see bootstrap addition
// and #11411)
restore_error_handler();

$router = $container->get(FallbackRouter::class);
$fileToInclude = $router->performLegacyRouting($_SERVER['REQUEST_URI']);
if ($fileToInclude === null) {
    http_response_code(404);
    exit(1);
}

// For global variables to get the correct scoping, this needs to be done at
// the file root level instead of inside a function. GLOBALS and OEGlobalsBag
// are fine, but the raw variables don't get defined when called from a function
require $fileToInclude;
