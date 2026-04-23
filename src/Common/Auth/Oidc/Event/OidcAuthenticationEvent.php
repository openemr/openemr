<?php

/**
 * Dispatched after successful OIDC token validation and local user resolution.
 *
 * Module listeners can use this for audit logging, session enrichment,
 * or any post-authentication logic. The event is read-only — the
 * authentication decision has already been made.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Event;

use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use Symfony\Contracts\EventDispatcher\Event;

final class OidcAuthenticationEvent extends Event
{
    public const EVENT_NAME = 'oidc.authentication.success';

    /**
     * @param NormalizedIdentity   $identity  The validated external identity.
     * @param int                  $userId    The local OpenEMR user ID.
     * @param string               $username  The local OpenEMR username.
     * @param \DateTimeImmutable   $expiresAt When the OIDC token expires.
     * @param string|null          $jti       The JWT ID claim, if present.
     * @param array<string, mixed> $claims    All raw claims from the token.
     */
    public function __construct(
        private readonly NormalizedIdentity $identity,
        private readonly int $userId,
        private readonly string $username,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly ?string $jti = null,
        private readonly array $claims = [],
    ) {
    }

    public function getIdentity(): NormalizedIdentity
    {
        return $this->identity;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getJti(): ?string
    {
        return $this->jti;
    }

    /**
     * @return array<string, mixed>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }
}
