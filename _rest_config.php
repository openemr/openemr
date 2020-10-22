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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
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

    /** @var portal routemap is an array of patterns and routes */
    public static $PORTAL_ROUTE_MAP;

    /** @var portal fhir routemap is an array of patterns and routes */
    public static $PORTAL_FHIR_ROUTE_MAP;

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
            self::$REST_FULL_URL = $_SERVER['REQUEST_SCHEME'] . "//" . $_SERVER['SERVER_NAME'] . $_SERVER['REDIRECT_URL']; // @todo unsure here!
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
            self::$INSTANCE = new self();
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
        $result = AclMain::aclCheckCore($section, $value);
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

    static function is_skip_auth($resource)
    {
        return ($resource === "/api/auth" || $resource === "/fhir/auth" || $resource === "/portal/auth" || $resource === "/portalfhir/auth" || $resource === "/fhir/metadata");
    }

    static function get_bearer_token()
    {
        $parse = preg_split("/[\s,]+/", $_SERVER["HTTP_AUTHORIZATION"]);
        if (strtoupper(trim($parse[0])) !== 'BEARER') {
            return false;
        }

        return trim($parse[1]);
    }

    static function is_api_request($resource)
    {
        return (stripos(strtolower($resource), "/api/") !== false) ? true : false;
    }

    static function is_fhir_request($resource)
    {
        return (stripos(strtolower($resource), "/fhir/") !== false) ? true : false;
    }

    static function is_portal_request($resource)
    {
        return (stripos(strtolower($resource), "/portal/") !== false) ? true : false;
    }

    static function is_portal_fhir_request($resource)
    {
        return (stripos(strtolower($resource), "/portalfhir/") !== false) ? true : false;
    }

    static function verify_api_request($resource, $api)
    {
        $api = strtolower(trim($api));
        if (self::is_fhir_request($resource)) {
            if ($api !== 'fhir') {
                http_response_code(401);
                exit();
            }
        } elseif (self::is_portal_request($resource)) {
            if ($api !== 'port') {
                http_response_code(401);
                exit();
            }
        } elseif (self::is_portal_fhir_request($resource)) {
            if ($api !== 'pofh') {
                http_response_code(401);
                exit();
            }
        } elseif (self::is_api_request($resource)) {
            if ($api !== 'oemr') {
                http_response_code(401);
                exit();
            }
        } else {
            // somebody is up to no good
            http_response_code(401);
            exit();
        }

        return;
    }

    static function authentication_check($resource)
    {
        if (!self::is_skip_auth($resource)) {
            $token = $_SERVER["HTTP_X_API_TOKEN"];
            $authRestController = new AuthRestController();
            if (!$authRestController->isValidToken($token)) {
                self::destroySession();
                http_response_code(401);
                exit();
            }
        }
    }

    static function apiLog($response = '', $requestBody = '')
    {
        // only log when using standard api calls (skip when using local api calls from within OpenEMR)
        //  and when api log option is set
        if (!$GLOBALS['is_local_api'] && $GLOBALS['api_log_option']) {
            if ($GLOBALS['api_log_option'] == 1) {
                // Do not log the response and requestBody
                $response = '';
                $requestBody = '';
            }

            // convert pertinent elements to json
            $requestBody = (!empty($requestBody)) ? json_encode($requestBody) : '';
            $response = (!empty($response)) ? json_encode($response) : '';

            // prepare values and call the log function
            $event = 'api';
            $category = 'api';
            $method = $_SERVER['REQUEST_METHOD'];
            $url = $_SERVER['REQUEST_URI'];
            $patientId = $_SESSION['pid'] ?? 0;
            $userId = $_SESSION['authUserID'] ?? 0;
            $api = [
                'user_id' => $userId,
                'patient_id' => $patientId,
                'method' => $method,
                'request' => $GLOBALS['resource'],
                'request_url' => $url,
                'request_body' => $requestBody,
                'response' => $response
            ];
            if ($patientId == 0) {
                $patientId = null; //entries in log table are blank for no patient_id, whereas in api_log are 0, which is why above $api value uses 0 when empty
            }
            EventAuditLogger::instance()->recordLogItem(1, $event, ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 'api log', $patientId, $category, 'open-emr', null, null, '', $api);
        }
    }
}

// Include our routes and init routes global
//
require_once(dirname(__FILE__) . "/_rest_routes.inc.php");
