<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Appointments;

use OpenEMR\Events\AbstractBoundFilterEvent;
use OpenEMR\Events\BoundFilter;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom appointment filters, affecting the queries for patient tracker
 *
 * @package OpenEMR\Events
 * @subpackage Appointments
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class AppointmentsFilterEvent extends AbstractBoundFilterEvent
{
    /**
     * The customFilter event occurs in the library/appointments.inc.php file in the fetchEvents()
     * function, which fetches an array of all appointments. Setting this object's customWhereFilter
     * can filter appointments that show up.
     */
    const EVENT_HANDLE = 'appointments.customFilter';
}
