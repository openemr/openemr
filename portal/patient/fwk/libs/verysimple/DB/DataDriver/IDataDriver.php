<?php

/** @package verysimple::DB::DataDriver */

/**
 * IDataDriver is an interface that is used by Phreeze::DataAdapter
 * to communicate with a database storage engine.
 * Any server
 * that can implement these methods will be usable with
 * Phreeze.
 *
 * @package verysimple::DB::DataDriver
 * @author VerySimple Inc. <noreply@verysimple.com>
 * @copyright 1997-2010 VerySimple Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
interface IDataDriver
{
    /**
     * returns a string to identify the type of server
     * supported in the implementation
     *
     * @return string
     */
    function GetServerType();

    /**
     * Return true if the given connection is live
     *
     * @param $connection
     * @return bool
     */
    function Ping($connection);

    /**
     * Open the database with the given parameters.
     * the implementation
     * must provide a protocol for the connection string that is relevant
     *
     * @param string $connectionstring
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $charset
     *          the charset that will be used for the connection (example 'utf8')
     * @param string $bootstrap
     *          SQL that will be executed when the connection is first opened (example 'SET SQL_BIG_SELECTS=1')
     * @return connection
     */
    function Open($connectionstring, $database, $username, $password, $charset = '', $bootstrap = '');

    /**
     * Close the given connection reference
     *
     * @param mixed $connection
     */
    function Close($connection);

    /**
     * Execute a SQL query that is expected to return a resultset
     *
     * @param mixed $connection
     * @param string $sql sql query
     * @return resultset
     */
    function Query($connection, $sql);

    /**
     * Executes a SQL query that does not return a resultset, such as an insert or update
     *
     * @param mixed $connection
     * @param string $sql sql statement
     * @return int number of affected records
     */
    function Execute($connection, $sql);

    /**
     * Moves the database cursor forward and returns the current row as an associative array
     * When no more data is available, null is returned
     *
     * @param mixed $connection
     * @param mixed $rs
     * @return array (or null)
     */
    function Fetch($connection, $rs);

    /**
     * Returns the last auto-insert id that was inserted for the
     * given connection reference
     *
     * @param mixed $connection
     */
    function GetLastInsertId($connection);

    /**
     * Returns the last error message that the server encountered
     * for the given connection reference
     *
     * @param mixed $connection
     */
    function GetLastError($connection);

    /**
     * Releases the resources for the given resultset.
     *
     * @param mixed $connection
     * @param mixed $rs
     */
    function Release($connection, $rs);

    /**
     * Remove or escape any characters that will cause a SQL statement
     * to crash or cause an injection exploit
     *
     * @param string $val value to escape
     * @return string value after escaping
     */
    function Escape($val);

    /**
     * Return a stringified version of $val ready to insert with appropriate quoting and escaping
     * This method must handle at a minimum: strings, numbers, NULL and ISqlFunction objects
     *
     * @param mixed $val value to insert/update/query
     * @return string value ready to use in a SQL statement quoted and escaped if necessary
     */
    function GetQuotedSql($val);

    /**
     * Returns an array of tablenames for the given database
     *
     * @param mixed $connection connection reference
     * @param string $dbname name of the database
     * @param $ommitEmptyTables (default
     *          false) set to true and tables with no data will be omitted
     */
    function GetTableNames($connection, $dbname, $ommitEmptyTables = false);

    /**
     * Optimize, clean, defrag or whatever action is relevant for the database server
     *
     * @param mixed $connection connection reference
     * @param string $table name of table to optimize
     */
    function Optimize($connection, $table);

    /**
     * Start a database transaction and disable auto-commit if necessary
     *
     * @param mixed $connection connection reference
     */
    function StartTransaction($connection);

    /**
     * Commit the current database transaction and re-enable auto-commit
     *
     * @param mixed $connection connection reference
     */
    function CommitTransaction($connection);

    /**
     * Rollback the current database transaction and re-enable auto-commit
     *
     * @param mixed $connection connection reference
     */
    function RollbackTransaction($connection);
}
