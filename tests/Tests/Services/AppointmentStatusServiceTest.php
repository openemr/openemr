<?php

/**
 * AppointmentStatusServiceTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\AppointmentStatusService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AppointmentStatusServiceTest extends TestCase
{
    // No patient_data row is needed: every table the code touches keys on the pid alone.
    private const TEST_PID = 900000001;

    private const APPT_DATE = '2030-03-14';

    private const SWITCH = 'gbl_auto_update_appt_status';

    private const SESSION_USER = 'appt-status-test-user';

    /**
     * Globals and session keys the tests overwrite, with their values before the test
     * (null when absent).
     *
     * @var array<string, mixed>
     */
    private array $savedGlobals = [];

    /**
     * @var array<string, mixed>
     */
    private array $savedSession = [];

    private int $facilityId;

    /**
     * Loads the legacy delegator, which also brings in the tracker and encounter functions the
     * service calls.
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../library/appointment_status.inc.php';
    }

    /**
     * Starts from no rows for the test pid, the switch on, a known session user and the
     * appointment comment allowed as visit reason.
     */
    protected function setUp(): void
    {
        $this->deleteFixtureRows();

        $globals = OEGlobalsBag::getInstance();
        foreach ([self::SWITCH, 'auto_create_prevent_reason'] as $key) {
            $this->savedGlobals[$key] = $globals->get($key);
        }
        $globals->set(self::SWITCH, '1');
        $globals->set('auto_create_prevent_reason', '0');

        $adminId = QueryUtils::fetchSingleValue("SELECT `id` FROM `users` WHERE `username` = 'admin'", 'id');
        $this->assertIsNumeric($adminId, 'the admin user of the dev and CI installs is missing');
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (['authUser', 'authUserID'] as $key) {
            $this->savedSession[$key] = $session->has($key) ? $session->get($key) : null;
        }
        $session->set('authUser', self::SESSION_USER);
        $session->set('authUserID', (int) $adminId);

        $facilityId = QueryUtils::fetchSingleValue("SELECT `id` FROM `facility` ORDER BY `id` LIMIT 1", 'id');
        $this->assertIsNumeric($facilityId, 'no facility row to attach the encounter to');
        $this->facilityId = (int) $facilityId;
    }

    /**
     * Deletes the rows written for the test pid and puts back the globals and session keys.
     */
    protected function tearDown(): void
    {
        $this->deleteFixtureRows();

        // OEGlobalsBag::remove() leaves the $GLOBALS copy that get() reads, so an absent key
        // goes back as null, which get() answers the same way.
        $globals = OEGlobalsBag::getInstance();
        foreach ($this->savedGlobals as $key => $value) {
            $globals->set($key, $value);
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach ($this->savedSession as $key => $value) {
            if ($value === null) {
                $session->remove($key);
            } else {
                $session->set($key, $value);
            }
        }
    }

    /**
     * @return array<string, array{?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function switchOffProvider(): array
    {
        return [
            'absent (null)' => [null],
            'empty string' => [''],
            'zero' => ['0'],
        ];
    }

    /**
     * With the switch off nothing is written: no status, no tracker row, no encounter.
     */
    #[Test]
    #[DataProvider('switchOffProvider')]
    public function switchOffLeavesTheAppointmentAlone(?string $value): void
    {
        OEGlobalsBag::getInstance()->set(self::SWITCH, $value);
        $eid = $this->insertAppointment('10:00:00', '-');

        $this->update(self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('-', $this->appointmentStatus($eid));
        $this->assertSame([], $this->trackerRows());
        $this->assertSame([], $this->encounterRows());
    }

    /**
     * With the switch on the appointment gets the new status, a tracker row is opened for it and
     * the encounter created for the date is linked.
     */
    #[Test]
    public function switchOnMovesTheAppointmentAndOpensTheTracker(): void
    {
        $eid = $this->insertAppointment('10:00:00', '-', room: 'Room 7');

        $this->update(self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('<', $this->appointmentStatus($eid));

        $encounters = $this->encounterRows();
        $this->assertCount(1, $encounters, 'the date had no encounter, so one is created');
        $encounter = $encounters[0];
        $this->assertSame(self::APPT_DATE . ' 00:00:00', $encounter['date']);
        $this->assertSame('Follow-up visit', $encounter['reason']);
        $this->assertSame($this->facilityId, $encounter['facility_id']);
        $this->assertSame(77, $encounter['billing_facility']);
        $this->assertSame(5, $encounter['provider_id']);
        $this->assertSame(9, $encounter['pc_catid']);

        $trackers = $this->trackerRows();
        $this->assertCount(1, $trackers);
        $tracker = $trackers[0];
        $this->assertSame($eid, $tracker['eid']);
        $this->assertSame(self::APPT_DATE, $tracker['apptdate']);
        $this->assertSame('10:00:00', $tracker['appttime']);
        $this->assertSame(self::SESSION_USER, $tracker['original_user']);
        $this->assertSame($encounter['encounter'], $tracker['encounter']);
        $this->assertSame('<', $tracker['status']);
        $this->assertSame('Room 7', $tracker['room']);
        $this->assertSame(self::SESSION_USER, $tracker['user']);
    }

    /**
     * A '$' appointment is never changed.
     */
    #[Test]
    public function checkedOutAppointmentIsLeftAlone(): void
    {
        $eid = $this->insertAppointment('10:00:00', '$');

        $this->update(self::TEST_PID, self::APPT_DATE, '>');

        $this->assertSame('$', $this->appointmentStatus($eid));
        $this->assertSame([], $this->trackerRows());
    }

    /**
     * A '>' appointment is not moved back to '<'.
     */
    #[Test]
    public function checkedOutAppointmentDoesNotGoBackToTheExamRoom(): void
    {
        $eid = $this->insertAppointment('10:00:00', '>');

        $this->update(self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('>', $this->appointmentStatus($eid));
        $this->assertSame([], $this->trackerRows());
    }

    /**
     * A '<' appointment can still move to '>'.
     */
    #[Test]
    public function examRoomAppointmentCanBeCheckedOut(): void
    {
        $eid = $this->insertAppointment('10:00:00', '<');

        $this->update(self::TEST_PID, self::APPT_DATE, '>');

        $this->assertSame('>', $this->appointmentStatus($eid));
        $this->assertCount(1, $this->trackerRows());
    }

    /**
     * Only the latest non-recurring appointment of the date changes.
     */
    #[Test]
    public function latestNonRecurringAppointmentOfTheDateIsUpdated(): void
    {
        $morning = $this->insertAppointment('09:00:00', '-');
        $afternoon = $this->insertAppointment('14:00:00', '-');
        $recurring = $this->insertAppointment('16:00:00', '-', recurring: true);
        $otherDay = $this->insertAppointment('18:00:00', '-', date: '2030-03-15');

        $this->update(self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('-', $this->appointmentStatus($morning));
        $this->assertSame('<', $this->appointmentStatus($afternoon));
        $this->assertSame('-', $this->appointmentStatus($recurring));
        $this->assertSame('-', $this->appointmentStatus($otherDay));
    }

    /**
     * On a start time tie the appointment with the highest pc_eid changes.
     */
    #[Test]
    public function sameStartTimePicksTheNewestAppointment(): void
    {
        $older = $this->insertAppointment('10:00:00', '-');
        $newer = $this->insertAppointment('10:00:00', '-');

        $this->update(self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('-', $this->appointmentStatus($older));
        $this->assertSame('<', $this->appointmentStatus($newer));
    }

    /**
     * A date without appointments changes nothing, not even on other days.
     */
    #[Test]
    public function dateWithoutAppointmentsChangesNothing(): void
    {
        $eid = $this->insertAppointment('10:00:00', '-', date: '2030-03-15');

        $this->update(self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('-', $this->appointmentStatus($eid));
        $this->assertSame([], $this->trackerRows());
        $this->assertSame([], $this->encounterRows());
    }

    /**
     * The .inc.php function still does the update, through the service.
     */
    #[Test]
    public function legacyFunctionDelegatesToTheService(): void
    {
        $eid = $this->insertAppointment('10:00:00', '-');

        updateAppointmentStatus((string) self::TEST_PID, self::APPT_DATE, '<');

        $this->assertSame('<', $this->appointmentStatus($eid));
        $this->assertCount(1, $this->trackerRows());
    }

    /**
     * @return array<string, array{mixed, mixed, mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusableArgumentsProvider(): array
    {
        return [
            'pid null' => [null, self::APPT_DATE, '<'],
            'pid array' => [[self::TEST_PID], self::APPT_DATE, '<'],
            'date null' => [self::TEST_PID, null, '<'],
            'status null' => [self::TEST_PID, self::APPT_DATE, null],
        ];
    }

    /**
     * Arguments the service's types reject make the .inc.php function return without touching
     * anything.
     */
    #[Test]
    #[DataProvider('unusableArgumentsProvider')]
    public function legacyFunctionIgnoresArgumentsTheServiceCannotTake(mixed $pid, mixed $encdate, mixed $newstatus): void
    {
        $eid = $this->insertAppointment('10:00:00', '-');

        updateAppointmentStatus($pid, $encdate, $newstatus);

        $this->assertSame('-', $this->appointmentStatus($eid));
        $this->assertSame([], $this->trackerRows());
    }

    /**
     * The call under test.
     */
    private function update(int|string $pid, string $encdate, string $newstatus): void
    {
        AppointmentStatusService::updateAppointmentStatus($pid, $encdate, $newstatus);
    }

    /**
     * Inserts one appointment for the test pid and returns its pc_eid.
     */
    private function insertAppointment(
        string $startTime,
        string $status,
        string $date = self::APPT_DATE,
        bool $recurring = false,
        string $room = '',
    ): int {
        return QueryUtils::sqlInsert(
            "INSERT INTO `openemr_postcalendar_events` SET `pc_catid` = 9, `pc_multiple` = 0, `pc_aid` = '5',"
            . " `pc_pid` = ?, `pc_title` = 'Office Visit', `pc_hometext` = 'Follow-up visit', `pc_eventDate` = ?,"
            . " `pc_startTime` = ?, `pc_recurrtype` = ?, `pc_apptstatus` = ?, `pc_facility` = ?,"
            . " `pc_billing_location` = 77, `pc_room` = ?",
            [(string) self::TEST_PID, $date, $startTime, $recurring ? 1 : 0, $status, $this->facilityId, $room]
        );
    }

    /**
     * Current pc_apptstatus of one appointment.
     */
    private function appointmentStatus(int $eid): string
    {
        $status = QueryUtils::fetchSingleValue(
            "SELECT `pc_apptstatus` FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?",
            'pc_apptstatus',
            [$eid]
        );
        $this->assertIsString($status);
        return $status;
    }

    /**
     * Tracker rows of the test pid joined to their last element.
     *
     * @return list<array<string, scalar>>
     */
    private function trackerRows(): array
    {
        return $this->scalarRows(QueryUtils::fetchRecords(
            "SELECT t.`eid`, t.`apptdate`, t.`appttime`, t.`original_user`, t.`encounter`, e.`status`, e.`room`, e.`user`"
            . " FROM `patient_tracker` t LEFT JOIN `patient_tracker_element` e"
            . " ON e.`pt_tracker_id` = t.`id` AND e.`seq` = t.`lastseq`"
            . " WHERE t.`pid` = ? ORDER BY t.`id`",
            [self::TEST_PID]
        ));
    }

    /**
     * @return list<array<string, scalar>>
     */
    private function encounterRows(): array
    {
        return $this->scalarRows(QueryUtils::fetchRecords(
            "SELECT `encounter`, `date`, `reason`, `facility_id`, `billing_facility`, `provider_id`, `pc_catid`"
            . " FROM `form_encounter` WHERE `pid` = ? ORDER BY `id`",
            [self::TEST_PID]
        ));
    }

    /**
     * Narrows fetched rows to non-NULL scalars (integer columns come back as int) so the
     * assertions compare exact values.
     *
     * @param array<mixed> $rows
     * @return list<array<string, scalar>>
     */
    private function scalarRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $this->assertIsArray($row);
            $clean = [];
            foreach ($row as $key => $value) {
                $this->assertIsString($key);
                $this->assertIsScalar($value, "column $key is NULL");
                $clean[$key] = $value;
            }
            $result[] = $clean;
        }
        return $result;
    }

    /**
     * Deletes every row the code under test can write for the test pid.
     */
    private function deleteFixtureRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE e FROM `patient_tracker_element` e JOIN `patient_tracker` t ON t.`id` = e.`pt_tracker_id`"
            . " WHERE t.`pid` = ?",
            [self::TEST_PID]
        );
        foreach (
            [
                "DELETE FROM `patient_tracker` WHERE `pid` = ?",
                "DELETE FROM `forms` WHERE `pid` = ?",
                "DELETE FROM `form_encounter` WHERE `pid` = ?",
                "DELETE FROM `openemr_postcalendar_events` WHERE `pc_pid` = ?",
            ] as $sql
        ) {
            QueryUtils::sqlStatementThrowException($sql, [self::TEST_PID]);
        }
    }
}
