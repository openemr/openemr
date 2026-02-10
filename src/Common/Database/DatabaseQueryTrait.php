<?php

/**
 * DatabaseQueryTrait.php provides protected instance methods for database operations.
 * This trait wraps QueryUtils static methods to provide protected instance methods for classes
 * that need to make database queries.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use ADORecordSet;

trait DatabaseQueryTrait
{
    /**
     * @param string $table
     * @return string[]
     */
    protected function listTableFields($table)
    {
        return QueryUtils::listTableFields($table);
    }

    protected function escapeTableName(string $table): string
    {
        return QueryUtils::escapeTableName($table);
    }

    /**
     * @param string $columnName
     * @param string[] $tables
     */
    protected function escapeColumnName($columnName, $tables = []): string
    {
        return QueryUtils::escapeColumnName($columnName, $tables);
    }

    /**
     * @param string $sqlStatement
     * @param mixed[] $binds
     * @return list<array<mixed>>
     */
    protected function fetchRecordsNoLog($sqlStatement, $binds)
    {
        return QueryUtils::fetchRecordsNoLog($sqlStatement, $binds);
    }

    /**
     * @param string $sqlStatement
     * @param string $column
     * @param mixed[] $binds
     * @return list<mixed>
     */
    protected function fetchTableColumn($sqlStatement, $column, $binds = [])
    {
        return QueryUtils::fetchTableColumn($sqlStatement, $column, $binds);
    }

    /**
     * @param string $sqlStatement
     * @param string $column
     * @param mixed[] $binds
     */
    protected function fetchSingleValue($sqlStatement, $column, $binds = [])
    {
        return QueryUtils::fetchSingleValue($sqlStatement, $column, $binds);
    }

    /**
     * @param string $sqlStatement
     * @param mixed[] $binds
     * @param bool $noLog
     * @return list<array<mixed>>
     */
    protected function fetchRecords($sqlStatement, $binds = [], $noLog = false)
    {
        return QueryUtils::fetchRecords($sqlStatement, $binds, $noLog);
    }

    /**
     * @param string $sqlStatement
     * @param string $column
     * @param mixed[] $binds
     * @return array<mixed>
     */
    protected function fetchTableColumnAssoc($sqlStatement, $column, $binds = [])
    {
        return QueryUtils::fetchTableColumnAssoc($sqlStatement, $column, $binds);
    }

    /**
     * @param ADORecordSet|false $resultSet
     * @return array<mixed>|false
     */
    protected function fetchArrayFromResultSet($resultSet)
    {
        return QueryUtils::fetchArrayFromResultSet($resultSet);
    }

    /**
     * @param string $statement
     * @param mixed[] $binds
     * @param bool $noLog
     * @return ADORecordSet
     */
    protected function sqlStatementThrowException($statement, $binds, $noLog = false)
    {
        return QueryUtils::sqlStatementThrowException($statement, $binds, $noLog);
    }

    /**
     * @param string $tableName
     * @return bool
     */
    protected function existsTable($tableName)
    {
        return QueryUtils::existsTable($tableName);
    }

    /**
     * @param string $statement
     * @param mixed[] $binds
     * @return int
     */
    protected function sqlInsert($statement, $binds = [])
    {
        return QueryUtils::sqlInsert($statement, $binds);
    }

    /**
     * @param string $sqlUpToFromStatement
     * @param array{
     *   data?: mixed,
     *   where?: string,
     *   order?: string,
     *   join?: string,
     *   limit?: int,
     * } $map
     * @return array
     */
    protected function selectHelper($sqlUpToFromStatement, $map)
    {
        return QueryUtils::selectHelper($sqlUpToFromStatement, $map);
    }

    /**
     * @return int
     */
    protected function generateId()
    {
        return QueryUtils::generateId();
    }

    /**
     * @return int
     */
    protected function ediGenerateId()
    {
        return QueryUtils::ediGenerateId();
    }

    protected function startTransaction(): void
    {
        QueryUtils::startTransaction();
    }

    protected function commitTransaction(): void
    {
        QueryUtils::commitTransaction();
    }

    protected function rollbackTransaction(): void
    {
        QueryUtils::rollbackTransaction();
    }

    /**
     * @return int
     */
    protected function getLastInsertId()
    {
        return QueryUtils::getLastInsertId();
    }

    /**
     * @param mixed[] $params
     * @param bool $log
     * @return array<mixed>|false
     */
    protected function querySingleRow(string $sql, array $params, bool $log = true)
    {
        return QueryUtils::querySingleRow($sql, $params, $log);
    }

    /**
     * @return int
     */
    protected function escapeLimit(string|int $limit)
    {
        return QueryUtils::escapeLimit($limit);
    }
}
