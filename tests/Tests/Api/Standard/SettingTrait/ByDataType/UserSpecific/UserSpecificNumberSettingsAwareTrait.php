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
 * Provides test data for user-specific number settings.
 */
trait UserSpecificNumberSettingsAwareTrait
{
    protected static function getUserSpecificNumberDataProviderChunks(): iterable
    {
        // User has override "5", global default is 0
        yield [
            'calendar',
            'checkout_roll_off',
            [
                'setting_key' => 'checkout_roll_off',
                'setting_name' => 'Flow Board: display completed checkouts (minutes)',
                'setting_description' => 'Flow Board will only display completed checkouts for this many minutes. Zero is continuous display.',
                'setting_default_value' => 0,
                'setting_is_default_value' => false,
                'setting_value' => 5,
            ],
        ];

        // User has override "3", global default is 0
        yield [
            'carecoordination',
            'ccda_view_max_sections',
            [
                'setting_key' => 'ccda_view_max_sections',
                'setting_name' => 'Max Sections To Display',
                'setting_description' => 'Total number of clinical sections to display when viewing a CCD-A document (0 for unlimited)',
                'setting_default_value' => 0,
                'setting_is_default_value' => false,
                'setting_value' => 3,
            ],
        ];
    }
}
