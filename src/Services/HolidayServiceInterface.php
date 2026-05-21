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

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface HolidayServiceInterface
{
    /**
     * Persist the uploaded CSV, then import + publish it to the calendar
     * in one atomic-from-the-user's-perspective operation.
     *
     * @throws InvalidHolidayCsvException if the CSV fails validation.
     * @throws \RuntimeException for filesystem or persistence failures.
     */
    public function uploadAndSync(UploadedFile $upload): void;

    /**
     * Persist the uploaded CSV to the configured site directory.
     *
     * @throws InvalidHolidayCsvException if the CSV fails validation.
     * @throws \RuntimeException for filesystem failures.
     */
    public function uploadCsv(UploadedFile $upload): void;

    /**
     * Re-read the stored CSV into the calendar_external staging table.
     *
     * @throws InvalidHolidayCsvException if the stored CSV is missing or invalid.
     */
    public function importHolidaysFromCsv(): void;

    /**
     * Publish staged rows onto the live calendar. Holidays are
     * organization-wide; the published events appear on every facility's
     * calendar regardless of which session triggered the publish.
     */
    public function publishHolidayEvents(): void;

    /**
     * Last-modified timestamp of the stored CSV, or null if none.
     */
    public function getStoredCsvModifiedAt(): ?DateTimeImmutable;

    /**
     * Absolute filesystem path the service writes uploaded CSVs to.
     */
    public function getTargetFile(): string;

    /**
     * @return list<string> holiday dates within $startDate..$endDate inclusive.
     */
    public function getHolidaysByDateRange(string $startDate, string $endDate): array;

    public function isHoliday(string $date): bool;
}
