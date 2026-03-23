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

trait TabsCssSettingsAwareTrait
{
    protected static function getTabsCssDataProviderChunks(): iterable
    {
        yield [
            'appearance',
            'theme_tabs_layout',
            [
                'setting_key' => 'theme_tabs_layout',
                'setting_name' => 'Tabs Layout Theme',
                'setting_description' => 'Theme for the appearance of the frame tabs.',
                'setting_default_value' => 'tabs_style_full.css',
                'setting_is_default_value' => false,
                'setting_value' => 'tabs_style_compact.css',
                'setting_value_options' => [
                    ['option_value' => 'tabs_style_full.css', 'option_label' => 'Full'],
                    ['option_value' => 'tabs_style_compact.css', 'option_label' => 'Compact'],
                ],
            ],
        ];
    }
}
