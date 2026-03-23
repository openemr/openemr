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

namespace OpenEMR\Setting\Driver;

/**
 * @template TSettingValue
 */
interface SettingDriverInterface
{
    /**
     * @phpstan-return TSettingValue
     */
    public function getSettingDefaultValue(string $settingKey);

    /**
     * @phpstan-param TSettingValue $settingValue
     */
    public function setSettingValue(string $settingKey, $settingValue): void;

    /**
     * @phpstan-return TSettingValue
     */
    public function getSettingValue(string $settingKey);

    /**
     * @phpstan-param TSettingValue $settingValue
     */
    public function setMultiSettingValues(string $settingKey, array $settingValues): void;

    /**
     * @phpstan-return TSettingValue
     */
    public function getMultiSettingValue(string $settingKey): array;


    public function resetSetting(string $settingKey): void;
}
