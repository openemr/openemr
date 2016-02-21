<?php
$host = 'localhost/XE';
$u = 'mantis';
$p = 'C0yote71';

define( 'ADODB_ASSOC_CASE', $argc > 1 ? (int)$argv[1] : 2 );

require_once('adodb.inc.php');

$ADODB_FETCH_MODE=ADODB_FETCH_ASSOC;
$db = ADONewConnection('oci8');

$db->Connect($host, $u, $p);
$sql="SELECT * FROM m_config";

$res=$db->Execute($sql);
print_r($res->fields);

print_r($res->GetRowAssoc());
