<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Rules")]);
    exit;
}
use Symfony\Component\HttpFoundation\Request;
use OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter;

$request = Request::createFromGlobals();

$controllerRouter = new ControllerRouter();
$response = $controllerRouter->route($request);

// Send the normal response
$response->send();
