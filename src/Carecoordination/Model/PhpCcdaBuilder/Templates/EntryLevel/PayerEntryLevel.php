<?php

/**
 * PayerEntryLevel.php - Payer entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/payerEntryLevel.js
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

class PayerEntryLevel
{
    /**
     * Policy Activity (private)
     * JS: policyActivity (private)
     */
    private static function policyActivity(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.61', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.61'),
                [
                    'key' => 'id',
                    'attributes' => [
                        'root' => LeafLevel::inputProperty('identifier'),
                        'extension' => LeafLevel::inputProperty('extension'),
                    ],
                    'dataKey' => 'policy.identifiers',
                    'existsWhen' => Condition::keyExists('identifier'),
                    'required' => true,
                ],
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'policy.code',
                ],
                [
                    'key' => 'text',
                    'text' => LeafLevel::input(...),
                    'dataKey' => 'policy.description',
                    'existsWhen' => Condition::keyExists('policy.description'),
                ],
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'effectiveTime',
                    'content' => [
                        [
                            'key' => 'low',
                            'attributes' => [
                                'value' => LeafLevel::inputProperty('low'),
                            ],
                        ],
                        [
                            'key' => 'high',
                            'attributes' => [
                                'value' => LeafLevel::inputProperty('high'),
                            ],
                        ],
                    ],
                    'dataKey' => 'policy.effectiveTime',
                    'existsWhen' => Condition::keyExists('policy.effectiveTime'),
                ],
                [
                    'key' => 'performer',
                    'attributes' => ['typeCode' => 'PRF'],
                    'content' => [
                        FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.87'),
                        FieldLevel::assignedEntity(),
                    ],
                    'dataKey' => 'policy.insurance.performer',
                ],
                [
                    'key' => 'performer',
                    'attributes' => ['typeCode' => 'PRF'],
                    'content' => [
                        FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.88'),
                        array_merge(FieldLevel::effectiveTime(), ['key' => 'time']),
                        FieldLevel::assignedEntity(),
                    ],
                    'dataKey' => 'guarantor',
                ],
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'COV'],
                    'content' => [
                        FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.89'),
                        array_merge(FieldLevel::effectiveTime(), ['key' => 'time']),
                        [
                            'key' => 'participantRole',
                            'attributes' => ['classCode' => 'PAT'],
                            'content' => [
                                FieldLevel::id(),
                                [
                                    'key' => 'code',
                                    'attributes' => LeafLevel::code(),
                                    'dataKey' => 'code',
                                ],
                                FieldLevel::usRealmAddress(),
                                FieldLevel::telecom(),
                                [
                                    'key' => 'playingEntity',
                                    'content' => [
                                        FieldLevel::usRealmName(),
                                        [
                                            'key' => 'sdtc:birthTime',
                                            'attributes' => [
                                                'value' => LeafLevel::inputProperty('birthTime'),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'participant',
                    'dataTransform' => function ($input) {
                        if (isset($input['performer'])) {
                            $input['identifiers'] = $input['performer']['identifiers'] ?? null;
                            $input['address'] = $input['performer']['address'] ?? null;
                            $input['phone'] = $input['performer']['phone'] ?? null;
                        }
                        return $input;
                    },
                ],
                [
                    'key' => 'participant',
                    'attributes' => ['typeCode' => 'HLD'],
                    'content' => [
                        FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.90'),
                        [
                            'key' => 'participantRole',
                            'content' => [
                                FieldLevel::id(),
                                FieldLevel::usRealmAddress(),
                                [
                                    'key' => 'playingEntity',
                                    'content' => [
                                        FieldLevel::usRealmName(),
                                    ],
                                    'dataKey' => 'name',
                                    'existsWhen' => Condition::keyExists('name'),
                                ],
                            ],
                            'dataKey' => 'performer',
                        ],
                    ],
                    'dataKey' => 'policy_holder',
                ],
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'REFR'],
                    'content' => [
                        [
                            'key' => 'act',
                            'attributes' => [
                                'classCode' => 'ACT',
                                'moodCode' => 'DEF',
                            ],
                            'content' => [
                                FieldLevel::id(),
                                [
                                    'key' => 'code',
                                    'attributes' => [
                                        'code' => LeafLevel::inputProperty('authorization_code'),
                                        'displayName' => 'Health Insurance Plan Policy',
                                        'codeSystem' => '2.16.840.1.113883.3.221.5',
                                        'codeSystemName' => 'Source of Payment Typology',
                                    ],
                                    'existsWhen' => Condition::keyExists('authorization_code'),
                                ],
                                [
                                    'key' => 'text',
                                    'text' => LeafLevel::input(...),
                                    'dataKey' => 'plan_name',
                                ],
                            ],
                        ],
                    ],
                    'dataKey' => 'authorization',
                ],
            ],
        ];
    }

    /**
     * Coverage Activity
     * JS: exports.coverageActivity
     */
    public static function coverageActivity(): array
    {
        return [
            'key' => 'act',
            'attributes' => [
                'classCode' => 'ACT',
                'moodCode' => 'EVN',
            ],
            'content' => [
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.60', '2015-08-01'),
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.60'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                FieldLevel::templateCode('CoverageActivity'),
                FieldLevel::$statusCodeCompleted,
                [
                    'key' => 'entryRelationship',
                    'attributes' => ['typeCode' => 'COMP'],
                    'content' => [
                        array_merge(self::policyActivity(), ['required' => true]),
                    ],
                    'required' => true,
                ],
            ],
        ];
    }
}
