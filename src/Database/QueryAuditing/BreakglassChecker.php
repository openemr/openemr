<?php

/**
 * Breakglass user checker implementation.
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
use Doctrine\DBAL\ParameterType;

/**
 * Checks if a user belongs to the breakglass (emergency access) group.
 *
 * Results are cached for the lifetime of the instance to avoid repeated
 * database queries during a single request.
 *
 * Extracted from EventAuditLogger::isBreakglassUser().
 */
final class BreakglassChecker implements BreakglassCheckerInterface
{
    /** @var array<string, bool> */
    private array $cache = [];

    public function isBreakglassUser(Connection $connection, string $username): bool
    {
        if ($username === '') {
            return false;
        }

        if (isset($this->cache[$username])) {
            return $this->cache[$username];
        }

        // Query the GACL tables directly for performance
        // Uses BINARY for case-sensitive username matching
        $sql = <<<'SQL'
            SELECT `gacl_aro`.`value`
            FROM `gacl_aro`
            INNER JOIN `gacl_groups_aro_map` ON `gacl_aro`.`id` = `gacl_groups_aro_map`.`aro_id`
            INNER JOIN `gacl_aro_groups` ON `gacl_groups_aro_map`.`group_id` = `gacl_aro_groups`.`id`
            WHERE `gacl_aro_groups`.`value` = 'breakglass'
            AND BINARY `gacl_aro`.`value` = ?
            SQL;

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $username, ParameterType::STRING);
        $result = $stmt->execute();
        $row = $result->fetchOne();

        $this->cache[$username] = $row !== false;

        return $this->cache[$username];
    }
}
