<?php
/**
 * This library contains functions that implement the database load processing
 * of external database files into openEMR
 *
 * Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rohit Kumar <pandit.rohit@netsity.com>
 * @author  (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */


// Function to copy a package to temp
// $type (RXNORM, SNOMED etc.)
function temp_copy($filename,$type) {
    if(!file_exists($filename)) {
        return false;
    }

    if(!file_exists($GLOBALS['temporary_files_dir']."/".$type)) {
        if(!mkdir($GLOBALS['temporary_files_dir']."/".$type, 0777, true)) {
        		error_log("Could not make dir ".$GLOBALS['temporary_files_dir']."/".$type);
                return false;
        }
    }

    if(copy($filename,$GLOBALS['temporary_files_dir']."/".$type."/".basename($filename))) {
        return true;
    }
    else {
    	error_log("Could not copy file to".$GLOBALS['temporary_files_dir']."/".$type."/".basename($filename));
        return false;
    }
}

// Function to unarchive a package
// $type (RXNORM, SNOMED etc.)
function temp_unarchive($filename,$type) {
    $filename = $GLOBALS['temporary_files_dir']."/".$type."/".basename($filename);
    if(!file_exists($filename)) {
        return false;
    }
    else {

	// let's unzip the file
	// use checksums to determine the "version" 
	//
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

// Function to import the RXNORM tables
// $is_windows_flag - pass the IS_WINDOWS constant
function rxnorm_import($is_windows_flag) {

    // set paths
    $dirScripts = $GLOBALS['temporary_files_dir']."/RXNORM/scripts/mysql";
    $dir = $GLOBALS['temporary_files_dir']."/RXNORM/rrf";
    $dir=str_replace('\\','/',$dir);

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
            if(strpos($val,$file_name) !== false) {
                $val1 = str_replace($file_name,$replacement,$val);
        	if(trim($val1)!='') {
            	    sqlStatement($val1);
        	}
            }
        }
    }
    return true;
}

// Function to import SNOMED tables
function snomed_import($us_extension=FALSE) {

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
    $dir_snomed = $GLOBALS['temporary_files_dir']."/SNOMED/";
    $sub_path="Terminology/Content/";
    $dir=$dir_snomed;
    $dir=str_replace('\\','/',$dir);

    // executing the create statement for tables, these are defined in snomed_capture.inc file
    // this is skipped if the US extension is being added
    if (!$us_extension) {
        foreach($table_array_for_snomed as $val){
            if(trim($val)!=''){
                sqlStatement($val);
            }
        }
    }

    // reading the SNOMED directory and identifying the files to import and replacing the variables by originals values.
    if( is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            if ($filename != "." && $filename != ".." && !strpos($filename,"zip")) {
                $path=$dir."".$filename."/".$sub_path;
                if (!(is_dir($path))) {
                    $path=$dir."".$filename."/RF1Release/".$sub_path;
                }
                if( is_dir($path) && $handle1 = opendir($path)) {
                    while (false !== ($filename1 = readdir($handle1))) {
                        $load_script="Load data local infile '#FILENAME#' into table #TABLE# fields terminated by '\\t' ESCAPED BY '' lines terminated by '\\n' ignore 1 lines   ";
                        $array_replace=array("#FILENAME#","#TABLE#");
                        if ($filename1 != "." && $filename1 != "..") {
                            $file_replace=$path.$filename1;
                            if(strpos($filename1,"Concepts") !== false){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_concepts"),$load_script);
                            }	
                            if(strpos($filename1,"Descriptions") !== false){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_descriptions"),$load_script);
                            }
                            if(strpos($filename1,"Relationships") !== false){
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

/** Function to import SNOMED CT through the RF2 format. To start with, only
 * import the core. TODO: accommodate RF2 extensions.
 */
function snomed_rf2_import($version, $file_extension) {
	// set up array
	$table_array_for_snomed=array(			//concept table
			"sct_concepts_drop"=>"DROP TABLE IF EXISTS `sct_rf2_concepts`",
			"sct_concepts_structure"=>"CREATE TABLE IF NOT EXISTS `sct_rf2_concepts` (
            `conceptId` bigint(18) NOT NULL,
            `effectiveTime` int(8) NOT NULL,
			`active` tinyint(1) NOT NULL,
            `moduleId` bigint(18) NOT NULL,
            `definitionStatusId` bigint(18) NOT NULL,
            PRIMARY KEY (`conceptId`)
            ) ENGINE=MyISAM",
			"sct_descriptions_drop"=>"DROP TABLE IF EXISTS `sct_rf2_descriptions`",		//description table
			"sct_descriptions_structure"=>"CREATE TABLE IF NOT EXISTS `sct_rf2_descriptions` (
            `descriptionId` bigint(18) NOT NULL,
			`effectiveTime` int(8) NOT NULL,
			`active` tinyint(1) NOT NULL,
            `moduleId` bigint(18) NOT NULL,
            `conceptId` bigint(18) NOT NULL,
			`languageCode` varchar(8) NOT NULL,
			`typeId` bigint(18) NOT NULL,
            `term` varchar(255) NOT NULL,
            `caseSignificanceId` bigint(18) NOT NULL,
            PRIMARY KEY (`descriptionId`)
            ) ENGINE=MyISAM",
			"sct_relationships_drop"=>"DROP TABLE IF EXISTS `sct_rf2_relationships`",	//relation table
			"sct_relationships_structure"=>"CREATE TABLE IF NOT EXISTS `sct_rf2_relationships` (
            `relationshipId` bigint(18) NOT NULL,
			`effectiveTime` int(8) NOT NULL,
			`active` tinyint(1) NOT NULL,
            `moduleId` bigint(18) NOT NULL,
            `sourceId` bigint(18) NOT NULL,
            `destinationId` bigint(18) NOT NULL,
            `relationshipGroup` tinyint(1) NOT NULL,
			`typeId` bigint(18) NOT NULL,
            `characteristicTypeId`  bigint(18) NOT NULL,
            `modifierId`  bigint(18) NOT NULL,
            PRIMARY KEY (`relationshipId`)
            ) ENGINE=MyISAM",
			"sct_simplerefsets_drop"=>"DROP TABLE IF EXISTS `sct_rf2_simplerefsets`",	//simple refset (pick list) table
			"sct_simplerefsets_structure"=>"CREATE TABLE IF NOT EXISTS `sct_rf2_simplerefsets` (
            `id` bigint(36) NOT NULL,
			`effectiveTime` int(8) NOT NULL,
			`active` tinyint(1) NOT NULL,
            `moduleId` bigint(18) NOT NULL,
            `refsetId` bigint(18) NOT NULL,
            `referencedComponentId` bigint(18) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM",
			"sct_languagerefsets_drop"=>"DROP TABLE IF EXISTS `sct_rf2_languagerefsets`",	//language refset (to identify preferred terms)
			"sct_languagefsets_structure"=>"CREATE TABLE IF NOT EXISTS `sct_rf2_languagerefsets` (
            `id` bigint(36) NOT NULL,
			`effectiveTime` int(8) NOT NULL,
			`active` tinyint(1) NOT NULL,
            `moduleId` bigint(18) NOT NULL,
            `refsetId` bigint(18) NOT NULL,
            `referencedComponentId` bigint(18) NOT NULL,
			`acceptabilityId` bigint(18) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM",
			"sct_extendedmaprefsets_drop"=>"DROP TABLE IF EXISTS `sct_rf2_extendedmaprefsets`",	//mapping table (only included mappings to ICD-10)
			"sct_extendedmaprefsets_structure"=>"CREATE TABLE IF NOT EXISTS `sct_rf2_extendedmaprefsets` (	
            `id` bigint(36) NOT NULL,
			`effectiveTime` int(8) NOT NULL,
			`active` tinyint(1) NOT NULL,
            `moduleId` bigint(18) NOT NULL,
            `refsetId` bigint(18) NOT NULL,
            `referencedComponentId` bigint(18) NOT NULL,
			`mapGroup` int(8) NOT NULL,
			`mapPriority` int(8) NOT NULL,
			`mapRule` varchar(255) NOT NULL,
			`mapAdvice` varchar(255) NOT NULL,
			`mapTarget` varchar(36) NOT NULL,
			`correlationId` bigint(18) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM"
	);
		
	// executing the create statement for tables,
	foreach ( $table_array_for_snomed as $val ) {
		if (trim ( $val ) != '') {
			sqlStatement ( $val );
		}
	}
	
	 // set up paths
    $dir_snomed = $GLOBALS['temporary_files_dir']."/SNOMED_RF2/SnomedCT_RF2Release".$file_extension."/";
    $dir_snomed=str_replace('\\','/',$dir_snomed);
	
	if(!(is_dir($dir_snomed) && $handle = opendir($dir_snomed))) {
		echo "Cannot open $dir<br>";
		return false;
	}

	$load_script = "Load data local infile '#FILENAME#' into table #TABLE# fields terminated by '\\t' ESCAPED BY '' lines terminated by '\\n' ignore 1 lines   ";
	$array_replace = array (
			"#FILENAME#",
			"#TABLE#"
	);
	
	$dir_terminology=$dir_snomed."Snapshot/Terminology/";
	$dir_refset=$dir_snomed."Snapshot/Refset/";
	$language = "-en";		//TODO If you can figure out the language from the filename (not consistent among SNOMED distributions)
							//then change this variable, e.g. to "-es"
	//import the core tables. This should also work for US and Spanish Editions (Extensions are no longer distributed by these NRC's)
	import_snomed_rf2_file($dir_terminology, "sct2_Concept_Snapshot", $file_extension, "sct_rf2_concepts", $load_script, $array_replace);
	import_snomed_rf2_file($dir_terminology, "sct2_Description_Snapshot".$language, $file_extension, "sct_rf2_descriptions", $load_script, $array_replace);
	import_snomed_rf2_file($dir_terminology, "sct2_Relationship_Snapshot", $file_extension, "sct_rf2_relationships", $load_script, $array_replace);
	import_snomed_rf2_file($dir_refset, "Content/der2_Refset_SimpleSnapshot", $file_extension, "sct_rf2_simplerefsets", $load_script, $array_replace);
	import_snomed_rf2_file($dir_refset, "Language/der2_cRefset_LanguageSnapshot".$language, $file_extension, "sct_rf2_languagerefsets", $load_script, $array_replace);
	import_snomed_rf2_file($dir_refset, "Map/der2_iisssccRefset_ExtendedMapSnapshot", $file_extension, "sct_rf2_extendedmaprefsets", $load_script, $array_replace);
	
	$dir_extension = $dir_snomed."NL_Extension/";	//if the Dutch Extension is included, import that as well
	if (is_dir($dir_extension))			//Extension is distributed within Dutch Edition alongside the INT-version. Just add the extra lines to each table
	{
		$language_nl = "";				//Dutch extension contains English and Dutch descriptions, therefore does not include language in filename
		$dir_terminology_extension = $dir_extension."Snapshot/Terminology/";
		$dir_refset_extension = $dir_extension."Snapshot/Refset/";
		$files_array = scandir($dir_terminology_extension);
		$file_extension_nl = $file_extension;
		
		foreach ($files_array as $file)	//Dutch extension is generated 1-2 months later than core (of necessity), so find new file extension
		{
			if (preg_match("/sct2_Concept_Snapshot(_[A-Z]+_[0-9]{8}).txt/", $file, $matches))
			{
				$file_extension_nl = $matches[1];
				break;
			}
		}
		
		import_snomed_rf2_file($dir_terminology_extension, "sct2_Concept_Snapshot", $file_extension_nl, "sct_rf2_concepts", $load_script, $array_replace);
		import_snomed_rf2_file($dir_terminology_extension, "sct2_Description_Snapshot".$language_nl, $file_extension_nl, "sct_rf2_descriptions", $load_script, $array_replace);
		import_snomed_rf2_file($dir_terminology_extension, "sct2_Relationship_Snapshot", $file_extension_nl, "sct_rf2_relationships", $load_script, $array_replace);
		import_snomed_rf2_file($dir_refset_extension, "Content/der2_Refset_SimpleSnapshot", $file_extension_nl, "sct_rf2_simplerefsets", $load_script, $array_replace);
		import_snomed_rf2_file($dir_refset_extension, "Language/der2_cRefset_LanguageSnapshot".$language_nl, $file_extension_nl, "sct_rf2_languagerefsets", $load_script, $array_replace);
		import_snomed_rf2_file($dir_refset_extension, "Map/der2_iisssccRefset_ExtendedMapSnapshot", $file_extension_nl, "sct_rf2_extendedmaprefsets", $load_script, $array_replace);
	}

	return true; 
}

function import_snomed_rf2_file($dir, $filename, $fileextension, $table, $load_script, $array_replace)
{
	$file = $dir.$filename.$fileextension.".txt";
	if (file_exists($file))
	{
		$sql = str_replace($array_replace, array($file, $table), $load_script);
		sqlStatement($sql);
	}
}

// Function to import ICD tables $type differentiates ICD 9, 10 and eventually 11 (circa 2018 :-) etc.
//
function icd_import($type) {

    // set up paths
    $dir_icd = $GLOBALS['temporary_files_dir']."/".$type."/";
    $dir=str_replace('\\','/',$dir_icd);
    $db_load = '';
    $db_update = '';

    // the incoming array is a metadata array containing keys that substr match to the incoming filename
    // followed by the field name, position and length of each fixed length text record in the incoming
    // flat files. There are separate definitions for ICD 9 and 10 based on the type passed in
    $incoming = array();
    if ($type == 'ICD9') {
        $incoming['SHORT_DX'] = array('#TABLENAME#' => "icd9_dx_code", 
		'#FLD1#' => "dx_code", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "short_desc", '#POS2#' => 7, '#LEN2#' => 60);
        $incoming['SHORT_SG'] = array('#TABLENAME#' => "icd9_sg_code", 
		'#FLD1#' => "sg_code", '#POS1#' => 1, '#LEN1#' => 4, 
		'#FLD2#' => "short_desc", '#POS2#' => 6, '#LEN2#' => 60);
        $incoming['LONG_SG'] = array('#TABLENAME#' => "icd9_sg_long_code", 
		'#FLD1#' => "sg_code", '#POS1#' => 1, '#LEN1#' => 4, 
		'#FLD2#' => "long_desc", '#POS2#' => 6, '#LEN2#' => 300);
        $incoming['LONG_DX'] = array('#TABLENAME#' => "icd9_dx_long_code", 
		'#FLD1#' => "dx_code", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "long_desc", '#POS2#' => 7, '#LEN2#' => 300);
    } else {

        $incoming['icd10pcs_order_'] = array('#TABLENAME#' => "icd10_pcs_order_code", 
		'#FLD1#' => "pcs_code", '#POS1#' => 7, '#LEN1#' => 7,
		'#FLD2#' => "valid_for_coding", '#POS2#' => 15, '#LEN2#' => 1,
		'#FLD3#' => "short_desc", '#POS3#' => 17, '#LEN3#' => 60,
		'#FLD4#' => "long_desc", '#POS4#' => 78, '#LEN4#' => 300);
        $incoming['icd10cm_order_'] = array('#TABLENAME#' => "icd10_dx_order_code",
		'#FLD1#' => "dx_code", '#POS1#' =>7, '#LEN1#' => 7,
		'#FLD2#' => "valid_for_coding", '#POS2#' => 15, '#LEN2#' => 1,
		'#FLD3#' => "short_desc", '#POS3#' => 17, '#LEN3#' => 60,
		'#FLD4#' => "long_desc", '#POS4#' => 78, '#LEN4#' => 300);
        $incoming['reimb_map_pr_'] = array('#TABLENAME#' => "icd10_reimbr_pcs_9_10",
		'#FLD1#' => "code", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "code_cnt", '#POS2#' => 9, '#LEN2#' => 1,
		'#FLD3#' => "ICD9_01", '#POS3#' => 11, '#LEN3#' => 5,
		'#FLD4#' => "ICD9_02", '#POS4#' => 17, '#LEN4#' => 5,
		'#FLD5#' => "ICD9_03", '#POS5#' => 23, '#LEN5#' => 5,
		'#FLD6#' => "ICD9_04", '#POS6#' => 29, '#LEN6#' => 5,
		'#FLD7#' => "ICD9_05", '#POS7#' => 35, '#LEN7#' => 5,
		'#FLD8#' => "ICD9_06", '#POS8#' => 41, '#LEN8#' => 5);
        $incoming['reimb_map_dx_'] = array('#TABLENAME#' => "icd10_reimbr_dx_9_10",
		'#FLD1#' => "code", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "code_cnt", '#POS2#' => 9, '#LEN2#' => 1,
		'#FLD3#' => "ICD9_01", '#POS3#' => 11, '#LEN3#' => 5,
		'#FLD4#' => "ICD9_02", '#POS4#' => 17, '#LEN4#' => 5,
		'#FLD5#' => "ICD9_03", '#POS5#' => 23, '#LEN5#' => 5,
		'#FLD6#' => "ICD9_04", '#POS6#' => 29, '#LEN6#' => 5,
		'#FLD7#' => "ICD9_05", '#POS7#' => 35, '#LEN7#' => 5,
		'#FLD8#' => "ICD9_06", '#POS8#' => 41, '#LEN8#' => 5);
        $incoming['I10gem'] = array('#TABLENAME#' => "icd10_gem_dx_10_9",
		'#FLD1#' => "dx_icd10_source", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "dx_icd9_target", '#POS2#' => 9, '#LEN2#' => 5,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
        $incoming['I9gem'] = array('#TABLENAME#' => "icd10_gem_dx_9_10",
		'#FLD1#' => "dx_icd9_source", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "dx_icd10_target", '#POS2#' => 7, '#LEN2#' => 7,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
        $incoming['gem_pcsi9'] = array('#TABLENAME#' => "icd10_gem_pcs_10_9",
		'#FLD1#' => "pcs_icd10_source", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "pcs_icd9_target", '#POS2#' => 9, '#LEN2#' => 5,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
        $incoming['gem_i9pcs'] = array('#TABLENAME#' => "icd10_gem_pcs_9_10",
		'#FLD1#' => "pcs_icd9_source", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "pcs_icd10_target", '#POS2#' => 7, '#LEN2#' => 7,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
    }

    // set up the start of the load script to be appended from the incoming array defined above where incoming 
    // file matches 
    $db_load = "LOAD DATA LOCAL INFILE '#INFILE#' INTO TABLE #TABLENAME# FIELDS TERMINATED BY '\0' (@var) SET revision = 0, ";
    $col_template = "#FLD# = trim(Substring(@var, #POS#, #LEN#))";

    // load all data and set active revision
    if( is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
	    // bypass unwanted entries
	    if (!stripos($filename, ".txt") || stripos($filename,"diff")) {
		continue;
	    }

	    // reset the sql load command and susbtitute the filename
	    $run_sql = $db_load;
	    $run_sql = str_replace("#INFILE#", $dir . $filename, $run_sql);
	    $keys = array_keys($incoming);
    	    while ($this_key = array_pop($keys)) {
		if (stripos($filename, $this_key) !== false) {

		    // now substitute the tablename 
	    	    $run_sql = str_replace("#TABLENAME#", $incoming[$this_key]['#TABLENAME#'], $run_sql);
	
		    // the range defines the maximum number of fields contained 
		    // in any of the incoming files 
    		    foreach(range(1, 8) as $field) {
			$fld = "#FLD" . $field . "#";
			$nxtfld = "#FLD" . ($field+1) . "#";
			$pos = "#POS" . $field . "#";
			$len = "#LEN" . $field . "#";

			// concat this fields template in the sql string
			$run_sql .= $col_template;
		        $run_sql = str_replace("#FLD#", $incoming[$this_key][$fld], $run_sql);
		        $run_sql = str_replace("#POS#", $incoming[$this_key][$pos], $run_sql);
		        $run_sql = str_replace("#LEN#", $incoming[$this_key][$len], $run_sql);
			// at the end of this table's field list
			if (!array_key_exists($nxtfld, $incoming[$this_key])) {
			    break;
			}
			$run_sql .= ",";
    		    }
            	    sqlStatement($run_sql);

		    // now update the revision for this load
                    $res = sqlStatement("SELECT max(revision) rev FROM " . $incoming[$this_key]['#TABLENAME#']);
                    $row = sqlFetchArray($res);
                    $next_rev = $row['rev'] + 1;
		    $run_sql = "UPDATE " . $incoming[$this_key]['#TABLENAME#'] . " SET active = 0"; 
                    sqlQuery($run_sql);
		    $run_sql = "UPDATE " . $incoming[$this_key]['#TABLENAME#'] . " SET active = 1, revision = ? WHERE revision = 0";
                    sqlQuery($run_sql, array($next_rev) );
		    break;
		}
	    }
        }
        closedir($handle);
    }
    else {
        echo htmlspecialchars( xl('ERROR: No ICD import directory.'), ENT_NOQUOTES)."<br>";
	return;
    }

    // now update the tables where necessary
    if ($type == 'ICD9') {
        sqlStatement("update `icd9_dx_code` SET formatted_dx_code = dx_code");
        sqlStatement("update `icd9_dx_code` SET formatted_dx_code = concat(concat(left(dx_code, 3), '.'), substr(dx_code, 4)) WHERE dx_code RLIKE '^[V0-9]{1}.*' AND LENGTH(dx_code) > 3");
        sqlStatement("update `icd9_dx_code` SET formatted_dx_code = concat(concat(left(dx_code, 4), '.'), substr(dx_code, 5)) WHERE dx_code RLIKE '^[E]{1}.*' AND LENGTH(dx_code) > 4");
        sqlStatement("update `icd9_sg_code` SET formatted_sg_code = concat(concat(left(sg_code, 2), '.'), substr(sg_code, 3))");
        sqlStatement("update `icd9_dx_code` A, `icd9_dx_long_code` B set A.long_desc = B.long_desc where A.dx_code = B.dx_code and A.active = 1 and A.long_desc is NULL");
        sqlStatement("update `icd9_sg_code` A, `icd9_sg_long_code` B set A.long_desc = B.long_desc where A.sg_code = B.sg_code and A.active = 1 and A.long_desc is NULL");
    }
    else { // ICD 10
        sqlStatement("update `icd10_dx_order_code` SET formatted_dx_code = dx_code");
        sqlStatement("update `icd10_dx_order_code` SET formatted_dx_code = concat(concat(left(dx_code, 3), '.'), substr(dx_code, 4)) WHERE LENGTH(dx_code) > 3");
    }

    return true;
}

// Function to clean up temp files
// $type (RXNORM etc.)
function temp_dir_cleanup($type) {
    if(is_dir($GLOBALS['temporary_files_dir']."/".$type)) {
        rmdir_recursive($GLOBALS['temporary_files_dir']."/".$type);
    }
}

// Function to update version tracker table if successful
// $type (RXNORM etc.)
function update_tracker_table($type,$revision,$version,$file_checksum) {
    if ($type == 'RXNORM') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'RXNORM',?,?,?)", array($revision, $version, $file_checksum) );
        return true;
    }
    else if ($type == 'SNOMED') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'SNOMED',?,?,?)", array($revision, $version, $file_checksum) );
        return true;
    }
    else if ($type == 'SNOMED_RF2') {
    	sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'SNOMED_RF2',?,?,?)", array($revision, $version, $file_checksum) );
    	return true;
    }
    else if ($type == 'ICD9') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'ICD9',?,?,?)", array($revision, $version, $file_checksum) );
        return true;
    }
    else { // $type == 'ICD10') 
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`, `file_checksum`) VALUES (NOW(),'ICD10',?,?,?)", array($revision, $version, $file_checksum) );
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
	    continue;
        }
        unlink($file);
    }
    rmdir($dir);
}

// function to cleanup temp, copy and unarchive the zip file
function handle_zip_file($mode, $file) {
        // 1. copy the file to temp directory
        if (!temp_copy($file,$mode)) {
            echo htmlspecialchars( xl('ERROR: Unable to copy the file.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }

        // 2. unarchive the file
        if (!temp_unarchive($file,$mode)) {
            echo htmlspecialchars( xl('ERROR: Unable to extract the file.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }
}

?>
