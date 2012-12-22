<?php
/**
 * This file implements the database load processing when loading external
 * database files into openEMR
 *
 * Copyright (C) 2012 Patient Healthcare Analytics, Inc.
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
 * @author  (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author  Rohit Kumar <pandit.rohit@netsity.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/standard_tables_capture.inc");

// Ensure script doesn't time out and has enough memory
set_time_limit(0);
ini_set('memory_limit', '150M');

// Control access
if (!acl_check('admin', 'super')) {
    echo xlt('Not Authorized');
    exit;
}

$db = isset($_GET['db']) ? $_GET['db'] : '0';
$version = isset($_GET['version']) ? $_GET['version'] : '0';
$file_revision_date = isset($_GET['file_revision_date']) ? $_GET['file_revision_date'] : '0';
$file_checksum = isset($_GET['file_checksum']) ? $_GET['file_checksum'] : '0';
$newInstall = 	isset($_GET['newInstall']) ? $_GET['newInstall'] : '0';
$mainPATH = $GLOBALS['fileroot']."/contrib/".strtolower($db);

$files_array = scandir($mainPATH);
array_shift($files_array); // get rid of "."
array_shift($files_array); // get rid of ".."

foreach ($files_array as $file) {
    $this_file = $mainPATH."/".$file;
    if (strpos($file, ".zip") === false) {
        continue;
    }
    if (is_file($this_file)) {
        handle_zip_file($db, $this_file);
    }
}

// load the database
if ($db == 'RXNORM') {
    if (!rxnorm_import(IS_WINDOWS)) {
        echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
        temp_dir_cleanup($db);
        exit;
    }
} else if ( $db == 'SNOMED') {
    if ($version == "US Extension") {
        if (!snomed_import(TRUE)) {
            echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($db);
            exit;
         }
    }
    else { //$version is not "US Extension"
        if (!snomed_import(FALSE)) {
            echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($db);
            exit;
         }
    }
}
else { //$db == 'ICD'
    if (!icd_import($db)) {
        echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
        temp_dir_cleanup($db);
        exit;
    }
}

// set the revision version in the database
if (!update_tracker_table($db, $file_revision_date, $version, $file_checksum)) {
    echo htmlspecialchars( xl('ERROR: Unable to set the version number.'), ENT_NOQUOTES)."<br>";
    temp_dir_cleanup($db);
    exit;
}

// done, so clean up the temp directory
if ($newInstall === "1") {
    ?>
    <div><?php echo xlt("Successfully installed the following database") . ": " . text($db); ?></div>
    <?php
} else { 
    ?>
    <div><?php echo xlt("Successfully upgraded the following database") . ": " . text($db); ?></div>
    <?php
}
temp_dir_cleanup($db);
?>
