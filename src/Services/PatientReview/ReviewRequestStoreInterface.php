<?php

/**
 * Persistence for the review queue: requests, their history and their secrets.
 *
 * ReviewQueueService holds the rules; this is storage only. The interface exists so the rules
 * can be tested without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

use DateTimeImmutable;

interface ReviewRequestStoreInterface
{
    /**
     * Runs $action in a database transaction, rolling back if it throws.
     *
     * @template T
     * @param callable(): T $action
     * @return T
     */
    public function transactional(callable $action): mixed;

    /**
     * @param string $uuidBytes 16 raw bytes
     * @return int the new request id
     */
    public function insert(ReviewSubmission $submission, ReviewStatus $status, string $uuidBytes, DateTimeImmutable $now): int;

    public function find(int $id): ?ReviewRequest;

    /**
     * @param string $uuidBytes 16 raw bytes
     */
    public function findByUuid(string $uuidBytes): ?ReviewRequest;

    /**
     * Reads the request and holds its row until the surrounding transaction ends, so a decision
     * made on what was read cannot be overtaken by a concurrent state change. Call inside
     * transactional().
     */
    public function findForUpdate(int $id): ?ReviewRequest;

    /**
     * Open requests for one patient, type and resource type, newest first. A null resource type
     * matches only requests without one.
     *
     * @return list<ReviewRequest>
     */
    public function findOpen(int $pid, ReviewType $type, ?FhirResourceType $resourceType = null): array;

    /**
     * How many open requests of this type one API client has submitted for the patient, across
     * resource types.
     */
    public function countOpenForClient(int $pid, ReviewType $type, string $clientId): int;

    /**
     * How many times the request's payload has been replaced: its history rows that keep a
     * replaced payload.
     */
    public function countRevisions(int $requestId): int;

    /**
     * Open requests of one type and resource type that one API client submitted for the
     * patient, oldest first.
     *
     * @return list<ReviewRequest>
     */
    public function findOpenForClient(int $pid, ReviewType $type, FhirResourceType $resourceType, string $clientId): array;

    /**
     * Holds the target row until the surrounding transaction ends, so two submissions for the
     * same row cannot both find it without an open request. Call inside transactional().
     *
     * @throws \DomainException when requests for that table cannot be locked
     * @throws \RuntimeException when the target row does not exist
     */
    public function lockTarget(string $targetTable, string $targetId): void;

    /**
     * Open requests for one patient, type and target row, newest first.
     *
     * @return list<ReviewRequest>
     */
    public function findOpenForTarget(int $pid, ReviewType $type, string $targetTable, string $targetId): array;

    /**
     * The most recent request, open or not, for one patient, type and target row.
     */
    public function findLatestForTarget(int $pid, ReviewType $type, string $targetTable, string $targetId): ?ReviewRequest;

    /**
     * Oldest first, so staff work the queue in the order it arrived.
     *
     * @return list<ReviewRequest>
     */
    public function findByStatus(ReviewStatus $status, ?ReviewType $type, int $limit, int $offset): array;

    public function countByStatus(ReviewStatus $status, ?ReviewType $type): int;

    /**
     * Changes the status only if the request is still in $from, so two reviewers acting on the
     * same request cannot both succeed.
     *
     * @param ?int $reviewedBy users.id when a staff user decided the request
     * @return bool false when the request was not in $from
     */
    public function updateStatus(int $id, ReviewStatus $from, ReviewStatus $to, DateTimeImmutable $now, ?int $reviewedBy, ?string $note): bool;

    /**
     * Replaces the summary and payload. Call inside transactional(), holding the row from
     * findForUpdate(), so the status that allowed the replacement cannot change underneath it.
     *
     * @param array<array-key, mixed> $payload
     */
    public function replacePayload(int $id, string $summary, array $payload, DateTimeImmutable $now): void;

    /**
     * @param ?array<array-key, mixed> $previousPayload the payload a replacement overwrote; null
     *                                                  for a status change
     */
    public function appendEvent(
        int $requestId,
        ?ReviewStatus $from,
        ReviewStatus $to,
        ReviewActor $actor,
        DateTimeImmutable $now,
        ?string $note,
        ?array $previousPayload = null,
    ): void;

    /**
     * Oldest first.
     *
     * @return list<ReviewEvent>
     */
    public function events(int $requestId): array;

    /**
     * Stores or replaces the secret of this kind for the request.
     */
    public function putSecret(int $requestId, string $kind, string $ciphertext, DateTimeImmutable $now): void;

    public function getSecret(int $requestId, string $kind): ?string;

    public function deleteSecrets(int $requestId): void;
}
