<?php
$host = 'localhost';
$u = 'root';
$p = 'C0yote71';
$dbname = 'mantis_13x';

define( 'ADODB_ASSOC_CASE', $argc > 1 ? (int)$argv[1] : 2 );

require_once('adodb.inc.php');

$ADODB_FETCH_MODE=ADODB_FETCH_ASSOC;
//$ADODB_FETCH_MODE=ADODB_FETCH_BOTH;
$db = ADONewConnection('pgsql');

$db->Connect($host, $u, $p, $dbname);
$sql="SELECT * FROM mantis_config_table";

$res=$db->Execute($sql);
print_r($res->fields);

print_r($res->GetRowAssoc());

