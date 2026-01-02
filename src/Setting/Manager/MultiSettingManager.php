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

namespace OpenEMR\Setting\Manager;

/**
 * @extends AbstractSettingManager<array<string>>
 */
abstract class MultiSettingManager extends AbstractSettingManager
{
    abstract public function isDataTypeSupported(string $dataType): bool;

    public function getSettingDefaultValue(string $settingKey)
    {
        return parent::getSettingDefaultValue($settingKey) ?: [];
    }

    public function getSettingValue(string $settingKey)
    {
        return $this->driver->getMultiSettingValue($settingKey);
    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        $this->driver->setMultiSettingValues($settingKey, $settingValue);
    }
}
