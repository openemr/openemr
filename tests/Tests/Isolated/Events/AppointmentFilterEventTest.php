<?php

/**
 * Isolated tests for AppointmentFilterEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Events;

use OpenEMR\Events\PatientPortal\AppointmentFilterEvent;
use PHPUnit\Framework\TestCase;

class AppointmentFilterEventTest extends TestCase
{
    public function testConstructorSetsPropertiesViaSetters(): void
    {
        $dbRecord = ['pc_eid' => '1', 'pc_catid' => '5'];
        $appointment = ['date' => '2026-01-15', 'provider' => 'Dr. Smith'];
        $event = new AppointmentFilterEvent($dbRecord, $appointment);

        $this->assertSame($dbRecord, $event->getDbRecord());
        $this->assertSame($appointment, $event->getAppointment());
    }

    public function testGetSetDbRecordRoundTrip(): void
    {
        $event = new AppointmentFilterEvent(['a' => 1], ['b' => 2]);
        $newRecord = ['pc_eid' => '99'];
        $event->setDbRecord($newRecord);

        $this->assertSame($newRecord, $event->getDbRecord());
    }

    public function testGetSetAppointmentRoundTrip(): void
    {
        $event = new AppointmentFilterEvent(['a' => 1], ['b' => 2]);
        $newAppointment = ['date' => '2026-06-01', 'status' => 'confirmed'];
        $event->setAppointment($newAppointment);

        $this->assertSame($newAppointment, $event->getAppointment());
    }

    public function testSetDbRecordReturnsFluent(): void
    {
        $event = new AppointmentFilterEvent([], []);
        $result = $event->setDbRecord(['x' => 1]);

        $this->assertSame($event, $result);
    }

    public function testSetAppointmentReturnsFluent(): void
    {
        $event = new AppointmentFilterEvent([], []);
        $result = $event->setAppointment(['y' => 2]);

        $this->assertSame($event, $result);
    }
}
