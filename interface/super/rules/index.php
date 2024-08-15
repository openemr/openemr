<?php

require_once("include/header.php");
use Symfony\Component\HttpFoundation\Request;
use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;

$request = Request::createFromGlobals();

$controllerRouter = new ControllerRouter();
$response = $controllerRouter->route($request);

// Check if the response is a redirect (302)
if ($response->getStatusCode() === 302) {
    $redirectUrl = $response->headers->get('Location');
    echo "<script>
            top.restoreSession();
            window.location = '" . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . "';
          </script>";
} else {
    // Send the normal response
    $response->send();
}
