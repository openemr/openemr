<?php

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface;
use GuzzleHttp\Psr7\ServerRequest;
use OpenEMR\BC\FallbackRouter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$container = require_once __DIR__ . '/../bootstrap.php';
assert($container instanceof TypedContainerInterface);

$request = ServerRequest::fromGlobals();

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
try {
    $fileToInclude = $router->performLegacyRouting($request);
} catch (HttpExceptionInterface $e) {
    http_response_code($e->getStatusCode());
    $logger->info('Front controller cannot route URI {uri}: {status}', [
        'uri' => $request->getUri(),
        'status' => $e->getStatusCode(),
    ]);
    exit(1);
}

$logger->debug('Routed to {file}', ['file' => $fileToInclude]);

/*
 * At some point in the future, the front-controller will be require. Adding
 * code like the following into a central-but-legacy point (e.g.
 * interface/globals.php) can aid in verifying correct routing.
 *
register_shutdown_function(function ()  use ($logger) {
    if (!defined('FRONT_CONTROLLER_USED')) {
        $logger->warning(
            'The request to {req_url} did not go through the front controller. This ' .
            'means your web server may not be configured correctly. See ' .
            '{doc_url} for more details.',
            [
                'req_url' => $_SERVER['REQUEST_URI'] ?? '(unknown)',
                'doc_url' => 'https://need-some-page',
            ],
        );
    }
});
 */

// Finally, delegate the request to the original legacy path.
//
// For global variables to get the correct scoping, this needs to be done at
// the file root level instead of inside a function. GLOBALS and OEGlobalsBag
// are fine, but the raw variables don't get defined when called from a function
//
// But at minimum, clean up the vars from _this_ file.
unset($container, $request, $logger, $router);
require $fileToInclude;
