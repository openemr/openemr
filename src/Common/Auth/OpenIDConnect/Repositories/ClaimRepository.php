<?php

/**
 * ClaimRepository.php
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use InvalidArgumentException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClaimSetEntity;

class ClaimRepository implements ClaimSetRepositoryInterface
{
    public const SUPPORTED_CLAIMS = [
        'profile',
        'email',
        'email_verified',
        'phone',
        'phone_verified',
        'family_name',
        'given_name',
        'fhirUser',
        'locale',
        'api:oemr',
        'api:fhir',
        'api:port',
        'aud', // client_id
        'iat', // token create time
        'iss', // token issuer (https://domain)
        'exp', // token expiry time
        'sub'  // the subject of token, usually patient UUID
    ];
    public const PROTECTED_CLAIMS = [
        'profile',
        'email',
        'address',
        'phone'
    ];

    /**
     * @return string[] The claims supported by the server
     */
    public function getSupportedClaims(): array
    {
        return self::SUPPORTED_CLAIMS;
    }

    public function getClaimSetByScopeIdentifier(string $scopeIdentifier): ?ClaimSetEntity
    {
        if (in_array($scopeIdentifier, self::PROTECTED_CLAIMS, true)) {
            return null;
        }
        if (!in_array($scopeIdentifier, $this->getSupportedClaims(), true)) {
            throw new InvalidArgumentException("Unsupported scope identifier: " . $scopeIdentifier);
        }
        return new ClaimSetEntity($scopeIdentifier, [$scopeIdentifier]);
    }
}
