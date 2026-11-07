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

    public function setting(): GlobalConnectorsEnum
    {
        return match ($this) {
            self::QuestionnaireResponse => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_QUESTIONNAIRE_RESPONSE,
            self::VitalSignObservation => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_VITAL_SIGNS,
            self::AllergyIntolerance => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_ALLERGY_INTOLERANCE,
            self::Condition => GlobalConnectorsEnum::FHIR_PATIENT_WRITE_CONDITION,
        };
    }

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
