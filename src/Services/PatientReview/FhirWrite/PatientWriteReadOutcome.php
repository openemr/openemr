<?php

/**
 * What a patient app's read of an id finds in the review queue.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

enum PatientWriteReadOutcome
{
    /** The app's own write, still waiting for review: answered with the held resource. */
    case Held;
    /** The app's own write, denied or withdrawn: answered 410. */
    case Gone;
    /**
     * Not a held write this app can see: an approved write (now in the chart), another app's
     * write, or no write at all. The read goes on to the chart as any read does.
     */
    case NotHeld;
}
