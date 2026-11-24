<?php

/**
 * Whether a check from a received 835 was posted unattended.
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
 * Backed because the value is stored in era_auto_post_check.status.
 */
enum CheckStatus: string
{
    /** Its claim payments went through the posting engine. */
    case Posted = 'posted';
    /** Nothing from the check was posted; all of it is left for a person. */
    case Held = 'held';
}
