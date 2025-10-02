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
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\IdTokenSMARTResponse;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomRefreshTokenGrant extends RefreshTokenGrant
{
    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(private readonly SessionInterface $session, RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->logger = new SystemLogger();
        parent::__construct($refreshTokenRepository);
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        $client = $this->validateClient($request);
        $oldRefreshToken = $this->validateOldRefreshToken($request, $client->getIdentifier());
        $this->logger->debug("CustomRefreshTokenGrant->respondToAccessTokenRequest() scope info", [
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
                    $decodedContext = \json_decode($context, true, 512, JSON_THROW_ON_ERROR);
                    $this->accessTokenRepository->setContextForNewTokens($decodedContext);
                    if ($responseType instanceof IdTokenSMARTResponse) {
                        $responseType->setContextForNewTokens($decodedContext);
                    }
                } catch (\Exception $exception) {
                    $this->logger->error("OpenEMR Error: failed to decode token context json", ['exception' => $exception->getMessage()
                        , 'tokenId' => $oldRefreshToken['access_token_id']]);
                }
            }
        }
        return parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
    }

//    /**
//     * Retrieve request parameter and populate the site value if the requester hasn't passed it in.  This way
//     * we can handle multi-site.
//     *
//     * @param string                 $parameter
//     * @param ServerRequestInterface $request
//     * @param mixed                  $default
//     *
//     * @return null|string
//     */
//    protected function getRequestParameter($parameter, ServerRequestInterface $request, $default = null)
//    {
//        if ($parameter !== 'scope') {
//            return parent::getRequestParameter($parameter, $request, $default);
//        }
//
//        $requestParameters = (array) $request->getParsedBody();
//        if (isset($requestParameters[$parameter])) {
//            // make sure we are getting the site here
//            if (!preg_match('(site:)', $requestParameters[$parameter])) {
//                return $requestParameters[$parameter] . " site:" . $this->session->get('site_id', 'default');
//            }
//            return $requestParameters[$parameter];
//        } else {
//            return $default;
//        }
//    }


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
        $this->logger->debug("CustomRefreshTokenGrant->validateScopes() Attempting to validateScopes", ["scopes" => $scopes]);
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
        $this->logger->debug("CustomRefreshTokenGrant->validateScopes() scopes validated", ["scopes" => json_encode($validScopes)]);
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

    protected function validateClient(ServerRequestInterface $request)
    {
        $client = parent::validateClient($request);
        if (!($client instanceof ClientEntity)) {
            $this->logger->errorLogCaller("client returned was not a valid ClientEntity ", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }

        if (!$client->isEnabled()) {
            $this->logger->errorLogCaller("client returned was not enabled", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }
        return $client;
    }
}
