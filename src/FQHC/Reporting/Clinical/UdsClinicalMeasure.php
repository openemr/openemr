<?php

/**
 * UDS Table 6B clinical quality measures, mapped to the CY2026 CMS eCQM that
 * computes each one.
 *
 * OpenEMR already computes these eCQMs via the CQM/AMC/CDR engine
 * (`src/Cqm/`, `src/Services/Qdm/`). Most UDS clinical measures are the same
 * underlying eCQM, so this is a measure *map* — UDS measure name to CMS eCQM
 * id/version — not a new measure engine. Backed by the CMS eCQM id because
 * that value is what selects the measure definition from the engine and is
 * exchanged with it. Versions must be reconciled against the current-year UDS
 * Manual and CMS eCQM specification set before a reporting year is certified
 * "done" (see docs/fqhc/UDS-DATA-MODEL.md §3); treat this map as versioned
 * data, not a hard-coded constant, when a new reporting year lands.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\Clinical;

enum UdsClinicalMeasure: string
{
    case ChildhoodImmunizationStatus = 'CMS117v14';
    case CervicalCancerScreening = 'CMS124v14';
    case BreastCancerScreening = 'CMS125v14';
    case WeightAssessmentChildrenAdolescents = 'CMS155v14';
    case PreventiveCareBmiScreeningFollowUp = 'CMS69v14';
    case TobaccoUseScreeningCessation = 'CMS138v14';
    case StatinTherapyForCvdPreventionTreatment = 'CMS347v9';
    case ColorectalCancerScreening = 'CMS130v14';
    case HivScreening = 'CMS349v8';
    case DepressionScreeningFollowUp = 'CMS2v15';
    case DepressionRemissionAtTwelveMonths = 'CMS159v15';
    case InitiationEngagementOfSudTreatment = 'CMS137v14';
    case ControllingHighBloodPressure = 'CMS165v14';
    case DiabetesGlycemicStatusAssessment = 'CMS122v14';

    public function cmsId(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::ChildhoodImmunizationStatus => 'Childhood Immunization Status',
            self::CervicalCancerScreening => 'Cervical Cancer Screening',
            self::BreastCancerScreening => 'Breast Cancer Screening',
            self::WeightAssessmentChildrenAdolescents => 'Weight Assessment & Counseling, Children/Adolescents',
            self::PreventiveCareBmiScreeningFollowUp => 'Preventive Care & BMI Screening and Follow-Up',
            self::TobaccoUseScreeningCessation => 'Tobacco Use: Screening & Cessation',
            self::StatinTherapyForCvdPreventionTreatment => 'Statin Therapy for CVD Prevention/Treatment',
            self::ColorectalCancerScreening => 'Colorectal Cancer Screening',
            self::HivScreening => 'HIV Screening',
            self::DepressionScreeningFollowUp => 'Depression Screening & Follow-Up',
            self::DepressionRemissionAtTwelveMonths => 'Depression Remission at Twelve Months',
            self::InitiationEngagementOfSudTreatment => 'Initiation & Engagement of SUD Treatment',
            self::ControllingHighBloodPressure => 'Controlling High Blood Pressure',
            self::DiabetesGlycemicStatusAssessment => 'Diabetes: Glycemic Status Assessment > 9%',
        };
    }

    /**
     * Whether this measure's rate also feeds a UDS Table 7 health-outcomes
     * disparity line (docs/fqhc/UDS-DATA-MODEL.md §3). Table 7 also reports
     * early entry into prenatal care and low birth weight, which are not
     * eCQM-backed and are out of scope for this measure map.
     */
    public function feedsTable7(): bool
    {
        return match ($this) {
            self::ControllingHighBloodPressure, self::DiabetesGlycemicStatusAssessment => true,
            self::ChildhoodImmunizationStatus,
            self::CervicalCancerScreening,
            self::BreastCancerScreening,
            self::WeightAssessmentChildrenAdolescents,
            self::PreventiveCareBmiScreeningFollowUp,
            self::TobaccoUseScreeningCessation,
            self::StatinTherapyForCvdPreventionTreatment,
            self::ColorectalCancerScreening,
            self::HivScreening,
            self::DepressionScreeningFollowUp,
            self::DepressionRemissionAtTwelveMonths,
            self::InitiationEngagementOfSudTreatment => false,
        };
    }
}
