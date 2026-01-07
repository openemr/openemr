<?php

/**
 * GoalEntryLevel.php - Goal entry-level template definitions
 *
 * PHP port of oe-blue-button-generate/lib/entryLevel/goalEntryLevel.js
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

class GoalEntryLevel
{
    /**
     * Goal Activity Observation
     * JS: exports.goalActivityObservation
     */
    public static function goalActivityObservation(): array
    {
        return [
            'key' => 'observation',
            'attributes' => [
                'classCode' => 'OBS',
                'moodCode' => 'GOL',
            ],
            'content' => [
                FieldLevel::templateId('2.16.840.1.113883.10.20.22.4.121'),
                FieldLevel::templateIdExt('2.16.840.1.113883.10.20.22.4.121', '2022-06-01'),
                FieldLevel::uniqueId(),
                FieldLevel::id(),
                [
                    'key' => 'code',
                    'attributes' => LeafLevel::code(),
                    'dataKey' => 'goal_code',
                    'existsWhen' => Condition::propertyNotEmpty('code'),
                ],
                [
                    'key' => 'code',
                    'attributes' => ['nullFlavor' => 'UNK'],
                    'dataKey' => 'goal_code',
                    'existsWhen' => Condition::propertyEmpty('code'),
                ],
                FieldLevel::$statusCodeActive,
                FieldLevel::effectiveTime(),
                // String value (no SDOH code)
                [
                    'key' => 'value',
                    'attributes' => ['xsi:type' => 'ST'],
                    'text' => LeafLevel::inputProperty('name'),
                    'existsWhen' => Condition::propertyEmpty('sdoh_code'),
                ],
                // Coded value (SDOH)
                [
                    'key' => 'value',
                    'attributes' => [
                        'xsi:type' => 'CD',
                        'code' => LeafLevel::inputProperty('sdoh_code'),
                        'codeSystem' => LeafLevel::inputProperty('sdoh_code_system'),
                        'codeSystemName' => LeafLevel::inputProperty('sdoh_code_system_name'),
                        'displayName' => LeafLevel::inputProperty('sdoh_name'),
                    ],
                    'existsWhen' => Condition::propertyNotEmpty('sdoh_code'),
                ],
                FieldLevel::author(),
            ],
            'existsWhen' => fn($input) => ($input['type'] ?? null) === 'observation',
        ];
    }
}
