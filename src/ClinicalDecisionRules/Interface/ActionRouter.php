<?php

/**
 * This is a very simple action routing class for a given controller. Given
 * a controller and action (typically from OpenEMR\ClinicalDecisionRules\Interface\ControllerRouter), it goes through
 * these steps to find out which function in that controller should be invoked:
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 * @author    Copyright (C) 2024 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ClinicalDecisionRules\Interface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionRouter
{
    /**
     * @var BaseController
     */
    protected $controller;
    protected $path;
    protected $webRoot;
    protected $appRoot;
    protected $action;
    protected $templateRoot;

    public function __construct($controller, $action)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->appRoot = Common::base_dir();
        $this->webRoot = $GLOBALS['webroot'];
        $this->templateRoot = Common::template_dir();
    }

    public function route(Request $request): Response
    {
        if (!$this->action) {
            $this->action = 'default';
        }

        $result = $this->perform($this->action);
        // if the action is a file attachment download or anything else
        // we want to just return it.
        if ($result instanceof Response) {
            return $result;
        }
        $forward = $result->_forward ?? null;
        if ($forward) {
            $result = $this->perform($forward);
        }

        $_redirect = $result->_redirect ?? null;
        if ($_redirect) {
            return new Response('', 302, ['Location' => $_redirect]);
        }

        if (isset($this->controller->viewBean->_json)) {
            return new Response(json_encode($this->controller->viewBean->_json), 200, ['Content-Type' => 'application/json']);
        } else {
            $responseContent = $this->renderView($result);
        }
        return new Response($responseContent);
    }

    protected function perform($action)
    {
        $actionMethod = '_action_' . $action;

        if (method_exists($this->controller, $actionMethod)) {
            $result = $this->controller->$actionMethod();
        } else {
            $result = $this->controller->_action_default();
        }

        if ($result instanceof Response) {
            return $result;
        } else {
            return $this->controller->viewBean;
        }
    }

    protected function renderView($viewBean): string
    {
        $viewName = $viewBean->_view ?? '';
        $viewLocation = $this->resolveViewLocation($viewName);

        if (!is_file($viewLocation)) {
            return ''; // No view template found, return empty string.
        }

        $viewBean->_templateRoot = $this->templateRoot;
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
        $controllerName = strtolower($this->controller->getControllerName());
        $viewLocation = $this->templateRoot . 'controllers' . DIRECTORY_SEPARATOR . $controllerName . DIRECTORY_SEPARATOR . $viewName;
        if (!is_file($viewLocation)) {
            $viewLocation = $this->templateRoot . 'base' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $viewName;
        }

        return $viewLocation;
    }

    protected function resolveTemplate($templateName)
    {
        $templateLocation = $this->templateRoot . 'base' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $templateName;

        // return template if its found
        if (is_file($templateLocation)) {
            return $templateLocation;
        } else {
            return $this->templateRoot . 'base' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'basic.php';
        }
    }
}
