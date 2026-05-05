<?php

/**
 * Integration tests for JWTRepository against a real database.
 *
 * Specifically guards the "jti_exp > FROM_UNIXTIME(?)" filter — without the
 * FROM_UNIXTIME wrapper, MySQL coerces the TIMESTAMP column to its numeric
 * YYYYMMDDhhmmss form, the comparison is effectively always-true, and
 * expired rows leak through.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth\OpenIDConnect\Repositories;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class JWTRepositoryTest extends TestCase
{
    private JWTRepository $repository;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->repository = new JWTRepository();
        $this->cleanRows();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanRows();
        }
    }

    private function cleanRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM jwt_grant_history WHERE client_id LIKE 'jwt-repo-test-%'",
        );
    }

    public function testFutureExpiryRowMatchesFilteredQuery(): void
    {
        $jti = 'jwt-repo-test-future-' . bin2hex(random_bytes(4));
        $futureExp = time() + 3600;
        $this->repository->saveJwtHistory($jti, 'jwt-repo-test-future', $futureExp);

        $rows = $this->repository->getJwtGrantHistoryForJTI($jti, time());

        self::assertCount(1, $rows, 'Future-exp row must be returned by the filtered query');
    }

    public function testPastExpiryRowIsExcludedByFilteredQuery(): void
    {
        $jti = 'jwt-repo-test-past-' . bin2hex(random_bytes(4));
        $pastExp = time() - 3600;
        $this->repository->saveJwtHistory($jti, 'jwt-repo-test-past', $pastExp);

        // Sanity: unfiltered query still finds the row (proves the insert worked).
        $unfiltered = $this->repository->getJwtGrantHistoryForJTI($jti);
        self::assertCount(1, $unfiltered);

        // Filtered query with current time as the threshold should exclude it.
        $filtered = $this->repository->getJwtGrantHistoryForJTI($jti, time());
        self::assertSame([], $filtered, 'Past-exp row must NOT match jti_exp > FROM_UNIXTIME(now)');
    }

    /**
     * SSRF/replay regression for Aisle round-2 #2 (CWE-287). Pins the SQL
     * boundary of `jti_exp > FROM_UNIXTIME(?)`: when the lookup threshold
     * equals the stored value (which is what happens if a caller passes the
     * token's *own* exp instead of the current clock), the strict `>`
     * comparison is false and the row is missed — the exact bug
     * UniqueID::assert() used to reproduce. Documenting the boundary here
     * keeps the SQL operator's semantics observable, so a future flip to
     * `>=` is a deliberate, tested decision rather than a silent change.
     */
    public function testStrictFilterMissesRowWhenThresholdEqualsStoredExpiry(): void
    {
        $jti = 'jwt-repo-test-boundary-' . bin2hex(random_bytes(4));
        $exp = time() + 3600;
        $this->repository->saveJwtHistory($jti, 'jwt-repo-test-boundary', $exp);

        // Querying with $exp as the threshold reproduces the bug: stored
        // jti_exp == $exp, strict `>` is false, row is missed.
        self::assertSame(
            [],
            $this->repository->getJwtGrantHistoryForJTI($jti, $exp),
            'Strict `>` must miss the row when the threshold equals the stored exp '
            . '— this is exactly why UniqueID must pass current clock time, not the token exp.',
        );

        // Querying with current time (which is < $exp) finds the row — the
        // shape UniqueID now uses.
        self::assertCount(
            1,
            $this->repository->getJwtGrantHistoryForJTI($jti, time()),
            'Lookup with current time must find the still-valid row',
        );
    }

    public function testPurgeExpiredDropsPastRowsAndKeepsFutureRows(): void
    {
        $futureJti = 'jwt-repo-test-purge-keep-' . bin2hex(random_bytes(4));
        $pastJti = 'jwt-repo-test-purge-drop-' . bin2hex(random_bytes(4));
        $this->repository->saveJwtHistory($futureJti, 'jwt-repo-test-purge', time() + 3600);
        $this->repository->saveJwtHistory($pastJti, 'jwt-repo-test-purge', time() - 3600);

        $this->repository->purgeExpired();

        self::assertCount(1, $this->repository->getJwtGrantHistoryForJTI($futureJti));
        self::assertSame([], $this->repository->getJwtGrantHistoryForJTI($pastJti));
    }
}
