<?php

/**
 * EncounterEntryLevel.php - Encounter entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/encounterEntryLevel.js
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

class EncounterEntryLevel
{
    /**
     * Encounter Activities
     * JS: exports.encounterActivities
     */
    public static function encounterActivities(): array
    {
        return [
            'key' => 'encounter',
            'attributes' => [
                'classCode' => 'ENC',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.49', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.49'),
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
                                        'value' => LeafLevel::nextReference('Encounter'),
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
                    'dataKey' => 'encounter',
                ],
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                array_merge(FieldLevel::performer(), ['dataKey' => 'performers']),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'LOC'],
                    'content' => [
                        array_merge(SharedEntryLevel::serviceDeliveryLocation(), ['required' => true]),
                    ],
                    'dataKey' => 'locations',
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'SUBJ'],
                    'content' => [
                        array_merge(SharedEntryLevel::encDiagnosis(), ['required' => true]),
                    ],
                    'dataKey' => 'findings',
                ],
            ],
        ];
    }
}
