<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Subscriber\CORSListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CORSListenerTest extends TestCase
{
    public function testPreflightResponseIncludesAllowedMethods(): void
    {
        $request = HttpRestRequest::create(
            '/apis/default/api/patient',
            'OPTIONS',
            server: ['HTTP_ORIGIN' => 'https://example.com']
        );
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        (new CORSListener())->onKernelRequest($event);

        $this->assertSame(
            'GET, HEAD, POST, PUT, DELETE, PATCH, TRACE, OPTIONS',
            $event->getResponse()->headers->get('Access-Control-Allow-Methods')
        );
    }
}
