<?php

/**
 * CareTeamEntryLevel.php - Care Team entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/careTeamEntryLevel.js
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

class CareTeamEntryLevel
{
    /**
     * Care Team Provider Act
     * JS: careTeamProviderAct (private)
     */
    public static function careTeamProviderAct(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'PCPR',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.500.1', '2019-07-01'),
                FieldLevel::uniqueIdRoot(),
                FieldLevel::templateCode('CareTeamAct'),
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                ],
                array_merge(FieldLevel::effectiveTimeIVL_TS(), ['required' => true]),
                [
                    'key' => 'performer',
                    'attributes' => ['typeCode' => 'PRF'],
                    'content' => [
                        [
                            'key' => 'functionCode',
                            'attributes' => LeafLevel::code(),
                            'dataKey' => 'function_code',
                            'existsWhen' => Condition::propertyNotEmpty('function_code'),
                            'content' => [
                                [
                                    'key' => 'originalText',
                                    'attributes' => ['xmlns' => 'urn:hl7-org:v3'],
                                    'content' => [
                                        [
                                            'key' => 'reference',
                                            'attributes' => [
                                                'value' => LeafLevel::nextReference('teamMember'),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        FieldLevel::assignedEntity(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Care Team Organizer
     * JS: exports.careTeamOrganizer
     */
    public static function careTeamOrganizer(): array
    {
        return [
            'key' => 'organizer',
            'attributes' => [
                'classCode' => 'CLUSTER',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.500', '2019-07-01'),
                FieldLevel::uniqueIdRoot(),
                [
                    'key' => 'code',
                    'attributes' => [
                        'code' => '86744-0',
                        'codeSystem' => '2.16.840.1.113883.6.1',
                        'codeSystemName' => 'LOINC',
                        'displayName' => 'Care Team Information',
                    ],
                ],
                [
                    'key' => 'statusCode',
                    'attributes' => [
                        'code' => LeafLevel::inputProperty('status'),
                    ],
                ],
                array_merge(FieldLevel::effectiveTime(), ['required' => true]),
                FieldLevel::author(),
                [
                    'key' => 'component',
                    'content' => [self::careTeamProviderAct()],
                    'dataKey' => 'providers.provider',
                ],
            ],
            'dataKey' => 'care_team',
        ];
    }
}
