<?php

/**
 * PortalPasswordHasher — narrow seam over AuthHash::passwordHash for the portal
 * login controller. Exists so tests can inject a hasher that returns false,
 * exercising the defensive throw in the controller's password-change branch.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal;

interface PortalPasswordHasher
{
    public function hash(string $plain): string|false;
}
