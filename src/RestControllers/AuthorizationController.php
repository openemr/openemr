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

use DateInterval;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Nyholm\Psr7\Stream;
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
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClaimRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\IdentityRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\UserRepository;
use OpenEMR\Services\JWTClientAuthenticationService;
use OpenEMR\Common\Auth\OpenIDConnect\SMARTSessionTokenContextBuilder;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpSessionFactory;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\HttpUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use OpenEMR\RestControllers\SMART\SMARTAuthorizationController;
use OpenEMR\Services\DecisionSupportInterventionService;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Entities\ClaimSetEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\Environment;
use Throwable;

use function sqlQuery;

class AuthorizationController
{
    use CryptTrait;
    use SystemLoggerAwareTrait;

    public const ENDPOINT_SCOPE_AUTHORIZE_CONFIRM = "/scope-authorize-confirm";

    public const GRANT_TYPE_PASSWORD = 'password';
    public const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    public const OFFLINE_ACCESS_SCOPE = 'offline_access';

    // https://hl7.org/fhir/uv/bulkdata/authorization/index.html#issuing-access-tokens Spec states 5 min max
    public const GRANT_TYPE_ACCESS_CODE_TTL = "PT300S"; // 5 minutes

    /**
     * The endpoint for device code authorization (authorization_grant final step)
     */
    const DEVICE_CODE_ENDPOINT = "/device/code";
    const GRANT_TYPE_ACCESS_TOKEN_TTL = 'PT1H';
    const GRANT_TYPE_REFRESH_TOKEN_TTL = 'P3M';

    public string $authBaseUrl;
    public string $authBaseFullUrl;
    public string $siteId;
    private string $privateKey;
    private string $passphrase;
    private string $publicKey;
    private string $oaEncryptionKey;
    private string $grantType;
    private string $authRequestSerial;
    private CryptoGen $cryptoGen;
    private int|string|null $userId = null;

    /**
     * @var SMARTAuthorizationController
     */
    private SMARTAuthorizationController $smartAuthController;

    /**
     * Handles CRUD operations for OAUTH2 Trusted Users
     * @var TrustedUserService
     */
    private TrustedUserService $trustedUserService;

    /**
     * @var Environment
     */
    private Environment $twig;


    /**
     * @var DecisionSupportInterventionService
     */
    private DecisionSupportInterventionService $dsiService;

    private ScopeRepository $scopeRepository;

    private ClientRepository $clientRepository;

    /**
     * @var ?callable
     */
    private $uuidUserFactory;

    private OEGlobalsBag $globalsBag;

    private string $webroot;

    private ServerConfig $serverConfig;

    /**
     * @param SessionInterface $session
     * @param OEHttpKernel $kernel
     * @param bool $providerForm
     * @throws OAuthServerException
     */
    public function __construct(
        private SessionInterface $session,
        private OEHttpKernel $kernel,
        private bool $providerForm = true
    ) {
        $globalsBag = $this->kernel->getGlobalsBag();
        $this->webroot = $globalsBag->get('webroot', '');
        $this->globalsBag = $globalsBag;
        if (empty($this->session->get('site_id'))) {
            // should never reach this but just in case
            throw OAuthServerException::serverError("OpenEMR error - unable to collect site id, so forced exit");
        }
        $this->siteId = $this->session->get('site_id');
        $this->authBaseUrl = $this->webroot . '/oauth2/' . $this->siteId;
        $this->authBaseFullUrl = self::getAuthBaseFullURL($globalsBag, $this->session);
        // used for session stash
        $this->authRequestSerial = $this->session->get('authRequestSerial', '');
        // Create a crypto object that will be used for for encryption/decryption
        $this->cryptoGen = new CryptoGen();
        // verify and/or setup our key pairs.
        $this->configKeyPairs($this->session);
        $this->trustedUserService = new TrustedUserService();
    }

    private function getSmartAuthController(): SMARTAuthorizationController
    {
        if (!isset($this->smartAuthController)) {
            $this->smartAuthController = new SMARTAuthorizationController(
                $this->session,
                $this->kernel,
                $this->authBaseFullUrl,
                $this->authBaseFullUrl . self::ENDPOINT_SCOPE_AUTHORIZE_CONFIRM,
                __DIR__ . "/../../oauth2/",
                $this->getTwig()
            );
        }
        return $this->smartAuthController;
    }

    private function getTwig(): Environment
    {
        if (!isset($this->twig)) {
            // TODO: @adunsulag look at refactoring this.  I don't like how this kernel has ended up and is incompatible
            // with our current kernel.
            $oeKernel = $this->globalsBag->get("kernel");
            if (!$oeKernel instanceof Kernel) {
                throw new RuntimeException("OpenEMR Error: Unable to get OpenEMR Kernel from globals bag");
            }
            $twigContainer = new TwigContainer(__DIR__ . "/../../oauth2/", $oeKernel);
            $this->twig = $twigContainer->getTwig();
        }
        return $this->twig;
    }

    /**
     * @param SessionInterface $session
     * @return void
     * @throws OAuthServerException
     */
    private function configKeyPairs(SessionInterface $session): void
    {
        try {
            $oauth2KeyConfig = new OAuth2KeyConfig($this->globalsBag->get('OE_SITE_DIR'));
            $oauth2KeyConfig->configKeyPairs();
            $this->privateKey = $oauth2KeyConfig->getPrivateKeyLocation();
            $this->publicKey = $oauth2KeyConfig->getPublicKeyLocation();
            $this->oaEncryptionKey = $oauth2KeyConfig->getEncryptionKey();
            $this->passphrase = $oauth2KeyConfig->getPassPhrase();
        } catch (OAuth2KeyException $exception) {
            $this->getSystemLogger()->error("OpenEMR error - " . $exception->getMessage() . ", so forced exit");
            $serverException = OAuthServerException::serverError(
                "Security error - problem with authorization server keys.",
                $exception
            );
            $session->invalidate();
            throw $serverException;
        }
    }

    public function getPublicKeyLocation(): string
    {
        return $this->publicKey;
    }

    public function clientRegistration(HttpRestRequest $request): ResponseInterface
    {
        $response = $this->createServerResponse();
        $headers = [];
        try {
            $this->getSystemLogger()->debug("AuthorizationController::clientRegistration start");
            $request_headers = $request->getHeaders();
            foreach ($request_headers as $header => $value) {
                $headers[strtolower((string) $header)] = $value[0];
            }
            if (!$headers['content-type'] || !str_starts_with($headers['content-type'], 'application/json')) {
                throw new OAuthServerException('Unexpected content type', 0, 'invalid_client_metadata');
            }
            $data = $request->getPayload();
            if (!$data) {
                throw new OAuthServerException('Invalid JSON', 0, 'invalid_client_metadata');
            }
            $this->getSystemLogger()->debug("AuthorizationController::clientRegistration passed client_metadata checks");
            // many of these are optional and are here if we want to implement
            $keys = ['contacts' => null,
                'application_type' => null,
                'client_name' => null,
                'logo_uri' => null,
                'redirect_uris' => null,
                'post_logout_redirect_uris' => null,
                'token_endpoint_auth_method' => ['client_secret_basic', 'client_secret_post', 'private_key_jwt'],
                'policy_uri' => null,
                'tos_uri' => null,
                'jwks_uri' => null,
                'jwks' => null,
                'sector_identifier_uri' => null,
                'subject_type' => ['pairwise', 'public'],
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
                'scope' => null,
                // additional meta attributes can be added here
                'dsi_type' => array_values(DecisionSupportInterventionService::DSI_TYPES),
                'dsi_source_attributes' => [] // do we care to report errors on source attributes for the values we support? they won't save if we don't have it in the system
            ];
            $this->getSystemLogger()->debug("Initial validation passed");
            $clientRepository = $this->getClientRepository();
            $client_id = $clientRepository->generateClientId();
            $reg_token = $clientRepository->generateRegistrationAccessToken();
            $reg_client_uri_path = $clientRepository->generateRegistrationClientUriPath();
            $params = [
                'client_id' => $client_id,
                'client_id_issued_at' => time(),
                'registration_access_token' => $reg_token,
                'registration_client_uri_path' => $reg_client_uri_path
            ];

            $params['client_role'] = 'patient';
            // only include secret if a confidential app else force PKCE for native and web apps.
            $client_secret = '';
            $scope = $data->getString('scope');
            if ($data->get('application_type') === 'private') {
                $client_secret = $clientRepository->generateClientSecret();
                $params['client_secret'] = $client_secret;
                $params['client_role'] = 'user';

                // don't allow system scopes without a jwk or jwks_uri value
                if (
                    str_contains($scope, 'system/')
                    && !$data->has('jwks') && !$data->has('jwks_uri')
                ) {
                    throw new OAuthServerException('jwks is invalid', 0, 'invalid_client_metadata');
                }
                // don't allow user, system scopes, and offline_access for public apps
            } elseif (
                str_contains($scope, 'system/')
                || str_contains($scope, 'user/')
            ) {
                throw new OAuthServerException("system and user scopes are only allowed for confidential clients", 0, 'invalid_client_metadata');
            }
            $this->validateScopesAgainstServerApprovedScopes($request, $scope);

            foreach ($keys as $key => $supported_values) {
                if ($data->has($key)) {
                    if (in_array($key, ['contacts', 'redirect_uris', 'request_uris', 'post_logout_redirect_uris', 'grant_types', 'response_types', 'default_acr_values'])) {
                        $params[$key] = implode('|', $data->all($key));
                    } else if (in_array($key, ['dsi_source_attributes'])) {
                        $params[$key] = $data->all($key);
                    } elseif ($key === 'jwks') {
                        $jwks = $data->all('jwks');
                        if (is_string($jwks)) {
                            $jwks = json_decode($jwks, true, 512, JSON_THROW_ON_ERROR);
                        }
                        $params[$key] = json_encode($jwks);
                    } else {
                        $params[$key] = $data->get($key);
                    }
                    if (!empty($supported_values)) {
                        if (!in_array($params[$key], $supported_values)) {
                            throw new OAuthServerException("Unsupported $key value : $params[$key]", 0, 'invalid_client_metadata');
                        }
                    }
                }
            }
            if (!$data->has('redirect_uris')) {
                throw new OAuthServerException('redirect_uris is invalid', 0, 'invalid_redirect_uri');
            }
            if ($data->has('post_logout_redirect_uris') && !$data->has('post_logout_redirect_uris')) {
                throw new OAuthServerException('post_logout_redirect_uris is invalid', 0, 'invalid_client_metadata');
            }
            // save to oauth client table
            $clientSaved = false;
            try {
                $this->startTransaction();
                $dsiService = $this->getDecisionSupportInterventionService();
                // default is none
                $dsiTypeName = $params['dsi_type'] ?? DecisionSupportInterventionService::DSI_TYPES[ClientEntity::DSI_TYPE_NONE];
                $params['dsi_type'] = $dsiService->getDsiTypeForStringName($dsiTypeName);
                $dsiSourceAttributes = $data->all('dsi_source_attributes');
                $clientRepository->insertNewClient($client_id, $params, $this->siteId);
                if ($params['dsi_type'] !== ClientEntity::DSI_TYPE_NONE) {
                    $this->createDecisionSupportInterventionServiceForType($client_id, $params['client_name'], $params['dsi_type'], $dsiSourceAttributes);
                }
                // set it back to the string name for the response
                $params['dsi_type'] = $dsiTypeName;

                $clientSaved = true;
            } catch (Exception $exception) {
                $this->getSystemLogger()->errorLogCaller("Failed to create account Exception: " . $exception->getMessage(), ['trace' => $exception->getMessage()]);
                throw OAuthServerException::serverError("Try again. Unable to create account", $exception);
            } finally {
                if ($clientSaved) {
                    $this->commitTransaction();
                } else {
                    try {
                        $this->rollbackTransaction();
                    } catch (Exception $exception) {
                        $this->getSystemLogger()->errorLogCaller("Error rolling back transaction", ['trace' => $exception->getMessage()]);
                    }
                }
            }
            $reg_uri = $this->authBaseFullUrl . '/client/' . $reg_client_uri_path;
            unset($params['registration_client_uri_path']);
            $client_json = [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'registration_access_token' => $reg_token,
                'registration_client_uri' => $reg_uri,
                'client_id_issued_at' => time(),
                'client_secret_expires_at' => 0
            ];
            $array_params = ['contacts', 'redirect_uris', 'request_uris', 'post_logout_redirect_uris', 'response_types', 'grant_types', 'default_acr_values'];
            foreach ($array_params as $aparam) {
                if (isset($params[$aparam])) {
                    $params[$aparam] = explode('|', $params[$aparam]);
                }
            }
            if (!empty($params['jwks'])) {
                $params['jwks'] = json_decode((string) $params['jwks'], true);
            }
            if (isset($params['require_auth_time'])) {
                $params['require_auth_time'] = ($params['require_auth_time'] === 1);
            }
            // send response
            $jsonBody = json_encode(array_merge($client_json, $params));
            $response = $response->withHeader("Cache-Control", "no-store")
                ->withHeader("Pragma", "no-cache")
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(Response::HTTP_OK)
                ->withBody(Stream::create($jsonBody));
            $this->session->invalidate();
            return $response;
        } catch (JsonException $exception) {
            $this->getSystemLogger()->error("Exception occurred " . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $this->session->invalidate();
            return (new OAuthServerException('No JSON body', 0, 'invalid_client_metadata'))->generateHttpResponse($response);
        } catch (OAuthServerException $exception) {
            $this->getSystemLogger()->error("Exception occurred " . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $this->session->invalidate();
            return $exception->generateHttpResponse($response);
        }
    }

    /**
     * Verifies that the scope string only has approved scopes for the system.
     * @param $scopeString string the space separated scope string
     * @throws OAuthServerException
     */
    private function validateScopesAgainstServerApprovedScopes(HttpRestRequest $request, string $scopeString): void
    {
        $this->getSystemLogger()->debug(
            "AuthorizationController->validateScopesAgainstServerApprovedScopes() - Validating scopes",
            ['scopeString' => $scopeString]
        );

        $scopeRepo = $this->getScopeRepository($this->session);
        $scopes = explode(" ", $scopeString);
        foreach ($scopes as $scope) {
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

    public function base64url_decode($token): string
    {
        $b64 = strtr($token, '-_', '+/');
        return base64_decode($b64);
    }

    public function clientRegisteredDetails(HttpRestRequest $request): ResponseInterface
    {
        $response = $this->createServerResponse();

        try {
            $token = $request->request->get('access_token');
            if (!$token) {
                $token = $this->getBearerToken();
                if (!$token) {
                    throw new OAuthServerException('No Access Code', 0, 'invalid_request', Response::HTTP_FORBIDDEN);
                }
            }
            // TODO: @adunsulag this was the server path but can't we just have it be getPathInfo()?
            $pos = strpos((string) $request->server->get('PATH_INFO'), '/client/');
            if ($pos === false) {
                throw new OAuthServerException('Invalid path', 0, 'invalid_request', Response::HTTP_FORBIDDEN);
            }
            $uri_path = substr((string) $request->server->get('PATH_INFO'), $pos + 8);
            $client = sqlQuery("SELECT * FROM `oauth_clients` WHERE `registration_uri_path` = ?", [$uri_path]);
            if (!$client) {
                throw new OAuthServerException('Invalid client', 0, 'invalid_request', Response::HTTP_FORBIDDEN);
            }
            if ($client['registration_access_token'] !== $token) {
                throw new OAuthServerException('Invalid registration token', 0, 'invalid _request', Response::HTTP_FORBIDDEN);
            }
            $params['client_id'] = $client['client_id'];
            $params['client_secret'] = $this->cryptoGen->decryptStandard($client['client_secret']);
            $params['contacts'] = explode('|', (string) $client['contacts']);
            $params['application_type'] = $client['client_role'];
            $params['client_name'] = $client['client_name'];
            $params['redirect_uris'] = explode('|', (string) $client['redirect_uri']);

            // need to grab dsi information
            $this->addDSIInformation($params, $client);
            $response->withHeader("Cache-Control", "no-store");
            $response->withHeader("Pragma", "no-cache");
            $response->withHeader('Content-Type', 'application/json');
            $body = $response->getBody();
            $body->write(json_encode($params));

            $this->session->invalidate();
            return $response->withStatus(Response::HTTP_OK)->withBody($body);
        } catch (OAuthServerException $exception) {
            $this->session->invalidate();
            return $exception->generateHttpResponse($response);
        }
    }

    public function getBearerToken(): string
    {
        $request = $this->createServerRequest();
        $request_headers = $request->getHeaders();
        $headers = [];
        foreach ($request_headers as $header => $value) {
            $headers[strtolower((string) $header)] = $value[0];
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

    private function convertHttpRestRequestToServerRequest(HttpRestRequest $httpRequest): ServerRequestInterface
    {
        $psr17Factory = new PsrHttpFactory();
        return $psr17Factory->createRequest($httpRequest);
    }

    /**
     * @param HttpRestRequest $httpRequest
     * @return ResponseInterface
     */
    public function oauthAuthorizationFlow(HttpRestRequest $httpRequest): ResponseInterface
    {
        // in order to support our POST based auth requests we need to convert any POST params to GET so the rest of the code
        // flow will work properly
        $httpRequest = $this->convertPostParamsToGet($httpRequest);
        $logger = $this->getSystemLogger();
        $logger->debug("AuthorizationController->oauthAuthorizationFlow() starting authorization flow");
        $response = $this->createServerResponse();
        $request = $this->convertHttpRestRequestToServerRequest($httpRequest);
        $session = $this->session;
        if (!empty($httpRequest->getQueryParam('nonce'))) {
            $session->set('nonce', $httpRequest->getQueryParam('nonce'));
        }

        $logger->debug("AuthorizationController->oauthAuthorizationFlow() request query params ", ["queryParams" => $request->getQueryParams()]);

        $this->grantType = 'authorization_code';
        $server = $this->getAuthorizationServer($this->getScopeRepository($this->session));
        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            $logger->debug("AuthorizationController->oauthAuthorizationFlow() attempting to validate auth request");
            $authRequest = $server->validateAuthorizationRequest($request);
            $logger->debug("AuthorizationController->oauthAuthorizationFlow() auth request validated, csrf,scopes,client_id setup");

            $session->set('csrf', $authRequest->getState());
            $session->set('scopes', $request->getQueryParams()['scope']);
            $session->set('client_id', $request->getQueryParams()['client_id']);
            if ($authRequest->getClient() instanceof ClientEntity) {
                $session->set('client_role', $authRequest->getClient()->getClientRole());
            } else {
                $this->getSystemLogger()->error(
                    "AuthorizationController->oauthAuthorizationFlow() authRequest client is not a ClientEntity and could not set client role",
                    ['client' => $authRequest->getClient()->getIdentifier()]
                );
            }
            $session->set('launch', $request->getQueryParams()['launch'] ?? null);
            $session->set('redirect_uri', $authRequest->getRedirectUri() ?? null);
            $logger->debug("AuthorizationController->oauthAuthorizationFlow() session updated", ['session' => $this->session->all()]);
            if (!empty($session->get('launch')) && $this->shouldSkipAuthorizationFlow($authRequest)) {
                $userUuid = $this->getLoggedInCoreUserUuid($httpRequest);
                if (!empty($userUuid)) {
                    return $this->processAuthorizeFlowForLaunch($authRequest, $httpRequest, $response, $userUuid);
                } // otherwise we will proceed with the authorization flow for people to login
            }

            // If needed, serialize into a users session
            if ($this->providerForm) {
                // used to keep track of the auth flow and avoid the session from being destroyed on login / patient selection
                $session->set("oauth2_in_progress", true);
                $this->serializeUserSession($authRequest, $session);
                $logger->debug("AuthorizationController->oauthAuthorizationFlow() redirecting to provider form");
                $psrFactory = new Psr17Factory();
                return $psrFactory->createResponse(Response::HTTP_TEMPORARY_REDIRECT)->withHeader("Location", $this->authBaseUrl . "/provider/login");
            } else {
                throw OAuthServerException::serverError(
                    "OpenEMR error - unable to process authorization request, provider form is not enabled"
                );
            }
        } catch (OAuthServerException $exception) {
            $logger->error(
                "AuthorizationController->oauthAuthorizationFlow() OAuthServerException",
                ["hint" => $exception->getHint(), "message" => $exception->getMessage()
                    , 'payload' => $exception->getPayload()
                    , 'trace' => $exception->getTraceAsString()
                    , 'redirectUri' => $exception->getRedirectUri()
                    , 'errorType' => $exception->getErrorType()]
            );
            $httpRequest->getSession()->invalidate();
            return $exception->generateHttpResponse($response);
        } catch (Exception $exception) {
            $logger->error("AuthorizationController->oauthAuthorizationFlow() Exception message: " . $exception->getMessage());
            $httpRequest->getSession()->invalidate();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            return $response->withStatus(Response::HTTP_INTERNAL_SERVER_ERROR)->withBody($body);
        }
    }

    /**
     * Retrieve the authorization server with all of the grants configured
     * TODO: @adunsulag is there a better way to handle skipping the refresh token on the authorization grant?
     * Due to the way the server is created and the fact we have to skip the refresh token when an offline_scope is passed
     * for authorization_grant/password_grant.  We ignore offline_scope for custom_credentials
     * @param ScopeRepository $scopeRepository
     * @param bool $includeAuthGrantRefreshToken Whether the authorization server should issue a refresh token for an authorization grant.
     * @return AuthorizationServer
     * @throws Exception
     */
    public function getAuthorizationServer(ScopeRepository $scopeRepository, bool $includeAuthGrantRefreshToken = true): AuthorizationServer
    {
        $claimRepository = new ClaimRepository();
        $claims = $claimRepository->getSupportedClaims();
        $customClaim = [];
        foreach ($claims as $claim) {
            $claimSet = $claimRepository->getClaimSetByScopeIdentifier($claim);
            if (!empty($claimSet)) {
                $customClaim[] = $claimSet;
            }
        }
        if (!empty($this->session->get('nonce'))) {
            $customClaim[] = new ClaimSetEntity('nonce', ['nonce']);
        }

        // OpenID Connect Response Type
        $this->getSystemLogger()->debug("AuthorizationController->getAuthorizationServer() creating server");
        $responseType = new IdTokenSMARTResponse(
            $this->globalsBag,
            $this->session,
            $this->getUserRepository(),
            new ClaimExtractor($customClaim),
            new SMARTSessionTokenContextBuilder($this->globalsBag, $this->session)
        );
        $responseType->setSystemLogger($this->getSystemLogger());
        if (empty($this->grantType)) {
            $this->grantType = 'authorization_code';
        }

        // responseType is cloned inside the league auth server so we have to handle changes here before we send
        // into the $authServer the $responseType
        if ($this->grantType === 'authorization_code') {
            $responseType->markIsAuthorizationGrant(); // we have specific SMART responses for an authorization grant.
        }

        $authServer = new AuthorizationServer(
            $this->getClientRepository(),
            $this->getTokenRepository(),
            $scopeRepository,
            new CryptKey($this->privateKey, $this->passphrase),
            $this->oaEncryptionKey,
            $responseType
        );

        // Create JWT authentication service for use by grants
        $jwtAuthService = new JWTClientAuthenticationService(
            $this->getServerConfig()->getTokenUrl(), // Use token URL as audience
            $this->getClientRepository(),
            new JWTRepository(),
            null // HTTP client will be created as needed
        );
        $jwtAuthService->setLogger($this->getSystemLogger());

        $this->getSystemLogger()->debug("AuthorizationController->getAuthorizationServer() grantType is " . $this->grantType);
        if ($this->grantType === 'authorization_code') {
            $this->getSystemLogger()->debug(
                "logging global params",
                ['site_addr_oath' => $this->globalsBag->get('site_addr_oath'), 'web_root' => $this->globalsBag->get('web_root'), 'site_id' => $this->session->get('site_id')]
            );
            $fhirServiceConfig = new ServerConfig();
            $expectedAudience = [
                $fhirServiceConfig->getFhirUrl(),
                $this->globalsBag->get('site_addr_oath') . $this->globalsBag->get('web_root') . '/apis/' . $this->session->get('site_id', 'default') . "/api",
                $this->globalsBag->get('site_addr_oath') . $this->globalsBag->get('web_root') . '/apis/' . $this->session->get('site_id', 'default') . "/portal",
            ];
            $grant = new CustomAuthCodeGrant(
                new AuthCodeRepository(),
                $this->getRefreshTokenRepository($includeAuthGrantRefreshToken),
                new DateInterval('PT1M'), // auth code. should be short turn around.
                $expectedAudience
            );
            // Set the JWT authentication service on the grant
            $grant->setJWTAuthenticationService($jwtAuthService);
            $grant->setSystemLogger($this->getSystemLogger());

            $grant->setRefreshTokenTTL(new DateInterval(self::GRANT_TYPE_REFRESH_TOKEN_TTL)); // minimum per ONC
            $authServer->enableGrantType(
                $grant,
                new DateInterval(self::GRANT_TYPE_ACCESS_TOKEN_TTL) // access token
            );
        }
        if ($this->grantType === 'refresh_token') {
            $grant = new CustomRefreshTokenGrant($this->session, $this->getRefreshTokenRepository());
            $grant->setRefreshTokenTTL(new DateInterval(self::GRANT_TYPE_REFRESH_TOKEN_TTL));
            // Set the JWT authentication service on the grant
            $grant->setJWTAuthenticationService($jwtAuthService);
            $authServer->enableGrantType(
                $grant,
                new DateInterval(self::GRANT_TYPE_ACCESS_TOKEN_TTL) // The new access token will expire after 1 hour
            );
        }
        // TODO: break this up - throw exception for not turned on.
        if (!empty($this->globalsBag->get('oauth_password_grant')) && ($this->grantType === self::GRANT_TYPE_PASSWORD)) {
            $grant = new CustomPasswordGrant(
                $this->session,
                $this->getUserRepository(),
                $this->getRefreshTokenRepository($includeAuthGrantRefreshToken)
            );
            $grant->setRefreshTokenTTL(new DateInterval(self::GRANT_TYPE_REFRESH_TOKEN_TTL));
            $authServer->enableGrantType(
                $grant,
                new DateInterval(self::GRANT_TYPE_ACCESS_TOKEN_TTL) // access token
            );
        }
        if ($this->grantType === self::GRANT_TYPE_CLIENT_CREDENTIALS) {
            // Enable the client credentials grant on the server
            $client_credentials = new CustomClientCredentialsGrant(
                $this->session,
                $this->authBaseFullUrl . AuthorizationController::getTokenPath()
            );
            $client_credentials->setSystemLogger($this->getSystemLogger());
            // Set the JWT authentication service on the grant
            $client_credentials->setJWTAuthenticationService($jwtAuthService);
            $authServer->enableGrantType(
                $client_credentials,
                new DateInterval(self::GRANT_TYPE_ACCESS_CODE_TTL)
            );
        }

        $this->getSystemLogger()->debug("AuthorizationController->getAuthorizationServer() authServer created");
        return $authServer;
    }

    /**
     * @param $authRequest
     * @param SessionInterface $session
     * @return void
     */
    private function serializeUserSession($authRequest, SessionInterface $session): void
    {
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
            $outer = [
                'grantTypeId' => $authRequest->getGrantTypeId(),
                'authorizationApproved' => false,
                'redirectUri' => $authRequest->getRedirectUri(),
                'state' => $authRequest->getState(),
                'codeChallenge' => $authRequest->getCodeChallenge(),
                'codeChallengeMethod' => $authRequest->getCodeChallengeMethod(),
            ];
            $result = ['outer' => $outer, 'scopes' => $scoped, 'client' => $client];
            $this->authRequestSerial = json_encode($result, JSON_THROW_ON_ERROR);
            $session->set('authRequestSerial', $this->authRequestSerial);
        } catch (Exception $e) {
            echo $e;
        }
    }

    /**
     * @param HttpRestRequest $request
     * @return ResponseInterface
     */
    public function userLogin(HttpRestRequest $request): ResponseInterface
    {
        $session = $this->session;
        $clientId = $session->get('client_id');
        if (empty($clientId)) {
            // why are we logging in... we need to terminate
            $this->getSystemLogger()->errorLogCaller("application client_id was missing when it shouldn't have been");
            return $this->renderTwigPage(
                'oauth2/authorize/login',
                "error/general_http_error.html.twig",
                ['statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR]
            );
        }

        $clientService = $this->getClientRepository();
        $client = $clientService->getClientEntity($clientId);

        $patientRoleSupport = (!empty($this->globalsBag->get('rest_portal_api')) || !empty($this->globalsBag->get('rest_fhir_api')));
        $loginTwigVars = [
            'authorize' => null
            ,'mfaRequired' => false
            ,'redirect' => $this->authBaseUrl . "/login"
            ,'isTOTP' => false
            ,'isU2F' => false
            ,'u2fRequests' => ''
            ,'appId' => ''
            ,'enforce_signin_email' => $this->globalsBag->get('enforce_signin_email', 0) === '1'
            ,'user' => [
                'email' => $request->request->get('email', '')
                ,'username' => $request->request->get('username', '')
                ,'password' => $request->request->get('password', '')
            ]
            ,'patientRoleSupport' => $patientRoleSupport
            ,'invalid' => ''
            ,'client' => $client
            ,'csrfToken' => CsrfUtils::collectCsrfToken('oauth2', $session)
        ];
        if (empty($request->request->get('username')) && empty($request->request->get('password'))) {
            $this->getSystemLogger()->debug("AuthorizationController->userLogin() presenting blank login form");
            return $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
        }
        $continueLogin = false;
        if ($request->request->has('user_role')) {
            if (!CsrfUtils::verifyCsrfToken($request->request->get("csrf_token_form"), 'oauth2', $session)) {
                $this->getSystemLogger()->error("AuthorizationController->userLogin() Invalid CSRF token");
                CsrfUtils::csrfNotVerified(false, true, false);
                $request->request->replace(); // clear out username/password
                $request->overrideGlobals(); // override the globals with the cleared out request so we don't have the username/password in the request sequence
                $invalid = "Sorry. Invalid CSRF!"; // todo: display error
                $loginTwigVars['invalid'] = $invalid;
                return $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
            } else {
                $this->getSystemLogger()->debug("AuthorizationController->userLogin() verifying login information");
                $continueLogin = $this->verifyLogin(
                    $request->request->get('username'),
                    $request->request->get('password'),
                    $request->request->get('email', ''),
                    $request->request->get('user_role')
                );
                $this->getSystemLogger()->debug("AuthorizationController->userLogin() verifyLogin result", ["continueLogin" => $continueLogin]);
            }
        }

        if (!$continueLogin) {
            $this->getSystemLogger()->debug("AuthorizationController->userLogin() login invalid, presenting login form");
            $invalid = xl("Sorry, verify the information you have entered is correct"); // todo: display error
            $loginTwigVars['invalid'] = $invalid;
            return $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
        } else {
            $this->getSystemLogger()->debug("AuthorizationController->userLogin() login valid, continuing oauth process");
        }

        //Require MFA if turned on
        try {
            $mfa = new MfaUtils($this->userId);
            $mfaToken = $mfa->tokenFromRequest($request->request->get('mfa_type'));
            $mfaType = $mfa->getType();
            $TOTP = MfaUtils::TOTP;
            $loginTwigVars['isTOTP'] = in_array($TOTP, $mfaType);
            if ($request->request->get('user_role') === 'api' && $mfa->isMfaRequired() && is_null($mfaToken)) {
                $loginTwigVars['mfaRequired'] = true;
                if (in_array(MfaUtils::U2F, $mfaType)) {
                    $loginTwigVars['appId'] = $mfa->getAppId() ?? '';
                    $loginTwigVars['u2fRequests'] = $mfa->getU2fRequests() !== false ? $mfa->getU2fRequests() : '';
                }
                return $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
            }
            //Check the validity of the authentication token
            if ($request->request->get('user_role') === 'api'  && $mfa->isMfaRequired() && !is_null($mfaToken)) {
                if (!$mfaToken || !$mfa->check($mfaToken, $request->get('mfa_type'))) {
                    $invalid = xl("Sorry, Invalid code!");
                    $loginTwigVars['mfaRequired'] = true;
                    $loginTwigVars['invalid'] = $invalid;
                    return $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
                }
            }
        } catch (Throwable $error) {
            $loginTwigVars['mfaRequired'] = true;
            $loginTwigVars['invalid'] = xl("Sorry, an error occurred while processing your request. Please try again later.");
            $this->getSystemLogger()->errorLogCaller("failed to process MFA Error:" . $error->getMessage(), ['trace' => $error->getTraceAsString()]);
            // NOTE: if twig throws an exception, we have a problem here.
            return $this->renderTwigPage('oauth2/authorize/login', 'oauth2/oauth2-login.html.twig', $loginTwigVars);
        }

        $request->request->remove('username');
        $request->request->remove('password');
        $request->overrideGlobals(); // override the globals with the cleared out request so we don't have the username/password in the request sequence
        $session->set('persist_login', $request->request->has('persist_login') ? 1 : 0);
        $user = $this->getUserRepository()->getUserEntityByIdentifier($session->get('user_id'));
        $session->set('claims', $user->getClaims());
        // need to redirect to patient select if we have a launch context && this isn't a patient login

        // if we need to authorize any smart context as part of our OAUTH handler we do that here
        // otherwise we send on to our scope authorization confirm.
        if ($this->getSmartAuthController()->needSmartAuthorization()) {
            $redirect = $this->authBaseFullUrl . $this->smartAuthController->getSmartAuthorizationPath();
        } else {
            $redirect = $this->authBaseFullUrl . self::ENDPOINT_SCOPE_AUTHORIZE_CONFIRM;
        }
        $this->getSystemLogger()->debug(
            "AuthorizationController->userLogin() complete redirecting",
            ["scopes" => $session->get('scopes', '')
            ,
            'claims' => $session->get(
                'claims',
                []
            ),
            'redirect' => $redirect]
        );

        return $this->createServerResponse()->withStatus(Response::HTTP_TEMPORARY_REDIRECT)->withHeader("Location", $redirect);
    }

    private function renderTwigPage($pageName, $template, $templateVars): ResponseInterface
    {
        $twig = $this->getTwig();
        $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
        $dispatcher = $this->kernel->getEventDispatcher();
        $updatedTemplatePageEvent = $dispatcher->dispatch($templatePageEvent);
        $template = $updatedTemplatePageEvent->getTwigTemplate();
        $vars = $updatedTemplatePageEvent->getTwigVariables();
        // TODO: @adunsulag do we want to catch exceptions here?
        try {
            $responseBody = $twig->render($template, $vars);
        } catch (Exception $e) {
            $this->getSystemLogger()->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $responseBody = $twig->render("error/general_http_error.html.twig", ['statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        $factory = new Psr17Factory();
        return $factory->createResponse(Response::HTTP_OK)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withBody($factory->createStream($responseBody));
    }

    public function setUuidUserAccountFactory(callable $uuidUserFactory): void
    {
        $this->uuidUserFactory = $uuidUserFactory;
    }

    public function setClientRepository(ClientRepository $clientRepository): void
    {
        $this->clientRepository = $clientRepository;
    }

    public function getClientRepository(): ClientRepository
    {
        if (!isset($this->clientRepository)) {
            $clientRepository = new ClientRepository();
            $clientRepository->setSystemLogger($this->getSystemLogger());
            $this->clientRepository = $clientRepository;
        }
        return $this->clientRepository;
    }

    public function getScopeRepository(SessionInterface $session): ScopeRepository
    {
        if (isset($this->scopeRepository)) {
            return $this->scopeRepository;
        } else {
            $scopeRepository = new ScopeRepository($session);
            $scopeRepository->setServerConfig($this->getServerConfig());
            $scopeRepository->setSystemLogger($this->getSystemLogger());
            return $scopeRepository;
        }
    }

    public function getServerConfig(): ServerConfig
    {
        if (!isset($this->serverConfig)) {
            $this->serverConfig = new ServerConfig();
        }
        return $this->serverConfig;
    }

    public function setScopeRepository(ScopeRepository $scopeRepository): void
    {
        $this->scopeRepository = $scopeRepository;
    }

    public function getUuidUserAccount($userId): UuidUserAccount
    {
        if (is_callable($this->uuidUserFactory)) {
            return call_user_func($this->uuidUserFactory, [$userId]);
        } else {
            return new UuidUserAccount($userId);
        }
    }

    public function scopeAuthorizeConfirm(HttpRestRequest $request): ResponseInterface
    {
        // TODO: @adunsulag if there are no scopes or claims here we probably want to show an error...
        // show our scope auth piece
        $redirect = $this->authBaseUrl . self::DEVICE_CODE_ENDPOINT;
        $session = $this->session;
        $scopeString = $session->get('scopes', '');
        // check for offline_access

        $scopesList = explode(' ', (string) $scopeString);
        $offline_requested = false;
        $scopes = [];
        foreach ($scopesList as $scope) {
            if ($scope !== self::OFFLINE_ACCESS_SCOPE) {
                $scopes[] = $scope;
            } else {
                $offline_requested = true;
            }
        }
        $offline_access_date = (new DateTimeImmutable())->add(new DateInterval(self::GRANT_TYPE_REFRESH_TOKEN_TTL))->format("Y-m-d");
        $claims = $session->get('claims', []);

        $clientRepository = $this->getClientRepository();
        $client = $clientRepository->getClientEntity($session->get('client_id', []));

        $uuidToUser = $this->getUuidUserAccount($session->get('user_id', ''));
        $userRole = $uuidToUser->getUserRole();
        $userAccount = $uuidToUser->getUserAccount();
        $scopeString ??= "";
        $userRole ??= UuidUserAccount::USER_ROLE_PATIENT;
        $scopesByResource = [];
        $otherScopes = [];
        $hiddenScopes = [];
        $scopeRepository = $this->getScopeRepository($session);
        $fhirRequiredSmartScopes = $scopeRepository->fhirRequiredSmartScopes();
        foreach ($scopes as $scope) {
            // if there are any other scopes we want hidden we can put it here.
            if ($scope == 'openid') {
                $hiddenScopes[] = $scope;
            } else if (in_array($scope, $fhirRequiredSmartScopes)) {
                $otherScopes[$scope] = $scopeRepository->lookupDescriptionForScope($scope);
                continue;
            }

            $parts = explode("/", $scope);
            reset($parts);
            $resourcePerm = $parts[1] ?? "";
            $resourcePermParts = explode(".", $resourcePerm);
            $resource = $resourcePermParts[0];

            if (!empty($resource)) {
                $scopesByResource[$resource] ??= ['permissions' => []];

                $scopesByResource[$resource]['permissions'][$scope] = $scopeRepository->lookupDescriptionForScope($scope);
            }
        }
        // TODO: @adunsulag need to fire off an event here so that api writers can grab descriptions or update them for their scopes

// sort by the resource
        ksort($scopesByResource);

        $updatedClaims = [];
        foreach ($claims as $key => $value) {
            $key_n = explode('_', (string) $key);
            if (stripos((string) $scopeString, $key_n[0]) === false) {
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
            ,'offline_access_date' => $offline_access_date
            , "csrfToken" => CsrfUtils::collectCsrfToken('oauth2', $session)
        ];
        return $this->renderTwigPage('oauth2/authorize/scopes-authorize', "oauth2/scope-authorize.html.twig", $twigVars);
    }

    /**
     * Checks if we are in a SMART authorization endpoint
     * @param $end_point
     * @return bool
     */
    public function isSMARTAuthorizationEndPoint($end_point): bool
    {
        return $this->getSmartAuthController()->isValidRoute($end_point);
    }

    /**
     * Route handler for any SMART authorization contexts that we need for OpenEMR
     * @param $end_point
     */
    public function dispatchSMARTAuthorizationEndpoint($end_point, HttpRestRequest $request): ResponseInterface
    {
        return $this->getSmartAuthController()->dispatchRoute($end_point, $request);
    }

    private function verifyLogin($username, $password, $email = '', $type = 'api'): bool
    {
        $session = $this->session;
        $auth = new AuthUtils($type);
        $is_true = $auth->confirmPassword($username, $password, $email);
        if (!$is_true) {
            $this->getSystemLogger()->debug("AuthorizationController->verifyLogin() login attempt failed", ['username' => $username, 'email' => $email, 'type' => $type]);
            return false;
        } else {
            $this->getSystemLogger()->debug("AuthorizationController->verifyLogin() login attempt passed", ['username' => $username, 'email' => $email, 'type' => $type]);
        }
        // TODO: should user_id be set to be a uuid here?
        $userId = $auth->getUserId();
        $this->getSystemLogger()->debug("AuthorizationController->verifyLogin() getUserId", ['username' => $username, 'userId' => $userId, 'email' => $email, 'type' => $type]);
        if (isset($userId) && $this->userId = $userId) {
            $this->session->set('user_id', $this->getUserUuid($this->userId, 'users'));
            $this->getSystemLogger()->debug("AuthorizationController->verifyLogin() user login", ['user_id' => $session->get('user_id'),
                'username' => $username, 'email' => $email, 'type' => $type]);
            return true;
        }
        if ($id = $auth->getPatientId()) {
            $puuid = $this->getUserUuid($id, 'patient');
            // TODO: @adunsulag check with @sjpadgett on where this user_id is even used as we are assigning it to be a uuid
            $this->session->set('user_id', $puuid);
            $this->getSystemLogger()->debug("AuthorizationController->verifyLogin() patient login", ['pid' => $session->get('user_id')
                , 'username' => $username, 'email' => $email, 'type' => $type]);
            $this->session->set('pid', $id);
            $this->session->set('puuid', $puuid);
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
        $id = sqlQueryNoLog($account_sql, [$userId])['uuid'];

        return UuidRegistry::uuidToString($id);
    }

    /**
     * Note this corresponds with the /auth/code endpoint
     */
    public function authorizeUser(HttpRestRequest $request): ResponseInterface
    {
        $response = $this->createServerResponse();
        $authRequest = $this->deserializeUserSession();
        try {
            $authRequest = $this->updateAuthRequestWithUserApprovedScopes($authRequest, $request->request->all('scope'));
            $include_refresh_token = $this->shouldIncludeRefreshTokenForScopes($authRequest->getScopes());
            $server = $this->getAuthorizationServer($this->getScopeRepository($this->session), $include_refresh_token);

            $user = $this->getUserRepository()->getUserEntityByIdentifier($this->session->get('user_id'));
            $authRequest->setUser($user);
            $authRequest->setAuthorizationApproved(true);
            $result = $server->completeAuthorizationRequest($authRequest, $response);
            $redirect = $result->getHeader('Location')[0];
            $authorization = parse_url($redirect, PHP_URL_QUERY);
            // stash appropriate session for token endpoint.
            $this->session->remove('authRequestSerial');
            $this->session->remove('claims');
            $csrf_private_key = $this->session->get('csrf_private_key'); // switcheroo so this does not end up in the session cache
            $this->session->remove('csrf_private_key');
            $session_cache = json_encode($this->session->all(), JSON_THROW_ON_ERROR);
            $this->session->set('csrf_private_key', $csrf_private_key);
            unset($csrf_private_key);
            $code = [];
            // parse scope as also a query param if needed
            parse_str($authorization, $code);
            $code = $code["code"];
            // TODO: @adunsulag if the request is missing the key 'proceed' then we should error out here.
            if ($request->request->has('proceed') && !empty($code) && !empty($session_cache)) {
                if (!CsrfUtils::verifyCsrfToken($request->request->get("csrf_token_form"), 'oauth2', $this->session)) {
                    CsrfUtils::csrfNotVerified(false, true, false);
                    throw OAuthServerException::serverError("Failed authorization due to failed CSRF check.");
                } else {
                    $this->saveTrustedUser(
                        $this->session->get('client_id'),
                        $this->session->get('user_id'),
                        $this->session->get('scopes'),
                        $this->session->get('persist_login'),
                        $code,
                        $session_cache
                    );
                }
            } else {
                if (empty($this->session->get('csrf'))) {
                    throw OAuthServerException::serverError("Failed authorization due to missing data.");
                }
            }
            // Return the HTTP redirect response. Redirect is to client callback.
            $this->getSystemLogger()->debug("AuthorizationController->authorizeUser() sending server response");
            $this->session->invalidate();
            return $result;
        } catch (Exception $exception) {
            $this->getSystemLogger()->error("AuthorizationController->authorizeUser() Exception thrown", ["message" => $exception->getMessage()]);
            $this->session->invalidate();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            return $response->withStatus(Response::HTTP_INTERNAL_SERVER_ERROR)->withBody($body);
        }
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     */
    private function shouldIncludeRefreshTokenForScopes(array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() == self::OFFLINE_ACCESS_SCOPE) {
                return true;
            }
        }
        return false;
    }

    private function updateAuthRequestWithUserApprovedScopes(AuthorizationRequest $request, $approvedScopes): AuthorizationRequest
    {
        $this->getSystemLogger()->debug(
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
        $this->getSystemLogger()->debug(
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
            $requestData = $this->session->get('authRequestSerial', $this->authRequestSerial);
            $restore = json_decode((string) $requestData, true);
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
    public function oauthAuthorizeToken(HttpRestRequest $request): ResponseInterface
    {
        $this->getSystemLogger()->debug("AuthorizationController->oauthAuthorizeToken() starting request");
        $response = $this->createServerResponse();

        if ($request->getMethod() == 'OPTIONS') {
            // nothing to do here, just return
            return $response->withStatus(Response::HTTP_OK);
        }

        // authorization code which is normally only sent for new tokens
        // by the authorization grant flow.
        $code = $request->getParsedBody()['code'] ?? null;
        // grantType could be authorization_code, password or refresh_token.
        $this->grantType = $request->getParsedBody()['grant_type'];
        $this->getSystemLogger()->debug("AuthorizationController->oauthAuthorizeToken() grant type received", ['grant_type' => $this->grantType]);
        if ($this->grantType === 'authorization_code') {
            // re-populate from saved session cache populated in authorizeUser().
            $ssbc = $this->sessionUserByCode($code);
            if (!empty($ssbc)) {
                $this->session->replace(json_decode((string) $ssbc['session_cache'], true));
            } else {
                // TODO: @adunsulag should we throw an exception here?
            }

            $this->getSystemLogger()->debug(
                "AuthorizationController->oauthAuthorizeToken() restored session user from code ",
                ['session' => $this->session->all()]
            );
        }
        $leagueRequest = $this->convertHttpRestRequestToServerRequest($request);
        // TODO: explore why we create the request again...
        if ($this->grantType === 'refresh_token') {
            $leagueRequest = $this->createServerRequest();
        }
        // Finally time to init the server.
        $server = $this->getAuthorizationServer($this->getScopeRepository($this->session));
        try {
            if (($this->grantType === 'authorization_code') && empty($this->session->get('csrf'))) {
                // the saved session was not populated as expected
                $this->getSystemLogger()->error("AuthorizationController->oauthAuthorizeToken() CSRF check failed");
                throw new OAuthServerException('Bad request', 0, 'invalid_request', Response::HTTP_BAD_REQUEST);
            }
            $result = $server->respondToAccessTokenRequest($leagueRequest, $response);
            // save a password trusted user
            if ($this->grantType === self::GRANT_TYPE_PASSWORD) {
                $this->saveTrustedUserForPasswordGrant($request, $result);
            }
            $this->getSystemLogger()->debug("AuthorizationController->oauthAuthorizeToken() responded to access token request");
            $this->session->invalidate();
            return $result;
        } catch (OAuthServerException $exception) {
            $this->getSystemLogger()->debug(
                "AuthorizationController->oauthAuthorizeToken() OAuthServerException occurred",
                ["hint" => $exception->getHint(), "message" => $exception->getMessage(), "stack" => $exception->getTraceAsString()]
            );
            $this->session->invalidate();
            return $exception->generateHttpResponse($response);
        } catch (Exception $exception) {
            $this->getSystemLogger()->error(
                "AuthorizationController->oauthAuthorizeToken() Exception occurred",
                ["message" => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            $this->session->invalidate();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            return $response->withStatus(Response::HTTP_INTERNAL_SERVER_ERROR)->withBody($body);
        }
    }

    public function trustedUser($clientId, $userId): array|false
    {
        return $this->trustedUserService->getTrustedUser($clientId, $userId);
    }

    public function sessionUserByCode($code): array|false
    {
        return $this->trustedUserService->getTrustedUserByCode($code);
    }

    public function saveTrustedUser($clientId, $userId, $scope, $persist, $code = '', $session = '', $grant = 'authorization_code'): bool
    {
        if ($this->trustedUserService->saveTrustedUser($clientId, $userId, $scope, $persist, $code, $session, $grant) !== false) {
            return true;
        }
        return false;
    }

    public function decodeToken($token)
    {
        return json_decode($this->base64url_decode($token), true);
    }

    public function userSessionLogout(HttpRestRequest $request): ResponseInterface
    {
        $message = '';
        $response = $this->createServerResponse();
        $client_id = '';
        try {
            $id_token = $request->query->get('id_token_hint', '');
            if (empty($id_token)) {
                throw new OAuthServerException('Id token missing from request', 0, 'invalid _request', Response::HTTP_BAD_REQUEST);
            }
            $post_logout_url = $request->query->get('post_logout_redirect_uri', '');
            $state = $request->get('state', '');
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
                    $this->session->invalidate();
                    return (new Psr17Factory())->createResponse(Response::HTTP_TEMPORARY_REDIRECT)
                        ->withHeader('Location', $post_logout_url . "?state=$state");
                } else {
                    $this->session->invalidate();
                    throw new HttpException(Response::HTTP_UNAUTHORIZED, $message);
                }
            }
            $session_nonce = json_decode((string) $trustedUser['session_cache'], true)['nonce'] ?? '';
            // this should be enough to confirm valid id
            if ($session_nonce !== $id_nonce) {
                throw new OAuthServerException('Id token not issued from this server', 0, 'invalid _request', Response::HTTP_BAD_REQUEST);
            }
            // clear the users session
            $this->trustedUserService->deleteTrustedUserById($trustedUser['id']);
            $client = sqlQueryNoLog("SELECT logout_redirect_uris as valid FROM `oauth_clients` WHERE `client_id` = ? AND `logout_redirect_uris` = ?", [$client_id, $post_logout_url]);
            if (!empty($post_logout_url) && !empty($client['valid'])) {
                $this->session->invalidate();
                return (new Psr17Factory())->createResponse(Response::HTTP_TEMPORARY_REDIRECT)
                    ->withHeader('Location', $post_logout_url . "?state=$state");
            } else {
                $message = xlt("You have been signed out. Thank you.");
                $this->session->invalidate();
                $factory = new Psr17Factory();
                return $factory->createResponse(Response::HTTP_OK)
                    ->withHeader("Content-Type", "text/plain; charset=UTF-8")
                    ->withBody($factory->createStream($message));
            }
        } catch (OAuthServerException $exception) {
            $this->getSystemLogger()->errorLogCaller($exception->getMessage(), ['client_id' => $client_id]);
            $this->session->invalidate();
            return $exception->generateHttpResponse($response);
        }
    }

    public function tokenIntrospection(HttpRestRequest $request): ResponseInterface
    {
        $response = $this->createServerResponse();
        $response->withHeader("Cache-Control", "no-store");
        $response->withHeader("Pragma", "no-cache");
        $response->withHeader('Content-Type', 'application/json');

        $rawToken = $request->request->get('token');
        $token_hint = $request->request->get('token_type_hint');
        $clientId = $request->request->get('client_id');
        // not required for public apps but mandatory for confidential
        $clientSecret = $request->request->get('client_secret');

        // Check for JWT client assertion
        $clientAssertion = $request->request->get('client_assertion');
        $clientAssertionType = $request->request->get('client_assertion_type');

        $this->getSystemLogger()->debug(
            self::class . "->tokenIntrospection() start",
            ['token_type_hint' => $token_hint, 'client_id' => $clientId, 'has_assertion' => !empty($clientAssertion)]
        );

        $result = ['active' => false];
        // the ride starts. had to use a try because PHP doesn't support tryhard yet!
        try {
            // Handle JWT client authentication if present
            if (!empty($clientAssertion) && !empty($clientAssertionType)) {
                // Create JWT authentication service
                $jwtAuthService = new JWTClientAuthenticationService(
                    $this->getServerConfig()->getTokenUrl(),
                    $this->getClientRepository(),
                    new JWTRepository(),
                    null
                );
                $jwtAuthService->setLogger($this->getSystemLogger());

                // Convert the request to PSR-7 format for JWT validation
                $psrRequest = $this->convertHttpRestRequestToServerRequest($request);

                // Extract client ID from JWT and validate
                if ($jwtAuthService->hasJWTClientAssertion($psrRequest)) {
                    $extractedClientId = $jwtAuthService->extractClientIdFromJWT($psrRequest);

                    // Verify extracted client ID matches provided client_id if both present
                    if (!empty($clientId) && $clientId !== $extractedClientId) {
                        throw new OAuthServerException('Client ID mismatch', 0, 'invalid_request', Response::HTTP_BAD_REQUEST);
                    }

                    $clientId = $extractedClientId;
                    $client = QueryUtils::querySingleRow("SELECT * FROM `oauth_clients` WHERE `client_id` = ?", [$clientId]);

                    if (empty($client)) {
                        throw new OAuthServerException('Not a registered client', 0, 'invalid_request', Response::HTTP_UNAUTHORIZED);
                    }

                    if (intval($client['is_enabled']) !== 1) {
                        throw new OAuthServerException('Client failed security', 0, 'invalid_request', Response::HTTP_UNAUTHORIZED);
                    }

                    // Create client entity for JWT validation
                    $clientEntity = $this->getClientRepository()->getClientEntity($clientId);

                    // Validate JWT assertion
                    $jwtAuthService->validateJWTClientAssertion($psrRequest, $clientEntity);

                    $this->getSystemLogger()->debug("tokenIntrospection() JWT client authentication successful");
                }
            } else {
                // so regardless of client type(private/public) we need client for client app type and secret.
                $client = QueryUtils::querySingleRow("SELECT * FROM `oauth_clients` WHERE `client_id` = ?", [$clientId]);
                if (empty($client)) {
                    throw new OAuthServerException('Not a registered client', 0, 'invalid_request', Response::HTTP_UNAUTHORIZED);
                }
                // a no no. if private we need a secret.
                if (empty($clientSecret) && !empty($client['is_confidential'])) {
                    throw new OAuthServerException('Invalid client app type', 0, 'invalid_request', Response::HTTP_BAD_REQUEST);
                }
                // lets verify secret to prevent bad guys.
                if (intval($client['is_enabled'] !== 1)) {
                    // client is disabled and we don't allow introspection of tokens for disabled clients.
                    throw new OAuthServerException('Client failed security', 0, 'invalid_request', Response::HTTP_UNAUTHORIZED);
                }
                // lets verify secret to prevent bad guys.
                if (!empty($client['client_secret'])) {
                    $decryptedSecret = $this->cryptoGen->decryptStandard($client['client_secret']);
                    if ($decryptedSecret !== $clientSecret) {
                        throw new OAuthServerException('Client failed security', 0, 'invalid_request', Response::HTTP_UNAUTHORIZED);
                    }
                }
            }
            $jsonWebKeyParser = new JsonWebKeyParser($this->oaEncryptionKey, $this->publicKey);
            // will try hard to go on if missing token hint. this is to help with universal conformance.
            if (empty($token_hint)) {
                $token_hint = $jsonWebKeyParser->getTokenHintFromToken($rawToken);
            } elseif (($token_hint !== 'access_token' && $token_hint !== 'refresh_token') || empty($rawToken)) {
                throw new OAuthServerException('Missing token or unsupported hint.', 0, 'invalid_request', Response::HTTP_BAD_REQUEST);
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
                    $tokenRepository = $this->getTokenRepository();
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
                        $result = ['active' => false];
                    }
                } catch (Exception $exception) {
                    // JWT couldn't be parsed
                    $body = $response->getBody();
                    $body->write($exception->getMessage());
                    $this->session->invalidate();
                    return $response->withStatus(Response::HTTP_BAD_REQUEST)->withBody($body);
                }
            }
            if ($token_hint === 'refresh_token') {
                try {
                    // client_id comes back from the parsed refresh token
                    $result = $jsonWebKeyParser->parseRefreshToken($rawToken);
                } catch (Exception $exception) {
                    $body = $response->getBody();
                    $body->write($exception->getMessage());
                    $this->session->invalidate();
                    return $response->withStatus(Response::HTTP_BAD_REQUEST)->withBody($body);
                }
                $trusted = $this->trustedUser($result['client_id'], $result['sub']);
                if (empty($trusted['id'])) {
                    $result['active'] = false;
                    $result['status'] = 'revoked';
                }
                $tokenRepository = $this->getRefreshTokenRepository();
                if ($tokenRepository->isRefreshTokenRevoked($result['jti'])) {
                    $result['active'] = false;
                    $result['status'] = 'revoked';
                }
                if ($result['client_id'] !== $clientId) {
                    // return no info in this case. possible Phishing
                    $result = ['active' => false];
                }
            }
        } catch (OAuthServerException $exception) {
            // JWT couldn't be parsed
            $this->session->invalidate();
            $this->getSystemLogger()->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            return $exception->generateHttpResponse($response);
        }
        // we're here so emit results to interface thank you very much.
        $body = $response->getBody();
        $body->write(json_encode($result));
        $this->session->invalidate();
        return $response->withStatus(Response::HTTP_OK)->withBody($body);
    }

    /**
     * Returns the authentication server token Url endpoint
     * @deprecated Use ServerConfig::getTokenUrl()
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
    public static function getTokenPath(): string
    {
        return "/token";
    }

    /**
     * Returns the authentication server manage url
     * @return string
     */
    public function getManageUrl(): string
    {
        return $this->authBaseFullUrl . self::getManagePath();
    }

    /**
     * Returns the path prefix that the manage token authorization endpoint is on.
     * @return string
     */
    public static function getManagePath(): string
    {
        return "/manage";
    }

    /**
     * Returns the authentication server authorization url to use for oauth authentication
     * @deprecated Use ServerConfig::getAuthorizeUrl()
     * @return string
     */
    public function getAuthorizeUrl(): string
    {
        return $this->authBaseFullUrl . self::getAuthorizePath();
    }

    /**
     * Returns the path prefix that the authorization endpoint is on.
     * @return string
     */
    public static function getAuthorizePath(): string
    {
        return "/authorize";
    }

    /**
     * Returns the authentication server registration url to use for client app / api registration
     * @deprecated Use ServerConfig::getIntrospectionUrl()
     * @return string
     */
    public function getRegistrationUrl(): string
    {
        return $this->authBaseFullUrl . self::getRegistrationPath();
    }

    /**
     * Returns the path prefix that the registration endpoint is on.
     * @return string
     */
    public static function getRegistrationPath(): string
    {
        return "/registration";
    }

    /**
     * Returns the authentication server introspection url to use for checking tokens
     * @deprecated Use ServerConfig::getIntrospectionUrl()
     * @return string
     */
    public function getIntrospectionUrl(): string
    {
        return $this->authBaseFullUrl . self::getIntrospectionPath();
    }

    /**
     * Returns the path prefix that the introspection endpoint is on.
     * @return string
     */
    public static function getIntrospectionPath(): string
    {
        return "/introspect";
    }

    /**
     * @param OEGlobalsBag $globalsBag
     * @param SessionInterface $session
     * @return string
     */
    public static function getAuthBaseFullURL(OEGlobalsBag $globalsBag, SessionInterface $session): string
    {
        $baseUrl = $globalsBag->get('webroot', '') . '/oauth2/' . $session->get('site_id', 'default');
        // collect full url and issuing url by using 'site_addr_oath' global
        return $globalsBag->get('site_addr_oath', '') . $baseUrl;
    }

    /**
     * Given a password grant response, save the trusted user information to the database so password grant users
     * can proceed.
     * @param HttpRestRequest $result
     * @param ResponseInterface $result
     * @throws \JsonException
     */
    private function saveTrustedUserForPasswordGrant(HttpRestRequest $request, ResponseInterface $result): void
    {
        $body = $result->getBody();
        $body->rewind();
        // yep, even password grant gets one. could be useful.
        $code = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR)['id_token'];
        $this->session->remove("csrf_private_key");
        $session_cache = json_encode($this->session->all(), JSON_THROW_ON_ERROR);
        $requestBody = $request->getParsedBody();
        $this->saveTrustedUser(
            $requestBody['client_id'] ?? '',
            $this->session->get('pass_user_id'),
            $requestBody['scope'] ?? '',
            0,
            $code,
            $session_cache,
            self::GRANT_TYPE_PASSWORD
        );
    }

    private function shouldSkipAuthorizationFlow(AuthorizationRequest $authRequest): bool
    {
        $skip = false;
        $client = $authRequest->getClient();
        // if don't allow our globals settings to allow skipping the authorization flow when inside an ehr launch
        // we just return false
        if ($this->globalsBag->getInt('oauth_ehr_launch_authorization_flow_skip', 0) !== 1) {
            $this->getSystemLogger()->debug("AuthorizationController->shouldSkipAuthorizationFlow() - oauth_ehr_launch_authorization_flow_skip not set, not skipping even though launch is present.");
            return false;
        }
        if ($client instanceof ClientEntity) {
            if ($client->shouldSkipEHRLaunchAuthorizationFlow()) {
                $this->getSystemLogger()->debug("AuthorizationController->shouldSkipAuthorizationFlow() - client is configured to skip authorization flow.");
                return true;
            }
        }
        return false;
    }

    /**
     * @param AuthorizationRequest $authRequest
     * @param HttpRestRequest $request
     * @param ResponseInterface $response
     * @param string $userUuid
     * @return ResponseInterface
     * @throws \JsonException
     */
    private function processAuthorizeFlowForLaunch(AuthorizationRequest $authRequest, HttpRestRequest $request, ResponseInterface $response, string $userUuid)
    {
        $queryParams = $request->getQueryParams();
        $session = $request->getSession();
        if (empty($queryParams['autosubmit']) || $queryParams['autosubmit'] !== '1') {
            $this->getSystemLogger()->debug("AuthorizationController->processAuthorizeFlowForLaunch() - autosubmit not set, redirecting to autosubmit page.");
            // we are going to display a form here with a javascript to autosubmit this page so we can make our session
            // cookies on a first party domain to verify the user is logged in.  It requires a whole page load and it's
            // a slower approach but we can then rely on the session cookie as a first party domain.
            //  We can't rely on the session cookie from the launch endpoint because of third party browser blocking
            // we don't want to deal with storing the user information in the launch token as we don't want to have to
            // deal with the security implications of the launch token being hijacked/MITM.
            return $this->getSmartAuthController()->dispatchRoute(SMARTAuthorizationController::EHR_SMART_LAUNCH_AUTOSUBMIT, $request);
        }
        $this->getSystemLogger()->debug("AuthorizationController->processAuthorizeFlowForLaunch() - autosubmit set, processing authorization flow.");
        // if we have come back from an autosubmit we are going to check to see if we are logged in

        $launch = $request->getQueryParams()['launch'];
        SMARTLaunchToken::deserializeToken($launch);

        $client = $authRequest->getClient();
        // only authorize scopes specifically allowed by the client regardless of what is sent in the request
        $scopes = $client instanceof ClientEntity ? $client->getScopes() : [];
        $scopesById = array_combine($scopes, $scopes);
        $authRequest = $this->updateAuthRequestWithUserApprovedScopes($authRequest, $scopesById);
        $include_refresh_token = $this->shouldIncludeRefreshTokenForScopes($authRequest->getScopes());
        $server = $this->getAuthorizationServer($this->getScopeRepository($this->session), $include_refresh_token);

        // make sure we get our serialized session data
        $this->serializeUserSession($authRequest, $session);
        $apiSession = $this->session->all();
        $user = $this->getUserRepository()->getUserEntityByIdentifier($userUuid);
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
        // now we need to get our session user_id
        $this->saveTrustedUser($apiSession['client_id'], $apiSession['user_id'], $apiSession['scopes'], $apiSession['persist_login'], $code, $session_cache);

        $this->getSystemLogger()->debug("AuthorizationController->processAuthorizeFlowForLaunch() sending server response");
        $session->invalidate(); // invalidate the session so we don't have to worry about it
        return $result;
    }

    private function startTransaction(): void
    {
        QueryUtils::startTransaction();
        // we want to be able to commit this transaction separately from the main transaction
        // so we'll set this to true and then reset it after we commit
        $this->getDecisionSupportInterventionService()->setInNestedTransaction(true);
    }
    private function getDecisionSupportInterventionService(): DecisionSupportInterventionService
    {
        if (empty($this->dsiService)) {
            $dsiService = new DecisionSupportInterventionService();
            $this->dsiService = $dsiService;
        }
        return $this->dsiService;
    }

    private function commitTransaction(): void
    {
        QueryUtils::commitTransaction();
        $this->getDecisionSupportInterventionService()->setInNestedTransaction(false);
    }

    private function rollbackTransaction(): void
    {
        QueryUtils::rollbackTransaction();
        $this->getDecisionSupportInterventionService()->setInNestedTransaction(false);
    }

    private function createDecisionSupportInterventionServiceForType(string $clientId, string $clientName, int $dsiType, array $dsiSourceAttributes): void
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientId);
        $clientEntity->setName($clientName);

        $dsiService = $this->getDecisionSupportInterventionService();
        $service = $dsiService->getEmptyService($dsiType);
        $service->setClient($clientEntity);
        // would be nice to do key => value but it limits the structure of the attributes
        // so we'll go with an array of objects here
        foreach ($dsiSourceAttributes as $attribute) {
            $fieldName = $attribute['name'] ?? "";
            $value = $attribute['value'] ?? "";
            if ($service->hasField($fieldName)) {
                $service->setFieldValue($fieldName, $value);
            }
        }
        // if user is logged in we want to track who the creator was
        // if not logged in, user id will be null as we don't have anything to track.
        $userId = $this->session->get('authUserID', null);
        $dsiService->updateService($service, $userId);
    }

    private function addDSIInformation(array &$params, array $client): void
    {
        $dsiService = $this->getDecisionSupportInterventionService();
        $dsiType = $client['dsi_type'] ?? ClientEntity::DSI_TYPE_NONE;
        $params['dsi_type'] = $dsiService->getDsiTypeStringName($dsiType);
        if ($dsiType !== ClientEntity::DSI_TYPE_NONE) {
            $clientEntity = new ClientEntity();
            $clientEntity->setIdentifier($client['client_id']);
            $clientEntity->setName($client['client_name']);
            $clientEntity->setDSIType($dsiType);
            $service = $dsiService->getServiceForClient($clientEntity, false);
            $fields = $service->getFields();
            $dsiSourceAttributes = [];
            foreach ($fields as $field) {
                $dsiSourceAttributes[] = ['name' => $field['name'], 'value' => $field['value']];
            }
            $params['dsi_source_attributes'] = $dsiSourceAttributes;
        }
    }

    private function getLoggedInCoreUserUuid(HttpRestRequest $request)
    {
        $this->session->save(); // save the session so we can switch to the core session
        // TODO: do we need to have a setter here so we can unit test this functionality for the session factory?
        $sessionFactory = new HttpSessionFactory($request, $this->webroot, HttpSessionFactory::SESSION_TYPE_CORE, true);
        $coreSession = $sessionFactory->createSession();

        // if we can deserialize let's now check to see if the user is logged in
        // note this switching of sessions can slow things down a bit depending on how the php session storage is setup.
        // for now we only handle in-ehr launch for providers not patients.  We can add this later if needed.
        if (empty($coreSession->get('authUserID'))) {
            $this->getSystemLogger()->debug("AuthorizationController->processAuthorizeFlowForLaunch() no user logged in, redirecting to login page");
            // switch back so we don't destroy the original session
            $coreSession->save(); // there is no close method, since we're in readonly mode we can safely save and close
            $this->session->invalidate(); // restart the oauth2 session
            return null; // no uuid so we will go through login steps
        }
        $userId = $coreSession->get('authUserID');
        $userService = new UserService();
        $user = $userService->getUser($userId);
        if (empty($user)) {
            // switch back so we don't destroy the original session
            $this->getSystemLogger()->debug("AuthorizationController->processAuthorizeFlowForLaunch() no user found for logged in authUserID, redirecting to login page");
            $coreSession->save(); // there is no close method, since we're in readonly mode we can safely save and close
            $this->session->invalidate(); // restart the oauth2 session
            return null; // no uuid so we will go through login steps
        }
        $userUuid = $user['uuid'];
        $this->session->start();
        return $userUuid;
    }

    protected function getUserRepository(): UserRepository
    {
        $userIdentityRepository = new UserRepository($this->globalsBag, $this->session);
        $userIdentityRepository->setSystemLogger($this->getSystemLogger());
        return $userIdentityRepository;
    }
    /**
     * @return AccessTokenRepository
     */
    private function getTokenRepository(): AccessTokenRepository
    {
        $tokenRepository = new AccessTokenRepository($this->globalsBag, $this->session);
        $tokenRepository->setSystemLogger($this->getSystemLogger());
        return $tokenRepository;
    }

    /**
     * @param bool $includeAuthGrantRefreshToken
     * @return RefreshTokenRepository
     */
    private function getRefreshTokenRepository(bool $includeAuthGrantRefreshToken = true): RefreshTokenRepository
    {
        $repo = new RefreshTokenRepository($includeAuthGrantRefreshToken);
        $repo->setSystemLogger($this->getSystemLogger());
        return $repo;
    }

    protected function convertPostParamsToGet(HttpRestRequest $request): HttpRestRequest
    {
        $parsedBody = $request->getParsedBody();
        if (!empty($parsedBody)) {
            foreach ($parsedBody as $key => $value) {
                $request->query->set($key, $value);
            }
        }
        return $request;
    }
}
