<?php

/**
 * The FHIR resource type a patient-app write would create or change, e.g. Condition.
 *
 * Stored in patient_review_request.resource_type for ReviewType::FhirWrite requests. A type of
 * its own so it cannot be confused with the other strings on a submission (client id, target
 * table).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

final readonly class FhirResourceType
{
    /** FHIR resource type names: an upper-case letter, then letters; fits the column. */
    private const PATTERN = '/^[A-Z][A-Za-z]{0,63}$/';

    public function __construct(public string $value)
    {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new \DomainException('Not a FHIR resource type name');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
