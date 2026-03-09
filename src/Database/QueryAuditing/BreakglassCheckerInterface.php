<?php

/**
 * Interface for breakglass user checking.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@gmail.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Checks if a user is a "breakglass" (emergency access) user.
 *
 * Breakglass users may have all their queries logged regardless of
 * normal audit settings when breakglass logging is forced.
 */
interface BreakglassCheckerInterface
{
    /**
     * Check if the given username is a breakglass user.
     *
     * @param string $username The username to check
     */
    public function isBreakglassUser(string $username): bool;
}
