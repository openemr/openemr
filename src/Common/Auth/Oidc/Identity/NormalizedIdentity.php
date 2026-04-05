<?php

/**
 * Immutable value object representing a user's identity normalized from an OIDC token.
 *
 * Provider-specific claims are mapped into this common structure so that the rest
 * of the codebase never deals with raw JWT claims or provider differences.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

final readonly class NormalizedIdentity
{
    /**
     * @param string      $externalId    The 'sub' claim — unique per user per provider (must not be empty).
     * @param string      $issuer        The 'iss' claim — identifies which provider (must not be empty).
     * @param string      $email         Email address from the token (empty when not available,
     *                                   e.g. in token refresh where only sub/iss are needed).
     * @param bool        $emailVerified Whether the provider has verified the email.
     * @param string      $displayName   Human-readable name (may be empty if not provided).
     * @param string|null $tenantId      Provider-specific tenant identifier (e.g. GCIP firebase.tenant).
     */
    public function __construct(
        public string $externalId,
        public string $issuer,
        public string $email = '',
        public bool $emailVerified = false,
        public string $displayName = '',
        public ?string $tenantId = null,
    ) {
        if ($externalId === '') {
            throw new \DomainException('External ID (sub) must not be empty');
        }
        if ($issuer === '') {
            throw new \DomainException('Issuer (iss) must not be empty');
        }
    }

    /**
     * The globally unique key for this identity: issuer + external ID.
     *
     * @return non-empty-string
     */
    public function getCompositeKey(): string
    {
        return $this->issuer . '|' . $this->externalId;
    }
}
