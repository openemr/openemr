<?php

// Steps (time them if possible)
//  1. import ccda document
//  2. import to ccda table
//  3. import as new patient
//  4. run function to populate all the uuids via the universal service function that already exists

if (php_sapi_name() !== 'cli' || count($argv) != 2) {
    echo "Only php cli can execute a command\n";
    echo "example use: php import_ccda.php <directory>\n";
    die;
}

$dir = $argv[1] . '/*';

$_GET['site'] = 'default';
$ignoreAuth=1;
require_once("/var/www/localhost/htdocs/openemr/interface/globals.php");
require_once("/var/www/localhost/htdocs/openemr/library/uuid.php");

function outputMessage($message)
{
    echo $message;
    file_put_contents( "log.txt", $message, FILE_APPEND);
}

outputMessage("Starting patients importing\n");
$counter = 0;
$millisecondsStart = round(microtime(true) * 1000);
foreach(glob($dir) as $file) {
    //  1. import ccda document
    $fileContents = file_get_contents($file);
    $document = new Document();
    // TODO: collect CCDA category id instead of hardcoding 13
    $document->createDocument('00', 13, basename($file), 'text/xml', $fileContents);
    $documentId = $document->get_id();
    //  2. import to ccda table
    exec("php /var/www/localhost/htdocs/openemr/interface/modules/zend_modules/public/index.php ccda-import --site=" . $_SESSION['site_id'] . " --document_id=" . $documentId);
    $auditId = sqlQuery("SELECT max(`id`) as `maxid` FROM `audit_master`")['maxid'];
    //  3. import as new patient
    exec("php /var/www/localhost/htdocs/openemr/interface/modules/zend_modules/public/index.php ccda-newpatient --site=" . $_SESSION['site_id'] . " --am_id=" . $auditId . " --document_id=" . $documentId);
    $counter++;
    if (($counter % 5) == 0) {
        // echo every 5 records imported
        outputMessage($counter . " patient's imported (" . round(((round(microtime(true) * 1000)) - $millisecondsStart)/1000) . " seconds)\n");
    }
}
echo outputMessage("Completed patients importing (" . $counter .") (" . round(((round(microtime(true) * 1000)) - $millisecondsStart)/1000) . " seconds)\n");
//  4. run function to populate all the uuids via the universal service function that already exists
echo outputMessage("Started uuid creation\n");
autoPopulateAllMissingUuids();
echo outputMessage("Completed uuid creation (" . round(((round(microtime(true) * 1000)) - $millisecondsStart)/1000) . " seconds)\n");
