<?php

/**
 * ProblemEntryLevel.php - Problem entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/problemEntryLevel.js
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

class ProblemEntryLevel
{
    /**
     * Problem Status
     * JS: problemStatus (private)
     */
    public static function problemStatus(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.6'),
                FieldLevel::id(),
                FieldLevel::templateCode('ProblemStatus'),
                FieldLevel::$statusCodeCompleted,
                FieldLevel::effectiveTime(),
                [
                    'key' => 'value',
                    'attributes' => fn($input) => array_merge(
                        ['xsi:type' => 'CD'],
                        Translate::codeFromName('2.16.840.1.113883.3.88.12.80.68', $input)
                    ),
                    'dataKey' => 'name',
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Health Status Observation
     * JS: healthStatusObservation (private)
     */
    public static function healthStatusObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.5'),
                FieldLevel::templateCode('HealthStatusObservation'),
                FieldLevel::text(LeafLevel::nextReference('healthStatus')),
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'CD',
                        'code' => '81323004',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                        'codeSystemName' => 'SNOMED CT',
                        'displayName' => LeafLevel::inputProperty('patient_status'),
                    ],
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Problem Observation
     * JS: problemObservation (private)
     */
    public static function problemObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
                'negationInd' => LeafLevel::boolInputProperty('negation_indicator'),
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.4', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.4'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => '64572001',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                        'displayName' => 'Condition',
                    ],
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => LeafLevel::code(),
                            'dataKey' => 'translations',
                        ],
                    ],
                ],
                FieldLevel::text(LeafLevel::nextReference('problem')),
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['dataKey' => 'problem.date_time']),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        ['xsi:type' => 'CD'],
                        LeafLevel::code()
                    ),
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => LeafLevel::code(),
                            'dataKey' => 'translations',
                        ],
                    ],
                    'dataKey' => 'problem.code',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                    'required' => true,
                ],
                FieldLevel::author(),
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'REFR'],
                    'content' => [
                        array_merge(self::problemStatus(), ['required' => true]),
                    ],
                    'dataTransform' => function ($input) {
                        if ($input && isset($input['status'])) {
                            $result = $input['status'];
                            $result['identifiers'] = $input['identifiers'] ?? null;
                            return $result;
                        }
                        return null;
                    },
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(SharedEntryLevel::ageObservation(), ['required' => true]),
                    ],
                    'existsWhen' => Condition::keyExists('onset_age'),
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'REFR'],
                    'content' => [
                        array_merge(self::healthStatusObservation(), ['required' => true]),
                    ],
                    'existsWhen' => Condition::keyExists('patient_status'),
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        SharedEntryLevel::severityObservation(),
                    ],
                    'dataKey' => 'problem',
                    'existsWhen' => Condition::keyExists('severity'),
                ],
            ],
        ];
    }

    /**
     * Problem Concern Act
     * JS: exports.problemConcernAct
     */
    public static function problemConcernAct(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.3', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.3'),
                FieldLevel::uniqueId(),
                [
                    'key' => 'id',
                    'attributes' => [
                        'root' => LeafLevel::inputProperty('identifier'),
                        'extension' => LeafLevel::inputProperty('extension'),
                    ],
                    'dataKey' => 'source_list_identifiers',
                    'existsWhen' => Condition::keyExists('identifier'),
                    'required' => true,
                ],
                FieldLevel::templateCode('ProblemConcernAct'),
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                FieldLevel::author(),
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'SUBJ'],
                    'content' => [
                        array_merge(self::problemObservation(), ['required' => true]),
                    ],
                    'required' => true,
                ],
            ],
        ];
    }
}
