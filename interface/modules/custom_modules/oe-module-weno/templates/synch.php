<?php

/**
 * @package    OpenEMR
 * @link       http://www.open-emr.org
 * @author     omegasystemsgroup.com
 * @author     Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @copyright  Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Modules\WenoModule\Services\LogProperties;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

$wenoLog = new WenoLogService();
/*
 * access control is on Weno side based on the user login
 */
if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo TransmitProperties::styleErrors(xlt('Prescriptions Review Not Authorized'));
    exit;
}

$logProperties = new LogProperties();
$task = $_REQUEST['key'] ?? $_POST['key'] ?? '';
// Check if the task is to download the status log
if ($task == 'downloadStatusLog') {
    try {
        $result = downloadWenoLogCsvAndZip();
    } catch (Exception $e) {
        $result = false;
        $wenoLog->insertWenoLog("log download", text($e->getMessage()));
        error_log('Error downloading log: ' . errorLogEscape($e->getMessage()));
        http_response_code(500);
        exit;
    }
    if ($result === true) {
        $wenoLog->insertWenoLog("log download", "Success");
        http_response_code(200);
    } else {
        $wenoLog->insertWenoLog("log download", text($result));
        http_response_code(500);
    }

    exit;
}
// Check if the task is to sync the log
try {
    $result = $logProperties->logSync($task);
} catch (Exception $e) {
    $result = false;
    $wenoLog->insertWenoLog("Sync Report", $e->getMessage());
    error_log('Error syncing log: ' . errorLogEscape($e->getMessage()));
    http_response_code(500);
    exit;
}

if ($result == true) {
    $wenoLog->insertWenoLog("Sync Report", "Success");
    http_response_code(200);
} else {
    http_response_code(500);
}

function downloadWenoLogCsv()
{
    if (headers_sent()) {
        return js_escape("Headers already sent, CSV download cannot proceed.");
    }
    ob_start();
    // Query to get the log data
    $sql = "SELECT `id`, `value`, `status`, `created_at` FROM `weno_download_log` ORDER BY `id` DESC";
    $result = sqlStatement($sql);
    if (!$result) {
        return js_escape("Failed to retrieve data from the database.");
    }
    // Set the headers for the CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="weno_download_log.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    // Open stream
    $output = fopen('php://output', 'w');
    if ($output === false) {
        return ("Failed to open output stream.");
    }
    // Write headers
    fputcsv($output, ['ID', 'Value', 'Status', 'Created At']);
    // Write the rows from the log
    while ($row = sqlFetchArray($result)) {
        fputcsv($output, $row);
    }

    fclose($output);
    ob_flush();
    return true;
}
function downloadWenoLogCsvAndZip()
{
    if (headers_sent()) {
        return js_escape("Headers already sent, download cannot proceed.");
    }

    $tempDir = sys_get_temp_dir();
    $csvFileName = 'weno_download_log.csv';
    $csvFilePath = $tempDir . DIRECTORY_SEPARATOR . $csvFileName;
    $zipFileName = 'weno_support_debug.zip';
    $zipFilePath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;
    $wenoDirectory = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/";

    // Create CSV of log content to temporary file
    $csvFile = fopen($csvFilePath, 'w');
    if ($csvFile === false) {
        return js_escape("Failed to open temporary CSV file.");
    }
    // Query to get the log data
    $sql = "SELECT `id`, `value`, `status`, `created_at`, `data_in_context` FROM `weno_download_log` ORDER BY `id` DESC";
    $result = sqlStatement($sql);
    if (!$result) {
        fclose($csvFile);
        return js_escape("Failed to retrieve data from the database.");
    }
    fputcsv($csvFile, ['ID', 'Value', 'Status', 'Created At']);
    while ($row = sqlFetchArray($result)) {
        fputcsv($csvFile, $row);
    }
    fclose($csvFile);

    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return js_escape("Failed to create ZIP archive.");
    }
    $zip->addFile($csvFilePath, $csvFileName);
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($wenoDirectory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = basename($filePath);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    // download the ZIP archive
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($zipFilePath);

    // Optionally, delete the temporary files
    unlink($csvFilePath);
    unlink($zipFilePath);

    return true;
}
