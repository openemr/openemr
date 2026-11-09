<?php

/**
 * How a referral's recipient was told about their Task, or why they weren't (#15137). Stored in
 * referral_notice.outcome.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

enum ReferralNoticeOutcome: string
{
    /** The Referral C-CDA went to the entry's Direct address; the message log carries its delivery. */
    case DirectSent = 'direct_sent';
    /** The Referral C-CDA was queued by Direct, but couldn't be logged, so its delivery can't be followed here. */
    case DirectSentUnlogged = 'direct_unlogged';
    /** The entry has a Direct address, but the notice couldn't be sent to it. */
    case DirectFailed = 'direct_failed';
    /** The entry has a Direct address, but nothing here sends Direct. */
    case DirectUnavailable = 'direct_unavailable';
    /** A plain email with no patient details was queued to the entry's email address. */
    case EmailQueued = 'email_queued';
    /** The entry has an email address, but the email couldn't be queued. */
    case EmailFailed = 'email_failed';
    /** The entry has neither a Direct nor an email address. */
    case NoAddress = 'no_address';

    /**
     * Whether staff need to tell the recipient themselves.
     */
    public function needsStaff(): bool
    {
        return match ($this) {
            self::DirectSent, self::DirectSentUnlogged, self::EmailQueued => false,
            self::DirectFailed, self::DirectUnavailable, self::EmailFailed, self::NoAddress => true,
        };
    }

    /**
     * What the referral page says, translated.
     */
    public function label(): string
    {
        return match ($this) {
            self::DirectSent => xl('Recipient sent the referral by Direct'),
            self::DirectSentUnlogged => xl('Recipient sent the referral by Direct, but the send could not be logged, so its delivery is not shown here. Do not send it again.'),
            self::DirectFailed => xl('Recipient could not be sent the referral by Direct'),
            self::DirectUnavailable => xl('Recipient has a Direct address, but Direct messaging is not available'),
            self::EmailQueued => xl('Recipient emailed that a referral was sent'),
            self::EmailFailed => xl('Recipient could not be emailed'),
            self::NoAddress => xl('Recipient could not be notified: their address book entry has no Direct or email address'),
        };
    }
}
