<?php

namespace OEMR\OpenEMR\Modules\Voicenote;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class VoicenoteGlobalConfig
{
    const OEMR_APP_NAME = 'oemr_app_name';

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * Returns true if all of the telehealth settings have been configured.  Otherwise it returns false.
     * @return bool
     */
    public function isVoicenoteConfigured()
    {
        $config = $this->getGlobalSettingSectionConfiguration();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            if ($key == $this->isOptionalSetting($key)) {
                continue;
            }
            $value = $this->getGlobalSettingSectionConfiguration($key);

            if (empty($value)) {
                return false;
            }
        }
        return true;
    }

    public function getInstitutionName()
    {
        return $this->getGlobalSetting(self::OEMR_APP_NAME);
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::OEMR_APP_NAME => [
                'title' => 'Voicenote App Name'
                ,'description' => 'Voicenote app name.'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
        ];
        return $settings;
    }

    private function isOptionalSetting($key)
    {
        return $key == self::OEMR_APP_NAME;
    }
}
