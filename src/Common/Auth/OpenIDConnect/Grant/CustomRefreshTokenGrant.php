<?php

/**
 * CustomRefreshTokenGrant Handles the custom nature of some of our scope api requests to differentiate between the
 * standard api and the regular api.  Since we don't have access to the old refresh token scopes when we are creating
 * our scope repository, we initialize them here so we can use them in our scope repo.
 * @package openemr
 * @link      http://www.open-emr.org
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
use OpenEMR\Services\JWTClientAuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;

class CustomRefreshTokenGrant extends RefreshTokenGrant
{
    use SystemLoggerAwareTrait;


    /**
     * @var JWTClientAuthenticationService
     */
    private JWTClientAuthenticationService $jwtAuthService;

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

        // validate everything to do with the JWT...
        // Check if JWT authentication service is available and request has JWT assertion
        if (isset($this->jwtAuthService) && $this->jwtAuthService->hasJWTClientAssertion($request)) {
            // Validate JWT assertion
            try {
                $this->jwtAuthService->validateJWTClientAssertion($request, $client);
                $this->getSystemLogger()->debug("CustomRefreshTokenGrant->validateClient() JWT assertion validated successfully");
            } catch (OAuthServerException $e) {
                $this->getSystemLogger()->error(
                    "CustomRefreshTokenGrant->validateClient() JWT validation failed",
                    ['error' => $e->getMessage(), 'hint' => $e->getHint()]
                );
                throw $e;
            }
        }

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
                } catch (\Exception $exception) {
                    $this->getSystemLogger()->error("OpenEMR Error: failed to decode token context json", ['exception' => $exception->getMessage()
                        , 'tokenId' => $oldRefreshToken['access_token_id']]);
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
        // TODO: @adunsulag I'm not sure this funciton is needed anymore since we now validate against the
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

    protected function validateClient(ServerRequestInterface $request)
    {
        $client = parent::validateClient($request);
        if (!($client instanceof ClientEntity)) {
            $this->getSystemLogger()->errorLogCaller("client returned was not a valid ClientEntity ", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }

        if (!$client->isEnabled()) {
            $this->getSystemLogger()->errorLogCaller("client returned was not enabled", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }
        return $client;
    }
}
