<?php

/**
 * GCIP-specific claim mapper.
 *
 * Maps GCIP/Firebase ID token claims to NormalizedIdentity, extracting
 * the firebase.tenant field into tenantId.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth\Auth;

use OpenEMR\Common\Auth\Oidc\Identity\ClaimMapperInterface;
use OpenEMR\Common\Auth\Oidc\Identity\ClaimMappingException;
use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;

final class GcipClaimMapper implements ClaimMapperInterface
{
    public function map(array $claims): NormalizedIdentity
    {
        if (!isset($claims['sub']) || !is_string($claims['sub']) || $claims['sub'] === '') {
            throw new ClaimMappingException('GCIP token missing required "sub" claim');
        }

        if (!isset($claims['iss']) || !is_string($claims['iss'])) {
            throw new ClaimMappingException('GCIP token missing required "iss" claim');
        }

        if (!isset($claims['email']) || !is_string($claims['email']) || $claims['email'] === '') {
            throw new ClaimMappingException('GCIP token missing required "email" claim');
        }

        $givenName = isset($claims['given_name']) && is_string($claims['given_name']) ? $claims['given_name'] : '';
        $familyName = isset($claims['family_name']) && is_string($claims['family_name']) ? $claims['family_name'] : '';
        $name = isset($claims['name']) && is_string($claims['name'])
            ? $claims['name']
            : trim($givenName . ' ' . $familyName);

        $tenantId = null;
        if (isset($claims['firebase']) && is_array($claims['firebase'])) {
            $tenantId = isset($claims['firebase']['tenant']) && is_string($claims['firebase']['tenant'])
                ? $claims['firebase']['tenant']
                : null;
        }

        return new NormalizedIdentity(
            externalId: $claims['sub'],
            issuer: $claims['iss'],
            email: $claims['email'],
            emailVerified: !empty($claims['email_verified']),
            displayName: $name,
            tenantId: $tenantId,
        );
    }

    public function supports(array $claims): bool
    {
        return isset($claims['sub'], $claims['iss']);
    }
}
