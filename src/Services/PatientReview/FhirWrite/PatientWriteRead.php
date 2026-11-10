<?php

/**
 * The result of a patient app reading an id: what the queue holds there for the app, and the
 * resource to answer with when it is still held.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

final readonly class PatientWriteRead
{
    /**
     * @param ?array<array-key, mixed> $resource the held resource; set only when the outcome is Held
     */
    private function __construct(
        public PatientWriteReadOutcome $outcome,
        public ?array $resource,
    ) {
    }

    /**
     * @param array<array-key, mixed> $resource
     */
    public static function held(array $resource): self
    {
        return new self(PatientWriteReadOutcome::Held, $resource);
    }

    public static function gone(): self
    {
        return new self(PatientWriteReadOutcome::Gone, null);
    }

    public static function notHeld(): self
    {
        return new self(PatientWriteReadOutcome::NotHeld, null);
    }
}
