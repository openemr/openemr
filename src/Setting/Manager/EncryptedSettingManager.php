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

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Driver\SettingDriverInterface;

class EncryptedSettingManager extends ScalarSettingManager
{
    private readonly CryptoGen $cryptoGen;

    public function __construct(
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ) {
        $this->cryptoGen = new CryptoGen();

        parent::__construct($driver, $globalsService);
    }

    public function isDataTypeSupported(string $dataType): bool
    {
        return $dataType === GlobalSetting::DATA_TYPE_ENCRYPTED;
    }

    public function setSettingValue($settingKey, $settingValue): void
    {
        parent::setSettingValue(
            $settingKey,
            $this->cryptoGen->encryptStandard($settingValue),
        );
    }

    public function getSettingValue($settingKey)
    {
        $value = parent::getSettingValue($settingKey);

        if (
            empty($value)
            || !$this->cryptoGen->cryptCheckStandard($value)
        ) {
            return $value;
        }

        return $this->cryptoGen->decryptStandard($value);
    }
}
