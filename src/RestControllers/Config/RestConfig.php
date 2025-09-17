<?php

namespace OpenEMR\RestControllers\Config;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
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
                    $resource = str_replace(self::$ROOT_URL ?? '', '', $_SERVER['REQUEST_URI']);
                }
            }
        }

        return $resource;
    }

    public static function verifyAccessToken()
    {
        $logger = new SystemLogger();
        $response = self::createServerResponse();
        $request = self::createServerRequest();
        try {
            // if we there's a key problem need to catch the exception
            $server = new ResourceServer(
                new AccessTokenRepository(),
                self::$publicKey
            );
            $raw = $server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            $logger->error("RestConfig->verifyAccessToken() OAuthServerException", ["message" => $exception->getMessage()]);
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            if ($exception instanceof LogicException) {
                $logger->error(
                    "RestConfig->verifyAccessToken() LogicException, likely oauth2 public key is missing, corrupted, or misconfigured",
                    ["message" => $exception->getMessage()]
                );
                return (new OAuthServerException("Invalid access token", 0, 'invalid_token', 401))
                    ->generateHttpResponse($response);
            } else {
                $logger->error("RestConfig->verifyAccessToken() Exception", ["message" => $exception->getMessage()]);
                // do NOT reveal what happened at the server level if we have a server exception
                return (new OAuthServerException("Server Error", 0, 'unknown_error', 500))
                    ->generateHttpResponse($response);
            }
        }

        return $raw;
    }

    /**
     * Returns true if the access token for the given token id is valid.  Otherwise returns the access denied response.
     * @param $tokenId
     * @return bool|ResponseInterface
     */
    public static function validateAccessTokenRevoked($tokenId)
    {
        $repository = new AccessTokenRepository();
        if ($repository->isAccessTokenRevokedInDatabase($tokenId)) {
            $response = self::createServerResponse();
            return OAuthServerException::accessDenied('Access token has been revoked')->generateHttpResponse($response);
        }
        return true;
    }

    public static function isTrustedUser($clientId, $userId)
    {
        $trustedUserService = new TrustedUserService();
        $response = self::createServerResponse();
        try {
            if (!$trustedUserService->isTrustedUser($clientId, $userId)) {
                (new SystemLogger())->debug(
                    "invalid Trusted User.  Refresh Token revoked or logged out",
                    ['clientId' => $clientId, 'userId' => $userId]
                );
                throw new OAuthServerException('Refresh Token revoked or logged out', 0, 'invalid _request', 400);
            }
            return $trustedUserService->getTrustedUser($clientId, $userId);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response);
        }
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
        SessionUtil::apiSessionCookieDestroy();
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

    public static function request_authorization_check(HttpRestRequest $request, $section, $value, $aclPermission = ''): void
    {
        self::authorization_check($section, $value, $request->getSession()->get("authUser"), $aclPermission);
    }

    public static function authorization_check($section, $value, $user = '', $aclPermission = ''): void
    {
        $result = AclMain::aclCheckCore($section, $value, $user, $aclPermission);
        if (!$result) {
            if (!self::$notRestCall) {
                throw HttpException::fromStatusCode(403, "Organization policy does not have permit access resource");
            } else {
                exit(); // not sure why we exit here, but this is how it was before
            }
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
        return stripos(strtolower($resource), "/fhir/") !== false;
    }

    public static function is_portal_request($resource): bool
    {
        return stripos(strtolower($resource), "/portal/") !== false;
    }

    public static function is_api_request($resource): bool
    {
        return stripos(strtolower($resource), "/api/") !== false;
    }

    /**
     * If the FHIR System scopes enabled or not.  True if its turned on, false otherwise.
     * @return bool
     */
    public static function areSystemScopesEnabled()
    {
        return $GLOBALS['rest_system_scopes_api'] === '1';
    }

    public function authenticateUserToken($tokenId, $clientId, $userId): bool
    {
        $ip = collectIpAddresses();

        // check for token
        $accessTokenRepo = new AccessTokenRepository();
        $authTokenExpiration = $accessTokenRepo->getTokenExpiration($tokenId, $clientId, $userId);

        if (empty($authTokenExpiration)) {
            EventAuditLogger::instance()->newEvent('api', '', '', 0, "API failure: " . $ip['ip_string'] . ". Token not found for client[" . $clientId . "] and user " . $userId . ".");
            return false;
        }

        // Ensure token not expired (note an expired token should have already been caught by oauth2, however will also check here)
        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($authTokenExpiration));
        if ($expiryDateTime <= $currentDateTime) {
            EventAuditLogger::instance()->newEvent('api', '', '', 0, "API failure: " . $ip['ip_string'] . ". Token expired for client[" . $clientId . "] and user " . $userId . ".");
            return false;
        }

        // Token authentication passed
        EventAuditLogger::instance()->newEvent('api', '', '', 1, "API success: " . $ip['ip_string'] . ". Token successfully used for client[" . $clientId . "] and user " . $userId . ".");
        return true;
    }

    /**
     * Checks if we should log the response interface (we don't want to log binary documents or anything like that)
     * We only log requests with a content-type of any form of json fhir+application/json or application/json
     * @param ResponseInterface $response
     * @return bool If the request should be logged, false otherwise
     */
    private static function shouldLogResponse(ResponseInterface $response)
    {
        if ($response->hasHeader("Content-Type")) {
            $contentType = $response->getHeaderLine("Content-Type");
            if ($contentType === 'application/json') {
                return true;
            }
        }

        return false;
    }

    /**
     * Grabs all of the context information for the request's access token and populates any context variables the
     * request needs (such as patient binding information).  Returns the populated request
     * @param HttpRestRequest $restRequest
     * @return HttpRestRequest
     */
    public function populateTokenContextForRequest(HttpRestRequest $restRequest)
    {

        $context = $this->getTokenContextForRequest($restRequest);
        // note that the context here is the SMART value that is returned in the response for an AccessToken in this
        // case it is the patient value which is the logical id (ie uuid) of the patient.
        $patientUuid = $context['patient'] ?? null;
        if (!empty($patientUuid)) {
            // we only set the bound patient access if the underlying user can still access the patient
            if ($this->checkUserHasAccessToPatient($restRequest->getRequestUserId(), $patientUuid)) {
                $restRequest->setPatientUuidString($patientUuid);
            } else {
                (new SystemLogger())->error("OpenEMR Error: api had patient launch scope but user did not have access to patient uuid."
                    . " Resources restricted with patient scopes will not return results");
            }
        } else {
            (new SystemLogger())->error("OpenEMR Error: api had patient launch scope but no patient was set in the "
                . " session cache.  Resources restricted with patient scopes will not return results");
        }
        return $restRequest;
    }

    public function getTokenContextForRequest(HttpRestRequest $restRequest)
    {
        $accessTokenRepo = new AccessTokenRepository();
        // note this is pretty confusing as getAccessTokenId comes from the oauth_access_id which is the token NOT
        // the database id even though this is called accessTokenId....
        $token = $accessTokenRepo->getTokenByToken($restRequest->getAccessTokenId());
        $context = $token['context'] ?? "{}"; // if there is no populated context we just return an empty return
        try {
            return json_decode($context, true);
        } catch (\Exception $exception) {
            (new SystemLogger())->error("OpenEMR Error: failed to decode token context json", ['exception' => $exception->getMessage()
                , 'tokenId' => $restRequest->getAccessTokenId()]);
        }
        return [];
    }


    /**
     * Checks whether a user has access to the patient. Returns true if the user can access the given patient, false otherwise
     * @param $userId The id from the users table that represents the user
     * @param $patientUuid The uuid from the patient_data table that represents the patient
     * @return bool True if has access, false otherwise
     */
    private function checkUserHasAccessToPatient($userId, $patientUuid)
    {
        // TODO: the session should never be populated with the pid from the access token unless the user had access to
        // it.  However, if we wanted an additional check or if we wanted to fire off any kind of event that does
        // patient filtering by provider / clinic we would handle that here.
        return true;
    }


    /** prevents external cloning */
    private function __clone()
    {
    }
}
