<?php

/**
 * Bootstrap custom module skeleton.  This file is an example custom module that can be used
 * to create modules that can be utilized inside the OpenEMR system.  It is NOT intended for
 * production and is intended to serve as the barebone requirements you need to get started
 * writing modules that can be installed and used in OpenEMR.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

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
    public const CONFIG_OPTION_DEV_API_URL = 'oe_claimrev_config_dev_api_url';
    public const CONFIG_OPTION_DEV_SCOPE = 'oe_claimrev_config_dev_scope';
    public const CONFIG_OPTION_DEV_AUTHORITY = 'oe_claimrev_config_dev_authority';

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

    public function getClientId()
    {
        return $this->getGlobalSetting(self::CONFIG_OPTION_CLIENTID);
    }
    public function getClientSecret()
    {
        $encryptedValue = $this->getGlobalSetting(self::CONFIG_OPTION_CLIENTSECRET);
        return $this->cryptoGen->decryptStandard($encryptedValue);
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
    ];

    private const DEV_URL_CONFIG_KEYS = [
        'scope' => self::CONFIG_OPTION_DEV_SCOPE,
        'authority' => self::CONFIG_OPTION_DEV_AUTHORITY,
        'api_server' => self::CONFIG_OPTION_DEV_API_URL,
    ];

    /**
     * @param 'scope'|'authority'|'api_server' $urlType
     * @return non-empty-string
     * @throws ModuleNotConfiguredException if URL is not configured for the current environment
     */
    private function getEnvironmentUrl(string $urlType): string
    {
        $env = $this->getGlobalSetting(self::CONFIG_OPTION_ENVIRONMENT);
        $env = is_string($env) ? $env : 'P';

        $url = ($env === 'D')
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



    public function getAutoSendFiles()
    {
        return $this->getGlobalSetting(self::CONFIG_AUTO_SEND_CLAIM_FILES);
    }

    public function getGlobalSetting($settingKey)
    {
        return $this->globalsArray[$settingKey] ?? null;
    }

    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::CONFIG_OPTION_ENVIRONMENT => [
                'title' => 'ClaimRev Environment (P=Production)',
                'description' => 'The system you connect to. P for production',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => 'P',
            ],
            self::CONFIG_OPTION_DEV_API_URL => [
                'title' => 'Development API Server URL',
                'description' => 'API server URL when environment is set to D (Development)',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_DEV_SCOPE => [
                'title' => 'Development Scope URL',
                'description' => 'OAuth scope URL when environment is set to D (Development)',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_DEV_AUTHORITY => [
                'title' => 'Development Authority URL',
                'description' => 'OAuth authority URL when environment is set to D (Development)',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_CLIENTID => [
                'title' => 'Client ID',
                'description' => 'Contact ClaimRev for the client ID',
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
                'default' => '30',
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
        ];
        return $settings;
    }
}
