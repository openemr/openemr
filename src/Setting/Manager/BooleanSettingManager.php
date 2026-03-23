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
 * @extends ScalarSettingManager<bool>
 */
class BooleanSettingManager extends ScalarSettingManager
{
    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_BOOL;
    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        parent::setSettingValue($settingKey, (bool) $settingValue);
    }

    public function getSettingDefaultValue(string $settingKey)
    {
        return (bool) parent::getSettingDefaultValue($settingKey);
    }
    public function getSettingValue(string $settingKey): bool
    {
        return (bool) parent::getSettingValue($settingKey);
    }
}
