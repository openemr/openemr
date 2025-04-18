<?php

/**
 * Global configuration settings for the HIPAAi Chat module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org> - Modified by Geviti
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org> - Modified by Geviti
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\HipaaiChat;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig
{
    const CONFIG_PIIPS_API_KEY = 'oe_hipaaichat_piips_api_key';

    private $globalsArray;

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    public function __construct(array $globalsArray)
    {
        $this->globalsArray = $globalsArray;
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * Returns true if all of the settings have been configured.  Otherwise it returns false.
     * @return bool
     */
    public function isConfigured()
    {
        return true; // Placeholder: Module is always configured
    }

    public function getPiipsApiKey()
    {
        $encryptedValue = $this->getGlobalSetting(self::CONFIG_PIIPS_API_KEY);
        return $this->cryptoGen->decryptStandard($encryptedValue);
    }

    public function getGlobalSetting($settingKey)
    {
        return $this->globalsArray[$settingKey] ?? null;
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::CONFIG_PIIPS_API_KEY => [
                'title' => 'HIPAAi Chat PIIPS API Key (Encrypted)',
                'description' => 'API Key for the PII Protection Service (pii-guard-llm). STORED ENCRYPTED.',
                'type' => GlobalSetting::DATA_TYPE_ENCRYPTED,
                'default' => ''
            ]
        ];
        return $settings;
    }
}
