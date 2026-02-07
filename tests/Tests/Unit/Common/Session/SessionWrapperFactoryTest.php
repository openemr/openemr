<?php

/**
 * SessionWrapperFactoryTest - Tests for session wrapper factory behavior
 *
 * Tests that the SessionWrapperFactory correctly creates and manages
 * Symfony Session instances for both core and portal application contexts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenCoreEMR <hello@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Session;

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionWrapperFactoryTest extends TestCase
{
    /**
     * Store original superglobals to restore after each test
     *
     * @var array<mixed, mixed>
     */
    private array $originalCookie;

    /**
     * @var array<mixed, mixed>
     */
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();

        // Store original superglobals
        $this->originalCookie = $_COOKIE;
        $this->originalServer = $_SERVER;

        // Ensure web_root is set for OEGlobalsBag
        $GLOBALS['web_root'] = '';

        // Reset the singleton instance before each test
        $this->resetSingleton();
    }

    protected function tearDown(): void
    {
        // Restore original superglobals
        $_COOKIE = $this->originalCookie;
        $_SERVER = $this->originalServer;

        // Reset singleton after each test
        $this->resetSingleton();

        // Close any active session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::tearDown();
    }

    /**
     * Reset the SessionWrapperFactory singleton instance
     */
    private function resetSingleton(): void
    {
        $reflection = new ReflectionClass(SessionWrapperFactory::class);

        // Reset the SingletonTrait's instances array
        $instancesProperty = $reflection->getProperty('instances');
        $instancesProperty->setValue(null, []);
    }

    /**
     * Helper to set a private property on the factory via reflection
     */
    private function setFactoryProperty(SessionWrapperFactory $factory, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($factory);
        $prop = $reflection->getProperty($property);
        $prop->setValue($factory, $value);
    }

    /**
     * Helper to get a private property from the factory via reflection
     */
    private function getFactoryProperty(SessionWrapperFactory $factory, string $property): mixed
    {
        $reflection = new ReflectionClass($factory);
        $prop = $reflection->getProperty($property);
        return $prop->getValue($factory);
    }

    /**
     * Helper to ensure a PHP session is active for tests that need it
     */
    private function ensureSessionActive(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    // =========================================================================
    // Singleton Tests
    // =========================================================================

    /**
     * Test that getInstance returns the same instance (singleton pattern)
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $instance1 = SessionWrapperFactory::getInstance();
        $instance2 = SessionWrapperFactory::getInstance();

        $this->assertSame(
            $instance1,
            $instance2,
            'getInstance should return the same singleton instance'
        );
    }

    // =========================================================================
    // isSessionActive() Tests
    // =========================================================================

    /**
     * Test that isSessionActive returns false when no sessions are created
     */
    public function testIsSessionActiveReturnsFalseInitially(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should return false when no sessions have been created'
        );
    }

    /**
     * Test that isSessionActive returns true when portal session exists
     */
    public function testIsSessionActiveReturnsTrueWithPortalSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockSession = $this->createMock(SessionInterface::class);
        $this->setFactoryProperty($factory, 'portalSession', $mockSession);

        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should return true when portal session exists'
        );
    }

    /**
     * Test that isSessionActive returns true when core session exists
     */
    public function testIsSessionActiveReturnsTrueWithCoreSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockSession = $this->createMock(SessionInterface::class);
        $this->setFactoryProperty($factory, 'coreSession', $mockSession);

        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should return true when core session exists'
        );
    }

    /**
     * Test isSessionActive after creating core session via getCoreSession
     */
    public function testIsSessionActiveAfterCoreSessionCreation(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $factory->getCoreSession();

        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should return true after getCoreSession is called'
        );
    }

    /**
     * Test isSessionActive after creating portal session via getPortalSession
     */
    public function testIsSessionActiveAfterPortalSessionCreation(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $factory->getPortalSession();

        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should return true after getPortalSession is called'
        );
    }

    // =========================================================================
    // Session Caching Tests
    // =========================================================================

    /**
     * Test that getCoreSession returns the same cached instance on subsequent calls
     */
    public function testGetCoreSessionReturnsCachedInstance(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $session1 = $factory->getCoreSession();
        $session2 = $factory->getCoreSession();

        $this->assertSame(
            $session1,
            $session2,
            'getCoreSession should return the same cached instance'
        );
    }

    /**
     * Test that getPortalSession returns the same cached instance on subsequent calls
     */
    public function testGetPortalSessionReturnsCachedInstance(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $session1 = $factory->getPortalSession();
        $session2 = $factory->getPortalSession();

        $this->assertSame(
            $session1,
            $session2,
            'getPortalSession should return the same cached instance'
        );
    }

    /**
     * Test that getCoreSession with reset=true creates a new instance
     */
    public function testGetCoreSessionWithResetCreatesNewInstance(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $session1 = $factory->getCoreSession();
        $session2 = $factory->getCoreSession(true);

        $this->assertNotSame(
            $session1,
            $session2,
            'getCoreSession with reset=true should create a new instance'
        );
    }

    /**
     * Test that getPortalSession with reset=true creates a new instance
     */
    public function testGetPortalSessionWithResetCreatesNewInstance(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $session1 = $factory->getPortalSession();
        $session2 = $factory->getPortalSession(true);

        $this->assertNotSame(
            $session1,
            $session2,
            'getPortalSession with reset=true should create a new instance'
        );
    }

    // =========================================================================
    // getActiveSession() Tests
    // =========================================================================

    /**
     * Test that getActiveSession returns core session when no App cookie is set
     */
    public function testGetActiveSessionReturnsCoreWhenNoCookie(): void
    {
        unset($_COOKIE[SessionUtil::APP_COOKIE_NAME]);
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $activeSession = $factory->getActiveSession();

        // Verify it's the core session (same object)
        $coreSession = $factory->getCoreSession();
        $this->assertSame(
            $coreSession,
            $activeSession,
            'getActiveSession without App cookie should return core session'
        );
    }

    /**
     * Test that getActiveSession returns core session when core cookie is set
     */
    public function testGetActiveSessionReturnsCoreWithCoreCookie(): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $activeSession = $factory->getActiveSession();

        $coreSession = $factory->getCoreSession();
        $this->assertSame(
            $coreSession,
            $activeSession,
            'getActiveSession with core cookie should return core session'
        );
    }

    /**
     * Test that getActiveSession returns portal session when portal cookie is set
     */
    public function testGetActiveSessionReturnsPortalWithPortalCookie(): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $activeSession = $factory->getActiveSession();

        $portalSession = $factory->getPortalSession();
        $this->assertSame(
            $portalSession,
            $activeSession,
            'getActiveSession with portal cookie should return portal session'
        );
    }

    /**
     * Test that getActiveSession returns core session for API cookie
     */
    public function testGetActiveSessionReturnsCoreWithApiCookie(): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::API_SESSION_ID;
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $activeSession = $factory->getActiveSession();

        $coreSession = $factory->getCoreSession();
        $this->assertSame(
            $coreSession,
            $activeSession,
            'getActiveSession with API cookie should return core session (non-portal default)'
        );
    }

//    TODO: Get information how properly to distinguish OAuth Session and how to return proper SymfonySession in such case
//    /**
//     * Test that getActiveSession returns core session for OAuth cookie
//     */
//    public function testGetActiveSessionReturnsCoreWithOAuthCookie(): void
//    {
//        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::OAUTH_SESSION_ID;
//        $this->ensureSessionActive();
//
//        $factory = SessionWrapperFactory::getInstance();
//        $activeSession = $factory->getActiveSession();
//
//        $coreSession = $factory->getCoreSession();
//        $this->assertSame(
//            $coreSession,
//            $activeSession,
//            'getActiveSession with OAuth cookie should return core session (non-portal default)'
//        );
//    }

    /**
     * Test that all non-portal session types route to core session
     *
     * @dataProvider nonPortalSessionTypeProvider
     */
    public function testAllNonPortalSessionTypesRouteToCore(string $sessionType): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = $sessionType;
        $this->ensureSessionActive();

        $this->resetSingleton();
        $factory = SessionWrapperFactory::getInstance();
        $activeSession = $factory->getActiveSession();

        $coreSession = $factory->getCoreSession();
        $this->assertSame(
            $coreSession,
            $activeSession,
            "Session type '$sessionType' should route to core session via getActiveSession"
        );
    }

    /**
     * Data provider for non-portal session types
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonPortalSessionTypeProvider(): array
    {
        return [
            'Core session' => [SessionUtil::CORE_SESSION_ID],
            'API session' => [SessionUtil::API_SESSION_ID], // TODO: Discover how properly to do this
            'OAuth session' => [SessionUtil::OAUTH_SESSION_ID], // TODO: Discover how properly to do this
        ];
    }

    // =========================================================================
    // Destroy Session Tests
    // =========================================================================

    /**
     * Test that destroyPortalSession clears the portal session
     */
    public function testDestroyPortalSessionClearsSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Set up a mock portal session via reflection
        $mockSession = $this->createMock(SessionInterface::class);
        $mockSession->expects($this->once())->method('invalidate');
        $this->setFactoryProperty($factory, 'portalSession', $mockSession);

        $this->assertTrue($factory->isSessionActive(), 'Session should be active before destroy');

        $factory->destroyPortalSession();

        $this->assertNull(
            $this->getFactoryProperty($factory, 'portalSession'),
            'portalSession should be null after destroy'
        );
        $this->assertNull(
            $this->getFactoryProperty($factory, 'activeSession'),
            'activeSession should be null after portal session destroy'
        );
    }

    /**
     * Test that destroyCoreSession clears the core session
     */
    public function testDestroyCoreSessionClearsSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Set up a mock core session via reflection
        $mockSession = $this->createMock(SessionInterface::class);
        $mockSession->expects($this->once())->method('invalidate');
        $this->setFactoryProperty($factory, 'coreSession', $mockSession);

        $this->assertTrue($factory->isSessionActive(), 'Session should be active before destroy');

        $factory->destroyCoreSession();

        $this->assertNull(
            $this->getFactoryProperty($factory, 'coreSession'),
            'coreSession should be null after destroy'
        );
        $this->assertNull(
            $this->getFactoryProperty($factory, 'activeSession'),
            'activeSession should be null after core session destroy'
        );
    }

    /**
     * Test that destroyPortalSession does nothing when no portal session exists
     */
    public function testDestroyPortalSessionDoesNothingWhenNoSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Should not throw when portalSession is null
        $factory->destroyPortalSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should remain false after destroying non-existent session'
        );
    }

    /**
     * Test that destroyCoreSession does nothing when no core session exists
     */
    public function testDestroyCoreSessionDoesNothingWhenNoSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Should not throw when coreSession is null
        $factory->destroyCoreSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should remain false after destroying non-existent session'
        );
    }

    /**
     * Test that destroying portal session does not affect core session
     */
    public function testDestroyPortalSessionDoesNotAffectCoreSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockPortalSession = $this->createMock(SessionInterface::class);
        $mockPortalSession->expects($this->once())->method('invalidate');
        $mockCoreSession = $this->createMock(SessionInterface::class);

        $this->setFactoryProperty($factory, 'portalSession', $mockPortalSession);
        $this->setFactoryProperty($factory, 'coreSession', $mockCoreSession);

        $factory->destroyPortalSession();

        $this->assertNull(
            $this->getFactoryProperty($factory, 'portalSession'),
            'portalSession should be null after destroy'
        );
        $this->assertSame(
            $mockCoreSession,
            $this->getFactoryProperty($factory, 'coreSession'),
            'coreSession should remain unchanged after portal session destroy'
        );
        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should still be true because core session remains'
        );
    }

    /**
     * Test that destroying core session does not affect portal session
     */
    public function testDestroyCoreSessionDoesNotAffectPortalSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockCoreSession = $this->createMock(SessionInterface::class);
        $mockCoreSession->expects($this->once())->method('invalidate');
        $mockPortalSession = $this->createMock(SessionInterface::class);

        $this->setFactoryProperty($factory, 'coreSession', $mockCoreSession);
        $this->setFactoryProperty($factory, 'portalSession', $mockPortalSession);

        $factory->destroyCoreSession();

        $this->assertNull(
            $this->getFactoryProperty($factory, 'coreSession'),
            'coreSession should be null after destroy'
        );
        $this->assertSame(
            $mockPortalSession,
            $this->getFactoryProperty($factory, 'portalSession'),
            'portalSession should remain unchanged after core session destroy'
        );
        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should still be true because portal session remains'
        );
    }

    // =========================================================================
    // Session Data Tests
    // =========================================================================

    /**
     * Test that data can be set and retrieved on core session
     */
    public function testCoreSessionSupportsGetAndSet(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $session = $factory->getCoreSession();

        $session->set('test_key', 'test_value');

        $this->assertEquals(
            'test_value',
            $session->get('test_key'),
            'Core session should support setting and getting data'
        );
    }

    /**
     * Test that data can be set and retrieved on portal session
     */
    public function testPortalSessionSupportsGetAndSet(): void
    {
        $this->ensureSessionActive();

        $factory = SessionWrapperFactory::getInstance();
        $session = $factory->getPortalSession();

        $session->set('portal_key', 'portal_value');

        $this->assertEquals(
            'portal_value',
            $session->get('portal_key'),
            'Portal session should support setting and getting data'
        );
    }

    // =========================================================================
    // Commented Out - Old Tests (Pre-SymfonySession Refactoring)
    //
    // These tests reference the old API that was removed during the refactoring
    // to use Symfony Session for both core and portal contexts:
    // - findSessionWrapper() method (removed)
    // - getWrapper() method (removed)
    // - PHPSessionWrapper class (removed)
    // - SymfonySessionWrapper class (removed)
    // - SessionWrapperInterface (removed)
    // - isSymfonySession() / getSymfonySession() methods (removed)
    // =========================================================================

    // /**
    //  * Test that non-portal requests return PHPSessionWrapper
    //  */
    // public function testNonPortalRequestReturnsPHPSessionWrapper(): void
    // {
    //     // Set up non-portal cookie (core OpenEMR)
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //
    //     // Use reflection to call findSessionWrapper directly to avoid session issues
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Non-portal requests should return PHPSessionWrapper'
    //     );
    // }

    // /**
    //  * Test that when session is already active, PHPSessionWrapper is returned
    //  * regardless of cookie value
    //  *
    //  * This is the key fix - prevents "session already started" errors when
    //  * API/OAuth requests have a portal cookie present
    //  */
    // public function testActiveSessionReturnsPHPSessionWrapper(): void
    // {
    //     // Simulate portal cookie being present
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // Start a session to simulate API/OAuth having already started one
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $this->assertEquals(
    //         PHP_SESSION_ACTIVE,
    //         session_status(),
    //         'Session should be active for this test'
    //     );
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //
    //     // Use reflection to call findSessionWrapper directly
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'When session is already active, should return PHPSessionWrapper to avoid conflicts'
    //     );
    //
    //     // Clean up session
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that API request URIs with subpath installations work correctly
    //  *
    //  * Addresses concern: Will this still work correctly if OpenEMR is installed
    //  * at a subpath, like http://example.com/openemr/apis/foo
    //  */
    // public function testApiRequestWithSubpathInstallation(): void
    // {
    //     // Simulate subpath installation: /openemr/apis/fhir/Patient
    //     $_SERVER['REQUEST_URI'] = '/openemr/apis/fhir/Patient';
    //
    //     // With a portal cookie present (common scenario)
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // Start a session to simulate API listener having started one
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     // The key assertion: even with portal cookie and subpath,
    //     // active session check should prevent conflict
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Subpath API requests with active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that OAuth request URIs with subpath installations work correctly
    //  */
    // public function testOAuthRequestWithSubpathInstallation(): void
    // {
    //     // Simulate subpath installation: /openemr/oauth2/authorize
    //     $_SERVER['REQUEST_URI'] = '/openemr/oauth2/authorize';
    //
    //     // With a portal cookie present
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // Start a session to simulate OAuth listener having started one
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Subpath OAuth requests with active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test session wrapper caching - subsequent calls should return same instance
    //  * NOTE: Replaced by testGetCoreSessionReturnsCachedInstance and
    //  *       testGetPortalSessionReturnsCachedInstance above
    //  */
    // public function testWrapperIsCached(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //
    //     // Start session to avoid session start attempts
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $wrapper1 = $factory->getWrapper();
    //     $wrapper2 = $factory->getWrapper();
    //
    //     $this->assertSame(
    //         $wrapper1,
    //         $wrapper2,
    //         'getWrapper should return the same cached instance'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that no App cookie defaults to non-portal behavior
    //  */
    // public function testMissingAppCookieReturnsPHPSessionWrapper(): void
    // {
    //     // No App cookie set
    //     unset($_COOKIE[SessionUtil::APP_COOKIE_NAME]);
    //
    //     // Start session to prevent session start attempts during test
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Missing App cookie should return PHPSessionWrapper (non-portal default)'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test Core session type (CORE_SESSION_ID = "OpenEMR")
    //  */
    // public function testCoreSessionTypeReturnsPHPSessionWrapper(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Core session type (OpenEMR) should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test API session type (API_SESSION_ID = "apiOpenEMR")
    //  */
    // public function testApiSessionTypeReturnsPHPSessionWrapper(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::API_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'API session type (apiOpenEMR) should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test OAuth session type (OAUTH_SESSION_ID = "authserverOpenEMR")
    //  */
    // public function testOAuthSessionTypeReturnsPHPSessionWrapper(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::OAUTH_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'OAuth session type (authserverOpenEMR) should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test Portal session type with active session returns PHPSessionWrapper
    //  * (Portal would normally get SymfonySessionWrapper, but active session takes precedence)
    //  */
    // public function testPortalSessionTypeWithActiveSessionReturnsPHPSessionWrapper(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // Start session BEFORE factory call - this is the conflict scenario
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Portal session type with already-active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test shared library file scenario: Portal cookie present during API request
    //  *
    //  * This is the original bug scenario - a user has the portal open in one tab,
    //  * then an API request is made. The API request has the portal cookie, but
    //  * SiteSetupListener has already started an API session.
    //  */
    // public function testSharedLibraryPortalCookieWithApiSession(): void
    // {
    //     // Portal cookie from another tab/context
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // Simulate API context: /apis/default/fhir/Patient
    //     $_SERVER['REQUEST_URI'] = '/apis/default/fhir/Patient';
    //
    //     // API listener has already started a session
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Shared library accessed with portal cookie during API session should not conflict'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test shared library file scenario: Portal cookie present during OAuth request
    //  */
    // public function testSharedLibraryPortalCookieWithOAuthSession(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '/oauth2/authorize';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Shared library accessed with portal cookie during OAuth session should not conflict'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test shared library file scenario: Core cookie present during API request
    //  */
    // public function testSharedLibraryCoreCookieWithApiSession(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '/apis/default/api/patient';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Shared library accessed with core cookie during API session should work'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test module entry point scenario: Module accessed from different session contexts
    //  *
    //  * Modules often have shared entry points that can be accessed from core,
    //  * portal, or API contexts.
    //  */
    // public function testModuleEntryPointWithMixedSessionContext(): void
    // {
    //     // Simulate module being accessed with portal cookie but API session active
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '/interface/modules/custom_modules/oe-module-example/public/index.php';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Module entry point with mixed session context should not conflict'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test root installation path: /apis/fhir/Patient
    //  */
    // public function testRootInstallationApiPath(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '/apis/fhir/Patient';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Root installation API path with active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test deep subpath installation: /health/systems/openemr/apis/fhir/Patient
    //  */
    // public function testDeepSubpathInstallation(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '/health/systems/openemr/apis/fhir/Patient';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Deep subpath installation with active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that session status check works regardless of REQUEST_URI
    //  *
    //  * The fix relies on session_status() check, not path matching.
    //  * This test confirms it works even with unusual paths.
    //  */
    // public function testSessionStatusCheckIndependentOfPath(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // Unusual path that doesn't match any pattern
    //     $_SERVER['REQUEST_URI'] = '/some/random/path/that/looks/nothing/like/apis';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     // Key assertion: active session prevents conflict regardless of path
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Session status check should work regardless of REQUEST_URI path'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test with empty REQUEST_URI
    //  */
    // public function testEmptyRequestUri(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Empty REQUEST_URI with active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test with missing REQUEST_URI (CLI context)
    //  */
    // public function testMissingRequestUri(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     unset($_SERVER['REQUEST_URI']);
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $reflection = new ReflectionClass($factory);
    //     $method = $reflection->getMethod('findSessionWrapper');
    //     $wrapper = $method->invoke($factory, []);
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         'Missing REQUEST_URI (CLI) with active session should return PHPSessionWrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test init data is properly set on wrapper
    //  * NOTE: Replaced by testCoreSessionSupportsGetAndSet and
    //  *       testPortalSessionSupportsGetAndSet above
    //  */
    // public function testInitDataIsSetOnWrapper(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $initData = ['test_key' => 'test_value'];
    //
    //     $wrapper = $factory->getWrapper($initData);
    //
    //     $this->assertEquals(
    //         'test_value',
    //         $wrapper->get('test_key'),
    //         'Init data should be set on the wrapper'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that PHPSessionWrapper returns isSymfonySession() = false
    //  *
    //  * This is critical because shared files use isSymfonySession() to detect
    //  * if the request is coming from portal context vs core/admin/API context.
    //  */
    // public function testPHPSessionWrapperIsNotSymfonySession(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $wrapper = $factory->getWrapper();
    //
    //     $this->assertInstanceOf(PHPSessionWrapper::class, $wrapper);
    //     $this->assertFalse(
    //         $wrapper->isSymfonySession(),
    //         'PHPSessionWrapper should return false for isSymfonySession()'
    //     );
    //     $this->assertNull(
    //         $wrapper->getSymfonySession(),
    //         'PHPSessionWrapper should return null for getSymfonySession()'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that API request with portal cookie returns wrapper with isSymfonySession() = false
    //  *
    //  * This is THE key scenario: user has portal open, API call is made.
    //  * The API should NOT be treated as a portal request.
    //  */
    // public function testApiWithPortalCookieIsNotSymfonySession(): void
    // {
    //     // Portal cookie present (user has portal open in another tab)
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     // API/OAuth has already started a session
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $wrapper = $factory->getWrapper();
    //
    //     // Should get PHPSessionWrapper, not SymfonySessionWrapper
    //     $this->assertInstanceOf(PHPSessionWrapper::class, $wrapper);
    //
    //     // isSymfonySession() should return false - this is NOT a portal request
    //     $this->assertFalse(
    //         $wrapper->isSymfonySession(),
    //         'API request with portal cookie should NOT be treated as Symfony/portal session'
    //     );
    //
    //     // Shared file code like this should correctly fall through to admin branch:
    //     // if ($session->isSymfonySession() && $session->has('pid')) { /* portal */ }
    //     // else { /* admin/api */ }
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that OAuth request with portal cookie returns wrapper with isSymfonySession() = false
    //  */
    // public function testOAuthWithPortalCookieIsNotSymfonySession(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //     $_SERVER['REQUEST_URI'] = '/oauth2/token';
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $wrapper = $factory->getWrapper();
    //
    //     $this->assertInstanceOf(PHPSessionWrapper::class, $wrapper);
    //     $this->assertFalse(
    //         $wrapper->isSymfonySession(),
    //         'OAuth request with portal cookie should NOT be treated as Symfony/portal session'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Simulate the pattern used in portal/messaging/secure_chat.php and similar files
    //  *
    //  * Pattern:
    //  *   if ($session->isSymfonySession() && $session->has('pid') && $session->has('patient_portal_onsite_two')) {
    //  *       // Portal context - use pid from session
    //  *   } else {
    //  *       // Admin context - require authUserID
    //  *   }
    //  */
    // public function testSharedFilePatternWithApiAndPortalCookie(): void
    // {
    //     // Scenario: API request has portal cookie from another tab
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     // Set up session data as if it were a portal session
    //     $_SESSION['pid'] = '123';
    //     $_SESSION['patient_portal_onsite_two'] = true;
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $wrapper = $factory->getWrapper();
    //
    //     // Simulate the shared file pattern
    //     $isPortalContext = $wrapper->isSymfonySession()
    //         && $wrapper->has('pid')
    //         && $wrapper->has('patient_portal_onsite_two');
    //
    //     // Even though session has portal data, isSymfonySession() is false
    //     // so this should NOT be treated as portal context
    //     $this->assertFalse(
    //         $isPortalContext,
    //         'API request should not enter portal context branch even with portal session data'
    //     );
    //
    //     // The code should fall through to admin/API branch
    //     $isAdminContext = !$wrapper->isSymfonySession() || !$wrapper->has('pid');
    //     $this->assertTrue(
    //         $isAdminContext,
    //         'API request should enter admin/API context branch'
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test CSRF verification pattern used in shared files
    //  *
    //  * Pattern from portal files:
    //  *   CsrfUtils::verifyCsrfToken($token, 'sphere', $session->getSymfonySession())
    //  *
    //  * When getSymfonySession() returns null, CSRF utils should handle it gracefully.
    //  */
    // public function testCsrfPatternWithNullSymfonySession(): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::API_SESSION_ID;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $factory = SessionWrapperFactory::getInstance();
    //     $wrapper = $factory->getWrapper();
    //
    //     // getSymfonySession() returns null for PHPSessionWrapper
    //     $symfonySession = $wrapper->getSymfonySession();
    //     $this->assertNull($symfonySession);
    //
    //     // Code that passes this to CsrfUtils should handle null gracefully
    //     // (CsrfUtils::verifyCsrfToken accepts ?Session parameter)
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Test that all session types correctly report isSymfonySession() = false
    //  * when session is already active (our fix scenario)
    //  *
    //  * @dataProvider sessionTypeProvider
    //  */
    // public function testAllSessionTypesWithActiveSessionAreNotSymfony(string $sessionType): void
    // {
    //     $_COOKIE[SessionUtil::APP_COOKIE_NAME] = $sessionType;
    //
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         @session_start();
    //     }
    //
    //     $this->resetSingleton();
    //     $factory = SessionWrapperFactory::getInstance();
    //     $wrapper = $factory->getWrapper();
    //
    //     $this->assertInstanceOf(
    //         PHPSessionWrapper::class,
    //         $wrapper,
    //         "Session type '$sessionType' with active session should return PHPSessionWrapper"
    //     );
    //     $this->assertFalse(
    //         $wrapper->isSymfonySession(),
    //         "Session type '$sessionType' with active session should return isSymfonySession()=false"
    //     );
    //
    //     if (session_status() === PHP_SESSION_ACTIVE) {
    //         session_write_close();
    //     }
    // }

    // /**
    //  * Data provider for session types (used by commented-out test above)
    //  *
    //  * @return array<string, array{string}>
    //  *
    //  * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
    //  */
    // public static function sessionTypeProvider(): array
    // {
    //     return [
    //         'Core session' => [SessionUtil::CORE_SESSION_ID],
    //         'API session' => [SessionUtil::API_SESSION_ID],
    //         'OAuth session' => [SessionUtil::OAUTH_SESSION_ID],
    //         'Portal session' => [SessionUtil::PORTAL_SESSION_ID],
    //     ];
    // }
}
