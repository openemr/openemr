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

// $ignoreAuth = true; // no login required

//set de_identification_config to 1 to run the de_identification_upgrade script
$de_identification_config = 0;

require_once('../../interface/globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;

function tableExists_de($tblname)
{
    $row = sqlQuery("SHOW TABLES LIKE '" . add_escape_custom($tblname) . "'");
    if (empty($row)) {
        return false;
    }

    return true;
}

function upgradeFromSqlFile_de($filename)
{
    global $webserver_root;

    flush();
    echo "<font color='green'>";
    echo xlt('Processing');
    echo " " . text($filename) . "...</font><br />\n";

    $fullname = "$webserver_root/sql/" . convert_safe_file_dir_name($filename);

    $fd = fopen($fullname, 'r');
    if ($fd == false) {
        echo xlt("Error, unable to open file");
        echo " " . text($fullname) . "\n";
        flush();
        exit();
    }

    $query = "";
    $line = "";
    $skipping = false;
    $proc = 0;

    while (!feof($fd)) {
        $line = fgets($fd, 2048);
        $line = rtrim($line);

        if (preg_match('/^\s*--/', $line)) {
            continue;
        }

        if ($line == "") {
            continue;
        }

        if (preg_match('/^#IfNotTable\s+(\S+)/', $line, $matches)) {
            $skipping = tableExists_de($matches[1]);
            if ($skipping) {
                echo "<font color='green'>";
            }

            echo xlt('Skipping section');
            echo " " . text($line) . "</font><br />\n";
        } elseif (preg_match('/^#EndIf/', $line)) {
            $skipping = false;
        }

        if (preg_match('/^\s*#/', $line)) {
            continue;
        }

        if ($skipping) {
            continue;
        }

        if ($proc == 1) {
            $query .= "\n";
        }

        $query = $query . $line;

        if (substr($query, -1) == '$') {
            $query = rtrim($query, '$');
            if ($proc == 0) {
                $proc = 1;
            } else {
                $proc = 0; //executes procedures and functions
                if (!sqlStatement($query)) {
                    echo "<font color='red'>";
                    echo xlt("The above statement failed"); echo ": " .
                      text(getSqlLastError()) . "<br />";
                    echo xlt("Upgrading will continue");
                    echo ".<br /></font>\n";
                }

                   $query = '';
            }
        }

        if (substr($query, -1) == ';' and $proc == 0) {
            $query = rtrim($query, ';');
            echo text($query) . "<br />\n";  //executes sql statements
            if (!sqlStatement($query)) {
                echo "<font color='red'>";
                echo xlt("The above statement failed"); echo ": " .
                text(getSqlLastError()) . "<br />";
                echo xlt("Upgrading will continue");
                echo ".<br /></font>\n";
            }

            $query = '';
        }
    }

    flush();
} // end function


$sqldir = "$webserver_root/sql";
$dh = opendir($sqldir);
if (! $dh) {
    die(xlt("Cannot read") . " " . text($sqldir));
}

closedir($dh);
?>
<html>
<head>
<title><?php echo xlt('OpenEMR Database Upgrade'); ?></title>
<link rel='STYLESHEET' href='../../interface/themes/style_sky_blue.css'>
</head>
<body> <br />
<center>
<span class='title'><?php echo xlt('OpenEMR Database Upgrade for De-identification'); ?></span>
<br />
</center>
<?php
if (!empty($_POST['form_submit'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    upgradeFromSqlFile_de("database_de_identification.sql");

//  grant file privilege to user

    $dbh = $GLOBALS['dbh'];

    if ($dbh == false) {
        echo "\n";
        echo "<p>" . text(getSqlLastError()) . " (#" . text(getSqlLastErrorNo()) . ")\n";
        exit();
    }  $login = $sqlconf["login"];
    $loginhost = $sqlconf["host"];
    generic_sql_select_db($sqlconf['dbase']) or die(text(getSqlLastError()));
    if (sqlStatement("GRANT FILE ON *.* TO '$login'@'$loginhost'") == false) {
        echo xlt("Error when granting file privilege to the OpenEMR user.");
        echo "\n";
        echo "<p>" . text(getSqlLastError()) . " (#" . text(getSqlLastErrorNo()) . ")\n";
        echo xlt("Error");
        echo "\n";
        exit();
    } else {
        echo "<font color='green'>";
    }

    echo xlt("File privilege granted to OpenEMR user.");
    echo "<br /></font>\n";

    echo "<p><font color='green'>";
    echo xlt("Database upgrade finished.");
    echo "</font></p>\n";
    echo "<p><font color='red'>";
    echo xlt("Please restart the apache server before playing with de-identification");
    echo "</font></p>\n";
    echo "<p><font color='red'>";
    echo xlt("Please set de_identification_config variable back to zero");
    echo "</font></p>\n";
    echo "</body></html>\n";
    sqlClose($dbh);
    exit();
}
?>

<script>
function form_validate()
{
 if(document.forms[0].root_user_name.value == "")
 {
  alert("<?php echo xls('Enter Database root Username');?>");
  return false;
 }
 /*if(document.forms[0].root_user_pass.value == "")
 {
  alert("<?php echo xls('Enter Database root Password');?>");
  return false;
 }*/
 return true;
}
</script>

<center>
<form method='post' action='de_identification_upgrade.php' onsubmit="return form_validate();">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<br />
<p><?php  if ($de_identification_config != 1) {
    echo "<p><font color='red'>";
    echo xlt("Please set");
    echo " 'de_identification_config' ";
    echo xlt("variable to one to run de-identification upgrade script");
    echo "<br /><br />";
    echo "([OPENEMR]/contrib/util/de_identification_upgrade.php)";
   } else {
       echo xlt('Upgrades the OpenEMR database to include Procedures, Functions and tables needed for De-identification process');?></p><br />
        <table class="de_id_upgrade_login" align="center">
    <tr><td>&nbsp;</td><td colspan=3 align=center>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <td>&nbsp;</td>
        <td><?php echo xlt('Enter Database root Username'); ?></td>
        <td>:</td>
        <td> <input type='text' size='20' name='root_user_name' id='root_user_name'
            value= "" title="<?php echo xla('Enter Database root Username'); ?>" /> </td>
        <td>&nbsp;</td>
    </tr>
    <tr valign="top">
        <td>&nbsp;</td>
        <td><?php echo xlt('Enter Database root Password'); ?></td>
        <td>:</td>
        <td><input type='password' size='20' name='root_user_pass' id='root_user_pass'
            value= "" title="<?php echo xlt('Enter Database root Password'); ?>" /> </td>
        <td>&nbsp;</td>
    </tr>
    <tr><td>&nbsp;</td><td colspan=3 align=center>&nbsp;</td><td>&nbsp;</td></tr>

    </table>
<p><input type='submit' name='form_submit' value="<?php echo xla('Upgrade Database');?>"  /></p>
    <?php } ?>
</form>
</center>
</body>
</html>
