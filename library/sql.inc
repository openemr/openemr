<?php

include_once(dirname(__FILE__) . "/sqlconf.php");
require_once(dirname(__FILE__) . "/adodb/adodb.inc.php");

define('ADODB_FETCH_ASSOC',2);
$database = NewADOConnection("mysql");
$database->PConnect($host, $login, $pass, $dbase);
$GLOBALS['adodb']['db'] = $database;
$GLOBALS['dbh'] = $database->_connectionID;

function sqlConnect($login,$pass,$dbase,$host,$port = '3306')
{
	return $GLOBALS['dbh'] = $database->_connectionID;
}

function sqlStatement($statement)
{
	//----------run a mysql query, return the handle
	$query = mysql_query($statement, $GLOBALS['dbh']) or die ("query failed: $statement " . mysql_error());
	return $query;
}

function idSqlStatement($statement)
{
	//----------run a mysql query, return the handle
	$query = mysql_query($statement, $GLOBALS['dbh']) or die ("query failed: $statement");
	return mysql_insert_id();
}


function sqlClose()
{
	//----------Close our mysql connection
	$closed = mysql_close($GLOBALS['dbh']) or die ("could not disconnect from mysql server link");
	return $closed;
}

function sqlInsert($statement)
{
	//----------run a mysql insert, return the last id generated
	mysql_query($statement, $GLOBALS['dbh']) or die ("insert failed: $statement");
	return mysql_insert_id($GLOBALS['dbh']);
}

function sqlInsertClean($statement)
{
	//----------run a mysql insert, return the last id generated
	mysql_query($statement, $GLOBALS['dbh']) or die ("insert failed: $statement");
	return mysql_insert_id($GLOBALS['dbh']);
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
	$query = mysql_query($statement, $GLOBALS['dbh']) or die ("query failed: $statement, error:" . mysql_error());
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
	$query = mysql_query($statement, $GLOBALS['dbh']) or die ("query failed: $statement, error:" . mysql_error());
	return $query;
}
function get_db() {
	return $GLOBALS['adodb']['db'];
}
function generate_id () {
	$database = $GLOBALS['adodb']['db'];
	return $database->GenID("sequences");
}
?>
