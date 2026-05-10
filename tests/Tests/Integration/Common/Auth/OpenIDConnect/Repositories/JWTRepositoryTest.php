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

    /**
     * Aisle round-2 finding #5 (CWE-294) regression. Legacy rows could have
     * `jti_exp = NULL` (a token without `exp` was once accepted, stored
     * NULL, and the replay-lookup filter `jti_exp > FROM_UNIXTIME(?)`
     * silently excluded them — `NULL > x` is `NULL`, not true). The
     * lookup is now `(jti_exp IS NULL OR jti_exp > FROM_UNIXTIME(?))`,
     * which fail-closes: any NULL row counts as still-in-window and
     * therefore as a replay match.
     */
    public function testFilteredQueryMatchesNullExpiryRow(): void
    {
        $jti = 'jwt-repo-test-null-exp-' . bin2hex(random_bytes(4));
        // Insert directly with NULL jti_exp — saveJwtHistory($jti, $client, null)
        // also produces this shape (FROM_UNIXTIME(NULL) returns NULL).
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO jwt_grant_history (jti, client_id, jti_exp, creation_date) VALUES (?, ?, NULL, NOW())',
            [$jti, 'jwt-repo-test-null-exp'],
        );

        $rows = $this->repository->getJwtGrantHistoryForJTI($jti, time());

        self::assertCount(
            1,
            $rows,
            'NULL jti_exp must be matched by the replay-lookup filter — '
            . 'fail-closed semantics ensure the same JTI can never be replayed.',
        );
    }

    /**
     * Aisle finding (CWE-362) regression. Replay protection used
     * to be a check-then-insert sequence on a non-UNIQUE column, so two
     * concurrent presentations of the same JWT could both pass the SELECT
     * and both INSERT. The fix adds `UNIQUE KEY uq_jti (jti)` and switches
     * the INSERT to `INSERT IGNORE`; the second insert is silently skipped
     * (affected_rows = 0) and saveJwtHistory returns false — the canonical
     * "race victim" signal callers translate to a replay rejection.
     */
    public function testSaveJwtHistoryReturnsFalseOnDuplicateJti(): void
    {
        $jti = 'jwt-repo-test-uq-' . bin2hex(random_bytes(4));
        $exp = time() + 3600;

        self::assertTrue(
            $this->repository->saveJwtHistory($jti, 'jwt-repo-test-uq', $exp),
            'First insert must succeed and report it inserted the row',
        );

        self::assertFalse(
            $this->repository->saveJwtHistory($jti, 'jwt-repo-test-uq', $exp),
            'Second insert with same jti must be blocked by UNIQUE KEY uq_jti '
            . 'and return false (the race-victim signal callers convert to a '
            . 'replay rejection).',
        );

        // Belt-and-braces: only one row exists despite two insert attempts.
        $rows = QueryUtils::fetchRecords(
            'SELECT id FROM jwt_grant_history WHERE jti = ?',
            [$jti],
        );
        self::assertCount(1, $rows, 'UNIQUE constraint must block the second insert');
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

    /**
     * Aisle round-5 #9 (CWE-20) regression. The pre-fix schema stored
     * `jti` as VARCHAR(100) with the UNIQUE constraint on the raw
     * column. IdP-issued JTIs and the validator's synthetic replay
     * keys can exceed 100 chars; under non-strict SQL mode the insert
     * silently truncated, and two distinct long JTIs sharing the
     * first 100 chars would collide on the UNIQUE constraint —
     * either misclassifying a legitimate token as a replay or
     * blocking the row entirely.
     *
     * Post-fix the column is VARCHAR(512) and the UNIQUE moved to a
     * BINARY(32) GENERATED sha256 derivation. This test:
     *   - inserts a 200-char jti (would have been truncated pre-fix)
     *   - inserts a SECOND 200-char jti sharing the first 100 chars
     *     (would have been a UNIQUE-constraint collision pre-fix)
     *   - asserts both inserts succeed and round-trip via the
     *     full-value SELECT
     */
    public function testSaveJwtHistoryRoundTripsLongJtisWithoutCollision(): void
    {
        // 200 distinct chars; first 100 chars identical between A and B
        // so a VARCHAR(100) truncation would collapse them to the same
        // unique-key value.
        $sharedPrefix = str_repeat('a', 100);
        $jtiLong1 = $sharedPrefix . str_repeat('b', 100);
        $jtiLong2 = $sharedPrefix . str_repeat('c', 100);

        self::assertNotSame($jtiLong1, $jtiLong2);
        self::assertSame(
            substr($jtiLong1, 0, 100),
            substr($jtiLong2, 0, 100),
            'Test fixture: the two jtis must share their first 100 chars',
        );

        self::assertTrue(
            $this->repository->saveJwtHistory($jtiLong1, 'jwt-repo-test-long-jti', time() + 3600),
            'First long jti must insert',
        );
        self::assertTrue(
            $this->repository->saveJwtHistory($jtiLong2, 'jwt-repo-test-long-jti', time() + 3600),
            'Second long jti with same 100-char prefix must NOT collide — '
            . 'pre-fix this would have failed with affected_rows = 0',
        );

        // Round-trip: lookup by full-length jti returns the right row,
        // not the other one with the same 100-char prefix.
        $row1 = $this->repository->getJwtGrantHistoryForJTI($jtiLong1);
        $row2 = $this->repository->getJwtGrantHistoryForJTI($jtiLong2);
        self::assertCount(1, $row1, 'Long jti #1 round-trips');
        self::assertCount(1, $row2, 'Long jti #2 round-trips');
        self::assertSame($jtiLong1, $row1[0]['jti']);
        self::assertSame($jtiLong2, $row2[0]['jti']);

        // Cleanup — the per-test setUp truncate doesn't run between
        // these two inserts, so leave the table in a consistent state
        // for any test that runs after.
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM jwt_grant_history WHERE client_id = ?',
            ['jwt-repo-test-long-jti'],
        );
    }
}
