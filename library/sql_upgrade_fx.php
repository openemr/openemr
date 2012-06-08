<?php
// Copyright (C) 2008-2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Functions to allow safe database and global modifications
// during upgrading and patches
//

function tableExists($tblname) {
  $row = sqlQuery("SHOW TABLES LIKE '$tblname'");
  if (empty($row)) return false;
  return true;
}

function columnExists($tblname, $colname) {
  $row = sqlQuery("SHOW COLUMNS FROM $tblname LIKE '$colname'");
  if (empty($row)) return false;
  return true;
}

function columnHasType($tblname, $colname, $coltype) {
  $row = sqlQuery("SHOW COLUMNS FROM $tblname LIKE '$colname'");
  if (empty($row)) return true;
  return (strcasecmp($row['Type'], $coltype) == 0);
}

function tableHasRow($tblname, $colname, $value) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value'");
  return $row['count'] ? true : false;
}

function tableHasRow2D($tblname, $colname, $value, $colname2, $value2) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value' AND $colname2 LIKE '$value2'");
  return $row['count'] ? true : false;
}

function tableHasRow3D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value' AND $colname2 LIKE '$value2' AND $colname3 LIKE '$value3'");
  return $row['count'] ? true : false;
}

function tableHasRow4D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3, $colname4, $value4) {
  $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
    "$colname LIKE '$value' AND $colname2 LIKE '$value2' AND $colname3 LIKE '$value3' AND $colname4 LIKE '$value4'");
  return $row['count'] ? true : false;
}

function tableHasIndex($tblname, $colname) {
  $row = sqlQuery("SHOW INDEX FROM `$tblname` WHERE `Key_name` = '$colname'");
  return (empty($row)) ? false : true;
}

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
