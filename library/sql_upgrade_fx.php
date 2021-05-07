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
 * @author    Teny <teny@zhservices.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @link      https://www.open-emr.org
 */

use OpenEMR\Common\Uuid\UuidRegistry;

/**
 * Return the name of the OpenEMR database.
 *
 * @return string
 */
function databaseName()
{
    $row = sqlQuery("SELECT database() AS dbname");
    return $row['dbname'];
}

/**
 * Check if a Sql table exists.
 *
 * @param string $tblname Sql Table Name
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
 * @param string $tblname Sql Table Name
 * @param string $colname Sql Column Name
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
 * @param string $tblname Sql Table Name
 * @param string $colname Sql Column Name
 * @param string $coltype Sql Column Type
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
 * Check if a Sql column has a certain type and a certain default value.
 *
 * @param string $tblname    Sql Table Name
 * @param string $colname    Sql Column Name
 * @param string $coltype    Sql Column Type
 * @param string $coldefault Sql Column Default
 * @return boolean              returns true if the sql column is of the specified type and default
 */
function columnHasTypeDefault($tblname, $colname, $coltype, $coldefault)
{
    $row = sqlQuery("SHOW COLUMNS FROM $tblname WHERE `Field` = ?", [$colname]);
    if (empty($row)) {
        return true;
    }

    // Check if the type matches
    if (strcasecmp($row['Type'], $coltype) != 0) {
        return false;
    }

    // Now for the more difficult check for if the default matches
    if ($coldefault == "NULL") {
        // Special case when checking if default is NULL
        $row = sqlQuery("SHOW COLUMNS FROM $tblname WHERE `Field` = ? AND `Default` IS NULL", [$colname]);
        return (!empty($row));
    } elseif ($coldefault == "") {
        // Special case when checking if default is ""(blank)
        $row = sqlQuery("SHOW COLUMNS FROM $tblname WHERE `Field` = ? AND `Default` IS NOT NULL AND `Default` = ''", [$colname]);
        return (!empty($row));
    } else {
        // Standard case when checking if default is neither NULL or ""(blank)
        return (strcasecmp($row['Default'], $coldefault) == 0);
    }
}

/**
 * Check if a Sql row exists. (with one value)
 *
 * @param string $tblname Sql Table Name
 * @param string $colname Sql Column Name
 * @param string $value   Sql value
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
 * @param string $tblname  Sql Table Name
 * @param string $colname  Sql Column Name 1
 * @param string $value    Sql value 1
 * @param string $colname2 Sql Column Name 2
 * @param string $value2   Sql value 2
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
 * @param string $tblname  Sql Table Name
 * @param string $colname  Sql Column Name 1
 * @param string $value    Sql value 1
 * @param string $colname2 Sql Column Name 2
 * @param string $value2   Sql value 2
 * @param string $colname3 Sql Column Name 3
 * @param string $value3   Sql value 3
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
 * @param string $tblname  Sql Table Name
 * @param string $colname  Sql Column Name 1
 * @param string $value    Sql value 1
 * @param string $colname2 Sql Column Name 2
 * @param string $value2   Sql value 2
 * @param string $colname3 Sql Column Name 3
 * @param string $value3   Sql value 3
 * @param string $colname4 Sql Column Name 4
 * @param string $value4   Sql value 4
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
 * @param string $tblname Sql Table Name
 * @param string $colname Sql Index/Key
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
 * @param string $engine  engine name ( myisam, memory, innodb )...
 * @return boolean true if the table has been created using specified engine
 */
function tableHasEngine($tblname, $engine)
{
    $row = sqlQuery('SELECT 1 FROM information_schema.tables WHERE table_name=? AND engine=? AND table_type="BASE TABLE"', array($tblname, $engine));
    return (empty($row)) ? false : true;
}


/**
 * Check if a list exists.
 *
 * @param string $option_id Sql List Option ID
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
    if (file_exists(dirname(__FILE__) . "/../sites/" . $_SESSION['site_id'] . "/clickoptions.txt")) {
        $file_handle = fopen(dirname(__FILE__) . "/../sites/" . $_SESSION['site_id'] . "/clickoptions.txt", "rb");
        $seq = 10;
        $prev = '';
        echo "Importing clickoption setting<br />";
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
                SqlStatement($sql1, array('lists', $parts[0] . '_issue_list', ucwords(str_replace("_", " ", $parts[0])) . ' Issue List'));
                $seq = 10;
            }

            $sql2 = "INSERT INTO list_options (`list_id`,`option_id`,`title`,`seq`) VALUES (?,?,?,?)";
            SqlStatement($sql2, array($parts[0] . '_issue_list', $parts[1], $parts[1], $seq));
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
    if (count($records) > 0) {
        $seq = 0;
        foreach ($records as $key => $value) {
            sqlStatement("INSERT INTO list_options ( list_id, option_id, title, seq) VALUES ('Occupation', ?, ?, ?)", array($value, $value, ($seq + 10)));
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
    if (count($records) > 0) {
        $seq = 0;
        foreach ($records as $key => $value) {
            sqlStatement("INSERT INTO list_options ( list_id, option_id, title, seq) VALUES ('reaction', ?, ?, ?)", array($value, $value, ($seq + 10)));
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
    if (count($records) > 0) {
        $seq = 0;
        foreach ($records as $key => $value) {
            sqlStatement("INSERT INTO list_options ( list_id, option_id, title, seq) VALUES ('Immunization_Manufacturer', ?, ?, ?)", array($value, $value, ($seq + 10)));
            $seq = $seq + 10;
        }
    }
}

/*
 *  This function is to populate the weno drug table if the feature is enabled before upgrade.
 */
function ImportDrugInformation()
{
    if ($GLOBALS['weno_rx_enable']) {
        $drugs = file_get_contents('contrib/weno/erx_weno_drugs.sql');
        $drugsArray = preg_split('/;\R/', $drugs);

        // Settings to drastically speed up import with InnoDB
        sqlStatementNoLog("SET autocommit=0");
        sqlStatementNoLog("START TRANSACTION");

        foreach ($drugsArray as $drug) {
            if (empty($drug)) {
                continue;
            }
            sqlStatementNoLog($drug);
        }

        // Settings to drastically speed up import with InnoDB
        sqlStatementNoLog("COMMIT");
        sqlStatementNoLog("SET autocommit=1");
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
    $binds = array(databaseName());
    $sql = 'SELECT TABLE_NAME AS table_name FROM information_schema.tables WHERE table_schema = ? AND table_type = "BASE TABLE"';

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
        $records[$row['table_name']] = $row['table_name'];
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
    $r = sqlStatement('ALTER TABLE `' . $table . '` ENGINE=?', $engine);
    return true;
}

function convertLayoutProperties()
{
    $res = sqlStatement("SELECT DISTINCT form_id FROM layout_options ORDER BY form_id");
    while ($row = sqlFetchArray($res)) {
        $form_id = $row['form_id'];
        $props = array(
            'title' => 'Unknown',
            'mapping' => 'Core',
            'notes' => '',
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
        } elseif (substr($form_id, 0, 3) == 'LBT') {
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
        } elseif ($form_id == 'DEM') {
            $props['title'] = 'Demographics';
        } elseif ($form_id == 'HIS') {
            $props['title'] = 'History';
        } elseif ($form_id == 'FACUSR') {
            $props['title'] = 'Facility Specific User Information';
        } elseif ($form_id == 'CON') {
            $props['title'] = 'Contraception Issues';
        } elseif ($form_id == 'GCA') {
            $props['title'] = 'Abortion Issues';
        } elseif ($form_id == 'SRH') {
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

function flush_echo($string = '')
{
    if ($string) {
        echo $string;
    }
    // now flush to force browser to pay attention.
    if (empty($GLOBALS['force_simple_sql_upgrade'])) {
        // this is skipped when running sql upgrade from command line
        echo str_pad('', 4096) . "\n";
    }
    ob_flush();
    flush();
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
 * #IfNotColumnTypeDefault
 *   arguments: table_name colname value value2
 *   behavior:  If the table table_name does not have a column colname with a data type equal to value and a default equal to value2, then the block will be executed
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
 * #IfRow
 *   arguments: table_name colname value
 *   behavior:  If the table table_name does have a row where colname = value, the block will be executed.
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
 * #IfUuidNeedUpdate
 *   argument: table_name
 *   behavior: this will add and populate a uuid column into table
 *
 *  #IfUuidNeedUpdateId
 *   argument: table_name primary_id
 *   behavior: this will add and populate a uuid column into table
 *
 * #IfUuidNeedUpdateVertical
 *   argument: table_name table_columns
 *   behavior: this will add and populate a uuid column into vertical table for combinations of table_columns given
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
 * #IfNotWenoRx
 * Custom function for importing new drug data
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
* #IfDocumentNamingNeeded
*  desc: populate name field with document names.
*  arguments: none
*
 * #EndIf
 *   all blocks are terminated with a #EndIf statement.
 *
 * @param string $filename Sql upgrade/patch filename
 */
function upgradeFromSqlFile($filename, $path = '')
{
    global $webserver_root;
    $skip_msg = xlt("Skipping section");

    flush();
    echo "<p class='text-success'>" . xlt("Processing") . " " . $filename . "...</p>\n";

    $fullname = ((!empty($path) && is_dir($path)) ? $path : $webserver_root) . "/sql/$filename";
    $file_size = filesize($fullname);

    $fd = fopen($fullname, 'r');
    if ($fd == false) {
        echo xlt("ERROR. Could not open") . " " . $fullname . ".\n";
        flush();
        return;
    }

    $query = "";
    $line = "";
    $skipping = false;
    $special = false;
    $trim = true;
    $progress = 0;

    while (!feof($fd)) {
        $line = fgets($fd, 2048);
        $line = rtrim($line);

        $progress += strlen($line);

        if (preg_match('/^\s*--/', $line)) {
            continue;
        }

        if ($line == "") {
            continue;
        }

        if (empty($GLOBALS['force_simple_sql_upgrade'])) {
            // this is skipped when running sql upgrade from command line
            $progress_stat = 100 - round((($file_size - $progress) / $file_size) * 100, 0);
            $progress_stat = $progress_stat > 100 ? 100 : $progress_stat;
            echo "<script>processProgress = $progress_stat;progressStatus();</script>";
        }

        if (preg_match('/^#IfNotTable\s+(\S+)/', $line, $matches)) {
            $skipping = tableExists($matches[1]);
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfTable\s+(\S+)/', $line, $matches)) {
            $skipping = !tableExists($matches[1]);
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !columnExists($matches[1], $matches[2]);
            } else {
                // If no such table then the column is deemed "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfMissingColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = columnExists($matches[1], $matches[2]);
            } else {
                // If no such table then the column is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotColumnTypeDefault\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            // This allows capturing a default setting that is not blank
            if (tableExists($matches[1])) {
                $skipping = columnHasTypeDefault($matches[1], $matches[2], $matches[3], $matches[4]);
            } else {
                // If no such table then the column type is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotColumnTypeDefault\s+(\S+)\s+(\S+)\s+(\S+)/', $line, $matches)) {
            // This allows capturing a default setting that is blank
            if (tableExists($matches[1])) {
                $skipping = columnHasTypeDefault($matches[1], $matches[2], $matches[3], $matches[4]);
            } else {
                // If no such table then the column type is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotColumnType\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = columnHasType($matches[1], $matches[2], $matches[3]);
            } else {
                // If no such table then the column type is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                // If no such index then skip.
                $skipping = !tableHasIndex($matches[1], $matches[2]);
            } else {
                // If no such table then skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasIndex($matches[1], $matches[2]);
            } else {
                // If no such table then the index is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow($matches[1], $matches[2], $matches[3]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotRow4D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = tableHasRow4D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7], $matches[8], $matches[9]);
            } else {
                // If no such table then the row is deemed not "missing".
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotRow2Dx2\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
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
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !(tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]));
            } else {
                // If no such table then should skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !(tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]));
            } else {
                // If no such table then should skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
            if (tableExists($matches[1])) {
                $skipping = !(tableHasRow($matches[1], $matches[2], $matches[3]));
            } else {
                // If no such table then should skip.
                $skipping = true;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotMigrateClickOptions/', $line)) {
            if (tableExists("issue_types")) {
                $skipping = true;
            } else {
                // Create issue_types table and import the Issue Types and clickoptions settings from codebase into the database
                clickOptionsMigrate();
                $skipping = false;
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotListOccupation/', $line)) {
            if ((listExists("Occupation")) || (!columnExists('patient_data', 'occupation'))) {
                $skipping = true;
            } else {
                // Create Occupation list
                CreateOccupationList();
                $skipping = false;
                echo "<p class='text-success'>Built Occupation List</p>\n";
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotListReaction/', $line)) {
            if ((listExists("reaction")) || (!columnExists('lists', 'reaction'))) {
                $skipping = true;
            } else {
                // Create Reaction list
                CreateReactionList();
                $skipping = false;
                echo "<p class='text-success'>Built Reaction List</p>\n";
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotListImmunizationManufacturer/', $line)) {
            if (listExists("Immunization_Manufacturer")) {
                $skipping = true;
            } else {
                // Create Immunization Manufacturer list
                CreateImmunizationManufacturerList();
                $skipping = false;
                echo "<p class='text-success'>Built Immunization Manufacturer List</p>\n";
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfNotWenoRx/', $line)) {
            if (tableHasRow('erx_weno_drugs', "drug_id", '1008') == true) {
                $skipping = true;
            } else {
                //import drug data
                ImportDrugInformation();
                $skipping = false;
                echo "<p class='text-success'>Imported eRx Weno Drug Data</p>\n";
            }
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
            // convert all *text types to use default null setting
        } elseif (preg_match('/^#IfTextNullFixNeeded/', $line)) {
            $items_to_convert = sqlStatement(
                "SELECT col.`table_name` AS table_name, col.`column_name` AS column_name,
      col.`data_type` AS data_type, col.`column_comment` AS column_comment
      FROM `information_schema`.`columns` col INNER JOIN `information_schema`.`tables` tab
      ON tab.TABLE_CATALOG=col.TABLE_CATALOG AND tab.table_schema=col.table_schema AND tab.table_name=col.table_name
      WHERE col.`data_type` IN ('tinytext', 'text', 'mediumtext', 'longtext')
      AND col.is_nullable = 'NO' AND col.table_schema = ? AND tab.table_type = 'BASE TABLE'",
                array(databaseName())
            );
            $skipping = true;
            while ($item = sqlFetchArray($items_to_convert)) {
                if (empty($item['table_name'])) {
                    continue;
                }
                if ($skipping) {
                    $skipping = false;
                    echo '<p>Starting conversion of *TEXT types to use default NULL.</p>', "\n";
                    flush_echo();
                }
                if (!empty($item['column_comment'])) {
                    $res = sqlStatement("ALTER TABLE `" . add_escape_custom($item['table_name']) . "` MODIFY `" . add_escape_custom($item['column_name']) . "` " . add_escape_custom($item['data_type']) . " COMMENT '" . add_escape_custom($item['column_comment']) . "'");
                } else {
                    $res = sqlStatement("ALTER TABLE `" . add_escape_custom($item['table_name']) . "` MODIFY `" . add_escape_custom($item['column_name']) . "` " . add_escape_custom($item['data_type']));
                }
                // If above query didn't work, then error will be outputted via the sqlStatement function.
                echo "<p class='text-success'>" . text($item['table_name']) . "." . text($item['column_name']) . " sql column was successfully converted to " . text($item['data_type']) . " with default NULL setting.</p>\n";
                flush_echo();
            }
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfTableEngine\s+(\S+)\s+(MyISAM|InnoDB)/', $line, $matches)) {
            // perform special actions if table has specific engine
            $skipping = !tableHasEngine($matches[1], $matches[2]);
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfInnoDBMigrationNeeded/', $line)) {
            // find MyISAM tables and attempt to convert them
            //tables that need to skip InnoDB migration (stay at MyISAM for now)
            $tables_skip_migration = array('form_eye_mag');

            $tables_list = getTablesList(array('engine' => 'MyISAM'));

            $skipping = true;
            foreach ($tables_list as $k => $t) {
                if (empty($t)) {
                    continue;
                }
                if ($skipping) {
                    $skipping = false;
                    echo '<p>Starting migration to InnoDB, please wait.</p>', "\n";
                    flush_echo();
                }

                if (in_array($t, $tables_skip_migration)) {
                    printf('<p class="text-success">Table %s was purposefully skipped and NOT migrated to InnoDB.</p>', $t);
                    continue;
                }

                $res = MigrateTableEngine($t, 'InnoDB');
                if ($res === true) {
                    printf('<p class="text-success">Table %s migrated to InnoDB.</p>', $t);
                } else {
                    printf('<p class="text-danger">Error migrating table %s to InnoDB</p>', $t);
                    error_log(sprintf('Error migrating table %s to InnoDB', errorLogEscape($t)));
                }
            }

            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfUuidNeedUpdate\s+(\S+)/', $line, $matches)) {
            $uuidRegistry = new UuidRegistry(['table_name' => $matches[1]]);
            if (tableExists($matches[1]) && $uuidRegistry->tableNeedsUuidCreation()) {
                $skipping = false;
                echo "<p>Going to add UUIDs to " . $matches[1] . " table</p>\n";
                flush_echo();
                $number = $uuidRegistry->createMissingUuids();
                echo "<p class='text-success'>Successfully completed added " . $number . " UUIDs to " . $matches[1] . " table</p>\n";
                flush_echo();
            } else {
                $skipping = true;
            }
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfUuidNeedUpdateId\s+(\S+)\s+(\S+)/', $line, $matches)) {
            $uuidRegistry = new UuidRegistry([
                'table_name' => $matches[1],
                'table_id' => $matches[2]
            ]);
            if (
                tableExists($matches[1]) &&
                columnExists($matches[1], $matches[2]) &&
                $uuidRegistry->tableNeedsUuidCreation()
            ) {
                $skipping = false;
                echo "<p>Going to add UUIDs to " . $matches[1] . " table</p>\n";
                flush_echo();
                $number = $uuidRegistry->createMissingUuids();
                echo "<p class='text-success'>Successfully completed added " . $number . " UUIDs to " . $matches[1] . " table</p>\n";
                flush_echo();
            } else {
                $skipping = true;
            }
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#IfUuidNeedUpdateVertical\s+(\S+)\s+(\S+)/', $line, $matches)) {
            $vertical_table_columns = explode(":", $matches[2]);
            $uuidRegistry = new UuidRegistry(['table_name' => $matches[1], 'table_vertical' => $vertical_table_columns]);
            if (tableExists($matches[1]) && $uuidRegistry->tableNeedsUuidCreation()) {
                $skipping = false;
                echo "<p>Going to add UUIDs to " . $matches[1] . " vertical table</p>\n";
                flush_echo();
                $number = $uuidRegistry->createMissingUuids();
                echo "<p class='text-success'>Successfully completed added " . $number . " UUIDs to " . $matches[1] . " vertical table</p>\n";
                flush_echo();
            } else {
                $skipping = true;
            }
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#ConvertLayoutProperties/', $line)) {
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            } else {
                echo "Converting layout properties ...<br />\n";
                flush_echo();
                convertLayoutProperties();
            }
        } elseif (preg_match('/^#IfDocumentNamingNeeded/', $line)) {
            $emptyNames = sqlStatementNoLog("SELECT `id`, `url`, `name`, `couch_docid` FROM `documents` WHERE `name` = '' OR `name` IS NULL");
            if (sqlNumRows($emptyNames) > 0) {
                echo "<p>Converting document names.</p>\n";
                flush_echo();
                while ($row = sqlFetchArray($emptyNames)) {
                    if (!empty($row['couch_docid'])) {
                        sqlStatementNoLog("UPDATE `documents` SET `name` = ? WHERE `id` = ?", [$row['url'], $row['id']]);
                    } else {
                        sqlStatementNoLog("UPDATE `documents` SET `name` = ? WHERE `id` = ?", [basename_international($row['url']), $row['id']]);
                    }
                }
                echo "<p class='text-success'>Completed conversion of document names</p>\n";
                flush_echo();
                $skipping = false;
            } else {
                $skipping = true;
            }
            if ($skipping) {
                echo "<p class='text-success'>$skip_msg $line</p>\n";
            }
        } elseif (preg_match('/^#EndIf/', $line)) {
            $skipping = false;
        }
        if (preg_match('/^#SpecialSql/', $line)) {
            $special = true;
            $line = " ";
        } elseif (preg_match('/^#EndSpecialSql/', $line)) {
            $special = false;
            $trim = false;
            $line = " ";
        } elseif (preg_match('/^\s*#/', $line)) {
            continue;
        }

        if ($skipping) {
            continue;
        }

        if ($special) {
            $query = $query . " " . $line;
            continue;
        }

        $query = $query . $line;

        if (substr(trim($query), -1) == ';') {
            if ($trim) {
                $query = rtrim($query, ';');
            } else {
                $trim = true;
            }

            flush_echo("$query<br />\n");

            if (!sqlStatement($query)) {
                echo "<p class='text-danger'>The above statement failed: " .
                    getSqlLastError() . "<br />Upgrading will continue.<br /></p>\n";
                flush_echo();
            }

            $query = '';
        }
    }

    flush();
} // end function
