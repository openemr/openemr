<?php

/** @package    Patient Portal::Controller */

/** import supporting libraries */
require_once("verysimple/Phreeze/PortalController.php");
require_once(dirname(__FILE__) . "/../../../lib/appsql.class.php");
/**
 * AppBaseController is a base class Controller class from which
 * the front controllers inherit.  it is not necessary to use this
 * class or any code, however you may use if for application-wide
 * functions such as authentication
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 * @package Patient Portal::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class AppBasePortalController extends PortalController
{
    static $DEFAULT_PAGE_SIZE = 20;

    /**
     * Init is called by the base controller before the action method
     * is called.  This provided an oportunity to hook into the system
     * for all application actions.  This is a good place for authentication
     * code.
     */
    protected function Init()
    {

    /*  if ( !in_array($this->GetRouter()->GetUri(),array('login','loginform','logout')) )
        {
            require_once("App/SecureApp.php");
            $this->RequirePermission(SecureApp::$PERMISSION_ADMIN,'SecureApp.LoginForm');
        }*/
    }

    /**
     * Returns the number of records to return per page
     * when pagination is used
     */
    protected function GetDefaultPageSize()
    {
        return self::$DEFAULT_PAGE_SIZE;
    }

    /**
     * Returns the name of the JSONP callback function (if allowed)
     */
    protected function JSONPCallback()
    {
        // TODO: uncomment to allow JSONP
        // return RequestUtil::Get('callback','');

        return '';
    }

    /**
     * Return the default SimpleObject params used when rendering objects as JSON
     * @return array
     */
    protected function SimpleObjectParams()
    {
        return array('camelCase' => true);
    }

    /**
     * Helper method to get values from stdClass without throwing errors
     * @param stdClass $json
     * @param string $prop
     * @param string $default
     */
    protected function SafeGetVal($json, $prop, $default = '')
    {
        return (property_exists($json, $prop))
            ? $json->$prop
            : $default;
    }

    /**
     * Helper utility that calls RenderErrorJSON
     * @param Exception
     */
    protected function RenderExceptionJSON(Exception $exception)
    {
        $this->RenderErrorJSON($exception->getMessage(), null, $exception);
    }

    /**
     * Output a Json error message to the browser
     * @param string $message
     * @param array key/value pairs where the key is the fieldname and the value is the error
     */
    protected function RenderErrorJSON($message, $errors = null, $exception = null)
    {
        $err = new stdClass();
        $err->success = false;
        $err->message = $message;
        $err->errors = array();

        if ($errors != null) {
            foreach ($errors as $key => $val) {
                $err->errors[lcfirst($key)] = $val;
            }
        }

        if ($exception) {
            $err->stackTrace = explode("\n#", substr($exception->getTraceAsString(), 1));
        }

        @header('HTTP/1.1 401 Unauthorized');
        $this->RenderJSON($err, RequestUtil::Get('callback'));
    }
}
