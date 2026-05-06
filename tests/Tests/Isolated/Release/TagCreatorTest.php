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

use OpenEMR\Release\TagCreationRequest;
use OpenEMR\Release\TagCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class TagCreatorTest extends TestCase
{
    public function testCreatesAnnotatedTagAndRef(): void
    {
        /** @var list<array{method: string, url: string, body: string}> $captured */
        $captured = [];
        $http = new MockHttpClient(
            function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $body = $options['body'] ?? null;
                $captured[] = [
                    'method' => $method,
                    'url' => $url,
                    'body' => is_string($body) ? $body : '',
                ];
                if (str_ends_with($url, '/git/tags')) {
                    return new MockResponse(
                        json_encode(['sha' => 'a' . str_repeat('b', 39)], JSON_THROW_ON_ERROR),
                        ['http_code' => 201],
                    );
                }
                if (str_ends_with($url, '/git/refs')) {
                    return new MockResponse('{}', ['http_code' => 201]);
                }
                self::fail('Unexpected URL: ' . $url);
            },
        );

        $creator = new TagCreator($http, 'https://api.example.test');
        $result = $creator->create(new TagCreationRequest(
            repo: 'openemr/openemr',
            version: '8.1.0',
            commitSha: str_repeat('c', 40),
            conductorPrUrl: 'https://github.com/openemr/openemr/pull/11896',
            appToken: 'test-token',
            date: '2026-04-29',
        ));

        self::assertSame('v8_1_0', $result->tagName);
        self::assertCount(2, $captured);

        self::assertSame('POST', $captured[0]['method']);
        self::assertStringContainsString('/repos/openemr/openemr/git/tags', $captured[0]['url']);
        $tagBody = json_decode($captured[0]['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($tagBody);
        self::assertSame('v8_1_0', $tagBody['tag']);
        self::assertIsString($tagBody['message']);
        self::assertStringContainsString('OpenEMR 8.1.0 released 2026-04-29', $tagBody['message']);
        self::assertStringContainsString('Created by openemr-release-bot', $tagBody['message']);

        self::assertSame('POST', $captured[1]['method']);
        self::assertStringContainsString('/repos/openemr/openemr/git/refs', $captured[1]['url']);
        $refBody = json_decode($captured[1]['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($refBody);
        self::assertSame('refs/tags/v8_1_0', $refBody['ref']);
    }

    public function testTagCreationFailureSurfaces(): void
    {
        $http = new MockHttpClient(static fn(): MockResponse => new MockResponse(
            '{"message":"validation failed"}',
            ['http_code' => 422],
        ));
        $creator = new TagCreator($http);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/HTTP 422/');
        $creator->create(new TagCreationRequest(
            repo: 'openemr/openemr',
            version: '8.1.0',
            commitSha: str_repeat('c', 40),
            conductorPrUrl: 'https://example.test',
            appToken: 'token',
            date: '2026-04-29',
        ));
    }

    public function testRequestRejectsBadVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TagCreationRequest(
            repo: 'openemr/openemr',
            version: '8.1',
            commitSha: str_repeat('c', 40),
            conductorPrUrl: 'https://example.test',
            appToken: 'token',
            date: '2026-04-29',
        );
    }

    public function testRequestRejectsBadCommitSha(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TagCreationRequest(
            repo: 'openemr/openemr',
            version: '8.1.0',
            commitSha: 'not-a-sha',
            conductorPrUrl: 'https://example.test',
            appToken: 'token',
            date: '2026-04-29',
        );
    }
}
