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

trait DatabaseQueryTrait
{
    protected function listTableFields($table)
    {
        return QueryUtils::listTableFields($table);
    }

    protected function escapeTableName($table)
    {
        return QueryUtils::escapeTableName($table);
    }

    protected function escapeColumnName($columnName, $tables = [])
    {
        return QueryUtils::escapeColumnName($columnName, $tables);
    }

    protected function fetchRecordsNoLog($sqlStatement, $binds)
    {
        return QueryUtils::fetchRecordsNoLog($sqlStatement, $binds);
    }

    protected function fetchTableColumn($sqlStatement, $column, $binds = [])
    {
        return QueryUtils::fetchTableColumn($sqlStatement, $column, $binds);
    }

    protected function fetchSingleValue($sqlStatement, $column, $binds = [])
    {
        return QueryUtils::fetchSingleValue($sqlStatement, $column, $binds);
    }

    protected function fetchRecords($sqlStatement, $binds = [], $noLog = false)
    {
        return QueryUtils::fetchRecords($sqlStatement, $binds, $noLog);
    }

    protected function fetchTableColumnAssoc($sqlStatement, $column, $binds = [])
    {
        return QueryUtils::fetchTableColumnAssoc($sqlStatement, $column, $binds);
    }

    protected function fetchArrayFromResultSet($resultSet)
    {
        return QueryUtils::fetchArrayFromResultSet($resultSet);
    }

    protected function sqlStatementThrowException($statement, $binds, $noLog = false)
    {
        return QueryUtils::sqlStatementThrowException($statement, $binds, $noLog);
    }

    protected function existsTable($tableName)
    {
        return QueryUtils::existsTable($tableName);
    }

    protected function sqlInsert($statement, $binds = [])
    {
        return QueryUtils::sqlInsert($statement, $binds);
    }

    protected function selectHelper($sqlUpToFromStatement, $map)
    {
        return QueryUtils::selectHelper($sqlUpToFromStatement, $map);
    }

    protected function generateId()
    {
        return QueryUtils::generateId();
    }

    protected function ediGenerateId()
    {
        return QueryUtils::ediGenerateId();
    }

    protected function startTransaction()
    {
        return QueryUtils::startTransaction();
    }

    protected function commitTransaction()
    {
        return QueryUtils::commitTransaction();
    }

    protected function rollbackTransaction()
    {
        return QueryUtils::rollbackTransaction();
    }

    protected function getLastInsertId()
    {
        return QueryUtils::getLastInsertId();
    }

    protected function querySingleRow(string $sql, array $params)
    {
        return QueryUtils::querySingleRow($sql, $params);
    }

    protected function escapeLimit(string|int $limit)
    {
        return QueryUtils::escapeLimit($limit);
    }
}
