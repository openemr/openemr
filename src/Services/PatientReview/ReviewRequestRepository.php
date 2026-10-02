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
            'INSERT INTO `patient_review_request` (`uuid`, `pid`, `type`, `source`, `client_id`, `status`, `summary`,'
            . ' `payload`, `payload_format`, `target_table`, `target_id`, `created_at`, `updated_at`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $uuidBytes,
                $submission->pid,
                $submission->type->value,
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

    public function findForUpdate(int $id): ?ReviewRequest
    {
        $row = QueryUtils::querySingleRow('SELECT * FROM `patient_review_request` WHERE `id` = ? FOR UPDATE', [$id]);
        return is_array($row) && $row !== [] ? $this->mapper->toRequest($row) : null;
    }

    public function findOpen(int $pid, ReviewType $type): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `patient_review_request` WHERE `pid` = ? AND `type` = ? AND `status` IN (?, ?)'
            . ' ORDER BY `created_at` DESC, `id` DESC',
            [$pid, $type->value, ReviewStatus::Pending->value, ReviewStatus::AwaitingPayment->value]
        );
        return array_map($this->mapper->toRequest(...), $rows);
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

    public function appendEvent(int $requestId, ?ReviewStatus $from, ReviewStatus $to, ReviewActor $actor, DateTimeImmutable $now, ?string $note): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `patient_review_event` (`request_id`, `from_status`, `to_status`, `actor_type`, `actor_id`, `created_at`, `note`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$requestId, $from?->value, $to->value, $actor->type->value, $actor->id, $this->mapper->formatDateTime($now), $note]
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
