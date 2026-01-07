<?php

/**
 * SharedEntryLevel.php - Shared entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/sharedEntryLevel.js
 * Contains reusable entry templates used across multiple sections.
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

class SharedEntryLevel
{
    /**
     * Severity Observation
     * JS: exports.severityObservation
     */
    public static function severityObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.8', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.8'),
                FieldLevel::templateCode('SeverityObservation'),
                FieldLevel::text(LeafLevel::nextReference('severity')),
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        LeafLevel::code()
                    ),
                    'dataKey' => 'code',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                    'required' => true,
                ],
                [
                    'key' => 'interpretationCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'interpretation',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                ],
            ],
            'dataKey' => 'severity',
            'existsWhen' => Condition::keyExists('code'),
        ];
    }

    /**
     * Reaction Observation
     * JS: exports.reactionObservation
     */
    public static function reactionObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.9', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.9'),
                FieldLevel::id(),
                FieldLevel::templateCode('AllergyObservation'),
                FieldLevel::text(LeafLevel::sameReference('reaction')),
                FieldLevel::$statusCodeCompleted,
                FieldLevel::effectiveTime(),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        LeafLevel::code()
                    ),
                    'dataKey' => 'reaction',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                    'required' => true,
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [self::severityObservation()],
                    'existsWhen' => Condition::keyExists('severity'),
                ],
            ],
        ];
    }

    /**
     * Service Delivery Location
     * JS: exports.serviceDeliveryLocation
     */
    public static function serviceDeliveryLocation(): array
    {
        return [
            'key' => 'participantRole',
            'attributes' => ['classCode' => 'SDLOC'],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.32'),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'location_type',
                    'required' => true,
                ],
                FieldLevel::usRealmAddress(),
                FieldLevel::telecom(),
                [
                    'key' => 'playingEntity',
                    'attributes' => ['classCode' => 'PLC'],
                    'content' => [
                        [
                            'key' => 'name',
                            'text' => LeafLevel::inputProperty('name'),
                        ],
                    ],
                    'existsWhen' => Condition::keyExists('name'),
                ],
            ],
        ];
    }

    /**
     * Age Observation
     * JS: exports.ageObservation
     */
    public static function ageObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.31'),
                FieldLevel::templateCode('AgeObservation'),
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'PQ',
                        'value' => LeafLevel::inputProperty('onset_age'),
                        'unit' => LeafLevel::codeOnlyFromName('2.16.840.1.113883.11.20.9.21', 'onset_age_unit'),
                    ],
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Indication
     * JS: exports.indication
     */
    public static function indication(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.19'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'code',
                    'required' => true,
                ],
                FieldLevel::$statusCodeCompleted,
                FieldLevel::effectiveTime(),
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCD,
                        LeafLevel::code()
                    ),
                    'dataKey' => 'value',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                ],
            ],
        ];
    }

    /**
     * Precondition for Substance Administration
     * JS: exports.preconditionForSubstanceAdministration
     */
    public static function preconditionForSubstanceAdministration(): array
    {
        return [
            'key' => 'criterion',
            'content' => [
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('code'),
                        'codeSystem' => '2.16.840.1.113883.5.4',
                    ],
                    'dataKey' => 'code',
                ],
                [
                    'key' => 'value',
                    'attributes' => array_merge(
                        LeafLevel::$typeCE,
                        LeafLevel::code()
                    ),
                    'dataKey' => 'value',
                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                ],
            ],
        ];
    }

    /**
     * Drug Vehicle
     * JS: exports.drugVehicle
     */
    public static function drugVehicle(): array
    {
        return [
            'key' => 'participantRole',
            'attributes' => ['classCode' => 'MANU'],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.24'),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => '412307009',
                        'displayName' => 'drug vehicle',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                        'codeSystemName' => 'SNOMED CT',
                    ],
                ],
                [
                    'key' => 'playingEntity',
                    'attributes' => ['classCode' => 'MMAT'],
                    'content' => [
                        [
                            'key' => 'code',
                            'attributes' => LeafLevel::code(),
                            'required' => true,
                        ],
                        [
                            'key' => 'name',
                            'text' => LeafLevel::inputProperty('name'),
                        ],
                    ],
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Instructions
     * JS: exports.instructions
     */
    public static function instructions(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'INT',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.20'),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'code',
                    'required' => true,
                ],
                FieldLevel::$statusCodeCompleted,
            ],
        ];
    }

    /**
     * Encounter Diagnosis
     * JS: exports.encDiagnosis
     */
    public static function encDiagnosis(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.80', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.80'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.19'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'xsi:type' => 'CE',
                        'code' => '29308-4',
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'displayName' => 'ENCOUNTER DIAGNOSIS',
                    ],
                ],
                FieldLevel::effectiveTime(),
                FieldLevel::author(),
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'false',
                    ],
                    'content' => [
                        [
                            'key' => 'observation',
                            'attributes' => [
                                'classCode' => 'OBS',
                                'moodCode' => 'EVN',
                                'negationInd' => 'false',
                            ],
                            'content' => [
                                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.4', '2015-08-01'),
                                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.4'),
                                FieldLevel::id(),
                                [
                                    'key' => 'code',
                                    'attributes' => [
                                        'code' => '404684003',
                                        'codeSystem' => '2.16.840.1.113883.6.96',
                                        'codeSystemName' => 'SNOMED CT',
                                        'displayName' => 'Finding',
                                    ],
                                    'content' => [
                                        [
                                            'key' => 'translation',
                                            'attributes' => [
                                                'code' => '75321-0',
                                                'codeSystem' => '2.16.840.1.113883.6.1',
                                                'codeSystemName' => 'LOINC',
                                                'displayName' => 'Clinical finding',
                                            ],
                                        ],
                                    ],
                                ],
                                FieldLevel::$statusCodeCompleted,
                                array_merge(FieldLevel::effectiveTime(), ['dataKey' => 'date_time']),
                                [
                                    'key' => 'value',
                                    'attributes' => array_merge(
                                        LeafLevel::$typeCD,
                                        LeafLevel::code()
                                    ),
                                    'dataKey' => 'value',
                                    'existsWhen' => [Condition::class, 'codeOrDisplayname'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Notes Act
     * JS: exports.notesAct
     */
    public static function notesAct(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.202', '2016-11-01'),
                [
                    'key' => 'code',
                    'attributes' => [
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'code' => '34109-9',
                        'displayName' => 'Note',
                    ],
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => LeafLevel::code(),
                            'dataKey' => 'translations',
                        ],
                    ],
                ],
                [
                    'key' => 'text',
                    'content' => [
                        [
                            'key' => 'reference',
                            'attributes' => [
                                'value' => LeafLevel::nextReference('note'),
                            ],
                        ],
                    ],
                ],
                FieldLevel::$statusCodeCompleted,
                FieldLevel::effectiveTime(),
                FieldLevel::actAuthor(),
            ],
        ];
    }
}
