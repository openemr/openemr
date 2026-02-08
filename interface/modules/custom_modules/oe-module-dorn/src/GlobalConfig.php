<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 namespace OpenEMR\Modules\Dorn;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig
{
    public const CONFIG_OPTION_API_URL = 'oe_dorn_api_url';
    public const CONFIG_OPTION_ENVIRONMENT = 'oe_dorn_config_environment';
    public const CONFIG_OPTION_CLIENTID = 'oe_dorn_config_clientid';
    public const CONFIG_OPTION_CLIENTSECRET = 'oe_dorn_config_clientsecret';
    public const CONFIG_OPTION_SCOPE = 'oe_dorn_config_scope';
    public const CONFIG_OPTION_AUTHORITY = 'oe_dorn_config_authority';


    public const CONFIG_ENABLE_MENU = "oe_dorn_config_add_menu_button";

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    public function __construct(private array $globalsArray)
    {
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * Returns true if all of the settings have been configured.  Otherwise it returns false.
     *
     * @return bool
     */
    public function isConfigured()
    {
        // $keys = [self::CONFIG_OPTION_TEXT, self::CONFIG_OPTION_ENCRYPTED];
        // foreach ($keys as $key) {
        //     $value = $this->getGlobalSetting($key);
        //     if (empty($value)) {
        //         return false;
        //     }
        // }
        return true;
    }

    public function getClientId()
    {
        return $this->getGlobalSetting(self::CONFIG_OPTION_CLIENTID);
    }
    public function getClientSecret()
    {
        $encryptedValue = $this->getGlobalSetting(self::CONFIG_OPTION_CLIENTSECRET);
        return $this->cryptoGen->decryptStandard($encryptedValue);
    }

    public function getClientScope()
    {
        if ($this->getGlobalSetting(self::CONFIG_OPTION_ENVIRONMENT) == "S") {
            return "https://stagingclaimrevcom.onmicrosoft.com/portal/api/.default";
        } elseif ($this->getGlobalSetting(self::CONFIG_OPTION_ENVIRONMENT) == "D") {
            return "https://claimrevportaldevelopment.onmicrosoft.com/labs.claimrev.com/.default";
        }
        return "https://portalclaimrev.onmicrosoft.com/portal/api/.default";
    }

    public function getClientAuthority()
    {
        if ($this->getGlobalSetting(self::CONFIG_OPTION_ENVIRONMENT) == "S") {
            return "https://stagingclaimrevcom.b2clogin.com/stagingclaimrevcom.onmicrosoft.com/B2C_1_sign-in-service/oauth2/v2.0/token";
        } elseif ($this->getGlobalSetting(self::CONFIG_OPTION_ENVIRONMENT) == "D") {
            return "https://claimrevportaldevelopment.b2clogin.com/claimrevportaldevelopment.onmicrosoft.com/B2C_1_sign-in-service/oauth2/v2.0/token";
        }
        return "https://portalclaimrev.b2clogin.com/portalclaimrev.onmicrosoft.com/B2C_1_sign-in-service/oauth2/v2.0/token";
    }

    public function getApiServer()
    {
        return $this->getGlobalSetting(self::CONFIG_OPTION_API_URL);
    }


    public function getTextOption()
    {
        return $this->getGlobalSetting(self::CONFIG_OPTION_TEXT);
    }

    /**
     * Returns our decrypted value if we have one, or false if the value could not be decrypted or is empty.
     *
     * @return bool|string
     */
    public function getEncryptedOption()
    {
        $encryptedValue = $this->getGlobalSetting(self::CONFIG_OPTION_ENCRYPTED);
        return $this->cryptoGen->decryptStandard($encryptedValue);
    }

    public function getGlobalSetting($settingKey)
    {
        return $this->globalsArray[$settingKey] ?? null;
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::CONFIG_OPTION_ENVIRONMENT => [
                'title' => 'ClaimRev Environment (P=Production)'
                ,'description' => 'The system you connect to. P for production'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => 'P'
            ],
            self::CONFIG_OPTION_API_URL => [
                'title' => 'API URL'
                ,'description' => 'The api system you to connect to'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::CONFIG_OPTION_CLIENTID => [
                'title' => 'Client ID'
                ,'description' => 'Contact ClaimRev for the client ID'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::CONFIG_OPTION_CLIENTSECRET => [
                'title' => 'ClaimRev Client Secret'
                ,'description' => 'Contact ClaimRev for this value'
                ,'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                ,'default' => ''
            ]
            ,self::CONFIG_ENABLE_MENU => [
                'title' => 'Add module menu item'
                ,'description' => 'Adding a menu item to the system (requires logging out and logging in again)'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
        ];//
        return $settings;
    }
}
