<?php

/* sql_upgrade.php
 *
 * This may be run after an upgraded OpenEMR has been installed.
 * It's purpose is to upgrade the MySQL OpenEMR database as needed
 * for the new release.
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2011-2021 Brady Miller <brady.g.miller@gmail.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* @TODO add language selection. needs RTL testing */

$GLOBALS['ongoing_sql_upgrade'] = true;

if (php_sapi_name() === 'cli') {
    // setting for when running as command line script
    // need this for output to be readable when running as command line
    $GLOBALS['force_simple_sql_upgrade'] = true;
}

// Checks if the server's PHP version is compatible with OpenEMR:
require_once(__DIR__ . "/src/Common/Compatibility/Checker.php");
$response = OpenEMR\Common\Compatibility\Checker::checkPhpVersion();
if ($response !== true) {
    die(htmlspecialchars($response));
}

@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();
// Disable PHP timeout.  This will not work in safe mode.
@ini_set('max_execution_time', '0');
if (ob_get_level() === 0) {
    ob_start();
}

$ignoreAuth = true; // no login required
$sessionAllowWrite = true;
$GLOBALS['connection_pooling_off'] = true; // force off database connection pooling

require_once('interface/globals.php');
require_once('library/sql_upgrade_fx.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Header;
use OpenEMR\Services\Utils\SQLUpgradeService;
use OpenEMR\Services\VersionService;

// Force logging off
$GLOBALS["enable_auditlog"] = 0;

$versions = array();
$sqldir = "$webserver_root/sql";
$dh = opendir($sqldir);
if (!$dh) {
    die("Cannot read $sqldir");
}

while (false !== ($sfname = readdir($dh))) {
    if ($sfname[0] === '.') {
        continue;
    }

    if (preg_match('/^(\d+)_(\d+)_(\d+)-to-\d+_\d+_\d+_upgrade.sql$/', $sfname, $matches)) {
        $version = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
        $versions[$version] = $sfname;
    }
}

closedir($dh);
ksort($versions);

$res2 = sqlStatement("select * from lang_languages where lang_description = ?", array($GLOBALS['language_default']));
for ($iter = 0; $row = sqlFetchArray($res2); $iter++) {
    $result2[$iter] = $row;
}

if (count($result2) == 1) {
    $defaultLangID = $result2[0]["lang_id"];
    $defaultLangName = $result2[0]["lang_description"];
    $direction = (int)$result2[0]["lang_is_rtl"] === 1 ? 'rtl' : 'ltr';
} else {
    //default to english if any problems
    $defaultLangID = 1;
    $defaultLangName = "English";
}

$_SESSION['language_choice'] = $defaultLangID;
$_SESSION['language_direction'] = $direction;
CsrfUtils::setupCsrfKey();
session_write_close();

$sqlUpgradeService = new SQLUpgradeService();

header('Content-type: text/html; charset=utf-8');

?>
<!-- @todo Adding DOCTYPE html breaks BS width/height percentages. Why? -->
<html>
<head>
    <title>OpenEMR Database Upgrade</title>
    <?php Header::setupHeader(); ?>
    <link rel="shortcut icon" href="public/images/favicon.ico" />
<script>
    let currentVersion;
    let processProgress = 0;
    let doPoll = 0;
    let serverPaused = 0;
    // recursive long polling where ending is based
    // on global doPoll true or false.
    // added a forcePollOff parameter to avoid polling from staying on indefinitely when updating from patch.sql
    async function serverStatus(version = '', start = 0, forcePollOff = 0) {
        let updateMsg = "";
        let endMsg = "<li class='text-success bg-light'>" +
            <?php echo  xlj("End watching server processes for upgrade version"); ?>  + " " + currentVersion + "</li>";
        if (version) {
            currentVersion = version;
            updateMsg = "<li class='text-light bg-success'>" +
              <?php echo  xlj("Start watching server processes for upgrade version"); ?> + " " + version + "</li>";
        }

        // start polling
        let url = "library/ajax/sql_server_status.php?poll=" + encodeURIComponent(currentVersion);
        let data = new FormData;
        data.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken('sqlupgrade')); ?>);
        data.append("poll", currentVersion);

        let response = await fetch(url, {
            method: 'post',
            body: data
        });

        if (response.status === 502) {
            progressStatus("<li class='text-light bg-danger'> ERROR: Restarting. " + response.statusText) + "</li>";
            // connection timeout, just reconnect
            if (doPoll) {
                await serverStatus();
            }
        } else if (response.status !== 200) {
            // show error
            progressStatus("<li class='text-light bg-danger'> ERROR: " + response.statusText) + "</li>";
            // reconnect in one second
            if (doPoll) {
                await serverStatus();
            }
        } else {
            // await status
            let status = await response.text();
            if (status === 'Internal Server Error') {
                let errorMsg = "<li class='text-light bg-danger'>" +
                    <?php echo xlj("Stopping activity status checks. Internal Server Error"); ?> +"</li>";
                progressStatus(errorMsg);
                // end polling
                doPoll = 0;
            }
            if (status === 'Authentication Error') {
                let errorMsg = "<li class='text-light bg-danger'>" +
                    <?php echo xlj("Stopping status checks. Csrf Error. No harm to upgrade and will continue."); ?> +"</li>";
                progressStatus(errorMsg);
                // end polling
                doPoll = 0;
            }
            if (version) {
                progressStatus(updateMsg);
            }
            if (start === 1) {
                doPoll = 1;
            }
            if (forcePollOff === 1) {
                doPoll = 0;
            }
            // display to screen div
            if (status > "") {
                progressStatus(status);
            }

            // and so forth.
            if (doPoll) {
                await serverStatus();
            } else {
                progressStatus(endMsg);
            }
        }
}
/*
* Focus scrolls to bottom of view
* */
function doScrolls() {
    let serverStatus = document.getElementById("serverStatus");
    let isServerBottom = serverStatus.scrollHeight - serverStatus.clientHeight <= serverStatus.scrollTop + 1;
    let processDetails = document.getElementById("processDetails");
    let isDetailsBottom = processDetails.scrollHeight - processDetails.clientHeight <= processDetails.scrollTop + 1;

    if(!isServerBottom) {
        serverStatus.scrollTop = serverStatus.scrollHeight - serverStatus.clientHeight;
    }if(!isDetailsBottom) {
        processDetails.scrollTop = processDetails.scrollHeight - processDetails.clientHeight;
    }
}

function progressStatus(msg = '') {
    let eventList = document.getElementById('status-message');
    let progressEl = document.getElementById('progress');

    if (currentVersion == "UUID") {
        if (processProgress < 30) {
            processProgress++;
        } else if (processProgress < 40) {
            if (Math.random() > 0.9) {
                processProgress++;
            }
        } else if (processProgress < 50) {
            if (Math.random() > 0.95) {
                processProgress++;
            }
        } else if (processProgress < 60) {
            if (Math.random() > 0.97) {
                processProgress++;
            }
        } else if (processProgress < 70) {
            if (Math.random() > 0.98) {
                processProgress++;
            }
        } else if (processProgress < 80) {
            if (Math.random() > 0.99) {
                processProgress++;
            }
        } else if (processProgress < 96) {
            if (Math.random() > 0.999) {
                processProgress++;
            }
        } else if (processProgress < 99) {
            if (Math.random() > 0.9999) {
                processProgress++;
            }
        }
        progressEl.style.width = processProgress + "%";
        progressEl.innerHTML = processProgress + "%" + " UUID Update";
    } else {
        progressEl.style.width = processProgress + "%";
        progressEl.innerHTML = processProgress + "%" + " v" + currentVersion;
    }
    if (msg) {
        eventList.innerHTML += msg;
        doScrolls();
    }
}

function setWarnings(othis) {
    if (othis.value < '5.0.0') {
        document.querySelector('.version-warning').classList.remove("d-none");
    } else {
        document.querySelector('.version-warning').classList.add("d-none");
    }
}

function pausePoll(othis) {
    if (serverPaused === 0 && doPoll === 1) {
        let alertMsg = "<li class='text-dark bg-warning'>" +
            <?php echo xlj("Paused status checks."); ?> +"</li>";
        progressStatus(alertMsg);
        serverPaused = 1;
        doPoll = 0;
        document.querySelector('.pause-server').classList.remove("btn-success");
        document.querySelector('.pause-server').classList.add("btn-warning");
    } else if (serverPaused === 1) {
        let alertMsg = "<li class='text-dark bg-success'>" +
            <?php echo xlj("Resuming status checks."); ?> +"</li>";
        progressStatus(alertMsg);
        serverPaused = 0;
        doPoll = 1;
        serverStatus('', 1);
        document.querySelector('.pause-server').classList.remove("btn-warning");
        document.querySelector('.pause-server').classList.add("btn-success");
    }
}
</script>
</head>
<body>
<div class="container my-3">
    <div class="row">
        <div class="col-12">
            <h2><?php echo xlt("OpenEMR Database Upgrade"); ?></h2>            
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <p><?php echo xlt("If you are unsure or were using a development version between two releases, then choose the older of possible releases."); ?></p>
        </div>
    </div>
    <div>
        <form class="form-inline" method='post' action='sql_upgrade.php'>
            <div class="form-group mb-1">
                <label><?php echo xlt("Please select the prior release you are converting from"); ?>:</label>
                <select class='mx-3 form-control' name='form_old_version' onchange="setWarnings(this)">
                    <?php
                    $cnt_versions = count($versions);
                    foreach ($versions as $version => $filename) {
                        --$cnt_versions;
                        echo " <option value='$version'";
                        // Defaulting to most recent version or last version in list.
                        if ($version == ($_POST['form_old_version'] ?? '')) {
                            echo " selected";
                        } elseif ($cnt_versions === 0 && !($_POST['form_old_version'] ?? '')) {
                            echo " selected";
                        }
                        echo ">$version</option>\n";
                    }
                    ?>
                </select>
            </div>
            <span class="alert alert-warning text-danger version-warning d-none">
                <?php echo xlt("If you are upgrading from a version below 5.0.0 to version 5.0.0 or greater, do note that this upgrade can take anywhere from several minutes to several hours (you will only see a whitescreen until it is complete; do not stop the script before it is complete or you risk corrupting your data)"); ?>.
            </span>
            <button type='submit' class='btn btn-primary btn-transmit' name='form_submit' value='Upgrade Database'>
                <?php echo xlt("Upgrade Database"); ?>
            </button>
            <div class="btn-group">
            </div>
        </form>
        <!-- server status card -->
        <div class="card card-header">
            <span class="btn-group">
                <a class="btn btn-success pause-server fa fa-pause float-left" onclick="pausePoll(this)" title="<?php echo xla("Click to start or end sql server activity checks."); ?>"></a>
                <a class="btn btn-primary w-100" data-toggle="collapse" href="#serverStatus">
                    <?php echo xlt("Server Status"); ?><i class="fa fa-angle-down rotate-icon float-right"></i>
                </a>
            </span>
        </div>
        <div id="serverStatus" class="card card-body pb-2 h-25 overflow-auto collapse show">
            <div class="bg-light text-dark">
                <ul id="status-message"></ul>
            </div>
        </div>
    </div>
    <!-- collapse place holder for upgrade processing on submit. -->
    <div class="card card-header">
        <a class="btn btn-primary" data-toggle="collapse" href="#processDetails">
            <?php echo xlt("Processing Details"); ?><i class="fas fa-angle-down rotate-icon float-right"></i>
        </a>
        <div id="progress-div" class="bg-secondary float-left">
            <div id="progress" class="mt-1 progress-bar bg-success" style="height:1.125rem;width:0;"></div>
        </div>
    </div>
    <div id='processDetails' class='card card-body pb-2 h-50 overflow-auto collapse show'>
        <div class='bg-light text-dark'>
    <?php if (!empty($_POST['form_submit'])) {
        $form_old_version = $_POST['form_old_version'];

        foreach ($versions as $version => $filename) {
            if (strcmp($version, $form_old_version) < 0) {
                continue;
            }
            // set polling version and start
            $sqlUpgradeService->flush_echo("<script>serverStatus(" . js_escape($version) . ", 1);</script>");
            $sqlUpgradeService->upgradeFromSqlFile($filename);
            // end polling
            sleep(2); // fixes odd bug, where if the sql upgrade goes to fast, then the polling does not stop
            $sqlUpgradeService->flush_echo("<script>processProgress = 100;doPoll = 0;</script>");
        }

        if (!empty($GLOBALS['ippf_specific'])) {
            // Upgrade custom stuff for IPPF.
            $sqlUpgradeService->upgradeFromSqlFile('ippf_upgrade.sql');
        }

        if ((!empty($v_realpatch)) && ($v_realpatch != "") && ($v_realpatch > 0)) {
            // This release contains a patch file, so process it.
            echo "<script>serverStatus('Patch', 0, 1);</script>";
            $sqlUpgradeService->upgradeFromSqlFile('patch.sql');
        }
        flush();

        echo "<br /><p class='text-success'>Updating UUIDs (this could take some time)<br />\n";
        $sqlUpgradeService->flush_echo("<script>processProgress = 10; serverStatus('UUID', 1);</script>");
        $updateUuidLog = UuidRegistry::populateAllMissingUuids();
        if (!empty($updateUuidLog)) {
            echo "Updated UUIDs: " . text($updateUuidLog) . "</p><br />\n";
        } else {
            echo "Did not need to update or add any new UUIDs</p><br />\n";
        }
        sleep(2); // fixes odd bug, where if process goes to fast, then the polling does not stop
        $sqlUpgradeService->flush_echo("<script>processProgress = 100;doPoll = 0;</script>");

        echo "<p class='text-success'>" . xlt("Updating global configuration defaults") . "..." . "</p><br />\n";
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

        echo "<p class='text-success'>" . xlt("Updating Access Controls") . "..." . "</p><br />\n";
        require("acl_upgrade.php");
        echo "<br />\n";

        $versionService = new VersionService();
        $currentVersion = $versionService->fetch();
        $desiredVersion = $currentVersion;
        $desiredVersion['v_database'] = $v_database;
        $desiredVersion['v_tag'] = $v_tag;
        $desiredVersion['v_realpatch'] = $v_realpatch;
        $desiredVersion['v_patch'] = $v_patch;
        $desiredVersion['v_minor'] = $v_minor;
        $desiredVersion['v_major'] = $v_major;

        $canRealPatchBeApplied = $versionService->canRealPatchBeApplied($desiredVersion);
        $line = "Updating version indicators";

        if ($canRealPatchBeApplied) {
            $line = $line . ". " . xlt("Patch was also installed, updating version patch indicator");
        }

        echo "<p class='text-success'>" . $line . "...</p><br />\n";
        $versionService->update($desiredVersion);

        echo "<p><p class='text-success'>" . xlt("Database and Access Control upgrade finished.") . "</p></p>\n";
        echo "</div></body></html>\n";
        exit();
    }
    ?>
        </div>
    </div>
</div>
</body>
</html>
