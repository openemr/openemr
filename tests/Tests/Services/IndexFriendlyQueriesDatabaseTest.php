<?php

/**
 * Appointments found by patient, and the daily summary report's dates, read through indexes: the same
 * rows as before, without reading the whole table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\Patient\DuplicatePatientService;
use OpenEMR\Services\Patient\PatientMergeLog;
use OpenEMR\Services\Patient\PatientMergeService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

require_once dirname(__DIR__, 3) . '/library/appointments.inc.php';

/**
 * openemr_postcalendar_events.pc_pid is a varchar. Compared with a number, every row is read and
 * converted, index or not; compared with text, its index finds the patient's events. So the queries that
 * find a patient's events bind the pid as text. Each test here seeds 500 events for other patients so a
 * full read (of the table, or of the whole index) shows in the session's Handler_read_rnd_next and
 * Handler_read_next counts, and checks that a patient's events come back exactly as before.
 */
class IndexFriendlyQueriesDatabaseTest extends TestCase
{
    private const FILLER = 500;

    private int $pid;
    private int $otherPid;
    private string $uuid;
    private string $otherUuid;

    protected function setUp(): void
    {
        // fetchRecurrences() names a repeat from globals appointments.inc.php sets at its file scope. PHPUnit
        // loads test files inside a method, so those land there; the two this test's event uses are set here.
        $GLOBALS['REPEAT_FREQ'] ??= [1 => 'every'];
        $GLOBALS['REPEAT_FREQ_TYPE'] ??= [1 => 'week'];
        $next = QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data', 'next_pid');
        $this->assertIsNumeric($next);
        $this->pid = (int) $next;
        $this->otherPid = $this->pid + 1;
        $this->uuid = random_bytes(16);
        $this->otherUuid = random_bytes(16);
        foreach ([[$this->pid, $this->uuid], [$this->otherPid, $this->otherUuid]] as [$pid, $uuid]) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-index', 'Appointments', NOW())",
                [$pid, $uuid]
            );
        }
        // The patient's own events: one visit and one recurring; the other patient's visit; an event with no patient.
        $this->insertEvent((string) $this->pid, '2031-03-02', 0);
        $this->insertEvent((string) $this->pid, '2031-03-03', 1);
        $this->insertEvent((string) $this->otherPid, '2031-03-02', 0);
        $this->insertEvent('', '2031-03-02', 0);
        // Events for other patients, so reading the whole table shows.
        // pc_pid is varchar(11): the marker has to fit, or it's cut short and matches nothing.
        $this->insertEvent('tfx-filler', '2031-03-04', 0);
        for ($rows = 1; $rows < self::FILLER; $rows *= 2) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO openemr_postcalendar_events (uuid, pc_catid, pc_aid, pc_pid, pc_title, pc_time, pc_eventDate, pc_endDate, pc_startTime, pc_endTime, pc_recurrtype, pc_recurrspec, pc_apptstatus) "
                . "SELECT UNHEX(REPLACE(UUID(), '-', '')), pc_catid, pc_aid, pc_pid, pc_title, NOW(), pc_eventDate, pc_endDate, pc_startTime, pc_endTime, 0, '', pc_apptstatus "
                . "FROM openemr_postcalendar_events WHERE pc_pid = 'tfx-filler'"
            );
        }
        $filler = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM openemr_postcalendar_events WHERE pc_pid = 'tfx-filler'", 'n');
        $this->assertGreaterThanOrEqual(self::FILLER, (int) self::number($filler), 'the filler events are there, so a full read would show');
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM openemr_postcalendar_events WHERE pc_pid IN (?, ?, 'tfx-filler') OR pc_title = 'test-fixture-index'",
            [(string) $this->pid, (string) $this->otherPid]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid IN (?, ?)', [$this->pid, $this->otherPid]);
        $left = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM openemr_postcalendar_events WHERE pc_pid IN (?, ?, 'tfx-filler') OR pc_title = 'test-fixture-index'",
            'n',
            [(string) $this->pid, (string) $this->otherPid]
        );
        $this->assertSame(0, (int) self::number($left), 'the seeded events are removed');
    }

    public function testAPatientsEventsComeBackAsBefore(): void
    {
        $service = new AppointmentService();

        $this->assertCount(2, $service->getAppointmentsForPatient($this->pid));
        $this->assertCount(2, $service->getAppointmentsForPatient('0' . $this->pid), 'a pid with a leading zero, as a URL can give it');
        $this->assertSame([], $service->getAppointmentsForPatient('00'), "'00' names no patient, not the events stored with pc_pid '0'");
        $this->assertSame([], $service->getAppointmentsForPatient(' '), "' ' names no patient, not the event with none");
        $this->assertCount(2, $this->searchByPatient($service, $this->uuid));
        $this->assertCount(1, $this->searchByPatient($service, $this->otherUuid));
        $this->assertCount(0, $this->searchByPatient($service, random_bytes(16)), 'a patient who is not here');
        $this->assertCount(1, fetchRecurrences($this->pid), 'only the recurring one');
    }

    public function testFindingAPatientsEventsReadsThroughTheIndex(): void
    {
        $service = new AppointmentService();

        // The control: compared with a number, pc_pid is read row by row.
        $this->assertGreaterThanOrEqual(self::FILLER, $this->rowsScanned(fn(): array => QueryUtils::fetchRecords(
            'SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_pid = ?',
            [$this->pid]
        )), 'a number reads every event');
        $this->assertLessThan(self::FILLER, $this->rowsScanned(fn(): array => $service->getAppointmentsForPatient($this->pid)), 'getAppointmentsForPatient');
        $this->assertLessThan(self::FILLER, $this->rowsScanned(fn(): array => $this->searchByPatient($service, $this->uuid)), 'search by puuid');
        $this->assertLessThan(self::FILLER, $this->rowsScanned(fn(): array => fetchRecurrences($this->pid)), 'fetchRecurrences');
        $this->assertLessThan(self::FILLER, $this->rowsScanned(fn(): array => fetchAppointments('2031-03-01', '2031-03-31', $this->pid)), 'fetchAppointments');
    }

    public function testCalendarPidIsThePlainNumber(): void
    {
        $this->assertSame('42', AppointmentService::calendarPid(42));
        $this->assertSame('42', AppointmentService::calendarPid('42'));
        $this->assertSame('42', AppointmentService::calendarPid('0042'));
        $this->assertSame('0', AppointmentService::calendarPid('000'));
        $this->assertSame('42', AppointmentService::calendarPid(' 42'), 'spaces around it');
        $this->assertSame('42', AppointmentService::calendarPid("42\n"), 'a newline after it');
        $this->assertSame('', AppointmentService::calendarPid(''), 'no patient');
        $this->assertSame('abc', AppointmentService::calendarPid(' abc '), 'other text, trimmed');
        $this->assertNull(AppointmentService::calendarPidOrNull(null), 'nothing matches no patient');
        $this->assertNull(AppointmentService::calendarPidOrNull(4.2), 'nor a float');
        $this->assertSame('42', AppointmentService::calendarPidOrNull('042'));
    }

    /**
     * A merge counts the source patient's events and moves them to the target through pc_pid's index. Its
     * audit logger is a stand-in, so the test writes no log rows.
     */
    public function testAMergeFindsAndMovesThePatientsEventsThroughTheIndex(): void
    {
        $merge = new PatientMergeService(
            $this->createMock(EventAuditLogger::class),
            new Session(new MockArraySessionStorage()),
            new NullLogger(),
            new DuplicatePatientService(ServiceContainer::getClock()),
            sys_get_temp_dir() . '/openemr-merge-test-documents',
        );
        $countRows = new \ReflectionMethod($merge, 'countRows');
        $updateRows = new \ReflectionMethod($merge, 'updateRows');
        $service = new AppointmentService();

        $this->assertLessThan(self::FILLER, $this->rowsScanned(fn(): mixed => $countRows->invoke($merge, 'openemr_postcalendar_events', 'pc_pid', $this->pid)), 'counting the events');
        $this->assertSame(2, $countRows->invoke($merge, 'openemr_postcalendar_events', 'pc_pid', $this->pid));
        $this->assertLessThan(self::FILLER, $this->rowsScanned(fn(): mixed => $updateRows->invoke($merge, 'openemr_postcalendar_events', 'pc_pid', $this->pid, $this->otherPid, new PatientMergeLog())), 'moving them');
        $this->assertCount(0, $service->getAppointmentsForPatient($this->pid));
        $this->assertCount(3, $service->getAppointmentsForPatient($this->otherPid), 'the other patient has both, and their own');
    }

    /**
     * The upgrade writes a pc_pid of digits stored another way (the REST API stored "05" as given) as the
     * plain number, so the text lookups find those events as the numeric comparison did. It runs on every
     * upgrade, even where a pc_pid index exists already (a site may have added its own): the upgrade file's
     * own block runs through SQLUpgradeService with the index in place.
     */
    public function testTheUpgradeWritesStoredPidsAsThePlainNumber(): void
    {
        $this->insertEvent('0' . $this->pid, '2031-03-05', 0);
        $this->insertEvent(' ' . $this->pid . ' ', '2031-03-06', 0);
        $service = new AppointmentService();
        $this->assertCount(2, $service->getAppointmentsForPatient($this->pid), 'the control: compared with text, those two are missed');
        $others = fn(): mixed => QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM openemr_postcalendar_events WHERE pc_pid IN ('', 'tfx-filler')", 'n');
        $othersBefore = $others();

        $this->assertCount(1, QueryUtils::fetchRecords("SHOW INDEX FROM openemr_postcalendar_events WHERE Key_name = 'pc_pid'"), 'the index is there already');
        $this->runUpgradeBlock('#IfTable openemr_postcalendar_events');

        $this->assertCount(4, $service->getAppointmentsForPatient($this->pid));
        $this->assertSame($othersBefore, $others(), 'no patient, and text that is not a number, stay as they were');
    }

    public function testBillingDatesAreIndexed(): void
    {
        $index = QueryUtils::fetchRecords("SHOW INDEX FROM billing WHERE Key_name = 'date'");
        $this->assertCount(1, $index);
        $this->assertSame('date', $index[0]['Column_name'] ?? null);
    }

    /**
     * The daily summary report's ranges match the DATE() comparisons they replace, at the edges of a day:
     * on a datetime, a day is [day, next day); on a date column DATE() changes nothing.
     */
    public function testDateRangesMatchTheDaysTheyReplace(): void
    {
        QueryUtils::sqlStatementThrowException('CREATE TEMPORARY TABLE index_date_probe (d DATETIME, ev DATE)');
        try {
            foreach (['2031-01-10 23:59:59', '2031-01-11 00:00:00', '2031-01-11 12:00:00', '2031-01-11 23:59:59', '2031-01-12 00:00:00', '2031-01-12 08:30:00'] as $moment) {
                QueryUtils::sqlStatementThrowException('INSERT INTO index_date_probe (d, ev) VALUES (?, DATE(?))', [$moment, $moment]);
            }
            $count = fn(string $where, array $binds): int => (int) self::number(QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM index_date_probe WHERE $where", 'n', $binds));

            $this->assertSame(3, $count('DATE(d) = ?', ['2031-01-11']), 'the control');
            $this->assertSame(3, $count('d >= ? AND d < DATE_ADD(?, INTERVAL 1 DAY)', ['2031-01-11', '2031-01-11']));
            $this->assertSame(5, $count('DATE(d) BETWEEN ? AND ?', ['2031-01-11', '2031-01-12']), 'the control');
            $this->assertSame(5, $count('d >= ? AND d < DATE_ADD(?, INTERVAL 1 DAY)', ['2031-01-11', '2031-01-12']));
            $this->assertSame($count('DATE(ev) = ?', ['2031-01-11']), $count('ev = ?', ['2031-01-11']));
            $this->assertSame($count('DATE(ev) BETWEEN ? AND ?', ['2031-01-10', '2031-01-11']), $count('ev BETWEEN ? AND ?', ['2031-01-10', '2031-01-11']));
        } finally {
            QueryUtils::sqlStatementThrowException('DROP TEMPORARY TABLE IF EXISTS index_date_probe');
        }
    }

    /**
     * @return array<mixed>
     */
    private function searchByPatient(AppointmentService $service, string $uuid): array
    {
        $data = $service->search(['puuid' => new TokenSearchField('puuid', [new TokenSearchValue(\OpenEMR\Common\Uuid\UuidRegistry::uuidToString($uuid), null, true)])])->getData();
        return is_array($data) ? $data : [];
    }

    /** Runs one block of this release's upgrade file, as written there, through SQLUpgradeService. */
    private function runUpgradeBlock(string $guard): void
    {
        $lines = file(dirname(__DIR__, 3) . '/sql/8_5_0-to-8_6_0_upgrade.sql', FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($lines);
        $start = array_search($guard, $lines, true);
        $this->assertIsInt($start, 'the block is in the upgrade file');
        $end = array_search('#EndIf', array_slice($lines, $start, null, true), true);
        $this->assertIsInt($end);
        $dir = sys_get_temp_dir() . '/openemr-upgrade-block-' . bin2hex(random_bytes(4));
        $this->assertTrue(mkdir($dir));
        try {
            file_put_contents("$dir/block.sql", implode("\n", array_slice($lines, $start, $end - $start + 1)) . "\n");
            $upgrade = new SQLUpgradeService();
            $upgrade->setRenderOutputToScreen(false);
            $upgrade->setThrowExceptionOnError(true);
            $upgrade->upgradeFromSqlFile('block.sql', $dir);
        } finally {
            if (is_file("$dir/block.sql")) {
                unlink("$dir/block.sql");
            }
            rmdir($dir);
        }
    }

    /** Rows read one after another while $run runs: table scans (Handler_read_rnd_next) and index scans (Handler_read_next). */
    private function rowsScanned(callable $run): int
    {
        $before = $this->handlerReads();
        $run();
        return $this->handlerReads() - $before;
    }

    private function handlerReads(): int
    {
        $total = 0;
        foreach (QueryUtils::fetchRecords("SHOW SESSION STATUS WHERE Variable_name IN ('Handler_read_rnd_next', 'Handler_read_next')") as $row) {
            $total += (int) self::number($row['Value'] ?? null);
        }
        return $total;
    }

    private function insertEvent(string $pid, string $date, int $recurrtype): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO openemr_postcalendar_events (uuid, pc_catid, pc_aid, pc_pid, pc_title, pc_time, pc_eventDate, pc_endDate, pc_startTime, pc_endTime, pc_recurrtype, pc_recurrspec, pc_apptstatus) "
            . "VALUES (?, 5, 1, ?, 'test-fixture-index', NOW(), ?, ?, '10:00:00', '10:15:00', ?, ?, '-')",
            [random_bytes(16), $pid, $date, $recurrtype > 0 ? '2031-04-01' : $date, $recurrtype, $recurrtype > 0 ? 'a:6:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"1";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";s:6:"exdate";s:0:"";}' : '']
        );
    }

    private static function number(mixed $value): int|float|string
    {
        self::assertIsNumeric($value);
        return $value;
    }
}
