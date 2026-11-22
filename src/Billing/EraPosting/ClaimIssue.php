<?php

/**
 * Why a claim payment from an ERA was not posted, or not posted in full.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

enum ClaimIssue
{
    /** No encounter matches the claim id the payer sent back. Nothing posted. */
    case ClaimNotFound;
    /** This check already posted to the claim in an earlier run. Nothing posted. */
    case AlreadyPosted;
    /** Claim status 4. The denial is recorded on the claim; nothing posted. */
    case Denied;
    /** Claim status 22. Not posted; the reversed insurance level may be re-opened. */
    case Reversal;
    /** A service line's charge differs from the invoice. Lines from it on are not posted. */
    case ChargeMismatch;
    /** A code the invoice doesn't have, and the global to add it is off. Lines from it on are not posted. */
    case CodeNotOnInvoice;
    /** A non-contractual adjustment on a line paid 0. Lines from it on are not posted. */
    case UnpaidAdjustment;
    /** The payer pended lines (reason 133). The rest posts, but the insurance level stays open. */
    case LinesPended;
}
