<?php

/**
 * How one onsite_portal_activity row becomes a patient_review_request: the request columns,
 * its history events and, for an open payment, its card secret.
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
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;

final readonly class LegacyActivityPlan
{
    /**
     * @param list<LegacyActivityEvent> $events oldest first; the first creates the request
     * @param ?string $cardCiphertext the legacy checksum, already encrypted, for an open payment
     * @param ?string $unrecognisedStatus the legacy status when it was not one the mapping knows
     */
    public function __construct(
        public int $legacyId,
        public int $pid,
        public ReviewType $type,
        public ReviewStatus $status,
        public string $summary,
        public ?string $payload,
        public PayloadFormat $payloadFormat,
        public ?string $targetTable,
        public ?string $targetId,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public ?int $reviewedBy,
        public ?DateTimeImmutable $reviewedAt,
        public ?string $reviewNote,
        public array $events,
        public ?string $cardCiphertext,
        public ?string $unrecognisedStatus,
    ) {
    }
}
