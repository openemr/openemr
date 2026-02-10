<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


require_once("../../globals.php");

use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

try {
    $request = Request::createFromGlobals();
    $controllerRouter = new ControllerRouter();
    $response = $controllerRouter->route($request);
} catch (AccessDeniedException | CsrfInvalidException $e) {
    $response = AccessDeniedHelper::createDeniedResponse(
        "ACL check failed for admin/super: Rules - " . $e->getMessage(),
        xl("Rules")
    );
} catch (NotFoundHttpException $e) {
    // Log the exception
    (new SystemLogger())->errorLogCaller($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    $contents = (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig()->render('error/404.html.twig');
    // Send the error response
    $response = new Response($contents, 404);
} catch (\Throwable $e) {
    // Log the exception
    (new SystemLogger())->errorLogCaller($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    $contents =  (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig()->render('error/general_http_error.html.twig');
    // Send the error response
    $response = new Response($contents, 500);
}

// Send the normal response
$response->send();
