<?php

/**
 * Contains all of the Weno global settings and configuration
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Omega Systems Group <https://omegasystemsgroup.com/>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

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
    const WENO_PROVIDER_UID = 'weno_provider_uid';

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
     * @return array[]
     * @deprecated Left for legacy purposes and replaced by installation set up.
     */
    public function getGlobalSettingSectionConfiguration(): array
    {
        return [
            self::WENO_RX_ENABLE => [
                'title' => 'Enable Weno eRx Service'
                , 'description' => xl('Enable Weno eRx Service') . ' ' . xl('Contact https://online.wenoexchange.com to sign up for Weno Free eRx service.')
                , 'type' => GlobalSetting::DATA_TYPE_BOOL
                , 'default' => ''
                , 'user_setting' => false
            ]
            , self::WENO_RX_ENABLE_TEST => [
                'title' => xl('Enable Weno eRx Service Test mode')
                , 'description' => xl('Enable Weno eRx Service Test mode. This option will automatically include test pharmacies in your pharmacy download')
                , 'type' => GlobalSetting::DATA_TYPE_BOOL
                , 'default' => ''
                , 'user_setting' => false
            ],
            self::WENO_PROVIDER_EMAIL => [
                'title' => xl('Weno Prescriber Email')
                , 'description' => xl('')
                , 'type' => GlobalSetting::DATA_TYPE_TEXT
                , 'default' => ''
                , 'user_setting' => true
            ]
            , self::WENO_PROVIDER_PASSWORD => [
                'title' => xl('Weno Prescriber Password')
                , 'description' => xl('')
                , 'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                , 'default' => ''
                , 'user_setting' => true
            ]
            , self::WENO_PROVIDER_UID => [
                'title' => xl('Weno Prescriber ID')
                , 'description' => xl('When a Weno eRx Prescriber, please enter your Weno User ID here or in your Users setting. If you are not a Weno Prescriber, please leave this field blank.')
                , 'type' => GlobalSetting::DATA_TYPE_TEXT
                , 'default' => ''
                , 'user_setting' => true
            ]
        ];
    }
}
