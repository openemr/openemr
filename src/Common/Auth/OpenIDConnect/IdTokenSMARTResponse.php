<?php

/**
 * Handles extra claims required for SMART on FHIR requests
 * @see http://hl7.org/fhir/smart-app-launch/scopes-and-launch-context/index.html
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect;

use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use LogicException;
use Nyholm\Psr7\Stream;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

// TODO: Look at renaming this class to OEIdTokenResponse since it applies to all OpenEMR OAuth2 responses
class IdTokenSMARTResponse extends IdTokenResponse
{
    use SystemLoggerAwareTrait;

    const SCOPE_SMART_LAUNCH = 'launch';
    const SCOPE_OFFLINE_ACCESS = 'offline_access';
    const SCOPE_SMART_LAUNCH_PATIENT = 'launch/patient';

    /**
     * @var boolean
     */
    private $isAuthorizationGrant;

    /**
     * The context values to use for issuing new tokens.  This is populated when a refresh grant is generating a new
     * access token.  We have to use our existing values.
     * @var array
     */
    private $contextForNewTokens;

    public function __construct(
        private OEGlobalsBag $globalsBag,
        private SessionInterface $session,
        IdentityProviderInterface $identityProvider,
        ClaimExtractor $claimExtractor,
        private SMARTSessionTokenContextBuilder $contextBuilder,
    ) {
        $this->isAuthorizationGrant = false;
        parent::__construct($identityProvider, $claimExtractor);
    }

    public function markIsAuthorizationGrant()
    {
        $this->isAuthorizationGrant = true;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHttpResponse(ResponseInterface $response)
    {
        // if we have offline_access then we allow the refresh token and everything to proceed as normal
        // if we don't have offline access we need to remove the refresh token
        // offline_access should only be granted to confidential clients (that can keep a secret) so we don't need
        // to check the client type here.
        // unfortunately League right now isn't supporting offline_access so we have to duplicate this code here
        // @see https://github.com/thephpleague/oauth2-server/issues/1005 for the oauth2 discussion on why they are
        // not handling oauth2 offline_access with refresh_tokens.
        if ($this->hasScope($this->accessToken->getScopes(), self::SCOPE_OFFLINE_ACCESS)) {
            $this->getSystemLogger()->debug("IdTokenSMARTResponse->generateHttpResponse() no offline_access scope, calling parent method");
            return parent::generateHttpResponse($response);
        }

        $expireDateTime = $this->accessToken->getExpiryDateTime()->getTimestamp();

        $responseParams = [
            'token_type'   => 'Bearer',
            'expires_in'   => $expireDateTime - \time(),
            'access_token' => (string) $this->accessToken,
        ];

        // we don't allow the refresh token if we don't have the offline capability
        $responseParams = \json_encode(\array_merge($this->getExtraParams($this->accessToken), $responseParams));

        if ($responseParams === false) {
            throw new LogicException('Error encountered JSON encoding response parameters');
        }

        $response = $response
            ->withStatus(Response::HTTP_OK)
            ->withHeader('pragma', 'no-cache')
            ->withHeader('cache-control', 'no-store')
            ->withHeader('content-type', 'application/json; charset=UTF-8')
            ->withBody(Stream::create($responseParams));
        return $response;
    }

    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        $extraParams = parent::getExtraParams($accessToken);

        $scopes = $accessToken->getScopes();

        $contextParams = $this->getContextForNewAccessTokens($scopes);
        $extraParams = array_merge($extraParams, $contextParams);
        // response should return the scopes we authorized inside the accessToken to be smart compatible
        // I would think this would be better put in the id_token but to be spec compliant we have to have this here
        $extraParams['scope'] = $this->getScopeString($accessToken->getScopes());

        $this->getSystemLogger()->debug("IdTokenSMARTResponse->getExtraParams() final params", ["params" => $extraParams]);
        return $extraParams;
    }


    private function hasScope($scopes, $searchScope)
    {
        // Verify scope and make sure openid exists.
        $valid  = false;

        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() == $searchScope) {
                $valid = true;
                break;
            }
        }

        return $valid;
    }

    private function getScopeString($scopes)
    {
        $scopeList = [];
        foreach ($scopes as $scope) {
            $scopeId = $scope->getIdentifier();
            // don't include scopes like site:default
            // they still get bundled into the AccessToken but for ONC certification
            // it won't allow custom scope permissions even though this is valid per Open ID Connect spec
            // so we will just skip listing in the 'scopes' response that is sent back to
            // the client.
            if (!str_contains((string) $scopeId, ':')) {
                $scopeList[] = $scopeId;
            }
        }
        return implode(' ', $scopeList);
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity): Builder
    {
        $claimsFormatter = ChainedFormatter::withUnixTimestampDates();
        $builder = new Builder(new JoseEncoder(), $claimsFormatter);

        // Add required id_token claims
        $builder = $builder
            ->permittedFor($accessToken->getClient()->getIdentifier())
            ->issuedBy($this->globalsBag->get('site_addr_oath') . $this->globalsBag->get('webroot') . "/oauth2/" . $this->session->get('site_id'))
            ->issuedAt(new \DateTimeImmutable('@' . time()))
            ->expiresAt(new \DateTimeImmutable('@' . $accessToken->getExpiryDateTime()->getTimestamp()))
            ->relatedTo($userEntity->getIdentifier());
        if ($this->session->has("nonce")) {
            $nonce = $this->session->get("nonce");
            $this->getSystemLogger()->debug("IdTokenSMARTResponse->getBuilder() nonce found in session", ["nonce" => $nonce]);
            $builder = $builder->withClaim('nonce', $nonce);
        } else {
            $this->getSystemLogger()->debug("IdTokenSMARTResponse->getBuilder() no nonce found in session");
        }
        return $builder;
    }

    /**
     * Sets the context array that will be saved to the database for new acess tokens.
     * @param array $context The array of context variables.  If this is not an array the context is set to null;
     */
    public function setContextForNewTokens(array $context)
    {
        $this->contextForNewTokens = !empty($context) ? $context : null;
    }

    /**
     * Retrieves the context to use for new access tokens based upon the passed in scopes.  It will use the existing
     * context saved in the repository or will build a new context from the passed in scopes.
     * @param $scopes The scopes in the access token that determines what context variables to use in the access token
     * @return array The built context session.
     */
    private function getContextForNewAccessTokens($scopes)
    {
        if (!empty($this->contextForNewTokens)) {
            $context = $this->contextBuilder->getContextForScopesWithExistingContext($this->contextForNewTokens, $scopes) ?? [];
        } else {
            $context = $this->contextBuilder->getContextForScopes($scopes) ?? [];
        }
        return $context;
    }
}
