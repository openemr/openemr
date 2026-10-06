<?php

/**
 * Database access through OpenEMR's QueryUtils (available in 7.0.x and 8.x).
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

use OpenEMR\Common\Database\QueryUtils;

final class Db
{
    /**
     * @param list<mixed> $binds
     * @return array<string, mixed>|null
     */
    public static function one(string $sql, array $binds = []): ?array
    {
        $row = QueryUtils::querySingleRow($sql, $binds);
        return is_array($row) && $row !== [] ? Val::map($row) : null;
    }

    /**
     * @param list<mixed> $binds
     * @return list<array<string, mixed>>
     */
    public static function all(string $sql, array $binds = []): array
    {
        return Val::maps(QueryUtils::fetchRecords($sql, $binds));
    }

    /**
     * @param list<mixed> $binds
     */
    public static function exec(string $sql, array $binds = []): void
    {
        QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    /**
     * @param list<mixed> $binds
     */
    public static function insert(string $sql, array $binds = []): int
    {
        return Val::int(QueryUtils::sqlInsert($sql, $binds));
    }

    /**
     * Run $action in one database transaction; roll back if it throws.
     *
     * @template T
     * @param callable(): T $action
     * @return T
     */
    public static function transaction(callable $action): mixed
    {
        return QueryUtils::inTransaction($action);
    }
}
