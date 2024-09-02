<?php

require_once("include/header.php");
use Symfony\Component\HttpFoundation\Request;
use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;

$request = Request::createFromGlobals();

$controllerRouter = new ControllerRouter();
$response = $controllerRouter->route($request);

// Send the normal response
$response->send();
