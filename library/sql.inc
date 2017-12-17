<?php
/**
* Sql functions/classes for OpenEMR.
*
* Includes classes and functions that OpenEMR uses
* to interact with SQL.
*
* LICENSE: This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @link      http://www.open-emr.org
*/

require_once(dirname(__FILE__) . "/sqlconf.php");
require_once(dirname(__FILE__) . "/../vendor/adodb/adodb-php/adodb.inc.php");
require_once(dirname(__FILE__) . "/../vendor/adodb/adodb-php/drivers/adodb-mysqli.inc.php");
require_once(dirname(__FILE__) . "/log.inc");
require_once(dirname(__FILE__) . "/ADODB_mysqli_log.php");

if (!defined('ADODB_FETCH_ASSOC')) {
    define('ADODB_FETCH_ASSOC', 2);
}

$database = NewADOConnection("mysqli_log"); // Use the subclassed driver which logs execute events
// Below optionFlags flag is telling the mysql connection to ensure local_infile setting,
// which is needed to import data in the Administration->Other->External Data Loads feature.
// (Note the MYSQLI_READ_DEFAULT_GROUP is just to keep the current setting hard-coded in adodb)
$database->optionFlags = array(array(MYSQLI_READ_DEFAULT_GROUP,0), array(MYSQLI_OPT_LOCAL_INFILE,1));
// Set mysql to use ssl, if applicable.
// Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
// Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
    if (defined('MYSQLI_CLIENT_SSL')) {
        $database->clientFlags = MYSQLI_CLIENT_SSL;
    }
}
$database->port = $port;
$database->PConnect($host, $login, $pass, $dbase);
$GLOBALS['adodb']['db'] = $database;
$GLOBALS['dbh'] = $database->_connectionID;

// Modified 5/2009 by BM for UTF-8 project ---------
if (!$disable_utf8_flag) {
    $success_flag = $database->Execute("SET NAMES 'utf8'");
    if (!$success_flag) {
        error_log("PHP custom error: from openemr library/sql.inc  - Unable to set up UTF8 encoding with mysql database: ".getSqlLastError(), 0);
    }
}

// Turn off STRICT SQL
$sql_strict_set_success = $database->Execute("SET sql_mode = ''");
if (!$sql_strict_set_success) {
    error_log("Unable to set strict sql setting: ".getSqlLastError(), 0);
}

// set up associations in adodb calls (not sure why above define
//  command does not work)
$GLOBALS['adodb']['db']->SetFetchMode(ADODB_FETCH_ASSOC);

if ($GLOBALS['debug_ssl_mysql_connection']) {
    error_log("CHECK SSL CIPHER IN MAIN ADODB: " . print_r($GLOBALS['adodb']['db']->ExecuteNoLog("SHOW STATUS LIKE 'Ssl_cipher';")->fields, true));
}

//fmg: This makes the login screen informative when no connection can be made
if (!$GLOBALS['dbh']) {
  //try to be more helpful
    if ($host == "localhost") {
        echo "Check that mysqld is running.<p>";
    } else {
        echo "Check that you can ping the server " . text($host) . ".<p>";
    }//if local
    HelpfulDie("Could not connect to server!", getSqlLastError());
    exit;
}//if no connection

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
function sqlStatementNoLog($statement, $binds = false)
{
  // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

  // Use adodb ExecuteNoLog with binding and return a recordset.
    $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);
    if ($recordset === false) {
        HelpfulDie("query failed: $statement", getSqlLastError());
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

    if ($r->EOF) {
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
    $recordset = $GLOBALS['adodb']['db']->Execute($statement, $binds);
    if ($recordset === false) {
        HelpfulDie("insert failed: $statement", getSqlLastError());
    }

  // Return the correct last id generated using function
  //   that is safe with the audit engine.
    return getSqlLastID();
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
* @param  string  $statement  query
* @param  array   $binds      binded variables array (optional)
* @return array
*/
function sqlQueryNoLog($statement, $binds = false)
{
  // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);

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
function sqlInsertClean_audit($statement)
{

    $ret = $GLOBALS['adodb']['db']->ExecuteNoLog($statement);
    if ($ret === false) {
        HelpfulDie("insert failed: $statement", getSqlLastError());
    }
}

/**
* Function that will safely return the last ID inserted,
* and accounts for the audit engine.
*
* @return  integer Last ID that was inserted into sql
*/
function getSqlLastID()
{
    return $GLOBALS['lastidado'] > 0 ? $GLOBALS['lastidado'] : $GLOBALS['adodb']['db']->Insert_ID();
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
    $sql = "SHOW COLUMNS FROM ". add_escape_custom($table);
    $resource = sqlQ($sql);
    $field_list = array();
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
function HelpfulDie($statement, $sqlerr = '')
{

    echo "<h2><font color='red'>" . xlt('Query Error') . "</font></h2>";

    if (!$GLOBALS['sql_string_no_show_screen']) {
        echo "<p><font color='red'>ERROR:</font> " . text($statement) . "</p>";
    }

    $logMsg="SQL Error with statement:".$statement;

    if ($sqlerr) {
        if (!$GLOBALS['sql_string_no_show_screen']) {
             echo "<p>Error: <font color='red'>" . text($sqlerr) . "</font></p>";
        }

        $logMsg.="--".$sqlerr;
    }//if error

    $backtrace = debug_backtrace();

    if (!$GLOBALS['sql_string_no_show_screen']) {
        for ($level = 1; $level < count($backtrace); $level++) {
            $info = $backtrace[$level];
            echo "<br>" . text($info["file"] . " at " . $info["line"] . ":" . $info["function"]);
            if ($level > 1) {
                echo "(" . text(implode(",", $info["args"])) . ")";
            }
        }
    }

    $logMsg.="==>".$backtrace[1]["file"]." at ".$backtrace[1]["line"].":".$backtrace[1]["function"];

    error_log($logMsg);

    exit;
}

/**
* @todo document use of the generate_id function
*/
function generate_id()
{
    $database = $GLOBALS['adodb']['db'];
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
* Simple wrapper for sqlInsert() function (deprecated).
*
* Function that will allow use of the adodb binding feature
* to prevent sql-injection.
*
* @deprecated
* @param  string   $statement  query
* @param  array    $binds      binded variables array (optional)
* @return integer  Last id generated from the sql insert command
*/
function idSqlStatement($statement, $binds = false)
{
  // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    return sqlInsert($statement, $binds);
}

/**
* Simple wrapper for sqlInsert() function (deprecated).
*
* Function that will allow use of the adodb binding feature
* to prevent sql-injection.
*
* @deprecated
* @param  string   $statement  query
* @param  array    $binds      binded variables array (optional)
* @return integer  Last id generated from the sql insert command
*/
function sqlInsertClean($statement, $binds = false)
{
  // Below line is to avoid a nasty bug in windows.
    if (empty($binds)) {
        $binds = false;
    }

    return sqlInsert($statement, $binds);
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
function generic_sql_select_db($database, $link = null)
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
    return mysqli_affected_rows($GLOBALS['dbh']);
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
function sqlBeginTrans()
{
    $GLOBALS['adodb']['db']->BeginTrans();
}


/**
 * Commit a transaction
 */
function sqlCommitTrans($ok = true)
{
    $GLOBALS['adodb']['db']->CommitTrans();
}


/**
 * Rollback a transaction
 */
function sqlRollbackTrans()
{
    $GLOBALS['adodb']['db']->RollbackTrans();
}
