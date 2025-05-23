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
 * #EndIf
 *   all blocks are terminated with a #EndIf statement.
 *
 * @param string $filename Sql upgrade/patch filename
 */
function upgradeFromSqlFile($filename, $path = '')
{
    $sqlUpgradeService = new \OpenEMR\Services\Utils\SQLUpgradeService();
    $sqlUpgradeService->upgradeFromSqlFile($filename, $path);
} // end function
