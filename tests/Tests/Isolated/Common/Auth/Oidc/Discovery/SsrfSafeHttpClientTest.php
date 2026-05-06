<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcHostResolverInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidationException;
use OpenEMR\Common\Auth\Oidc\Discovery\SsrfBlockedException;
use OpenEMR\Common\Auth\Oidc\Discovery\SsrfSafeHttpClient;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class SsrfSafeHttpClientTest extends TestCase
{
    public function testValidatedHostDispatchesWithCurlResolvePin(): void
    {
        $request = new Request('GET', 'https://example.com/.well-known/openid-configuration');
        $response = new Response(200, [], '{"ok":true}');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->expects($this->once())
            ->method('resolveAndAssert')
            ->with('example.com')
            ->willReturn(['93.184.216.34']);

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->expects($this->once())
            ->method('send')
            ->with($request, $this->matchesGuzzleOptions(['example.com:443:93.184.216.34']))
            ->willReturn($response);

        $client = new SsrfSafeHttpClient($inner, $validator);

        self::assertSame($response, $client->sendRequest($request));
    }

    public function testMultipleResolvedIpsAreAllPinnedAsCommaSeparated(): void
    {
        $request = new Request('GET', 'https://multi.example.net/jwks');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->method('resolveAndAssert')
            ->willReturn(['203.0.113.1', '203.0.113.2', '2001:db8::1']);

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->expects($this->once())
            ->method('send')
            ->with(
                $request,
                $this->matchesGuzzleOptions(['multi.example.net:443:203.0.113.1,203.0.113.2,2001:db8::1']),
            )
            ->willReturn(new Response(200));

        (new SsrfSafeHttpClient($inner, $validator))->sendRequest($request);
    }

    public function testNonStandardPortIsHonoredInResolvePin(): void
    {
        $request = new Request('GET', 'https://example.com:8443/jwks');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->method('resolveAndAssert')->willReturn(['93.184.216.34']);

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->expects($this->once())
            ->method('send')
            ->with($request, $this->matchesGuzzleOptions(['example.com:8443:93.184.216.34']))
            ->willReturn(new Response(200));

        (new SsrfSafeHttpClient($inner, $validator))->sendRequest($request);
    }

    public function testHttpDefaultsToPort80(): void
    {
        $request = new Request('GET', 'http://example.com/jwks');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->method('resolveAndAssert')->willReturn(['93.184.216.34']);

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->expects($this->once())
            ->method('send')
            ->with($request, $this->matchesGuzzleOptions(['example.com:80:93.184.216.34']))
            ->willReturn(new Response(200));

        (new SsrfSafeHttpClient($inner, $validator))->sendRequest($request);
    }

    /**
     * Build a PHPUnit Callback constraint that asserts the Guzzle options
     * passed to send() pin CURLOPT_RESOLVE to the expected entries and
     * disable redirects. Centralized so the per-test boilerplate stays
     * narrow and PHPStan sees a properly-typed callback signature.
     *
     * @param list<string> $expectedResolveEntries
     * @return Callback<mixed>
     */
    private function matchesGuzzleOptions(array $expectedResolveEntries): Callback
    {
        return $this->callback(static function (mixed $options) use ($expectedResolveEntries): bool {
            self::assertIsArray($options);
            $curlOptions = $options['curl'] ?? null;
            self::assertIsArray($curlOptions);
            self::assertSame($expectedResolveEntries, $curlOptions[\CURLOPT_RESOLVE] ?? null);
            self::assertFalse($options['allow_redirects'] ?? null);
            return true;
        });
    }

    public function testValidatorRejectionShortCircuitsBeforeAnyHttp(): void
    {
        $request = new Request('GET', 'https://rebinder.example/jwks');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->method('resolveAndAssert')
            ->willThrowException(new OidcUrlValidationException('URL host resolves to a private/local address'));

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->expects($this->never())->method('send');

        $client = new SsrfSafeHttpClient($inner, $validator);

        $this->expectException(SsrfBlockedException::class);
        $this->expectExceptionMessage('Blocked outbound request to rebinder.example');
        $client->sendRequest($request);
    }

    public function testEmptyHostInRequestUriIsBlocked(): void
    {
        // Hand-build a request with an empty host. Easier with a stub.
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn('');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->expects($this->never())->method('resolveAndAssert');

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->expects($this->never())->method('send');

        $this->expectException(SsrfBlockedException::class);
        $this->expectExceptionMessage('no host');

        (new SsrfSafeHttpClient($inner, $validator))->sendRequest($request);
    }

    public function testGuzzleConnectExceptionTranslatesToNetworkException(): void
    {
        $request = new Request('GET', 'https://example.com/jwks');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->method('resolveAndAssert')->willReturn(['93.184.216.34']);

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->method('send')
            ->willThrowException(new ConnectException('connect timeout', $request));

        $client = new SsrfSafeHttpClient($inner, $validator);

        try {
            $client->sendRequest($request);
            self::fail('Expected NetworkExceptionInterface');
        } catch (NetworkExceptionInterface $exception) {
            self::assertSame($request, $exception->getRequest());
            self::assertStringContainsString('connect timeout', $exception->getMessage());
        }
    }

    public function testGuzzleRequestExceptionTranslatesToRequestException(): void
    {
        $request = new Request('GET', 'https://example.com/jwks');

        $validator = $this->createMock(OidcHostResolverInterface::class);
        $validator->method('resolveAndAssert')->willReturn(['93.184.216.34']);

        $inner = $this->createMock(GuzzleClientInterface::class);
        $inner->method('send')
            ->willThrowException(new RequestException('bad gateway', $request));

        $client = new SsrfSafeHttpClient($inner, $validator);

        try {
            $client->sendRequest($request);
            self::fail('Expected RequestExceptionInterface');
        } catch (RequestExceptionInterface $exception) {
            self::assertSame($request, $exception->getRequest());
            self::assertStringContainsString('bad gateway', $exception->getMessage());
        }
    }
}
