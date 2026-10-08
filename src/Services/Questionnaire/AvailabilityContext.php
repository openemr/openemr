<?php

/**
 * The situation a questionnaire availability question is being asked in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

/**
 * Every scope an assignment row can be narrowed by, in one object.
 *
 * A null member means "the caller does not know or does not care", which matches any
 * assignment. A non-null member matches an assignment whose corresponding column is
 * either null (the assignment is unscoped on that axis) or equal.
 */
final readonly class AvailabilityContext
{
    public function __construct(
        public QuestionnaireSurface $surface,
        public ?int $pid = null,
        public ?int $encounter = null,
        public ?string $visitCategory = null,
        public ?int $facility = null,
        public ?int $provider = null,
        public ?string $clientId = null,
    ) {
    }

    public function withVisitCategory(?string $visitCategory): self
    {
        return new self(
            $this->surface,
            $this->pid,
            $this->encounter,
            $visitCategory,
            $this->facility,
            $this->provider,
            $this->clientId,
        );
    }
}
