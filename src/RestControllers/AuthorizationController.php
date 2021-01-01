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
use Exception;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\MfaUtils;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomPasswordGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomRefreshTokenGrant;
use OpenEMR\Common\Auth\OpenIDConnect\IdTokenSMARTResponse;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AuthCodeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\IdentityRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\UserRepository;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Entities\ClaimSetEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AuthorizationController
{
    use CryptTrait;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($providerForm = true)
    {
        $gbl = \RestConfig::GetInstance();
        $this->logger = SystemLogger::instance();

        $this->siteId = $_SESSION['site_id'] ?? $gbl::$SITE;
        $this->authBaseUrl = $GLOBALS['webroot'] . '/oauth2/' . $this->siteId;
        $this->authBaseFullUrl = self::getAuthBaseFullURL();
        // used for session stash
        $this->authRequestSerial = $_SESSION['authRequestSerial'] ?? '';
        // Create a crypto object that will be used for for encryption/decryption
        $this->cryptoGen = new CryptoGen();
        // verify and/or setup our key pairs.
        $this->privateKey = $GLOBALS['OE_SITE_DIR'] . '/documents/certificates/oaprivate.key';
        $this->publicKey = $GLOBALS['OE_SITE_DIR'] . '/documents/certificates/oapublic.key';
        $this->configKeyPairs();
        // true will display client/user server sign in. false, not.
        $this->providerForm = $providerForm;
    }

    private function configKeyPairs(): void
    {
        $response = $this->createServerResponse();
        try {
            // encryption key
            $eKey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2key'");
            if (!empty($eKey['name']) && ($eKey['name'] === 'oauth2key')) {
                // collect the encryption key from database
                $this->oaEncryptionKey = $this->cryptoGen->decryptStandard($eKey['value']);
                if (empty($this->oaEncryptionKey)) {
                    // if decrypted key is empty, then critical error and must exit
                    $this->logger->error("OpenEMR error - oauth2 key was blank after it was decrypted, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
            } else {
                // create a encryption key and store it in database
                $this->oaEncryptionKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($this->oaEncryptionKey)) {
                    // if empty, then force exit
                    $this->logger->error("OpenEMR error - random generator broken during oauth2 encryption key generation, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
                $this->oaEncryptionKey = base64_encode($this->oaEncryptionKey);
                if (empty($this->oaEncryptionKey)) {
                    // if empty, then force exit
                    $this->logger->error("OpenEMR error - base64 encoding broken during oauth2 encryption key generation, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
                sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES ('oauth2key', ?)", [$this->cryptoGen->encryptStandard($this->oaEncryptionKey)]);
            }
            // private key
            if (!file_exists($this->privateKey)) {
                // create the private/public key pair (store in filesystem) with a random passphrase (store in database)
                // first, create the passphrase (removing any prior passphrases)
                sqlStatementNoLog("DELETE FROM `keys` WHERE `name` = 'oauth2passphrase'");
                $this->passphrase = RandomGenUtils::produceRandomString(60, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
                if (empty($this->passphrase)) {
                    // if empty, then force exit
                    $this->logger->error("OpenEMR error - random generator broken during oauth2 key passphrase generation, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
                // second, create and store the private/public key pair
                $keysConfig = [
                    "default_md" => "sha256",
                    "private_key_type" => OPENSSL_KEYTYPE_RSA,
                    "private_key_bits" => 2048,
                    "encrypt_key" => true,
                    "encrypt_key_cipher" => OPENSSL_CIPHER_AES_256_CBC
                ];
                $keys = \openssl_pkey_new($keysConfig);
                if ($keys === false) {
                    // if unable to create keys, then force exit
                    $this->logger->error("OpenEMR error - key generation broken during oauth2, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
                $privkey = '';
                openssl_pkey_export($keys, $privkey, $this->passphrase, $keysConfig);
                $pubkey = openssl_pkey_get_details($keys);
                $pubkey = $pubkey["key"];
                if (empty($privkey) || empty($pubkey)) {
                    // if unable to construct keys, then force exit
                    $this->logger->error("OpenEMR error - key construction broken during oauth2, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
                // third, store the keys on drive and store the passphrase in the database
                file_put_contents($this->privateKey, $privkey);
                chmod($this->privateKey, 0640);
                file_put_contents($this->publicKey, $pubkey);
                chmod($this->publicKey, 0660);
                sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES ('oauth2passphrase', ?)", [$this->cryptoGen->encryptStandard($this->passphrase)]);
            }
            // confirm existence of passphrase
            $pkey = sqlQueryNoLog("SELECT `name`, `value` FROM `keys` WHERE `name` = 'oauth2passphrase'");
            if (!empty($pkey['name']) && ($pkey['name'] == 'oauth2passphrase')) {
                $this->passphrase = $this->cryptoGen->decryptStandard($pkey['value']);
                if (empty($this->passphrase)) {
                    // if decrypted pssphrase is empty, then critical error and must exit
                    $this->logger->error("OpenEMR error - oauth2 passphrase was blank after it was decrypted, so forced exit");
                    throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
                }
            } else {
                // oauth2passphrase is missing so must exit
                $this->logger->error("OpenEMR error - oauth2 passphrase is missing, so forced exit");
                throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
            }
            // confirm existence of key pair
            if (!file_exists($this->privateKey) || !file_exists($this->publicKey)) {
                // key pair is missing so must exit
                $this->logger->error("OpenEMR error - oauth2 keypair is missing, so forced exit");
                throw OAuthServerException::serverError("Security error - problem with authorization server keys.");
            }
        } catch (OAuthServerException $exception) {
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
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
            }
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
        // we do not allow a confidential app to be enabled by default;
        $is_client_enabled = $is_confidential_client ? 0 : 1;
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
        $info['scope'] = $info['scope'] ?? 'openid email phone address api:oemr api:fhir api:port api:pofh';

        // if a public app requests the launch scope we also do not let them through unless they've been manually
        // authorized by an administrator user.
        if ($is_client_enabled) {
            $is_client_enabled = strpos($info['scope'], SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE) !== false ? 0 : 1;
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

        if ($nonce = $request->getQueryParams()['nonce']) {
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
            $this->logger->error("AuthorizationController->oauthAuthorizationFlow() OAuthServerException", ["message" => $exception->getMessage()]);
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

    public function getAuthorizationServer(): AuthorizationServer
    {
        $protectedClaims = ['profile', 'email', 'address', 'phone'];
        $scopeRepository = new ScopeRepository();
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

        $authServer = new AuthorizationServer(
            new ClientRepository(),
            new AccessTokenRepository(),
            new ScopeRepository(),
            new CryptKey($this->privateKey, $this->passphrase),
            $this->oaEncryptionKey,
            $responseType
        );
        if (empty($this->grantType)) {
            $this->grantType = 'authorization_code';
        }
        $this->logger->debug("AuthorizationController->getAuthorizationServer() grantType is " . $this->grantType);
        if ($this->grantType === 'authorization_code') {
            $grant = new AuthCodeGrant(
                new AuthCodeRepository(),
                new RefreshTokenRepository(),
                new \DateInterval('PT1M') // auth code. should be short turn around.
            );
            $grant->setRefreshTokenTTL(new \DateInterval('P3M')); // minimum per ONC
            $authServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H') // access token
            );
        }
        if ($this->grantType === 'refresh_token') {
            $grant = new CustomRefreshTokenGrant(new refreshTokenRepository());
            $grant->setRefreshTokenTTL(new \DateInterval('P3M'));
            $authServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H') // The new access token will expire after 1 hour
            );
        }
        // TODO: break this up - throw exception for not turned on.
        if (!empty($GLOBALS['oauth_password_grant']) && ($this->grantType === 'password')) {
            $grant = new CustomPasswordGrant(
                new UserRepository(),
                new RefreshTokenRepository()
            );
            $grant->setRefreshTokenTTL(new DateInterval('P3M'));
            $authServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H') // access token
            );
        }
        if ($this->grantType === 'client_credentials') {
            // Enable the client credentials grant on the server
            $authServer->enableGrantType(
                new ClientCredentialsGrant(),
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

        $patientRoleSupport = (!empty($GLOBALS['rest_portal_api']) || !empty($GLOBALS['rest_portal_fhir_api']));

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
        $redirect = $this->authBaseUrl . "/device/code";
        $authorize = 'authorize';
        require_once(__DIR__ . "/../../oauth2/provider/login.php");
    }

    private function verifyLogin($username, $password, $email = '', $type = 'api'): bool
    {
        $auth = new AuthUtils($type);
        $is_true = $auth->confirmPassword($username, $password, $email);
        if (!$is_true) {
            $this->logger->debug("AuthorizationController->verifyLogin() login attempt failed", ['username' => $username]);
            return false;
        }
        if ($this->userId = $auth->getUserId()) {
            $_SESSION['user_id'] = $this->getUserUuid($this->userId, 'users');
            $this->logger->debug("AuthorizationController->verifyLogin() user login", ['pid' => $_SESSION['user_id']]);
            return true;
        }
        if ($id = $auth->getPatientId()) {
            $_SESSION['user_id'] = $this->getUserUuid($id, 'patient');
            $this->logger->debug("AuthorizationController->verifyLogin() patient login", ['pid' => $_SESSION['user_id']]);
            $_SESSION['pid'] = $_SESSION['user_id'];
            return true;
        }

        return false;
    }

    protected function getUserUuid($userId, $userRole): string
    {
        switch ($userRole) {
            case 'users':
                (new UuidRegistry(['table_name' => 'users']))->createMissingUuids();
                $account_sql = "SELECT `uuid` FROM `users` WHERE `id` = ?";
                break;
            case 'patient':
                (new UuidRegistry(['table_name' => 'patient_data']))->createMissingUuids();
                $account_sql = "SELECT `uuid` FROM `patient_data` WHERE `pid` = ?";
                break;
            default:
                return '';
        }
        $id = sqlQueryNoLog($account_sql, array($userId))['uuid'];

        $uuidRegistry = new UuidRegistry();
        return $uuidRegistry::uuidToString($id);
    }

    public function authorizeUser(): void
    {
        $this->logger->debug("AuthorizationController->authorizeUser() starting authorization");
        $response = $this->createServerResponse();
        $authRequest = $this->deserializeUserSession();
        try {
            $server = $this->getAuthorizationServer();
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
        if ($this->grantType === 'authorization_code') {
            // re-populate from saved session cache populated in authorizeUser().
            $ssbc = $this->sessionUserByCode($code);
            $_SESSION = json_decode($ssbc['session_cache'], true);
        }
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
            if ($this->grantType === 'password') {
                $body = $result->getBody();
                $body->rewind();
                // yep, even password grant gets one. could be useful.
                $code = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR)['id_token'];
                unset($_SESSION['csrf_private_key']); // gotta remove since binary and will break json_encode (not used for password granttype, so ok to remove)
                $session_cache = json_encode($_SESSION, JSON_THROW_ON_ERROR);
                $this->saveTrustedUser($_REQUEST['client_id'], $_SESSION['pass_user_id'], $_REQUEST['scope'], 0, $code, $session_cache, 'password');
            }
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($result);
        } catch (OAuthServerException $exception) {
            $this->logger->debug(
                "AuthorizationController->oauthAuthorizeToken() OAuthServerException occurred",
                ["message" => $exception->getMessage(), "stack" => $exception->getTraceAsString()]
            );
            SessionUtil::oauthSessionCookieDestroy();
            $this->emitResponse($exception->generateHttpResponse($response));
        } catch (Exception $exception) {
            $this->logger->error(
                "AuthorizationController->oauthAuthorizeToken() Exception occurred",
                ["message" => $exception->getMessage()]
            );
            SessionUtil::oauthSessionCookieDestroy();
            $body = $response->getBody();
            $body->write($exception->getMessage());
            $this->emitResponse($response->withStatus(500)->withBody($body));
        }
    }

    public function trustedUser($clientId, $userId)
    {
        return sqlQueryNoLog("SELECT * FROM `oauth_trusted_user` WHERE `client_id`= ? AND `user_id`= ?", array($clientId, $userId));
    }

    public function sessionUserByCode($code)
    {
        return sqlQueryNoLog("SELECT * FROM `oauth_trusted_user` WHERE `code`= ?", array($code));
    }

    public function saveTrustedUser($clientId, $userId, $scope, $persist, $code = '', $session = '', $grant = 'authorization_code')
    {
        $id = $this->trustedUser($clientId, $userId)['id'] ?? '';
        $sql = "REPLACE INTO `oauth_trusted_user` (`id`, `user_id`, `client_id`, `scope`, `persist_login`, `time`, `code`, session_cache, `grant_type`) VALUES (?, ?, ?, ?, ?, Now(), ?, ?, ?)";

        return sqlQueryNoLog($sql, array($id, $userId, $clientId, $scope, $persist, $code, $session, $grant));
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
            $rtn = sqlQueryNoLog("DELETE FROM `oauth_trusted_user` WHERE `oauth_trusted_user`.`id` = ?", array($trustedUser['id']));
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
            // will try hard to go on if missing token hint. this is to help with universal conformance.
            if (empty($token_hint)) {
                // determine if access or refresh.
                $access_parts = explode(".", $rawToken);
                if (count($access_parts) === 3) {
                    $token_hint = 'access_token';
                } else {
                    $token_hint = 'refresh_token';
                }
            } elseif (($token_hint !== 'access_token' && $token_hint !== 'refresh_token') || empty($rawToken)) {
                throw new OAuthServerException('Missing token or unsupported hint.', 0, 'invalid_request', 400);
            }

            // are we there yet! client's okay but, is token?
            if ($token_hint === 'access_token') {
                try {
                    // Attempt to parse and validate the JWT
                    $token = (new Parser())->parse($rawToken);
                    // defaults
                    $result = array(
                        'active' => true,
                        'status' => 'active',
                        'scope' => implode(" ", $token->getClaim('scopes')),
                        'client_id' => $clientId,
                        'exp' => $token->getClaim('exp'),
                        'sub' => $token->getClaim('sub'), // user_id
                    );
                    try {
                        if ($token->verify(new Sha256(), 'file://' . $this->publicKey) === false) {
                            $result['active'] = false;
                            $result['status'] = 'failed_verification';
                        }
                    } catch (Exception $exception) {
                        $result['active'] = false;
                        $result['status'] = 'invalid_signature';
                    }
                    // Ensure access token hasn't expired
                    $data = new ValidationData();
                    $data->setCurrentTime(\time());
                    if ($token->validate($data) === false) {
                        $result['active'] = false;
                        $result['status'] = 'expired';
                    }
                    $trusted = $this->trustedUser($result['client_id'], $result['sub']);
                    if (empty($trusted['id'])) {
                        $result['active'] = false;
                        $result['status'] = 'revoked';
                    }
                    if ($token->getClaim('aud') !== $clientId) {
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
                    // Validate refresh token
                    $this->setEncryptionKey($this->oaEncryptionKey);
                    $refreshToken = $this->decrypt($rawToken);
                    $refreshTokenData = \json_decode($refreshToken, true);
                } catch (Exception $exception) {
                    $body = $response->getBody();
                    $body->write($exception->getMessage());
                    SessionUtil::oauthSessionCookieDestroy();
                    $this->emitResponse($response->withStatus(400)->withBody($body));
                    exit();
                }
                $result = array(
                    'active' => true,
                    'status' => 'active',
                    'scope' => implode(" ", $refreshTokenData['scopes']),
                    'client_id' => $clientId,
                    'exp' => $refreshTokenData['expire_time'],
                    'sub' => $refreshTokenData['user_id'],
                );
                if ($refreshTokenData['expire_time'] < \time()) {
                    $result['active'] = false;
                    $result['status'] = 'expired';
                }
                $trusted = $this->trustedUser($refreshTokenData['client_id'], $result['sub']);
                if (empty($trusted['id'])) {
                    $result['active'] = false;
                    $result['status'] = 'revoked';
                }
                if ($refreshTokenData['client_id'] !== $clientId) {
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
}
