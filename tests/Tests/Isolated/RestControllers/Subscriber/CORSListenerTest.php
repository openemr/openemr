<?php

/**
 * Isolated tests for CORSListener.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Subscriber\CORSListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CORSListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = CORSListener::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertSame('onKernelRequest', $events[KernelEvents::REQUEST][0][0]);
        // Must stay above RoutesExtensionListener's REQUEST priority (40): routes
        // are never registered for OPTIONS, so if that listener runs first it
        // 404s on any preflight, aborting the REQUEST event chain before this
        // listener gets to build the actual preflight response.
        $this->assertGreaterThan(40, $events[KernelEvents::REQUEST][0][1]);
    }

    /**
     * Regression test: with CORSListener's old priority (25, lower than
     * RoutesExtensionListener's 40), an OPTIONS preflight never reached this
     * method at all — RoutesExtensionListener ran first, found no route
     * registered for OPTIONS, and threw a 404 that aborted the REQUEST event
     * chain. Now that this listener runs first, it must claim the response
     * for OPTIONS + Origin.
     */
    public function testOnKernelRequestHandlesOptionsWithOrigin(): void
    {
        $listener = new CORSListener();
        $event = $this->createRequestEvent('OPTIONS', ['Origin' => 'https://example.com']);

        $listener->onKernelRequest($event);

        $this->assertTrue($event->hasResponse(), 'CORSListener must claim the response for an OPTIONS preflight');
        $this->assertSame(Response::HTTP_OK, $event->getResponse()->getStatusCode());
    }

    /**
     * A bare OPTIONS request with no Origin header isn't a real CORS
     * preflight (browsers always send Origin on cross-origin preflight), so
     * this listener intentionally leaves it alone — it still falls through
     * to RoutesExtensionListener same as before this fix.
     */
    public function testOnKernelRequestIgnoresOptionsWithoutOrigin(): void
    {
        $listener = new CORSListener();
        $event = $this->createRequestEvent('OPTIONS', []);

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequestEvent(string $method, array $headers): RequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new HttpRestRequest(server: ['REQUEST_URI' => '/default/api/patient', 'REQUEST_METHOD' => $method]);
        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }
        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
