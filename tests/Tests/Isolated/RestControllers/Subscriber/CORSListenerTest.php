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
    private const SMART_APP_ORIGIN = 'https://app.example.com';
    private const ATTACKER_ORIGIN = 'https://evil.example';
    private const SMART_APP_CLIENT_ID = 'smart-app-client';
    private const ATTACKER_CLIENT_ID = 'attacker-registered-client';

    // ----- Preflight (OPTIONS) -----

    public function testPreflightForAllowedOriginEmitsFullHeaderSet(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'OPTIONS');
        $request->headers->set('Origin', self::SMART_APP_ORIGIN);

        $event = $this->fireRequestEvent($request);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(self::SMART_APP_ORIGIN, $response->headers->get('Access-Control-Allow-Origin'));
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
        $request->headers->set('Origin', 'https://unregistered.example');

        $event = $this->fireRequestEvent($request);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertFalse(
            $response->headers->has('Access-Control-Allow-Origin'),
            'Access-Control-Allow-Origin must not be emitted for a disallowed origin'
        );
        self::assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $response->headers->get('Vary'));
    }

    // ----- Response (bearer-authenticated) -----

    public function testResponseForBearerAuthEmitsAllowOriginWhenOriginMatchesTokenClient(): void
    {
        // Legitimate SMART app: bearer-token request from app.example.com,
        // token's clientId is the SMART app's own client, whose own
        // registered redirect_uris include app.example.com. Must emit
        // Allow-Origin so the app's JS can read the response body.
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::SMART_APP_ORIGIN);
        $request->attributes->set('clientId', self::SMART_APP_CLIENT_ID);
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertSame(self::SMART_APP_ORIGIN, $out->headers->get('Access-Control-Allow-Origin'));
        self::assertSame('true', $out->headers->get('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $out->headers->get('Vary'));
    }

    public function testResponseForBearerAuthDoesNotEmitAllowOriginForDifferentClientsOrigin(): void
    {
        // The core of the per-client scoping fix. The attacker's
        // registered client declared evil.example as a redirect_uri, so
        // evil.example is in the global union. But the request's bearer
        // token was issued to the SMART app client, not the attacker's
        // client. Even though the global union says "evil.example is a
        // known origin," the per-client check against the SMART app's
        // own origins rejects it. Response body is readable only by the
        // app whose client the token actually belongs to.
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::ATTACKER_ORIGIN);
        $request->attributes->set('clientId', self::SMART_APP_CLIENT_ID);
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse(
            $out->headers->has('Access-Control-Allow-Origin'),
            'Allow-Origin must not reflect an origin that is registered under a DIFFERENT client'
        );
        self::assertFalse($out->headers->has('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $out->headers->get('Vary'));
    }

    // ----- Response (session-cookie auth or unauth) -----

    public function testResponseForSessionCookieAuthDoesNotEmitAllowOrigin(): void
    {
        // LocalApiAuthorizationController + SkipAuthorizationStrategy
        // both set clientId=null on the request. Even if the request's
        // Origin header matches a globally-registered origin, no
        // Allow-Origin header is emitted -- the browser blocks the
        // cross-origin JS read of the response body, so a session
        // cookie cannot be used as a reflection oracle.
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::SMART_APP_ORIGIN);
        $request->attributes->set('clientId', null);
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse(
            $out->headers->has('Access-Control-Allow-Origin'),
            'Allow-Origin must not be emitted for session-cookie-authenticated responses'
        );
        self::assertFalse($out->headers->has('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $out->headers->get('Vary'));
    }

    public function testResponseForUnauthenticatedRequestDoesNotEmitAllowOrigin(): void
    {
        // No auth strategy ran -> no clientId attribute at all. Same
        // behavior as clientId=null: no Allow-Origin. The 401 body is
        // still returned by the server, but attacker JS cannot read
        // it.
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::SMART_APP_ORIGIN);
        $response = new Response(
            '{"error":"Unauthorized"}',
            Response::HTTP_UNAUTHORIZED,
            ['Content-Type' => 'application/json']
        );

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse(
            $out->headers->has('Access-Control-Allow-Origin'),
            'Allow-Origin must not be emitted on unauthenticated responses'
        );
        self::assertFalse($out->headers->has('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $out->headers->get('Vary'));
    }

    public function testResponseForBearerAuthWithEmptyClientIdDoesNotEmitAllowOrigin(): void
    {
        // Defensive: a clientId attribute present but empty-string
        // means the auth path left an unusable value. Treat as unauth
        // for CORS purposes rather than falling back to the global
        // allow-list.
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        $request->headers->set('Origin', self::SMART_APP_ORIGIN);
        $request->attributes->set('clientId', '');
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse($out->headers->has('Access-Control-Allow-Origin'));
        self::assertFalse($out->headers->has('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $out->headers->get('Vary'));
    }

    public function testRequestWithoutOriginSkipsAllCorsProcessing(): void
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient', 'GET');
        // no Origin header
        $request->attributes->set('clientId', self::SMART_APP_CLIENT_ID);
        $response = new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/fhir+json']);

        $event = $this->fireResponseEvent($request, $response);

        $out = $event->getResponse();
        self::assertFalse($out->headers->has('Access-Control-Allow-Origin'));
        self::assertFalse($out->headers->has('Access-Control-Allow-Credentials'));
        self::assertFalse($out->headers->has('Vary'));
    }

    // ----- harness -----

    private function fireRequestEvent(HttpRestRequest $request): RequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener = new CORSListener();
        $listener->setAllowedOriginRegistry($this->buildRegistry());
        $listener->onKernelRequest($event);
        return $event;
    }

    private function fireResponseEvent(HttpRestRequest $request, Response $response): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $listener = new CORSListener();
        $listener->setAllowedOriginRegistry($this->buildRegistry());
        $listener->onKernelResponse($event);
        return $event;
    }

    /**
     * Mock registry with two clients:
     * - SMART_APP_CLIENT_ID registered SMART_APP_ORIGIN.
     * - ATTACKER_CLIENT_ID registered ATTACKER_ORIGIN.
     *
     * The global union covers both, matching the pre-fix "any registered
     * origin is accepted anywhere" behaviour so the preflight-lenient
     * path and the "different client's origin" case are both exercised.
     */
    private function buildRegistry(): CORSAllowedOriginRegistry
    {
        $registry = $this->createMock(CORSAllowedOriginRegistry::class);

        $perClient = [
            self::SMART_APP_CLIENT_ID => [self::SMART_APP_ORIGIN],
            self::ATTACKER_CLIENT_ID  => [self::ATTACKER_ORIGIN],
        ];
        $normalizedPerClient = [];
        foreach ($perClient as $clientId => $origins) {
            $normalizedPerClient[$clientId] = array_filter(array_map(
                CORSAllowedOriginRegistry::normalizeOrigin(...),
                $origins
            ));
        }

        $globalUnion = [];
        foreach ($normalizedPerClient as $origins) {
            foreach ($origins as $origin) {
                $globalUnion[$origin] = true;
            }
        }
        $globalUnion = array_keys($globalUnion);

        $registry->method('getOriginsForClient')
            ->willReturnCallback(static fn (string $clientId): array => $normalizedPerClient[$clientId] ?? []);

        $registry->method('isAllowedForClient')
            ->willReturnCallback(static function (string $clientId, string $origin) use ($normalizedPerClient): bool {
                if ($clientId === '' || $origin === '' || strcasecmp($origin, 'null') === 0) {
                    return false;
                }
                $normalized = CORSAllowedOriginRegistry::normalizeOrigin($origin);
                if ($normalized === null) {
                    return false;
                }
                return in_array($normalized, $normalizedPerClient[$clientId] ?? [], true);
            });

        $registry->method('isAllowed')
            ->willReturnCallback(static function (string $origin) use ($globalUnion): bool {
                if ($origin === '' || strcasecmp($origin, 'null') === 0) {
                    return false;
                }
                $normalized = CORSAllowedOriginRegistry::normalizeOrigin($origin);
                return $normalized !== null && in_array($normalized, $globalUnion, true);
            });

        $registry->method('getAllowedOrigins')->willReturn($globalUnion);
        return $registry;
    }
}
