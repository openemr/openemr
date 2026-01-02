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

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Driver\SettingDriverInterface;

class EncryptedHashSettingManager extends EncryptedSettingManager
{
    private readonly AuthHash $authHash;

    public function __construct(
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        $this->authHash = new AuthHash();

        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_ENCRYPTED_HASH;
    }

    public function setSettingValue($settingKey, $settingValue): void
    {
        if (!AuthHash::hashValid($settingValue)) {
            $settingValue = $this->authHash->passwordHash($settingValue);
        }

        parent::setSettingValue($settingKey, $settingValue);
    }
}
