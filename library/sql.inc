<?php

include_once(dirname(__FILE__) . "/sqlconf.php");
require_once(dirname(__FILE__) . "/adodb/adodb.inc.php");

if (!defined('ADODB_FETCH_ASSOC')) define('ADODB_FETCH_ASSOC', 2);
$database = NewADOConnection("mysql");
$database->PConnect($host, $login, $pass, $dbase);
$GLOBALS['adodb']['db'] = $database;
$GLOBALS['dbh'] = $database->_connectionID;

//set UTF8 encoding if not disabled in globals.php
if (!$disable_utf8_flag) mysql_query("SET NAMES 'utf8'", $GLOBALS['dbh']);

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

function sqlConnect($login,$pass,$dbase,$host,$port = '3306')
{
  $GLOBALS['dbh'] = $database->_connectionID;
  //set UTF8 encoding if not disabled in globals.php
  if (!$disable_utf8_flag) mysql_query("SET NAMES 'utf8'", $GLOBALS['dbh']);
  return $GLOBALS['dbh'];
}

function sqlStatement($statement)
{
  //----------run a mysql query, return the handle
  $query = mysql_query($statement, $GLOBALS['dbh']) or 
    HelpfulDie("query failed: $statement", mysql_error($GLOBALS['dbh']));
  return $query;
}

function idSqlStatement($statement)
{
  return sqlInsert($statement);
}

function sqlClose()
{
  //----------Close our mysql connection
  $closed = mysql_close($GLOBALS['dbh']) or
    HelpfulDie("could not disconnect from mysql server link", mysql_error($GLOBALS['dbh']));
  return $closed;
}

function sqlInsert($statement)
{
  //----------run a mysql insert, return the last id generated
  mysql_query($statement, $GLOBALS['dbh']) or 
    HelpfulDie("insert failed: $statement", mysql_error($GLOBALS['dbh']));
  return mysql_insert_id($GLOBALS['dbh']);
}

function sqlInsertClean($statement)
{
  return sqlInsert($statement);
}

function sqlFetchArray($resource)
{
  if ($resource == FALSE)
    return false;
  return mysql_fetch_array($resource, MYSQL_ASSOC);
}

function sqlQuery ($statement)
{
  //echo "[$statement]<br>";
  //echo "link is: " . $GLOBALS['dbh'];
  $query = mysql_query($statement, $GLOBALS['dbh']) or 
    HelpfulDie("query failed: $statement", mysql_error($GLOBALS['dbh']));
  $rez = @mysql_fetch_array($query, MYSQL_ASSOC);
  if ($rez == FALSE)
    return FALSE;
  return $rez;
}

function sqlListFields($table) {
  $sql = "SHOW COLUMNS FROM ". mysql_real_escape_string($table);
  $res = sqlQ($sql);
  $field_list = array();
  while($row = mysql_fetch_array($res)) {
    $field_list[] = $row['Field'];
  }
  return $field_list;
}

function sqlLastID() {
  return mysql_insert_id($GLOBALS['dbh']);
}

function sqlQ ($statement)
{
  //echo "[$statement]<br>";
  //echo "link is: " . $GLOBALS['dbh'];
  $query = mysql_query($statement, $GLOBALS['dbh']) or
    HelpfulDie("query failed: $statement", mysql_error($GLOBALS['dbh']));
  return $query;
}

function get_db() {
  return $GLOBALS['adodb']['db'];
}

function generate_id () {
  $database = $GLOBALS['adodb']['db'];
  return $database->GenID("sequences");
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

?>
