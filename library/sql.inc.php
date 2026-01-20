<?php

/**
 * Sql functions/classes for OpenEMR.
 *
 * Includes classes and functions that OpenEMR uses
 * to interact with SQL.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/sqlconf.php");

use OpenEMR\Common\Session\SessionWrapperFactory;
$session = SessionWrapperFactory::getInstance()->getWrapper();

/**
 * Variables set by sqlconf.php or SqlConfigEvent
 *
 * @var string $host
 * @var string $port
 * @var string $login
 * @var string $pass
 * @var string $dbase
 * @var bool $disable_utf8_flag
 * @var string $secure_host
 * @var string $secure_port
 * @var string $secure_login
 * @var string $secure_pass
 * @var string $secure_dbase
 */

require_once(__DIR__ . "/../vendor/adodb/adodb-php/adodb.inc.php");
require_once(__DIR__ . "/../vendor/adodb/adodb-php/drivers/adodb-mysqli.inc.php");
require_once(__DIR__ . "/ADODB_mysqli_log.php");

if (!defined('ADODB_FETCH_ASSOC')) {
    define('ADODB_FETCH_ASSOC', 2);
}
// Our ADODB driver is already loaded.
// This prevents ADODB trying to find and
// load it again from the wrong place.
$ADODB_LASTDB = 'mysqli_log';

// Skip database connection during static analysis
// The OPENEMR_STATIC_ANALYSIS constant can be defined in static analysis tool bootstrap files
if (!defined('OPENEMR_STATIC_ANALYSIS') || !OPENEMR_STATIC_ANALYSIS) {
    $database = NewADOConnection("mysqli_log"); // Use the subclassed driver which logs execute events
// Below optionFlags flag is telling the mysql connection to ensure local_infile setting,
// which is needed to import data in the Administration->Other->External Data Loads feature.
// (Note the MYSQLI_READ_DEFAULT_GROUP is just to keep the current setting hard-coded in adodb)
    $database->setConnectionParameter(MYSQLI_READ_DEFAULT_GROUP, 0);
    $database->setConnectionParameter(MYSQLI_OPT_LOCAL_INFILE, 1);
// Set mysql to use ssl, if applicable.
// Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
// Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
    if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
        if (defined('MYSQLI_CLIENT_SSL')) {
            if (
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
            ) {
                // with client side certificate/key
                $database->ssl_key = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-key";
                $database->ssl_cert = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-cert";
                $database->ssl_ca = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca";
            } else {
                // without client side certificate/key
                $database->ssl_ca = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca";
            }
            $database->clientFlags = MYSQLI_CLIENT_SSL;
        }
    }
    $database->port = $port;
    if ((!empty($GLOBALS["enable_database_connection_pooling"]) || !empty($session->get("enable_database_connection_pooling"))) && empty($GLOBALS['connection_pooling_off'])) {
        $database->PConnect($host, $login, $pass, $dbase);
    } else {
        $database->connect($host, $login, $pass, $dbase);
    }
    $GLOBALS['adodb']['db'] = $database;
    $GLOBALS['dbh'] = $database->_connectionID;

// This makes the login screen informative when no connection can be made
    if (!$GLOBALS['dbh']) {
        if ($host === "localhost") {
            echo "Check that mysqld is running.<p>";
        } else {
            echo "Check that you can ping the server " . text($host) . ".<p>";
        }
        HelpfulDie("Could not connect to server!", getSqlLastError());
    }

// Modified 5/2009 by BM for UTF-8 project ---------
    if (!$disable_utf8_flag) {
        if (!empty($sqlconf["db_encoding"]) && ($sqlconf["db_encoding"] == "utf8mb4")) {
            $success_flag = $database->ExecuteNoLog("SET NAMES 'utf8mb4'");
            if (!$success_flag) {
                error_log("PHP custom error: from openemr library/sql.inc.php  - Unable to set up UTF8MB4 encoding with mysql database: " . errorLogEscape(getSqlLastError()), 0);
            }
        } else {
            $success_flag = $database->ExecuteNoLog("SET NAMES 'utf8'");
            if (!$success_flag) {
                error_log("PHP custom error: from openemr library/sql.inc.php  - Unable to set up UTF8 encoding with mysql database: " . errorLogEscape(getSqlLastError()), 0);
            }
        }
    }

// Turn off STRICT SQL
    $sql_strict_set_success = $database->ExecuteNoLog("SET sql_mode = ''");
    if (!$sql_strict_set_success) {
        error_log("Unable to set strict sql setting: " . errorLogEscape(getSqlLastError()), 0);
    }

// set up associations in adodb calls (not sure why above define
//  command does not work)
    $GLOBALS['adodb']['db']->SetFetchMode(ADODB_FETCH_ASSOC);

    if (!empty($GLOBALS['debug_ssl_mysql_connection'])) {
        error_log("CHECK SSL CIPHER IN MAIN ADODB: " . errorLogEscape(print_r($GLOBALS['adodb']['db']->ExecuteNoLog("SHOW STATUS LIKE 'Ssl_cipher';")->fields, true)));
    }
} // End of OPENEMR_STATIC_ANALYSIS guard


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
* @return recordset
*/
function sqlStatement($statement, $binds = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    // Use adodb Execute with binding and return a recordset.
    //   Note that the auditSQLEvent function is embedded
    //    in the Execute command.
    $recordset = $GLOBALS['adodb']['db']->Execute($statement, $binds);
    if ($recordset === false) {
        HelpfulDie("query failed: $statement", getSqlLastError());
    }

    return $recordset;
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
 * @return recordset
 */
function sqlStatementThrowException($statement, $binds = false)
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
        throw new \OpenEMR\Common\Database\SqlQueryException($statement, "Failed to execute statement. Error: " . getSqlLastError() . " Statement: " . $statement);
    }
    return $recordset;
}

/**
 * Return the last inserted sql id for a query.
 * @return int
 */
function sqlGetLastInsertId()
{
    // Return the correct last id generated using function
    //   that is safe with the audit engine.
    return ($GLOBALS['lastidado'] ?? 0) > 0 ? $GLOBALS['lastidado'] : $GLOBALS['adodb']['db']->Insert_ID();
}

/**
* Specialized sql query in OpenEMR that skips auditing.
*
* Function that will allow use of the adodb binding
* feature to prevent sql-injection. Will continue to
* be compatible with previous function calls that do
* not use binding. It is equivalent to the
* sqlStatement() function, EXCEPT it skips the
* audit engine. This function should only be used
* in very special situations.
* It will return a recordset object.
* The sqlFetchArray() function should be used to
* utilize the return object.
*
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return recordset
*/
function sqlStatementNoLog($statement, $binds = false, $throw_exception_on_error = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    // Use adodb ExecuteNoLog with binding and return a recordset.
    $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);
    if ($recordset === false) {
        if ($throw_exception_on_error) {
            throw new \OpenEMR\Common\Database\SqlQueryException($statement, "Failed to execute statement. Error: " . getSqlLastError() . " Statement: " . $statement);
        } else {
            HelpfulDie("query failed: $statement", getSqlLastError());
        }
    }

    return $recordset;
}

/**
* sqlStatement() function wrapper for CDR engine in OpenEMR.
* Allows option to turn on/off auditing specifically for the
* CDR engine.
*
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return recordset/resource
*/
function sqlStatementCdrEngine($statement, $binds = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    if ($GLOBALS['audit_events_cdr']) {
        return sqlStatement($statement, $binds);
    } else {
        return sqlStatementNoLog($statement, $binds);
    }
}

/**
* Returns a row (as an array) from a sql recordset.
*
* Function that will allow use of the adodb binding
* feature to prevent sql-injection.
* It will act upon the object returned from the
* sqlStatement() function (and sqlQ() function).
*
* @param recordset $r
* @return array
*/
function sqlFetchArray($r)
{
    //treat as an adodb recordset
    if ($r === false) {
        return false;
    }

    if ($r->EOF ?? '') {
        return false;
    }

    //ensure it's an object (ie. is set)
    if (!is_object($r)) {
        return false;
    }

    return $r->FetchRow();
}


/**
 * Wrapper for ADODB getAssoc
 *
 * @see http://adodb.org/dokuwiki/doku.php?id=v5:reference:connection:getassoc
 *
 * @param string $sql
 * @param string[] $bindvars
 * @param boolean $forceArray
 * @param boolean $first2Cols
 * @return array
 */
function sqlGetAssoc($sql, $bindvars = false, $forceArray = false, $first2Cols = false)
{

    return $GLOBALS['adodb']['db']->getAssoc($sql, $bindvars, $forceArray, $first2Cols);
}

/**
* Standard sql insert query in OpenEMR.
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
* @return integer  Last id generated from the sql insert command
*/
function sqlInsert($statement, $binds = false)
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
        HelpfulDie("insert failed: $statement", getSqlLastError());
    }

    // Return the correct last id generated using function
    //   that is safe with the audit engine.
    return $GLOBALS['lastidado'] > 0 ? $GLOBALS['lastidado'] : $GLOBALS['adodb']['db']->Insert_ID();
}

/**
* Specialized sql query in OpenEMR that only returns
* the first row of query results as an associative array.
*
* Function that will allow use of the adodb binding
* feature to prevent sql-injection.
*
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return array
*/
function sqlQuery($statement, $binds = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    $recordset = $GLOBALS['adodb']['db']->Execute($statement, $binds);

    if ($recordset === false) {
        HelpfulDie("query failed: $statement", getSqlLastError());
    }

    if ($recordset->EOF) {
        return false;
    }

    $rez = $recordset->FetchRow();
    if ($rez == false) {
        return false;
    }

    return $rez;
}

/**
* Specialized sql query in OpenEMR that bypasses the auditing engine
* and only returns the first row of query results as an associative array.
*
* Function that will allow use of the adodb binding
* feature to prevent sql-injection. It is equivalent to the
* sqlQuery() function, EXCEPT it skips the
* audit engine. This function should only be used
* in very special situations.
*
* Note: If you do an INSERT or UPDATE statement you will get an empty string ("") as a response
*
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return array|false|""
*/
function sqlQueryNoLog($statement, $binds = false, $throw_exception_on_error = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);

    if ($recordset === false) {
        if ($throw_exception_on_error) {
            throw new \OpenEMR\Common\Database\SqlQueryException($statement, "Failed to execute statement. Error: " . getSqlLastError() . " Statement: " . $statement);
        } else {
            HelpfulDie("query failed: $statement", getSqlLastError());
        }
    }

    if ($recordset->EOF) {
        return false;
    }

    $rez = $recordset->FetchRow();
    if ($rez == false) {
        return false;
    }

    return $rez;
}

/**
* Specialized sql query in OpenEMR that ignores sql errors, bypasses the
* auditing engine and only returns the first row of query results as an
* associative array.
*
* Function that will allow use of the adodb binding
* feature to prevent sql-injection. It is equivalent to the
* sqlQuery() function, EXCEPT it skips the
* audit engine and ignores erros. This function should only be used
* in very special situations.
*
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return array
*/
function sqlQueryNoLogIgnoreError($statement, $binds = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);

    if ($recordset === false) {
        // ignore the error and return FALSE
        return false;
    }

    if ($recordset->EOF) {
        return false;
    }

    $rez = $recordset->FetchRow();
    if ($rez == false) {
        return false;
    }

    return $rez;
}

/**
* sqlQuery() function wrapper for CDR engine in OpenEMR.
* Allows option to turn on/off auditing specifically for the
* CDR engine.
*
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return array
*/
function sqlQueryCdrEngine($statement, $binds = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    if ($GLOBALS['audit_events_cdr']) {
        return sqlQuery($statement, $binds);
    } else {
        return sqlQueryNoLog($statement, $binds);
    }
}

/**
* Specialized sql query in OpenEMR that skips auditing.
*
* This function should only be used in very special situations.
*
* @param  string  $statement  query
*/
function sqlInsertClean_audit($statement, $binds = false): void
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    $ret = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);
    if ($ret === false) {
        HelpfulDie("insert failed: $statement", getSqlLastError());
    }
}

/**
* Function that will safely return the last error,
* and accounts for the audit engine.
*
* @param   string  $mode either adodb(default) or native_mysql
* @return  string        last mysql error
*/
function getSqlLastError()
{
    return !empty($GLOBALS['last_mysql_error']) ? $GLOBALS['last_mysql_error'] : $GLOBALS['adodb']['db']->ErrorMsg();
}

/**
 * Function that will safely return the last error no,
 * and accounts for the audit engine.
 *
 * @param   string  $mode either adodb(default) or native_mysql
 * @return  string        last mysql error no
 */
function getSqlLastErrorNo()
{
    return !empty($GLOBALS['last_mysql_error_no']) ? $GLOBALS['last_mysql_error_no'] : $GLOBALS['adodb']['db']->ErrorNo();
}

/**
* Function that will return an array listing
* of columns that exist in a table.
*
* @param   string  $table sql table
* @return  array
*/
function sqlListFields($table)
{
    $sql = "SHOW COLUMNS FROM " . add_escape_custom($table);
    $resource = sqlStatementNoLog($sql);
    $field_list = [];
    while ($row = sqlFetchArray($resource)) {
        $field_list[] = $row['Field'];
    }

    return $field_list;
}

/**
* Returns the number of sql rows
*
* @param recordset $r
* @return integer Number of rows
*/
function sqlNumRows($r)
{
    return $r->RecordCount();
}

/**
* Error function for OpenEMR sql functions
*
* @param string $statement
* @param string $sqlerr
*/
function HelpfulDie($statement, $sqlerr = ''): never
{

    echo "<h2><font color='red'>" . xlt('Query Error') . "</font></h2>";

    if (!($GLOBALS['sql_string_no_show_screen'] ?? '')) {
        echo "<p><font color='red'>ERROR:</font> " . text($statement) . "</p>";
    }

    $logMsg = "SQL Error with statement:" . $statement;

    if ($sqlerr) {
        if (!$GLOBALS['sql_string_no_show_screen'] ?? '') {
             echo "<p>Error: <font color='red'>" . text($sqlerr) . "</font></p>";
        }

        $logMsg .= "--" . $sqlerr;
    }//if error

    $backtrace = debug_backtrace();

    if (!$GLOBALS['sql_string_no_show_screen'] ?? '') {
        for ($level = 1; $level < count($backtrace); $level++) {
            $info = $backtrace[$level];
            echo "<br />" . text($info["file"] . " at " . $info["line"] . ":" . $info["function"]);
            if ($level > 1) {
                echo "(" . text(implode(",", $info["args"])) . ")";
            }
        }
    }

    $logMsg .= "==>" . $backtrace[1]["file"] . " at " . $backtrace[1]["line"] . ":" . $backtrace[1]["function"];

    error_log(errorLogEscape($logMsg));

    exit(1);
}

/**
* Function provides generation of sequence numbers with built-in ADOdb function.
* Increments the number in the sequences table.
* One example of use is the counter for form_id in the forms table.
*
* @return integer
*/
function generate_id()
{
    $database = $GLOBALS['adodb']['db'];
    // @phpstan-ignore openemr.deprecatedSqlFunction
    return $database->GenID("sequences");
}

/**
* Deprecated function. Standard sql query in OpenEMR.
*
* Function that will allow use of the adodb binding
* feature to prevent sql-injection. Will continue to
* be compatible with previous function calls that do
* not use binding.
* It will return a recordset object.
* The sqlFetchArray() function should be used to
* utilize the return object.
*
* @deprecated
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return recordset
*/
function sqlQ($statement, $binds = false)
{
    // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    $recordset = $GLOBALS['adodb']['db']->Execute($statement, $binds) or
    HelpfulDie("query failed: $statement", getSqlLastError());
    return $recordset;
}


/**
* Sql close connection function (deprecated)
*
* No longer needed since PHP does this automatically.
*
* @deprecated
* @return boolean
*/
function sqlClose()
{
  //----------Close our mysql connection
    $closed = $GLOBALS['adodb']['db']->close or
    HelpfulDie("could not disconnect from mysql server link", getSqlLastError());
    return $closed;
}

/**
* Very simple wrapper function and not necessary (deprecated)
*
* Do not use.
*
* @deprecated
* @return connection
*/
function get_db()
{
    return $GLOBALS['adodb']['db'];
}

/**
 * Generic mysql select db function
 * Used when converted to mysqli to centralize special circumstances.
 * @param string $database
 */
function generic_sql_select_db($database, $link = null): void
{
    if (is_null($link)) {
        $link = $GLOBALS['dbh'];
    }

    mysqli_select_db($link, $database);
}

/**
 * Generic mysql affected rows function
 * Used when converted to mysqli to centralize special circumstances.
 *
 */
function generic_sql_affected_rows()
{
    return $GLOBALS['adodb']['db']->affected_rows();
}

/**
 * Generic mysql insert id function
 * Used when converted to mysqli to centralize special circumstances.
 *
                 */
function generic_sql_insert_id()
{
    return mysqli_insert_id($GLOBALS['dbh']);
}


/**
 * Begin a Transaction.
 */
function sqlBeginTrans(): void
{
    $GLOBALS['adodb']['db']->BeginTrans();
}


/**
 * Commit a transaction
 */
function sqlCommitTrans($ok = true): void
{
    $GLOBALS['adodb']['db']->CommitTrans();
}


/**
 * Rollback a transaction
 */
function sqlRollbackTrans(): void
{
    $GLOBALS['adodb']['db']->RollbackTrans();
}

/**
 * For the 3 functions below:
 *
 * To support an optional higher level of security, queries that access password
 * related information use these functions instead of the standard functions
 * provided by sql.inc.php.
 *
 * By default, the privQuery and privStatement calls pass-through to
 * the existing ADODB instance initialized by sql.inc.php.
 *
 * If an additional configuration file is created (secure_sqlconf.php) and saved
 * in the sites/<sitename> directory (e.g. sites/default).  The MySQL login
 * information defined in that file as $secure_* will be used to create an ADODB
 * instance specifically for querying privileged information.
 *
 * By configuring a server in this way, the default MySQL user can be denied access
 * to sensitive tables (currently only "users_secure" would qualify).  Thus
 * the likelyhood of unintended modification can be reduced (e.g. through SQL Injection).
 *
 * Details on how to set this up are included in Documentation/privileged_db/priv_db_HOWTO
 *
 * The trade off for this additional security is extra complexity in configuration and
 * maintenance of the database, hence it is not enabled at install time and must be
 * done manually.
 *
 */
function getPrivDB()
{
    $session = SessionWrapperFactory::getInstance()->getWrapper();
    if (!isset($GLOBALS['PRIV_DB'])) {
        $secure_config = $GLOBALS['OE_SITE_DIR'] . "/secure_sqlconf.php";
        if (file_exists($secure_config)) {
            require_once($secure_config);
            /**
             * Variables set by secure_sqlconf.php
             *
             * @var string $secure_host
             * @var string $secure_port
             * @var string $secure_login
             * @var string $secure_pass
             * @var string $secure_dbase
             */
            $GLOBALS['PRIV_DB'] = NewADOConnection("mysqli_log"); // Use the subclassed driver which logs execute events
            // Below optionFlags flag is telling the mysql connection to ensure local_infile setting,
            // which is needed to import data in the Administration->Other->External Data Loads feature.
            // (Note the MYSQLI_READ_DEFAULT_GROUP is just to keep the current setting hard-coded in adodb)
            $GLOBALS['PRIV_DB']->setConnectionParameter(MYSQLI_READ_DEFAULT_GROUP, 0);
            $GLOBALS['PRIV_DB']->setConnectionParameter(MYSQLI_OPT_LOCAL_INFILE, 1);
            // Set mysql to use ssl, if applicable.
            // Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
            // Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
            if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
                if (defined('MYSQLI_CLIENT_SSL')) {
                    if (
                        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
                        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
                    ) {
                        // with client side certificate/key
                        $GLOBALS['PRIV_DB']->ssl_key = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-key";
                        $GLOBALS['PRIV_DB']->ssl_cert = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-cert";
                        $GLOBALS['PRIV_DB']->ssl_ca = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca";
                    } else {
                        // without client side certificate/key
                        $GLOBALS['PRIV_DB']->ssl_ca = "{$GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca";
                    }
                    $GLOBALS['PRIV_DB']->clientFlags = MYSQLI_CLIENT_SSL;
                }
            }
            // $port variable is from main sqlconf.php (global scope)
            global $port;
            $GLOBALS['PRIV_DB']->port = $port;
            if ((!empty($GLOBALS["enable_database_connection_pooling"]) || !empty($session->get("enable_database_connection_pooling"))) && empty($GLOBALS['connection_pooling_off'])) {
                $GLOBALS['PRIV_DB']->PConnect($secure_host, $secure_login, $secure_pass, $secure_dbase);
            } else {
                $GLOBALS['PRIV_DB']->connect($secure_host, $secure_login, $secure_pass, $secure_dbase);
            }
            // set up associations in adodb calls
            $GLOBALS['PRIV_DB']->SetFetchMode(ADODB_FETCH_ASSOC);
            // debug hook for ssl stuff
            if (!empty($GLOBALS['debug_ssl_mysql_connection'])) {
                error_log("CHECK SSL CIPHER IN PRIV_DB ADODB: " . errorLogEscape(print_r($GLOBALS['PRIV_DB']->ExecuteNoLog("SHOW STATUS LIKE 'Ssl_cipher';")->fields, true)));
            }
        } else {
            $GLOBALS['PRIV_DB'] = $GLOBALS['adodb']['db'];
        }
    }

    return $GLOBALS['PRIV_DB'];
}
/**
 * mechanism to use "super user" for SQL queries related to password operations
 *
 * @param type $sql
 * @param type $params
 * @return type
 */
function privStatement($sql, $params = null)
{
    $recordset = is_array($params) ? getPrivDB()->ExecuteNoLog($sql, $params) : getPrivDB()->ExecuteNoLog($sql);

    if ($recordset === false) {
        // These error messages are explictly NOT run through xl() because we still
        // need them if there is a database problem.
        echo "Failure during database access! Check server error log.";
        $backtrace = debug_backtrace();

        error_log("Executing as user:" . errorLogEscape(getPrivDB()->user) . " Statement failed:" . errorLogEscape($sql) . ":" . errorLogEscape($GLOBALS['last_mysql_error'])
            . "==>" . errorLogEscape($backtrace[1]["file"]) . " at " . errorLogEscape($backtrace[1]["line"]) . ":" . errorLogEscape($backtrace[1]["function"]));
        exit(1);
    }

    return $recordset;
}
/**
 *
 * Wrapper for privStatement that just returns the first row of a query or FALSE
 * if there were no results.
 *
 * @param type $sql
 * @param type $params
 * @return boolean
 */
function privQuery($sql, $params = null)
{
    $recordset = privStatement($sql, $params);
    if ($recordset->EOF) {
        return false;
    }

    $rez = $recordset->FetchRow();
    if ($rez == false) {
        return false;
    }

    return $rez;
}


/**
* Function provides generation of sequence numbers with built-in ADOdb function.
* Increments the number in the edi_sequences table.
* One example of use is the counter for batches in the 837 claims creation.
*
* @return integer
*/
function edi_generate_id()
{
    $database = $GLOBALS['adodb']['db'];
    // @phpstan-ignore openemr.deprecatedSqlFunction
    return $database->GenID("edi_sequences");
}
