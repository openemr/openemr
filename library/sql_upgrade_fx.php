<?php
/**
* Upgrading and patching functions of database.
*
* Functions to allow safe database modifications
* during upgrading and patches.
*
* Copyright (C) 2008-2012 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
* 
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
* @author    Brady Miller <brady@sparmy.com>
* @author  Teny <teny@zhservices.com>
* @link      http://www.open-emr.org
*/

/**
* Check if a Sql table exists.
*
* @param  string  $tblname  Sql Table Name
* @return boolean           returns true if the sql table exists
*/
function tableExists($tblname) {
  $row = sqlQuery("SHOW TABLES LIKE '$tblname'");
  if (empty($row)) return false;
  return true;
}

/**
* Check if a Sql column exists in a selected table.
*
* @param  string  $tblname  Sql Table Name
* @param  string  $colname  Sql Column Name
* @return boolean           returns true if the sql column exists
*/
function columnExists($tblname, $colname) {
  $row = sqlQuery("SHOW COLUMNS FROM $tblname LIKE '$colname'");
  if (empty($row)) return false;
  return true;
}

/**
* Check if a Sql column has a certain type.
*
* @param  string  $tblname  Sql Table Name
* @param  string  $colname  Sql Column Name
* @param  string  $coltype  Sql Column Type
* @return boolean           returns true if the sql column is of the specified type
*/
function columnHasType($tblname, $colname, $coltype) {
  $row = sqlQuery("SHOW COLUMNS FROM $tblname LIKE '$colname'");
  if (empty($row)) return true;
  return (strcasecmp($row['Type'], $coltype) == 0);
}

/**
* Check if a Sql row exists. (with one value)
*
* @param  string  $tblname  Sql Table Name
* @param  string  $colname  Sql Column Name
* @param  string  $value    Sql value
* @return boolean           returns true if the sql row does exist
*/
function tableHasRow($tblname, $colname, $value) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value'");
  return $row['count'] ? true : false;
}

/**
* Check if a Sql row exists. (with two values)
*
* @param  string  $tblname   Sql Table Name
* @param  string  $colname   Sql Column Name 1
* @param  string  $value     Sql value 1
* @param  string  $colname2  Sql Column Name 2
* @param  string  $value2    Sql value 2
* @return boolean            returns true if the sql row does exist
*/
function tableHasRow2D($tblname, $colname, $value, $colname2, $value2) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value' AND $colname2 LIKE '$value2'");
  return $row['count'] ? true : false;
}

/**
* Check if a Sql row exists. (with three values)
*
* @param  string  $tblname   Sql Table Name
* @param  string  $colname   Sql Column Name 1
* @param  string  $value     Sql value 1
* @param  string  $colname2  Sql Column Name 2
* @param  string  $value2    Sql value 2
* @param  string  $colname3  Sql Column Name 3
* @param  string  $value3    Sql value 3
* @return boolean            returns true if the sql row does exist
*/
function tableHasRow3D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value' AND $colname2 LIKE '$value2' AND $colname3 LIKE '$value3'");
  return $row['count'] ? true : false;
}

/**
* Check if a Sql row exists. (with four values)
*
* @param  string  $tblname   Sql Table Name
* @param  string  $colname   Sql Column Name 1
* @param  string  $value     Sql value 1
* @param  string  $colname2  Sql Column Name 2
* @param  string  $value2    Sql value 2
* @param  string  $colname3  Sql Column Name 3
* @param  string  $value3    Sql value 3
* @param  string  $colname4  Sql Column Name 4
* @param  string  $value4    Sql value 4
* @return boolean            returns true if the sql row does exist
*/
function tableHasRow4D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3, $colname4, $value4) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value' AND $colname2 LIKE '$value2' AND $colname3 LIKE '$value3' AND $colname4 LIKE '$value4'");
  return $row['count'] ? true : false;
}

/**
* Check if a Sql table has a certain index/key.
*
* @param  string  $tblname  Sql Table Name
* @param  string  $colname  Sql Index/Key
* @return boolean           returns true if the sql tables has the specified index/key
*/
function tableHasIndex($tblname, $colname) {
  $row = sqlQuery("SHOW INDEX FROM `$tblname` WHERE `Key_name` = '$colname'");
  return (empty($row)) ? false : true;
}

/**
* Function to migrate the Clickoptions settings (if exist) from the codebase into the database.
*  Note this function is only run once in the sql upgrade script (from 4.1.1 to 4.1.2) if the
*  issue_types sql table does not exist.
*/
function clickOptionsMigrate() {
  // If the clickoptions.txt file exist, then import it.
  if (file_exists(dirname(__FILE__)."/../sites/".$_SESSION['site_id']."/clickoptions.txt")) {
    $file_handle = fopen(dirname(__FILE__)."/../sites/".$_SESSION['site_id']."/clickoptions.txt", "rb");
    $seq  = 10;
    $prev = '';
    echo "Importing clickoption setting<br>";
    while (!feof($file_handle) ) {
      $line_of_text = fgets($file_handle);
      if (preg_match('/^#/', $line_of_text)) continue;
      if ($line_of_text == "") continue;
      $parts = explode('::', $line_of_text);
      $parts[0] = trim(str_replace("\r\n","",$parts[0]));
      $parts[1] = trim(str_replace("\r\n","",$parts[1]));
      if ($parts[0] != $prev) {
        $sql1 = "INSERT INTO list_options (`list_id`,`option_id`,`title`) VALUES (?,?,?)";
        SqlStatement($sql1, array('lists',$parts[0].'_issue_list',ucwords(str_replace("_"," ",$parts[0])).' Issue List') );
        $seq = 10;
      }
      $sql2 = "INSERT INTO list_options (`list_id`,`option_id`,`title`,`seq`) VALUES (?,?,?,?)";
      SqlStatement($sql2, array($parts[0].'_issue_list', $parts[1], $parts[1], $seq) );
      $seq = $seq + 10;
      $prev = $parts[0];
    }
    fclose($file_handle);
  }
}

/**
* Upgrade or patch the database with a selected upgrade/patch file.
*
* The following "functions" within the selected file will be processed:
*
* #IfNotTable
*   argument: table_name
*   behavior: if the table_name does not exist,  the block will be executed
*
* #IfTable
*   argument: table_name
*   behavior: if the table_name does exist, the block will be executed
*
* #IfMissingColumn
*   arguments: table_name colname
*   behavior:  if the table exists but the column does not,  the block will be executed
*
* #IfNotColumnType
*   arguments: table_name colname value
*   behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed
*
* #IfNotRow
*   arguments: table_name colname value
*   behavior:  If the table table_name does not have a row where colname = value, the block will be executed.
*
* #IfNotRow2D
*   arguments: table_name colname value colname2 value2
*   behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.
*
* #IfNotRow3D
*   arguments: table_name colname value colname2 value2 colname3 value3
*   behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.
*
* #IfNotRow4D
*   arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
*   behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.
*
* #IfNotRow2Dx2
*   desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
*   arguments: table_name colname value colname2 value2 colname3 value3
*   behavior:  The block will be executed if both statements below are true:
*              1) The table table_name does not have a row where colname = value AND colname2 = value2.
*              2) The table table_name does not have a row where colname = value AND colname3 = value3.
*
* #IfRow2D
*   arguments: table_name colname value colname2 value2
*   behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.
*   
* #IfRow3D
*   arguments: table_name colname value colname2 value2 colname3 value3
*   behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.   
*
* #IfIndex
*   desc:      This function is most often used for dropping of indexes/keys.
*   arguments: table_name colname
*   behavior:  If the table and index exist the relevant statements are executed, otherwise not.
*
* #IfNotIndex
*   desc:      This function will allow adding of indexes/keys.
*   arguments: table_name colname
*   behavior:  If the index does not exist, it will be created
*
* #IfNotMigrateClickOptions
*   Custom function for the importing of the Clickoptions settings (if exist) from the codebase into the database
*
* #EndIf
*   all blocks are terminated with a #EndIf statement.
*
* @param  string  $filename  Sql upgrade/patch filename
*/
function upgradeFromSqlFile($filename) {
  global $webserver_root;

  flush();
  echo "<font color='green'>Processing $filename ...</font><br />\n";

  $fullname = "$webserver_root/sql/$filename";

  $fd = fopen($fullname, 'r');
  if ($fd == FALSE) {
    echo "ERROR.  Could not open '$fullname'.\n";
    flush();
    break;
  }

  $query = "";
  $line = "";
  $skipping = false;

  while (!feof ($fd)){
    $line = fgets($fd, 2048);
    $line = rtrim($line);

    if (preg_match('/^\s*--/', $line)) continue;
    if ($line == "") continue;

    if (preg_match('/^#IfNotTable\s+(\S+)/', $line, $matches)) {
      $skipping = tableExists($matches[1]);
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfTable\s+(\S+)/', $line, $matches)) {
      $skipping = ! tableExists($matches[1]);
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfMissingColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = columnExists($matches[1], $matches[2]);
      }
      else {
        // If no such table then the column is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotColumnType\s+(\S+)\s+(\S+)\s+(\S+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = columnHasType($matches[1], $matches[2], $matches[3]);
      }
      else {
        // If no such table then the column type is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        // If no such index then skip.
        $skipping = !tableHasIndex($matches[1], $matches[2]);
      }
      else {
        // If no such table then skip.
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = tableHasIndex($matches[1], $matches[2]);
      }
      else {
        // If no such table then the index is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = tableHasRow($matches[1], $matches[2], $matches[3]);
      }
      else {
        // If no such table then the row is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
      }
      else {
        // If no such table then the row is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]);
      }
      else {
        // If no such table then the row is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotRow4D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = tableHasRow4D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7], $matches[8], $matches[9]);
      }
      else {
        // If no such table then the row is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotRow2Dx2\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
	// If either check exist, then will skip
	$firstCheck = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
	$secondCheck = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[6], $matches[7]);
	if ($firstCheck || $secondCheck) {
	  $skipping = true;   
	}
	else {
          $skipping = false;
	}
      }
      else {
        // If no such table then the row is deemed not "missing".
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
      if (tableExists($matches[1])) {
        $skipping = !(tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]));
      }
      else {
        // If no such table then should skip.
        $skipping = true;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
    	if (tableExists($matches[1])) {
    		$skipping = !(tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]));
    	}
    	else {
    		// If no such table then should skip.
    		$skipping = true;
    	}
    	if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#IfNotMigrateClickOptions/', $line)) {
      if (tableExists("issue_types")) {
        $skipping = true;
      }
      else {
        // Create issue_types table and import the Issue Types and clickoptions settings from codebase into the database
        clickOptionsMigrate(); 
        $skipping = false;
      }
      if ($skipping) echo "<font color='green'>Skipping section $line</font><br />\n";
    }
    else if (preg_match('/^#EndIf/', $line)) {
      $skipping = false;
    }

    if (preg_match('/^\s*#/', $line)) continue;
    if ($skipping) continue;

    $query = $query . $line;
    if (substr($query, -1) == ';') {
      $query = rtrim($query, ';');
      echo "$query<br />\n";
      if (!sqlStatement($query)) {
        echo "<font color='red'>The above statement failed: " .
          mysql_error() . "<br />Upgrading will continue.<br /></font>\n";
      }
      $query = '';
    }
  }
  flush();
} // end function

?>
