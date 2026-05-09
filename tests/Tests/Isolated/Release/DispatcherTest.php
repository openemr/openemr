<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\Dispatcher;
use OpenEMR\Release\DispatchRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DispatcherTest extends TestCase
{
    private const SCHEMA_PATH = __DIR__ . '/../../../../tools/release/contracts/dispatch.schema.json';

    public function testValidRelCutDispatchesToBothConsumers(): void
    {
        /** @var list<array{url: string, body: string}> $captured */
        $captured = [];
        $http = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $body = $options['body'] ?? null;
                $captured[] = [
                    'url' => $url,
                    'body' => is_string($body) ? $body : '',
                ];
                return new MockResponse('', ['http_code' => 204]);
            },
        );

        $request = $this->buildRelCutRequest();
        $dispatcher = new Dispatcher($http, self::SCHEMA_PATH, 'https://api.example.test');
        $results = $dispatcher->dispatch($request, ['openemr/openemr-devops', 'openemr/website-openemr']);

        self::assertCount(2, $results);
        self::assertSame('openemr/openemr-devops', $results[0]->repo);
        self::assertTrue($results[0]->accepted);
        self::assertSame('openemr/website-openemr', $results[1]->repo);
        self::assertCount(2, $captured);
        self::assertStringContainsString('/repos/openemr/openemr-devops/dispatches', $captured[0]['url']);
        $body = json_decode($captured[0]['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame('openemr-rel-cut', $body['event_type']);
        self::assertIsArray($body['client_payload']);
        $payload = $body['client_payload'];
        self::assertIsArray($payload['data']);
        self::assertSame('rel-810', $payload['data']['branch']);
    }

    public function testInvalidPayloadRejectsBeforeAnyHttpCall(): void
    {
        $http = new MockHttpClient(static fn(): MockResponse => self::failOnHttp());
        $dispatcher = new Dispatcher($http, self::SCHEMA_PATH);

        $request = new DispatchRequest(
            event: DispatchRequest::EVENT_REL_CUT,
            repo: 'openemr/openemr',
            sha: 'not-a-sha', // invalid
            actor: 'bot',
            dispatchedAt: '2026-04-29T12:00:00Z',
            appToken: 'tok',
            data: ['branch' => 'rel-810', 'version' => '8.1.0', 'prev_release' => '8.0.0'],
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/schema validation/');
        $dispatcher->dispatch($request, ['openemr/openemr-devops']);
    }

    public function testProbeBypassesSchemaValidation(): void
    {
        $http = new MockHttpClient(static fn(): MockResponse => new MockResponse('', ['http_code' => 204]));
        $dispatcher = new Dispatcher($http, self::SCHEMA_PATH);

        $request = new DispatchRequest(
            event: DispatchRequest::EVENT_PROBE,
            repo: 'openemr/openemr',
            sha: str_repeat('a', 40),
            actor: 'bot',
            dispatchedAt: '2026-04-29T12:00:00Z',
            appToken: 'tok',
            data: ['anything' => 'goes'],
            probe: true,
        );

        $results = $dispatcher->dispatch($request, ['openemr/openemr-devops']);
        self::assertCount(1, $results);
        self::assertTrue($results[0]->accepted);
    }

    public function testGitHub422SurfacesAsRuntimeException(): void
    {
        $http = new MockHttpClient(static fn(): MockResponse => new MockResponse(
            '{"message":"validation failed"}',
            ['http_code' => 422],
        ));
        $dispatcher = new Dispatcher($http, self::SCHEMA_PATH);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/HTTP 422/');
        $dispatcher->dispatch($this->buildRelCutRequest(), ['openemr/openemr-devops']);
    }

    public function testEmptyTargetReposIsRejected(): void
    {
        $http = new MockHttpClient(static fn(): MockResponse => self::failOnHttp());
        $dispatcher = new Dispatcher($http, self::SCHEMA_PATH);

        $this->expectException(\InvalidArgumentException::class);
        $dispatcher->dispatch($this->buildRelCutRequest(), []);
    }

    private function buildRelCutRequest(): DispatchRequest
    {
        return new DispatchRequest(
            event: DispatchRequest::EVENT_REL_CUT,
            repo: 'openemr/openemr',
            sha: str_repeat('a', 40),
            actor: 'openemr-release-bot',
            dispatchedAt: '2026-04-29T12:00:00Z',
            appToken: 'tok',
            data: ['branch' => 'rel-810', 'version' => '8.1.0', 'prev_release' => '8.0.0'],
        );
    }

    private static function failOnHttp(): MockResponse
    {
        self::fail('Should not have made an HTTP call');
    }
}
