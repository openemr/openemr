<?php

/**
 * PatientUpdatedAuxEvent
 *
 * This event is fired after a patient is updated so modules can
 * listen for update of a patient and perform additional
 * processing, or modify insert data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2024 Omegasystems Group <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient;

use Symfony\Contracts\EventDispatcher\Event;

class PatientUpdatedEventAux extends Event
{
    /**
     * This event is triggered after a patient has been updated, and an assoc
     * array of new patient data is passed to the event object
     */
    const EVENT_HANDLE = 'patient.updated.aux';

    private $updatedPatientData;
    private $pid;

    public function __construct($pid, $updatedData)
    {
        $this->updatedPatientData = $updatedData;
        $this->pid = $pid;
    }

    /**
     * @return mixed
     */
    public function getUpdatedPatientData()
    {
        $pid = array(
            "pid" => $this->pid
        );

        $this->updatedPatientData = array_merge($this->updatedPatientData, $pid);
        return $this->updatedPatientData;
    }
}
