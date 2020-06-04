<?php

// Copyright (C) 2008-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This may be run after an upgraded OpenEMR has been installed.
// Its purpose is to upgrade the MySQL OpenEMR database as needed
// for the new release.

// Checks if the server's PHP version is compatible with OpenEMR:
require_once(dirname(__FILE__) . "/src/Common/Compatibility/Checker.php");
$response = OpenEMR\Common\Compatibility\Checker::checkPhpVersion();
if ($response !== true) {
    die(htmlspecialchars($response));
}

// Disable PHP timeout.  This will not work in safe mode.
ini_set('max_execution_time', '0');

$ignoreAuth = true; // no login required

require_once('interface/globals.php');
require_once('library/sql_upgrade_fx.php');

use OpenEMR\Services\VersionService;

$versionService = new VersionService();

// Fetching current version because it was updated by the sql_upgrade_fx
// script and this script will further modify it.
$currentVersion = $versionService->fetch();

$desiredVersion = $currentVersion;
$desiredVersion->setDatabase($v_database);
$desiredVersion->setTag($v_tag);
$desiredVersion->setRealPatch($v_realpatch);
$desiredVersion->setPatch($v_patch);
$desiredVersion->setMinor($v_minor);
$desiredVersion->setMajor($v_major);

// Force logging off
$GLOBALS["enable_auditlog"] = 0;

$versions = array();
$sqldir = "$webserver_root/sql";
$dh = opendir($sqldir);
if (! $dh) {
    die("Cannot read $sqldir");
}

while (false !== ($sfname = readdir($dh))) {
    if (substr($sfname, 0, 1) == '.') {
        continue;
    }

    if (preg_match('/^(\d+)_(\d+)_(\d+)-to-\d+_\d+_\d+_upgrade.sql$/', $sfname, $matches)) {
        $version = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
        $versions[$version] = $sfname;
    }
}

closedir($dh);
ksort($versions);
?>
<html>
<head>
<title>OpenEMR Database Upgrade</title>
    <link rel="stylesheet" href="public/assets/bootstrap/dist/css/bootstrap.min.css">
    <script src="public/assets/jquery/dist/jquery.min.js"></script>
    <script src="public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="shortcut icon" href="public/images/favicon.ico" />
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2>OpenEMR Database Upgrade</h2>
            </div>
        </div>
        <div class="jumbotron p-4">
            <form class="form-inline" method='post' action='sql_upgrade.php'>
                <div class="form-group mb-3">
                    <label>Please select the prior release you are converting from:</label>
                    <select class='ml-3 form-control' name='form_old_version'>
                        <?php
                        foreach ($versions as $version => $filename) {
                            echo " <option value='$version'";
                            // Defaulting to most recent version, which is now 5.0.2.
                            if ($version === '5.0.2') {
                                echo " selected";
                            }

                            echo ">$version</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <p>If you are unsure or were using a development version between two
                    releases, then choose the older of possible releases.</p>
                <p class="text-danger">If you are upgrading from a version below 5.0.0 to version 5.0.0 or greater, do note that this upgrade can take anywhere from several minutes to several hours (you will only see a whitescreen until it is complete; do not stop the script before it is complete or you risk corrupting your data).</p>
                <input type='submit' class='btn btn-primary' name='form_submit' value='Upgrade Database' />
            </form>
        </div>
    </div>

    <?php
    if (!empty($_POST['form_submit'])) {
        $form_old_version = $_POST['form_old_version'];

        foreach ($versions as $version => $filename) {
            if (strcmp($version, $form_old_version) < 0) {
                continue;
            }

            upgradeFromSqlFile($filename);
        }

        if (!empty($GLOBALS['ippf_specific'])) {
            // Upgrade custom stuff for IPPF.
            upgradeFromSqlFile('ippf_upgrade.sql');
        }

        if ((!empty($v_realpatch)) && ($v_realpatch != "") && ($v_realpatch > 0)) {
            // This release contains a patch file, so process it.
            upgradeFromSqlFile('patch.sql');
        }

        flush();

        echo "<p class='text-success'>Updating global configuration defaults...</p><br />\n";
        $skipGlobalEvent = true; //use in globals.inc.php script to skip event stuff
        require_once("library/globals.inc.php");
        foreach ($GLOBALS_METADATA as $grpname => $grparr) {
            foreach ($grparr as $fldid => $fldarr) {
                list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                if (is_array($fldtype) || (substr($fldtype, 0, 2) !== 'm_')) {
                    $row = sqlQuery("SELECT count(*) AS count FROM globals WHERE gl_name = '$fldid'");
                    if (empty($row['count'])) {
                        sqlStatement("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
                        "VALUES ( '$fldid', '0', '$flddef' )");
                    }
                }
            }
        }

        echo "<p class='text-success'>Updating Access Controls...</p><br />\n";
        require("acl_upgrade.php");
        echo "<br />\n";

        $canRealPatchBeApplied = $versionService->canRealPatchBeApplied($desiredVersion);
        $line = "Updating version indicators";

        if ($canRealPatchBeApplied) {
            $line = $line . ". Patch was also installed, updating version patch indicator";
        }

        echo "<p class='text-success'>" . $line . "...</p><br />\n";
        $result = $versionService->update($desiredVersion);

        if (!$result) {
            echo "<p class='text-danger'>Version could not be updated</p><br />\n";
            exit();
        }

        echo "<p><p class='text-success'>Database and Access Control upgrade finished.</p></p>\n";
        echo "</body></html>\n";
        exit();
    }

    ?>

    </body>
</html>
