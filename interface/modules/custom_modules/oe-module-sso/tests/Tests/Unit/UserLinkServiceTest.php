<?php

/**
 * UserLinkService Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Tests\Unit;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\SSO\Services\UserLinkService;
use PHPUnit\Framework\TestCase;

class UserLinkServiceTest extends TestCase
{
    private UserLinkService $service;
    private const TEST_PROVIDER_TYPE = 'phpunit_test_provider';
    private const TEST_EMAIL = 'phpunit_sso_test@example.com';
    private ?int $testProviderId = null;
    private ?int $testUserId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserLinkService();
        $this->createTestProvider();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestData();
    }

    private function createTestProvider(): void
    {
        // Create a test provider in the database
        sqlStatement(
            "INSERT INTO sso_providers (provider_type, name, enabled, config, auto_provision, default_acl, created_at)
             VALUES (?, ?, 1, '{}', 0, 'users', NOW())",
            [self::TEST_PROVIDER_TYPE, 'PHPUnit Test Provider']
        );

        $result = sqlQuery(
            "SELECT id FROM sso_providers WHERE provider_type = ?",
            [self::TEST_PROVIDER_TYPE]
        );
        $this->testProviderId = (int)$result['id'];
    }

    private function cleanupTestData(): void
    {
        // Clean up user links
        if ($this->testProviderId) {
            sqlStatement("DELETE FROM sso_user_links WHERE provider_id = ?", [$this->testProviderId]);
        }

        // Clean up test provider
        sqlStatement("DELETE FROM sso_providers WHERE provider_type = ?", [self::TEST_PROVIDER_TYPE]);

        // Clean up test user if created
        if ($this->testUserId) {
            sqlStatement("DELETE FROM gacl_groups_aro_map WHERE aro_id IN (SELECT id FROM gacl_aro WHERE value = ?)", ['phpunit_sso_user']);
            sqlStatement("DELETE FROM gacl_aro WHERE value = ?", ['phpunit_sso_user']);
            sqlStatement("DELETE FROM users_secure WHERE username = ?", ['phpunit_sso_user']);
            sqlStatement("DELETE FROM `groups` WHERE user = ?", ['phpunit_sso_user']);
            sqlStatement("DELETE FROM users WHERE id = ?", [$this->testUserId]);
        }
    }

    public function testFindOrLinkUserReturnsNullWithEmptySubject(): void
    {
        $userInfo = [
            'sub' => '',
            'email' => self::TEST_EMAIL,
        ];

        $result = $this->service->findOrLinkUser($this->testProviderId, $userInfo);

        $this->assertNull($result);
    }

    public function testFindOrLinkUserReturnsNullWhenUserNotFound(): void
    {
        $userInfo = [
            'sub' => 'unique_sub_id_' . time(),
            'email' => 'nonexistent_' . time() . '@example.com',
        ];

        $result = $this->service->findOrLinkUser($this->testProviderId, $userInfo, false);

        $this->assertNull($result);
    }

    public function testFindOrLinkUserReturnsExistingLinkedUser(): void
    {
        // First, create a test user
        $username = 'phpunit_existing_user_' . time();
        $userId = sqlInsert(
            "INSERT INTO users (username, password, fname, lname, email, authorized, active)
             VALUES (?, 'NoLongerUsed', 'Test', 'User', ?, 0, 1)",
            [$username, self::TEST_EMAIL]
        );

        // Create secure entry
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        sqlStatement(
            "INSERT INTO users_secure (id, username, password, last_update_password) VALUES (?, ?, ?, NOW())",
            [$userId, $username, $passwordHash]
        );

        // Create a link
        $providerUserId = 'test_sub_' . time();
        sqlStatement(
            "INSERT INTO sso_user_links (user_id, provider_id, provider_user_id, email, linked_at, last_login)
             VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$userId, $this->testProviderId, $providerUserId, self::TEST_EMAIL]
        );

        $userInfo = [
            'sub' => $providerUserId,
            'email' => self::TEST_EMAIL,
        ];

        // Now find the linked user
        $result = $this->service->findOrLinkUser($this->testProviderId, $userInfo);

        $this->assertNotNull($result);
        $this->assertEquals($userId, $result['id']);
        $this->assertEquals($username, $result['username']);

        // Clean up
        sqlStatement("DELETE FROM sso_user_links WHERE provider_id = ? AND provider_user_id = ?", [$this->testProviderId, $providerUserId]);
        sqlStatement("DELETE FROM users_secure WHERE id = ?", [$userId]);
        sqlStatement("DELETE FROM users WHERE id = ?", [$userId]);
    }

    public function testFindOrLinkUserLinksByEmail(): void
    {
        // Create a test user with the email we'll search for
        $username = 'phpunit_email_user_' . time();
        $testEmail = 'phpunit_' . time() . '@example.com';
        $userId = sqlInsert(
            "INSERT INTO users (username, password, fname, lname, email, authorized, active)
             VALUES (?, 'NoLongerUsed', 'Test', 'User', ?, 0, 1)",
            [$username, $testEmail]
        );

        // Create secure entry
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        sqlStatement(
            "INSERT INTO users_secure (id, username, password, last_update_password) VALUES (?, ?, ?, NOW())",
            [$userId, $username, $passwordHash]
        );

        $providerUserId = 'new_sub_' . time();
        $userInfo = [
            'sub' => $providerUserId,
            'email' => $testEmail,
        ];

        // Find should create a new link
        $result = $this->service->findOrLinkUser($this->testProviderId, $userInfo);

        $this->assertNotNull($result);
        $this->assertEquals($userId, $result['id']);

        // Verify link was created
        $link = sqlQuery(
            "SELECT * FROM sso_user_links WHERE provider_id = ? AND provider_user_id = ?",
            [$this->testProviderId, $providerUserId]
        );
        $this->assertNotEmpty($link);
        $this->assertEquals($userId, $link['user_id']);

        // Clean up
        sqlStatement("DELETE FROM sso_user_links WHERE provider_id = ? AND provider_user_id = ?", [$this->testProviderId, $providerUserId]);
        sqlStatement("DELETE FROM users_secure WHERE id = ?", [$userId]);
        sqlStatement("DELETE FROM users WHERE id = ?", [$userId]);
    }

    public function testGetUserLinksReturnsEmptyForUserWithNoLinks(): void
    {
        $links = $this->service->getUserLinks(999999);

        $this->assertIsArray($links);
        $this->assertEmpty($links);
    }

    public function testUnlinkUserRemovesLink(): void
    {
        // Create test data
        $userId = 1;
        $providerUserId = 'test_unlink_sub_' . time();
        sqlStatement(
            "INSERT INTO sso_user_links (user_id, provider_id, provider_user_id, email, linked_at)
             VALUES (?, ?, ?, ?, NOW())",
            [$userId, $this->testProviderId, $providerUserId, self::TEST_EMAIL]
        );

        // Verify link exists
        $linkBefore = sqlQuery(
            "SELECT id FROM sso_user_links WHERE provider_id = ? AND provider_user_id = ?",
            [$this->testProviderId, $providerUserId]
        );
        $this->assertNotEmpty($linkBefore);

        // Unlink
        $result = $this->service->unlinkUser($userId, $this->testProviderId);
        $this->assertTrue($result);

        // Verify link is removed
        $linkAfter = sqlQuery(
            "SELECT id FROM sso_user_links WHERE provider_id = ? AND provider_user_id = ?",
            [$this->testProviderId, $providerUserId]
        );
        $this->assertEmpty($linkAfter);
    }
}
