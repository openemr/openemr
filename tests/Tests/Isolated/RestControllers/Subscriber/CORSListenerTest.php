<?php

/**
 * Tests for CORSListener.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Auth\OpenIDConnect\CORSAllowedOriginRegistry;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Subscriber\CORSListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CORSListenerTest extends TestCase
{
    private const ALLOWED_ORIGIN = 'https://app.example.com';
    private const DISALLOWED_ORIGIN = 'https://evil.example';

    public function testPreflightForAllowedOriginEmitsFullHeaderSet(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'OPTIONS');
        $request->headers->set('Origin', self::ALLOWED_ORIGIN);

        $event = $this->fireRequestEvent($request);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(self::ALLOWED_ORIGIN, $response->headers->get('Access-Control-Allow-Origin'));
        self::assertSame('true', $response->headers->get('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $response->headers->get('Vary'));
        $methods = (string) $response->headers->get('Access-Control-Allow-Methods', '');
        self::assertStringContainsString('POST', $methods);
        self::assertStringContainsString('GET', $methods);
        $headers = (string) $response->headers->get('Access-Control-Allow-Headers', '');
        self::assertStringContainsString('authorization', $headers);
    }

    public function testPreflightForDisallowedOriginOmitsAllowOriginButKeepsVary(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'OPTIONS');
        $request->headers->set('Origin', self::DISALLOWED_ORIGIN);

        $event = $this->fireRequestEvent($request);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertFalse(
            $response->headers->has('Access-Control-Allow-Origin'),
            'Access-Control-Allow-Origin must not be emitted for a disallowed origin'
        );
        self::assertFalse(
            $response->headers->has('Access-Control-Allow-Credentials'),
            'Access-Control-Allow-Credentials must not be emitted for a disallowed origin'
        );
        self::assertSame('Origin', $response->headers->get('Vary'));
    }

    public function testResponseForAllowedOriginSetsAllowOriginAndVary(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::ALLOWED_ORIGIN);
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        self::assertSame(self::ALLOWED_ORIGIN, $event->getResponse()->headers->get('Access-Control-Allow-Origin'));
        self::assertSame('true', $event->getResponse()->headers->get('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $event->getResponse()->headers->get('Vary'));
    }

    public function testResponseForDisallowedOriginOmitsAllowOrigin(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::DISALLOWED_ORIGIN);
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse(
            $out->headers->has('Access-Control-Allow-Origin'),
            'Access-Control-Allow-Origin must not reflect a disallowed origin'
        );
        self::assertFalse(
            $out->headers->has('Access-Control-Allow-Credentials'),
            'Access-Control-Allow-Credentials must not be emitted for a disallowed origin'
        );
        self::assertSame('Origin', $out->headers->get('Vary'));
    }

    public function testRequestWithoutOriginSkipsAllCorsProcessing(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        // no Origin header
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse($out->headers->has('Access-Control-Allow-Origin'));
        self::assertFalse($out->headers->has('Access-Control-Allow-Credentials'));
        self::assertFalse($out->headers->has('Vary'));
    }

    private function fireRequestEvent(HttpRestRequest $request): RequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener = new CORSListener();
        $listener->setAllowedOriginRegistry($this->buildRegistryAllowing(self::ALLOWED_ORIGIN));
        $listener->onKernelRequest($event);
        return $event;
    }

    private function fireResponseEvent(HttpRestRequest $request, Response $response): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $listener = new CORSListener();
        $listener->setAllowedOriginRegistry($this->buildRegistryAllowing(self::ALLOWED_ORIGIN));
        $listener->onKernelResponse($event);
        return $event;
    }

    private function buildRegistryAllowing(string $allowedOrigin): CORSAllowedOriginRegistry
    {
        $registry = $this->createMock(CORSAllowedOriginRegistry::class);
        $normalizedAllowed = CORSAllowedOriginRegistry::normalizeOrigin($allowedOrigin);
        $registry->method('isAllowed')
            ->willReturnCallback(fn (string $origin): bool => CORSAllowedOriginRegistry::normalizeOrigin($origin) === $normalizedAllowed);
        $registry->method('getAllowedOrigins')
            ->willReturn($normalizedAllowed === null ? [] : [$normalizedAllowed]);
        return $registry;
    }
}
