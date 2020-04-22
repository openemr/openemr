<?php

/**
 * This file implements the database load processing when loading external
 * database files into openEMR
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author    Rohit Kumar <pandit.rohit@netsity.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2011 Phyaura, LLC <info@phyaura.com>
 * @copyright Copyright (c) 2012 Patient Healthcare Analytics, Inc.
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");
require_once("$srcdir/standard_tables_capture.inc");

use OpenEMR\Common\Acl\AclMain;

// Ensure script doesn't time out
set_time_limit(0);

// Control access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('Not Authorized');
    exit;
}

$db = isset($_GET['db']) ? $_GET['db'] : '0';
$version = isset($_GET['version']) ? $_GET['version'] : '0';
$rf = isset($_GET['rf']) ? $_GET['rf'] : '0';
$file_revision_date = isset($_GET['file_revision_date']) ? $_GET['file_revision_date'] : '0';
$file_checksum = isset($_GET['file_checksum']) ? $_GET['file_checksum'] : '0';
$newInstall =   isset($_GET['newInstall']) ? $_GET['newInstall'] : '0';
$mainPATH = $GLOBALS['fileroot'] . "/contrib/" . strtolower($db);

$files_array = scandir($mainPATH);
array_shift($files_array); // get rid of "."
array_shift($files_array); // get rid of ".."

foreach ($files_array as $file) {
    $this_file = $mainPATH . "/" . $file;
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
        echo htmlspecialchars(xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES) . "<br />";
        temp_dir_cleanup($db);
        exit;
    }
} elseif ($db == 'SNOMED') {
    if ($rf == "rf2") {
        if (!snomedRF2_import()) {
            echo htmlspecialchars(xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES) . "<br />";
            temp_dir_cleanup($db);
            exit;
        } else {
            drop_old_sct();
            chg_ct_external_torf2();
        }
    } elseif ($version == "US Extension") {
        if (!snomed_import(true)) {
            echo htmlspecialchars(xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES) . "<br />";
            temp_dir_cleanup($db);
            exit;
        } else {
            drop_old_sct2();
            chg_ct_external_torf1();
        }
    } else {
        if (!snomed_import(false)) {
            echo htmlspecialchars(xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES) . "<br />";
            temp_dir_cleanup($db);
            exit;
        } else {
            drop_old_sct2();
            chg_ct_external_torf1();
        }
    }
} elseif ($db == 'CQM_VALUESET') {
    if (!valueset_import($db)) {
        echo htmlspecialchars(xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES) . "<br />";
        temp_dir_cleanup($db);
        exit;
    }
} else { //$db == 'ICD'
    if (!icd_import($db)) {
        echo htmlspecialchars(xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES) . "<br />";
        temp_dir_cleanup($db);
        exit;
    }
}

// set the revision version in the database
if (!update_tracker_table($db, $file_revision_date, $version, $file_checksum)) {
    echo htmlspecialchars(xl('ERROR: Unable to set the version number.'), ENT_NOQUOTES) . "<br />";
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
