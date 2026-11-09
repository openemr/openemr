<?php

/**
 * Writes review queue activity to the OpenEMR audit log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

use OpenEMR\Common\Logging\EventAuditLogger;

final readonly class EventAuditReviewTrail implements ReviewAuditTrailInterface
{
    private const EVENT = 'patient-review';

    /**
     * @param string $group the audit group recorded with each entry
     */
    public function __construct(
        private EventAuditLogger $auditLogger,
        private string $group,
    ) {
    }

    /**
     * One entry for the read, naming every request shown, filed under the patient of the first
     * (they are all one patient's).
     */
    public function recordAccess(array $requests, ReviewActor $actor): void
    {
        $first = $requests[0];
        $ids = implode(', ', array_map(static fn(ReviewRequest $request): string => (string) $request->id, $requests));
        $comment = (count($requests) === 1 ? 'Review request ' : 'Review requests ') . $ids
            . ' (' . $first->type->value . ') read';
        if ($first->resourceType !== null) {
            $comment .= ' for ' . $first->resourceType->value;
        }
        if ($first->clientId !== null) {
            $comment .= ' via API client ' . $first->clientId;
        }

        $this->auditLogger->recordLogItem(
            1,
            self::EVENT,
            $actor->name,
            $this->group,
            $comment,
            $first->pid,
            self::EVENT,
            $first->source === ReviewSource::Api ? 'api' : 'onsite-portal',
            null,
            null,
            '',
        );
    }

    public function record(ReviewRequest $request, ?ReviewStatus $from, ReviewActor $actor, ?string $note): void
    {
        $comment = $from === null
            ? 'Review request ' . $request->id . ' (' . $request->type->value . ') submitted as ' . $request->status->value
            : 'Review request ' . $request->id . ' (' . $request->type->value . ') ' . $from->value . ' to ' . $request->status->value;
        if ($request->resourceType !== null) {
            $comment .= ' for ' . $request->resourceType->value;
        }
        if ($request->clientId !== null) {
            $comment .= ' via API client ' . $request->clientId;
        }

        $this->auditLogger->recordLogItem(
            1,
            self::EVENT,
            $actor->name,
            $this->group,
            $comment,
            $request->pid,
            self::EVENT,
            $request->source === ReviewSource::Api ? 'api' : 'onsite-portal',
            null,
            null,
            $note ?? '',
        );
    }
}
