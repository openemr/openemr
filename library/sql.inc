<?php

include_once(dirname(__FILE__) . "/sqlconf.php");
require_once(dirname(__FILE__) . "/adodb/adodb.inc.php");
include_once(dirname(__FILE__) . "/log.inc");

if (!defined('ADODB_FETCH_ASSOC')) define('ADODB_FETCH_ASSOC', 2);
$database = NewADOConnection("mysql");
$database->PConnect($host, $login, $pass, $dbase);
$GLOBALS['adodb']['db'] = $database;
$GLOBALS['dbh'] = $database->_connectionID;

// Modified 5/2009 by BM for UTF-8 project ---------
if (!$disable_utf8_flag) {
 $success_flag = $database->Execute("SET NAMES 'utf8'");
  if (!$success_flag) {
   error_log("PHP custom error: from openemr library/sql.inc  - Unable to set up UTF8 encoding with mysql database: ".$database->ErrorMsg(), 0);
  }
}

// set up associations in adodb calls (not sure why above define
//  command does not work)
$GLOBALS['adodb']['db']->SetFetchMode(ADODB_FETCH_ASSOC);

//fmg: This makes the login screen informative when no connection can be made
if (!$GLOBALS['dbh']) {
  //try to be more helpful
  if ($host == "localhost") {
    echo "Check that mysqld is running.<p>";
  } else {
    echo "Check that you can ping the server '$host'.<p>";
  }//if local
  HelpfulDie("Could not connect to server!", mysql_error($GLOBALS['dbh']));
  exit;
}//if no connection

// Function that will allow use of the adodb binding
//   feature to prevent sql-injection. Will continue to
//   be compatible with previous function calls that do
//   not use binding.
// If use adodb binding, then will return a recordset object.
// If do not use binding, then will return a resource object.
// The sqlFetchArray() function should be used to
//   utilize the return object (it will accept both recordset
//   and resource objects).
function sqlStatement($statement, $binds=NULL )
{
  if (is_array($binds)) {
    // Use adodb Execute with binding and return a recordset.
    //   Note that the auditSQLEvent function is embedded
    //    in the adodb Execute command.
    $recordset = $GLOBALS['adodb']['db']->Execute( $statement, $binds );
    if ($recordset === FALSE) {
      HelpfulDie("query failed: $statement", $GLOBALS['adodb']['db']->ErrorMsg());
    }
    return $recordset;
  }
  else {
    // Use mysql_query and return a resource.
    $resource = mysql_query($statement, $GLOBALS['dbh']);
    if ($resource === FALSE) {
      auditSQLEvent($statement, FALSE);
      HelpfulDie("query failed: $statement", mysql_error($GLOBALS['dbh']));
    }
    auditSQLEvent($statement, TRUE);
    return $resource;
  }
}

// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// It will act upon the object returned from the 
//   sqlStatement() function (and sqlQ() function).
// It will automatically figure out if the input
//   object is a recordset or a resource.
function sqlFetchArray($r)
{
  if (!is_resource($r)) {
    //treat as an adodb recordset
    if ($r === FALSE)
      return false;
    if ($r->EOF)
      return false;
    //ensure it's an object (ie. is set)
    if (!is_object($r))
      return false;
    return $r->FetchRow();
  }
  else {
    //treat as a mysql_query resource
    if ($r == FALSE)
      return false;
    return mysql_fetch_array($r, MYSQL_ASSOC);
  }
}

// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// This function is specialized for insert functions
//   and will return the last id generated from the
//   insert.
function sqlInsert($statement, $binds=array())
{
  //Run a adodb execute
  // Note the auditSQLEvent function is embedded in the
  //   adodb Execute function.
  $recordset = $GLOBALS['adodb']['db']->Execute($statement, $binds);
  if ($recordset === FALSE) {
    HelpfulDie("insert failed: $statement", $GLOBALS['adodb']['db']->ErrorMsg());
  }
  // Return the correct last id generated using function
  //   that is safe with the audit engine.
  return getSqlLastID();
}

// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// This function is specialized for simply returning
//   the first row of a query as an associative array.
// The $status_or_binds parameter can do following:
//   FALSE or an array() - Run adodb Execute with binding,
//     if applicable.
//   TRUE - run mysql_query for a specific case in the audit engine
//     (library/log.inc) to skip auditing and reporting if the query
//     does not work.
function sqlQuery($statement, $status_or_binds=FALSE)
{
  if ((is_array($status_or_binds)) || ($status_or_binds === FALSE)) {
    // run the adodb Execute function
    //   Note the auditSQLEvent function is embedded in the
    //     adodb Execute function.
    if (is_array($status_or_binds)) {
      $recordset = $GLOBALS['adodb']['db']->Execute( $statement, $status_or_binds );
    }
    else {
      $recordset = $GLOBALS['adodb']['db']->Execute( $statement );
    }
    if ($recordset === FALSE) {
      HelpfulDie("query failed: $statement", $GLOBALS['adodb']['db']->ErrorMsg());
    }
    if ($recordset->EOF)
     return FALSE;
    $rez = $recordset->FetchRow();
    if ($rez == FALSE)
      return FALSE;
    return $rez;
  }
  else {
    // run mysql_query to bypass the audit engine if error
    //   in query.
    $resource = mysql_query($statement, $GLOBALS['dbh']);
    auditSQLEvent($statement, TRUE);
   //$rez = @mysql_fetch_array($query, MYSQL_ASSOC);
    $rez = @mysql_fetch_array($resource, MYSQL_ASSOC); //Replace $query with $resource to calculate correct checksum
    if ($rez == FALSE)
      return FALSE;
    return $rez;
  }
}

// ViSolve: Create a seperate function for audit log calls.
// The audit calls are handled through separate function.
// Before the audit calls are executed, the mysql_insert_id() is stored in GLOBALS['lastiddao']
//  and used in the relevant places, i.e in the mysql adodb driver files..
// Note:  This GLOBAL variable gets its value only when the audit is enabled.
function sqlInsertClean_audit($statement)
{
  // Get the mysql_insert_id() before the execution of the log statement
 //$lastid=mysql_insert_id($GLOBALS['dbh']);
 //$GLOBALS['lastidado']=$lastid;
// when audit is enabled and $GLOBALS['lastidado'] is not set, then set the GLOBALS irrespective of dbh and adodb handlers.

// ViCarePlus :: Fix for Audit logging Issue :: Sep 24,2010 .
// Issue:: The LAST_INSERT_ID of the audit log table overrides the value in the $GLOBALS['lastidado'] variable. 
//         In a single transaction, when more than one query is being executed, this issue happens. 
// Fix: This code has been taken care by the function auditSQLEvent() in log.inc file
//  if ($GLOBALS['lastidado'] > 0)
//	{ /* do nothing */ }
//        else {
//                 $lastid=mysql_insert_id($GLOBALS['dbh']);
//                 $GLOBALS['lastidado']=$lastid;
//        }

  //----------run a mysql insert, return the last id generated
  $ret = mysql_query($statement, $GLOBALS['dbh']);
  if ($ret === FALSE) {
    HelpfulDie("insert failed: $statement", mysql_error($GLOBALS['dbh']));
  }
}

// Function that will safely return the last
//   ID inserted, and accounts for
//   the audit engine.
function getSqlLastID() {
  if ($GLOBALS['lastidado'] >0) {
    return $GLOBALS['lastidado'];
  }
  else {
    return $GLOBALS['adodb']['db']->Insert_ID();
  }
}

// Function that will return an array listing
//   of columns that exist in a table.
function sqlListFields($table) {
  $sql = "SHOW COLUMNS FROM ". mysql_real_escape_string($table);
  $resource = sqlQ($sql);
  $field_list = array();
  while($row = mysql_fetch_array($resource)) {
    $field_list[] = $row['Field'];
  }
  return $field_list;
}

// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// It will act upon the object returned from the
//   sqlStatement() function (and sqlQ() function).
// It will automatically figure out if the input
//   object is a recordset or a resource.
// It will return the number of rows.
function sqlNumRows($r)
{
  if (!is_resource($r)) {
    //treat as an adodb recordset
    return $r->RecordCount();
  }
  else {
    //treat as a mysql_query resource
    return mysql_num_rows($r);
  }
}

//fmg: Much more helpful that way...
function HelpfulDie ($statement, $sqlerr='')
{
  echo "<p><p><font color='red'>ERROR:</font> $statement<p>";
  if ($sqlerr) {
    echo "Error: <font color='red'>$sqlerr</font><p>";
  }//if error
  exit;
}

function generate_id () {
  $database = $GLOBALS['adodb']['db'];
  return $database->GenID("sequences");
}

// Does not fully incorporate the audit engine, so
//   recommend not using this function (if bind is set,
//   then will get logged, however if bind is not set,
//   then will not get logged).  
// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// Function that will allow use of the adodb binding
//   feature to prevent sql-injection. Will continue to
//   be compatible with previous function calls that do
//   not use binding.
// If use adodb binding, then will return a recordset object.
// If do not use binding, then will return a resource object.
// The sqlFetchArray() function should be used to
//   utilize the return object (it will accept both recordset
//   and resource objects).
function sqlQ($statement, $binds=NULL )
{
  if (is_array($binds)) {
    $recordset = $GLOBALS['adodb']['db']->Execute( $statement, $binds ) or
      HelpfulDie("query failed: $statement", $GLOBALS['adodb']['db']->ErrorMsg());
    return $recordset;
  }
  else {
    $resource = mysql_query($statement, $GLOBALS['dbh']) or
      HelpfulDie("query failed: $statement", mysql_error($GLOBALS['dbh']));
    return $resource;
  }
}

// DEPRECATED FUNCTION
//
// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// A simple wrapper for the sqlInsert() function.
function idSqlStatement($statement , $binds=NULL )
{
  return sqlInsert($statement, $binds);
}

// DEPRECATED FUNCTION
//
// Function that will allow use of the adodb binding
//   feature to prevent sql-injection.
// A simple wrapper for the sqlInsert() function.
function sqlInsertClean($statement, $binds=NULL )
{
  return sqlInsert($statement, $binds);
}

// DEPRECATED FUNCTION
function sqlConnect($login,$pass,$dbase,$host,$port = '3306')
{
  $GLOBALS['dbh'] = $database->_connectionID;
  return $GLOBALS['dbh'];
}

// DEPRECATED FUNCTION
//
// Function to close the connection. PHP does
//   this automatically, so not needed.
function sqlClose()
{
  //----------Close our mysql connection
  $closed = $GLOBALS['adodb']['db']->close or
    HelpfulDie("could not disconnect from mysql server link", $GLOBALS['adodb']['db']->ErrorMsg());
  return $closed;
}

// DEPRECATED FUNCTION
function get_db() {
  return $GLOBALS['adodb']['db'];
}

// DEPRECATED FUNCTION
function sqlLastID() {
  return mysql_insert_id($GLOBALS['dbh']);
}

?>
