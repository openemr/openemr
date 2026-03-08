<?php

/**
 * Isolated tests for SiteSetupListener.
 *
 * Uses a test subclass that overrides environment-dependent methods
 * (globals.php, session creation, OAuth keys) so the request-handling
 * logic can be tested without Docker or a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 Michael A. Smith <michael@opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Auth\OAuth2KeyException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Subscriber\SiteSetupListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Test subclass that stubs out environment-dependent methods so
 * onKernelRequest can run without globals.php, real sessions, or OAuth keys.
 */
class TestableSiteSetupListener extends SiteSetupListener
{
    /** @var array<string, mixed> Track which protected methods were called and with what args */
    public array $calls = [];

    protected function setupApiSession(
        HttpRestRequest $request,
        string $webroot,
        string $siteId,
        bool $isOauth2Request
    ): void {
        $this->calls['setupApiSession'] = [
            'siteId' => $siteId,
            'isOauth2Request' => $isOauth2Request,
        ];
    }

    protected function loadApplicationGlobals(RequestEvent $event, bool $ignoreAuth): mixed
    {
        $this->calls['loadApplicationGlobals'] = ['ignoreAuth' => $ignoreAuth];
        return null;
    }

    protected function setupCoreSessionBridge(HttpRestRequest $request, string $webroot): void
    {
        $this->calls['setupCoreSessionBridge'] = true;
    }

    protected function setupOAuthKeys(mixed $globalsBag, HttpRestRequest $request): void
    {
        $this->calls['setupOAuthKeys'] = true;
        $request->setApiBaseFullUrl('https://localhost/apis');
    }
}

/**
 * Variant that throws OAuth2KeyException from setupOAuthKeys
 * to test the catch block in onKernelRequest.
 */
class ThrowingOAuthKeyListener extends TestableSiteSetupListener
{
    protected function setupOAuthKeys(mixed $globalsBag, HttpRestRequest $request): void
    {
        throw new OAuth2KeyException('Test key failure');
    }
}

class SiteSetupListenerTest extends TestCase
{
    private string $originalDocRoot;

    protected function setUp(): void
    {
        parent::setUp();
        // getWebroot() reads $_SERVER['DOCUMENT_ROOT']
        $this->originalDocRoot = (string) ($_SERVER['DOCUMENT_ROOT'] ?? '');
        $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 6);
    }

    protected function tearDown(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = $this->originalDocRoot;
        unset($_GET['site']);
        parent::tearDown();
    }

    // ── getSubscribedEvents ──────────────────────────────────────────

    public function testGetSubscribedEvents(): void
    {
        $events = SiteSetupListener::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertSame('onKernelRequest', $events[KernelEvents::REQUEST][0][0]);
        $this->assertSame(100, $events[KernelEvents::REQUEST][0][1]);
    }

    // ── getValidSiteFromPath ─────────────────────────────────────────

    public function testGetValidSiteFromPathWithDefaultSite(): void
    {
        $this->assertSame('default', SiteSetupListener::getValidSiteFromPath('/default/api/'));
    }

    public function testGetValidSiteFromPathDefaultsOnEmptyPath(): void
    {
        // empty string defaults to "/default/"
        $this->assertSame('default', SiteSetupListener::getValidSiteFromPath(''));
    }

    public function testGetValidSiteFromPathDefaultsOnSlashOnly(): void
    {
        // "/" has no second slash, so falls back to "default"
        $this->assertSame('default', SiteSetupListener::getValidSiteFromPath('/'));
    }

    public function testGetValidSiteFromPathRejectsInvalidCharacters(): void
    {
        $this->assertNull(SiteSetupListener::getValidSiteFromPath('/inv@lid/api/'));
    }

    public function testGetValidSiteFromPathRejectsNonexistentSite(): void
    {
        $this->assertNull(SiteSetupListener::getValidSiteFromPath('/nonexistent-site/api/'));
    }

    public function testGetValidSiteFromPathRejectsSpaces(): void
    {
        $this->assertNull(SiteSetupListener::getValidSiteFromPath('/my site/api/'));
    }

    // ── onKernelRequest: early return for non-HttpRestRequest ────────

    public function testOnKernelRequestIgnoresNonHttpRestRequest(): void
    {
        $listener = new TestableSiteSetupListener();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/default/api/patient');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        // Should return early without throwing — the request is not an HttpRestRequest
        $listener->onKernelRequest($event);

        $this->assertEmpty($listener->calls, 'No setup methods should be called for non-HttpRestRequest');
    }

    // ── onKernelRequest: invalid site ────────────────────────────────

    public function testOnKernelRequestThrowsForInvalidSite(): void
    {
        $listener = new TestableSiteSetupListener();
        $event = $this->createRequestEvent('/nonexistent/api/patient');

        try {
            $listener->onKernelRequest($event);
            $this->fail('Expected HttpException was not thrown'); // @codeCoverageIgnore
        } catch (HttpException $e) {
            $this->assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
        }
    }

    public function testOnKernelRequestThrowsForInvalidSiteIdFormat(): void
    {
        $listener = new TestableSiteSetupListener();
        $event = $this->createRequestEvent('/inv@lid/api/patient');

        try {
            $listener->onKernelRequest($event);
            $this->fail('Expected HttpException was not thrown'); // @codeCoverageIgnore
        } catch (HttpException $e) {
            $this->assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
        }
    }

    // ── onKernelRequest: API request without APICSRFTOKEN ────────────

    public function testOnKernelRequestSetsUpApiSession(): void
    {
        $listener = new TestableSiteSetupListener();
        $event = $this->createRequestEvent('/default/api/patient');

        $listener->onKernelRequest($event);

        /** @var HttpRestRequest $request */
        $request = $event->getRequest();

        // Site ID wiring
        $this->assertSame('default', $request->attributes->get('siteId'));
        $this->assertSame('default', $request->getRequestSite());
        $this->assertSame('default', $_GET['site']);

        // API session was set up (not core bridge)
        $this->assertArrayHasKey('setupApiSession', $listener->calls);
        $this->assertSame('default', $listener->calls['setupApiSession']['siteId']);
        $this->assertFalse($listener->calls['setupApiSession']['isOauth2Request']);
        $this->assertArrayNotHasKey('setupCoreSessionBridge', $listener->calls);

        // Globals and OAuth keys were initialized
        $this->assertArrayHasKey('loadApplicationGlobals', $listener->calls);
        $this->assertTrue($listener->calls['loadApplicationGlobals']['ignoreAuth']);
        $this->assertArrayHasKey('setupOAuthKeys', $listener->calls);
    }

    // ── onKernelRequest: OAuth2 request ──────────────────────────────

    public function testOnKernelRequestDetectsOauth2Request(): void
    {
        $listener = new TestableSiteSetupListener();
        // checkForOauth2Request checks basePath ending in "/oauth2"
        // Symfony needs SCRIPT_NAME + SCRIPT_FILENAME to compute basePath
        $event = $this->createRequestEvent(
            '/openemr/oauth2/default/token',
            server: [
                'SCRIPT_NAME' => '/openemr/oauth2/index.php',
                'SCRIPT_FILENAME' => '/var/www/openemr/oauth2/index.php',
            ]
        );

        $listener->onKernelRequest($event);

        $this->assertArrayHasKey('setupApiSession', $listener->calls);
        $this->assertTrue($listener->calls['setupApiSession']['isOauth2Request']);
    }

    // ── onKernelRequest: local API request with APICSRFTOKEN ─────────

    public function testOnKernelRequestWithApicsrftokenSetsCoreSessionBridge(): void
    {
        $listener = new TestableSiteSetupListener();
        $event = $this->createRequestEvent('/default/api/patient', headers: ['APICSRFTOKEN' => 'test-token']);

        $listener->onKernelRequest($event);

        // API session should NOT be created — APICSRFTOKEN path skips it
        $this->assertArrayNotHasKey('setupApiSession', $listener->calls);

        // Core session bridge SHOULD be created
        $this->assertArrayHasKey('setupCoreSessionBridge', $listener->calls);

        // Globals and OAuth keys still initialized
        $this->assertArrayHasKey('loadApplicationGlobals', $listener->calls);
        $this->assertFalse($listener->calls['loadApplicationGlobals']['ignoreAuth']);
        $this->assertArrayHasKey('setupOAuthKeys', $listener->calls);
    }

    // ── onKernelRequest: OAuth2KeyException ─────────────────────────

    public function testOnKernelRequestWrapsOAuth2KeyExceptionInHttpException(): void
    {
        $listener = new ThrowingOAuthKeyListener();
        $event = $this->createRequestEvent('/default/api/patient');

        try {
            $listener->onKernelRequest($event);
            $this->fail('Expected HttpException was not thrown'); // @codeCoverageIgnore
        } catch (HttpException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertSame('Test key failure', $e->getMessage());
            $this->assertInstanceOf(OAuth2KeyException::class, $e->getPrevious());
        }
    }

    // ── onKernelRequest: webroot with APICSRFTOKEN ────────────────

    public function testOnKernelRequestWithApicsrftokenSetsWebroot(): void
    {
        $listener = new TestableSiteSetupListener();
        $event = $this->createRequestEvent('/default/api/patient', headers: ['APICSRFTOKEN' => 'test-token']);

        $listener->onKernelRequest($event);

        /** @var HttpRestRequest $request */
        $request = $event->getRequest();
        $this->assertNotNull($request->attributes->get('webroot'));
    }

    // ── Helper ───────────────────────────────────────────────────────

    /**
     * @param array<string, string> $headers
     * @param array<string, string> $server  Extra server vars (e.g. SCRIPT_NAME for basePath)
     */
    private function createRequestEvent(
        string $uri,
        array $headers = [],
        array $server = []
    ): RequestEvent {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $server = array_merge(['REQUEST_URI' => $uri], $server);
        $request = new HttpRestRequest(server: $server);
        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }
        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
