<?php

/**
 * Database tests for setting the appointment status the fee sheet offered after Save.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\FeeSheetAppointmentStatusPrompt;
use OpenEMR\Billing\FeeSheetAppointmentStatusSetter;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AppointmentService;
use PHPUnit\Framework\TestCase;

class FeeSheetAppointmentStatusSetterTest extends TestCase
{
    private const DATE = '2026-10-22';

    /** A pid and encounter no real records have; the rows are this test's. */
    private int $pid = 0;

    private int $encounter = 0;

    private int $eid = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pid = random_int(900_000_000, 999_999_999);
        $this->encounter = $this->pid - 1;
        // The Flow Board history is only written for an appointment with a patient record.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex) VALUES (?, ?, ?, 'Cody', 'Coder', '1970-01-01', 'Female')",
            [UuidRegistry::getRegistryForTable('patient_data')->createUuid(), $this->pid, (string) $this->pid]
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO form_encounter (date, reason, pid, encounter, pc_catid) VALUES (?, ?, ?, ?, 5)',
            [self::DATE . ' 00:00:00', 'test', $this->pid, $this->encounter]
        );
        $this->eid = QueryUtils::sqlInsert(
            'INSERT INTO openemr_postcalendar_events (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_eventDate,'
            . " pc_startTime, pc_apptstatus, pc_recurrtype) VALUES (5, 0, 1, ?, 'Office Visit', ?, '10:30:00', '@', 0)",
            [$this->pid, self::DATE]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM patient_tracker_element WHERE pt_tracker_id IN (SELECT id FROM patient_tracker WHERE pid = ?)',
            [$this->pid]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_tracker WHERE pid = ?', [$this->pid]);
        QueryUtils::sqlStatementThrowException('DELETE FROM openemr_postcalendar_events WHERE pc_pid = ?', [$this->pid]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [$this->pid]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [$this->pid]);
        parent::tearDown();
    }

    public function testSetsTheOfferedAppointmentAndRecordsWhoOnTheFlowBoard(): void
    {
        $this->assertTrue($this->setter()->set($this->pid, $this->encounter, $this->eid, 'coder'));

        $this->assertSame('$', $this->appointmentStatus());
        $this->assertSame(
            [['encounter' => (string) $this->encounter, 'original_user' => 'coder', 'status' => '$', 'user' => 'coder']],
            array_map(
                static fn (array $row): array => array_map(static fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $row),
                QueryUtils::fetchRecords(
                    'SELECT t.encounter, t.original_user, e.status, e.user FROM patient_tracker AS t'
                    . ' JOIN patient_tracker_element AS e ON e.pt_tracker_id = t.id WHERE t.pid = ?',
                    [$this->pid]
                )
            )
        );
    }

    public function testRefusesAnotherAppointment(): void
    {
        $this->assertFalse($this->setter()->set($this->pid, $this->encounter, $this->eid + 1, 'coder'));
        $this->assertSame('@', $this->appointmentStatus());
    }

    public function testRefusesAnEncounterOfAnotherPatient(): void
    {
        $this->assertFalse($this->setter()->set($this->pid + 1, $this->encounter, $this->eid, 'coder'));
        $this->assertSame('@', $this->appointmentStatus());
    }

    public function testRefusesAnAppointmentNoLongerAskedAbout(): void
    {
        QueryUtils::sqlStatementThrowException("UPDATE openemr_postcalendar_events SET pc_apptstatus = 'x' WHERE pc_eid = ?", [$this->eid]);

        $this->assertFalse($this->setter()->set($this->pid, $this->encounter, $this->eid, 'coder'));
        $this->assertSame('x', $this->appointmentStatus());
    }

    private function setter(): FeeSheetAppointmentStatusSetter
    {
        return new FeeSheetAppointmentStatusSetter(new FeeSheetAppointmentStatusPrompt('$'), new AppointmentService());
    }

    private function appointmentStatus(): string
    {
        $status = QueryUtils::fetchSingleValue('SELECT pc_apptstatus FROM openemr_postcalendar_events WHERE pc_eid = ?', 'pc_apptstatus', [$this->eid]);

        return is_string($status) ? $status : '';
    }
}
