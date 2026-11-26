<?php

/**
 * QueryUtils.php  Is a helper class for commonly used database functions.  Eventually everything in the sql.inc.php file
 * could be migrated to this file or at least contained in this namespace.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use ADORecordSet;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use Throwable;

class QueryUtils
{
    /**
     * @var list<string>|null
     */
    private static ?array $tableListCache = null;

    /** @var array<string, string[]> */
    private static array $tableFieldsCache = [];

    /** @var array<string, list<array<mixed>>> */
    private static array $autoIncrementCache = [];

    /**
     * Drop memoized schema reads. Required after any DDL executed within the
     * same request (which really shouldn't happen), otherwise subsequent reads
     * report the pre-DDL shape.
     */
    public static function clearSchemaCache(): void
    {
        self::$tableListCache = null;
        self::$tableFieldsCache = [];
        self::$autoIncrementCache = [];
    }

    /**
     * Every table name in the current database, used as the allow-list for
     * identifier escaping.
     *
     * @return list<string>
     */
    public static function listTables(): array
    {
        if (self::$tableListCache === null) {
            $res = self::sqlStatementThrowException("SHOW TABLES", [], noLog: true);
            $tables_array = [];
            while ($row = self::fetchArrayFromResultSet($res)) {
                $keys_return = array_keys($row);
                $tableName = $row[$keys_return[0]];
                // SHOW TABLES only ever yields strings; the guard is what proves
                // that to static analysis without casting away the row type.
                if (is_string($tableName)) {
                    $tables_array[] = $tableName;
                }
            }
            self::$tableListCache = $tables_array;
        }

        return self::$tableListCache;
    }

    /**
     * Function that will return an array listing
     * of columns that exist in a table.
     *
     * @param   string  $table sql table
     * @return  string[]
     */
    public static function listTableFields($table)
    {
        if (!array_key_exists($table, self::$tableFieldsCache)) {
            $sql = "SHOW COLUMNS FROM " . self::escapeTableName($table);
            $field_list = [];
            $records = self::fetchRecords($sql, [], false);
            foreach ($records as $record) {
                $field = $record["Field"] ?? null;
                if (is_string($field)) {
                    $field_list[] = $field;
                }
            }

            self::$tableFieldsCache[$table] = $field_list;
        }

        return self::$tableFieldsCache[$table];
    }

    /**
     * Rows from SHOW COLUMNS describing a table's auto-increment columns.
     *
     * @return list<array<mixed>>
     */
    public static function listAutoIncrementColumns(string $table): array
    {
        if (!array_key_exists($table, self::$autoIncrementCache)) {
            self::$autoIncrementCache[$table] = self::fetchRecordsNoLog(
                "SHOW COLUMNS FROM " . self::escapeTableName($table) . " WHERE extra LIKE ?",
                ['%auto_increment%'],
            );
        }

        return self::$autoIncrementCache[$table];
    }

    public static function escapeTableName(string $table): string
    {
        // Reject table names containing backticks to prevent identifier-context injection
        if (str_contains($table, '`')) {
            throw new SqlQueryException("", "ERROR: OpenEMR SQL Escaping ERROR of the following string: " . \errorLogEscape($table));
        }

        // Whitelist against actual tables, then backtick-quote to keep in identifier context
        $tableName = \escape_identifier($table, self::listTables(), true, false);
        return sprintf('`%s`', $tableName);
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
    public static function fetchRecordsNoLog($sqlStatement, $binds = []): array
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
                code: self::getLastErrorCode(),
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
    public static function fetchTableColumn($sqlStatement, $column, $binds = []): array
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
        return $records[0] ?? null;
    }

    /**
     * @param string $sqlStatement
     * @param mixed[] $binds
     * @param bool $noLog
     * @return list<array<mixed>>
     */
    public static function fetchRecords($sqlStatement, $binds = [], $noLog = false): array
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
    public static function fetchTableColumnAssoc($sqlStatement, $column, $binds = []): array
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
                code: self::getLastErrorCode(),
                sqlError: $error,
            );
        }
        return $recordset;
    }

    /**
     * @param string $tableName Table name to check if it exists must conform to the following regex ^[a-zA-Z_]{1}[a-zA-Z0-9_]{1,63}$
     * @return bool
     */
    public static function existsTable($tableName): bool
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
        } catch (SqlQueryException) {
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
                code: self::getLastErrorCode(),
                sqlError: $error,
            );
        }

        // Return the correct last id generated using function
        //   that is safe with the audit engine.
        return OEGlobalsBag::getInstance()->get('lastidado') > 0 ? OEGlobalsBag::getInstance()->get('lastidado') : OEGlobalsBag::getInstance()->get('adodb')['db']->Insert_ID();
    }

    /**
     * Shared getter for SQL selects.
     *
     * @param string $sqlUpToFromStatement - The sql string up to (and including) the FROM line.
     * @param mixed $map array{
     *   data?: mixed,
     *   where?: string,
     *   order?: string,
     *   join?: string,
     *   limit?: int,
     * } $map - Query information (where clause(s), join clause(s), order, data, etc).
     * @throws SqlQueryException If the query is invalid
     * @return ?array list of associative arrays, or for a limit of 1 the single row (null if none matched)
     */
    public static function selectHelper($sqlUpToFromStatement, $map): ?array
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
            return $results[0] ?? null;
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

    /**
     * Commits the current transaction. Surfaces COMMIT failure as a
     * SqlQueryException carrying the driver's error code — necessary for
     * Galera, where write-conflict certification can reject the commit
     * even after every statement in the transaction succeeded locally.
     * Prior behavior discarded CommitTrans()'s return value, which would
     * silently report success on a cert-rejected commit.
     */
    public static function commitTransaction(): void
    {
        if (self::getADODB()->CommitTrans() === false) {
            $error = self::getLastError();
            throw new SqlQueryException(
                sqlStatement: 'COMMIT',
                message: "COMMIT failed. Error: " . $error,
                code: self::getLastErrorCode(),
                sqlError: $error,
            );
        }
    }

    public static function rollbackTransaction(): void
    {
        self::getADODB()->RollbackTrans();
    }

    /**
     * MariaDB / Galera error codes that indicate a transient write-conflict
     * where retrying the whole transaction is the correct recovery.
     *
     * 1213 ER_LOCK_DEADLOCK — classic InnoDB deadlock. MariaDB resolves by
     *      aborting one of the deadlocked transactions with this code; the
     *      calling application is expected to retry. Galera also surfaces
     *      cross-node certification failures as 1213 at commit time (MariaDB
     *      maps SQLSTATE 40001 to this).
     *
     * 1180 ER_ERROR_DURING_COMMIT — generic "something went wrong at commit"
     *      wrapper. On Galera this is how WSREP certification conflicts
     *      surface for writes that were optimistically applied locally but
     *      rejected globally. Only treat as retryable when the companion
     *      message identifies WSREP; a bare 1180 could be a disk or binlog
     *      problem that retrying won't fix.
     */
    private const RETRYABLE_SQLSTATE_DEADLOCK = 1213;
    private const RETRYABLE_SQLSTATE_COMMIT_ERROR = 1180;

    /** Default retry budget. Zero disables retries (legacy behavior). */
    public const DEFAULT_TRANSACTION_RETRIES = 3;

    /** Default initial backoff between retries (milliseconds). */
    public const DEFAULT_TRANSACTION_RETRY_DELAY_MS = 50;

    /**
     * Runs the $action within a database transaction, automatically committing
     * it on success and rolling back if there's an exception.
     *
     * Retries the whole transaction up to $maxRetries times when the failure
     * is a MariaDB / Galera write-conflict error code (see
     * RETRYABLE_SQLSTATE_*). Uses exponential backoff with full jitter
     * (AWS architecture-blog pattern) — each retry doubles the base delay
     * and picks a uniform-random actual delay in [0, base]. This smears
     * retries over time instead of thundering-herd re-colliding at the
     * same instant.
     *
     * Design intent: openemr-devops is adopting Galera multi-master
     * (openemr/openemr-devops#931 follow-up — write-conflict certification
     * is Galera's commit contract). Classic single-primary MariaDB
     * deadlock-retries are already standard SQL practice, so this
     * wrapper benefits non-Galera deployments too. Default budget of
     * 3 retries matches the common best-practice (deadlock cycles
     * resolve in 1-2 attempts; anything requiring more is a hot-spot
     * contention problem the application needs to redesign around).
     *
     * Replay-safety contract for callers: the $action callback MAY be
     * invoked more than once when the transaction is retried. Before
     * each invocation the DB transaction is rolled back and a fresh
     * one started, so DB state the callback reads is consistent with
     * a new attempt. But SIDE EFFECTS OUTSIDE THE TRANSACTION do not
     * roll back automatically: callers that mutate PHP globals, write
     * to `$_SESSION`, enqueue external work (email, HTTP calls), or
     * capture `lastInsertId()` into caller-scope variables must
     * design those side effects to be idempotent OR opt out of retry
     * by passing `maxRetries: 0`. This is the same hazard as every
     * classic deadlock-retry loop — on single-primary MariaDB today
     * a deadlock also rolls back the DB but any non-DB side effects
     * stick; this wrapper doesn't change the contract, just makes
     * the retry explicit.
     *
     * @phpstan-template T
     * @param callable(): T $action
     * @param int $maxRetries Max retry attempts on transient write-conflict (0 = no retry, legacy behavior)
     * @param int $retryDelayMs Initial base delay in milliseconds (doubles per attempt before jitter)
     * @return T
     */
    public static function inTransaction(
        callable $action,
        int $maxRetries = self::DEFAULT_TRANSACTION_RETRIES,
        int $retryDelayMs = self::DEFAULT_TRANSACTION_RETRY_DELAY_MS,
    ): mixed {
        $attempt = 0;
        while (true) {
            self::startTransaction();

            try {
                $return = $action();
                self::commitTransaction();
                return $return;
            } catch (Throwable $e) {
                self::rollbackTransaction();

                if ($attempt < $maxRetries && self::isRetryableWriteConflict($e)) {
                    $attempt++;
                    // Exponential backoff with full jitter: base doubles each
                    // attempt; actual sleep is uniform in [0, base_ms * 1000 μs].
                    $baseUs = $retryDelayMs * 1000 * (1 << ($attempt - 1));
                    usleep(random_int(0, $baseUs));
                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * Whether a transaction failure is a transient write-conflict worth
     * retrying. Scoped to SqlQueryException: a stray RuntimeException from
     * inside the callable that happens to carry a numeric code equal to
     * 1213 or 1180 is not a database retry signal and must propagate. The
     * caller must have produced the exception via one of QueryUtils's
     * SQL execution paths (or constructed a SqlQueryException directly)
     * for retry to be considered.
     *
     * Within SqlQueryException, checks the exception's code AND a
     * message-substring heuristic so we catch both the common
     * standard-deadlock path (code 1213 with any message) and the
     * Galera-specific certification-failure path (code 1180 with a
     * WSREP-mentioning message).
     *
     * Non-retryable by design: 1205 ER_LOCK_WAIT_TIMEOUT (the holder isn't
     * going to release just because we retry), constraint violations,
     * syntax errors, disk-full, etc.
     *
     * Public so callers that build their own transaction loops (e.g.
     * GaclApi's hand-rolled BeginTrans / CommitTrans / RollbackTrans
     * sequences that haven't yet migrated to inTransaction()) can
     * participate in the same retry policy without duplicating the
     * decision logic.
     */
    public static function isRetryableWriteConflict(Throwable $e): bool
    {
        if (!($e instanceof SqlQueryException)) {
            return false;
        }
        $code = $e->getCode();
        if ($code === self::RETRYABLE_SQLSTATE_DEADLOCK) {
            return true;
        }
        if ($code === self::RETRYABLE_SQLSTATE_COMMIT_ERROR) {
            // 1180 is broad; require the message to identify a Galera
            // certification-level conflict before treating as retryable.
            $message = $e->getMessage();
            if (stripos($message, 'wsrep') !== false
                || stripos($message, 'certification') !== false
                || stripos($message, 'deadlock') !== false
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public static function getLastInsertId()
    {
        // Return the correct last id generated using function
        //   that is safe with the audit engine.
        return (OEGlobalsBag::getInstance()->get('lastidado') ?? 0) > 0 ? OEGlobalsBag::getInstance()->get('lastidado') : OEGlobalsBag::getInstance()->get('adodb')['db']->Insert_ID();
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
            ServiceContainer::getLogger()->debug('Non-array $params passed to {method}: {trace}', [
                'method' => __METHOD__,
                'trace' => (new \Exception())->getTraceAsString(),
            ]);
        }
        $result = self::sqlStatementThrowException($sql, $params, noLog: !$log);
        return self::fetchArrayFromResultSet($result);
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
        return !empty(OEGlobalsBag::getInstance()->get('last_mysql_error'))
            ? (string) OEGlobalsBag::getInstance()->get('last_mysql_error')
            : (string) OEGlobalsBag::getInstance()->get('adodb')['db']->ErrorMsg();
    }

    /**
     * MySQL / MariaDB native error code from the most recent failing query.
     * Preferred over parsing getLastError()'s string: the code is stable
     * across locales, driver versions, and MariaDB-version-specific message
     * phrasing. Used by inTransaction()'s retry logic to decide whether a
     * failure is a transient write-conflict (deadlock / Galera cert
     * failure) or a fatal error (constraint violation, syntax, etc.).
     *
     * Returns 0 when no DB error code is available (e.g. the failure
     * originated before the query reached the server, or the ADODB bag
     * is unavailable). Callers should NOT treat 0 as success.
     */
    public static function getLastErrorCode(): int
    {
        if (!function_exists('sqlQuery')) {
            // adodb bootstrap hasn't loaded (unit tests that only
            // exercise this helper's branches), nothing to query.
            return 0;
        }
        return (int) self::getADODB()->ErrorNo();
    }

    /**
     * @return \ADODB_mysqli_log
     */
    private static function getADODB()
    {
        if (!function_exists('sqlQuery')) {
            // GROSS HACK: if if the first query run goes through QueryUtils
            // rather than the global functions, the connection may not have
            // been set up yet.
            require_once __DIR__ . '/../../../library/sql.inc.php';
        }

        return OEGlobalsBag::getInstance()->get('adodb')['db'];
    }
}
