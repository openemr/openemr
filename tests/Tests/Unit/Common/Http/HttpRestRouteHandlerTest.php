<?php

namespace OpenEMR\Tests\Unit\Common\Http;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class HttpRestRouteHandlerTest extends TestCase
{
    public function testCheckSecurityWithNoRequestUserRole(): void
    {
        $this->markTestIncomplete("This test is incomplete and needs to be implemented properly.");
//        $kernel = $this->createMock(OEHttpKernel::class);
//        $kernel->method('getSystemLogger')
//            ->willReturn(new SystemLogger());
//        $restRouteHandler = new HttpRestRouteHandler($kernel);
//        $restRouteHandler->checkSecurity($kernel, HttpRestRequest::create('/test-route', 'GET'));
//        $this->expectException(AccessDeniedException::class, "AccessDeniedException should be thrown when request user role is not set");
    }

    public function testCheckSecurityWithInvalidEventReturned(): void
    {
        $this->markTestIncomplete("This test is incomplete and needs to be implemented properly.");
//        $this->expectException(AccessDeniedException::class, "AccessDeniedException should be thrown when request user role is invalid");
//
//        $kernel = $this->createMock(OEHttpKernel::class);
//        $eventDispatcher = $this->createMock(EventDispatcher::class);
//        $eventDispatcher->method('dispatch')
//            ->willReturn($this->createMock(Event::class)); // needs to be a RestApiSecurityCheckEvent
//        $this->expectException(AccessDeniedException::class, "AccessDeniedException should be thrown when event is not RestApiSecurityCheckEvent");
    }

    public function testDispatch(): void
    {
        $resource = 'test-route';
        $request = HttpRestRequest::create('/' . $resource, 'GET');

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')
            ->willReturn(new RestApiSecurityCheckEvent($request));
        // make sure controller is being called correctly
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getSystemLogger')
            ->willReturn(new SystemLogger());
        $kernel->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventDispatcher);

        $request->setRequestUserRole('users');
        $request->setResource($resource);
        $restRouteHandler = new HttpRestRouteHandler($kernel);
        $controller =  function (HttpRestRequest $request): void {};
        $restRouteHandler->dispatch([
            'GET /' . $resource . '-2' => function (): void {}
            ,'GET /' . $resource => $controller
        ], $request);
        $this->assertEquals($controller, $request->attributes->get("_controller"), "Controller should be set correctly for the route");
    }

    public function testDispatchWithOperation(): void {
        $resource = '';
        $path = '$bulkdata-status';
        $request = HttpRestRequest::create('/' . $path, 'GET');

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')
            ->willReturn(new RestApiSecurityCheckEvent($request));
        // make sure controller is being called correctly
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getSystemLogger')
            ->willReturn(new SystemLogger());
        $kernel->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventDispatcher);

        $request->setRequestUserRole('system');
        $request->setResource($resource);
        $request->setOperation('$bulkdata-status');
        $restRouteHandler = new HttpRestRouteHandler($kernel);
        $controller =  function (HttpRestRequest $request): void {};
        $restRouteHandler->dispatch([
            'GET /' . $path => $controller
        ], $request);
        $this->assertEquals($controller, $request->attributes->get("_controller"), "Controller should be set correctly for the route");
    }
}
