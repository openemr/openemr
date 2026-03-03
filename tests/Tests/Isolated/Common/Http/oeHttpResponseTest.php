<?php

/**
 * Isolated tests for oeHttpResponse wrapper
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\oeHttpResponse;
use PHPUnit\Framework\TestCase;

class oeHttpResponseTest extends TestCase
{
    /**
     * Build a mock PSR-7-like response object for testing
     * @param array<string, list<string>> $headers
     */
    private function makeMockResponse(
        string $body = '',
        int $statusCode = 200,
        array $headers = [],
    ): MockResponse {
        return new MockResponse($body, $statusCode, $headers);
    }

    public function testBodyReturnsStringBody(): void
    {
        $response = new oeHttpResponse($this->makeMockResponse('Hello World'));

        $this->assertSame('Hello World', $response->body());
    }

    public function testJsonDecodesAsArrayByDefault(): void
    {
        $json = (string) json_encode(['key' => 'value', 'num' => 42]);
        $response = new oeHttpResponse($this->makeMockResponse($json));

        $result = $response->json();
        $this->assertIsArray($result);
        $this->assertSame('value', $result['key']);
        $this->assertSame(42, $result['num']);
    }

    public function testJsonDecodesAsObjectWhenFalse(): void
    {
        $json = (string) json_encode(['key' => 'value']);
        $response = new oeHttpResponse($this->makeMockResponse($json));

        $result = $response->json(false);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame('value', $result->key);
    }

    public function testHeaderDelegatesToUnderlyingResponse(): void
    {
        $mock = $this->makeMockResponse('', 200, [
            'Content-Type' => ['application/json'],
        ]);
        $response = new oeHttpResponse($mock);

        $this->assertSame(['application/json'], $response->header('Content-Type'));
        $this->assertSame([], $response->header('X-Missing'));
    }

    public function testHeadersDelegatesToUnderlyingResponse(): void
    {
        $allHeaders = [
            'Content-Type' => ['text/html'],
            'X-Custom' => ['foo'],
        ];
        $response = new oeHttpResponse($this->makeMockResponse('', 200, $allHeaders));

        $this->assertSame($allHeaders, $response->headers());
    }

    public function testStatusReturnsStatusCode(): void
    {
        $response = new oeHttpResponse($this->makeMockResponse('', 404));

        $this->assertSame(404, $response->status());
    }

    public function testCallProxiesUnknownMethods(): void
    {
        $mock = $this->makeMockResponse();
        $response = new oeHttpResponse($mock);

        // Test __call directly since PHPStan can't resolve dynamic method dispatch
        $this->assertSame('custom:test', $response->__call('customMethod', ['test']));
    }
}

/**
 * Simple mock for PSR-7-like response used by oeHttpResponse tests
 */
class MockResponse
{
    /** @param array<string, list<string>> $headers */
    public function __construct(
        private readonly string $body = '',
        private readonly int $statusCode = 200,
        private readonly array $headers = [],
    ) {
    }

    public function getBody(): MockBody
    {
        return new MockBody($this->body);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return list<string> */
    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    /** @return array<string, list<string>> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function customMethod(string $arg): string
    {
        return "custom:$arg";
    }
}

/**
 * Simple mock for PSR-7-like response body
 */
class MockBody implements \Stringable
{
    public function __construct(private readonly string $body)
    {
    }

    public function __toString(): string
    {
        return $this->body;
    }
}
