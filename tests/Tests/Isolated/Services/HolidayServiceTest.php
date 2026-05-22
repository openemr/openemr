<?php

/**
 * Isolated tests for HolidayService.
 *
 * Uses an in-memory SQLite connection, a real Symfony Filesystem against a
 * scratch siteDir, the real HolidayCsvParser, a fixed ClockInterface, and
 * UploadedFile in test mode (so is_uploaded_file() checks are bypassed).
 *
 * The DBAL exception-handling branches are exercised with a mocked
 * Connection that throws on the first call.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Services\HolidayCsvParser;
use OpenEMR\Services\HolidayService;
use OpenEMR\Services\InvalidHolidayCsvException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Group('isolated')]
class HolidayServiceTest extends TestCase
{
    private Connection $connection;
    private Filesystem $filesystem;
    private string $siteDir;
    private ClockInterface $clock;

    protected function setUp(): void
    {
        $GLOBALS['disable_translation'] = true;
        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE calendar_external (
                date TEXT,
                description TEXT,
                source TEXT
            )
            SQL);
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE openemr_postcalendar_events (
                pc_eid INTEGER PRIMARY KEY AUTOINCREMENT,
                pc_catid INTEGER,
                pc_aid INTEGER,
                pc_pid INTEGER,
                pc_title TEXT,
                pc_time TEXT,
                pc_eventDate TEXT,
                pc_duration INTEGER,
                pc_recurrspec TEXT,
                pc_alldayevent INTEGER,
                pc_eventstatus INTEGER,
                pc_facility INTEGER,
                pc_sharing INTEGER
            )
            SQL);

        $this->filesystem = new Filesystem();
        $this->siteDir = sys_get_temp_dir() . '/holiday-service-test-' . bin2hex(random_bytes(6));
        $this->filesystem->mkdir($this->siteDir);

        $fixed = new DateTimeImmutable('2026-05-21 12:00:00');
        $this->clock = new class ($fixed) implements ClockInterface {
            public function __construct(private readonly DateTimeImmutable $fixed)
            {
            }
            public function now(): DateTimeImmutable
            {
                return $this->fixed;
            }
        };
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['disable_translation']);
        $this->connection->close();
        if (is_dir($this->siteDir)) {
            $this->filesystem->remove($this->siteDir);
        }
    }

    private function newService(?Connection $connection = null): HolidayService
    {
        return new HolidayService(
            connection: $connection ?? $this->connection,
            csvParser: new HolidayCsvParser(),
            siteDir: $this->siteDir,
            filesystem: $this->filesystem,
            clock: $this->clock,
        );
    }

    private function uploadedCsv(string $csv, string $name = 'holidays.csv'): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'holiday-upload-');
        if ($tmp === false) {
            self::fail('tempnam failed'); // @codeCoverageIgnore
        }
        file_put_contents($tmp, $csv);
        return new UploadedFile($tmp, $name, 'text/csv', null, true);
    }

    public function testGetTargetFileBuildsExpectedPath(): void
    {
        self::assertSame(
            $this->siteDir . '/documents/holidays_storage/holidays_to_import.csv',
            $this->newService()->getTargetFile(),
        );
    }

    public function testGetStoredCsvModifiedAtReturnsNullWhenAbsent(): void
    {
        self::assertNull($this->newService()->getStoredCsvModifiedAt());
    }

    public function testGetStoredCsvModifiedAtReturnsTimestampWhenPresent(): void
    {
        $service = $this->newService();
        $this->filesystem->mkdir(dirname($service->getTargetFile()));
        $this->filesystem->touch($service->getTargetFile());
        $mtime = $service->getStoredCsvModifiedAt();
        self::assertInstanceOf(DateTimeImmutable::class, $mtime);
    }

    public function testUploadCsvRejectsNonCsvExtension(): void
    {
        $this->expectException(InvalidHolidayCsvException::class);
        $this->expectExceptionMessage('File must be a CSV');
        $this->newService()->uploadCsv($this->uploadedCsv("2026-12-25,X\n", 'holidays.txt'));
    }

    public function testUploadCsvRejectsInvalidUpload(): void
    {
        $upload = new UploadedFile(
            __FILE__,
            'broken.csv',
            'text/csv',
            UPLOAD_ERR_INI_SIZE,
            true,
        );
        $this->expectException(InvalidHolidayCsvException::class);
        $this->expectExceptionMessageMatches('/^Upload failed: /');
        $this->newService()->uploadCsv($upload);
    }

    public function testUploadCsvWritesFileToTargetPath(): void
    {
        $service = $this->newService();
        $service->uploadCsv($this->uploadedCsv("2026-12-25,Christmas\n"));
        self::assertFileExists($service->getTargetFile());
        self::assertSame("2026-12-25,Christmas\n", file_get_contents($service->getTargetFile()));
    }

    public function testImportHolidaysFromCsvMissingFileThrows(): void
    {
        $this->expectException(InvalidHolidayCsvException::class);
        $this->expectExceptionMessage('CSV file not found');
        $this->newService()->importHolidaysFromCsv();
    }

    public function testImportHolidaysFromCsvPopulatesStagingTable(): void
    {
        $service = $this->newService();
        $service->uploadCsv($this->uploadedCsv(<<<CSV
            date,description
            2026-12-25,Christmas
            2026-12-26,Boxing Day
            CSV));
        $service->importHolidaysFromCsv();
        $rows = $this->connection->fetchAllAssociative('SELECT date, description, source FROM calendar_external ORDER BY date');
        self::assertSame([
            ['date' => '2026-12-25', 'description' => 'Christmas', 'source' => 'csv'],
            ['date' => '2026-12-26', 'description' => 'Boxing Day', 'source' => 'csv'],
        ], $rows);
    }

    public function testImportHolidaysFromCsvDbalErrorWrapped(): void
    {
        $service = $this->newService();
        $service->uploadCsv($this->uploadedCsv("2026-12-25,X\n"));

        $failing = $this->createMock(Connection::class);
        $failing->method('transactional')->willThrowException($this->createMock(DbalException::class));

        $failingService = $this->newService($failing);
        // Move the CSV path's targetFile to align with the new service's siteDir (same instance).
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to import holidays into the staging table');
        $failingService->importHolidaysFromCsv();
    }

    public function testPublishHolidayEventsCopiesStagedRowsToCalendar(): void
    {
        $service = $this->newService();
        $service->uploadCsv($this->uploadedCsv("date,description\n2026-12-25,Christmas\n"));
        $service->importHolidaysFromCsv();
        $service->publishHolidayEvents();

        $events = $this->connection->fetchAllAssociative('SELECT pc_catid, pc_title, pc_time, pc_eventDate, pc_facility, pc_sharing FROM openemr_postcalendar_events');
        self::assertCount(1, $events);
        // SQLite returns DB integers as strings via PDO; compare loosely.
        self::assertEquals(HolidayService::CATEGORY_HOLIDAY, $events[0]['pc_catid']);
        self::assertSame('Christmas', $events[0]['pc_title']);
        self::assertSame('2026-05-21 12:00:00', $events[0]['pc_time']);
        self::assertSame('2026-12-25', $events[0]['pc_eventDate']);
        self::assertEquals(0, $events[0]['pc_facility']);
        self::assertEquals(1, $events[0]['pc_sharing']);
    }

    public function testPublishHolidayEventsClearsPreviousHolidaysEvenWhenEmpty(): void
    {
        $service = $this->newService();
        // Pre-seed one stale holiday event.
        $this->connection->insert('openemr_postcalendar_events', [
            'pc_catid' => HolidayService::CATEGORY_HOLIDAY,
            'pc_title' => 'Stale Holiday',
            'pc_eventDate' => '2026-01-01',
        ]);
        // Empty staging table: publish should still wipe the stale event.
        $service->publishHolidayEvents();
        $count = $this->connection->fetchOne('SELECT COUNT(*) FROM openemr_postcalendar_events WHERE pc_catid = ?', [HolidayService::CATEGORY_HOLIDAY]);
        self::assertEquals(0, $count);
    }

    public function testPublishHolidayEventsDbalErrorWrapped(): void
    {
        $failing = $this->createMock(Connection::class);
        $failing->method('transactional')->willThrowException($this->createMock(DbalException::class));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to publish holiday events');
        $this->newService($failing)->publishHolidayEvents();
    }

    public function testUploadAndSyncRunsTheWholePipeline(): void
    {
        $service = $this->newService();
        $service->uploadAndSync($this->uploadedCsv("date,description\n2026-12-25,Christmas\n"));
        $event = $this->connection->fetchAssociative('SELECT pc_title, pc_eventDate FROM openemr_postcalendar_events');
        self::assertSame(['pc_title' => 'Christmas', 'pc_eventDate' => '2026-12-25'], $event);
    }

    public function testUploadAndSyncDbalErrorWrapped(): void
    {
        $failing = $this->createMock(Connection::class);
        // First call inside transactional() throws to exercise the wrapper.
        $failing->method('transactional')->willThrowException($this->createMock(DbalException::class));
        $service = $this->newService($failing);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to apply holidays to the calendar');
        $service->uploadAndSync($this->uploadedCsv("2026-12-25,X\n"));
    }

    public function testGetHolidaysByDateRangeReturnsMatchingRows(): void
    {
        $this->seedHolidayEvent('2026-12-25', 'Christmas');
        $this->seedHolidayEvent('2026-12-26', 'Boxing Day');
        $this->seedHolidayEvent('2027-01-01', 'New Year');
        $service = $this->newService();
        self::assertSame(
            ['2026-12-25', '2026-12-26'],
            $service->getHolidaysByDateRange('2026-12-01', '2026-12-31'),
        );
    }

    public function testGetHolidaysByDateRangeDbalErrorWrapped(): void
    {
        $failing = $this->createMock(Connection::class);
        $failing->method('fetchAllAssociative')->willThrowException($this->createMock(DbalException::class));
        $service = $this->newService($failing);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to look up holidays');
        $service->getHolidaysByDateRange('2026-01-01', '2026-12-31');
    }

    public function testIsHolidayTrueForKnownDate(): void
    {
        $this->seedHolidayEvent('2026-12-25', 'Christmas');
        self::assertTrue($this->newService()->isHoliday('2026-12-25'));
    }

    public function testIsHolidayFalseForUnknownDate(): void
    {
        $this->seedHolidayEvent('2026-12-25', 'Christmas');
        self::assertFalse($this->newService()->isHoliday('2026-07-04'));
    }

    public function testIsHolidayNormalizesSlashDates(): void
    {
        $this->seedHolidayEvent('2026-12-25', 'Christmas');
        self::assertTrue($this->newService()->isHoliday('2026/12/25'));
    }

    public function testIsHolidayCachesAcrossCalls(): void
    {
        $service = $this->newService();
        $this->seedHolidayEvent('2026-12-25', 'Christmas');
        self::assertTrue($service->isHoliday('2026-12-25'));
        // Add another row directly; the cached set should not see it.
        $this->seedHolidayEvent('2027-01-01', 'New Year');
        self::assertFalse($service->isHoliday('2027-01-01'));
    }

    public function testIsHolidayDbalErrorWrapped(): void
    {
        $failing = $this->createMock(Connection::class);
        $failing->method('fetchAllAssociative')->willThrowException($this->createMock(DbalException::class));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to look up holidays');
        $this->newService($failing)->isHoliday('2026-12-25');
    }

    private function seedHolidayEvent(string $date, string $title): void
    {
        $this->connection->insert('openemr_postcalendar_events', [
            'pc_catid' => HolidayService::CATEGORY_HOLIDAY,
            'pc_title' => $title,
            'pc_eventDate' => $date,
        ]);
    }
}
