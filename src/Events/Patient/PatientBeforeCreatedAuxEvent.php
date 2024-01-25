<?php

/**
 * BeforePatientCreatedAuxEvent
 *
 * This event is fired before a patient is created so modules can
 * listen for creation of a patient and perform additional
 * processing, or modify insert data.
 *
 * The difference between this event and BeforePatientCreatedEvent
 * is that, it'll give users who attach different tables to the
 * without saving in the patient_data table save their records as well.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2024 Omegasystems Group <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient;

use Symfony\Contracts\EventDispatcher\Event;

class PatientBeforeCreatedAuxEvent extends Event
{
    /**
     * This event is triggered before a patient has been created, and an assoc
     * array of new patient data is passed to the event object
     */
    const EVENT_HANDLE = 'patient.before-created-aux';

    private $patientData;
    private $pid;

    /**
     * BeforePatientUpdatedEvent constructor takes an array
     * of key/value pairs that represent fields of the patient_data
     * table
     *
     * @param array $patientData
     */
    public function __construct($pid, array $patientData)
    {
        $this->patientData = $patientData;
        $this->pid = $pid;
    }

    /**
     * @return mixed
     */
    public function getPatientData()
    {
        $pid = array('pid' => $this->pid);
        $this->patientData = array_merge($pid, $this->patientData);

        return $this->patientData;
    }

    /**
     * @param mixed $patientData
     * @param $pid
     */
    public function setPatientData($pid, array $patientData): void
    {
        $this->patientData = $patientData;
    }
}
