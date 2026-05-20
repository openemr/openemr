<?php

/**
 * Holiday service interface.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

interface HolidayServiceInterface
{
    /**
     * Accept the uploaded CSV ($_FILES['form_file']), validate it, store it,
     * then import + synchronize it into the live calendar in one step.
     *
     * @param array<string, mixed> $files raw $_FILES array
     */
    public function uploadAndSync(array $files): bool;

    /**
     * Accept the uploaded CSV without importing or synchronizing.
     *
     * @param array<string, mixed> $files raw $_FILES array
     */
    public function uploadCsv(array $files): bool;

    /**
     * Re-read the stored CSV into calendar_external (the staging table).
     */
    public function importHolidaysFromCsv(): bool;

    /**
     * Push calendar_external rows into openemr_postcalendar_events so they
     * appear on the calendar.
     */
    public function createHolidayEvents(): bool;

    /**
     * Metadata about the stored CSV file. Empty array when none is present.
     *
     * @return array{date?: string}
     */
    public function getCsvFileData(): array;

    /**
     * Absolute filesystem path to the stored CSV file (may or may not exist).
     */
    public function getTargetFile(): string;

    /**
     * Localized error message from the most recent failed operation, or ''.
     */
    public function getLastError(): string;

    /**
     * List of holiday/closed dates in the given inclusive range.
     *
     * @return list<string> dates as stored in pc_eventDate
     */
    public function getHolidaysByDateRange(string $startDate, string $endDate): array;

    /**
     * True when the given date is a holiday/closed date.
     */
    public function isHoliday(string $date): bool;
}
