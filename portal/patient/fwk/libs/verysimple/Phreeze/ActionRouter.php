<?php

/** @package    verysimple::Phreeze */

require_once("verysimple/HTTP/RequestUtil.php");
require_once("verysimple/Util/UrlWriterMode.php");
require_once("verysimple/Phreeze/IRouter.php");

/**
 * class for dealing with URLs
 *
 * @package verysimple::HTTP
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc. http://www.verysimple.com
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class ActionRouter implements IRouter
{
    protected $stripApi = true;
    protected $delim = '&';
    protected static $_format;

    /**
     * Constructor allows a rewriting pattern to be specified
     *
     * @param string $format
     *          sprintf compatible format
     */
    public function __construct($format = "%s.%s.page?%s", private $_mode = UrlWriterMode::WEB, private $_appRoot = '', private $_defaultRoute = '')
    {
        self::$_format = $format;
    }

    /**
     * @inheritdocs
     */
    public function GetUri()
    {
        return implode('/', RequestUtil::GetUrlParts($this->_appRoot));
    }

    /**
     * @inheritdocs
     */
    public function GetUrlParams()
    {
        return $_REQUEST;
    }

    /**
     * @inheritdocs
     */
    public function GetUrlParam($key, $default = '')
    {
        // make the route params case insensitive
        return RequestUtil::Get($key, $default, false, true);
    }

    /**
     * @inheritdocs
     */
    public function GetUrl($controller, $method, $params = '', $requestMethod = '')
    {
        $format = str_replace("{delim}", $this->delim, self::$_format);

        $qs = "";
        $d = "";
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                // if no val, the omit the equal sign (this might be used in rest-type requests)
                $qs .= $d . $key . (strlen((string) $val) ? ("=" . urlencode((string) $val)) : "");
                $d = $this->delim;
            }
        } else {
            $qs = $params;
        }

        $url = sprintf($format, $controller, $method, $qs);

        // strip off trailing delimiters from the url
        $url = (str_ends_with($url, "&amp;")) ? substr($url, 0, strlen($url) - 5) : $url;
        $url = (str_ends_with($url, "&") || str_ends_with($url, "?")) ? substr($url, 0, strlen($url) - 1) : $url;

        $api_check = explode("/api/", RequestUtil::GetCurrentUrl());
        if ($this->stripApi && count($api_check) > 1) {
            $url = $api_check [0] . "/" . $url;
        }

        return $url;
    }

    /**
     * @inheritdocs
     */
    public function GetRoute($uri = "")
    {
        if ($uri == "") {
            $action = RequestUtil::Get('action');
            if (! $action) {
                $action = $this->_defaultRoute;
            }

            $uri = $action ?: RequestUtil::GetCurrentURL();
        }

        // get the action requested
        $params = explode(".", str_replace("/", ".", $uri));
        $controller_param = isset($params [0]) && $params [0] ? $params [0] : "";
        $controller_param = str_replace([
                ".",
                "/",
                "\\"
        ], [
                "",
                "",
                ""
        ], $controller_param);

        if (! $controller_param) {
            throw new Exception("Invalid or missing Controller parameter");
        }

        $method_param = isset($params [1]) && $params [1] ? $params [1] : "";
        if (! $method_param) {
            $method_param = "DefaultAction";
        }

        return  [
                $controller_param,
                $method_param
        ];
    }

    /**
     * Returns true or false based on the $value passed in as to whether or not the
     * URL Writer is currently in that mode.
     *
     * @param $value String
     *          mode to check against the current mode
     * @return boolean TRUE if arg passed in is the current mode
     */
    public function ModeIs($value)
    {
        if (strcmp((string) $this->_mode, (string) $value) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns how the Dispatcher plucks it's controller and method from the URL.
     *
     * @param $default_action The
     *          Default action in case the argument hasn't been supplied
     */
    public function GetAction($url_param = "action", $default_action = "Account.DefaultAction")
    {
        return match ($this->_mode) {
            // default is to return the standard browser-based action=%s.%s&%s:
            default => RequestUtil::Get($url_param, $default_action),
        };
    }
}
