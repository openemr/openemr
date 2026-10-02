<?php

/**
 * Turns database rows into ReviewRequest and ReviewEvent objects.
 *
 * Kept apart from the repository so the mapping, including the legacy payload encodings, can be
 * tested without a database. Integer columns arrive as int or numeric string depending on the
 * driver, so they are parsed, not cast.
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
use Ramsey\Uuid\Uuid;

final class ReviewRequestMapper
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param array<array-key, mixed> $row a patient_review_request row
     */
    public function toRequest(array $row): ReviewRequest
    {
        $format = PayloadFormat::from($this->string($row, 'payload_format'));
        $uuid = $this->string($row, 'uuid');

        return new ReviewRequest(
            id: $this->int($row, 'id'),
            uuid: strlen($uuid) === 16 ? Uuid::fromBytes($uuid)->toString() : $uuid,
            pid: $this->int($row, 'pid'),
            type: ReviewType::from($this->string($row, 'type')),
            source: ReviewSource::from($this->string($row, 'source')),
            clientId: $this->nullableString($row, 'client_id'),
            status: ReviewStatus::from($this->string($row, 'status')),
            summary: $this->nullableString($row, 'summary') ?? '',
            payload: $this->decodePayload($this->nullableString($row, 'payload'), $format),
            payloadFormat: $format,
            targetTable: $this->nullableString($row, 'target_table'),
            targetId: $this->nullableString($row, 'target_id'),
            createdAt: $this->dateTime($row, 'created_at'),
            updatedAt: $this->dateTime($row, 'updated_at'),
            reviewedBy: $this->nullableInt($row, 'reviewed_by'),
            reviewedAt: $this->nullableDateTime($row, 'reviewed_at'),
            reviewNote: $this->nullableString($row, 'review_note'),
        );
    }

    /**
     * @param array<array-key, mixed> $row a patient_review_event row
     */
    public function toEvent(array $row): ReviewEvent
    {
        $from = $this->nullableString($row, 'from_status');

        return new ReviewEvent(
            id: $this->int($row, 'id'),
            requestId: $this->int($row, 'request_id'),
            from: $from === null ? null : ReviewStatus::from($from),
            to: ReviewStatus::from($this->string($row, 'to_status')),
            actorType: ReviewActorType::from($this->string($row, 'actor_type')),
            actorId: $this->nullableInt($row, 'actor_id'),
            createdAt: $this->dateTime($row, 'created_at'),
            note: $this->nullableString($row, 'note'),
        );
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public function encodePayload(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    public function formatDateTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->format(self::DATETIME_FORMAT);
    }

    /**
     * A payload that is not a map (legacy raw text, or a scalar) comes back as ['value' => ...]
     * so callers always get an array.
     *
     * @return array<array-key, mixed>
     */
    private function decodePayload(?string $payload, PayloadFormat $format): array
    {
        if ($payload === null || $payload === '') {
            return [];
        }
        $decoded = match ($format) {
            PayloadFormat::Json => json_decode($payload, true, 512, JSON_THROW_ON_ERROR),
            // Legacy onsite_portal_activity.table_args for profile changes. Objects are never
            // revived: the data is patient-submitted.
            PayloadFormat::PhpSerialized => unserialize($payload, ['allowed_classes' => false]),
            PayloadFormat::Text => $payload,
        };

        return is_array($decoded) ? $decoded : ['value' => $decoded];
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function string(array $row, string $column): string
    {
        $value = $row[$column] ?? null;
        if (!is_string($value)) {
            throw new \UnexpectedValueException('Column ' . $column . ' must be a string');
        }
        return $value;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function nullableString(array $row, string $column): ?string
    {
        $value = $row[$column] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new \UnexpectedValueException('Column ' . $column . ' must be a string or null');
        }
        return $value;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function int(array $row, string $column): int
    {
        $value = $this->nullableInt($row, $column);
        if ($value === null) {
            throw new \UnexpectedValueException('Column ' . $column . ' must be an integer');
        }
        return $value;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function nullableInt(array $row, string $column): ?int
    {
        $value = $row[$column] ?? null;
        if ($value === null) {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return intval($value);
        }
        throw new \UnexpectedValueException('Column ' . $column . ' must be an integer or null');
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function dateTime(array $row, string $column): DateTimeImmutable
    {
        $value = $this->nullableDateTime($row, $column);
        if ($value === null) {
            throw new \UnexpectedValueException('Column ' . $column . ' must be a datetime');
        }
        return $value;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function nullableDateTime(array $row, string $column): ?DateTimeImmutable
    {
        $value = $this->nullableString($row, $column);
        if ($value === null || $value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }
        $dateTime = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $value);
        if ($dateTime === false) {
            throw new \UnexpectedValueException('Column ' . $column . ' is not a datetime');
        }
        return $dateTime;
    }
}
