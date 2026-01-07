<?php

/**
 * CcdaTemplateCodes.php - CCDA Template Codes Registry
 *
 * PHP port of oe-blue-button-meta sections_entries_codes
 * Contains template codes for sections and entries
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\CodeSystems;

class CcdaTemplateCodes
{
    /**
     * Template codes registry - matching oe-blue-button-meta structure
     */
    private static array $codes = [
        // Document Level
        'CCD' => [
            'code' => '34133-9',
            'name' => 'Summarization of Episode Note',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        
        // Section Codes
        'AllergiesSection' => [
            'code' => '48765-2',
            'name' => 'Allergies, adverse reactions, alerts',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'MedicationsSection' => [
            'code' => '10160-0',
            'name' => 'History of medication use',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ProblemsSection' => [
            'code' => '11450-4',
            'name' => 'Problem list',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ProceduresSection' => [
            'code' => '47519-4',
            'name' => 'History of procedures',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ResultsSection' => [
            'code' => '30954-2',
            'name' => 'Relevant diagnostic tests and/or laboratory data',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'EncountersSection' => [
            'code' => '46240-8',
            'name' => 'History of encounters',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ImmunizationsSection' => [
            'code' => '11369-6',
            'name' => 'History of immunizations',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'VitalSignsSection' => [
            'code' => '8716-3',
            'name' => 'Vital signs',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'SocialHistorySection' => [
            'code' => '29762-2',
            'name' => 'Social history',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'PlanOfCareSection' => [
            'code' => '18776-5',
            'name' => 'Plan of care note',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'GoalsSection' => [
            'code' => '61146-7',
            'name' => 'Goals',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'HealthConcernsSection' => [
            'code' => '75310-3',
            'name' => 'Health concerns document',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'FunctionalStatusSection' => [
            'code' => '47420-5',
            'name' => 'Functional status assessment',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'MentalStatusSection' => [
            'code' => '10190-7',
            'name' => 'Mental status',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'CareTeamSection' => [
            'code' => '85847-2',
            'name' => 'Patient Care team information',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'PayersSection' => [
            'code' => '48768-6',
            'name' => 'Payment sources',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'MedicalEquipmentSection' => [
            'code' => '46264-8',
            'name' => 'Medical equipment',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'AdvanceDirectivesSection' => [
            'code' => '42348-3',
            'name' => 'Advance directives',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ReasonForReferralSection' => [
            'code' => '42349-1',
            'name' => 'Reason for referral',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'InstructionsSection' => [
            'code' => '69730-0',
            'name' => 'Instructions',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'AssessmentSection' => [
            'code' => '51848-0',
            'name' => 'Assessment',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'AssessmentAndPlanSection' => [
            'code' => '51847-2',
            'name' => 'Assessment and Plan',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'FamilyHistorySection' => [
            'code' => '10157-6',
            'name' => 'History of family member diseases',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'HistoryOfPresentIllnessSection' => [
            'code' => '10164-2',
            'name' => 'History of present illness',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ChiefComplaintSection' => [
            'code' => '10154-3',
            'name' => 'Chief complaint',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ReasonForVisitSection' => [
            'code' => '29299-5',
            'name' => 'Reason for visit',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'ReviewOfSystemsSection' => [
            'code' => '10187-3',
            'name' => 'Review of systems',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'PhysicalExamSection' => [
            'code' => '29545-1',
            'name' => 'Physical findings',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'GeneralStatusSection' => [
            'code' => '10210-3',
            'name' => 'General status',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        
        // Entry Codes
        'AllergyProblemAct' => [
            'code' => '48765-2',
            'name' => 'Allergies, adverse reactions, alerts',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'AllergyConcernAct' => [
            'code' => '48765-2',
            'name' => 'Allergies, adverse reactions, alerts',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'AllergyObservation' => [
            'code' => 'ASSERTION',
            'name' => 'Assertion',
            'code_system' => '2.16.840.1.113883.5.4',
            'code_system_name' => 'ActCode',
        ],
        'AllergyStatusObservation' => [
            'code' => '33999-4',
            'name' => 'Status',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'SeverityObservation' => [
            'code' => 'SEV',
            'name' => 'Severity Observation',
            'code_system' => '2.16.840.1.113883.5.4',
            'code_system_name' => 'ActCode',
        ],
        'ProblemConcernAct' => [
            'code' => 'CONC',
            'name' => 'Concern',
            'code_system' => '2.16.840.1.113883.5.6',
            'code_system_name' => 'HL7ActClass',
        ],
        'ProblemStatus' => [
            'code' => '33999-4',
            'name' => 'Status',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'HealthStatusObservation' => [
            'code' => '11323-3',
            'name' => 'Health status',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'AgeObservation' => [
            'code' => '445518008',
            'name' => 'Age At Onset',
            'code_system' => '2.16.840.1.113883.6.96',
            'code_system_name' => 'SNOMED CT',
        ],
        'MedicationActivity' => [
            'code' => null,
            'name' => null,
            'code_system' => null,
            'code_system_name' => null,
        ],
        'EncounterDiagnosis' => [
            'code' => '29308-4',
            'name' => 'Diagnosis',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'SmokingStatusObservation' => [
            'code' => '72166-2',
            'name' => 'Tobacco smoking status NHIS',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'TobaccoUse' => [
            'code' => '11367-0',
            'name' => 'History of tobacco use',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
        'VitalSignsOrganizer' => [
            'code' => '46680005',
            'name' => 'Vital signs',
            'code_system' => '2.16.840.1.113883.6.96',
            'code_system_name' => 'SNOMED CT',
        ],
        'GoalObservation' => [
            'code' => null,
            'name' => null,
            'code_system' => null,
            'code_system_name' => null,
        ],
        'CareTeamOrganizer' => [
            'code' => '86744-0',
            'name' => 'Care Team',
            'code_system' => '2.16.840.1.113883.6.1',
            'code_system_name' => 'LOINC',
        ],
    ];

    /**
     * Get template code by name
     */
    public static function get(string $name): array
    {
        return self::$codes[$name] ?? [
            'code' => null,
            'name' => $name,
            'code_system' => null,
            'code_system_name' => null,
        ];
    }

    /**
     * Check if template code exists
     */
    public static function exists(string $name): bool
    {
        return isset(self::$codes[$name]);
    }

    /**
     * Add or update a template code
     */
    public static function set(string $name, array $code): void
    {
        self::$codes[$name] = $code;
    }

    /**
     * Get all template codes
     */
    public static function all(): array
    {
        return self::$codes;
    }
}
