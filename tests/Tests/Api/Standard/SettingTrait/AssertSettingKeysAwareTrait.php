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

namespace OpenEMR\Tests\Api\Standard\SettingTrait;

use OpenEMR\Setting\Manager\AbstractSettingManager;
use PHPUnit\Framework\TestCase;

/**
 * @see SettingManagerInterface > TSetting
 * @see AbstractSettingManager::normalizeSetting
 *
 * @mixin TestCase
 */
trait AssertSettingKeysAwareTrait
{
    protected function assertArrayHasAllSettingKeys(array $settingData): void
    {
        $this->assertArrayHasKey('setting_key', $settingData);
        $this->assertIsString($settingData['setting_key']);

        $this->assertArrayHasKey('setting_name', $settingData);
        $this->assertIsString($settingData['setting_name']);

        $this->assertArrayHasKey('setting_description', $settingData);
        $this->assertIsString($settingData['setting_description']);

        $this->assertArrayHasKey('setting_default_value', $settingData);

        $this->assertArrayHasKey('setting_is_default_value', $settingData);
        $this->assertIsBool($settingData['setting_is_default_value']);

        $this->assertArrayHasKey('setting_value', $settingData);
    }
}
