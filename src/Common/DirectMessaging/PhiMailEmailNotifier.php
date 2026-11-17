<?php

/**
 * Emails phiMail alerts to the practice's return address.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use PHPMailer\PHPMailer\PHPMailer;

final readonly class PhiMailEmailNotifier implements PhiMailNotifier
{
    private const FROM_NAME = 'phiMail Gateway';

    /** @var \Closure(): PHPMailer */
    private \Closure $newMailer;

    /**
     * @param string $address the practice_return_email_path global, which is both sender and recipient; '' sends nothing
     * @param ?\Closure(): PHPMailer $newMailer builds each message's mailer; defaults to MyMailer
     */
    public function __construct(
        private string $address,
        ?\Closure $newMailer = null,
    ) {
        $this->newMailer = $newMailer ?? static fn(): PHPMailer => new \MyMailer();
    }

    /**
     * Send the alert. Delivery is best effort: a failed send is not reported.
     */
    public function notify(string $subject, string $body): void
    {
        if ($this->address === '') {
            return;
        }

        $mail = ($this->newMailer)();
        $mail->From = $this->address;
        $mail->FromName = self::FROM_NAME;
        $mail->Body = $body;
        $mail->Subject = $subject;
        $mail->addAddress($this->address);
        $mail->send();
    }
}
