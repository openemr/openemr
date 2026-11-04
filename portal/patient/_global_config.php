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
    /** @var bool set to true to send debug info to the browser */
    public static $DEBUG_MODE = false;

    public static $PORTAL = false;
    /** @var string default action is the controller.method fired when no route is specified */
    public static $DEFAULT_ACTION = "Provider.Home";

    /** @var array<string, array<string, mixed>> routemap is an array of patterns and routes */
    public static $ROUTE_MAP;

    /** @var string specify the template render engine (Smarty, Savant, PHP) */
    public static $TEMPLATE_ENGINE = 'SavantRenderEngine';

    /** @var string template path is the physical location of view template files */
    public static $TEMPLATE_PATH;

    /** @var string template cache path is the physical location where templates can be cached */
    public static $TEMPLATE_CACHE_PATH;

    /** @var string app root is the root directory of the application */
    public static $APP_ROOT;

    /** @var string root url of the application */
    public static $ROOT_URL;

    /** @var string root url of the application */
    public static $WEB_ROOT;

    private static $INSTANCE;
    private static $IS_INITIALIZED = false;

    private $context;
    private $router;
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
    public static function Init()
    {
        if (!self::$IS_INITIALIZED) {
            RequestUtil::NormalizeUrlRewrite();

            self::$IS_INITIALIZED = true;
        }
    }

    /**
     * Returns an instance of the GlobalConfig singleton
     * @return GlobalConfig
     */
    public static function GetInstance()
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
    public function GetContext()
    {
        if ($this->context == null) {
        }

        return $this->context;
    }

    /**
     * Returns a URL Writer used to parse/generate URLs
     * @return UrlWriter
     */
    public function GetRouter()
    {
        if ($this->router == null) {
            $this->router = new GenericRouter(self::$ROOT_URL, self::GetDefaultAction(), self::$ROUTE_MAP);
        }

        return $this->router;
    }


    /**
     * Returns the requested action requested by the user
    * @return string
    */
    public function GetAction(): string
    {
        [$controller, $method] = $this->GetRouter()->GetRoute();
        return $controller . '.' . $method;
    }

    /**
     * Returns the default action if none is specified by the user
     * @return string
     */
    public function GetDefaultAction()
    {
        return self::$DEFAULT_ACTION;
    }

    /**
     * @return IRenderEngine
     */
    public function GetRenderEngine()
    {
        if ($this->render_engine == null) {
            $engine_class = self::$TEMPLATE_ENGINE;
            $engine = new $engine_class(self::$TEMPLATE_PATH, self::$TEMPLATE_CACHE_PATH);
            if (!$engine instanceof IRenderEngine) {
                throw new \LogicException(sprintf('Template engine %s must implement %s', $engine_class, IRenderEngine::class));
            }
            $this->render_engine = $engine;
            $this->render_engine->assign("ROOT_URL", self::$ROOT_URL);
        }

        return $this->render_engine;
    }
}
