<?php

/**
 * Tests for SessionCleanupListener.
 *
 * Verifies that the terminate listener correctly preserves sessions for
 * local API requests (APICSRFTOKEN) and invalidates them for external
 * API requests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\LocalApiAuthorizationController;
use OpenEMR\RestControllers\Subscriber\SessionCleanupListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionCleanupListenerTest extends TestCase
{
    public function testSubscribesToTerminateEvent(): void
    {
        $events = SessionCleanupListener::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $events);
    }

    public function testExternalApiRequestSessionIsInvalidated(): void
    {
        $session = $this->createStartedSession();
        $session->set('site_id', 'default');
        $session->set('authUserID', 1);

        $request = new HttpRestRequest();
        $request->setSession($session);
        // No is_local_api attribute — this is an external API request

        $event = $this->createTerminateEvent($request);
        $listener = new SessionCleanupListener();
        $listener->onRequestTerminated($event);

        // Session should be invalidated (cleared)
        $this->assertNull(
            $session->get('site_id'),
            'External API session should be invalidated'
        );
    }

    /**
     * Simulate the real flow: LocalApiAuthorizationController marks the
     * request, then SessionCleanupListener must recognize that mark and
     * preserve the session. Neither class's attribute name is hardcoded
     * here — the test breaks if they ever disagree.
     */
    public function testLocalApiRequestSessionIsPreserved(): void
    {
        $session = $this->createStartedSession();
        $session->set('site_id', 'default');
        $session->set('authUserID', 1);

        // Build a request the way the real flow does: APICSRFTOKEN header
        // triggers LocalApiAuthorizationController to mark it as local API.
        $request = new HttpRestRequest();
        $request->setSession($session);
        $request->headers->set('APICSRFTOKEN', 'test-token');

        $authController = new LocalApiAuthorizationController(new NullLogger(), new OEGlobalsBag());
        $this->assertTrue($authController->shouldProcessRequest($request));

        // Now the cleanup listener should recognize the request as local API
        // and preserve the session.
        $event = $this->createTerminateEvent($request);
        $listener = new SessionCleanupListener();
        $listener->onRequestTerminated($event);

        $this->assertSame(
            'default',
            $session->get('site_id'),
            'Local API session site_id must be preserved after cleanup'
        );
        $this->assertSame(
            1,
            $session->get('authUserID'),
            'Local API session authUserID must be preserved after cleanup'
        );
    }

    public function testOAuth2InProgressSessionIsPreserved(): void
    {
        $session = $this->createStartedSession();
        $session->set('oauth2_in_progress', true);
        $session->set('site_id', 'default');

        $request = new HttpRestRequest();
        $request->setSession($session);

        $event = $this->createTerminateEvent($request);
        $listener = new SessionCleanupListener();
        $listener->onRequestTerminated($event);

        $this->assertSame(
            'default',
            $session->get('site_id'),
            'OAuth2 in-progress session should be preserved'
        );
    }

    private function createStartedSession(): Session
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        return $session;
    }

    private function createTerminateEvent(HttpRestRequest $request): TerminateEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();
        return new TerminateEvent($kernel, $request, $response);
    }
}
