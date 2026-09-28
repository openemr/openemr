<?php

/**
 * CustomRefreshTokenGrant Handles the custom nature of some of our scope api requests to differentiate between the
 * standard api and the regular api.  Since we don't have access to the old refresh token scopes when we are creating
 * our scope repository, we initialize them here so we can use them in our scope repo.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Grant;

use DateInterval;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\IdTokenSMARTResponse;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Services\JWTClientAuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomRefreshTokenGrant extends RefreshTokenGrant
{
    use SystemLoggerAwareTrait;


    /**
     * @var JWTClientAuthenticationService
     */
    private JWTClientAuthenticationService $jwtAuthService;

    /**
     * Per-request memo for validateClient() results. Both our
     * override respondToAccessTokenRequest() and the parent's
     * implementation call $this->validateClient($request) on the
     * same request object — for JWT-authenticated clients the second
     * call would fail because validateJWTClientAssertion records a
     * one-time JTI on the first call. Cache by spl_object_id so the
     * second call reuses the result.
     *
     * Keyed by spl_object_id($request); value is the ClientEntity
     * returned by the previous validation.
     *
     * @var array<int, ClientEntity>
     */
    private array $validateClientMemo = [];

    public function __construct(private readonly SessionInterface $session, RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        parent::__construct($refreshTokenRepository);
    }


    /**
     * Set the JWT authentication service
     *
     * @param JWTClientAuthenticationService $jwtAuthService
     */
    public function setJWTAuthenticationService(JWTClientAuthenticationService $jwtAuthService): void
    {
        $this->jwtAuthService = $jwtAuthService;
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        $client = $this->validateClient($request);
        $oldRefreshToken = $this->validateOldRefreshToken($request, $client->getIdentifier());
        $this->getSystemLogger()->debug("CustomRefreshTokenGrant->respondToAccessTokenRequest() scope info", [
            "oldRefreshToken['scopes']" => $oldRefreshToken['scopes'],
            "requestParameter['scopes']" => $this->getRequestParameter('scope', $request, null),
            "_REQUEST['scope']" => $_REQUEST['scope'] ?? ""
            ]);

        // we are going to grab our old access token and grab any context information that we may have
        if ($this->accessTokenRepository instanceof AccessTokenRepository) {
            $oldToken = $this->accessTokenRepository->getTokenByToken($oldRefreshToken['access_token_id']);
            $context = $oldToken['context'] ?? '{}';
            if (!empty($context)) {
                try {
                    $decodedContext = \json_decode((string) $context, true, 512, JSON_THROW_ON_ERROR);
                    $this->accessTokenRepository->setContextForNewTokens($decodedContext);
                    if ($responseType instanceof IdTokenSMARTResponse) {
                        $responseType->setContextForNewTokens($decodedContext);
                    }
                } catch (\JsonException $exception) {
                    $this->getSystemLogger()->error("OpenEMR Error: failed to decode token context json", ['exception' => $exception, 'tokenId' => $oldRefreshToken['access_token_id']]);
                }
            }
        }
        return parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
    }

    /**
     * Validate scopes in the request and initialize our scope repository with the scopes from the request.
     *
     * @param string|array $scopes
     * @param string       $redirectUri
     *
     * @throws OAuthServerException
     *
     * @return ScopeEntityInterface[]
     */
    public function validateScopes($scopes, $redirectUri = null)
    {
        // TODO: @adunsulag I'm not sure this function is needed anymore since we now validate against the
        // entire server supported scopes.
        $this->getSystemLogger()->debug("CustomRefreshTokenGrant->validateScopes() Attempting to validateScopes", ["scopes" => $scopes]);
        $scopeRepo = $this->scopeRepository;
        if (\is_array($scopes)) {
            $scopes = $this->convertScopesArrayToQueryString($scopes);
        }

        // the scopes will either come from the request,
        // or will come from the OLD refresh token which is
        // exactly what we want to build our requests off

        // TODO: the RefreshTokenGrant requires the sub-scopes to be the EXACT same identifier as the old refresh token
        // this means that a request for a new access token with something like patient/Patient.r when the refresh token
        // has patient/Patient.rs will fail.  This is because the scopes are validated against the old refresh token scopes.
        // if people want this behavior we need to rewrite this method or they can grab a new refresh token with the correct scopes.
        $validScopes = parent::validateScopes($scopes, $redirectUri);
        $this->getSystemLogger()->debug("CustomRefreshTokenGrant->validateScopes() scopes validated", ["scopes" => json_encode($validScopes)]);
        return $validScopes;
    }

    /**
     * Converts a scopes query array to a string so its in the format we need for the scope repository
     *
     * @param array $scopes
     *
     * @return string
     */
    private function convertScopesArrayToQueryString(array $scopes)
    {
        return implode(' ', $scopes);
    }

    /**
     * Override to support JWT client assertions, otherwise fall back to traditional client secret authentication.
     * @param ServerRequestInterface $request
     * @return array
     * @throws OAuthServerException
     */
    protected function getClientCredentials(ServerRequestInterface $request)
    {
        $logger = $this->getSystemLogger();
        // Check if JWT authentication service is available and request has JWT assertion
        if (isset($this->jwtAuthService) && $this->jwtAuthService->hasJWTClientAssertion($request)) {
            $logger->debug('CustomRefreshTokenGrant::getClientCredentials: Detected JWT client assertion, using asymmetric authentication');

            try {
                // Extract client ID from JWT
                $clientId = $this->jwtAuthService->extractClientIdFromJWT($request);
                $logger->debug("CustomRefreshTokenGrant::getClientCredentials: Extracted client ID from JWT", ['client_id' => $clientId]);
            } catch (OAuthServerException $e) {
                $logger->error(
                    'CustomRefreshTokenGrant::getClientCredentials: Failed to extract client ID from JWT',
                    ['error' => $e->getMessage(), 'hint' => $e->getHint()]
                );
                throw $e;
            }
            return [$clientId, null]; // No client secret for JWT authentication
        } else {
            // Fall back to traditional client secret authentication
            $logger->debug('CustomRefreshTokenGrant::getClientCredentials: Using traditional client secret authentication');
            return parent::getClientCredentials($request);
        }
    }

    /**
     * Authenticates the refresh-token client either via a JWT client
     * assertion or a shared client secret. When a JWT assertion is
     * present it is validated directly and the parent shared-secret
     * check is skipped; otherwise the parent runs and enforces
     * whatever ClientRepository::validateClient() requires (which for
     * a confidential client is a matching client_secret).
     *
     * Result is memoized per request object so the same client is
     * returned on repeat calls within a single refresh flow — the
     * parent respondToAccessTokenRequest() runs validateClient()
     * again after our override does, and validateJWTClientAssertion
     * records a one-time JTI that would fail the second call.
     */
    protected function validateClient(ServerRequestInterface $request)
    {
        $requestKey = spl_object_id($request);
        if (isset($this->validateClientMemo[$requestKey])) {
            return $this->validateClientMemo[$requestKey];
        }
        if (isset($this->jwtAuthService) && $this->jwtAuthService->hasJWTClientAssertion($request)) {
            $clientId = $this->jwtAuthService->extractClientIdFromJWT($request);
            if (!is_string($clientId) || $clientId === '') {
                throw OAuthServerException::invalidClient($request);
            }
            $client = $this->clientRepository->getClientEntity($clientId);
            if (!($client instanceof ClientEntity)) {
                throw OAuthServerException::invalidClient($request);
            }
            $this->jwtAuthService->validateJWTClientAssertion($request, $client);
        } else {
            $client = parent::validateClient($request);
            if (!($client instanceof ClientEntity)) {
                // $client may be false / null / a non-ClientEntity, so
                // don't dereference it here. Log the client_id from the
                // request if we can get it.
                $this->getSystemLogger()->error(
                    "Client returned was not a valid ClientEntity",
                    ['client' => $this->getRequestParameter('client_id', $request, null)]
                );
                throw OAuthServerException::invalidClient($request);
            }
        }
        if (!$client->isEnabled()) {
            $this->getSystemLogger()->error("Client {client} returned was not enabled", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }
        $this->validateClientMemo[$requestKey] = $client;
        return $client;
    }
}
