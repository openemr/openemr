<?php

/**
 * Provider Registry - Manages SSO provider instances
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Services;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\SSO\Providers\EntraProvider;
use OpenEMR\Modules\SSO\Providers\GenericOidcProvider;
use OpenEMR\Modules\SSO\Providers\GoogleProvider;
use OpenEMR\Modules\SSO\Providers\ProviderInterface;

class ProviderRegistry
{
    private SystemLogger $logger;
    private CryptoGen $crypto;
    private array $providers = [];
    private array $providerClasses = [
        'entra' => EntraProvider::class,
        'google' => GoogleProvider::class,
        'generic_oidc' => GenericOidcProvider::class,
    ];

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->crypto = new CryptoGen();
    }

    /**
     * Get a provider by ID
     */
    public function getProvider(string $providerId): ?ProviderInterface
    {
        if (isset($this->providers[$providerId])) {
            return $this->providers[$providerId];
        }

        if (!isset($this->providerClasses[$providerId])) {
            return null;
        }

        // Load config from database
        $config = $this->loadProviderConfig($providerId);
        if ($config === null) {
            return null;
        }

        // Decrypt client secret if present
        if (!empty($config['client_secret'])) {
            $config['client_secret'] = $this->decryptSecret($config['client_secret']);
        }

        $class = $this->providerClasses[$providerId];
        $provider = new $class($config);
        $this->providers[$providerId] = $provider;

        return $provider;
    }

    /**
     * Get all enabled providers
     *
     * @return ProviderInterface[]
     */
    public function getEnabledProviders(): array
    {
        $enabled = [];

        $results = sqlStatement(
            "SELECT provider_type, config, enabled FROM sso_providers WHERE enabled = 1"
        );

        while ($row = sqlFetchArray($results)) {
            $providerId = $row['provider_type'];
            if (!isset($this->providerClasses[$providerId])) {
                continue;
            }

            $config = json_decode($row['config'], true) ?? [];
            $config['enabled'] = (bool)$row['enabled'];

            // Decrypt client secret
            if (!empty($config['client_secret'])) {
                $config['client_secret'] = $this->decryptSecret($config['client_secret']);
            }

            $class = $this->providerClasses[$providerId];
            $provider = new $class($config);

            if ($provider->isEnabled()) {
                $enabled[] = $provider;
                $this->providers[$providerId] = $provider;
            }
        }

        return $enabled;
    }

    /**
     * Get all registered provider types
     */
    public function getAvailableProviders(): array
    {
        $available = [];
        foreach ($this->providerClasses as $id => $class) {
            $provider = new $class([]);
            $available[$id] = [
                'id' => $id,
                'name' => $provider->getName(),
                'icon' => $provider->getIcon(),
                'configFields' => $provider->getConfigFields(),
            ];
        }
        return $available;
    }

    /**
     * Save provider configuration
     */
    public function saveProviderConfig(string $providerId, array $config): bool
    {
        if (!isset($this->providerClasses[$providerId])) {
            return false;
        }

        // Encrypt client secret before saving
        if (!empty($config['client_secret'])) {
            $config['client_secret'] = $this->encryptSecret($config['client_secret']);
        }

        $enabled = !empty($config['enabled']) ? 1 : 0;
        $autoProvision = !empty($config['auto_provision']) ? 1 : 0;
        $defaultAcl = $config['default_acl'] ?? null;

        // Get display name from provider
        $class = $this->providerClasses[$providerId];
        $tempProvider = new $class($config);
        $name = $tempProvider->getName();

        $existing = sqlQuery(
            "SELECT id FROM sso_providers WHERE provider_type = ?",
            [$providerId]
        );

        if ($existing) {
            sqlStatement(
                "UPDATE sso_providers
                 SET name = ?, enabled = ?, config = ?, auto_provision = ?, default_acl = ?, updated_at = NOW()
                 WHERE provider_type = ?",
                [$name, $enabled, json_encode($config), $autoProvision, $defaultAcl, $providerId]
            );
        } else {
            sqlStatement(
                "INSERT INTO sso_providers (provider_type, name, enabled, config, auto_provision, default_acl, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$providerId, $name, $enabled, json_encode($config), $autoProvision, $defaultAcl]
            );
        }

        // Clear cached provider
        unset($this->providers[$providerId]);

        return true;
    }

    /**
     * Get provider database ID
     */
    public function getProviderDbId(string $providerId): ?int
    {
        $result = sqlQuery(
            "SELECT id FROM sso_providers WHERE provider_type = ?",
            [$providerId]
        );
        return $result ? (int)$result['id'] : null;
    }

    private function loadProviderConfig(string $providerId): ?array
    {
        $result = sqlQuery(
            "SELECT config, enabled, auto_provision, default_acl FROM sso_providers WHERE provider_type = ?",
            [$providerId]
        );

        if (!$result) {
            return null;
        }

        $config = json_decode($result['config'], true) ?? [];
        $config['enabled'] = (bool)$result['enabled'];
        $config['auto_provision'] = (bool)$result['auto_provision'];
        $config['default_acl'] = $result['default_acl'];

        return $config;
    }

    private function encryptSecret(string $secret): string
    {
        return $this->crypto->encryptStandard($secret);
    }

    private function decryptSecret(string $encrypted): string
    {
        // Try to decrypt - if it fails, assume it's not encrypted (legacy data)
        $decrypted = $this->crypto->decryptStandard($encrypted);
        if ($decrypted !== false) {
            return $decrypted;
        }
        // Return original value if decryption fails (not encrypted)
        $this->logger->debug('SSO: Client secret not encrypted, using raw value');
        return $encrypted;
    }
}
