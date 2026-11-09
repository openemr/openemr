<?php

/**
 * A ReviewAuditTrailInterface that keeps what it was asked to record, or fails on demand.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewAuditTrailInterface;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewStatus;

final class RecordingReviewAuditTrail implements ReviewAuditTrailInterface
{
    /** @var list<array{id: int, from: ?ReviewStatus, to: ReviewStatus, actor: string, note: ?string}> */
    public array $entries = [];

    /** When set, record() throws it, standing in for an audit log that cannot be written. */
    public ?\RuntimeException $failure = null;

    public function record(ReviewRequest $request, ?ReviewStatus $from, ReviewActor $actor, ?string $note): void
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
        $this->entries[] = ['id' => $request->id, 'from' => $from, 'to' => $request->status, 'actor' => $actor->name, 'note' => $note];
    }

    /** @var list<array{ids: list<int>, actor: string}> one per recordAccess() call */
    public array $reads = [];

    /**
     * Records the ids read, or throws the failure a test set.
     */
    public function recordAccess(array $requests, ReviewActor $actor): void
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
        $this->reads[] = ['ids' => array_map(static fn(ReviewRequest $request): int => $request->id, $requests), 'actor' => $actor->name];
    }
}
