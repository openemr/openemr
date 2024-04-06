<?php

/**
 * This file implements the listing of the staged database files
 * downloaded from an external source (e.g. CMS, NIH, etc.)
 *
 * The logic will also render the appropriate action button which
 * can be one of the following:
 *      INSTALL - this is rendered when the external database has
 *                not been installed in this openEMR instance
 *      UPGRADE - this is rendered when the external database has
 *                been installed and the staged files are more recent
 *                than the instance installed
 * When the staged files are the same as the instance installed then
 * an appropriate message is rendered
 *
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

use OpenEMR\Common\Acl\AclMain;

// Ensure script doesn't time out and has enough memory
set_time_limit(0);
ini_set('memory_limit', '150M');

// Control access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('Not Authorized');
    exit;
}

$db = isset($_GET['db']) ? $_GET['db'] : '0';
$mainPATH = $GLOBALS['fileroot'] . "/contrib/" . strtolower($db);
$file_checksum = "";

//
// Get current revision (if installed)
//
// this retreives the most recent revision_date for a table "name"
//
// for SNOMED and RXNORM you get the date from the filename as those naming
// conventions allow for that derivation
// for ICD versions the revision date is equal to the load_release_date attribute
// value from the supported_external_dataloads table
//
$installed_flag = 0;
$supported_file = 0;
$current_revision = '';
$current_version = '';
$current_name = '';
$current_checksum = '';

// Ordering by the imported_date with tiebreaker being the revision_date
$sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision_date`, `revision_version`, `name`, `file_checksum` FROM `standardized_tables_track` WHERE upper(`name`) = ? ORDER BY `imported_date` DESC, `revision_date` DESC", array($db));
if (!empty($sqlReturn)) {
    $installed_flag = 1;
    $current_name = $sqlReturn['name'];
    $current_revision = $sqlReturn['revision_date'];
    $current_version = $sqlReturn['revision_version'];
    $current_checksum = $sqlReturn['file_checksum'];
}

// See if a database file exist (collect revision and see if upgrade is an option)
$file_revision_path = ''; //Holds the database file
$file_revision_date = ''; //Holds the database file revision date
$version = '';
$revisions = array();
$files_array = array();
if (is_dir($mainPATH)) {
    $files_array = scandir($mainPATH);

    array_shift($files_array); // get rid of "."
    array_shift($files_array); // get rid of ".."

    //
    // this foreach loop only encounters 1 file for SNOMED, RXNORM and ICD9 but will cycle through all the
    // the release files for ICD10
    //
    $i = -1;
    foreach ($files_array as $file) {
        $i++;
        $file = $mainPATH . "/" . $file;
        if (is_file($file)) {
            if (!strpos($file, ".zip") !== false) {
                unset($files_array[$i]);
                continue;
            }

            $supported_file = 0;
            if ($db == 'RXNORM') {
                if (preg_match("/RxNorm_full_([0-9]{8}).zip/", $file, $matches)) {
            // Hard code the version RxNorm feed to be Standard
                    //  (if add different RxNorm types/versions/lanuages, then can use this)
            //
                    $version = "Standard";
                    $date_release = substr($matches[1], 4) . "-" . substr($matches[1], 0, 2) . "-" . substr($matches[1], 2, -4);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                }
            } elseif ($db == 'SNOMED') {
                if (preg_match("/SnomedCT_INT_([0-9]{8}).zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be International:English
                    //  (if add different SNOMED types/versions/languages, then can use this)
                    //
                    $version = "International:English";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_Release_INT_([0-9]{8}).zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be International:English
                    //  (if add different SNOMED types/versions/languages, then can use this)
                    //
                    $version = "International:English";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_RF1Release_INT_([0-9]{8}).zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be International:English
                    //  (if add different SNOMED types/versions/languages, then can use this)
                    //
                    $version = "International:English";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_Release_US[0-9]*_([0-9]{8}).zip/", $file, $matches)) {
                    // This is the SNOMED US extension pack which can only be installed on top
                    // of a International SNOMED version.
                    // Hard code this version SNOMED feed to be US Extension
                    // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                    // a complete SNOMED pacakge.
                    //
                    $version = "US Extension";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/sct1_National_US_([0-9]{8}).zip/", $file, $matches)) {
                    // This is the SNOMED US extension pack which can only be installed on top
                    // of a International SNOMED version.
                    // Hard code this version SNOMED feed to be US Extension
                    //
                    $version = "US Extension";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_RF1Release_US[0-9]*_([0-9]{8}).zip/", $file, $matches)) {
                    // This is the Complete SNOMED US extension package
                    // Hard code this version SNOMED feed to be Complete US Extension
                    //
                    $version = "Complete US Extension";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_Release-es_INT_([0-9]{8}).zip/", $file, $matches)) {
                    // Hard code this SNOMED version feed to be International:Spanish
                    //
                    $version = "International:Spanish";
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_InternationalRF2_PRODUCTION_([0-9]{8})[0-9a-zA-Z]{8}.zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be International:English
                    //
                    $version = "International:English";
                    $rf2 = true;
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_ManagedServiceIE_PRODUCTION_IE1000220_([0-9]{8})[0-9a-zA-Z]{8}.zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be International:English Irish version
                    //
                    $version = "International:English";
                    $rf2 = true;
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_USEditionRF2_PRODUCTION_([0-9]{8})[0-9a-zA-Z]{8}.zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be Complete US Extension
                    //
                    $version = "Complete US Extension";
                    $rf2 = true;
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } elseif (preg_match("/SnomedCT_SpanishRelease-es_PRODUCTION_([0-9]{8})[0-9a-zA-Z]{8}.zip/", $file, $matches)) {
                    // Hard code the version SNOMED feed to be International:Spanish
                    //
                    $version = "International:Spanish";
                    $rf2 = true;
                    $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                } else {
                    // nothing
                }
            } elseif (is_numeric(strpos($db, "ICD"))) {
                $qry_str = "SELECT `load_checksum`,`load_source`,`load_release_date` FROM `supported_external_dataloads` WHERE `load_type` = ? and `load_filename` = ? and `load_checksum` = ? ORDER BY `load_release_date` DESC";

                // this query determines whether you can load the data into openEMR. you must have the correct
                // filename and checksum for each file that are part of the same release.
                //
                // IMPORTANT: Releases that contain mutliple zip file (e.g. ICD10) are grouped together based
                // on the load_release_date attribute value specified in the supported_external_dataloads table
                //
                // Just in case same filename is released on different release dates, best to actually include the md5sum in the query itself.
                // (and if a hit, then it is a pass)
                // (even if two duplicate files that are in different releases, will still work since chooses most recent)
                $file_checksum = md5(file_get_contents($file));
                $sqlReturn = sqlQuery($qry_str, array($db, basename($file), $file_checksum));

                if (!empty($sqlReturn)) {
                    $version = $sqlReturn['load_source'];
                    $date_release = $sqlReturn['load_release_date'];
                    $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $file, 'checksum' => $file_checksum);
                    array_push($revisions, $temp_date);
                    $supported_file = 1;
                }
            } elseif ($db == 'CQM_VALUESET') {
                if (preg_match("/e[p,c]_.*_cms_([0-9]{8}).xml.zip/", $file, $matches)) {
                     $version = "Standard";
                         $date_release = substr($matches[1], 0, 4) . "-" . substr($matches[1], 4, -2) . "-" . substr($matches[1], 6);
                         $temp_date = array('date' => $date_release, 'version' => $version, 'path' => $mainPATH . "/" . $matches[0]);
                         array_push($revisions, $temp_date);
                         $supported_file = 1;
                }
            }

            if ($supported_file === 1) {
                ?><div class="stg"><?php echo text(basename($file)); ?></div>
                <?php
            } else {
                ?>
                <div class="error_msg"><?php echo xlt("UNSUPPORTED database load file"); ?>: <br /><?php echo text(basename($file)) ?><span class="msg" id="<?php echo attr($db); ?>_unsupportedmsg">!</span></div>
                <?php
            }
        }
    }
} else {
    ?>
    <div class="error_msg"><?php echo xlt("The installation directory needs to be created."); ?><span class="msg" id="<?php echo attr($db); ?>_dirmsg">!</span></div>
    <?php
}

if (count($files_array) === 0) {
    ?>
   <div class="error_msg"><?php echo xlt("No files staged for installation"); ?><span class="msg" id="<?php echo attr($db); ?>_msg">!</span></div>
   <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
    <?php
}


// only render messages and action buttons when supported files exists
// otherwise we have an error message already displayed to the user
if ($supported_file === 1) {
    $success_flag = 1;

    // Only allow 1 staged revision for the SNOMED and RXNORM imports
    if (($db == "SNOMED" || $db == "RXNORM") && (count($revisions) > 1)) {
        ?>
        <div class="error_msg"><?php echo xlt("The number of staged files is incorrect. Only place the file that you wish to install/upgrade to."); ?></div>
        <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
        <?php
        $success_flag = 0;
    }

    // Ensure all release dates and revisions are the same for multiple file imports
    // and collect the date and revision. Also collect a checksum and path.
    $file_revision_date = '';
    $file_revision = '';
    $file_checksum = '';
    $file_revision_path = '';
    foreach ($revisions as $value) {
        // date check
        $temp_file_revision_date = $value['date'];
        if (empty($file_revision_date)) {
            $file_revision_date = $temp_file_revision_date;
        } else {
            if (($file_revision_date != $temp_file_revision_date) && ($success_flag === 1)) {
                ?>
                <div class="error_msg"><?php echo xlt("The staged files release dates are not all from the same release."); ?></div>
          <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
                $success_flag = 0;
            }
        }

        // revision check
        $temp_file_revision = $value['version'];
        if (empty($file_revision)) {
            $file_revision = $temp_file_revision;
        } else {
            if (($file_revision != $temp_file_revision) && ($success_flag === 1)) {
                ?>
                <div class="error_msg"><?php echo xlt("The staged files revisions are not all from the same release."); ?></div>
          <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
                $success_flag = 0;
            }
        }

        // collect checksum (if a multiple file import, then can use any one)
        $file_checksum = $value['checksum'] ?? '';
        // collect path (if a multiple file import, then can use any one)
        $file_revision_path = $value['path'];
    }

    // Determine and enforce only a certain number of files to be staged
    if ($success_flag === 1) {
        $number_files = 1;
        $sql_query_ret = sqlStatement("SELECT * FROM `supported_external_dataloads` WHERE `load_type` = ? AND `load_source` = ? AND `load_release_date` = ?", array($db,$file_revision,$file_revision_date));
        $number_files_temp = sqlNumRows($sql_query_ret);
        if ($number_files_temp > 1) {
            // To ensure number_files is set to 1 for imports that are not tracked in the supported_external_dataloads table
            $number_files = $number_files_temp;
        }

        if (count($revisions) != $number_files) {
            ?>
            <div class="error_msg"><?php echo xlt("The number of staged files is incorrect. Only place the files that you wish to install/upgrade to."); ?></div>
            <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
            <?php
            $success_flag = 0;
        }
    }

    // If new version is being offered, then provide install/upgrade options
    if ($success_flag === 1) {
        $action = "";
        if ($installed_flag === 1) {
            if ($current_name == "SNOMED" && $current_version != $file_revision && $file_revision != "US Extension" && $current_version == "US Extension") {
                // The US extension for snomed has been previously installed, so will allow to Replace with installation of any international set.
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
                <div class="stg"><?php echo text(basename($file_revision_path)); ?> <?php echo xlt("is a different version of the following database") . ": " . text($db); ?></div>
                <?php
                $action = xl("REPLACE");
            } elseif ($current_name == "SNOMED" && $current_version != $file_revision && $file_revision != "US Extension") {
                // A different language version of the SNOMED database has been staged, and will offer to Replace database with this staged version.
                ?>
                <div class="stg"><?php echo text(basename($file_revision_path)); ?> <?php echo xlt("is a different language version of the following database") . ": " . text($db); ?></div>
                <?php
                $action = xl("REPLACE");
            } elseif ($current_name == "SNOMED" && $current_version == "US Extension" && $file_revision == "US Extension") {
                // The Staged US Extension SNOMED package has already been installed
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
                <div class="error_msg"><?php echo xlt("The compatible staged US Extension SNOMED package has already been installed."); ?></div>
            <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
            } elseif ($current_name == "SNOMED" && $current_version != "International:English" && $file_revision == "US Extension") {
                // The Staged US Extension SNOMED file is not compatible with non-english snomed sets
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
                <div class="error_msg"><?php echo xlt("The installed International SNOMED version is not compatible with the staged US Extension SNOMED package."); ?></div>
            <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
            } elseif (($current_name == "SNOMED" && $current_version == "International:English" && $file_revision == "US Extension") && ((strtotime($current_revision . " +6 month") < strtotime($file_revision_date)) || (strtotime($current_revision . " -6 month") > strtotime($file_revision_date)))) {
                // The Staged US Extension SNOMED file is not compatible with the current SNOMED International Package (ie. the International package is outdated)
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
                <div class="error_msg"><?php echo xlt("The installed International SNOMED version is out of date and not compatible with the staged US Extension SNOMED file."); ?></div>
            <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
            } elseif ($current_name == "SNOMED" && $current_version == "International:English" && $file_revision == "US Extension") {
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                // Offer to upgrade to the US Extension.
                ?>
                <div class="stg"><?php echo text(basename($file_revision_path)); ?> <?php echo xlt("is an extension of the following database") . ": " . text($db); ?></div>
                <?php
                $action = xl("UPGRADE");
            } elseif ((strtotime($current_revision) == strtotime($file_revision_date))) {
                // Note the exception here when installing US Extension
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
            <div class="error_msg"><?php echo xlt("The installed version and the staged files are the same."); ?></div>
            <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
            } elseif (strtotime($current_revision) > strtotime($file_revision_date)) {
                // Note the exception here when installing US Extension
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
                <div class="error_msg"><?php echo xlt("The installed version is a more recent version than the staged files."); ?></div>
                <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
            } else {
                ?>
                <div class="stg"><?php echo text(basename($file_revision_path)); ?> <?php echo xlt("is a more recent version of the following database") . ": " . text($db); ?></div>
                <?php
                $action = xl("UPGRADE");
            }
        } else {
            if ($db == "SNOMED" && $file_revision == "US Extension") {
                // The old Staged US Extension SNOMED package could not be installed by itself (it was done after the international package is installed).
                // Note the US extension package has been deprecated for some time and was replaced by the Complete US extension package, which is
                // a complete SNOMED pacakge.
                ?>
                <div class="error_msg"><?php echo xlt("The staged US Extension SNOMED package can not be installed until after the International SNOMED package has been installed."); ?></div>
            <div class="stg msg"><?php echo xlt("Follow these instructions for installing or upgrading the following database") . ": " . text($db); ?><span class="msg" id="<?php echo attr($db); ?>_instrmsg">?</span></div>
                <?php
            } elseif (count($files_array) > 0) {
                $action = xl("INSTALL");
            } else {
                //do nothing
            }
        }

        if (strlen($action) > 0) {
            $rf = "rf1";
            if (!empty($rf2)) {
                $rf = "rf2";
            }
            ?>
            <input id="<?php echo attr($db); ?>_install_button" class="btn btn-primary btn-sm" version="<?php echo attr($file_revision); ?>" rf="<?php echo $rf; ?>" file_revision_date="<?php echo attr($file_revision_date); ?>" file_checksum="<?php echo attr($file_checksum); ?>" type="button" value="<?php echo attr($action); ?>"/>
      </div>
            <?php
        }
    }
}
?>
