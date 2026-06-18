<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\CodeTypes;

class CodeTypesMappings
{
    public const SNOMED_ENCOUNTER_TYPE_MAPPINGS = [
        'visit-after-hours' => '185463005',
        'visit-after-hours-not-night' => '185464004',
        'weekend-visit' => '185465003',
        'office-visit' => '30346009',
        'established-patient' => '3391000175108',
        'new-patient' => '37894004',
        'postoperative-follow-up' => '439740005'
    ];

    public const SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS = [
        // note only thing not mapped here is 'other' as we don't know what that goes here
        // note valueset OID is: 2.16.840.1.113883.3.526.3.1008
        'religious_exemption' => '183945002',
        'patient_decision' => '105480006',
        'parental_decision' => '105480006', // patient and parental refuse are considered the same
        'financial_problem' => '160932005',
        'financial_circumstances_change' => '160934006',
        'alternative_treatment_requested' => '182890002',
        'patient_declined_procedure' => '105480006',
        'patient_declined_drug' => '182895007',
        'patient_declined_drug_effects' => '182897004',
        'patient_declined_drug_beliefs' => '182900006',
        'patient_declined_drug_cannot_pay' => '182902003',
        'patient_moved' => '184081006',
        'patient_dissatisfied_result' => '185479006',
        'patient_dissatisfied_doctor' => '185481008',
        'patient_variable_income' => '224187001',
        'patient_self_discharge' => '225928004',
        'drugs_not_completed' => '266710000',
        'family_illness' => '266966009',
        'follow_defaulted' => '275694009',
        'patient_noncompliance' => '275936005',
        'patient_noshow' => '281399006',
        'patient_further_opinion' => '310343007',
        'patient_treatment_delay' => '373787003',
        'patient_medication_declined' => '406149000',
        'patient_medication_forgot' => '408367005',
        'patient_non_compliant' => '413311005',
        'procedure_not_wanted' => '416432009',
        'income_insufficient' => '423656007',
        'income_necessities_only' => '424739004',
        'refused' => '443390004',
        'patient_procedure_discontinued' => '713247000'
    ];

    public const CPT4_ENCOUNTER_TYPE_MAPPINGS = [
        'new-patient-10' => 'New Patient (Brief)',
        'new-patient-15-29' => 'New Patient (Limited)',
        'new-patient-30-44' => 'Level 3, New Patient, Office Visit',
        'new-patient-45-59' => 'Extended Physical Exam',
        'new-patient-60-74' => 'New Exam (Comprehensive)',
        'established-patient-10-19' => 'Established Patient (Limited)',
        'established-patient-20-29' => 'Established Patient (Detailed)',
        'established-patient-30-39' => 'Established Patient (Extended)',
        'established-patient-40-54' => 'Established Patient (Comprehensive)',
    ];

    public const CODE_TYPE_SNOMED = "SNOMED";
    public const CODE_TYPE_SNOMED_CT = "SNOMED-CT";
    public const CODE_TYPE_SNOMED_PR = "SNOMED-PR";
    public const CODE_TYPE_CPT4 = "CPT4";

    public const LIST_ID_ENCOUNTER_TYPES = 'encounter-types';
    public const LIST_ID_IMMUNIZATION_REFUSAL = 'immunization_refusal_reason';
}
