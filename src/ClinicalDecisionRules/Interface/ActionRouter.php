<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

namespace OpenEMR\ClinicalDecisionRules\Interface;
/**
 * This is a very simple action routing class for a given controller. Given
 * a controller and action (typically from OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter), it goes through
 * these steps to find out which function in that controller should be invoked:
 *
 * todo - document these steps
 *
 * @author aron
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionRouter
{
    protected $controller;
    protected $path;
    protected $webRoot;
    protected $appRoot;
    protected $action;

    public function __construct($controller, $action, $path)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->path = $path;
        $this->appRoot = Common::base_dir();
        $this->webRoot = $GLOBALS['webroot'];
    }

    public function route(Request $request): Response
    {
        if (!$this->action) {
            $this->action = 'default';
        }

        $result = $this->perform($this->action);

        $forward = $result->_forward ?? null;
        if ($forward) {
            $result = $this->perform($forward);
        }

        $_redirect = $result->_redirect ?? null;
        if ($_redirect) {
            return new Response('', 302, ['Location' => $_redirect]);
        }

        $responseContent = $this->renderView($result);
        return new Response($responseContent);
    }

    protected function perform($action)
    {
        $actionMethod = '_action_' . $action;

        if (method_exists($this->controller, $actionMethod)) {
            $this->controller->$actionMethod();
        } else {
            $this->controller->_action_default();
        }

        return $this->controller->viewBean;
    }

    protected function renderView($viewBean): string
    {
        $viewName = $viewBean->_view ?? '';
        $viewLocation = $this->resolveViewLocation($viewName);

        if (!is_file($viewLocation)) {
            return ''; // No view template found, return empty string.
        }

        $viewBean->_appRoot = $this->appRoot;
        $viewBean->_webRoot = $this->webRoot;
        $viewBean->_view_body = $viewLocation;

        $template = $this->resolveTemplate($viewBean->_template ?? null);
        ob_start();
        require $template;
        return ob_get_clean();
    }

    protected function resolveViewLocation($viewName)
    {
        $viewLocation = $this->path . '/view/' . $viewName;
        if (!is_file($viewLocation)) {
            $viewLocation = Common::base_dir() . 'base/view/' . $viewName;
        }

        return $viewLocation;
    }

    protected function resolveTemplate($templateName)
    {
        $templateLocation = $this->path . '/template/' . $templateName;

        if (!is_file($templateLocation)) {
            $templateLocation = Common::base_dir() . 'base/template/' . $templateName;
        }

        // return template if its found
        if (is_file($templateLocation)) {
            return $templateLocation;
        } else {
            return Common::base_dir() . 'base/template/basic.php';
        }
    }

    protected function resolveHelper($name)
    {
        // try local
        $location = $this->path . '/helper/' . $name;

        if (!is_file($location)) {
            $location = Common::base_dir() . 'base/helper/' . $name;
        }

        // return template if its found
        return is_file($location) ? $location : null;
    }
}
