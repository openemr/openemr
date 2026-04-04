<?php

/**
 * Integration tests verifying AuthUtils delegates IP rate limiting to IpLoginRateLimiter.
 *
 * Uses the existing admin/pass test user and the real ip_tracking table.
 * Runs in 'api' mode to avoid session setup requirements.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth;

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthUtils::class)]
final class AuthUtilsIpDelegationTest extends TestCase
{
    /**
     * A synthetic IP that we inject via $_SERVER so we can track it
     * without interfering with real ip_tracking data.
     */
    private const TEST_IP = '10.88.88.1';

    private ?string $originalRemoteAddr = null;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        // Inject a deterministic IP so AuthUtils -> collectIpAddresses() returns it
        $this->originalRemoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = self::TEST_IP;

        // Ensure clean slate for our test IP
        $this->cleanTestIp();
    }

    protected function tearDown(): void
    {
        // Restore original REMOTE_ADDR
        if ($this->originalRemoteAddr !== null) {
            $_SERVER['REMOTE_ADDR'] = $this->originalRemoteAddr;
        } else {
            unset($_SERVER['REMOTE_ADDR']);
        }

        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTestIp();
        }
    }

    private function cleanTestIp(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `ip_tracking` WHERE `ip_string` = ?",
            [self::TEST_IP],
        );
    }

    private function getIpTrackingRow(): ?array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_login_fail_counter`, `total_ip_login_fail_counter`, `ip_last_login_fail`'
            . ' FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        return $rows[0] ?? null;
    }

    public function testFailedLoginIncrementsIpCounter(): void
    {
        $wrongPassword = 'definitely-wrong-password';
        $auth = new AuthUtils('api');
        $auth->confirmPassword('admin', $wrongPassword);

        $row = $this->getIpTrackingRow();

        self::assertNotNull($row, 'ip_tracking record should exist after failed login');
        self::assertSame('1', (string) $row['ip_login_fail_counter']);
        self::assertSame('1', (string) $row['total_ip_login_fail_counter']);
        self::assertNotNull($row['ip_last_login_fail']);
    }

    public function testMultipleFailedLoginsAccumulateIpCounter(): void
    {
        $wrongPassword = 'wrong-1';
        $auth = new AuthUtils('api');
        $auth->confirmPassword('admin', $wrongPassword);

        $wrongPassword2 = 'wrong-2';
        $auth2 = new AuthUtils('api');
        $auth2->confirmPassword('admin', $wrongPassword2);

        $row = $this->getIpTrackingRow();

        self::assertNotNull($row);
        self::assertSame('2', (string) $row['ip_login_fail_counter']);
        self::assertSame('2', (string) $row['total_ip_login_fail_counter']);
    }

    public function testSuccessfulLoginResetsIpCounter(): void
    {
        // First, generate a failed attempt
        $wrongPassword = 'wrong-password';
        $auth = new AuthUtils('api');
        $auth->confirmPassword('admin', $wrongPassword);

        $row = $this->getIpTrackingRow();
        self::assertSame('1', (string) ($row['ip_login_fail_counter'] ?? '0'));

        // Now succeed with correct password
        $correctPassword = 'pass';
        $auth2 = new AuthUtils('api');
        $result = $auth2->confirmPassword('admin', $correctPassword);

        self::assertTrue($result, 'confirmPassword should return true for correct credentials');

        $row = $this->getIpTrackingRow();
        self::assertNotNull($row);
        self::assertSame('0', (string) $row['ip_login_fail_counter'], 'Current counter should reset after successful login');
        // Total counter is never reset — it's a lifetime counter
        self::assertSame('1', (string) $row['total_ip_login_fail_counter']);
        self::assertNull($row['ip_last_login_fail']);
    }

    public function testFailedLoginWithNonexistentUserStillTracksIp(): void
    {
        $password = 'irrelevant';
        $auth = new AuthUtils('api');
        $auth->confirmPassword('nonexistent_user_xyz_test', $password);

        $row = $this->getIpTrackingRow();

        self::assertNotNull($row, 'ip_tracking should record attempts even for nonexistent users');
        self::assertSame('1', (string) $row['ip_login_fail_counter']);
    }
}
