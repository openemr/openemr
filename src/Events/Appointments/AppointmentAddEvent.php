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

class AppointmentAddEvent extends Event
{
    /**
     * this events fires when the add_edit_event screen opens and the
     * telehealth module is active
     */
    const ACTION_RENDER_SESSION_BUTTON = 'add.edit.render.button';

    const ACTION_RENDER_CANCEL_BUTTON = 'add.edit.render.cancel';

    const ACTION_RENDER_CANCEL_JAVASCRIPT = 'render.cancel.javascript';


}
