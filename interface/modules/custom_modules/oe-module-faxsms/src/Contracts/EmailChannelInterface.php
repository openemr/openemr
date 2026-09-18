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
 * Marks a service client as email-capable. A client implements this only when
 * it can actually send an email through the dispatch action; the shared SMTP
 * helper AppDispatch::mailEmail() remains available to all clients regardless.
 */
interface EmailChannelInterface
{
    /**
     * Send an email through the vendor.
     *
     * @return mixed
     */
    public function sendEmail(): mixed;
}
