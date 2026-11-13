<?php

/**
 * A Direct message to send through phiMail.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailOutgoingMessage
{
    /**
     * @param string $recipient the recipient's Direct address
     * @param bool $verifyFinalDelivery whether to ask the recipient's system to confirm delivery,
     *     which fails the delivery when that system does not support it
     * @param ?int $patientId the patient the message is about, for the audit and message logs
     * @param ?int $referralId the referral (transactions.id) the message carries, for the message log,
     *     so a reply to it can be filed with that referral
     */
    public function __construct(
        public string $recipient,
        public string $text,
        public ?PhiMailOutgoingAttachment $attachment = null,
        public bool $verifyFinalDelivery = false,
        public ?int $patientId = null,
        public ?int $referralId = null,
    ) {
    }
}
