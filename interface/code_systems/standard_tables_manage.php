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
    echo htmlspecialchars( xl('Not Authorized'), ENT_NOQUOTES);
    exit;
}

// Collect parameters (ensure mode is either rxnorm or snomed)
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
if ($mode != 'rxnorm' && $mode != 'snomed') {
    exit;
}
$process = isset($_GET['process']) ? $_GET['process'] : '0';

// Set path constant
if ($mode == 'rxnorm') {
    $mainPATH = $GLOBALS['fileroot']."/contrib/rxnorm";
}
else { // $mode == 'snomed'
    $mainPATH = $GLOBALS['fileroot']."/contrib/snomed";
}

// Get current revision (if installed)
$installed_flag = false;
$current_revision = '';
if ($mode == 'rxnorm') {
    $sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision` FROM `standardized_tables_track` WHERE `name` = 'RXNORM' ORDER BY `revision_date` DESC");
}
else { // $mode == 'snomed'
    $sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision` FROM `standardized_tables_track` WHERE `name` = 'SNOMED' ORDER BY `revision_date` DESC");
}
if (!empty($sqlReturn)) {
    $installed_flag = true;
    $current_revision = $sqlReturn['revision'];
}

// See if a database file exist (collect revision and see if upgrade is an option)
$pending_new = false;
$pending_upgrade = false;
$file_revision_path = ''; //Holds the database file
$file_revision_date = ''; //Holds the database file revision date
$revisions = array();
$files_array = array();
if (is_dir($mainPATH)) {
    $files_array = scandir($mainPATH);
}
foreach ($files_array as $file) {
    $file = $mainPATH."/".$file;
    if (is_file($file)) {
        if ($mode == 'rxnorm') {
            if (preg_match("/RxNorm_full_([0-9]{8}).zip/",$file,$matches)) {
                $temp_date = array(substr($matches[1],4)."-".substr($matches[1],0,2)."-".substr($matches[1],2,-4)=>$mainPATH."/".$matches[0]);
                $revisions = array_merge($revisions,$temp_date);
            }
        }
        else { // $mode == 'snomed'
            if (preg_match("/SnomedCT_INT_([0-9]{8}).zip/",$file,$matches)) {
                $temp_date = array(substr($matches[1],0,4)."-".substr($matches[1],4,-2)."-".substr($matches[1],6)=>$mainPATH."/".$matches[0]);
                $revisions = array_merge($revisions,$temp_date);
            }
        }
    }
}
if (!empty($revisions)) {
    //sort dates and store the most recent dated file
    krsort($revisions);
    reset($revisions);
    $file_revision_path = $revisions[key($revisions)];
    reset($revisions);
    $file_revision_date = key($revisions);
    
    if ( !($installed_flag) && !empty($file_revision_date) ) {
        $pending_new = true;
    }
    else if (strtotime($file_revision_date) > strtotime($current_revision)) {
        $pending_upgrade = true;
    }
    else {
        // do nothing
    }
}

// Use the above booleans ($pending_new=>new,$pending_upgrade=>upgrade,$installed_flag=>installed vs. not installed)
//   to figure out what to do in below script.

?>
<html>
<head>
<?php html_header_show();?>
<?php if ($mode == 'rxnorm') { ?>
    <title><?php echo htmlspecialchars( xl('RxNorm'), ENT_NOQUOTES); ?></title>
<?php } else { //$mode == 'snomed' ?>
    <title><?php echo htmlspecialchars( xl('SNOMED'), ENT_NOQUOTES); ?></title>
<?php } ?>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<script type="text/javascript" src="../../library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>

<script type="text/javascript">
function loading_show() {
    $('#loading').show();
}
</script>

</head>
<body class="body_top">

<?php if ($mode == 'rxnorm') { ?>
    <span class="title"><?php echo htmlspecialchars( xl('RxNorm Database'), ENT_NOQUOTES); ?></span><br><br>
<?php } else { //$mode == 'snomed' ?>
    <span class="title"><?php echo htmlspecialchars( xl('SNOMED Database'), ENT_NOQUOTES); ?></span><br><br>
<?php } ?>

<?php if ($pending_new || $pending_upgrade) { ?>
    <?php
    if ($process != 1) {
        if ($pending_new) {
            echo htmlspecialchars( xl('Database is not installed.'), ENT_NOQUOTES)."<br>";
            echo htmlspecialchars( xl('Click Install button to install database release from the following date').": ", ENT_NOQUOTES)."<b>".htmlspecialchars($file_revision_date, ENT_NOQUOTES)."</b><br>";
            echo "(".htmlspecialchars( xl('Note it will take 5-10 minutes to fully process after you click Install'), ENT_NOQUOTES).")<br><br>";
            echo "<div id='loading' style='margin:10px;display:none;'><img src='../pic/ajax-loader.gif'/></div>";
            echo "<a href='standard_tables_manage.php?process=1&mode=".$mode."' class='css_button' onclick='loading_show();top.restoreSession();'><span>".htmlspecialchars( xl('Install'), ENT_NOQUOTES)."</span></a><br><br>";
        }
        else { //$pending_upgrade
            echo htmlspecialchars( xl('The following database release is currently installed').": ",ENT_NOQUOTES)."<b>".htmlspecialchars($current_revision,ENT_NOQUOTES)."</b><br>";
            echo htmlspecialchars( xl('Click Upgrade button to upgrade database release from the following date').": ", ENT_NOQUOTES)."<b>".htmlspecialchars($file_revision_date, ENT_NOQUOTES)."</b><br>";
            echo "(".htmlspecialchars( xl('Note it will take 5-10 minutes to fully process after you click Upgrade'), ENT_NOQUOTES).")<br><br>";
            echo "<div id='loading' style='margin:10px;display:none;'><img src='../pic/ajax-loader.gif'/></div>";
            echo "<a href='standard_tables_manage.php?process=1&mode=".$mode."' class='css_button' onclick='loading_show();top.restoreSession();'><span>".htmlspecialchars( xl('Upgrade'), ENT_NOQUOTES)."</span></a><br><br>";
        }
    }
    else {
        // install/upgrade the rxnorm database

        // Clean up temp dir before start
        temp_dir_cleanup($mode);

        // 1. copy the file to temp directory
        echo htmlspecialchars( xl('Copying the database file. This will take some time...'), ENT_NOQUOTES)."<br>";
        if (!temp_copy($file_revision_path,$mode)) {
            echo htmlspecialchars( xl('ERROR: Unable to copy the file.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }

        // 2. unarchive the file
        echo htmlspecialchars( xl('Extracting the file. This will take some time...'), ENT_NOQUOTES)."<br>";
        if (!temp_unarchive($file_revision_path,$mode)) {
            echo htmlspecialchars( xl('ERROR: Unable to extract the file.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }

        // 3. load the database
        echo htmlspecialchars( xl('Loading the files into the database. This will take some time...'), ENT_NOQUOTES)."<br>";
        if ($mode == 'rxnorm') {
            if (!rxnorm_import(IS_WINDOWS)) {
                echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
                temp_dir_cleanup($mode);
                exit;
            }
        }
        else { //$mode == 'snomed'
            if (!snomed_import()) {
                echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
                temp_dir_cleanup($mode);
                exit;
            }
        }

        // 4. set the revision version in the database
        echo htmlspecialchars( xl('Setting the version number in the database...'), ENT_NOQUOTES)."<br>";
        if (!update_tracker_table($mode,$file_revision_date)) {
            echo htmlspecialchars( xl('ERROR: Unable to set the version number.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }

        // done, so clean up the temp directory
        if ($pending_new) {
            echo "<b>".htmlspecialchars( xl('Successfully installed the database.'), ENT_NOQUOTES)."</b><br>";
        }
        else { //$pending_upgrade
            echo "<b>".htmlspecialchars( xl('Successfully upgraded the database.'), ENT_NOQUOTES)."</b><br>";
        }
        temp_dir_cleanup($mode);
    }
    ?>
<?php } else if ($installed_flag) { ?>
    <?php echo htmlspecialchars( xl('The following database release is currently installed').": ",ENT_NOQUOTES)."<b>".htmlspecialchars($current_revision,ENT_NOQUOTES)."</b><br>"; ?>
    <?php echo htmlspecialchars( xl('If you want to upgrade the database, then place the database zip file in the following directory').": contrib/".$mode, ENT_NOQUOTES); ?><br><br>
<?php } else { // !$installed_flag ?>
    <?php echo htmlspecialchars( xl('Database is not installed.'), ENT_NOQUOTES); ?><br>
    <?php echo htmlspecialchars( xl('Place the database zip file in the following directory if want the option to install').": contrib/".$mode, ENT_NOQUOTES); ?><br><br>
<?php }?>
</body>
</html>
