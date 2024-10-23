<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
namespace OpenEMR\ClinicalDecisionRules\Interface;

use OpenEMR\ClinicalDecisionRules\Interface\ActionRouter;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter
 *
 * @author aron
 */
class ControllerRouter
{
    protected function createActionRouter(BaseController $controller, string $action)
    {
        return new ActionRouter($controller, $action);
    }

    public function shouldSkipAdminAcl(string $controller): bool
    {
        return $controller === 'review' || $controller === 'log';
    }
    /**
     * xxx todo: error handling
     * Route the request to the appropriate controller and action.
     */
    public function route(Request $request): Response
    {
        $actionParam = $request->get('action', '');
        $paramParts = explode('!', $actionParam);
        $controller = $paramParts[0] ?? '';
        $action = $paramParts[1] ?? '';
        // TODO: @adunsulag what ACL if any do we need to review the CDR rule?
        if ($this->shouldSkipAdminAcl($controller) && !AclMain::aclCheckCore('admin', 'super')) {
            throw new AccessDeniedException("admin", "super", "Invalid ACL access to CDR routes");
        }
        $classFQCN = __NAMESPACE__ . "\\Controller\\Controller" . ucfirst($controller);
        if (!class_exists($classFQCN)) {
            throw new NotFoundHttpException("Controller not found: $classFQCN");
        }
        $controllerInstance = new $classFQCN();

        $actionRouter = $this->createActionRouter(
            $controllerInstance,
            $action
        );

        return $actionRouter->route($request);
    }
}
