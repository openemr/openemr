<?php
/**
 * Useful globals class for Rest
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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

    private $context = false;

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
     * Returns true if api called from within OpenEMR authorized session (ie. locally).
     * @return true or false if not local.
     */
    function GetContext()
    {
        if (!$this->context) {
            // collect the session id
            $local_auth = isset($_SERVER['HTTP_APPSECRET']) ? $_SERVER['HTTP_APPSECRET'] : false;
            // collect the api csrf token
            $app_token = isset($_SERVER['HTTP_APPTOKEN']) ? $_SERVER['HTTP_APPTOKEN'] : false;
            if (!empty($local_auth)) {
                session_id($local_auth); // a must for cURL. See oeHttp Client request.
            }
            session_start();
            if (empty($local_auth) || empty($app_token) || ($app_token !== $_SESSION['api_csrf_token'])) {
                // Need the session id and a api csrf token that matches current session
                //  If not, then destroy session and return false context
                session_destroy();
            } else {
                if (!empty($_SESSION['authUserID']) && !empty($_SESSION['authUser'])) {
                    // Need a set user/id in the session to continue
                    //  If so, then return the api_csrf_token to signal a proper authenticated session.
                    $this->context = true;
                } else {
                    //  If not, then destroy session and return false context
                    session_destroy();
                }
            }
            error_log("DEBUG2: " . $local_auth);
            error_log("DEBUG3: " . $app_token);
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
        if (!isset($_SESSION)) {
            return;
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
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
}

// Include our routes and init routes global
//
require_once(dirname(__FILE__) . "/_rest_routes.inc.php");
