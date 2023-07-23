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
 *   1. use: php import_ccda.php <ccda-directory> <site> <openemr-directory> <development-mode>
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
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// comment this out when using this script (and then uncomment it again when done using script)
exit;

if (php_sapi_name() !== 'cli' || count($argv) != 5) {
    echo "Only php cli can execute a command\n";
    echo "use: php import_ccda.php <ccda-directory> <site> <openemr-directory> <development-mode>\n";
    echo "example use: php import_ccda.php /var/www/localhost/htdocs/openemr/synthea default /var/www/localhost/htdocs/openemr true\n";
    echo "example use: php import_ccda.php /var/www/localhost/htdocs/openemr/synthea default /var/www/localhost/htdocs/openemr false\n";
    die;
}

function outputMessage($message)
{
    echo $message;
    file_put_contents("log.txt", $message, FILE_APPEND);
}

// collect parameters (need to do before globals)
$dir = $argv[1] . '/*';
$_GET['site'] = $argv[2];
$openemrPath = $argv[3];
$seriousOptimizeFlag = $argv[4];
if ($seriousOptimizeFlag == "true") {
    $seriousOptimize = true;
} else {
    $seriousOptimize = false;
}

$ignoreAuth = 1;
require_once($openemrPath . "/interface/globals.php");

use OpenEMR\Common\Uuid\UuidRegistry;

// show parameters (need to do after globals)
outputMessage("ccda directory: " . $argv[1] . "\n");
outputMessage("site: " . $_SESSION['site_id'] . "\n");
outputMessage("openemr path: " . $openemrPath . "\n");

if ($seriousOptimize) {
    outputMessage("development mode is on\n");
    // temporarily disable the audit log
    $auditLogSetting = sqlQueryNoLog("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'enable_auditlog'")['gl_value'] ?? 0;
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = 'enable_auditlog'");
    $auditLogBreakglassSetting = sqlQueryNoLog("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'gbl_force_log_breakglass'")['gl_value'] ?? 0;
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = 'gbl_force_log_breakglass'");
} else {
    outputMessage("development mode is off\n");
}

outputMessage("Starting patients import\n");
$counter = 0;
$millisecondsStart = round(microtime(true) * 1000);
foreach (glob($dir) as $file) {
    if ($seriousOptimize) {
        // development-mode is on (note step 1 and step 2 are bypassed)
        // 3. import as new patient (note need to escape ' characters in the filename)
        $file = str_replace("'", "\'", $file);
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
    $counter++;
    $incrementCounter = 50; // echo every 50 records imported
    if (($counter % $incrementCounter) == 0) {
        $timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
        outputMessage($counter . " patients imported (" . $timeSec . " total seconds) (" . ((isset($lasttimeSec) ? ($timeSec - $lasttimeSec) : $timeSec) / $incrementCounter) . " average seconds per patient for last " . $incrementCounter . " patients)\n");
        $lasttimeSec = $timeSec;
    }
}
$timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
echo outputMessage("Completed patients import (" . $counter . " patients) (" . $timeSec . " total seconds) (" . (($timeSec) / $counter) . " average seconds per patient)\n");
//  4. run function to populate all the uuids via the universal service function that already exists
echo outputMessage("Started uuid creation\n");
UuidRegistry::populateAllMissingUuids(false);
$timeSec = round(((round(microtime(true) * 1000)) - $millisecondsStart) / 1000);
echo outputMessage("Completed uuid creation (" . $timeSec . " total seconds; " . $timeSec / 3600 . " total hours)\n");

if ($seriousOptimize) {
    // reset the audit log to the original value
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'enable_auditlog'", [$auditLogSetting]);
    sqlStatementNoLog("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'gbl_force_log_breakglass'", [$auditLogBreakglassSetting]);
}
