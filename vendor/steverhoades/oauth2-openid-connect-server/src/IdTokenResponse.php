<?php
/**
 * @author Steve Rhoades <sedonami@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace OpenIDConnectServer;

use Lcobucci\JWT\Signer\Key\LocalFileReference;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class IdTokenResponse extends BearerTokenResponse
{
    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ClaimExtractor
     */
    protected $claimExtractor;

    public function __construct(
        IdentityProviderInterface $identityProvider,
        ClaimExtractor $claimExtractor
    ) {
        $this->identityProvider = $identityProvider;
        $this->claimExtractor   = $claimExtractor;
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity)
    {
        if (class_exists("Lcobucci\JWT\Token\Builder")) {
            $claimsFormatter = \Lcobucci\JWT\Encoding\ChainedFormatter::withUnixTimestampDates();
            $builder = new \Lcobucci\JWT\Token\Builder(new \Lcobucci\JWT\Encoding\JoseEncoder(), $claimsFormatter);
        } else {
            $builder = new \Lcobucci\JWT\Builder();
        }

        // Since version 8.0 league/oauth2-server returns \DateTimeImmutable
        $expiresAt = $accessToken->getExpiryDateTime();
        if ($expiresAt instanceof \DateTime) {
            $expiresAt = \DateTimeImmutable::createFromMutable($expiresAt);
        }

        // Add required id_token claims
        $builder
            ->permittedFor($accessToken->getClient()->getIdentifier())
            ->issuedBy('https://' . $_SERVER['HTTP_HOST'])
            ->issuedAt(new \DateTimeImmutable())
            ->expiresAt($expiresAt)
            ->relatedTo($userEntity->getIdentifier());

        return $builder;
    }

    /**
     * @param AccessTokenEntityInterface $accessToken
     * @return array
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        if (false === $this->isOpenIDRequest($accessToken->getScopes())) {
            return [];
        }

        /** @var UserEntityInterface $userEntity */
        $userEntity = $this->identityProvider->getUserEntityByIdentifier($accessToken->getUserIdentifier());

        if (false === is_a($userEntity, UserEntityInterface::class)) {
            throw new \RuntimeException('UserEntity must implement UserEntityInterface');
        } else if (false === is_a($userEntity, ClaimSetInterface::class)) {
            throw new \RuntimeException('UserEntity must implement ClaimSetInterface');
        }

        // Add required id_token claims
        $builder = $this->getBuilder($accessToken, $userEntity);

        // Need a claim factory here to reduce the number of claims by provided scope.
        $claims = $this->claimExtractor->extract($accessToken->getScopes(), $userEntity->getClaims());

        foreach ($claims as $claimName => $claimValue) {
            $builder = $builder->withClaim($claimName, $claimValue);
        }

        $token = $builder->getToken(
            new Sha256(),
            LocalFileReference::file($this->privateKey->getKeyPath(), (string) $this->privateKey->getPassPhrase())
        );

        return [
            'id_token' => $token->toString()
        ];
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    private function isOpenIDRequest($scopes)
    {
        // Verify scope and make sure openid exists.
        $valid  = false;

        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() === 'openid') {
                $valid = true;
                break;
            }
        }

        return $valid;
    }

}
