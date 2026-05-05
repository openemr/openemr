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
use PHPUnit\Framework\TestCase;

final class AuthUtilsIpDelegationTest extends TestCase
{
    /**
     * A synthetic IP that we inject via $_SERVER so we can track it
     * without interfering with real ip_tracking data.
     */
    private const TEST_IP = '10.88.88.1';

    private ?string $originalRemoteAddr = null;
    private ?string $originalForwardedFor = null;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        // Inject a deterministic IP so AuthUtils -> collectIpAddresses() returns it
        $this->originalRemoteAddr = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : null;
        $_SERVER['REMOTE_ADDR'] = self::TEST_IP;

        // Some tests vary HTTP_X_FORWARDED_FOR; capture the original so we can
        // restore it in tearDown.
        $this->originalForwardedFor = is_string($_SERVER['HTTP_X_FORWARDED_FOR'] ?? null)
            ? $_SERVER['HTTP_X_FORWARDED_FOR']
            : null;
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

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

        // Restore original HTTP_X_FORWARDED_FOR
        if ($this->originalForwardedFor !== null) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $this->originalForwardedFor;
        } else {
            unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTestIp();
        }
    }

    /**
     * Match exact REMOTE_ADDR and any "REMOTE_ADDR (xxx)" variant — the latter
     * existed in the buggy codebase that used $ip['ip_string'] as the rate-
     * limiter key and would create one row per X-Forwarded-For value seen.
     * The LIKE pattern ensures cleanTestIp() removes orphans from a previously
     * buggy run too, so re-running the suite never inherits stale state.
     */
    private function cleanTestIp(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `ip_tracking` WHERE `ip_string` = ? OR `ip_string` LIKE ?",
            [self::TEST_IP, self::TEST_IP . ' (%'],
        );
    }

    /** @return array<string, int|string|null>|null */
    private function getIpTrackingRow(): ?array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `ip_login_fail_counter`, `total_ip_login_fail_counter`, `ip_last_login_fail`'
            . ' FROM `ip_tracking` WHERE `ip_string` = ?',
            [self::TEST_IP],
        );

        if ($rows === []) {
            return null;
        }

        /** @var array<string, int|string|null> $row */
        $row = $rows[0];
        return $row;
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

    /**
     * Aisle round-2 finding #3 (CWE-807) regression. The rate limiter must
     * key on REMOTE_ADDR alone, not on the concatenated `ip_string` (which
     * embeds HTTP_X_FORWARDED_FOR). An attacker who can vary the
     * `X-Forwarded-For` header across requests must still hit the same
     * rate-limit bucket — otherwise throttling is trivially bypassed by
     * sending a different XFF value per attempt.
     */
    public function testFailedAttemptsAggregateOnRemoteAddrIgnoringXForwardedFor(): void
    {
        $xffValues = ['1.1.1.1', '2.2.2.2', '3.3.3.3'];

        foreach ($xffValues as $xff) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $xff;
            // confirmPassword takes $password by reference (it zeroes the buffer
            // post-check), so the argument must be a real variable, not a literal.
            $password = 'wrong-' . $xff;
            (new AuthUtils('api'))->confirmPassword('admin', $password);
        }

        $row = $this->getIpTrackingRow();
        self::assertNotNull($row, 'TEST_IP row must exist after the failed attempts');
        self::assertSame(
            (string) count($xffValues),
            (string) $row['ip_login_fail_counter'],
            'All attempts must aggregate on REMOTE_ADDR despite varying XFF — '
            . 'otherwise XFF rotation bypasses the rate limit (CWE-807).',
        );

        // Belt-and-braces: assert no XFF-tagged buckets were created. If the
        // limiter regressed back to keying on ip_string, we'd see rows like
        // `10.88.88.1 (1.1.1.1)` here.
        $orphanRows = QueryUtils::fetchRecords(
            'SELECT `ip_string` FROM `ip_tracking` WHERE `ip_string` LIKE ?',
            [self::TEST_IP . ' (%'],
        );
        self::assertSame([], $orphanRows, 'No XFF-tagged ip_tracking rows must be created');
    }
}
