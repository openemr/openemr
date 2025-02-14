<?php

/**
 * Contains all of the TeleHealth global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class TelehealthGlobalConfig
{
    const COMLINK_VIDEO_TELEHEALTH_API = 'comlink_telehealth_video_uri';
    const COMLINK_VIDEO_REGISTRATION_API = 'comlink_telehealth_registration_uri';
    const COMLINK_VIDEO_API_USER_ID = 'comlink_telehealth_user_id';
    const COMLINK_VIDEO_API_USER_PASSWORD = 'comlink_telehealth_user_password';
    const COMLINK_VIDEO_TELEHEALTH_CMS_ID = 'comlink_telehealth_cms_id';
    // note patients always auto provision
    const COMLINK_AUTO_PROVISION_PROVIDER = 'comlink_autoprovision_provider';
    const UNIQUE_INSTALLATION_ID = "unique_installation_id";
    const INSTALLATION_NAME  = "openemr_name";

    // character length to generate for the unique registration code for the user
    const APP_REGISTRATION_CODE_LENGTH = 12;

    // TODO: @adunsulag replace this with the name of the app that comlink is using.
    const COMLINK_MOBILE_APP_TITLE = "Comlink App";

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
    }

    /**
     * @return string
     */
    public function getAppTitle()
    {
        return self::COMLINK_MOBILE_APP_TITLE;
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

    public function getInstitutionId()
    {
        return $this->getGlobalSetting(self::UNIQUE_INSTALLATION_ID);
    }

    public function getInstitutionName()
    {
        return $this->getGlobalSetting(self::INSTALLATION_NAME);
    }

    public function getRegistrationAPIURI()
    {
        return $this->getGlobalSetting(self::COMLINK_VIDEO_REGISTRATION_API);
    }

    public function getTelehealthAPIURI()
    {
        return $this->getGlobalSetting(self::COMLINK_VIDEO_TELEHEALTH_API);
    }

    public function getRegistrationAPIUserId()
    {
        return $this->getGlobalSetting(self::COMLINK_VIDEO_API_USER_ID);
    }

    public function getRegistrationAPIPassword()
    {
        $encryptedValue = $this->getGlobalSetting(self::COMLINK_VIDEO_API_USER_PASSWORD);
        return $this->cryptoGen->decryptStandard($encryptedValue);
    }

    public function getRegistrationAPICmsId()
    {
        return $this->getGlobalSetting(self::COMLINK_VIDEO_TELEHEALTH_CMS_ID);
    }

    public function shouldAutoProvisionProviders(): bool
    {
        $setting = $this->getGlobalSetting(self::COMLINK_AUTO_PROVISION_PROVIDER);
        return $setting !== "";
    }

    public function getGlobalSetting($settingKey)
    {
        global $GLOBALS;
        // don't like this as php 8.1 requires this but OpenEMR works with globals and this is annoying.
        return $GLOBALS[$settingKey] ?? null;
    }

    public function getAppRegistrationCodeLength()
    {
        return self::APP_REGISTRATION_CODE_LENGTH;
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::COMLINK_VIDEO_REGISTRATION_API => [
                'title' => 'Telehealth Registration URI'
                ,'description' => 'Registration endpoint URI'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::COMLINK_VIDEO_TELEHEALTH_API => [
                'title' => 'Telehealth Video API URI'
                ,'description' => 'The URI for the video bridge api'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::COMLINK_VIDEO_API_USER_ID => [
                'title' => 'Telehealth Installation User ID'
                ,'description' => 'This is your unique video application api user id. Contact ComLink if you have not received it'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::COMLINK_VIDEO_API_USER_PASSWORD => [
                'title' => 'Telehealth Installation User Password (Encrypted)'
                ,'description' => 'This is your unique video application api password. Contact ComLink if you have not received it'
                ,'type' => GlobalSetting::DATA_TYPE_ENCRYPTED
                ,'default' => ''
            ]
            ,self::COMLINK_VIDEO_TELEHEALTH_CMS_ID => [
                'title' => 'Telehealth Installation CMSID'
                ,'description' => 'This is your unique video application CMSID. Contact ComLink if you have not received it'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ]
            ,self::COMLINK_AUTO_PROVISION_PROVIDER => [
                'title' => 'Auto Register Providers For Telehealth'
                ,'description' => 'Disable this setting if you will manually enable the providers you wish to be registered for Telehealth'
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
