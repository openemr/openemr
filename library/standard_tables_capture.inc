<?php
/*******************************************************************/
// Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
//
// Authors:
//         Rohit Kumar <pandit.rohit@netsity.com>
//         Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
/*******************************************************************/
// This library contains functions for importing the RxNorm and SNOMED
// databases.
/*******************************************************************/

// Function to copy a package to temp
// $type (rxnorm, snomed etc.)
function temp_copy($filename,$type) {

    if(!file_exists($filename)) {
        return false;
    }

    if(!mkdir($GLOBALS['temporary_files_dir']."/".$type, 0777, true)) {
        return false;
    }

    if(copy($filename,$GLOBALS['temporary_files_dir']."/".$type."/".basename($filename))) {
        return true;
    }
    else {
        return false;
    }
}

// Function to unarchive a package
// $type (rxnorm, snomed etc.)
function temp_unarchive($filename,$type) {
    $filename = $GLOBALS['temporary_files_dir']."/".$type."/".basename($filename);
    if(!file_exists($filename)) {
        return false;
    }
    else {
        $zip = new ZipArchive;
        if ($zip->open($filename) === TRUE) {
            if (!($zip->extractTo($GLOBALS['temporary_files_dir']."/".$type))) {
                return false;
            }
            $zip->close();
            return true;
        }
        else {
            return false;
        }
    }
}


// Function to import the rxnorm tables
// $is_windows_flag - pass the IS_WINDOWS constant
function rxnorm_import($is_windows_flag) {

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

    // set paths
    $dirScripts = $GLOBALS['temporary_files_dir']."/rxnorm/scripts/mysql";
    $dir = $GLOBALS['temporary_files_dir']."/rxnorm/rrf";

    // load scripts
    $file_load = file_get_contents($dirScripts.'/Table_scripts_mysql_rxn.sql', true);
    if ($is_windows_flag) {
        $data_load = file_get_contents($dirScripts.'/Load_scripts_mysql_rxn_win.sql', true);
    }
    else {
        $data_load = file_get_contents($dirScripts.'/Load_scripts_mysql_rxn_unix.sql', true);
    }
    $indexes_load = file_get_contents($dirScripts.'/Indexes_mysql_rxn.sql', true);

    //
    // Creating the structure for table and applying indexes
    //

    $file_array=explode(";",$file_load);
    foreach($file_array as $val){
        if(trim($val)!='')
        {
            sqlStatement($val);
        }
    }

    $indexes_array=explode(";",$indexes_load);

    foreach($indexes_array as $val1){
        if(trim($val1)!='')
        {
            sqlStatement($val1);
        }
    }

    $data=explode(";",$data_load);
    foreach($data as $val)
    {
        foreach($rx_info as $key => $value)
        {
            $file_name= $value['origin'];
            $replacement=$dir."/".$file_name;

            $pattern='/'.$file_name.'/';
            if(strpos($val,$file_name))     {
                $val1[]=       str_replace($file_name,$replacement,$val);
            }
        }
    }
    foreach($val1 as $val){
        if(trim($val)!='')      {
            sqlStatement($val);
        }
    }

    return true;
}

// Function to import snomed tables
function snomed_import() {

    // set up array
    $table_array_for_snomed=array(
        "sct_concepts_drop"=>"DROP TABLE IF EXISTS `sct_concepts`",
        "sct_concepts_structure"=>"CREATE TABLE IF NOT EXISTS `sct_concepts` (
            `ConceptId` bigint(20) NOT NULL,
            `ConceptStatus` int(11) NOT NULL,
            `FullySpecifiedName` varchar(255) NOT NULL,
            `CTV3ID` varchar(5) NOT NULL,
            `SNOMEDID` varchar(8) NOT NULL,
            `IsPrimitive` tinyint(1) NOT NULL,
            PRIMARY KEY (`ConceptId`)
            ) ENGINE=MyISAM",
        "sct_descriptions_drop"=>"DROP TABLE IF EXISTS `sct_descriptions`",
        "sct_descriptions_structure"=>"CREATE TABLE IF NOT EXISTS `sct_descriptions` (
            `DescriptionId` bigint(20) NOT NULL,
            `DescriptionStatus` int(11) NOT NULL,
            `ConceptId` bigint(20) NOT NULL,
            `Term` varchar(255) NOT NULL,
            `InitialCapitalStatus` tinyint(1) NOT NULL,
            `DescriptionType` int(11) NOT NULL,
            `LanguageCode` varchar(8) NOT NULL,
            PRIMARY KEY (`DescriptionId`)
            ) ENGINE=MyISAM",
        "sct_relationships_drop"=>"DROP TABLE IF EXISTS `sct_relationships`",
        "sct_relationships_structure"=>"CREATE TABLE IF NOT EXISTS `sct_relationships` (
            `RelationshipId` bigint(20) NOT NULL,
            `ConceptId1` bigint(20) NOT NULL,
            `RelationshipType` bigint(20) NOT NULL,
            `ConceptId2` bigint(20) NOT NULL,
            `CharacteristicType` int(11) NOT NULL,
            `Refinability` int(11) NOT NULL,
            `RelationshipGroup` int(11) NOT NULL,
            PRIMARY KEY (`RelationshipId`)
            ) ENGINE=MyISAM"
);

    // set up paths
    $dir_snomed = $GLOBALS['temporary_files_dir']."/snomed/";
    $sub_path="Terminology/Content/";
    $dir=$dir_snomed;

    // executing the create statement for tables, these are defined in snomed_capture.inc file
    foreach($table_array_for_snomed as $val){
        if(trim($val)!=''){
            sqlStatement($val);
        }
    }

    // reading the snomed directry and identifying the files to import and replacing the variables by originals values.
    if( is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            if ($filename != "." && $filename != ".." && strpos($filename,"zip")==0) {
                $path=$dir."".$filename."/".$sub_path;
                if( is_dir($path) && $handle1 = opendir($path)) {
                    while (false !== ($filename1 = readdir($handle1))) {
                        $load_script="Load data local infile '#FILENAME#' into table #TABLE# fields terminated by '\\t' ESCAPED BY '' lines terminated by '\\n' ignore 1 lines   ";
                        $array_replace=array("#FILENAME#","#TABLE#");
                        if ($filename1 != "." && $filename1 != "..") {
                            $file_replace=$path.$filename1;
                            if(strpos($filename1,"Concepts")){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_concepts"),$load_script);
                            }	
                            if(strpos($filename1,"Descriptions")){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_descriptions"),$load_script);
                            }
                            if(strpos($filename1,"Relationships")){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_relationships"),$load_script);
                            }
                            if($new_str!=''){
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

    return true;
}

// Function to clean up temp files
// $type (rxnorm etc.)
function temp_dir_cleanup($type) {
    if(is_dir($GLOBALS['temporary_files_dir']."/".$type)) {
        rmdir_recursive($GLOBALS['temporary_files_dir']."/".$type);
    }
}

// Function to update version tracker table if successful
// $type (rxnorm etc.)
function update_tracker_table($type,$revision) {
    if ($type == 'rxnorm') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`) VALUES (NOW(),'RXNORM',?)", array($revision) );
        return true;
    }
    else { //$type == 'snomed'
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`) VALUES (NOW(),'SNOMED',?)", array($revision) );
        return true;
    }
    return false;
}

// Function to delete an entire directory
function rmdir_recursive($dir) {
    $files = scandir($dir);
    array_shift($files);    // remove '.' from array
    array_shift($files);    // remove '..' from array

    foreach ($files as $file) {
        $file = $dir . '/' . $file;
        if (is_dir($file)) {
            rmdir_recursive($file);
            rmdir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dir);
}

?>
