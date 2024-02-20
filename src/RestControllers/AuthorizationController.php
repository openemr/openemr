<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

require_once(__DIR__ . "/../Common/Session/SessionUtil.php");

use DateInterval;
use DateTimeImmutable;
use Exception;
use Google\Service\CloudHealthcare\FhirConfig;
use GuzzleHttp\Client;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Nyholm\Psr7Server\ServerRequestCreator;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\MfaUtils;
use OpenEMR\Common\Auth\OAuth2KeyConfig;
use OpenEMR\Common\Auth\OAuth2KeyException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomAuthCodeGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomPasswordGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomRefreshTokenGrant;
use OpenEMR\Common\Auth\OpenIDConnect\IdTokenSMARTResponse;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeyParser;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AuthCodeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\IdentityRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\UserRepository;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\HttpUtils;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use OpenEMR\RestControllers\SMART\SMARTAuthorizationController;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Entities\ClaimSetEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RestConfig;
use RuntimeException;
use Twig\Environment;

class AuthorizationController
{
    use CryptTrait;

    public const ENDPOINT_SCOPE_AUTHORIZE_CONFIRM = "/scope-authorize-confirm";

    public const GRANT_TYPE_PASSWORD = 'password';
    public const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    public const OFFLINE_ACCESS_SCOPE = 'offline_access';

    public $authBaseUrl;
    public $authBaseFullUrl;
    public $siteId;
    private $privateKey;
    private $passphrase;
    private $publicKey;
    private $oaEncryptionKey;
    private $grantType;
    private $providerForm;
    private $authRequestSerial;
    private $cryptoGen;
    private $userId;

    /**
     * @var SMARTAuthorizationController
     */
    private $smartAuthController;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handles CRUD operations for OAUTH2 Trusted Users
     * @var TrustedUserService
     */
    private $trustedUserService;

    /**
     * @var RestConfig
     */
    private $restConfig;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct($providerForm = true)
    {
        if (is_callable([RestConfig::class, 'GetInstance'])) {
            $gbl = RestConfig::GetInstance();
        } else {
            $gbl = \RestConfig::GetInstance();
        }
        $this->restConfig = $gbl;
        $this->logger = new SystemLogger();

        $this->siteId = $_SESSION['site_id'] ?? $gbl::$SITE;
        $this->authBaseUrl = $GLOBALS['webroot'] . '/oauth2/' . $this->siteId;
        $this->authBaseFullUrl = self::getAuthBaseFullURL();
        // used for session stash
        $this->authRequestSerial = $_SESSION['authRequestSerial'] ?? '';
        // Create a crypto object that will be used for for encryption/decryption
        $this->cryptoGen = new CryptoGen();
        // verify and/or setup our key pairs.
        $this->configKeyPairs();

        // true will display client/user server sign in. false, not.
        $this->providerForm = $providerForm;



        $this->trustedUserService = new TrustedUserService();
    }

    private function getSmartAuthController(): SMARTAuthorizationController
    {
        if (!isset($this->smartAuthController)) {
            $this->smartAuthController = new SMARTAuthorizationController(
                $this->logger,
                $this->authBaseFullUrl,
                $this->authBaseFullUrl . self::ENDPOINT_SCOPE_AUTHORIZE_CONFIRM,
                __DIR__ . "/../../oauth2/",
                $this->getTwig(),
                $GLOBALS['kernel']->getEventDispatcher()
            );
        }
        return $this->smartAuthController;
    }

    private function getTwig(): Environment
    {
        if (!isset($this->twig)) {
            $twigContainer = new TwigContainer(__DIR__ . "/../../oauth2/", $GLOBALS['kernel']);
            $this->twig = $twigContainer->getTwig();
        }
        return $this->twig;
    }

    private function configKeyPairs(): void
    {
        $response = $this->createServerResponse();
        try {
            $oauth2KeyConfig = new OAuth2KeyConfig($GLOBALS['OE_SITE_DIR']);
            $oauth2KeyConfig->configKeyPairs();
            $this->privateKey = $oauth2KeyConfig->getPrivateKeyLocation();
            $this->publicKey = $oauth2KeyConfig->getPublicKeyLocation();
            $this->oaEncryptionKey = $oauth2KeyConfig->getEncryptionKey();
            $this->passphrase = $oauth2KeyConfig->getPassPhrase();
        } catch (OAuth2KeyException $exception) {
            $this->logger->error("OpenEMR error - " . $exception->getMessage() . ", so forced exit");
            $serverException = OAuthServerException::serverError(
                "Security error - problem with authorization server keys.",
                $exception
            );
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($serverException->generateHttpResponse($response));
            exit;
        }
    }

    public function clientRegistration(): void
    {
        $response = $this->createServerResponse();
        $request = $this->createServerRequest();
        $headers = array();
        try {
            $request_headers = $request->getHeaders();
            foreach ($request_headers as $header => $value) {
                $headers[strtolower($header)] = $value[0];
            }
            if (!$headers['content-type'] || strpos($headers['content-type'], 'application/json') !== 0) {
                throw new OAuthServerException('Unexpected content type', 0, 'invalid_client_metadata');
            }
            $json = file_get_contents('php://input');
            if (!$json) {
                throw new OAuthServerException('No JSON body', 0, 'invalid_client_metadata');
            }
            $data = json_decode($json, true);
            if (!$data) {
                throw new OAuthServerException('Invalid JSON', 0, 'invalid_client_metadata');
            }
            // many of these are optional and are here if we want to implement
            $keys = array('contacts' => null,
                'application_type' => null,
                'client_name' => null,
                'logo_uri' => null,
                'redirect_uris' => null,
                'post_logout_redirect_uris' => null,
                'token_endpoint_auth_method' => array('client_secret_basic', 'client_secret_post'),
                'policy_uri' => null,
                'tos_uri' => null,
                'jwks_uri' => null,
                'jwks' => null,
                'sector_identifier_uri' => null,
                'subject_type' => array('pairwise', 'public'),
                'default_max_age' => null,
                'require_auth_time' => null,
                'default_acr_values' => null,
                'initiate_login_uri' => null, // for anything with a SMART 'launch/ehr' context we need to know how to initiate the login
                'request_uris' => null,
                'response_types' => null,
                'grant_types' => null,
                // info on scope can be seen at
                // OAUTH2 Dynamic Client Registration RFC 7591 Section 2 Page 9
                // @see https://tools.ietf.org/html/rfc7591#section-2
                'scope' => null
            );
            $clientRepository = new ClientRepository();
            $client_id = $clientRepository->generateClientId();
            $reg_token = $clientRepository->generateRegistrationAccessToken();
            $reg_client_uri_path = $clientRepository->generateRegistrationClientUriPath();
            $params = array(
                'client_id' => $client_id,
                'client_id_issued_at' => time(),
                'registration_access_token' => $reg_token,
                'registration_client_uri_path' => $reg_client_uri_path
            );

            $params['client_role'] = 'patient';
            // only include secret if a confidential app else force PKCE for native and web apps.
            $client_secret = '';
            if ($data['application_type'] === 'private') {
                $client_secret = $clientRepository->generateClientSecret();
                $params['client_secret'] = $client_secret;
                $params['client_role'] = 'user';

                // don't allow system scopes without a jwk or jwks_uri value
                if (
                    strpos($data['scope'], 'system/') !== false
                    && empty($data['jwks']) && empty($data['jwks_uri'])
                ) {
                    throw new OAuthServerException('jwks is invalid', 0, 'invalid_client_metadata');
                }
            // don't allow user, system scopes, and offline_access for public apps
            } elseif (
                strpos($data['scope'], 'system/') !== false
                || strpos($data['scope'], 'user/') !== false
            ) {
                throw new OAuthServerException("system and user scopes are only allowed for confidential clients", 0, 'invalid_client_metadata');
            }
            $this->validateScopesAgainstServerApprovedScopes($data['scope']);

            foreach ($keys as $key => $supported_values) {
                if (isset($data[$key])) {
                    if (in_array($key, array('contacts', 'redirect_uris', 'request_uris', 'post_logout_redirect_uris', 'grant_types', 'response_types', 'default_acr_values'))) {
                        $params[$key] = implode('|', $data[$key]);
                    } elseif ($key === 'jwks') {
                        $params[$key] = json_encode($data[$key]);
                    } else {
                        $params[$key] = $data[$key];
                    }
                    if (!empty($supported_values)) {
                        if (!in_array($params[$key], $supported_values)) {
                            throw new OAuthServerException("Unsupported $key value : $params[$key]", 0, 'invalid_client_metadata');
                        }
                    }
                }
            }
            if (!isset($data['redirect_uris'])) {
                throw new OAuthServerException('redirect_uris is invalid', 0, 'invalid_redirect_uri');
            }
            if (isset($data['post_logout_redirect_uris']) && empty($data['post_logout_redirect_uris'])) {
                throw new OAuthServerException('post_logout_redirect_uris is invalid', 0, 'invalid_client_metadata');
            }
            // save to oauth client table
            try {
                $clientRepository->insertNewClient($client_id, $params, $this->siteId);
            } catch (\Exception $exception) {
                throw OAuthServerException::serverError("Try again. Unable to create account", $exception);
            }
            $reg_uri = $this->authBaseFullUrl . '/client/' . $reg_client_uri_path;
            unset($params['registration_client_uri_path']);
            $client_json = array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'registration_access_token' => $reg_token,
                'registration_client_uri' => $reg_uri,
                'client_id_issued_at' => time(),
                'client_secret_expires_at' => 0
            );
            $array_params = array('contacts', 'redirect_uris', 'request_uris', 'post_logout_redirect_uris', 'response_types', 'grant_types', 'default_acr_values');
            foreach ($array_params as $aparam) {
                if (isset($params[$aparam])) {
                    $params[$aparam] = explode('|', $params[$aparam]);
                }
            }
            if (!empty($params['jwks'])) {
                $params['jwks'] = json_decode($params['jwks'], true);
            }
            if (isset($params['require_auth_time'])) {
                $params['require_auth_time'] = ($params['require_auth_time'] === 1);
            }
            // send response
            $response->withHeader("Cache-Control", "no-store");
            $response->withHeader("Pragma", "no-cache");
            $response->withHeader('Content-Type', 'application/json');
            $body = $response->getBody();
            $body->write(json_encode(array_merge($client_json, $params)));

            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($response->withStatus(200)->withBody($body));
        } catch (OAuthServerException $exception) {
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
        }
    }

    /**
     * Verifies that the scope string only has approved scopes for the system.
     * @param $scopeString the space separated scope string
     */
    private function validateScopesAgainstServerApprovedScopes($scopeString)
    {
        $requestScopes = explode(" ", $scopeString);
        if (empty($requestScopes)) {
            return;
        }

        $scopeRepo = new ScopeRepository($this->restConfig);
        $scopeRepo->setRequestScopes($scopeString);
        foreach ($requestScopes as $scope) {
            $validScope = $scopeRepo->getScopeEntityByIdentifier($scope);
            if (empty($validScope)) {
                throw OAuthServerException::invalidScope($scope);
            }
        }
    }

    private function createServerResponse(): ResponseInterface
    {
        return (new Psr17Factory())->createResponse();
    }

    private function createServerRequest(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();

        return (new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        ))->fromGlobals();
    }

    public function base64url_encode($data): string
    {
        return HttpUtils::base64url_encode($data);
    }

    public function base64url_decode($token)
    {
        $b64 = strtr($token, '-_', '+/');
        return base64_decode($b64);
    }

    public function emitResponse($response): void
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
        // send it along.
        echo $response->getBody();
    }

    public function clientRegisteredDetails(): void
    {
        $response = $this->createServerResponse();

        try {
            $token = $_REQUEST['access_token'];
            if (!$token) {
                $token = $this->getBearerToken();
                if (!$token) {
                    throw new OAuthServerException('No Access Code', 0, 'invalid_request', 403);
                }
            }
            $pos = strpos($_SERVER['PATH_INFO'], '/client/');
            if ($pos === false) {
                throw new OAuthServerException('Invalid path', 0, 'invalid_request', 403);
            }
            $uri_path = substr($_SERVER['PATH_INFO'], $pos + 8);
            $client = sqlQuery("SELECT * FROM `oauth_clients` WHERE `registration_uri_path` = ?", array($uri_path));
            if (!$client) {
                throw new OAuthServerException('Invalid client', 0, 'invalid_request', 403);
            }
            if ($client['registration_access_token'] !== $token) {
                throw new OAuthServerException('Invalid registration token', 0, 'invalid _request', 403);
            }
            $params['client_id'] = $client['client_id'];
            $params['client_secret'] = $this->cryptoGen->decryptStandard($client['client_secret']);
            $params['contacts'] = explode('|', $client['contacts']);
            $params['application_type'] = $client['client_role'];
            $params['client_name'] = $client['client_name'];
            $params['redirect_uris'] = explode('|', $client['redirect_uri']);

            $response->withHeader("Cache-Control", "no-store");
            $response->withHeader("Pragma", "no-cache");
            $response->withHeader('Content-Type', 'application/json');
            $body = $response->getBody();
            $body->write(json_encode($params));

            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($response->withStatus(200)->withBody($body));
        } catch (OAuthServerException $exception) {
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
        }
    }

    public function getBearerToken(): string
    {
        $request = $this->createServerRequest();
        $request_headers = $request->getHeaders();
        $headers = [];
        foreach ($request_headers as $header => $value) {
            $headers[strtolower($header)] = $value[0];
        }
        $authorization = $headers['authorization'];
        if ($authorization) {
            $pieces = explode(' ', $authorization);
            if (strcasecmp($pieces[0], 'bearer') !== 0) {
                return "";
            }

            return rtrim($pieces[1]);
        }
        return "";
    }

    public function oauthAuthorizationFlow(): void
    {
        $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() starting authorization flow");
        $response = $this->createServerResponse();
        $request = $this->createServerRequest();

        if ($nonce = $request->getQueryParams()['nonce'] ?? null) {
            $_SESSION['nonce'] = $request->getQueryParams()['nonce'];
        }

        $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() request query params ", ["queryParams" => $request->getQueryParams()]);

        $this->grantType = 'authorization_code';
        $server = $this->getAuthorizationServer();
        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() attempting to validate auth request");
            $authRequest = $server->validateAuthorizationRequest($request);
            $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() auth request validated, csrf,scopes,client_id setup");
            $_SESSION['csrf'] = $authRequest->getState();
            $_SESSION['scopes'] = $request->getQueryParams()['scope'];
            $_SESSION['client_id'] = $request->getQueryParams()['client_id'];
            $_SESSION['client_role'] = $authRequest->getClient()->getClientRole();
            $_SESSION['launch'] = $request->getQueryParams()['launch'] ?? null;
            $_SESSION['redirect_uri'] = $authRequest->getRedirectUri() ?? null;
            $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() session updated", ['session' => $_SESSION]);
            if (!empty($_SESSION['launch']) && $this->shouldSkipAuthorizationFlow($authRequest)) {
                $this->processAuthorizeFlowForLaunch($authRequest, $request, $response);
            }
            // If needed, serialize into a users session
            if ($this->providerForm) {
                $this->serializeUserSession($authRequest, $request);
                $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() redirecting to provider form");
                // call our login then login calls authorize if approved by user
                header("Location: " . $this->authBaseUrl . "/provider/login", true, 301);
                exit;
            }
        } catch (OAuthServerException $exception) {
            $this->logger->error(
                "AuthorizationController->oauthAuthorizationFlow() OAuthServerException",
                ["hint" => $exception->getHint(), "message" => $exception->getMessage()
                    , 'payload' => $exception->getPayload()
                    , 'trace' => $exception->getTraceAsString()
                    , 'redirectUri' => $exception->getRedirectUri()
                    , 'errorType' => $exception->getErrorType()]
            );
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
        } catch (Exception $exception) {
            $this->logger->error("AuthorizationController->oauthAuthorizationFlow() Exception message: " . $exception->getMessage());
            SessionUtil::oauthSessionCookieDestroy();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            $this->emitResponse($response->withStatus(500)->withBody($body));
        }
    }

    /**
     * Retrieve the authorization server with all of the grants configured
     * TODO: @adunsulag is there a better way to handle skipping the refresh token on the authorization grant?
     * Due to the way the server is created and the fact we have to skip the refresh token when an offline_scope is passed
     * for authorization_grant/password_grant.  We ignore offline_scope for custom_credentials
     * @param bool $includeAuthGrantRefreshToken Whether the authorization server should issue a refresh token for an authorization grant.
     * @return AuthorizationServer
     * @throws Exception
     */
    public function getAuthorizationServer($includeAuthGrantRefreshToken = true): AuthorizationServer
    {
        $protectedClaims = ['profile', 'email', 'address', 'phone'];
        $scopeRepository = new ScopeRepository($this->restConfig);
        $claims = $scopeRepository->getSupportedClaims();
        $customClaim = [];
        foreach ($claims as $claim) {
            if (in_array($claim, $protectedClaims, true)) {
                continue;
            }
            $customClaim[] = new ClaimSetEntity($claim, [$claim]);
        }
        if (!empty($_SESSION['nonce'])) {
            // nonce scope added later. this is for id token nonce claim.
            $customClaim[] = new ClaimSetEntity('nonce', ['nonce']);
        }

        // OpenID Connect Response Type
        $this->logger->debug("AuthorizationController->getAuthorizationServer() creating server");
        $responseType = new IdTokenSMARTResponse(new IdentityRepository(), new ClaimExtractor($customClaim), $this->getTwig());

        if (empty($this->grantType)) {
            $this->grantType = 'authorization_code';
        }

        // responseType is cloned inside the league auth server so we have to handle changes here before we send
        // into the $authServer the $responseType
        if ($this->grantType === 'authorization_code') {
            $responseType->markIsAuthorizationGrant(); // we have specific SMART responses for an authorization grant.
        }

        $authServer = new AuthorizationServer(
            new ClientRepository(),
            new AccessTokenRepository(),
            new ScopeRepository($this->restConfig),
            new CryptKey($this->privateKey, $this->passphrase),
            $this->oaEncryptionKey,
            $responseType
        );

        $this->logger->debug("AuthorizationController->getAuthorizationServer() grantType is " . $this->grantType);
        if ($this->grantType === 'authorization_code') {
            $this->logger->debug(
                "logging global params",
                ['site_addr_oath' => $GLOBALS['site_addr_oath'], 'web_root' => $GLOBALS['web_root'], 'site_id' => $_SESSION['site_id']]
            );
            $fhirServiceConfig = new ServerConfig();
            $expectedAudience = [
                $fhirServiceConfig->getFhirUrl(),
                $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . '/apis/' . $_SESSION['site_id'] . "/api",
                $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . '/apis/' . $_SESSION['site_id'] . "/portal",
            ];
            $grant = new CustomAuthCodeGrant(
                new AuthCodeRepository(),
                new RefreshTokenRepository($includeAuthGrantRefreshToken),
                new \DateInterval('PT1M'), // auth code. should be short turn around.
                $expectedAudience
            );

            $grant->setRefreshTokenTTL(new \DateInterval('P3M')); // minimum per ONC
            $authServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H') // access token
            );
        }
        if ($this->grantType === 'refresh_token') {
            $grant = new CustomRefreshTokenGrant(new RefreshTokenRepository());
            $grant->setRefreshTokenTTL(new \DateInterval('P3M'));
            $authServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H') // The new access token will expire after 1 hour
            );
        }
        // TODO: break this up - throw exception for not turned on.
        if (!empty($GLOBALS['oauth_password_grant']) && ($this->grantType === self::GRANT_TYPE_PASSWORD)) {
            $grant = new CustomPasswordGrant(
                new UserRepository(),
                new RefreshTokenRepository($includeAuthGrantRefreshToken)
            );
            $grant->setRefreshTokenTTL(new DateInterval('P3M'));
            $authServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H') // access token
            );
        }
        if ($this->grantType === self::GRANT_TYPE_CLIENT_CREDENTIALS) {
            // Enable the client credentials grant on the server
            $client_credentials = new CustomClientCredentialsGrant(AuthorizationController::getAuthBaseFullURL() . AuthorizationController::getTokenPath());
            $client_credentials->setLogger($this->logger);
            $client_credentials->setHttpClient(new Client()); // set our guzzle client here
            $authServer->enableGrantType(
                $client_credentials,
                // https://hl7.org/fhir/uv/bulkdata/authorization/index.html#issuing-access-tokens Spec states 5 min max
                new \DateInterval('PT300S')
            );
        }

        $this->logger->debug("AuthorizationController->getAuthorizationServer() authServer created");
        return $authServer;
    }

    private function serializeUserSession($authRequest, ServerRequestInterface $httpRequest): void
    {
        $launchParam = isset($httpRequest->getQueryParams()['launch']) ? $httpRequest->getQueryParams()['launch'] : null;
        // keeping somewhat granular
        try {
            $scopes = $authRequest->getScopes();
            $scoped = [];
            foreach ($scopes as $scope) {
                $scoped[] = $scope->getIdentifier();
            }
            $client['name'] = $authRequest->getClient()->getName();
            $client['redirectUri'] = $authRequest->getClient()->getRedirectUri();
            $client['identifier'] = $authRequest->getClient()->getIdentifier();
            $client['isConfidential'] = $authRequest->getClient()->isConfidential();
            $outer = array(
                'grantTypeId' => $authRequest->getGrantTypeId(),
                'authorizationApproved' => false,
                'redirectUri' => $authRequest->getRedirectUri(),
                'state' => $authRequest->getState(),
                'codeChallenge' => $authRequest->getCodeChallenge(),
                'codeChallengeMethod' => $authRequest->getCodeChallengeMethod(),
            );
            $result = array('outer' => $outer, 'scopes' => $scoped, 'client' => $client);
            $this->authRequestSerial = json_encode($result, JSON_THROW_ON_ERROR);
            $_SESSION['authRequestSerial'] = $this->authRequestSerial;
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function userLogin(): void
    {
        $response = $this->createServerResponse();

        $clientId = $_SESSION['client_id'] ?? null;

        $twig = $this->getTwig();
        if (empty($clientId)) {
            // why are we logging in... we need to terminate
            $this->logger->errorLogCaller("application client_id was missing when it shouldn't have been");
            echo $twig->render("error/general_http_error.html.twig", ['statusCode' => 500]);
            die();
        }
        $clientService = new ClientRepository();
        $client = $clientService->getClientEntity($clientId);

        $patientRoleSupport = (!empty($GLOBALS['rest_portal_api']) || !empty($GLOBALS['rest_fhir_api']));
        $loginTwigVars = [
            'authorize' => null
            ,'mfaRequired' => false
            ,'redirect' => $this->authBaseUrl . "/login"
            ,'isTOTP' => false
            ,'isU2F' => false
            ,'u2fRequests' => ''
            ,'appId' => ''
            ,'enforce_signin_email' => $GLOBALS['enforce_signin_email'] === '1' ?? false
            ,'user' => [
                'email' => $_POST['email'] ?? ''
                ,'username' => $_POST['username'] ?? ''
                ,'password' => $_POST['password'] ?? ''
            ]
            ,'patientRoleSupport' => $patientRoleSupport
            ,'invalid' => ''
            ,'client' => $client
        ];
        if (empty($_POST['username']) && empty($_POST['password'])) {
            $this->logger->debug("AuthorizationController->userLogin() presenting blank login form");
            $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
            exit();
        }
        $continueLogin = false;
        if (isset($_POST['user_role'])) {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'oauth2')) {
                $this->logger->error("AuthorizationController->userLogin() Invalid CSRF token");
                CsrfUtils::csrfNotVerified(false, true, false);
                unset($_POST['username'], $_POST['password']);
                $invalid = "Sorry. Invalid CSRF!"; // todo: display error
                $loginTwigVars['invalid'] = $invalid;
                $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
                exit();
            } else {
                $this->logger->debug("AuthorizationController->userLogin() verifying login information");
                $continueLogin = $this->verifyLogin($_POST['username'], $_POST['password'], ($_POST['email'] ?? ''), $_POST['user_role']);
                $this->logger->debug("AuthorizationController->userLogin() verifyLogin result", ["continueLogin" => $continueLogin]);
            }
        }

        if (!$continueLogin) {
            $this->logger->debug("AuthorizationController->userLogin() login invalid, presenting login form");
            $invalid = xl("Sorry, verify the information you have entered is correct"); // todo: display error
            $loginTwigVars['invalid'] = $invalid;
            $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
            exit();
        } else {
            $this->logger->debug("AuthorizationController->userLogin() login valid, continuing oauth process");
        }

        //Require MFA if turned on
        $mfa = new MfaUtils($this->userId);
        $mfaToken = $mfa->tokenFromRequest($_POST['mfa_type'] ?? null);
        $mfaType = $mfa->getType();
        $TOTP = MfaUtils::TOTP;
        $loginTwigVars['isTOTP'] = in_array($TOTP, $mfaType);
        if ($_POST['user_role'] === 'api' && $mfa->isMfaRequired() && is_null($mfaToken)) {
            $loginTwigVars['mfaRequired'] = true;
            if (in_array(MfaUtils::U2F, $mfaType)) {
                $loginTwigVars['appId'] = $mfa->getAppId() ?? '';
                $loginTwigVars['u2fRequests'] = $mfa->getU2fRequests() ?? '';
            }
            $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
            exit();
        }
        //Check the validity of the authentication token
        if ($_POST['user_role'] === 'api'  && $mfa->isMfaRequired() && !is_null($mfaToken)) {
            if (!$mfaToken || !$mfa->check($mfaToken, $_POST['mfa_type'])) {
                $invalid = "Sorry, Invalid code!";
                $loginTwigVars['mfaRequired'] = true;
                $loginTwigVars['invalid'] = $invalid;
                $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
                exit();
            }
        }

        unset($_POST['username'], $_POST['password']);
        $_SESSION['persist_login'] = isset($_POST['persist_login']) ? 1 : 0;
        $user = new UserEntity();
        $user->setIdentifier($_SESSION['user_id']);
        $_SESSION['claims'] = $user->getClaims();
        // need to redirect to patient select if we have a launch context && this isn't a patient login

        // if we need to authorize any smart context as part of our OAUTH handler we do that here
        // otherwise we send on to our scope authorization confirm.
        if ($this->getSmartAuthController()->needSmartAuthorization()) {
            $redirect = $this->authBaseFullUrl . $this->smartAuthController->getSmartAuthorizationPath();
        } else {
            $redirect = $this->authBaseFullUrl . self::ENDPOINT_SCOPE_AUTHORIZE_CONFIRM;
        }
        $this->logger->debug("AuthorizationController->userLogin() complete redirecting", ["scopes" => $_SESSION['scopes']
            , 'claims' => $_SESSION['claims'], 'redirect' => $redirect]);

        header("Location: $redirect");
        exit;
    }

    private function renderTwigPage($pageName, $template, $templateVars)
    {
        $twig = $this->getTwig();
        $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
        $dispatcher = $GLOBALS['kernel']->getEventDispatcher();
        $updatedTemplatePageEvent = $dispatcher->dispatch($templatePageEvent);
        $template = $updatedTemplatePageEvent->getTwigTemplate();
        $vars = $updatedTemplatePageEvent->getTwigVariables();
        // TODO: @adunsulag do we want to catch exceptions here?
        try {
            echo $twig->render($template, $vars);
        } catch (\Exception $e) {
            $this->logger->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            echo $twig->render("error/general_http_error.html.twig", ['statusCode' => 500]);
            die();
        }
    }

    public function scopeAuthorizeConfirm()
    {
        // TODO: @adunsulag if there are no scopes or claims here we probably want to show an error...

        // TODO: @adunsulag this is also where we want to show a special message if the offline scope is present.

        // show our scope auth piece
        $oauthLogin = true;
        $redirect = $this->authBaseUrl . "/device/code";
        $scopeString = $_SESSION['scopes'] ?? '';
        // check for offline_access

        $scopesList = explode(' ', $scopeString);
        $offline_requested = false;
        $scopes = [];
        foreach ($scopesList as $scope) {
            if ($scope !== self::OFFLINE_ACCESS_SCOPE) {
                $scopes[] = $scope;
            } else {
                $offline_requested = true;
            }
        }
        $offline_access_date = (new DateTimeImmutable())->add(new \DateInterval("P3M"))->format("Y-m-d");


        $claims = $_SESSION['claims'] ?? [];

        $clientRepository = new ClientRepository();
        $client = $clientRepository->getClientEntity($_SESSION['client_id']);
        $clientName = "<" . xl("Client Name Not Found") . ">";
        if (!empty($client)) {
            $clientName =  $client->getName();
        }

        $uuidToUser = new UuidUserAccount($_SESSION['user_id']);
        $userRole = $uuidToUser->getUserRole();
        $userAccount = $uuidToUser->getUserAccount();


        $oauthLogin = $oauthLogin ?? null;
        $offline_requested = $offline_requested ?? false;
        $scopes = $scopes ?? [];
        $scopeString = $scopeString ?? "";
        $offline_access_date = $offline_access_date ?? "";
        $userRole = $userRole ?? UuidUserAccount::USER_ROLE_PATIENT;
        $clientName = $clientName ?? "";

        if ($oauthLogin !== true) {
            $message = xlt("Error. Not authorized");
            SessionUtil::oauthSessionCookieDestroy();
            echo $message;
            exit();
        }

        $scopesByResource = [];
        $otherScopes = [];
        $scopeDescriptions = [];
        $hiddenScopes = [];
        $scopeRepository = new ScopeRepository();
        foreach ($scopes as $scope) {
            // if there are any other scopes we want hidden we can put it here.
            if (in_array($scope, ['openid'])) {
                $hiddenScopes[] = $scope;
            } else if (in_array($scope, $scopeRepository->fhirRequiredSmartScopes())) {
                $otherScopes[$scope] = $scopeRepository->lookupDescriptionForScope($scope, $userRole == UuidUserAccount::USER_ROLE_PATIENT);
                continue;
            }

            $parts = explode("/", $scope);
            $context = reset($parts);
            $resourcePerm = $parts[1] ?? "";
            $resourcePermParts = explode(".", $resourcePerm);
            $resource = $resourcePermParts[0] ?? "";
            $permission = $resourcePermParts[1] ?? "";

            if (!empty($resource)) {
                $scopesByResource[$resource] = $scopesByResource[$resource] ?? ['permissions' => []];

                $scopesByResource[$resource]['permissions'][$scope] = $scopeRepository->lookupDescriptionForScope($scope, $userRole == UuidUserAccount::USER_ROLE_PATIENT);
            }
        }
// sort by the resource
        ksort($scopesByResource);

        $updatedClaims = [];
        foreach ($claims as $key => $value) {
            $key_n = explode('_', $key);
            if (stripos($scopeString, $key_n[0]) === false) {
                continue;
            }
            if ((int)$value === 1) {
                $value = 'True';
            }
            $updatedKey = $key;
            $updatedClaims[$key] = $value;
            if ($key != 'fhirUser') {
                $updatedKey = ucwords(str_replace("_", " ", $key));
            }
            $scopeDescriptions[$updatedKey] = $value;
        }

        $twigVars = [
            'redirect' => $redirect
            ,'client' => $client
            ,'otherScopes' => $otherScopes
            ,'scopesByResource' => $scopesByResource
            ,'hiddenScopes' => $hiddenScopes
            ,'claims' => $updatedClaims
            ,'userAccount' => $userAccount
            ,'offlineRequested' => true == $offline_requested
            ,'offline_access_date' => $offline_access_date ?? ""
        ];
        $this->renderTwigPage('oauth2/authorize/scopes-authorize', "oauth2/scope-authorize.html.twig", $twigVars);
        die();
    }

    /**
     * Checks if we are in a SMART authorization endpoint
     * @param $end_point
     * @return bool
     */
    public function isSMARTAuthorizationEndPoint($end_point)
    {
        return $this->getSmartAuthController()->isValidRoute($end_point);
    }

    /**
     * Route handler for any SMART authorization contexts that we need for OpenEMR
     * @param $end_point
     */
    public function dispatchSMARTAuthorizationEndpoint($end_point)
    {
        return $this->getSmartAuthController()->dispatchRoute($end_point);
    }

    private function verifyLogin($username, $password, $email = '', $type = 'api'): bool
    {
        $auth = new AuthUtils($type);
        $is_true = $auth->confirmPassword($username, $password, $email);
        if (!$is_true) {
            $this->logger->debug("AuthorizationController->verifyLogin() login attempt failed", ['username' => $username, 'email' => $email, 'type' => $type]);
            return false;
        }
        // TODO: should user_id be set to be a uuid here?
        if ($this->userId = $auth->getUserId()) {
            $_SESSION['user_id'] = $this->getUserUuid($this->userId, 'users');
            $this->logger->debug("AuthorizationController->verifyLogin() user login", ['user_id' => $_SESSION['user_id'],
                'username' => $username, 'email' => $email, 'type' => $type]);
            return true;
        }
        if ($id = $auth->getPatientId()) {
            $puuid = $this->getUserUuid($id, 'patient');
            // TODO: @adunsulag check with @sjpadgett on where this user_id is even used as we are assigning it to be a uuid
            $_SESSION['user_id'] = $puuid;
            $this->logger->debug("AuthorizationController->verifyLogin() patient login", ['pid' => $_SESSION['user_id']
                , 'username' => $username, 'email' => $email, 'type' => $type]);
            $_SESSION['pid'] = $id;
            $_SESSION['puuid'] = $puuid;
            return true;
        }

        return false;
    }

    protected function getUserUuid($userId, $userRole): string
    {
        switch ($userRole) {
            case 'users':
                UuidRegistry::createMissingUuidsForTables(['users']);
                $account_sql = "SELECT `uuid` FROM `users` WHERE `id` = ?";
                break;
            case 'patient':
                UuidRegistry::createMissingUuidsForTables(['patient_data']);
                $account_sql = "SELECT `uuid` FROM `patient_data` WHERE `pid` = ?";
                break;
            default:
                return '';
        }
        $id = sqlQueryNoLog($account_sql, array($userId))['uuid'];

        return UuidRegistry::uuidToString($id);
    }

    /**
     * Note this corresponds with the /auth/code endpoint
     */
    public function authorizeUser(): void
    {
        $this->logger->debug("AuthorizationController->authorizeUser() starting authorization");
        $response = $this->createServerResponse();
        $authRequest = $this->deserializeUserSession();
        try {
            $authRequest = $this->updateAuthRequestWithUserApprovedScopes($authRequest, $_POST['scope']);
            $include_refresh_token = $this->shouldIncludeRefreshTokenForScopes($authRequest->getScopes());
            $server = $this->getAuthorizationServer($include_refresh_token);
            $user = new UserEntity();
            $user->setIdentifier($_SESSION['user_id']);
            $authRequest->setUser($user);
            $authRequest->setAuthorizationApproved(true);
            $result = $server->completeAuthorizationRequest($authRequest, $response);
            $redirect = $result->getHeader('Location')[0];
            $authorization = parse_url($redirect, PHP_URL_QUERY);
            // stash appropriate session for token endpoint.
            unset($_SESSION['authRequestSerial']);
            unset($_SESSION['claims']);
            $csrf_private_key = $_SESSION['csrf_private_key']; // switcheroo so this does not end up in the session cache
            unset($_SESSION['csrf_private_key']);
            $session_cache = json_encode($_SESSION, JSON_THROW_ON_ERROR);
            $_SESSION['csrf_private_key'] = $csrf_private_key;
            unset($csrf_private_key);
            $code = [];
            // parse scope as also a query param if needed
            parse_str($authorization, $code);
            $code = $code["code"];
            if (isset($_POST['proceed']) && !empty($code) && !empty($session_cache)) {
                if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'oauth2')) {
                    CsrfUtils::csrfNotVerified(false, true, false);
                    throw OAuthServerException::serverError("Failed authorization due to failed CSRF check.");
                } else {
                    $this->saveTrustedUser($_SESSION['client_id'], $_SESSION['user_id'], $_SESSION['scopes'], $_SESSION['persist_login'], $code, $session_cache);
                }
            } else {
                if (empty($_SESSION['csrf'])) {
                    throw OAuthServerException::serverError("Failed authorization due to missing data.");
                }
            }
            // Return the HTTP redirect response. Redirect is to client callback.
            $this->logger->debug("AuthorizationController->authorizeUser() sending server response");
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($result);
            exit;
        } catch (Exception $exception) {
            $this->logger->error("AuthorizationController->authorizeUser() Exception thrown", ["message" => $exception->getMessage()]);
            SessionUtil::oauthSessionCookieDestroy();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            $this->emitResponse($response->withStatus(500)->withBody($body));
        }
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     */
    private function shouldIncludeRefreshTokenForScopes(array $scopes)
    {
        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() == self::OFFLINE_ACCESS_SCOPE) {
                return true;
            }
        }
        return false;
    }

    private function updateAuthRequestWithUserApprovedScopes(AuthorizationRequest $request, $approvedScopes)
    {
        $this->logger->debug(
            "AuthorizationController->updateAuthRequestWithUserApprovedScopes() attempting to update auth request with user approved scopes",
            ['userApprovedScopes' => $approvedScopes ]
        );
        $requestScopes = $request->getScopes();
        $scopeUpdates = [];
        // we only allow scopes from the original session request, if user approved scope it will show up here.
        foreach ($requestScopes as $scope) {
            if (isset($approvedScopes[$scope->getIdentifier()])) {
                $scopeUpdates[] = $scope;
            }
        }
        $this->logger->debug(
            "AuthorizationController->updateAuthRequestWithUserApprovedScopes() replaced request scopes with user approved scopes",
            ['updatedScopes' => $scopeUpdates]
        );

        $request->setScopes($scopeUpdates);
        return $request;
    }

    private function deserializeUserSession(): AuthorizationRequest
    {
        $authRequest = new AuthorizationRequest();
        try {
            $requestData = $_SESSION['authRequestSerial'] ?? $this->authRequestSerial;
            $restore = json_decode($requestData, true, 512);
            $outer = $restore['outer'];
            $client = $restore['client'];
            $scoped = $restore['scopes'];
            $authRequest->setGrantTypeId($outer['grantTypeId']);
            $e = new ClientEntity();
            $e->setName($client['name']);
            $e->setRedirectUri($client['redirectUri']);
            $e->setIdentifier($client['identifier']);
            $e->setIsConfidential($client['isConfidential']);
            $authRequest->setClient($e);
            $scopes = [];
            foreach ($scoped as $scope) {
                $s = new ScopeEntity();
                $s->setIdentifier($scope);
                $scopes[] = $s;
            }
            $authRequest->setScopes($scopes);
            $authRequest->setAuthorizationApproved($outer['authorizationApproved']);
            $authRequest->setRedirectUri($outer['redirectUri']);
            $authRequest->setState($outer['state']);
            $authRequest->setCodeChallenge($outer['codeChallenge']);
            $authRequest->setCodeChallengeMethod($outer['codeChallengeMethod']);
        } catch (Exception $e) {
            echo $e;
        }

        return $authRequest;
    }

    /**
     * Note this corresponds with the /token endpoint
     */
    public function oauthAuthorizeToken(): void
    {
        $this->logger->debug("AuthorizationController->oauthAuthorizeToken() starting request");
        $response = $this->createServerResponse();
        $request = $this->createServerRequest();

        if ($request->getMethod() == 'OPTIONS') {
            // nothing to do here, just return
            $this->emitResponse($response->withStatus(200));
            return;
        }

        // authorization code which is normally only sent for new tokens
        // by the authorization grant flow.
        $code = $request->getParsedBody()['code'] ?? null;
        // grantType could be authorization_code, password or refresh_token.
        $this->grantType = $request->getParsedBody()['grant_type'];
        $this->logger->debug("AuthorizationController->oauthAuthorizeToken() grant type received", ['grant_type' => $this->grantType]);
        if ($this->grantType === 'authorization_code') {
            // re-populate from saved session cache populated in authorizeUser().
            $ssbc = $this->sessionUserByCode($code);
            $_SESSION = json_decode($ssbc['session_cache'], true);
            $this->logger->debug("AuthorizationController->oauthAuthorizeToken() restored session user from code ", ['session' => $_SESSION]);
        }
        // TODO: explore why we create the request again...
        if ($this->grantType === 'refresh_token') {
            $request = $this->createServerRequest();
        }
        // Finally time to init the server.
        $server = $this->getAuthorizationServer();
        try {
            if (($this->grantType === 'authorization_code') && empty($_SESSION['csrf'])) {
                // the saved session was not populated as expected
                $this->logger->error("AuthorizationController->oauthAuthorizeToken() CSRF check failed");
                throw new OAuthServerException('Bad request', 0, 'invalid_request', 400);
            }
            $result = $server->respondToAccessTokenRequest($request, $response);
            // save a password trusted user
            if ($this->grantType === self::GRANT_TYPE_PASSWORD) {
                $this->saveTrustedUserForPasswordGrant($result);
            }
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($result);
        } catch (OAuthServerException $exception) {
            $this->logger->debug(
                "AuthorizationController->oauthAuthorizeToken() OAuthServerException occurred",
                ["hint" => $exception->getHint(), "message" => $exception->getMessage(), "stack" => $exception->getTraceAsString()]
            );
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
        } catch (Exception $exception) {
            $this->logger->error(
                "AuthorizationController->oauthAuthorizeToken() Exception occurred",
                ["message" => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            SessionUtil::oauthSessionCookieDestroy();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            $this->emitResponse($response->withStatus(500)->withBody($body));
        }
    }

    public function trustedUser($clientId, $userId)
    {
        return $this->trustedUserService->getTrustedUser($clientId, $userId);
    }

    public function sessionUserByCode($code)
    {
        return $this->trustedUserService->getTrustedUserByCode($code);
    }

    public function saveTrustedUser($clientId, $userId, $scope, $persist, $code = '', $session = '', $grant = 'authorization_code')
    {
        return $this->trustedUserService->saveTrustedUser($clientId, $userId, $scope, $persist, $code, $session, $grant);
    }

    public function decodeToken($token)
    {
        return json_decode($this->base64url_decode($token), true);
    }

    public function userSessionLogout(): void
    {
        $message = '';
        $response = $this->createServerResponse();
        try {
            $id_token = $_REQUEST['id_token_hint'] ?? '';
            if (empty($id_token)) {
                throw new OAuthServerException('Id token missing from request', 0, 'invalid _request', 400);
            }
            $post_logout_url = $_REQUEST['post_logout_redirect_uri'] ?? '';
            $state = $_REQUEST['state'] ?? '';
            $token_parts = explode('.', $id_token);
            $id_payload = $this->decodeToken($token_parts[1]);

            $client_id = $id_payload['aud'];
            $user = $id_payload['sub'];
            $id_nonce = $id_payload['nonce'] ?? '';
            $trustedUser = $this->trustedUser($client_id, $user);
            if (empty($trustedUser['id'])) {
                // not logged in so just continue as if were.
                $message = xlt("You are currently not signed in.");
                if (!empty($post_logout_url)) {
                    SessionUtil::oauthSessionCookieDestroy();
                    header('Location:' . $post_logout_url . "?state=$state");
                } else {
                    SessionUtil::oauthSessionCookieDestroy();
                    die($message);
                }
                exit;
            }
            $session_nonce = json_decode($trustedUser['session_cache'], true)['nonce'] ?? '';
            // this should be enough to confirm valid id
            if ($session_nonce !== $id_nonce) {
                throw new OAuthServerException('Id token not issued from this server', 0, 'invalid _request', 400);
            }
            // clear the users session
            $rtn = $this->trustedUserService->deleteTrustedUserById($trustedUser['id']);
            $client = sqlQueryNoLog("SELECT logout_redirect_uris as valid FROM `oauth_clients` WHERE `client_id` = ? AND `logout_redirect_uris` = ?", array($client_id, $post_logout_url));
            if (!empty($post_logout_url) && !empty($client['valid'])) {
                SessionUtil::oauthSessionCookieDestroy();
                header('Location:' . $post_logout_url . "?state=$state");
            } else {
                $message = xlt("You have been signed out. Thank you.");
                SessionUtil::oauthSessionCookieDestroy();
                die($message);
            }
        } catch (OAuthServerException $exception) {
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
        }
    }

    public function tokenIntrospection(): void
    {
        $response = $this->createServerResponse();
        $response->withHeader("Cache-Control", "no-store");
        $response->withHeader("Pragma", "no-cache");
        $response->withHeader('Content-Type', 'application/json');

        $rawToken = $_REQUEST['token'] ?? null;
        $token_hint = $_REQUEST['token_type_hint'] ?? null;
        $clientId = $_REQUEST['client_id'] ?? null;
        // not required for public apps but mandatory for confidential
        $clientSecret = $_REQUEST['client_secret'] ?? null;

        $this->logger->debug(
            self::class . "->tokenIntrospection() start",
            ['token_type_hint' => $token_hint, 'client_id' => $clientId]
        );

        // the ride starts. had to use a try because PHP doesn't support tryhard yet!
        try {
            // so regardless of client type(private/public) we need client for client app type and secret.
            $client = sqlQueryNoLog("SELECT * FROM `oauth_clients` WHERE `client_id` = ?", array($clientId));
            if (empty($client)) {
                throw new OAuthServerException('Not a registered client', 0, 'invalid_request', 401);
            }
            // a no no. if private we need a secret.
            if (empty($clientSecret) && !empty($client['is_confidential'])) {
                throw new OAuthServerException('Invalid client app type', 0, 'invalid_request', 400);
            }
            // lets verify secret to prevent bad guys.
            if (intval($client['is_enabled'] !== 1)) {
                // client is disabled and we don't allow introspection of tokens for disabled clients.
                throw new OAuthServerException('Client failed security', 0, 'invalid_request', 401);
            }
            // lets verify secret to prevent bad guys.
            if (!empty($client['client_secret'])) {
                $decryptedSecret = $this->cryptoGen->decryptStandard($client['client_secret']);
                if ($decryptedSecret !== $clientSecret) {
                    throw new OAuthServerException('Client failed security', 0, 'invalid_request', 401);
                }
            }
            $jsonWebKeyParser = new JsonWebKeyParser($this->oaEncryptionKey, $this->publicKey);
            // will try hard to go on if missing token hint. this is to help with universal conformance.
            if (empty($token_hint)) {
                $token_hint = $jsonWebKeyParser->getTokenHintFromToken($rawToken);
            } elseif (($token_hint !== 'access_token' && $token_hint !== 'refresh_token') || empty($rawToken)) {
                throw new OAuthServerException('Missing token or unsupported hint.', 0, 'invalid_request', 400);
            }

            // are we there yet! client's okay but, is token?
            if ($token_hint === 'access_token') {
                try {
                    $result = $jsonWebKeyParser->parseAccessToken($rawToken);
                    $result['client_id'] = $clientId;
                    $trusted = $this->trustedUser($result['client_id'], $result['sub']);
                    if (empty($trusted['id'])) {
                        $result['active'] = false;
                        $result['status'] = 'revoked';
                    }
                    $tokenRepository = new AccessTokenRepository();
                    if ($tokenRepository->isAccessTokenRevokedInDatabase($result['jti'])) {
                        $result['active'] = false;
                        $result['status'] = 'revoked';
                    }
                    $audience = $result['aud'];
                    if (!empty($audience)) {
                        // audience is an array... we will only validate against the first item
                        $audience = current($audience);
                    }
                    if ($audience !== $clientId) {
                        // return no info in this case. possible Phishing
                        $result = array('active' => false);
                    }
                } catch (Exception $exception) {
                    // JWT couldn't be parsed
                    $body = $response->getBody();
                    $body->write($exception->getMessage());
                    SessionUtil::oauthSessionCookieDestroy();
                    $this->emitResponse($response->withStatus(400)->withBody($body));
                    exit();
                }
            }
            if ($token_hint === 'refresh_token') {
                try {
                    // client_id comes back from the parsed refresh token
                    $result = $jsonWebKeyParser->parseRefreshToken($rawToken);
                } catch (Exception $exception) {
                    $body = $response->getBody();
                    $body->write($exception->getMessage());
                    SessionUtil::oauthSessionCookieDestroy();
                    $this->emitResponse($response->withStatus(400)->withBody($body));
                    exit();
                }
                $trusted = $this->trustedUser($result['client_id'], $result['sub']);
                if (empty($trusted['id'])) {
                    $result['active'] = false;
                    $result['status'] = 'revoked';
                }
                $tokenRepository = new RefreshTokenRepository();
                if ($tokenRepository->isRefreshTokenRevoked($result['jti'])) {
                    $result['active'] = false;
                    $result['status'] = 'revoked';
                }
                if ($result['client_id'] !== $clientId) {
                    // return no info in this case. possible Phishing
                    $result = array('active' => false);
                }
            }
        } catch (OAuthServerException $exception) {
            // JWT couldn't be parsed
            SessionUtil::oauthSessionCookieDestroy();
            $this->logger->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $this->emitResponse($exception->generateHttpResponse($response));
            exit();
        }
        // we're here so emit results to interface thank you very much.
        $body = $response->getBody();
        $body->write(json_encode($result));
        SessionUtil::oauthSessionCookieDestroy();
        $this->emitResponse($response->withStatus(200)->withBody($body));
        exit();
    }

    /**
     * Returns the authentication server token Url endpoint
     * @return string
     */
    public function getTokenUrl()
    {
        return $this->authBaseFullUrl . self::getTokenPath();
    }

    /**
     * Returns the path prefix that the token authorization endpoint is on.
     * @return string
     */
    public static function getTokenPath()
    {
        return "/token";
    }

    /**
     * Returns the authentication server manage url
     * @return string
     */
    public function getManageUrl()
    {
        return $this->authBaseFullUrl . self::getManagePath();
    }

    /**
     * Returns the path prefix that the manage token authorization endpoint is on.
     * @return string
     */
    public static function getManagePath()
    {
        return "/manage";
    }

    /**
     * Returns the authentication server authorization url to use for oauth authentication
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return $this->authBaseFullUrl . self::getAuthorizePath();
    }

    /**
     * Returns the path prefix that the authorization endpoint is on.
     * @return string
     */
    public static function getAuthorizePath()
    {
        return "/authorize";
    }

    /**
     * Returns the authentication server registration url to use for client app / api registration
     * @return string
     */
    public function getRegistrationUrl()
    {
        return $this->authBaseFullUrl . self::getRegistrationPath();
    }

    /**
     * Returns the path prefix that the registration endpoint is on.
     * @return string
     */
    public static function getRegistrationPath()
    {
        return "/registration";
    }

    /**
     * Returns the authentication server introspection url to use for checking tokens
     * @return string
     */
    public function getIntrospectionUrl()
    {
        return $this->authBaseFullUrl . self::getIntrospectionPath();
    }

    /**
     * Returns the path prefix that the introspection endpoint is on.
     * @return string
     */
    public static function getIntrospectionPath()
    {
        return "/introspect";
    }


    public static function getAuthBaseFullURL()
    {
        $baseUrl = $GLOBALS['webroot'] . '/oauth2/' . $_SESSION['site_id'];
        // collect full url and issuing url by using 'site_addr_oath' global
        $authBaseFullURL = $GLOBALS['site_addr_oath'] . $baseUrl;
        return $authBaseFullURL;
    }

    /**
     * Given a password grant response, save the trusted user information to the database so password grant users
     * can proceed.
     * @param ServerResponseInterface $result
     */
    private function saveTrustedUserForPasswordGrant(ResponseInterface $result)
    {
        $body = $result->getBody();
        $body->rewind();
        // yep, even password grant gets one. could be useful.
        $code = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR)['id_token'];
        unset($_SESSION['csrf_private_key']); // gotta remove since binary and will break json_encode (not used for password granttype, so ok to remove)
        $session_cache = json_encode($_SESSION, JSON_THROW_ON_ERROR);
        $this->saveTrustedUser($_REQUEST['client_id'], $_SESSION['pass_user_id'], $_REQUEST['scope'], 0, $code, $session_cache, self::GRANT_TYPE_PASSWORD);
    }

    private function shouldSkipAuthorizationFlow(AuthorizationRequest $authRequest)
    {
        $skip = false;
        $client = $authRequest->getClient();
        // if don't allow our globals settings to allow skipping the authorization flow when inside an ehr launch
        // we just return false
        if ($GLOBALS['oauth_ehr_launch_authorization_flow_skip'] !== '1') {
            $this->logger->debug("AuthorizationController->shouldSkipAuthorizationFlow() - oauth_ehr_launch_authorization_flow_skip not set, not skipping even though launch is present.");
            return false;
        }
        if ($client instanceof ClientEntity) {
            if ($client->shouldSkipEHRLaunchAuthorizationFlow()) {
                $this->logger->debug("AuthorizationController->shouldSkipAuthorizationFlow() - client is configured to skip authorization flow.");
                return true;
            }
        }
        return false;
    }

    private function processAuthorizeFlowForLaunch(AuthorizationRequest $authRequest, ServerRequestInterface $request, ResponseInterface $response)
    {
        $queryParams = $request->getQueryParams();

        if (empty($queryParams['autosubmit']) || $queryParams['autosubmit'] !== '1') {
            $this->logger->debug("AuthorizationController->processAuthorizeFlowForLaunch() - autosubmit not set, redirecting to autosubmit page.");
            // we are going to display a form here with a javascript to autosubmit this page so we can make our session
            // cookies on a first party domain to verify the user is logged in.  It requires a whole page load and it's
            // a slower approach but we can then rely on the session cookie as a first party domain.
            //  We can't rely on the session cookie from the launch endpoint because of third party browser blocking
            // we don't want to deal with storing the user information in the launch token as we don't want to have to
            // deal with the security implications of the launch token being hijacked/MITM.
            $this->getSmartAuthController()->dispatchRoute(SMARTAuthorizationController::EHR_SMART_LAUNCH_AUTOSUBMIT);
            exit;
        }
        $this->logger->debug("AuthorizationController->processAuthorizeFlowForLaunch() - autosubmit set, processing authorization flow.");
        // if we have come back from an autosubmit we are going to check to see if we are logged in

        $launch = $request->getQueryParams()['launch'];
        $launchToken = SMARTLaunchToken::deserializeToken($launch);

        // if we can deserialize let's now check to see if the user is logged in
        // note this switching of sessions can slow things down a bit depending on how the php session storage is setup.
        SessionUtil::switchToCoreSession($GLOBALS['webroot'], true);
        // for now we only handle in-ehr launch for providers not patients.  We can add this later if needed.
        if (empty($_SESSION['authUserID'])) {
            $this->logger->debug("AuthorizationController->processAuthorizeFlowForLaunch() no user logged in, redirecting to login page");
            // switch back so we don't destroy the original session
            SessionUtil::switchToOAuthSession($GLOBALS['webroot']);
            return;
        }
        $userId = $_SESSION['authUserID'];
        $userService = new UserService();
        $user = $userService->getUser($userId);
        if (empty($user)) {
            // switch back so we don't destroy the original session
            SessionUtil::switchToOAuthSession($GLOBALS['webroot']);
            return;
        }
        $userUuid = $user['uuid'];
        SessionUtil::switchToOAuthSession($GLOBALS['webroot']);

        $client = $authRequest->getClient();
        // only authorize scopes specifically allowed by the client regardless of what is sent in the request
        $scopes = $client->getScopes();
        $scopesById = array_combine($scopes, $scopes);
        $authRequest = $this->updateAuthRequestWithUserApprovedScopes($authRequest, $scopesById);
        $include_refresh_token = $this->shouldIncludeRefreshTokenForScopes($authRequest->getScopes());
        $server = $this->getAuthorizationServer($include_refresh_token);

        // make sure we get our serialized session data
        $this->serializeUserSession($authRequest, $request);
        $apiSession = $_SESSION;
        $user = new UserEntity();
        $user->setIdentifier($userUuid);
        $authRequest->setUser($user);
        $authRequest->setAuthorizationApproved(true);
        $result = $server->completeAuthorizationRequest($authRequest, $response);
        $redirect = $result->getHeader('Location')[0];
        $authorization = parse_url($redirect, PHP_URL_QUERY);
        $code = [];
        // parse scope as also a query param if needed
        parse_str($authorization, $code);
        $code = $code["code"];
        $apiSession['launch'] = $launch;
        $apiSession['client_id'] = $client->getIdentifier();
        $apiSession['user_id'] = $userUuid;
        // scopes in the session are a single string.
        $apiSession['scopes'] = implode(" ", $scopes);
        $apiSession['persist_login'] = 0;
        unset($apiSession['csrf_private_key']);
        $session_cache = json_encode($apiSession, JSON_THROW_ON_ERROR);
        // now we need to get our $_SESSION['user_id']
        $this->saveTrustedUser($apiSession['client_id'], $apiSession['user_id'], $apiSession['scopes'], $apiSession['persist_login'], $code, $session_cache);

        $this->logger->debug("AuthorizationController->processAuthorizeFlowForLaunch() sending server response");
        SessionUtil::oauthSessionCookieDestroy();
        $this->emitResponse($result);
        exit;
    }
}
