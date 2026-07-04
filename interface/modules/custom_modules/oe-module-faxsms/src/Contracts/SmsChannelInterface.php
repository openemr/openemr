<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Contracts;

/**
 * Marks a service client as SMS-capable. A client implements this only when it
 * can actually send an SMS; concrete clients accept the optional
 * (toPhone, subject, message, from) arguments their callers pass.
 */
interface SmsChannelInterface
{
    /**
     * Send an SMS through the vendor.
     *
     * @return mixed
     */
    public function sendSMS(): mixed;
}
