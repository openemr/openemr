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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\Database;
use OpenEMR\Core\OEGlobalsBag;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class HolidayService implements HolidayServiceInterface
{
    public const TABLE_NAME = 'calendar_external';
    public const CATEGORY_HOLIDAY = 6;
    public const CATEGORY_CLOSED = 7;
    public const UPLOAD_DIR = 'documents/holidays_storage';
    public const FILE_NAME = 'holidays_to_import.csv';
    /**
     * Default `pc_recurrspec` value for a one-off holiday event: no recurrence,
     * no exclusion dates. Computed once at runtime from a named-field array so
     * the schema is auditable instead of an opaque serialized blob.
     */
    private const NO_RECURRENCE_SPEC = [
        'event_repeat_freq' => '0',
        'event_repeat_freq_type' => '0',
        'event_repeat_on_num' => '1',
        'event_repeat_on_day' => '0',
        'event_repeat_on_freq' => '0',
        'exdate' => '',
    ];

    private readonly string $targetFile;
    private readonly Filesystem $filesystem;
    /** @var array<string, true>|null */
    private ?array $holidayDateSet = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly HolidayCsvParserInterface $csvParser,
        string $siteDir,
        ?Filesystem $filesystem = null,
    ) {
        $this->targetFile = $siteDir . '/' . self::UPLOAD_DIR . '/' . self::FILE_NAME;
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * Construct an instance from the legacy global context. Use only at script
     * entry points and inside procedural legacy functions; new code should
     * inject dependencies through the constructor instead.
     */
    public static function createForLegacyContext(): self
    {
        // OpenEMR has not yet exposed a non-deprecated way to obtain a shared
        // Doctrine\DBAL\Connection from a service. Until the BC layer settles,
        // this factory is the single place that reaches into it.
        // @phpstan-ignore method.deprecated
        $connection = Database::instance()->getDbalConnection();
        return new self(
            connection: $connection,
            csvParser: new HolidayCsvParser(),
            siteDir: OEGlobalsBag::getInstance()->getString('OE_SITE_DIR'),
        );
    }

    public function uploadAndSync(UploadedFile $upload): void
    {
        $this->uploadCsv($upload);
        if (!$this->filesystem->exists($this->targetFile)) {
            throw new InvalidHolidayCsvException(xl('CSV file not found'));
        }
        try {
            $this->connection->transactional(function (): void {
                $this->doImportStagedRows($this->csvParser->parse($this->targetFile));
                $this->doPublishStagedRows();
            });
        } catch (DbalException $e) {
            throw new RuntimeException(xl('Failed to apply holidays to the calendar'), previous: $e);
        }
        $this->holidayDateSet = null;
    }

    public function uploadCsv(UploadedFile $upload): void
    {
        if (!$upload->isValid()) {
            throw new InvalidHolidayCsvException(
                sprintf(xl('Upload failed: %s'), $upload->getErrorMessage())
            );
        }
        if (strtolower($upload->getClientOriginalExtension()) !== 'csv') {
            throw new InvalidHolidayCsvException(xl('File must be a CSV'));
        }

        // Validate first so we never persist a bad CSV. Iterate without
        // materializing so a multi-megabyte upload doesn't get held in memory.
        foreach ($this->csvParser->parse($upload->getPathname()) as $_) {
            // intentionally empty — parse() throws on invalid rows
        }

        $uploadDir = dirname($this->targetFile);
        try {
            $this->filesystem->mkdir($uploadDir, 0700);
        } catch (IOException $e) {
            throw new RuntimeException(xl('Unable to create upload directory'), previous: $e);
        }

        try {
            $upload->move($uploadDir, basename($this->targetFile));
        } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
            throw new RuntimeException(xl('Unable to save uploaded file'), previous: $e);
        }
    }

    public function importHolidaysFromCsv(): void
    {
        if (!$this->filesystem->exists($this->targetFile)) {
            throw new InvalidHolidayCsvException(xl('CSV file not found'));
        }

        $rows = $this->csvParser->parse($this->targetFile);

        try {
            $this->connection->transactional(function () use ($rows): void {
                $this->doImportStagedRows($rows);
            });
        } catch (DbalException $e) {
            throw new RuntimeException(xl('Failed to import holidays into the staging table'), previous: $e);
        }
    }

    public function publishHolidayEvents(): void
    {
        try {
            $this->connection->transactional(function (): void {
                $this->doPublishStagedRows();
            });
        } catch (DbalException $e) {
            throw new RuntimeException(xl('Failed to publish holiday events'), previous: $e);
        }

        // Invalidate the in-memory cache only after the commit succeeds.
        $this->holidayDateSet = null;
    }

    /**
     * DELETE all staged rows and INSERT the parsed CSV rows. Must be called
     * inside a transaction so the swap is atomic. Streams rows from the
     * generator straight into INSERT so large CSVs don't get materialized.
     *
     * @param iterable<int, HolidayRow> $rows
     */
    private function doImportStagedRows(iterable $rows): void
    {
        // DELETE rather than TRUNCATE so the change participates in the
        // surrounding transaction (TRUNCATE issues an implicit commit on
        // MySQL/MariaDB).
        $this->connection->executeStatement('DELETE FROM ' . self::TABLE_NAME);
        foreach ($rows as $row) {
            $this->connection->insert(self::TABLE_NAME, [
                'date' => $row->dateForStorage(),
                'description' => $row->description,
                'source' => 'csv',
            ]);
        }
    }

    /**
     * Read the staging table and republish it onto the calendar. Must be
     * called inside a transaction so the read and the delete+insert see a
     * consistent snapshot of calendar_external.
     *
     * Holidays are inserted with `pc_facility = 0`, the OpenEMR sentinel
     * for "all facilities", so a clinician viewing any facility (or "All
     * Facilities") in the calendar sees them. The category-scoped DELETE
     * is symmetric — it removes every previous holiday event regardless
     * of facility — so the publish is consistent across the org.
     */
    private function doPublishStagedRows(): void
    {
        $staged = $this->connection->fetchAllAssociative(
            <<<'SQL'
            SELECT date, description FROM calendar_external
            SQL
        );

        $recurrSpec = serialize(self::NO_RECURRENCE_SPEC);

        $this->connection->executeStatement(
            <<<'SQL'
            DELETE FROM openemr_postcalendar_events WHERE pc_catid = ?
            SQL,
            [self::CATEGORY_HOLIDAY]
        );

        foreach ($staged as $row) {
            $description = $row['description'];
            $date = $row['date'];
            if (!is_string($description) || !is_string($date)) {
                continue;
            }
            $this->connection->executeStatement(
                <<<'SQL'
                INSERT INTO openemr_postcalendar_events (
                    pc_catid, pc_aid, pc_pid, pc_title, pc_time,
                    pc_eventDate, pc_duration, pc_recurrspec, pc_alldayevent,
                    pc_eventstatus, pc_facility, pc_sharing
                ) VALUES (?, 0, 0, ?, NOW(), ?, 86400, ?, 1, 1, ?, 2)
                SQL,
                [
                    self::CATEGORY_HOLIDAY,
                    $description,
                    $date,
                    $recurrSpec,
                    0, // pc_facility: "all facilities" — holidays are org-wide
                ]
            );
        }
    }

    public function getStoredCsvModifiedAt(): ?string
    {
        if (!$this->filesystem->exists($this->targetFile)) {
            return null;
        }
        $mtime = filemtime($this->targetFile);
        if ($mtime === false) {
            return null;
        }
        return date('d/m/Y H:i:s', $mtime);
    }

    public function getTargetFile(): string
    {
        return $this->targetFile;
    }

    public function getHolidaysByDateRange(string $startDate, string $endDate): array
    {
        try {
            $rows = $this->connection->fetchAllAssociative(
                <<<'SQL'
                SELECT pc_eventDate FROM openemr_postcalendar_events
                WHERE (pc_catid = ? OR pc_catid = ?)
                  AND pc_eventDate >= ? AND pc_eventDate <= ?
                SQL,
                [self::CATEGORY_HOLIDAY, self::CATEGORY_CLOSED, $startDate, $endDate]
            );
        } catch (DbalException $e) {
            throw new RuntimeException(xl('Failed to look up holidays'), previous: $e);
        }
        $dates = [];
        foreach ($rows as $row) {
            $value = $row['pc_eventDate'] ?? null;
            if (is_string($value)) {
                $dates[] = $value;
            }
        }
        return $dates;
    }

    public function isHoliday(string $date): bool
    {
        // Stored dates use Y-m-d; accept either separator in input.
        $date = strtr($date, '/', '-');
        if ($this->holidayDateSet === null) {
            try {
                $rows = $this->connection->fetchAllAssociative(
                    <<<'SQL'
                    SELECT pc_eventDate FROM openemr_postcalendar_events
                    WHERE pc_catid = ? OR pc_catid = ?
                    SQL,
                    [self::CATEGORY_HOLIDAY, self::CATEGORY_CLOSED]
                );
            } catch (DbalException $e) {
                throw new RuntimeException(xl('Failed to look up holidays'), previous: $e);
            }
            $set = [];
            foreach ($rows as $row) {
                $value = $row['pc_eventDate'] ?? null;
                if (is_string($value)) {
                    $set[$value] = true;
                }
            }
            $this->holidayDateSet = $set;
        }

        return isset($this->holidayDateSet[$date]);
    }
}
