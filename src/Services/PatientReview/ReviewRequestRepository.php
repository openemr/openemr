<?php

/**
 * Database storage for the review queue.
 *
 * Tables: patient_review_request, patient_review_event, patient_review_secret.
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
use OpenEMR\Common\Database\QueryUtils;

final readonly class ReviewRequestRepository implements ReviewRequestStoreInterface
{
    /** Target tables whose rows lockTarget() can hold, with their key column. */
    private const LOCKABLE_TARGETS = [
        'onsite_documents' => 'id',
        'patient_data' => 'pid',
    ];

    public function __construct(private ReviewRequestMapper $mapper)
    {
    }

    public function transactional(callable $action): mixed
    {
        return QueryUtils::inTransaction($action);
    }

    public function insert(ReviewSubmission $submission, ReviewStatus $status, string $uuidBytes, DateTimeImmutable $now): int
    {
        $timestamp = $this->mapper->formatDateTime($now);
        return QueryUtils::sqlInsert(
            'INSERT INTO `patient_review_request` (`uuid`, `pid`, `type`, `resource_type`, `source`, `client_id`, `status`, `summary`,'
            . ' `payload`, `payload_format`, `target_table`, `target_id`, `created_at`, `updated_at`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $uuidBytes,
                $submission->pid,
                $submission->type->value,
                $submission->resourceType?->value,
                $submission->source->value,
                $submission->clientId,
                $status->value,
                $submission->summary,
                $this->mapper->encodePayload($submission->payload),
                PayloadFormat::Json->value,
                $submission->targetTable,
                $submission->targetId,
                $timestamp,
                $timestamp,
            ]
        );
    }

    public function find(int $id): ?ReviewRequest
    {
        $row = QueryUtils::querySingleRow('SELECT * FROM `patient_review_request` WHERE `id` = ?', [$id]);
        return is_array($row) && $row !== [] ? $this->mapper->toRequest($row) : null;
    }

    /**
     * The request issued this uuid, or null; the uuid column is unique.
     *
     * @param string $uuidBytes 16 raw bytes, as the queue issued it
     */
    public function findByUuid(string $uuidBytes): ?ReviewRequest
    {
        $row = QueryUtils::querySingleRow('SELECT * FROM `patient_review_request` WHERE `uuid` = ?', [$uuidBytes]);
        return is_array($row) && $row !== [] ? $this->mapper->toRequest($row) : null;
    }

    public function findForUpdate(int $id): ?ReviewRequest
    {
        $row = QueryUtils::querySingleRow('SELECT * FROM `patient_review_request` WHERE `id` = ? FOR UPDATE', [$id]);
        return is_array($row) && $row !== [] ? $this->mapper->toRequest($row) : null;
    }

    public function findOpen(int $pid, ReviewType $type, ?FhirResourceType $resourceType = null): array
    {
        $sql = 'SELECT * FROM `patient_review_request` WHERE `pid` = ? AND `type` = ? AND `status` IN (?, ?)';
        $binds = [$pid, $type->value, ReviewStatus::Pending->value, ReviewStatus::AwaitingPayment->value];
        if ($resourceType === null) {
            $sql .= ' AND `resource_type` IS NULL';
        } else {
            $sql .= ' AND `resource_type` = ?';
            $binds[] = $resourceType->value;
        }
        $rows = QueryUtils::fetchRecords($sql . ' ORDER BY `created_at` DESC, `id` DESC', $binds);
        return array_map($this->mapper->toRequest(...), $rows);
    }

    /**
     * Counts pending and awaiting-payment requests the client submitted for the patient.
     */
    public function countOpenForClient(int $pid, ReviewType $type, string $clientId): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS `total` FROM `patient_review_request` WHERE `pid` = ? AND `type` = ? AND `client_id` = ?'
            . ' AND `status` IN (?, ?)',
            [$pid, $type->value, $clientId, ReviewStatus::Pending->value, ReviewStatus::AwaitingPayment->value]
        );
        $total = is_array($row) ? ($row['total'] ?? 0) : 0;

        return is_int($total) || (is_string($total) && ctype_digit($total)) ? intval($total) : 0;
    }

    public function countRevisions(int $requestId): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS `total` FROM `patient_review_event` WHERE `request_id` = ? AND `payload` IS NOT NULL',
            [$requestId]
        );
        $total = is_array($row) ? ($row['total'] ?? 0) : 0;

        return is_int($total) || (is_string($total) && ctype_digit($total)) ? intval($total) : 0;
    }

    public function findOpenForClient(int $pid, ReviewType $type, FhirResourceType $resourceType, string $clientId): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `patient_review_request` WHERE `pid` = ? AND `type` = ? AND `resource_type` = ? AND `client_id` = ?'
            . ' AND `status` IN (?, ?) ORDER BY `created_at` ASC, `id` ASC',
            [$pid, $type->value, $resourceType->value, $clientId, ReviewStatus::Pending->value, ReviewStatus::AwaitingPayment->value]
        );
        return array_map($this->mapper->toRequest(...), $rows);
    }

    public function lockTarget(string $targetTable, string $targetId): void
    {
        $key = self::LOCKABLE_TARGETS[$targetTable] ?? throw new \DomainException('Review requests for ' . $targetTable . ' cannot be locked');
        // Table and column come from the constant above, never from the caller's text.
        $locked = QueryUtils::fetchRecords(
            'SELECT ' . QueryUtils::escapeColumnName($key, [$targetTable]) . ' FROM ' . QueryUtils::escapeTableName($targetTable)
            . ' WHERE ' . QueryUtils::escapeColumnName($key, [$targetTable]) . ' = ? FOR UPDATE',
            [$targetId]
        );
        // A row deleted since the caller checked it would leave a request for nothing.
        if ($locked === []) {
            throw new \RuntimeException('The review target no longer exists');
        }
    }

    public function findOpenForTarget(int $pid, ReviewType $type, string $targetTable, string $targetId): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `patient_review_request` WHERE `pid` = ? AND `type` = ? AND `target_table` = ? AND `target_id` = ?'
            . ' AND `status` IN (?, ?) ORDER BY `created_at` DESC, `id` DESC',
            [$pid, $type->value, $targetTable, $targetId, ReviewStatus::Pending->value, ReviewStatus::AwaitingPayment->value]
        );
        return array_map($this->mapper->toRequest(...), $rows);
    }

    public function findLatestForTarget(int $pid, ReviewType $type, string $targetTable, string $targetId): ?ReviewRequest
    {
        $row = QueryUtils::querySingleRow(
            'SELECT * FROM `patient_review_request` WHERE `pid` = ? AND `type` = ? AND `target_table` = ? AND `target_id` = ?'
            . ' ORDER BY `created_at` DESC, `id` DESC LIMIT 1',
            [$pid, $type->value, $targetTable, $targetId]
        );
        return is_array($row) && $row !== [] ? $this->mapper->toRequest($row) : null;
    }

    public function findByStatus(ReviewStatus $status, ?ReviewType $type, int $limit, int $offset): array
    {
        $sql = 'SELECT * FROM `patient_review_request` WHERE `status` = ?';
        $binds = [$status->value];
        if ($type !== null) {
            $sql .= ' AND `type` = ?';
            $binds[] = $type->value;
        }
        // LIMIT and OFFSET are integers we control, not user text; the driver cannot bind them.
        $sql .= ' ORDER BY `created_at` ASC, `id` ASC LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset);

        return array_map($this->mapper->toRequest(...), QueryUtils::fetchRecords($sql, $binds));
    }

    public function countByStatus(ReviewStatus $status, ?ReviewType $type): int
    {
        $sql = 'SELECT COUNT(*) AS `total` FROM `patient_review_request` WHERE `status` = ?';
        $binds = [$status->value];
        if ($type !== null) {
            $sql .= ' AND `type` = ?';
            $binds[] = $type->value;
        }
        $row = QueryUtils::querySingleRow($sql, $binds);
        $total = is_array($row) ? ($row['total'] ?? 0) : 0;

        return is_int($total) || (is_string($total) && ctype_digit($total)) ? intval($total) : 0;
    }

    public function updateStatus(int $id, ReviewStatus $from, ReviewStatus $to, DateTimeImmutable $now, ?int $reviewedBy, ?string $note): bool
    {
        $timestamp = $this->mapper->formatDateTime($now);
        if ($reviewedBy === null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `patient_review_request` SET `status` = ?, `updated_at` = ? WHERE `id` = ? AND `status` = ?',
                [$to->value, $timestamp, $id, $from->value]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `patient_review_request` SET `status` = ?, `updated_at` = ?, `reviewed_by` = ?, `reviewed_at` = ?,'
                . ' `review_note` = ? WHERE `id` = ? AND `status` = ?',
                [$to->value, $timestamp, $reviewedBy, $timestamp, $note, $id, $from->value]
            );
        }

        return QueryUtils::affectedRows() === 1;
    }

    public function replacePayload(int $id, string $summary, array $payload, DateTimeImmutable $now): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `patient_review_request` SET `summary` = ?, `payload` = ?, `payload_format` = ?, `updated_at` = ? WHERE `id` = ?',
            [$summary, $this->mapper->encodePayload($payload), PayloadFormat::Json->value, $this->mapper->formatDateTime($now), $id]
        );
    }

    /**
     * Adds one row to the request's history.
     *
     * @param ?array<array-key, mixed> $previousPayload the payload a replacement overwrote; stored
     *                                                  as JSON in patient_review_event.payload
     */
    public function appendEvent(
        int $requestId,
        ?ReviewStatus $from,
        ReviewStatus $to,
        ReviewActor $actor,
        DateTimeImmutable $now,
        ?string $note,
        ?array $previousPayload = null,
    ): void {
        QueryUtils::sqlInsert(
            'INSERT INTO `patient_review_event` (`request_id`, `from_status`, `to_status`, `actor_type`, `actor_id`, `created_at`, `note`,'
            . ' `payload`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $requestId,
                $from?->value,
                $to->value,
                $actor->type->value,
                $actor->id,
                $this->mapper->formatDateTime($now),
                $note,
                $previousPayload === null ? null : $this->mapper->encodePayload($previousPayload),
            ]
        );
    }

    public function events(int $requestId): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `patient_review_event` WHERE `request_id` = ? ORDER BY `id` ASC',
            [$requestId]
        );
        return array_map($this->mapper->toEvent(...), $rows);
    }

    public function putSecret(int $requestId, string $kind, string $ciphertext, DateTimeImmutable $now): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `patient_review_secret` WHERE `request_id` = ? AND `kind` = ?',
            [$requestId, $kind]
        );
        QueryUtils::sqlInsert(
            'INSERT INTO `patient_review_secret` (`request_id`, `kind`, `ciphertext`, `created_at`) VALUES (?, ?, ?, ?)',
            [$requestId, $kind, $ciphertext, $this->mapper->formatDateTime($now)]
        );
    }

    public function getSecret(int $requestId, string $kind): ?string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT `ciphertext` FROM `patient_review_secret` WHERE `request_id` = ? AND `kind` = ?',
            [$requestId, $kind]
        );
        $ciphertext = is_array($row) ? ($row['ciphertext'] ?? null) : null;

        return is_string($ciphertext) ? $ciphertext : null;
    }

    public function deleteSecrets(int $requestId): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_review_secret` WHERE `request_id` = ?', [$requestId]);
    }
}
