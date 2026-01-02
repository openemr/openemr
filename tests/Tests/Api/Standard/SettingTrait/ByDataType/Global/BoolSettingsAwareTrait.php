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

trait BoolSettingsAwareTrait
{
    protected static function getBoolDataProviderChunks(): iterable
    {
        yield [
            'features',
            'drive_encryption',
            [
                'setting_key' => 'drive_encryption',
                'setting_name' => 'Enable Encryption of Items Stored on Drive (Strongly recommend keeping this on)',
                'setting_description' => 'This will enable encryption of items that are stored on the drive. Strongly recommend keeping this setting on for security purposes.',
                'setting_default_value' => false,
                'setting_is_default_value' => true,
                'setting_value' => false,
            ],
        ];

        yield [
            'appearance',
            'gbl_pt_list_new_window',
            [
                'setting_key' => 'gbl_pt_list_new_window',
                'setting_name' => 'Patient List New Window',
                'setting_description' => 'Default state of New Window checkbox in the patient list.',
                'setting_default_value' => false,
                'setting_is_default_value' => true,
                'setting_value' => false
            ],
        ];
    }
}
