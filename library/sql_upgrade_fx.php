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
* @author    Brady Miller <brady.g.miller@gmail.com>
* @author  Teny <teny@zhservices.com>
* @link      http://www.open-emr.org
*/

/**
* Check if a Sql table exists.
*
* @param  string  $tblname  Sql Table Name
* @return boolean           returns true if the sql table exists
*/
function tableExists($tblname)
{
    $row = sqlQuery("SHOW TABLES LIKE '$tblname'");
    if (empty($row)) {
        return false;
    }

    return true;
}

/**
* Check if a Sql column exists in a selected table.
*
* @param  string  $tblname  Sql Table Name
* @param  string  $colname  Sql Column Name
* @return boolean           returns true if the sql column exists
*/
function columnExists($tblname, $colname)
{
    $row = sqlQuery("SHOW COLUMNS FROM $tblname LIKE '$colname'");
    if (empty($row)) {
        return false;
    }

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
function columnHasType($tblname, $colname, $coltype)
{
    $row = sqlQuery("SHOW COLUMNS FROM $tblname LIKE '$colname'");
    if (empty($row)) {
        return true;
    }

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
function tableHasRow($tblname, $colname, $value)
{
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
function tableHasRow2D($tblname, $colname, $value, $colname2, $value2)
{
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
function tableHasRow3D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3)
{
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
function tableHasRow4D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3, $colname4, $value4)
{
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
function tableHasIndex($tblname, $colname)
{
    $row = sqlQuery("SHOW INDEX FROM `$tblname` WHERE `Key_name` = '$colname'");
    return (empty($row)) ? false : true;
}


/**
 * Check if a table has a certain engine
 *
 * @param string $tblname database table Name
 * @param string $engine engine name ( myisam, memory, innodb )...
 * @return boolean true if the table has been created using specified engine
 */
function tableHasEngine($tblname, $engine)
{
    $row = sqlQuery('SELECT 1 FROM information_schema.tables WHERE table_name=? AND engine=? AND table_type="BASE TABLE"', array($tblname,$engine ));
    return (empty($row)) ? false : true;
}



/**
* Check if a list exists.
*
* @param  string  $option_id  Sql List Option ID
* @return boolean           returns true if the list exists
*/
function listExists($option_id)
{
    $row = sqlQuery("SELECT * FROM list_options WHERE list_id = 'lists' AND option_id = ?", array($option_id));
    if (empty($row)) {
        return false;
    }

    return true;
}
/**
* Function to migrate the Clickoptions settings (if exist) from the codebase into the database.
*  Note this function is only run once in the sql upgrade script (from 4.1.1 to 4.1.2) if the
*  issue_types sql table does not exist.
*/
function clickOptionsMigrate()
{
  // If the clickoptions.txt file exist, then import it.
    if (file_exists(dirname(__FILE__)."/../sites/".$_SESSION['site_id']."/clickoptions.txt")) {
        $file_handle = fopen(dirname(__FILE__)."/../sites/".$_SESSION['site_id']."/clickoptions.txt", "rb");
        $seq  = 10;
        $prev = '';
        echo "Importing clickoption setting<br>";
        while (!feof($file_handle)) {
            $line_of_text = fgets($file_handle);
            if (preg_match('/^#/', $line_of_text)) {
                continue;
            }

            if ($line_of_text == "") {
                continue;
            }

            $parts = explode('::', $line_of_text);
            $parts[0] = trim(str_replace("\r\n", "", $parts[0]));
            $parts[1] = trim(str_replace("\r\n", "", $parts[1]));
            if ($parts[0] != $prev) {
                $sql1 = "INSERT INTO list_options (`list_id`,`option_id`,`title`) VALUES (?,?,?)";
                SqlStatement($sql1, array('lists',$parts[0].'_issue_list',ucwords(str_replace("_", " ", $parts[0])).' Issue List'));
                $seq = 10;
            }

            $sql2 = "INSERT INTO list_options (`list_id`,`option_id`,`title`,`seq`) VALUES (?,?,?,?)";
            SqlStatement($sql2, array($parts[0].'_issue_list', $parts[1], $parts[1], $seq));
            $seq = $seq + 10;
            $prev = $parts[0];
        }

        fclose($file_handle);
    }
}
/**
*  Function to create list Occupation.
*  Note this function is only run once in the sql upgrade script  if the list Occupation does not exist
*/
function CreateOccupationList()
{
    $res = sqlStatement("SELECT DISTINCT occupation FROM patient_data WHERE occupation <> ''");
    while ($row = sqlFetchArray($res)) {
        $records[] = $row['occupation'];
    }

    sqlStatement("INSERT INTO list_options (list_id, option_id, title) VALUES('lists', 'Occupation', 'Occupation')");
    if (count($records)>0) {
        $seq = 0;
        foreach ($records as $key => $value) {
            sqlStatement("INSERT INTO list_options ( list_id, option_id, title, seq) VALUES ('Occupation', ?, ?, ?)", array($value, $value, ($seq+10)));
            $seq = $seq + 10;
        }
    }
}
/**
*  Function to create list reaction.
*  Note this function is only run once in the sql upgrade script  if the list reaction does not exist
*/
function CreateReactionList()
{
    $res = sqlStatement("SELECT DISTINCT reaction FROM lists WHERE reaction <> ''");
    while ($row = sqlFetchArray($res)) {
        $records[] = $row['reaction'];
    }

    sqlStatement("INSERT INTO list_options (list_id, option_id, title) VALUES('lists', 'reaction', 'Reaction')");
    if (count($records)>0) {
        $seq = 0;
        foreach ($records as $key => $value) {
            sqlStatement("INSERT INTO list_options ( list_id, option_id, title, seq) VALUES ('reaction', ?, ?, ?)", array($value, $value, ($seq+10)));
            $seq = $seq + 10;
        }
    }
}

/*
* Function to add existing values in the immunization table to the new immunization manufacturer list
* This function will be executed always, but only missing values will ne inserted to the list
*/
function CreateImmunizationManufacturerList()
{
    $res = sqlStatement("SELECT DISTINCT manufacturer FROM immunizations WHERE manufacturer <> ''");
    while ($row = sqlFetchArray($res)) {
        $records[] = $row['manufacturer'];
    }

    sqlStatement("INSERT INTO list_options (list_id, option_id, title) VALUES ('lists','Immunization_Manufacturer','Immunization Manufacturer')");
    if (count($records)>0) {
        $seq = 0;
        foreach ($records as $key => $value) {
            sqlStatement("INSERT INTO list_options ( list_id, option_id, title, seq) VALUES ('Immunization_Manufacturer', ?, ?, ?)", array($value, $value, ($seq+10)));
            $seq = $seq + 10;
        }
    }
}

/**
 * Request to information_schema
 *
 * @param array $arg possible arguments: engine, table_name
 * @return SQLStatement
 */
function getTablesList($arg = array())
{
    $binds = array();
    $sql = 'SELECT table_name FROM information_schema.tables WHERE table_schema=database() AND table_type="BASE TABLE"';
    
    if (!empty($arg['engine'])) {
        $binds[] = $arg['engine'];
        $sql .= ' AND engine=?';
    }
    
    if (!empty($arg['table_name'])) {
        $binds[] = $arg['table_name'];
        $sql .= ' AND table_name=?';
    }

    $res = sqlStatement($sql, $binds);

    $records = array();
    while ($row = sqlFetchArray($res)) {
        $records[ $row['table_name'] ] = $row['table_name'];
    }

    return $records;
}


/**
 * Convert table engine.
 * @param string $table
 * @param string $engine
 * ADODB will fail if there was an error during conversion
 */
function MigrateTableEngine($table, $engine)
{
    $r = sqlStatement('ALTER TABLE `'.$table.'` ENGINE=?', $engine);
    return true;
}

function convertLayoutProperties()
{
    $res = sqlStatement("SELECT DISTINCT form_id FROM layout_options ORDER BY form_id");
    while ($row = sqlFetchArray($res)) {
        $form_id = $row['form_id'];
        $props = array(
        'title'    => 'Unknown',
        'mapping'  => 'Core',
        'notes'    => '',
        'activity' => '1',
        'option_value' => '0',
        );
        if (substr($form_id, 0, 3) == 'LBF') {
            $props = sqlQuery(
                "SELECT title, mapping, notes, activity, option_value FROM list_options WHERE list_id = 'lbfnames' AND option_id = ?",
                array($form_id)
            );
            if (empty($props)) {
                continue;
            }
            if (empty($props['mapping'])) {
                $props['mapping'] = 'Clinical';
            }
        } else if (substr($form_id, 0, 3) == 'LBT') {
            $props = sqlQuery(
                "SELECT title, mapping, notes, activity, option_value FROM list_options WHERE list_id = 'transactions' AND option_id = ?",
                array($form_id)
            );
            if (empty($props)) {
                continue;
            }
            if (empty($props['mapping'])) {
                $props['mapping'] = 'Transactions';
            }
        } else if ($form_id == 'DEM') {
            $props['title'] = 'Demographics';
        } else if ($form_id == 'HIS') {
            $props['title'] = 'History';
        } else if ($form_id == 'FACUSR') {
            $props['title'] = 'Facility Specific User Information';
        } else if ($form_id == 'CON') {
            $props['title'] = 'Contraception Issues';
        } else if ($form_id == 'GCA') {
            $props['title'] = 'Abortion Issues';
        } else if ($form_id == 'SRH') {
            $props['title'] = 'IPPF SRH Data';
        }

        $query = "INSERT INTO layout_group_properties SET " .
        "grp_form_id = ?, " .
        "grp_group_id = '', " .
        "grp_title = ?, " .
        "grp_mapping = ?, " .
        "grp_activity = ?, " .
        "grp_repeats = ?";
        $sqlvars = array($form_id, $props['title'], $props['mapping'], $props['activity'], $props['option_value']);
        if ($props['notes']) {
            $jobj = json_decode($props['notes'], true);
            if (isset($jobj['columns'])) {
                $query .= ", grp_columns = ?";
                $sqlvars[] = $jobj['columns'];
            }
            if (isset($jobj['size'])) {
                $query .= ", grp_size = ?";
                $sqlvars[] = $jobj['size'];
            }
            if (isset($jobj['issue'])) {
                $query .= ", grp_issue_type = ?";
                $sqlvars[] = $jobj['issue'];
            }
            if (isset($jobj['aco'])) {
                $query .= ", grp_aco_spec = ?";
                $sqlvars[] = $jobj['aco'];
            }
            if (isset($jobj['services'])) {
                $query .= ", grp_services = ?";
              // if present but empty, means all services
                $sqlvars[] = $jobj['services'] ? $jobj['services'] : '*';
            }
            if (isset($jobj['products'])) {
                $query .= ", grp_products = ?";
              // if present but empty, means all products
                $sqlvars[] = $jobj['products'] ? $jobj['products'] : '*';
            }
            if (isset($jobj['diags'])) {
                $query .= ", grp_diags = ?";
              // if present but empty, means all diags
                $sqlvars[] = $jobj['diags'] ? $jobj['diags'] : '*';
            }
        }
        sqlStatement($query, $sqlvars);

        $gres = sqlStatement(
            "SELECT DISTINCT group_name FROM layout_options WHERE form_id = ? ORDER BY group_name",
            array($form_id)
        );

      // For each group within this layout...
        while ($grow = sqlFetchArray($gres)) {
            $group_name = $grow['group_name'];
            $group_id = '';
            $title = '';
            $a = explode('|', $group_name);
            foreach ($a as $tmp) {
                $group_id .= substr($tmp, 0, 1);
                $title = substr($tmp, 1);
            }
            sqlStatement(
                "UPDATE layout_options SET group_id = ? WHERE form_id = ? AND group_name = ?",
                array($group_id, $form_id, $group_name)
            );
            $query = "INSERT IGNORE INTO layout_group_properties SET " .
            "grp_form_id = ?, " .
            "grp_group_id = ?, " .
            "grp_title = '" . add_escape_custom($title) . "'";
          // grp_title not using $sqlvars because of a bug causing '' to become '0'.
            $sqlvars = array($form_id, $group_id);
          /****************************************************************
          if ($props['notes']) {
            if (isset($jobj['columns'])) {
              $query .= ", grp_columns = ?";
              $sqlvars[] = $jobj['columns'];
            }
            if (isset($jobj['size'])) {
              $query .= ", grp_size = ?";
              $sqlvars[] = $jobj['size'];
            }
          }
          ****************************************************************/
          // echo $query; foreach ($sqlvars as $tmp) echo " '$tmp'"; echo "<br />\n"; // debugging
            sqlStatement($query, $sqlvars);
        } // end group
    } // end form
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
* #IfColumn
*   arguments: table_name colname
*   behavior:  if the table and column exist,  the block will be executed
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
* #IfNotListOccupation
* Custom function for creating Occupation List
*
* #IfNotListReaction
* Custom function for creating Reaction List
*
* #IfTextNullFixNeeded
*   desc: convert all text fields without default null to have default null.
*   arguments: none
*
* #IfTableEngine
*   desc:      Execute SQL if the table has been created with given engine specified.
*   arguments: table_name engine
*   behavior:  Use when engine conversion requires more than one ALTER TABLE
*
* #IfInnoDBMigrationNeeded
*   desc: find all MyISAM tables and convert them to InnoDB.
*   arguments: none
*   behavior: can take a long time.
*
* #EndIf
*   all blocks are terminated with a #EndIf statement.
*
* @param  string  $filename  Sql upgrade/patch filename
*/
function upgradeFromSqlFile($filename)
{
    global $webserver_root;

    flush();
    echo "<font color='green'>Processing $filename ...</font><br />\n";

    $fullname = "$webserver_root/sql/$filename";

    $fd = fopen($fullname, 'r');
    if ($fd == false) {
        echo "ERROR.  Could not open '$fullname'.\n";
        flush();
        return;
    }

    $query = "";
    $line = "";
    $skipping = false;

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
            $skipping = tableExists($matches[1]);
            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfTable\s+(\S+)/', $line, $matches)) {
            $skipping = ! tableExists($matches[1]);
            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !columnExists($matches[1], $matches[2]);
            } else {
                // If no such table then the column is deemed "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfMissingColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = columnExists($matches[1], $matches[2]);
            } else {
                // If no such table then the column is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotColumnType\s+(\S+)\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = columnHasType($matches[1], $matches[2], $matches[3]);
            } else {
                // If no such table then the column type is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                // If no such index then skip.
                $skipping = !tableHasIndex($matches[1], $matches[2]);
            } else {
                // If no such table then skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasIndex($matches[1], $matches[2]);
            } else {
                // If no such table then the index is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow($matches[1], $matches[2], $matches[3]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotRow4D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow4D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7], $matches[8], $matches[9]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotRow2Dx2\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
            // If either check exist, then will skip
                $firstCheck = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
                $secondCheck = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[6], $matches[7]);
                if ($firstCheck || $secondCheck) {
                    $skipping = true;
                } else {
                      $skipping = false;
                }
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !(tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]));
            } else {
                // If no such table then should skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !(tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]));
            } else {
                // If no such table then should skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotMigrateClickOptions/', $line)) {
            if (tableExists("issue_types")) {
                $skipping = true;
            } else {
                // Create issue_types table and import the Issue Types and clickoptions settings from codebase into the database
                clickOptionsMigrate();
                $skipping = false;
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotListOccupation/', $line)) {
            if ((listExists("Occupation")) || (!columnExists('patient_data', 'occupation'))) {
                $skipping = true;
            } else {
                // Create Occupation list
                CreateOccupationList();
                $skipping = false;
                echo "<font color='green'>Built Occupation List</font><br />\n";
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotListReaction/', $line)) {
            if ((listExists("reaction")) || (!columnExists('lists', 'reaction'))) {
                $skipping = true;
            } else {
                // Create Reaction list
                CreateReactionList();
                $skipping = false;
                echo "<font color='green'>Built Reaction List</font><br />\n";
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#IfNotListImmunizationManufacturer/', $line)) {
            if (listExists("Immunization_Manufacturer")) {
                $skipping = true;
            } else {
                // Create Immunization Manufacturer list
                CreateImmunizationManufacturerList();
                $skipping = false;
                echo "<font color='green'>Built Immunization Manufacturer List</font><br />\n";
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } // convert all *text types to use default null setting
        else if (preg_match('/^#IfTextNullFixNeeded/', $line)) {
            $items_to_convert = sqlStatement(
                "SELECT col.`table_name`, col.`column_name`, col.`data_type`, col.`column_comment` 
          FROM `information_schema`.`columns` col INNER JOIN `information_schema`.`tables` tab 
          ON tab.TABLE_CATALOG=col.TABLE_CATALOG AND tab.table_schema=col.table_schema AND tab.table_name=col.table_name
          WHERE col.`data_type` IN ('tinytext', 'text', 'mediumtext', 'longtext') 
          AND col.is_nullable='NO' AND col.table_schema=database() AND tab.table_type='BASE TABLE'"
            );
            if (sqlNumRows($items_to_convert) == 0) {
                $skipping = true;
            } else {
                $skipping = false;
                echo '<font color="black">Starting conversion of *TEXT types to use default NULL.</font><br />',"\n";
                while ($item = sqlFetchArray($items_to_convert)) {
                    if (!empty($item['column_comment'])) {
                        $res = sqlStatement("ALTER TABLE `" . add_escape_custom($item['table_name']) . "` MODIFY `" . add_escape_custom($item['column_name']) . "` " . add_escape_custom($item['data_type'])  . " COMMENT '" . add_escape_custom($item['column_comment']) . "'");
                    } else {
                        $res = sqlStatement("ALTER TABLE `" . add_escape_custom($item['table_name']) . "` MODIFY `" . add_escape_custom($item['column_name']) . "` " . add_escape_custom($item['data_type']));
                    }

                    // If above query didn't work, then error will be outputted via the sqlStatement function.
                    echo "<font color='green'>" . text($item['table_name']) . "." . text($item['column_name'])  . " sql column was successfully converted to " . text($item['data_type']) . " with default NULL setting.</font><br />\n";
                }
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } // perform special actions if table has specific engine
        else if (preg_match('/^#IfTableEngine\s+(\S+)\s+(MyISAM|InnoDB)/', $line, $matches)) {
            $skipping = !tableHasEngine($matches[1], $matches[2]);
            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } // find MyISAM tables and attempt to convert them
        else if (preg_match('/^#IfInnoDBMigrationNeeded/', $line)) {
            //tables that need to skip InnoDB migration (stay at MyISAM for now)
            $tables_skip_migration = array('form_eye_mag');

            $tables_list = getTablesList(array('engine'=>'MyISAM'));
            if (count($tables_list)==0) {
                $skipping = true;
            } else {
                $skipping = false;
                echo '<font color="black">Starting migration to InnoDB, please wait.</font><br />',"\n";
                foreach ($tables_list as $k => $t) {
                    if (in_array($t, $tables_skip_migration)) {
                        printf('<font color="green">Table %s was purposefully skipped and NOT migrated to InnoDB.</font><br />', $t);
                        continue;
                    }

                    $res = MigrateTableEngine($t, 'InnoDB');
                    if ($res === true) {
                        printf('<font color="green">Table %s migrated to InnoDB.</font><br />', $t);
                    } else {
                        printf('<font color="red">Error migrating table %s to InnoDB</font><br />', $t);
                        error_log(sprintf('Error migrating table %s to InnoDB', $t));
                    }
                }
            }

            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            }
        } else if (preg_match('/^#ConvertLayoutProperties/', $line)) {
            if ($skipping) {
                echo "<font color='green'>Skipping section $line</font><br />\n";
            } else {
                echo "Converting layout properties ...<br />\n";
                convertLayoutProperties();
            }
        } else if (preg_match('/^#EndIf/', $line)) {
            $skipping = false;
        }

        if (preg_match('/^\s*#/', $line)) {
            continue;
        }

        if ($skipping) {
            continue;
        }

        $query = $query . $line;
        if (substr($query, -1) == ';') {
            $query = rtrim($query, ';');
            echo "$query<br />\n";
            if (!sqlStatement($query)) {
                echo "<font color='red'>The above statement failed: " .
                getSqlLastError() . "<br />Upgrading will continue.<br /></font>\n";
            }

            $query = '';
        }
    }

    flush();
} // end function
