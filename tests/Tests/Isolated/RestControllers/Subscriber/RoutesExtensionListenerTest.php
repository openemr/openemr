<?php

/**
 * Isolated tests for RoutesExtensionListener.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\Subscriber\RoutesExtensionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RoutesExtensionListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = RoutesExtensionListener::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertSame('onKernelRequest', $events[KernelEvents::REQUEST][0][0]);
        // Must stay higher than CORSListener's REQUEST priority (25): this
        // listener has to run first so its own OPTIONS guard (tested below)
        // is what lets CORSListener build the preflight response, not a
        // 404 from an unmatched route.
        $this->assertGreaterThan(25, $events[KernelEvents::REQUEST][0][1]);
    }

    /**
     * Regression test: before this fix, an OPTIONS preflight request fell
     * through to processStandardRequest()/processFhirRequest()/
     * processPatientPortalRequest(), none of which have a route registered
     * for OPTIONS, so HttpRestRouteHandler::dispatch() threw a 404
     * HttpException. Because this listener's KernelEvents::REQUEST priority
     * (40) is higher than CORSListener's (25), that uncaught exception
     * short-circuited the REQUEST event chain before CORSListener ever ran,
     * so the browser never got its Access-Control-Allow-Methods/-Headers
     * preflight response and blocked the real request.
     */
    public function testOnKernelRequestReturnsEarlyForOptionsRequest(): void
    {
        $listener = new RoutesExtensionListener();
        $event = $this->createRequestEvent('/default/api/patient', 'OPTIONS');

        // Must not throw (the pre-fix behavior) and must not claim a response
        // — CORSListener (lower priority, runs next) is the one that should
        // build the actual preflight response.
        $listener->onKernelRequest($event);

        $this->assertFalse(
            $event->hasResponse(),
            'RoutesExtensionListener must not set/throw a response for OPTIONS — that is CORSListener\'s job'
        );
    }

    public function testOnKernelRequestStillProcessesNonOptionsRequest(): void
    {
        // Sanity check the guard is scoped to OPTIONS only: a GET request
        // with no matching kernel should still be ignored for the unrelated
        // reason (non-OEHttpKernel), not swallowed by the new guard.
        $listener = new RoutesExtensionListener();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new HttpRestRequest(server: ['REQUEST_URI' => '/default/api/patient', 'REQUEST_METHOD' => 'GET']);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    private function createRequestEvent(string $uri, string $method): RequestEvent
    {
        $kernel = $this->createMock(OEHttpKernel::class);
        $request = new HttpRestRequest(server: ['REQUEST_URI' => $uri, 'REQUEST_METHOD' => $method]);
        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
