<?php

/**
 * Configuration for OIDC token validation.
 *
 * Encapsulates the expected issuer, audience, and timing tolerances
 * that the token validator checks against.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

final readonly class OidcValidationParameters
{
    private const DEFAULT_CLOCK_SKEW_SECONDS = 30;
    private const DEFAULT_MAX_TOKEN_AGE_SECONDS = 86400; // 24 hours

    /**
     * @param string $expectedIssuer     The expected iss claim (e.g. "https://accounts.google.com").
     * @param string $expectedAudience   The expected aud claim (your client ID).
     * @param int    $clockSkewSeconds   Allowed clock skew for exp/iat/nbf validation.
     * @param int    $maxTokenAgeSeconds Maximum acceptable age based on iat claim.
     * @param list<string> $allowedAlgorithms Accepted signing algorithms.
     */
    public function __construct(
        public string $expectedIssuer,
        public string $expectedAudience,
        public int $clockSkewSeconds = self::DEFAULT_CLOCK_SKEW_SECONDS,
        public int $maxTokenAgeSeconds = self::DEFAULT_MAX_TOKEN_AGE_SECONDS,
        public array $allowedAlgorithms = ['RS256'],
    ) {
    }
}
