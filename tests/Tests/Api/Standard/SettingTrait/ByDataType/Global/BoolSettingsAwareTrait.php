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
    /**
     * Provides test data for global boolean settings.
     *
     * Only includes settings where fixture value equals default value,
     * so tests work for both getOneBySettingKey and resetOneBySettingKey.
     */
    protected static function getBoolDataProviderChunks(): iterable
    {
        yield [
            'features',
            'drive_encryption',
            [
                'setting_key' => 'drive_encryption',
                'setting_name' => 'Enable Encryption of Items Stored on Drive (Strongly recommend keeping this on)',
                'setting_description' => 'This will enable encryption of items that are stored on the drive. Strongly recommend keeping this setting on for security purposes.',
                'setting_default_value' => true,
                'setting_is_default_value' => true,
                'setting_value' => true,
            ],
        ];

        // rest_api has different fixture value than default, so can't use for both get and reset tests
        // yield [
        //     'connectors',
        //     'rest_api',
        //     [
        //         'setting_key' => 'rest_api',
        //         'setting_name' => 'Enable OpenEMR Standard REST API',
        //         'setting_description' => 'Enable OpenEMR Standard RESTful API.',
        //         'setting_default_value' => false,
        //         'setting_is_default_value' => false,
        //         'setting_value' => true,
        //     ],
        // ];
    }
}
