<?php

/**
 * Records review queue activity in the audit log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

interface ReviewAuditTrailInterface
{
    /**
     * @param ?ReviewStatus $from null when the request was just created
     */
    public function record(ReviewRequest $request, ?ReviewStatus $from, ReviewActor $actor, ?string $note): void;
}
