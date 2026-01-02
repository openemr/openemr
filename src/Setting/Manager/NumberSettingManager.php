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
 * @extends AbstractSettingManager<int>
 */
class NumberSettingManager extends ScalarSettingManager
{
    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_NUMBER;
    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        parent::setSettingValue($settingKey, (int) $settingValue);
    }

    public function getSettingDefaultValue(string $settingKey)
    {
        return (int) parent::getSettingDefaultValue($settingKey);
    }

    public function getSettingValue(string $settingKey)
    {
        return (int) parent::getSettingValue($settingKey);
    }
}
