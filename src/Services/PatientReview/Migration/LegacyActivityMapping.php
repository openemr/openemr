<?php

/**
 * The rules for turning an onsite_portal_activity row into a review request (#14337).
 *
 * The legacy row spreads one state over status, pending_action, action_taken and
 * require_audit. Only status decides the new state:
 *
 *   waiting              -> the type's first state (pending; awaiting_payment for an invoice)
 *   waiting transaction  -> the type's first state
 *   closed               -> completed
 *   deleted              -> cancelled
 *   editing              -> cancelled (a portal document the patient never sent for review)
 *   anything else        -> the type's first state, with the legacy status in the event note,
 *                           so it stays in front of staff and nothing is closed silently
 *
 * action_taken becomes the note on the closing event and the request's review_note;
 * action_user and action_taken_time become reviewed_by and reviewed_at when a user closed it.
 * Profile payloads stay PHP-serialized and everything else stays text, as stored; the mapper
 * reads both. The encrypted card data in checksum moves to patient_review_secret for a payment
 * that is still open and is dropped for a closed one.
 *
 * Rows whose activity is not profile, payment, document or invoice, that have no patient, or
 * that are documents without a document id (nothing could find their request again), are not
 * migrated (plan() returns null).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Migration;

use DateTimeImmutable;
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;

final class LegacyActivityMapping
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const SUMMARY_LENGTH = 255;

    /** Actor name recorded on events the migration had to attribute to the system. */
    public const SYSTEM_ACTOR = 'onsite_portal_activity migration';

    /** Note on a document the patient was still editing. */
    public const NOTE_NEVER_SUBMITTED = 'migrated: document was being edited and never sent for review';

    /** Prefix of the note on a row whose status the mapping does not know. */
    public const NOTE_UNRECOGNISED = 'migrated from unrecognised legacy status: ';

    /**
     * @param array<array-key, mixed> $row an onsite_portal_activity row
     * @param DateTimeImmutable $now used when the row has no date at all
     */
    public function plan(array $row, DateTimeImmutable $now): ?LegacyActivityPlan
    {
        $legacyId = $this->int($row['id'] ?? null);
        $pid = $this->int($row['patient_id'] ?? null);
        $type = ReviewType::tryFrom($this->string($row['activity'] ?? null));
        if ($legacyId === null || $pid === null || $type === null || $type === ReviewType::FhirWrite) {
            return null;
        }
        $documentId = $this->int($row['table_args'] ?? null);
        if ($type === ReviewType::Document && $documentId === null) {
            return null;
        }

        $legacyStatus = trim($this->string($row['status'] ?? null));
        [$status, $statusNote, $unrecognised] = $this->status($type, $legacyStatus);

        $createdAt = $this->dateTime($row['date'] ?? null) ?? $this->dateTime($row['action_taken_time'] ?? null) ?? $now;
        $actionTime = $this->dateTime($row['action_taken_time'] ?? null);
        $closed = !$status->isOpen();
        $updatedAt = $closed && $actionTime !== null && $actionTime > $createdAt ? $actionTime : $createdAt;

        $actionUser = $this->int($row['action_user'] ?? null);
        // An invoice's action_user is the patient it was sent to, not a reviewer.
        $reviewedBy = $closed && $type !== ReviewType::Invoice ? $actionUser : null;
        $actionTaken = trim($this->string($row['action_taken'] ?? null));
        $reviewNote = $closed && $actionTaken !== '' ? $actionTaken : null;

        $initial = $type->initialStatus();
        $events = [new LegacyActivityEvent(
            null,
            $initial,
            $type === ReviewType::Invoice ? ReviewActor::system(self::SYSTEM_ACTOR) : ReviewActor::patient($pid, ''),
            $createdAt,
            $status === $initial ? $statusNote : null,
        )];
        if ($status !== $initial) {
            $events[] = new LegacyActivityEvent(
                $initial,
                $status,
                $reviewedBy !== null ? ReviewActor::user($reviewedBy, '') : ReviewActor::system(self::SYSTEM_ACTOR),
                $updatedAt,
                $this->joinNotes($reviewNote, $statusNote),
            );
        }

        [$targetTable, $targetId] = $this->target($type, $pid, $documentId);
        $payload = $this->nullableString($row['table_args'] ?? null);
        $checksum = trim($this->string($row['checksum'] ?? null));

        return new LegacyActivityPlan(
            legacyId: $legacyId,
            pid: $pid,
            type: $type,
            status: $status,
            summary: mb_substr($this->string($row['narrative'] ?? null), 0, self::SUMMARY_LENGTH),
            payload: $payload === '' ? null : $payload,
            payloadFormat: $type === ReviewType::Profile ? PayloadFormat::PhpSerialized : PayloadFormat::Text,
            targetTable: $targetTable,
            targetId: $targetId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            reviewedBy: $reviewedBy,
            reviewedAt: $reviewedBy !== null ? $actionTime : null,
            reviewNote: $reviewNote,
            events: $events,
            cardCiphertext: $type === ReviewType::Payment && !$closed && $checksum !== '' && $checksum !== '0' ? $checksum : null,
            unrecognisedStatus: $unrecognised ? $legacyStatus : null,
        );
    }

    /**
     * @return array{ReviewStatus, ?string, bool} the status, an event note, whether the legacy
     *                                            status was unrecognised
     */
    private function status(ReviewType $type, string $legacyStatus): array
    {
        return match ($legacyStatus) {
            'waiting', 'waiting transaction' => [$type->initialStatus(), null, false],
            'closed' => [ReviewStatus::Completed, null, false],
            'deleted' => [ReviewStatus::Cancelled, null, false],
            'editing' => $type === ReviewType::Document
                ? [ReviewStatus::Cancelled, self::NOTE_NEVER_SUBMITTED, false]
                : [$type->initialStatus(), self::NOTE_UNRECOGNISED . $legacyStatus, true],
            default => [$type->initialStatus(), self::NOTE_UNRECOGNISED . ($legacyStatus === '' ? '(empty)' : $legacyStatus), true],
        };
    }

    /**
     * @return array{?string, ?string}
     */
    private function target(ReviewType $type, int $pid, ?int $documentId): array
    {
        return match ($type) {
            ReviewType::Profile => ['patient_data', (string) $pid],
            ReviewType::Document => $documentId === null ? [null, null] : [PortalReviewWorkflows::DOCUMENT_TABLE, (string) $documentId],
            ReviewType::Payment, ReviewType::Invoice, ReviewType::FhirWrite => [null, null],
        };
    }

    private function joinNotes(?string ...$notes): ?string
    {
        $present = array_values(array_filter($notes, static fn(?string $note): bool => $note !== null && $note !== ''));

        return $present === [] ? null : implode('; ', $present);
    }

    private function int(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (is_string($value) && preg_match('/^\s*[1-9]\d*\s*$/', $value) === 1) {
            return intval(trim($value));
        }
        return null;
    }

    private function string(mixed $value): string
    {
        return is_string($value) ? $value : (is_int($value) ? (string) $value : '');
    }

    private function nullableString(mixed $value): ?string
    {
        return $value === null ? null : $this->string($value);
    }

    private function dateTime(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || $value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }
        $dateTime = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $value);

        return $dateTime === false ? null : $dateTime;
    }
}
