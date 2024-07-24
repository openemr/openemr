<?php

// Disable PHP timeout
@ini_set('max_execution_time', '0');

require_once dirname(__DIR__, 4) . "/globals.php";

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\WenoModule\Services\DownloadWenoPharmacies;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;
use OpenEMR\Modules\WenoModule\Services\WenoValidate;

//Ensure user has proper access permissions. Will automatically reset encryption key if needed.
$wenoValidate = new WenoValidate();
$isKey = $wenoValidate->validateAdminCredentials(true, "pharmacy");

$cryptoGen = new CryptoGen();
$weno_username = $GLOBALS['weno_admin_username'] ?? '';
$weno_password = $cryptoGen->decryptStandard($GLOBALS['weno_admin_password'] ?? '');
$encryption_key = $cryptoGen->decryptStandard($GLOBALS['weno_encryption_key'] ?? '');
$baseurl = "https://online.wenoexchange.com/en/EPCS/DownloadPharmacyDirectory";

$pharmacyDownloadService = new DownloadWenoPharmacies();
$pharmacyService = new PharmacyService();
$wenoLog = new WenoLogService();

$data = array(
    "UserEmail" => $weno_username,
    "MD5Password" => md5($weno_password),
    "ExcludeNonWenoTest" => "N",
    "Daily" => $_GET['daily'] ?? 'N'
);

$logMessage = "User Initiated Daily Pharmacy Update";
if ($data['Daily'] == 'N') {
    $logMessage = "User Initiated Weekly Pharmacy Update";
    $pharmacyService->removeWenoPharmacies();
}

$json_object = json_encode($data);
$method = 'aes-256-cbc';

$key = substr(hash('sha256', $encryption_key, true), 0, 32);

$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

$encrypted = base64_encode(openssl_encrypt($json_object, $method, $key, OPENSSL_RAW_DATA, $iv));

$fileUrl = $baseurl . "?useremail=" . urlencode($weno_username) . "&data=" . urlencode($encrypted);
$storeLocation = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/weno_pharmacy.zip";
$path_to_extract = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/";

$comment = "User Initiated Unscheduled Daily Pharmacy Import";
if ($data['Daily'] == 'N') {
    $comment = "User Initiated Unscheduled Weekly Pharmacy Import";
}
EventAuditLogger::instance()->newEvent(
    "pharmacy_log",
    $_SESSION['authUser'],
    $_SESSION['authProvider'],
    1,
    $comment
);

$wenoLog->insertWenoLog("pharmacy", 'Start File Download');
error_log('Start File Download');

download_zipfile($fileUrl, $storeLocation); // TODO: Uncomment this line

$wenoLog->insertWenoLog("pharmacy", 'End File Download');
error_log('End File Download');

$zip = new ZipArchive();
$csvFile = '';
if ($zip->open($storeLocation) === true) {
    $zip->extractTo($path_to_extract);
    $files = glob($path_to_extract . "/*.csv");
    if ($files) {
        foreach ($files as $file) {
            if (stripos($file, 'weno_pharmacy_lite') !== false) {
                $csvFile = $file;
                break;
            }
        }
        $zip->close();
        unlink($storeLocation); // TODO: Uncomment this line
    } else {
        $rpt = file_get_contents($storeLocation);
        $isError = $wenoLog->scrapeWenoErrorHtml($rpt);
        if ($isError['is_error']) {
            error_log('Pharmacy download failed: ' . errorLogEscape($isError['messageText']));
            $wenoLog->insertWenoLog("pharmacy", errorLogEscape($isError['messageText']));
            die(js_escape($isError['messageText']));
        }
        EventAuditLogger::instance()->newEvent("pharmacy_log", $_SESSION['authUser'], $_SESSION['authProvider'], 0, ($isError['messageText']));
        $wenoLog->insertWenoLog("pharmacy", "Failed");
        // no need to continue so send error to UI alert and die.
        die(js_escape('Pharmacy download failed.'));
    }
    // process the csv file
    // Number of rows imported or false if error
    $wenoLog->insertWenoLog("pharmacy", $logMessage);
    error_log($logMessage);

    // The money shot!
    $count = $pharmacyDownloadService->processWenoPharmacyCsv($csvFile, ($data['Daily'] == 'N'));

    // remove csv downloaded csv files
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    // log success if it has count imports
    if ($count !== false) {
        EventAuditLogger::instance()->newEvent(
            "pharmacy_log",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            1,
            "User Initiated Pharmacy Download was Imported Successfully."
        );
        $wenoLog->insertWenoLog("pharmacy", "Success " . text($count) . " pharmacies Updated");
        error_log("User Initiated Pharmacy Imported " . text($count) . " Pharmacies");
    } else {
        EventAuditLogger::instance()->newEvent(
            "pharmacy_log",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            0,
            "Pharmacy Import download failed."
        );
        $wenoLog->insertWenoLog("pharmacy", "Failed");
        error_log("User Initialed Pharmacy Import Failed");
    }
} else {
    EventAuditLogger::instance()->newEvent("pharmacy_log", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Pharmacy download zip open failed.");
    error_log('Pharmacy download zip open failed.');
    $wenoLog->insertWenoLog("pharmacy", "Pharmacy download zip open failed.");
    // no need to continue so send error to UI alert and die.
    die(js_escape('Pharmacy download zip open failed.'));
}

function download_zipfile($fileUrl, $zipped_file): void
{
    $fp = fopen($zipped_file, 'w+');

    $ch = curl_init($fileUrl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_exec($ch);

    curl_close($ch);
    fclose($fp);
}
