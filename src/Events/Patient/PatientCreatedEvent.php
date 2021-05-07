<?php

/**
 * PatientCreatedEvent
 *
 * This event is fired when a patient is created so modules can
 * listen for creation of a patient and perform additional
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

class PatientCreatedEvent extends Event
{
    /**
     * This event is triggered after a patient has been created, and an assoc
     * array of new patient data is passed to the event object
     */
    const EVENT_HANDLE = 'patient.created';

    private $patientData;

    /**
     * PatientUpdatedEvent constructor takes an array
     * of key/value pairs that represent fields of the patient_data
     * table
     *
     * @param array $patientData
     */
    public function __construct(array $patientData)
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
