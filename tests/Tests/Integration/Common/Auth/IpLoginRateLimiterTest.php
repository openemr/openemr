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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IpLoginRateLimiter::class)]
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

        self::assertSame('2', (string) $rows[0]['ip_login_fail_counter']);
        self::assertSame('2', (string) $rows[0]['total_ip_login_fail_counter']);
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

        self::assertSame('0', (string) $rows[0]['ip_login_fail_counter']);
        // Total counter is NOT reset (lifetime counter)
        self::assertSame('2', (string) $rows[0]['total_ip_login_fail_counter']);
        self::assertNull($rows[0]['ip_last_login_fail']);
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
            static fn(array $row): bool => str_starts_with((string) ($row['ip_string'] ?? ''), '10.99.99.'),
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
            static fn(array $row): bool => str_starts_with((string) ($row['ip_string'] ?? ''), '10.99.99.'),
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
        $ipId = (int) $rows[0]['id'];

        IpLoginRateLimiter::resetCounterById($ipId);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_login_fail_counter` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        self::assertSame('0', (string) $rows[0]['ip_login_fail_counter']);
    }

    public function testForceBlockAndUnblock(): void
    {
        $this->rateLimiter->ensureTracked(self::TEST_IP);

        $rows = QueryUtils::fetchRecords(
            'SELECT `id` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        $ipId = (int) $rows[0]['id'];

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
        $ipId = (int) $rows[0]['id'];

        IpLoginRateLimiter::disableTimingAttackPrevention($ipId);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_no_prevent_timing_attack` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        self::assertSame('1', (string) $rows[0]['ip_no_prevent_timing_attack']);

        IpLoginRateLimiter::enableTimingAttackPrevention($ipId);

        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_no_prevent_timing_attack` FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );
        self::assertSame('0', (string) $rows[0]['ip_no_prevent_timing_attack']);
    }
}
