<?php

/**
 * FunctionalStatusEntryLevel.php - Functional Status entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/functionalStatusEntryLevel.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\EntryLevel;

use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\Condition;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\FieldLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\LeafLevel;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\Translate;

class FunctionalStatusEntryLevel
{
    /**
     * Mental Status Observation
     * JS: exports.mentalStatusObservation
     */
    public static function mentalStatusObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.74', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.74'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => '373930000',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                        'codeSystemName' => 'SNOMED CT',
                        'displayName' => 'Cognitive function',
                    ],
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => [
                                'code' => '75275-8',
                                'codeSystem' => '2.16.840.1.113883.6.1',
                                'codeSystemName' => 'LOINC',
                                'displayName' => 'Cognitive function',
                            ],
                        ],
                    ],
                ],
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        LeafLevel::code()
                    ),
                    'dataKey' => 'value',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                ],
                FieldLevel::author(),
            ],
        ];
    }

    /**
     * Functional Status Observation
     * JS: functionalStatusObservation (private)
     */
    public static function functionalStatusObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.67', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.67'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => '54522-8',
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'displayName' => 'Functional status',
                    ],
                    'content' => [
                        [
                            'key' => 'originalText',
                            'content' => [
                                [
                                    'key' => 'reference',
                                    'attributes' => [
                                        'value' => LeafLevel::nextReference('functional_status'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                ],
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        LeafLevel::code()
                    ),
                    'dataKey' => 'value',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                ],
                FieldLevel::author(),
            ],
        ];
    }

    /**
     * Functional Status Self Care Observation
     * JS: functionalStatusSelfCareObservation (private)
     */
    public static function functionalStatusSelfCareObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.128'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => ['nullFlavor' => 'NA'],
                ],
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'CD',
                        'code' => '371153006',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                        'codeSystemName' => 'SNOMED CT',
                        'displayName' => 'Independent',
                    ],
                ],
                FieldLevel::author(),
            ],
        ];
    }

    /**
     * Functional Status Organizer
     * JS: exports.functionalStatusOrganizer
     */
    public static function functionalStatusOrganizer(): array
    {
        return [
            'key' => 'organizer',
            'attributes' => [
                'classCode' => 'CLUSTER',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.66', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.66'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => 'd5',
                        'codeSystem' => '2.16.840.1.113883.6.254',
                        'codeSystemName' => 'ICF',
                        'displayName' => 'Self-Care',
                    ],
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                ],
                FieldLevel::author(),
                [
                    'key' => 'component',
                    'content' => [self::functionalStatusObservation()],
                    'dataKey' => 'observation',
                    'required' => true,
                ],
                [
                    'key' => 'component',
                    'content' => [self::functionalStatusSelfCareObservation()],
                    'dataKey' => 'observation',
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Disability Status Observation
     * JS: exports.disabilityStatusObservation
     */
    public static function disabilityStatusObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.505', '2023-05-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.505'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => LeafLevel::deepInputProperty('overall_status.code'),
                        'codeSystem' => LeafLevel::deepInputProperty('overall_status.code_system'),
                        'codeSystemName' => LeafLevel::deepInputProperty('overall_status.code_system_name'),
                        'displayName' => LeafLevel::deepInputProperty('overall_status.display'),
                    ],
                ],
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'CD',
                        'code' => LeafLevel::deepInputProperty('overall_status.answer_code'),
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'displayName' => LeafLevel::deepInputProperty('overall_status.answer_display'),
                    ],
                ],
                // Individual disability questions as entryRelationship components
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'COMP'],
                    'content' => [
                        [
                            'key' => 'observation',
                            'attributes' => [
                                'classCode' => 'OBS',
                                'moodCode' => 'EVN',
                            ],
                            'content' => [
                                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.86'),
                                FieldLevel::uniqueId(),
                                FieldLevel::id(),
                                [
                                    'key' => 'code',
                                    'attributes' => [
                                        'code' => LeafLevel::inputProperty('code'),
                                        'codeSystem' => LeafLevel::inputProperty('code_system'),
                                        'codeSystemName' => LeafLevel::inputProperty('code_system_name'),
                                        'displayName' => LeafLevel::inputProperty('display'),
                                    ],
                                ],
                                FieldLevel::$statusCodeCompleted,
                                [
                                    'key' => 'value',
                                    'attributes' => [
                                        'xsi:type' => 'CD',
                                        'code' => LeafLevel::inputProperty('answer_code'),
                                        'codeSystem' => fn($input) => $input['answer_code_system'] ?? '2.16.840.1.113883.6.1',
                                        'codeSystemName' => 'LOINC',
                                        'displayName' => LeafLevel::inputProperty('answer_display'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'disability_questions.question',
                ],
            ],
            'existsWhen' => fn($input) => $input && isset($input['overall_status']),
        ];
    }
}
