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

trait HourSettingsAwareTrait
{
    /**
     * Provides test data for global hour settings.
     *
     * Only includes settings where fixture value equals default value.
     */
    protected static function getHourDataProviderChunks(): iterable
    {
        yield [
            'calendar',
            'schedule_start',
            [
                'setting_key' => 'schedule_start',
                'setting_name' => 'Calendar Starting Hour',
                'setting_description' => 'Beginning hour of day for calendar events.',
                'setting_default_value' => 8,
                'setting_is_default_value' => true,
                'setting_value' => 8,
            ],
        ];

        yield [
            'calendar',
            'schedule_end',
            [
                'setting_key' => 'schedule_end',
                'setting_name' => 'Calendar Ending Hour',
                'setting_description' => 'Ending hour of day for calendar events.',
                'setting_default_value' => 17,
                'setting_is_default_value' => true,
                'setting_value' => 17,
            ],
        ];
    }
}
