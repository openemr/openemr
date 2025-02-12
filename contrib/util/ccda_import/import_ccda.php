<?php

/**
 * Import CCDA script
 *
 * Prior to use:
 *   1. Turn on Carecoordination modules in OpenEMR.
 *   2. Place ccdas in a directory.
 *   3. Uncomment exit at top of this script.
 *   4. Consider turning off the audit log (turn off both the 'Enable Audit Logging' and
 *      'Audit all Emergency User Queries' settings) in OpenEMR to improve performance (if audit log
 *      not needed).
 *
 * Use:
 *   1. use: php import_ccda.php <ccda-directory> <site> <openemr-directory> <development-mode> <enable-moves> <dedup>
 *   2. use example: php import_ccda.php /var/www/localhost/htdocs/openemr/synthea default /var/www/localhost/htdocs/openemr true
 *   3. use example: php import_ccda.php /var/www/localhost/htdocs/openemr/synthea default /var/www/localhost/htdocs/openemr false
 *   4. Note that development-mode will markedly improve performance by bypassing the import of
 *      the ccda document and bypassing the use of the audit_master and audit_details tables and
 *      will directly import the new patient data from the ccda. Note this should never be done
 *      on sites that already contain real data/use. This will also turn off the audit log during
 *      the import.
 *   5. Note that a log.txt file is created with log/stats of the run.
 *
 * Description of what this script automates (for unlimited number of ccda documents):
 *  1. import ccda document (bypassed in development-mode)
 *  2. import to ccda table (bypassed in development-mode)
 *  3. import as new patient
 *  4. run function to populate all the uuids via the universal service function that already exists
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// comment this out when using this script (and then uncomment it again when done using script)
// exit;

function parseArgs($argv): array
{
    $args = [];
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--')) {
            list($key, $value) = explode('=', substr($arg, 2), 2) + [1 => null];
            if ($key === 'help') {
                showHelp();
            }
            $args[$key] = $value;
        }
    }
    return $args;
}

function showHelp(): void
{
    // import_ccda.php --sourcePath=/xampp/htdocs/openemr/contrib/import_ccdas --site=default --openemrPath=/xampp/htdocs/openemr --isDev=true --enableMoves=true
    echo "\n";
    echo "Usage: php import_ccda.php [OPTIONS]\n";
    echo "\n";
    echo "Options:\n";
    echo "  --sourcePath     Required. Path to the directory containing CCDA files to import.\n";
    echo "  --site           Required. OpenEMR site ID.\n";
    echo "  --openemrPath    Required. Path to OpenEMR web root.\n";
    echo "  --isDev          Optional. Set to 'true' for development mode, 'false' for production. Default: true.\n";
    echo "  --enableMoves    Optional. Set to 'true' to move processed files, 'false' to disable. Default: false.\n";
    echo "  --dedup          Optional. Set to 'true' to enable duplicate checking, 'false' to disable. Default: false.\n";
    echo "  --help           Show this help message.\n";
    echo "\n";
    echo "Example:\n";
    echo "  php import_ccda.php --sourcePath=/path/to/import/documents \\\n";
    echo "                      --site=default \\\n";
    echo "                      --openemrPath=/var/www/openemr \\\n";
    echo "                      --isDev=true \\\n";
    echo "                      --enableMoves=false \\\n";
    echo "                      --dedup=false\n";
    echo "\n";
    exit;
}

function outputMessage($message): void
{
    echo("\n");
    echo $message;
    file_put_contents("log.txt", $message, FILE_APPEND);
}

// collect parameters (need to do before globals)
$args = parseArgs($argv);

// Required arguments
$requiredArgs = ['sourcePath', 'site', 'openemrPath'];

// Validate input
foreach ($requiredArgs as $req) {
    // ignore defaults
    if (!isset($args[$req])) {
        showHelp();
    }
}

$dir = rtrim($args['sourcePath'], '/') . '/*';
$_GET['site'] = $args['site'] ?? 'default';
$openemrPath = $args['openemrPath'] ?? '';
$seriousOptimizeFlag = filter_var($args['isDev'] ?? true, FILTER_VALIDATE_BOOLEAN); // default to true/on
$enableMoves = filter_var($args['enableMoves'] ?? false, FILTER_VALIDATE_BOOLEAN); // default to false/off
$dedup = filter_var($args['dedup'] ?? false, FILTER_VALIDATE_BOOLEAN); // default to false/off

$seriousOptimize = false;
if ($seriousOptimizeFlag == "true") {
    $seriousOptimize = true;
}

$ignoreAuth = 1;
require_once($openemrPath . "/interface/globals.php");

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Cda\CdaComponentParseHelpers;

// show parameters (need to do after globals)
outputMessage("ccda directory: " . $argv[1]);
outputMessage("site: " . $_SESSION['site_id']);
outputMessage("openemr path: " . $openemrPath);

if ($seriousOptimize) {
    outputMessage("development mode is on");
    // temporarily disable the audit log
    $auditLogSetting = sqlQueryNoLog("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'enable_auditlog'")['gl_value'] ?? 0;
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = 'enable_auditlog'");
    $auditLogBreakglassSetting = sqlQueryNoLog("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'gbl_force_log_breakglass'")['gl_value'] ?? 0;
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = 'gbl_force_log_breakglass'");
} else {
    outputMessage("development mode is off");
}

outputMessage("Starting patients import\n");
$counter = 0;
$millisecondsStart = round(microtime(true) * 1000);
foreach (glob($dir) as $file) {
    if (!is_file($file)) {
        continue;
    }
    $patientData = [];
    try {
        $file = str_replace("'", "\'", $file);
        if ($dedup) {
            $patientData = CdaComponentParseHelpers::parseCcdaPatientRole($file);
            if (empty($patientData)) {
                echo outputMessage("File load issue. Skipping: " . text($file) . "\n");
                continue;
            }
            $duplicates = CdaComponentParseHelpers::checkDuplicatePatient($patientData);
            if (!empty($duplicates)) {
                if ($enableMoves) {
                    CdaComponentParseHelpers::moveToDuplicateDir($file, $openemrPath . "/contrib/import_ccdas/duplicates");
                }
                echo outputMessage("Duplicate patient found and skipped: " . json_encode($duplicates) . "\n");
                continue;
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    //  1. import ccda document (bypassed in development-mode)
    if ($seriousOptimize) {
        // development-mode is on (note step 1 and step 2 are bypassed)
        // 3. import as new patient
        exec("php " . $openemrPath . "/bin/console openemr:ccda-newpatient-import --site=" . $_SESSION['site_id'] . " --document=" . $file);
    } else {
        // development mode is off
        //  1. import ccda document
        $fileContents = file_get_contents($file);
        $document = new Document();
        // TODO: collect CCDA category id instead of hardcoding 13
        $document->createDocument('00', 13, basename($file), 'text/xml', $fileContents);
        $documentId = $document->get_id();
        //  2. import to ccda table
        exec("php " . $openemrPath . "/bin/console openemr:ccda-import --site=" . $_SESSION['site_id'] . " --document_id=" . $documentId);
        $auditId = sqlQueryNoLog("SELECT max(`id`) as `maxid` FROM `audit_master`")['maxid'];
        //  3. import as new patient
        exec("php " . $openemrPath . "/bin/console openemr:ccda-newpatient --site=" . $_SESSION['site_id'] . " --am_id=" . $auditId . " --document_id=" . $documentId);
    }
    try {
        if ($enableMoves) {
            // move the C-CDA XML to the processed directory
            CdaComponentParseHelpers::moveToDuplicateDir($file, $openemrPath . "/contrib/import_ccdas/processed");
        }
    } catch (Exception $e) {
        outputMessage("Error moving file: " . $e->getMessage() . "\n");
    }
    echo('.');
    $counter++;
    $incrementCounter = 50; // echo every 50 records imported
    if (($counter % $incrementCounter) == 0) {
        $timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
        outputMessage($counter . " patients imported (" . $timeSec . " total seconds) (" . ((isset($lasttimeSec) ? ($timeSec - $lasttimeSec) : $timeSec) / $incrementCounter) . " average seconds per patient for last " . $incrementCounter . " patients)\n");
        $lasttimeSec = $timeSec;
    }
}
$timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
if ($counter > 0) {
    echo outputMessage("Completed patients import (" . $counter . " patients) (" . $timeSec . " total seconds) (" . (($timeSec) / $counter) . " average seconds per patient)");
//  4. run function to populate all the uuids via the universal service function that already exists
    echo outputMessage("Started uuid creation");
    UuidRegistry::populateAllMissingUuids(false);
    $timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
    echo outputMessage("Completed uuid creation (" . $timeSec . " total seconds; " . $timeSec / 3600 . " total hours)\n");
}

if ($seriousOptimize) {
    // reset the audit log to the original value
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'enable_auditlog'", [$auditLogSetting]);
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'gbl_force_log_breakglass'", [$auditLogBreakglassSetting]);
}
