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

class QueryUtils
{
    /**
     * Function that will return an array listing
     * of columns that exist in a table.
     *
     * @param   string  $table sql table
     * @return  array
     */
    public static function listTableFields($table)
    {
        $sql = "SHOW COLUMNS FROM " . \escape_table_name($table);
        $field_list = array();
        $records = self::fetchRecords($sql, [], false);
        foreach ($records as $record) {
            $field_list[] = $record["Field"];
        }

        return $field_list;
    }

    public static function escapeTableName($table)
    {
        return \escape_table_name($table);
    }

    public static function escapeColumnName($columnName, $tables = [])
    {
        return \escape_sql_column_name($columnName, $tables);
    }

    public static function fetchRecordsNoLog($sqlStatement, $binds)
    {
        // Below line is to avoid a nasty bug in windows.
        if (empty($binds)) {
            $binds = false;
        }

        $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($sqlStatement, $binds);

        if ($recordset === false) {
            throw new SqlQueryException($sqlStatement, "Failed to execute statement. Error: "
                . getSqlLastError() . " Statement: " . $sqlStatement);
        }
        $list = [];
        while ($record = sqlFetchArray($recordset)) {
            $list[] = $record;
        }
        return $list;
    }
    /**
     * Executes the SQL statement passed in and returns a list of all of the values contained in the column
     * @param $sqlStatement
     * @param $column string column you want returned
     * @param array $binds
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return array
     */
    public static function fetchTableColumn($sqlStatement, $column, $binds = array())
    {
        $recordSet = self::sqlStatementThrowException($sqlStatement, $binds);
        $list = [];
        while ($record = sqlFetchArray($recordSet)) {
            $list[] = $record[$column] ?? null;
        }
        return $list;
    }

    public static function fetchSingleValue($sqlStatement, $column, $binds = array())
    {
        $records = self::fetchTableColumn($sqlStatement, $column, $binds);
        // note if $records[0] is actually the value 0 then the value returned is null...
        // do we want that behavior?
        if (!empty($records[0])) {
            return $records[0];
        }
        return null;
    }

    public static function fetchRecords($sqlStatement, $binds = array(), $noLog = false)
    {
        $result = self::sqlStatementThrowException($sqlStatement, $binds, $noLog);
        $list = [];
        while ($record = \sqlFetchArray($result)) {
            $list[] = $record;
        }
        return $list;
    }

    /**
     * Executes the sql statement and returns an associative array for a single column of a table
     * @param $sqlStatement The statement to run
     * @param $column The column you want returned
     * @param array $binds
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return array
     */
    public static function fetchTableColumnAssoc($sqlStatement, $column, $binds = array())
    {
        $recordSet = self::sqlStatementThrowException($sqlStatement, $binds);
        $list = [];
        while ($record = sqlFetchArray($recordSet)) {
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
     * @param recordset $resultSet
     * @return array
     */
    public static function fetchArrayFromResultSet($resultSet)
    {
        return sqlFetchArray($resultSet);
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
     * @param  string  $statement  query
     * @param  array   $binds      binded variables array (optional)
     * @param  noLog   boolean     if true the sql statement bypasses the database logger, false logs the sql statement
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return recordset
     */
    public static function sqlStatementThrowException($statement, $binds, $noLog = false)
    {
        if ($noLog) {
            return \sqlStatementNoLog($statement, $binds, true);
        } else {
            return \sqlStatementThrowException($statement, $binds);
        }
    }

    /**
     * @param $tableName Table name to check if it exists must conform to the following regex ^[a-zA-Z_]{1}[a-zA-Z0-9_]{1,63}$
     * @return bool
     */
    public static function existsTable($tableName)
    {

        try {
            if (preg_match("/^[a-zA-Z_]{1}[a-zA-Z0-9_]{1,63}$/", $tableName) === false) {
                return false; // don't allow invalid table names
            }
            // escape table name just DIES if the table name is not valid so we need to handle that here
            // to determine if it exists
            // normally we'd skip throwing the exception but default OpenEMR behavior is to die if the exception isn't
            // thrown which doesn't help us at all.

            $query = "SELECT 1 as id FROM " . $tableName . " LIMIT 1";
            $statement = \sqlStatementNoLog($query, [], true);
            if ($statement !== false) {
                unset($statement); // free the resource
                return true;
            }
        } catch (\Exception $e) {
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
     * @param  string   $statement  query
     * @param  array    $binds      binded variables array (optional)
     * @throws SqlQueryException Thrown if there is an error in the database executing the statement
     * @return integer  Last id generated from the sql insert command
     */
    public static function sqlInsert($statement, $binds = array())
    {
        // Below line is to avoid a nasty bug in windows.
        if (empty($binds)) {
            $binds = false;
        }

        //Run a adodb execute
        // Note the auditSQLEvent function is embedded in the
        //   Execute function.
        $recordset = $GLOBALS['adodb']['db']->Execute($statement, $binds, true);
        if ($recordset === false) {
            throw new SqlQueryException($statement, "Insert failed. SQL error " . getSqlLastError() . " Query: " . $statement);
        }

        // Return the correct last id generated using function
        //   that is safe with the audit engine.
        return $GLOBALS['lastidado'] > 0 ? $GLOBALS['lastidado'] : $GLOBALS['adodb']['db']->Insert_ID();
    }

    /**
     * Shared getter for SQL selects.
     *
     * @param $sqlUpToFromStatement - The sql string up to (and including) the FROM line.
     * @param $map - Query information (where clause(s), join clause(s), order, data, etc).
     * @throws SqlQueryException If the query is invalid
     * @return array of associative arrays | one associative array.
     */
    public static function selectHelper($sqlUpToFromStatement, $map)
    {
        $where = isset($map["where"]) ? $map["where"] : null;
        $data  = isset($map["data"]) && is_array($map['data']) ? $map["data"]  : [];
        $join  = isset($map["join"])  ? $map["join"]  : null;
        $order = isset($map["order"]) ? $map["order"] : null;
        $limit = isset($map["limit"]) ? intval($map["limit"]) : null;

        $sql = $sqlUpToFromStatement;

        $sql .= !empty($join)  ? " " . $join        : "";
        $sql .= !empty($where) ? " " . $where       : "";
        $sql .= !empty($order) ? " " . $order       : "";
        $sql .= !empty($limit) ? " LIMIT " . $limit : "";

        $multipleResults = sqlStatementThrowException($sql, $data);

        $results = array();

        while ($row = sqlFetchArray($multipleResults)) {
            array_push($results, $row);
        }

        if ($limit === 1) {
            return $results[0];
        }

        return $results;
    }

    public static function generateId()
    {
        return \generate_id();
    }

    public static function ediGenerateId()
    {
        return \edi_generate_id();
    }

    public static function startTransaction()
    {
        \sqlBeginTrans();
    }

    public static function commitTransaction()
    {
        \sqlCommitTrans();
    }

    public static function rollbackTransaction()
    {
        \sqlRollbackTrans();
    }

    public static function getLastInsertId()
    {
        return \sqlGetLastInsertId();
    }

    public static function querySingleRow(string $sql, array $params)
    {
        $result = self::sqlStatementThrowException($sql, $params);
        return \sqlFetchArray($result);
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
     * @param   string|int $s  Limit variable to be escaped.
     * @return  int     Escaped limit variable.
     */
    public static function escapeLimit(string|int $limit)
    {
        return \escape_limit($limit);
    }
}
