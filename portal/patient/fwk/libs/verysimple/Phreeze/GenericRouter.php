<?php

/** @package    verysimple::Phreeze */

/**
 * import supporting libraries
 */
require_once('IRouter.php');
require_once('verysimple/HTTP/RequestUtil.php');

/**
 * Generic Router is an implementation of IRouter that uses patterns to connect
 * routes to a Controller/Method
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2013 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.2
 */
class GenericRouter implements IRouter
{
    /**
     *
     * @var array associative array of map data. Example: <pre>
     *      $routes = array(
     *      "GET:" => array("route" => "Default.Home"),
     *      "GET:user/(:num)/packages" => array("route" => "Package.Query", "params" => array("userId" => 1)),
     *      "POST:user/(:num)/package/(:num)" => array("route" => "Package.Update", "params" => array("userId" => 1, "packageId" => 3)),
     *      "GET:automation/(:any)" => array("route" => "Automation.DoAutomation", "params" => array("action" => 1))
     *      );
     *      </pre>
     */
    public $routeMap;

    /** @var string the default action if requested route is empty (typically the application home page) */
    public $defaultAction = 'Default.Home';

    /** @var string the fully qualified root url for the app. Ex: "https://site.local/" */
    public $appRootUrl = '';

    /** @var string cached URI  */
    private $uri = '';

    /** @var array specific route record that was matched based on the URL  */
    private $matchedRoute;

    /** @var string the default action if requested route is not found  */
    public static $ROUTE_NOT_FOUND = "Default.Error404";

    /**
     * Instantiate the GenericRouter
     *
     * @param string $appRootUrl
     *          the root url of the application including trailing slash (ex http://localhost/)
     * @param string $defaultAction
     *          action to call if no route is provided (ex Default.DefaultAction)
     * @param array $mapping
     *          the
     */
    public function __construct($appRootUrl, $defaultAction, array $routeMap)
    {
        $this->defaultAction = $defaultAction;
        $this->appRootUrl = $appRootUrl;
        $this->routeMap = $routeMap;
        $this->matchedRoute = null;
    }

    /**
     * Return the root url for this application as provided at construction
     */
    public function GetRootUrl()
    {
        return $this->appRootUrl;
    }

    /**
     * @inheritdocs
     */
    public function GetRoute($uri = "")
    {

        // reset the uri cache
        $this->uri = '';

        if ($uri == "") {
            $uri = RequestUtil::GetMethod() . ":" . $this->GetUri();
        }

            // literal match check
        if (isset($this->routeMap [$uri])) {
            // expects mapped values to be in the form: Controller.Model
            list ( $controller, $method ) = explode(".", $this->routeMap [$uri] ["route"]);

            if (!empty($GLOBALS['bootstrap_pid'])) {
                // p_acl check
                if ($this->routeMap[$uri]["p_acl"] != 'p_all') {
                    // failed p_acl check
                    $error = 'Unauthorized';
                    throw new Exception($error);
                }
            }

            if (!empty($GLOBALS['bootstrap_register'])) {
                // p_reg check
                if ($this->routeMap[$uri]["p_reg"] !== true) {
                    // failed p_reg check
                    $error = 'Unauthorized';
                    throw new Exception($error);
                }
            }

            $this->matchedRoute = array (
                    "key" => $this->routeMap [$uri],
                    "route" => $this->routeMap [$uri] ["route"],
                    "params" => isset($this->routeMap [$uri] ["params"]) ? $this->routeMap [$uri] ["params"] : array ()
            );

            return array (
                    $controller,
                    $method
            );
        }

        // loop through the route map for wild cards:
        foreach ($this->routeMap as $key => $value) {
            $unalteredKey = $key;

            // convert wild cards to RegEx.
            // currently only ":any" and ":num" are supported wild cards
            $key = str_replace(':any', '.+', $key);
            $key = str_replace(':num', '[0-9]+', $key);

            // check for RegEx match
            if (preg_match('#^' . $key . '$#', $uri, $match)) {
                if (!empty($GLOBALS['bootstrap_pid'])) {
                    // p_acl check
                    $p_acl = $this->routeMap[$unalteredKey]["p_acl"];
                    if (
                        ($p_acl == 'p_none') ||
                        (($p_acl == 'p_limited') && ($GLOBALS['bootstrap_uri_id'] != $match[1]))
                    ) {
                        // failed p_acl check
                        $error = 'Unauthorized';
                        throw new Exception($error);
                    }
                }

                $this->matchedRoute = array (
                        "key" => $unalteredKey,
                        "route" => $value ["route"],
                        "params" => isset($value ["params"]) ? $value ["params"] : array ()
                );

                // expects mapped values to be in the form: Controller.Model
                list ( $controller, $method ) = explode(".", $value ["route"]);
                return array (
                        $controller,
                        $method
                );
            }
        }

        // this is a page-not-found route
        $this->matchedRoute = array (
                "key" => '',
                "route" => '',
                "params" => array ()
        );

        // if we haven't returned by now, we've found no match:
        return explode('.', self::$ROUTE_NOT_FOUND, 2);
    }

    /**
     *
     * @see IRouter::GetUri()
     */
    public function GetUri()
    {
        if (! $this->uri) {
            $this->uri = array_key_exists('_REWRITE_COMMAND', $_REQUEST) ? $_REQUEST ['_REWRITE_COMMAND'] : '';

            // if a root folder was provided, then we need to strip that out as well
            if ($this->appRootUrl) {
                $prefix = str_replace(RequestUtil::GetServerRootUrl(), '/', $this->appRootUrl);
                if (substr($this->uri, 0, strlen($prefix)) == $prefix) {
                    $this->uri = substr($this->uri, strlen($prefix));
                }
            }

            // strip trailing slash
            while (substr($this->uri, - 1) == '/') {
                $this->uri = substr($this->uri, 0, - 1);
            }
        }

        return $this->uri;
    }

    /**
     * @inheritdocs
     */
    public function GetUrl($controller, $method, $params = '', $requestMethod = "")
    {
        $found = false;

        // params may be either an array of key/value pairs, or a string in the format key=val&key=val&key=val
        if (! is_array($params)) {
            parse_str($params, $params);
        }

            // The app root url is needed so we can return the fully qualified URL
        $url = $this->appRootUrl ? $this->appRootUrl : RequestUtil::GetBaseURL();

        // normalize the url so that there are no trailing slashes
        $url = rtrim($url, '/');

        // enumerate all of the routes in the map and look for the first one that matches
        foreach ($this->routeMap as $key => $value) {
            list ( $routeController, $routeMethod ) = explode(".", $value ["route"]);

            $routeRequestMethodArr = explode(":", $key, 2);
            $routeRequestMethod = $routeRequestMethodArr [0];

            // In order to match a route it needs to meet 3 conditions:
            // 1. controller and method match
            // 2. the requestMethod is either a match or one or the other is a wildcard
            // 3. the number of parameters is equal
            if ($routeController == $controller && $routeMethod == $method && ($requestMethod == "" || $routeRequestMethod == "*" || $routeRequestMethod == $requestMethod) && (! array_key_exists("params", $value) || count($params) == count($value ["params"]))) {
                $keyArr = explode('/', $key);

                // strip the request method off the key:
                $reqMethodAndController = explode(":", $keyArr [0]);
                $keyArr [0] = (count($reqMethodAndController) == 2 ? $reqMethodAndController [1] : $reqMethodAndController [0]);

                // merge the parameters passed in with the routemap's path
                // example: path is user/(:num)/events and parameters are [userCode]=>111
                // this would yield an array of [0]=>user, [1]=>111, [2]=>events
                if (array_key_exists("params", $value)) {
                    foreach ($value ["params"] as $rKey => $rVal) {
                        if (! array_key_exists($rKey, $params)) {
                            throw new Exception("Missing parameter '$rKey' for route $controller.$method");
                        }

                        $keyArr [$value ["params"] [$rKey]] = $params [$rKey];
                    }
                }

                // put the url together:
                foreach ($keyArr as $urlPiece) {
                    $url = $url . ($urlPiece != '' ? "/$urlPiece" : '');
                }

                // no route, just a request method? RESTful to add a trailing slash:
                if ($routeRequestMethodArr [1] == "") {
                    $url . "/";
                }

                $found = true;
                break;
            }
        }

        if (! $found) {
            throw new Exception('No route found for ' . ($requestMethod ? $requestMethod : '*') . ":$controller.$method" . ($params ? '?' . implode('&', $params) : ''));
        }

        // if this is the root url then we want to include the trailing slash
        if (($url . '/') == $this->appRootUrl) {
            $url = $this->appRootUrl;
        }

        return $url;
    }

    /**
     * @inheritdocs
     */
    public function GetUrlParams()
    {
        return explode('/', $this->GetUri());
    }

    /**
     * @inheritdocs
     */
    public function GetUrlParam($paramKey, $default = '')
    {
        // if the route hasn't been requested, then we need to initialize before we can get url params
        if ($this->matchedRoute == null) {
            $this->GetRoute();
        }

        $params = $this->GetUrlParams();
        $uri = $this->GetUri();
        $count = 0;
        $routeMap = $this->matchedRoute ["key"];
        $returnVal = '';

        if (isset($this->matchedRoute ["params"] [$paramKey])) {
            $indexLocation = $this->matchedRoute ["params"] [$paramKey];
            $returnVal = $params [$indexLocation];
        } else {
            $returnVal = RequestUtil::Get($paramKey);
        }

        return $returnVal != '' ? $returnVal : $default;
    }
}
