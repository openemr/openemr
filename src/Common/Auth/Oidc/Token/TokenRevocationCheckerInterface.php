<?php

/**
 * Read-side interface for the OIDC token revocation list. Lets the validator
 * depend on a tiny surface that's trivial to fake in tests.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

interface TokenRevocationCheckerInterface
{
    public function isRevoked(string $jti): bool;
}
