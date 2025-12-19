<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global;

trait DefaultVisitCategorySettingsAwareTrait
{
    protected static function getDefaultVisitCategoryDataProviderChunks(): iterable
    {
        yield [
            'encounter-form',
            'default_visit_category',
            [
                'setting_key' => 'default_visit_category',
                'setting_name' => 'Default Visit Category',
                'setting_description' => 'Define a default visit category',
                'setting_default_value' => '_blank',
                'setting_is_default_value' => true,
                'setting_value' => '_blank',
                'setting_value_options' => [
                    [
                        'option_value' => '1',
                        'option_label' => 'No Show'
                    ],
                    [
                        'option_value' => '2',
                        'option_label' => 'In Office'
                    ],
                    [
                        'option_value' => '3',
                        'option_label' => 'Out Of Office'
                    ],
                    [
                        'option_value' => '4',
                        'option_label' => 'Vacation'
                    ],
                    [
                        'option_value' => '5',
                        'option_label' => 'Office Visit'
                    ],
                    [
                        'option_value' => '6',
                        'option_label' => 'Holidays'
                    ],
                    [
                        'option_value' => '7',
                        'option_label' => 'Closed'
                    ],
                    [
                        'option_value' => '8',
                        'option_label' => 'Lunch'
                    ],
                    [
                        'option_value' => '9',
                        'option_label' => 'Established Patient'
                    ],
                    [
                        'option_value' => '10',
                        'option_label' => 'New Patient'
                    ],
                    [
                        'option_value' => '11',
                        'option_label' => 'Reserved'
                    ],
                    [
                        'option_value' => '12',
                        'option_label' => 'Health and Behavioral Assessment'
                    ],
                    [
                        'option_value' => '13',
                        'option_label' => 'Preventive Care Services'
                    ],
                    [
                        'option_value' => '14',
                        'option_label' => 'Ophthalmological Services'
                    ],
                    [
                        'option_value' => '15',
                        'option_label' => 'Group Therapy'
                    ]
                ],
            ],
        ];
    }
}
