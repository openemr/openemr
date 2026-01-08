<?php

/**
 * Abstract implementation of the ESign controller. Implement the
 * rest of me to create your own controller.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/Viewer.php';
require_once $GLOBALS['srcdir'] . '/ESign/ViewableIF.php';

abstract class Abstract_Controller implements ViewableIF
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    protected $_method = null;
    protected $_params = null;
    protected $_viewDir = null;
    protected $_viewScript = null;
    protected $_viewer = null;
    protected $_request = null;
    protected $_view = null;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_method = $this->_request->getParam('method');
        $this->_viewDir = $GLOBALS['srcdir'] . "/ESign/views";
        $this->_viewScript = 'esign_error.php';
        $this->_view = new Viewer();
    }

    /**
     * Triggered when the module's ESign/ButtonIF is clicked.
     * The controller method gets all the parameters that match
     * data-* within the button's attributes.
     */
    abstract public function esign_form_view();

    /**
     * Triggered when the module's form is saved (refresh
     * the log.)
     */
    abstract public function esign_log_view();

    /**
     * Triggered when the ESign Sigature form is submitted
     */
    abstract public function esign_form_submit();

    protected function getRequest()
    {
        return $this->_request;
    }

    protected function setViewScript($viewScript)
    {
        $this->_viewScript = $viewScript;
    }

    public function getViewScript()
    {
        return $this->_viewDir . DIRECTORY_SEPARATOR . $this->_viewScript;
    }

    public function run()
    {
        if (method_exists($this, $this->_method)) {
            $this->{$this->_method}();
        } else {
            throw new \Exception("The method " . $this->_method . " does not exist and cannot be executed");
        }
    }

    public function getHtml()
    {
        return $this->_view->getHtml($this);
    }

    public function render()
    {
        return $this->_view->render($this);
    }
}

class Request
{
    protected $_params = [];

    public function __construct()
    {
        $this->parseParams();
    }

    public function getParam($key, $default = '')
    {
        return $this->_params[$key] ?? $default;
    }

    protected function parseParams()
    {
        foreach ($_REQUEST as $key => $value) {
            $this->_params[$key] = $value;
        }
    }
}

class Response
{
    public $formId;
    public $formDir;
    public $encounterId;
    public $locked;
    public $editButtonHtml;

    public function __construct(public $status, public $message)
    {
    }
}
