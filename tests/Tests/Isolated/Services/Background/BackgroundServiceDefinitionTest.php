<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background;

use OpenEMR\Services\Background\BackgroundServiceDefinition;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('background-services')]
class BackgroundServiceDefinitionTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $def = new BackgroundServiceDefinition(
            name: 'test_svc',
            title: 'Test Service',
            function: 'runTestService',
            requireOnce: '/path/to/file.php',
            executeInterval: 5,
            sortOrder: 50,
            active: true,
            running: false,
            nextRun: '2026-03-28 10:00:00',
        );

        $this->assertSame('test_svc', $def->name);
        $this->assertSame('Test Service', $def->title);
        $this->assertSame('runTestService', $def->function);
        $this->assertSame('/path/to/file.php', $def->requireOnce);
        $this->assertSame(5, $def->executeInterval);
        $this->assertSame(50, $def->sortOrder);
        $this->assertTrue($def->active);
        $this->assertFalse($def->running);
        $this->assertSame('2026-03-28 10:00:00', $def->nextRun);
    }

    public function testDefaultValues(): void
    {
        $def = new BackgroundServiceDefinition(
            name: 'minimal',
            title: 'Minimal',
            function: 'doWork',
        );

        $this->assertNull($def->requireOnce);
        $this->assertSame(0, $def->executeInterval);
        $this->assertSame(100, $def->sortOrder);
        $this->assertFalse($def->active);
        $this->assertFalse($def->running);
        $this->assertNull($def->nextRun);
    }

    /**
     * @return array{name: string, title: string, function: string, require_once: ?string, execute_interval: numeric-string, sort_order: numeric-string, active: numeric-string, running: numeric-string, next_run: string, lock_expires_at: ?string, lease_is_live: ?numeric-string}
     */
    private static function row(
        string $name = 'svc',
        bool $active = true,
        ?string $lockExpiresAt = null,
        bool $leaseIsLive = false,
        string $nextRun = '2026-03-28 10:15:00',
        int $executeInterval = 5,
    ): array {
        return [
            'name' => $name,
            'title' => 'Service',
            'function' => 'doWork',
            'require_once' => null,
            'execute_interval' => (string) $executeInterval,
            'sort_order' => '100',
            'active' => $active ? '1' : '0',
            'running' => $leaseIsLive ? '1' : '0',
            'next_run' => $nextRun,
            'lock_expires_at' => $lockExpiresAt,
            'lease_is_live' => $leaseIsLive ? '1' : '0',
        ];
    }

    public function testFromDatabaseRow(): void
    {
        $row = self::row(name: 'phimail', executeInterval: 5);
        $row['title'] = 'phiMail Direct Messaging';
        $row['function'] = 'phimail_check';
        $row['require_once'] = '/library/phimail.php';

        $def = BackgroundServiceDefinition::fromDatabaseRow($row);

        $this->assertSame('phimail', $def->name);
        $this->assertSame('phiMail Direct Messaging', $def->title);
        $this->assertSame('phimail_check', $def->function);
        $this->assertSame('/library/phimail.php', $def->requireOnce);
        $this->assertSame(5, $def->executeInterval);
        $this->assertSame(100, $def->sortOrder);
        $this->assertTrue($def->active);
        $this->assertFalse($def->running);
        $this->assertSame('2026-03-28 10:15:00', $def->nextRun);
        $this->assertNull($def->lockExpiresAt);
    }

    public function testFromDatabaseRowWithNullRequireOnce(): void
    {
        $row = self::row(active: false, nextRun: '1970-01-01 00:00:00', executeInterval: 10);

        $def = BackgroundServiceDefinition::fromDatabaseRow($row);

        $this->assertNull($def->requireOnce);
        $this->assertSame('1970-01-01 00:00:00', $def->nextRun);
        $this->assertFalse($def->active);
    }

    public function testFromDatabaseRowReportsRunningWhenLeaseIsLive(): void
    {
        // The SQL-computed `lease_is_live` is authoritative — not the
        // legacy `running` column, not a PHP-side comparison of
        // `lock_expires_at` against `time()`. A worker crash that leaves
        // `running = 1` but no live lease reports as running=false.
        $future = '2099-01-01 00:00:00';
        $row = self::row(lockExpiresAt: $future, leaseIsLive: true);

        $def = BackgroundServiceDefinition::fromDatabaseRow($row);

        $this->assertTrue($def->running);
        $this->assertSame($future, $def->lockExpiresAt);
    }

    public function testFromDatabaseRowReportsNotRunningWhenLeaseExpired(): void
    {
        // Stale lease left by a crashed worker: lock_expires_at is set,
        // `running` column still says 1, but `lease_is_live` is 0 because
        // the DB evaluated `lock_expires_at > NOW()` as false.
        $past = '1999-01-01 00:00:00';
        $row = self::row(lockExpiresAt: $past, leaseIsLive: false);

        $def = BackgroundServiceDefinition::fromDatabaseRow($row);

        $this->assertFalse($def->running);
        $this->assertSame($past, $def->lockExpiresAt);
    }

    public function testToArray(): void
    {
        $def = new BackgroundServiceDefinition(
            name: 'test_svc',
            title: 'Test',
            function: 'runTest',
            requireOnce: '/path.php',
            executeInterval: 5,
            sortOrder: 50,
            active: true,
            running: false,
            nextRun: '2026-03-28 10:00:00',
        );

        $array = $def->toArray();

        $this->assertSame('test_svc', $array['name']);
        $this->assertSame('Test', $array['title']);
        $this->assertSame('runTest', $array['function']);
        $this->assertSame('/path.php', $array['require_once']);
        $this->assertSame('5', $array['execute_interval']);
        $this->assertSame('50', $array['sort_order']);
        $this->assertSame('1', $array['active']);
        $this->assertSame('0', $array['running']);
        $this->assertSame('0', $array['lease_is_live']);
        $this->assertNull($array['lock_expires_at']);
    }

    public function testRoundTrip(): void
    {
        $original = new BackgroundServiceDefinition(
            name: 'roundtrip',
            title: 'Roundtrip Test',
            function: 'doRoundtrip',
            requireOnce: '/lib/roundtrip.php',
            executeInterval: 15,
            sortOrder: 75,
            active: true,
            running: true,
            nextRun: '2026-03-28 12:00:00',
            lockExpiresAt: '2099-01-01 00:00:00',
        );

        $restored = BackgroundServiceDefinition::fromDatabaseRow($original->toArray());

        $this->assertSame($original->name, $restored->name);
        $this->assertSame($original->title, $restored->title);
        $this->assertSame($original->function, $restored->function);
        $this->assertSame($original->requireOnce, $restored->requireOnce);
        $this->assertSame($original->executeInterval, $restored->executeInterval);
        $this->assertSame($original->sortOrder, $restored->sortOrder);
        $this->assertSame($original->active, $restored->active);
        $this->assertSame($original->running, $restored->running);
        $this->assertSame($original->nextRun, $restored->nextRun);
        $this->assertSame($original->lockExpiresAt, $restored->lockExpiresAt);
    }
}
