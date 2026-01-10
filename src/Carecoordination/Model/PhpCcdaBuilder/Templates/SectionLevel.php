<?php

/**
 * SectionLevel.php - Section-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/sectionLevel2.js
 * Contains templates for all CCDA sections (allergies, medications, problems, etc.)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates;

use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\Condition;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\FieldLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\LeafLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\Translate;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\EntryLevel\EntryLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\EntryLevel\SharedEntryLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\CodeSystems\CcdaTemplateCodes;

class SectionLevel
{
    private const NDA = "No Data Available";

    /**
     * Template codes for sections (from oe-blue-button-meta)
     */
    private const TEMPLATE_CODES = [
        'AllergiesSection' => ['code' => '48765-2', 'name' => 'Allergies and adverse reactions', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'MedicationsSection' => ['code' => '10160-0', 'name' => 'History of Medication use Narrative', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'ProblemSection' => ['code' => '11450-4', 'name' => 'Problem list - Reported', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'ProceduresSection' => ['code' => '47519-4', 'name' => 'History of Procedures Document', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'ResultsSection' => ['code' => '30954-2', 'name' => 'Relevant diagnostic tests/laboratory', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'EncountersSection' => ['code' => '46240-8', 'name' => 'History of Hospitalizations+Outpatient', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'ImmunizationsSection' => ['code' => '11369-6', 'name' => 'History of Immunization Narrative', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'PayersSection' => ['code' => '48768-6', 'name' => 'Payment sources', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'PlanOfCareSection' => ['code' => '18776-5', 'name' => 'Plan of care note', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'GoalSection' => ['code' => '61146-7', 'name' => 'Goals', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'SocialHistorySection' => ['code' => '29762-2', 'name' => 'Social history Narrative', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'VitalSignsSection' => ['code' => '8716-3', 'name' => 'Vital signs', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'CareTeamSection' => ['code' => '85847-2', 'name' => 'Care Team', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'MedicalEquipmentSection' => ['code' => '46264-8', 'name' => 'History of medical device use', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'FunctionalStatusSection' => ['code' => '47420-5', 'name' => 'Functional status assessment note', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'MentalStatusSection' => ['code' => '10190-7', 'name' => 'Mental status Narrative', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'AssessmentSection' => ['code' => '51848-0', 'name' => 'Evaluation note', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'AdvanceDirectivesSection' => ['code' => '42348-3', 'name' => 'Advance Directives', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'ReasonForReferralSection' => ['code' => '42349-1', 'name' => 'Reason for referral', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
        'HealthConcernSection' => ['code' => '75310-3', 'name' => 'Health concerns Document', 'code_system' => '2.16.840.1.113883.6.1', 'code_system_name' => 'LOINC'],
    ];

    /**
     * Generate template code element
     */
    private static function templateCode(string $name): array
    {
        $raw = self::TEMPLATE_CODES[$name] ?? null;
        if (!$raw) {
            return [];
        }
        return [
            'key' => 'code',
            'attributes' => [
                'code' => $raw['code'],
                'displayName' => $raw['name'],
                'codeSystem' => $raw['code_system'],
                'codeSystemName' => $raw['code_system_name'],
            ],
        ];
    }

    /**
     * Generate template title element
     */
    private static function templateTitle(string $name): array
    {
        $raw = self::TEMPLATE_CODES[$name] ?? null;
        return [
            'key' => 'title',
            'text' => fn() => $raw['name'] ?? $name,
        ];
    }

    /**
     * Allergies Section (entries required)
     */
    public static function allergiesSectionEntriesRequired(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('allergies')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.6.1', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.6.1'),
                    self::templateCode('AllergiesSection'),
                    self::templateTitle('AllergiesSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => 'No known Allergies and Intolerances',
                        'existsWhen' => fn($input) => !empty($input['allergies'][0]['no_know_allergies']),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            EntryLevel::allergyProblemAct(),
                            EntryLevel::allergyProblemActNKA(),
                        ],
                        'dataKey' => 'allergies',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Medications Section (entries required)
     */
    public static function medicationsSectionEntriesRequired(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('medications')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.1.1', '2014-06-09'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.1.1'),
                    self::templateCode('MedicationsSection'),
                    self::templateTitle('MedicationsSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('medications'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::medicationActivity(), 'required' => true],
                        ],
                        'dataKey' => 'medications',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Problems Section (entries required)
     */
    public static function problemsSectionEntriesRequired(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('problems')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.5.1', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.5.1'),
                    self::templateCode('ProblemSection'),
                    self::templateTitle('ProblemSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('problems'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::problemConcernAct(), 'required' => true],
                        ],
                        'dataKey' => 'problems',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Procedures Section (entries required)
     */
    public static function proceduresSectionEntriesRequired(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('procedures')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.7'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.7.1'),
                    self::templateCode('ProceduresSection'),
                    self::templateTitle('ProceduresSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('procedures'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'content' => [
                            // Only one of these will render based on procedure_type
                            EntryLevel::procedureActivityProcedure(), // Default - renders for type=procedure or unset
                        ],
                        'dataKey' => 'procedures',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Results Section (entries required)
     */
    public static function resultsSectionEntriesRequired(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('results')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.3.1', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.3.1'),
                    self::templateCode('ResultsSection'),
                    self::templateTitle('ResultsSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('results'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::resultOrganizer(), 'required' => true],
                        ],
                        'dataKey' => 'results',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Encounters Section (entries optional)
     */
    public static function encountersSectionEntriesOptional(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('encounters')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.22.1', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.22.1'),
                    self::templateCode('EncountersSection'),
                    self::templateTitle('EncountersSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('encounters'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::encounterActivities(), 'required' => true],
                        ],
                        'dataKey' => 'encounters',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Immunizations Section (entries optional)
     */
    public static function immunizationsSectionEntriesOptional(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('immunizations')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.2'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.2.1'),
                    self::templateCode('ImmunizationsSection'),
                    self::templateTitle('ImmunizationsSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('immunizations'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::immunizationActivity(), 'required' => true],
                        ],
                        'dataKey' => 'immunizations',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Vital Signs Section (entries optional)
     */
    public static function vitalSignsSectionEntriesOptional(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('vitals')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.4.1', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.4.1'),
                    self::templateCode('VitalSignsSection'),
                    self::templateTitle('VitalSignsSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('vitals'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::vitalSignsOrganizer(), 'required' => true],
                        ],
                        'dataKey' => 'vitals',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Social History Section
     */
    public static function socialHistorySection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('social_history')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.17', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.17'),
                    self::templateCode('SocialHistorySection'),
                    self::templateTitle('SocialHistorySection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('social_history'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [EntryLevel::smokingStatusObservation()],
                        'dataKey' => 'social_history',
                        'existsWhen' => Condition::propertyNotEmpty('value'),
                    ],
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [EntryLevel::genderStatusObservation()],
                        'dataKey' => 'social_history',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Care Team Section
     */
    public static function careTeamSection(array $htmlHeader = [], string $na = ''): array
    {
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('care_team')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.500', '2019-07-01'),
                    self::templateCode('CareTeamSection'),
                    self::templateTitle('CareTeamSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => 'A Care Team is not assigned.',
                        'existsWhen' => Condition::keyDoesntExist('care_team'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'content' => EntryLevel::careTeamOrganizer(),
                        'existsWhen' => Condition::keyExists('care_team'),
                    ],
                ],
            ]],
        ];
    }

    /**
     * Payers Section
     */
    public static function payersSection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('payers')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.18'),
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.18', '2015-08-01'),
                    self::templateCode('PayersSection'),
                    self::templateTitle('PayersSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('payers'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::coverageActivity(), 'required' => true],
                        ],
                        'dataKey' => 'payers',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Plan of Care Section
     */
    public static function planOfCareSection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('plan_of_care')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.10', '2014-06-09'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.10'),
                    self::templateCode('PlanOfCareSection'),
                    self::templateTitle('PlanOfCareSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('plan_of_care'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => [
                            'typeCode' => fn($input) => ($input['type'] ?? '') === 'observation' ? 'DRIV' : null,
                        ],
                        'content' => [
                            EntryLevel::planOfCareActivityAct(),
                            EntryLevel::planOfCareActivityObservation(),
                        ],
                        'dataKey' => 'plan_of_care',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Goals Section
     */
    public static function goalSection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('goals')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.60'),
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.60', '2015-08-01'),
                    self::templateCode('GoalSection'),
                    self::templateTitle('GoalSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('goals'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => [
                            'typeCode' => fn($input) => ($input['type'] ?? '') === 'observation' ? 'DRIV' : null,
                        ],
                        'content' => [EntryLevel::goalActivityObservation()],
                        'dataKey' => 'goals',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Advance Directives Section
     */
    public static function advanceDirectivesSection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('advance_directives')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.21.1', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.21.1'),
                    self::templateCode('AdvanceDirectivesSection'),
                    self::templateTitle('AdvanceDirectivesSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('advance_directives'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [
                            [EntryLevel::advanceDirectiveObservation(), 'required' => true],
                        ],
                        'dataKey' => 'advance_directives',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Health Concerns Section
     */
    public static function healthConcernSection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('concern')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.58', '2015-08-01'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.58'),
                    self::templateCode('HealthConcernSection'),
                    self::templateTitle('HealthConcernSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('concern'),
                    ],
                    $htmlHeader,
                    FieldLevel::author(),
                    [
                        'key' => 'entry',
                        'content' => [[EntryLevel::healthConcernActivityAct()]],
                        'dataKey' => 'concern',
                        'existsWhen' => Condition::keyExists('value'),
                    ],
                ],
                'dataKey' => 'health_concerns',
            ]],
        ];
    }

    /**
     * Medical Equipment Section (entries optional)
     */
    public static function medicalEquipmentSectionEntriesOptional(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('medical_devices')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.23', '2014-06-09'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.23'),
                    self::templateCode('MedicalEquipmentSection'),
                    self::templateTitle('MedicalEquipmentSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('medical_devices'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'content' => [EntryLevel::medicalDeviceActivityProcedure()],
                        'dataKey' => 'medical_devices',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Functional Status Section
     */
    public static function functionalStatusSection(array $htmlHeader = [], string $na = ''): array
    {
        $na = $na ?: self::NDA;
        return [
            'key' => 'component',
            'content' => [[
                'key' => 'section',
                'attributes' => fn($input) => Condition::isNullFlavorSection('functional_status')($input) ? ['nullFlavor' => 'NI'] : [],
                'content' => [
                    FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.2.14', '2014-06-09'),
                    FieldLevel::templateId('2.16.840.1.113883.10.20.22.2.14'),
                    self::templateCode('FunctionalStatusSection'),
                    self::templateTitle('FunctionalStatusSection'),
                    [
                        'key' => 'text',
                        'text' => fn() => $na,
                        'existsWhen' => Condition::keyDoesntExist('functional_status'),
                    ],
                    $htmlHeader,
                    [
                        'key' => 'entry',
                        'attributes' => ['typeCode' => 'DRIV'],
                        'content' => [EntryLevel::functionalStatusOrganizer()],
                        'dataKey' => 'functional_status',
                    ],
                ],
            ]],
        ];
    }
}
