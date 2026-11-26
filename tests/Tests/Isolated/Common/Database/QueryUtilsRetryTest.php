<?php

/**
 * Isolated tests for QueryUtils::isRetryableWriteConflict — the retry
 * decision helper used by QueryUtils::inTransaction() to decide whether a
 * transaction failure should be retried (MariaDB deadlock, Galera
 * certification conflict) vs propagated (constraint violation, syntax
 * error, lock-wait timeout).
 *
 * The retry LOOP itself (inTransaction) touches the ADODB global via
 * startTransaction / commitTransaction / rollbackTransaction, so it
 * isn't isolated-tier testable without substantial mocking. The DECISION
 * logic — whether an arbitrary Throwable warrants a retry — is pure and
 * covered here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryUtilsRetryTest extends TestCase
{
    public function testDeadlockCodeIsRetryableRegardlessOfMessage(): void
    {
        $e = new SqlQueryException(
            sqlStatement: 'UPDATE patient_data SET fname = ? WHERE pid = ?',
            message: 'Any arbitrary message — the 1213 code alone qualifies',
            code: 1213,
        );
        $this->assertTrue(
            QueryUtils::isRetryableWriteConflict($e),
            'ER_LOCK_DEADLOCK (1213) must always be retryable — that is the MariaDB contract for deadlocks regardless of cluster topology',
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function galeraCommitErrorMessageProvider(): array
    {
        return [
            'wsrep lowercase'       => ['WSREP replication failed. Check wsrep connection state'],
            'wsrep uppercase'       => ['wsrep certification failed'],
            'certification failure' => ['Got error during COMMIT: cluster conflict detected (certification failure)'],
            'deadlock in message'   => ['Deadlock found when trying to get lock; try restarting transaction'],
        ];
    }

    #[DataProvider('galeraCommitErrorMessageProvider')]
    public function testCommitErrorIsRetryableWhenMessageIdentifiesGalera(string $message): void
    {
        $e = new SqlQueryException(
            sqlStatement: 'COMMIT',
            message: $message,
            code: 1180,
        );
        $this->assertTrue(
            QueryUtils::isRetryableWriteConflict($e),
            'ER_ERROR_DURING_COMMIT (1180) with a WSREP/certification/deadlock message indicates a transient Galera write-conflict and should be retried',
        );
    }

    public function testCommitErrorIsNotRetryableWhenMessageDoesNotIdentifyGalera(): void
    {
        $e = new SqlQueryException(
            sqlStatement: 'COMMIT',
            message: 'Got error 28 "No space left on device" during COMMIT',
            code: 1180,
        );
        $this->assertFalse(
            QueryUtils::isRetryableWriteConflict($e),
            '1180 is generic — a disk-full or binlog failure is not retryable; require explicit WSREP/certification/deadlock in the message',
        );
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonRetryableCodeProvider(): array
    {
        return [
            'lock-wait timeout (1205)'   => [1205],  // retrying won't release the holder's lock
            'duplicate entry (1062)'     => [1062],  // constraint violation
            'syntax error (1064)'        => [1064],
            'no such table (1146)'       => [1146],
            'fk constraint fails (1452)' => [1452],
            'access denied (1045)'       => [1045],
            'generic / unknown (0)'      => [0],     // getLastErrorCode() returns 0 when ADODB unavailable
        ];
    }

    #[DataProvider('nonRetryableCodeProvider')]
    public function testNonRetryableCodesStayNonRetryable(int $code): void
    {
        $e = new SqlQueryException(
            sqlStatement: 'SELECT',
            message: 'Any message',
            code: $code,
        );
        $this->assertFalse(
            QueryUtils::isRetryableWriteConflict($e),
            sprintf('error code %d should NOT be retryable — only 1213 (deadlock) and 1180+WSREP/cert/deadlock-message are transient', $code),
        );
    }

    public function testArbitraryThrowableIsNotRetryable(): void
    {
        // Non-SqlQueryException Throwable with getCode() returning 0 — e.g.
        // a RuntimeException the transaction body threw directly. These
        // should propagate, not retry.
        $e = new \RuntimeException('Something exploded inside the callable');
        $this->assertFalse(
            QueryUtils::isRetryableWriteConflict($e),
            'A non-DB Throwable thrown by the transaction body must propagate, not be retried',
        );
    }

    /**
     * A RuntimeException with code 1213 is NOT a database retry signal —
     * code-1213 only triggers retry when the exception type is
     * SqlQueryException. This keeps the retry policy scoped to failures
     * that actually originated in the DB layer. If a caller's transaction
     * body threw its own RuntimeException with an arbitrary numeric code
     * matching a SQLSTATE code, retry would be unsafe (we don't know what
     * state the callable left behind — the intent of catching THAT error
     * is caller-specific, not retry).
     */
    public function testRuntimeExceptionWithDeadlockCodeIsNotRetryable(): void
    {
        $e = new \RuntimeException('coincidentally code 1213', 1213);
        $this->assertFalse(
            QueryUtils::isRetryableWriteConflict($e),
            'retry is scoped to SqlQueryException; a stray RuntimeException with code=1213 is not a DB retry signal',
        );
    }
}
