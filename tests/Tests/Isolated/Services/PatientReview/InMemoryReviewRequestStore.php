<?php

/**
 * An in-memory ReviewRequestStoreInterface, so ReviewQueueService's rules can be tested without a
 * database. It round-trips rows through ReviewRequestMapper the way the repository does, and
 * transactional() restores the previous state when the action throws.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use DateTimeImmutable;
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewRequestStoreInterface;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewSubmission;
use OpenEMR\Services\PatientReview\ReviewType;

final class InMemoryReviewRequestStore implements ReviewRequestStoreInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $requests = [];
    /** @var list<array<string, mixed>> */
    private array $events = [];
    /** @var array<string, string> keyed by "requestId|kind" */
    private array $secrets = [];
    private int $nextId = 1;

    /** When set, the next updateStatus() behaves as if another reviewer got there first. */
    public bool $loseNextUpdate = false;

    public function __construct(private readonly ReviewRequestMapper $mapper = new ReviewRequestMapper())
    {
    }

    public function transactional(callable $action): mixed
    {
        $snapshot = [$this->requests, $this->events, $this->secrets, $this->nextId];
        try {
            return $action();
        } catch (\Throwable $e) {
            [$this->requests, $this->events, $this->secrets, $this->nextId] = $snapshot;
            throw $e;
        }
    }

    public function insert(ReviewSubmission $submission, ReviewStatus $status, string $uuidBytes, DateTimeImmutable $now): int
    {
        $id = $this->nextId++;
        $timestamp = $this->mapper->formatDateTime($now);
        $this->requests[$id] = [
            'id' => $id,
            'uuid' => $uuidBytes,
            'pid' => $submission->pid,
            'type' => $submission->type->value,
            'source' => $submission->source->value,
            'client_id' => $submission->clientId,
            'status' => $status->value,
            'summary' => $submission->summary,
            'payload' => $this->mapper->encodePayload($submission->payload),
            'payload_format' => PayloadFormat::Json->value,
            'target_table' => $submission->targetTable,
            'target_id' => $submission->targetId,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ];
        return $id;
    }

    public function find(int $id): ?ReviewRequest
    {
        return isset($this->requests[$id]) ? $this->mapper->toRequest($this->requests[$id]) : null;
    }

    public function findForUpdate(int $id): ?ReviewRequest
    {
        // Nothing runs concurrently in memory, so there is no row to hold.
        return $this->find($id);
    }

    public function findOpen(int $pid, ReviewType $type): array
    {
        $open = [];
        foreach (array_reverse($this->requests) as $row) {
            $request = $this->mapper->toRequest($row);
            if ($request->pid === $pid && $request->type === $type && $request->status->isOpen()) {
                $open[] = $request;
            }
        }
        return $open;
    }

    public function findByStatus(ReviewStatus $status, ?ReviewType $type, int $limit, int $offset): array
    {
        $found = [];
        foreach ($this->requests as $row) {
            $request = $this->mapper->toRequest($row);
            if ($request->status === $status && ($type === null || $request->type === $type)) {
                $found[] = $request;
            }
        }
        return array_slice($found, $offset, $limit);
    }

    public function countByStatus(ReviewStatus $status, ?ReviewType $type): int
    {
        return count($this->findByStatus($status, $type, PHP_INT_MAX, 0));
    }

    public function updateStatus(int $id, ReviewStatus $from, ReviewStatus $to, DateTimeImmutable $now, ?int $reviewedBy, ?string $note): bool
    {
        if ($this->loseNextUpdate) {
            $this->loseNextUpdate = false;
            return false;
        }
        if (!isset($this->requests[$id]) || $this->requests[$id]['status'] !== $from->value) {
            return false;
        }
        $timestamp = $this->mapper->formatDateTime($now);
        $this->requests[$id]['status'] = $to->value;
        $this->requests[$id]['updated_at'] = $timestamp;
        if ($reviewedBy !== null) {
            $this->requests[$id]['reviewed_by'] = $reviewedBy;
            $this->requests[$id]['reviewed_at'] = $timestamp;
            $this->requests[$id]['review_note'] = $note;
        }
        return true;
    }

    public function appendEvent(int $requestId, ?ReviewStatus $from, ReviewStatus $to, ReviewActor $actor, DateTimeImmutable $now, ?string $note): void
    {
        $this->events[] = [
            'id' => count($this->events) + 1,
            'request_id' => $requestId,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_type' => $actor->type->value,
            'actor_id' => $actor->id,
            'created_at' => $this->mapper->formatDateTime($now),
            'note' => $note,
        ];
    }

    public function events(int $requestId): array
    {
        $events = [];
        foreach ($this->events as $row) {
            if ($row['request_id'] === $requestId) {
                $events[] = $this->mapper->toEvent($row);
            }
        }
        return $events;
    }

    public function putSecret(int $requestId, string $kind, string $ciphertext, DateTimeImmutable $now): void
    {
        $this->secrets[$requestId . '|' . $kind] = $ciphertext;
    }

    public function getSecret(int $requestId, string $kind): ?string
    {
        return $this->secrets[$requestId . '|' . $kind] ?? null;
    }

    public function deleteSecrets(int $requestId): void
    {
        foreach (array_keys($this->secrets) as $key) {
            if (str_starts_with($key, $requestId . '|')) {
                unset($this->secrets[$key]);
            }
        }
    }

    public function secretCount(): int
    {
        return count($this->secrets);
    }
}
