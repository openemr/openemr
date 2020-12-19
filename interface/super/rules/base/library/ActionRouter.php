<?php

 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * This is a very simple action routing class for a given controller. Given
 * a controller and action (typically from ControllerRouter), it goes through
 * these steps to find out which function in that controller should be invoked:
 *
 * todo - document these steps
 *
 * @author aron
 */
class ActionRouter
{

    var $controller;
    var $path;
    var $webRoot;
    var $appRoot;
    var $action;

    function __construct($controller, $action, $path)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->path = $path;
        $this->appRoot = base_dir();
        $this->webRoot = $GLOBALS['webroot'];
    }

    function route()
    {
        if (!$this->action) {
            $this->action = "default";
        }

        $result = $this->perform($this->action);

        $forward = $result->_forward ?? null;
        if ($forward) {
            $this->perform($forward);
            return;
        }

        $_redirect = $result->_redirect ?? null;
        if ($_redirect) {
            $baseTemplatesDir = base_dir() . "base/template";
            require($baseTemplatesDir . "/redirect.php");
        }
    }

    function perform($action)
    {
        $action_method = '_action_' . $action;

        // execute the default action if action is not found
        method_exists($this->controller, $action_method) ?
                $this->controller->$action_method() : $this->controller->_action_default();
        $result = $this->controller->viewBean;

        // resolve view location
        $viewName = $result->_view ?? null;
        $view_location = $this->path . "/view/" . $viewName;
        if (!is_file($view_location)) {
            // try common
            $view_location = base_dir() . "base/view/" . $viewName;
        }

        // set viewbean in page scope
        $viewBean = $result;

        // set helpers
        $helpers = $viewBean->helpers ?? null;
        if (!is_null($helpers)) {
            foreach ($helpers as $helper) {
                $helperPath = $this->resolveHelper($helper);
                if (!is_null($helperPath)) {
                    require_once($helperPath);
                }
            }
        }

        if (!is_file($view_location)) {
            // no view template
            return $result;
        }

        $viewBean->_appRoot = $this->appRoot;
        $viewBean->_webRoot = $this->webRoot;
        $viewBean->_view_body = $view_location;

        $template = $this->resolveTemplate($result->_template ?? null);
        require($template);

        return $result;
    }

    function resolveTemplate($templateName)
    {
        // try local
        $template_location = $this->path . "/template/" . $templateName;

        // try common
        if (!is_file($template_location)) {
            $template_location = base_dir() . "base/template/" . $templateName;
        }

        if (is_file($template_location)) {
            // return template if its found
            return $template_location;
        } else {
            // otherwise use the basic template
            $baseTemplatesDir = base_dir() . "base/template";
            return $baseTemplatesDir . "/basic.php";
        }
    }

    function resolveHelper($name)
    {
        // try local
        $location = $this->path . "/helper/" . $name;

        // try common
        if (!is_file($location)) {
            $location = base_dir() . "base/helper/" . $name;
        }

        if (is_file($location)) {
            // return template if its found
            return $location;
        } else {
            return null;
        }
    }
}
