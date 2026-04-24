<?php

/**
 * Immutable value object representing a registered background service.
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

/**
 * The `lease_is_live` field is computed at query time (e.g.
 * `lock_expires_at > NOW() AS lease_is_live`) and is intentionally NOT part
 * of the generated `BackgroundServicesRow` schema because it is not a table
 * column. All callers of `fromDatabaseRow()` MUST include it so liveness is
 * always derived from the DB clock — never from PHP's `time()`, which can
 * drift from the MySQL session timezone.
 *
 * @phpstan-type BackgroundServicesQueryRow array{
 *   name: string,
 *   title: string,
 *   active: numeric-string,
 *   running: numeric-string,
 *   next_run: string,
 *   execute_interval: numeric-string,
 *   function: string,
 *   require_once: ?string,
 *   sort_order: numeric-string,
 *   lock_expires_at: ?string,
 *   lease_is_live: ?numeric-string
 * }
 */
final readonly class BackgroundServiceDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $title,
        public readonly string $function,
        public readonly ?string $requireOnce = null,
        public readonly int $executeInterval = 0,
        public readonly int $sortOrder = 100,
        public readonly bool $active = false,
        public readonly bool $running = false,
        public readonly ?string $nextRun = null,
        public readonly ?string $lockExpiresAt = null,
    ) {
    }

    /**
     * @param BackgroundServicesQueryRow $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            name: $row['name'],
            title: $row['title'],
            function: $row['function'],
            requireOnce: $row['require_once'],
            executeInterval: (int) $row['execute_interval'],
            sortOrder: (int) $row['sort_order'],
            active: (int) $row['active'] !== 0,
            // `running` reflects the live lease — not the legacy `running`
            // column, which can be stale (e.g. set to 1 by a worker that
            // crashed before release). `lease_is_live` is computed in SQL
            // using the DB's own clock, so reporting matches what
            // acquireLock() will enforce.
            running: (int) ($row['lease_is_live'] ?? 0) === 1,
            nextRun: $row['next_run'],
            lockExpiresAt: $row['lock_expires_at'],
        );
    }

    /**
     * @return BackgroundServicesQueryRow
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'active' => $this->active ? '1' : '0',
            'running' => $this->running ? '1' : '0',
            'next_run' => $this->nextRun ?? '1970-01-01 00:00:00',
            'execute_interval' => (string) $this->executeInterval,
            'function' => $this->function,
            'require_once' => $this->requireOnce,
            'sort_order' => (string) $this->sortOrder,
            'lock_expires_at' => $this->lockExpiresAt,
            // Emit the SQL-computed flag so a round-tripped row reports the
            // same liveness after fromDatabaseRow() re-constructs it.
            'lease_is_live' => $this->running ? '1' : '0',
        ];
    }
}
