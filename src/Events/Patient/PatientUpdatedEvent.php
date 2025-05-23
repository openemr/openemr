<?php

/**
 * PatientUpdatedEvent
 *
 * This event is fired when a patient is updated so modules can
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

use Symfony\Contracts\EventDispatcher\Event;

class PatientUpdatedEvent extends Event
{
    /**
     * This event is triggered after a patient has been updated, and an assoc
     * array of new patient data is passed to the event object
     */
    const EVENT_HANDLE = 'patient.updated';

    private $dataBeforeUpdate;
    private $newPatientData;

    /**
     * PatientUpdatedEvent constructor.
     * @param $dataBeforeUpdate
     * @param $newPatientData
     */
    public function __construct($dataBeforeUpdate, $newPatientData)
    {
        $this->dataBeforeUpdate = $dataBeforeUpdate;
        $this->newPatientData = $newPatientData;
    }

    /**
     * @return mixed
     */
    public function getDataBeforeUpdate()
    {
        return $this->dataBeforeUpdate;
    }

    /**
     * @param mixed $dataBeforeUpdate
     */
    public function setDataBeforeUpdate($dataBeforeUpdate): void
    {
        $this->dataBeforeUpdate = $dataBeforeUpdate;
    }

    /**
     * @return mixed
     */
    public function getNewPatientData()
    {
        return $this->newPatientData;
    }

    /**
     * @param mixed $newPatientData
     */
    public function setNewPatientData($newPatientData): void
    {
        $this->newPatientData = $newPatientData;
    }
}
