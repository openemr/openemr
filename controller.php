<?php

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require_once("interface/globals.php");

try {
    if ($_GET === []) {
        throw new BadRequestHttpException("Missing query parameters");
    }

    $controller = new Controller();

    // Use explicit routing if 'controller' param is set, otherwise fall back to legacy positional routing
    $dispatcher = isset($_GET['controller']) ? $controller->dispatch(...) : $controller->act(...);
    echo $dispatcher($_GET);
} catch (AccessDeniedHttpException $e) {
    http_response_code($e->getStatusCode());
    echo AccessDeniedHelper::renderUnauthorizedTemplate($e->getMessage());
} catch (HttpExceptionInterface $e) {
    http_response_code($e->getStatusCode());
    echo htmlspecialchars($e->getMessage());
} catch (\Throwable $e) {
    $errorRef = bin2hex(random_bytes(8));
    ServiceContainer::getLogger()->error("controller.php error", [
        'ref' => $errorRef,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    http_response_code(500);
    echo "Internal Server Error (ref: $errorRef)";
}
