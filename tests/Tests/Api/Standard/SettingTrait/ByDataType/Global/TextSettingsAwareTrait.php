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

trait TextSettingsAwareTrait
{
    protected static function getTextDataProviderChunks(): iterable
    {
        yield [
            'branding',
            'openemr_name',
            [
                'setting_key' => 'openemr_name',
                'setting_name' => 'Application Title',
                'setting_description' => 'Application name used throughout the user interface.',
                'setting_default_value' => 'OpenEMR',
                'setting_is_default_value' => true,
                'setting_value' => 'OpenEMR',
            ],
        ];
    }
}
