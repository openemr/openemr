<?php

/**
 * SessionBridge Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Tests\Unit;

use OpenEMR\Modules\SSO\Services\SessionBridge;
use PHPUnit\Framework\TestCase;

class SessionBridgeTest extends TestCase
{
    private SessionBridge $sessionBridge;
    private const TEST_PROVIDER_TYPE = 'phpunit_session_test';
    private ?int $testProviderId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionBridge = new SessionBridge();
        $this->createTestProvider();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestData();
    }

    private function createTestProvider(): void
    {
        sqlStatement(
            "INSERT INTO sso_providers (provider_type, name, enabled, config, created_at)
             VALUES (?, ?, 1, '{}', NOW())",
            [self::TEST_PROVIDER_TYPE, 'PHPUnit Session Test']
        );

        $result = sqlQuery(
            "SELECT id FROM sso_providers WHERE provider_type = ?",
            [self::TEST_PROVIDER_TYPE]
        );
        $this->testProviderId = (int)$result['id'];
    }

    private function cleanupTestData(): void
    {
        // Clean up auth states
        if ($this->testProviderId) {
            sqlStatement("DELETE FROM sso_auth_states WHERE provider_id = ?", [$this->testProviderId]);
            sqlStatement("DELETE FROM sso_audit_log WHERE provider_id = ?", [$this->testProviderId]);
        }

        // Clean up test provider
        sqlStatement("DELETE FROM sso_providers WHERE provider_type = ?", [self::TEST_PROVIDER_TYPE]);
    }

    public function testStoreAuthStateCreatesRecord(): void
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $codeVerifier = bin2hex(random_bytes(32));

        $result = $this->sessionBridge->storeAuthState(
            $this->testProviderId,
            $state,
            $nonce,
            $codeVerifier,
            'default',
            600
        );

        $this->assertTrue($result);

        // Verify record was created
        $record = sqlQuery(
            "SELECT * FROM sso_auth_states WHERE state = ?",
            [$state]
        );

        $this->assertNotEmpty($record);
        $this->assertEquals($nonce, $record['nonce']);
        $this->assertEquals($codeVerifier, $record['code_verifier']);
        $this->assertEquals($this->testProviderId, $record['provider_id']);
        $this->assertEquals('default', $record['site_id']);
    }

    public function testRetrieveAuthStateReturnsCorrectData(): void
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $codeVerifier = bin2hex(random_bytes(32));
        $siteId = 'test_site';

        $this->sessionBridge->storeAuthState(
            $this->testProviderId,
            $state,
            $nonce,
            $codeVerifier,
            $siteId
        );

        $result = $this->sessionBridge->retrieveAuthState($state);

        $this->assertNotNull($result);
        $this->assertEquals($nonce, $result['nonce']);
        $this->assertEquals($codeVerifier, $result['code_verifier']);
        $this->assertEquals($this->testProviderId, $result['provider_id']);
        $this->assertEquals($siteId, $result['site_id']);
    }

    public function testRetrieveAuthStateDeletesStateAfterRetrieval(): void
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $codeVerifier = bin2hex(random_bytes(32));

        $this->sessionBridge->storeAuthState(
            $this->testProviderId,
            $state,
            $nonce,
            $codeVerifier
        );

        // First retrieval should succeed
        $result1 = $this->sessionBridge->retrieveAuthState($state);
        $this->assertNotNull($result1);

        // Second retrieval should fail (state consumed)
        $result2 = $this->sessionBridge->retrieveAuthState($state);
        $this->assertNull($result2);
    }

    public function testRetrieveAuthStateReturnsNullForInvalidState(): void
    {
        $result = $this->sessionBridge->retrieveAuthState('nonexistent_state_' . time());

        $this->assertNull($result);
    }

    public function testRetrieveAuthStateReturnsNullForExpiredState(): void
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $codeVerifier = bin2hex(random_bytes(32));

        // Insert with already-expired timestamp
        sqlStatement(
            "INSERT INTO sso_auth_states (state, nonce, code_verifier, provider_id, site_id, expires_at, created_at)
             VALUES (?, ?, ?, ?, 'default', DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW())",
            [$state, $nonce, $codeVerifier, $this->testProviderId]
        );

        $result = $this->sessionBridge->retrieveAuthState($state);

        $this->assertNull($result);
    }

    public function testLogAuditEventCreatesRecord(): void
    {
        $eventType = 'test_event';
        $eventData = ['test_key' => 'test_value'];

        $this->sessionBridge->logAuditEvent(
            $eventType,
            $this->testProviderId,
            123,
            $eventData
        );

        $record = sqlQuery(
            "SELECT * FROM sso_audit_log WHERE provider_id = ? AND event_type = ? ORDER BY id DESC LIMIT 1",
            [$this->testProviderId, $eventType]
        );

        $this->assertNotEmpty($record);
        $this->assertEquals($this->testProviderId, $record['provider_id']);
        $this->assertEquals(123, $record['user_id']);
        $this->assertEquals($eventType, $record['event_type']);

        $decodedData = json_decode($record['event_data'], true);
        $this->assertEquals('test_value', $decodedData['test_key']);
    }

    public function testIsSsoSessionReturnsFalseWithoutAuthMethod(): void
    {
        // Clear any existing auth_method
        unset($_SESSION['auth_method']);

        $result = $this->sessionBridge->isSsoSession();

        $this->assertFalse($result);
    }

    public function testIsSsoSessionReturnsFalseForLocalAuth(): void
    {
        $_SESSION['auth_method'] = 'local';

        $result = $this->sessionBridge->isSsoSession();

        $this->assertFalse($result);

        unset($_SESSION['auth_method']);
    }

    public function testIsSsoSessionReturnsTrueForSsoAuth(): void
    {
        $_SESSION['auth_method'] = 'entra';

        $result = $this->sessionBridge->isSsoSession();

        $this->assertTrue($result);

        unset($_SESSION['auth_method']);
    }

    public function testGetSessionProviderReturnsNullForNonSsoSession(): void
    {
        unset($_SESSION['auth_method']);

        $result = $this->sessionBridge->getSessionProvider();

        $this->assertNull($result);
    }

    public function testGetSessionProviderReturnsProviderType(): void
    {
        $_SESSION['auth_method'] = 'google';

        $result = $this->sessionBridge->getSessionProvider();

        $this->assertEquals('google', $result);

        unset($_SESSION['auth_method']);
    }
}
