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
     * @param NormalizedIdentity   $identity      The normalized identity from the token.
     * @param array<string, mixed> $claims        All raw claims from the token.
     * @param \DateTimeImmutable   $expiresAt     When the token expires.
     * @param string|null          $jti           The literal `jti` claim if the IdP emitted
     *                                            one. Null for providers that omit it
     *                                            (e.g. Firebase/GCIP in some configurations).
     *                                            Useful for audit logging where "what the
     *                                            IdP actually emitted" matters.
     * @param non-empty-string     $revocationKey The validator's per-token-issuance
     *                                            identifier. Equals `$jti` when present, or
     *                                            a synthetic `oidc-synthetic:hash(iss|sub|iat)`
     *                                            value when not. Always non-null. This is
     *                                            the key the validator uses for replay
     *                                            protection and revocation lookups; callers
     *                                            tracking the token across requests
     *                                            (session storage, logout listeners, future
     *                                            admin-revocation API) should persist this
     *                                            value, not `$jti`.
     */
    public function __construct(
        public NormalizedIdentity $identity,
        public array $claims,
        public \DateTimeImmutable $expiresAt,
        public ?string $jti,
        public string $revocationKey,
    ) {
    }
}
