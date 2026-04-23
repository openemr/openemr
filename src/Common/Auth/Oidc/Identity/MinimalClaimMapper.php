<?php

/**
 * Minimal claim mapper requiring only sub and iss.
 *
 * Used by provider-agnostic code paths (e.g. the session refresh endpoint)
 * where only the subject and issuer are needed for security pinning. Does
 * not require email or any provider-specific claims.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

final class MinimalClaimMapper implements ClaimMapperInterface
{
    public function map(array $claims): NormalizedIdentity
    {
        $sub = $claims['sub'] ?? null;
        $iss = $claims['iss'] ?? null;

        if (!is_string($sub) || $sub === '') {
            throw ClaimMappingException::missingClaim('sub');
        }

        if (!is_string($iss) || $iss === '') {
            throw ClaimMappingException::missingClaim('iss');
        }

        $email = '';
        if (isset($claims['email']) && is_string($claims['email'])) {
            $email = $claims['email'];
        }

        return new NormalizedIdentity(
            externalId: $sub,
            issuer: $iss,
            email: $email,
            emailVerified: isset($claims['email_verified']) && (bool) $claims['email_verified'],
        );
    }

    public function supports(array $claims): bool
    {
        return isset($claims['sub'], $claims['iss'])
            && is_string($claims['sub'])
            && is_string($claims['iss']);
    }
}
