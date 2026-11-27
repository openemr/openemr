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
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;

final readonly class EventAuditReviewTrail implements ReviewAuditTrailInterface
{
    private const EVENT = 'patient-review';

    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * @param string $group the audit group recorded with each entry
     * @param ?\Closure(string): ?PatientWriteClientType $clientType the registered API client's
     *        type, so an entry says whether the app is confidential or public; null to leave it out
     */
    public function __construct(
        private EventAuditLogger $auditLogger,
        private string $group,
        private ?\Closure $clientType = null,
    ) {
    }

    /**
     * One entry for the read, naming every request shown, filed under the patient of the first
     * (they are all one patient's). Requests of more than one type, resource type or app are
     * named in groups, each with its own.
     */
    public function recordAccess(array $requests, ReviewActor $actor): void
    {
        $first = $requests[0];
        /** @var array<string, non-empty-list<ReviewRequest>> $groups */
        $groups = [];
        foreach ($requests as $request) {
            $key = $request->type->value . '|' . ($request->resourceType->value ?? '') . '|' . ($request->clientId ?? '');
            $groups[$key][] = $request;
        }
        $label = count($requests) === 1 ? 'Review request ' : 'Review requests ';
        $comment = count($groups) === 1
            ? $label . self::ids($requests) . ' (' . $first->type->value . ') read' . $this->where($first->resourceType, $first->clientId)
            : $label . self::ids($requests) . ' read: ' . implode('; ', array_map($this->describe(...), array_values($groups)));

        $this->log($actor, $comment, $first->pid, $first->source, '');
    }

    /**
     * "<ids> (<type>) for <resource type> via API client <id> (<kind>)" for requests that share
     * their type, resource type and app.
     *
     * @param non-empty-list<ReviewRequest> $requests
     */
    private function describe(array $requests): string
    {
        $first = $requests[0];

        return self::ids($requests) . ' (' . $first->type->value . ')' . $this->where($first->resourceType, $first->clientId);
    }

    /**
     * @param non-empty-list<ReviewRequest> $requests
     */
    private static function ids(array $requests): string
    {
        return implode(', ', array_map(static fn(ReviewRequest $request): string => (string) $request->id, $requests));
    }

    public function record(ReviewRequest $request, ?ReviewStatus $from, ReviewActor $actor, ?string $note): void
    {
        $comment = $from === null
            ? 'Review request ' . $request->id . ' (' . $request->type->value . ') submitted as ' . $request->status->value
            : 'Review request ' . $request->id . ' (' . $request->type->value . ') ' . $from->value . ' to ' . $request->status->value;
        $comment .= $this->where($request->resourceType, $request->clientId);
        // An approved patient app write is put in the chart under the request's uuid: the id the
        // app was given when the write was held.
        if (
            $from === ReviewStatus::Approved
            && $request->status === ReviewStatus::Completed
            && $request->type === ReviewType::FhirWrite
            && $request->resourceType !== null
        ) {
            $comment .= '; written to the chart as ' . $request->resourceType->value . '/' . $request->uuid;
        }

        $this->log($actor, $comment, $request->pid, $request->source, $note ?? '');
    }

    public function recordRefusal(
        int $pid,
        ReviewActor $actor,
        ReviewType $type,
        FhirResourceType $resourceType,
        string $clientId,
        string $reason,
        ?string $targetUuid,
    ): void {
        $comment = ($targetUuid === null ? 'Review request' : 'Change to review request')
            . ' (' . $type->value . ') refused' . $this->where($resourceType, $clientId) . ': ' . $reason;
        // The id comes from the request URL; only a uuid is recorded, never other text.
        if ($targetUuid !== null && preg_match(self::UUID_PATTERN, $targetUuid) === 1) {
            $comment .= '; sent to ' . $targetUuid;
        }

        $this->auditLogger->recordLogItem(
            0,
            self::EVENT,
            $actor->name,
            $this->group,
            $comment,
            $pid,
            self::EVENT,
            'api',
            null,
            null,
            '',
        );
    }

    /**
     * " for <type> via API client <id> (<confidential|public>)", each part only when known.
     */
    private function where(?FhirResourceType $resourceType, ?string $clientId): string
    {
        $where = $resourceType !== null ? ' for ' . $resourceType->value : '';
        if ($clientId === null) {
            return $where;
        }
        $where .= ' via API client ' . $clientId;
        $kind = $this->clientType === null ? null : ($this->clientType)($clientId);

        return $where . match ($kind) {
            PatientWriteClientType::Confidential => ' (confidential)',
            PatientWriteClientType::Public => ' (public)',
            null => '',
        };
    }

    private function log(ReviewActor $actor, string $comment, int $pid, ReviewSource $source, string $note): void
    {
        $this->auditLogger->recordLogItem(
            1,
            self::EVENT,
            $actor->name,
            $this->group,
            $comment,
            $pid,
            self::EVENT,
            $source === ReviewSource::Api ? 'api' : 'onsite-portal',
            null,
            null,
            $note,
        );
    }
}
