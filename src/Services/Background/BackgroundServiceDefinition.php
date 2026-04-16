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

use OpenEMR\Common\Database\TableTypes;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
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
     * @param BackgroundServicesRow $row
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
            // `running` is derived from the lease: a service is only
            // actually running if the lease exists and has not expired.
            // This means stuck locks from crashed workers report as
            // not-running, matching reality.
            running: self::leaseIsLive($row['lock_expires_at']),
            nextRun: $row['next_run'],
            lockExpiresAt: $row['lock_expires_at'],
        );
    }

    /**
     * @return BackgroundServicesRow
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
        ];
    }

    /**
     * A lease is "live" when it has a future expiration timestamp.
     * Treats malformed timestamps as not-live (fail safe — better to
     * let the next tick attempt to re-acquire than to report a lock
     * we can't interpret as held).
     */
    private static function leaseIsLive(?string $lockExpiresAt): bool
    {
        if ($lockExpiresAt === null || $lockExpiresAt === '') {
            return false;
        }
        $expiry = strtotime($lockExpiresAt);
        return $expiry !== false && $expiry > time();
    }
}
