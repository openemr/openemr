<?php

/**
 * AuthHashPortalPasswordHasher — production PortalPasswordHasher wrapping
 * AuthHash::passwordHash.
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
    public function hash(string $plain): string
    {
        return (new AuthHash())->passwordHash($plain);
    }
}
