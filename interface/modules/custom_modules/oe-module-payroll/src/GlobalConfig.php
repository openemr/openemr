<?php

/*
 * @package OpenEMR
 *
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2023.
 * @license "All rights reserved"
 */

namespace Juggernaut\Modules\Payroll;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;
class GlobalConfig
{
    const CONFIG_OPTION_TEXT = 'oe_payroll_config_option_text';
    const CONFIG_OPTION_ENCRYPTED = 'oe_payroll_config_option_encrypted';
    const CONFIG_OVERRIDE_TEMPLATES = "oe_payroll_override_twig_templates";
    const CONFIG_ENABLE_MENU = "oe_payroll_add_menu_button";
    const CONFIG_ENABLE_BODY_FOOTER = "oe_payroll_add_body_footer";
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
        $keys = [self::CONFIG_OPTION_TEXT, self::CONFIG_OPTION_ENCRYPTED];
        foreach ($keys as $key) {
            $value = $this->getGlobalSetting($key);
            if (empty($value)) {
                return false;
            }
        }
        return true;
    }
    public function getTextOption()
    {
        return $this->getGlobalSetting(self::CONFIG_OPTION_TEXT);
    }

    /**
     * Returns our decrypted value if we have one, or false if the value could not be decrypted or is empty.
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
            self::CONFIG_OPTION_TEXT => [
                'title' => 'payroll Module Text Option'
                ,'description' => 'Example global config option with text'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::CONFIG_OPTION_ENCRYPTED => [
                'title' => 'payroll Module Encrypted Option (Encrypted)'
                ,'description' => 'Example of adding an encrypted global configuration value for your module.  Used for sensitive data'
                ,'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                ,'default' => ''
            ]
            ,self::CONFIG_OVERRIDE_TEMPLATES => [
                'title' => 'payroll Module enable overriding twig files'
                ,'description' => 'Shows example of overriding a twig file'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
            ,self::CONFIG_ENABLE_MENU => [
                'title' => 'payroll Module add module menu item'
                ,'description' => 'Shows example of adding a menu item to the system (requires logging out and logging in again)'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
        ];
        return $settings;
    }
}
