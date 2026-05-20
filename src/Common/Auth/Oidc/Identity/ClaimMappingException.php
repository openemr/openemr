<?php

/**
 * Thrown when JWT claims cannot be mapped to a NormalizedIdentity.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

final class ClaimMappingException extends \RuntimeException
{
    public static function missingClaim(string $claimName): self
    {
        return new self("Required claim '{$claimName}' is missing or empty");
    }
}
