<?php

/**
 * GCIP Configuration Service Unit Tests
 * 
 * <!-- AI-Generated Content Start -->
 * This test file contains unit tests for the GcipConfigService class,
 * testing configuration storage, retrieval, validation, and encryption
 * functionality for GCIP authentication settings.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth\Tests
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth\Tests;

use PHPUnit\Framework\TestCase;
use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;

/**
 * Unit tests for GcipConfigService
 */
class GcipConfigServiceTest extends TestCase
{
    /**
     * @var GcipConfigService
     */
    private $configService;

    /**
     * Set up test environment - AI-Generated
     */
    protected function setUp(): void
    {
        $this->configService = new GcipConfigService();
    }

    /**
     * Test GCIP enabled/disabled state - AI-Generated
     */
    public function testIsGcipEnabled(): void
    {
        // Test default state (disabled) - AI-Generated
        $this->assertFalse($this->configService->isGcipEnabled());
        
        // Test enabling GCIP - AI-Generated
        $this->configService->setConfigValue('gcip_enabled', true);
        $this->assertTrue($this->configService->isGcipEnabled());
        
        // Test disabling GCIP - AI-Generated
        $this->configService->setConfigValue('gcip_enabled', false);
        $this->assertFalse($this->configService->isGcipEnabled());
    }

    /**
     * Test configuration value storage and retrieval - AI-Generated
     */
    public function testConfigValueStorageAndRetrieval(): void
    {
        $testProjectId = 'test-project-123';
        $testClientId = 'test-client-id';
        
        // Test setting and getting project ID - AI-Generated
        $result = $this->configService->setConfigValue('gcip_project_id', $testProjectId);
        $this->assertTrue($result);
        $this->assertEquals($testProjectId, $this->configService->getProjectId());
        
        // Test setting and getting client ID - AI-Generated
        $result = $this->configService->setConfigValue('gcip_client_id', $testClientId);
        $this->assertTrue($result);
        $this->assertEquals($testClientId, $this->configService->getClientId());
    }

    /**
     * Test client secret encryption - AI-Generated
     */
    public function testClientSecretEncryption(): void
    {
        $testSecret = 'test-client-secret-123';
        
        // Set encrypted client secret - AI-Generated
        $result = $this->configService->setConfigValue('gcip_client_secret', $testSecret, true);
        $this->assertTrue($result);
        
        // Verify secret can be retrieved and decrypted - AI-Generated
        $retrievedSecret = $this->configService->getClientSecret();
        $this->assertEquals($testSecret, $retrievedSecret);
    }

    /**
     * Test configuration validation - AI-Generated
     */
    public function testConfigurationValidation(): void
    {
        // Test invalid configuration (missing required fields) - AI-Generated
        $validation = $this->configService->validateConfiguration();
        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
        
        // Set required configuration values - AI-Generated
        $this->configService->setConfigValue('gcip_project_id', 'test-project');
        $this->configService->setConfigValue('gcip_client_id', 'test-client-id');
        $this->configService->setConfigValue('gcip_client_secret', 'test-secret', true);
        $this->configService->setConfigValue('gcip_redirect_uri', 'https://example.com/callback');
        
        // Test valid configuration - AI-Generated
        $validation = $this->configService->validateConfiguration();
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    /**
     * Test audit logging configuration - AI-Generated
     */
    public function testAuditLoggingConfiguration(): void
    {
        // Test default audit logging state (enabled) - AI-Generated
        $this->assertTrue($this->configService->isAuditLoggingEnabled());
        
        // Test disabling audit logging - AI-Generated
        $this->configService->setConfigValue('gcip_audit_logging', false);
        $this->assertFalse($this->configService->isAuditLoggingEnabled());
        
        // Test enabling audit logging - AI-Generated
        $this->configService->setConfigValue('gcip_audit_logging', true);
        $this->assertTrue($this->configService->isAuditLoggingEnabled());
    }

    /**
     * Test getting all configuration - AI-Generated
     */
    public function testGetAllConfig(): void
    {
        // Set some test configuration values - AI-Generated
        $this->configService->setConfigValue('gcip_enabled', true);
        $this->configService->setConfigValue('gcip_project_id', 'test-project');
        $this->configService->setConfigValue('gcip_client_id', 'test-client');
        
        $config = $this->configService->getAllConfig();
        
        // Verify configuration structure - AI-Generated
        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('project_id', $config);
        $this->assertArrayHasKey('client_id', $config);
        $this->assertArrayHasKey('client_secret_set', $config);
        $this->assertArrayHasKey('audit_logging', $config);
        
        // Verify values - AI-Generated
        $this->assertTrue($config['enabled']);
        $this->assertEquals('test-project', $config['project_id']);
        $this->assertEquals('test-client', $config['client_id']);
    }

    /**
     * Test invalid configuration key handling - AI-Generated
     */
    public function testInvalidConfigurationKey(): void
    {
        // Test setting invalid configuration key - AI-Generated
        $result = $this->configService->setConfigValue('invalid_key', 'test-value');
        $this->assertFalse($result);
    }

    /**
     * Test tenant ID configuration - AI-Generated
     */
    public function testTenantIdConfiguration(): void
    {
        $testTenantId = 'test-tenant-123';
        
        // Test setting and getting tenant ID - AI-Generated
        $this->configService->setConfigValue('gcip_tenant_id', $testTenantId);
        $this->assertEquals($testTenantId, $this->configService->getTenantId());
        
        // Test empty tenant ID - AI-Generated
        $this->configService->setConfigValue('gcip_tenant_id', '');
        $this->assertEmpty($this->configService->getTenantId());
    }

    /**
     * Test redirect URI configuration - AI-Generated
     */
    public function testRedirectUriConfiguration(): void
    {
        $testUri = 'https://example.com/oauth/callback';
        
        // Test setting and getting redirect URI - AI-Generated
        $this->configService->setConfigValue('gcip_redirect_uri', $testUri);
        $this->assertEquals($testUri, $this->configService->getRedirectUri());
    }

    /**
     * Clean up test environment - AI-Generated
     */
    protected function tearDown(): void
    {
        // Clean up test configuration values - AI-Generated
        $testKeys = [
            'gcip_enabled',
            'gcip_project_id', 
            'gcip_client_id',
            'gcip_client_secret',
            'gcip_tenant_id',
            'gcip_redirect_uri',
            'gcip_audit_logging'
        ];
        
        foreach ($testKeys as $key) {
            $this->configService->setConfigValue($key, '');
        }
    }
}