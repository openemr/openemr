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
     * Open requests for one patient and type, newest first.
     *
     * @return list<ReviewRequest>
     */
    public function findOpen(int $pid, ReviewType $type): array;

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

    public function appendEvent(int $requestId, ?ReviewStatus $from, ReviewStatus $to, ReviewActor $actor, DateTimeImmutable $now, ?string $note): void;

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
