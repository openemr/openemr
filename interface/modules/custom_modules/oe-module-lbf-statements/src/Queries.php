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
     * @param array<int|string, mixed> $binds
     * @return list<array<mixed>>
     */
    public function fetchRecords(string $sql, array $binds = []): array
    {
        return QueryUtils::fetchRecords($sql, $binds);
    }

    /**
     * @param array<int|string, mixed> $params
     */
    public function querySingleRow(string $sql, array $params = []): mixed
    {
        return QueryUtils::querySingleRow($sql, $params);
    }

    /**
     * @param array<int|string, mixed> $binds
     */
    public function sqlStatementThrowException(string $sql, array $binds = []): mixed
    {
        return QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    /**
     * @param array<int|string, mixed> $binds
     */
    public function sqlInsert(string $sql, array $binds = []): int
    {
        return Values::asInt(QueryUtils::sqlInsert($sql, $binds));
    }
}
