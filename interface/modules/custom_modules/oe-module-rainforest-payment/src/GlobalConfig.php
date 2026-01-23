<?php

/**
 * Global configuration for Rainforest Payment Module
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Firehed
 * @copyright Copyright (c) 2026 Firehed
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\RainforestPayment;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig
{
    public const CONFIG_API_KEY = 'rainforest_api_key';
    public const CONFIG_MERCHANT_ID = 'rainforest_merchant_id';
    public const CONFIG_PLATFORM_ID = 'rainforest_platform_id';
    public const CONFIG_ENABLED = 'rainforest_payment_enabled';

    private CryptoGen $cryptoGen;
    private OEGlobalsBag $globalsBag;

    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
        $this->globalsBag = OEGlobalsBag::getInstance();
    }

    /**
     * Check if the module is enabled
     */
    public function isEnabled(): bool
    {
        return $this->globalsBag->get(self::CONFIG_ENABLED, '0') === '1';
    }

    /**
     * Check if the module is properly configured
     */
    public function isConfigured(): bool
    {
        $apiKey = $this->getApiKey();
        $merchantId = $this->getMerchantId();
        $platformId = $this->getPlatformId();

        return !empty($apiKey) && !empty($merchantId) && !empty($platformId);
    }

    /**
     * Get the API key (decrypted)
     */
    public function getApiKey(): string
    {
        $encrypted = $this->globalsBag->get(self::CONFIG_API_KEY, '');
        if (empty($encrypted)) {
            return '';
        }
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);
        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Get the merchant ID (decrypted)
     */
    public function getMerchantId(): string
    {
        $encrypted = $this->globalsBag->get(self::CONFIG_MERCHANT_ID, '');
        if (empty($encrypted)) {
            return '';
        }
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);
        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Get the platform ID (decrypted)
     */
    public function getPlatformId(): string
    {
        $encrypted = $this->globalsBag->get(self::CONFIG_PLATFORM_ID, '');
        if (empty($encrypted)) {
            return '';
        }
        $decrypted = $this->cryptoGen->decryptStandard($encrypted);
        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Get global setting value
     */
    public function getGlobalSetting(string $key): mixed
    {
        return $this->globalsBag->get($key);
    }

    /**
     * Get the global settings section configuration for the admin UI
     *
     * @return array<string, array<string, string|bool|int>>
     */
    public function getGlobalSettingSectionConfiguration(): array
    {
        return [
            self::CONFIG_ENABLED => [
                'title' => 'Enable Rainforest Payment Gateway',
                'description' => 'Enable the Rainforest payment gateway for patient portal payments',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '0'
            ],
            self::CONFIG_API_KEY => [
                'title' => 'Rainforest API Key',
                'description' => 'Your Rainforest API key (stored encrypted)',
                'type' => GlobalSetting::DATA_TYPE_ENCRYPTED,
                'default' => ''
            ],
            self::CONFIG_MERCHANT_ID => [
                'title' => 'Rainforest Merchant ID',
                'description' => 'Your Rainforest merchant ID (stored encrypted)',
                'type' => GlobalSetting::DATA_TYPE_ENCRYPTED,
                'default' => ''
            ],
            self::CONFIG_PLATFORM_ID => [
                'title' => 'Rainforest Platform ID',
                'description' => 'Your Rainforest platform ID (stored encrypted)',
                'type' => GlobalSetting::DATA_TYPE_ENCRYPTED,
                'default' => ''
            ],
        ];
    }
}
