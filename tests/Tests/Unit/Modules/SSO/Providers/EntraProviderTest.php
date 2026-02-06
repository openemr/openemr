<?php

/**
 * Tests for the SSO EntraProvider
 *
 * @package   OpenEMR\Tests\Unit\Modules\SSO\Providers
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Modules\SSO\Providers;

use OpenEMR\Modules\SSO\Providers\EntraProvider;
use PHPUnit\Framework\TestCase;

final class EntraProviderTest extends TestCase
{
    private EntraProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new EntraProvider([
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'tenant_id' => 'test-tenant-id',
            'enabled' => true,
        ]);
    }

    public function testGetId(): void
    {
        $this->assertEquals('entra', $this->provider->getId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('Microsoft Entra ID', $this->provider->getName());
    }

    public function testGetIcon(): void
    {
        $icon = $this->provider->getIcon();
        $this->assertIsString($icon);
        $this->assertStringContainsString('svg', $icon);
    }

    public function testIsConfiguredWithValidConfig(): void
    {
        $this->assertTrue($this->provider->isConfigured());
    }

    public function testIsConfiguredWithMissingClientId(): void
    {
        $provider = new EntraProvider([
            'client_secret' => 'test',
            'tenant_id' => 'test',
        ]);
        $this->assertFalse($provider->isConfigured());
    }

    public function testIsConfiguredWithMissingClientSecret(): void
    {
        $provider = new EntraProvider([
            'client_id' => 'test',
            'tenant_id' => 'test',
        ]);
        $this->assertFalse($provider->isConfigured());
    }

    public function testIsConfiguredWithMissingTenantId(): void
    {
        $provider = new EntraProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
        ]);
        $this->assertFalse($provider->isConfigured());
    }

    public function testIsEnabled(): void
    {
        $this->assertTrue($this->provider->isEnabled());
    }

    public function testIsEnabledWhenDisabled(): void
    {
        $provider = new EntraProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
            'tenant_id' => 'test',
            'enabled' => false,
        ]);
        $this->assertFalse($provider->isEnabled());
    }

    public function testGetDefaultScopes(): void
    {
        $scopes = $this->provider->getDefaultScopes();
        $this->assertIsArray($scopes);
        $this->assertContains('openid', $scopes);
        $this->assertContains('email', $scopes);
        $this->assertContains('profile', $scopes);
        $this->assertContains('offline_access', $scopes);
    }

    public function testSupportsGroupClaims(): void
    {
        $this->assertTrue($this->provider->supportsGroupClaims());
    }

    public function testGetConfigFields(): void
    {
        $fields = $this->provider->getConfigFields();
        $this->assertIsArray($fields);
        $this->assertArrayHasKey('client_id', $fields);
        $this->assertArrayHasKey('client_secret', $fields);
        $this->assertArrayHasKey('tenant_id', $fields);
        $this->assertArrayHasKey('domain_hint', $fields);
    }

    public function testExtractUserInfo(): void
    {
        $claims = [
            'sub' => 'user-object-id',
            'email' => 'user@example.com',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
            'groups' => ['group1', 'group2'],
            'roles' => ['Admin'],
        ];

        $userInfo = $this->provider->extractUserInfo($claims);

        $this->assertEquals('user-object-id', $userInfo['sub']);
        $this->assertEquals('user@example.com', $userInfo['email']);
        $this->assertEquals('Test User', $userInfo['name']);
        $this->assertEquals('Test', $userInfo['given_name']);
        $this->assertEquals('User', $userInfo['family_name']);
        $this->assertEquals(['group1', 'group2'], $userInfo['groups']);
        $this->assertEquals(['Admin'], $userInfo['roles']);
    }

    public function testExtractUserInfoWithUpn(): void
    {
        $claims = [
            'sub' => 'user-object-id',
            'upn' => 'user@contoso.com',
            'name' => 'Test User',
        ];

        $userInfo = $this->provider->extractUserInfo($claims);

        $this->assertEquals('user@contoso.com', $userInfo['email']);
    }

    public function testExtractUserInfoWithPreferredUsername(): void
    {
        $claims = [
            'sub' => 'user-object-id',
            'preferred_username' => 'user@example.com',
            'name' => 'Test User',
        ];

        $userInfo = $this->provider->extractUserInfo($claims);

        $this->assertEquals('user@example.com', $userInfo['email']);
    }

    public function testSetConfig(): void
    {
        $newConfig = [
            'client_id' => 'new-client-id',
            'client_secret' => 'new-client-secret',
            'tenant_id' => 'new-tenant-id',
            'enabled' => true,
        ];

        $this->provider->setConfig($newConfig);
        $this->assertTrue($this->provider->isConfigured());
    }
}
