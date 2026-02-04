<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenEMR\BC\FallbackRouter;
use OpenEMR\Common\Logging\SystemLogger;

// Future scope: Put a router ahead of the fallback routing; any well-formed
// new routes will be executed without touching the existing systems. Such new
// routes must rely only on modern conventions (DI, no reliance on globals,
// etc).

$router = new FallbackRouter(dirname(__DIR__), new SystemLogger(100));
$fileToInclude = $router->performLegacyRouting($_SERVER['REQUEST_URI']);
if ($fileToInclude === null) {
    http_response_code(404);
    exit(1);
}

// For global variables to get the correct scoping, this needs to be done at
// the file root level instead of inside a function. GLOBALS and OEGlobalsBag
// are fine, but the raw variables don't get defined when called from a function
require $fileToInclude;
