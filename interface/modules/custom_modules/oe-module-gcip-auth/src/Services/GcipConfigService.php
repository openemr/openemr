<?php

/**
 * GCIP Configuration Service
 * 
 * <!-- AI-Generated Content Start -->
 * This service handles the management of GCIP authentication configuration
 * settings, including secure storage and retrieval of Google Cloud Identity
 * Platform credentials, OAuth settings, and module preferences.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth\Services
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth\Services;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\GlobalsService;

/**
 * Service for managing GCIP authentication configuration
 */
class GcipConfigService
{
    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    /**
     * @var GlobalsService
     */
    private $globalsService;

    /**
     * Configuration keys for GCIP settings - AI-Generated
     */
    private const CONFIG_KEYS = [
        'gcip_enabled' => 'GCIP_Auth_Enabled',
        'gcip_project_id' => 'GCIP_Project_ID',
        'gcip_client_id' => 'GCIP_Client_ID',
        'gcip_client_secret' => 'GCIP_Client_Secret',
        'gcip_tenant_id' => 'GCIP_Tenant_ID',
        'gcip_redirect_uri' => 'GCIP_Redirect_URI',
        'gcip_domain_restriction' => 'GCIP_Domain_Restriction',
        'gcip_auto_user_creation' => 'GCIP_Auto_User_Creation',
        'gcip_default_role' => 'GCIP_Default_Role',
        'gcip_audit_logging' => 'GCIP_Audit_Logging'
    ];

    /**
     * GcipConfigService constructor
     * 
     * <!-- AI-Generated Content Start -->
     * Initializes the configuration service with encryption and globals
     * services for secure handling of GCIP authentication settings.
     * <!-- AI-Generated Content End -->
     */
    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
        $this->globalsService = new GlobalsService();
    }

    /**
     * Check if GCIP authentication is enabled
     * 
     * <!-- AI-Generated Content Start -->
     * Returns whether GCIP authentication is currently enabled for the
     * OpenEMR installation based on the configuration settings.
     * <!-- AI-Generated Content End -->
     *
     * @return bool
     */
    public function isGcipEnabled(): bool
    {
        return $this->getConfigValue('gcip_enabled', false);
    }

    /**
     * Get GCIP project ID
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves the Google Cloud project ID configured for GCIP authentication.
     * This is required for proper OAuth2 flow configuration.
     * <!-- AI-Generated Content End -->
     *
     * @return string|null
     */
    public function getProjectId(): ?string
    {
        return $this->getConfigValue('gcip_project_id');
    }

    /**
     * Get GCIP client ID
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves the OAuth2 client ID for GCIP authentication. This is a
     * public identifier used in the OAuth2 authorization flow.
     * <!-- AI-Generated Content End -->
     *
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->getConfigValue('gcip_client_id');
    }

    /**
     * Get GCIP client secret (encrypted)
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves and decrypts the OAuth2 client secret for GCIP authentication.
     * The secret is stored encrypted in the database for security.
     * <!-- AI-Generated Content End -->
     *
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        $encryptedSecret = $this->getConfigValue('gcip_client_secret');
        if (!$encryptedSecret) {
            return null;
        }

        // Decrypt the client secret - AI-Generated
        return $this->cryptoGen->decryptStandard($encryptedSecret);
    }

    /**
     * Get GCIP tenant ID
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves the tenant ID for multi-tenant GCIP configurations.
     * This is optional and used for organizations with multiple tenants.
     * <!-- AI-Generated Content End -->
     *
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        return $this->getConfigValue('gcip_tenant_id');
    }

    /**
     * Get GCIP redirect URI
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves the configured redirect URI for OAuth2 callbacks.
     * This must match the URI configured in the Google Cloud Console.
     * <!-- AI-Generated Content End -->
     *
     * @return string|null
     */
    public function getRedirectUri(): ?string
    {
        return $this->getConfigValue('gcip_redirect_uri');
    }

    /**
     * Check if audit logging is enabled
     * 
     * <!-- AI-Generated Content Start -->
     * Returns whether audit logging is enabled for GCIP authentication
     * events and activities for compliance and security monitoring.
     * <!-- AI-Generated Content End -->
     *
     * @return bool
     */
    public function isAuditLoggingEnabled(): bool
    {
        return $this->getConfigValue('gcip_audit_logging', true);
    }

    /**
     * Set GCIP configuration value
     * 
     * <!-- AI-Generated Content Start -->
     * Stores a configuration value for GCIP authentication. Sensitive values
     * like client secrets are automatically encrypted before storage.
     * <!-- AI-Generated Content End -->
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @param bool $encrypt Whether to encrypt the value
     * @return bool Success status
     */
    public function setConfigValue(string $key, $value, bool $encrypt = false): bool
    {
        if (!isset(self::CONFIG_KEYS[$key])) {
            return false;
        }

        $globalKey = self::CONFIG_KEYS[$key];
        
        // Encrypt sensitive values - AI-Generated
        if ($encrypt && !empty($value)) {
            $value = $this->cryptoGen->encryptStandard($value);
        }

        return $this->globalsService->setGlobalSetting($globalKey, $value);
    }

    /**
     * Get GCIP configuration value
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves a configuration value for GCIP authentication from the
     * global settings storage with optional default value support.
     * <!-- AI-Generated Content End -->
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if not found
     * @return mixed Configuration value or default
     */
    private function getConfigValue(string $key, $default = null)
    {
        if (!isset(self::CONFIG_KEYS[$key])) {
            return $default;
        }

        $globalKey = self::CONFIG_KEYS[$key];
        $value = $this->globalsService->getGlobalSetting($globalKey);
        
        return $value !== null ? $value : $default;
    }

    /**
     * Validate GCIP configuration
     * 
     * <!-- AI-Generated Content Start -->
     * Validates the current GCIP configuration to ensure all required
     * settings are present and properly formatted for authentication.
     * <!-- AI-Generated Content End -->
     *
     * @return array Validation results with errors if any
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        // Check required settings - AI-Generated
        if (!$this->getProjectId()) {
            $errors[] = 'GCIP Project ID is required';
        }

        if (!$this->getClientId()) {
            $errors[] = 'GCIP Client ID is required';
        }

        if (!$this->getClientSecret()) {
            $errors[] = 'GCIP Client Secret is required';
        }

        if (!$this->getRedirectUri()) {
            $errors[] = 'GCIP Redirect URI is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get all GCIP configuration for display
     * 
     * <!-- AI-Generated Content Start -->
     * Retrieves all GCIP configuration values in a format suitable for
     * display in the admin interface, excluding sensitive values.
     * <!-- AI-Generated Content End -->
     *
     * @return array Configuration array
     */
    public function getAllConfig(): array
    {
        return [
            'enabled' => $this->isGcipEnabled(),
            'project_id' => $this->getProjectId(),
            'client_id' => $this->getClientId(),
            'client_secret_set' => !empty($this->getConfigValue('gcip_client_secret')),
            'tenant_id' => $this->getTenantId(),
            'redirect_uri' => $this->getRedirectUri(),
            'audit_logging' => $this->isAuditLoggingEnabled(),
            'domain_restriction' => $this->getConfigValue('gcip_domain_restriction'),
            'auto_user_creation' => $this->getConfigValue('gcip_auto_user_creation', false),
            'default_role' => $this->getConfigValue('gcip_default_role', 'Clinician')
        ];
    }
}