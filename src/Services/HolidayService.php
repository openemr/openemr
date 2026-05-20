<?php

/**
 * Holiday service.
 *
 * Combines the responsibilities of the legacy Holidays_Controller and
 * Holidays_Storage classes: uploaded-CSV handling, the calendar_external
 * staging table, and synchronization into openemr_postcalendar_events.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    sharonco <sharonco@matrix.co.il>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

final class HolidayService implements HolidayServiceInterface
{
    public const TABLE_NAME = 'calendar_external';
    public const CATEGORY_HOLIDAY = '6';
    public const CATEGORY_CLOSED = '7';
    public const UPLOAD_DIR = 'documents/holidays_storage';
    public const FILE_NAME = 'holidays_to_import.csv';

    private readonly string $siteDir;
    private readonly string $targetFile;
    private string $lastError = '';
    /** @var array<string, true>|null */
    private ?array $holidayDateSet = null;

    public function __construct(
        private readonly HolidayCsvParserInterface $csvParser,
        ?string $siteDir = null,
    ) {
        $this->siteDir = $siteDir ?? OEGlobalsBag::getInstance()->getString('OE_SITE_DIR');
        $this->targetFile = $this->siteDir . '/' . self::UPLOAD_DIR . '/' . self::FILE_NAME;
    }

    public function uploadAndSync(array $files): bool
    {
        if (!$this->uploadCsv($files)) {
            return false;
        }

        if (!$this->importHolidaysFromCsv()) {
            return false;
        }

        return $this->createHolidayEvents();
    }

    public function uploadCsv(array $files): bool
    {
        $this->lastError = '';
        $file = $files['form_file'] ?? null;
        if (!is_array($file)) {
            $this->lastError = xl('No file uploaded');
            return false;
        }

        $uploadDir = $this->siteDir . '/' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0700, true) && !is_dir($uploadDir)) {
            $this->lastError = xl('Unable to create upload directory');
            return false;
        }

        if (!$this->isValidCsvUpload($file)) {
            return false;
        }

        $tmpName = $file['tmp_name'];
        if (!is_string($tmpName) || !move_uploaded_file($tmpName, $this->targetFile)) {
            $this->lastError = xl('Unable to save uploaded file');
            return false;
        }

        return true;
    }

    public function importHolidaysFromCsv(): bool
    {
        $this->lastError = '';
        if ($this->getCsvFileData() === []) {
            $this->lastError = xl('CSV file not found');
            return false;
        }

        $handle = fopen($this->targetFile, 'r');
        if ($handle === false) {
            $this->lastError = xl('CSV import failed');
            return false;
        }

        try {
            $deleted = false;
            while (($data = $this->csvParser->readNextDataRow($handle)) !== null) {
                if (!$deleted) {
                    $this->truncateCalendarExternal();
                    $deleted = true;
                }
                $row = [$data[0], $data[1] ?? ''];
                sqlStatement(
                    'INSERT INTO ' . escape_table_name(self::TABLE_NAME)
                        . '(date,description,source) VALUES (?,?,?)',
                    [$row[0], $row[1], 'csv']
                );
            }
        } finally {
            fclose($handle);
        }

        return true;
    }

    public function createHolidayEvents(): bool
    {
        $this->holidayDateSet = null;
        $holidays = $this->getStagedHolidays();
        if ($holidays === []) {
            return true;
        }

        $this->deleteHolidayEvents();

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $pcFacility = $session->get('pc_facility') ?? 0;

        foreach ($holidays as $holiday) {
            $row = [
                self::CATEGORY_HOLIDAY,
                0,
                0,
                $holiday['description'],
                $holiday['date'],
                86400,
                'a:6:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";s:6:"exdate";s:0:"";}',
                1,
                1,
                $pcFacility,
                2,
            ];

            sqlInsert(
                <<<'SQL'
                INSERT INTO openemr_postcalendar_events (
                    pc_catid, pc_aid, pc_pid, pc_title, pc_time,
                    pc_eventDate, pc_duration, pc_recurrspec, pc_alldayevent,
                    pc_eventstatus, pc_facility, pc_sharing
                ) VALUES (?,?,?,?,NOW(),?,?,?,?,?,?,?)
                SQL,
                $row
            );
        }

        return true;
    }

    public function getCsvFileData(): array
    {
        if (!file_exists($this->targetFile)) {
            return [];
        }

        $mtime = filemtime($this->targetFile);
        if ($mtime === false) {
            return [];
        }

        return ['date' => date('d/m/Y H:i:s', $mtime)];
    }

    public function getTargetFile(): string
    {
        return $this->targetFile;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function getHolidaysByDateRange(string $startDate, string $endDate): array
    {
        $holidays = [];
        $res = sqlStatement(
            <<<'SQL'
            SELECT pc_eventDate FROM openemr_postcalendar_events
            WHERE (pc_catid = ? OR pc_catid = ?)
              AND pc_eventDate >= ? AND pc_eventDate <= ?
            SQL,
            [self::CATEGORY_HOLIDAY, self::CATEGORY_CLOSED, $startDate, $endDate]
        );
        while ($row = sqlFetchArray($res)) {
            $holidays[] = (string) $row['pc_eventDate'];
        }

        return $holidays;
    }

    public function isHoliday(string $date): bool
    {
        if ($this->holidayDateSet === null) {
            $this->holidayDateSet = [];
            $res = sqlStatement(
                <<<'SQL'
                SELECT pc_eventDate FROM openemr_postcalendar_events
                WHERE pc_catid = ? OR pc_catid = ?
                SQL,
                [self::CATEGORY_HOLIDAY, self::CATEGORY_CLOSED]
            );
            while ($row = sqlFetchArray($res)) {
                $this->holidayDateSet[(string) $row['pc_eventDate']] = true;
            }
        }

        return isset($this->holidayDateSet[$date]);
    }

    /**
     * @param array<string, mixed> $file
     */
    private function isValidCsvUpload(array $file): bool
    {
        if (!empty($file['error'])) {
            $this->lastError = xl('Upload failed');
            return false;
        }

        $tmpName = $file['tmp_name'] ?? '';
        if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
            $this->lastError = xl('Invalid upload');
            return false;
        }

        $name = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->lastError = xl('File must be a CSV');
            return false;
        }

        if (!$this->csvParser->isValidCsvContent($tmpName)) {
            $this->lastError = $this->csvParser->getLastError();
            return false;
        }

        return true;
    }

    /**
     * @return list<array{date: string, description: string}>
     */
    private function getStagedHolidays(): array
    {
        $holidays = [];
        $res = sqlStatement('SELECT date, description FROM ' . escape_table_name(self::TABLE_NAME));
        while ($row = sqlFetchArray($res)) {
            $holidays[] = [
                'date' => (string) $row['date'],
                'description' => (string) $row['description'],
            ];
        }
        return $holidays;
    }

    private function truncateCalendarExternal(): void
    {
        sqlStatement('TRUNCATE TABLE ' . escape_table_name(self::TABLE_NAME));
    }

    private function deleteHolidayEvents(): void
    {
        sqlStatement(
            'DELETE FROM openemr_postcalendar_events WHERE pc_catid = ?',
            [self::CATEGORY_HOLIDAY]
        );
    }
}
