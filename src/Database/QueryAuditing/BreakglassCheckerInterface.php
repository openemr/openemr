<?php

/**
 * Interface for breakglass user checking.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

use Doctrine\DBAL\Driver\Connection;

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
     * @param Connection $connection Connection to use for the lookup
     * @param string $username The username to check
     */
    public function isBreakglassUser(Connection $connection, string $username): bool;
}
