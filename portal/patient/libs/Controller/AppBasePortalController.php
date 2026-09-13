<?php

/** @package    Patient Portal::Controller */

/** import supporting libraries */
require_once("verysimple/Phreeze/PortalController.php");
require_once(__DIR__ . "/../../../lib/appsql.class.php");
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
    static $DEFAULT_PAGE_SIZE = 25;

    /**
     * Init is called by the base controller before the action method
     * is called.  This provided an opportunity to hook into the system
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
        return ['camelCase' => true];
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
     * Apply request parameters as equality filters on a query Criteria.
     *
     * Request input may only ever drive equality (`_Equals`) filters. The
     * comparator applied to a column is a code-level decision and must never be
     * selectable from the request: assigning arbitrary criteria properties from
     * request keys (e.g. `*_BitwiseAnd`, `*_In`, `*_LiteralFunction`, or the
     * framework `Filters` property) is a mass-assignment weakness (CWE-915) and,
     * for the bitwise comparators, reaches an unquoted SQL context (CWE-89).
     *
     * Both the bare-column convenience form (`?Id=5` -> `Id_Equals`) and the
     * explicit form (`?Id_Equals=5`) are preserved.
     *
     * @param Criteria $criteria the query criteria to populate
     */
    protected function ApplyRequestEqualsFilters(Criteria $criteria): void
    {
        $request = \OpenEMR\Common\Http\CurrentRequest::get();
        $keys = array_unique(array_merge($request->query->keys(), $request->request->keys()));

        foreach ($keys as $prop) {
            $prop_normal = ucfirst((string) $prop);

            if (str_ends_with($prop_normal, '_Equals') && property_exists($criteria, $prop_normal)) {
                $criteria->$prop_normal = RequestUtil::Get($prop);
            } elseif (property_exists($criteria, $prop_normal . '_Equals')) {
                // convenience so that the _Equals suffix is not needed
                $criteria->{$prop_normal . '_Equals'} = RequestUtil::Get($prop);
            }
        }
    }

    /**
     * Helper utility that calls RenderErrorJSON
     * @param mixed $exception
     */
    protected function RenderExceptionJSON(Exception $exception)
    {
        $this->RenderErrorJSON($exception->getMessage(), null, $exception);
    }

    /**
     * Output a Json error message to the browser
     * @param string $message
     * @param array $errors key/value pairs where the key is the fieldname and the value is the error
     */
    protected function RenderErrorJSON($message, $errors = null, $exception = null)
    {
        $err = new stdClass();
        $err->success = false;
        $err->message = $message;
        $err->errors = [];

        if ($errors != null) {
            foreach ($errors as $key => $val) {
                $err->errors[lcfirst((string) $key)] = $val;
            }
        }

        if ($exception) {
            $err->stackTrace = explode("\n#", substr((string) $exception->getTraceAsString(), 1));
        }

        @header('HTTP/1.1 401 Unauthorized');
        $this->RenderJSON($err, RequestUtil::Get('callback'));
    }
}
