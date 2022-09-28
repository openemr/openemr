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
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\RestControllers\SMART\SMARTAuthorizationController;
use OpenEMR\Services\TrustedUserService;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Entities\ClaimSetEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
     * @var \RestConfig
     */
    private $restConfig;

    public function __construct($providerForm = true)
    {
        $gbl = \RestConfig::GetInstance();
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

        $this->smartAuthController = new SMARTAuthorizationController(
            $this->logger,
            $this->authBaseFullUrl,
            $this->authBaseFullUrl . self::ENDPOINT_SCOPE_AUTHORIZE_CONFIRM,
            __DIR__ . "/../../oauth2/"
        );

        $this->trustedUserService = new TrustedUserService();
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
            $client_id = $this->base64url_encode(RandomGenUtils::produceRandomBytes(32));
            $reg_token = $this->base64url_encode(RandomGenUtils::produceRandomBytes(32));
            $reg_client_uri_path = $this->base64url_encode(RandomGenUtils::produceRandomBytes(16));
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
                $client_secret = $this->base64url_encode(RandomGenUtils::produceRandomBytes(64));
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
            $badSave = $this->newClientSave($client_id, $params);
            if (!empty($badSave)) {
                throw OAuthServerException::serverError("Try again. Unable to create account");
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
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function base64url_decode($token)
    {
        $b64 = strtr($token, '-_', '+/');
        return base64_decode($b64);
    }

    public function newClientSave($clientId, $info): bool
    {
        $user = $_SESSION['authUserID'] ?? null; // future use for provider client.
        $site = $this->siteId;
        $is_confidential_client = empty($info['client_secret']) ? 0 : 1;

        $contacts = $info['contacts'];
        $redirects = $info['redirect_uris'];
        $logout_redirect_uris = $info['post_logout_redirect_uris'] ?? null;
        $info['client_secret'] = $info['client_secret'] ?? null; // just to be sure empty is null;
        // set our list of default scopes for the registration if our scope is empty
        // This is how a client can set if they support SMART apps and other stuff by passing in the 'launch'
        // scope to the dynamic client registration.
        // per RFC 7591 @see https://tools.ietf.org/html/rfc7591#section-2
        // TODO: adunsulag do we need to reject the registration if there are certain scopes here we do not support
        // TODO: adunsulag should we check these scopes against our '$this->supportedScopes'?
        $info['scope'] = $info['scope'] ?? 'openid email phone address api:oemr api:fhir api:port';

        $scopes = explode(" ", $info['scope']);
        $scopeRepo = new ScopeRepository();

        if ($scopeRepo->hasScopesThatRequireManualApproval($is_confidential_client == 1, $scopes)) {
            $is_client_enabled = 0; // disabled
        } else {
            $is_client_enabled = 1; // enabled
        }

        // encrypt the client secret
        if (!empty($info['client_secret'])) {
            $info['client_secret'] = $this->cryptoGen->encryptStandard($info['client_secret']);
        }


        try {
            $sql = "INSERT INTO `oauth_clients` (`client_id`, `client_role`, `client_name`, `client_secret`, `registration_token`, `registration_uri_path`, `register_date`, `revoke_date`, `contacts`, `redirect_uri`, `grant_types`, `scope`, `user_id`, `site_id`, `is_confidential`, `logout_redirect_uris`, `jwks_uri`, `jwks`, `initiate_login_uri`, `endorsements`, `policy_uri`, `tos_uri`, `is_enabled`) VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, ?, ?, 'authorization_code', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $i_vals = array(
                $clientId,
                $info['client_role'],
                $info['client_name'],
                $info['client_secret'],
                $info['registration_access_token'],
                $info['registration_client_uri_path'],
                $contacts,
                $redirects,
                $info['scope'],
                $user,
                $site,
                $is_confidential_client,
                $logout_redirect_uris,
                ($info['jwks_uri'] ?? null),
                ($info['jwks'] ?? null),
                ($info['initiate_login_uri'] ?? null),
                ($info['endorsements'] ?? null),
                ($info['policy_uri'] ?? null),
                ($info['tos_uri'] ?? null),
                $is_client_enabled
            );

            return sqlQueryNoLog($sql, $i_vals);
        } catch (\RuntimeException $e) {
            die($e);
        }
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
            $this->logger->debug("AuthorizationController->oauthAuthorizationFlow() session updated", ['session' => $_SESSION]);
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
        $responseType = new IdTokenSMARTResponse(new IdentityRepository(), new ClaimExtractor($customClaim));

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

        $patientRoleSupport = (!empty($GLOBALS['rest_portal_api']) || !empty($GLOBALS['rest_fhir_api']));

        if (empty($_POST['username']) && empty($_POST['password'])) {
            $this->logger->debug("AuthorizationController->userLogin() presenting blank login form");
            $oauthLogin = true;
            $redirect = $this->authBaseUrl . "/login";
            require_once(__DIR__ . "/../../oauth2/provider/login.php");
            exit();
        }
        $continueLogin = false;
        if (isset($_POST['user_role'])) {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'oauth2')) {
                $this->logger->error("AuthorizationController->userLogin() Invalid CSRF token");
                CsrfUtils::csrfNotVerified(false, true, false);
                unset($_POST['username'], $_POST['password']);
                $invalid = "Sorry. Invalid CSRF!"; // todo: display error
                $oauthLogin = true;
                $redirect = $this->authBaseUrl . "/login";
                require_once(__DIR__ . "/../../oauth2/provider/login.php");
                exit();
            } else {
                $this->logger->debug("AuthorizationController->userLogin() verifying login information");
                $continueLogin = $this->verifyLogin($_POST['username'], $_POST['password'], ($_POST['email'] ?? ''), $_POST['user_role']);
                $this->logger->debug("AuthorizationController->userLogin() verifyLogin result", ["continueLogin" => $continueLogin]);
            }
        }

        if (!$continueLogin) {
            $this->logger->debug("AuthorizationController->userLogin() login invalid, presenting login form");
            $invalid = "Sorry, Invalid!"; // todo: display error
            $oauthLogin = true;
            $redirect = $this->authBaseUrl . "/login";
            require_once(__DIR__ . "/../../oauth2/provider/login.php");
            exit();
        } else {
            $this->logger->debug("AuthorizationController->userLogin() login valid, continuing oauth process");
        }

        //Require MFA if turned on
        $mfa = new MfaUtils($this->userId);
        $mfaToken = $mfa->tokenFromRequest($_POST['mfa_type'] ?? null);
        $mfaType = $mfa->getType();
        $TOTP = MfaUtils::TOTP;
        $U2F = MfaUtils::U2F;
        if ($_POST['user_role'] === 'api' && $mfa->isMfaRequired() && is_null($mfaToken)) {
            $oauthLogin = true;
            $mfaRequired = true;
            $redirect = $this->authBaseUrl . "/login";
            if (in_array(MfaUtils::U2F, $mfaType)) {
                $appId = $mfa->getAppId();
                $requests = $mfa->getU2fRequests();
            }
            require_once(__DIR__ . "/../../oauth2/provider/login.php");
            exit();
        }
        //Check the validity of the authentication token
        if ($_POST['user_role'] === 'api'  && $mfa->isMfaRequired() && !is_null($mfaToken)) {
            if (!$mfaToken || !$mfa->check($mfaToken, $_POST['mfa_type'])) {
                $invalid = "Sorry, Invalid code!";
                $oauthLogin = true;
                $mfaRequired = true;
                $mfaType = $mfa->getType();
                $redirect = $this->authBaseUrl . "/login";
                require_once(__DIR__ . "/../../oauth2/provider/login.php");
                exit();
            }
        }

        unset($_POST['username'], $_POST['password']);
        $_SESSION['persist_login'] = isset($_POST['persist_login']) ? 1 : 0;
        $user = new UserEntity();
        $user->setIdentifier($_SESSION['user_id']);
        $_SESSION['claims'] = $user->getClaims();
        $oauthLogin = true;
        // need to redirect to patient select if we have a launch context && this isn't a patient login
        $authorize = 'authorize';

        // if we need to authorize any smart context as part of our OAUTH handler we do that here
        // otherwise we send on to our scope authorization confirm.
        if ($this->smartAuthController->needSmartAuthorization()) {
            $redirect = $this->authBaseFullUrl . $this->smartAuthController->getSmartAuthorizationPath();
        } else {
            $redirect = $this->authBaseFullUrl . self::ENDPOINT_SCOPE_AUTHORIZE_CONFIRM;
        }
        $this->logger->debug("AuthorizationController->userLogin() complete redirecting", ["scopes" => $_SESSION['scopes']
            , 'claims' => $_SESSION['claims'], 'redirect' => $redirect]);

        header("Location: $redirect");
        exit;
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
        require_once(__DIR__ . "/../../oauth2/provider/scope-authorize.php");
    }

    /**
     * Checks if we are in a SMART authorization endpoint
     * @param $end_point
     * @return bool
     */
    public function isSMARTAuthorizationEndPoint($end_point)
    {
        return $this->smartAuthController->isValidRoute($end_point);
    }

    /**
     * Route handler for any SMART authorization contexts that we need for OpenEMR
     * @param $end_point
     */
    public function dispatchSMARTAuthorizationEndpoint($end_point)
    {
        return $this->smartAuthController->dispatchRoute($end_point);
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
                    $result = $jsonWebKeyParser->parseRefreshToken($rawToken);
                    $result['client_id'] = $clientId;
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
}
