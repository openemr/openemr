<?php
/**
 * Useful globals class for Rest
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

// also a handy place to add utility methods
//
class RestConfig
{
    /** @var set to true to send debug info to the browser */
    public static $DEBUG_MODE = false;

    /** @var default action is the controller.method fired when no route is specified */
    public static $DEFAULT_ACTION = "";

    /** @var routemap is an array of patterns and routes */
    public static $ROUTE_MAP;

    /** @var app root is the root directory of the application */
    public static $APP_ROOT;

    /** @var root url of the application */
    public static $ROOT_URL;

    private static $INSTANCE;
    private static $IS_INITIALIZED = false;

    private $context;

    /** prevents external construction */
    private function __construct()
    {
    }

    /** prevents external cloning */
    private function __clone()
    {
    }

    /**
     * Initialize the RestConfig object
     */
    static function Init()
    {
        if (!self::$IS_INITIALIZED) {
            self::$ROOT_URL = $GLOBALS['web_root'] . "/apis";
            self::$IS_INITIALIZED = true;
        }
    }

    /**
     * Returns an instance of the RestConfig singleton
     * @return RestConfig
     */
    static function GetInstance()
    {
        if (!self::$IS_INITIALIZED) {
            self::Init();
        }

        if (!self::$INSTANCE instanceof self) {
            self::$INSTANCE = new self;
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

}
