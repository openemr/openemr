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

use Comlink\OpenEMR\Module\GlobalConfig;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;

class TelehealthGlobalConfig
{
    const COMLINK_VIDEO_TELEHEALTH_API = 'comlink_telehealth_video_uri';
    const COMLINK_VIDEO_REGISTRATION_API = 'comlink_telehealth_registration_uri';
    const COMLINK_VIDEO_API_USER_ID = 'comlink_telehealth_user_id';
    const COMLINK_VIDEO_API_USER_PASSWORD = 'comlink_telehealth_user_password';
    const COMLINK_VIDEO_TELEHEALTH_CMS_ID = 'comlink_telehealth_cms_id';
    // note patients always auto provision
    const COMLINK_AUTO_PROVISION_PROVIDER = 'comlink_autoprovision_provider';
    const COMLINK_ENABLE_THIRDPARTY_INVITATIONS = "comlink_telehealth_thirdparty_enabled";
    const UNIQUE_INSTALLATION_ID = "unique_installation_id";
    const INSTALLATION_NAME  = "openemr_name";
    const DEBUG_MODE_FLAG = "comlink_telehealth_debug";

    const COMLINK_MINIMIZED_SESSION_POSITION_DEFAULT = "comlink_telehealth_minimized_position_default";
    const DEFAULT_MINIMIZED_SESSION_POSITION_DEFAULT = 'bottom-left';


    // character length to generate for the unique registration code for the user
    const APP_REGISTRATION_CODE_LENGTH = 12;

    // TODO: @adunsulag replace this with the name of the app that comlink is using.
    const COMLINK_MOBILE_APP_TITLE = "Comlink App";

    const VERIFY_SETTINGS_BUTTON = "comlink_verify_settings_button";

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    public function __construct($publicWebPath)
    {
        $this->cryptoGen = new CryptoGen();
        $this->publicWebPath = $publicWebPath;
    }

    public function getPortalTimeout()
    {
        return $this->getGlobalSetting('portal_timeout') ?? 1800; // timeout is in seconds
    }

    public function getOpenEMRName()
    {
        return $this->getGlobalSetting('openemr_name');
    }

    public function getPatientReminderName()
    {
        return $this->getGlobalSetting('patient_reminder_sender_email');
    }

    public function getQualifiedSiteAddress()
    {
        return $this->getGlobalSetting('qualified_site_addr');
    }

    public function getPortalOnsiteAddress()
    {
        // return the portal address to be used.
        if ($this->getGlobalSetting('portal_onsite_two_basepath') == '1') {
            return $this->getQualifiedSiteAddress() . '/portal/patient';
        } else {
            return $this->getGlobalSetting('portal_onsite_two_address');
        }
    }

    public function isThirdPartyInvitationsEnabled()
    {
        return $this->getGlobalSetting(self::COMLINK_ENABLE_THIRDPARTY_INVITATIONS) == '1';
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

        // if third party is enabled make sure the portal is configured
        if ($this->isThirdPartyInvitationsEnabled()) {
            return $this->isThirdPartyConfigurationSetup();
        }
        return true;
    }

    private function isThirdPartyConfigurationSetup()
    {
        // check to make sure the dependent portal settings are setup correctly
        $enabled = $this->getGlobalSetting('portal_onsite_two_enable') == '1';
        $useBasePath = $this->getGlobalSetting('portal_onsite_two_basepath') == '1';
        if (!$enabled) {
            return false;
        }
        if (!$useBasePath) {
            // check to make sure the portal url is not the default
            $defaultValue = $this->getGlobalSetting('portal_onsite_two_address');
            // TODO: @adunsulag can we pull the default onsite configuration pulled out into a constant somewhere?
            if ($defaultValue == 'https://your_web_site.com/openemr/portal') {
                return false;
            }
        }
        return true;
    }

    public function isDebugModeEnabled()
    {
        $setting = $this->getGlobalSetting(self::DEBUG_MODE_FLAG);
        return $setting !== "";
    }

    public function getImagesStaticRelative()
    {
        return $this->getGlobalSetting('images_static_relative');
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

    public function getMinimizedSessionDefaultPosition() {
        $setting = $this->getGlobalSetting(self::COMLINK_MINIMIZED_SESSION_POSITION_DEFAULT);
        if (empty($setting)) {
            $setting = self::DEFAULT_MINIMIZED_SESSION_POSITION_DEFAULT;
        }
        return $setting;
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
            ,self::COMLINK_ENABLE_THIRDPARTY_INVITATIONS => [
                'title' => 'Third Party Session Invitations Allowed (Requires Portal To Be Configured)'
                , 'description' => 'Allow an existing patient to be invited or new patient to be invited to a telehealth session'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
            ,self::COMLINK_MINIMIZED_SESSION_POSITION_DEFAULT => [
                'title' => 'Default Minimized Telehealth Location'
                ,'description' => 'Where should the minimized window appear by default on the screen'
                ,'default' => self::DEFAULT_MINIMIZED_SESSION_POSITION_DEFAULT
                // really don't like how the 'type' can be an array of values, but we have to work with existing architecture
                ,'type' => [
                    'bottom-left' => xl('Bottom Left')
                    ,'top-left' => xl('Top Left')
                    ,'bottom-right' => xl('Bottom Right')
                    ,'top-right' => xl('Top Right')
                ]
            ]
            ,self::DEBUG_MODE_FLAG => [
                'title' => 'Debug Mode'
                , 'description' => 'Turn on debug versions of javascript and other debug settings'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
//            ,self::VERIFY_SETTINGS_BUTTON => [
//                'title' => 'Verify Comlink Installation Settings'
//                ,'description' => 'Verifies the comlink telehealth provisioning settings are correct. Requires the settings to be saved first'
//                ,'type' => GlobalSetting::DATA_TYPE_BUTTON_AJAX_DISPLAY
//                ,'default' => ''
//                ,'options' => [
//                    GlobalSetting::DATA_TYPE_OPTION_AJAX_URL => $this->publicWebPath . 'index.php?action=verify_installation_settings'
//                ]
//            ]
        ];
        return $settings;
    }

    public function setupConfiguration(GlobalsService $service)
    {
        global $GLOBALS;
        $section = xlt("TeleHealth");
        $service->createSection($section, 'Portal');

        $settings = $this->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $value = $GLOBALS[$key] ?? $config['default'];
            $setting = new GlobalSetting(
                xlt($config['title']),
                $config['type'],
                $value,
                xlt($config['description']),
                true
            );
            if (!empty($config['options'])) {
                foreach ($config['options'] as $key => $option) {
                    $setting->addFieldOption($key, $option);
                }
            }
            $service->appendToSection(
                $section,
                $key,
                $setting
            );
        }
    }

    private function isOptionalSetting($key)
    {
        return $key == self::COMLINK_AUTO_PROVISION_PROVIDER || $key == self::VERIFY_SETTINGS_BUTTON
            || $key == self::COMLINK_ENABLE_THIRDPARTY_INVITATIONS;
    }
}
