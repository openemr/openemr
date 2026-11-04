<?php

/** @package    verysimple::Phreeze */

/**
 * Controller is a base controller object used for an MVC pattern.
 * It renders views through a render engine and JSON for the API. The Phreeze ORM it was built
 * around has been removed; the portal's records go through the OpenEMR services.
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2011 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 3.1
 */
abstract class PortalController
{
    protected $RenderEngine;
    protected $Context;
    private $_router;
    public $DebugOutput = "";
    public $CaptureOutputMode = false;
    public static string $ApiIdentifier = "api/";

    /**
     * Constructor initializes the controller.
     * This method cannot be overriden. If you need
     * to do something during construction, add it to Init
     *
     * @param IRenderEngine $renderEngine
     * @param Context $context
     * @param IRouter $router
     */
    final public function __construct($renderEngine, $context = null, ?IRouter $router = null)
    {
        $this->RenderEngine = & $renderEngine;
        $this->_router = $router ?: new GenericRouter();
        $this->Context = $context ?: new Context();

        $this->Init();
    }

    /**
     * Returns true if the controller asked the dispatcher to stop. The portal controllers never
     * do; the Dispatcher still asks.
     *
     * @return bool
     */
    public function IsTerminated()
    {
        return false;
    }

    /**
     * Returns the router object used to convert url/uri to controller method
     *
     * @return IRouter
     */
    protected function GetRouter()
    {
        return $this->_router;
    }

    /**
     * Init is called by the base constructor immediately after construction.
     */
    abstract protected function Init();

    /**
     * Assigns a variable to the view
     *
     * @param string $varname
     * @param mixed $varval
     */
    protected function Assign($varname, $varval)
    {
        $this->RenderEngine->assign($varname, $varval);
    }

    /**
     * Renders the specified view
     *
     * @param string $view
     *          (optional) if not provided, the view is automatically bound using the class and method name
     */
    protected function Render($view = "")
    {
        if ($view == "") {
            // automatic binding
            $backtrace = debug_backtrace();
            $view = str_replace("Controller", "", $backtrace [1] ['class']) . $backtrace [1] ['function'];
        }

        // capture output instead of rendering if specified
        if ($this->CaptureOutputMode) {
            $this->DebugOutput = $this->RenderEngine->fetch($view);
        } else {
            $this->RenderEngine->display($view);
        }
    }

    /**
     * Renders the given value as JSON
     *
     * @param mixed $var the variable, array, object, etc to be rendered as JSON
     * @param string $callback if a callback is provided, this will be rendered as JSONP
     */
    protected function RenderJSON($var, $callback = "")
    {
        $output = json_encode($var, JSON_THROW_ON_ERROR);

        if ($callback) {
            $output = "$callback(" . $output . ")";
        }

        // capture output instead of rendering if specified
        if ($this->CaptureOutputMode) {
            $this->DebugOutput = $output;
        } else {
            @header($callback ? 'Content-type: text/plain' : 'Content-type: application/json');
            print $output;
        }
    }

    /**
     * Send a crash message to the browser and terminate
     *
     * @param string $errmsg
     *          text message to display
     * @param int $code
     *          used for support for this error
     * @param Exception $exception
     *          if exception was thrown, can be provided for more info
     */
    protected function Crash($errmsg = "Unknown Error", $code = 0, $exception = null)
    {
        $ex = $exception ?: new Exception($errmsg, $code);
        throw $ex;
    }

    /**
     * Throw an exception if an undeclared method is accessed
     *
     * @access public
     * @param string $name
     * @param mixed $vars
     * @throws Exception
     */
    public function __call($name, $vars = null)
    {
        throw new Exception(static::class . "::" . $name . " is not implemented");
    }
}
