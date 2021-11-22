<?php

/**
 * AppointmentFilterEvent allows you to filter the patient appointment records and add any additional data elements or
 * properties to appointments that will be sent to the patient appointment templates
 * @see templates/portal/appointment-item.html.twig for reference
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientPortal;

use Symfony\Component\EventDispatcher\Event;

class AppointmentFilterEvent extends Event
{
    /**
     * ,
    ['dbRecord' => $row, 'appointment' => $formattedRecord]
     */
    public const EVENT_NAME = 'home.appointment.filter';

    /**
     * @var array
     */
    private $dbRecord;

    /**
     * @var array
     */
    private $appointment;

    public function __construct($dbRecord, $appointment)
    {
        $this->setDbRecord($dbRecord);
        $this->setAppointment($appointment);
    }

    /**
     * @return array
     */
    public function getDbRecord(): array
    {
        return $this->dbRecord;
    }

    /**
     * @param array $dbRecord
     * @return AppointmentFilterEvent
     */
    public function setDbRecord(array $dbRecord): AppointmentFilterEvent
    {
        $this->dbRecord = $dbRecord;
        return $this;
    }

    /**
     * @return array
     */
    public function getAppointment(): array
    {
        return $this->appointment;
    }

    /**
     * @param array $appointment
     * @return AppointmentFilterEvent
     */
    public function setAppointment(array $appointment): AppointmentFilterEvent
    {
        $this->appointment = $appointment;
        return $this;
    }
}
