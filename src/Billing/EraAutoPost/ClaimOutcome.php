<?php

/**
 * What became of one claim payment in an unattended posting run.
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
 * Backed because the value is stored in era_auto_post_claim.outcome.
 */
enum ClaimOutcome: string
{
    /** Found, and every service line was posted. */
    case Posted = 'posted';
    /** Not posted, or not in full: its check was held, or the posting engine refused or flagged it. */
    case Review = 'review';
    /** Already posted from this check before the run. */
    case Skipped = 'skipped';
}
