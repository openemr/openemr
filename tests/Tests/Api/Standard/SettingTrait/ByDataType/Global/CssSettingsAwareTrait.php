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

trait CssSettingsAwareTrait
{
    protected static function getCssDataProviderChunks(): iterable
    {
        yield [
            'appearance',
            'css_header',
            [
                'setting_key' => 'css_header',
                'setting_name' => 'General Theme',
                'setting_description' => 'Pick a CSS theme.',
                'setting_default_value' => 'style_light.css',
                'setting_is_default_value' => false,
                'setting_value' => 'style_forest_green.css',
                'setting_value_options' => [
                    ['option_value' => 'style_light.css', 'option_label' => 'Light'],
                    ['option_value' => 'style_default.css', 'option_label' => 'Classic'],
                    ['option_value' => 'style_tan.css', 'option_label' => 'Tan'],
                    ['option_value' => 'style_dark.css', 'option_label' => 'Dark'],
                    ['option_value' => 'style_red.css', 'option_label' => 'Red'],
                    ['option_value' => 'style_daylight.css', 'option_label' => 'Daylight'],
                    ['option_value' => 'style_forest_green.css', 'option_label' => 'Forest Green'],
                    ['option_value' => 'style_ackbar.css', 'option_label' => 'Ackbar'],
                    ['option_value' => 'style_nighttime.css', 'option_label' => 'Nighttime'],
                    ['option_value' => 'style_spacelab.css', 'option_label' => 'Space Lab'],
                ],
            ],
        ];
    }
}
