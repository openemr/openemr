<?php

namespace OpenEMR\Tests\RestControllers\Subscriber;

use OpenEMR\Core\Kernel;
use OpenEMR\RestControllers\Subscriber\SiteSetupListener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\HeaderBag;

class SiteSetupListenerTest extends TestCase
{
    #[Test]
    public function testOnKernelRequest()
    {
        $listener = new SiteSetupListener();
        $site = 'default';
        // Simulate a request event
        $request = $this->createMock(Request::class);
        $request->server = $this->createMock(ServerBag::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->expects($this->once())->method('set')
            ->with('siteId', $site);

        $request->headers = $this->createMock(HeaderBag::class);
        $request->headers->expects($this->once())->method('get')
            ->with('APICSRFTOKEN')
            ->willReturn(null); // Simulating no CSRF token for this test
        $request->expects($this->once())->method('getPathInfo')
            ->willReturn($site . '/apis/');
        $event = $this->createMock(RequestEvent::class);
        $event->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);

        $kernel = $this->createMock(\OpenEMR\Core\OEHttpKernel::class);
        $dispatcher = $this->createMock(EventDispatcher::class);
        $kernel->expects($this->atLeastOnce())->method('getEventDispatcher')
            ->willReturn($dispatcher);
        $event->expects($this->atLeastOnce())->method('getKernel')
            ->willReturn($kernel);
        // Call the method and assert no exceptions are thrown
        $listener->onKernelRequest($event);

        // now we need to check to make sure session was started
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status(), "Session should be active after onKernelRequest call");

        $this->assertInstanceOf(Kernel::class, $GLOBALS['kernel'], "Kernel should be set in globals after onKernelRequest call");
        $this->assertArrayHasKey("site_id", $_SESSION, "Session should have siteId set after onKernelRequest call");
        $this->assertEquals($site, $_SESSION['site_id'], "Session site_id should match the site set in onKernelRequest");
        $this->assertEquals('apiOpenEMR', session_name(), "Session name should be 'apiOpenEMR' after onKernelRequest call");
    }
    public function testOnKernelRequestFailsInvalidCSRFToken()
    {
        $this->markTestIncomplete("Need to implement test for invalid CSRF token handling in SiteSetupListener");
    }

    public function testOnKernelRequestFailsMissingSiteDirectory()
    {
        $this->markTestIncomplete("Need to implement test for missing site directory handling in SiteSetupListener");
    }

    public function testOnKernelRequestFailsInvalidSiteIdFormat()
    {
        $this->markTestIncomplete("Need to implement test for invalid site ID format handling in SiteSetupListener");
    }

    public function testOnKernelRequestFailsNoSiteId()
    {
        $this->markTestIncomplete("Need to implement test for no site ID handling in SiteSetupListener");
    }

    public function testOnKernelRequestValidCSRFToken()
    {
        $this->markTestIncomplete("Need to implement test for valid CSRF token handling in SiteSetupListener");
    }

    #[Test]
    public function testGetSubscribedEvents()
    {
        $events = SiteSetupListener::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertIsArray($events[KernelEvents::REQUEST]);
        $this->assertCount(1, $events[KernelEvents::REQUEST]);
        $this->assertEquals('onKernelRequest', $events[KernelEvents::REQUEST][0][0]);
        $this->assertEquals(50, $events[KernelEvents::REQUEST][0][1]);
    }
}
