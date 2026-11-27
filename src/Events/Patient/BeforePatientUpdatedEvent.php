<?php

/**
 * BeforePatientUpdatedEvent
 *
 * This event is fired before a patient is updated so modules can
 * listen for changes in patient data and perform additional
 * processing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Patient;

use Symfony\Contracts\EventDispatcher\Event;

class BeforePatientUpdatedEvent extends Event
{
    /**
     * This event is triggered before a patient has been updated, and an assoc
     * array of new patient data is passed to the event object
     */
    const EVENT_HANDLE = 'patient.before-updated';

    /**
     * @param array<mixed> $patientData
     */
    public function __construct(private array $patientData)
    {
    }

    /**
     * @return array<mixed>
     */
    public function getPatientData(): array
    {
        return $this->patientData;
    }

    /**
     * @param array<mixed> $patientData
     */
    public function setPatientData(array $patientData): void
    {
        $this->patientData = $patientData;
    }
}
