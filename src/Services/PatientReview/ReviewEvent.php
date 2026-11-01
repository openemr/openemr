<?php

/**
 * One state change of a review request, as stored in patient_review_event.
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

final readonly class ReviewEvent
{
    /**
     * @param ?ReviewStatus $from null for the event that created the request
     */
    public function __construct(
        public int $id,
        public int $requestId,
        public ?ReviewStatus $from,
        public ReviewStatus $to,
        public ReviewActorType $actorType,
        public ?int $actorId,
        public DateTimeImmutable $createdAt,
        public ?string $note,
    ) {
    }
}
