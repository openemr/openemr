<?php

/**
 * AllergyEntryLevel.php - Allergy entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/allergyEntryLevel.js
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

class AllergyEntryLevel
{
    /**
     * Allergy Status Observation
     * JS: allergyStatusObservation (private)
     */
    public static function allergyStatusObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.28'),
                FieldLevel::templateCode('AllergyStatusObservation'),
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCE,
                        LeafLevel::code()
                    ),
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                    'required' => true,
                ],
            ],
            'dataKey' => 'status',
        ];
    }

    /**
     * Allergy Intolerance Observation - No Known Allergies
     * JS: exports.allergyIntoleranceObservationNKA
     */
    public static function allergyIntoleranceObservationNKA(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
                'negationInd' => 'true',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.7', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.7'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                FieldLevel::templateCode('AllergyObservation'),
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        [
                            'code' => '419199007',
                            'codeSystem' => '2.16.840.1.113883.6.96',
                            'codeSystemName' => 'SNOMED-CT',
                            'displayName' => 'Allergy to substance (disorder)',
                        ]
                    ),
                    'content' => [
                        [
                            'key' => 'originalText',
                            'content' => [
                                [
                                    'key' => 'reference',
                                    'attributes' => [
                                        'value' => LeafLevel::nextReference('reaction'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'required' => true,
                ],
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'CSM'],
                    'content' => [
                        [
                            'key' => 'participantRole',
                            'attributes' => ['classCode' => 'MANU'],
                            'content' => [
                                [
                                    'key' => 'playingEntity',
                                    'attributes' => ['classCode' => 'MMAT'],
                                    'content' => [
                                        [
                                            'key' => 'code',
                                            'attributes' => ['nullFlavor' => 'NA'],
                                        ],
                                    ],
                                ],
                            ],
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Allergy Problem Act - No Known Allergies
     * JS: exports.allergyProblemActNKA
     */
    public static function allergyProblemActNKA(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.30', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.30'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                FieldLevel::templateCode('AllergyConcernAct'),
                FieldLevel::$statusCodeActive,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(self::allergyIntoleranceObservationNKA(), ['required' => true]),
                    ],
                    'existsWhen' => Condition::keyExists('no_know_allergies'),
                    'required' => true,
                ],
            ],
            'existsWhen' => Condition::keyExists('no_know_allergies'),
        ];
    }

    /**
     * Allergy Intolerance Observation
     * JS: exports.allergyIntoleranceObservation
     */
    public static function allergyIntoleranceObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
                'negationInd' => LeafLevel::boolInputProperty('negation_indicator'),
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.7', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.7'),
                FieldLevel::id(),
                FieldLevel::templateCode('AllergyObservation'),
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        LeafLevel::code()
                    ),
                    'content' => [
                        [
                            'key' => 'originalText',
                            'content' => [
                                [
                                    'key' => 'reference',
                                    'attributes' => [
                                        'value' => LeafLevel::nextReference('reaction'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'intolerance',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                    'required' => true,
                ],
                FieldLevel::author(),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'CSM'],
                    'content' => [
                        [
                            'key' => 'participantRole',
                            'attributes' => ['classCode' => 'MANU'],
                            'content' => [
                                [
                                    'key' => 'playingEntity',
                                    'attributes' => ['classCode' => 'MMAT'],
                                    'content' => [
                                        [
                                            'key' => 'code',
                                            'attributes' => LeafLevel::code(),
                                            'content' => [
                                                [
                                                    'key' => 'originalText',
                                                    'content' => [
                                                        [
                                                            'key' => 'reference',
                                                            'attributes' => [
                                                                'value' => LeafLevel::sameReference('reaction'),
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                                [
                                                    'key' => 'translation',
                                                    'attributes' => LeafLevel::code(),
                                                    'dataKey' => 'translations',
                                                ],
                                            ],
                                            'required' => true,
                                        ],
                                    ],
                                ],
                            ],
                            'required' => true,
                        ],
                    ],
                    'dataKey' => 'allergen',
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(self::allergyStatusObservation(), ['required' => true]),
                    ],
                    'existsWhen' => Condition::keyExists('status'),
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'MFST',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(SharedEntryLevel::reactionObservation(), ['required' => true]),
                    ],
                    'dataKey' => 'reactions',
                    'existsWhen' => Condition::keyExists('reaction'),
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(SharedEntryLevel::severityObservation(), ['required' => true]),
                    ],
                    'existsWhen' => Condition::keyExists('severity'),
                ],
            ],
            'dataKey' => 'observation',
        ];
    }

    /**
     * Allergy Problem Act
     * JS: exports.allergyProblemAct
     */
    public static function allergyProblemAct(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.30', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.30'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                FieldLevel::templateCode('AllergyProblemAct'),
                FieldLevel::$statusCodeActive,
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                FieldLevel::author(),
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(self::allergyIntoleranceObservation(), ['required' => true]),
                    ],
                    'existsWhen' => Condition::keyExists('observation'),
                    'required' => true,
                ],
            ],
            'existsWhen' => Condition::keyDoesntExist('no_know_allergies'),
        ];
    }
}
