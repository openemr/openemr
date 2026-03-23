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
 * Provides test data for user-specific boolean settings.
 */
trait UserSpecificBoolSettingsAwareTrait
{
    protected static function getUserSpecificBoolDataProviderChunks(): iterable
    {
        yield [
            'appearance',
            'enable_compact_mode',
            [
                'setting_key' => 'enable_compact_mode',
                'setting_name' => 'Enable Compact Mode',
                'setting_description' => 'Changes the current theme to be more compact.',
                'setting_default_value' => null,
                'setting_is_default_value' => false,
                'setting_value' => true,
            ],
        ];

        yield [
            'appearance',
            'gbl_pt_list_new_window',
            [
                'setting_key' => 'gbl_pt_list_new_window',
                'setting_name' => 'Patient List New Window',
                'setting_description' => 'Default state of New Window checkbox in the patient list.',
                'setting_default_value' => null,
                'setting_is_default_value' => false,
                'setting_value' => true,
            ],
        ];

        yield [
            'features',
            'expand_form',
            [
                'setting_key' => 'expand_form',
                'setting_name' => 'Expand Form',
                'setting_description' => 'Open all expandable forms in expanded state',
                'setting_default_value' => true,
                'setting_is_default_value' => false,
                'setting_value' => false,
            ],
        ];

        yield [
            'features',
            'messages_due_date',
            [
                'setting_key' => 'messages_due_date',
                'setting_name' => 'Messages - due date',
                'setting_description' => 'Enables choose due date to message',
                'setting_default_value' => false,
                'setting_is_default_value' => false,
                'setting_value' => true,
            ],
        ];
    }
}
