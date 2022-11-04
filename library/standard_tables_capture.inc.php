<?php

/**
 * This library contains functions that implement the database load processing
 * of external database files into openEMR
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rohit Kumar <pandit.rohit@netsity.com>
 * @author    (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2011 Phyaura, LLC <info@phyaura.com>
 * @copyright Copyright (c) 2019-2022 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Events\Codes\CodeTypeInstalledEvent;

// Function to copy a package to temp
// $type (RXNORM, SNOMED etc.)
function temp_copy($filename, $type)
{

    if (!file_exists($filename)) {
        return false;
    }

    if (!file_exists($GLOBALS['temporary_files_dir'] . "/" . $type)) {
        if (!mkdir($GLOBALS['temporary_files_dir'] . "/" . $type, 0777, true)) {
                return false;
        }
    }

    if (copy($filename, $GLOBALS['temporary_files_dir'] . "/" . $type . "/" . basename($filename))) {
        return true;
    } else {
        return false;
    }
}

// Function to unarchive a package
// $type (RXNORM, SNOMED etc.)
function temp_unarchive($filename, $type)
{
    $filename = $GLOBALS['temporary_files_dir'] . "/" . $type . "/" . basename($filename);
    if (!file_exists($filename)) {
        return false;
    } elseif ($type == "ICD10") {
        // copy zip file contents to /tmp/ICD10 due to CMS zip file
        $zip = new ZipArchive();
        $path = $GLOBALS['temporary_files_dir'] . "/" . $type;
        if ($zip->open($filename) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $sub_dir_filename = $zip->getNameIndex($i);
                $fileinfo = pathinfo($sub_dir_filename);
                if (!(copy("zip://" . $filename . "#" . $sub_dir_filename, "$path/" . $fileinfo['basename']))) {
                    return false;
                }
            }
            $zip->close();
            return true;
        } else {
            return false;
        }
    } else {
        // unzip the file
        $zip = new ZipArchive();
        if ($zip->open($filename) === true) {
            if (!($zip->extractTo($GLOBALS['temporary_files_dir'] . "/" . $type))) {
                return false;
            }
            $zip->close();
            return true;
        }
    }
}

// Function to import the RXNORM tables
// $is_windows_flag - pass the IS_WINDOWS constant
function rxnorm_import($is_windows_flag)
{
    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('RXNORM', ['is_windows_flag' => $is_windows_flag]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_PRE);
    }
    // set paths
    $dirScripts = $GLOBALS['temporary_files_dir'] . "/RXNORM/scripts/mysql";
    $dir = $GLOBALS['temporary_files_dir'] . "/RXNORM/rrf";
    $dir = str_replace('\\', '/', $dir);

    $rx_info = array();
    $rx_info['rxnatomarchive'] = array('title' => "Archive Data", 'dir' => "$dir", 'origin' => "RXNATOMARCHIVE.RRF", 'filename' => "RXNATOMARCHIVE.RRF", 'table' => "rxnatomarchive", 'required' => 0);
    $rx_info['rxnconso'] = array('title' => "Concept Names and Sources", 'dir' => "$dir", 'origin' => "RXNCONSO.RRF", 'filename' => "RXNCONSO.RRF", 'table' => "rxnconso",  'required' => 1);
    $rx_info['rxncui'] = array('title' => "Retired RXCUI Data", 'dir' => "$dir", 'origin' => "RXNCUI.RRF", 'filename' => "RXNCUI.RRF", 'table' => "rxncui", 'required' => 1);
    $rx_info['rxncuichanges'] = array('title' => "Concept Changes", 'dir' => "$dir", 'origin' => "RXNCUICHANGES.RRF", 'filename' => "RXNCUICHANGES.RRF", 'table' => "rxncuichanges", 'required' => 1);
    $rx_info['rxndoc'] = array('title' => "Documentation for Abbreviated Values", 'dir' => "$dir", 'origin' => "RXNDOC.RRF", 'filename' => "RXNDOC.RRF", 'table' => "rxndoc", 'required' => 1);
    $rx_info['rxnrel'] = array('title' => "Relationships", 'dir' => "$dir", 'origin' => "RXNREL.RRF", 'filename' => "RXNREL.RRF", 'table' => "rxnrel", 'required' => 1);
    $rx_info['rxnsab'] = array('title' => "Source Information", 'dir' => "$dir", 'origin' => "RXNSAB.RRF", 'filename' => "RXNSAB.RRF", 'table' => "rxnsab", 'required' => 0);
    $rx_info['rxnsat'] = array('title' => "Simple Concept and Atom Attributes", 'dir' => "$dir", 'origin' => "RXNSAT.RRF", 'filename' => "RXNSAT.RRF", 'table' => "rxnsat", 'required' => 0);
    $rx_info['rxnsty'] = array('title' => "Semantic Types ", 'dir' => "$dir", 'origin' => "RXNSTY.RRF", 'filename' => "RXNSTY.RRF", 'table' => "rxnsty", 'required' => 1);

    // load scripts
    $file_load = file_get_contents($dirScripts . '/Table_scripts_mysql_rxn.sql', true);
    if ($is_windows_flag) {
        $data_load = file_get_contents($dirScripts . '/Load_scripts_mysql_rxn_win.sql', true);
    } else {
        $data_load = file_get_contents($dirScripts . '/Load_scripts_mysql_rxn_unix.sql', true);
    }

    $indexes_load = file_get_contents($dirScripts . '/Indexes_mysql_rxn.sql', true);

    //
    // Creating the structure for table and applying indexes
    //

    $file_array = explode(";", $file_load);
    foreach ($file_array as $val) {
        if (trim($val) != '') {
            sqlStatementNoLog($val);
        }
    }

    $indexes_array = explode(";", $indexes_load);

    foreach ($indexes_array as $val1) {
        if (trim($val1) != '') {
            sqlStatementNoLog($val1);
        }
    }


    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("SET autocommit=0");
    sqlStatementNoLog("START TRANSACTION");
    $data = explode(";", $data_load);
    foreach ($data as $val) {
        foreach ($rx_info as $key => $value) {
            $file_name = $value['origin'];
            $replacement = $dir . "/" . $file_name;

            $pattern = '/' . $file_name . '/';
            if (strpos($val, $file_name) !== false) {
                $val1 = str_replace($file_name, $replacement, $val);
                if (trim($val1) != '') {
                    sqlStatementNoLog($val1);
                }
            }
        }
    }

    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("COMMIT");
    sqlStatementNoLog("SET autocommit=1");

    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('RXNORM', ['is_windows_flag' => $is_windows_flag]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_POST);
    }

    return true;
}

// Function to import SNOMED tables
function snomed_import($us_extension = false)
{
    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('SNOMED', ['us_extension' => $us_extension]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_POST);
    }

    // set up array
    $table_array_for_snomed = array(
        "sct_concepts_drop" => "DROP TABLE IF EXISTS `sct_concepts`",
        "sct_concepts_structure" => "CREATE TABLE IF NOT EXISTS `sct_concepts` (
            `ConceptId` bigint(20) NOT NULL,
            `ConceptStatus` int(11) NOT NULL,
            `FullySpecifiedName` varchar(255) NOT NULL,
            `CTV3ID` varchar(5) NOT NULL,
            `SNOMEDID` varchar(8) NOT NULL,
            `IsPrimitive` tinyint(1) NOT NULL,
            PRIMARY KEY (`ConceptId`)
            ) ENGINE=InnoDB",
        "sct_descriptions_drop" => "DROP TABLE IF EXISTS `sct_descriptions`",
        "sct_descriptions_structure" => "CREATE TABLE IF NOT EXISTS `sct_descriptions` (
            `DescriptionId` bigint(20) NOT NULL,
            `DescriptionStatus` int(11) NOT NULL,
            `ConceptId` bigint(20) NOT NULL,
            `Term` varchar(255) NOT NULL,
            `InitialCapitalStatus` tinyint(1) NOT NULL,
            `DescriptionType` int(11) NOT NULL,
            `LanguageCode` varchar(8) NOT NULL,
            PRIMARY KEY (`DescriptionId`),
            KEY `idx_concept_id` (`ConceptId`)
            ) ENGINE=InnoDB",
        "sct_relationships_drop" => "DROP TABLE IF EXISTS `sct_relationships`",
        "sct_relationships_structure" => "CREATE TABLE IF NOT EXISTS `sct_relationships` (
            `RelationshipId` bigint(20) NOT NULL,
            `ConceptId1` bigint(20) NOT NULL,
            `RelationshipType` bigint(20) NOT NULL,
            `ConceptId2` bigint(20) NOT NULL,
            `CharacteristicType` int(11) NOT NULL,
            `Refinability` int(11) NOT NULL,
            `RelationshipGroup` int(11) NOT NULL,
            PRIMARY KEY (`RelationshipId`)
            ) ENGINE=InnoDB"
    );

    // set up paths
    $dir_snomed = $GLOBALS['temporary_files_dir'] . "/SNOMED/";
    $sub_path = "Terminology/Content/";
    $dir = $dir_snomed;
    $dir = str_replace('\\', '/', $dir);

    // executing the create statement for tables, these are defined in snomed_capture.inc.php file
    // this is skipped if the US extension is being added
    if (!$us_extension) {
        foreach ($table_array_for_snomed as $val) {
            if (trim($val) != '') {
                sqlStatement($val);
            }
        }
    }

    // reading the SNOMED directory and identifying the files to import and replacing the variables by originals values.
    if (is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            if ($filename != "." && $filename != ".." && !strpos($filename, "zip")) {
                $path = $dir . "" . $filename . "/" . $sub_path;
                if (!(is_dir($path))) {
                    $path = $dir . "" . $filename . "/RF1Release/" . $sub_path;
                }

                if (is_dir($path) && $handle1 = opendir($path)) {
                    while (false !== ($filename1 = readdir($handle1))) {
                        $load_script = "Load data local infile '#FILENAME#' into table #TABLE# fields terminated by '\\t' ESCAPED BY '' lines terminated by '\\n' ignore 1 lines   ";
                        $array_replace = array("#FILENAME#","#TABLE#");
                        if ($filename1 != "." && $filename1 != "..") {
                            $file_replace = $path . $filename1;
                            if (strpos($filename1, "Concepts") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct_concepts"), $load_script);
                            }

                            if (strpos($filename1, "Descriptions") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct_descriptions"), $load_script);
                            }

                            if (strpos($filename1, "Relationships") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct_relationships"), $load_script);
                            }

                            if ($new_str != '') {
                                sqlStatement($new_str);
                            }
                        }
                    }
                }

                closedir($handle1);
            }
        }

        closedir($handle);
    }

    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('SNOMED', ['us_extension' => $us_extension]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_POST);
    }

    return true;
}

function drop_old_sct()
{
    $array_to_truncate = array(
        "sct_concepts_drop" => "DROP TABLE IF EXISTS `sct_concepts`",
        "sct_descriptions_drop" => "DROP TABLE IF EXISTS `sct_descriptions`",
        "sct_relationships_drop" => "DROP TABLE IF EXISTS `sct_relationships`"
    );
    foreach ($array_to_truncate as $val) {
        if (trim($val) != '') {
            sqlStatement($val);
        }
    }
}

function drop_old_sct2()
{
    $array_to_truncate = array(
        "sct2_concept_drop" => "DROP TABLE IF EXISTS `sct2_concept`",
        "sct2_description_drop" => "DROP TABLE IF EXISTS `sct2_description`",
        "sct2_identifier_drop" => "DROP TABLE IF EXISTS `sct2_identifier`",
        "sct2_relationship_drop" => "DROP TABLE IF EXISTS `sct2_relationship`",
        "sct2_statedrelationship_drop" => "DROP TABLE IF EXISTS `sct2_statedrelationship`",
        "sct2_textdefinition_drop" => "DROP TABLE IF EXISTS `sct2_textdefinition`"
    );
    foreach ($array_to_truncate as $val) {
        if (trim($val) != '') {
            sqlStatement($val);
        }
    }
}

function chg_ct_external_torf1()
{
    sqlStatement("UPDATE code_types SET ct_external = 2 WHERE ct_key = 'SNOMED'");
    sqlStatement("UPDATE code_types SET ct_external = 7 WHERE ct_key = 'SNOMED-CT'");
    sqlStatement("UPDATE code_types SET ct_external = 9 WHERE ct_key = 'SNOMED-PR'");
}

function chg_ct_external_torf2()
{
    sqlStatement("UPDATE code_types SET ct_external = 10 WHERE ct_key = 'SNOMED'");
    sqlStatement("UPDATE code_types SET ct_external = 11 WHERE ct_key = 'SNOMED-CT'");
    sqlStatement("UPDATE code_types SET ct_external = 12 WHERE ct_key = 'SNOMED-PR'");
}

function snomedRF2_import()
{
    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('SNOMED', []);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_PRE);
    }

    // set up array
    $table_array_for_snomed = array(
        "sct2_concept_drop" => "DROP TABLE IF EXISTS `sct2_concept`",
        "sct2_concept_structure" => "CREATE TABLE IF NOT EXISTS `sct2_concept` (
            `id` bigint(20) NOT NULL,
            `effectiveTime` date NOT NULL,
            `active` int(11) NOT NULL,
            `moduleId` bigint(20) NOT NULL,
            `definitionStatusId` bigint(25) NOT NULL,
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB",
        "sct2_description_drop" => "DROP TABLE IF EXISTS `sct2_description`",
        "sct2_description_structure" => "CREATE TABLE IF NOT EXISTS `sct2_description` (
            `id` bigint(20) NOT NULL,
            `effectiveTime` date NOT NULL,
            `active` bigint(11) NOT NULL,
            `moduleId` bigint(25) NOT NULL,
            `conceptId` bigint(20) NOT NULL,
            `languageCode` varchar(8) NOT NULL,
            `typeId` bigint(25) NOT NULL,
            `term` varchar(255) NOT NULL,
            `caseSignificanceId` bigint(25) NOT NULL,
             PRIMARY KEY (`id`, `active`, `conceptId`),
             KEY `idx_concept_id` (`conceptId`)
            ) ENGINE=InnoDB",
        "sct2_identifier_drop" => "DROP TABLE IF EXISTS `sct2_identifier`",
        "sct2_identifier_structure" => "CREATE TABLE IF NOT EXISTS `sct2_identifier` (
            `identifierSchemeId` bigint(25) NOT NULL,
            `alternateIdentifier` bigint(25) NOT NULL,
            `effectiveTime` date NOT NULL,
            `active` int(11) NOT NULL,
            `moduleId` bigint(25) NOT NULL,
            `referencedComponentId` bigint(25) NOT NULL,
             PRIMARY KEY (`identifierSchemeId`)
            ) ENGINE=InnoDB",
        "sct2_relationship_drop" => "DROP TABLE IF EXISTS `sct2_relationship`",
        "sct2_relationship_structure" => "CREATE TABLE IF NOT EXISTS `sct2_relationship` (
            `id` bigint(20) NOT NULL,
            `effectiveTime` date NOT NULL,
            `active` int(11) NOT NULL,
            `moduleId` bigint(25) NOT NULL,
            `sourceId` bigint(20) NOT NULL,
            `destinationId` bigint(20) NOT NULL,
            `typeId` bigint(25) NOT NULL,
            `characteristicTypeId` bigint(25) NOT NULL,
            `modifierId` bigint(25) NOT NULL,
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB",
        "sct2_statedrelationship_drop" => "DROP TABLE IF EXISTS `sct2_statedrelationship`",
        "sct2_statedrelationship_structure" => "CREATE TABLE IF NOT EXISTS `sct2_statedrelationship` (
            `id` bigint(20) NOT NULL,
            `effectiveTime` date NOT NULL,
            `active` int(11) NOT NULL,
            `moduleId` bigint(25) NOT NULL,
            `sourceId` bigint(20) NOT NULL,
            `destinationId` bigint(20) NOT NULL,
            `relationshipGroup` int(11) NOT NULL,
            `typeId` bigint(25) NOT NULL,
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB",
        "sct2_textdefinition_drop" => "DROP TABLE IF EXISTS `sct2_textdefinition`",
        "sct2_textdefinition_structure" => "CREATE TABLE IF NOT EXISTS `sct2_textdefinition` (
            `id` bigint(20) NOT NULL,
            `effectiveTime` date NOT NULL,
            `active` int(11) NOT NULL,
            `moduleId` bigint(25) NOT NULL,
            `conceptId` bigint(20) NOT NULL,
            `languageCode` varchar(8) NOT NULL,
            `typeId` bigint(25) NOT NULL,
            `term` varchar(655) NOT NULL,
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB"
    );

    // set up paths
    $dir_snomed = $GLOBALS['temporary_files_dir'] . "/SNOMED/";
    // $sub_path="Terminology/Content/";
    $sub_path = "Full/Terminology/";
    $dir = $dir_snomed;
    $dir = str_replace('\\', '/', $dir);

    // executing the create statement for tables, these are defined in snomed_capture.inc file
    // this is skipped if the US extension is being added
    //if (!$us_extension) {
        //var_dump($us_extension);
    foreach ($table_array_for_snomed as $val) {
        if (trim($val) != '') {
            sqlStatement($val);
        }
    }
    //}

    // reading the SNOMED directory and identifying the files to import and replacing the variables by originals values.
    if (is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            if ($filename != "." && $filename != ".." && !strpos($filename, "zip")) {
                $path = $dir . "" . $filename . "/" . $sub_path;
                if (!(is_dir($path))) {
                    $path = $dir . "" . $filename . "/RF2Release/" . $sub_path;
                }
                if (is_dir($path) && $handle1 = opendir($path)) {
                    while (false !== ($filename1 = readdir($handle1))) {
                        $load_script = "Load data local infile '#FILENAME#' into table #TABLE# fields terminated by '\\t' ESCAPED BY '' lines terminated by '\\n' ignore 1 lines   ";
                        $array_replace = array("#FILENAME#","#TABLE#");
                        if ($filename1 != "." && $filename1 != "..") {
                            $file_replace = $path . $filename1;
                            if (strpos($filename1, "Concept") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct2_concept"), $load_script);
                            }
                            if (strpos($filename1, "Description") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct2_description"), $load_script);
                            }
                            if (strpos($filename1, "Identifier") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct2_identifier"), $load_script);
                            }
                            if (strpos($filename1, "Relationship") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct2_relationship"), $load_script);
                            }
                            if (strpos($filename1, "StatedRelationship") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct2_statedrelationship"), $load_script);
                            }
                            if (strpos($filename1, "TextDefinition") !== false) {
                                $new_str = str_replace($array_replace, array($file_replace,"sct2_textdefinition"), $load_script);
                            }
                            if ($new_str != '') {
                                sqlStatement($new_str);
                            }
                        }
                    }
                }
                closedir($handle1);
            }
        }
        closedir($handle);
    }

    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('SNOMED', []);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_POST);
    }

    return true;
}

// Function to import ICD tables $type differentiates ICD 9, 10 and eventually 11 (circa 2018 :-) etc.
//
function icd_import($type)
{
    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('ICD', ['type' => $type]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_PRE);
    }

    // set up paths
    $dir_icd = $GLOBALS['temporary_files_dir'] . "/" . $type . "/";
    $dir = str_replace('\\', '/', $dir_icd);
    $db_load = '';
    $db_update = '';

    // the incoming array is a metadata array containing keys that substr match to the incoming filename
    // followed by the field name, position and length of each fixed length text record in the incoming
    // flat files. There are separate definitions for ICD 9 and 10 based on the type passed in
    $incoming = array();

    // find active revision
    $res = sqlQueryNoLog("SELECT max(revision) rev FROM icd10_pcs_order_code");
    $next_rev = ($res['rev'] ?? 0) + 1;
    $incoming['icd10pcs_codes_'] = array(
        'TABLENAME' => "icd10_pcs_order_code",
        'FLD1' => "pcs_code", 'POS1' => 0, 'LEN1' => 7,
        'FLD2' => "long_desc", 'POS2' => 8, 'LEN2' => 300,
        'REV' => $next_rev
    );

    $res = sqlQueryNoLog("SELECT max(revision) rev FROM icd10_dx_order_code");
    $next_rev = ($res['rev'] ?? 0) + 1;
    $incoming['icd10cm_order_'] = array(
        'TABLENAME' => "icd10_dx_order_code",
        'FLD1' => "dx_code", 'POS1' => 6, 'LEN1' => 7,
        'FLD2' => "valid_for_coding", 'POS2' => 14, 'LEN2' => 1,
        'FLD3' => "short_desc", 'POS3' => 16, 'LEN3' => 60,
        'FLD4' => "long_desc", 'POS4' => 77, 'LEN4' => 300,
        'REV' => $next_rev
    );

    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("SET autocommit=0");
    sqlStatementNoLog("START TRANSACTION");

    // first inactivate older set(s)
    sqlStatementNoLog("UPDATE icd10_pcs_order_code SET active = 0");
    sqlStatementNoLog("UPDATE icd10_dx_order_code SET active = 0");

    if (is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            // bypass unwanted entries
            if (!stripos($filename, ".txt") || stripos($filename, "addenda")) {
                continue;
            }

            $keys = array_keys($incoming);
            while ($this_key = array_pop($keys)) {
                if (stripos($filename, $this_key) !== false) {
                    $generator = getFileData($dir . $filename);
                    foreach ($generator as $value) {
                        $run_sql = "INSERT INTO `" . $incoming[$this_key]['TABLENAME'] . "` (";
                        $sql_place = "(";
                        $sql_values = [];
                        foreach (range(1, 4) as $field) {
                            $fld = "FLD" . $field;
                            $nxtfld = "FLD" . ($field + 1);
                            $pos = "POS" . $field;
                            $len = "LEN" . $field;
                            $run_sql .= $incoming[$this_key][$fld] . ", ";
                            $sql_place .= "?, ";
                            // concat this fields template in the sql string
                            array_push($sql_values, substr($value, $incoming[$this_key][$pos], $incoming[$this_key][$len]));
                            if (!array_key_exists($nxtfld, $incoming[$this_key])) {
                                $run_sql .= "active, revision) VALUES ";
                                $sql_place .= "?, ?)";
                                array_push($sql_values, 1);
                                array_push($sql_values, $incoming[$this_key]['REV']);
                                sqlStatementNoLog($run_sql . $sql_place, $sql_values);
                                break;
                            } else {
                                $run_sql .= " ";
                                $sql_place .= " ";
                            }
                        }
                    }
                }
            }
        }
        // Settings to drastically speed up import with InnoDB
        sqlStatementNoLog("COMMIT");
        sqlStatementNoLog("SET autocommit=1");
        closedir($handle);
    } else {
        echo htmlspecialchars(xl('ERROR: No ICD import directory.'), ENT_NOQUOTES) . "<br />";
        return;
    }

    // now update the tables where necessary
    sqlStatement("update `icd10_dx_order_code` SET formatted_dx_code = dx_code");
    sqlStatement("update `icd10_dx_order_code` SET formatted_dx_code = concat(concat(left(dx_code, 3), '.'), substr(dx_code, 4)) WHERE LENGTH(dx_code) > 3");

    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('ICD', ['type' => $type]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_POST);
    }

    return true;
}

function valueset_import($type)
{
    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('CQM_VALUESET', ['type' => $type]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_PRE);
    }

    $dir_valueset = $GLOBALS['temporary_files_dir'] . "/" . $type . "/";
    $dir = str_replace('\\', '/', $dir_valueset);

    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("SET autocommit=0");
    sqlStatementNoLog("START TRANSACTION");
    if (is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            if (stripos($filename, ".xml")) {
                    $abs_path = $dir . $filename;
                    $xml  = simplexml_load_file($abs_path, null, null, 'ns0', true);
                foreach ($xml->DescribedValueSet as $vset) {
                    $vset_attr = $vset->attributes();
                    $nqf = $vset->xpath('ns0:Group[@displayName="NQF Number"]/ns0:Keyword');
                    foreach ($vset->ConceptList as $cp) {
                        foreach ($nqf as $nqf_code) {
                            foreach ($cp->Concept as $con) {
                                $con_attr = $con->attributes();
                                sqlStatementNoLog(
                                    "INSERT INTO valueset values(?,?,?,?,?,?,?) on DUPLICATE KEY UPDATE 
                                    code_system = values(code_system),
                                    description = values(description),
                                    valueset_name = values(valueset_name)",
                                    array(
                                        $nqf_code,
                                        $con_attr->code,
                                        $con_attr->codeSystem,
                                        $con_attr->codeSystemName,
                                        $vset_attr->ID,
                                        $con_attr->displayName,
                                        $vset_attr->displayName
                                    )
                                );
                                sqlStatementNoLog(
                                    "INSERT INTO valueset_oid values(?,?,?,?,?,?,?) on DUPLICATE KEY UPDATE 
                                    code_system = values(code_system),
                                    description = values(description),
                                    valueset_name = values(valueset_name)",
                                    array(
                                         $nqf_code,
                                         $vset_attr->ID,
                                         $con_attr->codeSystem,
                                         'OID',
                                         $vset_attr->ID,
                                         $vset_attr->displayName,
                                         $vset_attr->displayName
                                    )
                                );
                            }
                        }
                    }
                }

                sqlStatementNoLog("UPDATE valueset set code_type='SNOMED CT' where code_type='SNOMEDCT'");
                sqlStatementNoLog("UPDATE valueset set code_type='ICD9' where code_type='ICD9CM'");
                sqlStatementNoLog("UPDATE valueset set code_type='ICD10' where code_type='ICD10CM'");
            }
        }
    }

    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("COMMIT");
    sqlStatementNoLog("SET autocommit=1");

    // let's fire off an event so people can listen if needed and handle any module upgrading, version checks,
    // or any manual processing that needs to occur.
    if (!empty($GLOBALS['kernel'])) {
        $codeTypeInstalledEvent = new CodeTypeInstalledEvent('CQM_VALUESET', ['type' => $type]);
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($codeTypeInstalledEvent, CodeTypeInstalledEvent::EVENT_INSTALLED_POST);
    }
        return true;
}

// Function to clean up temp files
// $type (RXNORM etc.)
function temp_dir_cleanup($type)
{
    if (is_dir($GLOBALS['temporary_files_dir'] . "/" . $type)) {
        rmdir_recursive($GLOBALS['temporary_files_dir'] . "/" . $type);
    }
}

// Function to update version tracker table if successful
// $type (RXNORM etc.)
function update_tracker_table($type, $revision, $version, $file_checksum)
{
    if ($type == 'RXNORM') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'RXNORM',?,?,?)", array($revision, $version, $file_checksum));
        return true;
    } elseif ($type == 'SNOMED') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'SNOMED',?,?,?)", array($revision, $version, $file_checksum));
        return true;
    } elseif ($type == 'ICD9') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'ICD9',?,?,?)", array($revision, $version, $file_checksum));
        return true;
    } elseif ($type == 'CQM_VALUESET') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'CQM_VALUESET',?,?,?)", array($revision, $version, $file_checksum));
        return true;
    } else { // $type == 'ICD10')
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'ICD10',?,?,?)", array($revision, $version, $file_checksum));
        return true;
    }

    return false;
}

// Function to delete an entire directory
function rmdir_recursive($dir)
{
    $files = scandir($dir);
    array_shift($files);    // remove '.' from array
    array_shift($files);    // remove '..' from array

    foreach ($files as $file) {
        $file = $dir . '/' . $file;
        if (is_dir($file)) {
            rmdir_recursive($file);
            continue;
        }

        unlink($file);
    }

    rmdir($dir);
}

// function to cleanup temp, copy and unarchive the zip file
function handle_zip_file($mode, $file)
{
        // 1. copy the file to temp directory
    if (!temp_copy($file, $mode)) {
        echo htmlspecialchars(xl('ERROR: Unable to copy the file.'), ENT_NOQUOTES) . "<br />";
        temp_dir_cleanup($mode);
        exit;
    }
        // 2. unarchive the file
    if (!temp_unarchive($file, $mode)) {
        echo htmlspecialchars(xl('ERROR: Unable to extract the file.'), ENT_NOQUOTES) . "<br />";
        temp_dir_cleanup($mode);
        exit;
    }
}

/**
 * @return Generator
 */
function getFileData($fn)
{
    $file = fopen($fn, 'r');

    if (!$file) {
        return;
    }

    while (($line = fgets($file)) !== false) {
        yield $line;
    }

    fclose($file);
}
