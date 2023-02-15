<?php

/**
 * Contains all of the TeleHealth global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2022 Visual EHR <https://visualehr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\VisualEHR;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class VehrGlobalConfig
{
    // note patients always auto provision
    const COMLINK_AUTO_PROVISION_PROVIDER = 'comlink_autoprovision_provider';

    const CONFIG_ENABLE_MENU = "visualehr_enable_menu_item";

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
    public function isTelehealthConfigured()
    {
        $config = $this->getGlobalSettingSectionConfiguration();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            if ($key == $this->isOptionalSetting($key)) {
                continue;
            }
            $value = $this->getGlobalSetting($key);

            if (empty($value)) {
                return false;
            }
        }
        return true;
    }

    public function shouldAutoProvisionProviders(): bool
    {
        $setting = $this->getGlobalSetting(self::COMLINK_AUTO_PROVISION_PROVIDER);
        return $setting !== "";
    }

    public function getGlobalSetting($settingKey)
    {
        global $GLOBALS;
        return $GLOBALS[$settingKey] ?? null;
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::COMLINK_AUTO_PROVISION_PROVIDER => [
                'title' => 'Enable Visual Dashboard in Menu'
                ,'description' => 'Disable this and the visual dashboard will be hidden in the menu'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => '1'
            ]
        ];
        return $settings;
    }

    private function isOptionalSetting($key)
    {
        return $key == self::COMLINK_AUTO_PROVISION_PROVIDER;
    }
}
