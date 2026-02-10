<?php

/**
 * QueryUtils.php  Is a helper class for commonly used database functions.  Eventually everything in the sql.inc.php file
 * could be migrated to this file or at least contained in this namespace.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use ADORecordSet;
use OpenEMR\Common\Logging\SystemLogger;
use Throwable;

class QueryUtils
{
    /**
     * Function that will return an array listing
     * of columns that exist in a table.
     *
     * @param   string  $table sql table
     * @return  string[]
     */
    public static function listTableFields($table)
    {
        $sql = "SHOW COLUMNS FROM " . self::escapeTableName($table);
        $field_list = [];
        $records = self::fetchRecords($sql, [], false);
        foreach ($records as $record) {
            $field_list[] = $record["Field"];
        }

        return $field_list;
    }

    public static function escapeTableName(string $table): string
    {
        $res = self::sqlStatementThrowException("SHOW TABLES", [], noLog: true);
        $tables_array = [];
        while ($row = self::fetchArrayFromResultSet($res)) {
            $keys_return = array_keys($row);
            $tables_array[] = $row[$keys_return[0]];
        }

        // Now can escape(via whitelisting) the sql table name
        return \escape_identifier($table, $tables_array, true, false);
    }

    /**
     * @param string $columnName
     * @param string[] $tables
     */
    public static function escapeColumnName($columnName, $tables = []): string
    {
        return \escape_sql_column_name($columnName, $tables);
    }

    /**
     * @param string $sqlStatement
     * @param mixed[] $binds
     * @return list<array<mixed>>
     */
    public static function fetchRecordsNoLog($sqlStatement, $binds = [])
    {
        // Below line is to avoid a nasty bug in windows.
        if (empty($binds)) {
            $binds = false;
        }

        $recordset = self::getADODB()->ExecuteNoLog($sqlStatement, $binds);

        if ($recordset === false) {
            $error = self::getLastError();
            throw new SqlQueryException(
                sqlStatement: $sqlStatement,
                message: "Failed to execute statement. Error: " . $error . " Statement: " . $sqlStatement,
                sqlError: $error,
            );
        }
        $list = [];
        while ($record = self::fetchArrayFromResultSet($recordset)) {
            $list[] = $record;
        }
        return $list;
    }
    /**
     * Executes the SQL statement passed in and returns a list of all of the values contained in the column
     * @param string $sqlStatement
     * @param string $column column you want returned
     * @param mixed[] $binds
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return list<mixed>
     */
    public static function fetchTableColumn($sqlStatement, $column, $binds = [])
    {
        $recordSet = self::sqlStatementThrowException($sqlStatement, $binds);
        $list = [];
        while ($record = self::fetchArrayFromResultSet($recordSet)) {
            $list[] = $record[$column] ?? null;
        }
        return $list;
    }

    /**
     * @param string $sqlStatement
     * @param string $column
     * @param mixed[] $binds
     */
    public static function fetchSingleValue($sqlStatement, $column, $binds = [])
    {
        $records = self::fetchTableColumn($sqlStatement, $column, $binds);
        // note if $records[0] is actually the value 0 then the value returned is null...
        // do we want that behavior?
        if (!empty($records[0])) {
            return $records[0];
        }
        return null;
    }

    /**
     * @param string $sqlStatement
     * @param mixed[] $binds
     * @param bool $noLog
     * @return list<array<mixed>>
     */
    public static function fetchRecords($sqlStatement, $binds = [], $noLog = false)
    {
        $result = self::sqlStatementThrowException($sqlStatement, $binds, $noLog);
        $list = [];
        while ($record = self::fetchArrayFromResultSet($result)) {
            $list[] = $record;
        }
        return $list;
    }

    /**
     * Executes the sql statement and returns an associative array for a single column of a table
     * @param string $sqlStatement The statement to run
     * @param string $column The column you want returned
     * @param mixed[] $binds
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return array<mixed>
     */
    public static function fetchTableColumnAssoc($sqlStatement, $column, $binds = [])
    {
        $recordSet = self::sqlStatementThrowException($sqlStatement, $binds);
        $list = [];
        while ($record = self::fetchArrayFromResultSet($recordSet)) {
            $list[$column] = $record[$column] ?? null;
        }
        return $list;
    }

    /**
     * Returns a row (as an array) from a sql recordset.
     *
     * Function that will allow use of the adodb binding
     * feature to prevent sql-injection.
     * It will act upon the object returned from the
     * sqlStatement() function (and sqlQ() function).
     *
     * @param ADORecordSet|false $resultSet
     * @return array<mixed>|false
     */
    public static function fetchArrayFromResultSet($resultSet)
    {
        //treat as an adodb recordset
        if ($resultSet === false) {
            return false;
        }

        if ($resultSet->EOF ?? '') {
            return false;
        }

        //ensure it's an object (ie. is set)
        if (!is_object($resultSet)) {
            return false;
        }

        return $resultSet->FetchRow();
    }

    /**
     * Standard sql query in OpenEMR.
     *
     * Function that will allow use of the adodb binding
     * feature to prevent sql-injection. Will continue to
     * be compatible with previous function calls that do
     * not use binding.
     * It will return a recordset object.
     * The sqlFetchArray() function should be used to
     * utilize the return object.
     *
     * @param string $statement query
     * @param mixed[] $binds binded variables array (optional)
     * @param bool $noLog if true the sql statement bypasses the database logger, false logs the sql statement
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return ADORecordSet
     */
    public static function sqlStatementThrowException($statement, $binds = [], $noLog = false)
    {
        // Below line is to avoid a nasty bug in windows.
        if (empty($binds)) {
            $binds = false;
        }

        //Run a adodb execute
        // Note the auditSQLEvent function is embedded in the
        //   Execute function.
        if ($noLog) {
            $recordset = self::getADODB()->ExecuteNoLog($statement, $binds);
        } else {
            $recordset = self::getADODB()->Execute($statement, $binds, true);
        }
        if ($recordset === false) {
            $error = self::getLastError();
            throw new SqlQueryException(
                sqlStatement: $statement,
                message: "Failed to execute statement. Error: " . $error . " Statement: " . $statement,
                sqlError: $error,
            );
        }
        return $recordset;
    }

    /**
     * @param string $tableName Table name to check if it exists must conform to the following regex ^[a-zA-Z_]{1}[a-zA-Z0-9_]{1,63}$
     * @return bool
     */
    public static function existsTable($tableName)
    {

        try {
            if (preg_match("/^[a-zA-Z_]{1}[a-zA-Z0-9_]{1,63}$/", (string) $tableName) === false) {
                return false; // don't allow invalid table names
            }
            // escape table name just DIES if the table name is not valid so we need to handle that here
            // to determine if it exists
            // normally we'd skip throwing the exception but default OpenEMR behavior is to die if the exception isn't
            // thrown which doesn't help us at all.

            $query = "SELECT 1 as id FROM " . $tableName . " LIMIT 1";
            $statement = self::sqlStatementThrowException($query, [], noLog: true);
            if ($statement !== false) {
                unset($statement); // free the resource
                return true;
            }
        } catch (\Throwable) {
            // do nothing as we know the table doesn't exist
        }
        return false;
    }

    /**
     * Sql insert query in OpenEMR.
     *  Only use this function if you need to have the
     *  id returned. If doing an insert query and do
     *  not need the id returned, then use the
     *  sqlStatement function instead.
     *
     * Function that will allow use of the adodb binding
     * feature to prevent sql-injection. This function
     * is specialized for insert function and will return
     * the last id generated from the insert.
     *
     * @param string $statement query
     * @param mixed[] $binds binded variables array (optional)
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return int Last id generated from the sql insert command
     */
    public static function sqlInsert($statement, $binds = [])
    {
        // Below line is to avoid a nasty bug in windows.
        if (empty($binds)) {
            $binds = false;
        }

        //Run a adodb execute
        // Note the auditSQLEvent function is embedded in the
        //   Execute function.
        $recordset = self::getADODB()->Execute($statement, $binds, true);
        if ($recordset === false) {
            $error = self::getLastError();
            throw new SqlQueryException(
                sqlStatement: $statement,
                message: "Insert failed. SQL error " . $error . " Query: " . $statement,
                sqlError: $error,
            );
        }

        // Return the correct last id generated using function
        //   that is safe with the audit engine.
        return $GLOBALS['lastidado'] > 0 ? $GLOBALS['lastidado'] : $GLOBALS['adodb']['db']->Insert_ID();
    }

    /**
     * Shared getter for SQL selects.
     *
     * @param string $sqlUpToFromStatement - The sql string up to (and including) the FROM line.
     * @param array{
     *   data?: mixed,
     *   where?: string,
     *   order?: string,
     *   join?: string,
     *   limit?: int,
     * } $map - Query information (where clause(s), join clause(s), order, data, etc).
     * @throws SqlQueryException If the query is invalid
     * @return array of associative arrays | one associative array.
     */
    public static function selectHelper($sqlUpToFromStatement, $map)
    {
        $where = $map["where"] ?? null;
        $data  = isset($map["data"]) && is_array($map['data']) ? $map["data"]  : [];
        $join  = $map["join"] ?? null;
        $order = $map["order"] ?? null;
        $limit = isset($map["limit"]) ? intval($map["limit"]) : null;

        $sql = $sqlUpToFromStatement;

        $sql .= !empty($join)  ? " " . $join        : "";
        $sql .= !empty($where) ? " " . $where       : "";
        $sql .= !empty($order) ? " " . $order       : "";
        $sql .= !empty($limit) ? " LIMIT " . $limit : "";

        $multipleResults = self::sqlStatementThrowException($sql, $data);

        $results = [];

        while ($row = self::fetchArrayFromResultSet($multipleResults)) {
            array_push($results, $row);
        }

        if ($limit === 1) {
            return $results[0];
        }

        return $results;
    }

    /**
     * @return int
     */
    public static function generateId()
    {
        return self::getADODB()->GenID("sequences");
    }

    /**
     * @return int
     */
    public static function ediGenerateId()
    {
        return self::getADODB()->GenID("edi_sequences");
    }

    public static function startTransaction(): void
    {
        self::getADODB()->BeginTrans();
    }

    public static function commitTransaction(): void
    {
        self::getADODB()->CommitTrans();
    }

    public static function rollbackTransaction(): void
    {
        self::getADODB()->RollbackTrans();
    }

    /**
     * Runs the $action within a database transaction, automatically committing
     * it on success and rolling back if there's an exception.
     *
     * @phpstan-template T
     * @param callable(): T $action
     * @return T
     */
    public static function inTransaction(callable $action): mixed
    {
        self::startTransaction();

        try {
            $return = $action();

            self::commitTransaction();
            return $return;
        } catch (Throwable $e) {
            self::rollbackTransaction();
            throw $e;
        }
    }

    /**
     * @return int
     */
    public static function getLastInsertId()
    {
        // Return the correct last id generated using function
        //   that is safe with the audit engine.
        return ($GLOBALS['lastidado'] ?? 0) > 0 ? $GLOBALS['lastidado'] : $GLOBALS['adodb']['db']->Insert_ID();
    }

    /**
     * Executes a query and returns the first row as an associative array.
     *
     * @param string $sql query
     * @param mixed[] $params binded variables array (optional)
     * @param bool $log if true the sql statement is logged, false bypasses the database logger
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return array<mixed>|false
     */
    public static function querySingleRow(string $sql, $params = [], bool $log = true)
    {
        /** @var mixed $params */
        if (!is_array($params)) {
            (new SystemLogger())->debug('Non-array $params passed to {method}: {trace}', [
                'method' => __METHOD__,
                'trace' => (new \Exception())->getTraceAsString(),
            ]);
        }
        $result = self::sqlStatementThrowException($sql, $params, noLog: !$log);
        return self::fetchArrayFromResultSet($result);
    }

    /**
     * Escape a sql limit variable to prepare for a sql query.
     *
     * This will escape integers within the LIMIT ?, ? part of a sql query.
     * Note that there is a maximum value to these numbers, which is why
     * should only use for the LIMIT ? , ? part of the sql query and why
     * this is centralized to a function (in case need to upgrade this
     * function to support larger numbers in the future).
     *
     * @return  int     Escaped limit variable.
     */
    public static function escapeLimit(string|int $limit)
    {
        return \escape_limit($limit);
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE, or DELETE query.
     *
     * @return int|false
     */
    public static function affectedRows()
    {
        return self::getADODB()->Affected_Rows();
    }

    /**
     * Returns the last SQL error message from the database connection.
     *
     * Checks the audit engine's error state first, then falls back to
     * ADODB's ErrorMsg().
     */
    public static function getLastError(): string
    {
        return !empty($GLOBALS['last_mysql_error'])
            ? (string) $GLOBALS['last_mysql_error']
            : (string) $GLOBALS['adodb']['db']->ErrorMsg();
    }

    /**
     * @return \ADODB_mysqli_log
     */
    private static function getADODB()
    {
        return $GLOBALS['adodb']['db'];
    }
}
