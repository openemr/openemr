<?php

/**
 * Asks for a referral's recipient to be told by Direct that the referral was sent to them (#15137).
 *
 * Core dispatches it and never names the Direct implementation: a listener that can send Direct (the
 * Carecoordination module, which makes the Referral C-CDA) sends the notice and says how it went. With
 * no listener the event comes back unhandled, and the referral shows that Direct wasn't available.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\Referral;

use Symfony\Contracts\EventDispatcher\Event;

final class ReferralDirectNoticeEvent extends Event
{
    public const EVENT_NAME = 'referral.direct_notice';

    private ?string $messageId = null;
    private ?string $failure = null;

    /**
     * @param int $referralId the referral (transactions.id)
     * @param int $patientId the referral's patient
     * @param string $directAddress the recipient's Direct address
     * @param ?int $userId who saved the referral, or null when no signed-in user did
     */
    public function __construct(
        public readonly int $referralId,
        public readonly int $patientId,
        public readonly string $directAddress,
        public readonly ?int $userId,
    ) {
    }

    /**
     * The notice went out, under the message id the Direct server queued it with.
     */
    public function sent(string $messageId): void
    {
        $this->messageId = $messageId;
        $this->failure = null;
        $this->stopPropagation();
    }

    /**
     * The notice couldn't be sent, for this reason, which staff are shown.
     */
    public function failed(string $reason): void
    {
        $this->messageId = null;
        $this->failure = $reason;
        $this->stopPropagation();
    }

    public function messageId(): ?string
    {
        return $this->messageId;
    }

    public function failure(): ?string
    {
        return $this->failure;
    }

    /**
     * Whether a listener sent the notice or said why it couldn't.
     */
    public function wasHandled(): bool
    {
        return $this->messageId !== null || $this->failure !== null;
    }
}
