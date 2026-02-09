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
     * Test that isSessionActive returns true when activeSession is set via setActiveSession
     */
    public function testIsSessionActiveReturnsTrueWithActiveSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockSession = $this->createMock(SessionInterface::class);
        $factory->setActiveSession($mockSession);

        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should return true when activeSession is set'
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
            'API session' => [SessionUtil::API_SESSION_ID],
            'OAuth session' => [SessionUtil::OAUTH_SESSION_ID],
        ];
    }

    // =========================================================================
    // Destroy Session Tests
    // =========================================================================

    /**
     * Test that destroyPortalSession invalidates and clears the active session
     */
    public function testDestroyPortalSessionClearsSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockSession = $this->createMock(SessionInterface::class);
        $mockSession->expects($this->once())->method('invalidate');
        $factory->setActiveSession($mockSession);

        $this->assertTrue($factory->isSessionActive(), 'Session should be active before destroy');

        $factory->destroyPortalSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should be false after destroy'
        );
        $this->assertNull(
            $this->getFactoryProperty($factory, 'activeSession'),
            'activeSession should be null after portal session destroy'
        );
    }

    /**
     * Test that destroyCoreSession invalidates and clears the active session
     */
    public function testDestroyCoreSessionClearsSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockSession = $this->createMock(SessionInterface::class);
        $mockSession->expects($this->once())->method('invalidate');
        $factory->setActiveSession($mockSession);

        $this->assertTrue($factory->isSessionActive(), 'Session should be active before destroy');

        $factory->destroyCoreSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should be false after destroy'
        );
        $this->assertNull(
            $this->getFactoryProperty($factory, 'activeSession'),
            'activeSession should be null after core session destroy'
        );
    }

    /**
     * Test that destroyPortalSession does nothing when no session exists
     */
    public function testDestroyPortalSessionDoesNothingWhenNoSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Should not throw when activeSession is null
        $factory->destroyPortalSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should remain false after destroying non-existent session'
        );
    }

    /**
     * Test that destroyCoreSession does nothing when no session exists
     */
    public function testDestroyCoreSessionDoesNothingWhenNoSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Should not throw when activeSession is null
        $factory->destroyCoreSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should remain false after destroying non-existent session'
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
    // setActiveSession() Tests - API and OAuth session injection
    //
    // In API/OAuth contexts, an external listener (e.g. SiteSetupListener)
    // creates a Symfony Session and injects it via setActiveSession().
    // Since isSessionActive() checks activeSession directly, calling
    // setActiveSession() is sufficient for getActiveSession() to return
    // the injected session without needing core/portal sessions.
    // =========================================================================

    /**
     * Test that setActiveSession stores the session in the activeSession property
     */
    public function testSetActiveSessionStoresSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();
        $mockSession = $this->createMock(SessionInterface::class);

        $factory->setActiveSession($mockSession);

        $this->assertSame(
            $mockSession,
            $this->getFactoryProperty($factory, 'activeSession'),
            'setActiveSession should store the session in the activeSession property'
        );
    }

    /**
     * Test that setActiveSession can override a previously set active session
     */
    public function testSetActiveSessionOverridesPreviousSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $firstSession = $this->createMock(SessionInterface::class);
        $secondSession = $this->createMock(SessionInterface::class);

        $factory->setActiveSession($firstSession);
        $factory->setActiveSession($secondSession);

        $this->assertSame(
            $secondSession,
            $this->getFactoryProperty($factory, 'activeSession'),
            'setActiveSession should override a previously set session'
        );
    }

    /**
     * Test that getActiveSession returns the injected session
     *
     * Calling setActiveSession() is sufficient for getActiveSession() to return
     * the injected session — cookie-based routing is bypassed.
     */
    public function testGetActiveSessionReturnsInjectedSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();
        $mockApiSession = $this->createMock(SessionInterface::class);

        $factory->setActiveSession($mockApiSession);

        $this->assertTrue(
            $factory->isSessionActive(),
            'isSessionActive should be true when activeSession is set via setActiveSession'
        );

        $this->assertSame(
            $mockApiSession,
            $factory->getActiveSession(),
            'getActiveSession should return the session set via setActiveSession'
        );
    }

    /**
     * Test the typical API session lifecycle:
     * 1. API listener creates a Symfony Session and injects it
     * 2. getActiveSession() returns the API session
     * 3. Data set on the API session is accessible
     */
    public function testApiSessionLifecycle(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Step 1: API listener creates and injects API session
        $mockApiSession = $this->createMock(SessionInterface::class);
        $mockApiSession->method('get')->with('api_token')->willReturn('abc123');
        $factory->setActiveSession($mockApiSession);

        // Step 2: getActiveSession returns API session
        $activeSession = $factory->getActiveSession();
        $this->assertSame($mockApiSession, $activeSession);

        // Step 3: Data is accessible
        $this->assertEquals(
            'abc123',
            $activeSession->get('api_token'),
            'Data set on the injected API session should be accessible via getActiveSession'
        );
    }

    /**
     * Test the typical OAuth session lifecycle:
     * 1. OAuth listener creates a Symfony Session and injects it
     * 2. getActiveSession() returns the OAuth session
     * 3. Data set on the OAuth session is accessible
     */
    public function testOAuthSessionLifecycle(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        // Step 1: OAuth listener creates and injects OAuth session
        $mockOAuthSession = $this->createMock(SessionInterface::class);
        $mockOAuthSession->method('get')->with('oauth_client_id')->willReturn('client_xyz');
        $factory->setActiveSession($mockOAuthSession);

        // Step 2: getActiveSession returns OAuth session
        $activeSession = $factory->getActiveSession();
        $this->assertSame($mockOAuthSession, $activeSession);
        $this->assertEquals(
            'client_xyz',
            $activeSession->get('oauth_client_id'),
            'Data set on the injected OAuth session should be accessible via getActiveSession'
        );
    }

    /**
     * Test that destroyCoreSession clears the injected active session
     *
     * Verifies that destroy properly cleans up the active session,
     * preventing stale API sessions from persisting.
     */
    public function testDestroyCoreSessionClearsInjectedActiveSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockApiSession = $this->createMock(SessionInterface::class);
        $mockApiSession->expects($this->once())->method('invalidate');
        $factory->setActiveSession($mockApiSession);

        // Verify the injected session is returned
        $this->assertSame($mockApiSession, $factory->getActiveSession());

        $factory->destroyCoreSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should be false after destroyCoreSession'
        );
    }

    /**
     * Test that destroyPortalSession clears the injected active session
     *
     * Verifies that destroy properly cleans up the active session,
     * preventing stale OAuth sessions from persisting.
     */
    public function testDestroyPortalSessionClearsInjectedActiveSession(): void
    {
        $factory = SessionWrapperFactory::getInstance();

        $mockOAuthSession = $this->createMock(SessionInterface::class);
        $mockOAuthSession->expects($this->once())->method('invalidate');
        $factory->setActiveSession($mockOAuthSession);

        // Verify the injected session is returned
        $this->assertSame($mockOAuthSession, $factory->getActiveSession());

        $factory->destroyPortalSession();

        $this->assertFalse(
            $factory->isSessionActive(),
            'isSessionActive should be false after destroyPortalSession'
        );
    }


    // =========================================================================
    // Cross-Context Session Conflict Prevention Tests
    //
    // These test real-world scenarios where the browser has a cookie from one
    // context (e.g. portal) but the current request is handled by a different
    // context (e.g. API/OAuth). When a listener has already injected a session
    // via setActiveSession(), the App cookie must be irrelevant.
    // =========================================================================

    /**
     * Test: Portal cookie present, but API listener already injected an API session
     *
     * Scenario: User has portal open in one tab (App cookie = PORTAL_SESSION_ID).
     * An API request comes from the same browser. SiteSetupListener creates an
     * API session and calls setActiveSession(). getActiveSession() must return
     * the API session, NOT create a portal session from the cookie.
     */
    public function testPortalCookieWithInjectedApiSessionReturnsApiSession(): void
    {
        // Portal cookie is present from another tab
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;

        $factory = SessionWrapperFactory::getInstance();

        // API listener has already created and injected an API session
        $mockApiSession = $this->createMock(SessionInterface::class);
        $mockApiSession->method('get')->with('api_context')->willReturn('fhir');
        $factory->setActiveSession($mockApiSession);

        // getActiveSession must return the API session, not a portal session
        $activeSession = $factory->getActiveSession();
        $this->assertSame(
            $mockApiSession,
            $activeSession,
            'With portal cookie but injected API session, getActiveSession should return the API session'
        );
        $this->assertEquals('fhir', $activeSession->get('api_context'));
    }

    /**
     * Test: Portal cookie present, but OAuth listener already injected an OAuth session
     *
     * Scenario: User has portal open, OAuth authorization request comes in.
     * The OAuth listener creates its session and injects it.
     */
    public function testPortalCookieWithInjectedOAuthSessionReturnsOAuthSession(): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;

        $factory = SessionWrapperFactory::getInstance();

        $mockOAuthSession = $this->createMock(SessionInterface::class);
        $mockOAuthSession->method('get')->with('oauth_state')->willReturn('authorize');
        $factory->setActiveSession($mockOAuthSession);

        $activeSession = $factory->getActiveSession();
        $this->assertSame(
            $mockOAuthSession,
            $activeSession,
            'With portal cookie but injected OAuth session, getActiveSession should return the OAuth session'
        );
        $this->assertEquals('authorize', $activeSession->get('oauth_state'));
    }

    /**
     * Test: Core cookie present, but API listener already injected an API session
     *
     * Scenario: User has core OpenEMR open, API request comes in from same browser.
     */
    public function testCoreCookieWithInjectedApiSessionReturnsApiSession(): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::CORE_SESSION_ID;

        $factory = SessionWrapperFactory::getInstance();

        $mockApiSession = $this->createMock(SessionInterface::class);
        $factory->setActiveSession($mockApiSession);

        $this->assertSame(
            $mockApiSession,
            $factory->getActiveSession(),
            'With core cookie but injected API session, getActiveSession should return the API session'
        );
    }

    /**
     * Test: Module entry point accessed with portal cookie, but API session is active
     *
     * Modules often have shared entry points that can be accessed from core,
     * portal, or API contexts. The injected session must take priority.
     */
    public function testModuleEntryPointWithPortalCookieAndInjectedApiSession(): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
        $_SERVER['REQUEST_URI'] = '/interface/modules/custom_modules/oe-module-example/public/index.php';

        $factory = SessionWrapperFactory::getInstance();

        $mockApiSession = $this->createMock(SessionInterface::class);
        $factory->setActiveSession($mockApiSession);

        $this->assertSame(
            $mockApiSession,
            $factory->getActiveSession(),
            'Module entry point with portal cookie but injected API session should return API session'
        );
    }

    /**
     * Test: Injected session takes priority regardless of any App cookie value
     *
     * @dataProvider appCookieProvider
     */
    public function testInjectedSessionTakesPriorityOverAnyCookie(string $cookieValue): void
    {
        $_COOKIE[SessionUtil::APP_COOKIE_NAME] = $cookieValue;

        $this->resetSingleton();
        $factory = SessionWrapperFactory::getInstance();

        $mockInjectedSession = $this->createMock(SessionInterface::class);
        $factory->setActiveSession($mockInjectedSession);

        $this->assertSame(
            $mockInjectedSession,
            $factory->getActiveSession(),
            "Injected session should take priority over App cookie '$cookieValue'"
        );
    }

    /**
     * Data provider for all App cookie values
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function appCookieProvider(): array
    {
        return [
            'Core cookie' => [SessionUtil::CORE_SESSION_ID],
            'Portal cookie' => [SessionUtil::PORTAL_SESSION_ID],
            'API cookie' => [SessionUtil::API_SESSION_ID],
            'OAuth cookie' => [SessionUtil::OAUTH_SESSION_ID],
        ];
    }
}
