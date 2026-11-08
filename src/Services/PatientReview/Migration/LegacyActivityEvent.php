<?php

/**
 * One patient_review_event row written by the onsite_portal_activity migration.
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
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewStatus;

final readonly class LegacyActivityEvent
{
    public function __construct(
        public ?ReviewStatus $from,
        public ReviewStatus $to,
        public ReviewActor $actor,
        public DateTimeImmutable $at,
        public ?string $note,
    ) {
    }
}
