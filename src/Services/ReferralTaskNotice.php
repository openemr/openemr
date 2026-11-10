<?php

/**
 * The FHIR Task statuses the one who made a referral (Refer By) hears about (#14710): the
 * referral's Task was marked accepted, rejected, completed, failed or cancelled through the FHIR
 * API. Each case is the status, as FHIR names it. The service's own cancellations (the referral
 * sent elsewhere or deleted) are written without the API and aren't told. The notice says what
 * the Task was marked and names Refer To as the referral's recipient, not as the one who marked
 * it: nothing yet ties an API client to the recipient, and the message's header names whoever
 * made the change.
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
    case Cancelled = 'cancelled';

    /**
     * The notice, naming the recipient and, when the referral has one, its Referral Date. A
     * rejection, a failure or a cancellation ends with the reason the Task carries (its
     * statusReason) when it has one; the other notices don't carry it.
     */
    public function text(string $recipient, ?string $referralDate, ?string $reason = null): string
    {
        $withReason = $reason === null ? null : match ($this) {
            self::Rejected => $referralDate === null
                ? sprintf(xl('The Task for the referral to %1$s was marked rejected: %2$s'), $recipient, $reason)
                : sprintf(xl('The Task for the referral to %1$s made on %2$s was marked rejected: %3$s'), $recipient, $referralDate, $reason),
            self::Failed => $referralDate === null
                ? sprintf(xl('The Task for the referral to %1$s was marked failed: %2$s'), $recipient, $reason)
                : sprintf(xl('The Task for the referral to %1$s made on %2$s was marked failed: %3$s'), $recipient, $referralDate, $reason),
            self::Cancelled => $referralDate === null
                ? sprintf(xl('The Task for the referral to %1$s was marked cancelled: %2$s'), $recipient, $reason)
                : sprintf(xl('The Task for the referral to %1$s made on %2$s was marked cancelled: %3$s'), $recipient, $referralDate, $reason),
            self::Accepted, self::Completed => null,
        };
        if ($withReason !== null) {
            return $withReason;
        }
        if ($referralDate === null) {
            return sprintf(match ($this) {
                self::Accepted => xl('The Task for the referral to %s was marked accepted.'),
                self::Rejected => xl('The Task for the referral to %s was marked rejected.'),
                self::Completed => xl('The Task for the referral to %s was marked completed.'),
                self::Failed => xl('The Task for the referral to %s was marked failed.'),
                self::Cancelled => xl('The Task for the referral to %s was marked cancelled.'),
            }, $recipient);
        }
        return sprintf(match ($this) {
            self::Accepted => xl('The Task for the referral to %1$s made on %2$s was marked accepted.'),
            self::Rejected => xl('The Task for the referral to %1$s made on %2$s was marked rejected.'),
            self::Completed => xl('The Task for the referral to %1$s made on %2$s was marked completed.'),
            self::Failed => xl('The Task for the referral to %1$s made on %2$s was marked failed.'),
            self::Cancelled => xl('The Task for the referral to %1$s made on %2$s was marked cancelled.'),
        }, $recipient, $referralDate);
    }
}
