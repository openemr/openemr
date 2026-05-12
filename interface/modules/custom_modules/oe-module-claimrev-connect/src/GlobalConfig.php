<?php

/**
 * Bootstrap custom module skeleton.  This file is an example custom module that can be used
 * to create modules that can be utilized inside the OpenEMR system.  It is NOT intended for
 * production and is intended to serve as the barebone requirements you need to get started
 * writing modules that can be installed and used in OpenEMR.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig
{
    public const CONFIG_OPTION_ENVIRONMENT = 'oe_claimrev_config_environment';
    public const CONFIG_OPTION_CLIENTID = 'oe_claimrev_config_clientid';
    public const CONFIG_OPTION_CLIENTSECRET = 'oe_claimrev_config_clientsecret';
    public const CONFIG_OPTION_SCOPE = 'oe_claimrev_config_scope';
    public const CONFIG_OPTION_AUTHORITY = 'oe_claimrev_config_authority';

    public const CONFIG_AUTO_SEND_CLAIM_FILES = 'oe_claimrev_config_auto_send_claim_files';
    public const CONFIG_ENABLE_MENU = "oe_claimrev_config_add_menu_button";
    public const CONFIG_SERVICE_TYPE_CODES = "oe_claimrev_config_service_type_codes";
    public const CONFIG_ENABLE_ELIGIBILITY_CARD = "oe_claimrev_config_add_eligibility_card";
    public const CONFIG_USE_FACILITY_FOR_ELIGIBILITY = "oe_claimrev_config_use_facility_for_eligibility";
    public const CONFIG_ENABLE_REALTIME_ELIGIBILITY = "oe_claimrev_enable_rte";
    public const CONFIG_ENABLE_RESULTS_ELIGIBILITY = "oe_claimrev_eligibility_results_age";
    public const CONFIG_ENABLE_AUTO_SEND_ELIGIBILITY = "oe_claimrev_send_eligibility";
    public const CONFIG_X12_PARTNER_NAME = "oe_claimrev_x12_partner_name";
    public const CONFIG_ENABLE_WATCHDOG = "oe_claimrev_enable_watchdog";
    public const CONFIG_ENABLE_NOTIFICATIONS = "oe_claimrev_enable_notifications";
    public const CONFIG_NOTIFICATION_RECIPIENT = "oe_claimrev_notification_recipient";
    public const CONFIG_BENEFIT_CODE_FILTER = "oe_claimrev_benefit_code_filter";
    public const CONFIG_OPTION_PORTAL_URL = "oe_claimrev_config_portal_url";
    public const CONFIG_OPTION_DEV_API_URL = 'oe_claimrev_config_dev_api_url';
    public const CONFIG_OPTION_DEV_SCOPE = 'oe_claimrev_config_dev_scope';
    public const CONFIG_OPTION_DEV_AUTHORITY = 'oe_claimrev_config_dev_authority';
    public const CONFIG_ENABLE_TEST_MODE = 'oe_claimrev_enable_test_mode';
    public const CONFIG_ENABLE_SWEEP = 'oe_claimrev_enable_sweep';
    public const CONFIG_SWEEP_DAYS = 'oe_claimrev_sweep_days';
    public const CONFIG_SWEEP_LOOKAHEAD = 'oe_claimrev_sweep_lookahead';
    public const CONFIG_ENABLE_CALENDAR_INDICATORS = 'oe_claimrev_enable_calendar_indicators';

    private readonly CryptoGen $cryptoGen;

    /**
     * @param array<string, mixed> $globalsArray
     */
    public function __construct(private array $globalsArray)
    {
        $crypto = ServiceContainer::getCrypto();
        if (!$crypto instanceof CryptoGen) {
            throw new \RuntimeException('ServiceContainer::getCrypto() did not return a CryptoGen instance');
        }
        $this->cryptoGen = $crypto;
    }

    /**
     * Returns true if all of the settings have been configured.  Otherwise it returns false.
     */
    public function isConfigured(): bool
    {
        $requiredKeys = [
            self::CONFIG_OPTION_ENVIRONMENT,
            self::CONFIG_OPTION_CLIENTID,
            self::CONFIG_OPTION_CLIENTSECRET,
        ];
        foreach ($requiredKeys as $key) {
            $value = $this->getGlobalSetting($key);
            if ($value === null || $value === '') {
                return false;
            }
        }
        return true;
    }

    public function getClientId(): mixed
    {
        return $this->getGlobalSetting(self::CONFIG_OPTION_CLIENTID);
    }

    public function getClientSecret(): string|false
    {
        $encryptedValue = $this->getGlobalSetting(self::CONFIG_OPTION_CLIENTSECRET);
        return $this->cryptoGen->decryptFromDatabase(is_string($encryptedValue) ? $encryptedValue : null);
    }

    private const URL_CONFIGS = [
        'scope' => [
            'P' => 'https://portalclaimrev.onmicrosoft.com/portal/api/.default',
            'S' => 'https://stagingclaimrevcom.onmicrosoft.com/portal/api/.default',
        ],
        'authority' => [
            'P' => 'https://portalclaimrev.b2clogin.com/portalclaimrev.onmicrosoft.com/B2C_1_sign-in-service/oauth2/v2.0/token',
            'S' => 'https://stagingclaimrevcom.b2clogin.com/stagingclaimrevcom.onmicrosoft.com/B2C_1_sign-in-service/oauth2/v2.0/token',
        ],
        'api_server' => [
            'P' => 'https://api.claimrev.com',
            'S' => 'https://testapi.claimrev.com',
        ],
        'portal' => [
            'P' => 'https://portal.claimrev.com',
            'S' => 'https://testportal.claimrev.com',
        ],
    ];

    private const DEV_URL_CONFIG_KEYS = [
        'scope' => self::CONFIG_OPTION_DEV_SCOPE,
        'authority' => self::CONFIG_OPTION_DEV_AUTHORITY,
        'api_server' => self::CONFIG_OPTION_DEV_API_URL,
    ];

    /**
     * @param 'scope'|'authority'|'api_server'|'portal' $urlType
     * @return non-empty-string
     * @throws ModuleNotConfiguredException if URL is not configured for the current environment
     */
    private function getEnvironmentUrl(string $urlType): string
    {
        $env = $this->getGlobalSetting(self::CONFIG_OPTION_ENVIRONMENT);
        $env = is_string($env) ? $env : 'P';

        $url = ($env === 'D' && isset(self::DEV_URL_CONFIG_KEYS[$urlType]))
            ? $this->getGlobalSetting(self::DEV_URL_CONFIG_KEYS[$urlType])
            : (self::URL_CONFIGS[$urlType][$env] ?? null);

        if (!is_string($url) || $url === '') {
            throw new ModuleNotConfiguredException("ClaimRev {$urlType} URL not configured for environment '{$env}'");
        }

        return $url;
    }

    /** @return non-empty-string */
    public function getClientScope(): string
    {
        return $this->getEnvironmentUrl('scope');
    }

    /** @return non-empty-string */
    public function getClientAuthority(): string
    {
        return $this->getEnvironmentUrl('authority');
    }

    /** @return non-empty-string */
    public function getApiServer(): string
    {
        return $this->getEnvironmentUrl('api_server');
    }

    /**
     * Get the ClaimRev portal URL for claim editor links.
     */
    public function getPortalUrl(): string
    {
        $override = $this->getGlobalSetting(self::CONFIG_OPTION_PORTAL_URL);
        if (is_string($override) && $override !== '') {
            return rtrim($override, '/');
        }

        try {
            return $this->getEnvironmentUrl('portal');
        } catch (ModuleNotConfiguredException) {
            return 'https://portal.claimrev.com';
        }
    }



    public function isTestModeEnabled(): bool
    {
        $value = $this->getGlobalSetting(self::CONFIG_ENABLE_TEST_MODE);
        return $value !== null && $value !== '' && $value !== '0' && $value !== false;
    }

    public function getAutoSendFiles(): mixed
    {
        return $this->getGlobalSetting(self::CONFIG_AUTO_SEND_CLAIM_FILES);
    }

    public function getGlobalSetting(string $settingKey): mixed
    {
        return $this->globalsArray[$settingKey] ?? null;
    }

    /**
     * @return array<string, array{title: string, description: string, type: string, default: string}>
     */
    public function getGlobalSettingSectionConfiguration(): array
    {
        $settings = [
            self::CONFIG_OPTION_ENVIRONMENT => [
                'title' => 'ClaimRev Environment',
                'description' => 'Leave as P for production. Only change to D if directed by ClaimRev support.',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => 'P',
            ],
            self::CONFIG_OPTION_CLIENTID => [
                'title' => 'Client ID',
                'description' => 'Available in the ClaimRev Portal under Client Connect',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_CLIENTSECRET => [
                'title' => 'ClaimRev Client Secret',
                'description' => 'Contact ClaimRev for this value',
                'type' => GlobalSetting::DATA_TYPE_ENCRYPTED,
                'default' => '',
            ],
            self::CONFIG_X12_PARTNER_NAME => [
                'title' => 'X12 Partner Name',
                'description' => 'Name of the X12 Partner Record',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => 'ClaimRev',
            ],
            self::CONFIG_SERVICE_TYPE_CODES => [
                'title' => 'Eligibility Service Type Codes',
                'description' => 'Comma Separated List of Service Type Codes',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_BENEFIT_CODE_FILTER => [
                'title' => 'Benefit Code Filter',
                'description' => 'Comma separated benefit information codes to display (e.g. 1,6,A,B,C). Leave blank to show all. Filtered locally only - does not affect what is sent to ClaimRev.',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_AUTO_SEND_CLAIM_FILES => [
                'title' => 'Auto Send Claim Files',
                'description' => 'Send Claim Files to ClaimRev automatically',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_ENABLE_MENU => [
                'title' => 'Add module menu item',
                'description' => 'Adding a menu item to the system (requires logging out and logging in again)',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_ENABLE_ELIGIBILITY_CARD => [
                'title' => 'Add ClaimRev Eligibility Card To Patient Dashboard',
                'description' => 'Adds the ClaimRev Eligibility Card To Patient Dashboard',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_USE_FACILITY_FOR_ELIGIBILITY => [
                'title' => 'Use Facility for Eligibility',
                'description' => 'Information requester will be facility rather than provider',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_ENABLE_REALTIME_ELIGIBILITY => [
                'title' => 'Turn on Real-Time Eligibility',
                'description' => 'Enables eligibility checks on patients eligibility when an appointment is created',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_ENABLE_RESULTS_ELIGIBILITY => [
                'title' => 'Eligibility Age To Stale',
                'description' => 'THis is the number of days to consider eligibility stale',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_ENABLE_AUTO_SEND_ELIGIBILITY => [
                'title' => 'Turn on Eligibility Send Service',
                'description' => 'Enables the sending of eligibility json to ClaimRev',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_ENABLE_NOTIFICATIONS => [
                'title' => 'Enable ClaimRev Notifications',
                'description' => 'Poll ClaimRev for account notifications and deliver them to OpenEMR user messages',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '1',
            ],
            self::CONFIG_NOTIFICATION_RECIPIENT => [
                'title' => 'Notification Recipient Username(s)',
                'description' => 'OpenEMR username(s) to receive ClaimRev notifications. Separate multiple with semicolons (e.g. admin;biller1;biller2).',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => 'admin',
            ],
            self::CONFIG_ENABLE_WATCHDOG => [
                'title' => 'Enable Service Watchdog',
                'description' => 'Automatically resets ClaimRev background services that get stuck. Recommended to leave enabled.',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '1',
            ],
            self::CONFIG_ENABLE_TEST_MODE => [
                'title' => 'Enable Test Mode',
                'description' => 'Shows a Test Mode option on the Payment Advice screen that generates simulated ERA data from OpenEMR billing records. For demonstration and training only.',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            // --- Eligibility Sweep settings ---
            self::CONFIG_ENABLE_SWEEP => [
                'title' => 'Enable Eligibility Sweep',
                'description' => 'Automatically queue eligibility checks for upcoming appointments on scheduled days',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            self::CONFIG_SWEEP_DAYS => [
                'title' => 'Sweep Days',
                'description' => 'Comma-separated day numbers (0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat). E.g. 1,4 for Monday and Thursday.',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '1,4',
            ],
            self::CONFIG_SWEEP_LOOKAHEAD => [
                'title' => 'Sweep Lookahead Days',
                'description' => 'Number of days ahead to check appointments for eligibility',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '7',
            ],
            self::CONFIG_ENABLE_CALENDAR_INDICATORS => [
                'title' => 'Enable Calendar Eligibility Indicators',
                'description' => 'Show color indicators on the main OpenEMR calendar based on eligibility status. May impact calendar performance.',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '',
            ],
            // --- Override settings (auto-configured for production, configurable for alternate identity providers) ---
            self::CONFIG_OPTION_PORTAL_URL => [
                'title' => 'Portal URL Override',
                'description' => 'Auto-configured for production. Override only if using a custom portal URL.',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_DEV_API_URL => [
                'title' => 'API Server URL Override',
                'description' => 'Auto-configured for production. Override to use a different API server or identity provider.',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_DEV_SCOPE => [
                'title' => 'OAuth Scope Override',
                'description' => 'Auto-configured for production. Override when switching identity providers (e.g. Entra ID External, Zitadel).',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_DEV_AUTHORITY => [
                'title' => 'OAuth Authority URL Override',
                'description' => 'Auto-configured for production. Override when switching identity providers (e.g. Entra ID External, Zitadel).',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
        ];
        return $settings;
    }
}
