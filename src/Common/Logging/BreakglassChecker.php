<?php

/**
 * Checks if a user is in the breakglass (emergency access) group
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

use Doctrine\DBAL\Connection;

class BreakglassChecker implements BreakglassCheckerInterface
{
    /**
     * Memoized results keyed by username
     * @var array<string, bool>
     */
    private array $cache = [];

    /**
     * CRITICAL: The connection must be separate from the main application
     * connection. If it shares the audited connection, you will create a
     * dependency cycle when this is used as DBAL middleware - the audit
     * system needs to query the database, but the database connection setup
     * would depend on the audit system.
     */
    public function __construct(
        private readonly Connection $conn,
    ) {
    }

    public function isBreakglassUser(string $user): bool
    {
        if ($user === '') {
            return false;
        }

        if (array_key_exists($user, $this->cache)) {
            return $this->cache[$user];
        }

        $sql = <<<'SQL'
            SELECT 1
            FROM gacl_aro aro
            JOIN gacl_groups_aro_map map ON aro.id = map.aro_id
            JOIN gacl_aro_groups grp ON map.group_id = grp.id
            WHERE grp.value = 'breakglass'
              AND BINARY aro.value = ?
            SQL;

        $result = $this->conn->fetchOne($sql, [$user]);
        $this->cache[$user] = $result !== false;

        return $this->cache[$user];
    }
}
