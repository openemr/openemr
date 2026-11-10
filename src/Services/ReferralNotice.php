<?php

/**
 * The latest notice a referral's recipient got (#15137), as the referral page shows it: how they were
 * told, and for a Direct notice whether it was delivered.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\DirectMessaging\DirectMessageLogStatus;

final readonly class ReferralNotice
{
    /**
     * @param string $createdAt when it was sent or tried, as the database formats it
     * @param ?string $address the Direct or email address it went to
     * @param ?string $detail why it couldn't be sent
     * @param ?DirectMessageLogStatus $deliveryStatus a Direct notice's status in the message log
     * @param ?string $deliveryInfo what the Direct server said about its delivery
     */
    public function __construct(
        public ReferralNoticeOutcome $outcome,
        public string $createdAt,
        public ?string $address,
        public ?string $detail,
        public ?DirectMessageLogStatus $deliveryStatus = null,
        public ?string $deliveryInfo = null,
    ) {
    }

    /**
     * Whether a Direct notice went out but its delivery failed.
     */
    public function deliveryFailed(): bool
    {
        return $this->outcome === ReferralNoticeOutcome::DirectSent && $this->deliveryStatus === DirectMessageLogStatus::Failed;
    }

    /**
     * Whether staff need to tell the recipient themselves.
     */
    public function needsStaff(): bool
    {
        return $this->outcome->needsStaff() || $this->deliveryFailed();
    }

    /**
     * What the referral page says, translated.
     */
    public function label(): string
    {
        if ($this->deliveryFailed()) {
            return xl('Recipient sent the referral by Direct, but its delivery failed');
        }
        if ($this->outcome === ReferralNoticeOutcome::DirectSent && $this->deliveryStatus === DirectMessageLogStatus::Dispatched) {
            return xl('Recipient sent the referral by Direct, and its delivery was confirmed');
        }
        return $this->outcome->label();
    }
}
