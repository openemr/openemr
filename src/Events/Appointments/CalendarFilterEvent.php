<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Appointments;

use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\Event;

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
     * @var null|UserService
     *
     */
    private $userService = null;

    /**
     * @var string
     *
     * This is the custom filter that can add to the calendar event query
     */
    private $customWhereFilter = '1';

    /**
     *
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function getCustomWhereFilter()
    {
        return $this->customWhereFilter;
    }
}
