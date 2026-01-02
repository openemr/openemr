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

trait NumberSettingsAwareTrait
{
    protected static function getNumberDataProviderChunks(): iterable
    {
        yield [
            'locale',
            'age_display_limit',
            [
                'setting_key' => 'age_display_limit',
                'setting_name' => 'Age in Years for Display Format Change',
                'setting_description' => 'If YMD is selected for age display, switch to just Years when patients older than this value in years',
                'setting_default_value' => 3,
                'setting_is_default_value' => true,
                'setting_value' => 3,
            ],
        ];
    }
}
