<?php

/**
 * Default claim mapper using standard OIDC claims.
 *
 * Works with any OIDC-compliant provider that includes the standard claims
 * (sub, iss, email) in the ID token. Provider-specific mappers (e.g. for GCIP
 * or Azure AD) can extend or replace this for non-standard claim handling.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

final class StandardClaimMapper implements ClaimMapperInterface
{
    public function map(array $claims): NormalizedIdentity
    {
        $sub = $this->requireStringClaim($claims, 'sub');
        $iss = $this->requireStringClaim($claims, 'iss');
        $email = $this->requireStringClaim($claims, 'email');

        return new NormalizedIdentity(
            externalId: $sub,
            issuer: $iss,
            email: $email,
            emailVerified: $this->optionalBoolClaim($claims, 'email_verified'),
            displayName: $this->resolveDisplayName($claims),
        );
    }

    public function supports(array $claims): bool
    {
        return isset($claims['sub'], $claims['iss'], $claims['email'])
            && is_string($claims['sub'])
            && is_string($claims['iss'])
            && is_string($claims['email']);
    }

    /**
     * @param array<string, mixed> $claims
     * @return non-empty-string
     * @throws ClaimMappingException
     */
    private function requireStringClaim(array $claims, string $name): string
    {
        if (!isset($claims[$name]) || !is_string($claims[$name]) || $claims[$name] === '') {
            throw ClaimMappingException::missingClaim($name);
        }

        return $claims[$name];
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function optionalBoolClaim(array $claims, string $name): bool
    {
        if (!isset($claims[$name])) {
            return false;
        }

        return (bool) $claims[$name];
    }

    /**
     * Build display name from available claims, preferring 'name' over
     * 'given_name' + 'family_name'.
     *
     * @param array<string, mixed> $claims
     */
    private function resolveDisplayName(array $claims): string
    {
        if (isset($claims['name']) && is_string($claims['name']) && $claims['name'] !== '') {
            return $claims['name'];
        }

        $parts = [];
        if (isset($claims['given_name']) && is_string($claims['given_name']) && $claims['given_name'] !== '') {
            $parts[] = $claims['given_name'];
        }
        if (isset($claims['family_name']) && is_string($claims['family_name']) && $claims['family_name'] !== '') {
            $parts[] = $claims['family_name'];
        }

        return implode(' ', $parts);
    }
}
