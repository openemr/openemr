<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
namespace OpenEMR\ClinicalDecisionRules\Interface;

use OpenEMR\ClinicalDecisionRules\Interface\ActionRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter
 *
 * @author aron
 */
class ControllerRouter
{
    protected function createActionRouter(BaseController $controller, string $action, string $controllerDir) {
        return new ActionRouter($controller, $action, $controllerDir);
    }
    /**
     * xxx todo: error handling
     * Route the request to the appropriate controller and action.
     */
    public function route(Request $request): Response
    {
        $actionParam = $request->query->get('action', '');
        $paramParts = explode('!', $actionParam);
        $controller = $paramParts[0] ?? '';
        $action = $paramParts[1] ?? '';

        $controllerDir = Common::controller_dir($controller);


//        $controllerFile = $controllerDir . '/controller.php';
//        require_once $controllerFile;
        $classFQCN = __NAMESPACE__ . "\\Controller\\Controller" . ucfirst($controller);
        $controllerInstance = new $classFQCN();

        $actionRouter = $this->createActionRouter(
            $controllerInstance,
            $action,
            $controllerDir
        );

        return $actionRouter->route($request);
    }
}
