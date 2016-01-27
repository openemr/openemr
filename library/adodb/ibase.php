<?php
// bad commit = e3c11ed98970408eae649f929898983c6b5d2a6a

$host = 'localhost:/var/lib/firebird/2.5/data/employee.fdb';
$u = 'SYSDBA';
$p = 'C0yote71';

define( 'ADODB_ASSOC_CASE', $argc > 1 ? (int)$argv[1] : 2 );

/*
$dbh = ibase_connect($host, $u, $p);
$stmt = 'SELECT * FROM employee';

$sth = ibase_query($dbh, $stmt) or die(ibase_errmsg());
var_dump(ibase_fetch_row($sth));
*/

require_once('adodb.inc.php');

$ADODB_FETCH_MODE=ADODB_FETCH_ASSOC;
$db = ADONewConnection('ibase');
$db->dialect = 3;

$db->PConnect($host, $u, $p);
$sql="SELECT * FROM EMPLOYEE";
$res=$db->GetRow($sql);
print_r($res);

$res=$db->Execute($sql);
print_r($res->fields);
