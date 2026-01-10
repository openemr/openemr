<?php

/**
 * MedicalDeviceEntryLevel.php - Medical Device entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/medicalDeviceEntryLevel.js
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

class MedicalDeviceEntryLevel
{
    /**
     * Medical Device Activity Procedure
     * JS: exports.medicalDeviceActivityProcedure
     */
    public static function medicalDeviceActivityProcedure(): array
    {
        return [
            'key' => 'procedure',
            'attributes' => [
                'classCode' => 'PROC',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.14', '2014-06-09'),
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
                                        'value' => LeafLevel::nextReference('device'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                    'dataKey' => 'device',
                ],
                FieldLevel::effectiveTime(),
                FieldLevel::author(),
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'DEV'],
                    'content' => [
                        [
                            'key' => 'participantRole',
                            'attributes' => ['classCode' => 'MANU'],
                            'content' => [
                                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.37'),
                                FieldLevel::id(),
                                [
                                    'key' => 'playingDevice',
                                    'content' => [
                                        [
                                            'key' => 'code',
                                            'attributes' => LeafLevel::code(),
                                        ],
                                    ],
                                ],
                                [
                                    'key' => 'scopingEntity',
                                    'content' => [
                                        [
                                            'key' => 'id',
                                            'attributes' => [
                                                'root' => '2.16.840.1.113883.3.3719',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'device',
                ],
            ],
            'existsWhen' => Condition::propertyEquals('device_type', 'UDI'),
        ];
    }
}
