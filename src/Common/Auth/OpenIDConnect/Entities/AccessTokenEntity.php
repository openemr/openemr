<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    use AccessTokenTrait;
    use TokenEntityTrait;
    use EntityTrait;

    /**
     * @var bool
     */
    private bool $revoked = false;

    private string $issuer;

    public function setIssuer(?string $issuer): void
    {
        $this->issuer = $issuer;
    }
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIsRevoked(bool $revoked): void
    {
        $this->revoked = $revoked;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function isExpired(): bool
    {
        return $this->getExpiryDateTime() < new DateTimeImmutable();
    }

    // Note iss wasn't required in OAuth2 league https://github.com/thephpleague/oauth2-server/issues/1434
    // but it now is a required part of OAuth2 JWT access tokens. So we override the method to add it.
    // it appears that this may come in the future https://github.com/thephpleague/oauth2-server/issues/1434
    private function convertToJWT()
    {
        $this->initJwtConfiguration();

        $clientId = $this->getClient()->getIdentifier();
        assert($clientId !== '');
        $tokenId = $this->getIdentifier();
        assert(is_string($tokenId) && $tokenId !== '');
        $userId = (string) $this->getUserIdentifier();
        assert($userId !== '');

        $builder = $this->jwtConfiguration->builder()
            ->permittedFor($clientId)
            ->identifiedBy($tokenId)
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($userId)
            ->withClaim('scopes', $this->getScopes());
        // add issuer to token
        $issuer = $this->getIssuer();
        if ($issuer !== null && $issuer !== '') {
            $builder = $builder->issuedBy($issuer);
        }
        return $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }
}
