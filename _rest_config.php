<?php
/**
 * Useful globals class for Rest
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/src/Common/Session/SessionUtil.php");

use OpenEMR\RestControllers\AuthRestController;

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

    /** @var fhir routemap is an array of patterns and routes */
    public static $FHIR_ROUTE_MAP;

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

    /**  @var set to true if local api call */
    private static $localCall = false;

    /**  @var set to true if not rest call */
    private static $notRestCall = false;

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

    static function destroySession()
    {
        OpenEMR\Common\Session\SessionUtil::apiSessionCookieDestroy();
    }

    static function getPostData($data)
    {
        if (count($_POST)) {
            return $_POST;
        } elseif ($post_data = file_get_contents('php://input')) {
            if ($post_json = json_decode($post_data, true)) {
                return $post_json;
            } else {
                parse_str($post_data, $post_variables);
                if (count($post_variables)) {
                    return $post_variables;
                }
            }
        }

        return false;
    }

    static function authorization_check($section, $value)
    {
        if (self::$notRestCall || self::$localCall) {
            $result = acl_check($section, $value, $_SESSION['authUser']);
        } else {
            $authRestController = new AuthRestController();
            $result = $authRestController->aclCheck($_SERVER["HTTP_X_API_TOKEN"], $section, $value);
        }
        if (!$result) {
            if (!self::$notRestCall) {
                http_response_code(401);
            }
            exit();
        }
    }

    static function setLocalCall()
    {
        self::$localCall = true;
    }

    static function setNotRestCall()
    {
        self::$notRestCall = true;
    }

    static function is_authentication($resource)
    {
        return ($resource === "/api/auth" || $resource === "/fhir/auth");
    }

    static function get_bearer_token()
    {
        $parse = preg_split("/[\s,]+/", $_SERVER["HTTP_AUTHORIZATION"]);
        if (strtoupper(trim($parse[0])) !== 'BEARER') {
            return false;
        }

        return trim($parse[1]);
    }

    static function is_fhir_request($resource)
    {
        return (stripos(strtolower($resource), "/fhir/") !== false) ? true : false;
    }

    static function verify_api_request($resource, $api)
    {
        $api = strtolower(trim($api));
        if (self::is_fhir_request($resource)) {
            if ($api !== 'fhir') {
                http_response_code(401);
                exit();
            }
        } elseif ($api !== 'oemr') {
            http_response_code(401);
            exit();
        }

        return;
    }

    static function authentication_check($resource)
    {
        if (!self::is_authentication($resource)) {
            $token = $_SERVER["HTTP_X_API_TOKEN"];
            $authRestController = new AuthRestController();
            if (!$authRestController->isValidToken($token)) {
                http_response_code(401);
                exit();
            } else {
                $authRestController->optionallyAddMoreTokenTime($token);
            }
        }
    }
}

// Include our routes and init routes global
//
require_once(dirname(__FILE__) . "/_rest_routes.inc.php");
