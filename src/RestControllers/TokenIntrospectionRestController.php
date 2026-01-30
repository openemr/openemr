<?php
/*
 * TokenIntrospectionRestController.php  handles OAuth2 token introspection requests as per RFC 7662 and SMART on FHIR v2.2 specifications.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OAuth2KeyConfig;
use OpenEMR\Common\Auth\OpenIDConnect\FhirUserClaim;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeyParser;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Services\JWTClientAuthenticationService;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use OpenEMR\Services\TrustedUserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Exception;

class TokenIntrospectionRestController {

    use SystemLoggerAwareTrait;
    use GlobalInterfaceTrait;

    protected ?ServerConfig $serverConfig = null;
    protected ?ClientRepository $clientRepository = null;
    protected ?JWTRepository $jwtRepository = null;
    protected ?OAuth2KeyConfig $oauth2KeyConfig = null;

    protected ?JsonWebKeyParser $jsonWebKeyParser = null;

    protected ?CryptoGen $cryptoGen = null;

    protected ?TrustedUserService $trustedUserService = null;

    protected ?RefreshTokenRepository $refreshTokenRepository = null;

    protected ?AccessTokenRepository $accessTokenRepository = null;

    protected ?Psr17Factory $psr17Factory = null;

    public function __construct(OEGlobalsBag $globalsBag)
    {
        $this->setGlobalsBag($globalsBag);
    }

    public function postAction(HttpRestRequest $request): ResponseInterface {
        if (!$this->validateInitialRequestParameters($request)) {
            $response = $this->returnInactiveResponse($request);
        } else {
            $response = $this->tokenIntrospection($request);
        }
        $request->getSession()->invalidate();
        return $response;
    }

    /**
     * Validate initial request parameters
     * @param HttpRestRequest $request
     * @return bool
     */
    public function validateInitialRequestParameters(HttpRestRequest $request): bool {

        if (empty($request->request->get('token'))) {
            return false;
        }
        $serverRequest = $this->convertRestRequestToPsrRequest($request);
        if ($this->getJWTClientAuthenticationService()->hasJWTClientAssertion($serverRequest)) {
            return true;
        } else {
            // client_id is inside the refresh token so we can skip this check
            if ($request->request->get('token_hint', '') !== 'refresh_token') {
                // normally we'd throw a 401 on this but inferno fails to validate if we have a token but no client id/secret
                // so we will just return inactive.
                if (empty($request->request->get('client_id'))) {
                    return false;
                }
                // we don't check client_secret as public clients can get access tokens without a secret
            }
        }
        return true;
    }

    /**
     * @param Psr17Factory|null $psr17Factory
     */
    public function setPsr17Factory(?Psr17Factory $psr17Factory): void
    {
        $this->psr17Factory = $psr17Factory;
    }

    /**
     * @return Psr17Factory|null
     */
    public function getPsr17Factory(): ?Psr17Factory
    {
        if (!isset($this->psr17Factory)) {
            $this->psr17Factory = new Psr17Factory();
        }
        return $this->psr17Factory;
    }

    /**
     * @param AccessTokenRepository|null $accessTokenRepository
     */
    public function setAccessTokenRepository(?AccessTokenRepository $accessTokenRepository): void
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function getAccessTokenRepository(SessionInterface $session): AccessTokenRepository {
        if (!isset($this->accessTokenRepository)) {
            $this->accessTokenRepository = new AccessTokenRepository($this->getServerConfig(), $session);
        }
        return $this->accessTokenRepository;
    }

    public function getRefreshTokenRepository(): RefreshTokenRepository {
        if (!isset($this->refreshTokenRepository)) {
            $this->refreshTokenRepository = new RefreshTokenRepository();
        }
        return $this->refreshTokenRepository;
    }

    /**
     * @param RefreshTokenRepository|null $refreshTokenRepository
     */
    public function setRefreshTokenRepository(?RefreshTokenRepository $refreshTokenRepository): void
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function getTrustedUserService(): TrustedUserService {
        if (!isset($this->trustedUserService)) {
            $this->trustedUserService = new TrustedUserService();
        }
        return $this->trustedUserService;
    }

    /**
     * @param TrustedUserService|null $trustedUserService
     */
    public function setTrustedUserService(?TrustedUserService $trustedUserService): void
    {
        $this->trustedUserService = $trustedUserService;
    }

    public function getCryptoGen(): CryptoGen {
        if (!isset($this->cryptoGen)) {
            $this->cryptoGen = new CryptoGen();
        }
        return $this->cryptoGen;
    }

    public function setCryptoGen(CryptoGen $cryptoGen): void {
        $this->cryptoGen = $cryptoGen;
    }

    public function getJsonWebKeyParser() : JsonWebKeyParser {
        if (!isset($this->jsonWebKeyParser)) {
            $this->jsonWebKeyParser = new JsonWebKeyParser($this->getOAuth2KeyConfig()->getEncryptionKey(), $this->getOAuth2KeyConfig()->getPublicKeyLocation());
        }
        return $this->jsonWebKeyParser;
    }

    public function setJsonWebKeyParser(JsonWebKeyParser $jsonWebKeyParser): void {
        $this->jsonWebKeyParser = $jsonWebKeyParser;
    }

    /**
     * @return OAuth2KeyConfig
     * @throws \OpenEMR\Common\Auth\OAuth2KeyException
     */
    public function getOAuth2KeyConfig(): OAuth2KeyConfig {
        if (!isset($this->oauth2KeyConfig)) {
            $this->oauth2KeyConfig = new OAuth2KeyConfig($this->getGlobalsBag()->get('OE_SITE_DIR'));
            $this->oauth2KeyConfig->configKeyPairs();
        }
        return $this->oauth2KeyConfig;
    }

    public function getJWTRepository(): JWTRepository {
        if (!isset($this->jwtRepository)) {
            $this->jwtRepository = new JWTRepository();
        }
        return $this->jwtRepository;
    }

    public function setJWTRepository(JWTRepository $jwtRepository): void {
        $this->jwtRepository = $jwtRepository;
    }


    public function getJWTClientAuthenticationService(): JWTClientAuthenticationService {
        return new JWTClientAuthenticationService(
            $this->getServerConfig()->getTokenUrl(),
            $this->getClientRepository(),
            $this->getJWTRepository(),
            null
        );
    }

    public function getServerConfig(): ServerConfig
    {
        if (!isset($this->serverConfig)) {
            $this->serverConfig = new ServerConfig();
        }
        return $this->serverConfig;
    }

    public function setServerConfig(ServerConfig $serverConfig): void
    {
        $this->serverConfig = $serverConfig;
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

    protected function returnInactiveResponse(HttpRestRequest $request): ResponseInterface
    {
        $response = $this->getBasicResponse();
        $result = ['active' => false];
        $body = $response->getBody();
        $body->write(json_encode($result));
        return $response->withStatus(Response::HTTP_OK)->withBody($body);
    }

    protected function getBasicResponse(): ResponseInterface {
        $response = $this->createServerResponse();
        $response->withHeader("Cache-Control", "no-store");
        $response->withHeader("Pragma", "no-cache");
        $response->withHeader('Content-Type', 'application/json');
        return $response;
    }

    protected function convertRestRequestToPsrRequest(HttpRestRequest $request): ServerRequestInterface
    {
        return (new PsrHttpFactory())->createRequest($request);
    }

    protected function createServerResponse(): ResponseInterface
    {
        return $this->getPsr17Factory()->createResponse();
    }

    protected function tokenIntrospection(HttpRestRequest $request): ResponseInterface
    {
        $response = $this->createServerResponse();
        $response->withHeader("Cache-Control", "no-store");
        $response->withHeader("Pragma", "no-cache");
        $response->withHeader('Content-Type', 'application/json');

        $rawToken = $request->request->get('token');
        $token_hint = $request->request->get('token_type_hint');
        $clientId = $request->request->get('client_id');
        // not required for public apps but mandatory for confidential
        // TODO: @adunsulag it would seem like it'd be better to do this use the Authorization header?
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
                $psrRequest = $this->convertRestRequestToPsrRequest($request);

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
                    $decryptedSecret = $this->getCryptoGen()->decryptStandard($client['client_secret']);
                    if ($decryptedSecret !== $clientSecret) {
                        throw new OAuthServerException('Client failed security', 0, 'invalid_request', Response::HTTP_UNAUTHORIZED);
                    }
                }
            }
            $jsonWebKeyParser = $this->getJsonWebKeyParser();
            // will try hard to go on if missing token hint. this is to help with universal conformance.
            if (empty($token_hint)) {
                $token_hint = $jsonWebKeyParser->getTokenHintFromToken($rawToken);
            } elseif (($token_hint !== 'access_token' && $token_hint !== 'refresh_token') || empty($rawToken)) {
                throw new OAuthServerException('Missing token or unsupported hint.', 0, 'invalid_request', Response::HTTP_BAD_REQUEST);
            }

            // are we there yet! client's okay but, is token?
            if ($token_hint === 'access_token') {
                $result = $jsonWebKeyParser->parseAccessToken($rawToken);
                $result['client_id'] = $clientId;
                $trusted = $this->getTrustedUserService()->getTrustedUser($result['client_id'], $result['sub']);
                if (empty($trusted['id'])) {
                    $result['active'] = false;
                    $result['status'] = 'revoked';
                }
                $tokenRepository = $this->getAccessTokenRepository($request->getSession());
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
                } else {
                    // if we could parse the token, we're going to get the context for the the token
                    $dbTokenWithContext = $this->getAccessTokenRepository($request->getSession())->getTokenByToken($result['jti']);
                    $context = $dbTokenWithContext['context'] ?? '{}';
                    $decodedContext = \json_decode((string)$context, true, 512, JSON_THROW_ON_ERROR);
                    foreach ($decodedContext as $key => $value) {
                        // don't allow overwriting of standard claims
                        if (!in_array($key, ['active', 'status', 'scope', 'exp', 'sub', 'jti', 'aud', 'client_id'], true)) {
                            $result[$key] = $value;
                        }
                    }
                    if (!empty($dbTokenWithContext['user_id'])) {
                        $fhirUserClaim = new FhirUserClaim();
                        $fhirUserClaim->setFhirBaseUrl($this->getServerConfig()->getFhirUrl());
                        $result['fhirUser'] = $fhirUserClaim->getFhirUser($dbTokenWithContext['user_id']);
                    }
                }
            }
            if ($token_hint === 'refresh_token') {
                // client_id comes back from the parsed refresh token
                $result = $jsonWebKeyParser->parseRefreshToken($rawToken);
                $trusted = $this->getTrustedUserService()->getTrustedUser($result['client_id'], $result['sub']);
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

            // convert our date values to unix timestamps to be RFC7662 compliant
            if (isset($result['exp']) && $result['exp'] instanceof \DateTimeImmutable) {
                $result['exp'] = $result['exp']->getTimestamp();
            }
        }
        catch (Exception $exception) {
            // something else went wrong
            $this->getSystemLogger()->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'client_id' => $clientId]);
            // something else went wrong
            // NOTE : per RFC7662 we must return active:false on error for invalid tokens
            return $this->returnInactiveResponse($request);
        }
        // we're here so emit results to interface thank you very much.
        $body = $response->getBody();
        $body->write(json_encode($result));
        return $response->withStatus(Response::HTTP_OK)->withBody($body);
    }
}
