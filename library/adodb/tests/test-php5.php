<?php
/*
  V4.81 3 May 2006  (c) 2000-2011 John Lim (jlim#natsoft.com). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence.
  Set tabs to 8.
 */


error_reporting(E_ALL);

$path = dirname(__FILE__);

include("$path/../adodb-exceptions.inc.php");
include("$path/../adodb.inc.php");	

echo "<h3>PHP ".PHP_VERSION."</h3>\n";
try {

$dbt = 'oci8po';

try {
switch($dbt) {
case 'oci8po':
	$db = NewADOConnection("oci8po");
	
	$db->Connect('localhost','scott','natsoft','sherkhan');
	break;
default:
case 'mysql':
	$db = NewADOConnection("mysql");
	$db->Connect('localhost','root','','northwind');
	break;
	
case 'mysqli':
	$db = NewADOConnection("mysqli://root:@localhost/northwind");
	//$db->Connect('localhost','root','','test');
	break;
}
} catch (exception $e){
	echo "Connect Failed";
	adodb_pr($e);
	die();
}

$db->debug=1;

$cnt = $db->GetOne("select count(*) from adoxyz where ?<id and id<?",array(10,20));
$stmt = $db->Prepare("select * from adoxyz where ?<id and id<?");
if (!$stmt) echo $db->ErrorMsg(),"\n";
$rs = $db->Execute($stmt,array(10,20));

echo  "<hr /> Foreach Iterator Test (rand=".rand().")<hr />";
$i = 0;
foreach($rs as $v) {
	$i += 1;
	echo "rec $i: "; $s1 = adodb_pr($v,true); $s2 = adodb_pr($rs->fields,true);
	if ($s1 != $s2 && !empty($v)) {adodb_pr($s1); adodb_pr($s2);}
	else echo "passed<br>";
	flush();
}

$rs = new ADORecordSet_empty();
foreach($rs as $v) {
	echo "<p>empty ";var_dump($v);
}


if ($i != $cnt) die("actual cnt is $i, cnt should be $cnt\n");
else echo "Count $i is correct<br>";

$rs = $db->Execute("select bad from badder");

} catch (exception $e) {
	adodb_pr($e);
	echo "<h3>adodb_backtrace:</h3>\n";
	$e = adodb_backtrace($e->gettrace());
}

$rs = $db->Execute("select distinct id, firstname,lastname from adoxyz order by id");
echo "Result=\n",$rs,"</p>";

echo "<h3>Active Record</h3>";

	include_once("../adodb-active-record.inc.php");
	ADOdb_Active_Record::SetDatabaseAdapter($db);
	
try {
	class City extends ADOdb_Active_Record{};
	$a = new City();

} catch(exception $e){
	echo $e->getMessage();
}

try {
	
	$a = new City();
	
	echo "<p>Successfully created City()<br>";
	#var_dump($a->GetPrimaryKeys());
	$a->city = 'Kuala Lumpur';
	$a->Save();
	$a->Update();
	#$a->SetPrimaryKeys(array('city'));	
	$a->country = "M'sia";
	$a->save();
	$a->Delete();
} catch(exception $e){
	echo $e->getMessage();
}

//include_once("test-active-record.php");
?>