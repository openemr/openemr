<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Events\Appointments;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for know what type of appointment has been set on the calendar
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 *
 */
class AppointmentSetEvent extends Event
{
    /**
     * This event is triggered after a new patient appointment has been scheduled
     */
    const EVENT_HANDLE = 'appointment.set';

    /**
     * @var
     */
    private $post;

    public $eid;

    public function __construct($post)
    {
        $this->post = $post;
    }

    /**
     * @return array
     */
    public function givenAppointmentData(): array
    {
        return $this->post;
    }

    public function setEventId($eid)
    {
        $this->eid = $eid;
    }
}
