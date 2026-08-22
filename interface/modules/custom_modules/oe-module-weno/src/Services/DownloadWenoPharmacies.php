<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2023-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

if (!$GLOBALS ?? null) {
    require_once dirname(__DIR__, 5) . "/globals.php";
}

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\Csv\Reader;
use League\Csv\Statement;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;
use ZipArchive;

class DownloadWenoPharmacies
{
    public const DOWNLOAD_FILENAME = 'weno_pharmacy.zip';

    /** @var list<string> */
    private const TABLE_COLUMNS = [
        'Created',
        'Modified',
        'Deleted',
        'NCPDP_safe',
        'Mutually_Defined_ID_safe',
        'NPI_safe',
        'Business_Name',
        'Address_Line_1',
        'Address_Line_2',
        'City',
        'State',
        'ZipCode_safe',
        'Country_Code',
        'International',
        'Latitude',
        'Longitude',
        'Pharmacy_Phone_safe',
        'Test_Pharmacy',
        'State_Wide_Mail_Order',
        'Mail_Order_US_State_Serviced',
        'Mail_Order_ US_Territories_Serviced',
        'On_WENO',
        '24HR',
    ];

    /** Give up minting synthetic keys past this many collisions on one seed. */
    private const MAX_NCPDP_COLLISION_SUFFIX = 999;

    /** Tokens kept upper-case when title-casing an all-caps source value. */
    private const UPPERCASE_TOKENS = [
        'CVS', 'RX', 'LLC', 'LLP', 'LP', 'INC', 'PC', 'PA', 'DBA',
        'USA', 'US', 'NE', 'NW', 'SE', 'SW', 'II', 'III', 'IV',
    ];

    private readonly LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? ServiceContainer::getLogger();
    }

    public function getDownloadDirectory(): string
    {
        $siteDir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR');

        return rtrim(is_string($siteDir) ? $siteDir : '', '/') . '/documents/logs_and_misc/weno/';
    }

    public function getDownloadFilePath(): string
    {
        return $this->getDownloadDirectory() . self::DOWNLOAD_FILENAME;
    }

    /**
     * Download pharmacy directory payload from Weno and import it.
     *
     * @return int|false Imported row count, or false on failure
     */
    /**
     * @param bool $isFullRebuild true replaces the whole directory (clears the
     *                            table inside the import transaction and plain
     *                            INSERTs); false upserts a daily delta. No
     *                            default - the destructive mode must be asked
     *                            for explicitly.
     */
    public function downloadAndImport(string $url, bool $isFullRebuild, string $logContext = 'Pharmacy Directory'): int|false
    {
        $wenoLog = new WenoLogService();
        $storeLocation = $this->getDownloadFilePath();
        $pathToExtract = $this->getDownloadDirectory();

        $downloadResult = $this->retrieveDataFile($url, $pathToExtract);
        if ($downloadResult['success'] !== true) {
            $message = $downloadResult['message'];
            $wenoLog->insertWenoLog($logContext, $message);
            $this->logger->error('Weno pharmacy download failed', ['message' => $message]);
            return false;
        }

        $csvFile = $this->resolveCsvFilePath($storeLocation, $pathToExtract, $logContext);
        if ($csvFile === false) {
            return false;
        }

        $wenoLog->insertWenoLog($logContext, 'Pharmacy file ready for import');
        $count = $this->processWenoPharmacyCsv($csvFile, $isFullRebuild);
        $this->cleanupExtractedCsvFiles($pathToExtract);

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($count !== false) {
            EventAuditLogger::getInstance()->newEvent(
                'pharmacy_log',
                $session->get('authUser'),
                $session->get('authProvider'),
                1,
                "Pharmacy Download Imported $count Pharmacies Successfully."
            );
            // The status prefix is how getLastFullRebuildDate() finds the last
            // rebuild, and 'Success%' stays intact for the dashboard widgets.
            $mode = $isFullRebuild ? WenoLogService::FULL_REBUILD_STATUS : WenoLogService::DAILY_UPDATE_STATUS;
            $wenoLog->insertWenoLog($logContext, "$mode: $count pharmacies Updated");
            $this->logger->info('Weno pharmacy import completed', ['imported' => $count]);
            return $count;
        }

        EventAuditLogger::getInstance()->newEvent(
            'pharmacy_log',
            $session->get('authUser'),
            $session->get('authProvider'),
            0,
            'Pharmacy Import failed while processing CSV.'
        );
        $wenoLog->insertWenoLog($logContext, 'Failed processing pharmacy CSV');
        $this->logger->error('Weno pharmacy import failed while processing CSV');
        return false;
    }

    /**
     * @param string $filePath
     * @param bool   $isFullRebuild
     * @return false|int
     */
    public function processWenoPharmacyCsv($filePath, bool $isFullRebuild = false): false|int
    {
        $wenoLog = new WenoLogService();

        $connect = OEGlobalsBag::getInstance()->get('dbh');
        if ($connect->connect_error) {
            $wenoLog->insertWenoLog('Pharmacy Directory', 'Connection Failed.');
            $this->logger->error('Weno pharmacy import: database connection failed', ['error' => $connect->connect_error]);
            return false;
        }

        if (!is_file($filePath) || !is_readable($filePath)) {
            $wenoLog->insertWenoLog('Pharmacy Directory', 'Download file not found or not readable');
            $this->logger->error('Weno pharmacy import: download file not found or not readable', ['file' => $filePath]);
            return false;
        }

        if (filesize($filePath) === 0) {
            $wenoLog->insertWenoLog('Pharmacy Directory', 'Download file is empty');
            $this->logger->error('Weno pharmacy import: download file is empty', ['file' => $filePath]);
            return false;
        }

        try {
            if ($connect->begin_transaction() !== true) {
                throw new Exception('Unable to start the pharmacy import transaction');
            }

            // Full-directory rebuild: clear inside the transaction so a failed
            // import rolls back to the previous directory instead of leaving the
            // table empty. TRUNCATE cannot be used here - it commits implicitly.
            if ($isFullRebuild) {
                QueryUtils::sqlStatementThrowException('DELETE FROM weno_pharmacy');
            }

            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setEscape('\\');
            $headerOffset = $this->detectHeaderOffset($filePath);
            $csv->setHeaderOffset($headerOffset);
            $stmt = (new Statement())->offset(0);
            $records = $stmt->process($csv);
            $rawHeaders = $records->getHeader();
            if ($rawHeaders === false || $rawHeaders === []) {
                throw new Exception('Error reading header from file: ' . $filePath);
            }

            $normalizedHeaders = array_map($this->normalizeHeaderName(...), $rawHeaders);
            $hasNativeNcpdp = in_array('NCPDP_safe', $normalizedHeaders, true);
            $insertColumns = self::TABLE_COLUMNS;
            $columnsSql = implode(', ', array_map(static fn(string $col): string => '`' . str_replace('`', '``', $col) . '`', $insertColumns));
            $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));

            if ($isFullRebuild) {
                $sql = "INSERT INTO weno_pharmacy ($columnsSql) VALUES ($placeholders)";
            } else {
                $updates = [];
                foreach ($insertColumns as $col) {
                    if ($col === 'NCPDP_safe') {
                        continue;
                    }
                    $safe = str_replace('`', '``', $col);
                    $updates[] = "`$safe`=VALUES(`$safe`)";
                }
                $sql = "INSERT INTO weno_pharmacy ($columnsSql) VALUES ($placeholders) ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
            }

            $stmtInsert = $connect->prepare($sql);
            if (!$stmtInsert) {
                throw new Exception('Prepare failed: (' . $connect->errno . ') ' . $connect->error);
            }

            $types = str_repeat('s', count($insertColumns));
            $imported = 0;
            $usedNcpdp = [];

            foreach ($records as $record) {
                $normalized = $this->normalizeRecord($record, $rawHeaders, $normalizedHeaders);
                if ($this->isFooterOrNoiseRow($normalized)) {
                    continue;
                }

                $row = $this->mapRecordToTableRow($normalized, $hasNativeNcpdp, $usedNcpdp);
                if ($row === null) {
                    continue;
                }

                $usedNcpdp[$row['NCPDP_safe']] = true;
                $values = [];
                foreach ($insertColumns as $col) {
                    $values[] = $row[$col];
                }

                $stmtInsert->bind_param($types, ...$values);
                if (!$stmtInsert->execute()) {
                    throw new Exception('Insert failed for pharmacy NCPDP ' . (string) $row['NCPDP_safe']);
                }
                $imported++;
            }

            // Nothing usable in the payload. Throw so the catch below performs the
            // single rollback for this method, restoring the rows the DELETE above
            // removed on a full-directory run.
            if ($imported === 0) {
                throw new Exception('CSV contained no importable pharmacy rows in ' . $filePath);
            }

            if ($connect->commit() !== true) {
                throw new Exception('Unable to commit the pharmacy import');
            }

            return $imported;
        } catch (\Throwable $e) {
            $connect->rollback();
            $this->logger->error('Weno pharmacy CSV import error', ['exception' => $e->getMessage()]);
            $wenoLog->insertWenoLog('Pharmacy Directory', 'CSV import error: ' . substr($e->getMessage(), 0, 180));
            return false;
        }
    }

    /**
     * Download remote content into weno_pharmacy.zip (legacy filename retained).
     *
     * $url and $storelocation are intentionally untyped: adding string types here
     * would push type errors onto out-of-tree callers. Both are narrowed with
     * is_scalar() below instead.
     *
     * @param mixed $url
     * @param mixed $storelocation
     *
     * @return array{success:bool,message:string,bytes?:int,http_code?:int,content_type?:string}
     */
    public function retrieveDataFile($url, $storelocation): array
    {
        $pathToExtract = rtrim(is_scalar($storelocation) ? (string) $storelocation : '', '/') . '/';
        $storeLocation = $pathToExtract . self::DOWNLOAD_FILENAME;
        $requestUrl = is_scalar($url) ? (string) $url : '';

        if ($requestUrl === '') {
            return [
                'success' => false,
                'message' => 'Unable to build pharmacy download request: missing url',
            ];
        }

        if (!is_dir($pathToExtract) && !mkdir($pathToExtract, 0775, true) && !is_dir($pathToExtract)) {
            return [
                'success' => false,
                'message' => 'Unable to create pharmacy download directory',
            ];
        }

        if (is_file($storeLocation)) {
            @unlink($storeLocation);
        }

        // Confirm the target is writable before handing the path to the client.
        $fp = fopen($storeLocation, 'w+b');
        if ($fp === false) {
            return [
                'success' => false,
                'message' => 'Unable to open pharmacy download target for writing',
            ];
        }
        fclose($fp);

        $httpCode = 0;
        $contentType = '';
        $transportError = '';
        try {
            $client = new Client([
                // 5 minutes, down from the 1000s carried over from CURLOPT_TIMEOUT.
                // Guzzle blocks the calling script for the whole window, and the
                // background service should not sit on a hung socket for 17 minutes.
                'timeout' => 300,
                'connect_timeout' => 60,
                'allow_redirects' => true,
                // Weno signals failures with an HTML error page and a 200/4xx mix,
                // so keep non-2xx responses as responses and classify them below.
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0',
                ],
            ]);
            $response = $client->request('GET', $requestUrl, ['sink' => $storeLocation]);
            $httpCode = $response->getStatusCode();
            $contentType = $response->getHeaderLine('Content-Type');
        } catch (GuzzleException $e) {
            // Guzzle puts the full request URI in its exception messages, and the
            // Weno URL carries useremail plus the encrypted credential blob. That
            // message ends up in weno_download_log and the app log, so strip the
            // query string before it travels any further.
            $transportError = $this->redactUrlQuery($e->getMessage());
        }

        clearstatcache(true, $storeLocation);
        $bytes = is_file($storeLocation) ? (int) filesize($storeLocation) : 0;

        if ($transportError !== '') {
            @unlink($storeLocation);
            return [
                'success' => false,
                'message' => 'Pharmacy download transport failed: ' . $transportError,
                'bytes' => $bytes,
                'http_code' => $httpCode,
                'content_type' => $contentType,
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            @unlink($storeLocation);
            return [
                'success' => false,
                'message' => "Pharmacy download failed: HTTP $httpCode",
                'bytes' => $bytes,
                'http_code' => $httpCode,
                'content_type' => $contentType,
            ];
        }

        if ($bytes <= 0) {
            @unlink($storeLocation);
            return [
                'success' => false,
                'message' => 'Pharmacy download failed: empty file received from Weno',
                'bytes' => $bytes,
                'http_code' => $httpCode,
                'content_type' => $contentType,
            ];
        }

        return [
            'success' => true,
            'message' => "Pharmacy download saved ($bytes bytes)",
            'bytes' => $bytes,
            'http_code' => $httpCode,
            'content_type' => $contentType,
        ];
    }

    /**
     * Legacy entry point used by background jobs. Returns imported count or false.
     *
     * @param string $path_to_extract
     * @param string $storeLocation
     * @return int|false|string
     */
    public function extractFile($path_to_extract, $storeLocation): int|false|string
    {
        $csvFile = $this->resolveCsvFilePath((string) $storeLocation, rtrim((string) $path_to_extract, '/') . '/', 'Pharmacy Directory');
        if ($csvFile === false) {
            return false;
        }

        $wenoLog = new WenoLogService();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $logMessage = 'Background Initiated Pharmacy Update';
        $wenoLog->insertWenoLog('Pharmacy Directory', $logMessage);
        $this->logger->info('Weno pharmacy background import started');

        $count = $this->processWenoPharmacyCsv($csvFile);
        $this->cleanupExtractedCsvFiles(rtrim((string) $path_to_extract, '/') . '/');

        if ($count !== false) {
            EventAuditLogger::getInstance()->newEvent(
                'pharmacy_log',
                $session->get('authUser'),
                $session->get('authProvider'),
                1,
                "Background Task Pharmacy Download Imported $count Pharmacies Successfully."
            );
            $wenoLog->insertWenoLog('Pharmacy Directory', "Success $count pharmacies Updated");
            $this->logger->info('Weno pharmacy background import completed', ['imported' => $count]);
            return $count;
        }

        EventAuditLogger::getInstance()->newEvent(
            'pharmacy_log',
            $session->get('authUser'),
            $session->get('authProvider'),
            0,
            'Pharmacy Import download failed.'
        );
        $wenoLog->insertWenoLog('Pharmacy Directory', 'Failed');
        $this->logger->error('Weno pharmacy background import failed');
        return false;
    }

    /**
     * Resolve a usable CSV path from a downloaded zip or direct CSV payload.
     */
    public function resolveCsvFilePath(string $storeLocation, string $pathToExtract, string $logContext = 'Pharmacy Directory'): string|false
    {
        $wenoLog = new WenoLogService();
        $pathToExtract = rtrim($pathToExtract, '/') . '/';

        if (!is_file($storeLocation)) {
            $message = 'Pharmacy download file missing on server after transfer';
            $wenoLog->insertWenoLog($logContext, $message);
            $this->logger->error($message, ['file' => $storeLocation]);
            return false;
        }

        $bytes = (int) filesize($storeLocation);
        if ($bytes <= 0) {
            $message = 'Pharmacy download file is empty on server';
            $wenoLog->insertWenoLog($logContext, $message);
            $this->logger->error($message, ['file' => $storeLocation]);
            return false;
        }

        $kind = $this->detectFileKind($storeLocation);
        if ($kind === 'error_html') {
            $scrape = file_get_contents($storeLocation) ?: '';
            $scraped = $wenoLog->scrapeWenoErrorHtml($scrape);
            $scraped = is_array($scraped) ? $scraped : [];
            if (($scraped['is_error'] ?? false) === true) {
                $messageText = $scraped['messageText'] ?? '';
                $messageText = is_scalar($messageText) ? trim((string) $messageText) : '';
                $message = 'Pharmacy download failed: ' . ($messageText !== '' ? $messageText : 'Weno returned an error page');
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message);
                return false;
            }
            $message = 'Pharmacy download failed: response was HTML/error content, not pharmacy data';
            $wenoLog->insertWenoLog($logContext, $message);
            $this->logger->error($message);
            return false;
        }

        if ($kind === 'csv') {
            $csvPath = $pathToExtract . 'weno_pharmacy_import.csv';
            if (!@copy($storeLocation, $csvPath)) {
                // Returning $storeLocation here would parse, but the file keeps its
                // .zip name: cleanupExtractedCsvFiles() only globs *.csv, so it
                // would leak, and every log line would name the wrong file.
                $message = 'Unable to stage the direct CSV pharmacy payload for import';
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message, ['source' => $storeLocation, 'target' => $csvPath]);
                return false;
            }
            $wenoLog->insertWenoLog($logContext, 'Detected direct CSV pharmacy payload (not ZIP)');
            return $csvPath;
        }

        if ($kind === 'zip') {
            if (!class_exists(ZipArchive::class)) {
                $message = 'PHP ZipArchive extension is not available';
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message);
                return false;
            }

            $zip = new ZipArchive();
            $openResult = $zip->open($storeLocation);
            if ($openResult !== true) {
                $message = 'Pharmacy download zip open failed (code ' . (string) $openResult . ', bytes=' . $bytes . ')';
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message, ['file' => $storeLocation]);
                return false;
            }

            // Archive is remote content: reject any entry that would resolve
            // outside the download directory before extracting anything.
            $extractRoot = realpath($pathToExtract);
            if ($extractRoot === false) {
                $zip->close();
                $message = 'Pharmacy download extract directory is unavailable';
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message, ['dir' => $pathToExtract]);
                return false;
            }
            $extractRoot = rtrim(str_replace('\\', '/', $extractRoot), '/') . '/';
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                $entryPath = $entry === false ? '' : str_replace('\\', '/', $entry);
                $target = $extractRoot . ltrim($entryPath, '/');
                if (
                    $entry === false
                    || $entryPath === ''
                    || str_starts_with($entryPath, '/')
                    || str_contains($entryPath, '../')
                    || !str_starts_with($target, $extractRoot)
                ) {
                    $zip->close();
                    $message = 'Pharmacy download zip contains an unsafe entry path';
                    $wenoLog->insertWenoLog($logContext, $message);
                    $this->logger->error($message, ['entry' => $entryPath]);
                    return false;
                }
            }

            if (!$zip->extractTo($pathToExtract)) {
                $zip->close();
                $message = 'Pharmacy download zip extract failed';
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message);
                return false;
            }
            $zip->close();

            $files = glob($pathToExtract . '*.csv') ?: [];
            $csvFile = '';
            // Two passes so the reduced "lite" directory wins over any other
            // weno_pharmacy* CSV regardless of glob order.
            foreach ($files as $file) {
                if (stripos($file, 'weno_pharmacy_lite') !== false) {
                    $csvFile = $file;
                    break;
                }
            }
            if ($csvFile === '') {
                foreach ($files as $file) {
                    if (stripos($file, 'weno_pharmacy') !== false) {
                        $csvFile = $file;
                        break;
                    }
                }
            }
            if ($csvFile === '' && $files !== []) {
                $csvFile = $files[0];
            }
            if ($csvFile === '') {
                $message = 'No CSV file found in the pharmacy zip archive';
                $wenoLog->insertWenoLog($logContext, $message);
                $this->logger->error($message);
                return false;
            }

            $wenoLog->insertWenoLog($logContext, 'Detected ZIP pharmacy payload');
            return $csvFile;
        }

        $message = 'Pharmacy download content type unrecognized (not ZIP or CSV)';
        $wenoLog->insertWenoLog($logContext, $message);
        $this->logger->error($message, ['file' => $storeLocation]);
        return false;
    }

    private function detectFileKind(string $path): string
    {
        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return 'unknown';
        }
        $head = (string) fread($fh, 1024);
        fclose($fh);

        if (str_starts_with($head, "PK\x03\x04") || str_starts_with($head, 'PK')) {
            return 'zip';
        }

        $trimmed = ltrim($head);
        $lower = strtolower($trimmed);
        if (
            str_starts_with($lower, '<!doctype html')
            || str_starts_with($lower, '<html')
            || str_contains($lower, '<textarea')
            || str_contains($lower, 'exceeded_download_limits')
        ) {
            return 'error_html';
        }

        if (
            str_starts_with($trimmed, 'Confidential:')
            || str_contains($head, 'Business_Name')
            || str_contains($head, 'NCPDP_safe')
            || str_contains($head, 'On_WENO')
        ) {
            return 'csv';
        }

        $sample = substr($head, 0, 200);
        $printable = preg_match('/^[\x09\x0A\x0D\x20-\x7E]+$/', $sample) === 1;
        if ($printable && str_contains($sample, ',')) {
            return 'csv';
        }

        return 'unknown';
    }

    private function detectHeaderOffset(string $filePath): int
    {
        $fh = fopen($filePath, 'rb');
        if ($fh === false) {
            return 0;
        }
        $first = (string) fgets($fh);
        $second = (string) fgets($fh);
        fclose($fh);

        if (stripos($first, 'Confidential:') !== false || stripos($first, 'copyright') !== false) {
            return 1;
        }
        if (stripos($first, 'Business_Name') === false && stripos($second, 'Business_Name') !== false) {
            return 1;
        }
        if (stripos($first, 'NCPDP_safe') === false && stripos($second, 'NCPDP_safe') !== false) {
            return 1;
        }
        return 0;
    }

    private function normalizeHeaderName(string $header): string
    {
        $header = trim($header);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $map = [
            'mail order' => 'State_Wide_Mail_Order',
            'mail_order' => 'State_Wide_Mail_Order',
            'app' => 'App',
            'on_weno' => 'On_WENO',
            '24hr' => '24HR',
            'ncpdp_safe' => 'NCPDP_safe',
            'mutually_defined_id_safe' => 'Mutually_Defined_ID_safe',
            'npi_safe' => 'NPI_safe',
            'zipcode_safe' => 'ZipCode_safe',
            'pharmacy_phone_safe' => 'Pharmacy_Phone_safe',
            'state_wide_mail_order' => 'State_Wide_Mail_Order',
            'mail_order_us_state_serviced' => 'Mail_Order_US_State_Serviced',
            'mail_order_ us_territories_serviced' => 'Mail_Order_ US_Territories_Serviced',
            'test_pharmacy' => 'Test_Pharmacy',
            'business_name' => 'Business_Name',
            'address_line_1' => 'Address_Line_1',
            'address_line_2' => 'Address_Line_2',
            'country_code' => 'Country_Code',
        ];
        $key = strtolower(str_replace(['-', ' '], ['_', ' '], $header));
        $keyCompact = strtolower(str_replace([' ', '-'], ['_', '_'], $header));
        if (isset($map[$key])) {
            return $map[$key];
        }
        if (isset($map[$keyCompact])) {
            return $map[$keyCompact];
        }
        foreach (self::TABLE_COLUMNS as $col) {
            if (strcasecmp($col, $header) === 0) {
                return $col;
            }
        }
        return $header;
    }

    /**
     * @param array<array-key, mixed>  $record
     * @param array<array-key, string> $rawHeaders
     * @param array<array-key, string> $normalizedHeaders
     * @return array<string, string>
     */
    private function normalizeRecord(array $record, array $rawHeaders, array $normalizedHeaders): array
    {
        $out = [];
        foreach ($rawHeaders as $idx => $rawHeader) {
            $normalized = $normalizedHeaders[$idx] ?? $this->normalizeHeaderName($rawHeader);
            $value = $record[$rawHeader] ?? '';
            if (is_array($value)) {
                $value = implode('|', array_map(
                    static fn(mixed $item): string => is_scalar($item) ? (string) $item : '',
                    $value
                ));
            }
            $out[$normalized] = str_replace(['[', ']'], '', trim(is_scalar($value) ? (string) $value : ''));
        }
        return $out;
    }

    /**
     * @param array<string, string> $normalized
     */
    private function isFooterOrNoiseRow(array $normalized): bool
    {
        $joined = strtolower(implode(' ', $normalized));
        return str_contains($joined, 'confidential weno exchange')
            || str_contains($joined, 'copyright weno');
    }

    /**
     * @param array<string, string> $normalized
     * @param array<string, bool>   $usedNcpdp
     * @return array<string, string|null>|null
     */
    private function mapRecordToTableRow(array $normalized, bool $hasNativeNcpdp, array &$usedNcpdp): ?array
    {
        $businessName = trim($normalized['Business_Name'] ?? '');
        $address1 = trim($normalized['Address_Line_1'] ?? '');
        $city = trim($normalized['City'] ?? '');
        $state = trim($normalized['State'] ?? '');
        if ($businessName === '' && $address1 === '' && $city === '') {
            return null;
        }

        $ncpdp = trim($normalized['NCPDP_safe'] ?? '');
        if ($ncpdp === '') {
            // Reduced CSV no longer includes NCPDP. Build a stable 7-digit key so
            // existing selector query (strlen(ncpdp_safe) < 8) still returns rows.
            $ncpdp = $this->buildSyntheticNcpdp(
                $businessName,
                $address1,
                $city,
                $state,
                $normalized['ZipCode_safe'] ?? ''
            );
        }

        $base = $ncpdp;
        $suffix = 0;
        while (isset($usedNcpdp[$ncpdp])) {
            $suffix++;
            if ($suffix > self::MAX_NCPDP_COLLISION_SUFFIX) {
                // Past this the suffix eats so much of the seed that the key stops
                // identifying the pharmacy. Drop the row loudly instead of minting
                // a key that could collide with a different pharmacy next import.
                $this->logger->warning(
                    'Weno pharmacy import: exhausted synthetic NCPDP suffixes, row skipped',
                    ['base' => $base, 'business_name' => $businessName]
                );
                return null;
            }
            // Keep the key at 7 chars: the selector query filters on
            // strlen(ncpdp_safe) < 8, so an 8-char key drops out of search.
            $suffixText = str_pad((string) $suffix, 2, '0', STR_PAD_LEFT);
            $ncpdp = substr($base, 0, max(0, 7 - strlen($suffixText))) . $suffixText;
        }

        $mailOrderRaw = $normalized['State_Wide_Mail_Order'] ?? ($normalized['Mail Order'] ?? '');
        $stateWide = $this->normalizeMailOrderFlag($mailOrderRaw);

        $onWeno = $this->normalizeBoolish($normalized['On_WENO'] ?? '', 'True', 'False');
        $testPharmacy = $this->normalizeBoolish($normalized['Test_Pharmacy'] ?? '', 'True', 'False');
        if ($testPharmacy === '' && ($normalized['App'] ?? '') !== '') {
            $app = strtolower(trim($normalized['App']));
            $testPharmacy = in_array($app, ['test', 'true', 'yes', 'y', '1'], true) ? 'True' : 'False';
        }

        return [
            'Created' => $this->normalizeDateTime($normalized['Created'] ?? ''),
            'Modified' => $this->normalizeDateTime($normalized['Modified'] ?? ''),
            'Deleted' => $this->normalizeDateTime($normalized['Deleted'] ?? ''),
            'NCPDP_safe' => $ncpdp,
            'Mutually_Defined_ID_safe' => $normalized['Mutually_Defined_ID_safe'] ?? '',
            'NPI_safe' => $normalized['NPI_safe'] ?? '',
            'Business_Name' => $this->titleCase($businessName),
            'Address_Line_1' => $this->titleCase($address1),
            'Address_Line_2' => $this->titleCase($normalized['Address_Line_2'] ?? ''),
            'City' => $this->titleCase($city),
            'State' => $state,
            'ZipCode_safe' => trim($normalized['ZipCode_safe'] ?? ''),
            'Country_Code' => trim($normalized['Country_Code'] ?? ''),
            'International' => $normalized['International'] ?? '',
            'Latitude' => $normalized['Latitude'] ?? '',
            'Longitude' => $normalized['Longitude'] ?? '',
            'Pharmacy_Phone_safe' => $normalized['Pharmacy_Phone_safe'] ?? '',
            'Test_Pharmacy' => $testPharmacy !== '' ? $testPharmacy : 'False',
            'State_Wide_Mail_Order' => $stateWide,
            'Mail_Order_US_State_Serviced' => $normalized['Mail_Order_US_State_Serviced'] ?? '',
            'Mail_Order_ US_Territories_Serviced' => $normalized['Mail_Order_ US_Territories_Serviced'] ?? '',
            'On_WENO' => $onWeno !== '' ? $onWeno : 'False',
            '24HR' => $this->normalizeYesNo($normalized['24HR'] ?? ''),
        ];
    }

    private function buildSyntheticNcpdp(string $name, string $address, string $city, string $state, string $zip): string
    {
        $seed = strtolower(trim($name . '|' . $address . '|' . $city . '|' . $state . '|' . $zip));
        $num = (int) sprintf('%u', crc32($seed)) % 10000000;
        return str_pad((string) $num, 7, '0', STR_PAD_LEFT);
    }

    private function normalizeMailOrderFlag(string $value): string
    {
        $v = strtolower(trim($value));
        if (in_array($v, ['true', 'yes', 'y', '1', 'state', 'mail'], true)) {
            return 'State';
        }
        if (strcasecmp($value, 'State') === 0) {
            return 'State';
        }
        return 'Local';
    }

    private function normalizeBoolish(string $value, string $trueVal, string $falseVal): string
    {
        $v = strtolower(trim($value));
        if ($v === '') {
            return '';
        }
        if (in_array($v, ['true', 'yes', 'y', '1'], true)) {
            return $trueVal;
        }
        if (in_array($v, ['false', 'no', 'n', '0'], true)) {
            return $falseVal;
        }
        if (strcasecmp($value, $trueVal) === 0) {
            return $trueVal;
        }
        if (strcasecmp($value, $falseVal) === 0) {
            return $falseVal;
        }
        return $value;
    }

    private function normalizeYesNo(string $value): string
    {
        $v = strtolower(trim($value));
        if (in_array($v, ['yes', 'y', 'true', '1'], true)) {
            return 'Yes';
        }
        if (in_array($v, ['no', 'n', 'false', '0'], true)) {
            return 'No';
        }
        return $value;
    }

    private function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        $dt = \DateTime::createFromFormat('m/d/Y h:i:s A', $value);
        if ($dt instanceof \DateTime) {
            return $dt->format('Y-m-d H:i:s');
        }
        $dt = date_create($value);
        if ($dt instanceof \DateTimeInterface) {
            return $dt->format('Y-m-d H:i:s');
        }
        return null;
    }

    /**
     * Strip query strings from any URL in text bound for a log or a status row.
     */
    private function redactUrlQuery(string $text): string
    {
        $redacted = preg_replace('~(https?://[^\s?"\'<>]+)\?[^\s"\'<>]*~i', '$1?[redacted]', $text);

        return is_string($redacted) ? $redacted : 'Pharmacy download transport failure';
    }

    private function titleCase(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        // Weno ships these fields upper-cased, which is why we title-case at all.
        // Anything already mixed-case is intentional (CVS, McKesson, LLC) and is
        // left exactly as supplied.
        if ($value !== mb_strtoupper($value)) {
            return $value;
        }

        $titled = ucwords(mb_strtolower($value));
        // Restore tokens that are conventionally upper-case.
        return (string) preg_replace_callback(
            '/\b(' . implode('|', self::UPPERCASE_TOKENS) . ')\b/i',
            static fn(array $m): string => mb_strtoupper($m[1]),
            $titled
        );
    }

    private function cleanupExtractedCsvFiles(string $pathToExtract): void
    {
        $pathToExtract = rtrim($pathToExtract, '/') . '/';
        $files = glob($pathToExtract . '*.csv') ?: [];
        foreach ($files as $file) {
            if (is_file($file) && stripos($file, 'logsync.csv') === false) {
                @unlink($file);
            }
        }
    }
}
