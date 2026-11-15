<?php

/**
 * Base class for the clinical decision rules controllers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ClinicalDecisionRules\Interface;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\CodeManager;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleManager;

/**
 * Description of OpenEMR\ClinicalDecisionRules\Interface\BaseController
 *
 * @author aron
 */
abstract class BaseController
{
    public $viewBean;
    public $ruleManager;
    public $codeManager;

    public function __construct()
    {
        $this->viewBean = new \stdClass();
    }


    public function getControllerName()
    {
        // An anonymous class name is "Parent@anonymous" followed by a null byte
        // and the defining file's path. Drop that suffix: the name ends up in
        // view paths, and PHP 8.6 throws on a null byte in a filesystem path.
        $class = explode("\0", static::class)[0];
        $parts = explode('\\', $class);
        $name = str_replace('Controller', '', end($parts));
        return $name;
    }
    public function _action_error()
    {
        $this->viewBean->_view = "error.php";
    }

    /**
     * By default, controllers have no default action
     */
    function _action_default()
    {
        $this->_action_error();
    }

    public function emit_json($object)
    {
        // if we ever need to do anything funky... we could create a json.php view...
//        $this->set_view("json.php", "json.php");
        $this->viewBean->_json = $object;
    }

    public function set_view($view, $template = '')
    {
        $this->viewBean->_view = $view;
        if ($template) {
            $this->viewBean->_template = $template;
        }
    }

    public function forward($forward)
    {
        $this->viewBean->_forward = $forward;
    }

    public function redirect($redirect)
    {
        $this->viewBean->_redirect = $redirect;
    }

    /**
     *
     * @return RuleManager
     */
    public function getRuleManager()
    {
        if (!$this->ruleManager) {
            $this->ruleManager = new RuleManager();
        }

        return $this->ruleManager;
    }

    public function getCodeManager()
    {
        if (!$this->codeManager) {
            $this->codeManager = new CodeManager();
        }

        return $this->codeManager;
    }

    public function addHelper($helper)
    {
        if (is_null($this->viewBean->helpers ?? null)) {
            $this->viewBean->helpers = [];
        }

        array_push($this->viewBean->helpers, $helper);
    }
}
