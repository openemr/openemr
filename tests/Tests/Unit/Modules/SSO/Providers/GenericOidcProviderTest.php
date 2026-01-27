<?php

/**
 * Tests for the SSO GenericOidcProvider
 *
 * @package   OpenEMR\Tests\Unit\Modules\SSO\Providers
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Modules\SSO\Providers;

use OpenEMR\Modules\SSO\Providers\GenericOidcProvider;
use PHPUnit\Framework\TestCase;

final class GenericOidcProviderTest extends TestCase
{
    private GenericOidcProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new GenericOidcProvider([
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'discovery_url' => 'https://idp.example.com/.well-known/openid-configuration',
            'enabled' => true,
        ]);
    }

    public function testGetId(): void
    {
        $this->assertEquals('generic_oidc', $this->provider->getId());
    }

    public function testGetNameDefault(): void
    {
        $this->assertEquals('SSO', $this->provider->getName());
    }

    public function testGetNameWithDisplayName(): void
    {
        $provider = new GenericOidcProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
            'discovery_url' => 'https://example.com/.well-known/openid-configuration',
            'display_name' => 'Corporate SSO',
        ]);
        $this->assertEquals('Corporate SSO', $provider->getName());
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

    public function testIsConfiguredWithMissingDiscoveryUrl(): void
    {
        $provider = new GenericOidcProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
        ]);
        $this->assertFalse($provider->isConfigured());
    }

    public function testGetDefaultScopes(): void
    {
        $scopes = $this->provider->getDefaultScopes();
        $this->assertIsArray($scopes);
        $this->assertContains('openid', $scopes);
        $this->assertContains('email', $scopes);
        $this->assertContains('profile', $scopes);
    }

    public function testGetDefaultScopesWithCustomScopes(): void
    {
        $provider = new GenericOidcProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
            'discovery_url' => 'https://example.com/.well-known/openid-configuration',
            'scopes' => 'email profile custom_scope',
        ]);

        $scopes = $provider->getDefaultScopes();
        $this->assertContains('openid', $scopes); // Should always include openid
        $this->assertContains('email', $scopes);
        $this->assertContains('profile', $scopes);
        $this->assertContains('custom_scope', $scopes);
    }

    public function testSupportsGroupClaims(): void
    {
        $this->assertFalse($this->provider->supportsGroupClaims());
    }

    public function testSupportsGroupClaimsWhenEnabled(): void
    {
        $provider = new GenericOidcProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
            'discovery_url' => 'https://example.com/.well-known/openid-configuration',
            'supports_groups' => true,
        ]);
        $this->assertTrue($provider->supportsGroupClaims());
    }

    public function testGetConfigFields(): void
    {
        $fields = $this->provider->getConfigFields();
        $this->assertIsArray($fields);
        $this->assertArrayHasKey('client_id', $fields);
        $this->assertArrayHasKey('client_secret', $fields);
        $this->assertArrayHasKey('discovery_url', $fields);
        $this->assertArrayHasKey('display_name', $fields);
        $this->assertArrayHasKey('scopes', $fields);
        $this->assertArrayHasKey('email_claim', $fields);
        $this->assertArrayHasKey('name_claim', $fields);
        $this->assertArrayHasKey('groups_claim', $fields);
        $this->assertArrayHasKey('supports_groups', $fields);
    }

    public function testExtractUserInfo(): void
    {
        $claims = [
            'sub' => 'user-id-123',
            'email' => 'user@example.com',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ];

        $userInfo = $this->provider->extractUserInfo($claims);

        $this->assertEquals('user-id-123', $userInfo['sub']);
        $this->assertEquals('user@example.com', $userInfo['email']);
        $this->assertEquals('Test User', $userInfo['name']);
    }

    public function testExtractUserInfoWithCustomClaimMappings(): void
    {
        $provider = new GenericOidcProvider([
            'client_id' => 'test',
            'client_secret' => 'test',
            'discovery_url' => 'https://example.com/.well-known/openid-configuration',
            'email_claim' => 'user_email',
            'name_claim' => 'display_name',
            'groups_claim' => 'user_groups',
        ]);

        $claims = [
            'sub' => 'user-id-123',
            'user_email' => 'custom@example.com',
            'display_name' => 'Custom Name',
            'user_groups' => ['group1', 'group2'],
        ];

        $userInfo = $provider->extractUserInfo($claims);

        $this->assertEquals('custom@example.com', $userInfo['email']);
        $this->assertEquals('Custom Name', $userInfo['name']);
        $this->assertEquals(['group1', 'group2'], $userInfo['groups']);
    }
}
