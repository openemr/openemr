<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Appointments;

use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom appointment filters, affecting the queries for patient tracker
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class AppointmentsFilterEvent extends Event
{
    /**
     * The customFilter event occurs in the library/appointments.inc.php file in the fetchEvents()
     * function, which fetches an array of all appointments. Setting this object's customWhereFilter
     * can filter appointments that show up.
     */
    const EVENT_HANDLE = 'appointments.customFilter';

    /**
     * @var null|UserService
     *
     */
    private $userService = null;

    /**
     * @var string
     */
    private $customWhereFilter= '1';

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
