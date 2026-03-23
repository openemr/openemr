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
 * Provides test data for user-specific CSS settings.
 */
trait UserSpecificCssSettingsAwareTrait
{
    protected static function getUserSpecificCssDataProviderChunks(): iterable
    {
        yield [
            'appearance',
            'css_header',
            [
                'setting_key' => 'css_header',
                'setting_name' => 'General Theme*',
                'setting_description' => 'Pick a general theme (need to logout/login after changing this setting).',
                'setting_default_value' => 'style_light.css',
                'setting_is_default_value' => false,
                'setting_value' => 'style_dark.css',
            ],
        ];
    }
}
