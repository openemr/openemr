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
 * Provides test data for user-specific tabs CSS settings.
 */
trait UserSpecificTabsCssSettingsAwareTrait
{
    protected static function getUserSpecificTabsCssDataProviderChunks(): iterable
    {
        yield [
            'appearance',
            'theme_tabs_layout',
            [
                'setting_key' => 'theme_tabs_layout',
                'setting_name' => 'Tabs Layout Theme*',
                'setting_description' => 'Theme of the tabs layout (need to logout and then login to see this new setting).',
                'setting_default_value' => 'tabs_style_full.css',
                'setting_is_default_value' => false,
                'setting_value' => 'tabs_style_compact.css',
            ],
        ];
    }
}
