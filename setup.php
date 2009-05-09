<?php
//required for normal operation because of recent changes in PHP:
extract($_GET);
extract($_POST);
//turn off PHP compatibility warnings
ini_set("session.bug_compat_warn","off");

$url = ""; 
$upgrade = 0;
$state = $_POST["state"];

//If having problems with file and directory permission
// checking, then can be manually disabled here.
$checkPermissions = "TRUE";

//Below section is only for variables that require a path.
// The $manualPath variable can be edited by 3rd party
// installation scripts to manually set path. (this will
// allow straightforward use of this script by 3rd party
// installers)
$manualPath = "";
$dumpfile = $manualPath."sql/database.sql";
$translations_dumpfile_utf8 = $manualPath."contrib/util/language_translations/currentLanguage_utf8.sql";
$translations_dumpfile_latin1 = $manualPath."contrib/util/language_translations/currentLanguage_latin1.sql";
$icd9 = $manualPath."sql/icd9.sql";
$conffile = $manualPath."library/sqlconf.php";
$conffile2 = $manualPath."interface/globals.php";
$docsDirectory = $manualPath."documents";
$billingDirectory = $manualPath."edi";
$billingDirectory2 = $manualPath."era";
$billingLogDirectory = $manualPath."library/freeb";
$lettersDirectory = $manualPath."custom/letter_templates";
$gaclWritableDirectory = $manualPath."gacl/admin/templates_c";
$requiredDirectory1 = $manualPath."interface/main/calendar/modules/PostCalendar/pntemplates/compiled";
$requiredDirectory2 = $manualPath."interface/main/calendar/modules/PostCalendar/pntemplates/cache";
$gaclSetupScript1 = $manualPath."gacl/setup.php";
$gaclSetupScript2 = $manualPath."acl_setup.php";

//These are files and dir checked before install for
// correct permissions.
$writableFileList = array($conffile, $conffile2);
$writableDirList = array($docsDirectory, $billingDirectory, $billingDirectory2, $billingLogDirectory, $lettersDirectory, $gaclWritableDirectory, $requiredDirectory1, $requiredDirectory2);

//These are the dumpfiles that are loaded into database 
// The subsequent array holds the title of dumpfiles
$dumpfiles = array($dumpfile);
$dumpfilesTitles = array("Main");

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
 if ($state == 7) {
?>

<p>Congratulations! OpenEMR is now installed.</p>

<ul>
 <li>Access controls (php-GACL) are installed for fine-grained security, and can be administered in
     OpenEMR's admin->acl menu.</li>
 <li>Reading openemr/includes/config.php and openemr/interface/globals.php is a good idea. These files
     contain many options to choose from including themes.</li>
 <li>There's much information and many extra tools bundled within the OpenEMR installation directory. 
     Please refer to openemr/Documentation. Many forms and other useful scripts can be found at openemr/contrib.</li>
 <li>To ensure a consistent look and feel through out the application using 
     <a href='http://www.mozilla.org/products/firefox/'>Firefox</a> is recommended.</li>
 <li>The OpenEMR project home page and wiki can be found at <a href = "http://www.oemr.org" target="_blank">http://www.oemr.org</a></li>
 <li>The OpenEMR forums can be found at <a href = "http://sourceforge.net/projects/openemr" target="_blank">http://sourceforge.net/projects/openemr</a></li>
 <li>We pursue grants to help fund the future development of OpenEMR.  To apply for these grants, we need to estimate how many times this program is installed and how many practices are evaluating or using this software.  It would be awesome if you would email us at <a href="mailto:drbowen@charter.net">drbowen@charter.net</a> if you have installed this software. The more details about your plans with this software, the better, but even just sending us an email stating you just installed it is very helpful.</li>
</ul>
<p>
We recommend you print these instructions for future reference.
</p>	
<p>
<b>The initial OpenEMR user is "<?php echo $iuser; ?>" and the password is "pass".</b>
You should change this password!
</p>
<p>
If you edited the PHP or Apache configuration files during this installation process, then we recommend you restart your Apache server before following below OpenEMR link.
</p>
<p>
 <a href='./'>Click here to start using OpenEMR. </a>
</p>

<?php
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
	$collate = $_POST["collate"];
	$rootpass = $_POST["rootpass"];
  $iuser = $_POST["iuser"];
	$iuname = $_POST["iuname"];
	$igroup = $_POST["igroup"];
	$openemrBasePath = $_POST["openemrBasePath"];
	$openemrWebPath = $_POST["openemrWebPath"];
	//END POST VARIABLES


if (($config == 1) && ($state < 4)) {
	echo "OpenEMR has already been installed.  If you wish to force re-installation, then edit $conffile(change the 'config' variable to 0), and re-run this script.<br>\n";
}
else {
switch ($state) {

	case 1:
echo "<b>Step $state</b><br><br>\n";
echo "Now I need to know whether you want me to create the database on my own or if you have already created the database for me to use.  For me to create the database, you will need to supply the MySQL root password.\n
<span class='title'> <br />NOTE: clicking on \"Continue\" may delete or cause damage to data on your system. Before you continue please backup your data.</span>
<br><br>\n
<FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='2'>\n
<INPUT TYPE='RADIO' NAME='inst' VALUE='1' checked>Have setup create the database<br>\n
<INPUT TYPE='RADIO' NAME='inst' VALUE='2'>I have already created the database<br>\n
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";
break;

	case 2:
echo "<b>Step $state</b><br><br>\n";
echo "Now you need to supply the MySQL server information and path information. Detailed instructions on each item can be found in the <a href='INSTALL' target='_blank'><span STYLE='text-decoration: underline;'>'INSTALL'</span></a> manual file.
<br><br>\n
<FORM METHOD='POST'>
<INPUT TYPE='HIDDEN' NAME='state' VALUE='3'>
<INPUT TYPE='HIDDEN' NAME='inst' VALUE='$inst'>
<TABLE>\n
<TR VALIGN='TOP'><TD COLSPAN=2><font color='red'>MYSQL SERVER:</font></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Server Host: </span></TD><TD><INPUT TYPE='TEXT' VALUE='localhost' NAME='server' SIZE='30'></TD><TD><span class='text'>(If you run MySQL and Apache/PHP on the same computer, then leave this as 'localhost'. If they are on separate computers, then enter the IP address of the computer running MySQL.)</span><br></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Server Port: </span></TD><TD><INPUT TYPE='TEXT' VALUE='3306' NAME='port' SIZE='30'></TD><TD><span class='text'>(This is the MySQL port. The default port for MySQL is 3306.)</span><br></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Database Name: </span></TD><TD><INPUT TYPE='TEXT' VALUE='openemr' NAME='dbname' SIZE='30'></TD><TD><span class='text'>(This is the name of the OpenEMR database in MySQL - 'openemr' is the recommended)</span><br></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Login Name: </span></TD><TD><INPUT TYPE='TEXT' VALUE='openemr' NAME='login' SIZE='30'></TD><TD><span class='text'>(This is the name of the OpenEMR login name in MySQL - 'openemr' is the recommended)</span><br></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Password: </span></TD><TD><INPUT TYPE='PASSWORD' VALUE='' NAME='pass' SIZE='30'></TD><TD><span class='text'>(This is the Login Password for when PHP accesses MySQL - it should be at least 8 characters long and composed of both numbers and letters)</span><br></TD></TR>\n";
if ($inst != 2) {
echo "<TR VALIGN='TOP'><TD><span class='text'>Name for Root Account: </span></TD><TD><INPUT TYPE='TEXT' VALUE='root' NAME='root' SIZE='30'></TD><TD><span class='text'>(This is name for MySQL root account. For localhost, it is usually ok to leave it 'root'.)</span><br></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Root Pass: </span></TD><TD><INPUT TYPE='PASSWORD' VALUE='' NAME='rootpass' SIZE='30'></TD><TD><span class='text'>(This is your MySQL root password. For localhost, it is usually ok to leave it blank.)</span><br></TD></TR>\n";
echo "<TR VALIGN='TOP'><TD><span class='text'>User Hostname: </span></TD><TD><INPUT TYPE='TEXT' VALUE='localhost' NAME='loginhost' SIZE='30'></TD><TD><span class='text'>(If you run Apache/PHP and MySQL on the same computer, then leave this as 'localhost'. If they are on separate computers, then enter the IP address of the computer running Apache/PHP.)</span><br></TD></TR>";
echo "<TR VALIGN='TOP'><TD><span class='text'>UTF-8 Collation: </span></TD><TD>" .
  "<select name='collate'>" .
  "<option value='utf8_bin'          >Bin</option>" .
  "<option value='utf8_czech_ci'     >Czech</option>" .
  "<option value='utf8_danish_ci'    >Danish</option>" .
  "<option value='utf8_esperanto_ci' >Esperanto</option>" .
  "<option value='utf8_estonian_ci'  >Estonian</option>" .
  "<option value='utf8_general_ci' selected>General</option>" .
  "<option value='utf8_hungarian_ci' >Hungarian</option>" .
  "<option value='utf8_icelandic_ci' >Icelandic</option>" .
  "<option value='utf8_latvian_ci'   >Latvian</option>" .
  "<option value='utf8_lithuanian_ci'>Lithuanian</option>" .
  "<option value='utf8_persian_ci'   >Persian</option>" .
  "<option value='utf8_polish_ci'    >Polish</option>" .
  "<option value='utf8_roman_ci'     >Roman</option>" .
  "<option value='utf8_romanian_ci'  >Romanian</option>" .
  "<option value='utf8_slovak_ci'    >Slovak</option>" .
  "<option value='utf8_slovenian_ci' >Slovenian</option>" .
  "<option value='utf8_spanish2_ci'  >Spanish2 (Traditional)</option>" .
  "<option value='utf8_spanish_ci'   >Spanish (Modern)</option>" .
  "<option value='utf8_swedish_ci'   >Swedish</option>" .
  "<option value='utf8_turkish_ci'   >Turkish</option>" .
  "<option value='utf8_unicode_ci'   >Unicode (German, French, Russian, Armenian, Greek)</option>" .
  "<option value=''                  >None (Do not force UTF-8)</option>" .
  "</select>" .
  "</TD><TD><span class='text'>(Leave as General if you are not sure)</span><br></TD></TR>";
}
echo "<TR VALIGN='TOP'><TD>&nbsp;</TD></TR>";
echo "<TR VALIGN='TOP'><TD COLSPAN=2><font color='red'>OPENEMR USER:</font></TD></TR>";
echo "<TR VALIGN='TOP'><TD><span class='text'>Initial User:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='iuser' VALUE='admin'></TD><TD><span class='text'>(This is the login name of user that will be created for you. Limit this to one word.)</span></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Initial User's Name:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='iuname' VALUE='Administrator'></TD><TD><span class='text'>(This is the real name of the 'initial user'.)</span></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Initial Group:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='igroup' VALUE='Default'></TD><TD><span class='text'>(This is the group that will be created for your users.  This should be the name of your practice.)</span></TD></TR>
";
echo "<TR VALIGN='TOP'><TD>&nbsp;</TD></TR>";
echo "<TR VALIGN='TOP'><TD COLSPAN=2><font color='red'>OPENEMR PATHS:</font></TD></TR>";
echo "<TR VALIGN='TOP'><TD COLSPAN=3></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Absolute Path:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='openemrBasePath' VALUE='".realpath('./')."'></TD><TD><span class='text'>(This is the full absolute directory path to openemr. The value here is automatically created, and should not need to be modified. Do not worry about direction of slashes; they will be automatically corrected.)</span></TD></TR>
<TR VALIGN='TOP'><TD><span class='text'>Relative HTML Path:</span></TD><TD><INPUT SIZE='30' TYPE='TEXT' NAME='openemrWebPath' VALUE='/openemr'></TD><TD><span class='text'>(Set this to the relative html path, ie. what you would type into the web browser after the server address to get to OpenEMR. For example, if you type 'http://127.0.0.1/clinic/openemr/ to load OpenEMR, set this to '/clinic/openemr' without the trailing slash. Do not worry about direction of slashes; they will be automatically corrected.)</span></TD></TR>
";
echo "</TABLE>
<br>
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>";

break;


	case 3:

	if ($login == "" || !isset($login)) {
		echo "ERROR. Please pick a proper 'Login Name'.<br>\n";
	        echo "Click Back in browser to re-enter.<br>\n";
		break;
	}
        if (strpos($iuser, " ")) {
	        echo "ERROR. The 'Initial User' field can only contain one word and no spaces.<br>\n";
	        echo "Click Back in browser to re-enter.<br>\n";
	        break;
	}	
      	if ($pass == "" || !isset($pass)) {
	        echo "ERROR. Please pick a proper 'Password'.<br>\n";
	        echo "Click Back in browser to re-enter.<br>\n";
	        break;
	}	

echo "<b>Step $state</b><br><br>\n";
echo "Configuring OpenEMR...<br><br>\n";
	
	
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
  $sql = "create database $dbname";
  if ($collate) {
    $sql .= " character set utf8 collate $collate";
    mysql_query("SET NAMES 'utf8'", $dbh);
  }
	if (mysql_query($sql, $dbh) == FALSE) {
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
else {
  if ($collate) {
    mysql_query("SET NAMES 'utf8'", $dbh);
  }
	echo "OK.<br>\n";
}
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

    //select the correct translation dumpfile
    if ($collate) {
        array_push($dumpfiles,$translations_dumpfile_utf8);
	array_push($dumpfilesTitles,"Language Translation (utf8)");
    }
    else {
        array_push($dumpfiles,$translations_dumpfile_latin1);
        array_push($dumpfilesTitles,"Language Translation (latin1)");
    }
    
    $dumpfileCounter = 0;
    foreach ($dumpfiles as $var) {
        echo "Creating ".$dumpfilesTitles[$dumpfileCounter]." tables...\n";
	mysql_query("USE $dbname",$dbh);
	flush();
	$fd = fopen($var, 'r');
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
	$dumpfileCounter++;
    }
	echo "Adding Initial User...\n";
	flush();
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

echo "<br>Writing SQL Configuration...<br>";	
@touch($conffile); // php bug
$fd = @fopen($conffile, 'w');
$string = "<?php
//  OpenEMR
//  MySQL Config
//  Referenced from sql.inc

";

$it_died = 0;   //fmg: variable keeps running track of any errors

fwrite($fd,$string) or $it_died++;
fwrite($fd,"\$host\t= '$server';\n") or $it_died++;
fwrite($fd,"\$port\t= '$port';\n") or $it_died++;
fwrite($fd,"\$login\t= '$login';\n") or $it_died++;
fwrite($fd,"\$pass\t= '$pass';\n") or $it_died++;
fwrite($fd,"\$dbase\t= '$dbname';\n\n") or $it_died++;
fwrite($fd,"//added utf8 flag - bm 05-2009\n") or $it_died++;
fwrite($fd,"\$utf8_flag = ".($collate ? "true" : "false").";\n") or $it_died++;

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
?><?php // done just for coloring

fwrite($fd,$string) or $it_died++;

//it's rather irresponsible to not report errors when writing this file.
if ($it_died != 0) {
        echo "ERROR. Couldn't write $it_died lines to config file '$conffile'.\n";
        flush();
        break;
}
fclose($fd);

echo "Successfully wrote SQL configuration.<BR><br>";

echo "Writing OpenEMR webserver paths to config file...<br>";
//edit interface/globals.php
//first, ensure slashes are in correct direction (windows specific fix)
$openemrBasePath = str_replace('\\\\', '/', $openemrBasePath);
$openemrBasePath = str_replace('\\', '/', $openemrBasePath);
$openemrWebPath = str_replace('\\\\', '/', $openemrWebPath);
$openemrWebPath = str_replace('\\', '/', $openemrWebPath);
//second, edit file (web paths and set UTF8 if pertinent)
$data = file($conffile2) or die("Could not read ".$conffile2." file.");
$finalData = "";
$isCount = 0;
foreach ($data as $line) {
	$isHit = 0;
        if ((strpos($line,"\$webserver_root = \"")) === false) {
	}
	else {
	        $isHit = 1;
	        $isCount += 1;
	        $finalData .= "\$webserver_root = \"$openemrBasePath\";\n";
	}
        if ((strpos($line,"\$web_root = \"")) === false) {
	}
	else {
	        $isHit = 1;
	        $isCount += 1;
	        $finalData .= "\$web_root = \"$openemrWebPath\";\n";
	}
        if (!$isHit) {
	        $finalData .= $line;
	}
}
$fd = @fopen($conffile2, 'w') or die("Could not open ".$conffile2." file.");
fwrite($fd, $finalData);
fclose($fd);
if ($isCount == 2) {
	echo "Successfully wrote OpenEMR webserver paths to config file<br><br>";
}
else {
	echo "<FONT COLOR='red'>ERROR</FONT> writing openemr webserver root paths to config file ($conffile2). ($isCount)<br><br>\n";
}
	
echo "\n<br>Next step will install and configure access controls (php-GACL).<br>\n";
	
echo "
<FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='4'>
<INPUT TYPE='HIDDEN' NAME='iuser' VALUE='$iuser'>
<INPUT TYPE='HIDDEN' NAME='iuname' VALUE='$iuname'>
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";
	
	
break;	

        case 4:
echo "<b>Step $state</b><br><br>\n";
echo "Installing and Configuring Access Controls (php-GACL)...<br><br>";								      
									       
//run gacl config scripts, all sql config data now in sqlconf.php file		
require $gaclSetupScript1;
require $gaclSetupScript2;
echo "<br>";
 
//give the administrator user admin priviledges
$groupArray = array("Administrators");
set_user_aro($groupArray,$iuser,$iuname,"","");	
echo "Gave the '$iuser' user (password is 'pass') administrator access.<br><br>";

echo "Done installing and configuring access controls (php-GACL).<br>";
echo "Next step will configure PHP.";

echo "<br><FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='5'>\n
<INPUT TYPE='HIDDEN' NAME='iuser' VALUE='$iuser'>\n	
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";

break;

	case 5:
echo "<b>Step $state</b><br><br>\n";
echo "Configuration of PHP...<br><br>\n";
echo "We recommend making the following changes to your PHP installation, which can normally be done by editing the php.ini configuration file:\n";
echo "<ul>";
$gotFileFlag = 0;
if (version_compare(PHP_VERSION, '5.2.4', '>=')) {
    $phpINIfile = php_ini_loaded_file();
    if ($phpINIfile) {
        echo "<li><font color='green'>Your php.ini file can be found at ".$phpINIfile."</font></li>\n";
        $gotFileFlag = 1;
    }
}
echo "<li>To ensure proper functioning of OpenEMR you must make sure that settings in php.ini file include  \"short_open_tag = On\", \"display_errors = Off\", \"register_globals = Off\", \"magic_quotes_gpc = On\", \"max_execution_time\" set to at least 60, \"max_input_time\" set to at least 90, and \"memory_limit\" set to at least \"128M\".</li>\n";
echo "<li>In order to take full advantage of the patient documents capability you must make sure that settings in php.ini file include \"file_uploads = On\", that \"upload_max_filesize\" is appropriate for your use and that \"upload_tmp_dir\" is set to a correct value that will work on your system.</li>\n";
if (!$gotFileFlag) {
    echo "<li>If you are having difficulty finding your php.ini file, then refer to the <a href='INSTALL' target='_blank'><span STYLE='text-decoration: underline;'>'INSTALL'</span></a> manual for suggestions.</li>\n";
}
echo "</ul>";
	   
echo "<br>We recommend you print these instructions for future reference.<br><br>";
echo "Next step will configure Apache web server.";
						 
echo "<br><FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='6'>\n
<INPUT TYPE='HIDDEN' NAME='iuser' VALUE='$iuser'>\n
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";
							     
break;

       case 6:
echo "<b>Step $state</b><br><br>\n";
echo "Configuration of Apache web server...<br><br>\n";
echo "The \"".realpath($docsDirectory)."\", \"".realpath($billingDirectory)."\" and \"".realpath($billingDirectory2)."\" directories contain patient information, and
it is important to secure these directories. This can be done by placing pertinent .htaccess
files in these directories or by pasting the below to end of your apache configuration file:<br>
&nbsp;&nbsp;&lt;Directory ".realpath($docsDirectory)."&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;order deny,allow<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Deny from all<br>
&nbsp;&nbsp;&lt;/Directory&gt;<br>
&nbsp;&nbsp;&lt;Directory ".realpath($billingDirectory)."&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;order deny,allow<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Deny from all<br>
&nbsp;&nbsp;&lt;/Directory&gt;<br>
&nbsp;&nbsp;&lt;Directory ".realpath($billingDirectory2)."&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;order deny,allow<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Deny from all<br>
&nbsp;&nbsp;&lt;/Directory&gt;<br><br>";
				      
echo "If you are having difficulty finding your apache configuration file, then refer to the <a href='INSTALL' target='_blank'><span STYLE='text-decoration: underline;'>'INSTALL'</span></a> manual for suggestions.<br><br>\n";
echo "<br>We recommend you print these instructions for future reference.<br><br>";
echo "Click 'continue' for further instructions.";
	
echo "<br><FORM METHOD='POST'>\n
<INPUT TYPE='HIDDEN' NAME='state' VALUE='7'>\n
<INPUT TYPE='HIDDEN' NAME='iuser' VALUE='$iuser'>\n
<br>\n
<INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>\n";

break;	
	
	case 0:
	default:
echo "<p>Welcome to OpenEMR.  This utility will step you through the installation and configuration of OpenEMR for your practice.</p>\n";
echo "<ul><li>Before proceeding, be sure that you have a properly installed and configured MySQL server available, and a PHP configured webserver.</li>\n";

echo "<li>Detailed installation instructions can be found in the <a href='INSTALL' target='_blank'><span STYLE='text-decoration: underline;'>'INSTALL'</span></a> manual file.</li>\n";	

Echo "<li>If you are upgrading from a previous version, do NOT use this script.  Please read the 'Upgrading' section found in the <a href='INSTALL' target='_blank'><span STYLE='text-decoration: underline;'>'INSTALL'</span></a> manual file.</li></ul>";

if ($checkPermissions == "TRUE") {
	echo "<p>We will now ensure correct file and directory permissions before starting installation:</p>\n";
	echo "<FONT COLOR='green'>Ensuring following files are world-writable...</FONT><br>\n";
	$errorWritable = 0;
	foreach ($writableFileList as $tempFile) {
		if (is_writable($tempFile)) {
	        	echo "'".realpath($tempFile)."' file is <FONT COLOR='green'><b>ready</b></FONT>.<br>\n";
		}
		else {
	        	echo "<p><FONT COLOR='red'>UNABLE</FONT> to open file '".realpath($tempFile)."' for writing.<br>\n";
	        	echo "(configure file permissions; see below for further instructions)</p>\n";
	        	$errorWritable = 1;
		}
	}
	if ($errorWritable) {
		echo "<p><FONT COLOR='red'>You can't proceed until all above files are ready (world-writable).</FONT><br>\n";	
		echo "In linux, recommend changing file permissions with the 'chmod 666 filename' command.<br>\n";
		echo "Fix above file permissions and then click the 'Check Again' button to re-check files.<br>\n";
		echo "<FORM METHOD='POST'><INPUT TYPE='SUBMIT' VALUE='Check Again'></p></FORM><br>\n";
		break;
	}

	echo "<br><FONT COLOR='green'>Ensuring following directories have proper permissions...</FONT><br>\n";
	$errorWritable = 0;
	foreach ($writableDirList as $tempDir) {
		if (is_writable($tempDir)) {
	        	echo "'".realpath($tempDir)."' directory is <FONT COLOR='green'><b>ready</b></FONT>.<br>\n";
		}
		else {
		        echo "<p><FONT COLOR='red'>UNABLE</FONT> to open directory '".realpath($tempDir)."' for writing by web server.<br>\n";
		       	echo "(configure directory permissions; see below for further instructions)</p>\n";
	 	   	$errorWritable = 1;
		}
	}
	if ($errorWritable) {
		echo "<p><FONT COLOR='red'>You can't proceed until all directories are ready.</FONT><br>\n";
		echo "In linux, recommend changing owners of these directories to the web server. For example, in many linux OS's the web server user is 'apache', 'nobody', or 'www-data'. So if 'apache' were the web server user name, could use the command 'chown -R apache:apache directory_name' command.<br>\n";
	        echo "Fix above directory permissions and then click the 'Check Again' button to re-check directories.<br>\n";
	       	echo "<FORM METHOD='POST'><INPUT TYPE='SUBMIT' VALUE='Check Again'></p></FORM><br>\n";
		break;
	}

	echo "<br>All required files and directories have been verified. Click to continue installation.<br>\n";	
}
else {
	echo "<br>Click to continue installation.<br>\n";
}

echo "<FORM METHOD='POST'><INPUT TYPE='HIDDEN' NAME='state' VALUE='1'><INPUT TYPE='SUBMIT' VALUE='Continue'><br></FORM><br>";


}
}
?>

</span>

</BODY>
</HTML>
