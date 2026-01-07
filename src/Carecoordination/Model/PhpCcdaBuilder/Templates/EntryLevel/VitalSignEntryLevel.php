<?php

/**
 * VitalSignEntryLevel.php - Vital Sign entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/vitalSignEntryLevel.js
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

class VitalSignEntryLevel
{
    /**
     * Vital Sign Observation
     * JS: vitalSignObservation (private)
     */
    public static function vitalSignObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.27', '2014-06-09'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.27'),
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
                                        'value' => LeafLevel::nextReference('vital'),
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
                    'dataKey' => 'vital',
                    'required' => true,
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
                    'attributes' => [
                        'xsi:type' => 'PQ',
                        'value' => LeafLevel::inputProperty('value'),
                        'unit' => LeafLevel::inputProperty('unit'),
                    ],
                    'existsWhen' => Condition::keyExists('value'),
                    'required' => true,
                ],
                [
                    'key' => 'interpretationCode',
                    'attributes' => fn($input) => Translate::codeFromName('2.16.840.1.113883.5.83', $input),
                    'dataKey' => 'interpretations',
                ],
                FieldLevel::author(),
            ],
        ];
    }

    /**
     * Vital Signs Organizer
     * JS: exports.vitalSignsOrganizer
     */
    public static function vitalSignsOrganizer(): array
    {
        return [
            'key' => 'organizer',
            'attributes' => [
                'classCode' => 'CLUSTER',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.26', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.26'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => '46680005',
                        'codeSystem' => '2.16.840.1.113883.6.96',
                        'codeSystemName' => 'SNOMED-CT',
                        'displayName' => 'Vital signs',
                    ],
                    'content' => [
                        [
                            'key' => 'translation',
                            'attributes' => [
                                'code' => '74728-7',
                                'codeSystem' => '2.16.840.1.113883.6.1',
                                'codeSystemName' => 'LOINC',
                                'displayName' => 'Vital signs',
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
                    'key' => 'component',
                    'content' => [self::vitalSignObservation()],
                    'dataKey' => 'vital_list',
                    'existsWhen' => Condition::propertyNotEmpty('value'),
                    'required' => true,
                ],
            ],
        ];
    }
}
