<?php

/**
 * One patient-submitted request, as stored in patient_review_request.
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

final readonly class ReviewRequest
{
    /**
     * @param string $uuid canonical text form
     * @param array<array-key, mixed> $payload decoded; see PayloadFormat for legacy encodings
     */
    public function __construct(
        public int $id,
        public string $uuid,
        public int $pid,
        public ReviewType $type,
        public ReviewSource $source,
        public ?string $clientId,
        public ReviewStatus $status,
        public string $summary,
        public array $payload,
        public PayloadFormat $payloadFormat,
        public ?string $targetTable,
        public ?string $targetId,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public ?int $reviewedBy,
        public ?DateTimeImmutable $reviewedAt,
        public ?string $reviewNote,
    ) {
    }
}
