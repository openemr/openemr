<?php

namespace OpenEMR\Tests\Unit\Common\Http;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class HttpRestRouteHandlerTest extends TestCase {

    public function testCheckSecurityWithNoRequestUserRole()
    {
        $this->expectException(AccessDeniedException::class, "AccessDeniedException should be thrown when request user role is not set");
    }

    public function testCheckSecurityWithInvalidEventReturned()
    {
        $this->expectException(AccessDeniedException::class, "AccessDeniedException should be thrown when request user role is invalid");

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')
            ->willReturn($this->createMock(Event::class)); // needs to be a RestApiSecurityCheckEvent
        $this->expectException(AccessDeniedException::class, "AccessDeniedException should be thrown when event is not RestApiSecurityCheckEvent");
    }

    public function testDispatch()
    {
        $resource = 'test-route';
        $request = HttpRestRequest::create('/' . $resource, 'GET');

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')
            ->willReturn(new RestApiSecurityCheckEvent($request));
        // make sure controller is being called correctly
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->once($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventDispatcher);

        $request->setRequestUserRole('users');
        $request->setResource($resource);
        $restRouteHandler = new HttpRestRouteHandler($kernel);
        $controller =  function (HttpRestRequest $request) {};
        $updatedRequest = $restRouteHandler->dispatch([
            'GET /' . $resource . '-2' => function() {}
            ,'GET /' . $resource => $controller
        ], $request);
        $this->assertEquals($controller, $updatedRequest->attributes->get("_controller"), "Controller should be set correctly for the route");
    }
}
