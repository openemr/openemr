<?php

/**
 * Why nothing from a check was posted unattended.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

/**
 * Backed because the value is stored in era_auto_post_check.hold_reason.
 */
enum HoldReason: string
{
    /** The claim payments less the provider-level adjustments do not add up to the check amount. */
    case OutOfBalance = 'out_of_balance';
    /** None of the check's claims could be matched to an insurance company to post it for. */
    case NoPayer = 'no_payer';
    /** A payment session for this check exists under a different insurance company. */
    case PostedForAnotherPayer = 'posted_for_another_payer';
}
