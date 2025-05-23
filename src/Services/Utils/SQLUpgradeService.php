<?php

/**
 * SQLUpgradeService used for upgrading the database for the entire system as well as individual modules to install
 * and upgrade their SQL.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Teny <teny@zhservices.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (C) 2008-2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Utils;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Events\Core\SQLUpgradeEvent;

class SQLUpgradeService
{
    private $renderOutputToScreen = true;
    private $throwExceptionOnError = false;
    private $outputBuffer = [];

    public function __construct()
    {
        $this->renderOutputToScreen = true;
        $this->throwExceptionOnError = false;
    }

    /**
     * @return bool
     */
    public function isRenderOutputToScreen(): bool
    {
        return $this->renderOutputToScreen;
    }

    /**
     * @param bool $renderOutputToScreen
     * @return SQLUpgradeService
     */
    public function setRenderOutputToScreen(bool $renderOutputToScreen): SQLUpgradeService
    {
        $this->renderOutputToScreen = $renderOutputToScreen;
        return $this;
    }

    public function getRenderOutputBuffer(): array
    {
        return $this->outputBuffer;
    }

    /**
     * @return bool
     */
    public function isThrowExceptionOnError(): bool
    {
        return $this->throwExceptionOnError;
    }

    /**
     * @param bool $throwExceptionOnError
     * @return SQLUpgradeService
     */
    public function setThrowExceptionOnError(bool $throwExceptionOnError): SQLUpgradeService
    {
        $this->throwExceptionOnError = $throwExceptionOnError;
        return $this;
    }




    private function flush()
    {
        if ($this->isRenderOutputToScreen()) {
            flush();
        }
    }

    private function echo($msg)
    {
        if ($this->isRenderOutputToScreen()) {
            echo $msg;
        } else {
            $this->outputBuffer[] = $msg;
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
     * #IfUpdateEditOptionsNeeded
     *  desc: Change Layout edit options.
     *  arguments: mode(add or remove) layout_form_id the_edit_option comma_seperated_list_of_field_ids
     *
     * #IfVitalsDatesNeeded
     *  desc: Change date from zeroes to date of vitals form creation.
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

        // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
        // or any manual processing that needs to occur.
        if (!empty($GLOBALS['kernel'])) {
            $sqlUpgradeEvent = new SQLUpgradeEvent($filename, $path, $this);
            $GLOBALS['kernel']->getEventDispatcher()->dispatch($sqlUpgradeEvent, SQLUpgradeEvent::EVENT_UPGRADE_PRE);
        }

        $skip_msg = xlt("Skipping section");

        $this->flush();
        $this->echo("<p class='text-success'>" . xlt("Processing") . " " . $filename . "...</p>\n");

        $default_path = $webserver_root . "/sql";
        $fullname = ((!empty($path) && is_dir($path)) ? $path : $default_path) . "/$filename";
        $file_size = filesize($fullname);

        $fd = fopen($fullname, 'r');
        if ($fd == false) {
            $this->echo(xlt("ERROR. Could not open") . " " . $fullname . ".\n");
            $this->flush();
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
                $this->echo("<script>processProgress = $progress_stat;progressStatus();</script>");
            }

            if (preg_match('/^#IfNotTable\s+(\S+)/', $line, $matches)) {
                $skipping = $this->tableExists($matches[1]);
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfTable\s+(\S+)/', $line, $matches)) {
                $skipping = !$this->tableExists($matches[1]);
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = !$this->columnExists($matches[1], $matches[2]);
                } else {
                    // If no such table then the column is deemed "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfMissingColumn\s+(\S+)\s+(\S+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->columnExists($matches[1], $matches[2]);
                } else {
                    // If no such table then the column is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotColumnTypeDefault\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                // This allows capturing a default setting that is not blank
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->columnHasTypeDefault($matches[1], $matches[2], $matches[3], $matches[4]);
                } else {
                    // If no such table then the column type is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotColumnTypeDefault\s+(\S+)\s+(\S+)\s+(\S+)/', $line, $matches)) {
                // This allows capturing a default setting that is blank
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->columnHasTypeDefault($matches[1], $matches[2], $matches[3], $matches[4]);
                } else {
                    // If no such table then the column type is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotColumnType\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->columnHasType($matches[1], $matches[2], $matches[3]);
                } else {
                    // If no such table then the column type is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    // If no such index then skip.
                    $skipping = !$this->tableHasIndex($matches[1], $matches[2]);
                } else {
                    // If no such table then skip.
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotIndex\s+(\S+)\s+(\S+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->tableHasIndex($matches[1], $matches[2]);
                } else {
                    // If no such table then the index is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->tableHasRow($matches[1], $matches[2], $matches[3]);
                } else {
                    // If no such table then the row is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
                } else {
                    // If no such table then the row is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]);
                } else {
                    // If no such table then the row is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotRow4D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = $this->tableHasRow4D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7], $matches[8], $matches[9]);
                } else {
                    // If no such table then the row is deemed not "missing".
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotRow2Dx2\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    // If either check exist, then will skip
                    $firstCheck = $this->tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
                    $secondCheck = $this->tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[6], $matches[7]);
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
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfRow2D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = !($this->tableHasRow2D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]));
                } else {
                    // If no such table then should skip.
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfRow3D\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = !($this->tableHasRow3D($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7]));
                } else {
                    // If no such table then should skip.
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfRowIsNull\s+(\S+)\s+(\S+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = !($this->tableHasRowNull($matches[1], $matches[2]));
                } else {
                    // If no such table then should skip.
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                if ($this->tableExists($matches[1])) {
                    $skipping = !($this->tableHasRow($matches[1], $matches[2], $matches[3]));
                } else {
                    // If no such table then should skip.
                    $skipping = true;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotMigrateClickOptions/', $line)) {
                if ($this->tableExists("issue_types")) {
                    $skipping = true;
                } else {
                    // Create issue_types table and import the Issue Types and clickoptions settings from codebase into the database
                    $this->clickOptionsMigrate();
                    $skipping = false;
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotListOccupation/', $line)) {
                if (($this->listExists("Occupation")) || (!$this->columnExists('patient_data', 'occupation'))) {
                    $skipping = true;
                } else {
                    // Create Occupation list
                    $this->CreateOccupationList();
                    $skipping = false;
                    $this->echo("<p class='text-success'>Built Occupation List</p>\n");
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotListReaction/', $line)) {
                if (($this->listExists("reaction")) || (!$this->columnExists('lists', 'reaction'))) {
                    $skipping = true;
                } else {
                    // Create Reaction list
                    $this->CreateReactionList();
                    $skipping = false;
                    $this->echo("<p class='text-success'>Built Reaction List</p>\n");
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotListImmunizationManufacturer/', $line)) {
                if ($this->listExists("Immunization_Manufacturer")) {
                    $skipping = true;
                } else {
                    // Create Immunization Manufacturer list
                    $this->CreateImmunizationManufacturerList();
                    $skipping = false;
                    $this->echo("<p class='text-success'>Built Immunization Manufacturer List</p>\n");
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfNotWenoRx/', $line)) {
                if ($this->tableHasRow('erx_weno_drugs', "drug_id", '1008') == true) {
                    $skipping = true;
                } else {
                    //import drug data
                    $this->ImportDrugInformation();
                    $skipping = false;
                    $this->echo("<p class='text-success'>Imported eRx Weno Drug Data</p>\n");
                }
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
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
                    array($this->databaseName())
                );
                $skipping = true;
                while ($item = sqlFetchArray($items_to_convert)) {
                    if (empty($item['table_name'])) {
                        continue;
                    }
                    if ($skipping) {
                        $skipping = false;
                        $this->echo('<p>Starting conversion of *TEXT types to use default NULL.</p>', "\n");
                        $this->flush_echo();
                    }
                    if (!empty($item['column_comment'])) {
                        $res = sqlStatement("ALTER TABLE `" . add_escape_custom($item['table_name'])
                            . "` MODIFY `" . add_escape_custom($item['column_name']) . "` "
                            . add_escape_custom($item['data_type'])
                            . " COMMENT '" . add_escape_custom($item['column_comment']) . "'");
                    } else {
                        $res = sqlStatement("ALTER TABLE `" . add_escape_custom($item['table_name'])
                            . "` MODIFY `" . add_escape_custom($item['column_name']) . "` "
                            . add_escape_custom($item['data_type']));
                    }
                    // If above query didn't work, then error will be outputted via the sqlStatement function.
                    $this->echo("<p class='text-success'>" . text($item['table_name']) . "."
                        . text($item['column_name']) . " sql column was successfully converted to "
                        . text($item['data_type']) . " with default NULL setting.</p>\n");
                    $this->flush_echo();
                }
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfTableEngine\s+(\S+)\s+(MyISAM|InnoDB)/', $line, $matches)) {
                // perform special actions if table has specific engine
                $skipping = !$this->tableHasEngine($matches[1], $matches[2]);
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfInnoDBMigrationNeeded/', $line)) {
                // find MyISAM tables and attempt to convert them
                //tables that need to skip InnoDB migration (stay at MyISAM for now)
                $tables_skip_migration = array('form_eye_mag');

                $tables_list = $this->getTablesList(array('engine' => 'MyISAM'));

                $skipping = true;
                foreach ($tables_list as $k => $t) {
                    if (empty($t)) {
                        continue;
                    }
                    if ($skipping) {
                        $skipping = false;
                        $this->echo('<p>Starting migration to InnoDB, please wait.</p>', "\n");
                        $this->flush_echo();
                    }

                    if (in_array($t, $tables_skip_migration)) {
                        printf('<p class="text-success">Table %s was purposefully skipped and NOT migrated to InnoDB.</p>', $t);
                        continue;
                    }

                    $res = $this->MigrateTableEngine($t, 'InnoDB');
                    if ($res === true) {
                        printf('<p class="text-success">Table %s migrated to InnoDB.</p>', $t);
                    } else {
                        printf('<p class="text-danger">Error migrating table %s to InnoDB</p>', $t);
                        error_log(sprintf('Error migrating table %s to InnoDB', errorLogEscape($t)));
                    }
                }

                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#ConvertLayoutProperties/', $line)) {
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                } else {
                    $this->echo("Converting layout properties ...<br />\n");
                    $this->flush_echo();
                    $this->convertLayoutProperties();
                }
            } elseif (preg_match('/^#IfDocumentNamingNeeded/', $line)) {
                $emptyNames = sqlStatementNoLog("SELECT `id`, `url`, `name`, `couch_docid` FROM `documents` WHERE `name` = '' OR `name` IS NULL");
                if (sqlNumRows($emptyNames) > 0) {
                    $this->echo("<p>Converting document names.</p>\n");
                    $this->flush_echo();
                    while ($row = sqlFetchArray($emptyNames)) {
                        if (!empty($row['couch_docid'])) {
                            sqlStatementNoLog("UPDATE `documents` SET `name` = ? WHERE `id` = ?", [$row['url'], $row['id']]);
                        } else {
                            sqlStatementNoLog("UPDATE `documents` SET `name` = ? WHERE `id` = ?", [basename_international($row['url']), $row['id']]);
                        }
                    }
                    $this->echo("<p class='text-success'>Completed conversion of document names</p>\n");
                    $this->flush_echo();
                    $skipping = false;
                } else {
                    $skipping = true;
                }
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfUpdateEditOptionsNeeded\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                $skipping = $this->updateLayoutEditOptions($matches[1], $matches[2], $matches[3], $matches[4]);
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
                }
            } elseif (preg_match('/^#IfVitalsDatesNeeded/', $line)) {
                $emptyDates = sqlStatementNoLog("SELECT fv.id as vitals_id, f.date as new_date FROM form_vitals fv LEFT JOIN forms f on fv.id = f.form_id WHERE fv.date = '0000-00-00 00:00:00' AND f.form_name = 'Vitals'");
                if (sqlNumRows($emptyDates) > 0) {
                    $this->echo("<p>Converting empty vital dates.</p>\n");
                    $this->flush_echo();
                    while ($row = sqlFetchArray($emptyDates)) {
                        sqlStatementNoLog("UPDATE `form_vitals` SET `date` = ? WHERE `id` = ?", [$row['new_date'], $row['vitals_id']]);
                    }
                    $this->echo("<p class='text-success'>Completed conversion of empty vital dates</p>\n");
                    $this->flush_echo();
                    $skipping = false;
                } else {
                    $skipping = true;
                }
                if ($skipping) {
                    $this->echo("<p class='text-success'>$skip_msg $line</p>\n");
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

                $this->flush_echo("$query<br />\n");
                try {
                    // if a statement returns 0 we need to still throw an exception as that is how the old query was
                    // structured and we don't want to be backwards compatible.
                    if (!QueryUtils::sqlStatementThrowException($query, [])) {
                        if ($this->isThrowExceptionOnError()) {
                            throw new SqlQueryException($query, getSqlLastError());
                        }
                    }
                } catch (SqlQueryException $exception) {
                    $this->echo("<p class='text-danger'>The above statement failed: " .
                        getSqlLastError() . "<br />Upgrading will continue.<br /></p>\n");
                    $this->flush_echo();
                    if ($this->isThrowExceptionOnError()) {
                        throw $exception;
                    }
                }
                $query = '';
            }
        }

        $this->flush();

        // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
        // or any manual processing that needs to occur.
        if (!empty($GLOBALS['kernel'])) {
            $sqlUpgradeEvent = new SQLUpgradeEvent($filename, $path, $this);
            $GLOBALS['kernel']->getEventDispatcher()->dispatch($sqlUpgradeEvent, SQLUpgradeEvent::EVENT_UPGRADE_POST);
        }
    } // end function

    public function flush_echo($string = '')
    {
        if (!$this->isRenderOutputToScreen()) {
            return;
        }

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
     * Return the name of the OpenEMR database.
     *
     * @return string
     */
    private function databaseName()
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
    private function tableExists($tblname)
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
    private function columnExists($tblname, $colname)
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
    private function columnHasType($tblname, $colname, $coltype)
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
    private function columnHasTypeDefault($tblname, $colname, $coltype, $coldefault)
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
     * Check if a Sql row exists (with one null value)
     *
     * @param string $tblname Sql Table Name
     * @param string $colname Sql Column Name
     * @return boolean           returns true if the sql row does exist
     */
    private function tableHasRowNull($tblname, $colname)
    {
        $row = sqlQuery("SELECT COUNT(*) AS count FROM $tblname WHERE " .
            "$colname IS NULL");
        return $row['count'] ? true : false;
    }


    /**
     * Check if a Sql row exists. (with one value)
     *
     * @param string $tblname Sql Table Name
     * @param string $colname Sql Column Name
     * @param string $value   Sql value
     * @return boolean           returns true if the sql row does exist
     */
    private function tableHasRow($tblname, $colname, $value)
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
    private function tableHasRow2D($tblname, $colname, $value, $colname2, $value2)
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
    private function tableHasRow3D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3)
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
    private function tableHasRow4D($tblname, $colname, $value, $colname2, $value2, $colname3, $value3, $colname4, $value4)
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
    private function tableHasIndex($tblname, $colname)
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
    private function tableHasEngine($tblname, $engine)
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
    private function listExists($option_id)
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
    private function clickOptionsMigrate()
    {
        // If the clickoptions.txt file exist, then import it.
        if (file_exists(dirname(__FILE__) . "/../sites/" . $_SESSION['site_id'] . "/clickoptions.txt")) {
            $file_handle = fopen(dirname(__FILE__) . "/../sites/" . $_SESSION['site_id'] . "/clickoptions.txt", "rb");
            $seq = 10;
            $prev = '';
            $this->echo("Importing clickoption setting<br />");
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
    private function CreateOccupationList()
    {
        $res = sqlStatement("SELECT DISTINCT occupation FROM patient_data WHERE occupation <> ''");
        $records = [];
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
    private function CreateReactionList()
    {
        $res = sqlStatement("SELECT DISTINCT reaction FROM lists WHERE reaction <> ''");
        $records = [];
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
    private function CreateImmunizationManufacturerList()
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
    private function ImportDrugInformation()
    {
        if ($GLOBALS['weno_rx_enable'] ?? false) {
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
    private function getTablesList($arg = array())
    {
        $binds = array($this->databaseName());
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
     * @param string $engine  has to be set to InnoDB 8-7-24
     * ADODB will fail if there was an error during conversion
     */
    private function MigrateTableEngine($table, $engine)
    {
        if ($engine != "InnoDB") {
            return false;
        }

        $r = sqlStatement('ALTER TABLE `' . $table . '` ENGINE=InnoDB');

        return true;
    }


    private function convertLayoutProperties()
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
                // $this->echo($query); foreach ($sqlvars as $tmp) $this->echo(" '$tmp'"); $this->echo("<br />\n"); // debugging
                sqlStatement($query, $sqlvars);
            } // end group
        } // end form
    }

    private function updateLayoutEditOptions($mode, $form_id, $add_option, $values): bool
    {
        $flag = true;
        $subject = explode(',', str_replace(' ', '', $values));
        if (empty($subject)) {
            $this->echo("<p class='text-danger'>Missing field ids for update " . text($mode) . ".</p>");
            return true;
        }
        $sql = "SELECT `field_id`, `edit_options`, `seq` FROM `layout_options` WHERE `form_id` = ? ";
        $result = sqlStatementNoLog($sql, array($form_id));
        if (empty(sqlNumRows($result))) {
            $this->echo("<p class='text-danger'>No results returned for " . text($form_id) . ".</p>");
            return true;
        }

        sqlStatementNoLog('SET autocommit=0');
        sqlStatementNoLog('START TRANSACTION');
        try {
            while ($row = sqlFetchArray($result)) {
                if (in_array($row['field_id'], $subject)) {
                    $options = json_decode($row['edit_options'], true) ?? [];
                    if (!in_array($add_option, $options) && stripos($mode, 'add') !== false) {
                        $options[] = $add_option;
                    } elseif (in_array($add_option, $options) && stripos($mode, 'remove') !== false) {
                        $key = array_search($add_option, $options);
                        unset($options[$key]);
                    } else {
                        continue;
                    }
                    if ($flag) {
                        // just show this prior first change (so will be not shown if this is "skipped")
                        $this->echo("<p class='text-success'>Start Layouts Edit Options " . text($mode) . " " . text($add_option) . " update.</p>");
                    }
                    $new_options = json_encode($options);
                    $update_sql = "UPDATE `layout_options` SET `edit_options` = ? WHERE `form_id` = 'DEM' AND `field_id` = ? AND `seq` = ? ";
                    $this->echo('Setting new edit options ' . text($row['field_id']) . ' to ' . text($new_options) . "<br />");
                    sqlStatementNoLog($update_sql, array($new_options, $row['field_id'], $row['seq']));
                    $flag = false;
                }
            }
        } catch (SqlQueryException $e) {
            $this->echo("<p class='text-danger'>The above statement failed: " .
                text(getSqlLastError()) . "<br />Upgrading will continue.<br /></p>\n");
            $this->flush_echo();
            if ($this->isThrowExceptionOnError()) {
                throw $e;
            }
        }
        sqlStatementNoLog('COMMIT');
        sqlStatementNoLog('SET autocommit=1');
        if (!$flag) {
            // so will be not shown if this is "skipped"
            $this->echo("<p class='text-success'>Layout Edit Options " . text($mode) . " " . text($add_option) . " done.</p><br />");
            $this->flush_echo();
        }

        return $flag;
    }
}
