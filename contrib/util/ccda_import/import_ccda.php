<?php

/**
 * Import CCDA script
 *
 * Prior to use:
 *   1. Place ccdas in a directory.
 *   2. Uncomment exit at top of this script.
 *
 * Use:
 *   1. See below help function for command usage.
 *   2. Note that development-mode will markedly improve performance by bypassing the import of
 *      the ccda document and bypassing the use of the audit_master and audit_details tables and
 *      will directly import the new patient data from the ccda. This will also turn off the audit log during
 *      the import.
 *   3, NOTE THAT THIS SCRIPT IS NOT WORKING AT THIS TIME IF THE DEVELOPMENT MODE IS TURNED OFF
 *   4. Note that a log.txt file is created with log/stats of the run.
 *
 * Description of what this script automates (for unlimited number of ccda documents):
 *  1. import ccda document (bypassed in development-mode)
 *  2. import to ccda table (bypassed in development-mode)
 *  3. import as new patient
 *  4. run function to populate all the uuids via the universal service function that already exists
 *  5. (optional via enableMoves) move files after being processed to the <openemrPath>/contrib/import_ccdas/processed
 *                                directory
 *  6. (optional via dedup) check for a patient duplicate before importing (if it is a duplicate and enableMoves is
 *                          true, then will not import patient and will move the file to the
 *                          <openemrPath>/contrib/import_ccdas/duplicates directory and log the duplicate information)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021-2025 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Enable this script via environment variable
if (!getenv('OPENEMR_ENABLE_CCDA_IMPORT')) {
    die('Set OPENEMR_ENABLE_CCDA_IMPORT=1 environment variable to enable this script');
}

// only allow use from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

function parseArgs($argv): array
{
    $args = [];
    foreach ($argv as $arg) {
        if (str_starts_with((string) $arg, '--')) {
            [$key, $value] = explode('=', substr((string) $arg, 2), 2) + [1 => null];
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
    echo "\n";
    echo "Usage: php import_ccda.php [OPTIONS]\n";
    echo "\n";
    echo "Options:\n";
    echo "  --authName      Required if isDev=false. userAuth so Documents can be saved/moved.\n";
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
    echo "                      --authName=admin \\\n";
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

$dir = rtrim((string) $args['sourcePath'], '/') . '/*';
$_GET['site'] = $args['site'] ?? 'default';
$openemrPath = $args['openemrPath'] ?? '';
$seriousOptimizeFlag = filter_var($args['isDev'] ?? true, FILTER_VALIDATE_BOOLEAN); // default to true/on
$enableMoves = filter_var($args['enableMoves'] ?? false, FILTER_VALIDATE_BOOLEAN); // default to false/off
$dedup = filter_var($args['dedup'] ?? false, FILTER_VALIDATE_BOOLEAN); // default to false/off
$authName = $args['authName'] ?? '';
$processedDir = rtrim((string) $args['sourcePath'], '/') . "/processed";
$duplicateDir = rtrim((string) $args['sourcePath'], '/') . "/duplicates";
$seriousOptimize = false;
if ($seriousOptimizeFlag == "true") {
    $seriousOptimize = true;
}

$ignoreAuth = 1;
require_once($openemrPath . "/interface/globals.php");

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Cda\CdaComponentParseHelpers;

// show parameters (need to do after globals)
outputMessage("OpenEMR path: " . $openemrPath);
outputMessage("CCDA Imports Location: " . $args['sourcePath']);
outputMessage("Site: " . $_SESSION['site_id']);

if ($seriousOptimize) {
    outputMessage("Development Mode is ON (performance mode)\n");
    // temporarily disable the audit log
    $auditLogSetting = sqlQueryNoLog("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'enable_auditlog'")['gl_value'] ?? 0;
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = 'enable_auditlog'");
    $auditLogBreakglassSetting = sqlQueryNoLog("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'gbl_force_log_breakglass'")['gl_value'] ?? 0;
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = 'gbl_force_log_breakglass'");
} else {
    outputMessage("Development Mode is OFF (audit log will be used)\n");
}

outputMessage("Starting patients import.\n");

$counter = 0;
$millisecondsStart = round(microtime(true) * 1000);
// iterate through all the files in the directory
foreach (glob($dir) as $file) {
    if (!is_file($file)) {
        continue;
    }
    sqlQueryNoLog("truncate audit_master");
    sqlQueryNoLog("truncate audit_details");
    $patientData = [];
    try {
        if ($dedup) {
            $patientData = CdaComponentParseHelpers::parseCcdaPatientRole($file);
            if (empty($patientData)) {
                outputMessage("File load issue. Skipping: " . text($file) . "\n");
                continue;
            }
            $duplicates = CdaComponentParseHelpers::checkDuplicatePatient($patientData);
            if (!empty($duplicates)) {
                if ($enableMoves) {
                    CdaComponentParseHelpers::moveToDuplicateDir($file, $duplicateDir);
                }
                $dups = count(($duplicates ?? []));
                outputMessage("Patient is duplicated " . text($dups) . " times. Patient skipped: " . json_encode($duplicates[0]) . "\n");
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
        exec("php " . $openemrPath . "/bin/console openemr:ccda-import --site=" . $_SESSION['site_id'] . " --document_id=" . $documentId . " --auth_name=" . $authName);
        $auditId = sqlQueryNoLog("SELECT max(`id`) as `maxid` FROM `audit_master`")['maxid'];
        //  3. import as new patient
        exec("php " . $openemrPath . "/bin/console openemr:ccda-newpatient --site=" . $_SESSION['site_id'] . " --am_id=" . $auditId . " --document_id=" . $documentId . " --auth_name=" . $authName);
    }
    try {
        if ($enableMoves) {
            // move the C-CDA XML to the processed directory
            CdaComponentParseHelpers::moveToDuplicateDir($file, $processedDir);
        }
    } catch (Exception $e) {
        outputMessage("Error moving file: " . $e->getMessage() . "\n");
    }
    // Keep alive the notifications
    echo("System has successfully imported CCDA number: " . text($counter + 1) . "\n"); // don't log it
    flush();
    ob_flush();
    $counter++;
    $incrementCounter = 10; // echo every 10 records imported
    if (($counter % $incrementCounter) == 0) {
        $timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
        outputMessage($counter . " patients imported (" . $timeSec . " total seconds) (" . ((isset($lasttimeSec) ? ($timeSec - $lasttimeSec) : $timeSec) / $incrementCounter) . " average seconds per patient for last " . $incrementCounter . " patients)\n");
        $lasttimeSec = $timeSec;
    }
}
$timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
if ($counter > 0) {
    outputMessage("Completed patients import (" . $counter . " patients) (" . $timeSec . " total seconds) (" . (($timeSec) / $counter) . " average seconds per patient)");
    //  4. run function to populate all the uuids via the universal service function that already exists
    outputMessage("Started uuid creation");
    UuidRegistry::populateAllMissingUuids(false);
    $timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
    outputMessage("Completed uuid creation (" . $timeSec . " total seconds; " . $timeSec / 3600 . " total hours)\n");
}

outputMessage("Finished patients import" . " $counter\n");

if ($seriousOptimize) {
    // reset the audit log to the original value
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'enable_auditlog'", [$auditLogSetting]);
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'gbl_force_log_breakglass'", [$auditLogBreakglassSetting]);
}
