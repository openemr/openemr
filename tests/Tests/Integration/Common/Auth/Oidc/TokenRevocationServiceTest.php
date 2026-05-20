<?php

/**
 * Integration tests for TokenRevocationService against a real database.
 *
 * Requires Docker MySQL to be running with the oidc_token_revocation table.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth\Oidc;

use OpenEMR\Common\Auth\Oidc\Token\TokenRevocationService;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class TokenRevocationServiceTest extends TestCase
{
    private TokenRevocationService $service;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->service = new TokenRevocationService();
        $this->cleanTable();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTable();
        }
    }

    private function cleanTable(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . TokenRevocationService::TABLE_NAME . '`',
        );
    }

    public function testRevokeAndIsRevoked(): void
    {
        $expiry = new \DateTimeImmutable('+1 hour');

        $this->service->revoke('jti-123', $expiry);

        self::assertTrue($this->service->isRevoked('jti-123'));
    }

    public function testIsRevokedReturnsFalseForUnknownJti(): void
    {
        self::assertFalse($this->service->isRevoked('nonexistent-jti'));
    }

    public function testRevokeIsIdempotent(): void
    {
        $expiry = new \DateTimeImmutable('+1 hour');

        $this->service->revoke('jti-dup', $expiry);
        $this->service->revoke('jti-dup', $expiry); // Should not throw

        self::assertTrue($this->service->isRevoked('jti-dup'));
    }

    /**
     * Aisle round-5 #10 (CWE-362) regression. The pre-fix SELECT-
     * then-INSERT pattern left a TOCTOU window where two parallel
     * revoke() calls for the same jti could both pass the SELECT
     * (no row yet), then one INSERT would fail on `uq_jti_hash` and
     * sqlStatementThrowException() would propagate as a 500.
     *
     * Single-threaded tests can't reproduce true interleaving, but
     * we can simulate the *outcome* of the race directly: pre-insert
     * a row (as if the parallel call already won), then call
     * revoke() and assert it doesn't throw. Pre-fix the SELECT
     * short-circuit hid this — but the actual race scenario is when
     * the SELECT passes (no row) and the INSERT then collides; this
     * test exercises the equivalent end-state.
     */
    public function testRevokeDoesNotThrowWhenRowAlreadyExistsFromRaceWinner(): void
    {
        $expiry = new \DateTimeImmutable('+1 hour');
        $jti = 'jti-race-victim';

        // Simulate the race winner having already inserted the row.
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `' . TokenRevocationService::TABLE_NAME . '` (`jti`, `token_expiry`) VALUES (?, ?)',
            [$jti, $expiry->format('Y-m-d H:i:s')],
        );

        // The race-victim call should not throw on the duplicate-
        // key violation; INSERT IGNORE absorbs it at the DB layer.
        $this->service->revoke($jti, $expiry);

        // End-state: still exactly one row (no duplicate).
        $rows = QueryUtils::fetchRecords(
            'SELECT id FROM `' . TokenRevocationService::TABLE_NAME . '` WHERE `jti` = ?',
            [$jti],
        );
        self::assertCount(1, $rows);
    }

    public function testRevokeIgnoresEmptyJti(): void
    {
        $expiry = new \DateTimeImmutable('+1 hour');

        $this->service->revoke('', $expiry);

        // Empty jti should not be stored
        self::assertFalse($this->service->isRevoked(''));
    }

    public function testIsRevokedReturnsFalseForEmptyJti(): void
    {
        self::assertFalse($this->service->isRevoked(''));
    }

    public function testPurgeExpiredRemovesOldEntries(): void
    {
        // Insert an already-expired entry
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `' . TokenRevocationService::TABLE_NAME . '` (`jti`, `token_expiry`) VALUES (?, ?)',
            ['expired-jti', (new \DateTimeImmutable('-1 hour'))->format('Y-m-d H:i:s')],
        );

        // Insert a still-valid entry
        $this->service->revoke('valid-jti', new \DateTimeImmutable('+1 hour'));

        $this->service->purgeExpired();

        self::assertFalse($this->service->isRevoked('expired-jti'));
        self::assertTrue($this->service->isRevoked('valid-jti'));
    }
}
