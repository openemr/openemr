<?php

/**
 * QueryUtils as instance methods so isolated tests can substitute a fake.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Database\QueryUtils;

class Queries
{
    /**
     * Fetch every matching row.
     *
     * @param array<int|string, mixed> $binds
     * @return list<array<mixed>>
     */
    public function fetchRecords(string $sql, array $binds = []): array
    {
        return QueryUtils::fetchRecords($sql, $binds);
    }

    /**
     * Fetch one row, or null when none match.
     *
     * @param array<int|string, mixed> $params
     */
    public function querySingleRow(string $sql, array $params = []): mixed
    {
        return QueryUtils::querySingleRow($sql, $params);
    }

    /**
     * Run a statement and throw on a database error.
     *
     * @param array<int|string, mixed> $binds
     */
    public function sqlStatementThrowException(string $sql, array $binds = []): mixed
    {
        return QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    /**
     * Insert a row and return the new id.
     *
     * @param array<int|string, mixed> $binds
     */
    public function sqlInsert(string $sql, array $binds = []): int
    {
        return Values::asInt(QueryUtils::sqlInsert($sql, $binds));
    }

    /**
     * Run $action in one database transaction.
     *
     * Requires QueryUtils::inTransaction(), added in OpenEMR 8.
     *
     * @template T
     * @param callable(): T $action
     * @return T
     */
    public function inTransaction(callable $action): mixed
    {
        return QueryUtils::inTransaction($action);
    }

    /**
     * Take a named GET_LOCK, or return false if the wait timed out.
     */
    public function acquireLock(string $name, int $timeoutSeconds = 10): bool
    {
        $got = QueryUtils::fetchSingleValue(
            "SELECT GET_LOCK(?, ?) AS acquired",
            "acquired",
            [$name, $timeoutSeconds]
        );
        return is_scalar($got) && (string) $got === "1";
    }

    /**
     * Drop a named GET_LOCK held by this session.
     */
    public function releaseLock(string $name): void
    {
        QueryUtils::sqlStatementThrowException("DO RELEASE_LOCK(?)", [$name]);
    }
}
