<?php

/**
 * AuthHashPortalPasswordHasher — production PortalPasswordHasher wrapping
 * AuthHash::passwordHash. Narrows AuthHash's `mixed` return to string|false so
 * the controller has a single, testable failure mode.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal;

use OpenEMR\Common\Auth\AuthHash;

final class AuthHashPortalPasswordHasher implements PortalPasswordHasher
{
    public function hash(string $plain): string|false
    {
        $result = (new AuthHash())->passwordHash($plain);
        return is_string($result) ? $result : false;
    }
}
