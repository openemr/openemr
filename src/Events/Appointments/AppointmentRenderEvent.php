<?php

/*
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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
class AppointmentRenderEvent extends Event
{
    /**
     * This event is triggered in javascript when rendering appointment
     */
    const RENDER_JAVASCRIPT = 'appointment.render.javascript';

    /**
     * This event is triggered in below where patient is shown when rendering appointment
     */
    const RENDER_BELOW_PATIENT = 'appointment.render.below.patient';

    /**
     * @var
     */
    private $appt;

    public function __construct($appt)
    {
        $this->appt = $appt;
    }

    /**
     * @return array
     */
    public function getAppt(): array
    {
        return $this->appt;
    }
}
