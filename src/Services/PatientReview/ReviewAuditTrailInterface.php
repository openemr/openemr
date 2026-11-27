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

    /**
     * Records that open requests were shown: to a patient app reading back what it submitted,
     * or to staff on a review screen.
     *
     * @param non-empty-list<ReviewRequest> $requests all for one patient
     */
    public function recordAccess(array $requests, ReviewActor $actor): void;

    /**
     * Records a submission, or a change to one, that was refused, so nothing was added or changed.
     *
     * @param string $reason why, as the caller names it (e.g. "TooLarge (413)")
     * @param ?string $targetUuid the request uuid a change was sent to; null for a new submission
     */
    public function recordRefusal(
        int $pid,
        ReviewActor $actor,
        ReviewType $type,
        FhirResourceType $resourceType,
        string $clientId,
        string $reason,
        ?string $targetUuid,
    ): void;
}
