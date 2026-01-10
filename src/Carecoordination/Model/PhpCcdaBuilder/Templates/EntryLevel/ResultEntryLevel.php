<?php

/**
 * ResultEntryLevel.php - Result entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/resultEntryLevel.js
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

class ResultEntryLevel
{
    /**
     * Result Observation
     * JS: resultObservation (private)
     */
    public static function resultObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.2', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.2'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'result',
                    'required' => true,
                ],
                FieldLevel::text(LeafLevel::nextReference('result')),
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                // Physical Quantity value
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'PQ',
                        'value' => LeafLevel::inputProperty('value'),
                        'unit' => LeafLevel::inputProperty('unit'),
                    ],
                    'existsWhen' => Condition::propertyEquals('type', 'PQ'),
                ],
                // String value
                [
                    'key' => 'value',
                    'attributes' => ['xsi:type' => 'ST'],
                    'text' => LeafLevel::inputProperty('value'),
                    'existsWhen' => Condition::propertyEquals('type', 'ST'),
                ],
                // Coded Ordinal value
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'CO',
                        'code' => '260385009',
                        'codeSystemName' => 'SNOMED-CT',
                        'displayName' => 'Negative',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                    ],
                    'existsWhen' => Condition::propertyEquals('type', 'CO'),
                ],
                [
                    'key' => 'interpretationCode',
                    'attributes' => [
                        'code' => function ($input) {
                            if (is_string($input)) {
                                return substr($input, 0, 1);
                            }
                            return isset($input['code']) ? substr((string) $input['code'], 0, 1) : null;
                        },
                        'codeSystem' => '2.16.840.1.113883.5.83',
                        'displayName' => LeafLevel::input(...),
                        'codeSystemName' => 'ObservationInterpretation',
                    ],
                    'dataKey' => 'interpretations',
                ],
                [
                    'key' => 'referenceRange',
                    'content' => [
                        [
                            'key' => 'observationRange',
                            'content' => [
                                [
                                    'key' => 'text',
                                    'text' => LeafLevel::input(...),
                                    'dataKey' => 'range',
                                ],
                                // IVL_PQ with unit
                                [
                                    'key' => 'value',
                                    'attributes' => ['xsi:type' => 'IVL_PQ'],
                                    'content' => [
                                        [
                                            'key' => 'low',
                                            'attributes' => [
                                                'value' => LeafLevel::inputProperty('low'),
                                                'unit' => LeafLevel::inputProperty('unit'),
                                            ],
                                            'existsWhen' => Condition::propertyNotEmpty('low'),
                                        ],
                                        [
                                            'key' => 'high',
                                            'attributes' => [
                                                'value' => LeafLevel::inputProperty('high'),
                                                'unit' => LeafLevel::inputProperty('unit'),
                                            ],
                                            'existsWhen' => Condition::propertyNotEmpty('high'),
                                        ],
                                    ],
                                    'existsWhen' => fn($input) => $input && isset($input['unit']) && ($input['range_type'] ?? '') !== 'CO',
                                ],
                                // IVL_PQ without unit
                                [
                                    'key' => 'value',
                                    'attributes' => ['xsi:type' => 'IVL_PQ'],
                                    'content' => [
                                        [
                                            'key' => 'low',
                                            'attributes' => [
                                                'value' => LeafLevel::inputProperty('low'),
                                            ],
                                            'existsWhen' => Condition::propertyNotEmpty('low'),
                                        ],
                                        [
                                            'key' => 'high',
                                            'attributes' => [
                                                'value' => LeafLevel::inputProperty('high'),
                                            ],
                                            'existsWhen' => Condition::propertyNotEmpty('high'),
                                        ],
                                    ],
                                    'existsWhen' => fn($input) => $input && !isset($input['unit']) && ($input['range_type'] ?? '') !== 'CO',
                                ],
                                // CO reference range
                                [
                                    'key' => 'value',
                                    'attributes' => [
                                        'xsi:type' => 'CO',
                                        'code' => '260385009',
                                        'codeSystemName' => 'SNOMED-CT',
                                        'displayName' => 'Negative',
                                        'codeSystem' => '2.16.840.1.113883.6.96',
                                    ],
                                    'existsWhen' => Condition::propertyEquals('range_type', 'CO'),
                                ],
                            ],
                            'required' => true,
                        ],
                    ],
                    'dataKey' => 'reference_range',
                ],
            ],
        ];
    }

    /**
     * Result Organizer
     * JS: exports.resultOrganizer
     */
    public static function resultOrganizer(): array
    {
        return [
            'key' => 'organizer',
            'attributes' => [
                'classCode' => 'BATTERY',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.1', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.1'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => LeafLevel::code(),
                            'dataKey' => 'translations',
                        ],
                    ],
                    'dataKey' => 'result_set',
                    'required' => true,
                ],
                FieldLevel::$statusCodeCompleted,
                FieldLevel::author(),
                [
                    'key' => 'component',
                    'content' => [
                        array_merge(self::resultObservation(), ['required' => true]),
                    ],
                    'dataKey' => 'results',
                    'required' => true,
                ],
            ],
        ];
    }
}
