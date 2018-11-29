<?php
/**
 * Useful globals class for Rest
 *
 * Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
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
    public static $REST_FULL_URL;
    public static $VENDOR_DIR;
    public static $webserver_root;
    public static $web_root;
    public static $server_document_root;
    public static $SITE;

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
            self::setPaths();
            self::$REST_FULL_URL = $_SERVER['REQUEST_SCHEME'] . "//" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; // @todo unsure here!
            self::$ROOT_URL = self::$web_root . "/apis";
            self::$VENDOR_DIR = self::$webserver_root . "/vendor";
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

    /**
     * Basic paths when GLOBALS are not yet available.
     * @return none
     */
    static function SetPaths()
    {
        $isWindows = stripos(PHP_OS, 'WIN') === 0;
        self::$webserver_root = dirname(__FILE__);
        if ($isWindows) {
            //convert windows path separators
            self::$webserver_root = str_replace("\\", "/", self::$webserver_root);
        }
        // Collect the apache server document root (and convert to windows slashes, if needed)
        self::$server_document_root = realpath($_SERVER['DOCUMENT_ROOT']);
        if ($isWindows) {
            //convert windows path separators
            self::$server_document_root = str_replace("\\", "/", self::$server_document_root);
        }
        self::$web_root = substr(self::$webserver_root, strspn(self::$webserver_root ^ self::$server_document_root, "\0"));
        // Ensure web_root starts with a path separator
        if (preg_match("/^[^\/]/", self::$web_root)) {
            self::$web_root = "/" . self::$web_root;
        }

    }

    function destroySession()
    {
        if (!isset($_SESSION)) return;
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

}

// Include our routes and init routes global
//
include_once("./../_rest_routes.inc.php");
