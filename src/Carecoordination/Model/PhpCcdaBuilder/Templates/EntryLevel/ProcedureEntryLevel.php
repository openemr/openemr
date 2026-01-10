<?php

/**
 * ProcedureEntryLevel.php - Procedure entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/procedureEntryLevel.js
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

class ProcedureEntryLevel
{
    /**
     * Procedure Activity Act
     * JS: exports.procedureActivityAct
     */
    public static function procedureActivityAct(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'INT', // not constant in the specification
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.12'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
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
                                        'value' => LeafLevel::nextReference('procedure'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'procedure',
                    'required' => true,
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                    'required' => true,
                ],
                FieldLevel::effectiveTime(),
                [
                    'key' => 'priorityCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'priority',
                ],
                [
                    'key' => 'targetSiteCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'body_sites',
                ],
                array_merge(FieldLevel::performer(), ['dataKey' => 'performer']),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'LOC'],
                    'content' => [
                        array_merge(SharedEntryLevel::serviceDeliveryLocation(), ['required' => true]),
                    ],
                    'dataKey' => 'locations',
                ],
            ],
            'existsWhen' => Condition::propertyEquals('procedure_type', 'act'),
        ];
    }

    /**
     * Procedure Activity Procedure
     * JS: exports.procedureActivityProcedure
     */
    public static function procedureActivityProcedure(): array
    {
        return [
            'key' => 'procedure',
            'attributes' => [
                'classCode' => 'PROC',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.14'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
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
                                        'value' => LeafLevel::nextReference('procedure'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'procedure',
                    'required' => true,
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                    'required' => true,
                ],
                FieldLevel::effectiveTime(),
                [
                    'key' => 'priorityCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'priority',
                ],
                [
                    'key' => 'targetSiteCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'body_sites',
                ],
                [
                    'key' => 'specimen',
                    'attributes' => ['typeCode' => 'SPC'],
                    'content' => [
                        [
                            'key' => 'specimenRole',
                            'attributes' => ['classCode' => 'SPEC'],
                            'content' => [
                                FieldLevel::id(),
                                [
                                    'key' => 'specimenPlayingEntity',
                                    'content' => [
                                        [
                                            'key' => 'code',
                                            'attributes' => LeafLevel::code(),
                                            'dataKey' => 'code',
                                        ],
                                    ],
                                    'existsWhen' => Condition::keyExists('code'),
                                ],
                            ],
                            'required' => true,
                        ],
                    ],
                    'dataKey' => 'specimen',
                ],
                array_merge(FieldLevel::performer(), ['dataKey' => 'performer']),
                FieldLevel::author(),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'LOC'],
                    'content' => [
                        array_merge(SharedEntryLevel::serviceDeliveryLocation(), ['required' => true]),
                    ],
                    'dataKey' => 'locations',
                ],
            ],
            'existsWhen' => Condition::propertyEquals('procedure_type', 'procedure'),
        ];
    }

    /**
     * Procedure Activity Observation
     * JS: exports.procedureActivityObservation
     */
    public static function procedureActivityObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN', // not constant in the specification
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.13'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
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
                                        'value' => LeafLevel::nextReference('procedure'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'procedure',
                    'required' => true,
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                    'required' => true,
                ],
                FieldLevel::effectiveTime(),
                [
                    'key' => 'priorityCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'priority',
                ],
                [
                    'key' => 'value',
                    'attributes' => ['xsi:type' => 'CD'],
                ],
                [
                    'key' => 'targetSiteCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'body_sites',
                ],
                array_merge(FieldLevel::performer(), ['dataKey' => 'performers']),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'LOC'],
                    'content' => [
                        array_merge(SharedEntryLevel::serviceDeliveryLocation(), ['required' => true]),
                    ],
                    'dataKey' => 'locations',
                ],
            ],
            'existsWhen' => Condition::propertyEquals('procedure_type', 'observation'),
        ];
    }
}
