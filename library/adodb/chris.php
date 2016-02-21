<?php
define( 'ADODB_ASSOC_CASE', $argc > 1 ? (int)$argv[1] : 2 );
error_reporting( E_ALL & ~E_NOTICE );
$lib='adodb.inc.php';
if( !file_exists( $lib ) ) {
	$lib = 'phplens/adodb5/' . $lib;
}
echo "ADODB_ASSOC_CASE=".ADODB_ASSOC_CASE."\n";
require_once( $lib );

$ADODB_FETCH_MODE=ADODB_FETCH_ASSOC;

$objInterbaseDB = ADONewConnection('ibase');
$objInterbaseDB->dialect = 3;

$arrInterbaseConnectInfo=array();
$arrInterbaseConnectInfo["db_file"]="localhost:/var/lib/firebird/2.5/data/employee.fdb";
$arrInterbaseConnectInfo["db_username"]="SYSDBA";
$arrInterbaseConnectInfo["db_password"]="C0yote71";

$objInterbaseDB->PConnect($arrInterbaseConnectInfo["db_file"],$arrInterbaseConnectInfo["db_username"],$arrInterbaseConnectInfo["db_password"]);

$sql="SELECT first_name FROM EMPLOYEE";

$rs=$objInterbaseDB->execute($sql);

echo "ADODB_ASSOC_CASE=".ADODB_ASSOC_CASE."\n";
echo "----------------------------------\n";
print_r($rs->GetRowAssoc(ADODB_ASSOC_CASE)); print_r($rs->bind); print_r($rs->fields);
//print_r($rs->GetRowAssoc(1)); //print_r($rs->bind); print_r($rs->fields);
