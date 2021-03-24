<?php

/**
 * @package Adserv
 *
 * GLOBAL CONFIGURATION
 *
 * This file defines a singleton class that is used for dependency injection
 * into the framework dispatcher.
 *
 * For the most part the settings here shouldn't be changed.  The static
 * properties can be changed in either _app_config.php or _machine_config.php
 * depending on whether the setting is application-wide or machine-specific
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

/**
 * GlobalConfig is a singleton containing the global variables.
 * In general settings should not be changed in this file and should instead
 * be made in either _app_config.php or _machine_config.php
 *
 * For unit testing this object can be swapped out with another object entirely
 * to allow for mock data, mockput, etc as needed
 *
 * @package Portal
 * @author sjpadgett@gmail.com
 */
class GlobalConfig
{
    /** @var set to true to send debug info to the browser */
    public static $DEBUG_MODE = false;

    public static $PORTAL = false;
    /** @var default action is the controller.method fired when no route is specified */
    public static $DEFAULT_ACTION = "Provider.Home";

    /** @var routemap is an array of patterns and routes */
    public static $ROUTE_MAP;

    /** @var specify the template render engine (Smarty, Savant, PHP) */
    public static $TEMPLATE_ENGINE = 'SavantRenderEngine';

    /** @var template path is the physical location of view template files */
    public static $TEMPLATE_PATH;

    /** @var template cache path is the physical location where templates can be cached */
    public static $TEMPLATE_CACHE_PATH;

    /** @var app root is the root directory of the application */
    public static $APP_ROOT;

    /** @var root url of the application */
    public static $ROOT_URL;

    /** @var ConnectionSetting object containing settings for the DB connection **/
    public static $CONNECTION_SETTING;

    /** @var Setting to true will convert all NULL values to an empty string (set to false with caution!)  **/
    public static $CONVERT_NULL_TO_EMPTYSTRING = true;

    /** @var ICache (optional) object for level 2 caching (for example memcached) **/
    public static $LEVEL_2_CACHE;

    /** @var string if level 2 cache is specified, a temp path for writing files */
    public static $LEVEL_2_CACHE_TEMP_PATH;

    /** @var int if level 2 cache is specified, the timeout in seconds*/
    public static $LEVEL_2_CACHE_TIMEOUT = 15;

    private static $INSTANCE;
    private static $IS_INITIALIZED = false;

    private $context;
    private $router;
    private $phreezer;
    private $render_engine;

    /** prevents external construction */
    private function __construct()
    {
    }

    /** prevents external cloning */
    private function __clone()
    {
    }

    /**
     * Initialize the GlobalConfig object
     */
    static function Init()
    {
        if (!self::$IS_INITIALIZED) {
            require_once 'verysimple/HTTP/RequestUtil.php';
            RequestUtil::NormalizeUrlRewrite();

            require_once 'verysimple/Phreeze/PortalController.php';
            PortalController::$SmartyViewPrefix = '';
            PortalController::$DefaultRedirectMode = 'header';

            self::$IS_INITIALIZED = true;
        }
    }

    /**
     * Returns an instance of the GlobalConfig singleton
     * @return GlobalConfig
     */
    static function GetInstance()
    {
        if (!self::$IS_INITIALIZED) {
            self::Init();
        }

        if (!self::$INSTANCE instanceof self) {
            self::$INSTANCE = new self();
        }

        return self::$INSTANCE;
    }

    /**
     * Returns the context, used for storing session information
     * @return Context
     */
    function GetContext()
    {
        if ($this->context == null) {
        }

        return $this->context;
    }

    /**
     * Returns a URL Writer used to parse/generate URLs
     * @return UrlWriter
     */
    function GetRouter()
    {
        if ($this->router == null) {
            require_once("verysimple/Phreeze/GenericRouter.php");
            $this->router = new GenericRouter(self::$ROOT_URL, self::GetDefaultAction(), self::$ROUTE_MAP);
        }

        return $this->router;
    }


    /**
     * Returns the requested action requested by the user
    * @return string
    */
    function GetAction()
    {
        list($controller,$method) = $this->GetRouter()->GetRoute();
        return $controller . '.' . $method;
    }

    /**
     * Returns the default action if none is specified by the user
     * @return string
     */
    function GetDefaultAction()
    {
        return self::$DEFAULT_ACTION;
    }

    /**
     * Returns the Phreezer persistence layer
     * @return Phreezer
     */
    function GetPhreezer()
    {
        if ($this->phreezer == null) {
            if (!self::$CONVERT_NULL_TO_EMPTYSTRING) {
                require_once("verysimple/DB/DatabaseConfig.php");
                DatabaseConfig::$CONVERT_NULL_TO_EMPTYSTRING = false;
            }

            if (self::$DEBUG_MODE) {
                require_once("verysimple/Phreeze/ObserveToSmarty.php");
                $observer = new ObserveToSmarty($this->GetRenderEngine());
                $this->phreezer = new Phreezer(self::$CONNECTION_SETTING, $observer);
            } else {
                $this->phreezer = new Phreezer(self::$CONNECTION_SETTING);
            }

            if (self::$LEVEL_2_CACHE) {
                $this->phreezer->SetLevel2CacheProvider(self::$LEVEL_2_CACHE, self::$LEVEL_2_CACHE_TEMP_PATH);
                $this->phreezer->ValueCacheTimeout = self::$LEVEL_2_CACHE_TIMEOUT;
            }
        }

        return $this->phreezer;
    }

    /**
     * @return IRenderEngine
     */
    function GetRenderEngine()
    {
        if ($this->render_engine == null) {
            $engine_class = self::$TEMPLATE_ENGINE;
            if (!class_exists($engine_class)) {
                require_once 'verysimple/Phreeze/' . $engine_class  . '.php';
            }

            $this->render_engine = new $engine_class(self::$TEMPLATE_PATH, self::$TEMPLATE_CACHE_PATH);
            $this->render_engine->assign("ROOT_URL", self::$ROOT_URL);
            $this->render_engine->assign("PHREEZE_VERSION", Phreezer::$Version);
            $this->render_engine->assign("PHREEZE_PHAR", Phreezer::PharPath());
        }

        return $this->render_engine;
    }
}
