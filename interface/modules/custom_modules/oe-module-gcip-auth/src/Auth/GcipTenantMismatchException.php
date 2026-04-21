<?php

/**
 * Thrown when an otherwise-valid GCIP token carries a `firebase.tenant`
 * claim that is not in the configured allowlist, or no tenant claim at
 * all while the allowlist is non-empty.
 *
 * Kept as a distinct class (not folded into a generic auth exception) so
 * that tenant-boundary violations produce a unique signature in logs and
 * tests — easy to grep, easy to alert on.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth\Auth;

final class GcipTenantMismatchException extends \RuntimeException
{
    /**
     * @param list<string> $allowedTenantIds
     */
    public function __construct(
        public readonly array $allowedTenantIds,
        public readonly ?string $tokenTenantId,
    ) {
        parent::__construct('GCIP token tenant does not match the configured allowlist');
    }
}
