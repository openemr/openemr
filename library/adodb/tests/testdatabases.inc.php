<?php
  
/*
V4.80 8 Mar 2006  (c) 2000-2011 John Lim (jlim#natsoft.com). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence.
*/ 
 
 /* this file is used by the ADODB test program: test.php */
 ?>

<table><tr valign=top><td>
<form method=get>
<input type=checkbox name="testaccess" value=1 <?php echo !empty($testaccess) ? 'checked' : '' ?>> <b>Access</b><br>
<input type=checkbox name="testibase" value=1 <?php echo !empty($testibase) ? 'checked' : '' ?>> <b>Interbase</b><br>
<input type=checkbox name="testmssql" value=1 <?php echo !empty($testmssql) ? 'checked' : '' ?>> <b>MSSQL</b><br>
 <input type=checkbox name="testmysql" value=1 <?php echo !empty($testmysql) ? 'checked' : '' ?>> <b>MySQL</b><br>
<input type=checkbox name="testmysqlodbc" value=1 <?php echo !empty($testmysqlodbc) ? 'checked' : '' ?>> <b>MySQL ODBC</b><br>
<input type=checkbox name="testmysqli" value=1 <?php echo !empty($testmysqli) ? 'checked' : '' ?>> <b>MySQLi</b>
<br>
<td><input type=checkbox name="testsqlite" value=1 <?php echo !empty($testsqlite) ? 'checked' : '' ?>> <b>SQLite</b><br>
<input type=checkbox name="testproxy" value=1 <?php echo !empty($testproxy) ? 'checked' : '' ?>> <b>MySQL Proxy</b><br>
<input type=checkbox name="testoracle" value=1 <?php echo !empty($testoracle) ? 'checked' : '' ?>> <b>Oracle (oci8)</b> <br>
<input type=checkbox name="testpostgres" value=1 <?php echo !empty($testpostgres) ? 'checked' : '' ?>> <b>PostgreSQL</b><br>
<input type=checkbox name="testpgodbc" value=1 <?php echo !empty($testpgodbc) ? 'checked' : '' ?>> <b>PostgreSQL ODBC</b><br>
<td>
<input type=checkbox name="testpdopgsql" value=1 <?php echo !empty($testpdopgsql) ? 'checked' : '' ?>> <b>PgSQL PDO</b><br>
<input type=checkbox name="testpdomysql" value=1 <?php echo !empty($testpdomysql) ? 'checked' : '' ?>> <b>MySQL PDO</b><br>
<input type=checkbox name="testpdosqlite" value=1 <?php echo !empty($testpdosqlite) ? 'checked' : '' ?>> <b>SQLite PDO</b><br>
<input type=checkbox name="testpdoaccess" value=1 <?php echo !empty($testpdoaccess) ? 'checked' : '' ?>> <b>Access PDO</b><br>
<input type=checkbox name="testpdomssql" value=1 <?php echo !empty($testpdomssql) ? 'checked' : '' ?>> <b>MSSQL PDO</b><br>

<input type=checkbox name="testpdoora" value=1 <?php echo !empty($testpdoora) ? 'checked' : '' ?>> <b>OCI PDO</b><br>

<td><input type=checkbox name="testdb2" value=1 <?php echo !empty($testdb2) ? 'checked' : '' ?>> DB2<br>
<input type=checkbox name="testvfp" value=1 <?php echo !empty($testvfp) ? 'checked' : '' ?>> VFP+ODBTP<br>
<input type=checkbox name="testado" value=1 <?php echo !empty($testado) ? 'checked' : '' ?>> ADO (for mssql and access)<br>
<input type=checkbox name="nocountrecs" value=1 <?php echo !empty($nocountrecs) ? 'checked' : '' ?>> $ADODB_COUNTRECS=false<br>
<input type=checkbox name="nolog" value=1 <?php echo !empty($nolog) ? 'checked' : '' ?>> No SQL Logging<br>
<input type=checkbox name="time" value=1 <?php echo !empty($_GET['time']) ? 'checked' : '' ?>> ADOdb time test
</table>
<input type=submit>
</form>

<?php

if ($ADODB_FETCH_MODE != ADODB_FETCH_DEFAULT) print "<h3>FETCH MODE IS NOT ADODB_FETCH_DEFAULT</h3>";

if (isset($nocountrecs)) $ADODB_COUNTRECS = false;

// cannot test databases below, but we include them anyway to check
// if they parse ok...

if (sizeof($_GET) || !isset($_SERVER['HTTP_HOST'])) {
	echo "<BR>";
	ADOLoadCode2("sybase"); 
	ADOLoadCode2("postgres");
	ADOLoadCode2("postgres7");
	ADOLoadCode2("firebird");
	ADOLoadCode2("borland_ibase");
	ADOLoadCode2("informix");
	ADOLoadCode2('mysqli');
	if (defined('ODBC_BINMODE_RETURN')) {
		ADOLoadCode2("sqlanywhere");
		ADOLoadCode2("access");
	}
	ADOLoadCode2("mysql");
	ADOLoadCode2("oci8");
}

function ADOLoadCode2($d)
{
	ADOLoadCode($d);
	$c = ADONewConnection($d);
	echo "Loaded $d ",($c ? 'ok' : 'extension not installed'),"<br>";
}

flush();
if (!empty($testpostgres)) {
	//ADOLoadCode("postgres");

	$db = ADONewConnection('postgres');
	print "<h1>Connecting $db->databaseType...</h1>";
	if ($db->Connect("localhost","tester","test","northwind")) {
		testdb($db,"create table ADOXYZ (id integer, firstname char(24), lastname varchar,created date)");
	}else
		print "ERROR: PostgreSQL requires a database called test on server, user tester, password test.<BR>".$db->ErrorMsg();
}

if (!empty($testpgodbc)) { 
	
	$db = ADONewConnection('odbc');
	$db->hasTransactions = false;
	print "<h1>Connecting $db->databaseType...</h1>";
	
	if ($db->PConnect('Postgresql')) {
		$db->hasTransactions = true;
		testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date) type=innodb");
	} else print "ERROR: PostgreSQL requires a database called test on server, user tester, password test.<BR>".$db->ErrorMsg();
}

if (!empty($testibase)) {
	//$_GET['nolog'] = true;
	$db = ADONewConnection('firebird');
	print "<h1>Connecting $db->databaseType...</h1>";
	if ($db->PConnect("localhost:d:\\firebird\\151\\examples\\EMPLOYEE.fdb", "sysdba", "masterkey", ""))
		testdb($db,"create table ADOXYZ (id integer, firstname char(24), lastname char(24),price numeric(12,2),created date)");
	 else print "ERROR: Interbase test requires a database called employee.gdb".'<BR>'.$db->ErrorMsg();
	
}


if (!empty($testsqlite)) {
	$path =urlencode('d:\inetpub\adodb\sqlite.db');
	$dsn = "sqlite://$path/";
	$db = ADONewConnection($dsn);
	//echo $dsn;
	
	//$db = ADONewConnection('sqlite');
	

	if ($db && $db->PConnect("d:\\inetpub\\adodb\\sqlite.db", "", "", "")) {
		print "<h1>Connecting $db->databaseType...</h1>";
		testdb($db,"create table ADOXYZ (id int, firstname char(24), lastname char(24),created datetime)");
	} else 
		print "ERROR: SQLite";
	
}

if (!empty($testpdopgsql)) {
	$connstr = "pgsql:dbname=test";
	$u = 'tester';$p='test';
	$db = ADONewConnection('pdo');
	print "<h1>Connecting $db->databaseType...</h1>";
	$db->Connect($connstr,$u,$p) || die("CONNECT FAILED");
	testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
}

if (!empty($testpdomysql)) {
	$connstr = "mysql:dbname=northwind";
	$u = 'root';$p='';
	$db = ADONewConnection('pdo');
	print "<h1>Connecting $db->databaseType...</h1>";
	$db->Connect($connstr,$u,$p) || die("CONNECT FAILED");
	
	testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
}

if (!empty($testpdomssql)) {
	$connstr = "mssql:dbname=northwind";
	$u = 'sa';$p='natsoft';
	$db = ADONewConnection('pdo');
	print "<h1>Connecting $db->databaseType...</h1>";
	$db->Connect($connstr,$u,$p) || die("CONNECT FAILED");
	
	testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
}

if (!empty($testpdosqlite)) {
	$connstr = "sqlite:d:/inetpub/adodb/sqlite-pdo.db3";
	$u = '';$p='';
	$db = ADONewConnection('pdo');
	$db->hasTransactions = false;
	print "<h1>Connecting $db->databaseType...</h1>";
	$db->Connect($connstr,$u,$p) || die("CONNECT FAILED");
	testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
}

if (!empty($testpdoaccess)) {
	$connstr = 'odbc:nwind';
	$u = '';$p='';
	$db = ADONewConnection('pdo');
	$db->hasTransactions = false;
	print "<h1>Connecting $db->databaseType...</h1>";
	$db->Connect($connstr,$u,$p) || die("CONNECT FAILED");
	testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
}

if (!empty($testpdoora)) {
	$connstr = 'oci:';
	$u = 'scott';$p='natsoft';
	$db = ADONewConnection('pdo');
	#$db->hasTransactions = false;
	print "<h1>Connecting $db->databaseType...</h1>";
	$db->Connect($connstr,$u,$p) || die("CONNECT FAILED");
	testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
}

// REQUIRES ODBC DSN CALLED nwind
if (!empty($testaccess)) {
	$db = ADONewConnection('access');
	print "<h1>Connecting $db->databaseType...</h1>";
	$access = 'd:\inetpub\wwwroot\php\NWIND.MDB';
	$dsn = "nwind";
	$dsn = "Driver={Microsoft Access Driver (*.mdb)};Dbq=$access;Uid=Admin;Pwd=;";
	
	//$dsn =  'Provider=Microsoft.Jet.OLEDB.4.0;DATA SOURCE=' . $access . ';';
	if ($db->PConnect($dsn, "", "", ""))
		testdb($db,"create table ADOXYZ (id int, firstname char(24), lastname char(24),created datetime)");
	else print "ERROR: Access test requires a Windows ODBC DSN=nwind, Access driver";
	
}

if (!empty($testaccess) && !empty($testado)) { // ADO ACCESS

	$db = ADONewConnection("ado_access");
	print "<h1>Connecting $db->databaseType...</h1>";
	
	$access = 'd:\inetpub\wwwroot\php\NWIND.MDB';
	$myDSN =  'PROVIDER=Microsoft.Jet.OLEDB.4.0;'
		. 'DATA SOURCE=' . $access . ';';
		//. 'USER ID=;PASSWORD=;';
	$_GET['nolog'] = 1;
	if ($db->PConnect($myDSN, "", "", "")) {
		print "ADO version=".$db->_connectionID->version."<br>";
		testdb($db,"create table ADOXYZ (id int, firstname char(24), lastname char(24),created datetime)");
	} else print "ERROR: Access test requires a Access database $access".'<BR>'.$db->ErrorMsg();
	
}

if (!empty($testvfp)) { // ODBC
	$db = ADONewConnection('vfp');
	print "<h1>Connecting $db->databaseType...</h1>";flush();

	if ( $db->PConnect("vfp-adoxyz")) {
		testdb($db,"create table d:\\inetpub\\adodb\\ADOXYZ (id int, firstname char(24), lastname char(24),created date)");
	 } else print "ERROR: Visual FoxPro test requires a Windows ODBC DSN=vfp-adoxyz, VFP driver";
	
	echo "<hr />";
	$db = ADONewConnection('odbtp');
	
	if ( $db->PConnect('localhost','DRIVER={Microsoft Visual FoxPro Driver};SOURCETYPE=DBF;SOURCEDB=d:\inetpub\adodb;EXCLUSIVE=NO;')) {
	print "<h1>Connecting $db->databaseType...</h1>";flush();
	testdb($db,"create table d:\\inetpub\\adodb\\ADOXYZ (id int, firstname char(24), lastname char(24),created date)");
	 } else print "ERROR: Visual FoxPro odbtp requires a Windows ODBC DSN=vfp-adoxyz, VFP driver";
	
}


// REQUIRES MySQL server at localhost with database 'test'
if (!empty($testmysql)) { // MYSQL


	if (PHP_VERSION >= 5 || $_SERVER['HTTP_HOST'] == 'localhost') $server = 'localhost';
	else $server = "mangrove";
	$user = 'root'; $password = ''; $database = 'northwind';
	$db = ADONewConnection("mysqlt://$user:$password@$server/$database?persist");
	print "<h1>Connecting $db->databaseType...</h1>";
	
	if (true || $db->PConnect($server, "root", "", "northwind")) {
		//$db->Execute("DROP TABLE ADOXYZ") || die('fail drop');
		//$db->debug=1;$db->Execute('drop table ADOXYZ');
		testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date) Type=InnoDB");
	} else print "ERROR: MySQL test requires a MySQL server on localhost, userid='admin', password='', database='test'".'<BR>'.$db->ErrorMsg();
}

// REQUIRES MySQL server at localhost with database 'test'
if (!empty($testmysqli)) { // MYSQL

	$db = ADONewConnection('mysqli');
	print "<h1>Connecting $db->databaseType...</h1>";
	if (PHP_VERSION >= 5 || $_SERVER['HTTP_HOST'] == 'localhost') $server = 'localhost';
	else $server = "mangrove";
	if ($db->PConnect($server, "root", "", "northwind")) {
		//$db->debug=1;$db->Execute('drop table ADOXYZ');
		testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date)");
	} else print "ERROR: MySQL test requires a MySQL server on localhost, userid='admin', password='', database='test'".'<BR>'.$db->ErrorMsg();
}


// REQUIRES MySQL server at localhost with database 'test'
if (!empty($testmysqlodbc)) { // MYSQL
	
	$db = ADONewConnection('odbc');
	$db->hasTransactions = false;
	print "<h1>Connecting $db->databaseType...</h1>";
	if ($_SERVER['HTTP_HOST'] == 'localhost') $server = 'localhost';
	else $server = "mangrove";
	if ($db->PConnect('mysql', "root", ""))
		testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date) type=innodb");
	else print "ERROR: MySQL test requires a MySQL server on localhost, userid='admin', password='', database='test'".'<BR>'.$db->ErrorMsg();
}

if (!empty($testproxy)){
	$db = ADONewConnection('proxy');
	print "<h1>Connecting $db->databaseType...</h1>";
	if ($_SERVER['HTTP_HOST'] == 'localhost') $server = 'localhost';

	if ($db->PConnect('http://localhost/php/phplens/adodb/server.php'))
		testdb($db,
		"create table ADOXYZ (id int, firstname char(24), lastname char(24), created date) type=innodb");
	else print "ERROR: MySQL test requires a MySQL server on localhost, userid='admin', password='', database='test'".'<BR>'.$db->ErrorMsg();

}

ADOLoadCode('oci805');
ADOLoadCode("oci8po");
	
if (!empty($testoracle)) {
	$dsn = "oci8";//://scott:natsoft@kk2?persist";
	$db = ADONewConnection($dsn );//'oci8');
	
	//$db->debug=1;
	print "<h1>Connecting $db->databaseType...</h1>";
	if ($db->Connect('mobydick', "scott", "natsoft",'SID=mobydick'))
		testdb($db,"create table ADOXYZ (id int, firstname varchar(24), lastname varchar(24),created date)");
	else 
		print "ERROR: Oracle test requires an Oracle server setup with scott/natsoft".'<BR>'.$db->ErrorMsg();

}
ADOLoadCode("oracle"); // no longer supported
if (false && !empty($testoracle)) { 
	
	$db = ADONewConnection();
	print "<h1>Connecting $db->databaseType...</h1>";
	if ($db->PConnect("", "scott", "tiger", "natsoft.domain"))
		testdb($db,"create table ADOXYZ (id int, firstname varchar(24), lastname varchar(24),created date)");
	else print "ERROR: Oracle test requires an Oracle server setup with scott/tiger".'<BR>'.$db->ErrorMsg();

}

ADOLoadCode("odbc_db2"); // no longer supported
if (!empty($testdb2)) {
	if (PHP_VERSION>=5.1) {
		$db = ADONewConnection("db2");
		print "<h1>Connecting $db->databaseType...</h1>";
		
		#$db->curMode = SQL_CUR_USE_ODBC;
		#$dsn = "driver={IBM db2 odbc DRIVER};Database=test;hostname=localhost;port=50000;protocol=TCPIP; uid=natsoft; pwd=guest";
		if ($db->Connect('localhost','natsoft','guest','test')) {
			testdb($db,"create table ADOXYZ (id int, firstname varchar(24), lastname varchar(24),created date)");
		} else print "ERROR: DB2 test requires an server setup with odbc data source db2_sample".'<BR>'.$db->ErrorMsg();
	} else { 
		$db = ADONewConnection("odbc_db2");
		print "<h1>Connecting $db->databaseType...</h1>";
		
		$dsn = "db2test";
		#$db->curMode = SQL_CUR_USE_ODBC;
		#$dsn = "driver={IBM db2 odbc DRIVER};Database=test;hostname=localhost;port=50000;protocol=TCPIP; uid=natsoft; pwd=guest";
		if ($db->Connect($dsn)) {
			testdb($db,"create table ADOXYZ (id int, firstname varchar(24), lastname varchar(24),created date)");
		} else print "ERROR: DB2 test requires an server setup with odbc data source db2_sample".'<BR>'.$db->ErrorMsg();
	}
echo "<hr />";
flush();
	$dsn = "driver={IBM db2 odbc DRIVER};Database=sample;hostname=localhost;port=50000;protocol=TCPIP; uid=root; pwd=natsoft";
	
	$db = ADONewConnection('odbtp');
	if ($db->Connect('127.0.0.1',$dsn)) {
		
		$db->debug=1;
		 $arr = $db->GetArray( "||SQLProcedures" ); adodb_pr($arr);
	     $arr = $db->GetArray( "||SQLProcedureColumns|||GET_ROUTINE_SAR" );adodb_pr($arr);
	
		testdb($db,"create table ADOXYZ (id int, firstname varchar(24), lastname varchar(24),created date)");
	} else echo ("ERROR Connection");
	echo $db->ErrorMsg();
}


$server = 'localhost';



ADOLoadCode("mssqlpo");
if (false && !empty($testmssql)) { // MS SQL Server -- the extension is buggy -- probably better to use ODBC
	$db = ADONewConnection("mssqlpo");
	//$db->debug=1;
	print "<h1>Connecting $db->databaseType...</h1>";
	
	$ok = $db->Connect('','sa','natsoft','northwind');
	echo $db->ErrorMsg();
	if ($ok /*or $db->PConnect("mangrove", "sa", "natsoft", "ai")*/) {
		AutoDetect_MSSQL_Date_Order($db);
	//	$db->Execute('drop table adoxyz');
		testdb($db,"create table ADOXYZ (id int, firstname char(24) null, lastname char(24) null,created datetime null)");
	} else print "ERROR: MSSQL test 2 requires a MS SQL 7 on a server='$server', userid='adodb', password='natsoft', database='ai'".'<BR>'.$db->ErrorMsg();
	
}


ADOLoadCode('odbc_mssql');
if (!empty($testmssql)) { // MS SQL Server via ODBC
	$db = ADONewConnection();
	
	print "<h1>Connecting $db->databaseType...</h1>";
	
	$dsn = "PROVIDER=MSDASQL;Driver={SQL Server};Server=$server;Database=northwind;";
	$dsn = 'condor';
	if ($db->PConnect($dsn, "sa", "natsoft", ""))  {
		testdb($db,"create table ADOXYZ (id int, firstname char(24) null, lastname char(24) null,created datetime null)");
	}
	else print "ERROR: MSSQL test 1 requires a MS SQL 7 server setup with DSN setup";

}

ADOLoadCode("ado_mssql");
if (!empty($testmssql) && !empty($testado) ) { // ADO ACCESS MSSQL -- thru ODBC -- DSN-less
	
	$db = ADONewConnection("ado_mssql");
	//$db->debug=1;
	print "<h1>Connecting DSN-less $db->databaseType...</h1>";
	
	$myDSN="PROVIDER=MSDASQL;DRIVER={SQL Server};"
		. "SERVER=$server;DATABASE=NorthWind;UID=adodb;PWD=natsoft;Trusted_Connection=No";

		
	if ($db->PConnect($myDSN, "", "", ""))
		testdb($db,"create table ADOXYZ (id int, firstname char(24) null, lastname char(24) null,created datetime null)");
	else print "ERROR: MSSQL test 2 requires MS SQL 7";
	
}

if (!empty($testmssql) && !empty($testado)) { // ADO ACCESS MSSQL with OLEDB provider

	$db = ADONewConnection("ado_mssql");
	print "<h1>Connecting DSN-less OLEDB Provider $db->databaseType...</h1>";
	//$db->debug=1;
	$myDSN="SERVER=localhost;DATABASE=northwind;Trusted_Connection=yes";
	if ($db->PConnect($myDSN, "adodb", "natsoft", 'SQLOLEDB')) {
		testdb($db,"create table ADOXYZ (id int, firstname char(24), lastname char(24),created datetime)");
	} else print "ERROR: MSSQL test 2 requires a MS SQL 7 on a server='mangrove', userid='sa', password='', database='ai'";

}


if (extension_loaded('odbtp') && !empty($testmssql)) { // MS SQL Server via ODBC
	$db = ADONewConnection('odbtp');
	
	$dsn = "PROVIDER=MSDASQL;Driver={SQL Server};Server=$server;Database=northwind;uid=adodb;pwd=natsoft";
	
	if ($db->PConnect('localhost',$dsn, "", ""))  {
		print "<h1>Connecting $db->databaseType...</h1>";				
		testdb($db,"create table ADOXYZ (id int, firstname char(24) null, lastname char(24) null,created datetime null)");
	}
	else print "ERROR: MSSQL test 1 requires a MS SQL 7 server setup with DSN setup";

}


print "<h3>Tests Completed</h3>";

?>
