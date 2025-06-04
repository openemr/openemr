<?php

/**
 * FacilityUpdatedEvent
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2020 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Facility;

use Symfony\Contracts\EventDispatcher\Event;

class FacilityUpdatedEvent extends Event
{
    /**
     * This event is triggered after a facility has been updated, and an assoc
     * array of new facility data is passed to the event object
     */
    const EVENT_HANDLE = 'facility.updated';

    private $dataBeforeUpdate;
    private $newFacilityData;

    /**
     * FacilityUpdatedEvent constructor.
     * @param $dataBeforeUpdate
     * @param $newFacilityData
     */
    public function __construct($dataBeforeUpdate, $newFacilityData)
    {
        $this->dataBeforeUpdate = $dataBeforeUpdate;
        $this->newFacilityData = $newFacilityData;
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
    public function getNewFacilityData()
    {
        return $this->newFacilityData;
    }

    /**
     * @param mixed $newFacilityData
     */
    public function setNewFacilityData($newFacilityData): void
    {
        $this->newFacilityData = $newFacilityData;
    }
}
