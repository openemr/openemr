<?php

/**
 * AdvanceDirectivesEntryLevel.php - Advance Directives entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/advanceDirectivesEntryLevel.js
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

class AdvanceDirectivesEntryLevel
{
    /**
     * Advance Directive Observation (V3)
     * JS: exports.advanceDirectiveObservation
     */
    public static function advanceDirectiveObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.48', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.48'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('observation_code'),
                        'codeSystem' => LeafLevel::inputProperty('observation_code_system'),
                        'codeSystemName' => LeafLevel::inputProperty('observation_code_system_name'),
                        'displayName' => LeafLevel::inputProperty('observation_display'),
                    ],
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => [
                                'code' => '75320-2',
                                'codeSystem' => '2.16.840.1.113883.6.1',
                                'codeSystemName' => 'LOINC',
                                'displayName' => 'Advance directive',
                            ],
                        ],
                    ],
                ],
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'effectiveTime',
                    'content' => [
                        [
                            'key' => 'low',
                            'attributes' => [
                                'value' => LeafLevel::inputProperty('effective_date'),
                            ],
                        ],
                        [
                            'key' => 'high',
                            'attributes' => [
                                'nullFlavor' => 'NA',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'CD',
                        'code' => LeafLevel::inputProperty('observation_value_code'),
                        'codeSystem' => LeafLevel::inputProperty('observation_value_code_system'),
                        'codeSystemName' => LeafLevel::inputProperty('observation_value_code_system_name'),
                        'displayName' => LeafLevel::inputProperty('observation_value_display'),
                    ],
                ],
                FieldLevel::author(),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'CST'],
                    'content' => [
                        [
                            'key' => 'participantRole',
                            'attributes' => ['classCode' => 'AGNT'],
                            'content' => [
                                [
                                    'key' => 'addr',
                                    'attributes' => ['nullFlavor' => 'UNK'],
                                ],
                                [
                                    'key' => 'telecom',
                                    'attributes' => ['nullFlavor' => 'UNK'],
                                ],
                                [
                                    'key' => 'playingEntity',
                                    'attributes' => ['classCode' => 'PSN'],
                                    'content' => [
                                        [
                                            'key' => 'name',
                                            'attributes' => ['nullFlavor' => 'UNK'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'reference',
                    'attributes' => ['typeCode' => 'REFR'],
                    'content' => [
                        [
                            'key' => 'externalDocument',
                            'attributes' => [
                                'classCode' => 'DOC',
                                'moodCode' => 'EVN',
                            ],
                            'content' => [
                                [
                                    'key' => 'id',
                                    'attributes' => [
                                        'root' => LeafLevel::inputProperty('document_reference'),
                                    ],
                                ],
                                [
                                    'key' => 'code',
                                    'attributes' => ['nullFlavor' => 'UNK'],
                                ],
                                [
                                    'key' => 'text',
                                    'attributes' => ['mediaType' => 'text/plain'],
                                    'content' => [
                                        [
                                            'key' => 'reference',
                                            'attributes' => [
                                                'value' => LeafLevel::inputProperty('location'),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'existsWhen' => Condition::keyExists('document_reference'),
                ],
            ],
        ];
    }
}
