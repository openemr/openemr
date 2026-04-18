<?php

/**
 * Generates the OpenID Connect id_token for OpenEMR's SMART on FHIR / OAuth2
 * token responses.
 *
 * The id_token issuance logic is ported from
 * steverhoades/oauth2-openid-connect-server v3.0.1 (MIT License),
 * originally authored by Steve Rhoades. Full MIT permission notice preserved
 * at src/Common/Auth/OpenIDConnect/LICENSE.steverhoades-oauth2-openid-connect-server.
 *
 * @link      http://hl7.org/fhir/smart-app-launch/scopes-and-launch-context/index.html
 * @link      https://www.open-emr.org
 * @link      https://github.com/steverhoades/oauth2-openid-connect-server
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Steve Rhoades <sedonami@gmail.com>
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2018 Steve Rhoades <sedonami@gmail.com>
 * @copyright Copyright (c) 2026 Milan Zivkovic <zivkovic.milan@gmail.com>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Builder as TokenBuilder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use LogicException;
use Nyholm\Psr7\Stream;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\IdentityProviderInterface;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OEIdTokenResponse extends BearerTokenResponse implements LoggerAwareInterface
{
    public const SCOPE_SMART_LAUNCH = 'launch';
    public const SCOPE_OFFLINE_ACCESS = 'offline_access';
    public const SCOPE_SMART_LAUNCH_PATIENT = 'launch/patient';

    /**
     * Context values used when issuing new tokens. Populated by the refresh
     * grant path so a new access token can preserve existing context.
     *
     * @var array<array-key, mixed>|null
     */
    private ?array $contextForNewTokens = null;

    public function __construct(
        private OEGlobalsBag                    $globalsBag,
        private SessionInterface                $session,
        private IdentityProviderInterface       $identityProvider,
        private ClaimExtractor                  $claimExtractor,
        private SMARTSessionTokenContextBuilder $contextBuilder,
        private LoggerInterface|NullLogger      $logger = new NullLogger(),
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHttpResponse(ResponseInterface $response): ResponseInterface
    {
        // If the authorized scopes include offline_access we let the League
        // bearer-token response emit as usual (access + refresh tokens).
        // offline_access should only be granted to confidential clients (that
        // can keep a secret) so we don't need to check the client type here.
        // @see https://github.com/thephpleague/oauth2-server/issues/1005 for
        // why the League server doesn't handle offline_access itself.
        if ($this->hasScope($this->accessToken->getScopes(), self::SCOPE_OFFLINE_ACCESS)) {
            $this->logger->debug(
                'OEIdTokenResponse->generateHttpResponse() offline_access scope present, deferring to parent',
            );
            return parent::generateHttpResponse($response);
        }

        $expireDateTime = $this->accessToken->getExpiryDateTime()->getTimestamp();

        $responseParams = [
            'token_type'   => 'Bearer',
            'expires_in'   => $expireDateTime - time(),
            'access_token' => (string) $this->accessToken,
        ];

        $responseBody = json_encode(array_merge($this->getExtraParams($this->accessToken), $responseParams));

        if ($responseBody === false) {
            throw new LogicException('Error encountered JSON encoding response parameters');
        }

        return $response
            ->withStatus(Response::HTTP_OK)
            ->withHeader('pragma', 'no-cache')
            ->withHeader('cache-control', 'no-store')
            ->withHeader('content-type', 'application/json; charset=UTF-8')
            ->withBody(Stream::create($responseBody));
    }

    /**
     * Emits the id_token alongside OpenEMR's SMART context parameters.
     *
     * @return array<array-key, mixed>
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        $idTokenParams = $this->buildIdTokenParams($accessToken);

        $contextParams = $this->getContextForNewAccessTokens($accessToken->getScopes());

        $extraParams = array_merge($idTokenParams, $contextParams);

        // The SMART spec requires the authorized scopes to be returned on
        // the token response. They're already inside the access token, but
        // to stay spec-compliant we echo them at the envelope level too.
        $extraParams['scope'] = $this->getScopeString($accessToken->getScopes());

        $this->logger->debug('OEIdTokenResponse->getExtraParams() final params', ['params' => $extraParams]);

        return $extraParams;
    }

    /**
     * Builds and signs the id_token JWT. Returns an empty array when the
     * access token's scopes do not include `openid`.
     *
     * Ported from steverhoades/oauth2-openid-connect-server v3.0.1
     * IdTokenResponse::getExtraParams().
     *
     * @return array<string, string>
     */
    private function buildIdTokenParams(AccessTokenEntityInterface $accessToken): array
    {
        if (!$this->hasScope($accessToken->getScopes(), 'openid')) {
            return [];
        }

        $userIdentifier = $accessToken->getUserIdentifier();
        if (!is_string($userIdentifier) || $userIdentifier === '') {
            throw new RuntimeException('Access token is missing a user identifier for id_token issuance');
        }

        $userEntity = $this->identityProvider->getUserEntityByIdentifier($userIdentifier);

        $builder = $this->getBuilder($accessToken, $userEntity);

        $claims = $this->claimExtractor->extract($accessToken->getScopes(), $userEntity->getClaims());
        foreach ($claims as $claimName => $claimValue) {
            $builder = $builder->withClaim($claimName, $claimValue);
        }

        $keyContents = $this->privateKey->getKeyContents();
        if ($keyContents === '') {
            $keyContents = (string) file_get_contents($this->privateKey->getKeyPath());
        }
        if ($keyContents === '') {
            throw new RuntimeException('Unable to read private signing key for id_token issuance');
        }

        $token = $builder->getToken(
            new Sha256(),
            InMemory::plainText($keyContents, (string) $this->privateKey->getPassPhrase()),
        );

        return ['id_token' => $token->toString()];
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity): Builder
    {
        $builder = new TokenBuilder(new JoseEncoder(), ChainedFormatter::withUnixTimestampDates());

        $clientId = $accessToken->getClient()->getIdentifier();
        assert($clientId !== '');
        $siteAddr = $this->globalsBag->getString('site_addr_oath');
        $siteId = $this->session->get('site_id');
        $issuer = $siteAddr
            . $this->globalsBag->getKernel()->getWebRoot()
            . '/oauth2/'
            . (is_string($siteId) ? $siteId : '');
        $userId = $userEntity->getIdentifier();
        assert(is_string($userId) && $userId !== '');

        $builder = $builder
            ->permittedFor($clientId)
            ->issuedBy($issuer)
            ->issuedAt(new \DateTimeImmutable('@' . time()))
            ->expiresAt(new \DateTimeImmutable('@' . $accessToken->getExpiryDateTime()->getTimestamp()))
            ->relatedTo($userId);

        if ($this->session->has('nonce')) {
            $nonce = $this->session->get('nonce');
            $this->logger->debug('OEIdTokenResponse->getBuilder() nonce found in session', ['nonce' => $nonce]);
            $builder = $builder->withClaim('nonce', $nonce);
        } else {
            $this->logger->debug('OEIdTokenResponse->getBuilder() no nonce found in session');
        }

        return $builder;
    }

    /**
     * @param array<ScopeEntityInterface> $scopes
     */
    private function hasScope(array $scopes, string $searchScope): bool
    {
        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() === $searchScope) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<ScopeEntityInterface> $scopes
     */
    private function getScopeString(array $scopes): string
    {
        $scopeList = [];
        foreach ($scopes as $scope) {
            $scopeId = (string) $scope->getIdentifier();
            // Don't include granular api:<resource> scopes in the envelope
            // scope string. They're still bundled into the access token but
            // exposing them here breaks ONC certification, which rejects
            // custom scope permissions even though they're valid per spec.
            if (!str_starts_with($scopeId, 'api:')) {
                $scopeList[] = $scopeId;
            }
        }
        return implode(' ', $scopeList);
    }

    /**
     * Sets the context array saved to the database for new access tokens.
     *
     * @param array<array-key, mixed> $context
     */
    public function setContextForNewTokens(array $context): void
    {
        $this->contextForNewTokens = $context !== [] ? $context : null;
    }

    /**
     * Returns the context to use for new access tokens: the existing context
     * if one was preserved (refresh grant), otherwise a fresh context derived
     * from the scopes.
     *
     * @param array<ScopeEntityInterface> $scopes
     * @return array<array-key, mixed>
     */
    private function getContextForNewAccessTokens(array $scopes): array
    {
        if ($this->contextForNewTokens !== null && $this->contextForNewTokens !== []) {
            return $this->contextBuilder->getContextForScopesWithExistingContext($this->contextForNewTokens, $scopes);
        }

        return $this->contextBuilder->getContextForScopes($scopes);
    }
}
