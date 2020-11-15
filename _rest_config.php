<?php

/**
 * Useful globals class for Rest
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . "/src/Common/Session/SessionUtil.php");

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// also a handy place to add utility methods
// TODO before v6 release: refactor http_response_code(); for psr responses.
//
class RestConfig
{
    /** @var routemap is an array of patterns and routes */
    public static $ROUTE_MAP;

    /** @var fhir routemap is an  of patterns and routes */
    public static $FHIR_ROUTE_MAP;

    /** @var portal routemap is an  of patterns and routes */
    public static $PORTAL_ROUTE_MAP;

    /** @var portal fhir routemap is an  of patterns and routes */
    public static $PORTAL_FHIR_ROUTE_MAP;

    /** @var app root is the root directory of the application */
    public static $APP_ROOT;

    /** @var root url of the application */
    public static $ROOT_URL;
    // you can guess what the rest are!
    public static $VENDOR_DIR;
    public static $SITE;

    public static $webserver_root;
    public static $web_root;
    public static $server_document_root;
    public static $publicKey;
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

    /**
     * Returns an instance of the RestConfig singleton
     *
     * @return RestConfig
     */
    public static function GetInstance(): \RestConfig
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
     * Initialize the RestConfig object
     */
    public static function Init(): void
    {
        if (self::$IS_INITIALIZED) {
            return;
        }
        // The busy stuff.
        self::setPaths();
        self::setSiteFromEndpoint();
        self::$ROOT_URL = self::$web_root . "/apis";
        self::$VENDOR_DIR = self::$webserver_root . "/vendor";
        self::$publicKey = self::$webserver_root . "/sites/" . self::$SITE . "/documents/certificates/oapublic.key";
        self::$IS_INITIALIZED = true;
    }

    /**
     * Basic paths when GLOBALS are not yet available.
     *
     * @return void
     */
    private static function SetPaths(): void
    {
        $isWindows = (stripos(PHP_OS_FAMILY, 'WIN') === 0);
        // careful if moving this class to modify where's root.
        self::$webserver_root = __DIR__;
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
        // Will need these occasionally. sql init comes to mind!
        $GLOBALS['rootdir'] = self::$web_root . "/interface";
        // Absolute path to the source code include and headers file directory (Full path):
        $GLOBALS['srcdir'] = self::$webserver_root . "/library";
        // Absolute path to the location of documentroot directory for use with include statements:
        $GLOBALS['fileroot'] = self::$webserver_root;
        // Absolute path to the location of interface directory for use with include statements:
        $GLOBALS['incdir'] = self::$webserver_root . "/interface";
        // Absolute path to the location of documentroot directory for use with include statements:
        $GLOBALS['webroot'] = self::$web_root;
        // Static assets directory, relative to the webserver root.
        $GLOBALS['assets_static_relative'] = self::$web_root . "/public/assets";
        // Relative themes directory, relative to the webserver root.
        $GLOBALS['themes_static_relative'] = self::$web_root . "/public/themes";
        // Relative images directory, relative to the webserver root.
        $GLOBALS['images_static_relative'] = self::$web_root . "/public/images";
        // Static images directory, absolute to the webserver root.
        $GLOBALS['images_static_absolute'] = self::$webserver_root . "/public/images";
        //Composer vendor directory, absolute to the webserver root.
        $GLOBALS['vendor_dir'] = self::$webserver_root . "/vendor";
    }

    private static function setSiteFromEndpoint(): void
    {
        // Get site from endpoint if available. Unsure about this though!
        // Will fail during sql init otherwise.
        $endPointParts = self::parseEndPoint(self::getRequestEndPoint());
        if (count($endPointParts) > 1) {
            $site_id = $endPointParts[0] ?? '';
            if ($site_id) {
                self::$SITE = $site_id;
            }
        }
    }

    public static function parseEndPoint($resource): array
    {
        if ($resource[0] === '/') {
            $resource = substr($resource, 1);
        }
        return explode('/', $resource);
    }

    public static function getRequestEndPoint(): string
    {
        $resource = null;
        if (!empty($_REQUEST['_REWRITE_COMMAND'])) {
            $resource = "/" . $_REQUEST['_REWRITE_COMMAND'];
        } elseif (!empty($_SERVER['REDIRECT_QUERY_STRING'])) {
            $resource = str_replace('_REWRITE_COMMAND=', '/', $_SERVER['REDIRECT_QUERY_STRING']);
        } else {
            if (!empty($_SERVER['REQUEST_URI'])) {
                if (strpos($_SERVER['REQUEST_URI'], '?') > 0) {
                    $resource = strstr($_SERVER['REQUEST_URI'], '?', true);
                } else {
                    $resource = str_replace(self::$ROOT_URL, '', $_SERVER['REQUEST_URI']);
                }
            }
        }

        return $resource;
    }

    public static function verifyAccessToken()
    {
        $response = self::createServerResponse();
        $request = self::createServerRequest();
        $server = new ResourceServer(
            new AccessTokenRepository(),
            self::$publicKey
        );
        try {
            $raw = $server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response);
        }

        return $raw;
    }

    public static function createServerResponse(): ResponseInterface
    {
        $psr17Factory = new Psr17Factory();

        return $psr17Factory->createResponse();
    }

    public static function createServerRequest(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        return $creator->fromGlobals();
    }

    public static function destroySession(): void
    {
        OpenEMR\Common\Session\SessionUtil::apiSessionCookieDestroy();
    }

    public static function getPostData($data)
    {
        if (count($_POST)) {
            return $_POST;
        }

        if ($post_data = file_get_contents('php://input')) {
            if ($post_json = json_decode($post_data, true)) {
                return $post_json;
            }
            parse_str($post_data, $post_variables);
            if (count($post_variables)) {
                return $post_variables;
            }
        }

        return null;
    }

    public static function authorization_check($section, $value): void
    {
        $result = AclMain::aclCheckCore($section, $value);
        if (!$result) {
            if (!self::$notRestCall) {
                http_response_code(401);
            }
            exit();
        }
    }

    public static function setLocalCall(): void
    {
        self::$localCall = true;
    }

    public static function setNotRestCall(): void
    {
        self::$notRestCall = true;
    }

    public static function get_bearer_token(): string
    {
        $parse = preg_split("/[\s,]+/", $_SERVER["HTTP_AUTHORIZATION"]);
        if (strtoupper(trim($parse[0])) !== 'BEARER') {
            return '';
        }

        return trim($parse[1]);
    }

    public static function verify_api_request($resource, $api): void
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
    }

    public static function is_fhir_request($resource): bool
    {
        return stripos(strtolower($resource), "/fhir/") !== false;
    }

    public static function is_portal_request($resource): bool
    {
        return stripos(strtolower($resource), "/portal/") !== false;
    }

    public static function is_portal_fhir_request($resource): bool
    {
        return stripos(strtolower($resource), "/portalfhir/") !== false;
    }

    public static function is_api_request($resource): bool
    {
        return stripos(strtolower($resource), "/api/") !== false;
    }

    public static function authentication_check($resource): void
    {
        if (!self::is_authentication($resource)) {
            $token = $_SERVER["HTTP_X_API_TOKEN"];
        }
    }

    public static function apiLog($response = '', $requestBody = ''): void
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
            $patientId = (int)($_SESSION['pid'] ?? 0);
            $userId = (int)($_SESSION['authUserID'] ?? 0);
            $api = [
                'user_id' => $userId,
                'patient_id' => $patientId,
                'method' => $method,
                'request' => $GLOBALS['resource'],
                'request_url' => $url,
                'request_body' => $requestBody,
                'response' => $response
            ];
            if ($patientId === 0) {
                $patientId = null; //entries in log table are blank for no patient_id, whereas in api_log are 0, which is why above $api value uses 0 when empty
            }
            EventAuditLogger::instance()->recordLogItem(1, $event, ($_SESSION['authUser'] ?? ''), ($_SESSION['authProvider'] ?? ''), 'api log', $patientId, $category, 'open-emr', null, null, '', $api);
        }
    }

    public static function emitResponse($response, $build = false): void
    {
        if (headers_sent()) {
            throw new RuntimeException('Headers already sent.');
        }
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true);
        foreach ($response->getHeaders() as $name => $values) {
            $responseHeader = sprintf('%s: %s', $name, $response->getHeaderLine($name));
            header($responseHeader, false);
        }
        echo $response->getBody();
    }

    public function getUserAccount($userId, $userRole = 'users')
    {
        if (!is_numeric($userId)) {
            $uuidreg = new UuidRegistry();
            $userId = $uuidreg::uuidToBytes($userId);
        }

        switch ($userRole) {
            case 'users':
                $account_sql = "SELECT `id`, `username`, `authorized`, `lname` AS lastname, `fname` AS firstname, `mname` AS middlename, `phone`, `email`, `street`, `city`, `state`, `zip`, CONCAT(fname, ' ', lname) AS fullname FROM `users`";
                if (is_numeric($userId)) {
                    $account_sql .= " WHERE `id` = ?";
                } else {
                    $account_sql .= " WHERE `uuid` = ?";
                }
                break;
            case 'patient':
                $account_sql = "SELECT `pid`, `lname` AS lastname, `fname` AS firstname, `mname` AS middlename, `phone_contact` AS phone, `sex` AS gender, `email`, `DOB` AS birthdate, `street`, `postal_code` AS zip, `city`, `state`, CONCAT(fname, ' ', lname) AS fullname FROM `patient_data`";
                if (is_numeric($userId)) {
                    $account_sql .= " WHERE `pid` = ?";
                } else {
                    $account_sql .= " WHERE `uuid` = ?";
                }
                break;
            default:
                return null;
        }

        return sqlQueryNoLog($account_sql, array($userId));
    }

    /** prevents external cloning */
    private function __clone()
    {
    }
}

// Include our routes and init routes global
//
require_once(__DIR__ . "/_rest_routes.inc.php");
