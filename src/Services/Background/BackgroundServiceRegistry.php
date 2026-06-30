<?php

/**
 * Registry for managing background service registrations.
 *
 * Replaces ad-hoc SQL INSERT/UPDATE/DELETE patterns used by modules
 * with a consistent API backed by the background_services table.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Background;

use OpenEMR\Common\Database\QueryUtils;

/**
 * @phpstan-import-type BackgroundServicesQueryRow from BackgroundServiceDefinition
 */
class BackgroundServiceRegistry
{
    /**
     * Register or update a background service (idempotent upsert).
     *
     * ## Policy for the `active` flag: first install wins
     *
     * The `active` value from `$definition` is respected on initial INSERT
     * and is never overwritten on subsequent upserts:
     *
     * - **Fresh row (no existing service with this name):** the
     *   `$definition->active` value is written to the database. A module
     *   can ship its default enabled state this way.
     * - **Existing row:** title, function, require_once, execute_interval,
     *   and sort_order are all updated, but `active` is left untouched.
     *   This preserves any explicit enable/disable decision an admin has
     *   made through the UI or via a prior migration.
     *
     * Consequence: two installs of the same module version can end up with
     * different `active` values depending on whether the service existed
     * before. This is intentional — runtime state belongs to the operator,
     * not to the module package, and a module upgrade must not silently
     * re-enable a service an admin has turned off.
     *
     * Modules that need to flip a service's active state on upgrade (for
     * example, a security-driven kill switch) should call `setActive()`
     * explicitly from a migration step, so the decision is reviewable at
     * the call site rather than buried in package defaults.
     *
     * @see BackgroundServiceRegistry::setActive()
     */
    public function register(BackgroundServiceDefinition $definition): void
    {
        $sql = <<<'SQL'
            INSERT INTO `background_services`
                (`name`, `title`, `function`, `require_once`, `execute_interval`, `sort_order`, `active`)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `function` = VALUES(`function`),
                `require_once` = VALUES(`require_once`),
                `execute_interval` = VALUES(`execute_interval`),
                `sort_order` = VALUES(`sort_order`)
            SQL;

        QueryUtils::sqlStatementThrowException($sql, [
            $definition->name,
            $definition->title,
            $definition->function,
            $definition->requireOnce,
            $definition->executeInterval,
            $definition->sortOrder,
            $definition->active ? 1 : 0,
        ], true);
    }

    /**
     * Remove a background service by name.
     */
    public function unregister(string $name): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `background_services` WHERE `name` = ?',
            [$name],
            true,
        );
    }

    /**
     * Projection that derives lease liveness in SQL so the "running"
     * signal uses the same clock (and time_zone) as acquireLock(), rather
     * than PHP's clock via `time()` + `strtotime()`. Public so callers
     * outside the registry (e.g. the CLI command) can reuse the same
     * projection without duplicating the expression.
     */
    public const SELECT_WITH_LEASE_LIVE = <<<'SQL'
        SELECT `background_services`.*,
               (`lock_expires_at` IS NOT NULL AND `lock_expires_at` > NOW()) AS `lease_is_live`
          FROM `background_services`
        SQL;

    /**
     * Get a single service by name, or null if not found.
     */
    public function get(string $name): ?BackgroundServiceDefinition
    {
        /** @var list<BackgroundServicesQueryRow> $rows */
        $rows = QueryUtils::fetchRecordsNoLog(
            self::SELECT_WITH_LEASE_LIVE . ' WHERE `name` = ?',
            [$name],
        );

        if ($rows === []) {
            return null;
        }

        return BackgroundServiceDefinition::fromDatabaseRow($rows[0]);
    }

    /**
     * List all registered services, optionally filtering by active status.
     *
     * @return list<BackgroundServiceDefinition>
     */
    public function list(?bool $activeFilter = null): array
    {
        $sql = self::SELECT_WITH_LEASE_LIVE;
        $binds = [];

        if ($activeFilter !== null) {
            $sql .= ' WHERE `active` = ?';
            $binds[] = $activeFilter ? 1 : 0;
        }

        $sql .= ' ORDER BY `sort_order`';

        /** @var list<BackgroundServicesQueryRow> $rows */
        $rows = QueryUtils::fetchRecordsNoLog($sql, $binds);

        return array_map(
            BackgroundServiceDefinition::fromDatabaseRow(...),
            $rows,
        );
    }

    /**
     * Enable or disable a service.
     */
    public function setActive(string $name, bool $active): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `background_services` SET `active` = ? WHERE `name` = ?',
            [$active ? 1 : 0, $name],
            true,
        );
    }

    /**
     * Check if a service with the given name exists.
     */
    public function exists(string $name): bool
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT 1 FROM `background_services` WHERE `name` = ? LIMIT 1',
            [$name],
        );

        return $rows !== [];
    }
}
