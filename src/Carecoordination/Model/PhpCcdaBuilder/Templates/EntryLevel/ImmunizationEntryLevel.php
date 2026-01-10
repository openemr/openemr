<?php

/**
 * ImmunizationEntryLevel.php - Immunization entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/immunizationEntryLevel.js
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

class ImmunizationEntryLevel
{
    /**
     * Immunization Medication Information
     * JS: immunizationMedicationInformation (private)
     */
    public static function immunizationMedicationInformation(): array
    {
        return [
            'key' => 'manufacturedProduct',
            'attributes' => ['classCode' => 'MANU'],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.54', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.54'),
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
                                    'text' => LeafLevel::inputProperty('unencoded_name'),
                                    'content' => [
                                        [
                                            'key' => 'reference',
                                            'attributes' => [
                                                'value' => LeafLevel::nextReference('imminfo'),
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
                        [
                            'key' => 'lotNumberText',
                            'text' => LeafLevel::input(...),
                            'dataKey' => 'lot_number',
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
                    $input['product']['lot_number'] = $input['lot_number'] ?? null;
                }
                return $input;
            },
        ];
    }

    /**
     * Immunization Refusal Reason
     * JS: immunizationRefusalReason (private)
     */
    public static function immunizationRefusalReason(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.53'),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => fn($input) => Translate::codeFromName('2.16.840.1.113883.5.8', $input),
                    'required' => true,
                ],
                FieldLevel::$statusCodeCompleted,
            ],
        ];
    }

    /**
     * Immunization Activity Attributes
     * JS: immunizationActivityAttributes (private)
     */
    private static function immunizationActivityAttributes($input): ?array
    {
        $status = $input['status'] ?? null;
        if ($status) {
            if ($status === 'refused') {
                return [
                    'moodCode' => 'EVN',
                    'negationInd' => 'true',
                ];
            }
            if ($status === 'pending') {
                return [
                    'moodCode' => 'INT',
                    'negationInd' => 'false',
                ];
            }
            if ($status === 'complete') {
                return [
                    'moodCode' => 'EVN',
                    'negationInd' => 'false',
                ];
            }
        }
        return null;
    }

    /**
     * Immunization Activity
     * JS: exports.immunizationActivity
     */
    public static function immunizationActivity(): array
    {
        return [
            'key' => 'substanceAdministration',
            'attributes' => [
                ['classCode' => 'SBADM'],
                self::immunizationActivityAttributes(...),
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.52', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.52'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                FieldLevel::text(LeafLevel::nextReference('immunization')),
                FieldLevel::$statusCodeCompleted,
                array_merge(FieldLevel::effectiveTimeIVL_TS(), ['required' => true]),
                [
                    'key' => 'repeatNumber',
                    'attributes' => [
                        'value' => LeafLevel::inputProperty('sequence_number'),
                    ],
                    'existsWhen' => fn($input) => isset($input['sequence_number']) || ($input['sequence_number'] ?? null) === '',
                ],
                [
                    'key' => 'routeCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'administration.route',
                ],
                [
                    'key' => 'approachSiteCode',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'administration.body_site',
                ],
                [
                    'key' => 'doseQuantity',
                    'attributes' => [
                        'value' => LeafLevel::inputProperty('value'),
                        'unit' => LeafLevel::inputProperty('unit'),
                    ],
                    'dataKey' => 'administration.dose',
                ],
                [
                    'key' => 'consumable',
                    'content' => [
                        array_merge(self::immunizationMedicationInformation(), ['required' => true]),
                    ],
                    'dataKey' => 'product',
                    'required' => true,
                ],
                FieldLevel::performer(),
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
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'RSON'],
                    'content' => [
                        array_merge(self::immunizationRefusalReason(), ['required' => true]),
                    ],
                    'dataKey' => 'refusal_reason',
                ],
            ],
        ];
    }
}
