<?php

/**
 * Result of a successful OIDC token validation.
 *
 * Contains the parsed claims and the NormalizedIdentity derived from them.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;

final readonly class ValidatedToken
{
    /**
     * @param NormalizedIdentity   $identity  The normalized identity from the token.
     * @param array<string, mixed> $claims    All raw claims from the token.
     * @param \DateTimeImmutable   $expiresAt When the token expires.
     * @param string|null          $jti       The JWT ID claim, if present.
     */
    public function __construct(
        public NormalizedIdentity $identity,
        public array $claims,
        public \DateTimeImmutable $expiresAt,
        public ?string $jti = null,
    ) {
    }
}
