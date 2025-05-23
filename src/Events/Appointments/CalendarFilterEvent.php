<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Appointments;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event object for creating custom filters on calendar events
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class CalendarFilterEvent extends Event
{
    /**
     * The customWhereFilter event occurs in interface/main/calendar/modules/PostCalendar/pnuserapi.php and allows
     * filtering the display calendar events
     */
    const EVENT_HANDLE = 'calendar.customFilter';

    /**
     * @var string
     *
     * This is the custom filter that can add to the calendar event query
     */
    private $customWhereFilter = '1';

    /**
     * Get an string representing the calendar event filter
     *
     * @return string
     */
    public function getCustomWhereFilter()
    {
        return $this->customWhereFilter;
    }

    /**
     * @param $customWhereFilter
     *
     * Add a custom filter to the WHERE clause of calendar event query
     */
    public function setCustomWhereFilter($customWhereFilter)
    {
        $this->customWhereFilter = $customWhereFilter;
    }
}
