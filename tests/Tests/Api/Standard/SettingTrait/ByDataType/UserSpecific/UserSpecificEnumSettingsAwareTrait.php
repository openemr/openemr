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

namespace OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\UserSpecific;

/**
 * Provides test data for user-specific enum settings.
 */
trait UserSpecificEnumSettingsAwareTrait
{
    protected static function getUserSpecificEnumDataProviderChunks(): iterable
    {
        yield [
            'features',
            'enable_help',
            [
                'setting_key' => 'enable_help',
                'setting_name' => 'Enable Help Modal',
                'setting_description' => 'This will allow the display of help modal on help enabled pages',
                'setting_default_value' => 1,
                'setting_is_default_value' => false,
                'setting_value' => 2,
                'setting_value_options' => [
                    ['option_value' => 0, 'option_label' => 'Hide Help Modal'],
                    ['option_value' => 1, 'option_label' => 'Show Help Modal'],
                    ['option_value' => 2, 'option_label' => 'Disable Help Modal'],
                ],
            ],
        ];

        yield [
            'calendar',
            'event_color',
            [
                'setting_key' => 'event_color',
                'setting_name' => 'Appointment/Event Color',
                'setting_description' => 'This determines which color schema used for appointment',
                'setting_default_value' => 1,
                'setting_is_default_value' => false,
                'setting_value' => 2,
                'setting_value_options' => [
                    ['option_value' => 1, 'option_label' => 'Category Color Schema'],
                    ['option_value' => 2, 'option_label' => 'Facility Color Schema'],
                ],
            ],
        ];

        yield [
            'appearance',
            'search_any_patient',
            [
                'setting_key' => 'search_any_patient',
                'setting_name' => 'Search Patient By Any Demographics',
                'setting_description' => 'Search Patient By Any Demographics, Dual additionally lets direct access to Patient Finder, Comprehensive has collapsed input box, Fixed is similar to Dual with fixed size, None is do not show',
                'setting_default_value' => 'dual',
                'setting_is_default_value' => false,
                'setting_value' => 'fixed',
                'setting_value_options' => [
                    ['option_value' => 'dual', 'option_label' => 'Dual'],
                    ['option_value' => 'comprehensive', 'option_label' => 'Comprehensive'],
                    ['option_value' => 'fixed', 'option_label' => 'Fixed'],
                    ['option_value' => 'none', 'option_label' => 'None'],
                ],
            ],
        ];
    }
}
