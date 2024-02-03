<?php

/**
 * Contains all of the Weno global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 Omega Systems Group <https://omegasystemsgroup.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 namespace OpenEMR\Modules\WenoModule;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;

class WenoGlobalConfig
{
    //globals variable
    const WENO_ENCRYPTION_KEY = 'weno_encryption_key';
    const WENO_ADMIN_USERNAME = 'weno_admin_username';
    const WENO_ADMIN_PASSWORD = 'weno_admin_password';
    const WENO_SETUP_INSTRUCTION_LINK = 'weno_setup_instructions_link';
    const WENO_RX_ENABLE_TEST = 'weno_rx_enable_test';
    const WENO_RX_ENABLE = 'weno_rx_enable';
    const WENO_PROVIDER_PASSWORD = 'weno_provider_password';
    const WENO_PROVIDER_EMAIL = 'weno_provider_email';

    const GLOBAL_SECTION_NAME = 'Weno';

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * Returns true if all the weno settings have been configured.  Otherwise, it returns false.
     * @return bool
     */
    public function isWenoConfigured()
    {
        $config = $this->getGlobalSettingSectionConfiguration();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            $value = $this->getGlobalSetting($key);

            if (empty($value)) {
                return false;
            }
        }
        return true;
    }

    public function getGlobalSetting($settingKey)
    {
        // don't like this as php 8.1 requires this but OpenEMR works with globals and this is annoying.
        return $GLOBALS[$settingKey] ?? null;
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::WENO_RX_ENABLE => [
                'title' => 'Enable Weno eRx Service'
                ,'description' => xl('Enable Weno eRx Service') . ' ' . xl('Contact https://online.wenoexchange.com to sign up for Weno Free eRx service.')
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
                ,'user_setting' => false
            ]
            ,self::WENO_RX_ENABLE_TEST => [
                'title' => xl('Enable Weno eRx Service Test mode')
                ,'description' => xl('Enable Weno eRx Service Test mode. This option will automatically include test pharmacies in your pharmacy download')
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
                ,'user_setting' => false
            ]
            ,self::WENO_ENCRYPTION_KEY => [
                'title' => xl('Weno Encryption Key')
                ,'description' => xl('Encryption key issued by Weno eRx service on the Weno Developer Page')
                ,'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                ,'default' => ''
                ,'user_setting' => false
            ]
            ,self::WENO_ADMIN_USERNAME => [
                'title' => xl('Weno Admin Username')
                ,'description' => xl('This is required for Weno Pharmacy Directory Download in Background Services. Same as email for logging in into Weno')
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
                ,'user_setting' => false
            ]
            ,self::WENO_ADMIN_PASSWORD => [
                'title' => xl('Weno Admin Password')
                ,'description' => xl('')
                ,'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                ,'default' => ''
                ,'user_setting' => false
            ]
            ,self::WENO_PROVIDER_EMAIL => [
                'title' => xl('Weno Provider Email')
                ,'description' => xl('')
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
                ,'user_setting' => true
            ]
            ,self::WENO_PROVIDER_PASSWORD => [
                'title' => xl('Weno Provider Password')
                ,'description' => xl('')
                ,'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                ,'default' => ''
                ,'user_setting' => true
            ]
        ];
        return $settings;
    }
}
