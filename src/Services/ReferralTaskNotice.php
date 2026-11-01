<?php

/**
 * The FHIR Task statuses the one who made a referral (Refer By) hears about (#14710): the
 * receiving system accepted, rejected or completed the referral's Task through the FHIR API, or
 * marked it failed. Each case is the status, as FHIR names it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

enum ReferralTaskNotice: string
{
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * The notice, naming the recipient and, when the referral has one, its Referral Date. A
     * rejection or a failure ends with the recipient's reason (the Task's statusReason) when they
     * gave one; the other notices don't carry it.
     */
    public function text(string $recipient, ?string $referralDate, ?string $reason = null): string
    {
        $withReason = $reason === null ? null : match ($this) {
            self::Rejected => $referralDate === null
                ? sprintf(xl('%1$s rejected the referral: %2$s'), $recipient, $reason)
                : sprintf(xl('%1$s rejected the referral made on %2$s: %3$s'), $recipient, $referralDate, $reason),
            self::Failed => $referralDate === null
                ? sprintf(xl('%1$s could not complete the referral: %2$s'), $recipient, $reason)
                : sprintf(xl('%1$s could not complete the referral made on %2$s: %3$s'), $recipient, $referralDate, $reason),
            self::Accepted, self::Completed => null,
        };
        if ($withReason !== null) {
            return $withReason;
        }
        if ($referralDate === null) {
            return sprintf(match ($this) {
                self::Accepted => xl('%s accepted the referral.'),
                self::Rejected => xl('%s rejected the referral.'),
                self::Completed => xl('%s completed the referral.'),
                self::Failed => xl('%s could not complete the referral.'),
            }, $recipient);
        }
        return sprintf(match ($this) {
            self::Accepted => xl('%1$s accepted the referral made on %2$s.'),
            self::Rejected => xl('%1$s rejected the referral made on %2$s.'),
            self::Completed => xl('%1$s completed the referral made on %2$s.'),
            self::Failed => xl('%1$s could not complete the referral made on %2$s.'),
        }, $recipient, $referralDate);
    }
}
