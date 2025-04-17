<?php

/**
 * FacilityCreatedEvent
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2020 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Facility;

use Symfony\Contracts\EventDispatcher\Event;

class FacilityCreatedEvent extends Event
{
    /**
     * This event is triggered after a facility has been created, and an assoc
     * array of new facility data is passed to the event object
     */
    const EVENT_HANDLE = 'facility.created';

    private $facilityData;

    /**
     * FacilityUpdatedEvent constructor.
     * @param $facilityData
     */
    public function __construct($facilityData)
    {
        $this->facilityData = $facilityData;
    }

    /**
     * @return mixed
     */
    public function getFacilityData()
    {
        return $this->facilityData;
    }

    /**
     * @param mixed $facilityData
     */
    public function setFacilityData($facilityData): void
    {
        $this->facilityData = $facilityData;
    }
}
