<?php

/**
 * Contract for mapping raw OIDC JWT claims to a NormalizedIdentity.
 *
 * Each OIDC provider may encode identity information differently. Implementations
 * of this interface translate provider-specific claim structures into the common
 * NormalizedIdentity value object.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

interface ClaimMapperInterface
{
    /**
     * Map raw JWT claims to a NormalizedIdentity.
     *
     * @param array<string, mixed> $claims Decoded JWT payload.
     * @throws ClaimMappingException If required claims are missing or invalid.
     */
    public function map(array $claims): NormalizedIdentity;

    /**
     * Determine whether this mapper can handle the given claims.
     *
     * Used to select the correct mapper when multiple providers are configured.
     * Implementations should inspect provider-specific markers (e.g. the 'firebase'
     * claim for GCIP, the 'tid' claim for Azure AD) without performing full validation.
     *
     * @param array<string, mixed> $claims Decoded JWT payload.
     */
    public function supports(array $claims): bool;
}
