<?php

/**
 * Interface for checking if a user has breakglass (emergency access) privileges
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

interface BreakglassCheckerInterface
{
    public function isBreakglassUser(string $username): bool;
}
