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

trait ColorCodeSettingsAwareTrait
{
    /**
     * Provides test data for global color code settings.
     *
     * Only includes settings where fixture value equals default value.
     */
    protected static function getColorCodeDataProviderChunks(): iterable
    {
        yield [
            'calendar',
            'appt_display_sets_color_1',
            [
                'setting_key' => 'appt_display_sets_color_1',
                'setting_name' => 'Appointment Display Sets - Color 1',
                'setting_description' => 'Color for odd sets (except when last set is odd and all member appointments are displayed and at least one subsequent scheduled appointment exists (not displayed) or not all member appointments are displayed).',
                'setting_default_value' => '#FFFFFF',
                'setting_is_default_value' => true,
                'setting_value' => '#FFFFFF',
            ],
        ];
    }
}
