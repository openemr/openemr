<?php
//required for normal operation because of recent changes in PHP:
extract($_GET);
extract($_POST);
//turn off PHP compatibility warnings
ini_set("session.bug_compat_warn","off");

$url = "";
$dumpfile = "sql/database.sql";
$icd9 = "sql/icd9.sql";
$conffile = "library/sqlconf.php";
$upgrade = 0;
$defhost = 'localhost';
$state = $_POST["state"];
$gaclConfigFile1 = "gacl/gacl.ini.php";
$gaclConfigFile2 = "gacl/gacl.class.php";
$gaclWritableDirectory = "gacl/admin/templates_c";
$gaclSetupScript1 = "./gacl/setup.php";
$gaclSetupScript2 = "./acl_setup.php";
$requiredDirectory1 = "interface/main/calendar/modules/PostCalendar/pntemplates/compiled";
$requiredDirectory2 = "interface/main/calendar/modules/PostCalendar/pntemplates/cache";
$writableFileList = array($conffile, $gaclConfigFile1, $gaclConfigFile2);
$writableDirList = array($gaclWritableDirectory, $requiredDirectory1, $requiredDirectory2);

include_once($conffile);
?>
<HTML>
<HEAD>
<TITLE>OpenEMR Setup Tool</TITLE>
<LINK REL=STYLESHEET HREF="interface/themes/style_blue.css">
</HEAD>
<BODY>

<span class="title">OpenEMR Setup</span>
<br><br>
<span class="text">

<?php
 if (strtolower(ini_get('register_globals')) != 'off' && (bool) ini_get('register_globals')) {
  echo "It appears that you have register_globals enabled in your php.ini\n" .
   "configuration file.  This causes unacceptable security risks.  You must\n" .
   "turn it off before continuing with installation.\n";
  exit();
 }
?>

<?php
 if ($state == 5) {
?>

<p>Congratulations! OpenEMR is now successfully installed.

<ul>
 <li>Please Edit the 'interface/globals.php' file now to specify the correct
  URL paths, and to select a theme.</li>
  <li>Please restore secure permissions on the configuration files: /openemr/library/sqlconf.php, /openemr/gacl/gacl.ini.php, and /openemr/gacl/gacl.class.php files.</li> 
</ul>
<p>
 In order to take full advantage of the documents capability you
 must give your web server permissions on the document storage
 directory. Try "chown apache:apache -R openemrwebroot/documents"
 and then "chmod g+w openemrwebroot/documents".
 You must also make sure your PHP installation (normally set in
 your php.ini file) has "file_uploads enabled", that
 "upload_max_filesize" is appropriate for your use and that
 "upload_tmp_dir" is set to a correct value if the default of
 "/tmp" won't work on your system.
</p>
<p>
Access controls (php-GACL) are installed for fine-grained security,
and can be administered in OpenEMR's admin->acl menu.
</p>
<p>
There's much information and many extra tools bundled within the OpenEMR
installation directory. Please refer to openemr/Documentation.
<br>Many forms and other useful scripts can be found at openemr/contrib.
</p>
<p>
Reading openemr/includes/config.php and openemr/interface/globals.php is a good
idea.
</p>
<p>
To ensure a consistent look and feel through out the application
using <a href='http://www.mozilla.org/products/firefox/'>Firefox</a>
is recommended.
</p>
<p>
<b>The initial OpenEMR user is "<?php echo $iuser; ?>" and the password is "pass".</b>
You should change this password!
</p>
<p>
 <a href='./'>Click here to start using OpenEMR. </a>
</p>

<?
  exit();
 }
?>

<?php

	$server = $_POST["server"];
	$port = $_POST["port"];
	$dbname = $_POST["dbname"];
	$root = $_POST["root"];	
	$login = $_POST["login"];
	$pass = $_POST["pass"];
	$loginhost = $_POST["loginhost"];
	$rootpass = $_POST["rootpass"];


if ($config == 1) {
	echo "OpenEMR is already configured.  If you wish to re-configure the SQL server, edit $conffile, or change the 'config' variable to 0, and re-run this script.<br>\n";
}
else {
switch ($state) {

	case 1:
echo "<b>Step $state</b><br><br>\n";
echo "Now I need to know whether you want me to create the databases on my own or if you have already created the database for me to use.  If you are upgrading, you will want to select the latter function.  For me to create the databases, you will need to supply the MySQL root password.\n
<span class='title'> <br />NOTE: clicking on \"Continue\" may delete or cause damage to data on your system. Before you continue please backup your data.</span>
<br><br>\n
<FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='2'>\n
<INPUT TYPE='RADIO' NAME='inst' VALUE='1' checked>Have setup create the databases<br>\n
<INPUT TYPE='RADIO' NAME='inst' VALUE='2'>I have already created the databases<br>\n
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";
break;

	case 2:
echo "<b>Step $state</b><br><br>\n";
echo "Now you need to supply the MySQL server information.
<br><br>
<FORM METHOD='POST'>
<INPUT TYPE='HIDDEN' NAME='state' VALUE='3'>
<INPUT TYPE='HIDDEN' NAME='inst' VALUE='$inst'>
<TABLE>\n
<TR><TD><font color='red'>SERVER:</font></TD></TR>
<TR><TD><span class='text'>Server Host: </span></TD><TD><INPUT TYPE='TEXT' VALUE='$defhost' NAME='server' SIZE='30'><span class='text'>(This is the IP address of the machine running MySQL)</span><br></TD></TR>
<TR><TD><span class='text'>Server Port: </span></TD><TD><INPUT TYPE='TEXT' VALUE='3306' NAME='port' SIZE='30'><span class='text'>(The default port for MySQL is 3306)</span><br></TD></TR>
<TR><TD><span class='text'>Database Name: </span></TD><TD><INPUT TYPE='TEXT' VALUE='openemr' NAME='dbname' SIZE='30'><span class='text'>(This is the name of the OpenEMR database - 'openemr' is the recommended)</span><br></TD></TR>
<TR><TD><span class='text'>Login Name: </span></TD><TD><INPUT TYPE='TEXT' VALUE='openemr' NAME='login' SIZE='30'><span class='text'>(This is the name of the OpenEMR login name - 'openemr' is the recommended)</span><br></TD></TR>
<TR><TD><span class='text'>Password: </span></TD><TD><INPUT TYPE='PASSWORD' VALUE='' NAME='pass' SIZE='30'><span class='text'>(This is the Login Password for when PHP accesses MySQL - it should be at least 8 characters long and composed of both numbers and letters)</span><br></TD></TR>\n";
if ($inst != 2) {
echo "<TR><TD><font color='red'>CLIENT:</font></TD></TR>";
echo "<TR><TD><span class='text'>User Hostname: </span></TD><TD><INPUT TYPE='TEXT' VALUE='$defhost' NAME='loginhost' SIZE='30'><span class='text'>(This is the IP address of the server machine running Apache and PHP - if you are setting up one computer, this is the same as the Server Host above)</span><br></TD></TR>
<TR><TD><span class='text'>Name for Root Account: </span></TD><TD><INPUT TYPE='TEXT' VALUE='root' NAME='root' SIZE='30'><span class='text'>(This is name for MySQL root account. For localhost, it is usually ok to leave it 'root'.)</span><br></TD></TR>
<TR><TD><span class='text'>Root Pass: </span></TD><TD><INPUT TYPE='PASSWORD' VALUE='' NAME='rootpass' SIZE='30'><span class='text'>(This is your MySQL root password. For localhost, it is usually ok to leave it blank.)</span><br></TD></TR>\n";
}
echo "<TR><TD><font color='red'>USER:</font></TD></TR>";
echo "<TR><TD COLSPAN=2></TD></TR>
<TR><TD><span class='text'>Initial User:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='iuser' VALUE='admin'><span class='text'>(This is the user that will be created for you.  It will be an authorized user, so it should be for a Doctor or other Practitioner)</span></TD></TR>
<TR><TD><span class='text'>Initial User's Name:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='iuname' VALUE='Administrator'><span class='text'>(This is the real name of the initial user.)</span></TD></TR>
<TR><TD><span class='text'>Initial Group:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='igroup' VALUE='Default'><span class='text'>(This is the group that will be created for your users.  This should be the name of your practice.)</span></TD></TR>
";
echo "</TABLE>
<br>
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>";

break;


	case 3:


echo "<b>Step $state</b><br><br>\n";
	if ($pass == "" || $login == "" || !isset($login) || !isset($pass)) {
		echo "ERROR. Please pick a proper username and/or password.<br>\n";
		break;
	}

if ($inst != 2) {
	echo "Connecting to MySQL Server...\n";
	flush();
	if ($server == "localhost")
		$dbh = mysql_connect("$server","$root","$rootpass");
	else
		$dbh = mysql_connect("$server:$port","$root","$rootpass");
	if ($dbh == FALSE) {
		echo "ERROR.  Check your login credentials.\n";
		echo "<p>".mysql_error()." (#".mysql_errno().")\n";
		break;
	}
	else
		echo "OK.<br>\n";
	echo "Creating database...\n";
	flush();
	if (mysql_query("create database $dbname",$dbh) == FALSE) {
		echo "ERROR.  Check your login credentials.\n";
		echo "<p>".mysql_error()." (#".mysql_errno().")\n";
		break;
	}
	else
		echo "OK.<br>\n";
	echo "Creating user with permissions for database...\n";
	flush();
	if (mysql_query("GRANT ALL PRIVILEGES ON $dbname.* TO '$login'@'$loginhost' IDENTIFIED BY '$pass'",$dbh) == FALSE) {
		echo "ERROR when granting privileges to the specified user.\n";
      echo "<p>".mysql_error()." (#".mysql_errno().")\n";
		echo "ERROR.\n";
		break;
	}
	else
		echo "OK.<br>\n";
	echo "Reconnecting as new user...\n";
	mysql_close($dbh);
}
else
	echo "Connecting to MySQL Server...\n";

if ($server == "localhost")
	$dbh = mysql_connect("$server","$login","$pass");
else
	$dbh = mysql_connect("$server:$port","$login","$pass");

if ($dbh == FALSE) {
	echo "ERROR.  Check your login credentials.\n";
	echo "<p>".mysql_error()." (#".mysql_errno().")\n";
	break;
}
else
	echo "OK.<br>\n";
echo "Opening database...";
flush();
if (mysql_select_db("$dbname",$dbh) == FALSE) {
	echo "ERROR.  Check your login credentials.\n";
	echo "<p>".mysql_error()." (#".mysql_errno().")\n";
	break;
}
else
	echo "OK.<br>\n";
	flush();
if ($upgrade != 1) {
	echo "Creating initial tables...\n";
	mysql_query("USE $dbname",$dbh);
	flush();
	$fd = fopen($dumpfile, 'r');
	if ($fd == FALSE) {
		echo "ERROR.  Could not open dumpfile '$dumpfile'.\n";
		flush();
		break;
	}
	$query = "";
	$line = "";
	while (!feof ($fd)){
		$line = fgets($fd,1024);
		$line = rtrim($line);
		if (substr($line,0,2) == "--") // Kill comments
			continue;
		if (substr($line,0,1) == "#") // Kill comments
			continue;
		if ($line == "")
			continue;
		$query = $query.$line;		// Check for full query
		$chr = substr($query,strlen($query)-1,1);
		if ($chr == ";") { // valid query, execute
			$query = rtrim($query,";");
			mysql_query("$query",$dbh);
			$query = "";
		}
	}
	echo "OK<br>\n";
	fclose($fd);
	flush();
	echo "Adding Initial User...\n";
	flush();
	$iuser = $_POST["iuser"];
	$iuname = $_POST["iuname"];
	$igroup = $_POST["igroup"];
	//echo "INSERT INTO groups VALUES (1,'$igroup','$iuser')<br>\n";
	if (mysql_query("INSERT INTO groups (id, name, user) VALUES (1,'$igroup','$iuser')") == FALSE) {
		echo "ERROR.  Could not run queries.\n";
		echo "<p>".mysql_error()." (#".mysql_errno().")\n";
		flush();
		break;
	}
	if (mysql_query("INSERT INTO users (id, username, password, authorized, lname,fname) VALUES (1,'$iuser','1a1dc91c907325c69271ddf0c944bc72',1,'$iuname','')") == FALSE) {
		echo "ERROR.  Could not run queries.\n";
		echo "<p>".mysql_error()." (#".mysql_errno().")\n";
		flush();
		break;
	}
	echo "OK<br>\n";
	flush();
/*	echo "Inserting ICD-9-CM Codes into Database...\n";
	flush();
        $fd = fopen($icd9, 'r');
        if ($fd == FALSE) {
                echo "ERROR.  Could not open dumpfile.\n";
					 echo "<p>".mysql_error()." (#".mysql_errno().")\n";
                flush();
                break;
        }
        $query = "";
        $line = "";
        while (!feof ($fd)){
                $line = fgets($fd,1024);
                $line = rtrim($line);
                if (substr($line,0,2) == "--") // Kill comments
                        continue;
		if (substr($line,0,1) == "#") // Kill comments
			continue;
                if ($line == "")
                        continue;
                $query = $query.$line;          // Check for full query
                $chr = substr($query,strlen($query)-1,1);
                if ($chr == ";") { // valid query, execute
                        $query = rtrim($query,";");
                        mysql_query("$query",$dbh);
                        $query = "";
                }
        }
	echo "OK\n";
	fclose($fd);*/
	flush();
}
echo "\n<br>Next step will finalize SQL setup and install/configure access controls (php-GACL).<br>\n";	

echo "
<FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='4'>
<INPUT TYPE='HIDDEN' NAME='host' VALUE='$server'>
<INPUT TYPE='HIDDEN' NAME='dbname' VALUE='$dbname'>
<INPUT TYPE='HIDDEN' NAME='port' VALUE='$port'>
<INPUT TYPE='HIDDEN' NAME='login' VALUE='$login'>
<INPUT TYPE='HIDDEN' NAME='pass' VALUE='$pass'>
<INPUT TYPE='HIDDEN' NAME='iuser' VALUE='$iuser'>
<INPUT TYPE='HIDDEN' NAME='iuname' VALUE='$iuname'>
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";


break;

	case 4:
echo "<b>Step $state</b><br><br>\n";
echo "Writing SQL configuration file and setting up access controls(php-GACL)...<br><br>";	

echo "Writing SQL Configuration...<br>";	
@touch($conffile); // php bug
$fd = @fopen($conffile, 'w');
$string = "<?
//  OpenEMR
//  MySQL Config
//  Referenced from sql.inc

";

$it_died = 0;   //fmg: variable keeps running track of any errors

fwrite($fd,$string) or $it_died++;
fwrite($fd,"\$host\t= '$host';\n") or $it_died++;
fwrite($fd,"\$port\t= '$port';\n") or $it_died++;
fwrite($fd,"\$login\t= '$login';\n") or $it_died++;
fwrite($fd,"\$pass\t= '$pass';\n") or $it_died++;
fwrite($fd,"\$dbase\t= '$dbname';\n") or $it_died++;


$string = '

$sqlconf = array();
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;
//////////////////////////
//////////////////////////
//////////////////////////
//////DO NOT TOUCH THIS///
$config = 1; /////////////
//////////////////////////
//////////////////////////
//////////////////////////
?>
';
?><? // done just for coloring

fwrite($fd,$string) or $it_died++;

//it's rather irresponsible to not report errors when writing this file.
if ($it_died != 0) {
        echo "ERROR. Couldn't write $it_died lines to config file '$conffile'.\n";
        flush();
        break;
}
fclose($fd);

echo "Successfully wrote SQL configuration.<BR><br>";
	
echo "Installing and Configuring Access Controls (php-GACL)<br>";

//first, edit two gacl config files
// edit gacl.ini.php
$data = file($gaclConfigFile1) or die("Could not read ".$gaclConfigFile1." file.");
$finalData = "";
foreach ($data as $line) {
	$isHit = 0;
	if ((strpos($line,"db_host")) === false) {
      	}
	else {
	        $isHit = 1;
		$finalData .= "db_host = \"${host}\"\n";
	}
        if ((strpos($line,"db_user")) === false) {
	}
	else {
                $isHit = 1;
	        $finalData .= "db_user = \"${login}\"\n";
	}
        if ((strpos($line,"db_password")) === false) {
	}
	else {
                $isHit = 1;
	        $finalData .= "db_password = \"${pass}\"\n";
	}
        if ((strpos($line,"db_name")) === false) {
	}
	else {
                $isHit = 1;
	        $finalData .= "db_name = \"${dbname}\"\n";
	}
	if (!$isHit) {
		$finalData .= $line;
     	}
}
$fd = @fopen($gaclConfigFile1, 'w') or die("Could not open ".$gaclConfigFile1." file.");
fwrite($fd, $finalData);
fclose($fd); 
	
// edit gacl.class.php
$data = file($gaclConfigFile2) or die("Could not read ".$gaclConfigFile2." file.");
$finalData = "";
foreach ($data as $line) {
        $isHit = 0;
	if ((strpos($line,"var \$_db_host = ")) === false) {
	}
	else {
	        $isHit = 1;
	        $finalData .= "var \$_db_host = '$host';\n";
	}
	if ((strpos($line,"var \$_db_user = ")) === false) {
	}
	else {
	        $isHit = 1;
	        $finalData .= "var \$_db_user = '$login';\n";
	}
	if ((strpos($line,"var \$_db_password = ")) === false) {
	}
	else {
	        $isHit = 1;
	        $finalData .= "var \$_db_password = '$pass';\n";
	}
	if ((strpos($line,"var \$_db_name = ")) === false) {
	}
	else {
	        $isHit = 1;
	        $finalData .= "var \$_db_name = '$dbname';\n";
	}
	if (!$isHit) {
	        $finalData .= $line;
	}
}
$fd = @fopen($gaclConfigFile2, 'w') or die("Could not open ".$gaclConfigFile2." file.");
fwrite($fd, $finalData);
fclose($fd);

//second, run gacl config scripts		
require $gaclSetupScript1;
require $gaclSetupScript2;
echo "<br>";
 
//third, give the administrator user admin priviledges
$groupArray = array("Administrators");
set_user_aro($groupArray,$iuser,$iuname,"","");	
echo "Gave the '$iuser' user (password is 'pass') administrator access.<br>";
	
echo "<br><FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='5'>\n
<INPUT TYPE='HIDDEN' NAME='iuser' VALUE='$iuser'>\n
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";

break;

	case 0:
	default:
echo "Welcome to OpenEMR.  This utility will step you through the configuration of OpenEMR for your practice.  Before proceeding, be sure that you have a properly installed and configured MySQL server available, and a PHP configured webserver.<br><br>\n";

Echo "<p>If you are upgrading from a previous version, please read the README file.<br><br>";

echo "We will now ensure correct file permissions and directories before starting installation:<br><br>\n";
echo "<FONT COLOR='blue'>Ensuring following files are world-writable...</FONT><br>\n";
$errorWritable = 0;
foreach ($writableFileList as $tempFile) {
	if (is_writable($tempFile)) {
	        echo "'openemr/$tempFile' file is <FONT COLOR='blue'>ready</FONT>.<br>";
	}
	else {
	        echo "<br><FONT COLOR='red'>UNABLE</FONT> to open file 'openemr/$tempFile' for writing.<br>";
	        echo "(ensure 'openemr/$tempFile' file is world-writeable)<br>";
	        $errorWritable = 1;
	}
}
if ($errorWritable) {
	echo "<br><FONT COLOR='red'>You can't proceed until all above files are ready.</FONT><br>";
        echo "Fix above file permissions and then click the 'Check Again' button to re-check files.<br>";
	echo "In linux, recommend changing file permissions temporarily with the 'chmod 666 filename' command.<br>";
	echo "<FORM METHOD='POST'><INPUT TYPE='SUBMIT' VALUE='Check Again'><br></FORM><br>\n";
	break;
}

echo "<br><FONT COLOR='blue'>Ensuring following directories exist...</FONT><br>\n";
$errorWritable = 0;
foreach ($writableDirList as $tempDir) {
	if (file_exists($tempDir)) {
	        echo "'openemr/$tempDir' directory <FONT COLOR='blue'>exists</FONT>.<br>";
	}
	else {
	        echo "<br><FONT COLOR='red'>UNABLE</FONT> to find directory 'openemr/$tempDir'.<br>";
	        echo "(please create 'openemr/$tempDir' directory)<br>";
	        $errorWritable = 1;
        }
}
if ($errorWritable) {
	echo "<br><FONT COLOR='red'>You can't proceed until all above directories exist.</FONT><br>";
	echo "Add above marked directories and then click the 'Check Again' button to re-check for directories.<br>";
	echo "In linux, recommend using the 'mkdir directory_name' command<br><br>\n";
	echo "<FORM METHOD='POST'><INPUT TYPE='SUBMIT' VALUE='Check Again'><br></FORM><br>\n";
	break;
}	

echo "<br><FONT COLOR='blue'>Ensuring following directories have proper permissions...</FONT><br>\n";
$errorWritable = 0;
foreach ($writableDirList as $tempDir) {
	if (is_writable($tempDir)) {
	        echo "'openemr/$tempDir' directory is <FONT COLOR='blue'>ready</FONT>.<br>";
	}
	else {
	        echo "<br><FONT COLOR='red'>UNABLE</FONT> to open directory 'openemr/$tempDir' for writing.<br>";
	        echo "(ensure 'openemr/$tempDir' directory is world-writeable)<br>";
	        $errorWritable = 1;
	}
}
if ($errorWritable) {
	echo "<br><FONT COLOR='red'>You can't proceed until all directories are ready.</FONT><br>";
	echo "Fix above directory permissions and then click the 'Check Again' button to re-check directories.<br>";
	echo "In linux, recommend changing owners of directories permanently to the web server. For example, in many linux OS's the web server user is 'apache', 'nobody', or 'www-data'. So if 'apache' were the web server user name, could use the command 'chown -R apache:apache directory_name' command.<br><br>\n";
	echo "<FORM METHOD='POST'><INPUT TYPE='SUBMIT' VALUE='Check Again'><br></FORM><br>\n";
	break;
}

echo "<br><br>All required files and directories have been verified. Click to continue installation.<br>\n";	
echo "<FORM METHOD='POST'><INPUT TYPE='HIDDEN' NAME='state' VALUE='1'><INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>";


}
}
?>

</span>

</BODY>
</HTML>
