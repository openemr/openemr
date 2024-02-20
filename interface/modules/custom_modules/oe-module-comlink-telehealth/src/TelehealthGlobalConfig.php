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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use MyMailer;
use Twig\Environment;

class TelehealthGlobalConfig
{
    public const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";

    public const COMLINK_VIDEO_TELEHEALTH_API = 'comlink_telehealth_video_uri';
    public const COMLINK_VIDEO_REGISTRATION_API = 'comlink_telehealth_registration_uri';
    public const COMLINK_VIDEO_API_USER_ID = 'comlink_telehealth_user_id';
    public const COMLINK_VIDEO_API_USER_PASSWORD = 'comlink_telehealth_user_password';
    public const COMLINK_VIDEO_TELEHEALTH_CMS_ID = 'comlink_telehealth_cms_id';
    // note patients always auto provision
    public const COMLINK_AUTO_PROVISION_PROVIDER = 'comlink_autoprovision_provider';
    public const COMLINK_ENABLE_THIRDPARTY_INVITATIONS = "comlink_telehealth_thirdparty_enabled";
    public const UNIQUE_INSTALLATION_ID = "unique_installation_id";
    public const INSTALLATION_NAME  = "openemr_name";
    public const DEBUG_MODE_FLAG = "comlink_telehealth_debug";

    public const COMLINK_MINIMIZED_SESSION_POSITION_DEFAULT = "comlink_telehealth_minimized_position_default";
    public const DEFAULT_MINIMIZED_SESSION_POSITION_DEFAULT = 'bottom-left';


    // character length to generate for the unique registration code for the user
    public const APP_REGISTRATION_CODE_LENGTH = 12;

    // TODO: @adunsulag replace this with the name of the app that comlink is using.
    public const COMLINK_MOBILE_APP_TITLE = "Comlink App";

    public const VERIFY_SETTINGS_BUTTON = "comlink_verify_settings_button";

    /**
     * Setting used for enabling the onetime passwordless login option.
     */
    public const COMLINK_ONETIME_PASSWORD_LOGIN = "comlink_onetime_password_login";

    public const COMLINK_SECTION_FOOTER_BOX = "comlink_section_footer_box";

    public const COMLINK_ONETIME_PASSWORD_LOGIN_TIME_LIMIT = "comlink_onetime_password_login_time_limit";

    public const COMLINK_TELEHEALTH_PAYMENT_SUBSCRIPTION_ID = "comlink_telehealth_payment_subscription_id";

    public const MAX_LOGIN_LIMIT_TIME = 30;
    const LOCALE_TIMEZONE_DEFAULT = "Unassigned";
    const LOCALE_TIMEZONE = "gbl_time_zone";

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    /**
     * @var publicWebPath
     */
    private $publicWebPath;

    /**
     * @var Environment $twig
     */
    private $twig;


    public function __construct($publicWebPath, $moduleDirectoryName, Environment $twig)
    {
        $this->cryptoGen = new CryptoGen();
        $this->publicWebPath = $publicWebPath;
        $this->twig = $twig;
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
            $site_addr = $this->getGlobalSetting('portal_onsite_two_address');
            if (stripos($site_addr, "portal") !== false) {
                $site_addr = strtok($site_addr, '?');
                if (stripos($site_addr, "index.php") !== false) {
                    $site_addr = dirname($site_addr);
                }
                if (substr($site_addr, -1) == '/') {
                    $site_addr = substr($site_addr, 0, -1);
                }
            }
            return $site_addr;
        }
    }

    public function getPublicWebPath()
    {
        return $this->publicWebPath;
    }

    public function isOneTimePasswordLoginEnabled()
    {
        $setting = $this->getGlobalSetting(self::COMLINK_ONETIME_PASSWORD_LOGIN);
        if ($setting === null) {
            return false;
        } else {
            return $setting;
        }
    }

    public function isThirdPartyInvitationsEnabled()
    {
        return $this->getGlobalSetting(self::COMLINK_ENABLE_THIRDPARTY_INVITATIONS) == '1';
    }

    public function getFHIRPath()
    {
        // this is the internal fhir path not the one accessible from the globals config
        $webroot = $this->getGlobalSetting('webroot');
        $path = ($webroot ?? "") . '/apis/fhir/';
        return $path;
    }

    /**
     * @return string
     */
    public function getAppTitle()
    {
        return self::COMLINK_MOBILE_APP_TITLE;
    }

    /**
     * Checks if the core telehealth configuration settings are properly setup.
     * @return false|void
     */
    public function isTelehealthCoreSettingsConfigured()
    {
        $config = $this->getGlobalSettingSectionConfiguration();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            if ($key == $this->isOptionalSetting($key)) {
                continue;
            }
            $value = $this->getGlobalSetting($key);

            if (empty($value)) {
                (new SystemLogger())->debug("Telehealth is missing configuration key", ['key' => $key]);
                return false;
            }
        }
        return true;
    }
    /**
     * Returns true if all of the telehealth settings have been configured.  Otherwise it returns false.
     * @return bool
     */
    public function isTelehealthConfigured()
    {
        if (!$this->isTelehealthCoreSettingsConfigured()) {
            return false;
        }

        // if third party is enabled make sure the portal is configured
        if ($this->isThirdPartyInvitationsEnabled()) {
            return $this->isThirdPartyConfigurationSetup();
        }
        return true;
    }

    /**
     * Checks to determine if the mail server email notifications is setup properly
     * @return bool
     */
    public function isEmailNotificationsConfigured()
    {
        $myMailerSetup = MyMailer::isConfigured();
        if ($myMailerSetup & !empty($this->getPatientReminderName())) {
            return true;
        }
        return false;
    }

    private function isThirdPartyConfigurationSetup()
    {
        // check to make sure the dependent portal settings are setup correctly
        $enabled = $this->getGlobalSetting('portal_onsite_two_enable') == '1';
        $useBasePath = $this->getGlobalSetting('portal_onsite_two_basepath') == '1';
        if (!$enabled) {
            (new SystemLogger())->debug("Telehealth is missing portal_onsite_two_enable enabled");
            return false;
        }
        if (!$useBasePath) {
            // check to make sure the portal url is not the default
            $defaultValue = $this->getGlobalSetting('portal_onsite_two_address');
            // TODO: @adunsulag can we pull the default onsite configuration pulled out into a constant somewhere?
            if ($defaultValue == 'https://your_web_site.com/openemr/portal') {
                (new SystemLogger())->debug("Telehealth is using unconfigured portal_onsite_two_address");
                return false;
            }
        }
        // have to have the qualified site address for our full email link
        if (empty($this->getQualifiedSiteAddress())) {
            (new SystemLogger())->debug("Telehealth is missing qualified site address");
            return false;
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
        return UniqueInstallationUuid::getUniqueInstallationUuid();
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

    public function getMinimizedSessionDefaultPosition()
    {
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
            ,self::COMLINK_TELEHEALTH_PAYMENT_SUBSCRIPTION_ID => [
                'title' => 'Telehealth Payment Subscription ID'
                ,'description' => 'This is your unique video application payment subscription id. Signup via the Manage Modules configuration screen if you have not received it'
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
            ,self::COMLINK_ONETIME_PASSWORD_LOGIN => [
                'title' => 'Enable Pre-Authenticated Patient Login Link'
                , 'description' => 'Allow patients to receive a time limited link to access their telehealth session'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
            ,self::COMLINK_ONETIME_PASSWORD_LOGIN_TIME_LIMIT => [
                'title' => 'Pre-Authenticated Patient Login Link Timeout (Minutes)'
                , 'description' => 'The amount of minutes the pre-authenticated link will be valid for (maximum of 30 minutes). Note provide sufficient time as email delivery delays can cause the link to expire before the patient can use it. '
                , 'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => '15'
            ]
            ,self::DEBUG_MODE_FLAG => [
                'title' => 'Debug Mode'
                , 'description' => 'Turn on debug versions of javascript and other debug settings'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ]
            ,self::COMLINK_SECTION_FOOTER_BOX => [
                'title' => 'Telehealth Footer Box'
                , 'description' => 'This is an information section for providing additional information about this configuration'
                ,'type' => GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION
                ,'default' => ''
                ,'options' => [
                    GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK => [$this, 'renderFooterBox']
                ]
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

    public function renderFooterBox($fldid, $fldarray)
    {
        $emailNotificationsConfigured = $this->isEmailNotificationsConfigured();
        $isThirdPartyConfigurationSetup = $this->isThirdPartyConfigurationSetup();
        // need to check and make sure the portal site address has the same hostname / address as the site address override
        $qualifiedSiteAddress = $this->getQualifiedSiteAddress();
        $portalAddress = $this->getPortalOnsiteAddress();
        $qualifiedHost = parse_url($qualifiedSiteAddress, PHP_URL_HOST);
        $portalHost = parse_url($portalAddress, PHP_URL_HOST);
        $hostnamesMatch = $qualifiedHost === $portalHost;

        $isValidRegistrationUri = filter_var($this->getRegistrationAPIURI(), FILTER_VALIDATE_URL);
        $isValidTelehealthApi = filter_var($this->getTelehealthAPIURI(), FILTER_VALIDATE_URL);

        $isLocaleConfigured = $this->isLocaleConfigured();

        $dataArray = [
            'emailNotificationsConfigured' => $emailNotificationsConfigured
            ,'isThirdPartyConfigurationSetup' => $isThirdPartyConfigurationSetup
            ,'hostnamesMatch' => $hostnamesMatch
            ,'isValidTelehealthApi' => $isValidTelehealthApi
            ,'isValidRegistrationUri' => $isValidRegistrationUri
            ,'fldid' => $fldid
            ,'fldarray' => $fldarray
            ,'verifyInstallationPathUrl' => $this->publicWebPath . 'index.php?action=verify_installation_settings'
            ,'telehealthCvbUrl' => $this->publicWebPath . 'assets/js/src/cvb.min.js'
            ,'isLocaleConfigured' => $isLocaleConfigured
        ];

        return $this->twig->render("comlink/admin/telehealth_footer_box.html.twig", $dataArray);
    }

    private function isLocaleConfigured()
    {
        // timezone is not set in the $GLOBALS array oddly, not sure why, check against the database
        $record = QueryUtils::fetchRecords("SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name=?", [self::LOCALE_TIMEZONE]);
        if (!empty($record)) {
            if (empty($record[0]['gl_value'])) {
                return false;
                // default can get translated so we need to go with that
            } else if ($record[0]['gl_value'] == xl(self::LOCALE_TIMEZONE_DEFAULT)) {
                return false;
            }
        }
        return true;
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
        return $key == self::COMLINK_AUTO_PROVISION_PROVIDER
            || $key == self::VERIFY_SETTINGS_BUTTON
            || $key == self::COMLINK_ENABLE_THIRDPARTY_INVITATIONS
            || $key == self::COMLINK_MINIMIZED_SESSION_POSITION_DEFAULT
            || $key == self::DEBUG_MODE_FLAG
            || $key == self::COMLINK_SECTION_FOOTER_BOX
            || $key == self::COMLINK_ONETIME_PASSWORD_LOGIN
            || $key == self::COMLINK_ONETIME_PASSWORD_LOGIN_TIME_LIMIT
            || $key == self::COMLINK_TELEHEALTH_PAYMENT_SUBSCRIPTION_ID; // we don't require the payment subscription id
    }

    /**
     * Returns the One Time Password Timeout Setting in PHP DatePeriod format IE PT{minutes}M
     * If the setting exceeds
     * @return string
     */
    public function getOneTimePasswordTimeoutSetting()
    {
        $setting = intval($this->getGlobalSetting(self::COMLINK_ONETIME_PASSWORD_LOGIN_TIME_LIMIT));
        if ($setting > self::MAX_LOGIN_LIMIT_TIME) {
            $setting = self::MAX_LOGIN_LIMIT_TIME;
        } else if ($setting <= 0) { // set it to the default setting
            $setting = 15;
        }
        return "PT{$setting}M";
    }
}
