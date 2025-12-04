<?php

namespace OpenEMR\RestControllers\Config;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use LogicException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Services\TrustedUserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /** @var app root is the root directory of the application */
    public static $APP_ROOT;

    /** @var root url of the application */
    public static $ROOT_URL;
    // you can guess what the rest are!
    public static $VENDOR_DIR;
    public static $SITE;
    public static $apisBaseFullUrl;
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
    public static function GetInstance(): self
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
        $serverConfig = new ServerConfig();
        $serverConfig->setWebServerRoot(self::$webserver_root);
        $serverConfig->setSiteId(self::$SITE);
        self::$ROOT_URL = self::$web_root . "/apis";
        self::$VENDOR_DIR = self::$webserver_root . "/vendor";
        self::$publicKey = $serverConfig->getPublicRestKey();
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
            $resource = substr((string) $resource, 1);
        }
        return explode('/', (string) $resource);
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
                if (strpos((string) $_SERVER['REQUEST_URI'], '?') > 0) {
                    $resource = strstr((string) $_SERVER['REQUEST_URI'], '?', true);
                } else {
                    $resource = str_replace(self::$ROOT_URL ?? '', '', $_SERVER['REQUEST_URI']);
                }
            }
        }

        return $resource;
    }

    public static function request_authorization_check(HttpRestRequest $request, $section, $value, $aclPermission = ''): void
    {
        self::authorization_check($section, $value, $request->getSession()->get("authUser"), $aclPermission);
    }

    public static function authorization_check($section, $value, $user = '', $aclPermission = ''): void
    {
        $result = AclMain::aclCheckCore($section, $value, $user, $aclPermission);
        if (!$result) {
            if (self::$notRestCall) {
                exit(); // not sure why we exit here, but this is how it was before
            }
            throw new AccessDeniedHttpException("Organization policy does not have permit access resource");
        }
    }

    // Main function to check scope
    //  Use cases:
    //     Only sending $scopeType would be for something like 'openid'
    //     For using all 3 parameters would be for something like 'user/Organization.write'
    //       $scopeType = 'user', $resource = 'Organization', $permission = 'write'
    public static function scope_check($scopeType, $resource = null, $permission = null): void
    {
        if (!empty($GLOBALS['oauth_scopes'])) {
            // Need to ensure has scope
            if (empty($resource)) {
                // Simply check to see if $scopeType is an allowed scope
                $scope = $scopeType;
            } else {
                // Resource scope check
                $scope = $scopeType . '/' . $resource . '.' . $permission;
            }
            if (!in_array($scope, $GLOBALS['oauth_scopes'])) {
                (new SystemLogger())->debug("RestConfig::scope_check scope not in access token", ['scope' => $scope, 'scopes_granted' => $GLOBALS['oauth_scopes']]);
                throw new AccessDeniedException($scope, '', 'You do not have permission to access this resource');
            }
        } else {
            (new SystemLogger())->error("RestConfig::scope_check global scope array is empty");
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unauthorized Access');
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

    public static function is_fhir_request($resource): bool
    {
        return stripos(strtolower((string) $resource), "/fhir/") !== false;
    }

    public static function is_portal_request($resource): bool
    {
        return stripos(strtolower((string) $resource), "/portal/") !== false;
    }

    public static function is_api_request($resource): bool
    {
        return stripos(strtolower((string) $resource), "/api/") !== false;
    }

    /** prevents external cloning */
    private function __clone()
    {
    }
}
