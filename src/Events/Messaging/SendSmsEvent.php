<?php

/**
 * PatientReportEvent class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Messaging;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SendSmsEvent
 *
 * @package OpenEMR\Events\Messaging
 *
 * Enables adding send SMS messages dialog.
 * See for example: interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php
 */
class SendSmsEvent extends Event
{
    // sms send button
    const ACTIONS_RENDER_SMS_POST = 'sendSMS.actions.render.post';
    // sms send dialog sendSMS('mobile phone number')
    const JAVASCRIPT_READY_SMS_POST = 'sendSMS.javascript.load.post';
}
