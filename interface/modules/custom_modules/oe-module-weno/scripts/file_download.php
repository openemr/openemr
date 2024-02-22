<?php

// Disable PHP timeout
@ini_set('max_execution_time', '0');

require_once dirname(__DIR__, 4) . "/globals.php";

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

$cryptoGen = new CryptoGen();
$weno_username = $GLOBALS['weno_admin_username'] ?? '';
$weno_password = $cryptoGen->decryptStandard($GLOBALS['weno_admin_password'] ?? '');
$encryption_key = $cryptoGen->decryptStandard($GLOBALS['weno_encryption_key'] ?? '');
$baseurl = "https://online.wenoexchange.com/en/EPCS/DownloadPharmacyDirectory";

$data = array(
    "UserEmail" => $weno_username,
    "MD5Password" => md5($weno_password),
    "ExcludeNonWenoTest" => "N",
    "Daily" => "N"
);

if (date("l") == "Monday") { //if today is Monday download the weekly file
    $data["Daily"] = "N";
} else {
    $data["Daily"] = "Y";
}

$json_object = json_encode($data);
$method = 'aes-256-cbc';

$key = substr(hash('sha256', $encryption_key, true), 0, 32);

$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

$encrypted = base64_encode(openssl_encrypt($json_object, $method, $key, OPENSSL_RAW_DATA, $iv));

$fileUrl = $baseurl . "?useremail=" . urlencode($weno_username) . "&data=" . urlencode($encrypted);
$storelocation = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/weno_pharmacy.zip";
$path_to_extract = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/";

// takes URL of image and Path for the image as parameter
function download_zipfile($fileUrl, $zipped_file)
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

download_zipfile($fileUrl, $storelocation);

$zip = new ZipArchive();
$wenolog = new WenoLogService();

if ($zip->open($storelocation) === true) {
    $zip->extractTo($path_to_extract);

    $files = glob($path_to_extract . "/*.csv");
    if ($files) {
        $csvFile = $files[1];
        $filename = basename($csvFile);
        $csvFilename = $filename;

        echo 'File extracted successfully.';
        echo 'CSV filename: ' . text($csvFilename);
        $zip->close();
        unlink($storelocation);
    } else {
        EventAuditLogger::instance()->newEvent("pharmacy_log", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "No CSV file found in the zip archive.");
        echo 'No CSV file found in the zip archive.';
    }
} else {
    $rpt = file_get_contents($storelocation);
    $isError = $wenolog->scrapeWenoErrorHtml($rpt);
    if ($isError['is_error']) {
        error_log('Pharmacy download failed: ' . $isError['messageText']);
        $wenolog->insertWenoLog("pharmacy", "loginfail");
    }
    EventAuditLogger::instance()->newEvent("pharmacy_log", $_SESSION['authUser'], $_SESSION['authProvider'], 0, $isError['messageText']);
    $wenolog->insertWenoLog("pharmacy", "Failed");
    // no need to continue
    // send error to UI alert
    die(js_escape($isError['messageText']));
}

$insertPharmacy = new PharmacyService();

if ($data['Daily'] == 'N') {
    $insertPharmacy->removeWenoPharmacies();
}

$insertdata = [];

$l = 0;
if (file_exists($csvFile)) {
    // let's do transaction as this is long import
    $records = fopen($csvFile, "r");
    try {
        if ($records ?? null) {
            sqlStatementNoLog('SET autocommit=0');
            sqlStatementNoLog('START TRANSACTION');
        }
        while (!feof($records)) {
            $line = fgetcsv($records);

            if ($l <= 1) {
                $l++;
                continue;
            }
            if (!isset($line[1])) {
                continue;
            }
            if (!empty($line)) {
                if ($data['Daily'] == 'N') {
                    $ncpdp = str_replace(['[', ']'], '', $line[3] ?? '');
                    $npi = str_replace(['[', ']'], '', $line[5] ?? '');
                    $business_name = $line[6] ?? '';
                    $address_line_1 = $line[7] ?? '';
                    $address_line_2 = $line[8] ?? '';
                    $city = $line[9] ?? '';
                    $state = $line[10] ?? '';
                    $zipcode = str_replace(['[', ']'], '', $line[11]);
                    $country = $line[12] ?? '';
                    $international = $line[13] ?? '';
                    $pharmacy_phone = str_replace(['[', ']'], '', $line[16]);
                    $on_weno = $line[21] ?? '';
                    $test_pharmacy = $line[17] ?? '';
                    $state_wide_mail = $line[18] ?? '';
                    $fullDay = $line[22] ?? '';
                } else {
                    $ncpdp = str_replace(['[', ']'], '', $line[3] ?? '');
                    $npi = str_replace(['[', ']'], '', $line[7] ?? '');
                    $business_name = $line[8] ?? '';
                    $city = $line[11] ?? '';
                    $state = $line[12] ?? '';
                    $zipcode = str_replace(['[', ']'], '', $line[14] ?? '');
                    $country = $line[15] ?? '';
                    $address_line_1 = $line[9] ?? '';
                    $address_line_2 = $line[10] ?? '';
                    $international = $line[16] ?? '';
                    $pharmacy_phone = str_replace(['[', ']'], '', $line[20] ?? '');
                    $county = $line[33] ?? '';
                    $on_weno = $line[37] ?? '';
                    $compounding = $line[41] ?? '';
                    $medicaid_id = $line[45] ?? '';
                    $dea = $line[44] ?? '';
                    $test_pharmacy = $line[29] ?? '';
                    $fullDay = $line[40] ?? '';
                    $state_wide_mail = $line[47] ?? '';
                }

                $insertdata['ncpdp'] = $ncpdp;
                $insertdata['npi'] = $npi;
                $insertdata['business_name'] = $business_name;
                $insertdata['address_line_1'] = $address_line_1;
                $insertdata['address_line_2'] = $address_line_2;
                $insertdata['city'] = $city;
                $insertdata['state'] = $state;
                $insertdata['zipcode'] = $zipcode;
                $insertdata['country'] = $country;
                $insertdata['international'] = $international;
                $insertdata['pharmacy_phone'] = $pharmacy_phone;
                $insertdata['on_weno'] = $on_weno;
                $insertdata['test_pharmacy'] = $test_pharmacy;
                $insertdata['state_wide_mail'] = $state_wide_mail;
                $insertdata['fullDay'] = $fullDay;

                if ($data['Daily'] == 'Y') {
                    $insertPharmacy->updatePharmacies($insertdata);
                } else {
                    $insertPharmacy->insertPharmacies($insertdata);
                }
                ++$l;
            }
        }
    } catch (Exception $e) {
        throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
    }

    fclose($records);
    // Start the transaction.
    sqlStatementNoLog('COMMIT');
    sqlStatementNoLog('SET autocommit=1');

    // remove the files
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    // let's brag about it.
    EventAuditLogger::instance()->newEvent(
        "pharmacy_log",
        $_SESSION['authUser'],
        $_SESSION['authProvider'],
        1,
        "User Initiated Pharmacy Download was Imported Successfully."
    );
    $wenolog->insertWenoLog("pharmacy", "Success");
    error_log("User Initialed Pharmacy Imported");
} else {
    EventAuditLogger::instance()->newEvent(
        "pharmacy_log",
        $_SESSION['authUser'],
        $_SESSION['authProvider'],
        0,
        "Pharmacy Import download failed."
    );
    $wenolog->insertWenoLog("pharmacy", "Failed");
    error_log("User Initialed Pharmacy Import Failed File Missing");
}
