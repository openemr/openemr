<?php

/**
 * Why staff cannot post an open portal payment.
 *
 * Only an uncharged payment can be blocked. The clinic charges it from the card details the
 * patient gave, so without readable details there is nothing to charge, and posting it would
 * record money that was never received. A charged payment can always be posted, because the
 * gateway holds the transaction.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

enum PaymentPostingBlock
{
    /** The card details are stored but cannot be decrypted (the site's keys were replaced). */
    case CardUnreadable;

    /** The payment was saved with no card details. */
    case NoCardDetails;

    /** The card details lack a field needed to charge the card (PortalReviewWorkflows::CARD_FIELDS). */
    case IncompleteCardDetails;

    /**
     * What staff are told, translated.
     */
    public function message(): string
    {
        return match ($this) {
            self::CardUnreadable => xl('The card details of this payment could not be read and the card was not charged, so it cannot be posted. Ask the patient to submit the payment again.'),
            self::NoCardDetails => xl('This payment has no card details and the card was not charged, so it cannot be posted. Ask the patient to submit the payment again.'),
            self::IncompleteCardDetails => xl('The card details of this payment are incomplete and the card was not charged, so it cannot be posted. Ask the patient to submit the payment again.'),
        };
    }
}
