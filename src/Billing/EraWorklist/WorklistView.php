<?php

/**
 * The three lists of the ERA worklist page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

/**
 * Backed because the value travels in the page's URL.
 */
enum WorklistView: string
{
    /** Claim payments unattended posting left for a person. */
    case Review = 'review';
    /** Checks unattended posting posted, with their claims. */
    case Posted = 'posted';
    /** Received 835s that were not posted unattended, or hold a check that was held. */
    case Files = 'files';
}
