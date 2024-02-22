<?php

/**
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;
use OpenEMR\Modules\WenoModule\Services\WenoPharmaciesImport;
use ZipArchive;

class DownloadWenoPharmacies
{
    public function retrieveDataFile($url, $storelocation): ?string
    {
        $path_to_extract = $storelocation;
        $storelocation .= "weno_pharmacy.zip";
        if (!is_dir($path_to_extract)) {
            mkdir($path_to_extract, 0775, true);
        }
        $fp = fopen($storelocation, 'w+');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $result = $this->extractFile($path_to_extract, $storelocation);
        if ($result == "Imported") {
            return "Imported";
        } else {
            return $result;
        }
    }

    public function extractFile($path_to_extract, $storelocation): ?string
    {
        $zip = new ZipArchive();
        $wenoLog = new WenoLogService();
        $import = new WenoPharmaciesImport();

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
                $result = $import->importPharmacy($csvFile, $files);
                if ($result == "Imported") {
                    $wenoLog->insertWenoLog("pharmacy", "Success");
                } else {
                    $wenoLog->insertWenoLog("pharmacy", "Failed");
                }
                return $result;
            } else {
                EventAuditLogger::instance()->newEvent(
                    "pharmacy_log",
                    $_SESSION['authUser'],
                    $_SESSION['authProvider'],
                    0,
                    "No CSV file found in the zip archive."
                );
                $wenoLog->insertWenoLog("pharmacy", "Failed");
                return "Failed";
            }
        } else {
            $scrape = file_get_contents($storelocation);
            $wenolog = new WenoLogService();
            $isError = $wenolog->scrapeWenoErrorHtml($scrape);
            if ($isError['is_error']) {
                EventAuditLogger::instance()->newEvent("pharmacy_background", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Pharmacy Failed download! Weno error: " . $isError['messageText']);
                error_log('Pharmacy download failed: ' . $isError['messageText']);
                $wenolog->insertWenoLog("pharmacy", "loginfail");
            } else {
                EventAuditLogger::instance()->newEvent("pharmacy_background", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Pharmacy Failed download! Weno error Other");
                error_log("Pharmacy Failed download! Weno error: Other");
            }
            $wenoLog->insertWenoLog("pharmacy", "Failed");
            die;
        }
    }
}
