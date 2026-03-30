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
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://www.opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Background;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\TableTypes;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class BackgroundServiceRegistry
{
    /**
     * Register or update a background service (idempotent upsert).
     *
     * The ON DUPLICATE KEY UPDATE intentionally does NOT update `active`.
     * This preserves the admin's enable/disable decision. Use setActive()
     * to change a service's enabled state.
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
     * Get a single service by name, or null if not found.
     */
    public function get(string $name): ?BackgroundServiceDefinition
    {
        /** @var list<BackgroundServicesRow> $rows */
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT * FROM `background_services` WHERE `name` = ?',
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
        $sql = 'SELECT * FROM `background_services`';
        $binds = [];

        if ($activeFilter !== null) {
            $sql .= ' WHERE `active` = ?';
            $binds[] = $activeFilter ? 1 : 0;
        }

        $sql .= ' ORDER BY `sort_order`';

        /** @var list<BackgroundServicesRow> $rows */
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
