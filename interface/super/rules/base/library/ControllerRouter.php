<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of ControllerRouter
 *
 * @author aron
 */
class ControllerRouter
{
    /**
     * xxx todo: error handling
     */
    function route()
    {
        $actionParam = _get("action");
        $paramParts = explode("!", $actionParam);
        $controller = $paramParts[0];
        $action = $paramParts[1];

        $controllerDir = controller_dir($controller);
        $controllerFile = $controllerDir . "/controller.php";
        require_once($controllerFile);
        $controllerClassName = "Controller_$controller";
        $controllerInstance = new $controllerClassName();

        $actionRouter = new ActionRouter(
            $controllerInstance,
            $action,
            $controllerDir
        );

        $actionRouter->route();
    }
}
