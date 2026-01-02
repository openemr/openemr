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

use OpenEMR\Services\Globals\GlobalSetting;

/**
 * @extends AbstractSettingManager<string>
 */
class ScalarSettingManager extends AbstractSettingManager
{
    public function isDataTypeSupported(string $dataType): bool
    {
        return in_array($dataType, [
            GlobalSetting::DATA_TYPE_TEXT,
            GlobalSetting::DATA_TYPE_COLOR_CODE,
            GlobalSetting::DATA_TYPE_HOUR,
        ], true);
    }

    public function validateSettingValue(string $settingKey, $settingValue): void
    {

    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        $this->validateSettingValue($settingKey, $settingValue);

        $this->driver->setSettingValue($settingKey, $settingValue);
    }

    public function getSettingValue(string $settingKey)
    {
        return $this->driver->getSettingValue($settingKey);
    }
}
