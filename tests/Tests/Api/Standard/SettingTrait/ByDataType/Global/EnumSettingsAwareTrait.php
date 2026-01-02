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

trait EnumSettingsAwareTrait
{
    protected static function getEnumDataProviderChunks(): iterable
    {
        yield [
            'features',
            'enable_help',
            [
                'setting_key' => 'enable_help',
                'setting_name' => 'Enable Help Modal',
                'setting_description' => 'This will allow the display of help modal on help enabled pages',
                'setting_default_value' => '1',
                'setting_is_default_value' => true,
                'setting_value' => '1',
                'setting_value_options' => [[
                    'option_value' => '0',
                    'option_label' => 'Hide Help Modal'
                ],[
                    'option_value' => '1',
                    'option_label' => 'Show Help Modal',
                ],[
                    'option_value' => '2',
                    'option_label' => 'Disable Help Modal',
                ]],
            ],
        ];

        yield [
            'calendar',
            'event_color',
            [
                "setting_key" => "event_color",
                "setting_name" => "Appointment/Event Color",
                "setting_description" => "This determines which color schema used for appointment",
                "setting_default_value" => "1",
                "setting_is_default_value" => true,
                "setting_value" => "1",
                "setting_value_options" => [
                    [
                        "option_value" => "1",
                        "option_label" => "Category Color Schema"
                    ],
                    [
                        "option_value" => "2",
                        "option_label" => "Facility Color Schema"
                    ]
                ]
            ]
        ];
    }
}
