<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

if (!$GLOBALS ?? null) {
    require_once dirname(__DIR__, 5) . "/globals.php";
}

use Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use mysqli;
use OpenEMR\Common\Logging\EventAuditLogger;
use ZipArchive;

class DownloadWenoPharmacies
{
    public function __construct()
    {
    }

    /**
     * @param      $filePath
     * @param bool $isInsertOnly
     * @return false|int
     */
    public function processWenoPharmacyCsv($filePath, bool $isInsertOnly = true): false|int
    {
        $wenoLog = new WenoLogService();

        if (date("l") == "Monday" && $isInsertOnly) {
            $sql = "TRUNCATE TABLE weno_pharmacy";
            sqlStatement($sql);
        }
        // Use existing connection.
        // Compared to creating a new connection, this method is slower by 3 seconds.
        // Using the sqlStatement() method is even slower by 10 seconds. That's 13 seconds slower overall.
        $connect = $GLOBALS['dbh'];
        if ($connect->connect_error) {
            $wenoLog->insertWenoLog("pharmacy", "Connection Failed.");
            error_log("Connection failed: " . $connect->connect_error);
            return false;
        }
        // Check if file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $wenoLog->insertWenoLog("pharmacy", "Download file not found or not readable");
            error_log("Download file not found or not readable: " . $filePath);
            return false;
        }
        // Begin transaction
        $connect->begin_transaction();
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setEscape('\\'); // Set the escape character to handle backslashes
            $csv->setHeaderOffset(0);
            $stmt = (new Statement())->offset(0);
            $records = $stmt->process($csv);

            $headers = $records->getHeader();
            $rowNumber = stripos($headers[3] ?? '', 'NCPDP_safe') !== false ? 1 : 2;

            if ($rowNumber === 2) {
                $csv->setHeaderOffset(1);
                $records = $stmt->process($csv);
                $headers = $records->getHeader();
            }
            if ($headers === false) {
                throw new Exception("Error reading header from file: $filePath");
            }

            $columns = implode(", ", array_map(fn($col) => "`$col`", $headers));
            $placeholders = implode(", ", array_fill(0, count($headers), '?'));

            if ($isInsertOnly) {
                $sql = "INSERT INTO weno_pharmacy ($columns) VALUES ($placeholders)";
            } else {
                $updates = implode(", ", array_map(fn($col) => "`$col`=VALUES(`$col`)", $headers));
                $sql = "INSERT INTO weno_pharmacy ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updates";
            }

            $stmt = $connect->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . $connect->errno . ") " . $connect->error);
            }

            $types = str_repeat('s', count($headers));
            $recordSizeInBytes = 0;
            $batchSize = 30000;
            $batchRecords = [];
            foreach ($records as $record) {
                if (stripos($record['Created'], 'Confidential WENO Exchange') !== false) {
                    continue;
                }
                $rowNumber++;

                $record = array_map(fn($item) => str_replace(['[', ']'], '', trim($item ?? '')), $record);
                $dateTime = \DateTime::createFromFormat('m/d/Y h:i:s A', $record['Created']);
                $record['Created'] = $dateTime ? $dateTime->format('Y-m-d H:i:s') : null;
                $dateTime = \DateTime::createFromFormat('m/d/Y h:i:s A', $record['Modified']);
                $record['Modified'] = $dateTime ? $dateTime->format('Y-m-d H:i:s') : null;
                $dateTime = \DateTime::createFromFormat('m/d/Y h:i:s A', $record['Deleted']);
                $record['Deleted'] = $dateTime ? $dateTime->format('Y-m-d H:i:s') : null;

                $record['Business_Name'] = ucwords(strtolower($record['Business_Name']));
                $record['Address_Line_1'] = ucwords(strtolower($record['Address_Line_1']));
                $record['City'] = ucwords(strtolower($record['City']));

                if (count($record) !== count($headers)) {
                    error_log(text("Column count mismatch at row $rowNumber in file: $filePath"));
                    continue;
                }
                // Batch records in groups of batch size.
                $batchRecords[] = $record;

                if (count($batchRecords) === $batchSize) {
                    $recordSizeInBytes += strlen(serialize($batchRecords));
                    foreach ($batchRecords as $record) {
                        $stmt->bind_param($types, ...array_values($record));
                        $stmt->execute();
                    }
                    $batchRecords = [];
                }
            }
            // Finish processing any remaining records.
            if (!empty($batchRecords)) {
                $recordSizeInBytes += strlen(serialize($batchRecords));
                foreach ($batchRecords as $record) {
                    $stmt->bind_param($types, ...array_values($record));
                    $stmt->execute();
                }
            }

            $connect->commit();
           // $connect->close();
        } catch (Exception $e) {
            $connect->rollback();
            error_log(text($e->getMessage()));
            return false;
        }

        return $rowNumber - 2;
    }

    /**
     * @param $url
     * @param $storelocation
     * @return string|null
     */
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

        return true;
    }

    /**
     * @param $path_to_extract
     * @param $storeLocation
     * @return string|null
     */
    public function extractFile($path_to_extract, $storeLocation): ?string
    {
        $wenoLog = new WenoLogService();

        try {
            $zip = new ZipArchive();
        } catch (\Exception $e) {
            error_log('Error extracting zip file: ' . errorLogEscape($e->getMessage()));
            return "PHPError_install_zip_archive";
        }

        if ($zip->open($storeLocation) === true) {
            $zip->extractTo($path_to_extract);
            $files = glob($path_to_extract . "/*.csv");
            $csvFile = '';
            // search for the lite version either daily or weekly csv file
            if ($files) {
                foreach ($files as $file) {
                    if (stripos($file, 'weno_pharmacy_lite') !== false) {
                        $csvFile = $file;
                        break;
                    }
                }
                $zip->close();
                unlink($storeLocation); // TODO: uncomment this line
                if ($csvFile) {
                    // process the csv file
                    // Number of rows imported or false if error
                    $logMessage = "Background Initiated Pharmacy Update";
                    $wenoLog->insertWenoLog("pharmacy", $logMessage);
                    error_log($logMessage);

                    // process the csv file
                    $count = $this->processWenoPharmacyCsv($csvFile);

                    if ($count !== false) {
                        EventAuditLogger::instance()->newEvent(
                            "pharmacy_log",
                            $_SESSION['authUser'],
                            $_SESSION['authProvider'],
                            1,
                            "Background Task Pharmacy Download Imported $count Pharmacies Successfully."
                        );
                        $wenoLog->insertWenoLog("pharmacy", "Success $count pharmacies Updated");
                        error_log("Background Task Pharmacy Imported $count Pharmacies");
                    } else {
                        EventAuditLogger::instance()->newEvent(
                            "pharmacy_log",
                            $_SESSION['authUser'],
                            $_SESSION['authProvider'],
                            0,
                            "Pharmacy Import download failed."
                        );
                        $wenoLog->insertWenoLog("pharmacy", "Failed");
                        error_log("Background Task Pharmacy Import Failed");
                    }
                    // remove the files
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                    return $count;
                } else {
                    EventAuditLogger::instance()->newEvent(
                        "pharmacy_log",
                        $_SESSION['authUser'],
                        $_SESSION['authProvider'],
                        0,
                        "No CSV file found in the zip archive."
                    );
                    $wenoLog->insertWenoLog("pharmacy", "Failed");
                    return false;
                }
            } else {
                $scrape = file_get_contents($storeLocation);
                $wenolog = new WenoLogService();
                $isError = $wenolog->scrapeWenoErrorHtml($scrape);
                if ($isError['is_error']) {
                    EventAuditLogger::instance()->newEvent("pharmacy_background", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Pharmacy Failed download! Weno error: " . $isError['messageText']);
                    error_log('Pharmacy download failed: ' . errorLogEscape($isError['messageText']));
                    $wenolog->insertWenoLog("pharmacy", errorLogEscape($isError['messageText']));
                } else {
                    EventAuditLogger::instance()->newEvent("pharmacy_background", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Pharmacy Failed download! Weno error Other");
                    error_log("Pharmacy Failed download! Weno error: Other");
                    $wenoLog->insertWenoLog("pharmacy", "Failed");
                }
                die;
            }
        }
        return false;
    }
}
