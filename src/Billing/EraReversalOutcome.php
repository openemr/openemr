<?php

/**
 * What ERA posting did with an insurance level when it met a payment reversal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

enum EraReversalOutcome
{
    /** The level was closed and nothing replaced the payment: re-opened. */
    case Reopened;
    /** This check also posted a corrected payment for the claim: left closed. */
    case CorrectionPosted;
    /** The level was not closed: nothing to do. */
    case AlreadyOpen;
    /** The ERA's payer is not one of the claim's insurances on the date of service. */
    case PayerNotOnClaim;
    /** No payer chosen, and neither the reversed payment nor the coverage tells the level. */
    case LevelUnknown;
}
