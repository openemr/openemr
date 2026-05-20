<?php

/**
 * Integration tests for IpLoginRateLimiter against a real database.
 *
 * Requires Docker MySQL to be running with the ip_tracking table.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth;

use OpenEMR\Common\Auth\IpLoginRateLimiter;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class IpLoginRateLimiterTest extends TestCase
{
    private const TEST_IP = '10.99.99.1 (test-integration)';
    private const TEST_IP_2 = '10.99.99.2 (test-integration)';

    private IpLoginRateLimiter $rateLimiter;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->rateLimiter = new IpLoginRateLimiter();
        $this->cleanTestRecords();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTestRecords();
        }
    }

    private function cleanTestRecords(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `ip_tracking` WHERE `ip_string` LIKE '10.99.99.%'",
        );
    }

    public function testEnsureTrackedCreatesRecord(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_string` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        self::assertCount(1, $rows);
        self::assertSame(self::TEST_IP, $rows[0]['ip_string']);
    }

    public function testEnsureTrackedIsIdempotent(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_string` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        self::assertCount(1, $rows);
    }

    /**
     * Aisle round-4 #4 (CWE-362) regression. The fix swaps a racy
     * SELECT-then-INSERT for an `INSERT ... ON DUPLICATE KEY UPDATE`
     * upsert. The clause MUST stay a no-op — if a future refactor
     * turns it into a `VALUES(...)`-style overwrite, every login
     * attempt for an already-tracked IP would silently reset the
     * fail counter, last-fail timestamp, and block flags, neutering
     * the rate limiter without any visible failure. This test pins
     * the no-op contract: pre-populate a row with non-default state,
     * call `ensureTracked()`, assert the state survives.
     */
    public function testEnsureTrackedPreservesExistingCounters(): void
    {
        // Seed a row with a populated counter, a recent fail timestamp,
        // and the auto-block-emailed flag set — exactly the state a
        // real attacker-blocked IP would carry.
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `ip_tracking`'
            . ' (`ip_string`, `total_ip_login_fail_counter`, `ip_login_fail_counter`,'
            . ' `ip_last_login_fail`, `ip_force_block`, `ip_auto_block_emailed`)'
            . ' VALUES (?, 7, 3, NOW(), 1, 1)',
            [self::TEST_IP],
        );

        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `total_ip_login_fail_counter`, `ip_login_fail_counter`,'
            . ' `ip_force_block`, `ip_auto_block_emailed`,'
            . ' `ip_last_login_fail` IS NOT NULL AS `has_last_fail`'
            . ' FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        self::assertCount(1, $rows, 'still exactly one row for this IP');
        // MySQL returns numeric columns as decimal strings; assertEquals
        // does loose comparison (`7 == '7'`) which is the right shape for
        // this regression test, and avoids casting `mixed` to `int`.
        $row = $rows[0];
        self::assertEquals(7, $row['total_ip_login_fail_counter'], 'total counter preserved');
        self::assertEquals(3, $row['ip_login_fail_counter'], 'fail counter preserved');
        self::assertEquals(1, $row['ip_force_block'], 'force-block flag preserved');
        self::assertEquals(1, $row['ip_auto_block_emailed'], 'email-sent flag preserved');
        self::assertEquals(1, $row['has_last_fail'], 'last-fail timestamp preserved (not nulled)');
    }

    public function testEnsureTrackedHandlesEmptyString(): void
    {
        $this->rateLimiter->ensureTracked('');

        $rows = QueryUtils::fetchRecords(
            "SELECT `ip_string` FROM `ip_tracking` WHERE `ip_string` = 'blank'",
        );

        // Should create 'blank' record (or one may already exist)
        self::assertNotEmpty($rows);

        // Clean up
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `ip_tracking` WHERE `ip_string` = 'blank'",
        );
    }

    public function testCheckBlockedReturnsAllowedForNewIp(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $status = $this->rateLimiter->checkBlocked(self::TEST_IP);

        self::assertTrue($status->allowed);
    }

    public function testCheckBlockedReturnsAllowedWhenRateLimitDisabled(): void
    {
        // ip_max_failed_logins default is typically > 0, but if set to 0 it's disabled
        // This test verifies behavior for a new IP with zero failures
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $status = $this->rateLimiter->checkBlocked(self::TEST_IP);

        self::assertTrue($status->allowed);
        self::assertFalse($status->forceBlocked);
    }

    public function testCheckBlockedDetectsForceBlock(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        // Manually force-block the IP
        QueryUtils::sqlStatementThrowException(
            'UPDATE `ip_tracking` SET `ip_force_block` = 1 WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        $status = $this->rateLimiter->checkBlocked(self::TEST_IP);

        self::assertFalse($status->allowed);
        self::assertTrue($status->forceBlocked);
    }

    public function testCheckBlockedDetectsForceBlockWithSkipTiming(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        QueryUtils::sqlStatementThrowException(
            'UPDATE `ip_tracking` SET `ip_force_block` = 1, `ip_no_prevent_timing_attack` = 1 WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        $status = $this->rateLimiter->checkBlocked(self::TEST_IP);

        self::assertFalse($status->allowed);
        self::assertTrue($status->forceBlocked);
        self::assertTrue($status->skipTimingAttack);
    }

    public function testRecordFailedAttemptIncrementsCounter(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);
        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_login_fail_counter`, `total_ip_login_fail_counter` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        /** @var array<string, int|string|null> $row */
        $row = $rows[0];
        self::assertSame('2', (string) $row['ip_login_fail_counter']);
        self::assertSame('2', (string) $row['total_ip_login_fail_counter']);
    }

    public function testRecordSuccessfulLoginResetsCounter(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);
        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);
        $this->rateLimiter->recordSuccessfulLogin(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_login_fail_counter`, `total_ip_login_fail_counter`, `ip_last_login_fail` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        /** @var array<string, int|string|null> $row */
        $row = $rows[0];
        self::assertSame('0', (string) $row['ip_login_fail_counter']);
        // Total counter is NOT reset (lifetime counter)
        self::assertSame('2', (string) $row['total_ip_login_fail_counter']);
        self::assertNull($row['ip_last_login_fail']);
    }

    public function testCollectFailedLoginsReturnsAllRecords(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);
        $this->rateLimiter->ensureTracked(self::TEST_IP_2);

        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);
        $this->rateLimiter->recordFailedAttempt(self::TEST_IP_2);

        $rows = IpLoginRateLimiter::collectFailedLogins(
            showOnlyWithCount: true,
            showOnlyManuallyBlocked: false,
            showOnlyAutoBlocked: false,
        );

        $testIps = array_filter(
            $rows,
            static fn(array $row): bool => is_string($row['ip_string'] ?? null) && str_starts_with($row['ip_string'], '10.99.99.'),
        );

        self::assertCount(2, $testIps);
    }

    public function testCollectFailedLoginsFiltersManuallyBlocked(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);
        $this->rateLimiter->ensureTracked(self::TEST_IP_2);

        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);
        $this->rateLimiter->recordFailedAttempt(self::TEST_IP_2);

        // Force-block only one IP
        QueryUtils::sqlStatementThrowException(
            'UPDATE `ip_tracking` SET `ip_force_block` = 1 WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        $rows = IpLoginRateLimiter::collectFailedLogins(
            showOnlyWithCount: false,
            showOnlyManuallyBlocked: true,
            showOnlyAutoBlocked: false,
        );

        $testIps = array_filter(
            $rows,
            static fn(array $row): bool => is_string($row['ip_string'] ?? null) && str_starts_with($row['ip_string'], '10.99.99.'),
        );

        self::assertCount(1, $testIps);
        $blocked = array_values($testIps);
        self::assertSame(self::TEST_IP, $blocked[0]['ip_string']);
    }

    public function testResetCounterByIdResetsCounter(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);
        $this->rateLimiter->recordFailedAttempt(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `id` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        /** @var array<string, int|string|null> $idRow */
        $idRow = $rows[0];
        $ipId = (int) $idRow['id'];

        IpLoginRateLimiter::resetCounterById($ipId);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_login_fail_counter` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        /** @var array<string, int|string|null> $counterRow */
        $counterRow = $rows[0];
        self::assertSame('0', (string) $counterRow['ip_login_fail_counter']);
    }

    public function testForceBlockAndUnblock(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `id` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        /** @var array<string, int|string|null> $idRow */
        $idRow = $rows[0];
        $ipId = (int) $idRow['id'];

        IpLoginRateLimiter::forceBlock($ipId);

        $status = $this->rateLimiter->checkBlocked(self::TEST_IP);
        self::assertFalse($status->allowed);
        self::assertTrue($status->forceBlocked);

        IpLoginRateLimiter::unblock($ipId);

        $status = $this->rateLimiter->checkBlocked(self::TEST_IP);
        self::assertTrue($status->allowed);
    }

    public function testTimingAttackPreventionToggle(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `id` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        /** @var array<string, int|string|null> $idRow */
        $idRow = $rows[0];
        $ipId = (int) $idRow['id'];

        IpLoginRateLimiter::disableTimingAttackPrevention($ipId);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_no_prevent_timing_attack` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        /** @var array<string, int|string|null> $timingRow */
        $timingRow = $rows[0];
        self::assertSame('1', (string) $timingRow['ip_no_prevent_timing_attack']);

        IpLoginRateLimiter::enableTimingAttackPrevention($ipId);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_no_prevent_timing_attack` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        /** @var array<string, int|string|null> $timingRow2 */
        $timingRow2 = $rows[0];
        self::assertSame('0', (string) $timingRow2['ip_no_prevent_timing_attack']);
    }
}
