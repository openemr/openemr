<?php

/** @package    verysimple::Phreeze */

/**
 * import supporting libraries
 */
require_once('IRouter.php');

/**
 * Mock router for unit testing purposes
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc.
 * @copyright 1997-2012 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class MockRouter implements IRouter
{
    private $_params = array ();
    private $_uri;
    private $_url;

    /**
     * Backwards compatibility for Phreeze 2.0
     *
     * @deprecated use GetUrl instead
     * @param string $controller
     * @param string $method
     * @param string $params
     *          in the format param1=val1&param2=val2
     * @param bool $strip_api
     *          set to true to strip virtual part of the url in a rest call
     * @param string $delim
     *          the querystring variable delimiter (& or &amp; for generating valid html)
     * @return string URL
     */
    public function Get($controller, $method, $params = "", $strip_api = true, $delim = "&")
    {
        $this->stripApi = $strip_api;
        $this->delim = $delim;
        return $this->GetUrl($controller, $method, $params);
    }

    /**
     *
     * @param string $paramName
     * @param string $paramName
     */
    public function SetUrlParam($paramName, $value)
    {
        $this->_params [$paramName] = $value;
    }

    /**
     * @inheritdocs
     */
    public function GetRoute($uri = "")
    {
    }

    /**
     *
     * @see IRouter::GetUri()
     */
    public function GetUri()
    {
        return $this->_uri;
    }

    /**
     * @inheritdocs
     */
    public function GetUrl($controller, $method, $params = '', $requestMethod = '')
    {
        return $this->_url;
    }

    /**
     * @inheritdocs
     */
    public function GetUrlParams()
    {
        return $this->_params;
    }

    /**
     * @inheritdocs
     */
    public function GetUrlParam($paramKey, $default = '')
    {
        return array_key_exists($paramKey, $this->_params) ? $this->_params [$paramKey] : "";
    }

    /**
     *
     * @param unknown_type $value
     */
    public function SetUri($value)
    {
        $this->_uri = $value;
    }

    /**
     *
     * @param unknown_type $value
     */
    public function SetUrl($value)
    {
        $this->_url = $value;
    }
    public function ClearUrlParams()
    {
        $this->_params = array ();
    }
}
