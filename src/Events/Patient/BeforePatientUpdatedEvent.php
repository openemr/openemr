<?php

/**
 * BeforePatientUpdatedEvent
 *
 * This event is fired before a patient is updated so modules can
 * listen for changes in patient data and perform additional
 * processing.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient;

use Symfony\Component\EventDispatcher\Event;

class BeforePatientUpdatedEvent extends Event
{
    /**
     * This event is triggered before a patient has been updated, and an assoc
     * array of new patient data is passed to the event object
     */
    const EVENT_HANDLE = 'patient.before-updated';

    private $patientData;

    /**
     * BeforePatientUpdatedEvent constructor.
     * @param $patientData
     */
    public function __construct($patientData)
    {
        $this->patientData = $patientData;
    }

    /**
     * @return mixed
     */
    public function getPatientData()
    {
        return $this->patientData;
    }

    /**
     * @param mixed $patientData
     */
    public function setPatientData(array $patientData): void
    {
        $this->patientData = $patientData;
    }
}
