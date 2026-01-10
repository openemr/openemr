<?php

/**
 * MedicationEntryLevel.php - Medication entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/medicationEntryLevel.js
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

class MedicationEntryLevel
{
    /**
     * Medication Information (manufacturedProduct)
     * JS: medicationInformation (private)
     */
    public static function medicationInformation(): array
    {
        return [
            'key' => 'manufacturedProduct',
            'attributes' => ['classCode' => 'MANU'],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.23', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.23'),
                FieldLevel::id(),
                [
                    'key' => 'manufacturedMaterial',
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
                                                'value' => LeafLevel::nextReference('medinfo'),
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
                        ],
                    ],
                    'dataKey' => 'product',
                    'required' => true,
                ],
                [
                    'key' => 'manufacturerOrganization',
                    'content' => [
                        [
                            'key' => 'name',
                            'text' => LeafLevel::input(...),
                        ],
                    ],
                    'dataKey' => 'manufacturer',
                ],
            ],
            'dataTransform' => function ($input) {
                if (isset($input['product'])) {
                    $input['product']['unencoded_name'] = $input['unencoded_name'] ?? null;
                }
                return $input;
            },
        ];
    }

    /**
     * Medication Supply Order
     * JS: medicationSupplyOrder (private)
     */
    public static function medicationSupplyOrder(): array
    {
        return [
            'key' => 'supply',
            'attributes' => [
                'classCode' => 'SPLY',
                'moodCode' => 'INT',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.17'),
                FieldLevel::id(),
                FieldLevel::$statusCodeCompleted,
                FieldLevel::effectiveTimeIVL_TS(),
                [
                    'key' => 'repeatNumber',
                    'attributes' => [
                        'value' => LeafLevel::input(...),
                    ],
                    'dataKey' => 'repeatNumber',
                ],
                [
                    'key' => 'quantity',
                    'attributes' => [
                        'value' => LeafLevel::input(...),
                    ],
                    'dataKey' => 'quantity',
                ],
                [
                    'key' => 'product',
                    'content' => [self::medicationInformation()],
                    'dataKey' => 'product',
                ],
                FieldLevel::author(),
                [
                    'key' => 'entryRelationship',
                    'attributes' => [
                        'typeCode' => 'SUBJ',
                        'inversionInd' => 'true',
                    ],
                    'content' => [
                        array_merge(SharedEntryLevel::instructions(), ['required' => true]),
                    ],
                    'dataKey' => 'instructions',
                ],
            ],
        ];
    }

    /**
     * Medication Dispense
     * JS: medicationDispense (private)
     */
    public static function medicationDispense(): array
    {
        return [
            'key' => 'supply',
            'attributes' => [
                'classCode' => 'SPLY',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.18'),
                FieldLevel::id(),
                FieldLevel::$statusCodeCompleted,
                FieldLevel::effectiveTimeIVL_TS(),
                [
                    'key' => 'product',
                    'content' => [self::medicationInformation()],
                    'dataKey' => 'product',
                ],
                FieldLevel::performer(),
            ],
        ];
    }

    /**
     * Medication Activity
     * JS: exports.medicationActivity
     */
    public static function medicationActivity(): array
    {
        return [
            'key' => 'substanceAdministration',
            'attributes' => [
                'classCode' => 'SBADM',
                'moodCode' => function ($input) {
                    $status = $input['status'] ?? null;
                    if ($status) {
                        if ($status === 'Prescribed') {
                            return 'INT';
                        }
                        if ($status === 'Completed') {
                            return 'EVN';
                        }
                    }
                    return null;
                },
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.16', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.16'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                [
                    'key' => 'text',
                    'text' => LeafLevel::input(...),
                    'dataKey' => 'sig',
                ],
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTimeIVL_TS(), ['required' => true]),
                [
                    'key' => 'effectiveTime',
                    'attributes' => [
                        'xsi:type' => 'PIVL_TS',
                        'institutionSpecified' => 'true',
                        'operator' => 'A',
                    ],
                    'content' => [
                        [
                            'key' => 'period',
                            'attributes' => [
                                'value' => LeafLevel::inputProperty('value'),
                                'unit' => LeafLevel::inputProperty('unit'),
                            ],
                            'existsWhen' => Condition::propertyNotEmpty('unit'),
                        ],
                        [
                            'key' => 'period',
                            'attributes' => [
                                'value' => LeafLevel::inputProperty('value'),
                            ],
                            'existsWhen' => Condition::propertyEmpty('unit'),
                        ],
                    ],
                    'dataKey' => 'administration.interval.period',
                ],
                [
                    'key' => 'routeCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'administration.route',
                ],
                [
                    'key' => 'doseQuantity',
                    'attributes' => [
                        'value' => LeafLevel::inputProperty('value'),
                        'unit' => LeafLevel::inputProperty('unit'),
                    ],
                    'existsWhen' => fn($input) => $input && isset($input['unit']),
                    'dataKey' => 'administration.dose',
                ],
                [
                    'key' => 'doseQuantity',
                    'attributes' => [
                        'value' => LeafLevel::inputProperty('value'),
                    ],
                    'existsWhen' => fn($input) => $input && !isset($input['unit']),
                    'dataKey' => 'administration.dose',
                ],
                [
                    'key' => 'rateQuantity',
                    'attributes' => [
                        'value' => LeafLevel::inputProperty('value'),
                        'unit' => LeafLevel::inputProperty('unit'),
                    ],
                    'dataKey' => 'administration.rate',
                ],
                [
                    'key' => 'consumable',
                    'content' => [self::medicationInformation()],
                    'dataKey' => 'product',
                ],
                FieldLevel::author(),
            ],
        ];
    }
}
