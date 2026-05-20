<?php

/**
 * AppointmentFilterEvent allows you to filter the patient appointment records and add any additional data elements or
 * properties to appointments that will be sent to the patient appointment templates
 * @see templates/portal/appointment-item.html.twig for reference
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientPortal;

use Symfony\Contracts\EventDispatcher\Event;

class AppointmentFilterEvent extends Event
{
    /**
     * ,
    ['dbRecord' => $row, 'appointment' => $formattedRecord]
     */
    public const EVENT_NAME = 'home.appointment.filter';

    /**
     * @var array<string, mixed>
     */
    private array $dbRecord;

    /**
     * @var array<string, mixed>
     */
    private array $appointment;

    /**
     * @param array<string, mixed> $dbRecord
     * @param array<string, mixed> $appointment
     */
    public function __construct(array $dbRecord, array $appointment)
    {
        $this->setDbRecord($dbRecord);
        $this->setAppointment($appointment);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDbRecord(): array
    {
        return $this->dbRecord;
    }

    /**
     * @param array<string, mixed> $dbRecord
     * @return AppointmentFilterEvent
     */
    public function setDbRecord(array $dbRecord): AppointmentFilterEvent
    {
        $this->dbRecord = $dbRecord;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAppointment(): array
    {
        return $this->appointment;
    }

    /**
     * @param array<string, mixed> $appointment
     * @return AppointmentFilterEvent
     */
    public function setAppointment(array $appointment): AppointmentFilterEvent
    {
        $this->appointment = $appointment;
        return $this;
    }
}
