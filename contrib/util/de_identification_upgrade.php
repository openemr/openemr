<?php
/********************************************************************************\
 * Copyright (C) ViCarePlus, Visolve (vicareplus_engg@visolve.com)              *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
/********************************************************************************/

// Disable PHP timeout.  This will not work in safe mode.
ini_set('max_execution_time', '0');

$ignoreAuth = true; // no login required

//set de_identification_config to 1 to run the de_identification_upgrade script
$de_identification_config = 0;

require_once('../../interface/globals.php');
require_once('../../library/sql.inc');
require_once('../../library/sqlconf.php');

function tableExists_de($tblname) {
  $row = sqlQuery("SHOW TABLES LIKE '$tblname'");
  if (empty($row)) return false;
  return true;
}

function upgradeFromSqlFile_de($filename) {
  global $webserver_root;

  flush();
  echo "<font color='green'>"; echo xl('Processing'); echo " ".$filename."...</font><br />\n";

  $fullname = "$webserver_root/sql/$filename";

  $fd = fopen($fullname, 'r');
  if ($fd == FALSE) {
    echo xl("Error, unable to open file"); echo " ".$fullname."\n";
    flush();
    break;
  }

  $query = "";
  $line = "";
  $skipping = false;
  $proc = 0;

  while (!feof ($fd)){
    $line = fgets($fd, 2048);
    $line = rtrim($line);

    if (preg_match('/^\s*--/', $line)) continue;
    if ($line == "") continue;

    if (preg_match('/^#IfNotTable\s+(\S+)/', $line, $matches)) {
      $skipping = tableExists_de($matches[1]);
      if ($skipping) echo "<font color='green'>"; echo xl('Skipping section'); echo " ".$line."</font><br />\n";
    }
    else if (preg_match('/^#EndIf/', $line)) {
      $skipping = false;
    }
    if (preg_match('/^\s*#/', $line)) continue;
    if ($skipping) continue;

    if($proc == 1) $query .= "\n";
    $query = $query . $line;
      
  if (substr($query, -1) == '$')
  {
  $query = rtrim($query, '$');
  if($proc == 0)
  $proc = 1;
  else 
  {
    $proc = 0; //executes procedures and functions
    if (!sqlStatement($query))
      {
        echo "<font color='red'>"; echo xl("The above statement failed"); echo ": " .
          mysql_error() . "<br />"; echo xl("Upgrading will continue"); echo ".<br /></font>\n";
      }
         $query = '';
     
    }
   }
    
    if (substr($query, -1) == ';'and $proc == 0) {
      $query = rtrim($query, ';');
      echo "$query<br />\n";  //executes sql statements
      if (!sqlStatement($query)) {
        echo "<font color='red'>"; echo xl("The above statement failed"); echo ": " .
          mysql_error() . "<br />"; echo xl("Upgrading will continue"); echo ".<br /></font>\n";
      }
      $query = '';
    }
  }
  flush();
} // end function


$sqldir = "$webserver_root/sql";
$dh = opendir($sqldir);
if (! $dh) die( xl("Cannot read","e")." ".$sqldir);
closedir($dh);
?>
<html>
<head>
<title><?php xl('OpenEMR Database Upgrade','e'); ?></title>
<link rel='STYLESHEET' href='../../interface/themes/style_sky_blue.css'>
</head>
<body> <br>
<center>
<span class='title'><?php xl('OpenEMR Database Upgrade for De-identification','e'); ?></span>
<br>
</center>
<?php
if (!empty($_POST['form_submit'])) {
  upgradeFromSqlFile_de("database_de_identification.sql");

  // grant file privilege to user
  if ($sqlconf["host"] == "localhost")
	$dbh = mysql_connect($sqlconf['host'],$_POST['root_user_name'],$_POST['root_user_pass'],$sqlconf['host']) or die(mysql_error());
  else
	$dbh = mysql_connect($sqlconf['host'].":".$sqlconf['port'],$_POST['root_user_name'],$_POST['root_user_pass']) or die(mysql_error());
  if ($dbh == FALSE) {
	echo "\n";
	echo "<p>".mysql_error()." (#".mysql_errno().")\n";
	break;
  }  $login=$sqlconf["login"];   $loginhost=$sqlconf["host"];
  mysql_select_db($sqlconf['dbase']) or die(mysql_error());
  if (mysql_query("GRANT FILE ON *.* TO '$login'@'$loginhost'",$dbh) == FALSE) {
		echo xl("Error when granting file privilege to the OpenEMR user."); echo "\n";
      echo "<p>".mysql_error()." (#".mysql_errno().")\n";
		echo xl("Error"); echo "\n";
		break;
	}
        else
  echo "<font color='green'>"; echo xl("File privilege granted to OpenEMR user."); echo "<br></font>\n";

  echo "<p><font color='green'>"; echo xl("Database upgrade finished."); echo "</font></p>\n";
  echo "<p><font color='red'>"; echo xl("Please restart the apache server before playing with de-identification"); echo "</font></p>\n";
  echo "<p><font color='red'>"; echo xl("Please set de_identification_config variable back to zero"); echo "</font></p>\n"; echo "</body></html>\n";
  mysql_close($dbh);
  exit();
}
?>

<script language="JavaScript">
function form_validate()
{
 if(document.forms[0].root_user_name.value == "")
 { 
  alert("<?php echo xl('Enter Database root Username');?>");
  return false;
 }
 /*if(document.forms[0].root_user_pass.value == "")
 { 
  alert("<?php echo xl('Enter Database root Password');?>");
  return false;
 }*/
 return true;
}
</script>

<center>
<form method='post' action='de_identification_upgrade.php' onsubmit="return form_validate();">
</br>
<p><?php  if($de_identification_config != 1)
{
 echo "<p><font color='red'>";
 echo xl("Please set"); echo " 'de_identification_config' "; echo xl("variable to one to run de-identification upgrade script"); echo "</br></br>";
 echo "([OPENEMR]/contrib/util/de_identification_upgrade.php)";
}
else 
{
  xl('Upgrades the OpenEMR database to include Procedures, Functions and tables needed for De-identification process','e');?></p></br>
        <table class="de_id_upgrade_login" align="center">
	<tr><td>&nbsp;</td><td colspan=3 align=center>&nbsp;</td><td>&nbsp;</td></tr> 
	<tr valign="top">
		<td>&nbsp;</td>
		<td><?php xl('Enter Database root Username','e'); ?></td>
		<td>:</td>
		<td> <input type='text' size='20' name='root_user_name' id='root_user_name'
			value= "" title="<?php xl('Enter Database root Username','e'); ?>" /> </td>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td><?php xl('Enter Database root Password','e'); ?></td>
		<td>:</td>
		<td><input type='password' size='20' name='root_user_pass' id='root_user_pass'
			value= "" title="<?php xl('Enter Database root Password','e'); ?>" /> </td>
		<td>&nbsp;</td>
	</tr>   
	<tr><td>&nbsp;</td><td colspan=3 align=center>&nbsp;</td><td>&nbsp;</td></tr>  

	</table>
<p><input type='submit' name='form_submit' value="<?php xl('Upgrade Database','e');?>"  /></p>
<?php } ?>
</form>
</center>
</body>
</html>
