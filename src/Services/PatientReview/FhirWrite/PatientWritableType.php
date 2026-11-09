<?php

/**
 * The kinds of record a site can open to patient app writes.
 *
 * These aren't FHIR resource types one for one: only the vital sign category of Observation can
 * be opened, so a survey or lab Observation stays refused.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\PatientReview\FhirResourceType;

enum PatientWritableType
{
    case QuestionnaireResponse;
    case VitalSignObservation;
    case AllergyIntolerance;
    case Condition;

    /**
     * The site setting that opens this type to patient app writes.
     */
    public function setting(): GlobalConnectorsEnum
    {
        return match ($this) {
            self::QuestionnaireResponse => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_QUESTIONNAIRE_RESPONSE,
            self::VitalSignObservation => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_VITAL_SIGNS,
            self::AllergyIntolerance => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_ALLERGY_INTOLERANCE,
            self::Condition => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_CONDITION,
        };
    }

    /**
     * Whether the FHIR API accepts this type from patient apps yet. A type the API doesn't serve
     * is never advertised and never let through the permission gates, whatever its setting says:
     * its approval and read-back rules aren't in place, so a write could be held but not decided.
     */
    public function servedByApi(): bool
    {
        return match ($this) {
            self::QuestionnaireResponse => true,
            self::VitalSignObservation, self::AllergyIntolerance, self::Condition => false,
        };
    }

    /**
     * The kind of write a held request is, from the resource type it was filed under. An
     * Observation is a vital sign: no other Observation can be held.
     */
    public static function forResourceType(FhirResourceType $resourceType): ?self
    {
        foreach (self::cases() as $type) {
            if ($type->fhirResourceType()->equals($resourceType)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The FHIR resource type a held write of this kind is filed under in the review queue.
     */
    public function fhirResourceType(): FhirResourceType
    {
        return new FhirResourceType(match ($this) {
            self::QuestionnaireResponse => 'QuestionnaireResponse',
            self::VitalSignObservation => 'Observation',
            self::AllergyIntolerance => 'AllergyIntolerance',
            self::Condition => 'Condition',
        });
    }
}
