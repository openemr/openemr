<?php

/**
 * Writes one kind of approved patient app write to the chart.
 *
 * FhirWriteReviewHandler picks the applier for a request's resource type. Each writes through
 * the normal service for that data, under the id the app was given when the write was held
 * (the request's uuid), so the app's Location keeps pointing at the same resource.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewRequest;

interface PatientWriteApplierInterface
{
    public function type(): PatientWritableType;

    /**
     * Whether the resource, as held, could be written now: everything it refers to is on this
     * server. Checked when the write is held and when staff are offered Approve. Writes nothing.
     *
     * @param array<array-key, mixed> $resource the held resource
     */
    public function canWrite(array $resource): bool;

    /**
     * Runs inside the transaction that approves the request. Throwing rolls the approval back
     * and leaves the request pending.
     *
     * @param ReviewActor $reviewer the staff user approving it
     * @param PatientWriteApprovalOptions $options what staff chose, such as an encounter
     * @return ?string a note for the audit entry that completes the request, such as where in
     *                 the chart it was filed; null for none
     * @throws \RuntimeException when the record cannot be written, or not as staff chose
     */
    public function apply(ReviewRequest $request, ReviewActor $reviewer, PatientWriteApprovalOptions $options): ?string;
}
