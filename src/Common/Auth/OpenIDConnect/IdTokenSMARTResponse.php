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
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use LogicException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use OpenEMR\Services\PatientService;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class IdTokenSMARTResponse extends IdTokenResponse
{
    const SCOPE_SMART_LAUNCH = 'launch';
    const SCOPE_OFFLINE_ACCESS = 'offline_access';
    const SCOPE_SMART_LAUNCH_PATIENT = 'launch/patient';
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean
     */
    private $isAuthorizationGrant;

    /**
     * @var SMARTSessionTokenContextBuilder
     */
    private $contextBuilder;

    /**
     * The context values to use for issuing new tokens.  This is populated when a refresh grant is generating a new
     * access token.  We have to use our existing values.
     * @var array
     */
    private $contextForNewTokens;

    /**
     * @var Environment The twig environment.
     */
    private $twig;

    public function __construct(
        IdentityProviderInterface $identityProvider,
        ClaimExtractor $claimExtractor,
        Environment $twig
    ) {
        $this->isAuthorizationGrant = false;
        $this->logger = new SystemLogger();
        $this->contextBuilder = new SMARTSessionTokenContextBuilder($_SESSION, $twig);
        $this->twig = $twig;
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
            ->withStatus(200)
            ->withHeader('pragma', 'no-cache')
            ->withHeader('cache-control', 'no-store')
            ->withHeader('content-type', 'application/json; charset=UTF-8');

        $response->getBody()->write($responseParams);

        return $response;
    }

    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        $extraParams = parent::getExtraParams($accessToken);

        $scopes = $accessToken->getScopes();
        $this->logger->debug("IdTokenSMARTResponse->getExtraParams() params from parent ", ["params" => $extraParams]);

        $contextParams = $this->getContextForNewAccessTokens($scopes);
        $extraParams = array_merge($extraParams, $contextParams);
        // response should return the scopes we authorized inside the accessToken to be smart compatible
        // I would think this would be better put in the id_token but to be spec compliant we have to have this here
        $extraParams['scope'] = $this->getScopeString($accessToken->getScopes());

        $this->logger->debug("IdTokenSMARTResponse->getExtraParams() final params", ["params" => $extraParams]);
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
            if (strpos($scopeId, ':') === false) {
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
        return $builder
            ->permittedFor($accessToken->getClient()->getIdentifier())
            ->issuedBy($GLOBALS['site_addr_oath'] . $GLOBALS['webroot'] . "/oauth2/" . $_SESSION['site_id'])
            ->issuedAt(new \DateTimeImmutable('@' . time()))
            ->expiresAt(new \DateTimeImmutable('@' . $accessToken->getExpiryDateTime()->getTimestamp()))
            ->relatedTo($userEntity->getIdentifier());
    }

    /**
     * Sets the context array that will be saved to the database for new acess tokens.
     * @param $context The array of context variables.  If this is not an array the context is set to null;
     */
    public function setContextForNewTokens($context)
    {
        $this->contextForNewTokens = is_array($context) && !empty($context) ? $context : null;
    }

    /**
     * Retrieves the context to use for new access tokens based upon the passed in scopes.  It will use the existing
     * context saved in the repositoryor will build a new context from the passed in scopes.
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
